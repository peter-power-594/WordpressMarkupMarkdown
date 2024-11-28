<?php

namespace MarkupMarkdown\Addons\Released;

defined( 'ABSPATH' ) || exit;


class Layout {


	private $prop = array(
		'slug' => 'layout',
		'release' => 'stable',
		'active' => 1
	);


	private $gal = 0;


	private $toolbar_conf = '';


	public function __construct() {
		$this->prop[ 'label' ] = __( 'Layout', 'markup-markdown' );
		$this->prop[ 'desc' ] = __( 'A few tools to help you enhancing your layout. (Lightbox, Masonry, etc...)', 'markup-markdown' );
		mmd()->default_conf = array( 'MMD_USE_LIGHTBOX' => 1 );
		mmd()->default_conf = array( 'MMD_USE_IMAGESLOADED' => 1 );
		mmd()->default_conf = array( 'MMD_USE_MASONRY' => 1 );
		mmd()->default_conf = array( 'MMD_USE_BLOCKSTYLES' => 0 );
		mmd()->default_conf = array( 'MMD_CUSTOM_TOOLBAR' => 0 );
		mmd()->default_conf = array( 'MMD_KEEP_SPACES' => 0 );
		mmd()->default_conf = array( 'MMD_USE_HEADINGS' => [] );
		$this->toolbar_conf = mmd()->conf_blog_prefix . 'conf_easymde_toolbar.json';
		if ( defined( 'MMD_ADDONS' ) && in_array( $this->prop[ 'slug' ], MMD_ADDONS ) === FALSE ) :
			$this->prop[ 'active' ] = 0;
			return false; # Addon has been desactivated
		endif;
		if ( is_admin() ) :
			add_filter( 'mmd_verified_config', array( $this, 'update_config' ) );
			add_filter( 'mmd_var2const', array( $this, 'create_const' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'load_layout_assets' ) );
		else :
			add_filter( 'addon_markdown2html', array( $this, 'render_lightbox_masonry' ) );
			if ( defined( 'MMD_USE_BLOCKSTYLES' ) && MMD_USE_BLOCKSTYLES ) :
				add_filter( 'addon_markdown2html', array( $this, 'render_gutenberg_basics' ), 12, 1 );
			endif;
			add_action( 'wp_enqueue_scripts', array( $this, 'my_plugin_assets' ), 11 );
		endif;
	}


	public function __get( $name ) {
		if ( array_key_exists( $name, $this->prop ) ) {
			return $this->prop[ $name ];
		}
		return 'mmd_undefined';
	}


