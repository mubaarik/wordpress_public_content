<?php

/*
Plugin Name: AWPCP Mark as Sold Module
Plugin URI: http://www.awpcp.com
Description: Allow listings to be marked as sold.
Version: 3.6.2
Author: D. Rodenbaugh
Author URI: http://www.skylineconsult.com
*/

/******************************************************************************/
// This module is not included in the core of Another Wordpress Classifieds
// Plugin.
//
// It is a separate add-on premium module and is not subject to the terms of
// the GPL license used in the core package.
//
// This module cannot be redistributed or resold in any modified versions of
// the core Another Wordpress Classifieds Plugin product. If you have this
// module in your possession but did not purchase it via awpcp.com or otherwise
// obtain it through awpcp.com please be aware that you have obtained it
// through unauthorized means and cannot be given technical support through
// awpcp.com.
/******************************************************************************/

define( 'AWPCP_MARK_AS_SOLD_MODULE', 'Another WordPress Classifieds Plugin - Mark as Sold' );
define( 'AWPCP_MARK_AS_SOLD_MODULE_BASENAME', basename( dirname( __FILE__ ) ) );
define( 'AWPCP_MARK_AS_SOLD_MODULE_DIR', rtrim( plugin_dir_path( __FILE__ ), '/' ) );
define( 'AWPCP_MARK_AS_SOLD_MODULE_URL', rtrim( plugin_dir_url( __FILE__ ), '/' ) );
define( 'AWPCP_MARK_AS_SOLD_MODULE_DB_VERSION', '3.6.2' );
define( 'AWPCP_MARK_AS_SOLD_MODULE_REQUIRED_AWPCP_VERSION', '3.6' );

function awpcp_mark_as_sold_required_awpcp_version_notice() {
    if ( current_user_can( 'activate_plugins' ) ) {
        $module_name = __( 'Mark as Sold Module', 'awpcp-mark-as-sold' );
        $required_awpcp_version = AWPCP_MARK_AS_SOLD_MODULE_REQUIRED_AWPCP_VERSION;

        $message = __( 'The AWPCP <module-name> requires AWPCP version <awpcp-version> or newer!', 'awpcp-mark-as-sold' );
        $message = str_replace( '<module-name>', '<strong>' . $module_name . '</strong>', $message );
        $message = str_replace( '<awpcp-version>', $required_awpcp_version, $message );
        $message = sprintf( '<strong>%s:</strong> %s', __( 'Error', 'awpcp-mark-as-sold' ), $message );
        echo '<div class="error"><p>' . $message . '</p></div>';
    }
}

