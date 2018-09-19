/* global AWPCP */
AWPCP.run( 'campaign-manager/frontend', [ 'jquery', 'campaign-manager/campaign-loader' ],
function( $, CampaignLoader ) {
    $( function() {
        var placeholders = $( '.awpcp-advertisement-placeholder' ),
            campaignLoader = new CampaignLoader( placeholders );

        campaignLoader.loadCampaigns();
    } );
} );
