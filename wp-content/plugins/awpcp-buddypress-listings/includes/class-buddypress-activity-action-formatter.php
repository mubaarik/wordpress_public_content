<?php

function awpcp_buddypress_activity_action_formatter() {
    return new AWPCP_BuddyPressActivityActionFormatter( awpcp_listings_collection(), awpcp_listing_renderer(), awpcp_buddypress_wrapper() );
}

class AWPCP_BuddyPressActivityActionFormatter {

    private $listings;
    private $listing_renderer;
    private $buddypress;

    public function __construct( $listings, $listing_renderer, $buddypress ) {
        $this->listings = $listings;
        $this->listing_renderer = $listing_renderer;
        $this->buddypress = $buddypress;
    }

    public function format_listing_created_activity_action( $default, $activity ) {
        $action = __( '<user-link> created listing <listing-link>.', 'awpcp-buddypress-listings' );
        return $this->format_listing_activity_action( $activity, $action, $default );
    }

    private function format_listing_activity_action( $activity, $action, $default ) {
        try {
            $listing = $this->listings->get( $activity->item_id );
        } catch ( AWPCP_Exception $e ) {
            return $default;
        }

        $action = str_replace( '<user-link>', $this->buddypress->get_user_link( $activity->user_id ), $action );
        $action = str_replace( '<listing-link>', $this->listing_renderer->get_view_listing_link( $listing ), $action );

        return $action;
    }

    public function format_listing_edited_activity_action( $default, $activity ) {
        $action = __( '<user-link> edited listing <listing-link>.', 'awpcp-buddypress-listings' );
        return $this->format_listing_activity_action( $activity, $action, $default );
    }

    public function format_listing_deleted_activity_action( $default, $activity ) {
        $action = __( '<user-link> deleted listing <listing-link>.', 'awpcp-buddypress-listings' );
        return $this->format_listing_activity_action( $activity, $action, $default );
    }
}
