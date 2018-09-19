/* global AWPCP */
AWPCP.run( 'campaign-manager/manage-campaigns-admin-page', [ 'jquery', 'awpcp/settings' ],
function( $, settings ) {
    $( function() {
        var campaignsTable = $( 'table.awpcp-campaigns' );

        campaignsTable.admin( {
            actions: {
                remove: 'awpcp-delete-campaign'
            },
            exclude: ['edit'],
            ajaxurl: settings.get( 'ajaxurl' )
        } );
    } );
} );
