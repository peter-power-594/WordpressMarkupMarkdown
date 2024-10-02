<?php

namespace MarkupMarkdown\Addons\Released;

defined( 'ABSPATH' ) || exit;


class Latex {


	private $prop = array(
		'slug' => 'latex',
		'release' => 'stable',
		'active' => 0,
		'engine' => ''
	);


	public function __construct() {
		$this->prop[ 'label' ] = __( 'LaTeX', 'markup-markdown' );
		$this->prop[ 'desc' ] = __( 'Easily type and render math formulas inside your post.', 'markup-markdown' );
		if ( defined( 'MMD_ADDONS' ) && in_array( $this->prop[ 'slug' ], MMD_ADDONS ) === FALSE ) :
			$this->prop[ 'active' ] = 0;
			return false; # Addon has been desactivated
		endif;
		if ( is_admin() ) :
			add_filter( 'mmd_verified_config', array( $this, 'update_config' ) );
			add_filter( 'mmd_var2const', array( $this, 'create_const' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'load_layout_assets' ) );
		endif;
		if ( defined( 'MMD_USE_LATEX' ) && isset( MMD_USE_LATEX[ 0 ] ) && (int)MMD_USE_LATEX[ 0 ] === 1 ) :
			if ( isset( MMD_USE_LATEX[ 1 ] ) ) :
				$this->prop[ 'engine' ] = MMD_USE_LATEX[ 1 ];
				if ( is_admin() ) :
					add_action( 'mmd_load_engine_stylesheets', array( $this, 'load_latex_stylesheets' ) );
					add_action( 'mmd_load_engine_scripts', array( $this, 'load_admin_latex_scripts' ) );
				elseif ( isset( MMD_USE_LATEX[ 2 ] ) && (int)MMD_USE_LATEX[ 2 ] > 0 ) :
					add_action( 'mmd_load_engine_stylesheets', array( $this, 'load_latex_stylesheets' ) );
					add_action( 'mmd_load_engine_scripts', array( $this, 'load_front_latex_scripts' ) );
				endif;
			endif;
		endif;
	}


	public function __get( $name ) {
		if ( array_key_exists( $name, $this->prop ) ) {
			return $this->prop[ $name ];
		}
		return 'mmd_undefined';
	}

