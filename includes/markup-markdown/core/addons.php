<?php

namespace MarkupMarkdown;

defined( 'ABSPATH' ) || exit;
defined( 'MMD_PLUGIN_ACTIVATED' ) || exit;


class PluginAddons {


	public function __construct() {
		$addon_dir = mmd()->plugin_dir . '/includes/markup-markdown/addons/';
		# Load addons modules
		require_once $addon_dir  . 'released/layout.php';
		require_once $addon_dir  . 'released/media.php';
		require_once $addon_dir  . 'unsupported/acf.php';
		require_once $addon_dir  . 'unsupported/spellchecker.php';
	}


}

new \MarkupMarkdown\PluginAddons();
