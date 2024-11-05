<?php

namespace MarkupMarkdown\AutoPlugs;

defined( 'ABSPATH' ) || exit;


class BuddyPressDocs {


	/**
	 * @property String $plugin_uri
	 * The relative path to the plugin directory used for assets
	 * 
	 * @since 3.10.0
	 * @access private
	 */
	private $plugin_uri = '';


	public function __construct() {
		if ( file_exists( WP_PLUGIN_DIR . '/buddypress-docs/bp-docs.php' ) ) :
			if ( function_exists( 'bp_docs_init' ) ) :
				define( 'MMD_BUDDYPRESSDOCS_PLUG', true );
			endif;
			$this->init();
		endif;
	}


	public function init() {
		if ( ! is_admin() ) :
			add_action( 'bp_enqueue_scripts', array( $this, 'load_edit_mmdform' ) );
		endif;
	}


	/**
	 * Check we are on a bbpress related template and trigger the launch of the markdown editor
	 * 
	 * @since 3.10.0
	 * @access public 
	 * 
	 * @return Boolean TRUE if the edit form view was triggered or FALSE
	 */
	public function load_edit_mmdform() {
		if ( ! function_exists( 'bp_docs_is_doc_create' ) || ! function_exists( 'bp_docs_is_doc_edit' ) ) :
			return false;
		endif;
		if ( ! is_admin() ) :
			$should_load = false;
			if ( bp_docs_is_doc_create() || bp_docs_is_doc_edit() ) :
				$should_load = true;
			endif;
			if ( ! $should_load ) :
				return false;
			endif;
			add_filter( 'mmd_frontend_enabled', '__return_true' );
		endif;
		$this->plugin_uri = mmd()->plugin_uri;
		add_action( 'mmd_load_engine_stylesheets', array( $this, 'load_engine_stylesheets' ) );
		add_action( 'mmd_load_engine_scripts', array( $this, 'load_engine_scripts' ) );
		return true;
	}


	public function load_engine_stylesheets() {
		wp_enqueue_style( 'markup_markdown__bp_editor', $this->plugin_uri . 'assets/buddypress-docs/css/field.min.css', array( 'markup_markdown__wordpress_richedit' ), buddypress()->version );
	}


	public function load_engine_scripts() {
		wp_enqueue_script( 'markup_markdown__bp_editor', $this->plugin_uri . 'assets/buddypress-docs/js/field.min.js', array( 'markup_markdown__wordpress_richedit' ), buddypress()->version, true );
	}


}


new \MarkupMarkdown\AutoPlugs\BuddyPressDocs();
