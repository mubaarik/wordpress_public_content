<?php
/**
 Plugin Name: AWPCP Authorize.Net Module
 Plugin URI: http://www.awpcp.com
 Description: Adds support for Authorize.Net Payments using Advanced Integration Method (AIM)
 * Version: 3.6.3
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

define('AWPCP_AUTHORIZE_NET_MODULE', 'Another WordPress Classifieds Plugin - Authorize.Net Module');
define('AWPCP_AUTHORIZE_NET_MODULE_BASENAME', basename(dirname(__FILE__)));
define('AWPCP_AUTHORIZE_NET_MODULE_DIR', rtrim( plugin_dir_path( __FILE__ ), '/' ));
define('AWPCP_AUTHORIZE_NET_MODULE_URL', rtrim( plugin_dir_url( __FILE__ ), '/' ));
define('AWPCP_AUTHORIZE_NET_MODULE_DB_VERSION', '3.6.3');
define( 'AWPCP_AUTHORIZE_NET_MODULE_REQUIRED_AWPCP_VERSION', '3.6' );

function awpcp_authorize_net_required_awpcp_version_notice() {
    if ( current_user_can( 'activate_plugins' ) ) {
        $module_name = __( 'Authorize.Net Module', 'awpcp-authorize.net' );
        $required_awpcp_version = AWPCP_AUTHORIZE_NET_MODULE_REQUIRED_AWPCP_VERSION;

        $message = __( 'The AWPCP <module-name> requires AWPCP version <awpcp-version> or newer!', 'awpcp-authorize.net' );
        $message = str_replace( '<module-name>', '<strong>' . $module_name . '</strong>', $message );
        $message = str_replace( '<awpcp-version>', $required_awpcp_version, $message );
        $message = sprintf( '<strong>%s:</strong> %s', __( 'Error', 'awpcp-authorize.net' ), $message );
        echo '<div class="error"><p>' . $message . '</p></div>';
    }
}

if ( ! class_exists( 'AWPCP_ModulesManager' ) ) {

    add_action( 'admin_notices', 'awpcp_authorize_net_required_awpcp_version_notice' );

} else {

class AWPCP_AuthorizeNetModule extends AWPCP_Module {

    private $load_scripts = false;

    public function __construct() {
        parent::__construct(
            __FILE__,
            'Authorize.Net Module',
            'authorize.net',
            AWPCP_AUTHORIZE_NET_MODULE_DB_VERSION,
            AWPCP_AUTHORIZE_NET_MODULE_REQUIRED_AWPCP_VERSION
        );
    }

    public function required_awpcp_version_notice() {
        return awpcp_authorize_net_required_awpcp_version_notice();
    }

    public function load_dependencies() {
        require_once( AWPCP_AUTHORIZE_NET_MODULE_DIR . '/includes/payment-gateway-authorize.net.php' );
    }

    public function module_setup() {
        add_action('awpcp_register_settings', array($this, 'register_settings'));
        add_action('awpcp-register-payment-methods', array($this, 'register_payment_methods'));

        parent::module_setup();
    }

    public function is_authorize_net_enabled() {
        return get_awpcp_option('activate-authorize.net');
    }

    public function register_settings() {
        global $awpcp;

        $api = $awpcp->settings;
        $key = $api->add_section('payment-settings', __('Authorize.Net Settings', 'awpcp-authorize.net' ), 'authorize.net-settings', 20, array($api, 'section'));

        $label = __('Activate Authorize.Net', 'awpcp-authorize.net' );
        $api->add_setting($key, 'activate-authorize.net', $label, 'checkbox', 0, $label);

        $api->add_setting(
            $key,
            'authorize.net-login-id',
            _x('Login ID', 'authorize.net', 'awpcp-authorize.net' ),
            'textfield',
            '',
            ''
        );

        $api->add_validation_rule( $key, 'authorize.net-login-id', 'required', array( 'depends' => 'activate-authorize.net' ) );
        $api->add_behavior( $key, 'authorize.net-login-id', 'enabledIf', 'activate-authorize.net' );

        $api->add_setting(
            $key,
            'authorize.net-transaction-key',
            _x('Transaction Key', 'authorize.net', 'awpcp-authorize.net' ),
            'textfield',
            '',
            ''
        );

        $api->add_validation_rule( $key, 'authorize.net-transaction-key', 'required', array( 'depends' => 'activate-authorize.net' ) );
        $api->add_behavior( $key, 'authorize.net-transaction-key', 'enabledIf', 'activate-authorize.net' );
    }

    public function register_payment_methods($payments) {
        if ($this->is_authorize_net_enabled()) {
            $payments->register_payment_method(new AWPCP_AuthorizeNETPaymentGateway);
        }
    }
}

function awpcp_load_authorize_net_module( $manager ) {
    $manager->load( new AWPCP_AuthorizeNetModule() );
}
add_action( 'awpcp-load-modules', 'awpcp_load_authorize_net_module' );

}
