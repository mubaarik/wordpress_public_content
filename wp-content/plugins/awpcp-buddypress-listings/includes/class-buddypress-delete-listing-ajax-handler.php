<?php

if ( class_exists( 'AWPCP_AjaxHandler' ) ) {

function awpcp_buddypress_delete_listing_ajax_handler() {
    return new AWPCP_BuddyPressDeleteListingAjaxHandler(
        awpcp_listings_collection(),
        awpcp_listing_authorization(),
        awpcp()->settings,
        awpcp_request(),
        awpcp_ajax_response()
    );
}

class AWPCP_BuddyPressDeleteListingAjaxHandler extends AWPCP_AjaxHandler {

    private $listings;
    private $authorization;
    private $settings;
    private $request;

    public function __construct( $listings, $authorization, $settings, $request, $response ) {
        parent::__construct( $response );

        $this->listings = $listings;
        $this->authorization = $authorization;
        $this->request = $request;
        $this->settings = $settings;
    }

    public function ajax() {
        $listing_id = $this->request->post( 'id' );
        $nonce = $this->request->post( 'confirmation' );

        if ( ! wp_verify_nonce( $nonce, 'buddypress-delete-listing-' . $listing_id ) ) {
            return $this->multiple_errors_response( __( "Are you sure you want to do this?", 'awpcp-buddypress-listings' ) );
        }

        try {
            $listing = $this->listings->get( $listing_id );
        } catch ( AWPCP_Exception $e ) {
            return $this->multiple_errors_response( $e->get_errors() );
        }

        if ( $this->authorization->is_current_user_allowed_to_edit_listing( $listing ) ) {
            return $this->try_to_delete_listing( $listing );
        } else {
            return $this->multiple_errors_response( __( "You're not allowed to delete this listing.", 'awpcp-buddypress-listings' ) );
        }
    }

    private function try_to_delete_listing( $listing ) {
        $message = deletead( $listing->ad_id, '', '', true, $errors );

        if ( ! empty( $errors ) ) {
            return $this->multiple_errors_response( $errors );
        } else {
            return $this->success( array( 'message' => $message ) );
        }
    }
}

}
