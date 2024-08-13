/**
 * codemirror-spell-checker v1.1.20
 * Copyright Next Step Webs, Inc.
 * @link https://github.com/NextStepWebs/codemirror-spell-checker
 * @license MIT
 * Modified by Pierre-Henri Lavigne for the Wordpress Plugin Markup Markdown
 * @link https://github.com/peter-power-594/codemirror-spell-checker
 */

// Use strict mode (https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Strict_mode)
"use strict";


// Requires
var Typo = require("typo-js");

// Create function
function CodeMirrorSpellChecker(options) {
	// Initialize
	options = options || {};


	// Verify
	if(typeof options.codeMirrorInstance !== "function" || typeof options.codeMirrorInstance.defineMode !== "function") {
		console.log("CodeMirror Spell Checker: You must provide an instance of CodeMirror via the option `codeMirrorInstance`");
		return;
	}


	// Because some browsers don't support this functionality yet
	if(!String.prototype.includes) {
		String.prototype.includes = function() {
			"use strict";
			return String.prototype.indexOf.apply(this, arguments) !== -1;
		};
	}


	// Define the new mode
	options.codeMirrorInstance.defineMode("spell-checker", function(config) {
		// Load AFF/DIC data
		var affUrl = [],
			dicUrl = [],
			etrUrl = [],
			shortLang = "";
		if(undefined === options.language) {
			// Disable spell check by default
		} else if("string" === typeof options.language) {
			// Disable predefined dictionnaries
		} else if(options.language.aff && options.language.dic) {
			affUrl.push(options.language.aff);
			dicUrl.push(options.language.dic);
			etrUrl.push(options.language.etr || '');
			shortLang = options.language.code.replace(/_.*/, "");
			if(options.language.code) {
				CodeMirrorSpellChecker.langs.push(shortLang);
				CodeMirrorSpellChecker.lang = shortLang;
			} else {
				CodeMirrorSpellChecker.langs.push("def");
				CodeMirrorSpellChecker.lang = "def";
			}
		} else {
			for(var lang in options.language) {
				if(options.language.hasOwnProperty(lang)) {
					shortLang = options.language[lang].code.replace(/_.*/, "");
					if(options.language[lang].aff && options.language[lang].dic) {
						affUrl.push(options.language[lang].aff);
						dicUrl.push(options.language[lang].dic);
						etrUrl.push(options.language[lang].etr || '');
						CodeMirrorSpellChecker.langs.push(shortLang);
						if(!CodeMirrorSpellChecker.lang || !CodeMirrorSpellChecker.lang.length) {
							CodeMirrorSpellChecker.lang = shortLang;
						}
					}
				}
			}
		}

		// Load the dictionnaries data
		if(CodeMirrorSpellChecker.loading < 0) {
			CodeMirrorSpellChecker.loading = 0;
			var myXHR,
				getDicData = function() {
					myXHR = new XMLHttpRequest();
					myXHR.open("GET", dicUrl[CodeMirrorSpellChecker.loading], true);
					myXHR.onload = myCallBack;
					myXHR.send(null);
				},
				getAffData = function() {
					myXHR = new XMLHttpRequest();
					myXHR.open("GET", affUrl[CodeMirrorSpellChecker.loading], true);
					myXHR.onload = myCallBack;
					myXHR.send(null);
				},
				getEtrData = function() {
					if (!etrUrl[CodeMirrorSpellChecker.loading]){
						loadData( false );
					}
					else {
						myXHR = new XMLHttpRequest();
						myXHR.open("GET", etrUrl[CodeMirrorSpellChecker.loading], true);
						myXHR.onload = myCallBack;
						myXHR.send(null);
					}
				},
				myExtra = {},
				myCallBack = function() {
					if(myXHR.readyState === 4 && myXHR.status === 200) {
						loadData( myXHR.responseText );
					} else if(myXHR.readyState === 4 && myXHR.status !== 200) {
						CodeMirrorSpellChecker.num_loaded++;
						if(window.console && window.console.log) {
							window.console.log("CodeMirrorSpellChecker: Error while retrieving dictionaries data");
						}
						setTimeout(function() {
							document.dispatchEvent(new Event("CodeMirrorSpellCheckerReady"));
						}, 1000);
					}
				},
				loadData = function( myData ) {
					CodeMirrorSpellChecker.num_loaded++;
					var currLang = CodeMirrorSpellChecker.langs[CodeMirrorSpellChecker.loading];
					if(CodeMirrorSpellChecker.num_loaded === 1) {
						myExtra = {};
						if ( myData ) {
							myExtra = JSON.parse( myData );
						}
						setTimeout(getDicData, 1000);
					} else if(CodeMirrorSpellChecker.num_loaded === 2) {
						CodeMirrorSpellChecker.dic_data[currLang] = myXHR.responseText;
						setTimeout(getAffData, 1000);
					} else if(CodeMirrorSpellChecker.num_loaded === 3) {
						CodeMirrorSpellChecker.aff_data[currLang] = myXHR.responseText;
						if ( myExtra.extra && myExtra.extra.length ) {
							for ( var e = 0, extras = myExtra.extra; e < extras.length; e++ ) {
								CodeMirrorSpellChecker.dic_data[currLang] += "\n" + extras[ e ];
							}
						}
						CodeMirrorSpellChecker.typo[currLang] = new Typo(currLang,
							CodeMirrorSpellChecker.aff_data[currLang],
							CodeMirrorSpellChecker.dic_data[currLang], {
								platform: "any"
							}
						);
						CodeMirrorSpellChecker.aff_data[currLang] = null;
						CodeMirrorSpellChecker.dic_data[currLang] = null;
						if(CodeMirrorSpellChecker.loading < CodeMirrorSpellChecker.langs.length - 1) {
							CodeMirrorSpellChecker.loading++;
							CodeMirrorSpellChecker.num_loaded = 0;
							setTimeout(getEtrData, 1000);
						} else {
							setTimeout(function() {
								document.dispatchEvent(new Event("CodeMirrorSpellCheckerReady"));
							}, 1000);
						}
					}
				}
			getEtrData(); // Initialize
		}


		// Define what separates a word
		var rx_word = "!\"#$%&()*+,-—./:;<=>?@[\\]^_`’“”'{|}~ ";


		// Create the overlay and such

		var i18n = 0;

		var overlay = {
			token: function(stream) {
				var ch = stream.peek();
				var word = "";

				if(rx_word.includes(ch) || ch === '\uE000' || ch === '\uE001') {
					stream.next();
					return null;
				}

				while((ch = stream.peek()) != null && !rx_word.includes(ch)) {
					word += ch;
					stream.next();
				}

				// HTML <span lang="fr">Mon texte</span> will be parsed in *CodeMirror5* 
				// by order as the following _words_ (ch)  : span lang fr Mon texte span 
				// Warning : "_" is considered as delimiter so  
				// <span lang="en_US">My text</span> will be parsed in *CodeMirror5*
				// by order as the following _words_ (ch) : span lang en US My text span 
				if(word === "span") {
					if(i18n === 3) {
						// span from the </span>, switch back to default language
						CodeMirrorSpellChecker.lang = CodeMirrorSpellChecker.langs[0];
						i18n = 0;
					} else {
						// Beginning of a <span>, standby to look for a lang attribute
						i18n = 1;
					}
				} else if(word === "lang") {
					// **Probably** a lang attribute
					i18n = 2;
				} else if(i18n === 2) {
					i18n = 3;
					// We've got a match. At this point the _word_ value is the lang'sattribute data !
					if(CodeMirrorSpellChecker.typo[word]) {
						// Not 100% safe so a check on an existing dict is still required
						CodeMirrorSpellChecker.lang = word;
					}
				} else if(CodeMirrorSpellChecker.typo[CodeMirrorSpellChecker.lang] && !CodeMirrorSpellChecker.typo[CodeMirrorSpellChecker.lang].check(word)) {
					return "spell-error " + CodeMirrorSpellChecker.lang; // CSS class: cm-spell-error
				}

				return null;
			}
		};

		var mode = options.codeMirrorInstance.getMode(
			config, config.backdrop || "text/plain"
		);

		return options.codeMirrorInstance.overlayMode(mode, overlay, true);
	});
}


// Initialize data globally to reduce memory consumption
CodeMirrorSpellChecker.num_loaded = 0;
CodeMirrorSpellChecker.loading = -1;
CodeMirrorSpellChecker.aff_data = {};
CodeMirrorSpellChecker.dic_data = {};
CodeMirrorSpellChecker.typo = {};
CodeMirrorSpellChecker.langs = [];
CodeMirrorSpellChecker.lang = "";


// Export
module.exports = CodeMirrorSpellChecker;