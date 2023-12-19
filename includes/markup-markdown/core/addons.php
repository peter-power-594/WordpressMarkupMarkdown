<?php

namespace MarkupMarkdown;

defined( 'ABSPATH' ) || exit;
defined( 'MMD_PLUGIN_ACTIVATED' ) || exit;


class PluginAddons {


	private $prop = array(
		'setup' => array(),
		'inst' => array()
	);


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


	public function load_addons() {
		# Load addons modules
		$addon_dir = mmd()->plugin_dir . '/includes/markup-markdown/addons/';

		require_once $addon_dir  . 'released/engine_easymde.php';
		$tmp_addon1 = new EngineEasyMDE();
		$this->prop[ 'inst' ][ $tmp_addon1->slug ] = $tmp_addon1;
		unset( $tmp_addon1 );

		require_once $addon_dir  . 'released/opcache.php';
		$tmp_addon3 = new OPCacheAddon();
		$this->prop[ 'setup' ][] = $tmp_addon3->slug;
		$this->prop[ 'inst' ][ $tmp_addon3->slug ] = $tmp_addon3;
		unset( $tmp_addon3 );

		require_once $addon_dir  . 'released/layout.php';
		$tmp_addon4 = new LayoutAddon();
		$this->prop[ 'setup' ][] = $tmp_addon4->slug;
		$this->prop[ 'inst' ][ $tmp_addon4->slug ] = $tmp_addon4;
		unset( $tmp_addon4 );

		require_once $addon_dir  . 'released/media/youtube.php';
		$tmp_addon5 = new MediaYoutubeAddon();
		$this->prop[ 'setup' ][] = $tmp_addon5->slug;
		$this->prop[ 'inst' ][ $tmp_addon5->slug ] = $tmp_addon5;
		unset( $tmp_addon5 );

		require_once $addon_dir  . 'released/media/vimeo.php';
		$tmp_addon6 = new MediaVimeoAddon();
		$this->prop[ 'setup' ][] = $tmp_addon6->slug;
		$this->prop[ 'inst' ][ $tmp_addon6->slug ] = $tmp_addon6;
		unset( $tmp_addon6 );

		require_once $addon_dir  . 'released/media/image.php';
		$tmp_addon7 = new ImageAddon();
		$this->prop[ 'setup' ][] = $tmp_addon7->slug;
		$this->prop[ 'inst' ][ $tmp_addon7->slug ] = $tmp_addon7;
		unset( $tmp_addon7 );

		require_once $addon_dir  . 'unsupported/acf.php';
		$tmp_addon8 = new ACFAddon();
		$this->prop[ 'setup' ][] = $tmp_addon8->slug;
		$this->prop[ 'inst' ][ $tmp_addon8->slug ] = $tmp_addon8;
		unset( $tmp_addon8 );

		require_once $addon_dir  . 'unsupported/engine_summernote.php';
		$tmp_addon2 = new EngineSummerNote();
		$this->prop[ 'setup' ][] = $tmp_addon2->slug;
		$this->prop[ 'inst' ][ $tmp_addon2->slug ] = $tmp_addon2;
		unset( $tmp_addon2 );

		require_once $addon_dir  . 'unsupported/spellchecker.php';
		$tmp_addon9 = new SpellCheckerAddon();
		$this->prop[ 'setup' ][] = $tmp_addon9->slug;
		$this->prop[ 'inst' ][ $tmp_addon9->slug ] = $tmp_addon9;
		unset( $tmp_addon9 );

	}


}
