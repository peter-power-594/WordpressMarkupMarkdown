<?php

namespace MarkupMarkdown;

defined( 'ABSPATH' ) || exit;
defined( 'MMD_SUPPORT_ENABLED' ) || exit;


class Parser {


	private $parser = '';

	private $preview = 'false';

	public function __construct() {
		if ( MMD_SUPPORT_ENABLED > 0 ) :
			# Add the filter so the markdown can be parsed and the html generated properly on the frontend
			$this->preview = filter_input( INPUT_GET, 'preview', FILTER_SANITIZE_SPECIAL_CHARS );
			add_filter( 'post_markdown2html', array( $this, 'cached_post_markdown2html' ) );
			add_filter( 'field_markdown2html', array( $this, 'post_markdown2html' ) );
		else :
			add_filter( 'post_markdown2html', array( $this, 'dummy_markdown' ) );
			add_filter( 'field_markdown2html', array( $this, 'dummy_markdown' ) );
		endif;
	}


	/**
	 * Dummy method to return original post content in case markdown is disabled
	 *
	 * @access public
	 * @since 2.0.0
	 *
	 * @param   string $content The html to be parsed.
	 * @returns string The html rendered.
	 */
	public function dummy_markdown( $content ) {
		return $content;
	}


	/**
	 * Method to check for existing cached content before rendering the markdown
	 *
	 * @access public
	 * @since 2.0.0
	 *
	 * @param string $content the html to be parsed
	 * @returns string html rendered from the markdown
	 */
	public function cached_post_markdown2html( $content ) {
		$cache_content = mmd()->cache_dir . "/." . get_main_site_id() . '_' . get_the_id() . ".html";
		# Cache available
		if ( $this->preview !== 'true' && file_exists( $cache_content ) ) :
			$my_content = file_get_contents( $cache_content );
			return $my_content;
		endif;
		# Cache not available
		$html = $this->post_markdown2html( $content );
		# Decode the double quotes to avoid breaking native WP shortcodes
		$html_with_shortcodes = htmlspecialchars_decode( $html, ENT_COMPAT );
		file_put_contents( $cache_content, $html_with_shortcodes );
		return $html_with_shortcodes;
	}


	/**
	 * Method to ouput the html for (custom) post / page content
	 *
	 * @access public
	 * @since 1.5.4
	 *
	 * @param string $content the html to be parsed
	 * @returns string html rendered from the markdown
	 */
	public function post_markdown2html( $content ) {
		return apply_filters( 'addon_markdown2html', $this->markdown2html( $content ) );
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
		require_once( mmd()->plugin_dir . '/includes/parsedown/Parsedown.php' ); # 1.7.4
		require_once( mmd()->plugin_dir . '/includes/parsedown-extra/ParsedownExtra.php' ); # 0.8.1
		$this->parser = new \ParsedownExtra();
		return TRUE;
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
		$content = preg_replace( '#((<\/\w+>)(<\w+>))#', "$2\n$3", $content );
		return $this->parser->text( $content );
	}

}

new \MarkupMarkdown\Parser();
