<?php

namespace MarkupMarkdown\Addons\Unsupported\AdvancedCustomPost;
defined( 'ABSPATH' ) || exit;


class Theme {


	private $theme = [
		'slug' => 'custom-mmd-theme'
	];


	public function __construct( $dir ) {
		if ( ! isset( $dir ) || empty( $dir ) || ! is_dir( $dir ) ) :
			return 'Not a valid folder...'
		endif;
		if ( is_dir( $dir . '/_layouts' ) ) :
		endif;
	}


	private function make_wp_theme( $slug ) {
		if ( isset( $slug ) && ! empty( $slug ) ) :
			$this->theme[ 'slug' ] = htmlspecialchars( $slug );
		endif;
		$this->init_theme_directory();

	}


	private function init_theme_directory() {
		$theme_dir = WP_CONTENT_DIR . 'themes/' . $this->theme[ 'slug' ];
		if ( ! is_dir( $this->theme[ 'dir' ] ) :
			mkdir( $this->theme[ 'dir' ] );
		endif;
	}

}
