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


	public function __construct() {
		$addon_conf = mmd()->cache_dir . '/conf_screen.php';
		if ( file_exists( $addon_conf ) ) :
			require_once $addon_conf;
		endif;
		$this->load_addons();
	}


	public function __get( $name ) {
		if ( array_key_exists( $name, $this->prop ) ) {
			return $this->prop[ $name ];
		}
		return null;
	}


	
	private function load_addons() {
		# Load addons modules
		$this->addon_dir = mmd()->plugin_dir . '/MarkupMarkdown/Addons/';

		$this->load_builder_easymde();
		$this->load_cache();
		$this->load_layout();
		$this->load_media_youtube();
		$this->load_media_vimeo();
		$this->load_media_image();
		$this->load_acf();
		$this->load_builder_summernote();
		$this->load_spellchecker();
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


	private function load_acf() {
		require_once $this->addon_dir  . 'Unsupported/AdvancedCustomField.php';
		$tmp_addon = new \MarkupMarkdown\Addons\Unsupported\AdvancedCustomField();
		$this->prop[ 'setup' ][] = $tmp_addon->slug;
		$this->prop[ 'inst' ][ $tmp_addon->slug ] = $tmp_addon;
		unset( $tmp_addon );
	}


	private function load_builder_summernote() {
		require_once $this->addon_dir  . 'Unsupported/EngineSummernote.php';
		$tmp_addon = new \MarkupMarkdown\Addons\Unsupported\EngineSummernote();
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


}
