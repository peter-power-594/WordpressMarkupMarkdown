<?php

namespace MarkupMarkdown\Addons\Unsupported;

defined( 'ABSPATH' ) || exit;

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
		add_action( 'current_screen', array( $this, 'scandir_for_posts' ) );
		# New screen?
	}


	public function scandir_for_posts() {
		if ( ! function_exists( 'get_current_screen' ) ) :
			return false; # Hook not ready
		endif;
		$screen = get_current_screen();
		if ( ! isset( $screen ) || ! is_object( $screen ) || ! isset( $screen->id ) || 'edit-post' !== $screen->id ) :
			return false; # Not editing a post
		endif;
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
