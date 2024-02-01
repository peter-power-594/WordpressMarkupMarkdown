<?php

namespace MarkupMarkdown\Addons\Released\Media;

defined( 'ABSPATH' ) || exit;

class Image {


	private $prop = array(
		'slug' => 'Image',
		'label' => 'Responsive Image',
		'desc' => 'Add basic html code syntax for responsive media.',
		'release' => 'stable',
		'active' => 1
	);


	public $home_url = '';


	public $def_sizes = [];


	public function __construct( ) {
		if ( defined( 'MMD_ADDONS' ) && in_array( $this->prop[ 'slug' ], MMD_ADDONS ) === FALSE ) :
			$this->prop[ 'active' ] = 0;
			return FALSE; # Addon has been desactivated
		endif;
		if ( ! is_admin() ) :
			add_filter( 'addon_markdown2html', array( $this, 'render_responsive_image' ), 9, 1 );
		endif;
	}


	public function __get( $name ) {
		if ( array_key_exists( $name, $this->prop ) ) {
			return $this->prop[ $name ];
		}
		return 'mmd_undefined';
	}


	private function wp_image( $ops ) {
		if ( ! isset( $ops ) || ! is_array( $ops ) ) :
			return '';
		endif;
		// Check for caption inside alternative text
		$alt = ''; $caption = '';
		if ( isset( $ops[ 'label' ] ) && is_array( $ops[ 'label' ] ) ) :
			$alt = isset( $ops[ 'label' ][ 1 ] ) ? $ops[ 'label' ][ 1 ] : '';
			if ( strpos( $alt, '--' ) !== FALSE ) :
				$text = explode( '--', $alt );
				$alt = $text[ 0 ]; $caption = $text[ 1 ];
			endif;
		endif;
		$align = 'none';
		if ( isset( $ops[ 'align' ] ) && is_array( $ops[ 'align' ] )
			&& in_array( $ops[ 'align' ][ 1 ], array( 'none', 'left', 'right', 'center' ) ) ) :
			$align = $ops[ 'align' ][ 1 ];
		endif;
		$html = '<figure id="attachment_mmd_' . $ops[ 'idx' ] . '"';
		if ( ! empty( $caption ) ) :
			$html .= ' aria-describedby="caption-attachment-mmd' . $ops[ 'idx' ] . '" class="wp-caption ';
		else :
			$html .= ' class="';
		endif;
		$html .= 'align' . $align . '">';
		if ( isset( $ops[ 'url' ] ) && ! empty( $ops[ 'url' ] ) ) :
			$html .= '<a href="';
			if ( substr( $ops[ 'url' ], 0, 1 ) !== '/' ) :
				if ( strpos( $ops[ 'url' ], $this->home_url ) === FALSE ) :
					$html .= $ops[ 'url' ] . '" target="_blank" rel="noopener noreferrer">';
				else :
					$html .= '/' . str_replace( $this->home_url, '', $ops[ 'url' ] ) . '"';
				endif;
			else :
				$html .= $ops[ 'url' ] . '"';
			endif;
			if ( isset( $ops[ 'title' ] ) && ! empty( $ops[ 'title' ] ) ) :
				$html .= $ops[ 'title' ];
			endif;
			$html .= '>';
		endif;
		$html .= '<img decoding="async" loading="lazy" ';
		# Image source
		$src = '';
		if ( $ops[ 'src' ] && ! empty( $ops[ 'src' ] ) ) :
			$src = $ops[ 'src' ];
			$html .= ' src="' . $src . '"';
		endif;
		# Image size
		$width = 0; $height = 0;
		if ( isset( $ops[ 'width' ] ) && is_numeric( $ops[ 'width' ] ) ) :
			$width = (int)$ops[ 'width' ];
			$html .= ' width="' . $width . '"';
		endif;
		if ( isset( $ops[ 'height' ] ) && is_numeric( $ops[ 'height' ] ) ) :
			$height = (int)$ops[ 'height' ];
			$html .= ' height="' . $height . '"';
		endif;
		# If width or height is set to 'auto', we might miss a param
		if ( ! empty( $src ) && $width > 0 && $height > 0 ) :
			$html .= 'srcset="' . $src . ' ' . $width . 'w';
			// Check wich size is used
			foreach ( $this->def_sizes as $def_size ) :
				foreach( $def_size as $size_name => $size_value ) :
					if ( $width === $size_value[ 0 ] ) :
						continue;
					endif;
					$new_height = floor( $size_value[ 0 ] * $height / $width );
					$new_src = preg_replace( '#\d+x\d+\.([a-zA-Z]+)$#', $size_value[ 0 ] . 'x' . $new_height . '.$1', $src );
					$base_src = preg_replace( '#.*?wp-content#', '/wp-content', $new_src );
					if ( ! file_exists( ABSPATH . $base_src ) ) :
						$new_height--;
						$new_src = preg_replace( '#\d+x\d+\.([a-zA-Z]+)$#', $size_value[ 0 ] . 'x' . $new_height . '.$1', $src );
						$base_src = preg_replace( '#.*?wp-content#', '/wp-content', $new_src );
					endif;
					if ( ! file_exists( ABSPATH . $base_src ) ) :
						$new_height = $new_height + 2;
						$new_src = preg_replace( '#\d+x\d+\.([a-zA-Z]+)$#', $size_value[ 0 ] . 'x' . $new_height . '.$1', $src );
						$base_src = preg_replace( '#.*?wp-content#', '/wp-content', $new_src );
					endif;
					if ( file_exists( ABSPATH . $base_src ) ) :
						$html .= ', ' . $new_src . ' ' . $size_value[ 0 ] . 'w';
					endif;
				endforeach;
			endforeach;
			$orig_src = preg_replace( '#-\d+x\d+\.([a-z]+)$#', '.$1', $src );
			$base_src = preg_replace( '#.*?wp-content#', '/wp-content', $orig_src );
			if ( file_exists( ABSPATH . $base_src ) ) :
				# Force to a bigger value for large screen
				# At this point we don't know the original image size without reading
				$html .= ', ' . $orig_src . ' ' . ( $this->def_sizes[ count( $this->def_sizes ) - 1 ][ 'large' ][ 0 ] * 2 ) . 'w';
			endif;
			$html .= '"';
			$html .= ' sizes="(max-width: ' . $width . 'px) 100vw, ' . $width . 'px"';
		endif;
		# Image alternative text
		$html .= ' alt="' . trim( $alt ) . '"';
		$html .= '>';
		if ( isset( $ops[ 'url' ] ) ) :
			$html .= '</a>';
		endif;
		if ( ! empty( $caption ) ) :
			$html .= '<figcaption id="caption-attachment-mmd' . $ops[ 'idx' ]
				. '" class="wp-caption-text">' . trim( $caption ) . '</figcaption>';
		endif;
		$html .= '</figure>';
		return $html;
	}


