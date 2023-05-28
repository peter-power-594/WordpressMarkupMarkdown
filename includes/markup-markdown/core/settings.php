<?php

namespace MarkupMarkdown;

defined( 'ABSPATH' ) || exit;
defined( 'MMD_PLUGIN_ACTIVATED' ) || exit;


class PluginOptions {

	/**
	 * Used when the config file has change
	 * 
	 * @var boolean Status if the update, TRUE in case of success or FALSE
	 */
	public $updated = -1;


	public function __construct() {
		if ( ! is_admin() ) :
			# Don't do anything
			return TRUE;
		endif;
		# Menu
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		# Options Edit Screen
		$my_page = filter_input( INPUT_GET, 'page', FILTER_SANITIZE_SPECIAL_CHARS );
		if ( isset( $my_page ) && $my_page === 'markup-markdown-admin' ) :
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
		$conf_file = mmd()->cache_dir . '/conf.php';
		if ( $new && file_exists( $conf_file ) ) :
			return FALSE;
		endif;
		if ( ! file_exists( $conf_file ) ) :
			touch( $conf_file );
		endif;
		if ( ! isset( $params ) || ! is_array( $params ) ) :
			$params = mmd()->default_conf;
		endif;
		$php_code = [ "<?php" ];
		$php_code[] = "\n\tdefined( 'ABSPATH' ) || exit;";
		error_log( print_r( $params, true ));
		foreach ( $params as $const => $val ) :
			if ( is_integer( $val ) ) :
				$php_code[] = "\n\tdefine( '" . $const . "', " . (int)$val . " );";
			elseif ( is_array( $val ) ) :
				$php_code[] = "\n\tdefine( '" . $const . "', [ \"" . implode( "\", \"", $val ) . "\"] );";
			else :
				$php_code[] = "\n\tdefine( '" . $const . "', " . htmlspecialchars( $val ) . " );";
			endif;
		endforeach;
		$php_code[] = "\n?>";
		$this->updated = file_put_contents( $conf_file, implode( '', $php_code ) ) > 0 ? 1 : 0;
		if ( function_exists( 'opcache_invalidate' ) ) :
			opcache_invalidate( $conf_file );
		endif;
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
		<div class="wrap">
			<h1>Markup Markdown : <?php echo __( 'Settings' ); ?></h1>
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
		wp_enqueue_style( 'markup_markdown-options', $plugin_uri . '/assets/markup-markdown/css/plugin_options.css', [], '1.0.0' );
		wp_enqueue_script( 'jquery-ui-core' );
		wp_enqueue_script( 'jquery-ui-tabs' );
		wp_enqueue_script( 'markup_markdown-options', $plugin_uri . '/assets/markup-markdown/js/plugin_options.js', [ 'jquery-ui-tabs' ], '1.0.0', true );
	}


}


new \MarkupMarkdown\PluginOptions();