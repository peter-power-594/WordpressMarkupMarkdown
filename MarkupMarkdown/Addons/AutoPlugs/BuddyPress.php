
<?php

namespace MarkupMarkdown\Addons\AutoPlugs;

defined( 'ABSPATH' ) || exit;


class BuddyPress {


    public function __construct() {
		if ( file_exists( WP_PLUGIN_DIR . '/buddypress/buddypress.php' ) && ! is_admin() ) :
			if ( class_exists( 'BuddyPress' ) ) :
				define( 'MMD_BUDDYPRESS_PLUG', true );
			endif;
			add_action( 'bbp_enqueue_scripts', array( $this, 'load_edit_mmdform' ) );
			add_filter( 'mmd_proxy_filters', array( $this, 'get_buddypress_filters' ), 10, 1 );
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
				# Messages
				'bp_get_message_thread_excerpt', 'bp_get_messages_content_value',
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
			return FALSE;
		endif;
		if ( ! bbp_use_wp_editor() || ! is_bbpress() ) :
			return FALSE;
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


new \MarkupMarkdown\Addons\AutoPlugs\BuddyPress();
