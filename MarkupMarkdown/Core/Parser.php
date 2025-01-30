<?php

namespace MarkupMarkdown\Core;

defined( 'ABSPATH' ) || exit;
defined( 'MMD_SUPPORT_ENABLED' ) || exit;


class Parser {


	private $parser = '';

	private $preview = 'false';

	private $mmd_allowed = 1;

	private $cache_enabled = 0;

	public function __construct() {
		if ( MMD_SUPPORT_ENABLED > 0 ) :
			# Add the filter so the markdown can be parsed and the html generated properly on the frontend
			$this->preview = filter_input( INPUT_GET, 'preview', FILTER_SANITIZE_SPECIAL_CHARS );
			$this->cache_enabled = defined( 'WP_MMD_OPCACHE' ) && WP_MMD_OPCACHE ? 1 : 0;
			$this->mmd_allowed = 1;
		else :
			$this->mmd_allowed = 0;
		endif;
		add_filter( 'post_markdown2html', array( $this, 'format_mmd2html' ), 10, 2 );
		add_filter( 'field_markdown2html', array( $this, 'final_html' ), 10, 1 );
	}


	/**
	 * Method to check for existing cached content before rendering the markdown
	 *
	 * @access private
	 * @since 2.0.0
	 *
	 * @param String $content the HTML to be parsed
	 *
	 * @return String HTML rendered from the markdown
	 */
	private function static_html( $content ) {
		$cache_content = mmd()->cache_blog_prefix . get_the_id() . '.html';
		if ( $this->preview !== 'true' && file_exists( $cache_content ) ) :
			# Cache file already exists
			$my_content = file_get_contents( $cache_content );
			return $my_content;
		else :
			# New cache file
			$my_content = $this->live_html( $content );
			file_put_contents( $cache_content, $my_content );
			return $my_content;
		endif;
	}


	/**
	 * Method to render raw markdown content
	 *
	 * @access private
	 * @since 3.0.0
	 *
	 * @param String $content the HTML to be parsed
	 *
	 * @return String HTML rendered from the markdown
	 */
	private function live_html( $content ) {
		$html = $this->final_html( $content );
		# Decode the double quotes to avoid breaking native WP shortcodes
		$final = htmlspecialchars_decode( $html, ENT_COMPAT );
		unset( $html ); $code_blocks = [];
		preg_match_all( '#<code([^>]*)>(.*?)<\/code>#is', $final, $code_blocks );
		if ( isset( $code_blocks ) && is_array( $code_blocks ) && isset( $code_blocks[ 0 ] ) && isset( $code_blocks[ 1 ] ) ) :
			foreach( $code_blocks[ 0 ] as $id_block => $code_block ) :
				$my_lang = isset( $code_blocks[ 1 ][ $id_block ] ) && ! empty( $code_blocks[ 1 ][ $id_block ] ) ? $code_blocks[ 1 ][ $id_block ] : '';
				$my_code = isset( $code_blocks[ 2 ][ $id_block ] ) && ! empty( $code_blocks[ 2 ][ $id_block ] ) ? htmlentities( $code_blocks[ 2 ][ $id_block ] ) : '';
				$final = str_replace( $code_block, '<code' . $my_lang . '>' . $my_code .'</code>', $final );
			endforeach;
		endif; 
		return $final;
	}


	/**
	 * Method to output the HTML for (custom) post / page content
	 *
	 * @access public
	 * @since 1.5.4
	 *
	 * @param string $content the HTML to be parsed
	 * @returns string HTML rendered from the markdown
	 */
	public function final_html( $content ) {
		return apply_filters( 'addon_markdown2html', $this->markdown2html( $content ) );
	}


	/**
	 * Quick bridge to filter cache allowed in settings and cache allowed with the field
	 *
	 * @access public
	 * @since 1.5.4
	 *
	 * @param String $field_content the HTML content
	 * @param Boolean $cache_allowed TRUE if cache is allowed with the field
	 *
	 * @return String $content The modified HTML content
	 */
	public function format_mmd2html( $field_content, $cache_allowed ) {
		if ( ! $this->mmd_allowed ) :
			# Markdown has been disabled on the main content
			return $field_content;
		elseif ( $this->cache_enabled && $cache_allowed ) :
			return $this->static_html( $field_content );
		else :
			return $this->live_html( $field_content );
		endif;
	}


	/**
	 * Generate a new markdown parser to generate HMTL content
	 *
	 * @access private
	 * @since 1.7.4
	 *
	 * @returns Boolean TRUE if the new parser was initialized
	 * or FALSE in case a parse already exists
	 */
	private function custom_parser() {
		if ( isset( $this->parser ) && method_exists( $this->parser, 'text' ) ) :
			return FALSE;
		endif;
		require_once( mmd()->plugin_dir . '/MarkupMarkdown/Parsedown/Parsedown.php' );  # 1.7.4
		require_once( mmd()->plugin_dir . '/MarkupMarkdown/Parsedown/Extra.php' ); # 0.8.1
		$this->parser = new \MarkupMarkdown\Parsedown\Extra();
		$this->parser->setStrictMode( true );
		return TRUE;
	}




	/**
	 * Patched to render ordered / unordered list with custom triggers
	 * Feature previously created with the O2 AutoPlug in 3.8
	 *
	 * @since 3.9.0
	 * @access public
	 *
	 * @param String  $content The html source code
	 *
	 * @return String The modified content
	 */
	public function custom_list_filter( $content ) {
		return preg_replace( '#^([\s]*)[\\\\]*[\#]{1}\s#m', '${1}1. ', $content ); # Trigger ordered list written with the sharp sign
	}


	/**
	 * Global method to ouput the html from any markdown content
	 *
	 * @access public
	 * @since 1.0
	 *
	 * @param string $content the html to be parsed
	 * @returns string html rendered from the markdown
	 */
	public function markdown2html( $content ) {
		if ( ! isset( $this->parser ) || empty( $this->parser ) ) :
			$this->custom_parser();
		endif;
		$safe = preg_replace( '#((<\/\w+>)(<\w+>))#', "$2\n$3", isset( $content ) ? $content : '' );
		if ( defined( 'MMD_USE_HEADINGS' ) && ! in_array( '1', MMD_USE_HEADINGS ) && ! defined( 'WP_MMD_O2_PLUG' ) ) :
			$safe = $this->custom_list_filter( $content );
		endif;
		if ( defined( 'MMD_KEEP_SPACES' ) && MMD_KEEP_SPACES > 0 ) : # Since 3.7.1
			$safe_spaces = preg_replace( '#\n\s#m', "\n{-SPACE-}", $safe );
			$markdown = $this->parser->text( $safe_spaces );
			return preg_replace( '#\{-SPACE-\}#', " ", $markdown );
		else :
			return $this->parser->text( $safe );
		endif;
	}

}
