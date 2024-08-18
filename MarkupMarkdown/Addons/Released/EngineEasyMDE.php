<?php

namespace MarkupMarkdown\Addons\Released;

defined( 'ABSPATH' ) || exit;


class EngineEasyMDE {


	private $prop = array(
		'slug' => 'engine__easymde',
		'release' => 'stable',
		'active' => 1
	);


	public $is_admin = FALSE;


	/**
	 * @property Boolean $frontend_enabled
	 * To know if markdown syntax was enabled - or not on the frontend
	 *
	 * @since 3.3.0
	 * @access private
	 */
	public $frontend_enabled = false;


	/**
	 * @property Boolean $backend_enabled
	 * To know if markdown syntax was enabled - or not on the backend
	 *
	 * @since 3.4.0
	 * @access private
	 */
	public $backend_enabled = false;


	public function __construct() {
		$this->prop[ 'label' ] = __( 'EasyMde WYSIWYG', 'markup-markdown' );
		$this->prop[ 'desc' ] = __( 'The default Markdown Editor.', 'markup-markdown' );
		if ( defined( 'MMD_ADDONS' ) && in_array( 'engine__summernote', MMD_ADDONS ) !== FALSE ) :
			$this->prop[ 'active' ] = 0;
			return FALSE; # Addon has been desactivated (SummerNote activated)
		endif;
		$this->is_admin = is_admin() ? TRUE : FALSE;
		if ( $this->is_admin ) :
			# Hooks that run only in the backend
			add_action( 'wp_ajax_mmduser-editoptions', array( $this, 'save_mmd_edit_options' ) );
			$action = filter_input( INPUT_GET, 'action', FILTER_SANITIZE_SPECIAL_CHARS );
			if ( 'edit' === $action ) :
				add_filter( 'screen_settings', array( $this, 'mmd_post_screen_options_settings' ), 9 , 2 );
			endif;
			add_action( 'init', array( $this, 'prepare_editor_assets' ), 10000 );
		else :
			# Hooks that might be used on the frontend as well. Use same priority
			# Use the same or higher priority than defined in Core/Support.php
			add_action( 'wp_head', array( $this, 'prepare_editor_assets' ), 12 );
		endif;
		$this->disable_emojis();
		return TRUE;
	}


	public function __get( $name ) {
		if ( array_key_exists( $name, $this->prop ) ) {
			return $this->prop[ $name ];
		}
		return 'mmd_undefined';
	}


	/**
	 * Custom HTML inside the screen options panel
	 *
	 * @since 2.5.2
	 * @access public
	 */
	public function save_mmd_edit_options() {
		check_ajax_referer( 'mmdeditoptions', 'mmdeditoptionsnonce' );
		$user = wp_get_current_user();
		if ( ! $user ) {
			wp_die( -1 );
		}
		$user_options = filter_input_array( INPUT_POST, array(
			'options' => array(
				'filter' => FILTER_VALIDATE_INT,
				'flags'  => FILTER_REQUIRE_ARRAY,
			)
		));
		if( in_array( null, $user_options, true ) ) :
			wp_die( -1 );
		endif;
		$is_sticky = $user_options[ 'options' ][ 'mmd_sticky_toolbar' ];
		update_user_meta( $user->ID, '_mmd_sticky_toolbar', $is_sticky );
		wp_die( 1 );
	}


	/**
	 * Custom HTML inside the screen options panel
	 *
	 * @since 2.5.2
	 * @access public
	 *
	 * @param String $panel The HTML code for the current panel
	 * @param \WP_Screen $screen The current screen settings objet.
	 * @return String The modified HTML code for the current panel
	 */
	public function mmd_post_screen_options_settings( $panel, $screen ) {
		$is_sticky = get_user_meta( get_current_user_id(), '_mmd_sticky_toolbar', true );
		$sticky_options = array(
			'<fieldset class="mmd-easymde-prefs">',
				'<legend class="screen-mmd">Markup Markdown Options</legend>',
				'<label for="mmd_sticky_toolbar">',
					'<input class="sticky-toolbar-tog" name="mmd_sticky_toolbar" type="checkbox" id="mmd_sticky_toolbar" value="1"' . ( $is_sticky ? ' checked="checked"' : '' ) . '>',
					'Sticky Toolbar',
				'</label>',
				wp_nonce_field( 'mmdeditoptions', 'mmdeditoptionsnonce', false ), # Add a nonce
			'</fieldset>'
		);
		return implode( '', $sticky_options );
	}