	/**
	 * Filter to parse layout options inside the options screen when the form was submitted
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @return Void
	 */
	public function update_config( $my_cnf ) {
		$my_cnf[ 'goodvibes' ] = filter_input( INPUT_POST, 'mmd_goodvibes', FILTER_VALIDATE_INT );
		$my_cnf[ 'lightbox' ] = filter_input( INPUT_POST, 'mmd_lightbox', FILTER_VALIDATE_INT );
		$my_cnf[ 'imagesloaded' ] = filter_input( INPUT_POST, 'mmd_imagesloaded', FILTER_VALIDATE_INT );
		$my_cnf[ 'masonry' ] = filter_input( INPUT_POST, 'mmd_masonry', FILTER_VALIDATE_INT );
		$my_cnf[ 'toolbar' ] = preg_replace( "#[^a-z0-9_,]#", "", filter_input( INPUT_POST, 'mmd_toolbar', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) );
		$my_cnf[ 'headings' ] = [];
		$fm_headings = filter_input( INPUT_POST, 'mmd_headings', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
		if ( isset( $fm_headings ) && is_array( $fm_headings ) ) :
			foreach ( $fm_headings as $heading ) :
				$heading = (int)$heading;
				if ( in_array( $heading, $my_cnf[ 'headings' ] ) || ! is_numeric( $heading ) || $heading < 1 || $heading > 6 ) :
					continue;
				endif;
				$my_cnf[ 'headings' ][] = $heading;
			endforeach;
		endif;
		$my_cnf[ 'keep_spaces' ] = filter_input( INPUT_POST, 'mmd_keepspaces', FILTER_VALIDATE_INT );
		return $my_cnf;
	}
	public function create_const( $my_cnf ) {
		$my_cnf[ 'MMD_USE_LIGHTBOX' ] = isset( $my_cnf[ 'lightbox' ] ) ? $my_cnf[ 'lightbox' ] : 0;
		unset( $my_cnf[ 'lightbox' ] );
		$my_cnf[ 'MMD_USE_IMAGESLOADED' ] = isset( $my_cnf[ 'imagesloaded' ] ) ? $my_cnf[ 'imagesloaded' ] : 0;
		unset( $my_cnf[ 'imagesloaded' ] );
		$my_cnf[ 'MMD_USE_MASONRY' ] = isset( $my_cnf[ 'masonry' ] ) ? $my_cnf[ 'masonry' ] : 0;
		unset( $my_cnf[ 'masonry' ] );
		$my_cnf[ 'MMD_USE_BLOCKSTYLES' ] = isset( $my_cnf[ 'goodvibes' ] ) ? $my_cnf[ 'goodvibes' ] : 0;
		unset( $my_cnf[ 'goodvibes' ] );
		$my_cnf[ 'MMD_USE_HEADINGS' ] = isset( $my_cnf[ 'headings' ] ) && count( $my_cnf[ 'headings' ] ) > 1 ? $my_cnf[ 'headings' ] : [];
		unset( $my_cnf[ 'headings' ] );
		if ( isset( $my_cnf[ 'toolbar' ] ) > 0 ) :
			file_put_contents( $this->toolbar_conf, '{"my_buttons":' . json_encode( explode( ",", $my_cnf[ 'toolbar' ] ) ) . '}' );
			unset( $my_cnf[ 'toolbar' ] );
		endif;
		$my_cnf[ 'MMD_KEEP_SPACES' ] = isset( $my_cnf[ 'keep_spaces' ] ) ? $my_cnf[ 'keep_spaces' ] : 0;
		unset( $my_cnf[ 'keep_spaces' ] );
		return $my_cnf;
	}


	public function load_layout_assets( $hook ) {
		if ( 'settings_page_markup-markdown-admin' === $hook ) :
			add_action( 'mmd_tabmenu_options', array( $this, 'add_tabmenu' ) );
			add_action( 'mmd_tabcontent_options', array( $this, 'add_tabcontent' ) );
		endif;
	}


	/**
	 * Add the layout menu item inside the options screen
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @return Void
	 */
	public function add_tabmenu() {
		echo "\t\t\t\t\t\t<li><a href=\"#tab-layout\" class=\"mmd-ico ico-layout\">" . __( 'Layout', 'markup-markdown' ) . "</a></li>\n";
	}


	/**
	 * Display layout options inside the options screen
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @return Void
	 */
	public function add_tabcontent() {
		$conf_file = mmd()->conf_blog_prefix . 'conf.php';
		if ( file_exists( $conf_file ) ) :
			require_once $conf_file;
		endif;
		$my_tmpl = mmd()->plugin_dir . '/MarkupMarkdown/Addons/Released/Templates/LayoutForm.php';
		if ( file_exists( $my_tmpl ) ) :
			mmd()->clear_cache( $my_tmpl );
			$toolbar_conf = $this->toolbar_conf;
			include $my_tmpl;
		endif;
	}


	/**
	 * Increment the gallery counter to separate different lightbox
	 *
	 * @since 2.2.2
	 * @access public
	 *
	 * @param \HTML_node $gallery_style The html opening tag and styles of current gallery
	 *
	 * @return \HTML_node The updated html code
	 */
	public function gallery_style_filter( $gallery_style ) {
		$this->gal++;
		return $gallery_style;
	}


	/**
	 * Add extra html markup to trigger the lightbox on gallery
	 *
	 * @since 2.2.2
	 * @access public
	 *
	 * @param Array $attributes The current link
	 * @param Integer $post_ID The post ID
	 *
	 * @return Array The updated link attributes
	 */
	public function attachment_link_attributes_filter( $attributes, $post_ID ) {
		if ( isset( $attributes[ 'href' ] ) && strpos( $attributes[ 'href' ], 'attachment' ) === FALSE ) :
			$attributes[ 'data-lightbox' ] = 'gallery' . $post_ID . '-' . $this->gal;
		endif;
		return $attributes;
	}


	/**
	 * Load Lightbox assets on the frontend
	 *
	 * @since 3.0.0
	 * @access private
	 *
	 * @return Integer 1 if the framework is used or 0 if unused
	 */
	private function load_lighbox_framework() {
		if ( defined( 'MMD_USE_LIGHTBOX' ) && MMD_USE_LIGHTBOX > 0 ) :
			$plugin_uri = mmd()->plugin_uri;
			wp_deregister_script( 'lightbox' );
			wp_deregister_script( 'jquery-lightbox' );
			wp_enqueue_style( 'lightbox', $plugin_uri . 'assets/lightbox2/css/lightbox.min.css', [], '2.11.4' );
			wp_enqueue_script( 'lightbox', $plugin_uri . 'assets/lightbox2/js/lightbox.min.js', [ 'jquery' ], '2.11.4', true );
			add_filter( 'gallery_style', array( $this, 'gallery_style_filter' ), 11, 1 );
			add_filter( 'wp_get_attachment_link_attributes', array( $this, 'attachment_link_attributes_filter' ), 11, 2 );
			return 1;
		else :
			return 0;
		endif;
	}


	/**
	 * Load ImageLoaded assets on the frontend
	 *
	 * @since 3.0.0
	 * @access private
	 *
	 * @param Integer $lightbox_used 1 if the Lightbox framework is used - just for the dependency
	 *
	 * @return Integer 1 if the framework is used or 0 if unused
	 */
	private function load_imagesloaded_framework( $lightbox_used = 0 ) {
		if ( defined( 'MMD_USE_IMAGESLOADED' ) && MMD_USE_IMAGESLOADED > 0 ) :
			$plugin_uri = mmd()->plugin_uri;
			wp_deregister_script( 'imagesloaded' );
			wp_deregister_script( 'jquery-imagesloaded' );
			wp_enqueue_script( 'imagesloaded', $plugin_uri . 'assets/imagesloaded/js/imagesloaded.pkgd.min.js', $lightbox_used > 0 ? [ 'lightbox' ] : [], '5.0.0', true );
			return 1;
		else :
			return 0;
		endif;
	}


	/**
	 * Load ImageLoaded assets on the frontend
	 *
	 * @since 3.0.0
	 * @access private
	 *
	 * @param Integer $lightbox_used 1 if the Lightbox framework is used - just for the dependency
	 * @param Integer $imagesloaded_used 1 if the ImagesLoaded framework is used - just for the dependency
	 *
	 * @return Integer 1 if the framework is used or 0 if unused
	 */
	private function load_masonry_framework( $lightbox_used = 0, $imagesloaded_used = 0 ) {
		if ( defined( 'MMD_USE_MASONRY' ) && MMD_USE_MASONRY > 0 ) :
			if ( is_singular() && get_post_format() === 'gallery' ) :
				$masonry_used = 1;
			elseif ( is_archive() || is_category() || is_tag() || is_tax() ) :
				$masonry_used = 1;
			else :
				return 0;
			endif;
			wp_deregister_script( 'masonry' );
			wp_deregister_script( 'jquery-masonry' );
			$plugin_uri = mmd()->plugin_uri;
			wp_enqueue_script( 'masonry', $plugin_uri . 'assets/masonry-layout/js/masonry.pkgd.min.js', $imagesloaded_used > 0 ? [ 'imagesloaded' ] : ( $lightbox_used > 0 ? [ 'lightbox' ] : [] ), '4.2.2', true );
			wp_add_inline_style( 'lightbox', '.lightbox-set { margin: 0 -8px } .grid-sizer, .grid-item { margin: 0 8px 16px 8px; width: calc(50% - 16px) } .grid-item a, .grid-item a img { display: block }' );
			if ( $imagesloaded_used > 0 ) :
				wp_add_inline_script( 'masonry', 'jQuery( document ).ready(function() { jQuery( \'.grid\' ).each(function() { var $grid = jQuery( this ); $grid.imagesLoaded().progress(function() { $grid.masonry( \'layout\' ); }); }); });' );
			endif;
			return $masonry_used;
		else :
			return 0;
		endif;
	}


	/**
	 * Trigger Masonry or lightbox assets on the frontend
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @return Void
	 */
	public function my_plugin_assets() {
		$config = mmd()->conf_blog_prefix . 'conf.php';
		if ( ! file_exists( $config ) ) :
			return FALSE;
		endif;
		require_once $config;
		# Register and enqueue lightbox
		$lightbox_used = $this->load_lighbox_framework();
		# Register and enqueue lightbox
		$imagesloaded_used = $this->load_imagesloaded_framework( $lightbox_used );
		# Register and enqueue masonry
		$this->load_masonry_framework( $lightbox_used, $imagesloaded_used );
	}


	/**
	 * Format the html so lightboxes or masonry layout can be used
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @return Void
	 */
	public function render_lightbox_masonry( $content = '' ) {
		$replacers = array(
			# Adjust lightbox for image sets with masonry
			# Old versions with no *figure* and *figcaption* tags
			[ "#<li><a href=\"(/wp-content/.*?\.(jpg|jpeg|gif|png))\" title=\"(myset[0-9_]+)\s(.*?)</li>#u", "<div class=\"grid-item\"><a data-lightbox=\"$3\" href=\"$1\" title=\"$4</div>" ],
			[ "#<ul>\n<div class=\"grid-item\"><a data-lightbox=\"(.*?)\" href=\"(.*?)\"#u", "<div id=\"$1\" class=\"grid lightbox-set\" data-masonry='{ \"itemSelector\": \".grid-item\", \"columnWidth\": \".grid-sizer\", \"percentPosition\": true }'>\n<div class=\"grid-sizer\"></div>\n<div class=\"grid-item\"><a data-lightbox=\"$1\" href=\"$2\"" ],
			# New version with *figure* and *figcaption*
			[ "#<li><figure([^>]+)><a href=\"(/wp-content/.*?\.(jpg|jpeg|gif|png))\" title=\"(myset[0-9_]+)\s(.*?)</li>#u", "<div class=\"grid-item\"><figure$1><a data-lightbox=\"$4\" href=\"$2\" title=\"$5</div>" ],
			[ "#<ul>\n<div class=\"grid-item\"><figure id\=\"(.*?)\"([^>]+)><a data-lightbox=\"(.*?)\" href=\"(.*?)\"#u", "<div id=\"$1\" class=\"grid lightbox-set\" data-masonry='{ \"itemSelector\": \".grid-item\", \"columnWidth\": \".grid-sizer\", \"percentPosition\": true }'>\n<div class=\"grid-sizer\"></div>\n<div class=\"grid-item\"><figure id=\"$1\"$2><a data-lightbox=\"$3\" href=\"$4\"" ],
			# Safety clean
			[ "#</div>\n</ul>#u", "</div>\n</div>" ],
			# Adjust lightbox for single images
			[ "#<a href=\"(/wp-content/.*?\.(jpg|jpeg|gif|png))\"#u", "<a href=\"$1\" data-lightbox=\"mygallery\"" ],
		);
		foreach( $replacers as $regexp ) :
			$content = preg_replace( $regexp[ 0 ], $regexp[ 1 ], $content );
		endforeach;
		return $content;
	}

	/**
	 * Format the html so gutenberg block styles can be applied
	 *
	 * @since 3.3.0
	 * @access public
	 *
	 * @return Void
	 */
	public function render_gutenberg_basics( $content ) {
		# <h2 class="has-text-align-center"> => <h2 class="wp-block-heading has-text-align-center">
		# <h2 id="peter" class="has-text-align-center"> => <h2 d="peter" class="wp-block-heading has-text-align-center">
		$content = preg_replace( "#<h(\d)(.*?)class=\"#u", "<h$1$2class=\"wp-block-heading ", $content );
		# <h2> => <h2 class="wp-block-heading">
		$content = preg_replace( "#<h(\d)>#u", "<h$1 class=\"wp-block-heading\">", $content );
		return $content;
	}


}
