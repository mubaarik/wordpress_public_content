<?php

if ( class_exists( 'BP_Component' ) ) {

function awpcp_buddypress_listings_component() {
    return new AWPCP_BuddyPressListingsComponent();
}

class AWPCP_BuddyPressListingsComponent extends BP_Component {

    public function __construct() {
        parent::start(
            AWPCP_BUDDYPRESS_LISTINGS_MODULE_COMPONENT_ID,
            __( 'Listings', 'awpcp-buddypress-listings' ),
            AWPCP_BUDDYPRESS_LISTINGS_MODULE_DIR
        );
    }

    public function setup_nav( $main_nav = array(), $sub_nav = array() ) {
        $navigation_builder = awpcp_buddypress_listings_navigation_builder();
        $navigation = $navigation_builder->build_navigation( $this->id, $this->slug );
        parent::setup_nav( $navigation['main_nav'], $navigation['sub_nav'] );
    }

    public function setup_globals( $args = array() ) {
        parent::setup_globals( array(
            'has_directory' => true,
            'directory_title' => __( 'Sitewide Listings', 'awpcp-buddypress-listings' ),
        ) );
    }

    public function includes( $includes = array() ) {
        $includes = array(
            'filters',
            'screens',
            'activity',
        );

        parent::includes( $includes );
    }

    public function redirect_canonical( $redirect_url, $requested_url ) {
        if ( ! awpcp_buddypress_wrapper()->is_listings_component() ) {
            return $redirect_url;
        } else {
            return $requested_url;
        }
    }
}

}
