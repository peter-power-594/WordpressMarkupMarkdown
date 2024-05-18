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
		$addon_options = mmd()->cache_dir . '/conf.php';
		if ( file_exists( $addon_options ) ) :
			require_once $addon_options;
		endif;
		$core_dir = mmd()->plugin_dir . '/MarkupMarkdown/Core/';
		# Load the conf.
		$active_addons = mmd()->cache_dir . '/conf_screen.php';
		if ( file_exists( $active_addons ) ) :
			# If not present, wait for the addons to be loaded !
			require_once $active_addons;
		endif;
		# Load core modules
		require_once $core_dir . 'Support.php';
		$mmd_cpt = new \MarkupMarkdown\Core\Support();
		require_once $core_dir . 'Addons.php';
		$mmd_addons = new \MarkupMarkdown\Core\Addons( $mmd_cpt );
		require_once $core_dir . 'Settings.php';
		$mmd_settings = new \MarkupMarkdown\Core\Settings( $mmd_addons );
		# Just in case
		$this->prepare_cache( $mmd_settings );
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
		if ( 'markup-markdown' === $domain && false !== strpos( $mofile, WP_LANG_DIR . '/plugins/' ) ) :
			$locale = apply_filters( 'plugin_locale', determine_locale(), $domain );
			$mofile = mmd()->plugin_dir . 'languages/' . $domain . '-' . $locale . '.mo';
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
				'<a href="https://www.buymeacoffee.com/peterpower594" target="_blank" rel="noopener noreferrer">♥ ' . esc_html__( 'Buy me a coffee', 'markup-markdown' ) . '</a>',
				'<a href="https://wordpress.org/support/plugin/markup-markdown/" target="_blank" rel="noopener noreferrer">♣ ' . esc_html__( 'Support', 'markup-markdown' ) . '</a>',
				'<a href="https://wordpress.org/support/plugin/markup-markdown/reviews/?filter=5" target="_blank" rel="noopener noreferrer">★ ' . esc_html__( 'Rate this plugin »', 'markup-markdown' ) . '</a>'
			)
		);
	}


	private function prepare_cache() {
		$cache_directory = mmd()->cache_dir;
		if ( ! is_dir( $cache_directory ) ) :
			mkdir( $cache_directory );
		else :
			return FALSE;
		endif;
		if ( ! file_exists( $cache_directory . '/index.php' ) ) :
			touch( $cache_directory . '/index.php' );
			file_put_contents( $cache_directory . '/index.php', '<?php /* Silence is gold */ ?>' );
		endif;
		$this->make_default_conf();
		return TRUE;
	}


	private function make_default_conf() {
		$conf_file = mmd()->cache_dir . '/conf.php';
		if ( file_exists( $conf_file ) ) :
			return TRUE;
		endif;
		touch( $conf_file );
		$params = mmd()->default_conf;
		$php_code = [ "<?php" ];
		$php_code[] = "\n\tdefined( 'ABSPATH' ) || exit;";
		foreach ( $params as $const => $val ) :
			$php_code[] = "\n\tdefine( '" . $const . "', " . ( is_integer( $val ) ? $val : (int)$val ) . " );";
		endforeach;
		$php_code[] = "\n?>";
		return file_put_contents( $conf_file, implode( '', $php_code ) ) > 0 ? TRUE : FALSE;
	}


	public function plugin_activate() {
		$this->prepare_cache();
	}


	public function plugin_patches( $upgrader_object, $options ) {
		if ( $options[ 'action' ] == 'update' && $options[ 'type' ] == 'plugin' ) :
			foreach( $options[ 'plugins' ] as $my_plugin ) :
				if ( $my_plugin === 'markup-markdown/markup-markdown.php' ) :
					$this->prepare_cache();
				endif;
			endforeach;
		endif;
	}

}

new \MarkupMarkdown\Core\Activation();
