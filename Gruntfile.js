module.exports = function(grunt) {

	grunt.initConfig({
		sass: {
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
				'AA_src/markup-markdown/js/wordpress_richedit-easymde.js',
				'AA_src/markup-markdown/js/wordpress_richedit-media.js',
				'AA_src/markup-markdown/js/wordpress_richedit-preview.js'
			]
		},
		uglify: {
			options: {
				ouput: {
					comments: 'some'
				}
			},
			build: {
				files: {
					'assets/markup-markdown/js/wordpress_richedit-easymde.min.js': 'AA_src/markup-markdown/js/wordpress_richedit-easymde.js',
					'assets/markup-markdown/js/wordpress_richedit-media.min.js': 'AA_src/markup-markdown/js/wordpress_richedit-media.js',
					'assets/markup-markdown/js/wordpress_richedit-preview.min.js': 'AA_src/markup-markdown/js/wordpress_richedit-preview.js'
				}
			}
		},
		watch: {
			sass: {
				files: [
					'AA_src/**/*.scss'
				],
				tasks: [ 'sass' ]
			},
			js: {
				files: [
					'AA_src/**/*.js'
				],
				tasks: [ 'jshint', 'uglify' ]
			}
		},
		concurrent: {
			target: {
				tasks: [ 'watch:sass', 'watch:js' ],
				options: {
					logConcurrentOutput: true
				}
			}
		}
	});

	grunt.loadNpmTasks( 'grunt-concurrent' );
	grunt.loadNpmTasks( 'grunt-contrib-sass' );
	grunt.loadNpmTasks( 'grunt-contrib-jshint' );
	grunt.loadNpmTasks( 'grunt-contrib-uglify' );
	grunt.loadNpmTasks( 'grunt-contrib-concat' );
	grunt.loadNpmTasks( 'grunt-contrib-watch' );

	grunt.registerTask( 'default', [ 'concurrent:target' ] );

};
