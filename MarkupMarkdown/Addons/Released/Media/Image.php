<?php

namespace MarkupMarkdown\Addons\Released\Media;

defined( 'ABSPATH' ) || exit;

class Image {


	private $prop = array(
		'slug' => 'Image',
		'release' => 'stable',
		'active' => 1
	);


	public $home_url = '';


	public $def_sizes = [];


	public $gutenberg = 0;

	public function __construct( ) {
		$this->prop[ 'label' ] = esc_html__( 'Responsive Image', 'markup-markdown' );
		$this->prop[ 'desc' ] = esc_html__( 'Add basic html code syntax for responsive media.', 'markup-markdown' );
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


	public function load_image_block_assets() {
		if ( $this->gutenberg > 0 ) : # Already loaded
			return FALSE;
		endif;
		$this->gutenberg = 1;
		wp_enqueue_style( 'wp-block-image', '/wp-includes/blocks/image/style.min.css' ); # Required ?
		return TRUE;
	}


	private function wp_image( $ops ) {
		if ( ! isset( $ops ) || ! is_array( $ops ) ) :
			return '';
		endif;
		# Check for caption inside alternative text
		$alt = ''; $caption = '';
		if ( isset( $ops[ 'label' ] ) && is_array( $ops[ 'label' ] ) ) :
			$alt = isset( $ops[ 'label' ][ 1 ] ) ? $ops[ 'label' ][ 1 ] : '';
			if ( strpos( $alt, '--' ) !== FALSE ) :
				$text = explode( '--', $alt );
				$alt = $text[ 0 ]; $caption = $text[ 1 ];
			endif;
		endif;
		# Check for custom align
		$align = 'none';
		if ( isset( $ops[ 'align' ] ) && is_array( $ops[ 'align' ] ) && isset( $ops[ 'align' ][ 1 ] ) && in_array( $ops[ 'align' ][ 1 ], array( 'none', 'left', 'right', 'center' ) ) ) :
			$align = $ops[ 'align' ][ 1 ];
		endif;
		# Check for custom sizes set in html attributes
		$req_width = 0; $req_height = 0;
		if ( isset( $ops[ 'size' ] ) && is_array( $ops[ 'size' ] ) ) :
			if ( isset( $ops[ 'size' ][ 0 ] ) && isset( $ops[ 'size' ][ 0 ][ 1 ] ) ) :
				if ( (int)$ops[ 'size' ][ 0 ][ 1 ] > 0 ) :
					$req_width = (int)$ops[ 'size' ][ 0 ][ 1 ];
				endif;
			endif;
			if ( isset( $ops[ 'size' ][ 1 ] ) && isset( $ops[ 'size' ][ 1 ][ 1 ] ) ) :
				if ( (int)$ops[ 'size' ][ 1 ][ 1 ] > 0 ) :
					$req_height = (int)$ops[ 'size' ][ 1 ][ 1 ];
				endif;
			endif;
		endif;
		$html = '<figure id="attachment_mmd_' . $ops[ 'idx' ] . '"';
		if ( ! empty( $caption ) ) :
			$html .= ' aria-describedby="caption-attachment-mmd' . $ops[ 'idx' ] . '" class="wp-block-image wp-caption ';
		else :
			$html .= ' class="wp-block-image ';
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
		$html .= '<img decoding="async" loading="lazy"';
		# Image source
		$src = '';
		if ( $ops[ 'src' ] && ! empty( $ops[ 'src' ] ) ) :
			$src = $ops[ 'src' ];
			$html .= ' src="' . $src . '"';
		endif;
		# Image final size
		$width = 0; $height = 0;
		if ( $req_width > 0 ) :
			$width = $req_width;
		endif;
		if ( $req_height > 0 ) :
			$height = $req_height;
		endif;
		$wp_width = 0; $wp_height = 0;
		if ( isset( $ops[ 'width' ] ) && is_numeric( $ops[ 'width' ] ) && (int)$ops[ 'width' ] > 0 ) :
			$wp_width = (int)$ops[ 'width' ];
			if ( ! $req_width && ! $req_height ) :
				$width = (int)$ops[ 'width' ];
			endif;
		endif;
		if ( isset( $ops[ 'height' ] ) && is_numeric( $ops[ 'height' ] ) && (int)$ops[ 'height' ] > 0 ) :
			$wp_height = (int)$ops[ 'height' ];
			if ( ! $req_width && ! $req_height ) :
				$height = (int)$ops[ 'height' ];
			endif;
		endif;
		if ( $width > 0 ) :
			$html .= ' width="' . $width . '"';
		endif;
		if ( $height > 0 ) :
			$html .= ' height="' . $height . '"';
		endif;
		# If width or height is set to 'auto', we might miss a param
		if ( ! empty( $src ) && $wp_width > 0 && $wp_height > 0 ) :
			$html .= ' srcset="' . $src . ' ' . $wp_width . 'w';
			// Check which size is used
			foreach ( $this->def_sizes as $def_size ) :
				foreach( $def_size as $size_name => $size_value ) :
					if ( $wp_width === $size_value[ 0 ] ) :
						continue;
					endif;
					$new_height = floor( $size_value[ 0 ] * $wp_height / $wp_width );
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
				. '" class="wp-caption-text wp-element-caption">' . trim( $caption ) . '</figcaption>';
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
		if ( defined( 'MMD_USE_BLOCKSTYLES' ) && MMD_USE_BLOCKSTYLES ) :
			$this->load_image_block_assets();
		endif;
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
		$media_idx = 0;
		# Replace WP-like linked images <a href=""...><img src=".../foo-640x480.jpg"...></a>
		preg_match_all( '#<a href="(.*?)"([^>]*)><img src="/(.*?)-(\d+)x(\d+)\.([a-zA-Z]+)"(.*?)></a>#', $content, $wp_imgs );
		foreach( $wp_imgs[ 0 ] as $idx => $img_tag ) :
			preg_match( '#alt="(.*?)"#', $wp_imgs[ 7 ][ $idx ], $img_label );
			preg_match( '#align([a-z]+)#', $wp_imgs[ 7 ][ $idx ], $img_align );
			preg_match( '#width="(.*?)"#', $wp_imgs[ 7 ][ $idx ], $img_width );
			preg_match( '#height="(.*?)"#', $wp_imgs[ 7 ][ $idx ], $img_height );
			$new_img_tag = $this->wp_image(array(
				'idx'	=> $media_idx,
				'url'	=> $wp_imgs[ 1 ][ $idx ],
				'title' => $wp_imgs[ 2 ][ $idx ],
				'label' => $img_label,
				'align' => $img_align,
				'size'  => array( $img_width, $img_height ),
				'src'	=> '/' . $wp_imgs[ 3 ][ $idx ] . '-'
						. $wp_imgs[ 4 ][ $idx ] . 'x' . $wp_imgs[ 5 ][ $idx ]
						. '.' . $wp_imgs[ 6 ][ $idx ],
				'width' => $wp_imgs[ 4 ][ $idx ],
				'height' => $wp_imgs[ 5 ][ $idx ],
			));
			if ( ! empty( $new_img_tag ) ) :
				$content = str_replace( $img_tag, $new_img_tag, $content );
				$media_idx++;
			endif;
		endforeach;
		# Replace WP-like images <img src=".../bar-1024x768.jpg"...>
		preg_match_all( '#<img src="/(.*?)-(\d+)x(\d+)\.([a-zA-Z]+)"(.*?)>#', $content, $wp_imgs );
		foreach( $wp_imgs[ 0 ] as $idx => $img_tag ) :
			preg_match( '#alt="(.*?)"#', $wp_imgs[ 5 ][ $idx ], $img_label );
			preg_match( '#align([a-z]+)#', $wp_imgs[ 5 ][ $idx ], $img_align );
			preg_match( '#width="(.*?)"#', $wp_imgs[ 5 ][ $idx ], $img_width );
			preg_match( '#height="(.*?)"#', $wp_imgs[ 5 ][ $idx ], $img_height );
			$new_img_tag = $this->wp_image(array(
				'idx'	=> $media_idx,
				'label' => $img_label,
				'align' => $img_align,
				'size'  => array( $img_width, $img_height ),
				'src'	=> '/' . $wp_imgs[ 1 ][ $idx ] . '-'
						. $wp_imgs[ 2 ][ $idx ] . 'x' . $wp_imgs[ 3 ][ $idx ]
						. '.' . $wp_imgs[ 4 ][ $idx ],
				'width' => $wp_imgs[ 2 ][ $idx ],
				'height' => $wp_imgs[ 3 ][ $idx ],
			));
			if ( ! empty( $new_img_tag ) ) :
				$content = str_replace( $img_tag, $new_img_tag, $content );
				$media_idx++;
			endif;
		endforeach;
		# Replace other linked images <a href=""...><img src="....jpg"...></a>
		preg_match_all( '#<a href="(.*?)"([^>]*)><img src="(.*?\.[a-zA-Z]+)"(.*?)></a>#', $content, $wp_imgs );
		foreach( $wp_imgs[ 0 ] as $idx => $img_tag ) :
			preg_match( '#alt="(.*?)"#', $wp_imgs[ 4 ][ $idx ], $img_label );
			preg_match( '#align([a-z]+)#', $wp_imgs[ 4 ][ $idx ], $img_align );
			preg_match( '#width="(.*?)"#', $wp_imgs[ 4 ][ $idx ], $img_width );
			preg_match( '#height="(.*?)"#', $wp_imgs[ 4 ][ $idx ], $img_height );
			$new_img_tag = $this->wp_image(array(
				'idx'	=> $media_idx,
				'url'	=> $wp_imgs[ 1 ][ $idx ],
				'title' => $wp_imgs[ 2 ][ $idx ],
				'label' => $img_label,
				'align' => $img_align,
				'size'  => array( $img_width, $img_height ),
				'src'	=> $wp_imgs[ 3 ][ $idx ]
			));
			if ( ! empty( $new_img_tag ) ) :
				$content = str_replace( $img_tag, $new_img_tag, $content );
				$media_idx++;
			endif;
		endforeach;
		# Replace other standard images <img src="....jpg"...>
		preg_match_all( '#<img src="(.*?\.[a-zA-Z]+)"(.*?)>#', $content, $wp_imgs );
		foreach( $wp_imgs[ 0 ] as $idx => $img_tag ) :
			preg_match( '#alt="(.*?)"#', $wp_imgs[ 2 ][ $idx ], $img_label );
			preg_match( '#align([a-z]+)#', $wp_imgs[ 2 ][ $idx ], $img_align );
			preg_match( '#width="(.*?)"#', $wp_imgs[ 2 ][ $idx ], $img_width );
			preg_match( '#height="(.*?)"#', $wp_imgs[ 2 ][ $idx ], $img_height );
			$new_img_tag = $this->wp_image(array(
				'idx'	=> $media_idx,
				'label' => $img_label,
				'align' => $img_align,
				'size'  => array( $img_width, $img_height ),
				'src'	=> $wp_imgs[ 1 ][ $idx ]
			));
			if ( ! empty( $new_img_tag ) ) :
				$content = str_replace( $img_tag, $new_img_tag, $content );
				$media_idx++;
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
