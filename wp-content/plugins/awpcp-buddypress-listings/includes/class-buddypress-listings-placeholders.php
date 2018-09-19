<?php

function awpcp_buddypress_listings_placeholders() {
    return new AWPCP_BuddyPressListingsPlaceholders(
        awpcp_buddypress_wrapper(),
        awpcp_request()
    );
}

class AWPCP_BuddyPressListingsPlaceholders {

    private $buddypress;
    private $request;

    public function __construct( $buddypress, $request ) {
        $this->buddypress = $buddypress;
        $this->request = $request;
    }

    public function register_content_placeholders( $placeholders ) {
        $placeholders['bp_user_profile_url'] = array( 'callback' => array( $this, 'do_placeholder' ) );
        $placeholders['bp_user_listings_url'] = array( 'callback' => array( $this, 'do_placeholder' ) );
        $placeholders['bp_username'] = array( 'callback' => array( $this, 'do_placeholder' ) );

        $placeholders['bp_current_user_profile_url'] = array( 'callback' => array( $this, 'do_placeholder' ) );
        $placeholders['bp_current_user_listings_url'] = array( 'callback' => array( $this, 'do_placeholder' ) );
        $placeholders['bp_current_user_username'] = array( 'callback' => array( $this, 'do_placeholder' ) );

        return $placeholders;
    }

    public function do_placeholder( $listing, $placeholder, $context ) {
        $method_name = "do_{$placeholder}_placeholder";

        if ( $listing->user_id && method_exists( $this, $method_name ) ) {
            return call_user_func( array( $this, $method_name ), $listing, $placeholder, $context );
        } else {
            return '';
        }
    }

    public function do_bp_user_profile_url_placeholder( $listing, $placeholder, $context ) {
        return $this->get_user_profile_url( $listing->user_id );
    }

    private function get_user_profile_url( $user_id ) {
        return $this->buddypress->get_user_domain( $user_id );
    }

    public function do_bp_user_listings_url_placeholder( $listing, $placeholder, $context ) {
        return $this->get_user_listings_url( $listing->user_id );
    }

    private function get_user_listings_url( $user_id ) {
        $user_domain = $this->buddypress->get_user_domain( $user_id );
        $component_slug = $this->buddypress->listings()->slug;

        return user_trailingslashit( $user_domain . $component_slug );
    }

    public function do_bp_username_placeholder( $listing, $placeholder, $context ) {
        return $this->get_user_username( get_user_by( 'id', $listing->user_id ) );
    }

    private function get_user_username( $user ) {
        if ( ! function_exists( 'bp_core_get_username' ) ) {
            $username = $user->user_login;
        } else {
            $username = bp_core_get_username( $user->ID, $user->user_nicename, $user->user_login );
        }

        return $username;
    }

    public function do_bp_current_user_profile_url_placeholder() {
        $user = $this->request->get_current_user();
        return $this->get_user_profile_url( $user->ID );
    }

    public function do_bp_current_user_listings_url_placeholder() {
        $user = $this->request->get_current_user();
        return $this->get_user_listings_url( $user->ID );
    }

    public function do_bp_current_user_username_placeholder() {
        return $this->get_user_username( $this->request->get_current_user() );
    }
}
