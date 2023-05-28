<?php

namespace MarkupMarkdown;

defined( 'ABSPATH' ) || exit;

/**
 * Registration logic for the new ACF field type.
 */
class ACFAddon {


	private $prop = array(
		'slug' => 'acf',
		'label' => '<abbr title="Advanced Custom Fields">ACF</abbr>',
		'desc' => 'This addon enable the "Markup Markdown" <em>content</em> type so you can use the markdown editor with custom fields in ACF.'
	);


	public function __construct() {
		if ( defined( 'MMD_ADDONS' ) && in_array( $this->prop[ 'slug' ], MMD_ADDONS ) === FALSE ) :
			return FALSE; # Addon has been desactivated
		endif;
		add_action( 'init', array( $this, 'mmd_include_acf_field_markdown' ) );
	}


	public function __get( $name ) {
		if ( array_key_exists( $name, $this->prop ) ) {
			return $this->prop[ $name ];
		}
		return 'mmd_undefined';
	}


	public function mmd_include_acf_field_markdown() {
		if ( ! function_exists( 'acf_register_field_type' ) ) :
			return;
		endif;
		require_once mmd()->plugin_dir . 'includes/markup-markdown/addons/unsupported/acf/class-mmd-acf-field-markdown.php';
		acf_register_field_type( 'mmd_acf_field_markdown' );
	}


}
