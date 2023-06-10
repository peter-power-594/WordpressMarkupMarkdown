<?php

namespace MarkupMarkdown;

defined( 'ABSPATH' ) || exit;


class EngineSummerNote {


	private $prop = array(
		'slug' => 'engine__summernote',
		'label' => 'SummerNote WYSIWYG',
		'desc' => 'Custom HTML based markdown editor to used instead of EasyMDE'
	);


	public function __construct() {
		if ( defined( 'MMD_ADDONS' ) && in_array( 'engine__summernote', MMD_ADDONS ) === FALSE ) :
			return FALSE; # Addon has been desactivated
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
		$plugin_uri = mmd()->plugin_uri;
		# 1. Load editor related stylesheets
		wp_enqueue_style( 'markup_markdown__bootstrap_bundle',  $plugin_uri . 'assets/summernote/bootstrap-5.0.2.css', [], '0.8.20' );
		wp_enqueue_style( 'markup_markdown__cssengine_editor',  $plugin_uri . 'assets/summernote/summernote-0.8.20-bs5.css', [ 'markup_markdown__bootstrap_bundle' ], '0.8.20' );
		wp_enqueue_style( 'markup_markdown__wordpress_richedit', $plugin_uri . 'assets/markup-markdown/css/wordpress_richedit-summernote.css', [ 'markup_markdown__cssengine_editor' ], '1.0.10' );
		# 2. Load markdown related scripts
		wp_enqueue_script( 'markup_markdown__bootstrap_bundle', $plugin_uri . 'assets/summernote/bootstrap-5.0.2.bundle.min.js', [], '5.0.2' );
		wp_enqueue_script( 'markup_markdown__jsengine_editor', $plugin_uri . 'assets/summernote/summernote-0.8.20-bs5.js', [ 'markup_markdown__bootstrap_bundle' ], '0.8.21', true );
		wp_enqueue_script( 'markup_markdown__showdown', $plugin_uri . 'assets/showdown/showdown-2.1.0.min.js', [ 'markup_markdown__jsengine_editor' ], '2.1.0', true );
		wp_enqueue_script( 'markup_markdown__turndown', $plugin_uri . 'assets/turndown/turndown-7.1.2.js', [ 'markup_markdown__showdown' ], '7.1.2', true );
		wp_enqueue_script( 'markup_markdown__wordpress_richedit', $plugin_uri . 'assets/markup-markdown/js/wordpress_richedit-summernote.js', [ 'markup_markdown__turndown' ], '1.1.72', true );		
	}


}
