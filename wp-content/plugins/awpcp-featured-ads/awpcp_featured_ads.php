<?php

/**
 * Plugin Name: AWPCP Featured Ads Module
 * Plugin URI: http://awpcp.com/premium-modules/featured-ads-module
 * Version: 3.6.1
 * Description: Allows you to offer featured ads. Includes a widget to display featured ads in your sidebar(s)
 * Author: D. Rodenbaugh
 * Author URI: http://www.skylineconsult.com
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

define('AWPCP_FEATURED_ADS_MODULE', 'Another WordPress Classifieds Plugin - Featured Ads Module');
define( 'AWPCP_FEATURED_ADS_MODULE_DB_VERSION', '3.6.1' );
define( 'AWPCP_FEATURED_ADS_MODULE_REQUIRED_AWPCP_VERSION', '3.6' );

define('AWPCP_FEATURED_ADS_MODULE_BASENAME', basename(dirname(__FILE__)));
define('AWPCP_FEATURED_ADS_MODULE_DIR', rtrim( plugin_dir_path( __FILE__ ), '/' ));
define('AWPCP_FEATURED_ADS_MODULE_URL', rtrim( plugin_dir_url( __FILE__ ), '/' ));

require_once( AWPCP_FEATURED_ADS_MODULE_DIR . '/includes/class-featured-listings-finder.php' );

function awpcp_featured_ads_required_awpcp_version_notice() {
    if ( current_user_can( 'activate_plugins' ) ) {
        $module_name = __( 'Featured Ads Module', 'awpcp-featured-ads' );
        $required_awpcp_version = AWPCP_FEATURED_ADS_MODULE_REQUIRED_AWPCP_VERSION;

        $message = __( 'The AWPCP <module-name> requires AWPCP version <awpcp-version> or newer!', 'awpcp-featured-ads' );
        $message = str_replace( '<module-name>', '<strong>' . $module_name . '</strong>', $message );
        $message = str_replace( '<awpcp-version>', $required_awpcp_version, $message );
        $message = sprintf( '<strong>%s:</strong> %s', __( 'Error', 'awpcp-featured-ads' ), $message );
        echo '<div class="error"><p>' . $message . '</p></div>';
    }
}

if ( ! class_exists( 'AWPCP_ModulesManager' )  ) {

    add_action( 'admin_notices', 'awpcp_featured_ads_required_awpcp_version_notice' );

} else {

class AWPCP_FeaturedAdsModule extends AWPCP_Module {

    public function __construct() {
        parent::__construct(
            __FILE__,
            'Featured Ads Module',
            'featured-ads',
            AWPCP_FEATURED_ADS_MODULE_DB_VERSION,
            AWPCP_FEATURED_ADS_MODULE_REQUIRED_AWPCP_VERSION );
    }

    public function required_awpcp_version_notice() {
        return awpcp_featured_ads_required_awpcp_version_notice();
    }

    public function load_dependencies() {
        require_once( AWPCP_FEATURED_ADS_MODULE_DIR . '/frontend/widget-featured-ads.php' );
    }

    public function load_module() {
        add_action( 'widgets_init', 'awpcp_featured_ads_widgets_init' );
        add_action( 'awpcp_setup_shortcode', 'awpcp_featured_ads_setup_shortcode' );
    }

    protected function module_setup() {
        global $hasfeaturedadsmodule;

        $hasfeaturedadsmodule = true;

        add_action( 'wp_enqueue_scripts', 'awpcp_featured_ads_enqueue_scripts', 10 );

        add_action( 'awpcp-place-ad', 'awpcp_featured_ads_set_featured_status', 10, 2 );
        add_filter( 'awpcp-content-placeholders', 'awpcp_featured_ads_content_placeholders' );

        $finder = awpcp_featured_listings_finder();
        add_filter( 'awpcp-find-listings-conditions', array( $finder, 'filter_conditions' ), 10, 2 );
        add_filter( 'awpcp-find-listings-order-conditions', array( $finder, 'filter_order_conditions' ), 10, 4 );

        $this->register_scripts();

        parent::module_setup();
    }

    public function register_scripts() {
        $version = AWPCP_FEATURED_ADS_MODULE_DB_VERSION;
        $src = AWPCP_FEATURED_ADS_MODULE_URL . '/resources/css/frontend.css';
        wp_register_style( 'awpcp-featured-ads', $src, array( 'awpcp-frontend-style' ), $version );
    }
}

function awpcp_featured_ads_module() {
    return new AWPCP_FeaturedAdsModule();
}

function awpcp_activate_featured_ads_module() {
    awpcp_featured_ads_module()->install_or_upgrade();
}
awpcp_register_activation_hook( __FILE__, 'awpcp_activate_featured_ads_module' );

function awpcp_load_featured_ads_module( $manager ) {
    $manager->load( awpcp_featured_ads_module() );
}
add_action( 'awpcp-load-modules', 'awpcp_load_featured_ads_module' );

function awpcp_featured_ads_widgets_init() {
	register_widget('featured_ads_widget');
}

/**
 * @since  2.1.0
 */
