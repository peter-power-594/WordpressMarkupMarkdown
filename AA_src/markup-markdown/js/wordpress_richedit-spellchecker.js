/* global wp, EasyMDE */

(function( _win, _doc ) {


	function MmdSpellWizard( codeMirrorInstance ) {
		if ( ! codeMirrorInstance ) {
			return false;
		}
		this.cm = codeMirrorInstance;
		// To handle the dialog box itself
		this.el = null;
		this.display = false;
		this.active = false;
		// To handle the codemirror document
		this.from = {};
		this.to = {};
		this.origin = {};
		// Target dictionnary lang, required for multi-language
		this.lang = '';
		this.initialize();
	}


	MmdSpellWizard.prototype.hideSuggestPanel = function( myEvent ) {
		var _self = this;
		if ( ! _self.el ) {
			return false;
		}
		_self.el.style.display = 'none';
		_self.el.innerHTML = '';
		return true;
	};


	MmdSpellWizard.prototype.showSuggestPanel = function( myEvent ) {
		var _self = this;
		if ( ! _self.el ) {
			return false;
		}
		_self.el.style.top = ( Math.ceil( myEvent.clientY || 0 ) + 16 ) + 'px';
		_self.el.style.left = ( Math.ceil( myEvent.clientX || 0 ) - 16 ) + 'px';
		_self.el.style.display = 'block';
		return true;
	};


	MmdSpellWizard.prototype.buildSuggestPanel = function() {
		var _self = this;
		_self.el = _doc.createElement( 'div' );
		_self.el.id = 'mmd-suggestions';
		_self.el.className = 'mmd-spellcheck-suggestions';
		_doc.body.appendChild( _self.el );
		_doc.getElementById( 'mmd-suggestions' ).addEventListener( 'click', function( myEvent ) {
			event.preventDefault();
			// As we replaced an existing word, we need to provide full arguments for CodeMirror to handle the history
			_self.cm.replaceRange(
				myEvent.target.firstChild.nodeValue, // String replacement text
				_self.from,  // Object { line, ch }
				_self.to,    // Object { line, ch }
				_self.origin // String original text
			);
			setTimeout(function() {
				_self.hideSuggestPanel( myEvent );
			}, 450 );
			return false;
		}, false);
	};


	MmdSpellWizard.prototype.checkSelectedWord = function( myEvent ) {
		var _self = this;
		if ( myEvent && myEvent.target && /cm-spell-error/.test( myEvent.target.className || '' ) ) {
			if ( ! _self.display ) {
				// Fist time, need to build the container
				_self.buildSuggestPanel();
				_self.display = true;
			}
			_self.showSuggestPanel( myEvent );
			_self.lang = ( myEvent.target.className || '' ).match( /.*?cm-(\w+)$/ )[ 1 ];
			_self.active = true;
		}
		else if ( _self.active ) {
			_self.active = false;
			// Hide and empty the suggestion list panel
			_self.hideSuggestPanel( myEvent );
		}
	};


	MmdSpellWizard.prototype.updateSuggestions = function( myInstance ) {
		var _self = this;
		if ( ! _self.active ) {
			return false;
		}
		// Hint from https://stackoverflow.com/questions/26576054/codemirror-get-the-current-word-under-the-cursor
		var myCursor = myInstance.getCursor(),
			myWord = myInstance.findWordAt( myCursor );
		if ( myWord && myWord.anchor && myWord.head && CodeMirrorSpellChecker.typo[ _self.lang ] ) {
			var myText = myInstance.getRange( myWord.anchor, myWord.head ),
				mySuggestions = CodeMirrorSpellChecker.typo[ _self.lang ].suggest( myText ),
				mySuggestList = [ '<ol>' ];
			for ( var s = 0; s < mySuggestions.length; s++ ) {
				if ( ! /\d+/.test( mySuggestions[ s ] ) ) {
					mySuggestList.push( '<li><a href="#mmd-suggestions">' + mySuggestions[ s ] + '</li>' );
				}
			}
			mySuggestList.push( '</ol>' );
			_self.el = _doc.getElementById( 'mmd-suggestions' );					
			_self.el.innerHTML = mySuggestList.join( '' );
			_self.from = myWord.anchor;
			_self.to = myWord.head;
			_self.origin = myText;
		}
		_self.active = true;
		return true;
	};


	MmdSpellWizard.prototype.initialize = function() {
		var _self = this;
		// Bind the codemirror instance with the mousedown event
		_self.cm.on( 'mousedown', function( myInstance, myEvent ) {
			_self.checkSelectedWord( myEvent );
		});
		// Refresh the suggestions list if need be
		_self.cm.on( 'cursorActivity', function( myInstance ) {
			_self.updateSuggestions( myInstance );
		});
	};


	_win.MmdSpellWizard = MmdSpellWizard;

})( window, document );