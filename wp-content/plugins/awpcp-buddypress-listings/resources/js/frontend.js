/*global AWPCP*/
AWPCP.run( 'buddypress-listings/frontend', ['jquery', 'awpcp/settings'],
function( $, settings ) {
    $( function() {
        var container = $('#awpcp-buddypress-listings-list');

        container.on( 'click', '.awpcp-buddypress-listings-actions .delete-listing', function( e ) {
            e.preventDefault();
            disableListing( container, $( this ) );
        } );
    } );

    function disableListing( container, link ) {
        var listingId = link.closest( 'li' ).attr( 'data-id' ),
            nonce = link.attr( 'data-nonce' ),

            prompt = container.siblings( '.awpcp-buddypress-delete-prompt' ).clone(),
            listing = container.find( '#listing-' + listingId );

        listing.append(prompt);
        listing.find( '.awpcp-buddypress-listings-actions' ).hide();
        prompt.show();

        prompt.on( 'click', '.delete-listing', function() {
            prompt.find( '.spinner' ).addClass( 'spinner-enabled' );
            $.post( settings.get( 'ajaxurl' ), {
                action: 'awpcp-buddypress-delete-listing',
                confirmation: nonce,
                id: listingId
            }, function( response ) {
                if ( response.status === 'ok' ) {
                    listing.slideUp( function() { listing.remove(); } );
                } else if ( response.errors ) {
                    prompt.find( '.spinner' ).removeClass( 'spinner-enabled' );
                    prompt.append( $( '<div class="error">' + response.errors.join( '<br>' ) + '</div>' ) );
                }
            } );
        } );

        prompt.on( 'click', '.cancel', function() {
            prompt.remove();
            listing.find( '.awpcp-buddypress-listings-actions' ).show();
        } );
    }
});
