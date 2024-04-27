/* global wp */

(function( $, _win, _doc ) {

	var mediaFrame = {};
		mediaPreview = {},
		activeWidget = {},
		fieldNumber = 0,
		homeURL = '',
		spellCheckerReady = 0;


	function MarkupMarkdownWidget( textarea ) {
		this.todo = [];
		this.updating = false;
		this.isRendering = false;
		this.instance = {};
		this.init( textarea );
	}


	/**
	 * Initialize Wordpress Media Frame to upload or add a unique media item
	 *
	 * @returns {Void}
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
		var $textarea = $( textarea || '#none' );
		if ( ! $textarea.length ) {
			return false;
		}
		if ( ! $textarea.attr( 'id' ) && $textarea.attr( 'name' ) ) {
			$textarea.attr( 'id', $textarea.attr( 'name' ).replace( /[^a-zA-Z0-9]/g, '' ) );
		}
		var _self = this,
			isAdmin = $( 'body' ).hasClass( 'wp-admin' ) ? 1 : 0,
			isACF = $textarea.parent().hasClass( 'acf-input' ) ? 1 : 0;
		// Let the user upload contents
		_self.mediaUploader();
		// Build the toolbar
		var spell_check = { disabled: 1 },
			toolbar = [];
		if ( _win.wp && _win.wp.pluginMarkupMarkdown && _win.wp.pluginMarkupMarkdown.spellChecker ) {
			spell_check = _win.wp.pluginMarkupMarkdown.spellChecker;
			if ( typeof spell_check !== 'object' ) {
				spell_check = { disabled: 1 };
			}
		}
		var n = 0;
		for ( var b = 0, slug = '', buttons = _self.toolbarButtons; b < buttons.length; b++ ) {
			slug = buttons[ b ];
			if ( slug === "pipe" ) {
				toolbar.push( "|" );
			}
			else if ( /spell[-_]*check/.test( slug ) && ! spell_check.disabled ) {
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
						if( ! mediaFrame || ! mediaFrame.title ) {
							mediaFrame.initialize();
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
			spell_check = { disabled: 1 };
		}
		if ( isACF || ! isAdmin ) {
			var minimalToolbar = [];
			for ( var b = 0; b < toolbar.length; b++ ) {
				if ( ! /fullscreen|side/.test( toolbar[ b ] || '' ) ) {
					minimalToolbar.push( toolbar[ b ] );
				}
			}
			toolbar = minimalToolbar;
		}
		// Editor config
		var editorConfig = {
			autoDownloadFontAwesome: false,
			element: $textarea.get( 0 ),
			toolbar: toolbar,
			renderingConfig: {
				codeSyntaxHighlighting: true
			},
			previewRender: function( text, preview ) {
				mediaPreview.flushQueue();
				text = _self.previewRender( text, preview );
				setTimeout(function() {
					mediaPreview.runQueue();
					$( _win ).trigger( 'resize.mmd_win_sticky_toolbar' );
				}, 10);
				return text;
			}
		};
		if ( spell_check && spell_check !== 'none' && ! spell_check.disabled ) {
			// Reference: https://github.com/Ionaru/easy-markdown-editor/pull/333/files
			editorConfig.spellChecker = function( spellCheckConfig ) {
				spellCheckConfig.language = spell_check;
				CodeMirrorSpellChecker( spellCheckConfig );
			};
		}
		else {
			editorConfig.spellChecker = false;
		}
		_self.instance.editor = new EasyMDE( editorConfig );
		_self.instance.editor.codemirror.on("change", function() {
			$textarea.val( _self.instance.editor.value() );
		});
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
		if ( ! spell_check || ( spell_check.disabled && spell_check.disabled === 1 ) || spell_check === 'none' ) {
			// Event need to be triggered manually
			document.dispatchEvent( new Event( 'CodeMirrorSpellCheckerReady' ) );
			if ( isACF > 0 ) {
				$textarea.closest( '.acf-input' ).addClass( 'ready' );
			}
			else {
				$( '#wp-content-editor-container' ).addClass( 'ready' );
				new MarkupMarkdownOptions();
				$( _doc.body ).addClass( 'markupmarkdown-ready' );
			}
		}
		if ( isACF > 0 ) {
			setTimeout(function() {
				_self.instance.editor.codemirror.refresh();
			}, 250 );
		}
	};


	/**
	 * Callback
	 * @param {String} widgetShortCode The Gallery / Playlist shortcode
	 * @returns {Object} The Code Editor document updated with the widget shortcodes
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
	 * @param {String} markdownCode  The new markdown media content
	 * @returns {Object} The Code Editor document updated with the media markdown
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
	 * @param {String} text The source code used for the rendering
	 * @param {Object} preview The html node preview
	 * @returns {string} The default text preview
	 */
	MarkupMarkdownWidget.prototype.previewRender = function( text, preview ) {
		var _self = this;
		text = _self.instance.editor.markdown( text );
		// Render the headings
		text = text.replace( /<h\d[^\>]*>.*?\{[^\}]+\}<\/h\d>/g, function( wpHeadline ) {
			return mediaPreview.processTask( 'convertHeading', wpHeadline );
		});
		// Render the gallery shortcode. Ref /wp-includes/js/tinymce/plugins/wpgallery/plugin.js
		var galCounter = 0,
			getRandomNodeID = function( min, max ) {
				return ( 'tmp_node' + ( Math.random() * 999999999 ) ).replace( /\.|\,/, '' );
			};
		text = text.replace( /\[gallery([^\]]*)\]/g, function( wpGallery ) {
			galCounter++;
			return '<div id="' + getRandomNodeID() + '" class="tmp_media" data-pointer="tmp_gallery-' + galCounter + '">'
				+ mediaPreview.add2Queue( 'gallery', wpGallery, galCounter ) + '</div>';
		});
		text = text.replace( /\[playlist([^\]]*)\]/g, function( wpPlaylist ) {
			galCounter++;
			if ( /type\=\"video\"/.test( wpPlaylist ) ) {
				return '<div id="' + getRandomNodeID() + '" class="tmp_media" data-pointer="tmp_video_playlist-' + galCounter + '">'
					+ mediaPreview.add2Queue( 'videoPlaylist', wpPlaylist, galCounter ) + '</div>';
			}
			else {
				return '<div id="' + getRandomNodeID() + '" class="tmp_media" data-pointer="tmp_audio_playlist-' + galCounter + '">'
					+ mediaPreview.add2Queue( 'audioPlaylist', wpPlaylist, galCounter ) + '</div>';
			}
		});
		// Render the images
		text = text.replace( /<a.*?><img.*?>\{[^\}]+\}<\/a>/g, function( wpImage ) {
			return mediaPreview.processTask( 'convertImage', wpImage, false );
		});
		text = text.replace( /<img.*?>\{[^\}]+\}/g, function( wpImage ) {
			return mediaPreview.processTask( 'convertImage', wpImage, false );
		});
		text = text.replace( /<p><figure/, '<figure' ).replace( /<\/figure><\/p>/, '</figure>' );
		// Render the audio shortcodes
		var audCounter = 0;
		text = text.replace( /\[audio([^\]]*)\]\[\/audio\]/g, function( wpAudio ) {
			return mediaPreview.processTask( 'convertAudio', wpAudio, audCounter++ );
		});
		// Render the video shortcodes
		var vidCounter = 0;
		text = text.replace( /\[video([^\]]*)\]\[\/video\]/g, function( wpVideo ) {
			return mediaPreview.processTask( 'convertVideo', wpVideo, vidCounter++ );
		});
		if ( ! _self.isRendering ) {
			_self.isRendering = setTimeout(function() {
				$( window ).trigger( 'resize.mmd_preview' );
				_self.isRendering = 0;
			}, 450);
		}
		return text;
	};


	MarkupMarkdownWidget.prototype.init = function( textarea ) {
		var _self = this;
		if ( ( ! _self.toolbarButtons || ! _self.toolbarButtons.length ) && _win.wp && _win.wp.pluginMarkupMarkdown && _win.wp.pluginMarkupMarkdown.toolbarButtons ) {
			_self.toolbarButtons = _win.wp.pluginMarkupMarkdown.toolbarButtons;
		}
		else {
			_self.toolbarButtons = [
				"bold", "italic", "heading", "spell-checker", "pipe", "quote", "unordered-list", "ordered-list", "pipe", "link", "wpsimage", "table", "pipe", "fullscreen", "side-by-side", "preview", "guide"
			];
		}
		_self.core( textarea );
	};


	$( _doc ).ready(function() {
		$( _doc.body ).addClass( 'easymde' );
		var primaryAreaEnabled = 1;
		if ( wp.pluginMarkupMarkdown && typeof wp.pluginMarkupMarkdown.primaryArea !== 'undefined' ) {
			if ( ! wp.pluginMarkupMarkdown.primaryArea || isNaN( parseInt( wp.pluginMarkupMarkdown.primaryArea, 10 ) ) ) {
				// EasyMDE has been disabled on the primary post editor area but might be enabled with custom fields
				primaryAreaEnabled = 0;
			}
		}
		if ( ! primaryAreaEnabled ) {
			// Only custom fields or other fields managed by addons are used with Markdown
			// We just need to setup a few UI options like sticky and exit
			_doc.addEventListener( 'CodeMirrorSpellCheckerReady', function() {
				// Stuff inside the custom event CodeMirrorSpellCheckerReady are callbacks
				new MarkupMarkdownOptions();
				// Trigger a refresh to get real boxe sizes just in case
				$( _doc.body ).addClass( 'markupmarkdown-ready' )
					.trigger( 'click.mmd_body_sticky_toolbar' );
			});
			return true;
		}
		// Primary content used with markdown. Need to separate backend and frontend
		_doc.addEventListener( 'CodeMirrorSpellCheckerReady', function() {
			if ( $editorContainer && ! $editorContainer.hasClass( 'ready' ) ) {
				// Be careful as the event _CodeMirrorSpellCheckerReady_ can be triggered multiple times
				$editorContainer.addClass( 'ready' );
				// Initialize sticky (and other options if need be)
				new MarkupMarkdownOptions();
			}
			// Trigger a refresh to get real boxe sizes just in case
			$( _doc.body ).addClass( 'markupmarkdown-ready' )
				.trigger( 'click.mmd_body_sticky_toolbar' );
		});
		// Default is backend
		var $editorContainer = $( '#wp-content-editor-container' );
		if ( $editorContainer.length ) {
			// Initialize EasyMDE on the main content
			$editorContainer.addClass( 'markupmarkdown' )
				.find( '.wp-editor-area' ).each(function() {
					new MarkupMarkdownWidget( this );
				});
			return true;
		}
		// Fallback with the frontend for layer case with the ACF plugin
		// Custom fields will be trigger from the addon, only need to check for the main content
		$editorContainer = $( '.acf-input #acf-_post_content' ).parent();
		if ( $editorContainer.length ) {
			// Initialize EasyMDE on the main content
			$editorContainer.addClass( 'markupmarkdown' );
			new MarkupMarkdownWidget( $( '#acf-_post_content' ) );
			return true;
		}
	});


	_win.MarkupMarkdown = MarkupMarkdownWidget;


	/**
	 * From here an old school implementation to make the EasyMDE toolbars sticky with WayPoint
	 * We _don't rely_ on CoreMirror events but with the user interactions from the surrounded containers
	 */
	function MarkupMarkdownOptions() {
		var _self = this;
		if ( $( _doc.body ).hasClass( 'mmd-options-ready' ) ) {
			return false;
		}
		$( _doc.body ).addClass( 'mmd-options-ready' );
		var userTimerId = 0,
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
	 * @param {Object} $userPanel The jQuery Panel node
	 * @returns {Object} The user preferences
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
	 * @param {Object} $userPanel The jQuery Panel node
	 * @returns {Void}
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
	 *
	 * @since 2.5
	 *
	 * @returns {Void}
	 */
	MarkupMarkdownOptions.prototype.setStickyToolbar = function() {
		var _self = this,
			isStickyEnabled = parseInt( _self.userOptions.mmd_sticky_toolbar || 0, 10 ),
			isStickyActive = 0,
			minHeight = $( _win ).height() - ( $( '#wpadminbar ' ).height() || 0 ),
			initSticky = function( $el ) {
				if ( $el.hasClass( 'sicky-toolbar' ) ) {
					// Already active, exit
					return false;
				}
				var $toolbar = $el.find( '.editor-toolbar:eq(0)' ),
					currHeight = $el.height() - $toolbar.height();
				if ( currHeight < minHeight ) {
					// Don't set to sticky if the field is shorter than the screen
					return false;
				}
				isStickyActive = 1;
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
			},
			destroySticky = function() {
				if ( ! _self.stickyInst || ! _self.stickyInst.length ) {
					return false;
				}
				isStickyActive = 0;
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
				return true;
			},
			disableSticky = function() {
				// First unbind to avoid js errors
				$( _doc ).off( 'keyup.mmd_doc_sticky_toolbar' );
				$( _doc.body ).off( 'click.mmd_body_sticky_toolbar' );
				$( _win ).off( 'resize.mmd_win_sticky_toolbar' );
				// Trash html modifications
				destroySticky();
			};
		if ( ! isStickyEnabled ) {
			disableSticky();
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
				$clickedEditor,
				refreshTriggerPoints = function() {
					if ( ! waypointTimerID ) {
						waypointTimerID = setTimeout(function() {
							if ( $( '.editor-toolbar.fullscreen' ).length ) {
								destroySticky(); // Keep the our custom handlers
							}
							else if ( ! isStickyActive ) {
								setTimeout(function() { // Restart
									$( '.EasyMDEContainer' ).each(function() {
										initSticky( $( this ) );
									});
									$( _win ).trigger( 'resize.mmd_win_sticky_toolbar' );
								}, 50);
							}
							else {
								_win.Waypoint.refreshAll(); // Restart
							}
							waypointTimerID = 0;
						}, 450);
					}
				};
			$( _doc.body ).off( 'click.mmd_body_sticky_toolbar' )
				.on( 'click.mmd_body_sticky_toolbar', function( event ) {
					refreshTriggerPoints();
					$clickedEditor = $( event.target ).closest( '.EasyMDEContainer' );
				});
			// Quick fix to initialize sticky when the height is growing
			$( _doc ).off( 'keyup.mmd_doc_sticky_toolbar' )
				.on( 'keyup.mmd_doc_sticky_toolbar', function( event ) {
					refreshTriggerPoints();
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


})( window.jQuery, window, document );
