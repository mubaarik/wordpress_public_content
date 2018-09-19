<?php

function awpcp_buddypress_listings_request() {
    return new AWPCP_BuddyPress_Listings_Request(
        awpcp_buddypress_listings_query(),
        awpcp_buddypress_wrapper()
    );
}

class AWPCP_BuddyPress_Listings_Request {

    private $buddypress_query;
    private $buddypress;

    public function __construct( $buddypress_query, $buddypress ) {
        $this->buddypress_query = $buddypress_query;
        $this->buddypress = $buddypress;
    }

    public function filter_current_listing_id( $listing_id ) {
        if ( ! $this->buddypress_query->is_buddypress_single_listing_page() ) {
            return $listing_id;
        }

        return intval( $this->buddypress->action_variable( 0 ) );
    }
}
