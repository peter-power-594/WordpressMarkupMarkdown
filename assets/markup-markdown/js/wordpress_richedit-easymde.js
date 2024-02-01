(function( $, _win, _doc ) {

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
			if ( _win.wp && _win.wp.pluginMarkupMarkdown && _win.wp.pluginMarkupMarkdown.homeURL ) {
				homeURL = _win.wp.pluginMarkupMarkdown.homeURL;
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
				widget: function() {},
				multisel: function() {}
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
		// Build the toolbar
		var spell_check = '',
			toolbar = [];
		if ( _win.wp && _win.wp.pluginMarkupMarkdown && _win.wp.pluginMarkupMarkdown.spellChecker ) {
			spell_check = _win.wp.pluginMarkupMarkdown.spellChecker;
		}
		var n = 0;
		for ( var b = 0, slug = '', buttons = _self.toolbarButtons; b < buttons.length; b++ ) {
			slug = buttons[ b ];
			if ( slug === "pipe" ) {
				toolbar.push( "|" );
			}
			else if ( /spell[-_]*check/.test( slug ) ) {
				$.each(spell_check, function(myLang, targetLang) {
					n++;
					if ( n <= 1 ) {
						return true; // Skip first default language
					}
					if ( n === 2 ) {
						toolbar.push( "|" );
					}
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
			}
			else if ( /wps[-_]*image/.test( slug ) ) {
				toolbar.push({
					name: "wpsimage",
					action: function( editor ) {
						activeWidget = _self;
						if ( ! activeWidget.widgetCounter ) {
							activeWidget.widgetCounter = 1;
						}
						mediaFrame.open();
					},
					className: "fa fa-images",
					title: "Image"
				});
			}
			else {
				toolbar.push( slug.replace( '_', '-' ) );
			}
		}
		if ( n < 1 ) {
			spell_check = 'none';
		}
		// Editor config
		var editorConfig = {
			autoDownloadFontAwesome: false,
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
		if ( ! _win.wp.pluginMarkupMarkdown ) {
			_win.wp.pluginMarkupMarkdown = {};
		}
		if ( ! _win.wp.pluginMarkupMarkdown.instances ) {
			_win.wp.pluginMarkupMarkdown.instances = [];
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
		_win.wp.pluginMarkupMarkdown.instances.push( _self.instance.editor );
	};


	/**
	 * Callback
	 * @param widgetShortCode string The Gallery / Playlist shortcode
	 * @returns Object The Code Editor document updated with the widget shortcodes
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
	 * Iframe multiple selection callback
	 * @param markdownCode string The new markdown media content
	 * @returns Object The Code Editor document updated with the media markdown
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
	 * @param text String The source code used for the rendering
	 * @param preview Object The html node preview
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
		if ( ( ! _self.toolbarButtons || ! _self.toolbarButtons.length ) && _win.wp && _win.wp.pluginMarkupMarkdown && _win.wp.pluginMarkupMarkdown.toolbarButtons ) {
			_self.toolbarButtons = _win.wp.pluginMarkupMarkdown.toolbarButtons;
		}
		else {
			_self.toolbarButtons = [
				"bold", "italic", "heading", "spell_checker", "pipe", "quote",
				"unordered_list", "ordered_list", "pipe",
				"link", "wpsimage", "table", "pipe",
				"guide", "preview"
			];
		}
		_self.core( textarea );
	};


	$( _doc ).ready(function() {
		$( _doc.body ).addClass( 'easymde' );
		_doc.addEventListener( 'CodeMirrorSpellCheckerReady', function() {
			$( '#wp-content-editor-container' ).addClass( 'ready' );
		});
		$( '#wp-content-editor-container .wp-editor-area' ).each(function() {
			new MarkupMarkdownWidget( this );
		});
	});


	_win.MarkupMarkdown = MarkupMarkdownWidget;


})( window.jQuery, window, document );


(function( $, _win, _doc ) {


	function MarkupMarkdownOptions() {
		var _self = this,
			userTimerId = 0,
			$userPanel = $( '.mmd-easymde-prefs' );
		_self.userOptions = _self.getUserOptions( $userPanel );
		_self.stickyInst = [];
		$userPanel.on( 'click', function() {
			if ( userTimerId ) {
				clearTimeout( userTimerId );
			}
			userTimerId = setTimeout(function() {
				_self.saveUserOptions( $userPanel );
				_self.setStickyToolbar();
			}, 100 );
		});
		_self.setStickyToolbar();
	}


	/**
	 * EasyMDE save user options edit preferences
	 * @since 2.5
	 * @param $userPanel object The jQuery Panel node
	 * @returns object The user preferences
	 */
	MarkupMarkdownOptions.prototype.getUserOptions = function( $userPanel ) {
		var userOptions = {};
		$userPanel.find( 'input[type="checkbox"]' ).each(function() {
			userOptions[ this.id || 'none' ] = this.checked ? this.value : 0;
		});
		return userOptions;
	};


	/**
	 * Send a an ajax request to save the user edit preferences
	 * @since 2.5
	 * @param $userPanel Object The jQuery Panel node
	 * @returns void
	 */
	MarkupMarkdownOptions.prototype.saveUserOptions = function( $userPanel ) {
		var _self = this;
		_self.userOptions = _self.getUserOptions( $userPanel );
		$.post( _win.ajaxurl, {
			action: 'mmduser-editoptions',
			options: _self.userOptions,
			mmdeditoptionsnonce: $( '#mmdeditoptionsnonce' ).val()
		});
	};


	/**
	 * Make EasyMDE toolbars sticky if the height of the panel is greater than the screen's height
	 * @since 2.5
	 * @returns Void
	 */
	MarkupMarkdownOptions.prototype.setStickyToolbar = function() {
		var _self = this,
			stickyToolbars = parseInt( _self.userOptions.mmd_sticky_toolbar || 0, 10 ),
			minHeight = $( _win ).height() - ( $( '#wpadminbar ' ).height() || 0 ),
			initSticky = function( $el ) {
				if ( $el.hasClass( 'sicky-toolbar' ) ) {
					return false;
				}
				var $toolbar = $el.find( '.editor-toolbar:eq(0)' ),
					currHeight = $el.height() - $toolbar.height();
				if ( currHeight < minHeight ) {
					// Don't set to sticky if the field is shorter than the screen 
					return false;
				}
				$el.addClass( 'sicky-toolbar' ); $toolbar.addClass( 'mmd-sticky' );
				if ( ! $el.find( '.editor-endbar' ).length ) {
					$el.append( $( '<div class="editor-endbar"></div>' ) );
				}
				_self.stickyInst.push( new _win.Waypoint.Sticky({
					element: $toolbar[ 0 ]
				}) );
				_self.stickyInst.push( new _win.Waypoint({
					element: $el.children( '.editor-endbar' )[ 0 ],
					handler: function( direction ) {
						if ( direction === 'down' ) {
							$( this.element ).parent().addClass( 'mmd-sticky-end' );
						}
						else if ( direction === 'up' ) {
							$( this.element ).parent().removeClass( 'mmd-sticky-end' );
						}
					},
					offset: 'bottom-in-view'
				}) );
				return true;
			}; 
		if ( ! stickyToolbars ) {
			// First unbind to avoid js errors
			$( _doc ).off( 'keyup.mmd_doc_sticky_toolbar' );
			$( _doc.body ).off( 'click.mmd_body_sticky_toolbar' );
			$( _win ).off( 'resize.mmd_win_sticky_toolbar' );
			// Nest disable existing sticky toolbars if need be
			_self.stickyInst = _self.stickyInst || [];
			if ( _self.stickyInst.length ) {
				_win.Waypoint.destroyAll(); // Destroy all of them
				_self.stickyInst = [];
			}
			// Cleanup remaining attributes or nodes
			$( '.EasyMDEContainer ').each(function() {
				var $container = $( this ).removeClass( 'sicky-toolbar' ).removeClass( 'mmd-sticky-end' );
				$container.find( '.editor-toolbar' ).removeClass( 'mmd-sticky' ).each(function() {
					$( this ).removeAttr( 'style' );
					if ( $( this ).parent().hasClass( 'sticky-wrapper' ) ) {
						$container.prepend( this );
					}
				});
				$container.find( '.sticky-wrapper' ).remove();
			});
		}
		else {
			// Initialize existing sticky toolbars if need be
			$( '.EasyMDEContainer' ).each(function() {
				initSticky( $( this ) );
			});
			// Quick fix to keep the correct toolbar width
			$( _win ).off( 'resize.mmd_win_sticky_toolbar' )
				.on( 'resize.mmd_win_sticky_toolbar', function() {
					$( '.EasyMDEContainer' ).each(function() {
						$( this ).find( '.editor-toolbar.mmd-sticky' ).css({
							width: Math.ceil( $( this ).width() ) - 22
						});
					});
				}).trigger( 'resize.mmd_win_sticky_toolbar' );
			// Quick fix to refresh trigger points with accordeaon like elements
			var waypointTimerID = 0,
				$clickedEditor;
			$( _doc.body ).off( 'click.mmd_body_sticky_toolbar' )
				.on( 'click.mmd_body_sticky_toolbar', function( event ) {
					if ( ! waypointTimerID ) {
						waypointTimerID = setTimeout(function() {
							_win.Waypoint.refreshAll();
							waypointTimerID = 0;
						}, 450);
					}
					$clickedEditor = $( event.target ).closest( '.EasyMDEContainer' );
				});
			// Quick fix to initialize sticky when the height is growing
			$( _doc ).off( 'keyup.mmd_doc_sticky_toolbar' )
				.on( 'keyup.mmd_doc_sticky_toolbar', function( event ) {
					if ( ! waypointTimerID ) {
						waypointTimerID = setTimeout(function() {
							_win.Waypoint.refreshAll();
							waypointTimerID = 0;
						}, 450);
					}
					if ( event.keyCode && event.keyCode === 13 && $clickedEditor ) {
						if ( $clickedEditor.length && ! $clickedEditor.hasClass( 'sicky-toolbar' ) ) {
							setTimeout(function() {
								minHeight = $( _win ).height() - ( $( '#wpadminbar ' ).height() || 0 );
								initSticky( $clickedEditor );
								$( _win ).trigger( 'resize.mmd_win_sticky_toolbar' );
							}, 1000 );
						}
					}
				});
		}
	};


	$( _doc ).ready(function() {
		new MarkupMarkdownOptions();
	});


})( window.jQuery, window, document );
