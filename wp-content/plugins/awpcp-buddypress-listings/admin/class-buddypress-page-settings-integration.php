<?php

function awpcp_buddypress_page_settings_integration() {
    return new AWPCP_BuddyPress_Page_Settings_Integration();
}

class AWPCP_BuddyPress_Page_Settings_Integration {

    public function setup_filters() {
        add_filter( 'get_pages', array( $this, 'filter_pages' ), 10, 2 );
    }

    public function remove_filters() {
        remove_filter( 'get_pages', array( $this, 'filter_pages' ), 10, 2 );
    }

    public function filter_pages( $pages, $r ) {
        if ( ! isset( $r['name'] ) ) {
            return $pages;
        }

        if ( ! string_starts_with( $r['name'], 'bp_pages[' ) ) {
            return $pages;
        }

        $plugin_pages = awpcp_get_plugin_pages_ids();
        $other_pages = array();

        foreach ( $pages as $page ) {
            if ( ! in_array( $page->ID, $plugin_pages, true ) ) {
                $other_pages[] = $page;
            }
        }

        return $other_pages;
    }
}
