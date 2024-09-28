<?php

namespace MarkupMarkdown\Addons\Unsupported\AdvancedCustomPost;
defined( 'ABSPATH' ) || exit;


class Parser {

	# Native Wordpress properties
	public $ID = -1;
	public $post_author = 1;
	public $post_date = '2024-01-01 10:10:10';
	public $post_date_gmt = '2024-01-01 10:10:10';
	public $post_title = 'New Post';
	public $post_content = 'Lorem Ipsum Dolor Imet';
	public $post_status = 'publish';
	public $comment_status = 'closed';
	public $ping_status = 'closed';
	public $post_name = 'postname';
	public $post_type = 'post';
	public $filter = 'raw';
	# Markup Markdown additional properties
	public $post_md5 = '';
	public $post_categories = array();
	public $post_tags = array();
	public $post_template = '';
	public $post_excerpt = '';
	# Static files
	private $markdown_file = '';
	private $json_file = '';
	# Plugin config
	private $blog_conf = [];


	public function __construct( $file, $conf ) {
		if ( isset( $conf ) || is_array( $conf ) ) :
			$this->blog_conf = $conf;
		endif;
		$target_path = apply_filters( 'mmd_jekyll_posts_folder', ABSPATH . '_posts' );
		$this->check_cache( $target_path, $file );
	}


	/**
	 * Checking the status of the cache related to the target file and trigger
	 * the generation / update of the static json file
	 * 
	 * @access private
	 * @since 3.6.0
	 * 
	 * @param String $path The full path to the markdown file
	 * @param String $file The file name, extension included
	 * @return Boolean true if the target file was found and its related cache
	 *         already existed or was successfully created
	 */
	private function check_cache( $path = '', $file = '' ) {
		if ( empty( $path ) || empty( $file ) || ! is_dir( $path ) ) :
			return false;
		endif;
		$my_post = $path . '/' . $file;
		if ( ! file_exists( $my_post ) ) :
			return false;
		endif;
		$this->markdown_file = $my_post;
		$cache_file = mmd()->cache_dir . '/' . mmd()->curr_blog . '_' . preg_replace( '#\.[a-z]+$#', '.json', $file );
		if ( $this->cache_exists( $my_post, $cache_file ) ) :
			return true;
		endif;
		return $this->cache_create( $my_post, $cache_file );
	}


	/**
	 * Retrieve data from the target static cache file
	 * 
	 * @access private
	 * @since 3.6.0
	 * 
	 * @param String $source_file The full path with the filename to the markdown file
	 * @param String $cache_file The full path with the filename to the json cache file
	 * @return Boolean true in case of success or false if failed or invalid
	 */
	private function cache_exists( $source_file = '', $cache_file = '' ) {
		if ( ! file_exists( $source_file ) || ! file_exists( $cache_file ) ) :
			# The file does not exist
			return false;
		endif;
		$hasData = $this->wp_raw_post( json_decode( file_get_contents( $cache_file ) ) );
		if ( ! $hasData ) :
			# Something's wrong
			return false;
		endif;
		if ( ! function_exists( 'md5_file' ) ) :
			# Can't be sure at 100%
			return false;
		endif;
		$this->json_file = $cache_file;
		return isset( $this->post_md5 ) && $this->post_md5 === md5_file( $source_file ) ? true : false;
	}


	/**
	 * Create a static json cache file from the target static cache file
	 * 
	 * @access private
	 * @since 3.6.0
	 * 
	 * @param String $source_file The full path with the filename to the markdown file
	 * @param String $cache_file The full path with the filename to the json cache file
	 * @return Boolean true in case of success or false if failed
	 */
	private function cache_create( $source_file = '', $cache_file = '' ) {
		if ( ! file_exists( $source_file ) ) :
			return false;
		endif;
		$post_tmp = file_get_contents( $source_file );
		$post_row = $this->extract_data( $source_file, $post_tmp );
		if ( ! $post_row ) :
			return false;
		endif;
		$this->wp_raw_post( $post_row );
		$final_data = array(
			'ID' => -1,
			'post_author' => $this->post_author,
			'post_date' => $this->post_date,
			'post_date_gmt' => $this->post_date_gmt,
			'post_title' => $this->post_title,
			'post_content' => $this->post_content,
			'post_status' => $this->post_status,
			'comment_status' => $this->comment_status,
			'ping_status' => $this->ping_status,
			'post_name' => $this->post_name,
			'post_type' => $this->post_type,
			'filter' => $this->filter,
			'post_md5' => $this->post_md5,
			'post_categories' => $this->post_categories,
			'post_tags' => $this->post_tags,
			'post_template' => $this->post_template,
			'post_excerpt' => $this->post_excerpt
		);
		file_put_contents( $cache_file, json_encode( $final_data ) );
		mmd()->clear_cache( $cache_file );
		return true;
	}


