<?php

namespace MarkupMarkdown\AutoPlugs;

defined( 'ABSPATH' ) || exit;


/**
 * Add markdown to primary bbpress filters
 *
 * @since 3.9.0
 *
 */
class BBPress {


	/**
	 * @var String $plugin_uri the relative path to the plugin directory used for assets
	 */
	private $plugin_uri = '';


	public function __construct() {
		if ( file_exists( WP_PLUGIN_DIR . '/bbpress/bbpress.php' ) ) :
			if ( function_exists( 'bbpress' ) ) :
				define( 'MMD_BBPRESS_PLUG', true );
			endif;
		endif;
		if ( defined( 'MMD_BBPRESS_PLUG' ) && MMD_BBPRESS_PLUG && ! is_admin() ) :
			if ( ! defined( 'WP_MMD_MEDIA_UPLOADER' ) ) :
				define( 'WP_MMD_MEDIA_UPLOADER', FALSE );
			endif;
			add_action( 'bbp_enqueue_scripts', array( $this, 'load_edit_mmdform' ) );
			add_filter( 'mmd_proxy_filters', array( $this, 'get_bbpress_filters' ), 10, 1 );
		endif;
	}


	public function get_bbpress_filters( $arr = [] ) {
		return array_merge(
			$arr,
			array(
				'bbp_get_forum_content',
				'bbp_get_reply_content',
				'bbp_get_topic_content'
			)
		);
	}


	/**
	 * Check we are on a bbpress related template and trigger the launch of the markdown editor
	 * 
	 * @since 3.9.0
	 * @access public 
	 * 
	 * @return Boolean TRUE if the edit form view was triggered or FALSE
	 */
	public function load_edit_mmdform() {
		if ( ! function_exists( 'bbp_use_wp_editor' ) || ! function_exists( 'is_bbpress' ) ) :
			return false;
		endif;
		if ( ! bbp_use_wp_editor() || ! is_bbpress() ) :
			return false;
		endif;
		add_filter( 'mmd_frontend_enabled', '__return_true' );
		$this->plugin_uri = mmd()->plugin_uri;
		add_action( 'mmd_load_engine_stylesheets', array( $this, 'load_engine_stylesheets' ) );
		add_action( 'mmd_load_engine_scripts', array( $this, 'load_engine_scripts' ) );
		return TRUE;
	}


	public function load_engine_stylesheets() {
		wp_enqueue_style( 'markup_markdown__bbpress_editor', $this->plugin_uri . 'assets/bbpress/css/field.min.css', array( 'markup_markdown__wordpress_richedit' ), bbpress()->version );
	}


	public function load_engine_scripts() {
		wp_enqueue_script( 'markup_markdown__bbpress_editor', $this->plugin_uri . 'assets/bbpress/js/field.min.js', array( 'markup_markdown__wordpress_richedit' ), bbpress()->version, true );
	}


}


new \MarkupMarkdown\AutoPlugs\BBPress();
