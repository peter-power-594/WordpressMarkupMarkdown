(function( $ ) {

	$( document ).ready(function() {
		var hash = window.location.hash;
		$( "#tabs" ).tabs({
			active: $( hash && hash.length ? hash : '#tab-layout' ).prevAll().length - 1 
		});
	});

})( window.jQuery );