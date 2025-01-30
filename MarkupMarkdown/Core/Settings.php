<?php

namespace MarkupMarkdown\Core;

defined( 'ABSPATH' ) || exit;
defined( 'MMD_PLUGIN_ACTIVATED' ) || exit;


class Settings {

	/**
	 * Used when the config file has change
	 *
	 * @var boolean Status if the update, TRUE in case of success or FALSE
	 */
	public $updated = -1;


	/**
	 * Initialized addons
	 *
	 * @var Array $addons The addons properties
	 */
	public $addons = [];


	public function __construct( $addons ) {
		$this->addons = $addons;
		if ( ! is_admin() ) :
			# Don't do anything
			return TRUE;
		endif;
		# Menu
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		# Options Edit Screen
		$my_page = filter_input( INPUT_GET, 'page', FILTER_SANITIZE_SPECIAL_CHARS );
		if ( isset( $my_page ) && $my_page === 'markup-markdown-admin' ) :
			# Add Help and plugins toggler. Thi action should be run *after* admin_menu
			add_action( 'load-settings_page_markup-markdown-admin', array( $this, 'mmd_setup_tools' ), 10 );
			# Check if the setting form was submitted
			$this->update_config();
			# Load assets
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_setting_scripts' ) );
		endif;
	}


	/**
	 * Make the configuration file
	 *
	 * @since 1.7.2
	 * @access private
	 *
	 * @param array $params data as key => val used later as constants
	 * @param boolean $new to check whether the file already exists
	 *
	 * @returns boolean TRUE if the file already exists or was updated
	 */
	private function make_conf( $params = [], $new = FALSE ) {
		$conf_file = mmd()->conf_blog_prefix . 'conf.php';
		if ( $new && file_exists( $conf_file ) ) :
			return FALSE;
		endif;
		touch( $conf_file );
		if ( ! isset( $params ) || ! is_array( $params ) ) :
			$params = mmd()->default_conf;
		endif;
		$php_code = array( "<?php", "\n\tdefined( 'ABSPATH' ) || exit;" );
		foreach ( $params as $const => $val ) :
			if ( is_integer( $val ) ) :
				$php_code[] = "\n\tdefine( '" . $const . "', " . (int)$val . " );";
			elseif ( is_array( $val ) ) :
				$php_code[] = "\n\tdefine( '" . $const . "', [ \"" . implode( "\", \"", $val ) . "\" ] );";
			else :
				$php_code[] = "\n\tdefine( '" . $const . "', " . htmlspecialchars( $val ) . " );";
			endif;
		endforeach;
		$php_code[] = "\n?>";
		$this->updated = file_put_contents( $conf_file, implode( '', $php_code ) ) > 0 ? 1 : 0;
		mmd()->clear_cache( $conf_file );
	}


