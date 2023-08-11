<?php
/**
 * Markup Markdown
 *
 * Plugin Name: Markup Markdown
 * Description: Replaces the Gutenberg Block Editor in favor of pure markdown based markups
 * Version:     2.3.0
 * Author:      Pierre-Henri Lavigne
 * Author URI:  https://red.phutu.red/plugins/markup-markdown/
 * License:     GPLv2 or later
 * License URI: http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * Text Domain: markup-markdown
 * Domain Path: /languages
 * Requires at least: 4.9
 * Tested up to: 6.3.0
 *
 * This program is free software; you can redistribute it and/or modify it under the terms of the GNU
 * General Public License version 2, as published by the Free Software Foundation. You may NOT assume
 * that you can use any other version of the GPL.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without
 * even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 */

defined( 'ABSPATH' ) || exit;
define('MMD_FILE_URL', __FILE__);

if ( ! class_exists( 'Markup_Markdown' ) ) :

	class Markup_Markdown {

		protected $parser;

		protected $settings = array(
			'plugin_uri' => '',
			'plugin_dir' => '',
			'plugin_slug' => '',
			'cache_dir' => '',
			'default_conf' => array()
		);


		public function __construct() {
			$this->settings[ 'plugin_slug' ] = plugin_basename( __DIR__ );
			$this->settings[ 'plugin_uri' ] = plugin_dir_url( __FILE__ );
			$this->settings[ 'plugin_dir' ] = plugin_dir_path( __FILE__ );
			$this->settings[ 'cache_dir' ] = WP_CONTENT_DIR . "/mmd-cache";
			require_once $this->settings[ 'plugin_dir' ] . '/includes/markup-markdown/core/activation.php';
		}


		/**
		 * Overloading method __get
		 *
		 * @since 2.0.0
		 * @access public
		 *
		 * @param String $name The name of the key in the $settings variable to retrieve
		 * @return Mixed The value of the related key in $settings or an empty string
		 */
		public function __get( $name ) {
			return isset( $this->settings[ $name ] ) ? $this->settings[ $name ] : '';
		}


		/**
		 * Overloading method __set
		 *
		 * @since 2.0.0
		 * @access public
		 *
		 * @param String $name The name of the key in the $settings variable to overwrite
		 * @param Mixed $val The new value of the related key in the $settings variable
		 * @return Void
		 */
		public function __set( $name, $val ) {
			if ( isset( $this->settings[ 'name' ] ) && is_array( $this->settings[ $name ] ) && is_array( $val ) ) :
				$this->settings[ 'name' ] = array_merge( $this->settings[ 'name' ], $val );
			else :
				$fixed = array( 'plugin_uri', 'plugin_dir', 'plugin_slug', 'cache_dir', 'default_conf' );
				if ( ! in_array( $name, $fixed ) ) :
					$this->settings[ 'name' ] = $val;
				endif;
			endif;
		}


		public function markdown2html( $content ) {
			return apply_filters( 'field_markdown2html', $content );
		}


	}


	// Allow developers to access our properties and methods of the instance
	final class Markup_Markdown_Instance {

		private static $instance;

		public static function instance() {
			if ( ! isset( self::$instance ) && ! ( self::$instance instanceOf Markup_Markdown_Instance ) ) :
				self::$instance = new Markup_Markdown();
			endif;
			return self::$instance;
		}

	}


	if ( ! function_exists( 'mmd' ) ) :
		function mmd() {
			return Markup_Markdown_Instance::instance();
		}
		// Run
		mmd();
	endif;


endif;
