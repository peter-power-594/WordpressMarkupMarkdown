/* global wp, jQuery, katex, MathJax */

/**
 * @preserve The Markup Markdown Preview Module
 * @desc Everything needed to handle the preview. Mostly cache and media rules handlers
 * @author Pierre-Henri Lavigne <lavigne.pierrehenri@proton.me>
 * @version 1.1.3
 * @license GPL 3 - https://www.gnu.org/licenses/gpl-3.0.html#license-text
 */

(function( $ ) {

	var tmp_cache = {};

	/**
	 * MmdPreview is the public class available that will be used with the EasyMDE editor filters
	 *
	 * @param {Obect} ops The constructor options
	 *
	 * @returns {Object} 3 public methods
	 */
	function MmdPreview( ops ) {
		if ( ! ops ) {
			ops = {};
		}
		var callbacks = ops.callbacks || {},
			base_url = ops.base_url || '',
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
		return new renderEngine({
			base_url: base_url,
			callbacks: callbacks
		});
	}


	/**
	 * The preview render engine is a private class
	 *
	 * @param {Object} ops The rendering engine options
	 *
	 * @returns {Object} The following methods :
	 * - {Function} flushQueue A method to clear the cache queue
	 * - {Function} add2Queue A method to push a preview task to the queue
	 * - {Function} runQueue A method to trigger the tasks in the queue
	 */
	function renderEngine( ops ) {
		var _self = this;
		_self.isReady = 0;
		$( window ).on( 'resize.mmd_preview', function( event ) {
			_self.clipbox( event );
		});
		if ( ! $( '#mmd_preview-css' ).length ) {
			$( 'head' ).append( '<style type="text/css" id="mmd_preview-css"></style>' );
		}
		_self.previewSheet = document.getElementById( 'mmd_preview-css' ) || false;
		if ( ops.base_url && ops.callbacks ) {
			_self.base_url = ops.base_url;
			_self.callbacks = ops.callbacks;
			_self.isReady = 1;
		}
		var myQueue = [],
			myToolBox = {
				gallery: 'renderGallery',
				videoPlaylist: 'renderVideoPlaylist',
				audioPlaylist: 'renderAudioPlaylist',
				convertImage: 'convertHTMLImage',
				convertAudio: 'convertAudioShortcode',
				convertVideo: 'convertVideoShortcode',
				convertHeading: 'convertHeadingTags',
				convertLatexFormulas: 'renderLatexSnippets'
			};
		return {
			flushQueue: function() {
				myQueue = [];
			},
			add2Queue: function( toolbox, arg1, arg2 ) {
				var myHash = _self.hashString( arg1 || '' );
				if ( tmp_cache && tmp_cache[ myHash ] ) {
					return tmp_cache[ myHash ].join( '' );
				}
				else if ( toolbox && myToolBox[ toolbox ] ) {
					myQueue.push([ myToolBox[ toolbox ], arg1 || '', arg2 || 0 ]);
					return '';
				}
			},
			runQueue: function() {
				for ( var q = 0, myAction; q < myQueue.length; q++ ) {
					myAction = myQueue[ q ];
					_self[ myAction[ 0 ] ]( myAction[ 1 ], myAction[ 2 ] );
				}
			},
			processTask: function( toolbox, arg1, arg2 ) {
				if ( toolbox && myToolBox[ toolbox ] ) {
					return _self[ myToolBox[ toolbox ] ]( arg1 || '', arg2 || 0 );
				}
				else {
					return '';
				}
			}
		};
	}


	/**
	 * An implementation of Jenkins's one-at-a-time hash
	 * @source https://gist.github.com/atdt/2330641
	 * @source http://en.wikipedia.org/wiki/Jenkins_hash_function
	 *
	 * @param {String} key The key to use the hash from
	 *
	 * @returns {String} Hash of the string
	 */
	renderEngine.prototype.hashString = function ( key ) {
		var hash = 0, i = key.length;
		while ( i-- ) {
			hash += key.charCodeAt(i);
			hash += (hash << 10);
			hash ^= (hash >> 6);
		}
		hash += (hash << 3);
		hash ^= (hash >> 11);
		hash += (hash << 15);
		return "t" + hash;
	};


	/**
	 * Handle media preview HTML display size while rendering
	 *
	 * @param {Object} event The _resize_ event handler
	 *
	 */
	renderEngine.prototype.clipbox = function( event ) {
		var _self = this,
			css = [];
		$( '.tmp_media' ).each(function() {
			css.push( 'div[data-pointer="' + $( this ).attr( 'data-pointer' ) + '"]{min-height:' + Math.ceil( $( this ).height() ) + 'px}' );
		});
		css.push( '.tmp_span_block{display:block}' );
		css.push( '.tmp_span_inline{display:inline-block;vertical-align:baseline}' );
		_self.previewSheet.innerText = css.join( '' );
	};


	/**
	 * Extract and generates html attributes from markdown extra syntax
	 *
	 * @param {String} extractExtra Markdown extra markup
	 *
	 * @returns {String} The list of the related html attributes or FALSE in case of errors
	 */
	renderEngine.prototype.extractExtra = function( extraMarkup ) {
		if ( ! extraMarkup || ! extraMarkup.length ) {
			return false;
		}
		var extras = extraMarkup.match( /([^\s][\#\.]*[a-zA-Z0-9-_=]+[^\W]*)/g );
		if ( ! extras || ! extras.length ) {
			return false;
		}
		var attrs = {};
		for ( var e = 0, extra, tmp; e < extras.length; e++ ) {
			extra = extras[ e ];
			if ( /^#/.test( extra ) ) {
				attrs.id = attrs.id || '';
				attrs.id += extra.replace( '#', '' );
			}
			else if ( /^\./.test( extra ) ) {
				attrs.class = attrs.class || '';
				attrs.class += ' ' + extra.replace( '.', '' );
			}
			else if ( /\=/.test( extra ) ) {
				tmp = extra.split( '=' );
				attrs[ tmp[ 0 ] ] = tmp [ 1 ];
			}
		}
		var html = [];
		for ( var key in attrs ) {
			html.push( key + '="' + attrs[ key ].replace( /^\s*|\s*$/, '' ) + '"' );
		}
		return ' ' + html.join( ' ' );
	};


	renderEngine.prototype.convertHeadingTags = function( wpHeading ) {
		var myRenderApp = this,
			headingHash = myRenderApp.hashString( wpHeading );
		if ( tmp_cache && tmp_cache[ headingHash ] ) {
			return tmp_cache[ headingHash ].join( '' );
		}
		var matches = wpHeading.match( /<h(\d)[^\>]*>(.*?)\{([^\}]+)\}<\/h\d>/ );
		if ( ! matches || ! matches.length || matches.length !== 4 ) {
			tmp_cache[ headingHash ] = [ wpHeading ];
			return wpHeading;
		}
		var headline = [
			'<h' + matches[ 1 ],
			( myRenderApp.extractExtra( matches[ 3 ] ) || '' ),
			'>',
			matches[ 2 ],
			'</h' + matches[ 1 ] + '>',
		];
		tmp_cache[ headingHash ] = headline;
		return headline.join( '' );
	};


	/**
	 * Media Gallery Preview
	 *
	 * @param {String} wpGallery The WP gallery shortcode
	 * @param {Integer} galleryNumber The gallery counter used for the ID
	 *
	 * @returns {Boolean} TRUE in case of success or FALSE
	 */
	renderEngine.prototype.renderGallery = function( wpGallery, galleryNumber ) {
		var myRenderApp = this,
			galleryHash = myRenderApp.hashString( wpGallery );
		var mediaIds = ( wpGallery || '' ).match( /ids\=\"(.*?)\"/ );
		if ( ! mediaIds || ! mediaIds[ 1 ] ) {
			return '';
		}
		var galleryOptions = {
				size: 'thumbnail',
				columns: 3,
				link: 'none'
			},
			htmlGallery = function( imageIds ) {
				var html = [];
				html.push([
					'<div id="gallery-' + galleryNumber + '"',
					' class="gallery galleryid-' + galleryNumber,
					' gallery-columns-' + galleryOptions.columns,
					' gallery-size-' + galleryOptions.size,
					'" data-shortcode="' + encodeURIComponent( wpGallery ) + '">'
				].join( '' ));
				var items = [];
				for ( var j = 0; j < imageIds.length; j++ ) {
					items.push( htmlGalleryItem( wp.media.attachment( imageIds[ j ] ).attributes ) );
				}
				if ( ! items.length ) {
					return '';
				}
				html = html.concat( items );
				html.push( '</div>' );
				return html;
			},
			htmlGalleryItem = function( item ) {
				if ( ! item || ! item.sizes ) {
					return '';
				}
				var fig = [ '<figure class="gallery-item">' ];
				fig.push( '<div class="gallery-icon ' + ( item.width > item.height ? 'landscape' : 'portrait' ) + '">' );
				if ( galleryOptions.link !== 'none' ) {
					fig.push( '<a href="' + ( galleryOptions.link === 'file' ? item.url : item.link ) + '" target="_blank">' );
				}
				fig.push([
					'<img src="' + ( item.sizes[ galleryOptions.size ] ? item.sizes[ galleryOptions.size ].url : item.sizes.thumbnail.url ) + '"',
					' alt="' + ( item.alt || item.title ) + '"',
					' width="' + ( item.sizes[ galleryOptions.size ].width ? item.sizes[ galleryOptions.size ].width : item.width ) + '"',
					' height="' + ( item.sizes[ galleryOptions.size ].height ? item.sizes[ galleryOptions.size ].height : item.height ) + '"',
					item.caption ? ' aria-describedby="gallery-' + galleryNumber + '-' + item.id + '"' : '',
					'">'
				].join(''));
				if ( galleryOptions.link !== 'none' ) {
					fig.push( '</a>' );
				}
				fig.push( '</div>' );
				if ( item.caption ) {
					fig.push([
						'<figcaption class="wp-caption-text gallery-caption" id="gallery-' + galleryNumber + '-' + item.id + '">',
							item.caption,
						'</figcaption>'
					].join( '' ) );
				}
				fig.push( '</figure>' );
				return fig.join( '' );
			};
		if ( /columns/.test( wpGallery ) ) {
			var columns = wpGallery.match( /columns\=\"(.*?)\"/ ) || [];
			if ( columns && columns[ 1 ] && ! isNaN( +columns[ 1 ] ) ) {
				galleryOptions.columns = +columns[ 1 ];
			}
		}
		if ( /size/.test( wpGallery ) ) {
			var sizes = wpGallery.match( /size\=\"(.*?)\"/ ) || [];
			if ( sizes && sizes[ 1 ] && new RegExp( sizes[ 1 ].toLowerCase() ).test( 'small medium large full' ) ) {
				galleryOptions.size = sizes[ 1 ].toLowerCase();
			}
		}
		if ( /link/.test( wpGallery ) ) {
			var links = wpGallery.match( /link\=\"(.*?)\"/ ) || [];
			if ( links && links[ 1 ] && new RegExp( links[ 1 ].toLowerCase() ).test( 'post file none' ) ) {
				galleryOptions.link = links[ 1 ].toLowerCase();
			}
		}
		var imageIds = mediaIds[ 1 ].split( ',' ),
			attachmentLoaded = imageIds.length;
		for ( var i = 0; i < imageIds.length; i++) {
			imageIds[ i ] = +imageIds[ i ]; // parseInt( imageIds[ i ], 10 );
			if ( ! wp.media.attachment( imageIds[ i ] ) || ! wp.media.attachment( imageIds[ i ] ).attributes || ! wp.media.attachment( imageIds[ i ] ).attributes.sizes ) {
				attachmentLoaded--;
			}
		}
		var renderGallery = function() {
			var galleryNode = $( 'div[data-pointer="tmp_gallery-' + galleryNumber + '"]' )[ 0 ] || false;
			if ( ! galleryNode ) {
				return false;
			}
			var newHTMLGallery = htmlGallery( imageIds );
			if ( ! newHTMLGallery || ! newHTMLGallery.length ) {
				return false;
			}
			if ( ! tmp_cache[ galleryHash ] ) {
				tmp_cache[ galleryHash ] = newHTMLGallery;
			}
			galleryNode.innerHTML = newHTMLGallery.join( '' );
			if ( myRenderApp.callbacks && myRenderApp.callbacks.widget && typeof myRenderApp.callbacks.widget === 'function' ) {
				myRenderApp.callbacks.widget();
			}
		};
		if ( attachmentLoaded !== imageIds.length ) {
			// All attachment info are not available yet
			wp.media.query({ post__in: imageIds }).more().then(function() {
				setTimeout( function() {
					renderGallery();
					$( window ).trigger( 'resize.mmd_preview' );
				}, 250 );
			});
		}
		else {
			setTimeout(function() {
				renderGallery();
				$( window ).trigger( 'resize.mmd_preview' );
			}, 250 );
		}
		return '';
	};


	/**
	 * Convert HTML Image
	 *
	 * @param {String} wpImage An HTML image converted from markdown
	 *
	 * @returns {String} HTML Fixed HTML content
	 */
	renderEngine.prototype.convertHTMLImage = function( wpImage, dummy ) {
		var items = wpImage.match( /<img(.*?)>\{([^\}]+)\}/ ),
			myRenderApp = this,
			pictureHash = myRenderApp.hashString( wpImage );
		if ( tmp_cache && tmp_cache[ pictureHash ] ) {
			return tmp_cache[ pictureHash ].join( '' );
		}
		if ( ! items || items.length < 2 ) {
			return wpImage.replace( /\{[^\}]+\}/, '' );
		}
		var matches = wpImage.match( /<img(.*?)>\{([^\}]+)\}/g ); // Watch out for the "/g" (global) here !
		if ( ! matches || ! matches.length ) {
			return wpImage;
		}
		for ( var m = 0, links = [], link = '', item = [], figure = '', extras = [], alt = [], sizes = [], caption = ''; m < matches.length; m++ ) {
			links = wpImage.match( '<a href="(.*?)"[^>]*>' + items[ m ] );
			link = links && links.length > 0 ? links[ 1 ] : '';
			item = matches[ m ].match( /<img(.*?)>\{(.*?)\}/ );
			figure = '<figure' + ( myRenderApp.extractExtra( matches[ 2 ] ) || '' ) + '>';
			if ( link && link.length ) {
				figure += '<a href="' + link + '" target="_blank">'; // New tab for the preview
			}
			figure += '<img' + item[ 1 ] + '>';
			alt = item[ 1 ].match( /alt\=\"(.*?)\"/ );
			sizes = item[ 1 ].match( /src\=\"(.*?)-(\d+)x(\d+)\.(\w+)\"/ );
			caption = '';
			if ( alt && alt[ 1 ] && /--/.test( alt[ 1 ] ) ) {
				var txt = alt[ 1 ].split( '--' );
				alt = txt[ 0 ].replace( /^\s*|\s*$/, '' );
				caption = txt[ 1 ].replace( /^\s*|\s*$/, '' );
			}
			if ( link && link.length ) {
				figure += '</a>';
			}
			if ( caption && caption.length ) {
				figure = figure.replace( /class="(.*?)"/, 'class="wp-block-image wp-caption $1"' );
				figure = figure.replace( /alt=".*?"/, 'alt="' + alt + '"' );
				figure += '<figcaption class="wp-caption-text wp-element-caption">' + caption + '</figcaption>';
			}
			if ( sizes && sizes[ 2 ] && ! isNaN( +sizes[ 2 ] ) ) {
				figure = figure.replace( /<figure/, '<figure style="width:' + sizes[ 2 ] + 'px"' );
			}
			figure += '</figure>';
			if ( link && link.length ) {
				wpImage = wpImage.replace( new RegExp( '<a\\s[^>]*>' + matches[ m ] + '<\/a>', 'g' ), figure );
			}
			else {
				wpImage = wpImage.replace( matches[ m ], figure );
			}
		}
		tmp_cache[ pictureHash ] = [ wpImage ];
		return wpImage;
	};


	/**
	 * Convert HTML Audio
	 *
	 * @param {String} wpAudio The WP Audio Shortcode to convert to html
	 * @param {Integer} wpNumber The media number in the current HTML
	 *
	 * @returns {String} HTML Fixed HTML content
	 */
	renderEngine.prototype.convertAudioShortcode = function( wpAudio, wpNumber ) {
		wpAudio = wpAudio || '';
		var myRenderApp = this,
			trackHash = myRenderApp.hashString( wpAudio );
		if ( tmp_cache && tmp_cache[ trackHash ] ) {
			return tmp_cache[ trackHash ].join( '' );
		}
		var att = wpAudio.match( /^\[audio.*?src\=\"(.*?)\".*?\]\[\/audio\]$/ ) || false;
		if ( att && att.length && att.length > 0 ) {
			var aud = '<audio class="wp-audio-shortcode" id="audio-' + wpNumber + '" preload="none" ';
			aud += 'style="width: 100%;" controls="controls" src="' + att[ 1 ] + '"></audio>';
			tmp_cache[ trackHash ] = [ aud ];
			return aud;
		}
		else {
			return '';
		}
	};


	/**
	 * Convert HTML Video
	 *
	 * @param {String} wpVideo The WP Video Shortcode to convert to html
	 * @param {Integer} wpNumber The media number in the current HTML
	 *
	 * @returns {String} HTML Fixed HTML content
	 */
	renderEngine.prototype.convertVideoShortcode = function( wpVideo, wpNumber ) {
		wpVideo = wpVideo || '';
		var myRenderApp = this,
			movieHash = myRenderApp.hashString( wpVideo );
		if ( tmp_cache && tmp_cache[ movieHash ] ) {
			return tmp_cache[ movieHash ].join( '' );
		}
		var src = wpVideo.match( /src\=\"(.*?)\"/ ) || false,
			wth = wpVideo.match( /width\=\"(.*?)\"/ ) || false,
			hgt = wpVideo.match( /height\=\"(.*?)\"/ ) || false,
			svg = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' version='1.1' ",
			rct = "%3Crect ";
		if ( src && src.length && src.length > 0 ) {
			var vid = '<video class="wp-video-shortcode" id="video-' + wpNumber + '" preload="none" ';
			vid += 'style="width: 100%; height: auto;" controls="controls" ';
			if ( wth && wth.length && wth.length > 0 ) {
				vid += 'width="' + wth[ 1 ] + '" ';
				svg += "width='" + wth[ 1 ] + "' ";
				rct += "width='" + wth[ 1 ] + "' ";
			}
			if ( hgt && hgt.length && hgt.length > 0 ) {
				vid += 'height="' + hgt[ 1 ] + '" ';
				svg += "height='" + hgt[ 1 ] + "' ";
				rct += "height='" + hgt[ 1 ] + "' ";
			}
			rct += " fill='%23EDEDED'%3E%3C/rect%3E";
			svg += "%3E" + rct + "%3C/svg%3E";
			vid += 'poster="' + svg + '" ';
			vid += 'src="' + src[ 1 ] + '"></video>';
			tmp_cache[ movieHash ] = [ vid ];
			return vid;
		}
		else {
			return '';
		}
	};


	/**
	 * Media Video Playlist Preview
	 *
	 * @param {String} wpVideoPlaylist The WP playlist shortcode [playlist type="video" ids="xxx,xxx,xx"]
	 * @param {Integer} playListNumber The number used for the media ID in the HTML document
	 *
	 * @returns {Boolean} TRUE in case of success or FALSE
	 */
	renderEngine.prototype.renderVideoPlaylist = function( wpVideoPlaylist, playListNumber ) {
		var myRenderApp = this,
			mediaIds = ( wpVideoPlaylist || '' ).match( /ids\=\"(.*?)\"/ );
		if ( ! mediaIds || ! mediaIds[ 1 ] ) {
			return ''; // Something's wrong
		}
		var galleryHash = myRenderApp.hashString( wpVideoPlaylist );
		if ( tmp_cache && tmp_cache[ galleryHash ] ) {
			return tmp_cache[ galleryHash ].join( '' );
		}
		// HTML Vide Playlist
		var playlistHTML = function( videoIds ) {
			var tracks = [];
			for ( var j = 0, vid, obj; j < videoIds.length; j++ ) {
				vid = wp.media.attachment( +videoIds[ j ] ).attributes;
				if ( ! vid.url ) continue; // Just in case the media was deleted
				obj = {
					src: vid.url,
					type: vid.mime,
					title: vid.title,
					caption: vid.caption,
					description: vid.description,
					meta: vid.meta,
					dimensions: {
						original: {
							width: vid.width,
							height: vid.height
						},
						resized: {
							width: 640,
							height: Math.ceil( vid.height * 640 / vid.width )
						}
					},
					image: {
						src: vid.image.src,
						width: vid.image.width,
						height: vid.image.height
					},
					thumb: {
						src: vid.thumb.src,
						width: vid.thumb.width,
						height: vid.thumb.height
					}
				};
				tracks.push( obj );
			}
			if ( ! tracks.length ) {
				return '';
			}
			var html = [ '<div id="playlist-' + playListNumber + '" class="wp-playlist wp-video-playlist wp-playlist-light" data-shortcode="' + encodeURIComponent( wpVideoPlaylist ) + '">' ];
			html.push( '<video controls="controls" preload="none" width="' + tracks[ 0 ].dimensions.resized.width + '" height="' + tracks[ 0 ].dimensions.resized.height + '"></video>' );
			html.push( '<div class="wp-playlist-next"></div>' );
			html.push( '<div class="wp-playlist-prev"></div>' );
			html.push( '<script type="application/json" class="wp-playlist-script">' );
			html.push( '{"type":"video","tracklist":true,"tracknumbers":true,"images":true,"artists":true,"tracks":[' );
			for ( var k = 0; k < tracks.length; k++ ) {
				html.push( ( k > 0 ? ',' : '' ) + JSON.stringify( tracks[ k ] ) );
			}
			html.push( ']}</script>' );
			html.push( '</div>' );
			return html;
		};
		// Check all medias are ready before intializing the rendering
		var renderPlaylist = function() {
			var playlistNode = $( 'div[data-pointer="tmp_video_playlist-' + playListNumber + '"]' )[ 0 ] || false;
			if ( ! playlistNode ) {
				return false;
			}
			var newHTMLPlaylist = playlistHTML( videoIds );
			if ( ! newHTMLPlaylist || ! newHTMLPlaylist.length ) {
				return false;
			}
			playlistNode.innerHTML = newHTMLPlaylist.join( '' );
			if ( window.wp && window.wp.playlist && typeof window.wp.playlist.initialize === 'function' ) {
				setTimeout(function() {
					window.wp.playlist.initialize();
					setTimeout(function() {
						if ( ! tmp_cache[ galleryHash ] ) {
							tmp_cache[ galleryHash ] = [ playlistNode.innerHTML ];
						}
					}, 250);
				}, 250);
			}
			if ( myRenderApp.callbacks && myRenderApp.callbacks.widget && typeof myRenderApp.callbacks.widget === 'function' ) {
				myRenderApp.callbacks.widget();
			}
		};
		var videoIds = mediaIds[ 1 ].split( ',' ),
			attachmentLoaded = videoIds.length;
		for ( var i = 0, vid; i < videoIds.length; i++ ) {
			videoIds[ i ] = +videoIds[ i ]; // parseInt( videoIds[ i ], 10 );
			vid = wp.media.attachment( videoIds[ i ] ).attributes || false;
			if ( ! vid || ! vid.url || ( vid.url && ! vid.url.length ) ) {
				attachmentLoaded--;
			}
		}
		if ( attachmentLoaded < videoIds.length ) {
			// All attachment info are not available yet
			wp.media.query({ post__in: videoIds })
				.more().then(function( data ) {
					setTimeout( renderPlaylist, 250 );
				});
		}
		else {
			setTimeout( renderPlaylist, 250 );
		}
		return true;
	};


	/**
	 * Media Audio Playlist Preview
	 *
	 * @param {String} wpAudioPlaylist The WP playlist shortcode [playlist ids="xxx,xxx,xx"]
	 * @param {Integer} playListNumber The number used for the media ID in the HTML document
	 *
	 * @returns {Boolean} TRUE in case of success or FALSE
	 */
	renderEngine.prototype.renderAudioPlaylist = function( wpAudioPlaylist, playListNumber ) {
		var myRenderApp = this,
			mediaIds = ( wpAudioPlaylist || '' ).match( /ids\=\"(.*?)\"/ );
		if ( ! mediaIds || ! mediaIds[ 1 ] ) {
			return ''; // Something's wrong
		}
		var galleryHash = myRenderApp.hashString( wpAudioPlaylist );
		if ( tmp_cache && tmp_cache[ galleryHash ] ) {
			return tmp_cache[ galleryHash ].join( '' );
		}
		// HTML Vide Playlist
		var playlistHTML = function( audioIds ) {
			var tracks = [];
			for ( var j = 0, aud, obj; j < audioIds.length; j++ ) {
				aud = wp.media.attachment( +audioIds[ j ] ).attributes;
				if ( ! aud.url ) continue; // Just in case the media was deleted
				obj = {
					src: aud.url,
					type: aud.mime,
					title: aud.title,
					caption: aud.caption,
					description: aud.description,
					meta: aud.meta,
					image: {
						src: aud.image.src,
						width: aud.image.width,
						height: aud.image.height
					},
					thumb: {
						src: aud.thumb.src,
						width: aud.thumb.width,
						height: aud.thumb.height
					}
				};
				tracks.push( obj );
			}
			if ( ! tracks.length ) {
				return '';
			}
			var html = [ '<div id="playlist-' + playListNumber + '" class="wp-playlist wp-audio-playlist wp-playlist-light" data-shortcode="' + encodeURIComponent( wpAudioPlaylist ) + '">' ];
			html.push( '<audio controls="controls" preload="none" width="582"></audio>' );
			html.push( '<div class="wp-playlist-next"></div>' );
			html.push( '<div class="wp-playlist-prev"></div>' );
			html.push( '<script type="application/json" class="wp-playlist-script">' );
			html.push( '{"type":"audio","tracklist":true,"tracknumbers":true,"images":true,"artists":true,"tracks":[' );
			for ( var k = 0; k < tracks.length; k++ ) {
				html.push( ( k > 0 ? ',' : '' ) + JSON.stringify( tracks[ k ] ) );
			}
			html.push( ']}</script>' );
			html.push( '</div>' );
			return html;
		};
		// Check all medias are ready before intializing the rendering
		var renderPlaylist = function() {
			var playlistNode = $( 'div[data-pointer="tmp_audio_playlist-' + playListNumber + '"]' )[ 0 ] || false;
			if ( ! playlistNode ) {
				return false;
			}
			var newHTMLPlaylist = playlistHTML( audioIds );
			if ( ! newHTMLPlaylist || ! newHTMLPlaylist.length ) {
				return false;
			}
			playlistNode.innerHTML = newHTMLPlaylist.join( '' );
			if ( window.wp && window.wp.playlist && typeof window.wp.playlist.initialize === 'function' ) {
				setTimeout(function() {
					window.wp.playlist.initialize();
					setTimeout(function() {
						if ( ! tmp_cache[ galleryHash ] ) {
							tmp_cache[ galleryHash ] = [ playlistNode.innerHTML ];
						}
					}, 250);
				}, 250);
			}
			if ( myRenderApp.callbacks && myRenderApp.callbacks.widget && typeof myRenderApp.callbacks.widget === 'function' ) {
				myRenderApp.callbacks.widget();
			}
		};
		var audioIds = mediaIds[ 1 ].split( ',' ),
			attachmentLoaded = audioIds.length;
		for ( var i = 0, aud; i < audioIds.length; i++ ) {
			audioIds[ i ] = +audioIds[ i ]; // parseInt( audioIds[ i ], 10 );
			aud = wp.media.attachment( audioIds[ i ] ).attributes || false;
			if ( ! aud || ! aud.url || ( aud.url && ! aud.url.length ) ) {
				attachmentLoaded--;
			}
		}
		if ( attachmentLoaded < audioIds.length ) {
			// All attachment info are not available yet
			wp.media.query({ post__in: audioIds })
				.more().then(function( data ) {
					setTimeout( renderPlaylist, 250 );
				});
		}
		else {
			setTimeout( renderPlaylist, 250 );
		}
		return true;
	};


	/**
	 * LaTeX Snippets rendering
	 *
	 * @param {String} wpLatex The LaTeX snippet written in the markdown post
	 * @param {Integer} formularNumber The number used for the formula ID in the HTML document
	 *
	 * @returns {Boolean} TRUE in case of success or FALSE
	 */
	renderEngine.prototype.renderLatexSnippets = function( wpLatex, formularNumber ) {
		var myRenderApp = this,
			snippetHash = myRenderApp.hashString( wpLatex ),
			getSnippetNode = function() {
				var myNode = $( 'span[data-pointer="tmp_latex-' + formularNumber + '"]' )[ 0 ] || false;
				return myNode;
			};
		if ( tmp_cache && tmp_cache[ snippetHash ] ) {
			return tmp_cache[ snippetHash ].join( '' );
		}
		var latexSnippet = wpLatex.match( /\${1,2}([^\$]+)\${1,2}/ );
		if ( ! latexSnippet || ! latexSnippet[ 1 ] ) {
			return false;
		}
		var latexCode = latexSnippet[ 1 ].replace( /<br\s*\/*>/g, '\n' ).replace( /(^\n|\n$)/, '' ),
			snippetNode = getSnippetNode();
		if ( ! snippetNode ) {
			return false;
		}
		else if ( snippetNode.parentNode ) {
			var parentNode, pn = 0;
			while ( pn < 3 ) {
				parentNode = snippetNode.parentNode || {};
				if ( ( parentNode.tagName || '' ).toUpperCase() === 'CODE' || /hljs/.test( parentNode.className || '' ) ) {
					snippetNode.parentNode.removeChild( snippetNode );
					return false;
				}
				pn++;
			}
		}
		var renderSnippet = function() {
			if ( ! snippetNode ) {
				return false;
			}
			var isBlock = /\$\$/.test( latexSnippet ) ? true : false;
			if ( window.katex ) {
				katex.render(latexCode, snippetNode, {
					displayMode: isBlock,
					throwOnError: false
				});
			}
			else if ( window.MathJax ) {
				if ( MathJax.tex2chtml && typeof MathJax.tex2chtml === 'function' ) {
					snippetNode.appendChild( MathJax.tex2chtml( latexCode ), { em: 16, ex: 8, display: isBlock } );
				}
				else if ( MathJax.tex2svg && typeof MathJax.tex2svg === 'function' ) {
					snippetNode.appendChild( MathJax.tex2svg( latexCode ), { em: 16, ex: 8, display: isBlock } );
				}
				// snippetNode.appendChild( MathJax.HTML.Element( span, {}, latexCode ) );
			}
			if ( ! tmp_cache[ snippetHash ] ) {
				tmp_cache[ snippetHash ] = [ snippetNode.innerHTML ];
			}
		};
		if ( snippetNode && ! /tmp_ui_ready/.test( snippetNode.className ) ) {
			snippetNode.className += ' tmp_ui_ready';
			setTimeout( renderSnippet, 250 );			
		}
		return true;
	};


	window.MmdPreview = MmdPreview;


})( window.jQuery );
