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
          sourceMapFilename: 'public/css/video-base.map.css'
        },
        files: {
          "public/css/video-base.min.css": "public/css/source/main.less" // destination file and source file
        }
      }
    },
    browserify: {
      js: {
        src: './public/js/source/main.js',
        dest: './public/js/video-base.js'
      }
    },
    uglify: {
      my_target: {
        files: {
          './public/js/video-base.min.js': ['./public/js/video-base.js']
        }
      }
    },
    watch: {
      styles: {
        files: ['./public/style/source/**/*.less'],
        tasks: ['less'],
        options: {
          nospawn: true
        }
      },
      js: {
        files: ['./public/js/source/**/*.js'],
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