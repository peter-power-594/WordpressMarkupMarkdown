<?php
/**
 * Markup Markdown
 *
 * Plugin Name: Markup Markdown
 * Description: Replaces the Gutenberg Block Editor in favor of pure markdown based markups
 * Version:     3.13.0
 * Author:      Pierre-Henri Lavigne
 * Author URI:  https://www.markup-markdown.com
 * License:     GPLv2 or later
 * License URI: http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * Text Domain: markup-markdown
 * Domain Path: /languages
 * Requires at least: 4.9
 * Tested up to: 6.7.1
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
			'version' => '3.13.0',
			'plugin_uri' => '',
			'plugin_dir' => '',
			'plugin_slug' => '',
			'cache_dir' => '',
			'conf_dir' => '',
			'curr_blog' => '1_1',
			'default_conf' => array()
		);


		public function __construct() {
			$this->settings[ 'plugin_slug' ] = plugin_basename( __DIR__ );
			$this->settings[ 'plugin_uri' ] = plugin_dir_url( __FILE__ );
			$this->settings[ 'plugin_dir' ] = plugin_dir_path( __FILE__ );
			$this->settings[ 'cache_dir' ] = WP_CONTENT_DIR . '/mmd-cache';
			$this->settings[ 'conf_dir' ] = WP_CONTENT_DIR . '/mmd-conf';
			$this->settings[ 'cache_blog_prefix' ] = WP_CONTENT_DIR . '/mmd-cache/' . get_current_network_id() . '_' . get_current_blog_id() . '_';
			$this->settings[ 'conf_blog_prefix' ] = WP_CONTENT_DIR . '/mmd-conf/' . get_current_network_id() . '_' . get_current_blog_id() . '_';
			require_once $this->settings[ 'plugin_dir' ] . '/MarkupMarkdown/Core/Activation.php';
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
			if ( isset( $this->settings[ $name ] ) && is_array( $this->settings[ $name ] ) && is_array( $val ) ) :
				$this->settings[ $name ] = array_merge( $this->settings[ $name ], $val );
			else :
				$fixed = array( 'plugin_uri', 'plugin_dir', 'plugin_slug', 'cache_dir', 'conf_dir', 'curr_blog', 'default_conf' );
				if ( ! in_array( $name, $fixed ) ) :
					$this->settings[ $name ] = $val;
				endif;
			endif;
		}


		/**
		 *  @since 1.0
		 *  @access public
		 *
		 *  @param String $content The markdown code
		 *
		 *  @return String The HTML content
		 */
		public function markdown2html( $content ) {
			$filtered = apply_filters( 'field_markdown2html', $content );
			$html = htmlspecialchars_decode( $filtered, ENT_COMPAT );
			return do_shortcode( $html );
		}


		/**
		 *  @since 3.0
		 *  @access public
		 *
		 *  @param $file String Target file
		 *
		 *  @return Void
		 */
		public function clear_cache( $file = '' ) {
			if ( function_exists( 'wp_opcache_invalidate' ) ) :
				wp_opcache_invalidate( $file );
			elseif ( function_exists( 'opcache_invalidate' ) ) :
				opcache_invalidate( $file );
			endif;
		}


		/**
		 * Tiny function to filter user permissions
		 *
		 * @since 3.3.0
		 * @access public
		 *
		 * @param Boolean TRUE to grant access user with enough premission
		 *
		 * @return Boolean TRUE if granted or FALSE
		 */
		public function user_allowed( $user_id = 0 ) {
			if ( ! $user_id ) :
				$user_id = get_current_user_id();
			endif;
			if ( ! $user_id ) :
				# Disable *Guest* users
				return false;
			endif;
			$user = new \WP_User( $user_id );
			if ( $user && ! $user->has_cap( 'edit_posts' ) ) :
				# Disable *Subscribers* or users without edit permissions
				return false;
			endif;
			return true;
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
