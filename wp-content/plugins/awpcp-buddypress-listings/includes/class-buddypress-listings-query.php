<?php

function awpcp_buddypress_listings_query() {
    return new AWPCP_BuddyPress_Listings_Query( awpcp_buddypress_wrapper() );
}

class AWPCP_BuddyPress_Listings_Query {

    private $buddypress;

    public function __construct( $buddypress ) {
        $this->buddypress = $buddypress;
    }

    public function filter_is_single_listing_page( $is_page ) {
        if ( $this->is_buddypress_single_listing_page() ) {
            return true;
        } else {
            return $is_page;
        }
    }

    public function is_buddypress_single_listing_page() {
        if ( ! $this->buddypress->is_listings_component() ) {
            return false;
        }

        if ( ! $this->buddypress->displayed_user_id() ) {
            return false;
        }

        if ( 'view' != $this->buddypress->current_action() ) {
            return false;
        }

        return true;
    }
}
