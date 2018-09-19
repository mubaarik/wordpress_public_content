/*global module: false*/
/*jslint indent: 2*/

module.exports = function(grunt) {
  grunt.awpcp.registerPluginTasks( {
    name: 'RegionControl',
    slug: 'regions',
    path: 'premium-modules/awpcp-region-control/resources',
    concat: {
      src: [
        '<%= path.RegionControl %>/js/legacy.js',
        '<%= path.RegionControl %>/js/sidelist.js',
        '<%= path.RegionControl %>/js/jquery-sidelist.js',
        '<%= path.RegionControl %>/js/components/region-selector-popup/jquery-region-selector-popup.js',
        '<%= path.RegionControl %>/js/frontend.js'
      ],
      dest: '<%= path.RegionControl %>/js/regions.src.js'
    },
    less: {
      files: {
        '<%= path.RegionControl %>/css/region-control.css': '<%= path.RegionControl %>/less/region-control.less'
      }
    }
  } );
}