	/**
	 * Check and trigger the assets load if need be
	 *
	 * @access public
	 * @since 3.3.0
	 *
	 * @return Boolean TRUE if we need to load the assets or FALSE
	 */
	public function prepare_editor_assets() {
		if ( $this->is_admin ) : # Backend called earlier in the *init* hook or similar
			# We don't have access to the edit screen property yet so the check will be made in the next hook
			add_action( 'admin_enqueue_scripts', array( $this, 'load_assets' ) );
		else : # Frontend: we are inside the *wp_head* hook.
			# Check if allowed and load straight the asset
			$this->frontend_enabled = apply_filters( 'mmd_frontend_enabled', false );
			if ( ! $this->frontend_enabled ) :
				return false;
			endif;
			$this->load_assets();
		endif;
		return true;
	}


	/**
	 * Load step by setp the required assets
	 *
	 * @access public
	 * @since 3.0.0
	 *
	 * @param String $hook the current hook in use
	 *
	 * @return Void
	 */
	public function load_assets( $hook = 'unknown.php' ) {
		if ( $this->is_admin ) : # Backend
			$this->backend_enabled = apply_filters( 'mmd_backend_enabled', $hook, false );
			if ( ! $this->backend_enabled ) :
				# Not editing a post, do not load asset & exit
				return false;
			endif;
		else : # Frontend
			if ( ! is_singular() || ! $this->frontend_enabled ) :
				// Frontend and user is no logged or not possible to edit content
				return false;
			endif;
		endif;
		# (1) Load the media related manager assets
		$this->load_engine_media();
		# (2) Load the markdown editor related stylesheets
		$this->load_engine_stylesheets();
		# (3) Conditional markdown editor scripts loading inside the footer after all plugins are loaded
		add_action( $this->is_admin ? 'admin_footer' : 'wp_footer', array( $this, 'load_engine_scripts' ) );
	}


	/**
	 * Queue the media manager related assets
	 *
	 * @access public
	 * @since 3.3.0
	 *
	 * @return Void
	 */
	public function load_engine_media() {
		$args = array();
		$post_id = function_exists( 'get_the_ID' ) ? get_the_ID() : 0;
		if ( (int)$post_id > 0 ) :
			$args[ 'post' ] = $post_id;
		endif;
		wp_enqueue_media( $args );
		wp_playlist_scripts( 'audio' );
		wp_playlist_scripts( 'video' );
	}


	/**
	 * Trigger the loading of the editor scripts if and only if we are
	 * on the edit screen of a post / page using the markdown version of wysiwyg
	 *
	 * @access public
	 * @since 3.3.0
	 *
	 * @return Void
	 */
	public function load_engine_stylesheets() {
		$plugin_uri = mmd()->plugin_uri;
		wp_enqueue_style( 'markup_markdown__cssengine_editor',  $plugin_uri . 'assets/easy-markdown-editor/dist/easymde.min.css', [], '2.19.103' );
		wp_enqueue_style( 'markup_markdown__highlightjs_snippets', $plugin_uri . 'assets/highlightjs/github.css', [ 'markup_markdown__cssengine_editor' ], '8.9.1' );
		wp_enqueue_style( 'markup_markdown__wordpress_richedit', $plugin_uri . 'assets/markup-markdown/css/wordpress_richedit-easymde.min.css', [ 'markup_markdown__highlightjs_snippets' ], '1.2.2' );
		wp_enqueue_style( 'markup_markdown__font_awesome_regular', 'https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.15.4/css/regular.min.css', [ 'markup_markdown__wordpress_richedit' ], '5.15.14' );
		wp_enqueue_style( 'markup_markdown__font_awesome_solid', 'https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.15.4/css/solid.min.css', [ 'markup_markdown__font_awesome_regular' ], '5.15.14' );
		wp_enqueue_style( 'markup_markdown__font_awesome_icons', 'https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.15.4/css/fontawesome.min.css', [ 'markup_markdown__font_awesome_solid' ], '5.15.14' );
	}


