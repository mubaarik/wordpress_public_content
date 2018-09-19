<?php

/*
Plugin Name: AWPCP BuddyPress Module
Plugin URI: http://www.awpcp.com
Description: Adds support for AWPCP Listings in BuddyPress.
Version: 3.6.10
Author: D. Rodenbaugh
Author URI: http://www.skylineconsult.com
*/

/******************************************************************************/
// This module is not included in the core of Another Wordpress Classifieds
// Plugin.
//
// It is a separate add-on premium module and is not subject to the terms of
// the GPL license  used in the core package.
//
// This module cannot be redistributed or resold in any modified versions of
// the core Another Wordpress Classifieds Plugin product. If you have this
// module in your possession but did not purchase it via awpcp.com or otherwise
// obtain it through awpcp.com please be aware that you have obtained it
// through unauthorized means and cannot be given technical support through
// awpcp.com.
/******************************************************************************/

define( 'AWPCP_BUDDYPRESS_LISTINGS_MODULE', 'Another WordPress Classifieds Plugin - BuddyPress Listings' );
define( 'AWPCP_BUDDYPRESS_LISTINGS_MODULE_BASENAME', basename( dirname( __FILE__ ) ) );
define( 'AWPCP_BUDDYPRESS_LISTINGS_MODULE_DIR', rtrim( plugin_dir_path( __FILE__ ), '/' ) );
define( 'AWPCP_BUDDYPRESS_LISTINGS_MODULE_URL', rtrim( plugin_dir_url( __FILE__ ), '/' ) );
define( 'AWPCP_BUDDYPRESS_LISTINGS_MODULE_DB_VERSION', '3.6.10' );
define( 'AWPCP_BUDDYPRESS_LISTINGS_MODULE_REQUIRED_AWPCP_VERSION', '3.6' );
define( 'AWPCP_BUDDYPRESS_LISTINGS_MODULE_REQUIRED_BP_VERSION', '2.0.1' );
define( 'AWPCP_BUDDYPRESS_LISTINGS_MODULE_COMPONENT_ID', 'listings' );

function awpcp_buddypress_listings_required_awpcp_version_notice() {
    if ( current_user_can( 'activate_plugins' ) ) {
        $module_name = __( 'BuddyPress Module', 'awpcp-buddypress-listings' );
        $required_awpcp_version = AWPCP_BUDDYPRESS_LISTINGS_MODULE_REQUIRED_AWPCP_VERSION;

        $message = __( 'The AWPCP <module-name> requires AWPCP version <awpcp-version> or newer!', 'awpcp-buddypress-listings' );
        $message = str_replace( '<module-name>', '<strong>' . $module_name . '</strong>', $message );
        $message = str_replace( '<awpcp-version>', $required_awpcp_version, $message );
        $message = sprintf( '<strong>%s:</strong> %s', __( 'Error', 'awpcp-buddypress-listings' ), $message );
        echo '<div class="error"><p>' . $message . '</p></div>';
    }
}

