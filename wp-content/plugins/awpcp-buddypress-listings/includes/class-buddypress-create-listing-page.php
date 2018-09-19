<?php

function awpcp_buddypress_create_listing_page() {
    return new AWPCP_BuddyPressCreateListingsPage( awpcp_buddypress_wrapper() );
}

class AWPCP_BuddyPressCreateListingsPage extends AWPCP_Place_Ad_Page {

    private $buddypress;

    protected $show_menu_items = false;

    public function __construct( $buddypress ) {
        parent::__construct( 'awpcp-buddypress-create-listing', __( 'Create Listing', 'awpcp-buddypress-listings' ) );

        $this->buddypress = $buddypress;
    }

    public function enqueue_scripts() {
        wp_enqueue_script( 'awpcp-page-place-ad' );
    }

    public function setup() {
        $this->buddypress->listings()->page_content = parent::dispatch();
    }

    public function dispatch( $default = null ) {
        echo bp_buffer_template_part( 'listings/members/single' );
    }
}
