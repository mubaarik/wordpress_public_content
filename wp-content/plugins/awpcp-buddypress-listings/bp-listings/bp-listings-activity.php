<?php

function awpcp_buddypress_register_activity_actions() {
    $formatter = awpcp_buddypress_activity_action_formatter();

    bp_activity_set_action(
        AWPCP_BUDDYPRESS_LISTINGS_MODULE_COMPONENT_ID,
        'listing_created',
        __( 'New Listing', 'awpcp-buddypress-listings' ),
        array( $formatter, 'format_listing_created_activity_action' )
    );

    bp_activity_set_action(
        AWPCP_BUDDYPRESS_LISTINGS_MODULE_COMPONENT_ID,
        'listing_edited',
        __( 'Listing Edited', 'awpcp-buddypress-listings' ),
        array( $formatter, 'format_listing_edited_activity_action' )
    );

    bp_activity_set_action(
        AWPCP_BUDDYPRESS_LISTINGS_MODULE_COMPONENT_ID,
        'listing_deleted',
        __( 'Listing Deleted', 'awpcp-buddypress-listings' ),
        array( $formatter, 'format_listing_deleted_activity_action' )
    );
}
add_action( 'bp_register_activity_actions', 'awpcp_buddypress_register_activity_actions' );
