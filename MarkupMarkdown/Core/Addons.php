<?php

namespace MarkupMarkdown\Core;

defined( 'ABSPATH' ) || exit;
defined( 'MMD_PLUGIN_ACTIVATED' ) || exit;


class Addons {


	private $prop = array(
		'setup' => array(),
		'inst' => array()
	);


	private $addon_dir = '';


	private $plugs = array(
		'BBPress' => [ 0, 'https://wordpress.org/plugins/bbpress/' ],
		'BuddyPress' => [ 0, 'https://wordpress.org/plugins/buddypress/' ],
		'BuddyPressDocs' => [ 0, 'https://wordpress.org/plugins/buddypress-docs/' ],
		'DisableEmojis' => [ 1, 'https://wordpress.org/plugins/disable-emojis/' ],
		'O2' => [ 0, 'https://github.com/Automattic/o2' ],
		'Woocommerce' => [ 0, 'https://wordpress.org/plugins/woocommerce/' ],
		'WPGeshi' => [ 0, 'https://wordpress.org/plugins/wp-geshi-highlight/' ]
	);


	public function __construct() {
		add_filter( 'mmd_autoplugs_enabled', array( $this, 'should_load_plugs' ), 10, 1 );
		$addon_conf = mmd()->conf_blog_prefix . 'conf_screen.php';
		if ( file_exists( $addon_conf ) ) :
			require_once $addon_conf;
		endif;
		if ( is_admin() ) :
			add_filter( 'mmd_verified_config', array( $this, 'update_config' ) );
			add_filter( 'mmd_var2const', array( $this, 'create_const' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'prepare_autoplugs_tab' ) );
		endif;
		$this->load_addons();
		$this->load_autoplugs( apply_filters( 'mmd_autoplugs_enabled', true ) );
	}


	public function __get( $name ) {
		if ( array_key_exists( $name, $this->prop ) ) {
			return $this->prop[ $name ];
		}
		return null;
	}


	/**
	 * Default filter to allow or deny the plugs
	 *
	 * @access public
	 * @since 3.3.0
	 *
	 * @param Boolean $bool TRUE in case the plugs are allowed or FALSE
	 *
	 * @return Boolean TRUE if required or FALSE
	 */
	public function should_load_plugs( $bool ) {
		if ( ! defined( 'WP_MMD_PLUGS' ) ) :
			return $bool;
		endif;
		return WP_MMD_PLUGS ? true : false;
	}


	private function load_addons() {
		# Load addons modules
		$this->addon_dir = mmd()->plugin_dir . '/MarkupMarkdown/Addons/';

		# Kind of stable addons for a daily use
		$this->load_builder_easymde();
		$this->load_cache();
		$this->load_layout();
		$this->load_media_youtube();
		$this->load_media_vimeo();
		$this->load_media_image();
		$this->load_latex();
		# Kind of usable addons but I wouldn't bet for extensive use
		$this->load_spellchecker();
		$this->load_acf();
		$this->load_acp();
	}


	private function load_builder_easymde() {
		require_once $this->addon_dir  . 'Released/EngineEasyMDE.php';
		$tmp_addon = new \MarkupMarkdown\Addons\Released\EngineEasyMDE();
		$this->prop[ 'inst' ][ $tmp_addon->slug ] = $tmp_addon;
		unset( $tmp_addon );
	}


	private function load_cache() {
		require_once $this->addon_dir  . 'Released/OPCache.php';
		$tmp_addon = new \MarkupMarkdown\Addons\Released\OPCache();
		$this->prop[ 'setup' ][] = $tmp_addon->slug;
		$this->prop[ 'inst' ][ $tmp_addon->slug ] = $tmp_addon;
		unset( $tmp_addon );
	}


	private function load_layout() {
		require_once $this->addon_dir  . 'Released/Layout.php';
		$tmp_addon = new \MarkupMarkdown\Addons\Released\Layout();
		$this->prop[ 'setup' ][] = $tmp_addon->slug;
		$this->prop[ 'inst' ][ $tmp_addon->slug ] = $tmp_addon;
		unset( $tmp_addon );
	}


