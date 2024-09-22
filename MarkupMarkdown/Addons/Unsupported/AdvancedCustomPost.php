<?php

namespace MarkupMarkdown\Addons\Unsupported;

defined( 'ABSPATH' ) || exit;

class AdvancedCustomPost {


	private $prop = array(
		'slug' => 'acp',
		'release' => 'experimental',
		'active' => 0
	);


	private $acp_cnf_file = '';


	private $conf = [];


	public function __construct() {
		$this->prop[ 'label' ] = __( 'Advanced Custom Posts', 'markup-markdown' );
		$this->prop[ 'desc' ] = __( 'Manage your posts & pages as jekyll compatible static files.', 'markup-markdown' );
		if ( ! defined( 'MMD_ADDONS' ) || ( defined( 'MMD_ADDONS' ) && in_array( $this->prop[ 'slug' ], MMD_ADDONS ) === FALSE ) ) :
			$this->prop[ 'active' ] = 0;
			return FALSE; # Addon has been desactivated
		endif;
		$this->prop[ 'active' ] = 1;
		mmd()->default_conf = array( 'MMD_ACP_MANAGER' => 1 );
		$this->acp_cnf_file = mmd()->conf_blog_prefix . 'conf_acp.json';
		if ( file_exists( $this->acp_cnf_file ) ) :
			$this->conf = json_decode( file_get_contents( $this->acp_cnf_file ), true );
			add_action( 'current_screen', array( $this, 'wp_screen_proxy' ), 5 );
		endif;
		if ( is_admin() ) :
			add_filter( 'mmd_acp_post_types', array( $this, 'cpt_plug_filters' ) );
			add_filter( 'mmd_verified_config', array( $this, 'update_config' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'load_acp_assets' ), 11 , 1 );
		endif;
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


	public function load_acp_assets( $hook ) {
		if ( 'settings_page_markup-markdown-admin' === $hook ) :
			add_action( 'mmd_tabmenu_options', array( $this, 'add_tabmenu' ) );
			add_action( 'mmd_tabcontent_options', array( $this, 'add_tabcontent' ) );
		endif;
	}


	/**
	 * Filter to include or exclude custom post types
	 *
	 * @since 3.8.0
	 * @access public
	 *
	 * @param Array $ctp Custom post types objects list
	 * @return Array Selected custom post types only
	 */
	public function cpt_plug_filters( $ctp = [] ) {
		$safe_ctp = [];
		foreach ( $ctp as $slug => $val ) :
			if ( preg_match( '#^acf-#', $slug ) ) :
				continue;
			elseif ( preg_match( '#^admin#', $slug ) ) :
				continue;
			endif;
			$safe_ctp[ $slug ] = $val;
		endforeach;
		return $safe_ctp;
	}


	/**
	 * Filter to parse options inside the options screen when the form was submitted
	 *
	 * @since 3.8.0
	 * @access public
	 *
	 * @param Array $my_cnf Current configuration
	 * @return Array Dummy empty data
	 */
	public function update_config( $my_cnf ) {
		$acp_cnf[ 'use_git' ] = filter_input( INPUT_POST, 'mmd_use_git', FILTER_VALIDATE_INT );
		if ( ! isset( $acp_cnf[ 'use_git' ] ) ) :
			$acp_cnf[ 'use_git' ] = 0;
		endif;
		$acp_cnf[ 'blog_post_type' ] = filter_input( INPUT_POST, 'mmd_acp_blog_post_type', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		$acp_cnf[ 'blog_page_type' ] = filter_input( INPUT_POST, 'mmd_acp_blog_page_type', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		file_put_contents( $this->acp_cnf_file, json_encode( $acp_cnf ) );
		return [];
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
		include mmd()->plugin_dir . 'MarkupMarkdown/Addons/Unsupported/AdvancedCustomPost/templates/mmd-list-posts.php';
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
		include mmd()->plugin_dir . 'MarkupMarkdown/Addons/Unsupported/AdvancedCustomPost/templates/mmd-edit-post.php';
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



	/**
	 * Show the tab item inside the options screen
	 *
	 * @since 3.8.0
	 * @access public
	 *
	 * @return Void
	 */
	public function add_tabmenu() {
		echo "\t\t\t\t\t\t<li><a href=\"#tab-advancedcustompost\">" . __( 'Advanced Custom Post', 'markup-markdown' ) . "</a></li>\n";
	}


	/**
	 * Display options inside the options screen
	 *
	 * @since 3.8.0
	 * @access public
	 *
	 * @return Void
	 */
	public function add_tabcontent() {
		$my_tmpl = mmd()->plugin_dir . '/MarkupMarkdown/Addons/Unsupported/AdvancedCustomPost/templates/mmd-options.php';
		$my_cnf = [];
		if ( file_exists( $this->acp_cnf_file) ) :
			$my_cnf = $this->conf;
			if ( ! isset( $my_cnf ) || ! is_array( $my_cnf ) ) :
				$my_cnf = [];
			endif;
		endif;
		if ( file_exists( $my_tmpl ) ) :
			mmd()->clear_cache( $my_tmpl );
			include $my_tmpl;
		endif;
	}

	
}
