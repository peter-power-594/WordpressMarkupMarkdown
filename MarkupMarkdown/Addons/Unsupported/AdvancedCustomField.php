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
		add_action( 'wp', array( $this, 'mmd_frontend_filters' ) );
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


	/**
	 * Allow markdown use on the frontend so it can be triggered by acf_form_head / acf_form :
	 * + Filter to grant the markdown editor to be loaded on the frontend
	 * + Filter top disable TinyMCE on the frontend if need be, we switch the field type from "wysiwyg" to "textarea"
	 *
	 * @access public
	 * @since 3.3.0
	 *
	 * @return Boolean TRUE if backend related or FALSE if frontend related
	 */
	public function mmd_frontend_filters() {
		if ( ! is_admin() ) :
			return false;
		endif;
		add_action( 'acf/input/admin_enqueue_scripts', function() {
			add_filter( 'mmd_front_enabled', '__return_true' );
		});
		add_filter( 'acf/get_valid_field', function( $field ) {
			if ( strpos( $field[ 'name' ], 'post_content' ) !== FALSE && $field[ 'type' ] == 'wysiwyg' ) :
				if ( defined( 'MMD_SUPPORT_ENABLED' ) && MMD_SUPPORT_ENABLED ) :
					$field[ 'type' ] = 'textarea';
					$field[ 'toolbar' ] = 0;
					$field[ 'media_upload' ] = 0;
					$field[ 'rows' ] = 15;
					$field[ 'maxlength' ] = 524524;
				endif;
			endif;
			return $field;
		});
		return true;
	}


}
