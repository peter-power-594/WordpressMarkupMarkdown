(function( $ ) {


	function MarkupMarkdown( textarea ) {
    this.todo = [];
    this.updating = false;
    this.instance = {};
    this.init( textarea );
	}


	MarkupMarkdown.prototype.core = function( textarea ) {
    if ( ! textarea || ! $( textarea ).length ) {
      return false;
    }
		var _self = this,
			mediaFrame,
			gal = Math.floor(Math.random() * (9999 - 100) + 100),
			args = decodeURIComponent( jQuery( 'script[src*="wordpress_richedit"]' ).attr( 'src' ) ),
			home_url = '',
			spell_check = '',
			toolbar = [ "bold", "italic", "heading" ];
		if ( wp && wp.pluginMarkupMarkdown && wp.pluginMarkupMarkdown.homeURL ) {
			home_url = wp.pluginMarkupMarkdown.homeURL;
		}
		else {
			home_url = /home_url/.test( args ) ? args.replace( /.*?home_url=/, '' ).replace( /&.*?$/, '' ) : '/';
		}
    _self.home_url = home_url.replace( /^(.*?)(\.[a-z]+\/).*?$/, '$1$2' );
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
		// https://codex.wordpress.org/Javascript_Reference/wp.media
		// Single Picture
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
      _self.addMediaFromWidget( mediaFrame.state() );
    });
    mediaFrame.on( 'insert', function() {
      // **Insert** event is trigerred when the user presses the "Insert into post"
      // inside the media modal (one or multiples images are propably selected)
      _self.addMediaFromSel( mediaFrame.state() );
    });
		toolbar.push({
			name: "wpsimage",
			action: function( editor ) {
				mediaFrame.open();
			},
			className: "fa fa-picture-o",
			title: "Image"
		});
		// Single or Multipictures with iframes
		multiFrame = wp.media({
			title: 'Media',
			button: {
				text: 'OK'
			},
			multiple: true
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
    wp.pluginMarkupMarkdown.instances.push( _self.instance.editor );
	};



  MarkupMarkdown.prototype.addMediaFromWidget = function( frameState ) {
    var _self = this,
      myState = frameState ? frameState : false;
    if ( ! myState ) {
      return false;
    }
    var gal = myState.get( 'library' );
    if ( gal ) {
      var galShortCode = wp.media.gallery.shortcode( gal ).string(),
        cm = _self.instance.editor.codemirror, // Warning ! editor => easeMDE Editor Object, Codemirror Object => editor.codemirror
        doc = cm.getDoc(), // Code Mirror document
        cur = doc.getCursor(); // Code Mirror current curosr position
      if ( galShortCode && galShortCode.length ) {
        return doc.replaceRange( galShortCode, cur );
      }
    }
  };


  MarkupMarkdown.prototype.addMediaFromSel = function( frameState ) {
    var _self = this,
      sel = frameState ? frameState.get( 'selection' ) : false;
    if ( ! sel || ! sel.length ) {
      return false;
    }
    var cm = _self.instance.editor.codemirror, // Warning ! editor => easeMDE Editor Object, Codemirror Object => editor.codemirror
      doc = cm.getDoc(), // Code Mirror document
      cur = doc.getCursor(); // Code Mirror current curosr position
    return sel.map(function( item ) {
      var attachment = item.toJSON(),
        alt = attachment.alt || attachment.title, // Wordpress attachment alternative text
        cap = attachment.caption || '', // Wordpress attachment caption
        url = attachment.url, // Wordpress attachment url
        dpy = ( frameState.display( item ) || {} ).toJSON(), // Wordpress attachment display settings
        mkd = '';
      // Use the specific size if set and remove the domain from url
      if ( dpy.size && attachment.sizes[ dpy.size ] ) {
        url = attachment.sizes[ dpy.size ].url;
      }
      url = url.replace( _self.home_url, '' );
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
            mkd += '(' + dpy.linkUrl + ')';
          }
        }
        else {
          mkd += '(' + ( dpy.link === 'file' ? attachment.url : attachment.link ) + ')';
        }
      }
      return doc.replaceRange( mkd, cur );
    });
  };



  MarkupMarkdown.prototype.renderGallery = function( wpGallery, galleryNumber ) {
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
            '">'         
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
      }, 500);
    }
  };



  MarkupMarkdown.prototype.renderImage = function( wpImage ) {
    var item = wpImage.match( /<img(.*?)>\{\.(align[a-z]+)\}/ );
    if ( ! item || item.length < 2 ) {
      return wpImage.replace( /\{\.(.*?)\}/, '' );
    }
    var figure = '<figure class="' + item[ 2 ] + '"><img' + item[ 1 ] + '>',
      alt = item[ 1 ].match( /alt\=\"(.*?)\"/ ),
      sizes = item[ 1 ].match( /src\=\"(.*?)-(\d+)x(\d+)\.(\w+)\"/ ),
      caption = '';
    if ( alt && alt[ 1 ] && /--/.test( alt[ 1 ] ) ) {
      var txt = alt[ 1 ].split( '--' );
      alt = txt[ 0 ]; caption = txt[ 1 ];
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
    return figure;
  };


  /**
   * EasyMDE custom preview callbacks to support WP specific features
   * 
   * @since 2.1
   * 
   * @param string text The source code used for the rendering
   * @param object The html node preview
   * 
   * @returns string The default text preview
   */
  MarkupMarkdown.prototype.previewRender = function( text, preview ) {
    var _self = this;
		// Render the gallery shortcode. Ref /wp-includes/js/tinymce/plugins/wpgallery/plugin.js
		var galCounter = 0;
		text = text.replace( /\[gallery([^\]]*)\]/g, function( wpGallery ) {
			galCounter++;
      var myGallery = '<div id="tmp_gallery-' + galCounter + '"></div>';
      _self.renderGallery( wpGallery, galCounter );
			return myGallery;
		});
		text = _self.instance.editor.markdown( text );
    // Render the images
    text = text.replace( /<img.*?>\{\.align[a-z]+\}/g, function( wpImage ) {
      var fig = _self.renderImage( wpImage, galCounter );
      return fig;
    });
    text = text.replace( /<p><figure/, '<figure' ).replace( /<\/figure><\/p>/, '</figure>' );
    return text;
	};


	MarkupMarkdown.prototype.init = function( textarea ) {
		var _self = this;
		_self.core( textarea );
	};


  $( document ).ready(function() {
			$( 'body' ).addClass( 'easymde' );
			document.addEventListener( 'CodeMirrorSpellCheckerReady', function() {
				$( '#wp-content-editor-container' ).addClass( 'ready' );
			});
      $( '#wp-content-editor-container .wp-editor-area' ).each(function() {
        new MarkupMarkdown( this );
      });
  });

  window.MarkupMarkdown = MarkupMarkdown;


})( window.jQuery );
