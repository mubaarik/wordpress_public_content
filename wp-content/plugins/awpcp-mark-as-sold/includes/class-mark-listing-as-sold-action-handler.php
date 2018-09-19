<?php

function mark_listing_as_sold_action_handler() {
    return new AWPCP_MarkListingAsSoldActionHandler( awpcp_listings_metadata() );
}

class AWPCP_MarkListingAsSoldActionHandler {

    private $metadata;

    public function __construct( $metadata ) {
        $this->metadata = $metadata;
    }

    public function do_action( $output, $listing ) {
        if ( $this->metadata->set( $listing->ad_id, 'is-sold', true ) ) {
            $this->metadata->set( $listing->ad_id, 'sold-at', current_time( 'mysql' ) );
            awpcp_flash( __( 'The listing was successfully marked as sold.', 'awpcp-mark-as-sold' ) );
        } else {
            awpcp_flash( __( 'There was an error trying to mark this listing as sold.', 'awpcp-mark-as-sold' ), 'error' );
        }

        return $this->redirect();
    }

    protected function redirect() {
        return array( 'redirect' => 'details' );
    }
}
