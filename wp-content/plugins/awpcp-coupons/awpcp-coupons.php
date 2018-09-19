<?php

/**
 * Plugin Name: AWPCP Coupons Module
 * Plugin URI: http://www.awpcp.com
 * Description: Coupons module for AWPCP
 * Version: 3.6
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

global $wpdb;

define('AWPCP_COUPONS_MODULE', 'Another WordPress Classifieds Plugin - Coupons Module');
define('AWPCP_COUPONS_MODULE_DB_VERSION', '3.6');
define( 'AWPCP_COUPONS_MODULE_REQUIRED_AWPCP_VERSION', '3.6' );

define('AWPCP_COUPONS_MODULE_BASENAME', basename(dirname(__FILE__)));
define('AWPCP_COUPONS_MODULE_DIR', rtrim( plugin_dir_path( __FILE__ ), '/' ));
define('AWPCP_COUPONS_MODULE_URL', rtrim( plugin_dir_url( __FILE__ ), '/' ));

define('AWPCP_OPTION_USE_COUPON_SYSTEM', 'use-coupons-system');

define('AWPCP_TABLE_COUPONS', $wpdb->prefix . 'awpcp_coupons');

require_once( AWPCP_COUPONS_MODULE_DIR . '/includes/class-coupons-module-installer.php' );
require_once(AWPCP_COUPONS_MODULE_DIR . '/includes/coupon.php');

function awpcp_coupons_required_awpcp_version_notice() {
    if ( current_user_can( 'activate_plugins' ) ) {
        $module_name = __( 'Coupons Module', 'awpcp-coupons' );
        $required_awpcp_version = AWPCP_COUPONS_MODULE_REQUIRED_AWPCP_VERSION;

        $message = __( 'The AWPCP <module-name> requires AWPCP version <awpcp-version> or newer!', 'awpcp-coupons' );
        $message = str_replace( '<module-name>', '<strong>' . $module_name . '</strong>', $message );
        $message = str_replace( '<awpcp-version>', $required_awpcp_version, $message );
        $message = sprintf( '<strong>%s:</strong> %s', __( 'Error', 'awpcp-coupons' ), $message );
        echo '<div class="error"><p>' . $message . '</p></div>';
    }
}

if ( ! class_exists( 'AWPCP_ModulesManager' )  ) {

    add_action( 'admin_notices', 'awpcp_coupons_required_awpcp_version_notice' );

} else {

class AWPCP_CouponsModule extends AWPCP_Module {

	private $installer;

	public function __construct( $installer ) {
		parent::__construct(
			__FILE__,
			'Coupons Module',
			'coupons',
			AWPCP_COUPONS_MODULE_DB_VERSION,
			AWPCP_COUPONS_MODULE_REQUIRED_AWPCP_VERSION
		);

		$this->installer = $installer;
	}

	public function required_awpcp_version_notice() {
		awpcp_coupons_required_awpcp_version_notice();
	}

	public function get_installed_version() {
		return get_option( 'awpcp_coupons_db_version' );
	}

	public function install_or_upgrade() {
		$this->installer->install_or_upgrade( $this );
	}

	public function load_dependencies() {
		require_once( AWPCP_COUPONS_MODULE_DIR . '/admin/admin-panel-coupons.php' );
	}

	public function module_setup() {
		parent::module_setup();

		$this->admin_coupons = new AWPCP_AdminCoupons();

		add_action('awpcp_admin_add_submenu_page', array($this, 'admin_submenu'), 10, 2);

		add_action('awpcp-process-payment-transaction', array($this, 'process_transaction'));
		add_action('awpcp-render-transaction-items', array($this, 'coupon_form'), 10, 2);

		add_action('awpcp-place-ad', array($this, 'update_redemption_count'), 10, 2);
		add_action('awpcp-renew-ad', array($this, 'update_redemption_count'), 10, 2);

		add_action('awpcp-buy-subscription', array($this, 'update_redemption_count'), 10, 2);
		add_action('awpcp-renew-subscription', array($this, 'update_redemption_count'), 10, 2);

		$this->register_scripts();

		add_action( 'awpcp_register_settings', array( $this, 'register_settings' ) );
	}

	public function register_settings( $settings ) {
		$settings->add_setting( 'coupons-settings:default', AWPCP_OPTION_USE_COUPON_SYSTEM, __( 'Use Coupon System', 'awpcp-coupons' ), 'checkbox', 1, '' );
	}

	public function register_scripts() {
		$version = AWPCP_COUPONS_MODULE_DB_VERSION;
		wp_register_style( 'awpcp-coupons-admin', AWPCP_COUPONS_MODULE_URL . '/resources/css/admin-style.css', array( 'awpcp-frontend-style', 'awpcp-jquery-ui' ), $version );
		wp_register_script( 'awpcp-coupons-admin', AWPCP_COUPONS_MODULE_URL . '/resources/js/admin-script.js', array( 'awpcp', 'awpcp-admin-wp-table-ajax', 'jquery-ui-datepicker' ), $version, true );
	}

	/**
	 * Users an action add a new "Coupons" menu entry to the
	 * AWPCP Admin menu.
	 */
	public function admin_submenu($slug, $capability='activate_plugins') {
		$menu_slug = 'awpcp-admin-coupons';
		$page = add_submenu_page($slug, __('Manage Coupons/Discounts', 'awpcp-coupons' ), __('Coupons/Discounts', 'awpcp-coupons' ), $capability, $menu_slug, array($this->admin_coupons, 'dispatch'));
		add_action('admin_print_styles-' . $page, array($this->admin_coupons, 'scripts'));

		// TODO: now let's try to place the Coupons menu in the right place
		awpcp_insert_submenu_item_after($slug, $menu_slug, 'Configure2');
	}

	public function process_transaction($transaction) {
		if ($transaction->is_doing_checkout() && awpcp_is_coupon_system_enabled()) {
			$this->process_checkout_form( $transaction );
		} else if ( $transaction->is_payment_completed() || $transaction->is_completed() ) {
            $this->update_redemption_count( null, $transaction );
        }
	}

	/**
	 * @since 3.0.1
	 */
	private function process_checkout_form( $transaction ) {
		global $wpdb;

		$code = awpcp_post_param('coupon-code');
		$id = $transaction->get('coupon-id', 0);

		// see if we already have a coupon
		$old_coupon = $id > 0 ? AWPCP_Coupon::find_by_id($id) : null;

		if (is_null($old_coupon) && empty($code)) {
			return;
		} else if (!empty($code)) {
			$coupons = AWPCP_Coupon::find($wpdb->prepare("code=%s AND enabled=1", $code));
		} else {
			$coupons = array($old_coupon);
		}

		// loop through possible coupons and choose the first that
		// hasn't expired and hasn't exceeded the redemption limit
		$coupon = null;
		foreach ($coupons as $coupon) {
			$has_limit = $coupon->redemption_limit != 0;
			$can_be_redeemed = $coupon->redemption_count < $coupon->redemption_limit;

			if ($coupon->has_expired() || ($has_limit && !$can_be_redeemed)) {
				$coupon = null;
				continue;
			}

			break;
		}

		// add coupon discount to transaction items
		if (!is_null($coupon)) {
			// remove old discouht
			if (!is_null($old_coupon)) {
				$transaction->remove_item("coupon-{$old_coupon->id}");
			}

			$transaction->add_item(
				"coupon-{$coupon->id}",
				sprintf(_x('Coupon Discount (%s)', 'awpcp coupons', 'awpcp-coupons' ), $coupon->code),
				'',
				AWPCP_Payment_Transaction::PAYMENT_TYPE_MONEY,
				- $coupon->get_discount_amount($transaction->get_total_amount())
			);

			$transaction->set('coupon-id', $coupon->id);
		} else if (!empty($coupons)) {
			$message = _x('No coupons could be used, either they expired or already exceeded the redemption limit.', 'awpcp coupons', 'awpcp-coupons' );
			awpcp_flash($message);
		}
	}

	public function coupon_form($html, $transaction) {
		if (!awpcp_is_coupon_system_enabled()) return $html;
		if (!$transaction->is_doing_checkout()) return $html;

		$coupon = AWPCP_Coupon::find_by_id($transaction->get('coupon-id', 0));

		ob_start();
			include(AWPCP_COUPONS_MODULE_DIR . '/frontend/templates/coupons-form.tpl.php');
			$form = ob_get_contents();
		ob_end_clean();

		return $html . $form;
	}

	/**
	 * Triggered each time an Ad is placed/renewed or a Subscription is bought/renewed.
	 * It will increase the Coupon's redemption count if a coupon-id is found in the
	 * transaction associated to the Ad placed.
	 *
	 * @since 2.0.7
	 * @param $unused 	mixed the ID of an Ad or a Subscription object.
	 */
	public function update_redemption_count($unused, $transaction) {
		if (is_null($transaction)) return;

		$coupon = AWPCP_Coupon::find_by_id($transaction->get('coupon-id', 0));
		$processed = $transaction->get('coupon-processed', false);

		if (is_null($coupon) || $processed) return;

		$coupon->redemption_count = $coupon->redemption_count + 1;
		$coupon->save();

		$transaction->set('coupon-processed', true);
	}
}

function awpcp_coupons_module() {
	return new AWPCP_CouponsModule( awpcp_coupons_module_installer() );
}

function awpcp_activate_coupons_module() {
	awpcp_coupons_module()->install_or_upgrade();
}
awpcp_register_activation_hook( __FILE__, 'awpcp_activate_coupons_module' );

function awpcp_load_coupons_module( $manager ) {
    $manager->load( awpcp_coupons_module() );
}
add_action( 'awpcp-load-modules', 'awpcp_load_coupons_module' );

function awpcp_is_coupon_system_enabled() {
	return get_awpcp_option(AWPCP_OPTION_USE_COUPON_SYSTEM) == 1;
}

}
