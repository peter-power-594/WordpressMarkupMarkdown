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

	private $git_cnf_file = '';

	private $conf = [];


	protected $screen_edit_apost = '';
	protected $screen_edit_apage = '';
	protected $screen_list_posts = '';
	protected $screen_list_pages = '';

	public function __construct() {
		$this->prop[ 'label' ] = __( 'Advanced Custom Posts', 'markup-markdown' );
		$this->prop[ 'desc' ] = __( 'Manage your posts & pages as jekyll compatible static files.', 'markup-markdown' );
		if ( ! defined( 'MMD_ADDONS' ) || ( defined( 'MMD_ADDONS' ) && in_array( $this->prop[ 'slug' ], MMD_ADDONS ) === FALSE ) ) :
			$this->prop[ 'active' ] = 0;
			return FALSE; # Addon has been desactivated
		endif;
		$this->prop[ 'active' ] = 1;
		mmd()->default_conf = array( 'MMD_ACP_MANAGER' => 1 );
		if ( is_admin() ) :
			$this->acp_cnf_file = mmd()->conf_blog_prefix . 'conf_acp.json';
			$this->git_cnf_file = preg_replace( '#(\d+_\d+_)$#', '.$1', mmd()->conf_blog_prefix ) . 'conf_gif.json';
			if ( file_exists( $this->acp_cnf_file ) ) :
				$this->conf = json_decode( file_get_contents( $this->acp_cnf_file ), true );
				$this->screen_edit_apost = $this->conf[ 'blog_post_type' ];
				$this->screen_list_posts = 'edit-' . $this->screen_edit_apost;
				$this->screen_edit_apage = $this->conf[ 'blog_page_type' ];
				$this->screen_list_pages = 'edit-' . $this->screen_edit_apage;
				add_action( 'mmd_git_push', array( $this, 'push2git' ), 10, 2 );
				add_action( 'current_screen', array( $this, 'wp_screen_proxy' ), 5 );
			endif;
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
		if ( $this->screen_list_posts === $screen->id ) :
			$this->list_posts();
		elseif ( $this->screen_edit_apost === $screen->id ) :
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
		$acp_cnf = []; $git_cnf = [];
		$acp_cnf[ 'use_git' ] = filter_input( INPUT_POST, 'mmd_acp_use_git', FILTER_VALIDATE_INT );
		if ( ! isset( $acp_cnf[ 'use_git' ] ) ) :
			$acp_cnf[ 'use_git' ] = 0;
		endif;
		$git_cnf[ 'git_folder' ] = filter_input( INPUT_POST, 'mmd_acp_git_folder', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		if ( ! isset( $git_cnf[ 'git_folder' ] ) ) :
			$git_cnf[ 'git_folder' ] = '#';
		endif;
		$git_cnf[ 'git_username' ] = filter_input( INPUT_POST, 'mmd_acp_git_username', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		if ( ! isset( $git_cnf[ 'git_username' ] ) || empty( $git_cnf[ 'git_username' ] ) ) :
			$git_cnf[ 'git_username' ] = '';
		endif;
		$git_cnf[ 'git_useremail' ] = filter_input( INPUT_POST, 'mmd_acp_git_useremail', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		if ( ! isset( $git_cnf[ 'git_useremail' ] ) || empty( $git_cnf[ 'git_useremail' ] ) ) :
			$git_cnf[ 'git_useremail' ] = '';
		endif;
		$git_cnf[ 'git_repo_origin' ] = filter_input( INPUT_POST, 'mmd_acp_git_repo_origin', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		if ( ! isset( $git_cnf[ 'git_repo_origin' ] ) || empty( $git_cnf[ 'git_repo_origin' ] ) ) :
			$git_cnf[ 'git_repo_origin' ] = '';
		elseif ( $git_cnf[ 'git_folder' ] !== '#' ):
			if ( $this->load_kbjr_git() ) :
				$my_repo = \Kbjr\Git\Git::open( $this->sanitize_path( $git_cnf[ 'git_folder' ] ) );
				$my_repo->run( 'remote remove origin' );
				$my_repo->run( 'remote add origin ' . htmlspecialchars( $git_cnf[ 'git_repo_origin' ] ) );
			endif;
			unset( $git_cnf[ 'git_repo_origin' ] );
		endif;
		
		file_put_contents( $this->git_cnf_file, json_encode( $git_cnf ) );
		chmod( $this->git_cnf_file, 0600 );
		$acp_cnf[ 'blog_post_type' ] = filter_input( INPUT_POST, 'mmd_acp_blog_post_type', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		$acp_cnf[ 'blog_page_type' ] = filter_input( INPUT_POST, 'mmd_acp_blog_page_type', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		file_put_contents( $this->acp_cnf_file, json_encode( $acp_cnf ) );
		return $my_cnf; // Keep the return value
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
		$my_cnf = $this->conf;
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
		if ( ! file_exists( $my_tmpl ) ) :
			return false;
		endif;
		$my_cnf = []; $acp_cnf = []; $git_cnf = [];
		if ( file_exists( $this->acp_cnf_file ) ) :
			$acp_cnf = $this->conf;
			if ( ! isset( $acp_cnf ) || ! is_array( $acp_cnf ) ) :
				$acp_cnf = [];
			endif;
		endif;
		if ( file_exists( $this->git_cnf_file ) ) :
			$git_cnf = json_decode( file_get_contents( $this->git_cnf_file ), true );
			if ( ! isset( $git_cnf ) || ! is_array( $git_cnf ) ) :
				$git_cnf = [];
			endif;
		endif;
		$my_cnf = array_merge( $acp_cnf, $git_cnf );
		unset( $acp_cnf ); unset( $git_cnf );
		mmd()->clear_cache( $my_tmpl );
		include $my_tmpl;
	}


	/**
	 * Load the PHP-Git helper class
	 *
	 * @since 3.8.0
	 * @access public
	 *
	 * @return Boolean true if the class was loaded or false
	 */
	private function load_kbjr_git() {
		if ( class_exists( '\Kbjr\Git\Git', false ) ) :
			return true;
		endif;
		if ( ! file_exists( $this->git_cnf_file ) ) :
			return false;
		else:
			$git_cnf = json_decode( file_get_contents( $this->git_cnf_file ), true );
			if ( ! isset( $git_cnf ) || ! is_array( $git_cnf ) ) :
				return false;
			endif;
		endif;
		$this->conf = array_merge( $this->conf, $git_cnf );
		unset( $git_cnf );
		$dep_libs = [ 'Git', 'GitRepo' ];
		$git_helper = 1;
		foreach ( $dep_libs as $lib_idx => $lib_filename ) :
			$my_lib = mmd()->plugin_dir . 'MarkupMarkdown/Addons/Unsupported/AdvancedCustomPost/src/' . $lib_filename . '.php';
			if ( ! file_exists( $my_lib ) ) :
				$git_helper = 0;
				continue;
			endif;
			require_once $my_lib;
		endforeach;
		if ( ! $git_helper ) : # Something is missing
			return false;
		endif;
		return true;
	}


	/**
	 * Sanitize Windows / Linux / Path
	 *
	 * @access private
	 * @since 3.8.0
	 * 
	 * @return Bolean true if something was modified or fase in nothing was updated
	 */
	private function sanitize_path( $str ) {
		if ( ! isset( $str ) || empty( $str ) ) :
			return '';
		endif;
		if ( strpos( $str, '\\') !== false ) :
			$str = str_replace( '/', '\\', $str );
		else :
			$str = str_replace( '//', '/', $str );
		endif;
		return $str;
	}


	/**
	 * Commit and push data to a remote git repository
	 *
	 * @access private
	 * @since 3.8.0
	 * 
	 * @param String $mmd_file The target markdown file
	 * @return Bolean true in case of succes or false if an error occured
	 */
	public function push2git( $mmd_file, $mmd_title = '' ) {
		if ( ! isset( $mmd_file ) || empty( $mmd_file ) ) :
			return false;
		elseif ( ! $this->load_kbjr_git() ) :
			return false;
		endif;
		$git_folder = isset( $this->conf[ 'git_folder' ] ) ? $this->conf[ 'git_folder' ] : '';
		if ( empty( $git_folder ) || strpos( $git_folder, '#' ) !== false ) :
			return false;
		endif;
		$safe_git_folder = $this->sanitize_path( $git_folder );
		$safe_mmd_file = $this->sanitize_path( $mmd_file );
		if ( strpos( $safe_mmd_file, $safe_git_folder ) !== 0 ) :
			error_log( 'Damed something wrong' );
			return false;
		endif;
		$my_repo = \Kbjr\Git\Git::open( $safe_git_folder );
		$git_commands = [];
		if ( isset( $this->conf[ 'git_username' ] ) && ! empty( $this->conf[ 'git_username' ] ) ) :
			$git_commands[] = 'config user.name ' . htmlspecialchars( $this->conf[ 'git_username' ] );
		endif;
		if ( isset( $this->conf[ 'git_useremail' ] ) && ! empty( $this->conf[ 'git_useremail' ] ) ) :
			$git_commands[] = 'config user.email ' . htmlspecialchars( $this->conf[ 'git_useremail' ] );
		endif;
		$git_commands[] = 'add ./' . str_replace( $safe_git_folder, '', $safe_mmd_file );
		$git_commands[] = 'commit -v -m ' . escapeshellarg( sprintf( __( 'Updating post %s', 'markup-markdown' ), $mmd_title ) );
		$git_commands[] = 'push origin master';
		$log = '';
		while ( $log !== false && isset( $git_commands[ 0 ] ) ) :
			$log = $my_repo->run( array_shift( $git_commands ) );
		endwhile;
		if ( ! $log ) :
			return false;
		endif;
		return true;
	}


}
