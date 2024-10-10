<?php

defined( 'ABSPATH' ) || exit;

class mmd_acf_field_markdown extends \acf_field {


	/**
	 * Controls field type visibilty in REST requests.
	 *
	 * @var bool
	 */
	public $show_in_rest = true;


	/**
	 * Environment values relating to the theme or plugin.
	 *
	 * @var array $env Plugin or theme context such as 'url' and 'version'.
	 */
	private $env;


	/**
	 * Constructor.
	 */
	public function __construct() {

		/**
		 * Field type reference used in PHP and JS code.
		 *
		 * No spaces. Underscores allowed.
		 */
		$this->name = 'markupmarkdown';

		/**
		 * Field type label.
		 *
		 * For public-facing UI. May contain spaces.
		 */
		$this->label = 'Markup Markdown';

		/**
		 * The category the field appears within in the field type picker.
		 */
		$this->category = 'content'; // basic | content | choice | relational | jquery | layout | CUSTOM GROUP NAME

		/**
		 * Defaults for your custom user-facing settings for this field type.
		 */
		$this->defaults = array(
			# 'font_size'	=> 14,
		);

		/**
		 * Strings used in JavaScript code.
		 *
		 * Allows JS strings to be translated in PHP and loaded in JS via:
		 *
		 * ```js
		 * const errorMessage = acf._e("FIELD_NAME", "error");
		 * ```
		 */
		$this->l10n = array(
			'error'	=> __( 'Error! Please enter a higher value', 'TEXTDOMAIN' ),
		);

		$this->env = array(
			'url'     => site_url( str_replace( ABSPATH, '', __DIR__ ) ), // URL to the acf-FIELD-NAME directory.
			'version' => '1.0.12', // Replace this with your theme or plugin version constant.
		);

		parent::__construct();

		add_filter('acf/format_value/type=markupmarkdown', array( $this, 'mmd_acf_render_field' ), 10, 3 );
	}


	/**
	 * Settings to display when users configure a field of this type.
	 *
	 * These settings appear on the ACF “Edit Field Group” admin page when
	 * setting up the field.
	 *
	 * @param array $field
	 * @return void
	 */
	public function render_field_settings( $field ) {
		/*
		 * Repeat for each setting you wish to display for this field type.

		acf_render_field_setting(
			$field,
			array(
				'label'			=> __( 'Font Size','TEXTDOMAIN' ),
				'instructions'	=> __( 'Customise the input font size','TEXTDOMAIN' ),
				'type'			=> 'number',
				'name'			=> 'font_size',
				'append'		=> 'px',
			)
		);

		// To render field settings on other tabs in ACF 6.0+:
		// https://www.advancedcustomfields.com/resources/adding-custom-settings-fields/#moving-field-setting
		 */
	}


	/**
	 * HTML content to show when a publisher edits the field on the edit screen.
	 *
	 * @param Array $field The field settings and values.
	 * @return Void
	 */
	public function render_field( $field ) {
		/*
			Debug output to show what field data is available.
			echo '<pre>';
			print_r( $field );
			echo '</pre>';
		*/
		if ( ! defined( 'MMD_CUSTOM_FIELD' ) ) :
			define( 'MMD_CUSTOM_FIELD', 1 );
		endif;
		// Display an input field that uses the 'font_size' setting.
?><textarea class="wp-editor-area" style="height: 300px" cols="40" name="<?php echo esc_attr( $field[ 'name' ] ); ?>"><?php
	echo $field[ 'value' ];
?></textarea><?php
	}

	/**
	 * Enqueues CSS and JavaScript needed by HTML in the render_field() method.
	 *
	 * Callback for admin_enqueue_script.
	 *
	 * @return void
	 */
	public function input_admin_enqueue_scripts() {
		$url     = mmd()->plugin_uri . 'MarkupMarkdown/Addons/Unsupported/AdvancedCustomField/';
		$version = $this->env['version'];

		wp_register_script( 'mmd-markupmarkdown', $url . 'field.min.js', array( 'acf-input' ), $version );
		wp_register_style( 'mmd-markupmarkdown', $url . 'field.min.css', array( 'acf-input' ), $version );

		wp_enqueue_script( 'mmd-markupmarkdown' );
		wp_enqueue_style( 'mmd-markupmarkdown' );
	}


	/**
	 * Filters the markdown's field $value after being loaded by a template function such as get_field().
	 *
	 * https://www.advancedcustomfields.com/resources/acf-format_value/
	 *
	 * @param mixed $value The field value.
	 * @param integer $post_id  The post ID where the value is saved.
	 * @param array $field The field array containing all settings.
	 * @return mixed $value The field value updated.
	 */
	public function mmd_acf_render_field( $value, $post_id, $field ) {
		if ( ! function_exists( 'mmd' ) ) :
			return $value;
		endif;
		return mmd()->markdown2html( $value );
	}


}
