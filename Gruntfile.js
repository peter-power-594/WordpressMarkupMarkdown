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
					'assets/easy-markdown-editor/dist/easymde.min.css': 'AA_src/easy-markdown-editor/easymde.scss',
				}
			}
		},
		watch: {
			sass: {
				files: [
					'AA_src/**/*.scss'
				],
				tasks: [ 'sass' ]
			}
		},
		concurrent: {
			target: {
				tasks: [ 'watch:sass' ],
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