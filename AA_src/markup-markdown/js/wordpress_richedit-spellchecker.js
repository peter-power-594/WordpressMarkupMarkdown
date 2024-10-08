/* global wp, EasyMDE */
/**
 * @preserve The Markup Markdown's Spell Check Wizard Module
 * @desc Word suggestions from hungspell dictionaries
 * @author Pierre-Henri Lavigne <lavigne.pierrehenri@proton.me>
 * @version 1.0.2 
 * @license GPL 3 - https://www.gnu.org/licenses/gpl-3.0.html#license-text
 */

(function( _win, _doc ) {


	// As the suggestion box panel is shared between multiple Code Mirror instances,
	// we need to indentify the active Code Mirror instance
	var activeCmInstance, suggestionBox;

	function MmdSpellWizard( codeMirrorInstance ) {
		if ( ! codeMirrorInstance ) {
			return false;
		}
		this.cm = codeMirrorInstance;
		// To handle the dialog box itself
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


	/**
	 * Hide the suggestions panel
	 *
	 * @param {Object} MyEvent The event handler
	 * @returns {Boolean} TRUE in case of success or FALSE if nothing to do
	 */
	MmdSpellWizard.prototype.hideSuggestPanel = function( myEvent ) {
		var _self = this;
		if ( ! suggestionBox ) {
			return false;
		}
		suggestionBox.style.display = 'none';
		suggestionBox.innerHTML = '';
		return true;
	};


	/**
	 * Show the suggestions panel
	 *
	 * @param {Object} MyEvent The event handler
	 * @returns {Boolean} TRUE in case of success or FALSE if nothing to do
	 */
	MmdSpellWizard.prototype.showSuggestPanel = function( myEvent ) {
		var _self = this;
		if ( ! suggestionBox ) {
			return false;
		}
		suggestionBox.style.top = ( Math.ceil( myEvent.clientY || 0 ) + 16 ) + 'px';
		suggestionBox.style.left = ( Math.ceil( myEvent.clientX || 0 ) - 16 ) + 'px';
		suggestionBox.style.display = 'block';
		return true;
	};


	/**
	 * Build the suggestions panel HTML, append it to the body and bind it properly
	 *
	 * @returns {Boolean} TRUE in case of success or FALSE if nothing to do
	 */
	MmdSpellWizard.prototype.buildSuggestPanel = function() {
		var _self = this;
		if ( suggestionBox && suggestionBox.id ) {
			// Should only be called once
			return false;
		}
		suggestionBox = _doc.createElement( 'div' );
		suggestionBox.id = 'mmd-suggestions';
		suggestionBox.className = 'mmd-spellcheck-suggestions';
		_doc.body.appendChild( suggestionBox );
		_doc.getElementById( 'mmd-suggestions' ).addEventListener( 'click', function( myClickEvent ) {
			myClickEvent.preventDefault();
			if ( ! activeCmInstance ) {
				return false; // Just in case
			}
			// As we replaced an existing word, we need to provide full arguments for CodeMirror to handle the history
			activeCmInstance.replaceRange(
				myClickEvent.target.firstChild.nodeValue, // String replacement text
				_self.from,  // Object { line, ch }
				_self.to,    // Object { line, ch }
				_self.origin // String original text
			);
			setTimeout(function() {
				_self.hideSuggestPanel( myClickEvent );
			}, 450 );
			return false;
		}, false);
		return true;
	};


	/**
	 * Check if the selected string from the codemirror instance is a mispelled word
	 * and trigger the display of the suggestions panel
	 *
	 * @param {Object} MyEvent The event handler
	 * @returns {Boolean} TRUE in case of success or FALSE if nothing to do
	 */
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


	/**
	 * Update the list of suggestions using the dictionnary
	 *
	 * @param {Object} myInstance The current codemirror active instance
	 * @returns {Boolean} TRUE in case of success or FALSE if nothing to do
	 */
	MmdSpellWizard.prototype.updateSuggestions = function( myInstance ) {
		var _self = this;
		if ( ! _self.active ) {
			return false;
		}
		_self.active = true;
		// Hint from https://stackoverflow.com/questions/26576054/codemirror-get-the-current-word-under-the-cursor
		var myCursor = myInstance.getCursor(),
			myWord = myInstance.findWordAt( myCursor );
		if ( myWord && myWord.anchor && myWord.head && CustomCodeMirrorSpellChecker.typo( _self.lang ) ) {
			var myText = myInstance.getRange( myWord.anchor, myWord.head ),
				mySuggestions = CustomCodeMirrorSpellChecker.typo( _self.lang, 'suggest', myText ),
				mySuggestList = [ '<ol>' ];
			for ( var s = 0; s < mySuggestions.length; s++ ) {
				if ( ! /\d+/.test( mySuggestions[ s ] ) ) { // Exclude suggestions including numbers
					mySuggestList.push( '<li><a href="#mmd-suggestions">' + mySuggestions[ s ] + '</li>' );
				}
			}
			// mySuggestList.push( '<li class="last"><a href="#mmd-suggestions" title="Add ' + myText + ' to my dictionnary">' + myText + '</a></li>' );
			mySuggestList.push( '</ol>' );
			suggestionBox = _doc.getElementById( 'mmd-suggestions' );					
			suggestionBox.innerHTML = mySuggestList.join( '' );
			_self.from = myWord.anchor;
			_self.to = myWord.head;
			_self.origin = myText;
		}
		return true;
	};


	/**
	 * Initialize our Spellchecker Wizard. For now we only listen for mouse actions
	 *
	 * @returns {Void}
	 */
	MmdSpellWizard.prototype.initialize = function() {
		var _self = this;
		// Bind the codemirror instance with the mousedown event
		_self.cm.on( 'mousedown', function( myInstance, myEvent ) {
			activeCmInstance = _self.cm;
			_self.checkSelectedWord( myEvent );
		});
		// Refresh the suggestions list if need be
		_self.cm.on( 'cursorActivity', function( myInstance ) {
			_self.updateSuggestions( myInstance );
		});
	};


	_win.MmdSpellWizard = MmdSpellWizard;

})( window, document );
