<?php

namespace MarkupMarkdown\Addons\Unsupported;

defined( 'ABSPATH' ) || exit;

class MarkdownPost {


	private $data = array();


	public function __construct( $file ) {
		$this->parse_file( apply_filters( 'mmd_jekyll_posts_folder', ABSPATH . '_posts' ), $file );
	}


	private function parse_file( $path = '', $file = '' ) {
		if ( empty( $path ) || empty( $file ) || ! is_dir( $path ) ) :
			return false;
		endif;
		$my_post = $path . '/' . $file;
		if ( ! file_exists( $my_post ) ) :
			return false;
		endif;
		$cache_file = mmd()->cache_dir . '/' . mmd()->curr_blog . '_' . preg_replace( '#\.[a-z]+$#', '.json', $file );
		if ( file_exists( $cache_file ) ) :
			$this->data = json_decode( file_get_contents( $cache_file ) );
			if ( function_exists( 'md5_file' ) && md5_file( $my_post ) === $this->data->md5 ) :
				return true;
			endif;
		endif;
		$post_tmp = file_get_contents( $my_post );
		if ( ! $post_tmp || strpos( $post_tmp, '---' ) === false ) :
			return false;
		endif;
		$post_row = $this->extract_headers( explode( "\n", explode( "---\n", $post_tmp )[ 1 ] ) );
		if ( ! isset( $post_row[ 'post_type' ] ) ) :
			$post_row[ 'post_type' ] = 'post';
		endif;
		if ( file_exists( 'md5_file' ) ) :
			$post_row[ 'md5' ] = md5_file( $my_post );
		endif;
		$post_row[ 'content' ] = explode( "---\n", $post_tmp )[ 2 ];
		unset( $post_tmp );
		file_put_contents( $cache_file, json_encode( $post_row ) );
		return true;
	}


	private function extract_headers( $header_rows = array() ) {
		$my_rows = array();
		foreach( $header_rows as $row_data ) :
			if ( strpos( $row_data, ':' ) === false ) :
				continue;
			endif;
			preg_match( '#([a-z]+):#', $row_data, $row_key );
			$row_data = preg_replace( '#[a-z]+:[\s\t]*#', '', $row_data );
			if ( substr( $row_data, 0, 1 ) === '[' && substr( $row_data, -1 ) === ']' ) :
				$row_val = explode( ',', preg_replace( '#(^\[|\]$)#', '', $row_data ) );
				foreach( $row_val as $idx => $val ) :
					$row_val[ $idx ] = preg_replace( '#(^\"|\"$)#', '', trim( $val ) );
				endforeach;
			else :
				$row_val = preg_replace( '#(^\"|\"$)#', '', $row_data );
			endif;
			$my_rows[ $row_key[ 1 ] ] = $row_val;
		endforeach;
		return $my_rows;
	}


	public function __get( $name ) {
		if ( isset( $this->data->$name ) ) {
			return $this->data->$name;
		}
		return null;
	}
}


class Jekyll {


	private $prop = array(
		'slug' => 'jekyllmanager',
		'release' => 'experimental',
		'active' => 0
	);


	public function __construct() {
		$this->prop[ 'label' ] = __( 'Jekyll Data Manager', 'markup-markdown' );
		$this->prop[ 'desc' ] = __( 'Manage your WP posts or pages as jekyll compatible static files', 'markup-markdown' );
		if ( ! defined( 'MMD_ADDONS' ) || ( defined( 'MMD_ADDONS' ) && in_array( $this->prop[ 'slug' ], MMD_ADDONS ) === FALSE ) ) :
			$this->prop[ 'active' ] = 0;
			return FALSE; # Addon has been desactivated
		endif;
		$this->prop[ 'active' ] = 1;
		mmd()->default_conf = array( 'MMD_JEKYLL_MANAGER' => 1 );
		add_action( 'current_screen', array( $this, 'plug_mmd_posts' ) );
		# New screen?
	}


	public function plug_mmd_posts() {
		if ( ! function_exists( 'get_current_screen' ) ) :
			return false; # Hook not ready
		endif;
		$screen = get_current_screen();
		if ( ! isset( $screen ) || ! is_object( $screen ) || ! isset( $screen->id ) ) :
			return false; # Not editing a post
		endif;
		error_log( $screen->id );
		if ( 'edit-post' === $screen->id ) :
			return $this->list_mmd_posts();
		elseif ( 'post' === $screen->id ) :
			return $this->edit_mmd_post();
		endif;
	}


	private function edit_mmd_post() {
		require mmd()->plugin_dir . 'MarkupMarkdown/Addons/Unsupported/Jekyll/admin-tmpl/edit-post.php';
		exit;
	}


	private function list_mmd_posts() {
		$posts_dir = apply_filters( 'mmd_jekyll_posts_folder', ABSPATH . '_posts' );
		if ( ! is_dir( $posts_dir ) ) :
			return false;
		endif;
		if ( ! file_exists( mmd()->cache_dir . '/jekyll_posts.json' ) ) :
			$this->cache_posts( $posts_dir );
		endif;
		require mmd()->plugin_dir . 'MarkupMarkdown/Addons/Unsupported/Jekyll/admin-tmpl/list-posts.php';
		exit;
	}

	/**
	 * Parse the target posts directory and generate the json file
	 *
	 * @param String $posts_dir The folder with the static markdown post
	 * @return Boolean TRUE in case of success or FALSE
	 */
	private function cache_posts( $posts_dir = '' ) {
		if ( empty( $posts_dir ) ) :
			return false;
		endif;
		$dh = opendir( $posts_dir );
		if ( ! $dh ) :
			return false;
		endif;
		$files = [];
		while ( ( $file = readdir( $dh ) ) !== false ) :
			if ( $file === '.' || $file === '..' ) :
				continue;
			endif;
			$file_mime = pathinfo( $posts_dir . '/' . $file, PATHINFO_EXTENSION );
			if ( in_array( $file_mime, array( 'md', 'markdown' ) ) !== false ) :
				$files[] = $file;
			endif;
		endwhile;
		closedir( $dh );
		file_put_contents( mmd()->cache_dir . '/jekyll_posts.json', json_encode( array( "data" => $files ) ) );
		return true;
	}


	public function __get( $name ) {
		if ( array_key_exists( $name, $this->prop ) ) {
			return $this->prop[ $name ];
		}
		return 'mmd_undefined';
	}

}

if ( ! function_exists( 'mmd_get_post' ) ) :
	function mmd_get_post( $post_file ) {
		$posts_dir = apply_filters( 'mmd_jekyll_posts_folder', ABSPATH . '_posts' );
		$my_posts = \MarkupMarkdown\Addons\Unsupported\MarkdownPost( $posts_dir, $post_file );
	}
endif;
