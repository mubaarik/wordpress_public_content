/*global module: false*/
/*jslint indent: 2*/

module.exports = function(grunt) {
  grunt.awpcp.registerPluginTasks( {
    name: 'CommentsRatings',
    slug: 'comments-ratings',
    path: 'premium-modules/awpcp-comments-ratings/resources',
    concat: {
      src: [
        '<%= path.CommentsRatings %>/js/jquery.raty.min.js',
        '<%= path.CommentsRatings %>/js/comments.js',
        '<%= path.CommentsRatings %>/js/ratings.js',
        '<%= path.CommentsRatings %>/js/frontend.js'
      ],
      dest: '<%= path.CommentsRatings %>/js/comments-ratings.src.js'
    },
    less: {
    }
  } );
}
