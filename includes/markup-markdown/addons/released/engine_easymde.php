<?php

namespace MarkupMarkdown;

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
		endif;
	}


	public function __get( $name ) {
		if ( array_key_exists( $name, $this->prop ) ) {
			return $this->prop[ $name ];
		}
		return 'mmd_undefined';
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
		wp_enqueue_style( 'markup_markdown__cssengine_editor',  $plugin_uri . 'assets/easy-markdown-editor/dist/easymde.min.css', [], '2.18.100' );
		wp_enqueue_style( 'markup_markdown__highlightjs_snippets', $plugin_uri . 'assets/highlightjs/github.css', [ 'markup_markdown__cssengine_editor' ], '8.9.1' );
		wp_enqueue_style( 'markup_markdown__wordpress_richedit', $plugin_uri . 'assets/markup-markdown/css/wordpress_richedit-easymde.css', [ 'markup_markdown__highlightjs_snippets' ], '1.0.11' );
		# 2. Load markdown related scripts
		wp_enqueue_script( 'markup_markdown__jsengine_editor', $plugin_uri . 'assets/easy-markdown-editor/dist/easymde.min.js', [], '2.18.0', true );
		wp_enqueue_script( 'markup_markdown__highlightjs_snippets', $plugin_uri . 'assets/highlightjs/highlightjs.min.js', [ 'markup_markdown__jsengine_editor' ], '8.9.1', true );
		wp_enqueue_script( 'markup_markdown__codemirror_spellchecker', $plugin_uri . 'assets/custom-codemirror-spell-checker/dist/spell-checker.min.js', [ 'markup_markdown__highlightjs_snippets' ], '1.1.3', true );
		wp_enqueue_script( 'markup_markdown__wordpress_preview', $plugin_uri . 'assets/markup-markdown/js/wordpress_richedit-preview.js', [ 'markup_markdown__codemirror_spellchecker' ], '1.0.1', true );
		wp_enqueue_script( 'markup_markdown__wordpress_media', $plugin_uri . 'assets/markup-markdown/js/wordpress_richedit-media.js', [ 'markup_markdown__wordpress_preview' ], '1.0.5', true );
		wp_enqueue_script( 'markup_markdown__wordpress_richedit', $plugin_uri . 'assets/markup-markdown/js/wordpress_richedit-easymde.js', [ 'markup_markdown__wordpress_media' ], '1.2.4', true );
	}


}
