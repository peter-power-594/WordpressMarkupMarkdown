(function(f){if(typeof exports==="object"&&typeof module!=="undefined"){module.exports=f()}else if(typeof define==="function"&&define.amd){define([],f)}else{var g;if(typeof window!=="undefined"){g=window}else if(typeof global!=="undefined"){g=global}else if(typeof self!=="undefined"){g=self}else{g=this}g.CodeMirrorSpellChecker = f()}})(function(){var define,module,exports;return (function(){function r(e,n,t){function o(i,f){if(!n[i]){if(!e[i]){var c="function"==typeof require&&require;if(!f&&c)return c(i,!0);if(u)return u(i,!0);var a=new Error("Cannot find module '"+i+"'");throw a.code="MODULE_NOT_FOUND",a}var p=n[i]={exports:{}};e[i][0].call(p.exports,function(r){var n=e[i][1][r];return o(n||r)},p,p.exports,r,e,n,t)}return n[i].exports}for(var u="function"==typeof require&&require,i=0;i<t.length;i++)o(t[i]);return o}return r})()({1:[function(require,module,exports){
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
	spellCheckerData.completed = 0;

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
				_self.retrieveData( 'etr' );
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

},{"typo-js":3}],2:[function(require,module,exports){

},{}],3:[function(require,module,exports){
(function (__dirname){(function (){
/* globals chrome: false */
/* globals __dirname: false */
/* globals require: false */
/* globals Buffer: false */
/* globals module: false */

/**
 * Typo is a JavaScript implementation of a spellchecker using hunspell-style 
 * dictionaries.
 */

var Typo;

(function () {
"use strict";

/**
 * Typo constructor.
 *
 * @param {String} [dictionary] The locale code of the dictionary being used. e.g.,
 *                              "en_US". This is only used to auto-load dictionaries.
 * @param {String} [affData]    The data from the dictionary's .aff file. If omitted
 *                              and Typo.js is being used in a Chrome extension, the .aff
 *                              file will be loaded automatically from
 *                              lib/typo/dictionaries/[dictionary]/[dictionary].aff
 *                              In other environments, it will be loaded from
 *                              [settings.dictionaryPath]/dictionaries/[dictionary]/[dictionary].aff
 * @param {String} [wordsData]  The data from the dictionary's .dic file. If omitted
 *                              and Typo.js is being used in a Chrome extension, the .dic
 *                              file will be loaded automatically from
 *                              lib/typo/dictionaries/[dictionary]/[dictionary].dic
 *                              In other environments, it will be loaded from
 *                              [settings.dictionaryPath]/dictionaries/[dictionary]/[dictionary].dic
 * @param {Object} [settings]   Constructor settings. Available properties are:
 *                              {String} [dictionaryPath]: path to load dictionary from in non-chrome
 *                              environment.
 *                              {Object} [flags]: flag information.
 *                              {Boolean} [asyncLoad]: If true, affData and wordsData will be loaded
 *                              asynchronously.
 *                              {Function} [loadedCallback]: Called when both affData and wordsData
 *                              have been loaded. Only used if asyncLoad is set to true. The parameter
 *                              is the instantiated Typo object.
 *
 * @returns {Typo} A Typo object.
 */

Typo = function (dictionary, affData, wordsData, settings) {
	settings = settings || {};

	this.dictionary = null;
	
	this.rules = {};
	this.dictionaryTable = {};
	
	this.compoundRules = [];
	this.compoundRuleCodes = {};
	
	this.replacementTable = [];
	
	this.flags = settings.flags || {}; 
	
	this.memoized = {};

	this.loaded = false;
	
	var self = this;
	
	var path;
	
	// Loop-control variables.
	var i, j, _len, _jlen;
	
	if (dictionary) {
		self.dictionary = dictionary;
		
		// If the data is preloaded, just setup the Typo object.
		if (affData && wordsData) {
			setup();
		}
		// Loading data for Chrome extentions.
		else if (typeof window !== 'undefined' && 'chrome' in window && 'extension' in window.chrome && 'getURL' in window.chrome.extension) {
			if (settings.dictionaryPath) {
				path = settings.dictionaryPath;
			}
			else {
				path = "typo/dictionaries";
			}
			
			if (!affData) readDataFile(chrome.extension.getURL(path + "/" + dictionary + "/" + dictionary + ".aff"), setAffData);
			if (!wordsData) readDataFile(chrome.extension.getURL(path + "/" + dictionary + "/" + dictionary + ".dic"), setWordsData);
		}
		else {
			if (settings.dictionaryPath) {
				path = settings.dictionaryPath;
			}
			else if (typeof __dirname !== 'undefined') {
				path = __dirname + '/dictionaries';
			}
			else {
				path = './dictionaries';
			}
			
			if (!affData) readDataFile(path + "/" + dictionary + "/" + dictionary + ".aff", setAffData);
			if (!wordsData) readDataFile(path + "/" + dictionary + "/" + dictionary + ".dic", setWordsData);
		}
	}
	
	function readDataFile(url, setFunc) {
		var response = self._readFile(url, null, settings.asyncLoad);
		
		if (settings.asyncLoad) {
			response.then(function(data) {
				setFunc(data);
			});
		}
		else {
			setFunc(response);
		}
	}

	function setAffData(data) {
		affData = data;

		if (wordsData) {
			setup();
		}
	}

	function setWordsData(data) {
		wordsData = data;

		if (affData) {
			setup();
		}
	}

	function setup() {
		self.rules = self._parseAFF(affData);
		
		// Save the rule codes that are used in compound rules.
		self.compoundRuleCodes = {};
		
		for (i = 0, _len = self.compoundRules.length; i < _len; i++) {
			var rule = self.compoundRules[i];
			
			for (j = 0, _jlen = rule.length; j < _jlen; j++) {
				self.compoundRuleCodes[rule[j]] = [];
			}
		}
		
		// If we add this ONLYINCOMPOUND flag to self.compoundRuleCodes, then _parseDIC
		// will do the work of saving the list of words that are compound-only.
		if ("ONLYINCOMPOUND" in self.flags) {
			self.compoundRuleCodes[self.flags.ONLYINCOMPOUND] = [];
		}
		
		self.dictionaryTable = self._parseDIC(wordsData);
		
		// Get rid of any codes from the compound rule codes that are never used 
		// (or that were special regex characters).  Not especially necessary... 
		for (i in self.compoundRuleCodes) {
			if (self.compoundRuleCodes[i].length === 0) {
				delete self.compoundRuleCodes[i];
			}
		}
		
		// Build the full regular expressions for each compound rule.
		// I have a feeling (but no confirmation yet) that this method of 
		// testing for compound words is probably slow.
		for (i = 0, _len = self.compoundRules.length; i < _len; i++) {
			var ruleText = self.compoundRules[i];
			
			var expressionText = "";
			
			for (j = 0, _jlen = ruleText.length; j < _jlen; j++) {
				var character = ruleText[j];
				
				if (character in self.compoundRuleCodes) {
					expressionText += "(" + self.compoundRuleCodes[character].join("|") + ")";
				}
				else {
					expressionText += character;
				}
			}
			
			self.compoundRules[i] = new RegExp(expressionText, "i");
		}
		
		self.loaded = true;
		
		if (settings.asyncLoad && settings.loadedCallback) {
			settings.loadedCallback(self);
		}
	}
	
	return this;
};

Typo.prototype = {
	/**
	 * Loads a Typo instance from a hash of all of the Typo properties.
	 *
	 * @param object obj A hash of Typo properties, probably gotten from a JSON.parse(JSON.stringify(typo_instance)).
	 */
	
	load : function (obj) {
		for (var i in obj) {
			if (obj.hasOwnProperty(i)) {
				this[i] = obj[i];
			}
		}
		
		return this;
	},
	
	/**
	 * Read the contents of a file.
	 * 
	 * @param {String} path The path (relative) to the file.
	 * @param {String} [charset="ISO8859-1"] The expected charset of the file
	 * @param {Boolean} async If true, the file will be read asynchronously. For node.js this does nothing, all
	 *        files are read synchronously.
	 * @returns {String} The file data if async is false, otherwise a promise object. If running node.js, the data is
	 *          always returned.
	 */
	
	_readFile : function (path, charset, async) {
		charset = charset || "utf8";
		
		if (typeof XMLHttpRequest !== 'undefined') {
			var promise;
			var req = new XMLHttpRequest();
			req.open("GET", path, async);
			
			if (async) {
				promise = new Promise(function(resolve, reject) {
					req.onload = function() {
						if (req.status === 200) {
							resolve(req.responseText);
						}
						else {
							reject(req.statusText);
						}
					};
					
					req.onerror = function() {
						reject(req.statusText);
					}
				});
			}
		
			if (req.overrideMimeType)
				req.overrideMimeType("text/plain; charset=" + charset);
		
			req.send(null);
			
			return async ? promise : req.responseText;
		}
		else if (typeof require !== 'undefined') {
			// Node.js
			var fs = require("fs");
			
			try {
				if (fs.existsSync(path)) {
					return fs.readFileSync(path, charset);
				}
				else {
					console.log("Path " + path + " does not exist.");
				}
			} catch (e) {
				console.log(e);
				return '';
			}
		}
	},
	
	/**
	 * Parse the rules out from a .aff file.
	 *
	 * @param {String} data The contents of the affix file.
	 * @returns object The rules from the file.
	 */
	
	_parseAFF : function (data) {
		var rules = {};
		
		var line, subline, numEntries, lineParts;
		var i, j, _len, _jlen;
		
		var lines = data.split(/\r?\n/);
		
		for (i = 0, _len = lines.length; i < _len; i++) {
			// Remove comment lines
			line = this._removeAffixComments(lines[i]);
			line = line.trim();
			
			if ( ! line ) {
				continue;
			}
			
			var definitionParts = line.split(/\s+/);
			
			var ruleType = definitionParts[0];
			
			if (ruleType == "PFX" || ruleType == "SFX") {
				var ruleCode = definitionParts[1];
				var combineable = definitionParts[2];
				numEntries = parseInt(definitionParts[3], 10);
				
				var entries = [];
				
				for (j = i + 1, _jlen = i + 1 + numEntries; j < _jlen; j++) {
					subline = lines[j];
					
					lineParts = subline.split(/\s+/);
					var charactersToRemove = lineParts[2];
					
					var additionParts = lineParts[3].split("/");
					
					var charactersToAdd = additionParts[0];
					if (charactersToAdd === "0") charactersToAdd = "";
					
					var continuationClasses = this.parseRuleCodes(additionParts[1]);
					
					var regexToMatch = lineParts[4];
					
					var entry = {};
					entry.add = charactersToAdd;
					
					if (continuationClasses.length > 0) entry.continuationClasses = continuationClasses;
					
					if (regexToMatch !== ".") {
						if (ruleType === "SFX") {
							entry.match = new RegExp(regexToMatch + "$");
						}
						else {
							entry.match = new RegExp("^" + regexToMatch);
						}
					}
					
					if (charactersToRemove != "0") {
						if (ruleType === "SFX") {
							entry.remove = new RegExp(charactersToRemove  + "$");
						}
						else {
							entry.remove = charactersToRemove;
						}
					}
					
					entries.push(entry);
				}
				
				rules[ruleCode] = { "type" : ruleType, "combineable" : (combineable == "Y"), "entries" : entries };
				
				i += numEntries;
			}
			else if (ruleType === "COMPOUNDRULE") {
				numEntries = parseInt(definitionParts[1], 10);
				
				for (j = i + 1, _jlen = i + 1 + numEntries; j < _jlen; j++) {
					line = lines[j];
					
					lineParts = line.split(/\s+/);
					this.compoundRules.push(lineParts[1]);
				}
				
				i += numEntries;
			}
			else if (ruleType === "REP") {
				lineParts = line.split(/\s+/);
				
				if (lineParts.length === 3) {
					this.replacementTable.push([ lineParts[1], lineParts[2] ]);
				}
			}
			else {
				// ONLYINCOMPOUND
				// COMPOUNDMIN
				// FLAG
				// KEEPCASE
				// NEEDAFFIX
				
				this.flags[ruleType] = definitionParts[1];
			}
		}
		
		return rules;
	},
	
	/**
	 * Removes comments.
	 *
	 * @param {String} data A line from an affix file.
	 * @return {String} The cleaned-up line.
	 */
	
	_removeAffixComments : function (line) {
		// This used to remove any string starting with '#' up to the end of the line,
		// but some COMPOUNDRULE definitions include '#' as part of the rule.
		// So, only remove lines that begin with a comment, optionally preceded by whitespace.
		if ( line.match( /^\s*#/, "" ) ) {
			return '';
		}
		
		return line;
	},
	
	/**
	 * Parses the words out from the .dic file.
	 *
	 * @param {String} data The data from the dictionary file.
	 * @returns object The lookup table containing all of the words and
	 *                 word forms from the dictionary.
	 */
	
	_parseDIC : function (data) {
		data = this._removeDicComments(data);
		
		var lines = data.split(/\r?\n/);
		var dictionaryTable = {};
		
		function addWord(word, rules) {
			// Some dictionaries will list the same word multiple times with different rule sets.
			if (!dictionaryTable.hasOwnProperty(word)) {
				dictionaryTable[word] = null;
			}
			
			if (rules.length > 0) {
				if (dictionaryTable[word] === null) {
					dictionaryTable[word] = [];
				}

				dictionaryTable[word].push(rules);
			}
		}
		
		// The first line is the number of words in the dictionary.
		for (var i = 1, _len = lines.length; i < _len; i++) {
			var line = lines[i];
			
			if (!line) {
				// Ignore empty lines.
				continue;
			}

			var parts = line.split("/", 2);
			
			var word = parts[0];

			// Now for each affix rule, generate that form of the word.
			if (parts.length > 1) {
				var ruleCodesArray = this.parseRuleCodes(parts[1]);
				
				// Save the ruleCodes for compound word situations.
				if (!("NEEDAFFIX" in this.flags) || ruleCodesArray.indexOf(this.flags.NEEDAFFIX) == -1) {
					addWord(word, ruleCodesArray);
				}
				
				for (var j = 0, _jlen = ruleCodesArray.length; j < _jlen; j++) {
					var code = ruleCodesArray[j];
					
					var rule = this.rules[code];
					
					if (rule) {
						var newWords = this._applyRule(word, rule);
						
						for (var ii = 0, _iilen = newWords.length; ii < _iilen; ii++) {
							var newWord = newWords[ii];
							
							addWord(newWord, []);
							
							if (rule.combineable) {
								for (var k = j + 1; k < _jlen; k++) {
									var combineCode = ruleCodesArray[k];
									
									var combineRule = this.rules[combineCode];
									
									if (combineRule) {
										if (combineRule.combineable && (rule.type != combineRule.type)) {
											var otherNewWords = this._applyRule(newWord, combineRule);
											
											for (var iii = 0, _iiilen = otherNewWords.length; iii < _iiilen; iii++) {
												var otherNewWord = otherNewWords[iii];
												addWord(otherNewWord, []);
											}
										}
									}
								}
							}
						}
					}
					
					if (code in this.compoundRuleCodes) {
						this.compoundRuleCodes[code].push(word);
					}
				}
			}
			else {
				addWord(word.trim(), []);
			}
		}
		
		return dictionaryTable;
	},
	
	
	/**
	 * Removes comment lines and then cleans up blank lines and trailing whitespace.
	 *
	 * @param {String} data The data from a .dic file.
	 * @return {String} The cleaned-up data.
	 */
	
	_removeDicComments : function (data) {
		// I can't find any official documentation on it, but at least the de_DE
		// dictionary uses tab-indented lines as comments.
		
		// Remove comments
		data = data.replace(/^\t.*$/mg, "");
		
		return data;
	},
	
	parseRuleCodes : function (textCodes) {
		if (!textCodes) {
			return [];
		}
		else if (!("FLAG" in this.flags)) {
			// The flag symbols are single characters
			return textCodes.split("");
		}
		else if (this.flags.FLAG === "long") {
			// The flag symbols are two characters long.
			var flags = [];
			
			for (var i = 0, _len = textCodes.length; i < _len; i += 2) {
				flags.push(textCodes.substr(i, 2));
			}
			
			return flags;
		}
		else if (this.flags.FLAG === "num") {
			// The flag symbols are a CSV list of numbers.
			return textCodes.split(",");
		}
		else if (this.flags.FLAG === "UTF-8") {
			// The flags are single UTF-8 characters.
			// @see https://github.com/cfinke/Typo.js/issues/57
			return Array.from(textCodes);
		}
		else {
			// It's possible that this fallback case will not work for all FLAG values,
			// but I think it's more likely to work than not returning anything at all.
			return textCodes.split("");
		}
	},
	
	/**
	 * Applies an affix rule to a word.
	 *
	 * @param {String} word The base word.
	 * @param {Object} rule The affix rule.
	 * @returns {String[]} The new words generated by the rule.
	 */
	
	_applyRule : function (word, rule) {
		var entries = rule.entries;
		var newWords = [];
		
		for (var i = 0, _len = entries.length; i < _len; i++) {
			var entry = entries[i];
			
			if (!entry.match || word.match(entry.match)) {
				var newWord = word;
				
				if (entry.remove) {
					newWord = newWord.replace(entry.remove, "");
				}
				
				if (rule.type === "SFX") {
					newWord = newWord + entry.add;
				}
				else {
					newWord = entry.add + newWord;
				}
				
				newWords.push(newWord);
				
				if ("continuationClasses" in entry) {
					for (var j = 0, _jlen = entry.continuationClasses.length; j < _jlen; j++) {
						var continuationRule = this.rules[entry.continuationClasses[j]];
						
						if (continuationRule) {
							newWords = newWords.concat(this._applyRule(newWord, continuationRule));
						}
						/*
						else {
							// This shouldn't happen, but it does, at least in the de_DE dictionary.
							// I think the author mistakenly supplied lower-case rule codes instead 
							// of upper-case.
						}
						*/
					}
				}
			}
		}
		
		return newWords;
	},
	
	/**
	 * Checks whether a word or a capitalization variant exists in the current dictionary.
	 * The word is trimmed and several variations of capitalizations are checked.
	 * If you want to check a word without any changes made to it, call checkExact()
	 *
	 * @see http://blog.stevenlevithan.com/archives/faster-trim-javascript re:trimming function
	 *
	 * @param {String} aWord The word to check.
	 * @returns {Boolean}
	 */
	
	check : function (aWord) {
		if (!this.loaded) {
			throw "Dictionary not loaded.";
		}
		
		if (!aWord) {
			return false;
		}
		
		// Remove leading and trailing whitespace
		var trimmedWord = aWord.replace(/^\s\s*/, '').replace(/\s\s*$/, '');
		
		if (this.checkExact(trimmedWord)) {
			return true;
		}
		
		// The exact word is not in the dictionary.
		if (trimmedWord.toUpperCase() === trimmedWord) {
			// The word was supplied in all uppercase.
			// Check for a capitalized form of the word.
			var capitalizedWord = trimmedWord[0] + trimmedWord.substring(1).toLowerCase();
			
			if (this.hasFlag(capitalizedWord, "KEEPCASE")) {
				// Capitalization variants are not allowed for this word.
				return false;
			}
			
			if (this.checkExact(capitalizedWord)) {
				// The all-caps word is a capitalized word spelled correctly.
				return true;
			}

			if (this.checkExact(trimmedWord.toLowerCase())) {
				// The all-caps is a lowercase word spelled correctly.
				return true;
			}
		}
		
		var uncapitalizedWord = trimmedWord[0].toLowerCase() + trimmedWord.substring(1);
		
		if (uncapitalizedWord !== trimmedWord) {
			if (this.hasFlag(uncapitalizedWord, "KEEPCASE")) {
				// Capitalization variants are not allowed for this word.
				return false;
			}
			
			// Check for an uncapitalized form
			if (this.checkExact(uncapitalizedWord)) {
				// The word is spelled correctly but with the first letter capitalized.
				return true;
			}
		}
		
		return false;
	},
	
	/**
	 * Checks whether a word exists in the current dictionary.
	 *
	 * @param {String} word The word to check.
	 * @returns {Boolean}
	 */
	
	checkExact : function (word) {
		if (!this.loaded) {
			throw "Dictionary not loaded.";
		}

		var ruleCodes = this.dictionaryTable[word];
		
		var i, _len;
		
		if (typeof ruleCodes === 'undefined') {
			// Check if this might be a compound word.
			if ("COMPOUNDMIN" in this.flags && word.length >= this.flags.COMPOUNDMIN) {
				for (i = 0, _len = this.compoundRules.length; i < _len; i++) {
					if (word.match(this.compoundRules[i])) {
						return true;
					}
				}
			}
		}
		else if (ruleCodes === null) {
			// a null (but not undefined) value for an entry in the dictionary table
			// means that the word is in the dictionary but has no flags.
			return true;
		}
		else if (typeof ruleCodes === 'object') { // this.dictionary['hasOwnProperty'] will be a function.
			for (i = 0, _len = ruleCodes.length; i < _len; i++) {
				if (!this.hasFlag(word, "ONLYINCOMPOUND", ruleCodes[i])) {
					return true;
				}
			}
		}

		return false;
	},
	
	/**
	 * Looks up whether a given word is flagged with a given flag.
	 *
	 * @param {String} word The word in question.
	 * @param {String} flag The flag in question.
	 * @return {Boolean}
	 */
	 
	hasFlag : function (word, flag, wordFlags) {
		if (!this.loaded) {
			throw "Dictionary not loaded.";
		}

		if (flag in this.flags) {
			if (typeof wordFlags === 'undefined') {
				wordFlags = Array.prototype.concat.apply([], this.dictionaryTable[word]);
			}
			
			if (wordFlags && wordFlags.indexOf(this.flags[flag]) !== -1) {
				return true;
			}
		}
		
		return false;
	},
	
	/**
	 * Returns a list of suggestions for a misspelled word.
	 *
	 * @see http://www.norvig.com/spell-correct.html for the basis of this suggestor.
	 * This suggestor is primitive, but it works.
	 *
	 * @param {String} word The misspelling.
	 * @param {Number} [limit=5] The maximum number of suggestions to return.
	 * @returns {String[]} The array of suggestions.
	 */
	
	alphabet : "",
	
	suggest : function (word, limit) {
		if (!this.loaded) {
			throw "Dictionary not loaded.";
		}

		limit = limit || 5;

		if (this.memoized.hasOwnProperty(word)) {
			var memoizedLimit = this.memoized[word]['limit'];

			// Only return the cached list if it's big enough or if there weren't enough suggestions
			// to fill a smaller limit.
			if (limit <= memoizedLimit || this.memoized[word]['suggestions'].length < memoizedLimit) {
				return this.memoized[word]['suggestions'].slice(0, limit);
			}
		}
		
		if (this.check(word)) return [];
		
		// Check the replacement table.
		for (var i = 0, _len = this.replacementTable.length; i < _len; i++) {
			var replacementEntry = this.replacementTable[i];
			
			if (word.indexOf(replacementEntry[0]) !== -1) {
				var correctedWord = word.replace(replacementEntry[0], replacementEntry[1]);
				
				if (this.check(correctedWord)) {
					return [ correctedWord ];
				}
			}
		}
		
		if (!this.alphabet) {
			// Use the English alphabet as the default. Problematic, but backwards-compatible.
			this.alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
			
			// Any characters defined in the affix file as substitutions can go in the alphabet too.
			// Note that dictionaries do not include the entire alphabet in the TRY flag when it's there.
			// For example, Q is not in the default English TRY list; that's why having the default
			// alphabet above is useful.
			if ( 'TRY' in this.flags ) {
				this.alphabet += this.flags['TRY'];
			}
			
			// Plus any additional characters specifically defined as being allowed in words.
			if ( 'WORDCHARS' in this.flags ) {
				this.alphabet += this.flags['WORDCHARS'];
			}
			
			// Remove any duplicates.
			var alphaArray = this.alphabet.split("");
			alphaArray.sort();

			var alphaHash = {};
			for ( var i = 0; i < alphaArray.length; i++ ) {
				alphaHash[ alphaArray[i] ] = true;
			}
			
			this.alphabet = '';
			
			for ( var i in alphaHash ) {
				this.alphabet += i;
			}
		}
		
		var self = this;

		/**
		 * Returns a hash keyed by all of the strings that can be made by making a single edit to the word (or words in) `words`
		 * The value of each entry is the number of unique ways that the resulting word can be made.
		 *
		 * @arg mixed words Either a hash keyed by words or a string word to operate on.
		 * @arg bool known_only Whether this function should ignore strings that are not in the dictionary.
		 */
		function edits1(words, known_only) {
			var rv = {};
			
			var i, j, _iilen, _len, _jlen, _edit;

			var alphabetLength = self.alphabet.length;
			
			if (typeof words == 'string') {
				var word = words;
				words = {};
				words[word] = true;
			}

			for (var word in words) {
				for (i = 0, _len = word.length + 1; i < _len; i++) {
					var s = [ word.substring(0, i), word.substring(i) ];
				
					// Remove a letter.
					if (s[1]) {
						_edit = s[0] + s[1].substring(1);

						if (!known_only || self.check(_edit)) {
							if (!(_edit in rv)) {
								rv[_edit] = 1;
							}
							else {
								rv[_edit] += 1;
							}
						}
					}
					
					// Transpose letters
					// Eliminate transpositions of identical letters
					if (s[1].length > 1 && s[1][1] !== s[1][0]) {
						_edit = s[0] + s[1][1] + s[1][0] + s[1].substring(2);

						if (!known_only || self.check(_edit)) {
							if (!(_edit in rv)) {
								rv[_edit] = 1;
							}
							else {
								rv[_edit] += 1;
							}
						}
					}

					if (s[1]) {
						// Replace a letter with another letter.

						var lettercase = (s[1].substring(0,1).toUpperCase() === s[1].substring(0,1)) ? 'uppercase' : 'lowercase';

						for (j = 0; j < alphabetLength; j++) {
							var replacementLetter = self.alphabet[j];

							// Set the case of the replacement letter to the same as the letter being replaced.
							if ( 'uppercase' === lettercase ) {
								replacementLetter = replacementLetter.toUpperCase();
							}

							// Eliminate replacement of a letter by itself
							if (replacementLetter != s[1].substring(0,1)){
								_edit = s[0] + replacementLetter + s[1].substring(1);

								if (!known_only || self.check(_edit)) {
									if (!(_edit in rv)) {
										rv[_edit] = 1;
									}
									else {
										rv[_edit] += 1;
									}
								}
							}
						}
					}

					if (s[1]) {
						// Add a letter between each letter.
						for (j = 0; j < alphabetLength; j++) {
							// If the letters on each side are capitalized, capitalize the replacement.
							var lettercase = (s[0].substring(-1).toUpperCase() === s[0].substring(-1) && s[1].substring(0,1).toUpperCase() === s[1].substring(0,1)) ? 'uppercase' : 'lowercase';

							var replacementLetter = self.alphabet[j];

							if ( 'uppercase' === lettercase ) {
								replacementLetter = replacementLetter.toUpperCase();
							}

							_edit = s[0] + replacementLetter + s[1];

							if (!known_only || self.check(_edit)) {
								if (!(_edit in rv)) {
									rv[_edit] = 1;
								}
								else {
									rv[_edit] += 1;
								}
							}
						}
					}
				}
			}
			
			return rv;
		}

		function correct(word) {
			// Get the edit-distance-1 and edit-distance-2 forms of this word.
			var ed1 = edits1(word);
			var ed2 = edits1(ed1, true);
			
			// Sort the edits based on how many different ways they were created.
			var weighted_corrections = ed2;
			
			for (var ed1word in ed1) {
				if (!self.check(ed1word)) {
					continue;
				}

				if (ed1word in weighted_corrections) {
					weighted_corrections[ed1word] += ed1[ed1word];
				}
				else {
					weighted_corrections[ed1word] = ed1[ed1word];
				}
			}
			
			var i, _len;

			var sorted_corrections = [];
			
			for (i in weighted_corrections) {
				if (weighted_corrections.hasOwnProperty(i)) {
					sorted_corrections.push([ i, weighted_corrections[i] ]);
				}
			}

			function sorter(a, b) {
				var a_val = a[1];
				var b_val = b[1];
				if (a_val < b_val) {
					return -1;
				} else if (a_val > b_val) {
					return 1;
				}
				// @todo If a and b are equally weighted, add our own weight based on something like the key locations on this language's default keyboard.
				return b[0].localeCompare(a[0]);
			}
			
			sorted_corrections.sort(sorter).reverse();

			var rv = [];

			var capitalization_scheme = "lowercase";
			
			if (word.toUpperCase() === word) {
				capitalization_scheme = "uppercase";
			}
			else if (word.substr(0, 1).toUpperCase() + word.substr(1).toLowerCase() === word) {
				capitalization_scheme = "capitalized";
			}
			
			var working_limit = limit;

			for (i = 0; i < Math.min(working_limit, sorted_corrections.length); i++) {
				if ("uppercase" === capitalization_scheme) {
					sorted_corrections[i][0] = sorted_corrections[i][0].toUpperCase();
				}
				else if ("capitalized" === capitalization_scheme) {
					sorted_corrections[i][0] = sorted_corrections[i][0].substr(0, 1).toUpperCase() + sorted_corrections[i][0].substr(1);
				}
				
				if (!self.hasFlag(sorted_corrections[i][0], "NOSUGGEST") && rv.indexOf(sorted_corrections[i][0]) == -1) {
					rv.push(sorted_corrections[i][0]);
				}
				else {
					// If one of the corrections is not eligible as a suggestion , make sure we still return the right number of suggestions.
					working_limit++;
				}
			}

			return rv;
		}
		
		this.memoized[word] = {
			'suggestions': correct(word),
			'limit': limit
		};

		return this.memoized[word]['suggestions'];
	}
};
})();

// Support for use as a node.js module.
if (typeof module !== 'undefined') {
	module.exports = Typo;
}

}).call(this)}).call(this,"/node_modules/typo-js")
},{"fs":2}]},{},[1])(1)
});
