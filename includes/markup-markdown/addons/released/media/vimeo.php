<?php

namespace MarkupMarkdown;

defined( 'ABSPATH' ) || exit;

require_once mmd()->plugin_dir . '/includes/markup-markdown/abstracts/oembed.php';


class MediaVimeoAddon extends \MarkupMarkdown\OEmbedTinyAPI {


	public function __construct() {
		add_filter( 'addon_markdown2html', array( $this, 'vimeo2html' ) );
	}


	/**
	 * Method to parse Vimeo links and output the related iframes
	 *
	 * @access public
	 * @since 1.5.3
	 *
	 * @param string $content the html to be parsed
	 * @returns string html with Vimeo iframes embed code
	 */
	public function vimeo2html( $content = '' ) {
		return $this->oembed_service([
			'content'  => $content,
			'endpoint' => 'http://vimeo.com/api/oembed.json',
			'regexp'   => '#[a-zA-Z\/\/:\.]*vimeo.com/[^\"\n\<]+#u',
		]);
	}

}


new \MarkupMarkdown\MediaVimeoAddon();
