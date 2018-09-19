/*global module: false*/
/*jslint indent: 2*/

module.exports = function(grunt) {
  grunt.awpcp.registerPluginTasks( {
    name: 'CampaignManager',
    slug: 'campaign-manager',
    path: 'premium-modules/awpcp-campaign-manager/resources',
    concat: {
      files: {
        '<%= path.CampaignManager %>/js/campaign-manager-frontend.src.js': [
          '<%= path.CampaignManager %>/js/campaign-loader.js',
          '<%= path.CampaignManager %>/js/frontend.js',
        ],
        '<%= path.CampaignManager %>/js/campaign-manager-admin.src.js': [
          '<%= path.CampaignManager %>/js/jquery-campaign-advertisement-content-form.js',
          '<%= path.CampaignManager %>/js/manage-campaigns-admin-page.js',
          '<%= path.CampaignManager %>/js/campaign-form.js',
        ]
      }
    },
    less: {
      files: {
        '<%= path.CampaignManager %>/css/admin.css': '<%= path.CampaignManager %>/less/admin.less'
      }
    }
  } );
}
