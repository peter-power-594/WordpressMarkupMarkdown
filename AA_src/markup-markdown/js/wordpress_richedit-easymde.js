/* global wp, mmd_wpr_vars, EasyMDE */

/**
 * @preserve The Markup Markdown's EasyMDE Primary Module
 * @desc Core classes to handle the markdown editor inside the Wordpress admin edit screen
 * @author Pierre-Henri Lavigne <lavigne.pierrehenri@proton.me>
 * @version 1.6.4
 * @license GPL 3 - https://www.gnu.org/licenses/gpl-3.0.html#license-text
 */
(function( $, _win, _doc ) {

	var mediaFrame = {},
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
	 * @returns {Integer} 1 if the WP native library was activated or 0 if not available
	 */
	MarkupMarkdownWidget.prototype.mediaUploader = function() {
		var _self = this,
			homeURL = '';
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
		if ( _win.wp && _win.wp.pluginMarkupMarkdown && typeof _win.wp.pluginMarkupMarkdown.mediaUploader === 'number' ) {
			if ( ! _win.wp.pluginMarkupMarkdown.mediaUploader ) {
				return false;
			}
		}
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
		return true;
	};


	MarkupMarkdownWidget.prototype.core = function( textarea ) {
		var $textarea = $( textarea || '#none' );
		if ( ! $textarea.length || $textarea.hasClass( 'mmd-running' ) ) {
			return false;
		}
		if ( ! $textarea.attr( 'id' ) && $textarea.attr( 'name' ) ) {
			$textarea.attr( 'id', $textarea.attr( 'name' ).replace( /[^a-zA-Z0-9]/g, '' ) );
		}
		// Disable Grammarly on the native textare
		$textarea.addClass( 'mmd-running' ).attr({
			'data-gramm': 'false',
			'data-gramm_editor': 'false',
			'data-enable-grammarly': 'false'
		});
		var _self = this,
			isAdmin = $( 'body' ).hasClass( 'wp-admin' ) ? 1 : 0,
			isSecondary = 0,
			mediaUploader = 1;
		// isSecondary: 1 = textarea is related to a custom field, 0 = the textarea is to the primary content
		if ( $textarea.parent().hasClass( 'acf-input' ) ) {
			// ACF Custom Field
			isSecondary = 1;
		}
		else if ( $textarea.parents( 'form-field' ) ) {
			// Category / Tag Description field
			isSecondary = 1;
		}
		else if ( /^(acf|bbp)[-_]{1}/.test( $textarea.attr( 'id' ) || '' ) ) {
			// Category / Tag Description field
			isSecondary = 1;
		}
		// Let the user upload contents
		mediaUploader = _self.mediaUploader();
		// Build the toolbar
		var toolbar = [], defActions = {
			'mmd_bold': { action: EasyMDE.toggleBold, className: 'fa fa-bold' },
			'mmd_italic': { action: EasyMDE.toggleItalic, className: 'fa fa-italic' },
			'mmd_strikethrough': { action: EasyMDE.toggleStrikethrough, className: 'fa fa-strikethrough' },
			'mmd_heading': { action: EasyMDE.toggleHeadingSmaller, className: 'fa fa-header fa-heading' },
			'mmd_heading-smaller': { action: EasyMDE.toggleHeadingSmaller, className: 'fa fa-header fa-heading' },
			'mmd_heading-bigger': { action: EasyMDE.toggleHeadingBigger, className: 'fa fa-lg fa-header fa-heading' },
			'mmd_heading-1': { action: EasyMDE.toggleHeading1, className: 'fa fa-header fa-heading header-1' },
			'mmd_heading-2': { action: EasyMDE.toggleHeading2, className: 'fa fa-header fa-heading header-2' },
			'mmd_heading-3': { action: EasyMDE.toggleHeading3, className: 'fa fa-header fa-heading header-3' },
			'mmd_code': { action: EasyMDE.toggleCodeBlock, className: 'fa fa-code' },
			'mmd_quote': { action: EasyMDE.toggleBlockquote, className: 'fa fa-quote-left' },
			'mmd_unordered-list': { action: EasyMDE.toggleUnorderedList, className: 'fa fa-list-ul' },
			'mmd_ordered-list': { action: EasyMDE.toggleOrderedList, className: 'fa fa-list-ol' },
			'mmd_clean-block': { action: EasyMDE.cleanBlock, className: 'fa fa-eraser' },
			'mmd_link': { action: EasyMDE.drawLink, className: 'fa fa-link' },
			'mmd_image': { action: EasyMDE.drawImage, className: 'fa fa-picture-o' },
			'mmd_upload-image': { action: EasyMDE.drawUploadedImage, className: 'fa fa-image' },
			'mmd_table': { action: EasyMDE.drawTable, className: 'fa fa-table' },
			'mmd_horizontal-rule': { action: EasyMDE.drawHorizontalRule, className: 'fa fa-minus' },
			'mmd_preview': { action: EasyMDE.togglePreview, className: 'fa fa-eye no-disable' },
			'mmd_side-by-side': { action: EasyMDE.toggleSideBySide, className: 'fa fa-columns no-disable no-mobile' },
			'mmd_fullscreen': { action: EasyMDE.toggleFullScreen, className: 'fa fa-arrows-alt no-disable no-mobile' },
			'mmd_undo': { action: EasyMDE.undo, className: 'fa fa-undo' },
			'mmd_redo': { action: EasyMDE.redo, className: 'fa fa-redo' }
		};
		EasyMDE.wpsImage = function( editor ) {
			activeWidget = _self;
			if ( ! activeWidget.widgetCounter ) {
				activeWidget.widgetCounter = 1;
			}
			if( ! mediaFrame || ! mediaFrame.title ) {
				if ( mediaFrame && typeof mediaFrame.initialize !== 'function' ) {
					return false;
				}
				mediaFrame.initialize();
			}
			mediaFrame.open();
		};
		// First we check the spell checker options
		var spell_check = { disabled: 1 };
		if ( _win.wp && _win.wp.pluginMarkupMarkdown && _win.wp.pluginMarkupMarkdown.spellChecker ) {
			spell_check = _win.wp.pluginMarkupMarkdown.spellChecker;
			if ( typeof spell_check !== 'object' ) {
				spell_check = { disabled: 1 };
			}
		}
		var n = 0;
		var spellCheckLanguages = function ( myLang, targetLang ) {
			n++;
			if ( n <= 1 ) {
				return true; // Skip first default language
			}
			if ( n === 2 ) {
				toolbar.push( "|" );
			}
			var targetLangEditor = function( editor ) {
				var cm = _self.instance.editor.codemirror,
					doc = cm.getDoc(),
					sel = doc.getSelection() || false;
				if ( sel && sel.length ) {
					_self.instance.i18nAdded = 1;
					return doc.replaceSelection(
						'<span lang="' + targetLang.code + '">' + sel + '</span>'
					);
				}
			};
			toolbar.push({
				name: "mmd_wpsi18n_" + targetLang.code,
				action: targetLangEditor,
				className: "i18n " + targetLang.code,
				text: targetLang.code.toUpperCase(),
				title: targetLang.label
			});
		};
		// Then we check the language dir
		var langDir = 'ltr';
		if ( /mmd_dir\=(ltr|rtl)/.test( window.location.search || '' ) ) {
			langDir = window.location.search.match( /mmd_dir\=(ltr|rtl)/ )[ 1 ];
		}
		else if ( ( window.isRTL || 0 ) > 1 || ( 'rtl' === ( document.documentElement.dir || '' ) ) ) {
			langDir = 'rtl';
		}
		EasyMDE.switchHTMLDir = function() {
			var targetLangDir = langDir && langDir === 'ltr' ? 'rtl' : 'ltr';
			document.location.href = [
				document.location.href.replace( /mmd_dir=[a-z]+/, '' ),
				'&mmd_dir=' + targetLangDir
			].join( '' );
		};
		defActions.mmd_rtltextdir = { action: EasyMDE.switchHTMLDir, className: 'fa' + ( langDir === 'rtl' ? 's' : 'r' ) + ' fa-caret-square-left' };
		defActions.mmd_ltrtextdir = { action: EasyMDE.switchHTMLDir, className: 'fa' + ( langDir === 'ltr' ? 's' : 'r' ) + ' fa-caret-square-right' };
		// Build the toolbar
		for ( var b = 0, slug = '', targetAction = '', buttons = _self.toolbarButtons; b < buttons.length; b++ ) {
			slug = buttons[ b ];
			if ( /pipe/.test( slug ) ) {
				toolbar.push( "|" );
			}
			else if ( /spell[-_]*check/.test( slug ) ) {
				if ( ! spell_check.disabled ) {
					$.each(spell_check, spellCheckLanguages);					
				}
			}
			else if ( /wps[-_]*image/.test( slug ) ) {
				if ( mediaUploader > 0 ) {
					toolbar.push({
						name: "wpsimage",
						action: EasyMDE.wpsImage,
						className: "fa fa-images",
						title: mmd_wpr_vars && mmd_wpr_vars.wpsimage ? mmd_wpr_vars.wpsimage : "Image"
					});
				}
				else {
					toolbar.push( 'image' );
				}
			}
			else {
				targetAction = slug.replace( '_', '-' ).replace( 'mmd-', 'mmd_' );
				if ( defActions[ targetAction ] ) {
					defActions[ targetAction ].name = targetAction;
					if ( mmd_wpr_vars && mmd_wpr_vars[ targetAction ] ) {
						defActions[ targetAction ].title = mmd_wpr_vars[ targetAction ];
					}
					toolbar.push( defActions[ targetAction ] );
				}
				else {
					toolbar.push( targetAction.replace( 'mmd_', '' ) );
				}
			}
		}
		if ( n < 1 ) {
			spell_check = { disabled: 1 };
		}
		if ( isSecondary || ! isAdmin ) {
			var minimalToolbar = [];
			if ( /desc|acf|bbp/.test( $textarea.attr( 'name' ) || '' ) ) {
				// Description field, super tiny version
				for ( var c = 0; c < toolbar.length; c++ ) {
					if ( /\||bold|italic|pipe|list|link|image|preview|guide/.test( toolbar[ c ].name || toolbar[ c ] || '' ) ) {
						minimalToolbar.push( toolbar[ c ] );
					}
				}				
			}
			else {
				// Standard custom field
				for ( var d = 0; d < toolbar.length; d++ ) {
					if ( ! /fullscreen|side/.test( toolbar[ d ] || '' ) ) {
						minimalToolbar.push( toolbar[ d ] );
					}
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
			previewRenderedMarkdown: function( text, preview ) {
				mediaPreview.flushQueue();
				text = _self.previewRender( text, preview );
				setTimeout(function() {
					mediaPreview.runQueue();
					$( _win ).trigger( 'resize.mmd_win_sticky_toolbar' );
				}, 10);
				return text;
			},
			direction: langDir
		};
		if ( spell_check && spell_check !== 'none' && ! spell_check.disabled ) {
			// Reference: https://github.com/Ionaru/easy-markdown-editor/pull/333/files
			editorConfig.spellChecker = function( spellCheckConfig ) {
				spellCheckConfig.language = spell_check;
				CustomCodeMirrorSpellChecker( spellCheckConfig );
			};
		}
		else {
			editorConfig.spellChecker = false;
		}
		if ( ! _win.wp.pluginMarkupMarkdown ) {
			_win.wp.pluginMarkupMarkdown = {};
		}
		if ( ! _win.wp.pluginMarkupMarkdown.instances ) {
			_win.wp.pluginMarkupMarkdown.instances = [];
		}
		if ( _win.wp.pluginMarkupMarkdown.headingLevels ) {
			editorConfig.parsingConfig = editorConfig.parsingConfig || {};
			editorConfig.parsingConfig.headingLevels = _win.wp.pluginMarkupMarkdown.headingLevels;
		}
		_self.fieldNumber = fieldNumber++;
		// Increment HTML Media IDS
		var mediaCounters = $( textarea ).val().match( /\"myset.*?\s/g );
		if ( mediaCounters && mediaCounters.length ) {
			var startCounter = 0;
			for ( var e = 0, tmp; e < mediaCounters.length; e++ ) {
				tmp = parseInt( mediaCounters[ e ].replace( /\d_/, '' ).replace( /[^\d]+/, '' ), 10 );
				if ( tmp > startCounter ) {
					startCounter = tmp;
				}
			}
			_self.widgetCounter = startCounter + 1;
		}
		var launchEditor = function() {
			// Escape sharp signs used as order list items
			var escapeSharpSign = false;
			if ( editorConfig.parsingConfig && editorConfig.parsingConfig.headingLevels && editorConfig.parsingConfig.headingLevels.indexOf(1) === -1 ) {
				escapeSharpSign = true;
				var originalContent = $textarea.val();
				originalContent = originalContent.replace( /([\s\t]*)([\\]+)([\#]{1})\s/g, '$1$3 ' );
				originalContent = originalContent.replace( /^[\#]{1}\s/g, '\\# ' ).replace( /([\s\t]+)([\#]{1})\s/g, '$1\\# ');
				$textarea.val(  originalContent );
			}
			_self.instance.editor = new EasyMDE( editorConfig );
			_self.instance.editor.codemirror.on("change", function() {
				$textarea.val( _self.instance.editor.value() );
			});
			_win.wp.pluginMarkupMarkdown.instances.push( _self.instance.editor );
		};
		if ( ! spell_check || ( spell_check.disabled && spell_check.disabled === 1 ) || spell_check === 'none' ) {
			launchEditor();
			// Spellchecker disable. Event need to be triggered manually
			document.dispatchEvent( new Event( 'CodeMirrorSpellCheckerReady' ) );
			if ( isSecondary > 0 ) {
				$textarea.closest( '.acf-input, .form-field' ).addClass( 'ready' );
			}
			else {
				$( '#wp-content-editor-container' ).addClass( 'ready' );
				new MarkupMarkdownOptions();
				$( _doc.body ).addClass( 'markupmarkdown-ready' );
			}
		}
		else {
			launchEditor();
			// Spellchecker is enabled. Tiny panel to display suggestions
			if ( typeof MmdSpellWizard === 'function' ) {
				new MmdSpellWizard( _self.instance.editor.codemirror );
			}
		}
		if ( isSecondary > 0 ) {
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
	 * @param {String} text The HTML source code rendered from the markdown
	 * @param {Object} preview The html node preview
	 * @returns {string} The default text preview
	 */
	MarkupMarkdownWidget.prototype.previewRender = function( text, preview ) {
		var _self = this;
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
			return '<div id="' + getRandomNodeID() + '" class="tmp_media" data-pointer="tmp_gallery-' + galCounter + '">' + mediaPreview.add2Queue( 'gallery', wpGallery, galCounter ) + '</div>';
		});
		text = text.replace( /\[playlist([^\]]*)\]/g, function( wpPlaylist ) {
			galCounter++;
			if ( /type\=\"video\"/.test( wpPlaylist ) ) {
				return '<div id="' + getRandomNodeID() + '" class="tmp_media" data-pointer="tmp_video_playlist-' + galCounter + '">' + mediaPreview.add2Queue( 'videoPlaylist', wpPlaylist, galCounter ) + '</div>';
			}
			else {
				return '<div id="' + getRandomNodeID() + '" class="tmp_media" data-pointer="tmp_audio_playlist-' + galCounter + '">' + mediaPreview.add2Queue( 'audioPlaylist', wpPlaylist, galCounter ) + '</div>';
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
		// Render the LaTex formulas
		var ltxCounter = 0;
		text = text.replace( /\${1,2}[^\$]+\${1,2}/g, function( wpLatex ) {
			ltxCounter++;
			var isBlock = /^\$\$/.test( wpLatex ) ? true : false;
			return '<span id="' + getRandomNodeID() + '" class="tmp_media tmp_latex tmp_span_' + ( isBlock ? 'block' : 'inline' ) + '" data-pointer="tmp_latex-' + ltxCounter + '">' + mediaPreview.add2Queue( 'convertLatexFormulas', wpLatex, ltxCounter ) + '</span>';
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


	function MarkupMarkdownLauncher( primaryAreaEnabled ) {
		var myLauncher = function( sel ) {
			sel = sel || false;
			if ( ! sel ) {
				return false;
			}
			$( sel ).each(function() {
				new MarkupMarkdownWidget( this );
			});
			if ( ! $( sel ).length ) {
				document.dispatchEvent( new Event( 'CodeMirrorSpellCheckerReady' ) );
			}
		};
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
			myLauncher( 'textarea[name="description"]' );
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
			myLauncher( $editorContainer.find( '.wp-editor-area' ) );
			return true;
		}
		// Fallback with the frontend for layer case with the ACF plugin
		// Custom fields will be trigger from the addon, only need to check for the main content
		$editorContainer = $( '.acf-input #acf-_post_content' ).parent();
		if ( $editorContainer.length ) {
			// Initialize EasyMDE on the main content
			myLauncher( '#acf-_post_content' );
			return true;
		}
		// Term description
		myLauncher( 'textarea[name="description"]' );
	}

	// When the DOM is ready...
	$( _doc ).ready(function() {
		$( _doc.body ).addClass( 'easymde' );
		var primaryAreaEnabled = 1,
			spellCheckerEnabled = 0;
		if ( wp && wp.pluginMarkupMarkdown ) {
			if ( typeof wp.pluginMarkupMarkdown.primaryArea !== 'undefined' ) {
				if ( ! wp.pluginMarkupMarkdown.primaryArea || isNaN( parseInt( wp.pluginMarkupMarkdown.primaryArea, 10 ) ) ) {
					// EasyMDE has been disabled on the primary post editor area but might be enabled with custom fields
					primaryAreaEnabled = 0;
				}
			}
			if ( wp.pluginMarkupMarkdown.spellChecker ) {
				var spellChecker = wp.pluginMarkupMarkdown.spellChecker,
					isEmptySpellChecker = true;
				for ( var spellProp in spellChecker ) {
					if ( spellChecker.hasOwnProperty( spellProp ) ) {
						isEmptySpellChecker = false;
					}
				}
				if ( ! isEmptySpellChecker ) {
					spellCheckerEnabled = 1;
				}
			}
		}
		if ( primaryAreaEnabled ) {
			// Trigger the loading icon
			$( '#wp-content-editor-container' ).addClass( 'markupmarkdown' );
			$( '.acf-input #acf-_post_content' ).parent().addClass( 'markupmarkdown' );
		}
		if ( spellCheckerEnabled ) {
			_doc.addEventListener( 'CodeMirrorDictionariesReady', function() {
				new MarkupMarkdownLauncher( primaryAreaEnabled );
			});
			CustomCodeMirrorSpellChecker( wp.pluginMarkupMarkdown.spellChecker );
		}
		else {
			new MarkupMarkdownLauncher( primaryAreaEnabled );
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
