<?php

namespace MarkupMarkdown\Abstracts;

defined( 'ABSPATH' ) || exit;


abstract class OEmbedTinyAPI {


	public function __construct() {
		# Nothing to do here mate !
	}


	/**
	 * Method to parse video links and output the related iframes
	 * Previously in core from 1.6.0 until refactoring in v2
	 *
	 * @access public
	 * @since 2.0.0
	 *
	 * @param Array $ops the options [
	 *   'content'  => String the content to be parsed
	 *   'endpoint' => String the target service endpoint
	 *   'regexp'   => String the regular expression to use
	 * ]
	 * @returns string HTML with Vimeo iframes embed code
	 */
	protected function oembed_service( $ops = [] ) {
		if ( ! isset( $ops[ 'regexp' ] ) || ! isset( $ops[ 'endpoint' ] ) || ! isset( $ops[ 'content' ] ) || empty( $ops[ 'content' ] ) ) :
			return isset( $ops[ 'content' ] ) ? $ops[ 'content' ] : '';
		endif;
		$medias = [];
		$my_content = $ops[ 'content' ];
		preg_match_all( $ops[ 'regexp' ], $my_content, $medias );
		if ( ! isset( $medias ) || ! is_array( $medias ) || count( $medias ) < 1 ) :
			# No video links found
			return $my_content;
		endif;
		return $this->format_medias([
			"medias" => array_unique( $medias[ 0 ] ),
			"endpoint" => $ops[ 'endpoint' ],
			"content" => $ops[ 'content' ]
		]);
	}


	protected function check_url_parts( $my_media = '' ) {
		# From here we assume there are no question mark and only ampersand character
		$tok = strtok( str_replace( '&amp;', '&', $my_media ), '&' ); # Decode & if need be
		$parts = [];
		while ( $tok !== false ) :
			$parts[] = $tok;
			$tok = strtok( '&' );
		endwhile;
		$my_url = array_shift( $parts );
		$my_options = count( $parts ) > 1 ? '&' . implode( '&', $parts ) : '';
		if ( strpos( $my_options, 'width' ) === FALSE ) :
			# We assume there are no width. Set a minimum of 640
			$my_options .= '&width=640';
		endif;
		if ( strpos( $my_options, 'maxwidth' ) === FALSE ) :
			$my_options .= '&maxwidth=640';
		endif;
		return [
			'url' => $my_url,
			'options' => $my_options
		];
	}


	protected function retrieve_media_info( $remote_url ) {
		$response = wp_remote_get( $remote_url );
		if ( is_wp_error( $response ) || ! is_array( $response ) || ! isset( $response[ 'body' ] ) ) :
			return json_decode( '{"error","Error while trying to retrieve info about the following video' . $remote_url . '"}' );
		else :
			return json_decode( $response[ 'body' ] );
		endif;
	}


	protected function format_medias( $ops ) {
		$my_content = $ops[ 'content' ];
		foreach( $ops[ 'medias' ] as $my_media ) :
			if ( empty( $my_media ) ) :
				continue;
			endif;
			$media = $this->check_url_parts( $my_media );
			$data = $this->retrieve_media_info( $ops[ 'endpoint' ] . '?url=' . rawurlencode( $media[ 'url' ] ) . $media[ 'options' ] );
			if ( isset( $data->html ) ) :
				$my_content = preg_replace(
					"#<a href=\"" . preg_quote( $my_media, '#' ) . "\">.*?</a>#u",
					$data->html,
					$my_content
				);
			elseif ( isset( $data->error ) ) :
				error_log( "\nWP Markup Markdown: " . $data->error );
			endif;
		endforeach;
		return $my_content;
	}


}

