<?php

function awpcp_buddpress_listings_directory_page() {
    return new AWPCP_BuddyPressListingsDirectoryPage( awpcp_buddypress_enabled_listings_view(), awpcp_buddypress_wrapper(), awpcp_request() );
}

class AWPCP_BuddyPressListingsDirectoryPage extends AWPCP_BuddyPressListingsPage {

    public function setup_theme_compat() {
        bp_update_is_directory( true, AWPCP_BUDDYPRESS_LISTINGS_MODULE_COMPONENT_ID );

        add_filter( 'bp_get_buddypress_template', array( $this, 'template_hierarchy' ) );
        add_action( 'bp_template_include_reset_dummy_post_data', array( $this, 'reset_post_data' ) );
        add_filter( 'bp_replace_the_content', array( $this, 'dispatch' ) );
    }

    public function template_hierarchy( $templates ) {
        $templates = array_merge( array( 'listings/index-directory.php' ), $templates );
        return $templates;
    }

    public function reset_post_data() {
        $this->buddypress->reset_post_data( array(
            'ID'             => 0,
            'post_title'     => $this->buddypress->get_directory_title( AWPCP_BUDDYPRESS_LISTINGS_MODULE_COMPONENT_ID ),
            'post_author'    => 0,
            'post_date'      => 0,
            'post_content'   => '',
            'post_type'      => 'bp_' . AWPCP_BUDDYPRESS_LISTINGS_MODULE_COMPONENT_ID,
            'post_status'    => 'publish',
            'is_page'        => true,
            'comment_status' => 'closed'
        ) );
    }

    public function dispatch() {
        return bp_buffer_template_part( 'listings/index', null, false );
    }
}