	/**
	 * Object key name modified to match WP_Post attributes
	 * 
	 * @access private
	 * @since 3.6.0
	 * 
	 * @param String $key The markdown key name
	 * @return String The modified key name
	 */
	private function wp_key_filter( $key = '' ) {
		if ( empty( $key ) ) :
			return 'undefined';
		endif;
		if ( $key === 'title' ) :
			return 'post_title';
		elseif ( $key === 'published' ) :
			return 'post_status';
		elseif ( $key === 'date' ) :
			return 'post_date_gmt';
		elseif ( $key === 'categories' ) :
			return 'post_categories';
		elseif ( $key === 'tags' ) :
			return 'post_tags';
		elseif ( $key === 'description' || $key === 'excerpt' ) :
			return 'post_excerpt';
		elseif ( $key === 'layout' ) :
			return 'post_template';
		else :
			return $key;
		endif;
	}


	/**
	 * Object key name modified to match WP_Post attributes
	 * 
	 * @access private
	 * @since 3.6.0
	 * 
	 * @param String $val The markdown value
	 * @param String $key The markdown key name
	 * @return String The modified value
	 */
	private function wp_value_filter( $val = '', $key = ''  ) {
		if ( empty( $key ) ) :
			return $val;
		endif;
		if ( $key === 'post_status' ) :
			if ( is_bool( $val ) && $val ) :
				return 'publish';
			elseif ( is_string( $val ) && $val === 'publish' ) :
				return 'publish';
			else :
				return 'draft';
			endif;
		elseif ( $key === 'post_date_gmt' ) :
			return gmdate( 'Y-m-d H:i:s', strtotime( $val ) );
		elseif ( $key === 'post_categories' || $key === 'post_tags' ) :
			return is_array( $val ) ? $val : array( $val ); 
		else:
			return $val;
		endif;
	}