if ( ! class_exists( 'AWPCP_ModulesManager' )  ) {

    add_action( 'admin_notices', 'awpcp_mark_as_sold_required_awpcp_version_notice' );

} else {

class AWPCP_MarkAsSoldModule extends AWPCP_Module {

    public function __construct() {
        parent::__construct(
            __FILE__,
            'Mark as Sold Module',
            'mark-as-sold',
            AWPCP_MARK_AS_SOLD_MODULE_DB_VERSION,
            AWPCP_MARK_AS_SOLD_MODULE_REQUIRED_AWPCP_VERSION
        );
    }

    public function required_awpcp_version_notice() {
        return awpcp_mark_as_sold_required_awpcp_version_notice();
    }

    public function load_dependencies() {
        require_once( AWPCP_MARK_AS_SOLD_MODULE_DIR . '/includes/class-mark-as-sold-listing-image-filter.php' );
        require_once( AWPCP_MARK_AS_SOLD_MODULE_DIR . '/includes/class-mark-as-sold-listing-title-filter.php' );
        require_once( AWPCP_MARK_AS_SOLD_MODULE_DIR . '/includes/class-mark-as-sold-settings.php' );
        require_once( AWPCP_MARK_AS_SOLD_MODULE_DIR . '/includes/class-mark-listing-as-sold-action-handler.php' );
        require_once( AWPCP_MARK_AS_SOLD_MODULE_DIR . '/includes/class-mark-listing-as-sold-admin-action-handler.php' );
        require_once( AWPCP_MARK_AS_SOLD_MODULE_DIR . '/includes/class-mark-listing-as-sold-action.php' );
        require_once( AWPCP_MARK_AS_SOLD_MODULE_DIR . '/includes/class-remove-sold-listings-cron-job.php' );
        require_once( AWPCP_MARK_AS_SOLD_MODULE_DIR . '/includes/class-undo-mark-listing-as-sold-action-handler.php' );
        require_once( AWPCP_MARK_AS_SOLD_MODULE_DIR . '/includes/class-undo-mark-listing-as-sold-admin-action-handler.php' );
        require_once( AWPCP_MARK_AS_SOLD_MODULE_DIR . '/includes/class-undo-mark-listing-as-sold-action.php' );
    }

    public function module_setup() {
        parent::module_setup();

        $cron_job = awpcp_remove_sold_listings_cron_job();
        add_action( 'awpcp-remove-sold-listings-cron-job', array( $cron_job, 'run' ) );

        $settings_handler = awpcp_mark_as_sold_settings();
        add_action( 'awpcp_register_settings', array( $settings_handler, 'register_settings' ) );

        $image_filter = awpcp_mark_as_sold_listing_image_filter();
        add_filter( 'awpcp-image-placeholders', array( $image_filter, 'filter_image_placeholders' ), 10, 2 );

        $title_filter = $this->get_title_filter();
        add_filter( 'awpcp_title_link_placeholder', array( $title_filter, 'filter_title_link_placeholder' ), 10, 4 );

        add_filter( 'awpcp-listings-widget-listing-thumbnail', array( $image_filter, 'filter_listing_thumbnail_in_widget' ), 10, 2 );
    }

    protected function frontend_setup() {
        add_action( 'awpcp-listing-actions', array( $this, 'register_listing_actions' ), 10, 2 );
        $this->setup_custom_listing_actions_handlers();
    }

    public function register_listing_actions( $actions, $listing ) {
        $this->maybe_add_listing_action( $actions, $listing, awpcp_mark_listing_as_sold_action() );
        $this->maybe_add_listing_action( $actions, $listing, awpcp_undo_mark_listing_as_sold_action() );

        return $actions;
    }

    private function maybe_add_listing_action( &$actions, $listing, $action ) {
        if ( $action->is_enabled_for_listing( $listing ) ) {
            $actions[ $action->get_slug() ] = $action;
        }
    }

    public function setup_custom_listing_actions_handlers() {
        $handler = mark_listing_as_sold_action_handler();
        add_filter( 'awpcp-custom-listing-action-mark-listing-as-sold', array( $handler, 'do_action' ), 10, 2 );

        $handler = undo_mark_listing_as_sold_action_handler();
        add_filter( 'awpcp-custom-listing-action-undo-mark-listing-as-sold', array( $handler, 'do_action' ), 10, 2 );
    }

    protected function admin_setup() {
        add_action( 'awpcp-admin-listings-table-actions', array( $this, 'register_admin_listings_table_actions' ), 10, 3 );
        $this->setup_custom_admin_listings_table_actions_handlers();
    }

    public function register_admin_listings_table_actions( $actions, $listing, $page ) {
        $this->metadata = awpcp_listings_metadata();

        if ( $this->metadata->get( $listing->ad_id, 'is-sold' ) ) {
            $actions['undo-mark-listing-as-sold'] = array(
                __( 'Undo Mark as Sold', 'awpcp-mark-as-sold' ),
                $page->url( array( 'action' => 'undo-mark-listing-as-sold', 'id' => $listing->ad_id ) ),
            );
        } else {
            $actions['mark-listing-as-sold'] = array(
                __( 'Mark as Sold', 'awpcp-mark-as-sold' ),
                $page->url( array( 'action' => 'mark-listing-as-sold', 'id' => $listing->ad_id ) ),
            );
        }

        return $actions;
    }

    private function setup_custom_admin_listings_table_actions_handlers() {
        $handler = mark_listing_as_sold_admin_action_handler();
        add_filter( 'awpcp-custom-admin-listings-table-action-mark-listing-as-sold', array( $handler, 'do_action' ), 10, 2 );

        $handler = undo_mark_listing_as_sold_admin_action_handler();
        add_filter( 'awpcp-custom-admin-listings-table-action-undo-mark-listing-as-sold', array( $handler, 'do_action' ), 10, 2 );
    }

    public function setup_cron_jobs() {
        if ( ! wp_next_scheduled( 'awpcp-remove-sold-listings-cron-job' ) ) {
            wp_schedule_event( strtotime( 'today', current_time( 'timestamp' ) ), 'daily', 'awpcp-remove-sold-listings-cron-job' );
        }
    }

    public function remove_cron_jobs() {
        wp_clear_scheduled_hook( 'awpcp-remove-sold-listings-cron-job' );
    }

    public function get_title_filter() {
        static $instance = null;

        if ( is_null( $instance ) ) {
            $instance = new AWPCP_MarkAsSoldListingTitleFilter( awpcp_listings_metadata() );
        }

        return $instance;
    }
}

function awpcp_mark_as_sold_module() {
    return new AWPCP_MarkAsSoldModule( /*awpcp_mark_as_sold_module_installer()*/ );
}

function awpcp_activate_mark_as_sold_module() {
    $module = awpcp_mark_as_sold_module();

    $module->install_or_upgrade();
    $module->setup_cron_jobs();
}

if ( function_exists( 'awpcp_register_activation_hook' ) ) {
    awpcp_register_activation_hook( __FILE__, 'awpcp_activate_mark_as_sold_module' );
}

function awpcp_deactivate_mark_as_sold_module() {
    awpcp_mark_as_sold_module()->remove_cron_jobs();
}

if ( function_exists( 'awpcp_register_deactivation_hook' ) ) {
    awpcp_register_deactivation_hook( __FILE__, 'awpcp_deactivate_mark_as_sold_module' );
}

function awpcp_load_mark_as_sold_module( $manager ) {
    $manager->load( awpcp_mark_as_sold_module() );
}
add_action( 'awpcp-load-modules', 'awpcp_load_mark_as_sold_module' );

}
