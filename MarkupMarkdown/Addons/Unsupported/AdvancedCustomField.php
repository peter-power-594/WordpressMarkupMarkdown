<?php

namespace MarkupMarkdown\Addons\Unsupported;

defined( 'ABSPATH' ) || exit;

/**
 * Registration logic for the new ACF field type.
 */
class AdvancedCustomField {


	private $prop = array(
		'slug' => 'acf',
		'label' => '<abbr title="Advanced Custom Fields">ACF</abbr>',
		'desc' => 'This addon enable a new content type so you can write directly markdown with the "Markup Markdown" custom field from ACF.',
		'release' => 'beta',
		'active' => 1
	);


	public function __construct() {
		if ( defined( 'MMD_ADDONS' ) && in_array( $this->prop[ 'slug' ], MMD_ADDONS ) === FALSE ) :
			$this->prop[ 'active' ] = 0;
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
		require_once mmd()->plugin_dir . 'MarkupMarkdown/Addons/Unsupported/AdvancedCustomField/mmd_acf_field_markdown.php';
		acf_register_field_type( 'mmd_acf_field_markdown' );
	}


}
