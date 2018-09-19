<?php

function awpcp_buddypress_view_listing_page() {
    return new AWPCP_BuddyPressViewListingsPage( awpcp_listings_collection(), awpcp_buddypress_wrapper() );
}

class AWPCP_BuddyPressViewListingsPage extends AWPCP_Show_Ad_Page {

    private $listings;
    private $buddypress;

    public function __construct( $listings, $buddypress ) {
        parent::__construct( 'awpcp-buddypress-view-listing', __( 'View Listing', 'awpcp-buddypress-listings' ) );

        $this->listings = $listings;
        $this->buddypress = $buddypress;
    }

    public function setup() {
        $listing_id = $this->buddypress->action_variable( 0 );

        try {
            $this->ad = $this->listings->get( $listing_id );
            $page_content = showad( $listing_id, true );
        } catch ( AWPCP_Exception $e ) {
            $message = __( "The specified listing doesn't exist.", 'awpcp-buddypress-listings' );
            $page_content = awpcp_print_error( $message );
        }

        $this->buddypress->listings()->page_content = $page_content;
    }

    public function dispatch() {
        echo bp_buffer_template_part( 'listings/members/single' );
    }

    public function filter_awpcp_ad_details( $page_content ) {
        add_filter( 'embed_maybe_make_link', array( $this, 'enable_oembed_ad_details' ), 10, 2 );
        return $page_content;
    }

    public function enable_oembed_ad_details( $return, $url ) {
        remove_filter( 'embed_maybe_make_link', array( $this, 'enable_oembed_ad_details' ), 10 );

        $html = wp_oembed_get( $url );
        if ( false === $html ) {
            return $return;
        } else {
            return $html;
        }
    }

}
