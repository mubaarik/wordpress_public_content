<?php

function awpcp_buddypress_all_listings_page() {
    return new AWPCP_BuddyPressListingsPage( awpcp_buddypress_all_user_listings_view(), awpcp_buddypress_wrapper(), awpcp_request() );
}

function awpcp_buddypress_enabled_listings_page() {
    return new AWPCP_BuddyPressListingsPage( awpcp_buddypress_user_enabled_listings_view(), awpcp_buddypress_wrapper(), awpcp_request() );
}

function awpcp_buddypress_disabled_listings_page() {
    return new AWPCP_BuddyPressListingsPage( awpcp_buddypress_user_disabled_listings_view(), awpcp_buddypress_wrapper(), awpcp_request() );
}

class AWPCP_BuddyPressListingsPage {

    protected $view;
    protected $buddypress;
    protected $request;

    public function __construct( $view, $buddypress, $request ) {
        $this->view = $view;
        $this->buddypress = $buddypress;
        $this->request = $request;
    }

    public function enqueue_scripts() {
        wp_enqueue_style( 'awpcp-buddypress-listings' );
        wp_enqueue_script( 'awpcp-buddypress-listings' );
    }

    public function setup() {
        // necessary to make the view available from the template
        $this->buddypress->listings()->current_view = $this->view;
        $this->prepare_view();
    }

    private function prepare_view() {
        $user_id = $this->buddypress->displayed_user_id();
        $page = $this->request->param( 'apage', 1 );
        $items_per_page = 10;

        $this->view->prepare_items( compact( 'user_id', 'page', 'items_per_page' ) );
    }

    public function dispatch() {
        echo bp_buffer_template_part( 'listings/members/index', null, false );
    }
}
