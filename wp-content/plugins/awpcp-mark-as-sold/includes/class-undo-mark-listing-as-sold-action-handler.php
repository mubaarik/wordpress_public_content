<?php

function undo_mark_listing_as_sold_action_handler() {
    return new AWPCP_UndoMarkListingAsSoldActionHandler( awpcp_listings_metadata() );
}

class AWPCP_UndoMarkListingAsSoldActionHandler {

    private $metadata;

    public function __construct( $metadata ) {
        $this->metadata = $metadata;
    }

    public function do_action( $output, $listing ) {
        if ( $this->metadata->delete( $listing->ad_id, 'is-sold' ) ) {
            $this->metadata->delete( $listing->ad_id, 'sold-at' );
            awpcp_flash( __( 'The listing is no longer marked as sold.', 'awpcp-mark-as-sold' ) );
        } else {
            awpcp_flash( __( 'There was an error trying to undo marking this listing as sold.', 'awpcp-mark-as-sold' ), 'error' );
        }

        return $this->redirect();
    }

    protected function redirect() {
        return array( 'redirect' => 'details' );
    }
}
