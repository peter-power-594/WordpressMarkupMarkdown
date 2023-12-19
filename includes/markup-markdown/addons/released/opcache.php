<?php

namespace MarkupMarkdown;

defined( 'ABSPATH' ) || exit;


class OPCacheAddon {


	private $prop = array(
		'slug' => 'nopcache',
		'label' => 'Disable OP Cache',
		'desc' => 'By default static html files are generated to be used default PHP OPCache if available to speed up the rendering. Check to disable.',
		'release' => 'stable',
		'active' => 0
	);


	public function __construct() {
		if ( defined( 'WP_MMD_OPCACHE' ) && ! WP_MMD_OPCACHE ) :
			$this->prop[ 'active' ] = 1;
			return FALSE; # Disable in wp-config.php or somewhere else
		endif;
		if ( defined( 'MMD_ADDONS' ) && in_array( $this->prop[ 'slug' ], MMD_ADDONS ) !== FALSE ) : # Warning : disable so !== sign here
			define ( 'WP_MMD_OPCACHE', 0 );
			$this->prop[ 'active' ] = 1;
			return FALSE; # Addon has been desactivated
		endif;
		define( 'WP_MMD_OPCACHE', 1 ); # Cache is activated
		$this->prop[ 'active' ] = 0;
		return TRUE;
	}


	public function __get( $name ) {
		if ( array_key_exists( $name, $this->prop ) ) {
			return $this->prop[ $name ];
		}
		return 'mmd_undefined';
	}

}
