<?php

namespace MarkupMarkdown\Core;

defined( 'ABSPATH' ) || exit;
defined( 'MMD_PLUGIN_ACTIVATED' ) || exit;


class Support {


	/**
	 * @property Boolean $mmd_syntax To know if markdown syntax was enabled - or not
	 *
	 * @since 2.0.0
	 * @access private
	 */
	private $mmd_syntax = 1;


	public function __construct() {
		# Add Support
		add_action( 'init', array( $this, 'add_markdown_support' ), 10, 0 );
		if ( is_admin() ) :
			# Check then enable or disable the markdown editor on the backend
			add_action( 'init', array( $this, 'prepare_markdown_editor' ), 9999, 0 );
			# Enable or disable the post filters
			add_action( 'wp_loaded', array( $this, 'set_content_filters' ), 10, 0 );
		else :
			# Check then enable or disable the markdown editor on the frontend
			add_filter( 'mmd_front_enabled', array( $this, 'current_template_allowed' ), 9, 1 );
			add_action( 'get_header', array( $this, 'prepare_markdown_editor' ), 9, 0 );
			# Enable or disable the post filters
			add_action( 'get_header', array( $this, 'set_content_filters' ), 10, 0 );
		endif;
	}


	/**
	 * Tiny filter to switch on / off the loading of the markdown editor on the frontend
	 *
	 * @since 3.3.0
	 * @access public
	 *
	 * @param Boolean TRUE if the editor can be loaded on the frontend.
	 *
	 * @return Boolean TRUE if enabled or FALSE if disabed
	 */
	public function current_template_allowed( $bool = true ) {
		if ( ! get_current_user_id() ) :
			# Disable *Guest* users
			return false;
		endif;
		$user = wp_get_current_user();
		if ( $user && ! $user->has_cap( 'edit_posts' ) ) :
			# Disable *Subscribers* or users without edit permissions
			return false;
		endif;
		return $bool;
	}


	/**
	 * Add markdown support to every public custom post type
	 *
	 * @since 1.7.0
	 * @access public
	 *
	 * @return Void
	 */
	public function add_markdown_support() {
		# Custom Post Type support
		$post_types = get_post_types( array( 'public' => true, '_builtin' => false ) );
		if ( ! $post_types ) :
			$post_types = [];
		endif;
		array_unshift( $post_types, 'post', 'page' );
		foreach( $post_types as $post_type ) :
			add_post_type_support( $post_type, 'markup_markdown' );
		endforeach;
	}


	/**
	 * Get Current Post Type in the WordPress Admin Area
	 * @source https://wp-mix.com/get-current-post-type-wordpress/
	 *
	 * @since 1.7.0
	 * @access public
	 *
	 * @return String Post type in use or FALSE
	 */
	private function get_current_post_type() {
		global $post, $typenow, $current_screen;
		if ( $post && $post->post_type ) :
			return $post->post_type;
		elseif ( $typenow ) :
			return $typenow;
		elseif ( $current_screen && $current_screen->post_type ) :
			return $current_screen->post_type;
		elseif ( isset( $_REQUEST[ 'post_type' ] ) ) :
			return sanitize_key( $_REQUEST[ 'post_type' ] );
		elseif ( isset( $_REQUEST[ 'post' ] ) && function_exists( 'get_post_type' ) ) :
			return get_post_type( (int)$_REQUEST[ 'post' ] );
		else :
			return FALSE;
		endif;
	}


