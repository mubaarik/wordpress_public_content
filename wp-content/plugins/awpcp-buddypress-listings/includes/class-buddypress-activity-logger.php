<?php

function awpcp_buddypress_activity_logger() {
    return new AWPCP_BuddyPressActivityLogger( awpcp_buddypress_wrapper() );
}

class AWPCP_BuddyPressActivityLogger {

    private $buddypress;

    public function __construct( $buddypress ) {
        $this->buddypress = $buddypress;
    }

    public function log_listing_created( $listing ) {
        $listing_name = sprintf( '<strong>%s</strong>', $listing->get_title() );

        $this->buddypress->add_activity( array(
            'action' => sprintf( __( 'Listing %s created.', 'awpcp-buddypress-listings' ), $listing_name ),
            'component' => AWPCP_BUDDYPRESS_LISTINGS_MODULE_COMPONENT_ID,
            'type' => 'listing_created',
            'user_id' => $listing->user_id,
            'item_id' => $listing->ad_id,
        ) );
    }

    public function log_listing_edited( $listing ) {
        $listing_name = sprintf( '<strong>%s</strong>', $listing->get_title() );

        $this->buddypress->add_activity( array(
            'action' => sprintf( __( 'Listing %s edited.', 'awpcp-buddypress-listings' ), $listing_name ),
            'component' => AWPCP_BUDDYPRESS_LISTINGS_MODULE_COMPONENT_ID,
            'type' => 'listing_edited',
            'user_id' => $listing->user_id,
            'item_id' => $listing->ad_id,
        ) );
    }

    public function log_listing_deleted( $listing ) {
        $listing_name = sprintf( '<strong>%s</strong>', $listing->get_title() );

        $this->buddypress->add_activity( array(
            'action' => sprintf( __( 'Listing %s deleted.', 'awpcp-buddypress-listings' ), $listing_name ),
            'component' => AWPCP_BUDDYPRESS_LISTINGS_MODULE_COMPONENT_ID,
            'type' => 'listing_deleted',
            'user_id' => $listing->user_id,
            'item_id' => $listing->ad_id,
        ) );
    }
}
