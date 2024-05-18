<?php

namespace MarkupMarkdown\Addons\Released;

defined( 'ABSPATH' ) || exit;


class OPCache {


	private $prop = array(
		'slug' => 'nopcache',
		'release' => 'stable',
		'active' => 0
	);


	public function __construct() {
		$this->prop[ 'label' ] = __( 'Disable Static Cache', 'markup-markdown' );
		$this->prop[ 'desc' ] = __( 'Static html files can be generated to speed up the rendering if the default PHP OPCache if available. Uncheck to enable.', 'markup-markdown' );
		if ( defined( 'WP_MMD_OPCACHE' ) ) :
			 # Disable in wp-config.php or somewhere else
			$this->prop[ 'active' ] = ! WP_MMD_OPCACHE ? 1 : 0;
		elseif ( defined( 'MMD_ADDONS' ) ) :
			# Warning : disable by default so !== sign here
			if ( in_array( $this->prop[ 'slug' ], MMD_ADDONS ) !== FALSE ) :
				define( 'WP_MMD_OPCACHE', 0 );
				$this->prop[ 'active' ] = 1;
			else :
				define( 'WP_MMD_OPCACHE', 0 );
				$this->prop[ 'active' ] = 0;
			endif;
		else :
			# Since 3.3.0 cache is desactivated by default to avoid side effects
			define( 'WP_MMD_OPCACHE', 0 );
			$this->prop[ 'active' ] = 1;
		endif;
		return $this->prop[ 'active' ] ? FALSE : TRUE;
	}


	public function __get( $name ) {
		if ( array_key_exists( $name, $this->prop ) ) {
			return $this->prop[ $name ];
		}
		return 'mmd_undefined';
	}

}
