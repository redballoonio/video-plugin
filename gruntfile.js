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
          "public/css/video-base.min.css": "public/css/source/video-base.less" // destination file and source file
        }
      }
    }
  });
  grunt.loadNpmTasks('grunt-contrib-less');
  grunt.registerTask('default', ['less']);
};