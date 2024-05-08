<?php

namespace MarkupMarkdown\Addons\Released\Media;

defined( 'ABSPATH' ) || exit;

class ToolbarEasyMDE {


	protected $prop = array(
		"default_buttons" => array(
			"pipe" => array(
				"action"  => "none",
				"tooltip" => "",
				"label"   => __( 'Pipe', 'markup-markdown' ),
				"icon"    => "<i class=\"fa fa-pipe\" aria-hidden=\"true\"></i>"
			),
			"bold" => array(
				"action"  => "toggleBold",
				"tooltip" => __( 'Bold', 'markup-markdown' ),
				"label"   => __( 'Bold', 'markup-markdown' ),
				"icon"    => "<i class=\"fa fa-bold\" aria-hidden=\"true\"></i>"
			),
			"italic" => array(
				"action"  => "toggleItalic",
				"tooltip" => __( 'Italic', 'markup-markdown' ),
				"label"   => __( 'Italic', 'markup-markdown' ),
				"icon"    => "<i class=\"fa fa-italic\" aria-hidden=\"true\"></i>"
			),
			"strikethrough" => array(
				"action"  => "toggleStrikethrough",
				"tooltip" => __( 'Strikethrough', 'markup-markdown' ),
				"label"   => __( 'Strikethrough', 'markup-markdown' ),
				"icon"    => "<i class=\"fa fa-strikethrough\" aria-hidden=\"true\"></i>"
			),
			"heading" => array(
				"action"  => "toggleHeading",
				"tooltip" => __( 'Heading', 'markup-markdown' ),
				"label"   => __( 'Heading', 'markup-markdown' ),
				"icon"    => "<i class=\"fa fa-header fa-heading\" aria-hidden=\"true\"></i>"
			),
			"heading_smaller" => array(
				"action"  => "toggleHeadingSmaller",
				"tooltip" => __( 'Smaller Heading', 'markup-markdown' ),
				"label"   => __( 'Smaller Heading', 'markup-markdown' ),
				"icon"    => "<i class=\"fa fa-header fa-heading\" aria-hidden=\"true\"></i>"
			),
			"heading_bigger" => array(
				"action"  => "toggleHeadingBigger",
				"tooltip" => __( 'Bigger Heading', 'markup-markdown' ),
				"label"   => __( 'Bigger Heading', 'markup-markdown' ),
				"icon"    => "<i class=\"fa fa-lg fa-header fa-heading\" aria-hidden=\"true\"></i>"
			),
			"heading_1" => array(
				"action"  => "toggleHeading1",
				"tooltip" => __( 'Big Heading', 'markup-markdown' ),
				"label"   => __( 'Big Heading', 'markup-markdown' ),
				"icon"    => "<i class=\"fa fa-header fa-heading\" aria-hidden=\"true\"></i>"
			),
			"heading_2" => array(
				"action"  => "toggleHeading2",
				"tooltip" => __( 'Medium Heading', 'markup-markdown' ),
				"label"   => __( 'Medium Heading', 'markup-markdown' ),
				"icon"    => "<i class=\"fa fa-header fa-heading\" aria-hidden=\"true\"></i>"
			),
			"heading_3" => array(
				"action"  => "toggleHeading3",
				"tooltip" => __( 'Small Heading', 'markup-markdown' ),
				"label"   => __( 'Small Heading', 'markup-markdown' ),
				"icon"    => "<i class=\"fa fa-header fa-heading\" aria-hidden=\"true\"></i>"
			),
			"code" => array(
				"action"  => "toggleCodeBlock",
				"tooltip" => __( 'Code', 'markup-markdown' ),
				"label"   => __( 'Code', 'markup-markdown' ),
				"icon"    => "<i class=\"fa fa-code\" aria-hidden=\"true\"></i>"
			),
			"quote" => array(
				"action"  => "toggleBlockquote",
				"tooltip" => __( 'Quote', 'markup-markdown' ),
				"label"   => __( 'Quote', 'markup-markdown' ),
				"icon"    => "<i class=\"fa fa-quote-left\" aria-hidden=\"true\"></i>"
			),
			"unordered_list" => array(
				"action"  => "toggleGenericList",
				"tooltip" => __( 'Generic List', 'markup-markdown' ),
				"label"   => __( 'List', 'markup-markdown' ),
				"icon"    => "<i class=\"fa fa-list-ul\" aria-hidden=\"true\"></i>"
			),
			"ordered_list" => array(
				"action"  => "toggleOrderedList",
				"tooltip" => __( 'Numbered List', 'markup-markdown' ),
				"label"   => __( 'List', 'markup-markdown' ),
				"icon"    => "<i class=\"fa fa-list-ol\" aria-hidden=\"true\"></i>"
			),
			"clean_block" => array(
				"action"  => "cleanBlock",
				"tooltip" => __( 'Clean block', 'markup-markdown' ),
				"label"   => __( 'Clean', 'markup-markdown' ),
				"icon"    => "<i class=\"fa fa-eraser\" aria-hidden=\"true\"></i>"
			),
			"link" => array(
				"action"  => "drawLink",
				"tooltip" => __( 'Create Link', 'markup-markdown' ),
				"label"   => __( 'Link', 'markup-markdown' ),
				"icon"    => "<i class=\"fa fa-link\" aria-hidden=\"true\"></i>"
			),
			"wpsimage" => array( # image
				"action"  => "WPLibraryHandler",
				"tooltip" => __( 'Insert or Upload Media', 'markup-markdown' ),
				"label"   => __( 'Media Library', 'markup-markdown' ),
				"icon"    => "<i class=\"fa fa-images\" aria-hidden=\"true\"></i>"
			),
			"table" => array(
				"action"  => "drawTable",
				"tooltip" => __( 'Insert Table', 'markup-markdown' ),
				"label"   => __( 'Table', 'markup-markdown' ),
				"icon"    => "<i class=\"fa fa-table\" aria-hidden=\"true\"></i>"
			),
			"horizontal_rule" => array(
				"action"  => "drawHorizontalRule",
				"tooltip" => __( 'Insert Horizontal Line', 'markup-markdown' ),
				"label"   => __( 'Horizontal Line', 'markup-markdown' ),
				"icon"    => "<i class=\"fa fa-minus\" aria-hidden=\"true\"></i>"
			),
			"preview" => array(
				"action"  => "togglePreview",
				"tooltip" => __( 'Toggle Preview', 'markup-markdown' ),
				"label"   => __( 'Preview', 'markup-markdown' ),
				"icon"    => "<i class=\"fa fa-eye no-disable\" aria-hidden=\"true\"></i>"
			),
			"side_by_side" => array(
				"action"  => "toggleSideBySide",
				"tooltip" => __( 'Toggle Side by Side', 'markup-markdown' ),
				"label"   => __( 'Side by Side', 'markup-markdown' ),
				"icon"    => "<i class=\"fa fa-columns no-disable no-mobile\" aria-hidden=\"true\"></i>"
			),
			"fullscreen" => array(
				"action"  => "toggleFullScreen",
				"tooltip" => __( 'Toggle Fullscreen', 'markup-markdown' ),
				"label"   => __( 'Fullscreen', 'markup-markdown' ),
				"icon"    => "<i class=\"fa fa-arrows-alt no-disable no-mobile\" aria-hidden=\"true\"></i>"
			),
			"guide" => array(
				"action"  => "This link",
				"tooltip" => __( 'Markdown Guide', 'markup-markdown' ),
				"label"   => __( 'Guide', 'markup-markdown' ),
				"icon"    => "<i class=\"fa fa-question-circle\" aria-hidden=\"true\"></i>"
			),
			"undo" => array(
				"action"  => "undo",
				"tooltip" => __( 'Undo', 'markup-markdown' ),
				"label"   => __( 'Undo', 'markup-markdown' ),
				"icon"    => "<i class=\"fa fa-undo\" aria-hidden=\"true\"></i>"
			),
			"redo" => array(
				"action"  => "redo",
				"tooltip" => __( 'Redo', 'markup-markdown' ),
				"label"   => __( 'Redo', 'markup-markdown' ),
				"icon"    => "<i class=\"fa fa-redo\" aria-hidden=\"true\"></i>"
			),
			"spell_check" => array(
				"action"  => "spellcheck",
				"tooltip" => __( 'Spellchecker', 'markup-markdown' ),
				"label"   => __( 'Spellchecker', 'markup-markdown' ),
				"icon"    => "<i class=\"fa fa-globe\" aria-hidden=\"true\"></i>"
			)
		),
		"unused_buttons" => array(),
		"active_buttons" => array()
	);


