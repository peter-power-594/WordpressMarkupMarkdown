<?php

namespace MarkupMarkdown\Addons\Released\Media;

defined( 'ABSPATH' ) || exit;

class ToolbarEasyMDE {


	protected $prop = array(
		"default_buttons" => array(
			"pipe" => array(
				"action" => "none",
				"tooltip" => "",
				"label" => "Pipe",
				"icon" => "<i class=\"fa fa-pipe\" aria-hidden=\"true\"></i>"
			),
			"bold" => array(
				"action" => "toggleBold",
				"tooltip" => "Bold",
				"label" => "Bold",
				"icon" => "<i class=\"fa fa-bold\" aria-hidden=\"true\"></i>"
			),
			"italic" => array(
				"action" => "toggleItalic",
				"tooltip" => "Italic",
				"label" => "Italic",
				"icon" => "<i class=\"fa fa-italic\" aria-hidden=\"true\"></i>"
			),
			"strikethrough" => array(
				"action" => "toggleStrikethrough",
				"tooltip" => "Strikethrough",
				"label" => "Strikethrough",
				"icon" => "<i class=\"fa fa-strikethrough\" aria-hidden=\"true\"></i>"
			),
			"heading" => array(
				"action" => "toggleHeading",
				"tooltip" => "Heading",
				"label" => "Heading",
				"icon" => "<i class=\"fa fa-header fa-heading\" aria-hidden=\"true\"></i>"
			),
			"heading_smaller" => array(
				"action" => "toggleHeadingSmaller",
				"tooltip" => "Heading",
				"label" => "Heading",
				"icon" => "<i class=\"fa fa-header fa-heading\" aria-hidden=\"true\"></i>"
			),
			"heading_bigger" => array(
				"action" => "toggleHeadingBigger",
				"tooltip" => "Bigger Heading",
				"label" => "Bigger Heading",
				"icon" => "<i class=\"fa fa-lg fa-header fa-heading\" aria-hidden=\"true\"></i>"
			),
			"heading_1" => array(
				"action" => "toggleHeading1",
				"tooltip" => "Big Heading",
				"label" => "Big Heading",
				"icon" => "<i class=\"fa fa-header fa-heading\" aria-hidden=\"true\"></i>"
			),
			"heading_2" => array(
				"action" => "toggleHeading2",
				"tooltip" => "Medium Heading",
				"label" => "Medium Heading",
				"icon" => "<i class=\"fa fa-header fa-heading\" aria-hidden=\"true\"></i>"
			),
			"heading_3" => array(
				"action" => "toggleHeading3",
				"tooltip" => "Small Heading",
				"label" => "Small Heading",
				"icon" => "<i class=\"fa fa-header fa-heading\" aria-hidden=\"true\"></i>"
			),
			"code" => array(
				"action" => "toggleCodeBlock",
				"tooltip" => "Code",
				"label" => "Code",
				"icon" => "<i class=\"fa fa-code\" aria-hidden=\"true\"></i>"
			),
			"quote" => array(
				"action" => "toggleBlockquote",
				"tooltip" => "Quote",
				"label" => "Quote",
				"icon" => "<i class=\"fa fa-quote-left\" aria-hidden=\"true\"></i>"
			),
			"unordered_list" => array(
				"action" => "toggleGenericList",
				"tooltip" => "Generic List",
				"label" => "List",
				"icon" => "<i class=\"fa fa-list-ul\" aria-hidden=\"true\"></i>"
			),
			"ordered_list" => array(
				"action" => "toggleOrderedList",
				"tooltip" => "Numbered List",
				"label" => "List",
				"icon" => "<i class=\"fa fa-list-ol\" aria-hidden=\"true\"></i>"
			),
			"clean_block" => array(
				"action" => "cleanBlock",
				"tooltip" => "Clean block",
				"label" => "List",
				"icon" => "<i class=\"fa fa-eraser\" aria-hidden=\"true\"></i>"
			),
			"link" => array(
				"action" => "drawLink",
				"tooltip" => "Create Link",
				"label" => "Link",
				"icon" => "<i class=\"fa fa-link\" aria-hidden=\"true\"></i>"
			),
			"wpsimage" => array( # image
				"action" => "WPLibraryHandler",
				"tooltip" => "Insert or Upload Media",
				"label" => "Media Library",
				"icon" => "<i class=\"fa fa-images\" aria-hidden=\"true\"></i>"
			),/*
			"upload_image" => array(
				"action" => "drawUploadedImage",
				"tooltip" => "Raise browse-file window",
				"label" => "Upload",
				"icon" => "<i class=\"fa fa-images\" aria-hidden=\"true\"></i>"
			),*/
			"table" => array(
				"action" => "drawTable",
				"tooltip" => "Insert Table",
				"label" => "Table",
				"icon" => "<i class=\"fa fa-table\" aria-hidden=\"true\"></i>"
			),
			"horizontal_rule" => array(
				"action" => "drawHorizontalRule",
				"tooltip" => "Insert Horizontal Line",
				"label" => "Horizontal Line",
				"icon" => "<i class=\"fa fa-minus\" aria-hidden=\"true\"></i>"
			),
			"preview" => array(
				"action" => "togglePreview",
				"tooltip" => "Toggle Preview",
				"label" => "Preview",
				"icon" => "<i class=\"fa fa-eye no-disable\" aria-hidden=\"true\"></i>"
			),
			"side_by_side" => array(
				"action" => "toggleSideBySide",
				"tooltip" => "Toggle Side by Side",
				"label" => "Side by Side",
				"icon" => "<i class=\"fa fa-columns no-disable no-mobile\" aria-hidden=\"true\"></i>"
			),
			"fullscreen" => array(
				"action" => "toggleFullScreen",
				"tooltip" => "Toggle Fullscreen",
				"label" => "Fullscreen",
				"icon" => "<i class=\"fa fa-arrows-alt no-disable no-mobile\" aria-hidden=\"true\"></i>"
			),
			"guide" => array(
				"action" => "This link",
				"tooltip" => "Markdown Guide",
				"label" => "Guide",
				"icon" => "<i class=\"fa fa-question-circle\" aria-hidden=\"true\"></i>"
			),
			"undo" => array(
				"action" => "undo",
				"tooltip" => "Undo",
				"label" => "Undo",
				"icon" => "<i class=\"fa fa-undo\" aria-hidden=\"true\"></i>"
			),
			"redo" => array(
				"action" => "redo",
				"tooltip" => "Redo",
				"label" => "Redo",
				"icon" => "<i class=\"fa fa-redo\" aria-hidden=\"true\"></i>"
			),
			"spell_check" => array(
				"action" => "spellcheck",
				"tooltip" => "Spellchecker",
				"label" => "Spellchecker",
				"icon" => "<i class=\"fa fa-globe\" aria-hidden=\"true\"></i>"
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
			$toolbar_conf = [ "bold", "italic", "heading", "spell_check", "pipe", "quote", "unordered_list", "ordered_list", "pipe", "link", "wpsimage", "table", "pipe", "guide", "preview" ];
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