	/**
	 * Prepare the Markdown editor
	 *
	 * @since 1.7.0
	 * @access public
	 *
	 * @return Boolean TRUE if Markdown was activated of FALSE if disabled
	 */
	public function prepare_markdown_editor() {
		if ( ! $this->mmd_syntax ) :
			$this->mmd_syntax = 0;
			return FALSE;
		else:
			$my_post_type = $this->get_current_post_type();
			if ( isset( $my_post_type ) && ! empty( $my_post_type ) && ! post_type_supports( $my_post_type, 'markup_markdown' ) ) :
				$this->mmd_syntax = 0;
			endif;
			if ( ! is_admin() ) :
				$mmd_tmpl_enabled = apply_filters( 'mmd_front_enabled', false );
				if ( ! (int)$mmd_tmpl_enabled ) :
					return FALSE;
				endif;
			elseif ( ! $this->mmd_syntax ) :
				return FALSE;
			endif;
		endif;
		# Markdown can be used with custom fields, so only disable TinyMCE / Guternberg hooks when support is enabled
		# Clear static cache when post is saved
		if ( defined( 'WP_MMD_OPCACHE' ) && WP_MMD_OPCACHE ) :
			add_action( 'save_post', array( $this, 'clear_post_cache' ), 10, 3 );
		endif;
		# https://stackoverflow.com/questions/12648402/how-can-i-completely-remove-tinymce-in-wordpress/12648896
		add_filter( 'user_can_richedit', '__return_false', 50 );
		# https://wordpress.stackexchange.com/questions/72865/is-it-possible-to-remove-wysiwyg-for-a-certain-custom-post-type/72867
		add_filter( 'wp_editor_settings', function( $settings ) {
			$settings[ 'tinymce' ] = false;
			$settings[ 'quicktags' ] = false;
			$settings[ 'media_buttons' ] = false;
			return $settings;
		}, 10, 2 );
		# Disable Gutenberg
		$this->remove_gutenberg_hooks();
		# WYSIWYG loading assets has moved to includes/markup-markdown/addons
		return TRUE;
	}


	public function clear_post_cache( $post_ID, $post, $update ) {
		# If a modification was made, we must clear the cache to refresh it
		$cache_content = WP_CONTENT_DIR . "/mmd-cache/." . get_main_site_id() . '_' . $post_ID . ".html";
		if ( file_exists( $cache_content ) ) :
			@unlink( $cache_content );
		endif;
		if ( function_exists( 'opcache_invalidate' ) ) :
			opcache_invalidate( $cache_content );
		endif;
	}


	/**
	 * @source Classic Editor
	 */
	private function remove_gutenberg_hooks() {
		// Always remove the "Try Gutenberg" dashboard widget. See https://core.trac.wordpress.org/ticket/44635.
		remove_action( 'try_gutenberg_panel', 'wp_try_gutenberg_panel' );

		// Consider disabling other Block Editor functionality
		add_filter( 'use_block_editor_for_post_type', '__return_false', 100 );

		// Support older Gutenberg versions
		add_filter( 'gutenberg_can_edit_post_type', '__return_false', 100 );

		remove_action( 'admin_menu', 'gutenberg_menu' );
		remove_action( 'admin_init', 'gutenberg_redirect_demo' );

		// Gutenberg 5.3+
		remove_action( 'wp_enqueue_scripts', 'gutenberg_register_scripts_and_styles' );
		remove_action( 'admin_enqueue_scripts', 'gutenberg_register_scripts_and_styles' );
		remove_action( 'admin_notices', 'gutenberg_wordpress_version_notice' );
		remove_action( 'rest_api_init', 'gutenberg_register_rest_widget_updater_routes' );
		remove_action( 'admin_print_styles', 'gutenberg_block_editor_admin_print_styles' );
		remove_action( 'admin_print_scripts', 'gutenberg_block_editor_admin_print_scripts' );
		remove_action( 'admin_print_footer_scripts', 'gutenberg_block_editor_admin_print_footer_scripts' );
		remove_action( 'admin_footer', 'gutenberg_block_editor_admin_footer' );
		remove_action( 'admin_enqueue_scripts', 'gutenberg_widgets_init' );
		remove_action( 'admin_notices', 'gutenberg_build_files_notice' );

		remove_filter( 'load_script_translation_file', 'gutenberg_override_translation_file' );
		remove_filter( 'block_editor_settings', 'gutenberg_extend_block_editor_styles' );
		remove_filter( 'default_content', 'gutenberg_default_demo_content' );
		remove_filter( 'default_title', 'gutenberg_default_demo_title' );
		remove_filter( 'block_editor_settings', 'gutenberg_legacy_widget_settings' );
		remove_filter( 'rest_request_after_callbacks', 'gutenberg_filter_oembed_result' );

		// Previously used, compat for older Gutenberg versions.
		remove_filter( 'wp_refresh_nonces', 'gutenberg_add_rest_nonce_to_heartbeat_response_headers' );
		remove_filter( 'get_edit_post_link', 'gutenberg_revisions_link_to_editor' );
		remove_filter( 'wp_prepare_revision_for_js', 'gutenberg_revisions_restore' );

		remove_action( 'rest_api_init', 'gutenberg_register_rest_routes' );
		remove_action( 'rest_api_init', 'gutenberg_add_taxonomy_visibility_field' );
		remove_filter( 'registered_post_type', 'gutenberg_register_post_prepare_functions' );

		remove_action( 'do_meta_boxes', 'gutenberg_meta_box_save' );
		remove_action( 'submitpost_box', 'gutenberg_intercept_meta_box_render' );
		remove_action( 'submitpage_box', 'gutenberg_intercept_meta_box_render' );
		remove_action( 'edit_page_form', 'gutenberg_intercept_meta_box_render' );
		remove_action( 'edit_form_advanced', 'gutenberg_intercept_meta_box_render' );
		remove_filter( 'redirect_post_location', 'gutenberg_meta_box_save_redirect' );
		remove_filter( 'filter_gutenberg_meta_boxes', 'gutenberg_filter_meta_boxes' );

		remove_filter( 'body_class', 'gutenberg_add_responsive_body_class' );
		remove_filter( 'admin_url', 'gutenberg_modify_add_new_button_url' ); // old
		remove_action( 'admin_enqueue_scripts', 'gutenberg_check_if_classic_needs_warning_about_blocks' );
		remove_filter( 'register_post_type_args', 'gutenberg_filter_post_type_labels' );
	}



