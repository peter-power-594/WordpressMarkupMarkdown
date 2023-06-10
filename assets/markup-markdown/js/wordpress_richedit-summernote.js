
(function( $ ) {


	function MarkupMarkdown( textarea ) {
		this.todo = [];
		this.updating = false;
		this.home_url = '';
		this.instance = {};
		this.init( textarea );
	}


	MarkupMarkdown.prototype.cleanupMMD = function( html ) {
		var htmlContent = html.replace( /&[amp;]+#(\d+);/g, '&#$1;' )
			.replace( /<li><p>/g, '<li>' )
			.replace( /<\/p><\/li>/g, '</li>' )
			.replace( /<br><\/p>/g, '</p>' );
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


	MarkupMarkdown.prototype.updateHTML = function( ) {
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
	MarkupMarkdown.prototype.checkMedias = function( val, node ) {
		var _self = this;
		if ( /^[+]{2}\s/.test( val ) ) {
			_self.singleFrame.open();
			return 1;
		}
		if ( /^[+]{3}\s/.test( val ) ) {
			_self.multiFrame.open();
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
	MarkupMarkdown.prototype.checkHeadlines = function( val, node ) {
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
	MarkupMarkdown.prototype.checkCodeSnippets = function( val, node ) {
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
	MarkupMarkdown.prototype.checkListItems = function( val, node ) {
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
	MarkupMarkdown.prototype.checkBlockquote = function( val, node ) {
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
	MarkupMarkdown.prototype.formatContent = function( node ) {
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
	MarkupMarkdown.prototype.html2mmdToolkit = function() {
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
		return myService;
	};


	/**
	 * Method to convert Markdown Code to HTML code.
	 * Uses the Showdown library - https://github.com/showdownjs/showdown
	 *
	 * @returns {showdown.Converter}
	 */
	MarkupMarkdown.prototype.mmd2htmlToolkit = function() {
		var myService = new showdown.Converter();
		return myService;
	};


	/**
	 * Replace the original textarea / WYSIWYG with our own
	 *
	 * @returns Void
	 */
	MarkupMarkdown.prototype.switchEditor = function( textarea ) {
		var _self = this;
		// Clone the original Wordpress Textarea and hide it
		$originalEditor = $( textarea ),
		$newEditor = $originalEditor.clone( false );
		$originalEditor.hide();
		$newEditor.removeAttr( 'name' ).removeAttr( 'class' );
		$originalEditor.after( $newEditor );
		$originalEditor.parent().addClass( 'wp-editor-container' );
		// Run Summernote !
		_self.instance.editor = $newEditor.summernote({
			airMode: false,
			toolbar: [
				[ 'style', [ 'bold', 'italic', 'underline', 'clear' ] ],
				[ 'para', [ 'ul', 'ol', 'paragraph' ] ]
			],
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
	MarkupMarkdown.prototype.restoreContent = function() {
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
	MarkupMarkdown.prototype.insertMedia = function( ops ) {
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
	MarkupMarkdown.prototype.insertMedias = function( pics ) {
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
	 * Initialize Wordpress Media Frame to upload or add a unique media item
	 *
	 * @returns Void
	 */
	MarkupMarkdown.prototype.singleMediaUploader = function() {
		var _self = this;
		_self.singleFrame = wp.media({
			title: 'Media',
			button: {
				text: 'OK'
			},
			multiple: false
		});
		_self.singleFrame.on( 'select', function() {
			// Get media attachment details from the frame state
			var sel = _self.singleFrame.state().get( 'selection' );
			return sel.map(function( item ) {
				var attachment = item.toJSON(),
					alt = attachment.alt || '', // Wordpress attachment alternative text
					url = attachment.url.replace( _self.home_url, '' ); // Wordpress attachment url
				if ( url.charAt( 0 ) !== '/' && url.indexOf( 'http' ) === -1 ) {
					url = '/' + url;
				}
				_self.insertMedia({
					url: url,
					alt: alt
				});
			});
		});
	};


	/**
	 * Initialize Wordpress Media Frame to upload or add a group of media items
	 *
	 * @returns Void
	 */
	MarkupMarkdown.prototype.multipleMediaUploader = function() {
		var _self = this,
			gal = Math.floor(Math.random() * (9999 - 100) + 100);
		_self.multiFrame = wp.media({
			title: 'Media',
			button: {
				text: 'OK'
			},
			multiple: true
		});
		_self.multiFrame.on( 'select', function() {
			// Get media attachment details from the frame state
			var sel = _self.multiFrame.state().get( 'selection' );
			if ( sel.length === 1 ) {
				sel.map(function( item ) {
					var attachment = item.toJSON(),
						alt = attachment.alt || '', // WP attachment alternative text
						tit = /[^[a-zA-Z0-9-_%]]*/.test( attachment.title ) ? attachment.title : attachment.caption, // WP attachment title text - exclude defaut image name
						url = attachment.url.replace( _self.home_url, '' ), // WP attachment url
						src = attachment.sizes.large ? attachment.sizes.large.url : ( attachment.sizes.medium ? attachment.sizes.medium.url : attachment.sizes.thumbnail.url); // Large size image
						src = src.replace( _self.home_url, '' );
					if ( url.charAt( 0 ) !== '/' && url.indexOf( 'http' ) === -1 ) {
						url = '/' + url;
					}
					_self.insertMedia({
						url: url,
						alt: alt
					});
				});
				return true;
			}
			else {
				gal++;
				var pics = [];
				sel.map(function( item ) {
					var attachment = item.toJSON(),
						alt = attachment.alt || '',
						tit = /[^[a-zA-Z0-9-_%]]*/.test( attachment.title ) ? attachment.title : attachment.caption,
						url = attachment.url.replace( _self.home_url, '' ),
						src = attachment.sizes.large ? attachment.sizes.large.url : ( attachment.sizes.medium ? attachment.sizes.medium.url : attachment.sizes.thumbnail.url);
					if ( url.charAt( 0 ) !== '/' && url.indexOf( 'http' ) === -1 ) {
						url = '/' + url;
					}
					src = src.replace( _self.home_url, '' );
					if ( src.charAt( 0 ) !== '/' && src.indexOf( 'http' ) === -1 ) {
						src = '/' + src;
					}
					pics.push({
						alt: alt,
						tit: tit,
						gal: gal,
						url: url,
						src: src
					});
				});
				_self.insertMedias( pics );
				return true;
			}
		});
	};


	MarkupMarkdown.prototype.core = function( textarea ) {
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
		_self.singleMediaUploader();
		_self.multipleMediaUploader();
	};


	MarkupMarkdown.prototype.init = function( textarea ) {
		var _self = this;
		_self.core( textarea );
	};


	$( document ).ready(function() {
		$( 'body' ).addClass( 'summernote' );
		$( '#wp-content-editor-container .wp-editor-area' ).each(function() {
			new MarkupMarkdown( this );
		});
	});

	window.MarkupMarkdown = MarkupMarkdown;



})( window.jQuery );
