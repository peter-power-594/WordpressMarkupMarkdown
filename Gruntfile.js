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
					'assets/markup-markdown/css/wordpress_richedit-easymde.min.css': 'AA_src/markup-markdown/css/wordpress_richedit-easymde.scss'
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
		uglify: {
			options: {
				output: {
					comments: 'some'
				}
			},
			build: {
				files: {
					'assets/markup-markdown/js/wordpress_richedit-easymde.min.js': 'AA_src/markup-markdown/js/wordpress_richedit-easymde.js',
					'assets/markup-markdown/js/wordpress_richedit-media.min.js': 'AA_src/markup-markdown/js/wordpress_richedit-media.js',
					'assets/markup-markdown/js/wordpress_richedit-preview.min.js': 'AA_src/markup-markdown/js/wordpress_richedit-preview.js',
					'assets/markup-markdown/js/wordpress_richedit-spellchecker.min.js': 'AA_src/markup-markdown/js/wordpress_richedit-spellchecker.js',
					'assets/custom-codemirror-spell-checker/dist/spell-checker.min.js': 'assets/custom-codemirror-spell-checker/dist/spell-checker.js'
				}
			}
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
					'assets/custom-codemirror-spell-checker/dist/spell-checker.js': 'AA_src/custom-codemirror-spell-checker/js/spell-checker.js'
				}
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
				tasks: [ 'jshint', 'browserify', 'uglify' ]
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
	grunt.loadNpmTasks( 'grunt-contrib-watch' );

	grunt.registerTask( 'default', [ 'concurrent:target' ] );

};
