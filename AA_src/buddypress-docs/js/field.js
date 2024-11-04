/* global jQuery */
(function( _win, $ ) {

	function mmd_initialize_field() {
		if ( ! _win.MarkupMarkdown || typeof _win.MarkupMarkdown !== "function" ) {
			return false;
		}
		var $textarea = $( '#doc_content' );
		// The _CodeMirrorSpellCheckerReady_ event can be triggered multiple times
		document.addEventListener( 'CodeMirrorSpellCheckerReady', function() {
			$textarea.each(function() {
				$textarea.focus();
				var $myFormField = $textarea.parent();
				if ( $myFormField.length && ! $myFormField.hasClass( 'ready' ) ) {
					$myFormField.addClass( 'ready' );
					new MarkupMarkdown( this );
				}
			});
		});
	}


	mmd_initialize_field();


})( window, window.jQuery );
