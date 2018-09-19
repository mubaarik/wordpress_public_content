/*global AWPCP*/
AWPCP.run( 'regions/jquery-region-selector-popup', [ 'jquery' ],
function( $ ) {
    $.fn.regionSelectorPopup = function() {
        return $(this).each( function() {
            var popup = $(this);

            popup.find( '.awpcp-region-selector-popup-current-location' ).delegate( 'a', 'click', function( event ) {
                event.preventDefault();
                popup.find( '.awpcp-region-selector-popup-selector-container' ).toggleClass( 'is-hidden' );
            } );
        } );
    };
} );
