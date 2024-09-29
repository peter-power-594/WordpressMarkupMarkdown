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
		if ( ! $this->init_theme_directory() ) :
			return false;
		endif;
		$this->theme[ 'dir' ] = ;
		$this->generate_templates();
	}


	private function init_theme_directory() {
		$theme_dir = WP_CONTENT_DIR . 'themes/' . $this->theme[ 'slug' ];
		if ( ! is_dir( $this->theme[ 'dir' ] ) ) :
			$new_dir = mkdir( $this->theme[ 'dir' ] );
			return $new_dir !== false ? $theme_dir : false; 
		endif;
		return $theme_dir;
	}


	private function generate_templates() {
		$layouts = ABSPATH . '_layouts';
		if ( ! is_dir( $layouts ) ) :
			return false;
		endif;
		$files = scandir( $theme_dir );
		foreach( $files as $key => $val ) :
			if ( in_array( $val, [ '.', '..' ] ) ) :
				continue;
			elseif( is_dir( $val ) ) :
				continue;
			endif;
			$my_file = ABSPATH . '_layouts/' . $val;
			$file_parts = pathinfo( $my_file );
			if ( $file_parts[ 'extension' ] !== 'html' ) :
				continue;
			endif;
			switch ( $file_parts[ 'filename' ] ) :
				case 'post':
					$this->make_single_template( $my_file );
				break;
			endswitch;
		endforeach;
		return true;
	}


	private function make_single_template( $post_tmpl ) {
		$single_tmpl = $this->theme[ 'dir' ] . '/single.php';
		if ( ! copy( $post_tmpl, $single_tmpl ) ) :
			return false;
		endif;
		$tmp = file_get_contents( $single_tmpl );
		$tmp = $this->php_fixed( $tmp );
	}


	private function php_fixed( $str ) {
		$str = preg_replace( '#\{\{ page.content \}\}#', 'the_content()', $str );
		return $str;
	}
}
