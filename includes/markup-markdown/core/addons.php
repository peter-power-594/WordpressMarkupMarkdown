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
		$my_addon = new EngineEasyMDE();
		# $this->prop[ 'setup' ][] = $my_addon->slug;
		$this->prop[ 'inst' ][ $my_addon->slug ] = $my_addon;
		unset( $my_addon );

		require_once $addon_dir  . 'released/engine_summernote.php';
		$my_addon = new EngineSummerNote();
		$this->prop[ 'setup' ][] = $my_addon->slug;
		$this->prop[ 'inst' ][ $my_addon->slug ] = $my_addon;
		unset( $my_addon );

		require_once $addon_dir  . 'released/layout.php';
		$my_addon = new LayoutAddon();
		$this->prop[ 'setup' ][] = $my_addon->slug;
		$this->prop[ 'inst' ][ $my_addon->slug ] = $my_addon;
		unset( $my_addon );

		require_once $addon_dir  . 'released/media/youtube.php';
		$my_addon = new MediaYoutubeAddon();
		$this->prop[ 'setup' ][] = $my_addon->slug;
		$this->prop[ 'inst' ][ $my_addon->slug ] = $my_addon;
		unset( $my_addon );

		require_once $addon_dir  . 'released/media/vimeo.php';
		$my_addon = new MediaVimeoAddon();
		$this->prop[ 'setup' ][] = $my_addon->slug;
		$this->prop[ 'inst' ][ $my_addon->slug ] = $my_addon;
		unset( $my_addon );

		require_once $addon_dir  . 'released/media/image.php';
		$my_addon = new ImageAddon();
		$this->prop[ 'setup' ][] = $my_addon->slug;
		$this->prop[ 'inst' ][ $my_addon->slug ] = $my_addon;
		unset( $my_addon );

		require_once $addon_dir  . 'unsupported/acf.php';
		$my_addon = new ACFAddon();
		$this->prop[ 'setup' ][] = $my_addon->slug;
		$this->prop[ 'inst' ][ $my_addon->slug ] = $my_addon;
		unset( $my_addon );

		require_once $addon_dir  . 'unsupported/spellchecker.php';
		$my_addon = new SpellCheckerAddon();
		$this->prop[ 'setup' ][] = $my_addon->slug;
		$this->prop[ 'inst' ][ $my_addon->slug ] = $my_addon;
		unset( $my_addon );

	}


}
