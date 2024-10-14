<?php

namespace MarkupMarkdown\Addons\Unsupported;

defined( 'ABSPATH' ) || exit;

/**
 * Registration logic for the new ACF field type.
 */
class AdvancedCustomField {


	private $prop = array(
		'slug' => 'acf',
		'release' => 'beta',
		'active' => 1
	);


	public function __construct() {
		$this->prop[ 'label' ] = 'Advanced Custom Fields';
		$this->prop[ 'desc' ] = __( 'This addon enable a new content type so you can write directly markdown with the "Markup Markdown" custom field from ACF.', 'markup-markdown' );
		if ( defined( 'MMD_ADDONS' ) && in_array( $this->prop[ 'slug' ], MMD_ADDONS ) === FALSE ) :
			$this->prop[ 'active' ] = 0;
			return FALSE; # Addon has been desactivated
		endif;
		add_action( 'init', array( $this, 'mmd_include_acf_field_markdown' ) );
		if ( ! is_admin() ) :
			add_action( 'wp', array( $this, 'mmd_frontend_filters' ) );
		endif;
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
		add_filter( 'acf/post_type/available_supports', function( $acf_available_supports, $acf_post_type ) {
			$acf_available_supports[ 'markup-markdown' ] = 'Markup Markdown';
			return $acf_available_supports;
		}, 10, 2);
	}


	/**
	 * Allow markdown use on the frontend :
	 * + Filter to grant the markdown editor to be loaded on the frontend
	 * + Filter top disable TinyMCE on the frontend if need be, we switch the field type from "wysiwyg" to "textarea"
	 *
	 * @access public
	 * @since 3.3.0
	 *
	 * @return Boolean TRUE if backend related or FALSE if frontend related
	 */
	public function mmd_frontend_filters() {
		# Action triggered by acf_form_head()
		add_action( 'acf/input/admin_head', function() {
			add_filter( 'mmd_frontend_enabled', '__return_true' );
		});
		# Action triggered by acf_form()
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
