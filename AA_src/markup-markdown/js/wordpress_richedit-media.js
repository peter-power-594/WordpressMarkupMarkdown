/* global wp, jQuery */

/**
 * @preserve The Markup Markdown's Media Module
 * @desc A simple efficient bridge between the Wordpress Media Library and the EasyMDE's CodeMirror Instance
 * @author Pierre-Henri Lavigne <lavigne.pierrehenri@proton.me>
 * @version 1.0.27
 * @license GPL 3 - https://www.gnu.org/licenses/gpl-3.0.html#license-text
 */

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
		_self.callbacks = callbacks;
		// Pictures : https://codex.wordpress.org/Javascript_Reference/wp.media
		return {
			initialize: function() {
				_self.initialize();
			},
			open: function() {
				if ( _self.instance ) {
					_self.instance.open();
				}
			}
		};
	}


	MmdMedia.prototype.initialize = function() {
		var _self = this,
			isAdmin = /wp-admin/.test( document.body.className || '' ) ? true : false;
		_self.instance = wp.media({
			frame:  'post',
			type: 'image',
			multiple: true,
			title: 'Media'
			// button: { text: 'OK' },
		});
		if ( ! _self.instance ) {
			if ( window.console && window.console.log ) {
				window.console.log( 'Markup Markdown: The WP Media Library is not properly loaded' );
			}
			return false;
		}
		_self.instance.on( 'update', function() {
			// **Update** event is triggered when the user presses the "Create the {widget}"
			// from the media modal (example: generating a gallery)
			_self.addMediaFromWidget( _self.instance.state(), _self.callbacks.widget );
		});
		_self.instance.on( 'insert', function() {
			// **Insert** event is trigerred when the user presses the "Insert into post"
			// inside the media modal (one or multiples images are propably selected)
			_self.addMediaFromSel( _self.instance.state(), _self.callbacks.multisel );
		});
	};


	MmdMedia.prototype.addMediaFromWidget = function( frameState, callback ) {
		var _self = this,
			myFrameState = frameState ? frameState : false;
		if ( ! myFrameState ) {
			return false;
		}
		var myWidget = myFrameState.get( 'library' ),
			myAttributes = myWidget && myWidget.props ? myWidget.props.attributes || {} : {};
		if ( ! myAttributes.type ) {
			return false;
		}
		switch ( myAttributes.type || 'image' ) {
			case 'audio':
				return callback( wp.media.playlist.shortcode( myWidget ).string() );
			case 'video':
				return callback( wp.media.playlist.shortcode( myWidget ).string() );
			default: // Image Gallery
				return callback( wp.media.gallery.shortcode( myWidget ).string() );
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
			mkd = '', // Markdown code
			url = att.url.replace( _self.base_url, '' );
		if ( url.charAt( 0 ) !== '/' && url.indexOf( 'http' ) === -1 ) {
			url = '/' + url;
		}
		if ( ! dpy.link || ! dpy.link.length ) {
			return '';
		}
		if ( dpy.link === 'embed' ) {
			mkd = '[audio src="' + url + '"][/audio]';
		}
		else {
			mkd = '[' + att.title + '](';
			if ( dpy.link === 'file' ) {
				mkd += url;
			}
			else {
				url = ( att.link || '' ).replace( _self.base_url, '' );
				if ( url.charAt( 0 ) !== '/' && url.indexOf( 'http' ) === -1 ) {
					url = '/' + url;
				}
				mkd += url;
			}
			mkd += ')';
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
			mkd = '', // Markdown code
			url = att.url.replace( _self.base_url, '' );
		if ( url.charAt( 0 ) !== '/' && url.indexOf( 'http' ) === -1 ) {
			url = '/' + url;
		}
		if ( ! dpy.link || ! dpy.link.length ) {
			return '';
		}
		if ( dpy.link === 'embed' ) {
			mkd = '[video width="' + att.width + '" height="' + att.height + '" src="' + url + '"][/video]';
		}
		else {
			mkd = '[' + att.title + '](';
			if ( dpy.link === 'file' ) {
				mkd += url;
			}
			else {
				url = ( att.link || '' ).replace( _self.base_url, '' );
				if ( url.charAt( 0 ) !== '/' && url.indexOf( 'http' ) === -1 ) {
					url = '/' + url;
				}
				mkd += url;
			}
			mkd += ')';
		}
		return mkd;
	};


	window.MmdMedia = MmdMedia;


})( window.jQuery );
