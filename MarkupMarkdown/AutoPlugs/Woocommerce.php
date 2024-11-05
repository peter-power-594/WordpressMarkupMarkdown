<?php

namespace MarkupMarkdown\AutoPlugs;

defined( 'ABSPATH' ) || exit;


class Woocommerce {


	public function __construct() {
		if ( file_exists( WP_PLUGIN_DIR . '/woocommerce/woocommerce.php' ) ) :
			define( 'MMD_WOOCOMMERCE_PLUG', true );
			add_action( 'after_setup_theme', array( $this, 'mmd_woocommerce_plug' ) );
		endif;
	}


	public function mmd_woocommerce_plug() {
		add_filter( 'woocommerce_taxonomy_archive_description_raw', array( $this, 'tax_desc_mmd2html' ), 10, 2);
	}


	/**
	 * Filters the archive's raw description on taxonomy archives.
	 *
	 * @since 3.4.1
	 * @source woocommerce/includes/wc-template-functions.php
	 *
	 * @param string  $term_desc Raw description text.
	 * @param WP_Term $term      Term object for this taxonomy archive.
	 *
	 * @return String The modified term description
	 */
	 public function tax_desc_mmd2html( $term_desc, $term ) {
		 return apply_filters( 'post_markdown2html', str_replace( [ '<p>', '</p>' ], '', $term_desc ), 0 );
	 }


}


new \MarkupMarkdown\AutoPlugs\Woocommerce();