	private function logger( $str = '' ) {
		if ( ! empty( $str ) ) :
			error_log( "\nWP Markup Markdown: " . $str );
		endif;
	}


	public function __construct( $json = '' ) {
		if ( empty( $json ) ) :
			return FALSE;
		endif;
		if ( ! file_exists( $json ) ) :
			$toolbar_conf = [ "bold", "italic", "heading", "spell_check", "pipe", "quote", "unordered_list", "ordered_list", "pipe", "link", "wpsimage", "table", "pipe", "fullscreen", "side_by_side", "preview", "guide" ];
			file_put_contents( $json, '{"my_buttons":' . json_encode( $toolbar_conf ) . '}' );
		endif;
		mmd()->clear_cache( $json );
		$toolbar_conf = json_decode( file_get_contents( $json ) );
		$this->logger( ( ! isset( $toolbar_conf ) || ! $toolbar_conf ) ? "Unable to read the json file " . $json : '' );
		foreach ( $toolbar_conf->my_buttons as $button_slug ) :
			if ( ! in_array( $button_slug, $this->prop[ 'active_buttons' ] ) && isset( $this->prop[ 'default_buttons' ][ $button_slug ] ) ) :
				$this->prop[ 'active_buttons' ][] = array_merge( [ "slug" => $button_slug ], $this->prop[ 'default_buttons' ][ $button_slug ] );
			endif;
		endforeach;
		foreach ( $this->prop[ 'default_buttons' ] as $button_slug => $button_prop ) :
			if ( ! in_array( $button_slug, $toolbar_conf->my_buttons ) ) :
				$this->prop[ 'unused_buttons' ][] = array_merge( [ "slug" => $button_slug ], $button_prop );
			endif;
		endforeach;
	}


	public function __get( $name ) {
		return array_key_exists( $name, $this->prop ) ? $this->prop[ $name ] : 'mmd_undefined';
	}

}
