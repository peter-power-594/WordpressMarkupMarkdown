<?php

namespace MarkupMarkdown;

defined( 'ABSPATH' ) || exit;

require_once mmd()->plugin_dir . '/includes/markup-markdown/abstracts/oembed.php';


class MediaYoutubeAddon extends \MarkupMarkdown\OEmbedTinyAPI {


	private $prop = array(
		'slug' => 'youtube',
		'label' => 'Youtube',
		'desc' => 'Convert automatically Youtube links to an embedded iframe.',
		'release' => 'stable'
	);


	public function __construct() {
		if ( defined( 'MMD_ADDONS' ) && in_array( $this->prop[ 'slug' ], MMD_ADDONS ) === FALSE ) :
			return FALSE; # Addon has been desactivated
		endif;
		add_filter( 'addon_markdown2html', array( $this, 'youtube2html' ) );
	}


	public function __get( $name ) {
		if ( array_key_exists( $name, $this->prop ) ) {
			return $this->prop[ $name ];
		}
		return 'mmd_undefined';
	}


	/**
	 * Method to parse Youtube links and output the related iframes
	 * Previously in core from 1.6.0 until refactoring in v2
	 *
	 * @access public
	 * @since 2.0.0
	 *
	 * @param String $content The html to be parsed.
	 * @return String The html with Youtube iframes embed code.
	 */
	public function youtube2html( $content = '' ) {
		return $this->oembed_service([
			'content'  => $content,
			'endpoint' => 'https://www.youtube.com/oembed',
			'regexp'   => '#[a-zA-Z\/\/:\.]*youtu(be.com\/watch\?v=|.be\/)([a-zA-Z0-9\-_]+)([a-zA-Z0-9\/\*\-\_\?\&\;\%\=\.]*)#u'
		]);
	}


}
