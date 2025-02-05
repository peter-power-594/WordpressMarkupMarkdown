<?php

namespace MarkupMarkdown\AutoPlugs;

defined( 'ABSPATH' ) || exit;


class CodeSnippets {


	public function __construct() {
		if ( file_exists( WP_PLUGIN_DIR . '/code-snippets/code-snippets.php' ) ) :
			$this->init();
		endif;
	}


	public function init() {
		if ( is_admin() ) :
			$page = filter_input( INPUT_GET, 'page', FILTER_SANITIZE_SPECIAL_CHARS );
			if ( isset( $page ) && in_array( $page, array( 'add-snippet', 'edit-snippet' ) ) ) :
				add_filter( 'mmd_disable_gutenberg', '__return_false' );
			endif;
		endif;
	}


}


new \MarkupMarkdown\AutoPlugs\CodeSnippets();
