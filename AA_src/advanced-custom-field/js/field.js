/* global jQuery, acf */
(function( _win, $, acf ) {

	function mmd_initialize_field( field ) {
		if ( ! field || ! _win.MarkupMarkdown || typeof _win.MarkupMarkdown !== "function" ) {
			return false;
		}
		var $field = $( field ),
			$textarea = $field.find( 'textarea:eq(0)' );
		// The _CodeMirrorSpellCheckerReady_ event can be triggered multiple times
		document.addEventListener( 'CodeMirrorSpellCheckerReady', function() {
			var $acfInputField = $textarea.closest( '.acf-input' );
			if ( $acfInputField.length && ! $acfInputField.hasClass( 'ready' ) ) {
				new MarkupMarkdown( $textarea );
				$acfInputField.addClass( 'ready' );
			}
		});
	}

	if( acf && typeof acf.add_action !== 'undefined' ) {
		// Run initialize_field when existing fields of this type load,
		acf.add_action( 'ready_field/type=markupmarkdown', mmd_initialize_field );
		// new fields are appended via repeaters or similar
		acf.add_action( 'append_field/type=markupmarkdown', mmd_initialize_field );
	}


})( window, window.jQuery, window.acf );
