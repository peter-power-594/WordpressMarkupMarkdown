
(function( $ ) {


	var mediaFrame = {};
		mediaPreview = {},
		activeWidget = {},
		fieldNumber = 0;


	function MarkupMarkdownWidget( textarea ) {
		this.todo = [];
		this.updating = false;
		this.home_url = '';
		this.instance = {};
		this.widgetCounter = 0;
		this.init( textarea );
	}


	MarkupMarkdownWidget.prototype.cleanupMMD = function( html ) {
		var _self = this,
			htmlContent = html.replace( /&[amp;]+#(\d+);/g, '&#$1;' )
				.replace( /<li><p>/g, '<li>' )
				.replace( /<\/p><\/li>/g, '</li>' )
				.replace( /<p><figure/g, '<figure' )
				.replace( /<\/figure><\/p>/g, '</figure>' )
				.replace( /<p><\/p>/g, '' )
				.replace( /<p>(\[[a-z+][^\]]*\])<\/p>/g, '$1' ) 
				.replace( /<br><\/p>/g, '</p>' );
		// We have to render dynamically the media shortcodes if need be
		htmlContent = htmlContent.replace( /\[gallery([^\]]*)\]/g, function( wpGallery ) {
			_self.widgetCounter++;
			var myGallery = '<div id="tmp_gallery-' + _self.widgetCounter + '"></div>';
			mediaPreview.gallery( wpGallery, _self.widgetCounter );
			return myGallery;
		});
		htmlContent = htmlContent.replace( /\[playlist([^\]]*)\]/g, function( wpPlaylist ) {
			_self.widgetCounter++;
			if ( /type\=\"video\"/.test( wpPlaylist ) ) {
				var myPlaylist = '<div id="tmp_video_playlist-' + _self.widgetCounter + '"></div>';
				mediaPreview.videoPlaylist( wpPlaylist, _self.widgetCounter );
				return myPlaylist;
			}
			else {
				var myPlaylist = '<div id="tmp_audio_playlist-' + _self.widgetCounter + '"></div>';
				mediaPreview.audioPlaylist( wpPlaylist, _self.widgetCounter );
				return myPlaylist;
			}
		});
		var extraMarkup = htmlContent.replace( /\n|\r/, '' )
			.replace( /<pre>.*?<\/pre>/, '' )
			.match( />[^>]+<\/[\w]+>{.*?}/g );
		if ( ! extraMarkup || ! extraMarkup.length ) {
			return htmlContent;
		}
		for ( var mk = extraMarkup, lim = mk.length, attr = '', i = 0; i < lim; i++ ) {
			var mt = mk[ i ].match( />(.*?)<\/([\w]+)>{(.*?)}/ ),
				attr = mt[ 3 ] || false;
			if ( ! attr ) continue;
			attr = attr.replace( '#', 'id=' ).replace( '.', 'class=' )
				.replace( /\=([^\b\s]+)/, '="$1"' );
			htmlContent = htmlContent.replace( mk[ i ], [
				attr,
				'>',
				mt[ 1 ],
				'</' + mt[ 2 ] + '>'
			].join(''));
		}
		return htmlContent;
	};


	MarkupMarkdownWidget.prototype.updateHTML = function( ) {
		var _self = this,
			_myTodo = _self.todo || [];
		while ( _myTodo.length ) {
			var todo = _myTodo.shift();
			if ( todo.node && todo.node.previousSibling ) {
				// Make the cursor to the node before removing or inserting nodes to avoid side effets
				if ( todo.action === 'insertNode' ) {
					_self.instance.editor.summernote( 'editor.setLastRange', $.summernote.range.createFromNodeAfter(todo.node.previousSibling).select() );
					var parentNode = todo.node.parentNode;
					parentNode.insertBefore( todo.html, todo.node );
					var myP = document.createElement( 'p' ),
						myBR = document.createElement( 'br' );
					myP.appendChild( myBR ); 
					parentNode.insertBefore( myP, todo.node );
					parentNode.removeChild( todo.node );
					_self.instance.editor.summernote( 'editor.setLastRange', $.summernote.range.createFromNode( myBR ).select() );
					/*
					var parentNode = todo.node.parentNode;
					parentNode.insertBefore( todo.html, todo.node );
					parentNode.removeChild( todo.node );
					var freshParagraph = document.createElement( 'p' );
					freshParagraph.appendChild( document.createTextNode( ' ' ) );
					parentNode.appendChild( freshParagraph );
					// _self.instance.editor.summernote( 'editor.setLastRange', $.summernote.range.createFromNodeAfter( freshParagraph ).select() );
					*/
				}
				else if ( todo.action === 'pasteHTML' ) {
					_self.instance.editor.summernote( 'editor.setLastRange', $.summernote.range.createFromNodeAfter(todo.node.previousSibling).select() );
					todo.node.parentNode.removeChild( todo.node );
					_self.instance.editor.summernote( todo.action, todo.html );
				}
			}
			else {
				var tmp = document.createElement( 'p' );
				tmp.appendChild( document.createTextNode( ' ' ) );
				todo.node.parentNode.insertBefore( tmp, todo.node );
				_self.instance.editor.summernote('editor.setLastRange', $.summernote.range.createFromNodeAfter(tmp).select());
				todo.node.parentNode.removeChild( todo.node );
				_self.instance.editor.summernote( todo.action, todo.html );
				tmp.parentNode.removeChild( tmp );
			}
		}
		/*
			var htmlContent = $instance.summernote( 'code' );
			// Showdown / Turndown adjustements
			htmlContent = htmlContent.replace( /<pre>|<\/pre>/g, '' );
			return htmlContent;
		*/
	};


	/**
	 * Check and launch the Wordpress media modal if the "++" sign is entered
	 *
	 * @param String val the current input text
	 * @param HTML_Node node the current html node used inside the WYSIWYG
	 * @returns Integer 1 if an update was made or 0 if nothing was done
	 */
	MarkupMarkdownWidget.prototype.checkMedias = function( val, node ) {
		var _self = this;
		if ( /^[+]{2}\s/.test( val ) ) {
			activeWidget = _self;
			activeWidget.node = node;
			mediaFrame.open();
			return 1;
		}
		return 0;
	};


	/**
	 * Check if the user is inserting a new headline
	 * We check for the # character at the beginning of the string
	 *
	 * @param String val the current input text
	 * @param HTML_Node node the current html node used inside the WYSIWYG
	 * @returns Integer 1 if an update was made or 0 if nothing was done
	 */
	MarkupMarkdownWidget.prototype.checkHeadlines = function( val, node ) {
		var _self = this;
		// Check that '#' are at the beginning and there are at least 2 other chars
		if ( ! /^[#]+[^#]{1}|[#]+[^#]{1}$/.test( val ) ) return -1;
		// At this point we are sure that the string contains the '#' character
		var titLvl = val.match( /(^[#]+|[#]+[^#]{1}$)/ )[ 1 ].replace( /[^#]+/, '' ).length;
		// Make sure the level of headline is between 1 and 6 included
		if ( titLvl < 1 || titLvl > 6 ) return -2;
		// Use a space as trigger if the user types the # after typing the title
		if ( val.length > titLvl + 1 && ! /^[#]+\s|[#]+\s$/.test( val ) ) return -3;
		// All good
		_self.todo.push({
			action: 'pasteHTML',
			node: node,
			html: [
			'<h' + titLvl + '>',
			( node.firstChild && node.firstChild.nodeValue ? node.firstChild.nodeValue : ' ' ).replace( /^[#]+\s*|[#]+\s*/, '' ),
			'</h' + titLvl + '>'
			].join( '' )
		});
		return 1;
	};


	/**
	 * Check if the user is inserting a snippet code
	 * We check for the ` and the possible key variants (! or 1)
	 *
	 * @param String val the current input text
	 * @param HTML_Node node the current html node used inside the WYSIWYG
	 * @returns Integer 1 if an update was made or 0 if nothing was done
	 */
	MarkupMarkdownWidget.prototype.checkCodeSnippets = function( val, node ) {
		var _self = this;
		// Check that '`' or '!' are at the beginning and there are at least 2 other chars
		if ( ! /^[`\!1]{3}[^`\!1]{1}/.test( val ) ) return -1;
		// Make sure the user specifies the language to be used for the snippet
		if ( ! /^[`\!1]{3}[a-z]+\s/.test( val ) ) return -3;
		var lang = val.match( /^[`\!1]{3}([a-z]+)\s/ );
		_self.todo.push({
			action: 'pasteHTML',
			node: node,
			html: [
				'<pre><code class="' + lang[ 1 ] + ' language-' + lang[ 1 ] + '">',
				( node.firstChild.nodeValue || ' ' ).replace( /^[`\!1]+([a-z]+)\s*/, '' ),
				'</code></pre>'
			].join( '' )
		});
		return 1;
	};


	/**
	 * Check if the user is inserting a list item
	 * We check for the -, the * and the + characters
	 *
	 * @param String val the current input text
	 * @param HTML_Node node the current html node used inside the WYSIWYG
	 * @returns Integer 1 if an update was made or 0 if nothing was done
	 */
	MarkupMarkdownWidget.prototype.checkListItems = function( val, node ) {
		var _self = this;
		// Check that - or * or + is specified by the user
		if ( ! /^([-*+]{1}|\d{1}\.)\s|(\.[-*+]{1}\s|\.\d{1})$/.test( val ) ) return -1;
		var text = ( node.firstChild.nodeValue || ' ' ).replace( /^([-*+]{1}|\d{1}\.)\s|(\.[-*+]{1}\s|\.\d{1})$/, '' ),
			html = '<li>' + ( text.length > 1 ? text : '<br>' ) + '</li>';
		if ( ! /OL|UL/.test( ( node.parentNode.tagName || '' ).toUpperCase() ) ) {
			if ( /^\d|\.\d$/.test( val ) ) {
				html = [ '<ol>', html, '</ol>' ];
			}
			else {
				html = [ '<ul>', html, '</ul>' ];
			}
		}
		_self.todo.push({
			action: 'pasteHTML',
			node: node,
			html: html.join( '' )
		});
		return 1;
	};


	/**
	 * Check if the user is inserting a quote
	 * We check for the > characters
	 *
	 * @param {string} val the current input text
	 * @param {HTML_Node} node the current html node used inside the WYSIWYG
	 * @returns Integer 1 if an update was made or 0 if nothing was done
	 */
	MarkupMarkdownWidget.prototype.checkBlockquote = function( val, node ) {
		var _self = this;
		// Check that - or * or + is specified by the user
		if ( ! /^\>\s|\>\s$/.test( val ) ) return -1;
		var text = ( node.firstChild.nodeValue || ' ' ).replace( /^\>\s|\>\s$/, '' ).split( /[—-]+/ ),
			quote = text[ 0 ].length > 1 ? text[ 0 ] : 'Lorem Ipsum Dolor Imet',
			reference = text[ 1 ] && text[ 1 ].length > 2 ? text[ 1 ].replace( /^.*?\,/, '' ) : 'Anonymous Book',
			author = text[ 1 ] && text[ 1 ].length > 2 ? text[ 1 ].split( ',' )[ 0 ] : 'Jane Doe',
			// https://developer.mozilla.org/en-US/docs/Web/HTML/Element/blockquote
			html = [
				'<figure>',
					'<blockquote>',
						'<p>',
							quote,
						'</p>',
					'</blockquote>',
					'<figcaption>— ',
						author.replace( /[\s*|\b]$/, '' ) + ', ',
						'<cite>',
							reference,
						'</cite>',
					'</figcaption>',
				'</figure>'
			],
			_tmp = document.createElement( 'div' );
		_tmp.innerHTML = html.join( '' );
		_self.todo.push({
			action: 'insertNode',
			node: node,
			html: _tmp.firstChild
		});
		return 1;
	};


	/**
	 * Inline format Rules
	 *
	 * @type Boolean
	 * @param HTML_Node node the current html node used inside the WYSIWYG
	 * @return TRUE in case of modifications or FALSE if nothing was modified
	 */
	MarkupMarkdownWidget.prototype.formatContent = function( node ) {
		if ( ! node || ! node.nodeName ) {
			return false;
		}
		var _self = this,
			myNodeName = node.nodeName.toUpperCase();
		if ( /PRE|CODE/.test( myNodeName ) ) {
			if ( node.getElementsByTagName( 'br' ).length ) {
				var breakLine = node.getElementsByTagName( 'br' )[ 0 ];
				breakLine.parentNode.insertBefore( document.createTextNode( "\n" ), breakLine );
				breakLine.parentNode.removeChild( breakLine );
				return true;
			}
		}
		var myNodeContent = node.innerHTML || '',
			updated = 0;
		if ( /\s\*\*[^*]+\*\*/.test( myNodeContent ) ) { //**Bold text**
			node.innerHTML = myNodeContent.replace( /\s\*\*([^*]+)\*\*/, ' <strong>$1</strong>&nbsp;' );
			updated = 1;
		}
		else if ( /\s\_\_[^_]+\_\_/.test( myNodeContent ) ) { // __Bold text__
			node.innerHTML = myNodeContent.replace( /\s\_\_([^_]+)\_\_/, ' <strong>$1</strong>&nbsp;' );
			updated = 1;
		}
		else if ( /\s\*[^*]+\*/.test( myNodeContent ) ) { // *Emphasize text*
			node.innerHTML = myNodeContent.replace( /\s\*([^*]+)\*/, ' <em>$1</em>&nbsp;' );
			updated = 1;
		}
		else if ( /\s\_[^_]+\_/.test( myNodeContent ) ) { // _Emphasize text_
			node.innerHTML = myNodeContent.replace( /\s\_([^_]+)\_/, ' <em>$1</em>&nbsp;' );
			updated = 1;
		}
		else if ( /[^!]*\[.*?\]\((http|https):\/\/.*?\)/.test( myNodeContent ) ) { // [Link](https://www.example.com)
			var tmp = myNodeContent.match( /\[(.*?)\]\(((http|https):\/\/.*?)\)/ ),
				ext = new RegExp( _self.home_url ).test( tmp[ 2 ] ) ? 0 : 1;
			node.innerHTML = myNodeContent.replace( /\[(.*?)\]\(((http|https):\/\/.*?)\)/, '<a href="$2"' + ( ext > 0 ? ' target="_blank" rel="noopener"' : '' ) + '>$1</a>' );
			updated = 1;
		}
		if ( updated > 0 ) {
			// Restore the cursor at the end of the node - https://summernote.org/deep-dive/#setlastrange
			_self.instance.editor.summernote('editor.setLastRange', $.summernote.range.createFromNodeAfter(node).select());
		}
		return updated;
	};


	/**
	 *
	 * @returns TurndownService
	 */
	MarkupMarkdownWidget.prototype.html2mmdToolkit = function() {
		var myService = new TurndownService();
		// Special transform rules for the code tag
		myService.addRule("code-transorm", {
			filter: function( node ) {
				return node.nodeName.toUpperCase() === "PRE";
			},
			replacement: function( content, node, options ) {
				var code = node.firstChild,
					mmd = "```" + ( code.className || '' ).replace( /.*language-/, '' )
					+ "\n" + ( code.textContent || '' ).replace( '<br>', '' ) + "\n```\n";
				return mmd;
			}
		});
		myService.addRule('blank-link', {
			filter: function( node, options ) {
				return (
					node.nodeName === 'A' &&
					node.getAttribute( 'href' ) &&
					( node.getAttribute( 'target' ) || '' ) === '_blank'
				);
			},
			replacement: function( content, node ) {
				var href = node.getAttribute( 'href' );
				var title = ( node.getAttribute( 'title' ) || '' ).replace(/(\n+\s*)+/g, '\n');
				if ( title ) title = ' "' + title + '"';
				return '[' + content + '](' + href + title + ')';
				// Markdown extra
				// return '[' + content + '](' + href + title + '){target=_blank rel=noopener}
				// Kramdown
				// return '[' + content + '](' + href + title + '){:xn: target="_blank" rel="nofollow noreferrer noopener"}'
			}
		});
		myService.addRule('wp-gallery', {
			filter: function( node ) {
				return node.nodeName.toUpperCase() === 'DIV' && /tmp_gallery/.test( node.id || '' );
			},
			replacement: function( content, node, options ) {
				return window.decodeURIComponent( node.firstChild.getAttribute( 'data-shortcode' ) || '' );
			}
		});
		myService.addRule('wp-playlist', {
			filter: function( node ) {
				return node.nodeName.toUpperCase() === 'DIV' && /tmp_(audio|video)_playlist/.test( node.id || '' );
			},
			replacement: function( content, node, options ) {
				return window.decodeURIComponent( node.firstChild.getAttribute( 'data-shortcode' ) || '' );
			}
		});
		myService.addRule('wp-medias', {
			filter: function( node ) {
				return node.nodeName.toUpperCase() === 'DIV' && /tmp_(images|audios|videos)/.test( node.className || '' );
			},
			replacement: function( content, node, options ) {
				return window.decodeURIComponent( node.getAttribute( 'data-shortcode' ) || '' );
			}
		});
		myService.addRule('figure-transform', {
			filter: function( node ) {
				if ( node.nodeName.toUpperCase() === 'FIGURE' ) {
					if ( node.getAttribute( 'data-shortcode' ) ) {
						return true;
					}
					else if ( node.getElementsByTagName( 'blockquote' ).length || node.getElementsByTagName( 'img' ).length ) {
						return true;
					}
					return false;
				}
				else {
					return false;
				}
			},
			replacement: function( content, node, options ) {
				var firstChild = node.firstChild || false,
					mmd = '';
				if ( node.getAttribute( 'data-shortcode' ) ) {
					return window.decodeURIComponent( node.getAttribute( 'data-shortcode' ) );
				}
				else if ( firstChild && /^BLOCKQUOTE/.test( ( firstChild.nodeName || '' ).toUpperCase() ) ) {
					var html = firstChild.innerHTML;
					mmd += html.replace( /\n/, '' )
						.replace( /<br>/, '  \n' )
						.replace( '</p><p>', "\n" )
						.replace( /<p>(.*?)<\/p>/, "> $1" );
					if ( firstChild.nextSibling && /^FIGCAPTION/.test( ( firstChild.nextSibling.nodeName || '' ).toUpperCase() ) ) {
						html = firstChild.nextSibling.innerHTML.replace( /<[\/]*cite>/g, '' )
						mmd += "\n> " + html;
					}
				}
				return mmd;
			}
		});
		return myService;
	};


	/**
	 * Method to convert Markdown Code to HTML code.
	 * Uses the Showdown library - https://github.com/showdownjs/showdown
	 *
	 * @returns {showdown.Converter}
	 */
	MarkupMarkdownWidget.prototype.mmd2htmlToolkit = function() {
		var mmd_blockquote = {
			type: 'lang',
			filter: function( myText, myConverter, myOptions ) {
				return myText.replace( /\>\s.*?\n\>\s.*?\n/g, function( myQuote ) {
					var text = myQuote.replace( /\>\s*|\n/g, '' ).split( /[—-]+\s*/ );
						quote = text[ 0 ],
						reference = text[ 1 ] && text[ 1 ].length > 2 ? text[ 1 ].replace( /^.*?\,/, '' ) : '',
						author = text[ 1 ] && text[ 1 ].length > 2 ? text[ 1 ].split( ',' )[ 0 ] : '';
					return [
						'<figure>',
							'<blockquote><p>' + quote + '</p></blockquote>',
							( author.length || reference.length ) ? '<figcaption>' + myQuote.match( /([—-]+\s*)/ )[ 1 ] + ' ' : '',
								author.length ? author.replace( /[\s*|\b]$/, '' ) + ', ' : '',
								reference.length ? '<cite>' + reference + '</cite>' : '',
							( author.length || reference.length ) ? '</figcaption>' : '',
						'</figure>'
					].join( '' );
				});
			}
		};
		showdown.extension( 'mmd_blockquote', mmd_blockquote );
		var myService = new showdown.Converter({
			extensions: [ 'mmd_blockquote' ]
		});
		return myService;
	};


	/**
	 * Replace the original textarea / WYSIWYG with our own
	 *
	 * @returns Void
	 */
	MarkupMarkdownWidget.prototype.switchEditor = function( textarea ) {
		var _self = this;
		// Clone the original Wordpress Textarea and hide it
		$originalEditor = $( textarea ),
		$newEditor = $originalEditor.clone( false );
		$originalEditor.hide();
		$newEditor.removeAttr( 'name' ).removeAttr( 'class' );
		$originalEditor.after( $newEditor );
		$originalEditor.parent().addClass( 'wp-editor-container' );
		// Media Frame
		var wpMediaFrame = function( context ) {
			var ui = $.summernote.ui,
				button = ui.button({
					contents: '<i class="note-icon-picture"></i>',
					tooltip: 'Image',
					click: function () {
						activeWidget = _self;
						activeWidget.context = context;
						mediaFrame.open();
					}
				});
			return button.render();
		};
		// Run Summernote !
		_self.instance.editor = $newEditor.summernote({
			airMode: true,
			popover: {
				air: [
					[ 'style', [ 'bold', 'italic', 'underline', 'clear' ] ],
					[ 'media', [ 'link', 'wpMedia' ] ],
					[ 'para', [ 'ul', 'ol', 'paragraph' ] ]					
				]
			},
			buttons: {
				wpMedia: wpMediaFrame
			},
			disableDragAndDrop: true,
			dialogsInBody: false,
			tabDisable: true
		});
		_self.instance.textarea = $originalEditor;
		if ( ! wp.pluginMarkupMarkdown ) {
			wp.pluginMarkupMarkdown = {};
		}
		if ( ! wp.pluginMarkupMarkdown.instances ) {
			wp.pluginMarkupMarkdown.instances = [];
		}
		wp.pluginMarkupMarkdown.instances.push( _self.instance.editor );
	};


	/**
	 * Restore the post content if need available
	 *
	 * @returns Boolean TRUE if a content was set or false if default content is used
	 */
	MarkupMarkdownWidget.prototype.restoreContent = function() {
		var _self = this;
		// Initialize with proper HTML
		var text = $originalEditor.val(),
			initialHTML = text || '';
		if ( ! initialHTML.length ) {
			initialHTML = '<p>Let\'s begin!</p><p><br /></p>';
			_self.instance.editor.summernote( 'code', initialHTML );
			return false;
		}
		var mediaCounters = initialHTML.match( /\"myset.*?\s/g );
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
		text = text + "\n\n";
		// Render single image
		text = text.replace( /\n([\[]*\!\[.*)/g, function( singleImage ) {
			singleImage = singleImage.replace( "\n", '' );
			return [
				"\n" + '<div class="tmp_images" data-shortcode="' + encodeURIComponent( singleImage ) + '">',
					mediaPreview.convertImage( _self.mmd2html.makeHtml( singleImage ) ),
				'</div>'
			].join( '' );
		});
		text = text.replace( /\n\n- [\[]*\!\[[\s\S]*?(\n\n)/g, function( imagesList ) {
			imagesList = imagesList.replace( /\n\n/g, '' );
			return [
				"\n\n" + '<div class="tmp_images" data-shortcode="' + encodeURIComponent( imagesList ) + '">',
					_self.cleanupMMD( mediaPreview.convertImage( _self.mmd2html.makeHtml( imagesList ) ) ),
				'</div>' + "\n\n"
			].join( '' );
		});
		// Render the audio shortcodes
		var audCounter = 0;
		text = text.replace( /\n(\[audio[^\]]*\]\[\/audio\])/g, function( wpAudio ) {
			wpAudio = wpAudio.replace( "\n", '' );
			return [
				"\n" + '<div class="tmp_audios" data-shortcode="' + encodeURIComponent( wpAudio ) + '">',
					mediaPreview.convertAudio( wpAudio, audCounter++ ),
				'</div>'
			].join( '' );
		});
		// Render the video shortcodes
		var vidCounter = 0;
		text = text.replace( /\n\[video([^\]]*)\]\[\/video\]/g, function( wpVideo ) {
			wpVideo = wpVideo.replace( "\n", '' );
			return [
				"\n" + '<div class="tmp_videos" data-shortcode="' + encodeURIComponent( wpVideo ) + '">',
					mediaPreview.convertVideo( wpVideo, vidCounter++ ),
				'</div>'
			].join( '' );
		});
		initialHTML = _self.cleanupMMD( mediaPreview.convertImage( _self.mmd2html.makeHtml( text ) ) );
		if ( ! /<\/p>/.test( ( initialHTML.match( /[\S]+$/ ) || [ '' ] )[ 0 ] ) ) {
			// Make the last line editable if the last element is a block
			initialHTML += '<p><br /></p>';
		}
		_self.instance.editor.summernote( 'code', initialHTML );
		return true;
	};


	/**
	 *  @param String markdownCode The new markdown media content
	 *  
	 *  @return Object The Code Editor document updated with the media markdown
	 */
	MarkupMarkdownWidget.prototype.mediaMultiselCallBack = function( markdownCode ) {
		var _self = this,
			tmp = document.createElement( 'div' );
		if ( /\!\[/.test( markdownCode ) ) { // Images
			if ( /myset\%d/.test( markdownCode ) ) {
				activeWidget.widgetCounter++;
				markdownCode = markdownCode.replace( /myset\%d/g, 'myset' + activeWidget.fieldNumber + '_' + activeWidget.widgetCounter );
			}
			tmp.className = 'tmp_images';
			tmp.innerHTML = _self.cleanupMMD( mediaPreview.convertImage( _self.mmd2html.makeHtml( markdownCode ) ) );
			tmp.setAttribute( 'data-shortcode', encodeURIComponent( markdownCode ) );
			activeWidget.todo.push({
				action: 'insertNode',
				node: activeWidget.node,
				html: tmp
			});
			activeWidget.updateHTML();
			activeWidget.instance.editor.trigger( 'summernote.mmd_change' );
		}
		else if ( /\[audio/.test( markdownCode ) ) { // Audio
			var html = markdownCode.replace( /\[audio([^\]]*)\]\[\/audio\]/g, function( wpAudio ) {
				activeWidget.widgetCounter++;
				return mediaPreview.convertAudio( wpAudio, activeWidget.widgetCounter );
			});
			tmp.className = 'tmp_audios';
			tmp.innerHTML = html;
			tmp.setAttribute( 'data-shortcode', encodeURIComponent( markdownCode ) );
			activeWidget.todo.push({
				action: 'insertNode',
				node: activeWidget.node,
				html: tmp
			});
			activeWidget.updateHTML();
			activeWidget.instance.editor.trigger( 'summernote.mmd_change' );
		}
		else if ( /\[video/.test( markdownCode ) ) { // Video
			var html = markdownCode.replace( /\[video([^\]]*)\]\[\/video\]/g, function( wpVideo ) {
				activeWidget.widgetCounter++;
				return mediaPreview.convertVideo( wpVideo, activeWidget.widgetCounter );
			});
			tmp.className = 'tmp_videos';
			tmp.innerHTML = html;
			tmp.setAttribute( 'data-shortcode', encodeURIComponent( markdownCode ) );
			activeWidget.todo.push({
				action: 'insertNode',
				node: activeWidget.node,
				html: tmp
			});
			activeWidget.updateHTML();
			activeWidget.instance.editor.trigger( 'summernote.mmd_change' );
		}
	};


	/**
	 *  @param String widgetShortCode The Gallery / Playlist shortcode
	 *  
	 *  @return Object The Code Editor document updated with the widget shortcodes
	 */
	MarkupMarkdownWidget.prototype.mediaWidgetCallBack = function( widgetShortCode ) {
		activeWidget.widgetCounter++;
		if ( /gallery/.test( widgetShortCode ) ) {
			var myGallery = document.createElement( 'div' );
			myGallery.id = 'tmp_gallery-' + activeWidget.widgetCounter;
			if ( activeWidget.node ) {
				activeWidget.todo.push({
					action: 'insertNode',
					node: activeWidget.node,
					html: myGallery
				});
				activeWidget.updateHTML();
				activeWidget.instance.editor.trigger( 'summernote.mmd_change' );
			}
			else if ( activeWidget.context ) {
				activeWidget.context.invoke( 'editor.pasteHTML', myGallery );
			}
			mediaPreview.gallery( widgetShortCode, activeWidget.widgetCounter );
		}
		else if ( /playlist/.test( widgetShortCode ) ) {
			var myPlayList = document.createElement( 'div' );
			if ( /video/.test( widgetShortCode ) ) {
				myPlayList.id = 'tmp_video_playlist-' + activeWidget.widgetCounter;
				if ( activeWidget.node ) {
					activeWidget.todo.push({
						action: 'insertNode',
						node: activeWidget.node,
						html: myPlayList
					});
					activeWidget.updateHTML();
					activeWidget.instance.editor.trigger( 'summernote.mmd_change' );
				}
				else if ( activeWidget.context ) {
					activeWidget.context.invoke( 'editor.pasteHTML', myPlayList );
				}
				mediaPreview.videoPlaylist( widgetShortCode, activeWidget.widgetCounter );
			}
			else {
				myPlayList.id = 'tmp_audio_playlist-' + activeWidget.widgetCounter;
				if ( activeWidget.node ) {
					activeWidget.todo.push({
						action: 'insertNode',
						node: activeWidget.node,
						html: myPlayList
					});
					activeWidget.updateHTML();
					activeWidget.instance.editor.trigger( 'summernote.mmd_change' );
				}
				else if ( activeWidget.context ) {
					activeWidget.context.invoke( 'editor.pasteHTML', myPlayList );
				}
				mediaPreview.audioPlaylist( widgetShortCode, activeWidget.widgetCounter );
			}
		}
	};


	/**
	 * Initialize Wordpress Media Frame to upload or add a unique media item
	 *
	 * @returns Void
	 */
	MarkupMarkdownWidget.prototype.mediaUploader = function() {
		if ( mediaFrame && mediaFrame.title ) {
			return false;
		}
		var _self = this;
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
		mediaPreview = new MmdPreview({
			base_url: _self.home_url,
			callbacks:{
				widget: function() {
					if ( activeWidget && activeWidget.instance && activeWidget.instance.editor ) {
						activeWidget.instance.editor.trigger( 'summernote.mmd_change' );
					}
				},
				multisel: function() {
					if ( activeWidget && activeWidget.instance && activeWidget.instance.editor ) {
						activeWidget.instance.editor.trigger( 'summernote.mmd_change' );
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
		_self.fieldNumber = fieldNumber++; 
		// Let the user upload contents
		_self.mediaUploader();
		_self.switchEditor( textarea );
		_self.restoreContent();
		// Service to convert HTML to Markdown
		var html2mmd = _self.html2mmdToolkit();
		// Summernote Events Reference here : https://summernote.org/deep-dive/#api
		_self.instance.editor.on( 'summernote.mmd_change', function() {
			if ( _self.updating ) {
				return 1;
			}
			_self.updating = 1;
			_self.updateHTML();
			// $( '#content' ).val( html2mmd.turndown( content ) );
			var $textArea = _self.instance.textarea;
			$textArea.val( html2mmd.turndown( $textArea.parent().find( '.note-editable' ).get( 0 ) ) );
			_self.updating = 0;
		}); // .trigger( 'summernote.mmd_change' );
		// Markdown syntaxes
		_self.instance.editor.on( 'summernote.keyup', function() {
			if ( _self.todo.length ) {
				return true;
			}
			// The "current node" via Summernote is probably a text node content
			// So we grab the parent as a reference
			var currRng = _self.instance.editor.summernote( 'editor.getLastRange' ),
				currNode = currRng.ec || currRng.sc || {},
				rngVal = ( currNode.nodeValue || '' ).toString(), // currRng.getWordRange( true );
				parentNode = currNode.parentNode || false;
			if ( /note-editable/.test( parentNode.className || '' ) ) {
				// Just in case, don't grab the WYSIWYG container
				parentNode = currNode;
			}
			if ( ! rngVal || ! rngVal.length ) return true;
			_self.currNode = parentNode;
			var hasUpdate = 0;
			// Medias
			if ( _self.checkMedias( rngVal, parentNode ) > 0 ) hasUpdate = 1;
			// Titles Rules
			else if ( _self.checkHeadlines( rngVal, parentNode ) > 0 ) hasUpdate = 1;
			// Code Rules
			else if ( _self.checkCodeSnippets( rngVal, parentNode ) > 0 ) hasUpdate = 1;
			// List items
			else if ( _self.checkListItems( rngVal, parentNode ) > 0 ) hasUpdate = 1;
			// Blockquote items
			else if ( _self.checkBlockquote( rngVal, parentNode ) > 0 ) hasUpdate = 1;
			// Force the refresh
			else if ( _self.formatContent( parentNode ) > 0 ) hasUpdate = 1;
			_self.instance.editor.trigger( 'summernote.mmd_change' );
		});
	};


	MarkupMarkdownWidget.prototype.init = function( textarea ) {
		var _self = this,
			home_url = '';
		_self.mmd2html = _self.mmd2htmlToolkit();
		// Base Dir
		if ( wp && wp.pluginMarkupMarkdown && wp.pluginMarkupMarkdown.homeURL ) {
			home_url = wp.pluginMarkupMarkdown.homeURL;
		}
		else {
			var args = decodeURIComponent( jQuery( 'script[src*="wordpress_richedit-summernote"]' ).attr( 'src' ) ); 
			home_url = /home_url/.test( args ) ? args.replace( /.*?home_url=/, '' ).replace( /&.*?$/, '' ) : '/';
		}
		_self.home_url = home_url.replace( /^(.*?)(\.[a-z]+\/).*?$/, '$1$2' );
		_self.core( textarea );
	};


	$( document ).ready(function() {
		$( 'body' ).addClass( 'summernote' );
		$( '#wp-content-editor-container .wp-editor-area' ).each(function() {
			new MarkupMarkdownWidget( this );
		});
	});

	window.MarkupMarkdown = MarkupMarkdownWidget;



})( window.jQuery );
