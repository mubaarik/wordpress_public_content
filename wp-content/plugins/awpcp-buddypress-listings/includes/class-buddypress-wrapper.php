<?php

function awpcp_buddypress_wrapper() {
    if ( ! isset( $GLOBALS['awcpp-buddypress-wrapper'] ) ) {
        $GLOBALS['awcpp-buddypress-wrapper'] = new AWPCP_BuddyPressWrapper();
    }
    return $GLOBALS['awcpp-buddypress-wrapper'];
}

/**
 * Class to wrap many buddypress functions used in BuddyPress Listings.
 *
 * @since 1.0
 */
class AWPCP_BuddyPressWrapper {

    public function buddypress() {
        return buddypress();
    }

    public function version() {
        return defined( 'BP_VERSION' ) ? BP_VERSION : '0';
    }

    public function listings() {
        return $this->buddypress()->{AWPCP_BUDDYPRESS_LISTINGS_MODULE_COMPONENT_ID};
    }

    public function is_listings_component() {
        return bp_is_current_component( AWPCP_BUDDYPRESS_LISTINGS_MODULE_COMPONENT_ID );
    }

    public function displayed_user_id() {
        return bp_displayed_user_id();
    }

    public function displayed_user_domain() {
        return bp_displayed_user_domain();
    }

    public function loggedin_user_id() {
        return bp_loggedin_user_id();
    }

    public function loggedin_user_domain() {
        return bp_loggedin_user_domain();
    }

    public function get_user_domain( $user_id ) {
        return bp_core_get_user_domain( $user_id );
    }

    public function get_user_link( $user_id ) {
        return bp_core_get_userlink( $user_id );
    }

    public function get_directory_title( $component_id ) {
        return bp_get_directory_title( $component_id );
    }

    public function is_current_component( $component_slug ) {
        return bp_is_current_component( $component_slug );
    }

    public function current_action() {
        return bp_current_action();
    }

    public function action_variable( $index ) {
        return bp_action_variable( $index );
    }

    public function reset_post_data( $post_data ) {
        return bp_theme_compat_reset_post( $post_data );
    }

    public function add_activity( $args = array() ) {
        return bp_activity_add( $args );
    }

    public function should_use_theme_compat_with_current_theme() {
        return bp_detect_theme_compat_with_current_theme();
    }
}
