<?php

/* AWPCP Filters & Actions */

function awpcp_buddypress_enable_activity_logger() {
    $activity_logger = awpcp_buddypress_activity_logger();
    add_action( 'awpcp-place-ad', array( $activity_logger, 'log_listing_created' ) );
    add_action( 'awpcp_edit_ad', array( $activity_logger, 'log_listing_edited' ) );
    add_action( 'awpcp_before_delete_ad', array( $activity_logger, 'log_listing_deleted' ) );
}
add_action( 'bp_register_activity_actions', 'awpcp_buddypress_enable_activity_logger' );

/* BuddyPress Filters & Actions */

function awpcp_buddypress_listings_load_template_filter( $found_template, $templates ) {
    $buddypress = awpcp_buddypress_wrapper();

    if ( $buddypress->should_use_theme_compat_with_current_theme() ) {
        return $found_template;
    }

    if ( ! awpcp_buddypress_wrapper()->is_listings_component() ) {
        return $found_template;
    }

    if ( empty( $found_template ) ) {
        $found_template = bp_locate_template( $templates, false, false );
    }

    return apply_filters( 'awcp_bp_listings_load_template_filter', $found_template );
}
add_action( 'bp_located_template', 'awpcp_buddypress_listings_load_template_filter', 10, 2 );

function awpcp_buddypress_modify_listing_page_title( $title, $original_title, $sep, $seplocation ) {
    $buddypress = awpcp_buddypress_wrapper();

    if ( ! $buddypress->is_listings_component() ) {
        return $title;
    }

    $current_action = $buddypress->current_action();
    if ( ! in_array( $current_action, array( 'view', 'edit' ) ) ) {
        return $title;
    }

    $listing_id = $buddypress->action_variable( 0 );

    if ( $listing_id <= 0 ) {
        return $title;
    }

    try {
        $listing = awpcp_listings_collection()->get( $listing_id );
    } catch ( AWPCP_Exception $e ) {
        return $title;
    }

    if ( $current_action == 'view' ) {
        return awpcp_buddypress_modify_view_listing_page_title( $listing, $title, $sep, $seplocation );
    } else if ( $current_action == 'edit' ) {
        return awpcp_buddypress_modify_edit_listing_page_title( $listing, $sep, $seplocation );
    }
}
add_filter( 'bp_modify_page_title', 'awpcp_buddypress_modify_listing_page_title', 10, 4 );

function awpcp_buddypress_modify_view_listing_page_title( $listing, $title, $sep, $seplocation ) {
    $title_builder = awpcp_page_title_builder();
    $title_builder->set_current_listing( $listing );

    return $title_builder->build_title( $title, $sep, $seplocation );
}

function awpcp_buddypress_modify_edit_listing_page_title( $listing, $sep, $seplocation ) {
    $new_title = sprintf( __( 'Edit %s', 'awpcp-buddypress-listings' ), $listing->get_title() );

    if ( $seplocation == 'left' ) {
        return ' ' . $sep . ' ' . $new_title;
    } else {
        return $new_title . ' ' . $sep . ' ';
    }
}

function awpcp_buddypress_activity_filter_options() {
    echo '<option value="listing_created">' . __( 'New Listings', 'awpcp-buddypress-listings' ) . '</option>';
    echo '<option value="listing_edited">' . __( 'Listings Edited', 'awpcp-buddypress-listings' ) . '</option>';
    echo '<option value="listing_deleted">' . __( 'Listings Deleted', 'awpcp-buddypress-listings' ) . '</option>';
}
add_action( 'bp_activity_filter_options', 'awpcp_buddypress_activity_filter_options' );
add_action( 'bp_member_activity_filter_options', 'awpcp_buddypress_activity_filter_options' );