	/**
	 * Trigger the loading of the editor scripts
	 *
	 * @access public
	 * @since 3.3.0
	 *
	 * @return Void
	 */
	public function load_engine_scripts() {
		$plugin_uri = mmd()->plugin_uri;
		wp_enqueue_script( 'markup_markdown__jsengine_editor', $plugin_uri . 'assets/easy-markdown-editor/dist/easymde.min.js', [], '2.18.0', true );
		wp_enqueue_script( 'markup_markdown__highlightjs_snippets', $plugin_uri . 'assets/highlightjs/highlightjs.min.js', [ 'markup_markdown__jsengine_editor' ], '8.9.1', true );
		wp_enqueue_script( 'markup_markdown__waypoints', 'https://unpkg.com/waypoints@4.0.1/lib/jquery.waypoints.min.js', [ 'markup_markdown__jsengine_editor' ], '4.0.1', true );
		wp_enqueue_script( 'markup_markdown__sticky', 'https://unpkg.com/waypoints@4.0.1/lib/shortcuts/sticky.min.js', [ 'markup_markdown__waypoints' ], '4.0.1', true );
		wp_enqueue_script( 'markup_markdown__codemirror_spellchecker', $plugin_uri . 'assets/custom-codemirror-spell-checker/dist/spell-checker.min.js', [ 'markup_markdown__sticky' ], '1.1.22', true );
		wp_enqueue_script( 'markup_markdown__wordpress_spellchecker', $plugin_uri . 'assets/markup-markdown/js/wordpress_richedit-spellchecker.min.js', [ 'markup_markdown__codemirror_spellchecker' ], '1.0.0', true );
		wp_enqueue_script( 'markup_markdown__wordpress_preview', $plugin_uri . 'assets/markup-markdown/js/wordpress_richedit-preview.min.js', [ 'markup_markdown__wordpress_spellchecker' ], '1.0.22', true );
		wp_enqueue_script( 'markup_markdown__wordpress_media', $plugin_uri . 'assets/markup-markdown/js/wordpress_richedit-media.min.js', [ 'markup_markdown__wordpress_preview' ], '1.0.21', true );
		wp_enqueue_script( 'markup_markdown__wordpress_richedit', $plugin_uri . 'assets/markup-markdown/js/wordpress_richedit-easymde.min.js', [ 'markup_markdown__wordpress_media' ], '1.4.17', true );
		wp_localize_script( 'markup_markdown__wordpress_richedit', 'mmd_wpr_vars', array(
			'mmd_pipe'            => esc_html__( 'Pipe', 'markup-markdown' ),
			'mmd_bold'            => esc_html__( 'Bold', 'markup-markdown' ),
			'mmd_italic'          => esc_html__( 'Italic', 'markup-markdown' ),
			'mmd_strikethrough'   => esc_html__( 'Strikethrough', 'markup-markdown' ),
			'mmd_heading'         => esc_html__( 'Heading', 'markup-markdown' ),
			'mmd_heading-smaller' => esc_html__( 'Smaller Heading', 'markup-markdown' ),
			'mmd_heading-bigger'  => esc_html__( 'Bigger Heading', 'markup-markdown' ),
			'mmd_heading-1'       => esc_html__( 'Big Heading', 'markup-markdown' ),
			'mmd_heading-2'       => esc_html__( 'Medium Heading', 'markup-markdown' ),
			'mmd_heading-3'       => esc_html__( 'Small Heading', 'markup-markdown' ),
			'mmd_code'            => esc_html__( 'Code', 'markup-markdown' ),
			'mmd_quote'           => esc_html__( 'Quote', 'markup-markdown' ),
			'mmd_unordered-list'  => esc_html__( 'Generic List', 'markup-markdown' ),
			'mmd_ordered-list'    => esc_html__( 'Numbered List', 'markup-markdown' ),
			'mmd_clean-block'     => esc_html__( 'Clean block', 'markup-markdown' ),
			'mmd_link'            => esc_html__( 'Create Link', 'markup-markdown' ),
			'mmd_wpsimage'        => esc_html__( 'Insert or Upload Media', 'markup-markdown' ),
			'mmd_table'           => esc_html__( 'Insert Table', 'markup-markdown' ),
			'mmd_horizontal-rule' => esc_html__( 'Insert Horizontal Line', 'markup-markdown' ),
			'mmd_preview'         => esc_html__( 'Toggle Preview', 'markup-markdown' ),
			'mmd_side-by-side'    => esc_html__( 'Toggle Side by Side', 'markup-markdown' ),
			'mmd_fullscreen'      => esc_html__( 'Toggle Fullscreen', 'markup-markdown' ),
			'mmd_guide'           => esc_html__( 'Markdown Guide', 'markup-markdown' ),
			'mmd_undo'            => esc_html__( 'Undo', 'markup-markdown' ),
			'mmd_redo'            => esc_html__( 'Redo', 'markup-markdown' ),
			'mmd_spell-check'     => esc_html__( 'Spellchecker', 'markup-markdown' )
		));
		wp_add_inline_script( 'markup_markdown__wordpress_media', $this->add_inline_editor_conf() );
	}


