/* global wp */

/**
 * @preserve codemirror-spell-checker
 * @version 1.1.22
 * @license MIT
 * Copyright Next Step Webs, Inc.
 * @link https://github.com/NextStepWebs/codemirror-spell-checker
 * Modified by Pierre-Henri Lavigne for the Wordpress Plugin Markup Markdown
 * @link https://github.com/peter-power-594/codemirror-spell-checker
 */

"use strict"; // Use strict mode (https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Strict_mode)

// Requires
var Typo = require("typo-js");

var spellCheckerData = {
	completed: -1,
	typo: {},
	langs: [],
	lang: ""
};

function CodeMirrorDictionariesLoader( languages ) {

	if ( spellCheckerData.completed >= 0 ) {
		return false;
	}

	//  AFF/DIC data
	this.data = {
		aff: {},
		dic: {}
	};
	this.urls = {
		aff: [], // .aff files list
		dic: [], // .dic files list
		etr: []  // extra .dic files list
	};
	this.num_loaded = 0;
	this.loading = -1;
	this.shortLang = "";

	var isValidConfig = this.geti18nSettings( languages || false );
	if ( ! isValidConfig ) {
		// No need to go further
		return false;
	}
	
	spellCheckerData.completed = 0;
	
	// From here load the dictionaries
	this.myXHR = null;
	this.myExtra = '';
	this.loading = 0;
	this.retrieveData( 'etr' );
}


CodeMirrorDictionariesLoader.prototype.geti18nSettings = function( languages ) {
	var _self = this;
	// Get multi-lingual settings
	if ( ! languages ) {
		// No args or weird args: disable spell check by default
		return false;
	} else if ( "string" === typeof languages ) {
		// Unique args like "en_US": we disable the default  predefined dictionary from CodeMirror
		return false;
	} else if ( languages.aff && languages.dic ) {
		// Single object arg like this: { aff: '/path/to/file.aff', dic: '/path/to/file.dic' }
		// Keep it in case just for backward compatibility
		_self.urls.aff.push( languages.aff );
		_self.urls.dic.push( languages.dic );
		_self.urls.etr.push( languages.etr || '' );
		if ( languages.code) {
			_self.shortLang = languages.code.replace( /_.*/, "" );
			spellCheckerData.langs.push( _self.shortLang );
			spellCheckerData.lang = _self.shortLang;
		} else {
			spellCheckerData.langs.push( "def" );
			spellCheckerData.lang = "def";
		}
		return true;
	} else {
		for ( var lang in languages ) {
			if ( languages.hasOwnProperty( lang ) ) {
				_self.shortLang = languages[lang].code.replace( /_.*/, "" );
				if ( languages[lang].aff && languages[lang].dic) {
					_self.urls.aff.push( languages[lang].aff );
					_self.urls.dic.push( languages[lang].dic );
					_self.urls.etr.push( languages[lang].etr || '');
					spellCheckerData.langs.push( _self.shortLang );
					if ( ! spellCheckerData.lang || ! spellCheckerData.lang.length ) {
						spellCheckerData.lang = _self.shortLang;
					}
				}
			}
		}
		return true;
	}
};


CodeMirrorDictionariesLoader.prototype.loadData = function() {
	var _self = this;
	_self.num_loaded++;
	if ( _self.num_loaded === 1 ) { // Extra custom dictionary
		_self.myExtra = {};
		if ( _self.myXHR && _self.myXHR.responseText && _self.myXHR.responseText.length ) {
			_self.myExtra = _self.myXHR.responseText.split( /\n/ );
		}
		_self.myXHR = null;
		setTimeout(function() {
			_self.retrieveData( 'dic' );
		}, 50);
	} else if ( _self.num_loaded === 2 ) { // Regular dictionary
		_self.data.dic = _self.myXHR.responseText;
		_self.myXHR = null;
		if ( _self.myExtra && _self.myExtra.length ) {
			// Merge with existing custom data
			for ( var e = 0, extras = _self.myExtra; e < extras.length; e++ ) {
				if ( extras[ e ] && extras[ e ].length ) {
					_self.data.dic += "\n" + extras[ e ];
				}
			}
			_self.myExtra = {};
		}
		setTimeout(function() {
			_self.retrieveData( 'aff' );
		}, 50);
	} else if ( _self.num_loaded === 3) { // Aff dict. data
		_self.data.aff = _self.myXHR.responseText;
		_self.myXHR = null;
		// All data are ready
		var currLang = spellCheckerData.langs[ _self.loading ];
		spellCheckerData.typo[ currLang ] = new Typo(currLang,
			_self.data.aff,
			_self.data.dic,
			{
				platform: "any"
			}
		);
		_self.data.aff = null;
		_self.data.dic = null;
		if ( _self.loading < spellCheckerData.langs.length - 1 ) {
			_self.loading++;
			_self.num_loaded = 0;
			setTimeout(function() {
				_self.retriveData( 'etr' );
			}, 50);
		} else {
			spellCheckerData.completed = 1;
			setTimeout(function() {
				document.dispatchEvent(new Event("CodeMirrorDictionariesReady"));
			}, 50);
		}
	}
};


