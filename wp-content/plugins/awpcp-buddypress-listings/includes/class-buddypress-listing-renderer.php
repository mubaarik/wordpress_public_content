<?php

function awpcp_buddypress_listing_renderer() {
    return new AWPCP_BuddyPressListingRenderer( awpcp_buddypress_wrapper() );
}

class AWPCP_BuddyPressListingRenderer extends AWPCP_ListingRenderer {
    private $buddypress;

    public function __construct( $buddypress ) {
        $this->buddypress = $buddypress;
    }

    public function get_view_listing_url( $listing ) {
        return $this->get_listing_url( $listing, 'view' );
    }

    public function get_edit_listing_url( $listing ) {
        return $this->get_listing_url( $listing, 'edit' );
    }

    public function get_delete_listing_url( $listing ) {
        return $this->get_listing_url( $listing, 'delete' );
    }

    private function get_listing_url( $listing, $action ) {
        $user_domain = $this->buddypress->get_user_domain( $listing->user_id );
        $component_slug = $this->buddypress->listings()->slug;

        return trailingslashit( $user_domain . $component_slug ) . $action . '/' . $listing->ad_id . '/' . sanitize_title( $listing->get_title() ) . '/';
    }
}
