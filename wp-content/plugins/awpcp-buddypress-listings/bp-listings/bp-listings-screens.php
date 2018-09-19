<?php

function awpcp_buddypress_load_all_listings_page() {
    awpcp_buddypress_load_page( awpcp_buddypress_all_listings_page() );
}

function awpcp_buddypress_load_page( $page ) {
    if ( method_exists( $page, 'enqueue_scripts' ) ) {
        $page->enqueue_scripts();
    }

    $page->setup();
    add_action( 'bp_template_content', array( $page, 'dispatch' ) );

    bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'members/single/plugins' ) );
}

function awpcp_buddypress_load_disabled_listings_page() {
    awpcp_buddypress_load_page( awpcp_buddypress_disabled_listings_page() );
}

function awpcp_buddypress_load_enabled_listings_page() {
    awpcp_buddypress_load_page( awpcp_buddypress_enabled_listings_page() );
}

function awpcp_buddypress_load_create_listing_page() {
    awpcp_buddypress_load_page( awpcp_buddypress_create_listing_page() );
}

function awpcp_buddypress_load_view_listing_page() {
    $view = awpcp_buddypress_view_listing_page();

    add_filter( 'awpcp-ad-details', array( $view, 'filter_awpcp_ad_details' ), 8 );

    awpcp_buddypress_load_page( $view );
}

function awpcp_buddypress_load_edit_listing_page() {
    awpcp_buddypress_load_page( awpcp_buddypress_edit_listing_page() );
}

function awpcp_buddypress_load_listings_directory_page() {
    $buddypress = awpcp_buddypress_wrapper();

    if ( ! $buddypress->displayed_user_id() && $buddypress->is_listings_component() && ! $buddypress->current_action() ) {
        $directory = awpcp_buddpress_listings_directory_page();

        if ( method_exists( $directory, 'enqueue_scripts' ) ) {
            $directory->enqueue_scripts();
        }

        $directory->setup();
        add_action( 'bp_setup_theme_compat', array( $directory, 'setup_theme_compat' ) );

        bp_core_load_template( 'listings/index' );
    }
}
add_action( 'bp_screens', 'awpcp_buddypress_load_listings_directory_page' );