	public function update_config() {
		# Show the success or error message
		$options_saved = filter_input( INPUT_GET, 'options_saved', FILTER_VALIDATE_INT );
		if ( isset( $options_saved ) && ! empty( $options_saved ) ) :
			if ( $options_saved === 1 ) :
				# New settings were saved
				add_action( 'admin_notices', function() {
					echo '<div class="updated notice notice-success"><p>' . __( 'Success!' ) . '</p></div>';
				});
			elseif ( $options_saved === 0 ) :
				# Error while overriding the file
				add_action( 'admin_notices', function() {
					echo '<div class="updated notice notice-error"><p>' . __( 'Error while saving the changes.' ) . '</p></div>';
				});
			endif;
		endif;
		# Update conf is the settings form was submitted
		$my_nonce = filter_input( INPUT_POST, '_wpnonce', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		if ( ! $my_nonce || ( function_exists( 'wp_verify_nonce' ) && ! \wp_verify_nonce( $my_nonce, 'update-mmd_settings' ) ) ) :
			return FALSE;
		endif;
		$my_cnf = apply_filters( 'mmd_verified_config', array() );
		$my_cst = apply_filters( 'mmd_var2const', $my_cnf );
		$this->make_conf( $my_cst );
	}


	/**
	 * The options page
	 *
	 * @since 1.7.2
	 * @access public
	 *
	 * @return Void
	 */
	public function options_page() {
		if ( ! current_user_can( 'manage_options' ) ) :
			return '';
		endif;
		do_action( 'mmd_before_options' );
?>
		<div id="wrap">
			<h1>Markup Markdown <sup><?php echo mmd()->version; ?></sup> : <?php esc_html_e( 'Settings' ); ?></h1>
			<p><?php printf( esc_html__( 'Most of the following settings are related to addons. You can globally enable or disable addons from the %1$s screen options %2$s panel.', 'markup-markdown' ), '<a href="#show-settings-link" class="toggler">', '</a>' ); ?></p>
			<form method="post">
				<div id="tabs">
					<ul>
<?php do_action( 'mmd_tabmenu_options' ); ?>
					</ul>
<?php do_action( 'mmd_tabcontent_options' ); ?>
				</div><!-- #tabs -->
				<p class="submit">
					<input type="hidden" name="action" value="update">
					<?php wp_nonce_field( 'update-mmd_settings' ); ?>
					<input type="submit" name="submit" id="submit" class="button button-primary" value="<?php echo __( 'Update' ); ?>">
				</p>
			</form>
		</div><!-- .wrap -->
<?php
		do_action( 'mmd_after_options' );
	}


	/**
	 * Trigger when the menu item was added
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @returns void
	 */
	private function setup_options_completed() {
		if ( $this->updated > -1 ) :
			# Redirect the screen options page to avoid cache issues
			# when the config file has been updated
			$redirect_url = \menu_page_url( 'markup-markdown-admin', false )
				. '&options_saved=' . ( $this->updated > 0 ? '1' : '0' );
			\wp_redirect( $redirect_url, 302 );
			exit;
		endif;
	}


	/**
	 * Add the options menu in the admin area
	 *
	 * @since 1.7.2
	 * @access public
	 *
	 * @return Void
	 */
	public function add_admin_menu() {
		add_options_page( 'Markup Markdown', 'Markup Markdown', 'manage_options', 'markup-markdown-admin', array( $this, 'options_page' ) );
		$this->setup_options_completed();
	}


	public function mmd_setup_tools() {
		$update = $this->mmd_update_screen_options( filter_input( INPUT_POST, 'screen-options-apply', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) );
		add_filter( 'screen_options_show_screen', array( $this, 'mmd_screen_options_show_screen' ), 10, 2 );
		add_filter( 'screen_options_show_submit', array( $this, 'mmd_screen_options_show_submit' ), 10, 2 );
		add_filter( 'screen_settings', array( $this, 'mmd_screen_settings' ), 10 , 2 );
	}


	/**
	 * If the MMD screen options area has submitted, update the related conf
	 *
	 * @since 2.1.2
	 * @access private
	 *
	 * @param String $submit_button The value of the submit button
	 * @return Void
	 */
	private function mmd_update_screen_options( $submit_button = '' ) {
		if ( ! $submit_button || empty( $submit_button ) || $submit_button !== __( 'Apply' ) ) :
			return FALSE;
		endif;
		if ( ! check_admin_referer( 'screen-options-nonce', 'screenoptionnonce' ) ) :
			return FALSE;
		endif;
		$my_addons = filter_input( INPUT_POST, 'mmd_addons', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
		$my_cnf_screen = mmd()->conf_blog_prefix . 'conf_screen.php';
		if ( ! file_exists( $my_cnf_screen ) ) :
			touch( $my_cnf_screen );
		endif;
		$php_code = [ "<?php" ];
		$php_code[] = "\n\t" . 'defined( \'ABSPATH\' ) || exit;';
		$php_code[] = "\n\t" . 'define( \'MMD_ADDONS\', [';
		if ( isset( $my_addons ) && is_array( $my_addons ) ) :
			foreach( $my_addons as $addon ) :
				$php_code[] = "\n\t\t\"" . htmlspecialchars( $addon ) . '",';
			endforeach;
			$php_code[] = "\n\t\t" . '"eof"';
		endif;
		$php_code[] = "\n\t" . ']);';
		$php_code[] = "\n\t" . 'if ( ! defined( \'WP_MMD_OPCACHE\' ) ) :'
			. "\n\t\t" . 'define( \'WP_MMD_OPCACHE\', '
				.  ( in_array( 'nopcache', $my_addons ) ? 'false' : 'true' )
			. ' );'
			. "\n\t" . 'endif;';
		if ( file_put_contents( $my_cnf_screen, implode( '', $php_code ) ) ) :
			mmd()->clear_cache( $my_cnf_screen );
			$redirect_url = \menu_page_url( 'markup-markdown-admin', false )
				. '&options_saved=' . ( $this->updated > 0 ? '1' : '0' );
				\wp_redirect( $redirect_url, 302 );
			exit;
		endif;
		return FALSE;
	}


	/**
	 * Force to display the accordion with page screen options area on the top right of the MMD Settings page
	 *
	 * @since 2.1.2
	 * @access public
	 *
	 * @param Boolean $show_screen The current display panel setting of the screen.
	 * @param \WP_Screen $screen The current screen settings objet.
	 * @return Boolean TRUE in case the panel should be shown or FALSE.
	 */
	public function mmd_screen_options_show_screen( $show_screen, $screen ) {
		if ( is_object( $screen ) && isset( $screen->id ) && $screen->id === 'settings_page_markup-markdown-admin' ) :
			return TRUE;
		endif;
		return $show_screen;
	}


	/**
	 * Force to display the submit button inside the screen options area on the top right of the MMD Settings page
	 *
	 * @since 2.1.2
	 * @access public
	 *
	 * @param Boolean $show_screen The current submit display setting of the screen.
	 * @param \WP_Screen $screen The current screen settings objet.
	 * @return Boolean TRUE in case the panel should be shown or FALSE.
	 */
	public function mmd_screen_options_show_submit( $show_submit, $screen ) {
		if ( is_object( $screen ) && isset( $screen->id ) && $screen->id === 'settings_page_markup-markdown-admin' ) :
			return TRUE;
		endif;
		return $show_submit;
	}


	/**
	 * Custom HTML inside the screen options panel
	 *
	 * @since 2.1.2
	 * @access public
	 *
	 * @param String $panel The html code for the current panel
	 * @param \WP_Screen $screen The current screen settings objet.
	 * @return String The modified html code for the current panel
	 */
	public function mmd_screen_settings( $panel, $screen ) {
		if ( ! is_object( $screen ) || ( isset( $screen->id ) && $screen->id !== 'settings_page_markup-markdown-admin' ) ) :
			return $panel;
		endif;
		$conf_screen = mmd()->conf_blog_prefix . 'conf_screen.php';
		if ( file_exists( $conf_screen ) ) :
			require_once $conf_screen;
		endif;
		$html = '<fieldset class="metabox-prefs">';
		$html .= '<legend>' . __( 'Addons used', 'markup-markdown' ) . '</legend>';
		$html .= '<style>.dashicons-mmd-helpers{margin:5px 0 0 5px}.mmd-addon-helper{display:inline}</style>';
		$html .= '<p>' . __( 'You can manually activate or desactivate specific addons.', 'markup-markdown' ) . ' ' . __( 'Addons marked with <sup>*</sup> should be used with caution.', 'markup-markdown' ) . '</p>';
		$html .= '<ul>';
		foreach ( $this->addons->setup as $slug ) :
			if ( ! $this->addons->inst[ $slug ] ) :
				continue;
			endif;
			$addon_inst = $this->addons->inst[ $slug ];
			$html .= '<li class="mmd-addon-helper"><label for="mmd_addon-' . $slug . '">';
			$html .= '<input class="enable-' . $slug . '-addon" name="mmd_addons[]" id="mmd_addon-' . $slug . '" type="checkbox" value="' . $slug . '"'
				. ( ( ( ! defined( 'MMD_ADDONS' ) && $addon_inst->active > 0 ) || ( defined( 'MMD_ADDONS' ) && in_array( $slug, MMD_ADDONS ) ) ) ? ' checked="checked"' : '' ) . ' /> ';
			$html .= $addon_inst->label . ( $addon_inst->release !== 'stable' ? ' <sup>*</sup>' : '' );
			$html .= '<span class="dashicons dashicons-editor-help dashicons-mmd-helpers" title="' . htmlspecialchars( $addon_inst->desc ) . '"></span>';
			$html .= '</label></li>';
		endforeach;
		$html .= '</ul>';
		$html .= '</fieldset>';
		return $panel . $html;
	}


	/**
	 * The options page assets
	 *
	 * @since 1.9.1
	 * @access public
	 *
	 * @return Void
	 */
	public function enqueue_setting_scripts() {
		$plugin_uri = mmd()->plugin_uri;
		wp_enqueue_style( 'markup_markdown-options', $plugin_uri . '/assets/markup-markdown/css/plugin_options.min.css', [], '1.0.6' );
		wp_enqueue_style( 'markup_markdown-easymde_editor',  $plugin_uri . 'assets/easy-markdown-editor/dist/easymde.min.css', [], '2.19.102' );
		wp_enqueue_style( 'markup_markdown-font_awesome_regular', 'https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.15.4/css/regular.min.css', [ 'markup_markdown-easymde_editor' ], '5.15.14' );
		wp_enqueue_style( 'markup_markdown-font_awesome_solid', 'https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.15.4/css/solid.min.css', [ 'markup_markdown-font_awesome_regular' ], '5.15.14' );
		wp_enqueue_style( 'markup_markdown-font_awesome_icons', 'https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.15.4/css/fontawesome.min.css', [ 'markup_markdown-font_awesome_solid' ], '5.15.14' );
		foreach ( [ 'core', 'tabs', 'draggable', 'droppable', 'sortable', 'button' ] as $jq_component ) :
			wp_enqueue_script( 'jquery-ui-' . $jq_component );
		endforeach;
		wp_enqueue_script( 'markup_markdown-options', $plugin_uri . '/assets/markup-markdown/js/plugin_options.js', [ 'jquery-ui-tabs' ], '1.0.6', true );
	}


}
