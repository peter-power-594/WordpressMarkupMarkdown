<?php

namespace MarkupMarkdown\Addons\Released\Media;

defined( 'ABSPATH' ) || exit;

class ToolbarEasyMDE {


	protected $prop = array(
		"default_buttons" => array(
			"mmd_pipe" => array(
				"action"  => "none",
				"icon"    => "<i class=\"fa fa-pipe\" aria-hidden=\"true\"></i>"
			),
			"mmd_bold" => array(
				"action"  => "toggleBold",
				"icon"    => "<i class=\"fa fa-bold\" aria-hidden=\"true\"></i>"
			),
			"mmd_italic" => array(
				"action"  => "toggleItalic",
				"icon"    => "<i class=\"fa fa-italic\" aria-hidden=\"true\"></i>"
			),
			"mmd_strikethrough" => array(
				"action"  => "toggleStrikethrough",
				"icon"    => "<i class=\"fa fa-strikethrough\" aria-hidden=\"true\"></i>"
			),
			"mmd_heading" => array(
				"action"  => "toggleHeading",
				"icon"    => "<i class=\"fa fa-header fa-heading\" aria-hidden=\"true\"></i>"
			),
			"mmd_heading_smaller" => array(
				"action"  => "toggleHeadingSmaller",
				"icon"    => "<i class=\"fa fa-header fa-heading\" aria-hidden=\"true\"></i>"
			),
			"mmd_heading_bigger" => array(
				"action"  => "toggleHeadingBigger",
				"icon"    => "<i class=\"fa fa-lg fa-header fa-heading\" aria-hidden=\"true\"></i>"
			),
			"mmd_heading_1" => array(
				"action"  => "toggleHeading1",
				"icon"    => "<i class=\"fa fa-header fa-heading\" aria-hidden=\"true\"></i>"
			),
			"mmd_heading_2" => array(
				"action"  => "toggleHeading2",
				"icon"    => "<i class=\"fa fa-header fa-heading\" aria-hidden=\"true\"></i>"
			),
			"mmd_heading_3" => array(
				"action"  => "toggleHeading3",
				"icon"    => "<i class=\"fa fa-header fa-heading\" aria-hidden=\"true\"></i>"
			),
			"mmd_code" => array(
				"action"  => "toggleCodeBlock",
				"icon"    => "<i class=\"fa fa-code\" aria-hidden=\"true\"></i>"
			),
			"mmd_quote" => array(
				"action"  => "toggleBlockquote",
				"icon"    => "<i class=\"fa fa-quote-left\" aria-hidden=\"true\"></i>"
			),
			"mmd_unordered_list" => array(
				"action"  => "toggleGenericList",
				"icon"    => "<i class=\"fa fa-list-ul\" aria-hidden=\"true\"></i>"
			),
			"mmd_ordered_list" => array(
				"action"  => "toggleOrderedList",
				"icon"    => "<i class=\"fa fa-list-ol\" aria-hidden=\"true\"></i>"
			),
			"mmd_clean_block" => array(
				"action"  => "cleanBlock",
				"icon"    => "<i class=\"fa fa-eraser\" aria-hidden=\"true\"></i>"
			),
			"mmd_link" => array(
				"action"  => "drawLink",
				"icon"    => "<i class=\"fa fa-link\" aria-hidden=\"true\"></i>"
			),
			"mmd_wpsimage" => array( # image
				"action"  => "WPLibraryHandler",
				"icon"    => "<i class=\"fa fa-images\" aria-hidden=\"true\"></i>"
			),
			"mmd_table" => array(
				"action"  => "drawTable",
				"icon"    => "<i class=\"fa fa-table\" aria-hidden=\"true\"></i>"
			),
			"mmd_horizontal_rule" => array(
				"action"  => "drawHorizontalRule",
				"icon"    => "<i class=\"fa fa-minus\" aria-hidden=\"true\"></i>"
			),
			"mmd_preview" => array(
				"action"  => "togglePreview",
				"icon"    => "<i class=\"fa fa-eye no-disable\" aria-hidden=\"true\"></i>"
			),
			"mmd_side_by_side" => array(
				"action"  => "toggleSideBySide",
				"icon"    => "<i class=\"fa fa-columns no-disable no-mobile\" aria-hidden=\"true\"></i>"
			),
			"mmd_fullscreen" => array(
				"action"  => "toggleFullScreen",
				"icon"    => "<i class=\"fa fa-arrows-alt no-disable no-mobile\" aria-hidden=\"true\"></i>"
			),
			"mmd_guide" => array(
				"action"  => "This link",
				"icon"    => "<i class=\"fa fa-question-circle\" aria-hidden=\"true\"></i>"
			),
			"mmd_undo" => array(
				"action"  => "undo",
				"icon"    => "<i class=\"fa fa-undo\" aria-hidden=\"true\"></i>"
			),
			"mmd_redo" => array(
				"action"  => "redo",
				"icon"    => "<i class=\"fa fa-redo\" aria-hidden=\"true\"></i>"
			),
			"mmd_spell_check" => array(
				"action"  => "spellcheck",
				"icon"    => "<i class=\"fa fa-globe\" aria-hidden=\"true\"></i>"
			),
			"mmd_rtltextdir" => array(
				"action"  => "textdir",
				"icon"    => "<i class=\"far fa-caret-square-left\" aria-hidden=\"true\"></i>"
			),
			"mmd_ltrtextdir" => array(
				"action"  => "textdir",
				"icon"    => "<i class=\"far fa-caret-square-right\" aria-hidden=\"true\"></i>"
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
		$this->prop[ 'default_buttons' ][ 'mmd_pipe' ][ 'tooltip' ] = '';
		$this->prop[ 'default_buttons' ][ 'mmd_pipe' ][ 'label' ] = esc_html__( 'Pipe', 'markup-markdown' );
		$this->prop[ 'default_buttons' ][ 'mmd_bold' ][ 'tooltip' ] = esc_html__( 'Bold', 'markup-markdown' );
		$this->prop[ 'default_buttons' ][ 'mmd_bold' ][ 'label' ] = esc_html__( 'Bold', 'markup-markdown' );
		$this->prop[ 'default_buttons' ][ 'mmd_italic' ][ 'tooltip' ] = esc_html__( 'Italic', 'markup-markdown' );
		$this->prop[ 'default_buttons' ][ 'mmd_italic' ][ 'label' ] = esc_html__( 'Italic', 'markup-markdown' );
		$this->prop[ 'default_buttons' ][ 'mmd_strikethrough' ][ 'tooltip' ] = esc_html__( 'Strikethrough', 'markup-markdown' );
		$this->prop[ 'default_buttons' ][ 'mmd_strikethrough' ][ 'label' ] = esc_html__( 'Strikethrough', 'markup-markdown' );
		$this->prop[ 'default_buttons' ][ 'mmd_heading' ][ 'tooltip' ] = esc_html__( 'Heading', 'markup-markdown' );
		$this->prop[ 'default_buttons' ][ 'mmd_heading' ][ 'label' ] = esc_html__( 'Heading', 'markup-markdown' );
		$this->prop[ 'default_buttons' ][ 'mmd_heading_smaller' ][ 'tooltip' ] = esc_html__( 'Smaller Heading', 'markup-markdown' );
		$this->prop[ 'default_buttons' ][ 'mmd_heading_smaller' ][ 'label' ] = esc_html__( 'Smaller Heading', 'markup-markdown' );
		$this->prop[ 'default_buttons' ][ 'mmd_heading_bigger' ][ 'tooltip' ] = esc_html__( 'Bigger Heading', 'markup-markdown' );
		$this->prop[ 'default_buttons' ][ 'mmd_heading_bigger' ][ 'label' ] = esc_html__( 'Bigger Heading', 'markup-markdown' );
		$this->prop[ 'default_buttons' ][ 'mmd_heading_1' ][ 'tooltip' ] = esc_html__( 'Big Heading', 'markup-markdown' );
		$this->prop[ 'default_buttons' ][ 'mmd_heading_1' ][ 'label' ] = esc_html__( 'Big Heading', 'markup-markdown' );
		$this->prop[ 'default_buttons' ][ 'mmd_heading_2' ][ 'tooltip' ] = esc_html__( 'Medium Heading', 'markup-markdown' );
		$this->prop[ 'default_buttons' ][ 'mmd_heading_2' ][ 'label' ] = esc_html__( 'Medium Heading', 'markup-markdown' );
		$this->prop[ 'default_buttons' ][ 'mmd_heading_3' ][ 'tooltip' ] = esc_html__( 'Small Heading', 'markup-markdown' );
		$this->prop[ 'default_buttons' ][ 'mmd_heading_3' ][ 'label' ] = esc_html__( 'Small Heading', 'markup-markdown' );
		$this->prop[ 'default_buttons' ][ 'mmd_code' ][ 'tooltip' ] = esc_html__( 'Code', 'markup-markdown' );
		$this->prop[ 'default_buttons' ][ 'mmd_code' ][ 'label' ] = esc_html__( 'Code', 'markup-markdown' );
		$this->prop[ 'default_buttons' ][ 'mmd_quote' ][ 'tooltip' ] = esc_html__( 'Quote', 'markup-markdown' );
		$this->prop[ 'default_buttons' ][ 'mmd_quote' ][ 'label' ] = esc_html__( 'Quote', 'markup-markdown' );
		$this->prop[ 'default_buttons' ][ 'mmd_unordered_list' ][ 'tooltip' ] = esc_html__( 'Generic List', 'markup-markdown' );
		$this->prop[ 'default_buttons' ][ 'mmd_unordered_list' ][ 'label' ] = esc_html__( 'List', 'markup-markdown' );
		$this->prop[ 'default_buttons' ][ 'mmd_ordered_list' ][ 'tooltip' ] = esc_html__( 'Numbered List', 'markup-markdown' );
		$this->prop[ 'default_buttons' ][ 'mmd_ordered_list' ][ 'label' ] = esc_html__( 'List', 'markup-markdown' );
		$this->prop[ 'default_buttons' ][ 'mmd_clean_block' ][ 'tooltip' ] = esc_html__( 'Clean block', 'markup-markdown' );
		$this->prop[ 'default_buttons' ][ 'mmd_clean_block' ][ 'label' ] = esc_html__( 'Clean', 'markup-markdown' );
		$this->prop[ 'default_buttons' ][ 'mmd_link' ][ 'tooltip' ] = esc_html__( 'Create Link', 'markup-markdown' );
		$this->prop[ 'default_buttons' ][ 'mmd_link' ][ 'label' ] = esc_html__( 'Link', 'markup-markdown' );
		$this->prop[ 'default_buttons' ][ 'mmd_wpsimage' ][ 'tooltip' ] = esc_html__( 'Insert or Upload Media', 'markup-markdown' );
		$this->prop[ 'default_buttons' ][ 'mmd_wpsimage' ][ 'label' ] = esc_html__( 'Media Library', 'markup-markdown' );
		$this->prop[ 'default_buttons' ][ 'mmd_table' ][ 'tooltip' ] = esc_html__( 'Insert Table', 'markup-markdown' );
		$this->prop[ 'default_buttons' ][ 'mmd_table' ][ 'label' ] = esc_html__( 'Table', 'markup-markdown' );
		$this->prop[ 'default_buttons' ][ 'mmd_horizontal_rule' ][ 'tooltip' ] = esc_html__( 'Insert Horizontal Line', 'markup-markdown' );
		$this->prop[ 'default_buttons' ][ 'mmd_horizontal_rule' ][ 'label' ] = esc_html__( 'Horizontal Line', 'markup-markdown' );
		$this->prop[ 'default_buttons' ][ 'mmd_preview' ][ 'tooltip' ] = esc_html__( 'Toggle Preview', 'markup-markdown' );
		$this->prop[ 'default_buttons' ][ 'mmd_preview' ][ 'label' ] = esc_html__( 'Preview', 'markup-markdown' );
		$this->prop[ 'default_buttons' ][ 'mmd_side_by_side' ][ 'tooltip' ] = esc_html__( 'Toggle Side by Side', 'markup-markdown' );
		$this->prop[ 'default_buttons' ][ 'mmd_side_by_side' ][ 'label' ] = esc_html__( 'Side by Side', 'markup-markdown' );
		$this->prop[ 'default_buttons' ][ 'mmd_fullscreen' ][ 'tooltip' ] = esc_html__( 'Toggle Fullscreen', 'markup-markdown' );
		$this->prop[ 'default_buttons' ][ 'mmd_fullscreen' ][ 'label' ] = esc_html__( 'Fullscreen', 'markup-markdown' );
		$this->prop[ 'default_buttons' ][ 'mmd_guide' ][ 'tooltip' ] = esc_html__( 'Markdown Guide', 'markup-markdown' );
		$this->prop[ 'default_buttons' ][ 'mmd_guide' ][ 'label' ] = esc_html__( 'Guide', 'markup-markdown' );
		$this->prop[ 'default_buttons' ][ 'mmd_undo' ][ 'tooltip' ] = esc_html__( 'Undo', 'markup-markdown' );
		$this->prop[ 'default_buttons' ][ 'mmd_undo' ][ 'label' ] = esc_html__( 'Undo', 'markup-markdown' );
		$this->prop[ 'default_buttons' ][ 'mmd_redo' ][ 'tooltip' ] = esc_html__( 'Redo', 'markup-markdown' );
		$this->prop[ 'default_buttons' ][ 'mmd_redo' ][ 'label' ] = esc_html__( 'Redo', 'markup-markdown' );
		$this->prop[ 'default_buttons' ][ 'mmd_spell_check' ][ 'tooltip' ] = esc_html__( 'Spellchecker', 'markup-markdown' );
		$this->prop[ 'default_buttons' ][ 'mmd_spell_check' ][ 'label' ] = esc_html__( 'Spellchecker', 'markup-markdown' );
		$this->prop[ 'default_buttons' ][ 'mmd_rtltextdir' ][ 'tooltip' ] = esc_html__( 'Switch text direction to right', 'markup-markdown' );
		$this->prop[ 'default_buttons' ][ 'mmd_rtltextdir' ][ 'label' ] = esc_html__( 'Right to Left text direction', 'markup-markdown' );
		$this->prop[ 'default_buttons' ][ 'mmd_ltrtextdir' ][ 'tooltip' ] = esc_html__( 'Switch text direction to left', 'markup-markdown' );
		$this->prop[ 'default_buttons' ][ 'mmd_ltrtextdir' ][ 'label' ] = esc_html__( 'Left to Right text direction', 'markup-markdown' );
		if ( empty( $json ) ) :
			return false;
		endif;
		if ( ! file_exists( $json ) ) :
			$toolbar_conf = [ "mmd_bold", "mmd_italic", "mmd_heading", "mmd_spell_check", "mmd_pipe", "mmd_quote", "mmd_unordered_list", "mmd_ordered_list", "mmd_pipe", "mmd_link", "mmd_wpsimage", "mmd_table", "mmd_pipe", "mmd_fullscreen", "mmd_side_by_side", "mmd_preview", "mmd_guide" ];
			file_put_contents( $json, '{"my_buttons":' . json_encode( $toolbar_conf ) . '}' );
		endif;
		mmd()->clear_cache( $json );
		$toolbar_conf = json_decode( file_get_contents( $json ) );
		$this->logger( ( ! isset( $toolbar_conf ) || ! $toolbar_conf ) ? "Unable to read the json file " . $json : '' );
		foreach ( $toolbar_conf->my_buttons as $idx => $button_slug ) :
			if ( strpos( $button_slug, "mmd_" ) === FALSE ) :
				$toolbar_conf->my_buttons[ $idx ] = "mmd_" . $button_slug;
			endif;
		endforeach;
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
		# Add a few pipes
		$this->prop[ 'unused_buttons' ][] = array( "slug" => "pipe" );
		$this->prop[ 'unused_buttons' ][] = array( "slug" => "pipe" );
		$this->prop[ 'unused_buttons' ][] = array( "slug" => "pipe" );
	}


	public function __get( $name ) {
		return array_key_exists( $name, $this->prop ) ? $this->prop[ $name ] : 'mmd_undefined';
	}

}
