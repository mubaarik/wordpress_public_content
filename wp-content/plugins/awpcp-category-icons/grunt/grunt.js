/*global module: false*/
/*jslint indent: 2*/

module.exports = function(grunt) {
  grunt.awpcp.registerPluginTasks( {
    name: 'CategoryIcons',
    slug: 'category-icons',
    path: 'premium-modules/awpcp-category-icons/resources',
    concat: {
      files: {
        '<%= path.CategoryIcons %>/js/category-icons-admin.src.js': [
          '<%= path.CategoryIcons %>/js/admin/category-icons-manager.js',
          '<%= path.CategoryIcons %>/js/admin/custom-category-icons-uploader.js',
          '<%= path.CategoryIcons %>/js/admin.js'
        ]
      }
    },
    less: {
      files: {
        '<%= path.CategoryIcons %>/css/category-icons-admin.css': [
          '<%= path.CategoryIcons %>/less/category-icons-admin.less'
        ]
      }
    }
  } );
}
