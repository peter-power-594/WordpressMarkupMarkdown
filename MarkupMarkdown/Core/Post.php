<?php

namespace MarkupMarkdown\Core;
defined( 'ABSPATH' ) || exit;

class Post {


	public $ID = -1;
	public $post_author = 1;
	public $title = '';
	public $post_date = '2024-01-01 10:10:10';
	public $post_date_gmt = '2024-01-01 10:10:10';
	public $post_title = 'New Post';
	public $post_content = 'Lorem Ipsum Dolor Imet';
	public $comment_status = 'closed';
	public $ping_status = 'closed';
	public $post_name = 'postname';
	public $post_type = 'post';
	public $filter = 'raw';


	public function __construct( $file ) {
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
		return isset( $this->md5 ) && $this->md5 === md5_file( $source_file ) ? true : false;
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
		file_put_contents( $cache_file, json_encode( $post_row ) );
		$this->wp_raw_post( $post_row );
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
	private function wp_key_filter( $key = ''  ) {
		if ( empty( $key ) ) :
			return 'undefined';
		endif;
		return str_replace( 
			array( 'title', 'published', 'date' ),
			array( 'post_title', 'post_status', 'post_date_gmt' ),
			$key
		);
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
			return $val ? 'published' : 'draft';
		elseif ( $key === 'post_date_gmt' ) :
			return gmdate( 'Y-m-d', strtotime( $val ) ) . ' 12:00:00';
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
			if ( ! method_exists( $this, $key ) ) :
				$this->$key = $this->wp_value_filter( $value, $key );
			endif;
		endforeach;
		if ( function_exists( 'current_time' ) ) :
			$this->post_date = current_time( $this->post_date_gmt );
		else :
			$this->post_date = date( 'Y-m-d H:i:s', $this->post_date_gmt );					
		endif;
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
		$post_row = $this->extract_headers( explode( "\n", explode( "---\n", $data )[ 1 ] ) );
		if ( ! isset( $post_row->post_type ) ) :
			$post_row->post_type = 'post';
		endif;
		if ( function_exists( 'md5_file' ) ) :
			$post_row->md5 = md5_file( $file );
		endif;
		$post_row->post_content = explode( "---\n", $data )[ 2 ];
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
			$row_key = array();
			preg_match( '#([a-z]+):#', $row_data, $row_key );
			$row_data = preg_replace( '#[a-z]+:[\s\t]*#', '', $row_data );
			if ( substr( $row_data, 0, 1 ) === '[' && substr( $row_data, -1 ) === ']' ) :
				$row_val = explode( ',', preg_replace( '#(^\[|\]$)#', '', $row_data ) );
			else :
				$row_val = preg_replace( '#(^\"|\"$)#', '', $row_data );
			endif;
			$my_rows->$row_key[ 1 ] = $this->sanitize_row_value( $row_val );
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
	 * @params Array $post The current post attributes
	 * @returns String The post status
	 */
	private function get_post_status( $post ) {
		if ( ! isset( $post ) || ! is_object( $post ) || ! isset( $post->date ) ) :
			return true;
		endif;
		return gmdate( 'U' ) < strtotime( $post[ 'date' ] ) ? true : false;
	}


}