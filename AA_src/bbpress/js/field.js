/* global jQuery */
(function( _win, $ ) {

	function mmd_initialize_field() {
		if ( ! _win.MarkupMarkdown || typeof _win.MarkupMarkdown !== "function" ) {
			return false;
		}
		var $textarea = $( '#bbp_topic_content, #bbp_reply_content' );
		// The _CodeMirrorSpellCheckerReady_ event can be triggered multiple times
		document.addEventListener( 'CodeMirrorSpellCheckerReady', function() {
			var $bbpressFormField = $textarea.parent();
			if ( $bbpressFormField.length && ! $bbpressFormField.hasClass( 'ready' ) ) {
				$bbpressFormField.addClass( 'ready' );
				new MarkupMarkdown( $textarea );
			}
		});
	}

	mmd_initialize_field();


})( window, window.jQuery );
