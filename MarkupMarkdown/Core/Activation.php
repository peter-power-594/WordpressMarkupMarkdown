<?php

namespace MarkupMarkdown\Core;

defined( 'ABSPATH' ) || exit;
! defined( 'MMD_PLUGIN_ACTIVATED' ) || exit;


class Activation {


	public function __construct() {
		# Plugin Activation
		register_activation_hook( MMD_FILE_URL, array( $this, 'plugin_activate' ) );
		# Plugin Upgrade
		add_action( 'upgrader_process_complete', array( $this, 'plugin_patches' ), 10, 2 );
		# Translated Strings
		add_filter( 'load_textdomain_mofile', array( $this, 'plugin_textdomain' ), 10, 2 );
		# Plugin Properties
		add_filter( 'plugin_row_meta', array( $this, 'plugin_custom_metas' ), 10, 2 );
		# Add options and allow setup from the admin and edit screen
		define( 'MMD_PLUGIN_ACTIVATED', 1 );
		# Just in case
		$this->prepare_cache();
		# Get the otions
		$addon_options = mmd()->conf_blog_prefix . 'conf.php';
		if ( file_exists( $addon_options ) ) :
			require_once $addon_options;
		endif;
		$core_dir = mmd()->plugin_dir . '/MarkupMarkdown/Core/';
		# Load the conf.
		$active_addons = mmd()->conf_blog_prefix . 'conf_screen.php';
		if ( file_exists( $active_addons ) ) :
			# If not present, wait for the addons to be loaded !
			require_once $active_addons;
		endif;
		# Load core modules
		require_once $core_dir . 'Support.php';
		$mmd_cpt = new \MarkupMarkdown\Core\Support();
		require_once $core_dir . 'Addons.php';
		$mmd_addons = new \MarkupMarkdown\Core\Addons( $mmd_cpt );
		require_once $core_dir . 'AutoPlugs.php';
		$mmd_autoplugs = new \MarkupMarkdown\Core\AutoPlugs();
		do_action( 'mmd_addons_loaded' );
		require_once $core_dir . 'Settings.php';
		new \MarkupMarkdown\Core\Settings( $mmd_addons );
	}


	/**
	 * Load the local mo files inside the plugin folder
	 *
	 * @since  3.4.2
	 *
	 * @param String $mofile The file containing the translation string
	 * @param String $domain The plugin or asset related domain
	 * @return String $mofile The language specific translation file
	 */
	function plugin_textdomain( $mofile, $domain ) {
		if ( 'markup-markdown' === $domain ) :
			$locale = apply_filters( 'plugin_locale', determine_locale(), $domain );
			$new_mofile = mmd()->plugin_dir . 'languages/' . $domain . '-' . $locale . '.mo';
			if ( $new_mofile !== $mofile && file_exists( $new_mofile ) ) :
				return $new_mofile;
			endif;
		endif;
		return $mofile;
	}


	/**
	 * Meta links of the plugin
	 *
	 * @since  2.0.0
	 *
	 * @param   array  $input Existing links.
	 * @param   string $file  Current page.
	 * @return  array  $data  Modified links.
	 */
	public function plugin_custom_metas( $input, $file ) {
		if ( $file !== 'markup-markdown/markup-markdown.php' ) :
			return $input;
		endif;
		return array_merge(
			$input,
			array(
				'<a href="https://ko-fi.com/peterpower594" target="_blank" rel="noopener noreferrer">♥ ' . esc_html__( 'Buy me a coffee', 'markup-markdown' ) . '</a>',
				'<a href="https://wordpress.org/support/plugin/markup-markdown/" target="_blank" rel="noopener noreferrer">♣ ' . esc_html__( 'Support', 'markup-markdown' ) . '</a>',
				'<a href="https://wordpress.org/support/plugin/markup-markdown/reviews/?filter=5" target="_blank" rel="noopener noreferrer">★ ' . esc_html__( 'Rate this plugin »', 'markup-markdown' ) . '</a>'
			)
		);
	}


	private function prepare_cache() {
		$mmd_folders = array( mmd()->conf_dir, mmd()->cache_dir );
		foreach( $mmd_folders as $my_folder ) :
			if ( is_dir( $my_folder ) ) :
				continue;
			endif;
			mkdir( $my_folder );
			if ( ! file_exists( $my_folder . '/index.php' ) ) :
				touch( $my_folder . '/index.php' );
				file_put_contents( $my_folder . '/index.php', '<?php /* Silence is gold */ ?>' );
			endif;
		endforeach;
		return $this->make_default_conf( get_current_network_id(), get_current_blog_id() );
	}


	/**
	 * Migrate the settings file for network websites
	 *
	 * @since  3.5.0
	 *
	 * @param   string $ver  The current plugin version
	 * @return  Boolean true if data were migrated or false
	 */
	private function migrate_conf( $ver = '3.5.1' ) {
		if ( version_compare( $ver, '3.5.1', '>=' ) || ! is_dir( mmd()->conf_dir ) ) :
			return false;
		endif;
		$conf_files = array( 'conf.php', 'conf_screen.php', 'conf_easymde_toolbar.json' );
		$file_prefix = '1_1_';
		foreach( $conf_files as $my_conf_file ) :
			if ( file_exists( mmd()->cache_dir . '/' . $my_conf_file ) ):
				rename( mmd()->cache_dir . '/' . $my_conf_file, mmd()->conf_dir . '/' . $file_prefix . $my_conf_file );
			endif;
		endforeach;
		return true;
	}


	/**
	 * Create default configuration file
	 *
	 * @access private
	 * @sine 3.5.0
	 *
	 * @param Integer $curr_network_id The network ID when multisite is enabled. Wordpress default is 1.
	 * @param Integer $curr_blog_id The blog Id when multisite is enabled. Wordpress default is 1.
	 * @return Boolean true in case of success or false is an error occured
	 */
	private function make_default_conf( $curr_network_id = 1, $curr_blog_id = 1) {
		$conf_file = mmd()->conf_dir . '/' . $curr_network_id . '_' . $curr_blog_id . '_conf.php';
		if ( file_exists( $conf_file ) ) :
			return true;
		endif;
		if ( $curr_network_id === 1 && $curr_blog_id === 1 && $this->migrate_conf( mmd()->ver ) ) :
			return true;
		endif;
		touch( $conf_file );
		$params = mmd()->default_conf;
		$php_code = [ "<?php" ];
		$php_code[] = "\n\tdefined( 'ABSPATH' ) || exit;";
		foreach ( $params as $const => $val ) :
			$php_code[] = "\n\tdefine( '" . $const . "', " . ( is_integer( $val ) ? $val : (int)$val ) . " );";
		endforeach;
		$php_code[] = "\n?>";
		return file_put_contents( $conf_file, implode( '', $php_code ) ) > 0 ? true : false;
	}


	public function plugin_activate() {
		$this->prepare_cache();
	}


	public function plugin_patches( $upgrader_object, $options ) {
		if ( $options[ 'action' ] == 'update' && $options[ 'type' ] == 'plugin' ) :
			if ( isset( $options[ 'plugins' ] ) && is_array( $options[ 'plugins' ] ) ) :
				foreach( $options[ 'plugins' ] as $my_plugin ) :
					if ( 'markup-markdown/markup-markdown.php' === $my_plugin ) :
						$this->prepare_cache();
					endif;
				endforeach;
			endif;
		endif;
	}

}

new \MarkupMarkdown\Core\Activation();
