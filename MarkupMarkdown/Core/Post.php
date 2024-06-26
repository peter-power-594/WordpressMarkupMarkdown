<?php

namespace MarkupMarkdown\Core;
defined( 'ABSPATH' ) || exit;

class Post {


	private $data = array();


	public function __construct( $file ) {
		$this->check_cache( apply_filters( 'mmd_jekyll_posts_folder', ABSPATH . '_posts' ), $file );
	}


	private function check_cache( $path = '', $file = '' ) {
		if ( empty( $path ) || empty( $file ) || ! is_dir( $path ) ) :
			return false;
		endif;
		$my_post = $path . '/' . $file;
		if ( ! file_exists( $my_post ) ) :
			return false;
		endif;
		$cache_file = mmd()->cache_dir . '/' . mmd()->curr_blog . '_' . preg_replace( '#\.[a-z]+$#', '.json', $file );
		if ( $this->cache_exists( $cache_file ) ) :
			return true;
		endif;
		$post_tmp = file_get_contents( $my_post );
		$post_row = $this->extract_data( $my_post, $post_tmp );
		if ( ! $post_row ) :
			return false;
		endif;
		file_put_contents( $cache_file, json_encode( $post_row ) );
		return true;
	}


	private function cache_exists( $cache_file ) {
		if ( ! file_exists( $cache_file ) ) :
			# The file does not exist
			return false;
		endif;
		$this->data = json_decode( file_get_contents( $cache_file ) );
		if ( ! isset( $this->data ) || ! $this->data ) :
			# Something's wrong
			return false;
		endif;
		if ( ! function_exists( 'md5_file' ) ) :
			# Can't be sure at 100%
			return false;
		endif;
		return md5_file( $cache_file ) === $this->data->md5 ? true : false;
	}


	private function extract_data( $file, $data ) {
		if ( ! isset( $file ) || empty( $file ) || ! isset( $data ) || empty( $data ) ) :
			return false;
		endif;
		if ( strpos( $data, '---' ) === false ) :
			# Doesn't look like a Jekyll formated file 
			return false;
		endif;
		$post_row = $this->extract_headers( explode( "\n", explode( "---\n", $data )[ 1 ] ) );
		if ( ! isset( $post_row[ 'post_type' ] ) ) :
			$post_row[ 'post_type' ] = 'post';
		endif;
		if ( function_exists( 'md5_file' ) ) :
			$post_row[ 'md5' ] = md5_file( $file );
		endif;
		$post_row[ 'content' ] = explode( "---\n", $data )[ 2 ];
		return $post_row;
	}


	private function extract_headers( $header_rows = array() ) {
		$my_rows = array();
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
			$my_rows[ $row_key[ 1 ] ] = $this->sanitize_row_value( $row_val );
		endforeach;
		if ( ! isset( $my_rows[ 'published' ] ) ) :
			$my_rows[ 'published'] = $this->get_post_status( $my_rows );
		endif;
		return $my_rows;
	}


	/**
	 * Quick function to sanitize a value extracted from a Jekyll-like markdown header file
	 * 
	 * @param String|Arra $row_val The extracted value to sanitize
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
	private function get_post_status( $post = [] ) {
		if ( is_array( $post ) || ! isset( $post[ 'date' ] ) ) :
			return true;
		endif;
		return gmdate( 'U' ) < strtotime( $post[ 'date' ] ) ? true : false;
	}

}