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


	/**
	 * @property Boolean $mmd_parser To know if the parser was loaded - or not
	 *
	 * @since 3.6.4
	 * @access private
	 */
	private $mmd_parser = 0;


	/**
	 * @property Array $allowed_hooks
	 * The list of default hooks where the markdown editor will be used in the backend
	 * Can be overriden by developers with the *mmd_backend_enabled* filter
	 *
	 * @since 2.0.0
	 * @access private
	 */
	private $allowed_hooks = array(
		'post.php', 'post-new.php',
		'edit-tags.php', 'term.php',
	);


	public function __construct() {
		# Add Support. When possible we let developers take benefit of the default 10 priority
		add_action( 'init', array( $this, 'add_markdown_support' ) );
		if ( is_admin() ) :
			# Check then enable or disable the markdown editor on the backend
			add_filter( 'mmd_backend_enabled', array( $this, 'current_hook_allowed' ) );
			# Add the basic proxy filters for ajax requests flagged as 'is_admin'
			add_filter( 'mmd_proxy_filters', array( $this, 'push_proxy_filters' ), 9, 1);
			add_action( 'mmd_addons_loaded', array( $this, 'add_extra_proxy_filters' ) );
			# Check if we are at the right location
			add_action( 'init', array( $this, 'prepare_markdown_editor' ), 9999 ); # Priority 9999
			# Toggle on / off the markdown related filters. Different hook so ok with priority 10
			add_action( 'wp_loaded', array( $this, 'set_content_filters' ) );
		else :
			# Check then enable or disable the markdown editor on the frontend
			add_filter( 'mmd_frontend_enabled', array( $this, 'current_template_allowed' ) );
			# Check first if the request is related to the REST api
			add_action( 'rest_api_init', array( $this, 'whitelist_wp_api' ) );
			# With recent block editors / json theme :
			# - **get_header** hook is not fired
			# - **wp_head** hook is fired too late to trigger content filters
			$this->prepare_proxy_filters(); 
			add_action( 'mmd_addons_loaded', array( $this, 'add_extra_proxy_filters' ) );
			# Then check if the request is related to a front page / post.
			# We need the latest hook wp_head to keep compatibility with plugin like ACF
			# Priority should be greater than 10 so we go with 11
			add_action( 'wp_head', array( $this, 'prepare_markdown_editor' ), 11 );
			# Toggle on / off the markdown related filters.
			# Again to keep compatibility with existing plugins we keep the wp_head hook
			# Priority should be greater than the previous prepare_markdown_editor. Go for 12
			add_action( 'wp_head', array( $this, 'set_content_filters' ), 12 );
		endif;
	}


	/**
	 * Tiny filter to switch on / off the loading of the markdown editor on the backend
	 *
	 * @since 3.4.0
	 * @access public
	 *
	 * @param String $hook The target $hook. Default to a dummy value unknown.php
	 *
	 * @return Boolean TRUE if enabled or FALSE if disabed
	 */
	public function current_hook_allowed( $hook = 'unknown.php' ) {
		if ( in_array( $hook, $this->allowed_hooks ) !== false ) :
			return true;
		endif;
		return false;
	}


	/**
	 * Tiny filter to switch on / off the loading of the markdown editor on the frontend
	 *
	 * @since 3.3.0
	 * @access public
	 *
	 * @param Boolean TRUE if the editor can be loaded on the frontend. Default to false
	 *
	 * @return Boolean TRUE if enabled or FALSE if disabed
	 */
	public function current_template_allowed( $bool = false ) {
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
		load_plugin_textdomain( 'markup-markdown', false, mmd()->plugin_dir . 'languages' );
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
			return false;
		endif;
	}


	/**
	 * Output the rendering of markdown
	 *
	 * @since 3.3.4
	 * @access public
	 *
	 * @return Void
	 */
	public function whitelist_wp_api() {
		if ( ! wp_is_rest_endpoint() ) :
			return false;
		endif;
		$this->prepare_markdown_editor();
		# Allow markdown on REST API with terms description
		add_filter( 'rest_prepare_category', array( $this, 'prepare_desc_field' ), 10, 3 );
		add_filter( 'rest_prepare_post_tag', array( $this, 'prepare_desc_field' ), 10, 3 );
		if ( function_exists( 'get_taxonomies' ) ) :
			$my_taxonomies = get_taxonomies( array( 'show_in_rest' => true, '_builtin' => false ) );
			foreach( $my_taxonomies as $tax ) :
				add_filter( 'rest_prepare_' . $tax, array( $this, 'prepare_desc_field' ), 10, 3 );
			endforeach;
		endif;
		$this->set_content_filters();
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
			return false;
		endif;
		# Classic request with a post type defined. Backend or Frontend follow the rules defined
		$my_post_type = $this->get_current_post_type();
		if ( isset( $my_post_type ) && ! empty( $my_post_type ) && ! post_type_supports( $my_post_type, 'markup-markdown' ) && ! post_type_supports( $my_post_type, 'markup_markdown' ) ) :
			# Warning: Keep empty fonction, we DO NOT DISABLE markdown in case it's not with post related template / edit screen
			$this->mmd_syntax = 0;
		endif;
		if ( ! is_admin() ) :
			# Toggle on or off the markdown **editor** on the frontend
			$mmd_tmpl_enabled = apply_filters( 'mmd_frontend_enabled', false );
			if ( ! (int)$mmd_tmpl_enabled ) :
				return false;
			endif;
		endif;
		if ( ! $this->mmd_syntax ) :
			return false;
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
		return true;
	}


	public function clear_post_cache( $post_ID, $post, $update ) {
		# If a modification was made, we must clear the cache to refresh it
		$cache_content = WP_CONTENT_DIR . '/mmd-cache/.' . get_current_network_id() . '_' . get_current_blog_id() . '_' . $post_ID . '.html';
		if ( file_exists( $cache_content ) ) :
			@unlink( $cache_content );
		endif;
		mmd()->clear_cache( $cache_content );
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
		if ( ! $this->mmd_syntax ) :
			# @since 3.6.4
			return $field_content;
		endif;
		if ( wp_is_rest_endpoint() || ( ( is_home() || is_front_page() || is_singular() || is_archive() ) && in_the_loop() && is_main_query() ) ) :
			if ( post_type_supports( get_post_type(), 'markup-markdown' ) || post_type_supports( get_post_type(), 'markup_markdown' ) ) :
				# Filters removed since 3.8.0
				remove_filter( 'the_content', 'wpautop' );
				remove_filter( 'the_excerpt', 'wpautop' );
				$this->load_parser();
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
	 * Quick bridge to force disable static cache content with content used by other plugins
	 * 
	 * @since 3.9.0
	 * @access public
	 *
	 * @param String $extra_content The HTML excerpt field
	 *
	 * @return String the filtered content
	 */
	public function extra_field_mmd2html( $extra_content ) {
		$this->load_parser();
		return apply_filters( 'post_markdown2html', $extra_content, false );
	}


	/**
	 * Quick bridge to force disable static cache content with secondary field
	 *
	 * @since 3.4.0
	 * @access public
	 *
	 * @param String $field_desc The HTML or raw excerpt field
	 *
	 * @return String the filtered content
	 */
	public function description_field_mmd2html( $field_desc ) {
		# Don't use wp_strip_all_tags to break additional input
		# Don't disable the autoparagraph filters for backward compatibility
		return apply_filters( 'post_markdown2html', str_replace( [ '<p>', '</p>' ], '', $field_desc ), 0 );
	}


	/**
	 * Modify a single taxonomy description value within the REST response
	 * @source https://developer.wordpress.org/reference/hooks/rest_prepare_this-taxonomy/
	 *
	 * @since 3.4.1
	 * @access public
	 *
	 * @param \WP_REST_Response $response The response object
	 * @param \WP_Term          $item     The original term object.
	 * @param \WP_REST_Request  $request  Request used to generate the response
	 *
	 * @return \WP_REST_Response The updated response object
	 */
	public function prepare_desc_field( $response, $item, $request ) {
		if ( isset( $response ) && isset( $response->data ) && isset( $response->data[ 'description' ] ) ) :
			$response->data[ 'description' ] = $this->description_field_mmd2html( $response->data[ 'description' ] );
		endif;
		return $response;
	}


	/**
	 * Prepare in advanced content related filters
	 *
	 * @since 3.6.4
	 * @access public
	 *
	 * @return Void
	 */
	public function prepare_proxy_filters() {
		add_filter( 'the_content', array( $this, 'post_content_mmd2html' ), 9, 1 );
		add_filter( 'the_excerpt', array( $this, 'post_excerpt_mmd2html' ), 9, 1 );
		add_filter( 'category_description', array( $this, 'description_field_mmd2html' ), 9, 1 );
		add_filter( 'term_description', array( $this, 'description_field_mmd2html' ), 9, 1 );
		add_filter( 'mmd_proxy_filters', array( $this, 'push_proxy_filters' ), 9, 1);
	}


	public function push_proxy_filters( $arr ) {
		return is_array( $arr ) ? $arr : array();
	}


	public function add_extra_proxy_filters() {
		$extra_filters = apply_filters( 'mmd_proxy_filters', array(), 10, 1 );
		if ( isset( $extra_filters ) && is_array( $extra_filters ) ) :
			foreach( $extra_filters as $custom_filter ) :
				add_filter( $custom_filter, array( $this, 'extra_field_mmd2html' ), 10, 1 );
			endforeach;
		endif;
	}


	/**
	 * Prepare in advanced content related filters
	 *
	 * @since 3.6.4
	 * @access private
	 *
	 * @return Boolean TRUE if the parser has just been loaded or false if the parser is already loaded
	 */
	private function load_parser() {
		if ( $this->mmd_parser > 0 ) :
			return false;
		endif;
		$this->mmd_parser = 1;
		if ( ! defined( 'MMD_SUPPORT_ENABLED' ) ) :
			define( 'MMD_SUPPORT_ENABLED', 1 );
		endif;
		require_once mmd()->plugin_dir . 'MarkupMarkdown/Core/Parser.php';
		new \MarkupMarkdown\Core\Parser();
		return true;
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
			if ( ! defined( 'MMD_SUPPORT_ENABLED' ) ) :
				define( 'MMD_SUPPORT_ENABLED', $this->mmd_syntax > 0 ? true : false );
			endif;
			$this->load_parser();
		endif;
	}


}
