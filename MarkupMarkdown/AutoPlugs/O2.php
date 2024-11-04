<?php

namespace MarkupMarkdown\AutoPlugs;

defined( 'ABSPATH' ) || exit;


class O2 {


	public function __construct() {
		if ( file_exists( WP_PLUGIN_DIR . '/o2/o2.php' ) ) :
			define( 'MMD_O2_PLUG', true );
			add_filter( 'o2_post_fragment', array( $this, 'o2_post_fragment_filter' ), 11, 2 );
			add_filter( 'the_content', array( $this, 'o2_parse_list_filter' ), 1 );
		endif;
	}


	/**
	 * Filters O2 post content data to update escaped sharp signs '\#' from the markdown code
	 * to standard sharp signs '#' so ordered list can be generated
	 *
	 * @since 3.8.0
	 * @access public
	 *
	 * @param Array  $fragment The post fragment object
	 * @param Integer $post_ID The related WP Post ID
	 *
	 * @return Array The modified fragment
	 */
	public function o2_post_fragment_filter( $fragment, $post_ID ) {
		if ( isset( $fragment[ 'contentRaw' ] ) ) :
			$fragment[ 'contentRaw' ] = preg_replace( '#[\\\\]{1}[\#]{1}#', '#', $fragment[ 'contentRaw' ] );
			$fragment[ 'contentRaw' ] = preg_replace( '#\t#', ' ', $fragment[ 'contentRaw' ] );
		endif;
		return $fragment;
	}


	/**
	 * O2 content list filter to patch ordered / unordered list
	 *
	 * @since 3.8.0
	 * @access public
	 *
	 * @param String  $content The html source code
	 *
	 * @return String The modified content
	 */
	public function o2_parse_list_filter( $content ) {
		if ( defined( 'MMD_USE_HEADINGS' ) && ! in_array( '1', MMD_USE_HEADINGS ) ) :
			preg_match_all( '#(\t*)[\\\\]{1}[\#]{1}#', $content, $sharp_items ); # Trigger ordered list written with the sharp sign
			if ( isset( $sharp_items ) && count( $sharp_items ) > 0 ) :
				foreach( $sharp_items as $item ) :
					$tmp = str_replace( array( "\t", "\#" ), array( ' ', '#' ),  $item );
					$content = str_replace( $item, $tmp, $content );
				endforeach;
			endif;
			preg_match_all( '#(\t*)[xo*-+]{1}#', $content, $bullet_items ); # Trigger unordered list written
			if ( isset( $bullet_items ) && count( $bullet_items ) > 0 ) :
				foreach( $bullet_items as $item ) :
					$tmp = str_replace( "\t", ' ', $item );
					$content = str_replace( $item, $tmp, $content );
				endforeach;
			endif;
		endif;
		return $content;
	}


}


new \MarkupMarkdown\AutoPlugs\O2();
