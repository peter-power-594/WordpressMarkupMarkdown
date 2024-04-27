(function( $, acf ) {

	function mmd_initialize_field( field ) {
		if ( ! field || ! window.MarkupMarkdown || typeof window.MarkupMarkdown !== "function" ) {
			return false;
		}
		var $field = $( field ),
			$textarea = $field.find( 'textarea:eq(0)' );
		// Warning: set the binder BEFORE calling the MarkupMarkdown class
		document.addEventListener( 'CodeMirrorSpellCheckerReady', function() {
			var $acfInputField = $textarea.closest( '.acf-input' );
			if ( $acfInputField.length && ! $acfInputField.hasClass( 'ready' ) ) {
				$acfInputField.addClass( 'ready' );
			}
		});
		new MarkupMarkdown( $textarea );
	}

	if( acf && typeof acf.add_action !== 'undefined' ) {
		// Run initialize_field when existing fields of this type load,
		acf.add_action( 'ready_field/type=markupmarkdown', mmd_initialize_field );
		// new fields are appended via repeaters or similar
		acf.add_action( 'append_field/type=markupmarkdown', mmd_initialize_field );
	}


})( window.jQuery, window.acf );
