<?php

function awpcp_buddypress_edit_listing_page() {
    return new AWPCP_BuddyPressEditListingsPage( awpcp_listings_collection(), awpcp_buddypress_wrapper() );
}

class AWPCP_BuddyPressEditListingsPage extends AWPCP_EditAdPage {

    private $listings;
    private $buddypress;

    protected $show_menu_items = false;

    public function __construct( $listings, $buddypress ) {
        parent::__construct( 'awpcp-buddypress-edit-listing', __( 'Edit Listing', 'awpcp-buddypress-listings' ) );

        $this->listings = $listings;
        $this->buddypress = $buddypress;
    }

    public function enqueue_scripts() {
        wp_enqueue_script( 'awpcp-page-place-ad' );
    }

    public function setup() {
        $listing_id = $this->buddypress->action_variable( 0 );

        try {
            $this->ad = $this->listings->get( $listing_id );
            $page_content = parent::dispatch( 'details' );
        } catch ( AWPCP_Exception $e ) {
            $message = __( "The specified listing doesn't exist.", 'awpcp-buddypress-listings' );
            $page_content = $this->render( 'content', awpcp_print_error( $message ) );
        }

        $this->buddypress->listings()->page_content = $page_content;
    }

    public function dispatch( $default = null ) {
        echo bp_buffer_template_part( 'listings/members/single' );
    }

    protected function _dispatch( $default = null ) {
        return $this->handle_request( $default );
    }

    public function enter_email_and_key_step( $show_errors = true ) {
        $messages = array_map( 'awpcp_print_message', $this->messages );
        return $this->render( 'content', implode( '', $messages ) );
    }
}
