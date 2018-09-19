<?php

function awpcp_admin_bar_region_selector() {
    return new AWPCP_AdminBarRegionSelector( awpcp()->settings );
}

class AWPCP_AdminBarRegionSelector {

    private $settings;

    public function __construct( $settings ) {
        $this->settings = $settings;
    }

    public function maybe_add_admin_bar_menu_items( $admin_bar ) {
        if ( $this->settings->get_option( 'enable-region-selector-popup-in-admin-bar' ) ) {
            $this->add_admin_bar_menu_items( $admin_bar );
        }
    }

    private function add_admin_bar_menu_items( $admin_bar ) {
        $admin_bar->add_menu( array(
            'id' => 'awpcp-region-selector',
            'title' => awpcp_region_selector_popup()->render(),
            'parent' => 'top-secondary',
            'meta' => array( 'class' => 'awpcp-admin-bar-region-selector' )
        ) );
    }
}