	private function load_media_youtube() {
		require_once $this->addon_dir  . 'Released/Media/Youtube.php';
		$tmp_addon = new \MarkupMarkdown\Addons\Released\Media\Youtube();
		$this->prop[ 'setup' ][] = $tmp_addon->slug;
		$this->prop[ 'inst' ][ $tmp_addon->slug ] = $tmp_addon;
		unset( $tmp_addon );
	}


	private function load_media_vimeo() {
		require_once $this->addon_dir  . 'Released/Media/Vimeo.php';
		$tmp_addon = new \MarkupMarkdown\Addons\Released\Media\Vimeo();
		$this->prop[ 'setup' ][] = $tmp_addon->slug;
		$this->prop[ 'inst' ][ $tmp_addon->slug ] = $tmp_addon;
		unset( $tmp_addon );
	}


	private function load_media_image() {
		require_once $this->addon_dir  . 'Released/Media/Image.php';
		$tmp_addon = new \MarkupMarkdown\Addons\Released\Media\Image();
		$this->prop[ 'setup' ][] = $tmp_addon->slug;
		$this->prop[ 'inst' ][ $tmp_addon->slug ] = $tmp_addon;
		unset( $tmp_addon );
	}


	private function load_latex() {
		require_once $this->addon_dir  . 'Released/LaTeX.php';
		$tmp_addon = new \MarkupMarkdown\Addons\Released\Latex();
		$this->prop[ 'setup' ][] = $tmp_addon->slug;
		$this->prop[ 'inst' ][ $tmp_addon->slug ] = $tmp_addon;
		unset( $tmp_addon );
	}


	private function load_acf() {
		require_once $this->addon_dir  . 'Unsupported/AdvancedCustomField.php';
		$tmp_addon = new \MarkupMarkdown\Addons\Unsupported\AdvancedCustomField();
		$this->prop[ 'setup' ][] = $tmp_addon->slug;
		$this->prop[ 'inst' ][ $tmp_addon->slug ] = $tmp_addon;
		unset( $tmp_addon );
	}


	private function load_spellchecker() {
		require_once $this->addon_dir  . 'Unsupported/SpellChecker.php';
		$tmp_addon = new \MarkupMarkdown\Addons\Unsupported\SpellChecker();
		$this->prop[ 'setup' ][] = $tmp_addon->slug;
		$this->prop[ 'inst' ][ $tmp_addon->slug ] = $tmp_addon;
		unset( $tmp_addon );
	}


	private function load_acp() {
		require_once $this->addon_dir  . 'Unsupported/AdvancedCustomPost.php';
		$tmp_addon = new \MarkupMarkdown\Addons\Unsupported\AdvancedCustomPost();
		$this->prop[ 'setup' ][] = $tmp_addon->slug;
		$this->prop[ 'inst' ][ $tmp_addon->slug ] = $tmp_addon;
		unset( $tmp_addon );
	}