	/**
	 * Format the images html tags as wordpress
	 *
	 * @access public
	 *
	 * @params string $content The html generated from the markdown
	 * @return string $content The modified html code
	 */
	public function render_responsive_image( $content = '' ) {
		$wp_imgs = array();
		if ( ! $this->home_url ) :
			$this->home_url = preg_replace( '#(\.[a-z]+)\/.*?$#', '$1/', get_home_url() );
		endif;
		if ( ! $this->def_sizes ) :
			$this->def_sizes = [
				# [ 'thumbnail' => [ (int)get_option( 'thumbnail_size_w' ), (int)get_option( 'thumbnail_size_h' ) ] ],
				[ 'medium' => [ (int)get_option( 'medium_size_w' ), (int)get_option( 'medium_size_h' ) ] ],
				[ 'large' => [ (int)get_option( 'large_size_w' ), (int)get_option( 'large_size_h' ) ] ]
			];
		endif;
		# Replace linked images
		preg_match_all( '#<a href="(.*?)"([^>]*)><img src="/(.*?)-(\d+)x(\d+)\.([a-zA-Z]+)"(.*?)></a>#', $content, $wp_imgs );
		foreach( $wp_imgs[ 0 ] as $idx => $img_tag ) :
			preg_match( '#alt="(.*?)"#', $wp_imgs[ 7 ][ $idx ], $img_label );
			preg_match( '#align([a-z]+)#', $wp_imgs[ 7 ][ $idx ], $img_align );
			$new_img_tag = $this->wp_image(array(
				'idx'	=> $idx,
				'url'	=> $wp_imgs[ 1 ][ $idx ],
				'title' => $wp_imgs[ 2 ][ $idx ],
				'label' => $img_label,
				'align' => $img_align,
				'src'	=> '/' . $wp_imgs[ 3 ][ $idx ] . '-'
						. $wp_imgs[ 4 ][ $idx ] . 'x' . $wp_imgs[ 5 ][ $idx ]
						. '.' . $wp_imgs[ 6 ][ $idx ],
				'width' => $wp_imgs[ 4 ][ $idx ],
				'height' => $wp_imgs[ 5 ][ $idx ],
			));
			if ( ! empty( $new_img_tag ) ) :
				$content = str_replace( $img_tag, $new_img_tag, $content );
			endif;
		endforeach;
		# Replace images
		preg_match_all( '#<img src="/(.*?)-(\d+)x(\d+)\.([a-zA-Z]+)"(.*?)>#', $content, $wp_imgs );
		foreach( $wp_imgs[ 0 ] as $idx => $img_tag ) :
			preg_match( '#alt="(.*?)"#', $wp_imgs[ 5 ][ $idx ], $img_label );
			preg_match( '#align([a-z]+)#', $wp_imgs[ 5 ][ $idx ], $img_align );
			$new_img_tag = $this->wp_image(array(
				'idx'	=> $idx,
				'label' => $img_label,
				'align' => $img_align,
				'src'	=> '/' . $wp_imgs[ 1 ][ $idx ] . '-'
						. $wp_imgs[ 2 ][ $idx ] . 'x' . $wp_imgs[ 3 ][ $idx ]
						. '.' . $wp_imgs[ 4 ][ $idx ],
				'width' => $wp_imgs[ 2 ][ $idx ],
				'height' => $wp_imgs[ 3 ][ $idx ],
			));
			if ( ! empty( $new_img_tag ) ) :
				$content = str_replace( $img_tag, $new_img_tag, $content );
			endif;
		endforeach;
		# Cleanup HTML
		return str_replace(
			array( '<p><figure', '</figure></p>' ),
			array( '<figure', '</figure>' ),
			$content
		);
	}


}
