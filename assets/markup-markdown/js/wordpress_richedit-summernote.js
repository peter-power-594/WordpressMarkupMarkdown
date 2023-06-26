
(function( $ ) {


	var mediaFrame = {};
		activeWidget = {};


	function MarkupMarkdownWidget( textarea ) {
		this.todo = [];
		this.updating = false;
		this.home_url = '';
		this.instance = {};
		this.galleryCounter = 0;
		this.init( textarea );
	}


	MarkupMarkdownWidget.prototype.cleanupMMD = function( html ) {
		var _self = this,
			htmlContent = html.replace( /&[amp;]+#(\d+);/g, '&#$1;' )
				.replace( /<li><p>/g, '<li>' )
				.replace( /<\/p><\/li>/g, '</li>' )
				.replace( /<br><\/p>/g, '</p>' );
		htmlContent = htmlContent.replace( /\[gallery([^\]]*)\]/g, function( wpGallery ) {
			_self.galleryCounter++;
			var myGallery = '<div id="tmp_gallery-' + _self.galleryCounter + '"></div>';
			_self.renderGallery( wpGallery, _self.galleryCounter );
			return myGallery;
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
			console.log( mt );
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
				_self.instance.editor.summernote('editor.setLastRange', $.summernote.range.createFromNodeAfter(todo.node.previousSibling).select());
				todo.node.parentNode.removeChild( todo.node );
				_self.instance.editor.summernote( todo.action, todo.html );
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
		if ( ! /^[#]+[^#]{1}/.test( val ) ) return -1;
		// At this point we are sure that the string contains the '#' character
		var titLvl = val.match( /^([#]+)/ )[ 1 ].length;
		// Make sure the level of headline is between 1 and 6 included
		if ( titLvl < 1 || titLvl > 6 ) return -2;
		// Use a space as trigger if the user types the # after typing the title
		if ( val.length > titLvl + 1 && ! /^[#]+\s/.test( val ) ) return -3;
		// All good
		_self.todo.push({
			action: 'pasteHTML',
			node: node,
			html: [
			'<h' + titLvl + '>',
			( node.firstChild && node.firstChild.nodeValue ? node.firstChild.nodeValue : ' ' ).replace( /^[#]+\s*/, '' ),
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
		if ( ! /^([-*+]{1}|\d{1}\.)\s/.test( val ) ) return -1;
		var html = '<li>' + ( node.firstChild.nodeValue || ' ' ).replace( /^([-*+]{1}|\d{1}\.)\s/, '' ) + '</li>';
		if ( ! /OL|UL/.test( ( node.parentNode.tagName || '' ).toUpperCase() ) ) {
			if ( /^\d/.test( val ) ) {
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
		if ( ! /^\>\s/.test( val ) ) return -1;
		var text = ( node.firstChild.nodeValue || ' ' ).replace( /^\>\s/, '' ).split( /—|-|--/ ),
			quote = text[ 0 ].length > 1 ? text[ 0 ] : 'Lorem Ipsum Dolor Imet',
			reference = text.length > 2 ? text[ text.length - 1 ] : 'Anonymous Book',
			author = text.length > 2 ? text[ text.length - 2 ] : ( text.length > 1 ? text.length - 1 : 'Jane Doe' ),
			// https://developer.mozilla.org/en-US/docs/Web/HTML/Element/blockquote
			html = [
				'<figure>',
					'<blockquote>',
						'<p>',
							quote,
						'</p>',
					'</blockquote>',
					'<figcaption>—',
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
		if ( /\s\*\*.*?\*\*/.test( myNodeContent ) ) { //**Bold text**
			node.innerHTML = myNodeContent.replace( /\s\*\*(.*?)\*\*/, ' <strong>$1</strong>' );
			updated = 1;
		}
		else if ( /\s\_\_.*?\_\_/.test( myNodeContent ) ) { // __Bold text__
			node.innerHTML = myNodeContent.replace( /\s\_\_(.*?)\_\_/, ' <strong>$1</strong>' );
			updated = 1;
		}
		else if ( /\s\*.*?\*/.test( myNodeContent ) ) { // *Emphasize text*
			node.innerHTML = myNodeContent.replace( /\s\*(.*?)\*/, ' <em>$1</em>' );
			updated = 1;
		}
		else if ( /\s\_.*?\_/.test( myNodeContent ) ) { // _Emphasize text_
			node.innerHTML = myNodeContent.replace( /\s\_(.*?)\_/, ' <em>$1</em>' );
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
		/*
		myService.addRule("figure-transorm", {
			filter: function( node ) {
				return node.nodeName.toUpperCase() === "FIGURE";
			},
			replacement: function( content, node, options ) {
				var firstChild = node.firstChild || {},
				html = node.innerHTML.replace( /<[\/]*figure>/, '' ),
				mmd = '<figure>';
				if ( firstChild && /^BLOCKQUOTE/.test( ( firstChild.nodeName || '' ).toUpperCase() ) ) {
					mmd += html.replace( /\n/, "" )
						.replace( /<br>/, '' )
						.replace( '</p><p>', "\n" )
						.replace( "<blockquote><p>(.*?)</p></blockquote>", '> $1' );
				}
				mmd += '</figure>';
				return mmd;
			}
		});
		*/
		return myService;
	};


	/**
	 * Method to convert Markdown Code to HTML code.
	 * Uses the Showdown library - https://github.com/showdownjs/showdown
	 *
	 * @returns {showdown.Converter}
	 */
	MarkupMarkdownWidget.prototype.mmd2htmlToolkit = function() {
		var myService = new showdown.Converter();
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
					[ 'media', [ 'wpMedia' ] ],
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
		// Base Dir
		if ( wp && wp.pluginMarkupMarkdown && wp.pluginMarkupMarkdown.homeURL ) {
			_self.home_url = wp.pluginMarkupMarkdown.homeURL;
		}
		else {
			var args = decodeURIComponent( jQuery( 'script[src*="wordpress_richedit"]' ).attr( 'src' ) ); 
			_self.home_url = /home_url/.test( args ) ? args.replace( /.*?home_url=/, '' ).replace( /&.*?$/, '' ) : '/';
		}
		if ( ! wp.pluginMarkupMarkdown.instances ) {
			wp.pluginMarkupMarkdown.instances = [];
		}
		wp.pluginMarkupMarkdown.instances.push( _self.instance.editor );
	};


	/**
	 * Restore the post content if need available
	 *
	 * @returns Void
	 */
	MarkupMarkdownWidget.prototype.restoreContent = function() {
		var _self = this;
		// Initialize with proper HTML
		var initialMMD = $originalEditor.val(),
			initialHTML = initialMMD || '';
		if ( ! initialHTML.length ) {
			initialHTML = '<p>Let\'s begin!</p>';
		}
		else {
			// Service to convert Markdown to HTML
			var mmd2html = _self.mmd2htmlToolkit();
			initialHTML = _self.cleanupMMD( mmd2html.makeHtml( initialMMD ) );
		}
		_self.instance.editor.summernote( 'code', initialHTML );
	};


	/**
	 * Insert an image inside the WYSIWYG editor
	 *
	 * @returns Boolean TRUE in case of success or FALSE in case or error
	 */
	MarkupMarkdownWidget.prototype.insertMedia = function( ops ) {
		var _self = this;
		if ( ! _self.currNode ) {
			return false;
		}
		var myNodeContent = _self.currNode.innerHTML || '';
		_self.currNode.innerHTML = myNodeContent.replace(
			/[+]{2}/,
			'<img src="' + ops.url + '" alt="' + ops.alt + '" />'
		);
		_self.instance.editor.summernote('editor.setLastRange', $.summernote.range.createFromNodeAfter(_self.currNode).select());
		_self.instance.editor.trigger( 'summernote.mmd_change' );
	};


	/**
	 * Insert a group of images inside the WYSIWYG editor
	 *
	 * @returns Boolean TRUE in case of success or FALSE in case or error
	 */
	MarkupMarkdownWidget.prototype.insertMedias = function( pics ) {
		var _self = this;
		if ( ! _self.currNode ) {
			return false;
		}
		var html = [ '<ul>' ];
		for ( var p = 0, pic; p < pics.length; p++ ) {
			pic = pics[ p ];
			html.push([
				'<li>',
					'<a href="' + pic.url + '" title="myset' + pic.gal  + ' ' + ( pic.tit.length ? pic.tit : '' )  + '">',
						'<img src="' + pic.src + '" alt="' + pic.alt + '" />',
					'</a>',
				'</li>'
			].join(''))
		}
		html.push( '</ul>' );
		_self.todo.push({
			action: 'pasteHTML',
			node: _self.currNode,
			html: html.join( '' )
		});
		_self.updateHTML();
		_self.instance.editor.trigger( 'summernote.mmd_change' );
	};


	/**
	 * Add the gallery shortcode
	 *
	 * @returns Void
	 */
	MarkupMarkdownWidget.prototype.addMediaFromWidget = function( frameState ) {
		var myState = frameState ? frameState : false;
		if ( ! myState ) {
			return false;
		}
		var gal = myState.get( 'library' );
		if ( gal ) {
			var galShortCode = wp.media.gallery.shortcode( gal ).string();
			if ( galShortCode && galShortCode.length ) {
				activeWidget.galleryCounter++;
				var myGallery = '<div id="tmp_gallery-' + activeWidget.galleryCounter + '"></div>';
				if ( activeWidget.node ) {
					activeWidget.todo.push({
						action: 'pasteHTML',
						node: activeWidget.node,
						html: myGallery
					});
					activeWidget.updateHTML();
					activeWidget.instance.editor.trigger( 'summernote.mmd_change' );
				}
				else if ( activeWidget.context ) {
					activeWidget.context.invoke( 'editor.pasteHTML', myGallery );
				}
				activeWidget.renderGallery( galShortCode, activeWidget.galleryCounter );
			}
		}
	}


	/**
	 * Gallery Preview
	 *
	 * @returns Void
	 */
	MarkupMarkdownWidget.prototype.renderGallery = function( wpGallery, galleryNumber ) {
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
				var html = [
					[
						'<div id="gallery-' + galleryNumber + '"',
						' class="gallery galleryid-' + galleryNumber,
						' gallery-columns-' + galleryOptions.columns,
						' gallery-size-' + galleryOptions.size,
						'" data-shortcode="' + encodeURIComponent( wpGallery ) + '">'
					].join( '' )
				];
				for ( var j = 0; j < imageIds.length; j++ ) {
					html.push( htmlGalleryItem( wp.media.attachment( imageIds[ j ] ).attributes ) );
				}
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
		if ( attachmentLoaded !== imageIds.length ) {
			// All attachment info are not available yet
			wp.media.query({ post__in: imageIds })
				.more().then(function() {
					var galleryHTML = htmlGallery( imageIds );
					setTimeout(function() {
						var galleryNode = document.getElementById( 'tmp_gallery-' + galleryNumber ) || false;
						if ( galleryNode ) {
							galleryNode.innerHTML = galleryHTML.join( '' );
						}
					}, 500);
				});
		}
		else {
			var galleryHTML = htmlGallery( imageIds );
			setTimeout(function() {
				var galleryNode = document.getElementById( 'tmp_gallery-' + galleryNumber ) || false;
				if ( galleryNode ) {
					galleryNode.innerHTML = galleryHTML.join( '' );
				}
				activeWidget.instance.editor.trigger( 'summernote.mmd_change' );
				activeWidget = {};
			}, 500);
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
		// Pictures : https://codex.wordpress.org/Javascript_Reference/wp.media
		mediaFrame = wp.media({
			frame: 'post',
			type: 'image',
			multiple: true,
			title: 'Media'
			// button: { text: 'OK' },
		});
		mediaFrame.on( 'update', function() {
			// **Update** event is triggered when the user presses the "Create the {widget}"
			// from the media modal (example: generating a gallery)
			if ( activeWidget ) {
				activeWidget.addMediaFromWidget( mediaFrame.state() )
			}
		});
		mediaFrame.on( 'insert', function() {
			// **Insert** event is trigerred when the user presses the "Insert into post"
			// inside the media modal (one or multiples images are propably selected)
			if ( activeWidget ) {
				activeWidget.addMediaFromSel( mediaFrame.state() );
			}
		});
	};


	MarkupMarkdownWidget.prototype.core = function( textarea ) {
		if ( ! textarea || ! $( textarea ).length ) {
			return false;
		}
		var _self = this;
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
		// Let the user upload contents
		_self.mediaUploader();
	};


	MarkupMarkdownWidget.prototype.init = function( textarea ) {
		var _self = this;
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
