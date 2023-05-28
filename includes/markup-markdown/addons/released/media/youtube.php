<?php

namespace MarkupMarkdown;

defined( 'ABSPATH' ) || exit;

require_once mmd()->plugin_dir . '/includes/markup-markdown/abstracts/oembed.php';


class MediaYoutubeAddon extends \MarkupMarkdown\OEmbedTinyAPI {


	public function __construct() {
		add_filter( 'addon_markdown2html', array( $this, 'youtube2html' ) );
	}


	/**
	 * Method to parse Youtube links and output the related iframes
	 * Previously in core from 1.6.0 until refactoring in v2
	 *
	 * @access public
	 * @since 2.0.0
	 *
	 * @param string $content the html to be parsed
	 * @returns string html with Vimeo iframes embed code
	 */
	public function youtube2html( $content = '' ) {
		return $this->oembed_service([
			'content'  => $content,
			'endpoint' => 'https://www.youtube.com/oembed',
			'regexp'   => '#[a-zA-Z\/\/:\.]*youtu(be.com\/watch\?v=|.be\/)([a-zA-Z0-9\-_]+)([a-zA-Z0-9\/\*\-\_\?\&\;\%\=\.]*)#u'
		]);
	}


}


new \MarkupMarkdown\MediaYoutubeAddon();
