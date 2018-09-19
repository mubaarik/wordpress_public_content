<?php

function awpcp_mark_listing_as_sold_action() {
    return new AWPCP_MarkListingAsSoldAction( awpcp_listings_metadata() );
}

class AWPCP_MarkListingAsSoldAction extends AWPCP_ListingAction {

    private $metadata;

    public function __construct( $metadata ) {
        $this->metadata = $metadata;
    }

    public function is_enabled_for_listing( $listing ) {
        if ( $this->metadata->get( $listing->ad_id, 'is-sold' ) ) {
            return false;
        } else {
            return true;
        }
    }

    public function get_name() {
        return __( 'Mark as Sold', 'awpcp-mark-as-sold' );
    }

    public function get_slug() {
        return 'mark-listing-as-sold';
    }

    public function get_description() {
        return __( 'You can use this button to mark this listing as sold.', 'awpcp-mark-as-sold' );
    }

    public function get_confirmation_message() {
        return _x( 'Are you sure?', 'mark listing as sold form in frontend edit ad screen', 'awpcp-mark-as-sold' );
    }
}
