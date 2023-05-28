<?php

namespace MarkupMarkdown;

defined( 'ABSPATH' ) || exit;

class MediaAddon {

	public function __construct() {
		$media_addon_dir = mmd()->plugin_dir . 'includes/markup-markdown/addons/released/media';
		require_once $media_addon_dir . '/vimeo.php';
		require_once $media_addon_dir . '/youtube.php';
		require_once $media_addon_dir . '/image.php';
	}


}

new \MarkupMarkdown\MediaAddon();