if ( ! class_exists( 'AWPCP_ModulesManager' )  ) {

    add_action( 'admin_notices', 'awpcp_buddypress_listings_required_awpcp_version_notice' );

} else {

require( AWPCP_BUDDYPRESS_LISTINGS_MODULE_DIR . '/includes/class-buddypress-listings-loader.php' );
require( AWPCP_BUDDYPRESS_LISTINGS_MODULE_DIR . '/includes/class-buddypress-wrapper.php' );

class AWPCP_BuddyPressListingsModule extends AWPCP_Module {

    private $component_loader;

    public function __construct( $component_loader ) {
        parent::__construct(
            __FILE__,
            'BuddyPress Module',
            'buddypress-listings',
            AWPCP_BUDDYPRESS_LISTINGS_MODULE_DB_VERSION,
            AWPCP_BUDDYPRESS_LISTINGS_MODULE_REQUIRED_AWPCP_VERSION
        );

        $this->component_loader = $component_loader;
    }

    public function required_awpcp_version_notice() {
        return awpcp_buddypress_listings_required_awpcp_version_notice();
    }

    public function load_dependencies() {
        require_once( AWPCP_BUDDYPRESS_LISTINGS_MODULE_DIR . '/includes/class-buddypress-activity-action-formatter.php' );
        require_once( AWPCP_BUDDYPRESS_LISTINGS_MODULE_DIR . '/includes/class-buddypress-activity-logger.php' );
        require_once( AWPCP_BUDDYPRESS_LISTINGS_MODULE_DIR . '/includes/class-buddypress-create-listing-page.php' );
        require_once( AWPCP_BUDDYPRESS_LISTINGS_MODULE_DIR . '/includes/class-buddypress-delete-listing-ajax-handler.php' );
        require_once( AWPCP_BUDDYPRESS_LISTINGS_MODULE_DIR . '/includes/class-buddypress-edit-listing-page.php' );
        require_once( AWPCP_BUDDYPRESS_LISTINGS_MODULE_DIR . '/includes/class-buddypress-listings-page.php' );
        require_once( AWPCP_BUDDYPRESS_LISTINGS_MODULE_DIR . '/includes/class-buddypress-listings-directory-page.php' );
        require_once( AWPCP_BUDDYPRESS_LISTINGS_MODULE_DIR . '/includes/class-buddypress-listings-navigation-builder.php' );
        require_once( AWPCP_BUDDYPRESS_LISTINGS_MODULE_DIR . '/includes/class-buddypress-listings-placeholders.php' );
        require_once( AWPCP_BUDDYPRESS_LISTINGS_MODULE_DIR . '/includes/class-buddypress-listings-query.php' );
        require_once( AWPCP_BUDDYPRESS_LISTINGS_MODULE_DIR . '/includes/class-buddypress-listings-url-filter.php' );
        require_once( AWPCP_BUDDYPRESS_LISTINGS_MODULE_DIR . '/includes/class-buddypress-listings-request.php' );
        require_once( AWPCP_BUDDYPRESS_LISTINGS_MODULE_DIR . '/includes/class-buddypress-listings-settings.php' );
        require_once( AWPCP_BUDDYPRESS_LISTINGS_MODULE_DIR . '/includes/class-buddypress-listings-view.php' );
        require_once( AWPCP_BUDDYPRESS_LISTINGS_MODULE_DIR . '/includes/class-buddypress-view-listing-page.php' );

        require_once( AWPCP_BUDDYPRESS_LISTINGS_MODULE_DIR . '/admin/class-buddypress-page-settings-integration.php' );
    }

    public function load_module() {
        add_action( 'bp_core_loaded', array( $this, 'load_buddypress_component' ), 1 );
    }

    public function load_buddypress_component() {
        $this->component_loader->load( __FILE__ );
        add_action( 'init', array( $this, 'setup_filters_and_actions' ) );
        add_action( 'init', array( $this, 'register_scripts' ) );
    }

    public function setup_filters_and_actions() {
        add_action( 'load-settings_page_bp-components', array( $this, 'enqueue_scripts' ) );

        $placeholders = awpcp_buddypress_listings_placeholders();
        add_filter( 'awpcp-content-placeholders', array( $placeholders, 'register_content_placeholders' ) );

        $url_filter = awpcp_buddypress_listings_url_filter();
        add_filter( 'awpcp-listing-url', array( $url_filter, 'filter_listing_url' ), 10, 2 );
        add_filter( 'awpcp-edit-listing-url', array( $url_filter, 'filter_edit_listing_url' ), 10, 2 );
        add_filter( 'awpcp-delete-listing-url', array( $url_filter, 'filter_delete_listing_url' ), 10, 2 );

        $buddypress_settings = awpcp_buddypress_listings_settings();
        add_action( 'awpcp_register_settings', array( $buddypress_settings, 'register_settings' ) );

        $query = awpcp_buddypress_listings_query();
        add_filter( 'awpcp-is-single-listing-page', array( $query, 'filter_is_single_listing_page' ) );

        $request = awpcp_buddypress_listings_request();
        add_filter( 'awpcp-current-listing-id', array( $request, 'filter_current_listing_id' ) );

        if ( is_admin() ) {
            $settings_integration = awpcp_buddypress_page_settings_integration();
            add_action( 'settings_page_bp-page-settings', array( $settings_integration, 'setup_filters' ), 9 );
            add_action( 'settings_page_bp-page-settings', array( $settings_integration, 'remove_filters' ), 11 );
        }
    }

    public function register_scripts() {
        $version = AWPCP_BUDDYPRESS_LISTINGS_MODULE_DB_VERSION;

        $css = AWPCP_BUDDYPRESS_LISTINGS_MODULE_URL . '/resources/css';
        $js = AWPCP_BUDDYPRESS_LISTINGS_MODULE_URL . '/resources/js';

        wp_register_style( 'awpcp-buddypress-listings', "{$css}/frontend.css", $version, true );
        wp_register_style( 'awpcp-buddypress-listings-admin', "{$css}/admin.css", $version, true );

        wp_register_script( 'awpcp-buddypress-listings', "{$js}/buddypress-listings.min.js", array( 'awpcp' ), $version, true );
    }

    public function enqueue_scripts() {
        wp_enqueue_style( 'awpcp-buddypress-listings-admin' );
    }

    // XXX: perhaps we need to move this to bp-listings/bp-listings-ajax.php for consistency with other
    // BuddyPress plugins/modules.
    protected function ajax_setup() {
        $handler = awpcp_buddypress_delete_listing_ajax_handler();
        add_action( 'wp_ajax_awpcp-buddypress-delete-listing', array( $handler, 'ajax' ) );
    }
}

function awpcp_load_buddypres_listings_module( $manager ) {
    $manager->load( awpcp_buddypress_listings_module() );
}
add_action( 'awpcp-load-modules', 'awpcp_load_buddypres_listings_module' );

function awpcp_buddypress_listings_module() {
    return new AWPCP_BuddyPressListingsModule( awpcp_buddypress_listings_loader() );
}

}
