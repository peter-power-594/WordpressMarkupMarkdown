module.exports = function(grunt) {

	grunt.initConfig({
		'dart-sass': {
			options: {
				style: 'compressed',
				// sourcemap: 'none'
				noSourceMap: true
			},
			dist: {
				files: {
					'assets/easy-markdown-editor/dist/easymde.min.css': 'AA_src/easy-markdown-editor/css/easymde.scss',
					'assets/markup-markdown/css/wordpress_richedit-easymde.min.css': 'AA_src/markup-markdown/css/wordpress_richedit-easymde.scss',
					'MarkupMarkdown/Addons/Unsupported/AdvancedCustomField/field.min.css': 'AA_src/advanced-custom-field/css/field.scss'
				}
			}
		},
		jshint: {
			all: [
				'AA_src/custom-codemirror-spell-chekcher/js/spell-checker.js',
				'AA_src/markup-markdown/js/wordpress_richedit-easymde.js',
				'AA_src/markup-markdown/js/wordpress_richedit-media.js',
				'AA_src/markup-markdown/js/wordpress_richedit-preview.js',
				'AA_src/markup-markdown/js/wordpress_richedit-spellchecker.js'
			]
		},
		browserify: {
			options: {
				//ignore: [ 'typo-js' ],
				browserifyOptions: {
					standalone: 'CodeMirrorSpellChecker'
				}
			},
			build: {
				files: {
					'assets/custom-codemirror-spell-checker/dist/spell-checker.debug.js': 'AA_src/custom-codemirror-spell-checker/js/spell-checker.js'
				}
			}
		},
		copy: {
			builder_easymde: {
				src: 'AA_src/markup-markdown/js/wordpress_richedit-easymde.js',
				dest: 'assets/markup-markdown/js/wordpress_richedit-easymde.debug.js'
			},
			builder_media: {
				src: 'AA_src/markup-markdown/js/wordpress_richedit-media.js',
				dest: 'assets/markup-markdown/js/wordpress_richedit-media.debug.js',
			},
			builder_preview: {
				src: 'AA_src/markup-markdown/js/wordpress_richedit-preview.js',
				dest: 'assets/markup-markdown/js/wordpress_richedit-preview.debug.js'
			},
			builder_spellchecker: {
				src: 'AA_src/markup-markdown/js/wordpress_richedit-spellchecker.js',
				dest: 'assets/markup-markdown/js/wordpress_richedit-spellchecker.debug.js'
			}
		},
		uglify: {
			options: {
				output: {
					comments: 'some'
				}
			},
			build: {
				files: {
					'assets/markup-markdown/js/wordpress_richedit-easymde.min.js': 'assets/markup-markdown/js/wordpress_richedit-easymde.debug.js',
					'assets/markup-markdown/js/wordpress_richedit-media.min.js': 'assets/markup-markdown/js/wordpress_richedit-media.debug.js',
					'assets/markup-markdown/js/wordpress_richedit-preview.min.js': 'assets/markup-markdown/js/wordpress_richedit-preview.debug.js',
					'assets/markup-markdown/js/wordpress_richedit-spellchecker.min.js': 'assets/markup-markdown/js/wordpress_richedit-spellchecker.debug.js',
					'assets/custom-codemirror-spell-checker/dist/spell-checker.min.js': 'assets/custom-codemirror-spell-checker/dist/spell-checker.debug.js'
				}
			}
		},
		concat: {
			dist: {
				src: [
					'assets/easy-markdown-editor/dist/easymde.min.js',
					'assets/highlightjs/lib/highlightjs.min.js',
					'assets/jquery-waypoints/lib/jquery.waypoints.min.js',
					'assets/jquery-waypoints/lib/shortcuts/sticky.min.js',
					'assets/custom-codemirror-spell-checker/dist/spell-checker.min.js',
					'assets/markup-markdown/js/wordpress_richedit-spellchecker.min.js',
					'assets/markup-markdown/js/wordpress_richedit-preview.min.js',
					'assets/markup-markdown/js/wordpress_richedit-media.min.js',
					'assets/markup-markdown/js/wordpress_richedit-easymde.min.js'
				],
				dest: 'assets/markup-markdown/js/builder.min.js'
			}
		},
		watch: {
			mysass: {
				files: [
					'AA_src/**/*.scss'
				],
				tasks: [ 'dart-sass' ]
			},
			myjs: {
				files: [
					'AA_src/**/*.js'
				],
				tasks: [ 'jshint', 'browserify', 'copy', 'uglify', 'concat' ]
			}
		},
		concurrent: {
			target: {
				tasks: [ 'watch:mysass', 'watch:myjs' ],
				options: {
					logConcurrentOutput: true
				}
			}
		}
	});

	grunt.loadNpmTasks( 'grunt-concurrent' );
	grunt.loadNpmTasks( 'grunt-browserify' );
	grunt.loadNpmTasks( 'grunt-dart-sass' );
	grunt.loadNpmTasks( 'grunt-contrib-jshint' );
	grunt.loadNpmTasks( 'grunt-contrib-uglify' );
	grunt.loadNpmTasks( 'grunt-contrib-concat' );
	grunt.loadNpmTasks( 'grunt-contrib-copy' );
	grunt.loadNpmTasks( 'grunt-contrib-watch' );

	grunt.registerTask( 'default', [ 'concurrent:target' ] );

};
