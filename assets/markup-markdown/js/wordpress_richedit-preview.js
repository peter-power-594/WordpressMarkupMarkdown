(function( $ ) {


	function MmdPreview( ops ) {
		if ( ! ops ) {
			ops = {};
		}
		var _self = this,
			callbacks = ops.callbacks || {},
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
		var myEngine = new renderEngine({
			base_url: base_url,
			callbacks: callbacks
		});
		return {
			gallery: function( wpGallery, galleryNumber ) {
				return myEngine.renderGallery( wpGallery || '', galleryNumber || 0 );
			},
			videoPlaylist: function( wpVideoPlaylist, playlistNumber ) {
				return myEngine.renderVideoPlaylist( wpVideoPlaylist || '', playlistNumber || 0 );
			},
			audioPlaylist: function( wpAudioPlaylist, playlistNumber ) {
				return myEngine.renderAudioPlaylist( wpAudioPlaylist || '', playlistNumber || 0 );
			},
			convertImage: function( htmlImage ) {
				return myEngine.convertHTMLImage( htmlImage );
			},
			convertAudio: function( audioShortcode, audioNumber ) {
				return myEngine.convertAudioShortcode( audioShortcode, audioNumber );
			},
			convertVideo: function( videoShortcode, videoNumber ) {
				return myEngine.convertVideoShortcode( videoShortcode, videoNumber );
			}
		};
	}


	function renderEngine( ops ) {
		this.isReady = 0;
		if ( ops.base_url && ops.callbacks ) {
			this.base_url = ops.base_url;
			this.callbacks = ops.callbacks;
			this.isReady = 1;
		}
	}


	/**
	 * Media Gallery Preview
	 *
	 * @param String wpGallery The WP gallery shortcode
	 * @param Integer galleryNumber The gallery counter used for the ID
	 * 
	 * @returns Boolean TRUE in case of success or FALSE
	 */
	renderEngine.prototype.renderGallery = function( wpGallery, galleryNumber ) {
		var myRenderApp = this,
			mediaIds = ( wpGallery || '' ).match( /ids\=\"(.*?)\"/ );
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
			var galleryNode = document.getElementById( 'tmp_gallery-' + galleryNumber ) || false;
			if ( ! galleryNode ) {
				return false;
			}
			var newHTMLGallery = htmlGallery( imageIds );
			if ( ! newHTMLGallery || ! newHTMLGallery.length ) {
				return false;
			}
			galleryNode.innerHTML = newHTMLGallery.join( '' );
			if ( myRenderApp.callbacks && myRenderApp.callbacks.widget && typeof myRenderApp.callbacks.widget === 'function' ) {
				myRenderApp.callbacks.widget();
			}
		};
		if ( attachmentLoaded !== imageIds.length ) {
			// All attachment info are not available yet
			wp.media.query({ post__in: imageIds }).more().then(function() {
				setTimeout( renderGallery, 250 );
			});
		}
		else {
			setTimeout( renderGallery, 250 );
		}
		return true;
	};


	/**
	 * Convert HTML Image
	 *
	 * @param String wpImage An HTML image converted from markdown
	 * 
	 * @returns String HTML Fixed HTML
	 */
	renderEngine.prototype.convertHTMLImage = function( wpImage ) {
		var items = wpImage.match( /<img(.*?)>\{\.(align[a-z]+)\}/ );
		if ( ! items || items.length < 2 ) {
			return wpImage.replace( /\{\.(.*?)\}/, '' );
		}
		var matches = wpImage.match( /<img(.*?)>\{\.(align[a-z]+)\}/g );
		if ( ! matches || ! matches.length ) {
			return wpImage;
		}
		for ( var m = 0, links = [], link = '', item = [], figure = '', alt = [], sizes = [], caption = ''; m < matches.length; m++ ) {
			links = wpImage.match( '<a href="(.*?)"[^>]*>' + matches[ m ] ),
			link = links && links.length > 0 ? links[ 1 ] : '',
			item = matches[ m ].match( /<img(.*?)>\{\.(align[a-z]+)\}/ ),
			figure = '<figure class="' + item[ 2 ] + '">';
			if ( link && link.length ) {
				figure += '<a href="' + link + '" target="_blank">'; // New tab for the preview
			}
			figure += '<img' + item[ 1 ] + '>';
			alt = item[ 1 ].match( /alt\=\"(.*?)\"/ ),
			sizes = item[ 1 ].match( /src\=\"(.*?)-(\d+)x(\d+)\.(\w+)\"/ ),
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
				figure = figure.replace( /class="(.*?)"/, 'class="wp-caption $1"' );
				figure = figure.replace( /alt=".*?"/, 'alt="' + alt + '"' );
				figure += '<figcaption>' + caption + '</figcaption>';
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
		return wpImage;
	};


	/**
	 * Convert HTML Audio
	 *
	 * @param String wpAudio The WP Audio Shortcode to convert to html
	 * @param Integer wpNumber The media number in the current HTML
	 * 
	 * @returns String HTML Fixed HTML
	 */
	renderEngine.prototype.convertAudioShortcode = function( wpAudio, wpNumber ) {
		wpAudio = wpAudio || '';
		var att = wpAudio.match( /^\[audio.*?src\=\"(.*?)\".*?\]\[\/audio\]$/ ) || false;
		if ( att && att.length && att.length > 0 ) {
			return '<audio class="wp-audio-shortcode" id="audio-' + wpNumber + '" preload="none"'
				+ ' style="width: 100%;" controls="controls" src="' + att[ 1 ] + '"></audio>';
		}
		else {
			return '';
		}
	};


	/**
	 * Convert HTML Video
	 *
	 * @param String wpVideo The WP Video Shortcode to convert to html
	 * @param Integer wpNumber The media number in the current HTML
	 * 
	 * @returns String HTML Fixed HTML
	 */
	renderEngine.prototype.convertVideoShortcode = function( wpVideo, wpNumber ) {
		wpVideo = wpVideo || '';
		var src = wpVideo.match( /src\=\"(.*?)\"/ ) || false,
			wth = wpVideo.match( /width\=\"(.*?)\"/ ) || false,
			hgt = wpVideo.match( /height\=\"(.*?)\"/ ) || false;
		if ( src && src.length && src.length > 0 ) {
			var vid = '<video class="wp-video-shortcode" id="video-' + wpNumber + '" preload="auto" ';
			vid += ' style="width: 100%; height: auto;" controls="controls"';
			if ( wth && wth.length && wth.length > 0 ) {
				vid += 'width="' + wth[ 1 ] + '" ';
			}
			if ( hgt && hgt.length && hgt.length > 0 ) {
				vid += 'height="' + hgt[ 1 ] + '" ';
			}
			vid += 'src="' + src[ 1 ] + '"></video>';
			return vid;
		}
		else {
			return '';
		}
	};


	/**
	 * Media Video Playlist Preview
	 * 
	 * @param String wpVideoPlaylist The WP playlist shortcode [playlist type="video" ids="xxx,xxx,xx"]
	 * @param Integer playListNumber The number used for the media ID in the HTML document
	 *
	 * @returns Boolean TRUE in case of success or FALSE
	 */
	renderEngine.prototype.renderVideoPlaylist = function( wpVideoPlaylist, playListNumber ) {
		var myRenderApp = this,
			mediaIds = ( wpVideoPlaylist || '' ).match( /ids\=\"(.*?)\"/ );
		if ( ! mediaIds || ! mediaIds[ 1 ] ) {
			return ''; // Something's wrong
		}
		// HTML Vide Playlist 
		var playlistHTML = function( videoIds ) {
			var tracks = [];
			for ( var j = 0, vid, obj; j < videoIds.length; j++ ) {
				vid = wp.media.attachment( +videoIds[ j ] ).attributes;
				if ( ! vid.url ) continue; // Just in case the media was deleted
				obj = {
					src: vid.url.replace( myRenderApp.base_url, '' ),
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
						src: vid.image.src.replace( myRenderApp.base_url, '' ),
						width: vid.image.width,
						height: vid.image.height
					},
					thumb: {
						src: vid.thumb.src.replace( myRenderApp.base_url, '' ),
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
			var playlistNode = document.getElementById( 'tmp_video_playlist-' + playListNumber ) || false;
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
			renderPlaylist( renderPlaylist, 250 );
		}
		return true;
	};


	/**
	 * Media Audio Playlist Preview
	 * 
	 * @param String wpAudioPlaylist The WP playlist shortcode [playlist ids="xxx,xxx,xx"]
	 * @param Integer playListNumber The number used for the media ID in the HTML document
	 *
	 * @returns Boolean TRUE in case of success or FALSE
	 */
	renderEngine.prototype.renderAudioPlaylist = function( wpAudioPlaylist, playListNumber ) {
		var myRenderApp = this,
			mediaIds = ( wpAudioPlaylist || '' ).match( /ids\=\"(.*?)\"/ );
		if ( ! mediaIds || ! mediaIds[ 1 ] ) {
			return ''; // Something's wrong
		}
		// HTML Vide Playlist 
		var playlistHTML = function( audioIds ) {
			var tracks = [];
			for ( var j = 0, aud, obj; j < audioIds.length; j++ ) {
				aud = wp.media.attachment( +audioIds[ j ] ).attributes;
				if ( ! aud.url ) continue; // Just in case the media was deleted
				obj = {
					src: aud.url.replace( myRenderApp.base_url, '' ),
					type: aud.mime,
					title: aud.title,
					caption: aud.caption,
					description: aud.description,
					meta: aud.meta,
					image: {
						src: aud.image.src.replace( myRenderApp.base_url, '' ),
						width: aud.image.width,
						height: aud.image.height
					},
					thumb: {
						src: aud.thumb.src.replace( myRenderApp.base_url, '' ),
						width: aud.thumb.width,
						height: aud.thumb.height
					}
				};
				tracks.push( obj );
			}
			if ( ! tracks.length ) {
				return '';
			}
			var html = [ '<div id="playlist-' + playListNumber + '" class="wp-playlist wp-video-playlist wp-playlist-light" data-shortcode="' + encodeURIComponent( wpAudioPlaylist ) + '">' ];
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
			var playlistNode = document.getElementById( 'tmp_audio_playlist-' + playListNumber ) || false;
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
			renderPlaylist( renderPlaylist, 250 );
		}
		return true;
	};



	window.MmdPreview = MmdPreview;


})( window.jQuery );
