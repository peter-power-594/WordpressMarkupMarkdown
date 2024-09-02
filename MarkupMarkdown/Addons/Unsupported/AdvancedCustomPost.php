<?php

namespace MarkupMarkdown\Addons\Unsupported;

defined( 'ABSPATH' ) || exit;

class AdvancedCustomPost {


	private $prop = array(
		'slug' => 'acp',
		'release' => 'experimental',
		'active' => 0
	);


	public function __construct() {
		$this->prop[ 'label' ] = __( 'Advanced Custom Posts', 'markup-markdown' );
		$this->prop[ 'desc' ] = __( 'Manage your posts & pages as jekyll compatible static files', 'markup-markdown' );
		if ( ! defined( 'MMD_ADDONS' ) || ( defined( 'MMD_ADDONS' ) && in_array( $this->prop[ 'slug' ], MMD_ADDONS ) === FALSE ) ) :
			$this->prop[ 'active' ] = 0;
			return FALSE; # Addon has been desactivated
		endif;
		$this->prop[ 'active' ] = 1;
		mmd()->default_conf = array( 'MMD_ACP_MANAGER' => 1 );
		add_action( 'current_screen', array( $this, 'wp_screen_proxy' ), 5 );
		# New screen?
	}


	public function wp_screen_proxy() {
		if ( ! function_exists( 'get_current_screen' ) ) :
			return false; # Hook not ready
		endif;
		$screen = get_current_screen();
		if ( ! isset( $screen ) || ! is_object( $screen ) || ! isset( $screen->id ) ) :
			return false; # Not an interesting screen
		endif;
		if ( 'edit-post' === $screen->id ) :
			$this->list_posts();
		elseif ( 'post' === $screen->id ) :
			$this->edit_post( filter_input( INPUT_GET, 'post', FILTER_SANITIZE_SPECIAL_CHARS ) );
		endif;
	}


	/**
	 * List the posts from the __post directory
	 * 
	 * @return Bolean false if something went wrong
	 */
	private function list_posts() {
		$posts_dir = apply_filters( 'mmd_acp_posts_folder', ABSPATH . '_posts' );
		if ( ! is_dir( $posts_dir ) ) :
			return false;
		endif;
		if ( ! file_exists( mmd()->cache_dir . '/acp_posts.json' ) ) :
			$this->cache_posts( $posts_dir );
		else:
			$scan_nonce = filter_input( INPUT_GET, 'mmd_scan_dir', FILTER_SANITIZE_SPECIAL_CHARS );
			if ( isset( $scan_nonce ) && wp_verify_nonce( $scan_nonce, 'scan-dir' ) ) :
				@unlink( mmd()->cache_dir . '/acp_posts.json' );
				$this->cache_posts( $posts_dir );
			endif;
		endif;
		include mmd()->plugin_dir . 'MarkupMarkdown/Addons/Unsupported/AdvancedCustomPost/admin-tmpl/mmd-list-posts.php';
		exit;
	}


	/**
	 * Edit the post from a markdown file
	 * 
	 * @return Bolean false if something went wrong
	 */
	private function edit_post( $post ) {
		if ( ! isset( $post ) || empty( $post ) || is_numeric( $post ) ) :
			return false;
		endif;
		include mmd()->plugin_dir . 'MarkupMarkdown/Addons/Unsupported/AdvancedCustomPost/admin-tmpl/mmd-edit-post.php';
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
		file_put_contents( mmd()->cache_dir . '/acp_posts.json', json_encode( array( "data" => $files ) ) );
		return true;
	}


	public function __get( $name ) {
		if ( array_key_exists( $name, $this->prop ) ) {
			return $this->prop[ $name ];
		}
		return 'mmd_undefined';
	}

}
