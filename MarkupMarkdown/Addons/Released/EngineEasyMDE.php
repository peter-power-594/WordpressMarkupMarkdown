<?php

namespace MarkupMarkdown\Addons\Released;

defined( 'ABSPATH' ) || exit;


class EngineEasyMDE {


	private $prop = array(
		'slug' => 'engine__easymde',
		'label' => 'EasyMde WYSIWYG',
		'desc' => 'The default Markdown Editor.',
		'release' => 'stable',
		'active' => 1
	);


	public function __construct() {
		if ( defined( 'MMD_ADDONS' ) && in_array( 'engine__summernote', MMD_ADDONS ) !== FALSE ) :
			$this->prop[ 'active' ] = 0;
			return FALSE; # Addon has been desactivated (SummerNote activated)
		endif;
		if ( is_admin() ) :
			add_action( 'admin_enqueue_scripts', array( $this, 'load_engine_assets' ) );
			add_action( 'wp_ajax_mmduser-editoptions', array( $this, 'save_mmd_edit_options' ) );
			$action = filter_input( INPUT_GET, 'action', FILTER_SANITIZE_SPECIAL_CHARS );
			if ( 'edit' === $action ) :
				add_filter( 'screen_settings', array( $this, 'mmd_post_screen_options_settings' ), 9 , 2 );
			endif;
		endif;
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
	 * Trigger the loading of stylesheets and scripts if and only if we are
	 * on the edit screen of a post / page using the markdown version of wysiwyg
	 *
	 * @access public
	 *
	 * @param String $hook the current hook in use
	 * @return Void
	 */
	public function load_engine_assets( $hook ) {
		if ( $hook !== 'post.php' && $hook !== 'post-new.php' ) :
			return TRUE;
		endif;
		wp_enqueue_media();
		wp_playlist_scripts( 'audio' );
		wp_playlist_scripts( 'video' );
		$plugin_uri = mmd()->plugin_uri;
		# 1. Load editor related stylesheets
		wp_enqueue_style( 'markup_markdown__cssengine_editor',  $plugin_uri . 'assets/easy-markdown-editor/dist/easymde.min.css', [], '2.19.101' );
		wp_enqueue_style( 'markup_markdown__highlightjs_snippets', $plugin_uri . 'assets/highlightjs/github.css', [ 'markup_markdown__cssengine_editor' ], '8.9.1' );
		wp_enqueue_style( 'markup_markdown__wordpress_richedit', $plugin_uri . 'assets/markup-markdown/css/wordpress_richedit-easymde.css', [ 'markup_markdown__highlightjs_snippets' ], '1.1.15' );
		wp_enqueue_style( 'markup_markdown__font_awesome_regular', 'https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.15.4/css/solid.min.css', [ 'markup_markdown__wordpress_richedit' ], '5.15.14' );
		wp_enqueue_style( 'markup_markdown__font_awesome_icons', 'https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.15.4/css/fontawesome.min.css', [ 'markup_markdown__font_awesome_regular' ], '5.15.14' );
		# 2. Load markdown related scripts
		wp_enqueue_script( 'markup_markdown__jsengine_editor', $plugin_uri . 'assets/easy-markdown-editor/dist/easymde.min.js', [], '2.18.0', true );
		wp_enqueue_script( 'markup_markdown__highlightjs_snippets', $plugin_uri . 'assets/highlightjs/highlightjs.min.js', [ 'markup_markdown__jsengine_editor' ], '8.9.1', true );
		wp_enqueue_script( 'markup_markdown__waypoints', 'https://unpkg.com/waypoints@4.0.1/lib/jquery.waypoints.min.js', [ 'markup_markdown__jsengine_editor' ], '4.0.1' );
		wp_enqueue_script( 'markup_markdown__sticky', 'https://unpkg.com/waypoints@4.0.1/lib/shortcuts/sticky.min.js', [ 'markup_markdown__waypoints' ], '4.0.1' );
		wp_enqueue_script( 'markup_markdown__codemirror_spellchecker', $plugin_uri . 'assets/custom-codemirror-spell-checker/dist/spell-checker.min.js', [ 'markup_markdown__sticky' ], '1.1.3', true );
		wp_enqueue_script( 'markup_markdown__wordpress_preview', $plugin_uri . 'assets/markup-markdown/js/wordpress_richedit-preview.js', [ 'markup_markdown__codemirror_spellchecker' ], '1.0.1', true );
		wp_enqueue_script( 'markup_markdown__wordpress_media', $plugin_uri . 'assets/markup-markdown/js/wordpress_richedit-media.js', [ 'markup_markdown__wordpress_preview' ], '1.0.5', true );
		wp_enqueue_script( 'markup_markdown__wordpress_richedit', $plugin_uri . 'assets/markup-markdown/js/wordpress_richedit-easymde.js', [ 'markup_markdown__wordpress_media' ], '1.3.2', true );
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
		$js = "wp.pluginMarkupMarkdown = wp.pluginMarkupMarkdown || {};\n";
		$js .= "wp.pluginMarkupMarkdown.homeURL = \"" . $home_url . "\";\n";
		$json = mmd()->cache_dir . '/conf_easymde_toolbar.json';
		if ( file_exists( $json ) ) :
			$toolbarButtons = json_decode( preg_replace( "#[^a-z0-9-_\,\:\"\{\}\[\]]#", "", file_get_contents( $json ) ) );
			$js .= "wp.pluginMarkupMarkdown.toolbarButtons = [ \"" . implode( "\",\"", str_replace( '_', '-', $toolbarButtons->my_buttons ) ) . "\" ];\n";
		endif;
		return $js;
	}

}