function awpcp_featured_ads_setup_shortcode() {
	add_shortcode('AWPCPFEATUREDLISTINGS', 'awpcp_featured_ads_listings_shortcode');
}

/**
 * @since  2.1.0
 */
function awpcp_featured_ads_listings_shortcode( $shortcode_attrs ) {
    wp_enqueue_script('awpcp-featured-ads' );

    $defaults = array( 'menu' => true, 'limit' => 10, 'items_per_page' => 10 );
    $attrs = shortcode_atts( $defaults, $shortcode_attrs );
    $show_menu = awpcp_parse_bool($attrs['menu']);

    if ( isset( $shortcode_attrs['items_per_page'] ) ) {
        $items_per_page = absint( $attrs['items_per_page'] );
    } else {
        $items_per_page = absint( $attrs['limit'] );
    }

    $query = array(
        'featured' => true,
        'limit' => $items_per_page,
        'offset' => absint( awpcp_request_param( 'offset', 0 ) ),
    );

    $options = array(
        'show_menu_items' => $show_menu,
        'show_pagination' => true,
    );

    return awpcp_display_listings( $query, 'featured-listings-shortcode', $options );
}


/**
 * @since  2.1.0
 */
function awpcp_featured_ads_content_placeholders($placeholders) {
	$placeholders['isfeaturedclass'] = array(
		'callback' => 'awpcp_featured_ads_do_placeholder_featured_class',
	);

	return $placeholders;
}

/**
 * @since  2.1.0
 */
function awpcp_featured_ads_do_placeholder_featured_class($ad, $content) {
	return $ad->is_featured_ad ? 'awpcp_featured_ad_wrapper' : '';
}


function awpcp_featured_ads_set_featured_status($ad, $transaction=null) {
	if (is_null($transaction)) return;

	$payments = awpcp_payments_api();
	$term = $payments->get_transaction_payment_term($transaction);

	if (!is_a($term, 'AWPCP_Fee')) return;

	if (!$term->featured) return;

	$ad->set_featured_status($term->featured);
}


function awpcp_featured_ads_enqueue_scripts() {
	wp_enqueue_style( 'awpcp-featured-ads' );
}


function awpcp_featured_ad_class($adid, $layoutcode) {
	$isfeaturedclass = 'awpcp_featured_ad_wrapper';
	if (awpcp_is_featured_ad($adid))
		return str_replace("\$isfeaturedclass", $isfeaturedclass, $layoutcode);
	else
		return $layoutcode;
}

function awpcp_is_featured_ad($adid) {
    global $wpdb;
    $tbl_ads = $wpdb->prefix . "awpcp_ads";

    if (intval($adid) <=0) return false;

    $query = "select is_featured_ad from $tbl_ads where ad_id = $adid ";
    $res = $wpdb->get_var($query);

    return $res ? true : false;
}

function awpcp_featured_ads(){
	// dummy function to use for module presence tests - do not remove!
}

}
