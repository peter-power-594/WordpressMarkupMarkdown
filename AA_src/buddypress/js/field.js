/* global jQuery */
(function( _win, $ ) {

	function mmd_initialize_field() {
		if ( ! _win.MarkupMarkdown || typeof _win.MarkupMarkdown !== "function" ) {
			return false;
		}
		var $textarea = $( 'textarea[name="group-desc"], textarea[name="whats-new"], textarea[name="bp-groups-description"], textarea[name="bp-activities-content"], textarea[name="message_content"]' );
		// The _CodeMirrorSpellCheckerReady_ event can be triggered multiple times
		document.addEventListener( 'CodeMirrorSpellCheckerReady', function() {
			$textarea.each(function() {
				$textarea.focus();
				var $bpFormField = $textarea.parent();
				if ( $bpFormField.length && ! $bpFormField.hasClass( 'ready' ) ) {
					$bpFormField.addClass( 'ready' );
					new MarkupMarkdown( this );
					if ( ! _win.tinyMCE ) {
						simulateTinyMCE();
					}
				}
			});
		});
	}


	function simulateTinyMCE() {
		_win.tinyMCE = _win.tinyMCE || {};
		_win.tinyMCE.activeEditor = {};
		_win.tinyMCE.activeEditor.getContent = function() {
			if ( ! _win.wp || ! _win.wp.pluginMarkupMarkdown || ! _win.wp.pluginMarkupMarkdown.instances ) {
				return '';
			}
			return _win.wp.pluginMarkupMarkdown.instances[ 0 ] ? _win.wp.pluginMarkupMarkdown.instances[ 0 ].element.value : '';
		};
		_win.tinyMCE.activeEditor.setContent = function( str ) {
			if ( ! _win.wp || ! _win.wp.pluginMarkupMarkdown || ! _win.wp.pluginMarkupMarkdown.instances ) {
				return false;
			}
			if ( _win.wp.pluginMarkupMarkdown.instances[ 0 ] ) {
				_win.wp.pluginMarkupMarkdown.instances[ 0 ].element.value = str || '';
				return true;
			}
			return false;
		};
	}


	mmd_initialize_field();


})( window, window.jQuery );