CodeMirrorDictionariesLoader.prototype.requestCallBack = function() {
	var _self = this;
	if ( _self.myXHR.readyState === 4 && _self.myXHR.status === 200 ) {
		_self.loadData();
	} else if(myXHR.readyState === 4 && myXHR.status !== 200) {
		_self.num_loaded++;
		if ( window.console && window.console.log ) {
			window.console.log("CodeMirrorDictionariesLoader: Error while retrieving dictionaries data");
		}
		setTimeout(function() {
			document.dispatchEvent(new Event("CodeMirrorDictionariesReady"));
		}, 50);
	}
}


CodeMirrorDictionariesLoader.prototype.retrieveData = function( groupName ) {
	var _self = this,
		currIndex = _self.loading;
	if ( ! _self.urls[ groupName ] || ! _self.urls[ groupName ][ currIndex ] || ! _self.urls[ groupName ][ currIndex ].length ) {
		_self.loadData();
	}
	else {
		_self.myXHR = new XMLHttpRequest();
		_self.myXHR.open( 'GET', _self.urls[ groupName ][ currIndex ], true );
		_self.myXHR.onload = function() {
			_self.requestCallBack();
		};
		_self.myXHR.send( null );
	}
};



// Create function
function CodeMirrorSpellChecker( options ) {
	// Initialize
	options = options || {};

	if ( spellCheckerData.completed < 0 ) {
		return new CodeMirrorDictionariesLoader( options );
	}

	// Verify
	if ( typeof options.codeMirrorInstance !== "function" || typeof options.codeMirrorInstance.defineMode !== "function" ) {
		console.log( "CodeMirror Spell Checker: You must provide an instance of CodeMirror via the option `codeMirrorInstance`" );
		return;
	}


	// Because some browsers don't support this functionality yet
	if ( ! String.prototype.includes ) {
		String.prototype.includes = function() {
			"use strict";
			return String.prototype.indexOf.apply(this, arguments) !== -1;
		};
	}


	// Define the new mode
	options.codeMirrorInstance.defineMode( "spell-checker", function( config ) {

		// Define what separates a word
		var rx_word = "!\"#$%&()*+,-—./:;<=>?@[\\]^_`’“”'{|}~ ";


		// Create the overlay and such

		var i18n = 0;

		var overlay = {
			token: function( stream ) {
				var ch = stream.peek();
				var word = "";

				if ( rx_word.includes( ch ) || ch === '\uE000' || ch === '\uE001' ) {
					stream.next();
					return null;
				}

				while( ( ch = stream.peek() ) != null && ! rx_word.includes( ch ) ) {
					word += ch;
					stream.next();
				}

				if ( /^\p{Extended_Pictographic}$/u.test( word ) ) {
					return null;
				}

				// HTML <span lang="fr">Mon texte</span> will be parsed in *CodeMirror5* 
				// by order as the following _words_ (ch)  : span lang fr Mon texte span 
				// Warning : "_" is considered as delimiter so  
				// <span lang="en_US">My text</span> will be parsed in *CodeMirror5*
				// by order as the following _words_ (ch) : span lang en US My text span 
				if ( word === "span" ) {
					if ( i18n === 3 ) {
						// span from the </span>, switch back to default language
						spellCheckerData.lang = spellCheckerData.langs[0];
						i18n = 0;
					} else {
						// Beginning of a <span>, standby to look for a lang attribute
						i18n = 1;
					}
				} else if ( word === "lang" ) {
					// **Probably** a lang attribute
					i18n = 2;
				} else if ( i18n === 2 ) {
					i18n = 3;
					// We've got a match. At this point the _word_ value is the lang'sattribute data !
					if ( spellCheckerData.langs[ word ] ) {
						// Not 100% safe so a check on an existing dict is still required
						spellCheckerData.lang = word;
					}
				} else if ( spellCheckerData.typo[ spellCheckerData.lang ] && ! spellCheckerData.typo[ spellCheckerData.lang ].check( word ) ) {
					return "spell-error " + spellCheckerData.lang; // CSS class: cm-spell-error
				}

				return null;
			}
		};

		var mode = options.codeMirrorInstance.getMode(
			config, config.backdrop || "text/plain"
		);

		return options.codeMirrorInstance.overlayMode( mode, overlay, true );
	});


	document.dispatchEvent(new Event("CodeMirrorSpellCheckerReady"));
}


CodeMirrorSpellChecker.typo = function( myLang, myMethod, myArg ) {
	if ( ! myLang || ! spellCheckerData.typo[ myLang ] ) {
		return false;
	}
	else if ( ! myMethod ) {
		return true;
	}
	else {
		if ( typeof spellCheckerData.typo[ myLang ][ myMethod ] === 'function' ) {
			return spellCheckerData.typo[ myLang ][ myMethod ]( myArg );
		}
		else {
			return false;
		}
	}
};

// Export
module.exports = CodeMirrorSpellChecker;
