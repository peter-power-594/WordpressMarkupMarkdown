(function( $ ) {


	function MmdMedia( ops ) {
		if ( ! ops ) {
			ops = {};
		}
		var _self = this,
			callbacks = ops.callbacks || {};
			fallbacks = {
				widget: function() {},
				multisel: function() {}
			};
		if ( ! callbacks.widget || typeof callbacks.widget !== 'function' ) {
			callbacks.widget = fallbacks.widget;
		}
		if ( ! callbacks.multisel || typeof callbacks.multisel !== 'function' ) {
			callbacks.multisel = fallbacks.multisel;
		}
		_self.base_url = ops.base_url || '';
		// Pictures : https://codex.wordpress.org/Javascript_Reference/wp.media
		var mediaFrame = wp.media({
			frame: 'post',
			type: 'image',
			multiple: true,
			title: 'Media'
			// button: { text: 'OK' },
		});
		mediaFrame.on( 'update', function() {
			// **Update** event is triggered when the user presses the "Create the {widget}"
			// from the media modal (example: generating a gallery)
			_self.addMediaFromWidget( mediaFrame.state(), callbacks.widget );
		});
		mediaFrame.on( 'insert', function() {
			// **Insert** event is trigerred when the user presses the "Insert into post"
			// inside the media modal (one or multiples images are propably selected)
			_self.addMediaFromSel( mediaFrame.state(), callbacks.multisel );
		});
		return mediaFrame;
	}


	MmdMedia.prototype.addMediaFromWidget = function( frameState, callback ) {
		var _self = this,
			myFrameState = frameState ? frameState : false;
		if ( ! myFrameState ) {
			return false;
		}
		var myWidget = myFrameState.get( 'library' ),
			myAttributes = myWidget && myWidget.props ? myWidget.props[ 'attributes' ] || {} : {};
		if ( ! myAttributes.type ) {
			return false;
		}
		switch ( myAttributes.type || 'image' ) {
			case 'audio':
				return callback( wp.media.playlist.shortcode( myWidget ).string() );
			break;
			case 'video':
				return callback( wp.media.playlist.shortcode( myWidget ).string() );
			break;
			default: // Image Gallery
				return callback( wp.media.gallery.shortcode( myWidget ).string() );
			break;
		}
	};


	MmdMedia.prototype.addMediaFromSel = function( frameState, callback ) {
		var _self = this,
			sel = frameState ? frameState.get( 'selection' ) : false;
		if ( ! sel || ! sel.length ) {
			return false;
		}
		var selLength = sel.length > 1 ? 1 : 0,
			markdown = [];
		sel.map(function( item ) {
			var attachment = item.toJSON(),
				options = ( frameState.display( item ) || {} ).toJSON(); // Display Options
			options.multiple = selLength;
			switch ( attachment.type || 'image' ) {
				case 'audio':
					markdown.push( _self.addMediaAudio( attachment, options ) );
				break;
				case 'video':
					markdown.push( _self.addMediaVideo( attachment, options ) );
				break;
				default: // Image is set as our default
					markdown.push( _self.addMediaImage( attachment, options ) );
				break;
			}
		});
		return callback( markdown.length > 1 ? "- " + markdown.join( "\n- " ) : markdown[ 0 ] );
	};


	/**
	 * Image
	 * 
	 * @param Object att The media attributes from the uploader frame
	 * @param Object dpy The display options from the sidebar frame
	 * 
	 * @return String The markdown related code
	 */
	MmdMedia.prototype.addMediaImage = function( att, dpy ) {
		var _self = this,
			alt = att.alt || att.title, // Wordpress attachment alternative text
			cap = att.caption || '', // Wordpress attachment caption
			url = att.url, // Wordpress attachment url
			mkd = ''; // Markdown code
		// Use the specific size if set and remove the domain from url
		if ( dpy.size && att.sizes[ dpy.size ] ) {
			url = att.sizes[ dpy.size ].url;
		}
		url = url.replace( _self.base_url, '' );
		if ( url.charAt( 0 ) !== '/' && url.indexOf( 'http' ) === -1 ) {
			url = '/' + url;
		}
		// Standard markdown image:
		// ![Alternative text](/images/thumb.png)
		mkd = '![' + alt + ( cap && cap.length ? ' -- ' + cap.replace( /\r|\n/, '' ) : '' ) + '](' + url + ')';
		// Extra markown for classnames with alignment data:
		// ![Alternative text](/images/thumb.png){.alignright}
		if ( dpy.align && dpy.align.length ) {
			mkd += '{.align' + dpy.align + '}';
		}
		// Standard link: [Lorem Ipsum](https://www.lipsum.com)
		// Standard link on image
		// [![Alternative text](/images/thumb.png){.alignright}](https://www.lipsum.com)
		if ( dpy.link && dpy.link.length && dpy.link !== 'none' ) {
			mkd = '[' + mkd + ']';
			if ( dpy.link === 'custom' ) {
				if ( dpy.linkUrl && dpy.linkUrl.length ) {
					dpy.linkUrl = dpy.linkUrl.replace( _self.base_url, '' );
					if ( dpy.linkUrl.charAt( 0 ) !== '/' && dpy.linkUrl.indexOf( 'http' ) === -1 ) {
						dpy.linkUrl = '/' + dpy.linkUrl;
					}
					mkd += '(' + dpy.linkUrl;
				}
			}
			else {
				mkd += '(' + ( dpy.link === 'file' ? url : att.link );
			}
			if ( dpy.multiple && dpy.multiple > 0 ) {
				mkd += ' "myset%d ' + ( att.title && att.title.length && /[\w]+[^\w]/.test( att.title ) ? att.title : '' ) + '")';
			}
			else {
				mkd += ')';
			}
		}
		return mkd;
	};


	/**
	 * Audio [audio src="xxxx"][/audio]
	 * @ref https://wordpress.org/documentation/article/audio-shortcode/
	 * 
	 * @param Object att The media attributes from the uploader frame
	 * @param Object dpy The display options from the sidebar frame
	 * 
	 * @return String The WP shortcode
	 */
	MmdMedia.prototype.addMediaAudio = function( att, dpy ) {
		var _self = this,
			mkd = ''; // Markdown code
		att.url = att.url.replace( _self.base_url, '' );
		if ( ! dpy.link || ! dpy.link.length ) {
			return '';
		}
		if ( dpy.link === 'embed' ) {
			mkd = '[audio src="' + att.url + '"][/audio]';
		}
		else {
			mkd = '[' + att.title + ']';
			mkd += '(' + ( dpy.link === 'file' ? att.url : att.link ) + ')';
		}
		return mkd;
	};


	/**
	 * Video [video src="xxxx"][/video]
	 * @ref https://wordpress.org/documentation/article/video-shortcode/
	 * 
	 * @param Object att The media attributes from the uploader frame
	 * @param Object dpy The display options from the sidebar frame
	 * 
	 * @return String The WP shortcode
	 */
	MmdMedia.prototype.addMediaVideo = function( att, dpy ) {
		var _self = this,
			mkd = ''; // Markdown code
		att.url = att.url.replace( _self.base_url, '' );
		if ( ! dpy.link || ! dpy.link.length ) {
			return '';
		}
		if ( dpy.link === 'embed' ) {
			mkd = '[video width="' + att.width + '" height="' + att.height + '" src="' + att.url + '"][/video]';
		}
		else {
			mkd = '[' + att.title + ']';
			mkd += '(' + ( dpy.link === 'file' ? att.url : att.link ) + ')';
		}
		return mkd;
	};


	window.MmdMedia = MmdMedia;


})( window.jQuery );
