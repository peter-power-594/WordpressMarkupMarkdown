(function( $ ) {

	function MarkupMarkdownApp() {
		var _self = this;
		$( document ).ready(function() {
			_self.tabOptions();
			_self.toolbarButtons();
			_self.toggleButtons();
		});
	}


	MarkupMarkdownApp.prototype.toggleButtons = function() {
		$( '#wrap a.toggler' ).on( 'click', function( event ) {
			event.preventDefault();
			$( '#' + this.href.replace( /.*?#/, '' ) ).trigger( 'click' );
		});
	};


	MarkupMarkdownApp.prototype.tabOptions = function() {
		var hash = window.location.hash,
			$tabs = $( '#tabs' );
		if ( ! $tabs.length ) {
			return false;
		}
		$( "#tabs" ).tabs({
			active: $( hash && hash.length ? hash : '#tab-layout' ).prevAll().length - 1 
		});
		return true;
	};


	MarkupMarkdownApp.prototype.toolbarButtons = function() {
		var $toolbar_buttons = $( '#toolbar_buttons' ),
			$my_buttons = $( '#my_buttons' );
		if ( ! $toolbar_buttons.length || ! $my_buttons.length ) {
			return false;
		}
		var updateSetting = function() {
			var mmdToolbar = $.map( $( '#my_buttons' ).find( 'li' ), function( item ) {
				return ( item.getAttribute( 'data-slug' ) || '' ).replace( /[^a-z0-9_-]/, '' );
			} ).join( ',' );
			$( 'input[name="mmd_toolbar"]' ).val( mmdToolbar );
		};
		$( '#toolbar_buttons, #my_buttons' ).sortable({
			items:       "li:not(.ui-state-disabled)",
			connectWith: ".connected",
			cancel:      "a.ui-icon",
			revert:      "invalid",
			cursor:      "move",
			update:      updateSetting
		}).disableSelection();
		$my_buttons.on( 'click', function( event ) {
			var $target = $( event.target );
			if ( $target.closest( 'a.ui-trash-link' ).length ) {
				event.preventDefault();
				event.stopPropagation();
				$target.closest( 'li' ).appendTo( $toolbar_buttons );
			}
			updateSetting();
		});
		return true;
	};


	new MarkupMarkdownApp();


})( window.jQuery );