	/**
	 * Tiny switch to apply or not the markdown filters
	 * Since 3.0: Checking the post type inside the loop
	 * https://developer.wordpress.org/reference/hooks/the_content/
	 *
	 * @access public
	 * @since 2.0
	 *
	 * @param String $field_content the HTML content
	 * @param Integer $cache_allowed 1 if cache is allowed with the field
	 *
	 * @return String $content The modified HTML content
	 */
	private function content_data( $field_content, $cache_allowed ) {
		if ( wp_is_rest_endpoint() || ( ( is_singular() || is_archive() ) && in_the_loop() && is_main_query() ) ) :
			if ( post_type_supports( get_post_type(), 'markup_markdown' ) ) :
				return apply_filters( 'post_markdown2html', $field_content, $cache_allowed );
			else :
				return $field_content;
			endif;
		else :
			return $field_content;
		endif;
	}


	/**
	 * Quick bridge to allow static cache content with the post content data
	 *
	 * @since 3.3.0
	 * @access public
	 *
	 * @param String $post_content The HTML post content field
	 *
	 * @return String the filtered content
	 */
	public function post_content_mmd2html( $post_content ) {
		return $this->content_data( $post_content, 1 );
	}


	/**
	 * Quick bridge to force disable static cache content with the post excerpt data
	 *
	 * @since 3.3.0
	 * @access public
	 *
	 * @param String $post_excerpt The HTML excerpt field
	 *
	 * @return String the filtered content
	 */
	public function post_excerpt_mmd2html( $post_excerpt ) {
		return $this->content_data( $post_excerpt, 0 );
	}


	/**
	 * Enable or disable the filters regards to the WP_MMD_RAW_DATA constant
	 *
	 * @since 1.7.4
	 * @access public
	 *
	 * @return Void
	 */
	public function set_content_filters() {
		if ( defined( 'WP_MMD_RAW_DATA' ) && WP_MMD_RAW_DATA ) :
			define( 'MMD_SUPPORT_ENABLED', 0 );
			# Disable default content filters if WP_MMD_RAW_DATA defined and set to 1
			remove_all_filters( 'the_content' );
			remove_all_filters( 'the_excerpt' );
		else :
			define( 'MMD_SUPPORT_ENABLED', $this->mmd_syntax > 0 ? TRUE : FALSE );
			require_once mmd()->plugin_dir . 'MarkupMarkdown/Core/Parser.php';
			new \MarkupMarkdown\Core\Parser();
			add_filter( 'the_content', array( $this, 'post_content_mmd2html' ), 9 , 1 );
			add_filter( 'the_excerpt', array( $this, 'post_excerpt_mmd2html' ), 9 , 1 );
		endif;
	}


}
