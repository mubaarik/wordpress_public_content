<?php

function awpcp_undo_mark_listing_as_sold_action() {
    return new AWPCP_UndoMarkListingAsSoldAction( awpcp_listings_metadata() );
}

class AWPCP_UndoMarkListingAsSoldAction extends AWPCP_ListingAction {

    private $metadata;

    public function __construct( $metadata ) {
        $this->metadata = $metadata;
    }

    public function is_enabled_for_listing( $listing ) {
        if ( $this->metadata->get( $listing->ad_id, 'is-sold' ) ) {
            return true;
        } else {
            return false;
        }
    }

    public function get_name() {
        return __( 'Undo Mark as Sold', 'awpcp-mark-as-sold' );
    }

    public function get_slug() {
        return 'undo-mark-listing-as-sold';
    }

    public function get_description() {
        return __( 'You can use this button to remove the sold mark from this listing.', 'awpcp-mark-as-sold' );
    }

    public function get_confirmation_message() {
        return _x( 'Are you sure?', 'undo mark listing as sold form in frontend edit ad screen', 'awpcp-mark-as-sold' );
    }
}