	/**
	 * Filter to parse the autoplugs options from the options screen when the form was submitted
	 *
	 * @since 3.10.0
	 * @access public
	 *
	 * @return Void
	 */
	public function update_config( $my_cnf ) {
		$fm_plugs = filter_input( INPUT_POST, 'mmd_plugs', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
		$my_plugs = array();
		foreach( $this->plugs as $my_slug => $my_plug ) :
			$my_plugs[ $my_slug ] = in_array( $my_slug, $fm_plugs ) ? 1 : 0;
		endforeach;
		$my_cnf[ 'plugs' ] = $my_plugs;
		return $my_cnf;
	}
	public function create_const( $my_cnf ) {
		if ( isset( $my_cnf[ 'plugs' ] ) ) :
			$this->sanitize_save_conf( $my_cnf[ 'plugs' ] );
			unset( $my_cnf[ 'plugs' ] );
		endif;
		return $my_cnf;
	}
	public function sanitize_save_conf( $my_cnf = [] ) {
		$cnf_file = mmd()->conf_blog_prefix . 'plugs.php';
		$data = "<?php\n\tdefined( 'ABSPATH' ) || exit;";
		$data .= "\n\tdefine( \"MMD_AUTOPLUGS\", ";
		$safe_cnf = json_encode( $my_cnf );
		if ( ! $safe_cnf ) :
			$data .= '[]';
		else:
			$data .= str_replace( [ '{', '}', ':' ], [ '[', ']', '=>' ], $safe_cnf );
		endif;
		$data .= ");\n?>";
		file_put_contents( $cnf_file, $data );
		mmd()->clear_cache( $cnf_file );
		return $cnf_file;
	}


	/**
	 * Add a few "plugs" with existing WP Plugins to make a smooth connection
	 *
	 * @access public
	 * @since 3.3.0
	 *
	 * @param Boolean $auto TRUE to load automatically the plugs or FALSE
	 *
	 * @return Boolean TRUE in case series of plugs should be loaded or FALSE
	 */
	public function load_autoplugs( $auto = true ) {
		if ( ! $auto ) :
			return false;
		endif;
		$conf_file = $this->check_plugs();
		require_once $conf_file;
		if ( ! defined( 'MMD_AUTOPLUGS' ) ) :
			return false;
		endif;
		foreach ( MMD_AUTOPLUGS as $slug => $active ) :
			if ( ! $active ) :
				continue;
			endif;
			$curr_plug = $this->addon_dir . 'AutoPlugs/' . $slug . '.php';
			if ( file_exists( $curr_plug ) ) :
				require_once $curr_plug;
			endif;
		endforeach;
		return true;
	}


	/**
	 * Check existing plugins for required plugs
	 *
	 * @access private
	 * @since 3.10.0
	 *
	 * @return String the autoplugs settings file
	 */
	private function check_plugs() {
		$plugs_conf_file = mmd()->conf_blog_prefix . 'plugs.php';
		if ( file_exists( $plugs_conf_file ) ) :
			return $plugs_conf_file;
		endif;
		$my_plug_cnf = array();
		foreach ( $this->plugs as $plug_slug => $plug_setting ) :
			$curr_plug = $this->addon_dir . 'AutoPlugs/' . $plug_slug . '.php';
			if ( ! file_exists( $curr_plug ) ) :
				$my_plug_cnf[ $plug_slug ] = 0;
				continue;
			endif;
			require_once $curr_plug;
			$my_const = 'MMD_' . strtoupper( $plug_slug ) . '_PLUG';
			if ( defined( $my_const ) ) :
				$my_plug_cnf[ $plug_slug ] = 1;
			else :
				$my_plug_cnf[ $plug_slug ] = isset( $plug_setting[ 0 ] ) && (int)$plug_setting[ 0 ] > 0 ? 1 : 0;
			endif;
		endforeach;
		return $this->sanitize_save_conf( $my_plug_cnf );
	}


	/**
	 * Trigger the actions to update the tabs on the settings screen
	 *
	 * @since 3.10.0
	 * @access public
	 *
	 * @return Void
	 */
	public function prepare_autoplugs_tab( $hook ) {
		if ( 'settings_page_markup-markdown-admin' === $hook ) :
			add_action( 'mmd_tabmenu_options', array( $this, 'add_tabmenu' ), 99, 1 );
			add_action( 'mmd_tabcontent_options', array( $this, 'add_tabcontent' ), 99, 1 );
		endif;
	}


	/**
	 * Add the autoplugs menu item inside the options screen
	 *
	 * @since 3.10.0
	 * @access public
	 *
	 * @return Void
	 */
	public function add_tabmenu() {
		echo "\t\t\t\t\t\t<li><a href=\"#tab-plugs\" class=\"mmd-ico ico-plug\">" . __( 'Autoplugs', 'markup-markdown' ) . "</a></li>\n";
	}


	/**
	 * Display autoplugs options inside the options screen
	 *
	 * @since 3.10.0
	 * @access public
	 *
	 * @return Void
	 */
	public function add_tabcontent() {
		$conf_file = mmd()->conf_blog_prefix . 'plugs.php';
		if ( file_exists( $conf_file ) ) :
			require_once $conf_file;
		endif;
		$my_tmpl = mmd()->plugin_dir . '/MarkupMarkdown/Addons/AutoPlugs/Templates/PlugsForm.php';
		if ( file_exists( $my_tmpl ) ) :
			$default_plugs = $this->plugs;
			mmd()->clear_cache( $my_tmpl );
			include $my_tmpl;
		endif;
	}

}
