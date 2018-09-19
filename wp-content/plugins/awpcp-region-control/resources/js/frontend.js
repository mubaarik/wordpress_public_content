/*global AWPCP*/

AWPCP.run('regions/frontend', [
    'jquery',
    'awpcp/collapsible',
    'regions/jquery-sidelist',
    'regions/jquery-region-selector-popup'
],
function($) {
    $(function() {
        // Region Selector
        $('.awpcp-region-control-selector').collapsible();
        $( '.awpcp-region-selector-popup' ).regionSelectorPopup();

        // Region Sidelist
        $('.awpcp-regions-sidelist').sidelist();
    });
});
