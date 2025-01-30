<?php

namespace MarkupMarkdown\AutoPlugs;

defined( 'ABSPATH' ) || exit;


class WPGeshi {

	private $mmd_geshi_css_code = '';


	public function __construct() {
		if ( file_exists( WP_PLUGIN_DIR . '/wp-geshi-highlight/wp-geshi-highlight.php' ) ) :
			define( 'MMD_WPGESHI_PLUG', true );
			add_action( 'after_setup_theme', array( $this, 'mmd_wp_geshi_plug' ) );
		endif;
	}


	public function mmd_wp_geshi_plug() {
		# Just in case make sure one of the wp geshi core function is available
		if ( function_exists( 'wp_geshi_filter_replace_code' ) ) :
			global $wp_geshi_used_languages;
			$wp_geshi_used_languages = array();
			add_filter( 'addon_markdown2html', array( $this, 'trigger_wp_geshi' ), 11, 1 );
		endif;
		if ( function_exists( 'wp_geshi_add_css_to_head' ) ) :
			# We are gonna output the styles in the footer instead of the head
			add_action( 'wp_footer', array( $this, 'load_wp_geshi_stylesheets' ) );
		endif;
	}


	public function trigger_wp_geshi( $content ) {
		# Replace <pre><code class="language-php">...</code></pre> by <pre lang="php">...</pre>
		$pre_friendly = preg_replace(
			"#<pre><code class=\"lang-([a-z0-9]+)\">#",
			"<pre lang=\"$1\" escaped=\"true\" line=\"1\">",
			str_replace( '</code></pre>', '</pre>', $content )
		);
		# 1) Instead of filter_and_replace_code_snippets, we call directly filter_replace_code
		#    <pre lang="xxx">...</pre> will be replaced by a token <p>abc123</p>
		$ges_friendly = wp_geshi_filter_replace_code( $pre_friendly );
		# If code blocks are found, $wp_geshi_codesnipmatch_arrays will be defined
		global $wp_geshi_codesnipmatch_arrays;
		if ( ! isset( $wp_geshi_codesnipmatch_arrays ) || ! $wp_geshi_codesnipmatch_arrays || ! count( $wp_geshi_codesnipmatch_arrays ) ) :
			return $pre_friendly; # Otherwise nothing to do, just exiting
		endif;
		# 2) Prepare the styles
		#    As the filter might be called multiple times, the inline style might be overriden / lost.
		wp_geshi_highlight_and_generate_css();
		global $wp_geshi_css_code;
		if ( isset( $wp_geshi_css_code ) && ! empty( $wp_geshi_css_code ) ) :
			$this->mmd_geshi_css_code .= $wp_geshi_css_code;
		endif;
		# 3) Parse and replace the token with the appropriate colored snippets
		$html_friendly = wp_geshi_insert_highlighted_code_filter( $ges_friendly );
		return $html_friendly;
	}


	public function load_wp_geshi_stylesheets() {
		if ( ! is_singular() || empty( $this->mmd_geshi_css_code ) ) :
			return false;
		endif;
		# Override the global inline style variable. Solved the undefined error
		global $wp_geshi_css_code;
		$wp_geshi_css_code = "";
		if ( ! defined( 'WP_MMD_OPCACHE' ) || ! WP_MMD_OPCACHE || ! function_exists( 'ob_start' ) ) :
			# OP Cache is disabled or output buffering not available, just trigger the styles generator
			wp_geshi_add_css_to_head();
			wp_add_inline_style( 'wpgeshi-wp-geshi-highlight', $this->mmd_geshi_css_code );
		else :
			# OP Cache is enabled and required PHP module exists. We compile the css inside and push it to the static cache file
			$geshi_stylesheet = '';
			ob_start();
			wp_geshi_add_css_to_head();
			# There is only 1 global stylesheet at the moment but we use the global variable just in case
			# At least any stylesheet with the 'wpgeshi' stylesheet will be grabbed
			global $wp_styles;
			if ( isset( $wp_styles ) && isset( $wp_styles->queue ) ) :
				foreach( $wp_styles->queue as $handle ) :
					if ( strpos( $handle, 'wpgeshi' ) !== FALSE ) :
						$geshi_stylesheet = '<link rel="stylesheet" id="' . $handle . '-css" href="' . $wp_styles->registered[ $handle ]->src . '" media="all" />';
						echo "\n" . $geshi_stylesheet;
					endif;
				endforeach;
			endif;
			echo '<style id="wpgeshi-wp-geshi-highlight-inline-css" type="text/css">' . $this->mmd_geshi_css_code . '</style>';
			$post_geshi_css = ob_get_clean();
			$post_content = mmd()->cache_blog_prefix . get_the_id() . '.html';
			if ( file_exists( $post_content ) ) :
				file_put_contents( $post_content, $post_geshi_css, FILE_APPEND );
			endif;
			# For the first run we still need to ouput the content !
			echo $post_geshi_css;
		endif;
		return TRUE;
	}


}

new \MarkupMarkdown\AutoPlugs\WPGeshi();