	/**
	 * Method to add inline JavaScript setup variable to the admin edit screen
	 *
	 * @access public
	 * @since 3.0.0
	 *
	 * @returns string inline easymde configuration tool
	 */
	public function add_inline_editor_conf() {
		$home_url = get_home_url() . '/';
		$js = "window.wp = window.wp || {};\n"; # Just in case
		$js .= "wp.pluginMarkupMarkdown = wp.pluginMarkupMarkdown || {};\n";
		$js .= "wp.pluginMarkupMarkdown.homeURL = \"" . $home_url . "\";\n";
		$json = mmd()->conf_blog_prefix . 'conf_easymde_toolbar.json';
		if ( ! file_exists( $json ) ) :
			$toolbarSetup = mmd()->plugin_dir . "/MarkupMarkdown/Addons/Released/Media/ToolbarEasyMDE.php";
			if ( file_exists( $toolbarSetup ) ) :
				require_once $toolbarSetup; # Dummy init to generate the json file
				$my_toolbar = new \MarkupMarkdown\Addons\Released\Media\ToolbarEasyMDE( $json );
			endif;
		endif;
		$toolbarButtons = json_decode( preg_replace( "#[^a-z0-9-_\,\:\"\{\}\[\]]#", "", file_get_contents( $json ) ) );
		$js .= "wp.pluginMarkupMarkdown.primaryArea = " . ( defined( 'MMD_SUPPORT_ENABLED' ) && MMD_SUPPORT_ENABLED ? '1' : '0' ) . ";\n";
		$js .= "wp.pluginMarkupMarkdown.toolbarButtons = [ \"" . implode( "\",\"", str_replace( '_', '-', $toolbarButtons->my_buttons ) ) . "\" ];\n";
		return $js;
	}


	/**
	 * Method to disable the emojis
	 * Code from Ryan plugin with community patches
	 *
	 * @access private
	 * @since 3.6.0
	 *
	 * @returns Void
	 */
	private function disable_emojis() {
		add_action( 'init', array( $this, 'disable_front_emojis' ) );
		add_action( 'admin_init', array( $this, 'disable_back_emojis' ) );
	}


	/**
	 * Disable emojis
	 *
	 * @access public
	 * @since 3.6.0
	 *
	 * @returns Void
	 */
	public function disable_front_emojis() {
		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
		remove_action( 'wp_print_styles', 'print_emoji_styles' );	
		remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
		remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );	
		remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
		add_filter( 'tiny_mce_plugins', array( $this, 'disable_emojis_tinymce' ) );
		add_filter( 'wp_resource_hints', array( $this, 'disable_emojis_remove_dns_prefetch' ), 10, 2 );
	}


	/**
	 * Disable emojis
	 *
	 * @access public
	 * @since 3.6.0
	 *
	 * @returns Void
	 */
	public function disable_back_emojis() {
		remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
		remove_action( 'admin_print_styles', 'print_emoji_styles' );
	}


	/**
	 * Filter function used to remove the tinymce emoji plugin.
	 *
	 * @access public 
	 * @since 3.6.0
	 *
	 * @param Array $plugins  
	 * @returns Array The difference betwen the two arrays
	 */
	public function disable_emojis_tinymce( $plugins ) {
		if ( is_array( $plugins ) ) :
			return array_diff( $plugins, array( 'wpemoji' ) );
		endif;
		return array();
	}


	/**
	 * Remove emoji CDN hostname from DNS prefetching hints.
	 *
	 * @access public 
	 * @since 3.6.0
	 *
	 * @param Array $urls The URLs to print for resource hints.
	 * @param String $relation_type The relation type the URLs are printed for.
	 * @returns Array The difference betwen the two arrays.
	 */
	public function disable_emojis_remove_dns_prefetch( $urls, $relation_type ) {
		if ( 'dns-prefetch' == $relation_type ) :
			// Strip out any URLs referencing the WordPress.org emoji location
			$emoji_svg_url_bit = 'https://s.w.org/images/core/emoji/';
			foreach ( $urls as $key => $url ) :
				if ( strpos( $url, $emoji_svg_url_bit ) !== false ) :
					unset( $urls[$key] );
				endif;
			endforeach;
		endif;
		return $urls;
	}


}
