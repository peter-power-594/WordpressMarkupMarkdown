<?php

namespace MarkupMarkdown;

defined( 'ABSPATH' ) || exit;


class LayoutAddon {


	private $prop = array(
		'slug' => 'layout',
		'label' => 'Layout',
		'desc' => 'A few tools to help you enhancing your layout. (Lightbox, Masonry, etc...)',
		'release' => 'stable',
		'active' => 1
	);


	private $gal = 0;


	public function __construct() {
		mmd()->default_conf = array( 'MMD_USE_LIGHTBOX' => 1 );
		mmd()->default_conf = array( 'MMD_USE_IMAGESLOADED' => 1 );
		mmd()->default_conf = array( 'MMD_USE_MASONRY' => 1 );
		if ( defined( 'MMD_ADDONS' ) && in_array( $this->prop[ 'slug' ], MMD_ADDONS ) === FALSE ) :
			$this->prop[ 'active' ] = 0;
			return FALSE; # Addon has been desactivated
		endif;
		if ( is_admin() ) :
			add_filter( 'mmd_verified_config', array( $this, 'update_config' ) );
			add_filter( 'mmd_var2const', array( $this, 'create_const' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'load_layout_assets' ) );
		else :
			add_filter( 'addon_markdown2html', array( $this, 'render_lightbox_masonry' ) );
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
		$my_cnf[ 'lightbox' ] = filter_input( INPUT_POST, 'mmd_lightbox', FILTER_VALIDATE_INT );
		$my_cnf[ 'imagesloaded' ] = filter_input( INPUT_POST, 'mmd_imagesloaded', FILTER_VALIDATE_INT );
		$my_cnf[ 'masonry' ] = filter_input( INPUT_POST, 'mmd_masonry', FILTER_VALIDATE_INT );
		return $my_cnf;
	}
	public function create_const( $my_cnf ) {
		$my_cnf[ 'MMD_USE_LIGHTBOX' ] = isset( $my_cnf[ 'lightbox' ] ) ? $my_cnf[ 'lightbox' ] : 0;
		unset( $my_cnf[ 'lightbox' ] );
		$my_cnf[ 'MMD_USE_IMAGESLOADED' ] = isset( $my_cnf[ 'imagesloaded' ] ) ? $my_cnf[ 'imagesloaded' ] : 0;
		unset( $my_cnf[ 'imagesloaded' ] );
		$my_cnf[ 'MMD_USE_MASONRY' ] = isset( $my_cnf[ 'masonry' ] ) ? $my_cnf[ 'masonry' ] : 0;
		unset( $my_cnf[ 'masonry' ] );
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
		echo "\t\t\t\t\t\t<li><a href=\"#tab-layout\">Layout</a></li>\n";
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
		$conf_file = mmd()->cache_dir . '/conf.php';
		if ( file_exists( $conf_file ) ) :
			require_once $conf_file;
		endif;
		$my_cnf = array(
			'lightbox' => defined( 'MMD_USE_LIGHTBOX' ) ? MMD_USE_LIGHTBOX : 1,
			'masonry' => defined( 'MMD_USE_MASONRY' ) ? MMD_USE_MASONRY : 1,
			'imagesloaded' => defined( 'MMD_USE_IMAGESLOADED' ) ? MMD_USE_IMAGESLOADED : 1
		);
?>
					<div id="tab-layout">
						<h2>Layout</h2>
						<p>Here are a few settings you can change to modify the behavior of your blog posts.</p>
						<table class="form-table" role="presentation">
							<tbody>
								<tr class="site-use-lightbox">
									<th scope="row">
										Use Lightbox
									</th>
									<td>
										<label for="mmd_lightbox">
											<input type="checkbox" name="mmd_lightbox" id="mmd_lightbox" value="1" <?php echo $my_cnf[ 'lightbox' ] > 0 ? 'checked="checked"' : ''; ?> />
											An image inside a <em>post</em> or <em>page</em> that was linked to its original size will open in a modal (overlay on the same page) instead of a new window / tab.
										</label>
									</td>
								</tr>
								<tr class="site-use-masonry">
									<th scope="row">
										Use Masonry
									</th>
									<td>
										<label for="mmd_masonry">
											<input type="checkbox" name="mmd_masonry" id="mmd_masonry" value="1" <?php echo $my_cnf[ 'masonry' ] > 0 ? 'checked="checked"' : '';; ?>>
											Transform a bullet list of images as a 2 waterfall column layout when the <em>photo gallery</em> post format is selected.
										</label>
									</td>
									<tr class="site-use-imagesloaded">
										<th scope="row">
											Use Imagesloaded
										</th>
										<td>
											<label for="mmd_imagesloaded">
												<input type="checkbox" name="mmd_imagesloaded" id="mmd_imagesloaded" value="1" <?php echo $my_cnf[ 'imagesloaded' ] > 0 ? 'checked="checked"' : '';; ?> />
												Trigger the update of the layout after all images are loaded. Can solve specific issues in case the layout is broken with the gallery.
											</label>
										</td>
									</tr>
								</tr>
							</tbody>
						</table>
					</div><!-- #tab-layout -->
<?php
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
			$attributes[ 'data-lightbox' ] = 'gallery' . $this->gal;
		endif;
		return $attributes;
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
		$config = mmd()->cache_dir . '/conf.php';
		if ( ! file_exists( $config ) ) :
			return FALSE;
		endif;
		$plugin_uri = mmd()->plugin_uri;
		require_once $config;
		# Register and enqueue lightbox
		$use_lightbox = 0;
		if ( defined( 'MMD_USE_LIGHTBOX' ) && MMD_USE_LIGHTBOX > 0 ) :
			$use_lightbox = 1;
			wp_deregister_script( 'lightbox' );
			wp_deregister_script( 'jquery-lightbox' );
			wp_enqueue_style( 'lightbox', $plugin_uri . 'assets/lightbox2/css/lightbox.min.css', [], '2.11.4' );
			wp_enqueue_script( 'lightbox', $plugin_uri . 'assets/lightbox2/js/lightbox.min.js', [ 'jquery' ], '2.11.4', true );
			add_filter( 'gallery_style', array( $this, 'gallery_style_filter' ), 11, 1 );
			add_filter( 'wp_get_attachment_link_attributes', array( $this, 'attachment_link_attributes_filter' ), 11, 2 );
		endif;
		# Register and enqueue lightbox
		$use_imagesloaded = 0;
		if ( defined( 'MMD_USE_IMAGESLOADED' ) && MMD_USE_IMAGESLOADED > 0 ) :
			$use_imagesloaded = 1;
			wp_deregister_script( 'imagesloaded' );
			wp_deregister_script( 'jquery-imagesloaded' );
			wp_enqueue_script( 'imagesloaded', $plugin_uri . 'assets/imagesloaded/js/imagesloaded.pkgd.min.js', $use_lightbox > 0 ? [ 'lightbox' ] : [], '5.0.0', true );
		endif;
		# Register and enqueue masonry
		$use_masonry = 0;
		if ( defined( 'MMD_USE_MASONRY' ) && MMD_USE_MASONRY > 0 ) :
			wp_deregister_script( 'masonry' );
			wp_deregister_script( 'jquery-masonry' );
			if ( is_singular() && get_post_format() === 'gallery' ) :
				$use_masonry = 1;
			endif;
			if ( is_archive() || is_category() || is_tag() || is_tax() ) :
				$use_masonry = 1;
			endif;
			if ( ! $use_masonry ) :
				return TRUE;
			endif;
			wp_enqueue_script( 'masonry', $plugin_uri . 'assets/masonry-layout/js/masonry.pkgd.min.js', $use_imagesloaded > 0 ? [ 'imagesloaded' ] : ( $use_lightbox > 0 ? [ 'lightbox' ] : [] ), '4.2.2', true );
			wp_add_inline_style( 'lightbox', '.lightbox-set { margin: 0 -8px } .grid-sizer, .grid-item { margin: 0 8px 16px 8px; width: calc(50% - 16px) } .grid-item a, .grid-item a img { display: block }' );
			if ( $use_imagesloaded > 0 ) :
				wp_add_inline_script( 'masonry', 'jQuery( document ).ready(function() { jQuery( \'.grid\' ).each(function() { var $grid = jQuery( this ); $grid.imagesLoaded().progress(function() { $grid.masonry( \'layout\' ); }); }); });' );
			endif;
		endif;
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
		# Adjust lightbox for image sets with masonry
		# Old versions with no *figure* and *figcaption* tags
		$content = preg_replace(
			"#<li><a href=\"(/wp-content/.*?\.(jpg|jpeg|gif|png))\" title=\"(myset[0-9_]+)\s(.*?)</li>#u",
			"<div class=\"grid-item\"><a data-lightbox=\"$3\" href=\"$1\" title=\"$4</div>",
			$content
		);
		$content = preg_replace(
			"#<ul>\n<div class=\"grid-item\"><a data-lightbox=\"(.*?)\" href=\"(.*?)\"#u",
			"<div id=\"$1\" class=\"grid lightbox-set\" data-masonry='{ \"itemSelector\": \".grid-item\", \"columnWidth\": \".grid-sizer\", \"percentPosition\": true }'>\n<div class=\"grid-sizer\"></div>\n<div class=\"grid-item\"><a data-lightbox=\"$1\" href=\"$2\"",
			$content
		);
		# New version with *figure* and *figcaption*
		$content = preg_replace(
			"#<li><figure([^>]+)><a href=\"(/wp-content/.*?\.(jpg|jpeg|gif|png))\" title=\"(myset[0-9_]+)\s(.*?)</li>#u",
			"<div class=\"grid-item\"><figure$1><a data-lightbox=\"$4\" href=\"$2\" title=\"$5</div>",
			$content
		);
		$content = preg_replace(
			"#<ul>\n<div class=\"grid-item\"><figure([^>]+)><a data-lightbox=\"(.*?)\" href=\"(.*?)\"#u",
			"<div id=\"$1\" class=\"grid lightbox-set\" data-masonry='{ \"itemSelector\": \".grid-item\", \"columnWidth\": \".grid-sizer\", \"percentPosition\": true }'>\n<div class=\"grid-sizer\"></div>\n<div class=\"grid-item\"><figure$1><a data-lightbox=\"$2\" href=\"$3\"",
			$content
		);
		# Safety clean
		$content = preg_replace(
			"#</div>\n</ul>#u",
			"</div>\n</div>",
			$content
		);
		# Adjust lightbox for single images
		$content = preg_replace(
			"#<a href=\"(/wp-content/.*?\.(jpg|jpeg|gif|png))\"#u",
			"<a href=\"$1\" data-lightbox=\"mygallery\"",
			$content
		);
		return $content;
	}


}
