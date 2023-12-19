<?php

namespace MarkupMarkdown;

defined( 'ABSPATH' ) || exit;


class EngineSummerNote {


	private $prop = array(
		'slug' => 'engine__summernote',
		'label' => 'SummerNote WYSIWYG',
		'desc' => 'Custom HTML based markdown editor to used instead of EasyMDE.',
		'release' => 'alpha',
		'active' => 0
	);


	public function __construct() {
		if ( ! defined( 'MMD_ADDONS' ) || ( defined( 'MMD_ADDONS' ) && in_array( 'engine__summernote', MMD_ADDONS ) === FALSE ) ) :
			$this->prop[ 'active' ] = 0;
			return FALSE; # Addon has been desactivated
		endif;
		$this->prop[ 'active' ] = 1;
		if ( is_admin() ) :
			add_action( 'admin_enqueue_scripts', array( $this, 'load_engine_assets' ) );
		else :
			remove_filter( 'the_excerpt', 'wpautop' );
			remove_filter( 'the_content', 'wpautop' );
			add_filter( 'post_markdown2html', array( $this, 'format_enhanced_blockquote' ), 5, 1 );
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
		wp_enqueue_style( 'markup_markdown__bootstrap_bundle',  $plugin_uri . 'assets/summernote/bootstrap-5.0.2.css', [], '0.8.20' );
		wp_enqueue_style( 'markup_markdown__cssengine_editor',  $plugin_uri . 'assets/summernote/summernote-0.8.20-bs5.css', [ 'markup_markdown__bootstrap_bundle' ], '0.8.20' );
		wp_enqueue_style( 'markup_markdown__wordpress_richedit', $plugin_uri . 'assets/markup-markdown/css/wordpress_richedit-summernote.css', [ 'markup_markdown__cssengine_editor' ], '1.0.10' );
		# 2. Load markdown related scripts
		wp_enqueue_script( 'markup_markdown__bootstrap_bundle', $plugin_uri . 'assets/summernote/bootstrap-5.0.2.bundle.min.js', [], '5.0.2' );
		wp_enqueue_script( 'markup_markdown__jsengine_editor', $plugin_uri . 'assets/summernote/summernote-0.8.20-bs5.js', [ 'markup_markdown__bootstrap_bundle' ], '0.8.21', true );
		wp_enqueue_script( 'markup_markdown__showdown', 'https://unpkg.com/showdown@2.1.0/dist/showdown.js', [ 'markup_markdown__jsengine_editor' ], '2.1.0', true );
		wp_enqueue_script( 'markup_markdown__turndown', 'https://unpkg.com/turndown@7.1.2/dist/turndown.js', [ 'markup_markdown__showdown' ], '7.1.2', true );
		wp_enqueue_script( 'markup_markdown__wordpress_preview', $plugin_uri . 'assets/markup-markdown/js/wordpress_richedit-preview.js', [ 'markup_markdown__turndown' ], '1.0.1', true );
		wp_enqueue_script( 'markup_markdown__wordpress_media', $plugin_uri . 'assets/markup-markdown/js/wordpress_richedit-media.js', [ 'markup_markdown__wordpress_preview' ], '1.0.5', true );
		wp_enqueue_script( 'markup_markdown__wordpress_richedit', $plugin_uri . 'assets/markup-markdown/js/wordpress_richedit-summernote.js', [ 'markup_markdown__wordpress_media' ], '1.1.75', true );		
	}


	/**
	 * Filter to output the blockquote as the cleaned html version explained here:
	 * https://developer.mozilla.org/en-US/docs/Web/HTML/Element/blockquote
	 *
	 * @access public
	 *
	 * @param String $content the markdown to be parsed
	 * @return Text
	 */
	public function format_enhanced_blockquote( $content = '' ) {
		$blockquotes = array();
		$all_blockquotes = preg_match_all( '#\>\s.*?\n\>\s.*?\n#', $content, $blockquotes );
		if ( ! $all_blockquotes ) :
			return $content;
		endif;
		foreach( $blockquotes[ 0 ] as $blockquote ) :
			$strip = str_replace( array( "\r", "\n" ), '', $blockquote );
			$strip = preg_replace( '#\>\s*#', '', $strip );
			$lim = array();
			preg_match( '#[—-]+\s*#', $strip, $lim );
			if ( ! isset( $lim ) || count( $lim ) < 1 ) :
				continue;
			endif;
			$text = explode( $lim[ 0 ], $strip );
			$quote = $text[ 0 ];
			$reference = ( isset( $text[ 1 ] ) && strlen( $text[ 1 ] ) > 2 ) ? preg_replace( '#^.*?\,#', '', $text[ 1 ] ) : '';
			$author = ( isset( $text[ 1 ] ) && strlen( $text[ 1 ] ) > 2 ) ? explode( ',', $text[ 1 ] )[ 0 ] : '';
			$html = '<figure>'
					. '<blockquote><p>' . $quote . '</p></blockquote>'
					. ( strlen( $author ) > 0 || strlen( $reference ) > 0 ? '<figcaption>' . $lim[ 0 ]: '' )
						. ( strlen( $author ) > 0 ? $author . ', ' : '' )
						. ( strlen( $reference ) > 0 ? '<cite>' . $reference . '</cite>' : '' )
					. ( strlen( $author ) > 0 || strlen( $reference ) > 0 ? '</figcaption>' : ''  )
				. '</figure>';
			$content = str_replace( $blockquote, $html . "\n", $content );
		endforeach;
		return $content;
	}

}
