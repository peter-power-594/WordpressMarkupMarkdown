<?php

namespace MarkupMarkdown\Addons\AutoPlugs;

defined( 'ABSPATH' ) || exit;


class BuddyPress {


	/**
	 * @property Array $allowed_hooks
	 * The list of default hooks where the markdown editor will be used in the backend
	 *
	 * @since 2.0.0
	 * @access private
	 */
	private $allowed_hooks = array(
		'toplevel_page_bp-groups'
	);


    public function __construct() {
		if ( file_exists( WP_PLUGIN_DIR . '/buddypress/bp-loader.php' ) ) :
			if ( class_exists( 'BuddyPress' ) ) :
				define( 'MMD_BUDDYPRESS_PLUG', true );
			endif;
			# add_action( 'buddypress_enqueue_scripts', array( $this, 'load_edit_mmdform' ) );
			if ( ! is_admin() ) :
				add_filter( 'mmd_proxy_filters', array( $this, 'get_buddypress_filters' ), 10, 1 );
			else :
				$action = filter_input( INPUT_GET, 'action', FILTER_SANITIZE_SPECIAL_CHARS );
				if ( $action === 'edit' ) :
					add_action( 'current_screen', array( $this, 'grant_buddypress_hooks' ) );
				endif;
			endif;
		endif;
    }


	public function get_buddypress_filters( $arr = [] ) {
		return array_merge(
			$arr,
			array(
				# Activities
				'bp_get_activity_content_body', 'bp_get_activity_parent_content', 'bp_get_activity_content', 'bp_get_activity_feed_item_description',
				# Blog
				'bp_get_blog_description', 'bp_get_blog_latest_post_content', 
				# Group
				'bp_get_group_description',
				# Messages
				'bp_get_message_thread_excerpt', 'bp_get_messages_content_value',
			)
		);
	}


	public function grant_buddypress_hooks( $current_screen ) {
		if ( isset( $current_screen->id ) && in_array( $current_screen->id, $this->allowed_hooks ) !== false ) :
			add_filter( 'mmd_backend_enabled', '__return_true', 11 );
		endif;
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
		add_filter( 'mmd_frontend_enabled', '__return_true' );
		$this->plugin_uri = mmd()->plugin_uri;
		add_action( 'mmd_load_engine_stylesheets', array( $this, 'load_engine_stylesheets' ) );
		add_action( 'mmd_load_engine_scripts', array( $this, 'load_engine_scripts' ) );
		return true;
	}


	public function load_engine_stylesheets() {
		# wp_enqueue_style( 'markup_markdown__bbpress_editor', $this->plugin_uri . 'assets/bbpress/css/field.min.css', array( 'markup_markdown__wordpress_richedit' ), bbpress()->version );
	}


	public function load_engine_scripts() {
		# wp_enqueue_script( 'markup_markdown__bbpress_editor', $this->plugin_uri . 'assets/bbpress/js/field.min.js', array( 'markup_markdown__wordpress_richedit' ), bbpress()->version, true );
	}


}


new \MarkupMarkdown\Addons\AutoPlugs\BuddyPress();
