<?php

/*
Plugin Name: AWPCP Campaign Manager
Plugin URI: http://www.awpcp.com
Description: Show advertisements in the classifieds pages.
* Version: 3.6.8
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

define( 'AWPCP_CAMPAIGN_MANAGER_MODULE', 'Another WordPress Classifieds Plugin - Campaign Manager' );
define( 'AWPCP_CAMPAIGN_MANAGER_MODULE_BASENAME', basename( dirname( __FILE__ ) ) );
define( 'AWPCP_CAMPAIGN_MANAGER_MODULE_DIR', rtrim( plugin_dir_path( __FILE__ ), '/' ) );
define( 'AWPCP_CAMPAIGN_MANAGER_MODULE_URL', rtrim( plugin_dir_url( __FILE__ ), '/' ) );
define( 'AWPCP_CAMPAIGN_MANAGER_MODULE_DB_VERSION', '3.6.8' );
define( 'AWPCP_CAMPAIGN_MANAGER_MODULE_REQUIRED_AWPCP_VERSION', '3.6' );

define( 'AWPCP_CAMPAIGN_MANAGER_MODULE_SALES_REPRESENTATIVE_CAPABILITY', 'manage_campaigns' );

global $wpdb;

define( 'AWPCP_TABLE_ADVERTISEMENT_POSITIONS', "{$wpdb->prefix}awpcp_advertisement_positions" );
define( 'AWPCP_TABLE_CAMPAIGNS', "{$wpdb->prefix}awpcp_campaigns" );
define( 'AWPCP_TABLE_CAMPAIGN_ADVERTISEMENT_POSITIONS', "{$wpdb->prefix}awpcp_campaign_positions" );
define( 'AWPCP_TABLE_CAMPAIGN_SECTIONS', "{$wpdb->prefix}awpcp_campaign_sections" );
define( 'AWPCP_TABLE_CAMPAIGN_SECTION_ADVERTISEMENT_POSITIONS', "{$wpdb->prefix}awpcp_campaign_section_positions" );

function awpcp_campaign_manager_required_awpcp_version_notice() {
    if ( current_user_can( 'activate_plugins' ) ) {
        $module_name = __( 'Campaign Manager Module', 'awpcp-campaign-manager' );
        $required_awpcp_version = AWPCP_CAMPAIGN_MANAGER_MODULE_REQUIRED_AWPCP_VERSION;

        $message = __( 'The AWPCP <module-name> requires AWPCP version <awpcp-version> or newer!', 'awpcp-campaign-manager' );
        $message = str_replace( '<module-name>', '<strong>' . $module_name . '</strong>', $message );
        $message = str_replace( '<awpcp-version>', $required_awpcp_version, $message );
        $message = sprintf( '<strong>%s:</strong> %s', __( 'Error', 'awpcp-campaign-manager' ), $message );
        echo '<div class="error"><p>' . $message . '</p></div>';
    }
}

if ( ! class_exists( 'AWPCP_Module' ) ) {

    add_action( 'admin_notices', 'awpcp_campaign_manager_required_awpcp_version_notice' );

} else {

require_once( AWPCP_CAMPAIGN_MANAGER_MODULE_DIR . '/includes/class-add-campaign-section-ajax-handler.php' );
require_once( AWPCP_CAMPAIGN_MANAGER_MODULE_DIR . '/includes/class-advertisement-content-generator.php' );
require_once( AWPCP_CAMPAIGN_MANAGER_MODULE_DIR . '/includes/class-advertisement-placeholder-widget.php' );
require_once( AWPCP_CAMPAIGN_MANAGER_MODULE_DIR . '/includes/class-advertisement-positions-generator.php' );
require_once( AWPCP_CAMPAIGN_MANAGER_MODULE_DIR . '/includes/class-edit-campaign-section-ajax-handler.php' );
require_once( AWPCP_CAMPAIGN_MANAGER_MODULE_DIR . '/includes/class-campaign-section-configuration-options-ajax-handler.php' );
require_once( AWPCP_CAMPAIGN_MANAGER_MODULE_DIR . '/includes/class-campaign-advertisement-positions-collection.php' );
require_once( AWPCP_CAMPAIGN_MANAGER_MODULE_DIR . '/includes/class-campaign-advertisement-positions-saver.php' );
require_once( AWPCP_CAMPAIGN_MANAGER_MODULE_DIR . '/includes/class-campaign-advertisement-position-image-uploader.php' );
require_once( AWPCP_CAMPAIGN_MANAGER_MODULE_DIR . '/includes/class-campaign-advertisement-position-logic-factory.php' );
require_once( AWPCP_CAMPAIGN_MANAGER_MODULE_DIR . '/includes/class-campaign-advertisement-position-logic.php' );
require_once( AWPCP_CAMPAIGN_MANAGER_MODULE_DIR . '/includes/class-campaign-manager-admin-menu-creator.php' );
require_once( AWPCP_CAMPAIGN_MANAGER_MODULE_DIR . '/includes/class-campaign-manager-installer.php' );
require_once( AWPCP_CAMPAIGN_MANAGER_MODULE_DIR . '/includes/class-campaign-manager-resources-manager.php' );
require_once( AWPCP_CAMPAIGN_MANAGER_MODULE_DIR . '/includes/class-campaign-manager-settings.php' );
require_once( AWPCP_CAMPAIGN_MANAGER_MODULE_DIR . '/includes/class-campaign-saver.php' );
require_once( AWPCP_CAMPAIGN_MANAGER_MODULE_DIR . '/includes/class-campaign-sections-collection.php' );
require_once( AWPCP_CAMPAIGN_MANAGER_MODULE_DIR . '/includes/class-campaign-sections-table-factory.php' );
require_once( AWPCP_CAMPAIGN_MANAGER_MODULE_DIR . '/includes/class-campaign-sections-table.php' );
require_once( AWPCP_CAMPAIGN_MANAGER_MODULE_DIR . '/includes/class-campaign-section-advertisement-positions-collection.php' );
require_once( AWPCP_CAMPAIGN_MANAGER_MODULE_DIR . '/includes/class-campaign-section-logic-factory.php' );
require_once( AWPCP_CAMPAIGN_MANAGER_MODULE_DIR . '/includes/class-campaign-section-logic.php' );
require_once( AWPCP_CAMPAIGN_MANAGER_MODULE_DIR . '/includes/class-campaign-section-saver.php' );
require_once( AWPCP_CAMPAIGN_MANAGER_MODULE_DIR . '/includes/class-campaigns-collection.php' );
require_once( AWPCP_CAMPAIGN_MANAGER_MODULE_DIR . '/includes/class-campaigns-table-facory.php' );
require_once( AWPCP_CAMPAIGN_MANAGER_MODULE_DIR . '/includes/class-campaigns-table.php' );
require_once( AWPCP_CAMPAIGN_MANAGER_MODULE_DIR . '/includes/class-create-campaign-admin-page.php' );
require_once( AWPCP_CAMPAIGN_MANAGER_MODULE_DIR . '/includes/class-create-placeholder-campaign-admin-page.php' );
require_once( AWPCP_CAMPAIGN_MANAGER_MODULE_DIR . '/includes/class-delete-campaign-ajax-handler.php' );
require_once( AWPCP_CAMPAIGN_MANAGER_MODULE_DIR . '/includes/class-delete-campaign-section-ajax-handler.php' );
require_once( AWPCP_CAMPAIGN_MANAGER_MODULE_DIR . '/includes/class-delete-campaign-section-service.php' );
require_once( AWPCP_CAMPAIGN_MANAGER_MODULE_DIR . '/includes/class-delete-campaign-service.php' );
require_once( AWPCP_CAMPAIGN_MANAGER_MODULE_DIR . '/includes/class-insert-advertisement-placeholder-service.php' );
require_once( AWPCP_CAMPAIGN_MANAGER_MODULE_DIR . '/includes/class-load-campaigns-ajax-handler.php' );
require_once( AWPCP_CAMPAIGN_MANAGER_MODULE_DIR . '/includes/class-load-advertisement-positions-content-forms-ajax-handler.php' );
require_once( AWPCP_CAMPAIGN_MANAGER_MODULE_DIR . '/includes/class-manage-campaigns-admin-page.php' );
require_once( AWPCP_CAMPAIGN_MANAGER_MODULE_DIR . '/includes/class-manage-advertisement-positions-admin-page.php' );
require_once( AWPCP_CAMPAIGN_MANAGER_MODULE_DIR . '/includes/class-update-advertisement-position-content-ajax-handler.php' );
require_once( AWPCP_CAMPAIGN_MANAGER_MODULE_DIR . '/includes/functions.php' );

class AWPCP_CamapaignManagerModule extends AWPCP_Module {

    private $installer;

    public function __construct( $installer ) {
        parent::__construct( __FILE__,
                            'Campaign Manager Module',
                            'campaign-manager',
                             AWPCP_CAMPAIGN_MANAGER_MODULE_DB_VERSION,
                             AWPCP_CAMPAIGN_MANAGER_MODULE_REQUIRED_AWPCP_VERSION );

        $this->installer = $installer;
    }

    public function get_installed_version() {
        return get_option( 'awpcp-campaign-manager-installed-version' );
    }

    public function required_awpcp_version_notice() {
        return awpcp_campaign_manager_required_awpcp_version_notice();
    }

    public function install_or_upgrade() {
        $this->installer->install_or_upgrade( $this );
    }

    public function load_module() {
        add_action( 'widgets_init', array( $this, 'register_widgets' ) );
    }

    public function module_setup() {
        $this->register_scripts_and_styles();

        $settings = awpcp_campaign_manager_settings();
        add_action( 'awpcp_register_settings', array( $settings, 'register_settings' ) );

        parent::module_setup();
    }

    private function register_scripts_and_styles() {
        $manager = awpcp_campaign_manager_resources_manager();
        $manager->set_version( $this->version );
        $manager->set_base_url( AWPCP_CAMPAIGN_MANAGER_MODULE_URL );
        $manager->register_scripts_and_styles();
    }

    public function register_widgets() {
        register_widget( 'AWPCP_AdvertisementPlaceholderWidget' );
    }

    protected function admin_setup() {
        $menu_creator = awpcp_campaign_manager_admin_menu_creator();
        add_action( 'awpcp_add_menu_page', array( $menu_creator, 'create_menu' ) );
    }

    protected function frontend_setup() {
        $service = awpcp_insert_advertesiment_placeholder_service();

        add_filter( 'awpcp-content-before-listings-page', array( $service, 'insert_top_advertisement_placeholder' ) );
        add_filter( 'awpcp-content-after-listings-page', array( $service, 'insert_bottom_advertisement_placeholder' ) );
        add_filter( 'awpcp-render-listing-item', array( $service, 'insert_middle_advertisement_placeholder' ), 10, 3 );

        add_filter( 'awpcp-content-before-listing-page', array( $service, 'insert_top_advertisement_placeholder' ) );
        add_filter( 'awpcp-content-after-listing-page', array( $service, 'insert_bottom_advertisement_placeholder' ) );
    }

    protected function ajax_setup() {
        $handler = awpcp_delete_campaign_ajax_handler();
        add_action( 'wp_ajax_awpcp-delete-campaign', array( $handler, 'ajax' ) );

        $handler = awpcp_add_campaign_section_ajax_handler();
        add_action( 'wp_ajax_awpcp-add-campaign-section', array( $handler, 'ajax' ) );

        $handler = awpcp_edit_campaign_section_ajax_handler();
        add_action( 'wp_ajax_awpcp-edit-campaign-section', array( $handler, 'ajax' ) );

        $handler = awpcp_delete_campaign_section_ajax_handler();
        add_action( 'wp_ajax_awpcp-delete-campaign-section', array( $handler, 'ajax' ) );

        $handler = awpcp_campaign_section_configuration_options_ajax_handler();
        add_action( 'wp_ajax_awpcp-get-campaign-section-configuration-options', array( $handler, 'ajax' ) );

        $handler = awpcp_update_advertisement_position_content_ajax_handler();
        add_action( 'wp_ajax_update-advertisement-position-content', array( $handler, 'ajax' ) );

        $handler = awpcp_load_advertisement_positions_content_forms_ajax_handler();
        add_action( 'wp_ajax_awpcp-load-advertisement-positions-content-forms', array( $handler, 'ajax' ) );

        $handler = awpcp_load_campaigns_ajax_handler();
        add_action( 'wp_ajax_awpcp-load-campaigns', array( $handler, 'ajax' ) );
        add_action( 'wp_ajax_nopriv_awpcp-load-campaigns', array( $handler, 'ajax' ) );
    }
}

function awpcp_campaign_manager_module() {
    static $module = null;

    if ( is_null( $module ) ) {
        $module = new AWPCP_CamapaignManagerModule( awpcp_campaign_manager_module_installer() );
    }

    return $module;
}

function awpcp_activate_campaign_manager_module() {
    awpcp_campaign_manager_module()->install_or_upgrade();
}
awpcp_register_activation_hook( __FILE__, 'awpcp_activate_campaign_manager_module' );

function awpcp_load_campaign_manager_module( $manager ) {
    $manager->load( awpcp_campaign_manager_module() );
}
add_action( 'awpcp-load-modules', 'awpcp_load_campaign_manager_module' );

}
