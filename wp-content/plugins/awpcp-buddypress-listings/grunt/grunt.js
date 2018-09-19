/*global module: false*/
/*jslint indent: 2*/

module.exports = function(grunt) {
  grunt.awpcp.registerPluginTasks( {
    name: 'BuddyPressListings',
    slug: 'buddypress-listings',
    path: 'premium-modules/awpcp-buddypress-listings/resources',
    concat: {
      src: [
        '<%= path.BuddyPressListings %>/js/frontend.js'
      ],
      dest: '<%= path.BuddyPressListings %>/js/buddypress-listings.src.js'
    },
    less: {
      files: {
        '<%= path.BuddyPressListings %>/css/frontend.css': '<%= path.BuddyPressListings %>/less/frontend.less',
        '<%= path.BuddyPressListings %>/css/admin.css': '<%= path.BuddyPressListings %>/less/admin.less'
      }
    }
  } );
}
