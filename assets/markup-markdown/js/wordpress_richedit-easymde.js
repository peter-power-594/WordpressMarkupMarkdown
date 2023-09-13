(function( $ ) {

	var mediaFrame = {};
		mediaPreview = {},
		activeWidget = {},
		fieldNumber = 0,
		homeURL = '';


	function MarkupMarkdownWidget( textarea ) {
		this.todo = [];
		this.updating = false;
		this.instance = {};
		this.init( textarea );
	}


	/**
	 * Initialize Wordpress Media Frame to upload or add a unique media item
	 *
	 * @returns Void
	 */
	MarkupMarkdownWidget.prototype.mediaUploader = function() {
		var _self = this;
		if ( ! homeURL.length ) {
			if ( wp && wp.pluginMarkupMarkdown && wp.pluginMarkupMarkdown.homeURL ) {
				homeURL = wp.pluginMarkupMarkdown.homeURL;
			}
			else {
				var args = decodeURIComponent( jQuery( 'script[src*="wordpress_richedit-easymde"]' ).attr( 'src' ) );
				homeURL = /home_url/.test( args ) ? args.replace( /.*?home_url=/, '' ).replace( /&.*?$/, '' ) : '/';
			}
			homeURL = homeURL.replace( /^(.*?)(\.[a-z]+\/).*?$/, '$1$2' );
			_self.home_url = homeURL;
		}
		if ( ! _self.home_url && homeURL ) {
			_self.home_url = homeURL;
		}
		if ( mediaFrame && mediaFrame.title ) {
			return false;
		}
		mediaPreview = new MmdPreview({
			base_url: _self.home_url,
			callbacks:{
				widget: function() {
				},
				multisel: function() {
				}
			}
		});
		mediaFrame = new MmdMedia({
			base_url: _self.home_url,
			callbacks:{
				widget: function( wpShortCode ) {
					if ( wpShortCode && wpShortCode.length ) {
						_self.mediaWidgetCallBack( wpShortCode );
					}
				},
				multisel: function( markdownCode ) {
					if ( markdownCode && markdownCode.length ) {
						_self.mediaMultiselCallBack( markdownCode );
					}
				}
			}
		});
	};


	MarkupMarkdownWidget.prototype.core = function( textarea ) {
		if ( ! textarea || ! $( textarea ).length ) {
			return false;
		}
		var _self = this;
		// Let the user upload contents
		_self.mediaUploader();
		var spell_check = '',
			toolbar = [ "bold", "italic", "heading" ];
		if ( wp && wp.pluginMarkupMarkdown && wp.pluginMarkupMarkdown.spellChecker ) {
			spell_check = wp.pluginMarkupMarkdown.spellChecker;
		}
		n = 0;
		$.each(spell_check, function( myLang ) {
			n++;
			if ( n <= 1 ) {
			return true; // Skip first default language
			}
			if ( n === 2 ) {
			toolbar.push( "|" );
			}
			var targetLang = spell_check[myLang];
			toolbar.push({
				name: "wpsi18n_" + targetLang.code,
				action: function( editor ) {
					var cm = _self.instance.editor.codemirror,
					doc = cm.getDoc(),
					sel = doc.getSelection() || false;
					if ( sel && sel.length ) {
						_self.instance.i18nAdded = 1;
						return doc.replaceSelection(
							'<span lang="' + targetLang.code + '">' + sel + '</span>'
						);
					}
				},
				className: "i18n " + targetLang.code,
				text: targetLang.code.toUpperCase(),
				title: targetLang.label
			});
		});
		if ( n < 1 ) {
			spell_check = 'none';
		}
		toolbar.push( "|" );
		toolbar.push( "quote" );
		toolbar.push( "unordered-list" );
		toolbar.push( "ordered-list" );
		toolbar.push( "|" );
		toolbar.push( "link" );
		toolbar.push({
			name: "wpsimage",
			action: function( editor ) {
				activeWidget = _self;
				if ( ! activeWidget.widgetCounter ) {
					activeWidget.widgetCounter = 1;
				}
				mediaFrame.open();
			},
			className: "fa fa-picture-o",
			title: "Image"
		});
		toolbar.push( "table" );
		toolbar.push( "|" );
		toolbar.push( "guide" );
		toolbar.push( "preview" );
		// Editor config
		var editorConfig = {
			element: $( textarea )[ 0 ],
			toolbar: toolbar,
			renderingConfig: {
				codeSyntaxHighlighting: true
			},
			previewRender: function( text, preview ) {
				return _self.previewRender( text, preview );
			}
		};
		if ( spell_check && spell_check !== 'none' ) {
			// Reference: https://github.com/Ionaru/easy-markdown-editor/pull/333/files
			editorConfig.spellChecker = function( spellCheckConfig ) {
				spellCheckConfig.language = spell_check;
				CodeMirrorSpellChecker( spellCheckConfig );
			};
		}
		else {
			editorConfig.spellChecker = false;
			document.dispatchEvent( new Event( 'CodeMirrorSpellCheckerReady' ) );
		}
		_self.instance.editor = new EasyMDE( editorConfig );
		if ( ! wp.pluginMarkupMarkdown.instances ) {
			wp.pluginMarkupMarkdown.instances = [];
		}
		_self.fieldNumber = fieldNumber++; 
		var mediaCounters = $( textarea ).val().match( /\"myset.*?\s/g );
		if ( mediaCounters && mediaCounters.length ) {
			var startCounter = 0;
			for ( var c = 0, tmp; c < mediaCounters.length; c++ ) {
				tmp = parseInt( mediaCounters[ c ].replace( /\d_/, '' ).replace( /[^\d]+/, '' ), 10 );
				if ( tmp > startCounter ) {
					startCounter = tmp;
				}
			}
			_self.widgetCounter = startCounter + 1;
		}
		wp.pluginMarkupMarkdown.instances.push( _self.instance.editor );
	};


	/**
	 *  @param String widgetShortCode The Gallery / Playlist shortcode
	 *  @returns Object The Code Editor document updated with the widget shortcodes
	 */
	MarkupMarkdownWidget.prototype.mediaWidgetCallBack = function( widgetShortCode ) {
		activeWidget.widgetCounter++;
		// Warning !
		// instance.editor => easeMDE Editor Object
		// instance.editor.codemirror Object => editor.codemirror
		var cm = activeWidget.instance.editor.codemirror,
			doc = cm.getDoc(), // Code Mirror document
			cur = doc.getCursor(); // Code Mirror current cursor position
		return doc.replaceRange( widgetShortCode, cur );
	};


	/**
	 *  @param String markdownCode The new markdown media content
	 *  @returns Object The Code Editor document updated with the media markdown
	 */
	MarkupMarkdownWidget.prototype.mediaMultiselCallBack = function( markdownCode ) {
		// Warning !
		// instance.editor => easeMDE Editor Object
		// instance.editor.codemirror Object => editor.codemirror
		var cm = activeWidget.instance.editor.codemirror,
			doc = cm.getDoc(), // Code Mirror document
			cur = doc.getCursor(); // Code Mirror current cursor position
		return doc.replaceRange( markdownCode.replace( /myset\%d/g, 'myset' + activeWidget.fieldNumber + '_' + activeWidget.widgetCounter ), cur );
	};


	/**
	 * EasyMDE custom preview callbacks to support WP specific features
	 * @since 2.1
	 * @param string text The source code used for the rendering
	 * @param object The html node preview
	 * @returns string The default text preview
	 */
	MarkupMarkdownWidget.prototype.previewRender = function( text, preview ) {
		var _self = this;
		text = _self.instance.editor.markdown( text );
		// Render the gallery shortcode. Ref /wp-includes/js/tinymce/plugins/wpgallery/plugin.js
		var galCounter = 0;
		text = text.replace( /\[gallery([^\]]*)\]/g, function( wpGallery ) {
			galCounter++;
			var myGallery = '<div id="tmp_gallery-' + galCounter + '"></div>';
			mediaPreview.gallery( wpGallery, galCounter );
			return myGallery;
		});
		text = text.replace( /\[playlist([^\]]*)\]/g, function( wpPlaylist ) {
			galCounter++;
			if ( /type\=\"video\"/.test( wpPlaylist ) ) {
				var myPlaylist = '<div id="tmp_video_playlist-' + galCounter + '"></div>';
				mediaPreview.videoPlaylist( wpPlaylist, galCounter );
				return myPlaylist;
			}
			else {
				var myPlaylist = '<div id="tmp_audio_playlist-' + galCounter + '"></div>';
				mediaPreview.audioPlaylist( wpPlaylist, galCounter );
				return myPlaylist;
			}
		});
		// Render the images
		text = text.replace( /<a.*?><img.*?>\{\.align[a-z]+\}<\/a>/g, function( wpImage ) {
			var fig = mediaPreview.convertImage( wpImage );
			return fig;
		});
		text = text.replace( /<img.*?>\{\.align[a-z]+\}/g, function( wpImage ) {
			var fig = mediaPreview.convertImage( wpImage );
			return fig;
		});
		text = text.replace( /<p><figure/, '<figure' ).replace( /<\/figure><\/p>/, '</figure>' );
		// Render the audio shortcodes
		var audCounter = 0;
		text = text.replace( /\[audio([^\]]*)\]\[\/audio\]/g, function( wpAudio ) {
			return mediaPreview.convertAudio( wpAudio, audCounter++ );
		});
		// Render the video shortcodes
		var vidCounter = 0;
		text = text.replace( /\[video([^\]]*)\]\[\/video\]/g, function( wpVideo ) {
			return mediaPreview.convertVideo( wpVideo, vidCounter++ );
		});
		return text;
	};


	MarkupMarkdownWidget.prototype.init = function( textarea ) {
		var _self = this;
		_self.core( textarea );
	};


	$( document ).ready(function() {
		$( 'body' ).addClass( 'easymde' );
		document.addEventListener( 'CodeMirrorSpellCheckerReady', function() {
			$( '#wp-content-editor-container' ).addClass( 'ready' );
		});
		$( '#wp-content-editor-container .wp-editor-area' ).each(function() {
			new MarkupMarkdownWidget( this );
		});
	});

	window.MarkupMarkdown = MarkupMarkdownWidget;


})( window.jQuery );
