/* global jQuery */
(function( _win, $ ) {

	function mmd_initialize_field() {
		if ( ! _win.MarkupMarkdown || typeof _win.MarkupMarkdown !== "function" ) {
			return false;
		}
		var $textarea = $( 'textarea[name="group-desc"], textarea[name="whats-new"]' );
		// The _CodeMirrorSpellCheckerReady_ event can be triggered multiple times
		document.addEventListener( 'CodeMirrorSpellCheckerReady', function() {
			$textarea.each(function() {
				$textarea.focus();
				var $bpFormField = $textarea.parent();
				if ( $bpFormField.length && ! $bpFormField.hasClass( 'ready' ) ) {
					$bpFormField.addClass( 'ready' );
					new MarkupMarkdown( this );
				}
			});
		});
	}

	mmd_initialize_field();


})( window, window.jQuery );