	/**
	 * Wordpress friendly format adjustements for the cache file
	 * to avoid warnings or errors with native methods.
	 * 
	 * @access private
	 * @since 3.6.0
	 * 
	 * @param Object $data Object created from a json file
	 * @return TRUE in case of success or FALSE in case of troubles
	 */
	private function wp_raw_post( $data ) {
		if ( ! is_object( $data ) ) :
			return false;
		endif;
		$this->ID = -1 * rand(1, 999);
		$this->post_author = get_current_user_id();
		$this->filter = 'raw';
		foreach( $data as $key => $value ) :
			$key = $this->wp_key_filter( $key );
			$val = $this->wp_value_filter( $value, $key );
			$this->$key = $val;
		endforeach;
		$this->post_date = date( 'Y-m-d H:i:s', strtotime( $this->post_date_gmt ) + (int) ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS ) );					
		return true;
	}


	/**
	 * Extract data from a Jekyll formated markdown file
	 * 
	 * @access private
	 * @since 3.6.0
	 * 
	 * @param String $file The full path with the filename to the markdown file
	 * @param Object $data The raw text data
	 * @return Object $post_row Modified object with new properties
	 */
	private function extract_data( $file, $data ) {
		if ( ! isset( $file ) || empty( $file ) || ! isset( $data ) || empty( $data ) ) :
			return false;
		endif;
		if ( strpos( $data, '---' ) === false ) :
			# Doesn't look like a Jekyll formated file 
			return false;
		endif;
		$head_body = preg_split( "#---\r*\n#", $data );
		if ( ! $head_body || count( $head_body ) !== 3 ) :
			# Something looks weird
			return false;
		endif;
		$post_row = $this->extract_headers( preg_split( "#\r*\n#", $head_body[ 1 ] ) );
		if ( ! isset( $post_row->post_type ) ) :
			$post_row->post_type = 'post';
		endif;
		if ( function_exists( 'md5_file' ) ) :
			$post_row->post_md5 = md5_file( $file );
		endif;
		$post_row->post_content = $head_body[ 2 ];
		return $post_row;
	}


	/**
	 * Extract the header properties from a Jekyll formated markdown file
	 * 
	 * @access private
	 * @since 3.6.0
	 * 
	 * @param Array $header_rows The raw data rows from the markdown header part
	 * @return Object Extracted and ready to use propertiesas $key => $value
	 */
	private function extract_headers( $header_rows = array() ) {
		$my_rows = new \stdClass();
		foreach( $header_rows as $row_data ) :
			if ( strpos( $row_data, ':' ) === false ) :
				continue;
			endif;
			$row_keys = array();
			preg_match( '#^([a-z]+):#', $row_data, $row_keys );
			$row_data = preg_replace( '#^[a-z]+:[\s\t]*#', '', $row_data );
			if ( substr( $row_data, 0, 1 ) === '[' && substr( $row_data, -1 ) === ']' ) :
				$row_val = explode( ',', preg_replace( '#(^\[|\]$)#', '', $row_data ) );
			else :
				$row_val = preg_replace( '#(^\"|\"$)#', '', $row_data );
			endif;
			$row_key = $row_keys[ 1 ];
			$my_rows->$row_key = $this->sanitize_row_value( $row_val );
		endforeach;
		if ( ! isset( $my_rows->published ) ) :
			$my_rows->published = $this->get_post_status( $my_rows );
		endif;
		return $my_rows;
	}


	/**
	 * Quick function to sanitize a value extracted from a Jekyll-like markdown header file
	 * 
	 * @param String|Array $row_val The extracted value to sanitize
	 * @return String|Array The sanitized field value
	 */
	private function sanitize_row_value( $row_val ) {
		if ( ! isset( $row_val ) ) :
			return false;
		endif;
		if ( is_array( $row_val ) ) :
			foreach( $row_val as $idx => $val ) :
				# Remove unwanted characters like spaces or quotes
				$row_val[ $idx ] = preg_replace( '#(^\"|\"$)#', '', trim( $val ) );
			endforeach;
		else :
			# Boolean path
			if ( 'true' === $row_val ) :
				$row_val = true;
			elseif ( 'false' === $row_val ) :
				$row_val = false;
			endif;
		endif;
		return $row_val;
	}


	/**
	 * Get post status regards the pulish or future field
	 *
	 * @param Array $post The current post attributes
	 * @return String The post status
	 */
	private function get_post_status( $post ) {
		if ( ! isset( $post ) || ! is_object( $post ) ) :
			return true;
		endif;
		if ( isset( $post->post_date_gmt ) ) :
			return gmdate( 'U' ) < strtotime( $post->post_date_gmt ) ? true : false;
		elseif ( isset( $post->date ) ) :
			return gmdate( 'U' ) < $post->date ? true : false;
		endif;
		return true;
	}


	/**
	 * Write data to the original markdown text file
	 *
	 * @access private
	 * @since 3.8.0
	 * 
	 * @return Void
	 */
	private function write_data() {
		$mmd_data = "---";
		$mmd_data .= "\nlayout: post";
		$mmd_data .= "\ntitle: " . $this->post_title;
		$mmd_data .= "\ndescription: \"" . $this->post_excerpt . "\"";
		$mmd_data .= "\ndate: " . $this->post_date_gmt; # preg_replace( '#\s\d{2}:\d{2}:\d{2}#', '', $this->post_date_gmt );
		if ( $this->post_status === 'draft' ) :
			$mmd_data .= "\npublished: false";
		endif;
		$cat_count = count( $this->post_categories );
		if ( $cat_count > 0 ) :
			if ( $cat_count === 1 ) :
				$mmd_data .= "\ncategories: " . $this->post_categories[ 0 ];
			else:
				$mmd_data .= "\ncategories: [ " . implode( ', ', $this->post_categories ) . " ]";
			endif;
		endif;
		$tag_count = count( $this->post_tags );
		if ( $tag_count > 0 ) :
			if ( $tag_count === 1 ) :
				$mmd_data .= "\ntags: " . $this->post_tags[ 0 ];
			else:
				$mmd_data .= "\ntags: [ " . implode( ', ', $this->post_tags ) . " ]";
			endif;
		endif;
		$mmd_data .= "\n---\n";
		$mmd_data .= html_entity_decode( $this->post_content );
		@unlink( $this->json_file );
		return file_put_contents( $this->markdown_file, $mmd_data );
	}


	/**
	 * Check for modified data from the submitted data
	 *
	 * @access public
	 * @since 3.8.0
	 * 
	 * @return Bolean true if something was modified or fase in nothing was updated
	 */
	public function update() {
		$data = array(
			'post_status' => filter_input( INPUT_POST, 'post_status', FILTER_SANITIZE_SPECIAL_CHARS ),
			'post_title' => filter_input( INPUT_POST, 'post_title', FILTER_SANITIZE_SPECIAL_CHARS ),
			'post_publish' => filter_input( INPUT_POST, 'publish', FILTER_SANITIZE_SPECIAL_CHARS ),
			'post_content' => filter_input( INPUT_POST, 'content', FILTER_SANITIZE_SPECIAL_CHARS ),
			'original_publish' => filter_input( INPUT_POST, 'publish', FILTER_SANITIZE_SPECIAL_CHARS ),
			'post_tags' => filter_input( INPUT_POST, 'post_tags', FILTER_SANITIZE_SPECIAL_CHARS ),
			'post_date_year' => filter_input( INPUT_POST, 'aa', FILTER_SANITIZE_SPECIAL_CHARS ),
			'post_date_month' => filter_input( INPUT_POST, 'mm', FILTER_SANITIZE_SPECIAL_CHARS ),
			'post_date_day' => filter_input( INPUT_POST, 'jj', FILTER_SANITIZE_SPECIAL_CHARS ),
			'post_date_hours' => filter_input( INPUT_POST, 'hh', FILTER_SANITIZE_SPECIAL_CHARS ),
			'post_date_minutes' => filter_input( INPUT_POST, 'mn', FILTER_SANITIZE_SPECIAL_CHARS ),
			'post_date_seconds' => filter_input( INPUT_POST, 'ss', FILTER_SANITIZE_SPECIAL_CHARS )
		);
		$data = array_merge( $data, filter_input_array( INPUT_POST, array( 'post_category' => array( 'filter' => FILTER_SANITIZE_SPECIAL_CHARS, 'flags' => FILTER_REQUIRE_ARRAY ) ) ) );
		$update = false;

		# Check for modifications within the post status
		if ( isset( $data[ 'post_publish' ] ) && isset( $data[ 'original_publish' ] ) && $data[ 'post_publish' ] === $data[ 'original_publish' ] && $data[ 'original_publish' ] === __( 'Publish' ) ) :
			$this->post_status = 'publish';
			$update = true;
		elseif ( $this->post_status !== $data[ 'post_status' ] && in_array( $data[ 'post_status' ], array( 'publish', 'draft' ) ) ) :
			$this->post_status = $data[ 'post_status' ];
			$update = true;
		endif;
		# Check for modifications within the post title
		if ( isset( $data[ 'post_title' ] ) && $this->post_title !== $data[ 'post_title' ] ) :
			$this->post_title = $data[ 'post_title' ];
			$update = true;
		endif;
		# Check for modifications within the post content
		if ( isset( $data[ 'post_content' ] ) && $this->post_content !== $data[ 'post_content' ] ) :
			$this->post_content = $data[ 'post_content' ];
			$update = true;
		endif;
		# Check if the date was modified 
		$date = $this->post_date;
		if ( isset( $data[ 'post_date_year' ] ) && isset( $data[ 'post_date_month' ] ) && isset( $data[ 'post_date_day' ] ) && isset( $data[ 'post_date_hours' ] ) && isset( $data[ 'post_date_minutes' ] ) && isset( $data[ 'post_date_seconds' ] ) ) :
			$date = $data[ 'post_date_year' ] . '-' . $data[ 'post_date_month' ];
			$date .= '-' . $data[ 'post_date_day' ];
			$date .= ' ' . $data[ 'post_date_hours' ];
			$date .= ':' . $data[ 'post_date_minutes' ];
			$date .= ':' . $data[ 'post_date_seconds' ];
		endif;
		if ( preg_match( '#\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}#u', $date ) && $date !== $this->post_date ) :
			$this->post_date = $date;
			$this->post_date_gmt = gmdate( 'Y-m-d H:i:s', strtotime( $date ) );
			$update = true;
		endif;
		# Check for new categories attached to the post
		if ( isset( $data[ 'post_category' ] ) ) :
			$categories = [];
			foreach( $data[ 'post_category' ] as $idx => $category ) :
				$category = trim( $category );
				if ( ! isset( $category ) || is_integer( (int)$category ) || empty( $category ) ) :
					continue;
				endif;
				$categories[] = trim( $category );
			endforeach;
			if ( ! array_diff( $this->post_categories, $categories ) ) :
				$this->post_categories = $categories;
				$update = true;
			endif;
		endif;
		# Check for new tags attached to the post
		if ( isset( $data[ 'post_tags' ] ) ) :
			$tags = explode( ', ', $data[ 'post_tags' ] );
			foreach( $tags as $idx => $tag ) :
				$tags[ $idx ] = trim( $tag );
			endforeach;
			if ( ! array_diff( $this->post_tags, $tags ) ) :
				$this->post_tags = $tags;
				$update = true;
			endif;
		endif;
		if ( ! $update ) :
			return false;
		endif;
		$my_save = $this->write_data();
		if ( ! $my_save ) :
			return false;
		endif;
		if ( $this->post_status === 'publish' && isset( $this->blog_conf[ 'use_git' ] ) && (int)$this->blog_conf[ 'use_git' ] > 0 ) :
			do_action( 'mmd_git_push', $this->markdown_file, $data[ 'post_title' ] );
		endif;
		return true;
	}


}
