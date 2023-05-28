<?php

namespace MarkupMarkdown;

defined( 'ABSPATH' ) || exit;

/**
 * Registration logic for the new ACF field type.
 */
class ACFAddon {

	public function __construct() {
		add_action( 'init', array( $this, 'mmd_include_acf_field_markdown' ) );		
	}


	public function mmd_include_acf_field_markdown() {
		if ( ! function_exists( 'acf_register_field_type' ) ) :
			return;
		endif;
		require_once mmd()->plugin_dir . 'includes/markup-markdown/addons/unsupported/acf/class-mmd-acf-field-markdown.php';
		acf_register_field_type( 'mmd_acf_field_markdown' );
	}
}

new \MarkupMarkdown\ACFAddon();