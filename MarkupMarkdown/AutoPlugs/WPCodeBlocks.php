<?php

namespace MarkupMarkdown\AutoPlugs;

defined( 'ABSPATH' ) || exit;


class CodeBlocks {


	public function __construct() {
		if ( file_exists( WP_PLUGIN_DIR . '/wp-codemirror-block/index.php' ) ) :
			define( 'MMD_CODEMIRRORBLOCK_PLUG', true );
			add_action( 'after_setup_theme', array( $this, 'mmd_wp_codemirror_plug' ) );
		endif;
	}


	public function mmd_wp_codemirror_plug() {
		add_filter( 'addon_markdown2html', array( $this, 'trigger_wp_codemirror' ), 11, 1 );
		if ( ! is_admin() ) :
			add_action( 'wp_enqueue_scripts', array( $this, 'trigger_wp_codemirror_scripts' ) );
		endif;
	}


	/**
	 * Parse the html and modify the output to match CodeMirror blocks requirements
	 * @source /wp-content/plugins/wp-codemirror-block/includes/class-codemirror-blocks.php (render_code_block)
	 * @param {String} $content The html rendered from markdown 
	 * @return {String} The modified html content
	 */
	public function trigger_wp_codemirror( $content ) {
		if ( ! class_exists( 'CodeMirror_Blocks\CodeMirror_Blocks' ) ) :
			return $content;
		endif;
		$code_blocks = [];
		preg_match_all( '#<pre><code class=\"lang-([a-z0-9]+)\">(.*?)<\/code><\/pre>#is', $content, $code_blocks );
		if ( ! isset( $code_blocks ) || ! is_array( $code_blocks ) || ! count( $code_blocks ) ) :
			return $content;
		endif;
		$editor_option = \CodeMirror_Blocks\Settings::get_options();
		$modes = \CodeMirror_Blocks\Settings::modes();
		foreach( $code_blocks[ 0 ] as $id_block => $code_block ) :
			$my_lang = isset( $code_blocks[ 1 ][ $id_block ] ) && ! empty( $code_blocks[ 1 ][ $id_block ] ) ? strtolower( $code_blocks[ 1 ][ $id_block ] ) : '';
			$my_code = isset( $code_blocks[ 2 ][ $id_block ] ) && ! empty( $code_blocks[ 2 ][ $id_block ] ) ? htmlentities( $code_blocks[ 2 ][ $id_block ] ) : '';
			if ( empty( $my_lang ) ) :
				continue;
			endif;
			foreach( $modes as $key => $mode ) :
				if ( $my_lang === $mode[ 'name' ] ) :
					$attributes = [];
					$attributes = wp_parse_args( $attributes, $editor_option[ 'editor' ] );
					$attributes = wp_parse_args( $attributes, $editor_option[ 'panel' ] );
					$attributes[ 'maxHeight' ] = '400px';
					$attributes[ 'language' ] = preg_replace('/ \([\s\S]*?\)/', '', $mode[ 'label' ] );
					$attributes[ 'modeName' ] = $mode[ 'name' ];
					$attributes[ 'mode' ] = $mode[ 'value' ];
					$attributes[ 'mime' ] = $mode[ 'mime' ];
					$content = str_replace(
						$code_block,
						"<div class=\"wp-block-codemirror-blocks-code-block code-block\"><pre class=\"CodeMirror\" data-setting=\"" . str_replace( '&quot;', '&amp;quot;', esc_attr( wp_json_encode( $attributes, JSON_UNESCAPED_SLASHES ) ) ) . "\">" . $my_code . "</pre></div>",
						$content
					);
					break;
				endif;
			endforeach;
		endforeach;
		return $content;
	}


	public function trigger_wp_codemirror_scripts() {
		if ( ! class_exists( 'CodeMirror_Blocks\CodeMirror_Blocks' ) ) :
			return false;
		endif;
		wp_enqueue_style( 'codemirror' );
		wp_enqueue_script( 'codemirror-autoload' );
		wp_add_inline_script( 'codemirror-autoload', \CodeMirror_Blocks\CodeMirror_Blocks::inline_script( 'frontend' ), 'before' );
		wp_enqueue_script( 'codemirror-view' );
		return true;
	}


}

new \MarkupMarkdown\AutoPlugs\CodeBlocks();