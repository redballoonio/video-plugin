module.exports = function(grunt) {
  require('jit-grunt')(grunt);
  grunt.initConfig({
    less: {
      development: {
        options: {
          plugins: [
            require('less-plugin-group-css-media-queries'),
            new(require('less-plugin-autoprefix'))({ browsers: ["last 2 versions"] }),
            new(require('less-plugin-clean-css'))({advanced: true})
          ],
          compress: false,
          cleancss: true,
          yuicompress: true,
          optimization: 2,
          sourceMap: true,
          sourceMapFilename: 'css/video-base.map.css'
        },
        files: {
          "css/video-base.min.css": "css/source/main.less" // destination file and source file
        }
      }
    },
    browserify: {
      js: {
        src: './js/source/main.js',
        dest: './js/video-base.js'
      }
    },
    uglify: {
      my_target: {
        files: {
          './js/video-base.min.js': ['./js/video-base.js']
        }
      }
    },
    watch: {
      styles: {
        files: ['./style/source/**/*.less'],
        tasks: ['less'],
        options: {
          nospawn: true
        }
      },
      js: {
        files: ['./js/source/**/*.js'],
        tasks: ['browserify']
      }
    },
  });
  grunt.loadNpmTasks('grunt-contrib-less');
  grunt.loadNpmTasks('grunt-contrib-watch');
  grunt.loadNpmTasks('grunt-browserify');
  grunt.loadNpmTasks('grunt-contrib-uglify');
  grunt.registerTask('default', ['less', 'browserify', 'uglify']);
};