	/**
	 * Filter to parse Latex options from the options screen when the form was submitted
	 *
	 * @since 3.8.0
	 * @access public
	 *
	 * @return Void
	 */
	public function update_config( $my_cnf ) {
		$my_cnf[ 'latex_engine' ] = filter_input( INPUT_POST, 'mmd_uselatex', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		$my_cnf[ 'latex_active' ] = isset( $my_cnf[ 'latex_engine' ] ) && in_array( $my_cnf[ 'latex_engine' ], [ 'katex', 'mathml' ] ) ? 1 : 0;
		$my_cnf[ 'latex_front' ] = filter_input( INPUT_POST, 'mmd_latex_front', FILTER_VALIDATE_INT );
		$my_cnf[ 'latex_front_id' ] = filter_input( INPUT_POST, 'mmd_latex_front_id', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		return $my_cnf;
	}
	public function create_const( $my_cnf ) {
		$my_cnf[ 'MMD_USE_LATEX' ] = [
			isset( $my_cnf[ 'latex_active' ] ) && (int)$my_cnf[ 'latex_active' ] > 0  ? 1 : 0
		];
		unset( $my_cnf[ 'latex_active' ] );
		if ( $my_cnf[ 'MMD_USE_LATEX' ][ 0 ] > 0 ) :
			$my_cnf[ 'MMD_USE_LATEX' ][ 1 ] = isset( $my_cnf[ 'latex_engine' ] ) ? htmlspecialchars( $my_cnf[ 'latex_engine' ] ) : '';
			$my_cnf[ 'MMD_USE_LATEX' ][ 2 ] = isset( $my_cnf[ 'latex_front' ] ) ? (int)$my_cnf[ 'latex_front' ] : '';
			$my_cnf[ 'MMD_USE_LATEX' ][ 3 ] = isset( $my_cnf[ 'latex_front_id' ] ) ? htmlspecialchars( $my_cnf[ 'latex_front_id' ] ) : '';
		endif;
		unset( $my_cnf[ 'latex_engine' ] );
		unset( $my_cnf[ 'latex_front' ] );
		unset( $my_cnf[ 'latex_front_id' ] );
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
	 * @since 3.8.0
	 * @access public
	 *
	 * @return Void
	 */
	public function add_tabmenu() {
		echo "\t\t\t\t\t\t<li><a href=\"#tab-latex\">" . __( 'LaTeX', 'markup-markdown' ) . "</a></li>\n";
	}


	/**
	 * Display layout options inside the options screen
	 *
	 * @since 3.8.0
	 * @access public
	 *
	 * @return Void
	 */
	public function add_tabcontent() {
		$conf_file = mmd()->conf_blog_prefix . 'conf.php';
		if ( file_exists( $conf_file ) ) :
			require_once $conf_file;
		endif;
		$my_tmpl = mmd()->plugin_dir . '/MarkupMarkdown/Addons/Released/Templates/LaTeXForm.php';
		if ( file_exists( $my_tmpl ) ) :
			mmd()->clear_cache( $my_tmpl );
			$toolbar_conf = $this->toolbar_conf;
			include $my_tmpl;
		endif;
	}


	public function load_latex_stylesheets() {
		if ( ! isset( $this->prop[ 'engine' ] ) || empty( $this->prop[ 'engine' ] ) || $this->prop[ 'engine' ] === 'none' ) :
			# Do nothing
		elseif ( $this->prop[ 'engine' ] === 'katex' ) :
			wp_enqueue_style( 'markup_markdown__latex_katex', mmd()->plugin_uri . 'assets/katex/katex.min.css', [ 'markup_markdown__wordpress_richedit' ], '0.16.11' );
		endif;
	}


	public function load_admin_latex_scripts() {
		if ( ! isset( $this->prop[ 'engine' ] ) || empty( $this->prop[ 'engine' ] ) || $this->prop[ 'engine' ] === 'none' ) :
			# Do nothing
		elseif ( $this->prop[ 'engine' ] === 'katex' ) :
			wp_enqueue_script( 'markup_markdown__latex_katex', mmd()->plugin_uri . 'assets/katex/katex.min.js', [ 'markup_markdown__wordpress_richedit' ], '0.16.11', true );
		endif;
	}


	public function load_front_latex_scripts() {
		if ( ! isset( $this->prop[ 'engine' ] ) || empty( $this->prop[ 'engine' ] ) || $this->prop[ 'engine' ] === 'none' ) :
			# Do nothing
		elseif ( $this->prop[ 'engine' ] === 'katex' ) :
			wp_enqueue_script( 'markup_markdown__latex_katex', mmd()->plugin_uri . 'assets/katex/katex.min.js', [ 'markup_markdown__wordpress_richedit' ], '0.16.11', true );
			wp_enqueue_script( 'markup_markdown__latex_katex_render', mmd()->plugin_uri . 'assets/katex/contrib/auto-render.min.js', [ 'markup_markdown__latex_katex' ], '0.16.11', true );
			wp_add_inline_script( 'markup_markdown__latex_katex_render', $this->add_inline_katex_conf() );
		endif;
	}


	public function add_inline_katex_conf() {
		$js = 'document.addEventListener("DOMContentLoaded",function(){renderMathInElement(';
		if ( isset( MMD_USE_LATEX[ 3 ] ) ) :
			$js .= 'document.getElementById("' . MMD_USE_LATEX[ 3 ] . '")';
		else :
			$js .= 'document.body';
		endif;
		$js .= ',{delimiters:[{left:\'$$\',right:\'$$\',display:true},{left:\'\$\',right:\'\$\',display:false}],throwOnError:false});';
		$js .= '});';
		return $js;
	}


}
