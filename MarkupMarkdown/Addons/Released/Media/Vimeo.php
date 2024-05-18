<?php

namespace MarkupMarkdown\Addons\Released\Media;

defined( 'ABSPATH' ) || exit;

require_once mmd()->plugin_dir . '/MarkupMarkdown/Abstracts/OEmbedTinyAPI.php';


class Vimeo extends \MarkupMarkdown\Abstracts\OEmbedTinyAPI {


	private $prop = array(
		'slug' => 'vimeo',
		'release' => 'stable',
		'active' => 1
	);


	public function __construct() {
		$this->prop[ 'label' ] = esc_html__( 'Vimeo', 'markup-markdown' );
		$this->prop[ 'desc' ] = esc_html__( 'Convert automatically Vimeo links to an embedded iframe.', 'markup-markdown' );
		if ( defined( 'MMD_ADDONS' ) && in_array( $this->prop[ 'slug' ], MMD_ADDONS ) === FALSE ) :
			$this->prop[ 'active' ] = 0;
			return FALSE; # Addon has been desactivated
		endif;
		add_filter( 'addon_markdown2html', array( $this, 'vimeo2html' ) );
	}


	public function __get( $name ) {
		if ( array_key_exists( $name, $this->prop ) ) {
			return $this->prop[ $name ];
		}
		return 'mmd_undefined';
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
