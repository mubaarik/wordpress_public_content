<?php

/**
 * Plugin Name: AWPCP Region Control Module
 * Plugin URI: http://www.awpcp.com
 * Description: Allows AWPCP users to filter and place ads based on location.  Can be configured for any geographical region.
 * Version: 3.6.8
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


if (preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) {
	die('You are not allowed to call this page directly.');
}

if (function_exists('awpcp_opsconfig_regions')) {

	$msg = __('An old version of Region Control Module is already loaded. Please remove awpcp_region_control_module.php from your AWPCP plugin directory.', 'awpcp-region-control' );
	add_action('admin_notices', create_function('', 'echo \'<div class="error"><p>' . $msg . '</p></div>\';'));
	define('AWPCP_REGION_CONTROL_MODULE_CONFLICT', true);

// start defining the plugin
} else {

global $wpdb;

define('AWPCP_REGION_CONTROL_MODULE', 'Another WordPress Classifieds Plugin - Region Control Module');
define('AWPCP_REGION_CONTROL_MODULE_BASENAME', basename(dirname(__FILE__)));
define('AWPCP_REGION_CONTROL_MODULE_DIR', rtrim( plugin_dir_path( __FILE__ ), '/' ));
define('AWPCP_REGION_CONTROL_MODULE_URL', rtrim( plugin_dir_url( __FILE__ ), '/' ));
define( 'AWPCP_REGION_CONTROL_MODULE_DB_VERSION', '3.6.8' );
define( 'AWPCP_REGION_CONTROL_MODULE_REQUIRED_AWPCP_VERSION', '3.6' );

define( 'AWPCP_TABLE_REGIONS', $wpdb->prefix . 'awpcp_regions' );

require_once(AWPCP_REGION_CONTROL_MODULE_DIR . '/api/regions.php');

require_once( AWPCP_REGION_CONTROL_MODULE_DIR . '/frontend/class-admin-bar-region-selector.php' );
require_once( AWPCP_REGION_CONTROL_MODULE_DIR . '/frontend/class-region-selector-popup.php' );

require_once( AWPCP_REGION_CONTROL_MODULE_DIR . '/includes/exceptions.php' );
require_once( AWPCP_REGION_CONTROL_MODULE_DIR . '/includes/functions.php' );
require_once( AWPCP_REGION_CONTROL_MODULE_DIR . '/includes/class-location-service.php' );
require_once( AWPCP_REGION_CONTROL_MODULE_DIR . '/includes/class-regions-ad-count-event-listener.php' );
require_once( AWPCP_REGION_CONTROL_MODULE_DIR . '/includes/class-regions-field-options-finder.php' );
require_once( AWPCP_REGION_CONTROL_MODULE_DIR . '/includes/class-regions-listings-count-repairer.php' );
require_once( AWPCP_REGION_CONTROL_MODULE_DIR . '/includes/class-regions-listings-record-finder.php' );
require_once( AWPCP_REGION_CONTROL_MODULE_DIR . '/includes/class-regions-sidelist-builder.php' );
require_once( AWPCP_REGION_CONTROL_MODULE_DIR . '/includes/class-regions-sidelist.php' );
require_once( AWPCP_REGION_CONTROL_MODULE_DIR . '/includes/class-set-location-request-handler.php' );
require_once( AWPCP_REGION_CONTROL_MODULE_DIR . '/includes/class-user-default-location-handler.php' );
require_once( AWPCP_REGION_CONTROL_MODULE_DIR . '/includes/views/admin/class-calculate-regions-listings-count-page.php' );
require_once( AWPCP_REGION_CONTROL_MODULE_DIR . '/includes/views/admin/class-regenerate-regions-sidelist-page.php' );
require_once( AWPCP_REGION_CONTROL_MODULE_DIR . '/includes/views/admin/class-regions-sidelist-handler.php' );

require_once(AWPCP_REGION_CONTROL_MODULE_DIR . '/install.php');

function awpcp_region_control_required_awpcp_version_notice() {
    if ( current_user_can( 'activate_plugins' ) ) {
        $module_name = __( 'Regions Module', 'awpcp-region-control' );
        $required_awpcp_version = AWPCP_REGION_CONTROL_MODULE_REQUIRED_AWPCP_VERSION;

        $message = __( 'The AWPCP <module-name> requires AWPCP version <awpcp-version> or newer!', 'awpcp-region-control' );
        $message = str_replace( '<module-name>', '<strong>' . $module_name . '</strong>', $message );
        $message = str_replace( '<awpcp-version>', $required_awpcp_version, $message );
        $message = sprintf( '<strong>%s:</strong> %s', __( 'Error', 'awpcp-region-control' ), $message );
        echo '<div class="error"><p>' . $message . '</p></div>';
    }
}

if ( ! class_exists( 'AWPCP_ModulesManager' )  ) {

    add_action( 'admin_notices', 'awpcp_region_control_required_awpcp_version_notice' );

} else {

// for backward compatibility
$tbl_ad_regions = AWPCP_TABLE_REGIONS;

class AWPCP_RegionControlModule extends AWPCP_Module {

	private $installer;

    public function __construct( $installer ) {
        parent::__construct(
            __FILE__,
            'Regions Module',
            'region-control',
            AWPCP_REGION_CONTROL_MODULE_DB_VERSION,
            AWPCP_REGION_CONTROL_MODULE_REQUIRED_AWPCP_VERSION
		);

        $this->installer = $installer;
    }

    public function required_awpcp_version_notice() {
        return awpcp_region_control_required_awpcp_version_notice();
    }

    public function get_installed_version() {
    	return get_option( 'awpcp-region-control-db-version' );
    }

    public function install_or_upgrade() {
    	$this->installer->install_or_upgrade( $this );
    }

    public function load_dependencies() {
		require_once( AWPCP_REGION_CONTROL_MODULE_DIR . '/includes/views/admin/class-calculate-regions-listings-count-ajax-handler.php' );
		require_once( AWPCP_REGION_CONTROL_MODULE_DIR . '/includes/views/admin/class-regenerate-regions-sidelist-ajax-handler.php' );
    }

	protected function module_setup() {
		parent::module_setup();

		$listener = awpcp_regions_ad_count_event_listener();
		add_action( 'awpcp-place-ad', array( $listener, 'on_place_ad' ), 10, 1 );
		add_action( 'awpcp_before_edit_ad', array( $listener, 'on_before_edit_ad' ), 10, 1 );
		add_action( 'awpcp_edit_ad', array( $listener, 'on_edit_ad' ), 10, 1 );
		add_action( 'awpcp_approve_ad', array( $listener, 'on_approve_ad' ), 10, 1 );
		add_action( 'awpcp_disable_ad', array( $listener, 'on_disable_ad' ), 10, 1 );
		add_action( 'awpcp_before_delete_ad', array( $listener, 'on_before_delete_ad' ), 10, 1 );

		// tell AWPCP the module is available
		global $hasregionsmodule;
		$hasregionsmodule = 1;

		$this->initialize_session();

		add_action('awpcp_register_settings', array($this, 'register_settings'));
		add_action('awpcp_admin_add_submenu_page', array($this, 'admin_menu'), 10, 2);

		$handler = awpcp_user_default_location_handler();
		add_action( 'wp_loaded', array( $handler, 'maybe_set_current_user_location_as_active_region' ) );
		add_action( 'wp_login', array( $handler, 'maybe_set_logged_in_user_location_as_active_region' ), 10, 2 );
		add_action( 'awpcp-user-profile-updated', array( $handler, 'maybe_update_active_region_with_user_profile_information' ), 10, 2 );
		add_filter( 'awpcp-listing-details-user-info', array( $handler, 'set_active_region_as_default_user_location_in_listing_details_form' ), 10, 2 );
		add_filter( 'awpcp-get-posted-data', array( $handler, 'set_active_region_as_default_user_location_in_search_ads_form' ), 10, 2 );

		add_action('wp_loaded', 'awpcp_region_control_rules');
        add_action( 'page_rewrite_rules', 'awpcp_region_control_rewrite_rules' );
		add_filter('query_vars', 'awpcp_region_control_query_vars');

		$request_handler = awpcp_set_location_request_handler();

		add_action( 'template_redirect', array( $request_handler, 'dispatch' ) );

		add_filter('awpcp-ad-where-statement', array($this, 'get_ads_where_conditions'));
		add_filter( 'awpcp-find-listings-query', array( $this, 'filter_listings_query' ) );

		add_filter( 'awpcp-content-before-listings-pagination', array( $this, 'listings_before_content'), 10, 2 );
		add_filter( 'awpcp-categories-list-transient-key-params', array( $this, 'include_active_region_id_in_transient_key' ) );
		add_filter( 'awpcp-categories-list-container', array( $this, 'maybe_render_regions_sidelist' ), 10, 2 );

		add_filter( 'awpcp-region-fields', array( $this, 'region_selector_fields' ), 10, 3 );
		add_filter( 'awpcp-multiple-region-selector-configuration', array( $this, 'region_selector_configuration' ), 10, 3 );
		add_filter( 'awpcp-region-field-options', array( $this, 'region_selector_field_options' ), 10, 5 );
		add_filter( 'awpcp-get-regions-options', array( $this, 'region_selector_get_regions_options' ), 10, 5 );

		add_action('wp_ajax_awpcp-region-control-update-ad-count', array($this, 'ajax_update_ad_count'));

		add_action('wp_ajax_awpcp-region-control-autocomplete', array($this, 'ajax_autocomplete'));

		add_action('wp_ajax_awpcp-delete-region', 'awpcp_region_control_delete_region_ajax');

		$this->register_scripts();
		$this->localize_scripts();

		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_common_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_common_scripts' ) );

		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_scripts' ) );
	}

	protected function ajax_setup() {
		$handler = awpcp_regenerate_regions_sidelist_ajax_handler();
		add_action( 'wp_ajax_awpcp-regenerate-regions-sidelist', array( $handler, 'ajax' ) );

		$handler = awpcp_calculate_regions_listings_count_ajax_handler();
		add_action( 'wp_ajax_awpcp-calculate-regions-listings-count', array( $handler, 'ajax' ) );
	}

	protected function admin_setup() {
		$handler = awpcp_regions_sidelist_handler();
		add_action( 'admin_init', array( $handler, 'dispatch' ) );
	}

    protected function frontend_setup() {
        $admin_bar_region_selector = $this->get_admin_bar_region_selector();
        add_filter( 'admin_bar_menu', array( $admin_bar_region_selector, 'maybe_add_admin_bar_menu_items' ), 20 );
    }

    protected function get_admin_bar_region_selector() {
        return awpcp_admin_bar_region_selector();
    }

	public function hierarchy_problems_notice() {
		global $wpdb;

		$api = awpcp_regions_api();

		$hierarchy = $api->get_regions_type_hierarchy();
		$parent = null;

		foreach ($hierarchy as $level => $types) {
			if (count($types) === 1) {
				$parent = reset($types);
				continue;
			}

			$message = _x('Regions of types <strong>%s</strong> were found in level %d of your hierarchy. Please fix it so only one type of Region is found in each level of the regions hierarchy.', 'regions module', 'awpcp-region-control' );
			$names = $api->get_region_types_names($types);

			echo '<div class="error"><p>' . sprintf($message, join('</strong>, <strong>', $names), $level) . '</p></div>';

			if ($parent) {
				// find exactly how many regions of every type are in the problematic level
				$query = 'SELECT region_type, COUNT(region_id) AS count FROM ' . AWPCP_TABLE_REGIONS . ' ';
				$query.= 'WHERE region_parent IN (SELECT region_id FROM ' . AWPCP_TABLE_REGIONS . ' WHERE region_type = %d) ';
				$query.= 'GROUP BY region_type ORDER BY count DESC';

				$messages = array();
				$message = _x('<strong>%d</strong> regions of type <strong>%s</strong>', 'regions module', 'awpcp-region-control' );

				$results = $wpdb->get_results($wpdb->prepare($query, $parent));
				foreach ($results as $row) {
					$messages[] = sprintf($message, $row->count, $api->get_region_type_name($row->region_type));
				}

				// last row contains the type with less occurrences
				if ( ! empty( $results ) ) {
					$children = end( $results )->region_type;
				} else {
					continue;
				}

				// get the name of some of the problematic regions
				$query = 'SELECT region_name, region_id FROM ' . AWPCP_TABLE_REGIONS . ' ';
				$query.= 'WHERE region_parent IN (SELECT region_id FROM ' . AWPCP_TABLE_REGIONS . ' WHERE region_type = %d) ';
				$query.= 'AND region_type = %d ';
				$query.= 'ORDER BY region_id LIMIT %d, 5';

				$names = array();
				$base = add_query_arg('action', 'editregion', awpcp_current_url());
				$link = '<a href="%s">%s</a>';

				$offset = max(0, rand(0, end($results)->count - 5));
				$results = $wpdb->get_results($wpdb->prepare($query, $parent, $children, $offset));
				foreach ($results as $row) {
                if ( $row->region_name ) {
                    $name = $row->region_name;
                } else {
                    $name = _x( 'Unnamed Region', 'hierarchy problems notice', 'awpcp-region-control' );
                }
					$url = add_query_arg('regionid', $row->region_id, $base);
					$names[] = sprintf($link, $url, $name);
				}

				$message = _x('There are %s in level %d. Some of the regions of type <strong>%s</strong> in that level are: <strong>%s</strong>; please update the parent region for those regions so that they are placed in the right level.', 'e.g. There are [15 regions of type City and 3 regions of type County] in level 5. Some of the regions of type [City] are: Petaluma, Santa Rosa, Sebastopol; please update the parent region for those regions so that they are placed in the right level.', 'awpcp-region-control' );
				$message = sprintf($message, join(' and ', $messages), $level, $api->get_region_type_name($children), join( '</strong>, <strong>', $names ));

				echo awpcp_print_error( $message );
			}

			break;
		}
	}

	/**
	 * Conditionally start session if not already active.
	 * Moved from AWPCP's main class.
	 *
	 * @since  3.3.7
	 */
	public function initialize_session() {
		$session_id = session_id();

		if ( empty( $session_id ) ) {
			$request = awpcp_request();
			// if we are in a subdomain, let PHP choose the right domain
			if ( strcmp( $request->domain(), $request->domain( false ) ) == 0 ) {
				$domain = '';
			// otherwise strip the www part
			} else {
				$domain = $request->domain( false, '.' );
			}

			@session_set_cookie_params( 0, '/', $domain, false, true );
			@session_start();
		}
	}

	/**
	 * TODO: use new Multiple Region Selector scripts
	 */
	public function register_scripts() {
		$version = AWPCP_REGION_CONTROL_MODULE_DB_VERSION;

		// remove older versions that could have been registered by the main plugin
		wp_deregister_script('awpcp-region-control');
		wp_deregister_style('awpcp-region-control');

		$src = AWPCP_REGION_CONTROL_MODULE_URL . '/resources/css/region-control.css';
		wp_register_style( 'awpcp-region-control', $src, array( 'awpcp-frontend-style', 'dashicons' ), $version );

		$src = AWPCP_REGION_CONTROL_MODULE_URL . '/resources/js/regions.min.js';
		wp_register_script( 'awpcp-region-control', $src, array( 'awpcp' ), $version, true );

		$src = AWPCP_REGION_CONTROL_MODULE_URL . '/resources/js/legacy.js';
		wp_register_script('awpcp-region-control-legacy', $src, array(), $version, true);
		$src = AWPCP_REGION_CONTROL_MODULE_URL . '/resources/js/admin.js';
		wp_register_script('awpcp-region-control-admin', $src, array('awpcp-admin-general', 'awpcp-jquery-validate', 'jquery-form', 'jquery-ui-autocomplete'), $version, true);
	}

	/**
	 * @since 3.0
	 */
	public function localize_scripts() {
		$awpcp = awpcp();
		$awpcp->js->localize( 'region-control-frontend', 'no-regions-available', __( 'No Regions available', 'awpcp-region-control' ) );
		$awpcp->js->localize( 'region-control-admin', 'region_parent', __( 'Please type in the name of the parent region and select one of the suggested regions.', 'awpcp-region-control' ) );
	}

	public function enqueue_common_scripts() {
		// we need to load Region Control in every page becuase by
		// the time the region selector is rendered is already too
		// late to include the <link> tag in the header.
		// TODO: load the style on required pages: pages that show
		// region fields and region selector.
		wp_enqueue_style('awpcp-region-control');
	}

	public function enqueue_frontend_scripts() {
		$show_region_selector_in_admin_bar = get_awpcp_option( 'enable-region-selector-popup-in-admin-bar' );

		if ( $show_region_selector_in_admin_bar ) {
			wp_enqueue_script( 'awpcp-multiple-region-selector' );
		}

		if ( $show_region_selector_in_admin_bar || get_awpcp_option( 'showregionssidelist' ) ) {
			wp_enqueue_script( 'awpcp-region-control' );
			wp_enqueue_style('awpcp-region-control');
		}
	}

	public function register_settings() {
		global $awpcp;

		$api = $awpcp->settings;
		$key = $api->add_section( 'general-settings', __( 'Region Control Settings', 'awpcp-region-control' ), 'region-control-settings', 15, array( $api, 'section' ) );

		$api->add_setting($key, 'showregionssidelist', __('Show the regions sidelist', 'awpcp-region-control' ), 'checkbox', 0, __('Show the regions sidelist on category page (used with the Regions Control module)', 'awpcp-region-control' ));

		$api->add_setting(
			$key,
			'set-logged-in-user-default-location-as-active-region',
			__( "Use user's deafult location as active region?", 'awpcp-region-control' ),
			'checkbox',
			0,
			__( "If checked, when a user logs in, that user's default location (set in his or her profile) will be set as the active region. The user can then use one of the available Region Selectors or the Region Sidelist to change the active region for the current session.", 'awpcp-region-control' )
		);

		$api->add_setting(
			$key,
			'enable-region-selector-popup-in-admin-bar',
			__( 'Enable Region Selector Popup in the admin bar?', 'awpcp-region-control' ),
			'checkbox',
			0,
			__( 'If checked, an item showing the current location will be added to the admin bar in frontend screens. Users will be able to click that item to expand a Region Selector that they can use to change the active region and their default location.', 'awpcp-region-control' )
		);

		$api->add_setting(
			$key,
			'show-region-selector',
			__( 'Show the Region Selector', 'awpcp-region-control' ),
			'checkbox',
			1,
			__( 'If checked, the Region Selector will be shown in all listings pages.', 'awpcp-region-control' )
		);

		$api->add_setting(
			$key,
			'region-control-keep-selector-always-open',
			__( 'Keep the Region Selector always open', 'awpcp-region-control' ),
			'checkbox',
			0,
			__( 'The value of this setting does not affect the behaviour of the Region Selector Popup. It only applies for the Region Selector shown at the top of some of the AWPCP pages.', 'awpcp-region-control' )
		);

		$api->add_setting( $key, 'hide-empty-regions', __( 'Hide empty regions', 'region-control-settings' ), 'checkbox', 0, __( 'Do not show empty regions in region dropdowns  displayed in Region Selector and Search Ads screens.', 'region-control-settings' ) );
		$api->add_setting( $key, 'hide-regions-ads-count', __( 'Do not show region Ad count.', 'region-control-settings' ), 'checkbox', 0, '' );
	}

	public function admin_menu($slug, $capability) {
		global $awpcp_db_version;

		// avoid conflict with AWPCP 2.1.3.1 and older
		if (version_compare($awpcp_db_version, '2.1.3.1') <= 0)
			return;

		$title = __('Manage Regions', 'awpcp-region-control' );
		$menu = __('Regions', 'awpcp-region-control' );
		$page = 'Configure4';
		$function = 'awpcp_opsconfig_regions';
		$hook = add_submenu_page($slug, $title, $menu, $capability, $page, $function/*array($this->admin, 'dispatch')*/);
		add_action('load-' . $hook, array($this, 'load_manage_regions'));
	}

	/**
	 * Runs every time we are seeing the Manage Regions screen.
	 *
	 * @since 2.0.8
	 */
	public function load_manage_regions() {
		do_action( 'awpcp-admin-section-manage-regions' );

		add_action('admin_notices', array($this, 'hierarchy_problems_notice'));

		wp_enqueue_style('awpcp-region-control');

		wp_enqueue_script('awpcp-region-control-admin');
		wp_enqueue_script('awpcp-region-control-legacy');
	}

    public function get_active_region() {
        static $active_region = false;

        $current_location = $this->get_current_location_data();

        if ( $active_region === false && isset( $current_location['theactiveregionid'] ) ) {
            $region_id = absint( $current_location['theactiveregionid'] );
            $active_region = awpcp_regions_api()->find_by_id($region_id);
        }

        return $active_region;
    }

	public function set_location($region) {
		$current_location = awpcp_location_service()->set_active_region( $region );
		return awpcp_array_data( 'theactiveregionid', '', $current_location );
	}

    public function get_current_location_data() {
    	return awpcp_location_service()->get_current_location();
    }

	public function set_user_default_location( $region ) {
		$user_id = wp_get_current_user()->ID;
		$profile = (array) get_user_meta( $user_id, 'awpcp-profile', true );

		$profile['country'] = '';
		$profile['state'] = '';
		$profile['city'] = '';
		$profile['county'] = '';

		if ( is_null( $region ) ) {
			return;
		}

		$api = awpcp_regions_api();
		$parent = $region;

		do {
			switch ( $parent->region_type ) {
				case AWPCP_RegionsAPI::TYPE_COUNTRY:
					$profile['country'] = $parent->region_name;
					break;
				case AWPCP_RegionsAPI::TYPE_STATE:
					$profile['state'] = $parent->region_name;
					break;
				case AWPCP_RegionsAPI::TYPE_CITY:
					$profile['city'] = $parent->region_name;
					break;
				case AWPCP_RegionsAPI::TYPE_COUNTY:
					$profile['county'] = $parent->region_name;
					break;
			}

			$parent = $api->find_by_id( $parent->region_parent );
		} while( ! is_null( $parent ) );

		update_user_meta( $user_id, 'awpcp-profile', $profile );

		return $region->region_id;
	}

	public function get_current_location_names() {
		$api = awpcp_regions_api();

		$session = awpcp_region_control_session();
		$location = array();

		foreach ($api->get_regions_type_hierarchy() as $level => $types) {
			$slug = $api->get_region_type_slug($types[0]);
			if (isset($session[$slug]))
				$location[] = esc_html( $session[$slug]->region_name );
		}

		return $location;
	}

	public function get_current_location() {
		$location = $this->get_current_location_names();

		if (!empty($location)) {
			$text = esc_html( __('Itus alaabta taala %s.', 'awpcp-region-control' ) );
			return sprintf($text, ' <strong>' . join('&nbsp;&#8594;&nbsp;', $location) . '</strong>');
		} else {
			return esc_html( __('Itus alaabta meelkasta.', 'awpcp-region-control' ) );
		}
	}

	/**
	 * Used to restrict Ads to the ones associated with the Active Region.
	 *
	 * Handler for the awpcp-ad-where-statement filter, called from Ad.php.
	 */
	public function get_ads_where_conditions($conditions) {
		if ($active_region = $this->get_active_region()) {
			$conditions[] = awpcp_regions_api()->sql_where($active_region->region_id);
		}
		return $conditions;
	}

	/**
	 * Handler for awpcp-find-listings-query.
	 */
	public function filter_listings_query( $query ) {
		if ( ! in_array( 'public-listings', $query['context'], true ) ) {
			return $query;
		}

		if ( ! empty( $query['regions'] ) ) {
			return $query;
		}

		$active_region = $this->get_active_region();

		if ( ! is_object( $active_region ) ) {
			return $query;
		}

		switch ( $active_region->region_type ) {
			case 2:
				$param_name = 'country';
				break;
			case 3:
				$param_name = 'state';
				break;
			case 4:
				$param_name = 'city';
				break;
			case 5:
				$param_name = 'county';
				break;
			default:
				$param_name = null;
				break;
		}

		if ( is_null( $param_name ) ) {
			return $query;
		}

		$query['regions'][] = array( $param_name => array( '=', $active_region->region_name ) );

		return $query;
	}

	/**
	 * Used to insert region selector at the top of the listings page.
	 *
	 * Handler for awpcp-content-before-listings-pagination filter.
	 */
	public function listings_before_content($before_content, $context) {
		if ( $context != 'search' && get_awpcp_option( 'show-region-selector') ) {
			$before_content[5]['regions-selector'] = awpcp_region_control_selector();
		}

		return $before_content;
	}

	public function include_active_region_id_in_transient_key( $transient_key_params ) {
		if ( $active_region = $this->get_active_region() ) {
			$transient_key_params['active_region'] = $active_region;
		}
		return $transient_key_params;
	}

	public function maybe_render_regions_sidelist( $container, $options ) {
		$options = wp_parse_args( $options, array( 'show_sidebar' => false ) );

		if ( ! $options['show_sidebar'] || ! get_awpcp_option( 'showregionssidelist' ) ) {
			return $container;
		}

		$replacement = '[sidebar]<div class="awpcpcatlayoutleft">[categories-list]</div>';
		$container = str_replace( '[categories-list]', $replacement, $container );
		$container = str_replace( '[sidebar]', awpcp_region_control_render_sidelist(), $container );

		return $container;
	}

	/*------------------------------------------------------------------------
	 * Multiple Region Selector Integration
	 */

	public function region_selector_fields( $fields, $context, $enabled_fields ) {
		if ( $fields !== false ) return $fields;

		$api = awpcp_regions_api();
		$fields = awpcp_default_region_fields( $context, $enabled_fields );
		$ordered = array();

		foreach ($api->get_regions_type_hierarchy() as $level => $types) {
			$slug = $api->get_region_type_slug( $types[0] );
			if ( isset( $fields[ $slug ] ) ) {
				$ordered[ $slug ] = $fields[ $slug ];
				$ordered[ $slug ]['alwaysShown'] = false;
			}
		}

		// perhaps some fields are visible but there are no regions defined of
		// that type
		foreach ( $fields as $slug => $field ) {
			if ( ! isset( $ordered[ $slug ] ) ) {
				$ordered[ $slug ] = $fields[ $slug ];
				$ordered[ $slug ]['alwaysShown'] = false;
			}
		}

		return $ordered;
	}

	public function region_selector_configuration( $options, $context, $fields ) {
		if ( strcmp( $context, 'region-selector' ) === 0 ) {
			$options['maxRegions'] = 1;
		}

		$api = awpcp_regions_api();

		$hierarchy = array();
		foreach ( $api->get_regions_type_hierarchy() as $level => $types ) {
			$slug = $api->get_region_type_slug( $types[0] );
			if ( isset( $fields[ $slug ] ) ) {
				$hierarchy[] = $slug;
			}
		}

		// some users define the top level regions only, and allow users to
		// type the more specific ones, let's show text fields to support that
		$options['showTextField'] = true;
		$options['hierarchy'] = $hierarchy;

		return $options;
	}

	/**
	 * @since 3.2.7
	 */
	public function region_selector_field_options($options, $context, $region_type, $selected, $selected_parents ) {
		$hide_regions_ad_count = get_awpcp_option( 'hide-regions-ads-count', false );
		$hide_emtpy_regions = $this->should_hide_empty_regions_in_context( $context );

        if ( $options !== false ) {
            return $options;
        }

        $regions_api = awpcp_regions_api();
        $region_type_const = $regions_api->get_region_type_const( $region_type );
        $parent_type_const = $regions_api->get_parent_region_type( $region_type_const );
        $parent_type = $regions_api->get_region_type_slug( $parent_type_const );

        // do not return options if this is not the first field and the parent field has
        // not selected value.
        //
        // using in_array(array_keys()) instead of isset() because isset returns false
        // for array entries with null value.
        if ( in_array( $parent_type, array_keys( $selected_parents ) ) && empty( $selected_parents[ $parent_type ] ) ) {
            return array();
        }

        return awpcp_regions_field_options_finder()->get_field_options(
            $region_type, $selected_parents, $hide_emtpy_regions, $hide_regions_ad_count
        );
	}

	private function should_hide_empty_regions_in_context( $context ) {
		$hide_emtpy_regions = false;

		if ( get_awpcp_option( 'hide-empty-regions', false ) ) {
			if ( $context == 'region-selector' || $context == 'search' ) {
				$hide_emtpy_regions = true;
			}
		}

		return $hide_emtpy_regions;
	}

	/**
	 * Handler for AJAX request from the Multile Region Selector to get new options
	 * for a given field.
	 *
	 * TODO: Merge this function with region_selector_field_options().
	 *
	 * @since 3.1.0
	 */
	public function region_selector_get_regions_options( $options, $region_type, $parent_type, $parent_id, $context ) {
		if ( false !== $options ) {
			return $options;
		}

		$hide_regions_ad_count = get_awpcp_option( 'hide-regions-ads-count', false );
		$hide_emtpy_regions = $this->should_hide_empty_regions_in_context( $context );

		$api = awpcp_regions_api();

		switch ($region_type) {
			case 'state':
			case 'city':
			case 'county':
				$region_type = $api->get_region_type_const($region_type);
				$parent_type = $api->get_region_type_const($parent_type);
				$entries = $api->find_regions_by_parent( $region_type, $parent_type, $parent_id, $hide_emtpy_regions );
				break;
			case 'country':
			default:
				$entries = array();
		}

        $name_template = $hide_regions_ad_count ? '%s' : '%s (%d)';

        $options = array();
        foreach ( $entries as $entry ) {
            $options[] = array(
                'id' => $entry->region_name,
                'name' => sprintf( $name_template, $entry->region_name, $entry->count_enabled ),
            );
        }

		return $options;
	}

	/**
	 * TODO: Test!
	 * http://10.22.22.22/wp-admin/admin-ajax.php?action=awpcp-region-control-update-ad-count
	 * @fixed
	 */
	public function ajax_update_ad_count() {
		global $wpdb;

		$index = absint(get_option('awpcp-region-control-ad-count-index'));

		$sql = 'SELECT SQL_CALC_FOUND_ROWS DISTINCT ar.ad_id, r.region_id, r.region_type ';
		$sql.= 'FROM ' . AWPCP_TABLE_AD_REGIONS . ' AS ar ';
		$sql.= 'LEFT JOIN ' . AWPCP_TABLE_REGIONS . ' AS r ON (';
		$sql.= ' (region_name = country AND region_type = %d) OR ';
		$sql.= ' (region_name = state AND region_type = %d) OR ';
		$sql.= ' (region_name = county AND region_type = %d) OR ';
		$sql.= ' (region_name = city AND region_type = %d) ';
		$sql.= ') ';
		$sql.= 'WHERE ad_id > %%d ';
		$sql.= 'ORDER BY ad_id ASC ';
		$sql.= 'LIMIT 0, 100';

		$sql = $wpdb->prepare( $sql, AWPCP_RegionsAPI::TYPE_COUNTRY,
									 AWPCP_RegionsAPI::TYPE_STATE,
									 AWPCP_RegionsAPI::TYPE_COUNTY,
									 AWPCP_RegionsAPI::TYPE_CITY );

		$ads = array();
		$results = $wpdb->get_results( $wpdb->prepare( $sql, $index ) );
		// number of Ads left to process before this upgrade step
		$number_of_ads = $wpdb->get_var( 'SELECT FOUND_ROWS()' );

		// group regions by Ad ID.
		foreach ($results as $row) {
			if ( $row->region_id ) {
				$ads[ $row->ad_id ][] = $row->region_id;
			}
			$last = $row->ad_id;
		}

		// Some of the regions associated to the last Ad could have been
		// dropped by the 100 rows limit in the query above. For that reason
		// the last Ad won't be considered unless is the only Ad in the results
		// set.
		if (count($ads) > 1) {
			unset($ads[$last]);
		}

		$api = awpcp_regions_api();
        $ad_regions = awpcp_basic_regions_api();

		// sort types hierarchy so lowest level types appear first
		$hierarchy = $api->get_regions_type_hierarchy();
		krsort($hierarchy, SORT_NUMERIC);

		foreach ( $ads as $index => $regions_ids ) {
        	$ad = AWPCP_Ad::find_by_id( $index );
        	$regions = $api->find_regions( array( 'id' => $regions_ids ) );

        	if ( ! empty( $regions ) ) {
        		$ad_regions->delete_by_ad_id( $ad->ad_id );
        	}

	        foreach ( $regions as $region ) {
	            $api->update_ad_count( $region, 1, $ad->disabled ? 0 : 1 );
	        }
		}

		if ( count( $ads ) === 0 && count( $results ) > 0 ) {
			$index = end( $results )->ad_id;
		}

		// calculate the number of rows left to process
		$wpdb->get_results( $wpdb->prepare( $sql, $index ) );
		$remaining_ads = intval( $wpdb->get_var( 'SELECT FOUND_ROWS()' ) );

		update_option( 'awpcp-region-control-ad-count-index', $index );

		if ( $remaining_ads === 0 ) {
			delete_option( 'awpcp-region-control-update-ad-count' );
			delete_option( 'awpcp-region-control-ad-count-index' );
		}

		$response = array( 'status' => 'ok', 'recordsCount' => $number_of_ads, 'recordsLeft' => $remaining_ads );

		header( "Content-Type: application/json" );
		echo json_encode($response);
		die();
	}

	public function ajax_autocomplete() {
		$api = awpcp_regions_api();
		$term = awpcp_request_param('term');
		$type = (int) awpcp_request_param('type');

		$regions = $api->find_regions(array(
			'fields' => 'region_id AS id, region_name AS value, region_parent, region_type',
			'like' => $term,
			'type' => $api->get_parent_region_type($type),
			'state' => null,
			'offset' => 0,
			'limit' => 30,
			'order' => array('region_name ASC', 'region_type ASC')
		));

		if (!empty($regions)) {
			$results = $api->find_regions(array(
				'fields' => 'region_name, region_id',
				'id' => awpcp_get_properties($regions, 'region_parent')
			));

			foreach ($results as $region) {
				$parents[$region->region_id] = $region->region_name;
			}
		} else {
			$parents = array();
		}

		foreach ($regions as &$region) {
			if (is_a_duplicate_region_name($region->value)) {
				$type = $api->get_region_type_name($region->region_type);
				$parent = awpcp_array_data($region->region_parent, '', $parents);
				$region->label = sprintf("%s (%s, %s)", $region->value, $type, $parent);
			} else {
				$region->label = $region->value;
			}
		}

		header( "Content-Type: application/json" );
		echo json_encode(array('items' => $regions));
		die();
	}
}

/**
 * Maintained for backwards compatibility.
 */
function awpcp_regions() {
	return awpcp_region_control_module();
}

function awpcp_region_control_module() {
	static $instance = null;

	if ( is_null( $instance ) ) {
		$instance = new AWPCP_RegionControlModule( awpcp_region_control_module_installer() );
	}

    return $instance;
}

function awpcp_activate_region_control_module() {
    awpcp_region_control_module()->install_or_upgrade();
}
awpcp_register_activation_hook( __FILE__, 'awpcp_activate_region_control_module' );

function awpcp_load_region_control_module( $manager ) {
    $manager->load( awpcp_region_control_module() );
}
add_action( 'awpcp-load-modules', 'awpcp_load_region_control_module' );

function awpcp_opsconfig_regions($where) {
	if (get_option('awpcp-region-control-update-ad-count')) {
		return awpcp_region_control_update_ad_count();
	} else {
		return awpcp_region_control_manage_regions($where);
	}
}

/**
 * @since 3.2.0
 */
function awpcp_region_control_manage_regions( $where ) {
	$action = awpcp_request_param( 'action' );

	if ( $action == 'regenerate-regions-sidelist' ) {
		$page = new AWPCP_RegenerateRegionsSidelistPage();
		return $page->dispatch();
	} else if ( $action == 'calculate-regions-listings-count' ) {
		$page = new AWPCP_CalculateRegionsListingsCountPage();
		return $page->dispatch();
	} else {
		return awpcp_legacy_region_control_manage_regions( $where );
	}
}

function awpcp_legacy_region_control_manage_regions($where) {
	global $wpdb, $message, $awpcp_imagesurl;

	$tbl_ad_regions = $wpdb->prefix . "awpcp_regions";
	$output = '';

	$offset = absint( awpcp_request_param( 'offset', 0 ) );
	$results = absint( awpcp_request_param( 'results', 10 ) );

	if(!isset($message) || empty($message)) {
		$message='';
	}

	$output .= "<div class=\"wrap\"><h2>" . __('AWPCP - Regions Control Module', 'awpcp-region-control' ) . "</h2>";

	$sidebar = awpcp_admin_sidebar();
	$output .= $sidebar;

	if (empty($sidebar)) {
		$output .= "<div><div>";
	} else {
		$output .= "<div style=\"width:75%\"><div>";
	}

	$output .= '<p>' . __( "The regions control module allows you to specify regions in order to use drop down select options instead of having your users fill in any country/state/city of their choosing. This module is useful if you want to limit your classifieds services to specific countries or specific states or specific cities.", 'awpcp-region-control' ) . '</p>';
	$output .= '<p>' . __( 'For your convenience, states/towns and cities can be added one by one or in multiples.', 'awpcp-region-control' ) . '</p>';
	$output .= '<p>' . __( '<strong>Note:</strong> The "Sidelist" is the list of regions that will show on your main classifieds categories page. You can activate/deactivate from Classifieds->settings. (Continents cannot be added to the sidelist)', 'awpcp-region-control' ) . '</p>';


	$action='';
	$the_awpcp_regions_to_add='';
	$the_awpcp_region_type='';
	$the_awpcp_region_parent='';


	if( isset($_REQUEST['action']) && !empty($_REQUEST['action']) )
	{
		$action=$_REQUEST['action'];
	}

	if(isset($_REQUEST['disablemultipleregions']) && !empty($_REQUEST['disablemultipleregions']) )
	{

		if(isset($_REQUEST['awpcp_region_to_disable_enable']) && !empty($_REQUEST['awpcp_region_to_disable_enable']) )
		{
			$awpcp_regions_to_disable_enable=$_REQUEST['awpcp_region_to_disable_enable'];
		}

		if(!isset($awpcp_regions_to_disable_enable) || empty($awpcp_regions_to_disable_enable) )
		{
			$themessagetoprint = __( 'No regions were selected, therefore there is nothing to disable', 'awpcp-region-control' );
		} else {
			$awpcp_region_to_disable_enable_ID = array_filter( array_map( 'intval', $awpcp_regions_to_disable_enable ) );
			$awpcp_region_to_disable_enable_ids = join( ', ', $awpcp_region_to_disable_enable_ID );

			// Disable the regions
            $query = 'UPDATE ' . AWPCP_TABLE_REGIONS . ' SET `region_state` = 0 WHERE region_id IN (' . $awpcp_region_to_disable_enable_ids . ')';
            $wpdb->query( $query );

			awpcp_reconcile_regions();

			$themessagetoprint = __( 'The regions have been disabled', 'awpcp-region-control' );
		}

		$message="<div style=\"background-color: rgb(255, 251, 204);\" id=\"message\" class=\"updated fade\">$themessagetoprint</div>";
		$output .= awpcp_regions_paged_list($activeregion=1,$the_awpcp_regions_to_add='',$the_awpcp_region_type='',$the_awpcp_region_parent='',$the_awpcp_region_to_lookup='',$the_awpcp_regions_to_disable='',$the_awpcp_regions_to_enable='',$awpcp_region_disable_ads_handle='',$awpcp_region_enable_ads_handle='');
	}

	if(isset($_REQUEST['enablemultipleregions']) && !empty($_REQUEST['enablemultipleregions']) )
	{

		if(isset($_REQUEST['awpcp_region_to_disable_enable']) && !empty($_REQUEST['awpcp_region_to_disable_enable']) )
		{
			$awpcp_regions_to_disable_enable=$_REQUEST['awpcp_region_to_disable_enable'];
		}

		if(!isset($awpcp_regions_to_disable_enable) || empty($awpcp_regions_to_disable_enable) )
		{
			$themessagetoprint = __( 'No regions were selected, therefore there is nothing to enable', 'awpcp-region-control' );
		} else {
			$awpcp_region_to_disable_enable_ID = array_filter( array_map( 'intval', $awpcp_regions_to_disable_enable ) );
			$awpcp_region_to_disable_enable_ids= join( ', ', $awpcp_region_to_disable_enable_ID );

			// Enable the regions
            $query = 'UPDATE ' . AWPCP_TABLE_REGIONS . ' SET `region_state` = 1 WHERE region_id IN (' . $awpcp_region_to_disable_enable_ids . ')';
            $wpdb->query( $query );

			$themessagetoprint = __( 'The regions have been enabled.', 'awpcp-region-control' );
		}

		$message="<div style=\"background-color: rgb(255, 251, 204);\" id=\"message\" class=\"updated fade\">$themessagetoprint</div>";
		$output .= awpcp_regions_paged_list($activeregion=1,$the_awpcp_regions_to_add='',$the_awpcp_region_type='',$the_awpcp_region_parent='',$the_awpcp_region_to_lookup='',$the_awpcp_regions_to_disable='',$the_awpcp_regions_to_enable='',$awpcp_region_disable_ads_handle='',$awpcp_region_enable_ads_handle='');
	}

	if(isset($_REQUEST['addselectedregionstosidelist']) && !empty($_REQUEST['addselectedregionstosidelist']) )
	{

		if(isset($_REQUEST['awpcp_region_to_disable_enable']) && !empty($_REQUEST['awpcp_region_to_disable_enable']) )
		{
			$awpcp_regions_to_disable_enable=$_REQUEST['awpcp_region_to_disable_enable'];
		}

		if(!isset($awpcp_regions_to_disable_enable) || empty($awpcp_regions_to_disable_enable) )
		{
			$themessagetoprint = __( 'No regions were selected, therefore there is nothing to add to the sidelist', 'awpcp-region-control' );
		} else {
			$awpcp_region_to_disable_enable_ID = array_filter( array_map( 'intval', $awpcp_regions_to_disable_enable ) );
			$awpcp_region_to_disable_enable_ids = join( ', ', $awpcp_region_to_disable_enable_ID );

			// Add the region to the sidelist
            $query = 'UPDATE ' . AWPCP_TABLE_REGIONS . ' SET `region_sidelisted` = 1 WHERE region_id IN (' . $awpcp_region_to_disable_enable_ids . ')';
            $wpdb->query( $query );

			awpcp_regions_api()->clear_cache();

			$themessagetoprint = __( 'The regions have been added to the sidelist.', 'awpcp-region-control' );
		}

		$message = awpcp_print_message( $themessagetoprint );
		$output .= awpcp_regions_paged_list($activeregion=1,$the_awpcp_regions_to_add='',$the_awpcp_region_type='',$the_awpcp_region_parent='',$the_awpcp_region_to_lookup='',$the_awpcp_regions_to_disable='',$the_awpcp_regions_to_enable='',$awpcp_region_disable_ads_handle='',$awpcp_region_enable_ads_handle='');
	}

	if($action == 'addnewregion') {
		$the_awpcp_regions_to_add = awpcp_request_param('regions_addregions','');
		$the_awpcp_region_type = awpcp_request_param('regions_region_type', -1);
		$the_awpcp_region_parent = $the_awpcp_region_type == 1 ? 0 : awpcp_request_param('regions_region_parent', 0);

		// Handle error check
		if (!isset($the_awpcp_regions_to_add) || empty($the_awpcp_regions_to_add) || ($the_awpcp_region_type == -1 ) || ($the_awpcp_region_parent == -1))
		{
			$message = __('Either you did not enter any new regions to add or you did not select the region type or you did not select the region parent. Please correct the problem and try again.', 'awpcp-region-control' );
			$message = "<div style=\"background-color: rgb(255, 251, 204);\" id=\"message\" class=\"updated fade\">$message</div>";
			$output .= awpcp_regions_paged_list($activeregion='2',$the_awpcp_regions_to_add=$the_awpcp_regions_to_add,$the_awpcp_region_type=$the_awpcp_region_type,$the_awpcp_region_parent=$the_awpcp_region_parent,$the_awpcp_region_to_lookup='',$the_awpcp_regions_to_disable='',$the_awpcp_regions_to_enable='',$awpcp_region_disable_ads_handle='',$awpcp_region_enable_ads_handle='');

		} else if ($the_awpcp_region_type > 1 && $the_awpcp_region_parent == 0) {
			$message = __('Only Continent regions can have no parent.', 'awpcp-region-control' );
			$message = "<div style=\"background-color: rgb(255, 251, 204);\" id=\"message\" class=\"updated fade\">$message</div>";
			$output .= awpcp_regions_paged_list($activeregion='2',$the_awpcp_regions_to_add=$the_awpcp_regions_to_add,$the_awpcp_region_type=$the_awpcp_region_type,$the_awpcp_region_parent=$the_awpcp_region_parent,$the_awpcp_region_to_lookup='',$the_awpcp_regions_to_disable='',$the_awpcp_regions_to_enable='',$awpcp_region_disable_ads_handle='',$awpcp_region_enable_ads_handle='');

		} else {
			// Insert new regions
			$regionstoaddarray=explode("\n",$the_awpcp_regions_to_add);

			for ($i=0; isset($regionstoaddarray[$i]); ++$i) {
				$regionstoaddarray[ $i ] = trim( stripslashes( $regionstoaddarray[ $i ] ) );

				if(!region_already_exists($regionstoaddarray[$i],$the_awpcp_region_parent)) {
					global $wpdb;
					// even though data is already escaped by WordPress
					// all region control module assumes data is escaped again.
					$data = array('region_type' => $the_awpcp_region_type,
								  'region_state' => 1,
								  'region_name' => $regionstoaddarray[$i],
								  'region_parent' => $the_awpcp_region_parent);
					$result = $wpdb->insert(AWPCP_TABLE_REGIONS, $data);

					if ( $result !== false ) {
						awpcp_regions_api()->clear_cache();
					}
				}
			}

			$message = "<div style=\"background-color: rgb(255, 251, 204);\" id=\"message\" class=\"updated fade\">" . __( 'The new regions have been added.', 'awpcp-region-control' ) . "</div>";
			$output .= awpcp_regions_paged_list($activeregion=1,$the_awpcp_regions_to_add='',$the_awpcp_region_type='',$the_awpcp_region_parent='',$the_awpcp_region_to_lookup='',$the_awpcp_regions_to_disable='',$the_awpcp_regions_to_enable='',$awpcp_region_disable_ads_handle='',$awpcp_region_enable_ads_handle='');

		}
	}

	elseif($action == 'disableregion')
	{
		if(isset($_REQUEST['regionid']) )
		{
			$regionid=$_REQUEST['regionid'];

			if( $regionid == 'all' ) {
				$result = $wpdb->query( 'UPDATE ' . AWPCP_TABLE_REGIONS . ' SET region_state = 0' );
			} else {
				$query = 'UPDATE ' . AWPCP_TABLE_REGIONS . ' SET `region_state` = 0 ';
				$query.= 'WHERE region_id = %d OR region_parent = %d';

				$wpdb->query( $wpdb->prepare( $query, $regionid, $regionid ) );
			}

			awpcp_reconcile_regions();

			$message="<div style=\"background-color: rgb(255, 251, 204);\" id=\"message\" class=\"updated fade\">" . __( 'The region has been disabled.', 'awpcp-region-control' ) . "</div>";
			$output .= awpcp_regions_paged_list($activeregion=1,$the_awpcp_regions_to_add='',$the_awpcp_region_type='',$the_awpcp_region_parent='',$the_awpcp_region_to_lookup='',$the_awpcp_regions_todisable='',$the_awpcp_regions_to_enable='',$awpcp_region_disable_ads_handle='',$awpcp_region_enable_ads_handle='');
		} else {

			if( isset($_REQUEST['regions_disableregions']) )
			{
				$the_awpcp_regions_to_disable = addslashes_mq($_REQUEST['regions_disableregions']);
			}
			if( isset($_REQUEST['region_disable_ads_handle']) )
			{
				$the_awpcp_region_disable_ads_handle = intval($_REQUEST['region_disable_ads_handle']);
			}
			if( isset($_REQUEST['regions_region_parent']) )
			{
				$the_awpcp_region_parent = intval($_REQUEST['regions_region_parent']);
			}

			// Handle error check
			if( empty($the_awpcp_regions_to_disable) || empty($the_awpcp_region_disable_ads_handle) || empty($the_awpcp_region_parent) )
			{
				$message="<div style=\"background-color: rgb(255, 251, 204);\" id=\"message\" class=\"updated fade\">" . __( 'Something is missing. Make sure you have entered at least 1 region to disable, selected the region parent ID and indicated what you would like to do with any ads that have this region as part of their location.', 'awpcp-region-control' ) . "</div>";
				$output .= awpcp_regions_paged_list($activeregion='3',$the_awpcp_regions_to_add='',$the_awpcp_region_type='',$the_awpcp_region_parent='',$the_awpcp_region_to_lookup='',$the_awpcp_regions_to_disable=$the_awpcp_regions_to_disable,$the_awpcp_regions_to_enable='',$awpcp_region_disable_ads_handle=$the_awpcp_region_disable_ads_handle,$awpcp_region_enable_ads_handle='');
			}
			else {
				// disble the regions
				$regionstodisablearray=explode("\n",$the_awpcp_regions_to_disable);

				for ( $i = 0; isset($regionstodisablearray[$i]); ++$i ) {
					if ( region_already_exists($regionstodisablearray[$i],$the_awpcp_region_parent) ) {
						$query = 'UPDATE ' . AWPCP_TABLE_REGIONS . ' SET `region_state`=0 WHERE region_name=%s AND region_parent=%d';
						$query = $wpdb->prepare( $query, $regionstodisablearray[ $i ], $the_awpcp_region_parent );

						$wpdb->query( $query );
					}

					if ( $the_awpcp_region_disable_ads_handle == 1 ) {
						$api = awpcp_regions_api();

						$region = $api->find_by_name( $regionstodisablearray[$i] );

						$conditions[] = $api->sql_where( $region->region_id );
						$conditions[] = 'disabled = 0';

						$ads = AWPCP_Ad::query( array( 'where' => join( ' AND ', $conditions ) ) );

						foreach ( $ads as $ad ) {
							$ad->disable();
						}
					}
				}

				awpcp_reconcile_regions();

				$message="<div style=\"background-color: rgb(255, 251, 204);\" id=\"message\" class=\"updated fade\">" . __( 'The regions have been disabled.', 'awpcp-region-control' ) . "</div>";
				$output .= awpcp_regions_paged_list($activeregion=1,$the_awpcp_regions_to_add='',$the_awpcp_region_type='',$the_awpcp_region_parent='',$the_awpcp_region_to_lookup='',$the_awpcp_regions_todisable='',$the_awpcp_regions_to_enable='',$awpcp_region_disable_ads_handle='',$awpcp_region_enable_ads_handle='');
			}
		}
	}
	elseif($action == 'enableregion')
	{
		if(isset($_REQUEST['regionid']) )
		{
			$regionid=$_REQUEST['regionid'];

			if ( $regionid == 'all' ) {
				$query = 'UPDATE ' . AWPCP_TABLE_REGIONS . ' SET `region_state` = 1';
				$result = $wpdb->query( $query );
			} else {
				$query = 'UPDATE ' . AWPCP_TABLE_REGIONS . ' SET `region_state`=1 WHERE region_id = %d';
				$result = $wpdb->query( $wpdb->prepare( $query, $regionid ) );
			}

			if ( $result ) {
				$message = awpcp_print_message( __( "The region has been enabled.", 'awpcp-region-control' ) );
			} else {
				$message = awpcp_print_error( __( "The region couldn't be enabled due to a database error.", 'awpcp-region-control' ) );
			}

			$output .= awpcp_regions_paged_list($activeregion=1,$the_awpcp_regions_to_add='',$the_awpcp_region_type='',$the_awpcp_region_parent='',$the_awpcp_region_to_lookup='',$the_awpcp_regions_todisable='',$the_awpcp_regions_to_enable='',$awpcp_region_disable_ads_handle='',$awpcp_region_enable_ads_handle='');
		}
	}
	elseif($action == 'editregion') {
		if (isset($_REQUEST['regionid']) && !empty($_REQUEST['regionid'])) {
			$theregionid=$_REQUEST['regionid'];
		} else  {
			$theregionid = '';
			$message = awpcp_print_error( __( 'Action aborted: there was no region ID supplied.', 'awpcp-region-control' ) );
		}

		$output .= awpcp_regions_paged_list($activeregion=1,$the_awpcp_regions_to_add='',$the_awpcp_region_type='',$the_awpcp_region_parent='',$the_awpcp_region_to_lookup=$theregionid,$the_awpcp_regions_todisable='',$the_awpcp_regions_to_enable='',$awpcp_region_disable_ads_handle='',$awpcp_region_enable_ads_handle='');
	}
	elseif($action == 'lookupregion') {

		if( isset($_REQUEST['regions_region_name']) ) {
			$the_awpcp_region_name_to_lookup = addslashes_mq($_REQUEST['regions_region_name']);
		}

		if(!isset($the_awpcp_region_name_to_lookup) || empty($the_awpcp_region_name_to_lookup) )
		{
			$message="<div style=\"background-color: rgb(255, 251, 204);\" id=\"message\" class=\"updated fade\">" . __( 'No region name was entered. Please enter a region name to continue', 'awpcp-region-control' ) . "</div>";
			$output .= awpcp_regions_paged_list($activeregion=1,$the_awpcp_regions_to_add='',$the_awpcp_region_type='',$the_awpcp_region_parent='',$the_awpcp_region_to_lookup='',$the_awpcp_regions_todisable='',$the_awpcp_regions_to_enable='',$awpcp_region_disable_ads_handle='',$awpcp_region_enable_ads_handle='');

		} else {
			$query = "SELECT region_id FROM " . AWPCP_TABLE_REGIONS . " ";
			$query.= "WHERE region_name LIKE %s";
			$query = $wpdb->prepare($query, "%$the_awpcp_region_name_to_lookup%");

			$thegeionidstolookup = $wpdb->get_col( $query );

			if ( $thegeionidstolookup !== false && ! empty( $thegeionidstolookup ) ) {
				$output .= awpcp_regions_paged_list($activeregion=1,$the_awpcp_regions_to_add='',$the_awpcp_region_type='',$the_awpcp_region_parent='',$the_awpcp_region_to_lookup=$thegeionidstolookup,$the_awpcp_regions_todisable='',$the_awpcp_regions_to_enable='',$awpcp_region_disable_ads_handle='',$awpcp_region_enable_ads_handle='');
			} else {
				$message = __( 'There was no region found by the name entered: <region-name>', 'awpcp-region-control' );
				$message = str_replace( '<reigon-name>', '<strong>' . $the_awpcp_region_name_to_lookup . '</strong>', $message );

				$message = awpcp_print_error( $message );

				$output .= awpcp_regions_paged_list($activeregion=1,$the_awpcp_regions_to_add='',$the_awpcp_region_type='',$the_awpcp_region_parent='',$the_awpcp_region_to_lookup='',$the_awpcp_regions_todisable='',$the_awpcp_regions_to_enable='',$awpcp_region_disable_ads_handle='',$awpcp_region_enable_ads_handle='');
			}
		}
	}

	elseif($action == 'localizemodule')
	{

		if( !isset($_REQUEST['awpcp_region_for_localization']) || empty($_REQUEST['awpcp_region_for_localization']) )
		{
            $message = __( 'Please begin the localization process by setting the continent or countries.', 'awpcp-region-control' );
            $message = awpcp_print_message( $message );

			$output .= awpcp_regions_paged_list($activeregion='5',$the_awpcp_regions_to_add='',$the_awpcp_region_type='',$the_awpcp_region_parent='',$the_awpcp_region_to_lookup='',$the_awpcp_regions_to_disable='',$the_awpcp_regions_to_enable='',$awpcp_region_disable_ads_handle='',$awpcp_region_enable_ads_handle='');
		}

		elseif( isset($_REQUEST['awpcp_region_for_localization']) )
		{

			$awpcp_regions_for_localization=addslashes_mq($_REQUEST['awpcp_region_for_localization']);

			foreach( $awpcp_regions_for_localization as $awpcp_region_for_localization ) {
				$awpcpregionforlocalization[]=$awpcp_region_for_localization;
			}

            $idsawpcpregionforlocalization = join( ', ',$awpcpregionforlocalization );

            // Next localize the module according to the submitted data
            $query = 'UPDATE ' . AWPCP_TABLE_REGIONS . ' SET region_localized = 1 WHERE region_id IN (' . $idsawpcpregionforlocalization . ')';
            $wpdb->query( $query );

            awpcp_regions_api()->clear_cache();

            $message = __( 'The module has been localized. You now need to enable subregions.', 'awpcp-region-control' );
            $message = awpcp_print_message( $message );

			$output .= awpcp_regions_paged_list($activeregion='5',$the_awpcp_regions_to_add='',$the_awpcp_region_type='',$the_awpcp_region_parent=$awpcp_regions_for_localization,$the_awpcp_region_to_lookup='',$the_awpcp_regions_to_disable='',$the_awpcp_regions_to_enable='',$awpcp_region_disable_ads_handle='',$awpcp_region_enable_ads_handle='');

		}
	} elseif( $action == 'delocalize' ) {
		$query = 'UPDATE ' . AWPCP_TABLE_REGIONS . ' SET `region_localized` = 0 WHERE region_localized = 1';
		$wpdb->query( $query );

		awpcp_regions_api()->clear_cache();

		$message = __( 'Localization has been unset.', 'awpcp-region-control' );
		$message = awpcp_print_message( $message );

		$output .= awpcp_regions_paged_list($activeregion=1,$the_awpcp_regions_to_add='',$the_awpcp_region_type='',$the_awpcp_region_parent='',$the_awpcp_region_to_lookup='',$the_awpcp_regions_to_disable='',$the_awpcp_regions_to_enable='',$awpcp_region_disable_ads_handle='',$awpcp_region_enable_ads_handle='');

	} elseif( $action == 'updateregiondata' ) {
		$region_to_edit = '';

		if (isset($_REQUEST['regions_region_id']) && !empty($_REQUEST['regions_region_id'])) {
			$regions_region_id=intval($_REQUEST['regions_region_id']);
		}

		if( isset($regions_region_id) && !empty($regions_region_id) )
		{
			$regions_region_name = awpcp_request_param( 'regions_region_name' );
			$regions_region_state = intval(awpcp_request_param('regions_region_state', 0));
			$regions_region_type = intval(awpcp_request_param('regions_region_type'));
			$regions_region_parent = $regions_region_type == 1 ? 0 : intval(awpcp_request_param('regions_region_parent'));

			// Only Continents (region_type == 1) are allowed to have region_parent = 0
			if ($regions_region_parent > 0 || $regions_region_type === 1) {
				$data = array(
					'region_name' => stripslashes_deep( $regions_region_name ),
					'region_type' => $regions_region_type,
					'region_parent' => $regions_region_parent,
					'region_state' => $regions_region_state,
				);

				global $wpdb;
				$result = $wpdb->update( AWPCP_TABLE_REGIONS, $data, array( 'region_id' => $regions_region_id ) );

				if ( $result !== false ) {
					awpcp_regions_api()->clear_cache();
				}

				// TODO: Use awpcp_flash()?
				$message = _x('The Region data has been updated.', 'Edit Region success message', 'awpcp-region-control' );
			} else {
				$message = _x("You must select a Parent Region. The Region couldn't be updated.", 'Edit Region error message', 'awpcp-region-control' );
				$region_to_edit = $regions_region_id;
			}

		} else {
			$message = _x('Unable to update the Region data due to missing Region ID.', 'Edit Region error message', 'awpcp-region-control' );
		}

		$message = sprintf('<div style="background-color: rgb(255, 251, 204);" id="message" class="updated fade">%s</div>', $message);
		$output .= awpcp_regions_paged_list($activeregion=1,$the_awpcp_regions_to_add='',$the_awpcp_region_type='',$the_awpcp_region_parent='',$the_awpcp_region_to_lookup=$region_to_edit,$the_awpcp_regions_to_disable='',$the_awpcp_regions_to_enable='',$awpcp_region_disable_ads_handle='',$awpcp_region_enable_ads_handle='');

	}
	elseif( $action == 'addtosidelist')
	{

		if(isset($_REQUEST['regionid']) && !empty($_REQUEST['regionid']) )
		{
			$theregionid=$_REQUEST['regionid'];
		}

		$query = 'UPDATE ' . AWPCP_TABLE_REGIONS . ' SET `region_sidelisted` = 1 WHERE region_id = %d';
		$query = $wpdb->prepare( $query, $theregionid );

		$wpdb->query( $query );

		$message= awpcp_print_message( __( 'The region has been added to the sidelist.', 'awpcp-region-control' ) );
		$output .= awpcp_regions_paged_list($activeregion=1,$the_awpcp_regions_to_add='',$the_awpcp_region_type='',$the_awpcp_region_parent='',$the_awpcp_region_to_lookup='',$the_awpcp_regions_to_disable='',$the_awpcp_regions_to_enable='',$awpcp_region_disable_ads_handle='',$awpcp_region_enable_ads_handle='');

		awpcp_regions_api()->clear_cache();
	}
	elseif( $action == 'removefromsidelist')
	{

		if(isset($_REQUEST['regionid']) && !empty($_REQUEST['regionid']) )
		{
			$theregionid=$_REQUEST['regionid'];
		}

		$query = 'UPDATE ' . AWPCP_TABLE_REGIONS . ' SET `region_sidelisted` = 0 WHERE region_id = %d';
		$query = $wpdb->prepare( $query, $theregionid );

		$wpdb->query( $query );

		$message = awpcp_print_message( 'The region has been removed from the sidelist.', 'awpcp-region-control' );

		$output .= awpcp_regions_paged_list($activeregion=1,$the_awpcp_regions_to_add='',$the_awpcp_region_type='',$the_awpcp_region_parent='',$the_awpcp_region_to_lookup='',$the_awpcp_regions_to_disable='',$the_awpcp_regions_to_enable='',$awpcp_region_disable_ads_handle='',$awpcp_region_enable_ads_handle='');

		awpcp_regions_api()->clear_cache();
	}
	else
	{
		$output .= awpcp_regions_paged_list($activeregion=1,$the_awpcp_regions_to_add='',$the_awpcp_region_type='',$the_awpcp_region_parent='',$the_awpcp_region_to_lookup='',$the_awpcp_regions_to_disable='',$the_awpcp_regions_to_enable='',$awpcp_region_disable_ads_handle='',$awpcp_region_enable_ads_handle='');
	}
	$output .= "</div>";
	//Echo OK here
	echo $output;
}

function awpcp_region_control_update_ad_count() {
	wp_enqueue_script( 'awpcp-admin-manual-upgrade' );

    $tasks = array(
    	array(
			'name' => _x( 'Update Ad Count', 'region control upgrade', 'awpcp-region-control' ),
			'action' => 'awpcp-region-control-update-ad-count'
		),
    );

    $messages = array(
        'introduction' => __( 'Before you can use Region Control module we need to upgrade your database and update the Ad count for each region. Please press the Upgrade button shown below to start the process.', 'awpcp-region-control' ),
        'success' => sprintf( __( 'Congratulations. Your Region Control module has been successfully upgrated. You can now access the standard Manage Regions section. <a href="%s">Click here to Continue</a>.', 'awpcp-region-control' ), awpcp_current_url() ),
        'button' => _x( 'Upgrade', 'awpcp upgrade', 'awpcp-region-control' ),
    );

    $tasks = new AWPCP_AsynchronousTasksComponent( $tasks, $messages );
    $content = $tasks->render();

	include(AWPCP_REGION_CONTROL_MODULE_DIR . '/templates/admin-panel-upgrade.tpl.php');
}

/**
 * Make sure if a parent region is disabled the children of
 * the sub region are also disabled
 */
function awpcp_reconcile_regions() {
	global $wpdb;

	$disabled = array();

	//Reconcile children of continents
	$query = 'SELECT region_id FROM ' . AWPCP_TABLE_REGIONS . ' WHERE `region_type` = 1 AND region_state = 0';
	$disabled = array_merge( $disabled, $wpdb->get_col( $query ) );

	//Reconcile children of countries
	$query = 'SELECT region_id FROM ' . AWPCP_TABLE_REGIONS . ' WHERE `region_type` = 2 AND region_state = 0';
	$disabled = array_merge( $disabled, $wpdb->get_col( $query ) );

	//Reconcile children of states/towns
	$query = 'SELECT region_id FROM ' . AWPCP_TABLE_REGIONS . ' WHERE `region_type` = 3 AND region_state = 0';
	$disabled = array_merge( $disabled, $wpdb->get_col( $query ) );

	//Reconcile children of cities
	$query = 'SELECT region_id FROM ' . AWPCP_TABLE_REGIONS . ' WHERE `region_type`= 4 AND region_state = 0';
	$disabled = array_merge( $disabled, $wpdb->get_col( $query ) );

	if (!empty($disabled)) {
		$query = 'UPDATE ' . AWPCP_TABLE_REGIONS . ' ';
		$query.= 'SET region_state = 0 ';
		$query.= 'WHERE region_parent IN (' . join(',', $disabled) . ')';

		$wpdb->query( $query );
	}

	// Fix sidelist for localization
	if(has_localized_regions()){
		$awpcplocaliedrids=get_localized_region_ids();
		$awpcplocaliedridstc = join( ', ' ,$awpcplocaliedrids );

		$query = 'UPDATE ' . AWPCP_TABLE_REGIONS . ' SET region_sidelisted = 0 WHERE region NOT IN (' . $awpcplocaliedridstc . ')';
		$wpdb->query( $query );
	}
}

function awpcp_regions_paged_list($activeregion,$the_awpcp_regions_to_add,$the_awpcp_region_type,$the_awpcp_region_parent,$the_awpcp_region_to_lookup,$the_awpcp_regions_to_disable,$the_awpcp_regions_to_enable,$awpcp_region_disable_ads_handle,$awpcp_region_enable_ads_handle)
{
	global $wpdb, $message, $awpcp_imagesurl;

	$output = '';
	$tbl_ad_regions = $wpdb->prefix . "awpcp_regions";
	$awpcpregslocalizedmessage='';
	$theawpcpregiontypeforsort='';
	$theawpcpregionidtosortby='';

	$offset=(isset($_REQUEST['offset'])) ? (addslashes_mq($_REQUEST['offset'])) : ($offset=0);
	$results=(isset($_REQUEST['results']) && !empty($_REQUEST['results'])) ? addslashes_mq($_REQUEST['results']) : ($results=10);

	///////////////////////////////////////////////////////////
	// Show the paginated regions list for management
	//////////////////////////////////////////////////////////

	$from="$tbl_ad_regions";

	if( isset($the_awpcp_region_to_lookup) && !empty($the_awpcp_region_to_lookup) )
	{
		if( !is_array($the_awpcp_region_to_lookup) )
		{
			$output .= load_region_edit_form($the_awpcp_region_to_lookup);
		}
		else
		{
			foreach($the_awpcp_region_to_lookup as $the_awpcp_regiontolookup)
			{
				$regionstolookuplistitems[]=$the_awpcp_regiontolookup;

				//unset($the_awpcp_region_to_lookup);
			}
			if( isset($_REQUEST['awpcpregshowops']) && !empty($_REQUEST['awpcpregshowops']) )
			{
				unset($_REQUEST['awpcpregshowops']);
			}
			$regionstolookuparray=join("','",$regionstolookuplistitems);
			$where="region_id IN ('$regionstolookuparray')";
		}
	}
	if( isset($_REQUEST['sortbyregion']) && !empty($_REQUEST['sortbyregion']) )
	{
		$theawpcpregionidtosortby=$_REQUEST['sortbyregion'];
		if(isset($theawpcpregionidtosortby) && !empty($theawpcpregionidtosortby)){
			$theawpcpregiontypeforsort=get_theawpcpregiontype($theawpcpregionidtosortby);
		}

		$where="region_parent ='$theawpcpregionidtosortby'";
		$awpcpregionsorderby="ORDER BY region_name ASC";
		$awpcpregionsgroupby="";
	}

	if( isset($_REQUEST['awpcpregshowops']) && !empty($_REQUEST['awpcpregshowops']) )
	{
		$awpcpregshowops=$_REQUEST['awpcpregshowops'];

		if($awpcpregshowops == 'showdisabledonly')
		{
			$regions_showhideoptions="<a href=\"?page=Configure4&awpcpregshowops=showenabledonly\">" . __( 'Show Enabled Only', 'awpcp-region-control' ) . "</a> | <a href=\"?page=Configure4&awpcpregshowops=showallregions\">" . __( 'Show All', 'awpcp-region-control' ) . "</a>";
			$where=" region_state=0 ";
			if( has_localized_regions() )
			{
				$where.=" AND region_localized=1 ";
			}
			$awpcpregionsorderby="ORDER BY region_name";
			$awpcpregionsgroupby="GROUP BY region_id";
			$awpcpdisableenablemultiplebutton="<p><input type=\"submit\" name=\"enablemultipleregions\" class=\"button\" value=\"" . __( 'Enable Selected Regions', 'awpcp-region-control' ) . "\" /></p>";
		}
		elseif($awpcpregshowops == 'showenabledonly')
		{
			$regions_showhideoptions="<a href=\"?page=Configure4&awpcpregshowops=showdisabledonly\">" . __( 'Show Disabled Only', 'awpcp-region-control' ) . "</a> | <a href=\"?page=Configure4&awpcpregshowops=showallregions\">" . __( 'Show All', 'awpcp-region-control' ) . "</a>";
			$where="region_state=1";
			if( has_localized_regions() )
			{
				$where.=" AND region_localized=1 ";
			}
			$awpcpregionsorderby="ORDER BY region_name";
			$awpcpregionsgroupby="GROUP BY region_id";
		}
		elseif($awpcpregshowops == 'showallregions')
		{
			$regions_showhideoptions="<a href=\"?page=Configure4&awpcpregshowops=showdisabledonly\">" . __( 'Show Disabled Only', 'awpcp-region-control' ) . "</a> | <a href=\"?page=Configure4&awpcpregshowops=showenabledonly\">" . __( 'Show Enabled Only', 'awpcp-region-control' ) . "</a>";
			if( has_localized_regions() )
			{
				$where=" region_localized=1 ";
			}
			else
			{
				$where=" 1=1 ";
			}
			$awpcpregionsorderby="ORDER BY region_name ASC";
			$awpcpregionsgroupby="";
		}
	}

	if(!isset($regions_showhideoptions) || empty($regions_showhideoptions) )
	{
		$regions_showhideoptions="<a href=\"?page=Configure4&awpcpregshowops=showdisabledonly\">" . __( 'Show Disabled Only', 'awpcp-region-control' ) . "</a> | <a href=\"?page=Configure4&awpcpregshowops=showallregions\">" . __( 'Show All', 'awpcp-region-control' ) . "</a>";
	}

	if(!isset($where) || empty($where) )
	{
		if(has_localized_regions() )
		{
			$where=" region_localized=1 ";
			$awpcpregslocalizedmessage="<p>" . __( 'Your system is currently localized. When localized, only your localized regions will be displayed by default and your management links such as "Show Disabled Only", "Show enabled Only" and "Show All" will limit activity to your localized regions. To unset localization click the <strong>Localize Module</strong> tab then click the Unset Localization link.', 'awpcp-region-control' ) . "</p>";
		}
		else
		{
			$where=" region_state=1 ";
		}
	}


	$pager1=create_pager($from,$where,$offset,$results,$tpname='');
	$pager2=create_pager($from,$where,$offset,$results,$tpname='');

	$output .= "$message";

	$output .= "<div style=\"padding:5px;\"><h3>" . __( 'Region Management Tools', 'awpcp-region-control' ) . "</h3><br/>";

	if( isset($activeregion) && ($activeregion==1) )
	{
		$selected1="class=\"selected\"";
	}
	else
	{
		$selected1='';
	}
	if( isset($activeregion) && ($activeregion==2) )
	{
		$selected2="class=\"selected\"";
	}
	else
	{
		$selected2='';
	}

	if( isset($activeregion) && ($activeregion==3) )
	{
		$selected3="class=\"selected\"";
	}
	else
	{
		$selected3='';
	}

	if( isset($activeregion) && ($activeregion==4) )
	{
		$selected4="class=\"selected\"";
	}
	else
	{
		$selected4='';
	}

	if( isset($activeregion) && ($activeregion==5) )
	{
		$selected5="class=\"selected\"";
	}
	else
	{
		$selected5='';
	}

	if(!isset($activeregion) )
	{
		$selected1="class=\"selected\"";
		$selected2='';
		$selected3='';
		$selected4='';
		$selected5='';
	}

	$output .= "	<div id=\"tabbedmanager\">
										<ul class=\"region-manager-options\">
											<li id=\"tab1\" $selected1><a href=\"#\" onclick=\"javascript:region_config_option(1);\">" . __( 'Look Up Region', 'awpcp-region-control' ) . "</a></li>
											<li id=\"tab2\" $selected2><a href=\"#\" onclick=\"javascript:region_config_option(2);\">" . __( 'Add Region(s)', 'awpcp-region-control' ) . "</a></li>
											<li id=\"tab3\" $selected3><a href=\"#\" onclick=\"javascript:region_config_option(3);\">" . __( 'Disable Region(s)', 'awpcp-region-control' ) . "</a></li>
											<li id=\"tab4\" $selected4><a href=\"#\" onclick=\"javascript:region_config_option(4);\">" . __( 'Enable Region(s)', 'awpcp-region-control' ) . "</a></li>
											<li id=\"tab5\" $selected5><a href=\"#\" onclick=\"javascript:region_config_option(5);\">" . __( 'Localize Module', 'awpcp-region-control' ) . "</a></li>


										</ul>

								<!--Begin form for Look Up Region-->
								<div id=\"lookupregion\"";
	if( isset($activeregion) && ($activeregion==1) )
	{
		$output .= "style=\"display:block;padding:5px;\">";
	}
	elseif(!isset($activeregion) )
	{
		$output .= "style=\"display:block;padding:5px;\">";
	}
	elseif( isset($activeregion) && ($activeregion != 1) )
	{
		$output .= "style=\"display:none;padding:5px;\">";
	}

	if( isset($the_awpcp_region_to_lookup) && is_numeric($the_awpcp_region_to_lookup) )
	{
		unset($the_awpcp_region_to_lookup);
	}

	$output .= "
								<form method=\"post\" action=\"\" id=\"awpcp_opsconfig_regions\">
								  <div>
								  <input type=\"hidden\" name=\"regionopt\" id=\"regionopt\" />
								  <input type=\"hidden\" name=\"action\" value=\"lookupregion\" />
									<input type=\"text\" name=\"regions_region_name\" size=\"27\" value=\"";
	global $the_awpcp_region_to_lookup;
	if(isset($the_awpcp_region_to_lookup) && !empty($the_awpcp_region_to_lookup)) {
		$output .= stripslashes($the_awpcp_lookupregion);
	}
	$output .= "\" />
									<input type=\"submit\" class=\"button\" value=\"" . __( 'Find Region', 'awpcp-region-control' ) . "\" />

								  </div>
								</form>
								</div>
								<!--End form for Look Up Region-->

								<!--Begin form Add Region-->
								<div id=\"addregion\"";
	if( isset($activeregion) && ($activeregion==2) )
	{
		$output .= "style=\"display:block;padding:5px;\">";
	}
	else
	{
		$output .= "style=\"display:none;padding:5px;\">";
	}
	$output .= "<p>" . __( '' ) . "</p>
								<form method=\"post\" action=\"\" id=\"awpcp_opsconfig_regions\">
									<div>
									<input type=\"hidden\" name=\"regionopt\" id=\"regionopt\" />
									<input type=\"hidden\" name=\"action\" value=\"addnewregion\" />
										<textarea name=\"regions_addregions\" style=\"width:100%;height:200px;\">$the_awpcp_regions_to_add</textarea>";

	if(isset($the_awpcp_region_type) && ! empty($the_awpcp_region_type) )
	{
		$selected1='';
		$selected2='';
		$selected3='';
		$selected4='';
		$selected5='';

		if($the_awpcp_region_type == 1)
		{
			$selected1=" selected='selected'";
		}

		elseif($the_awpcp_region_type == 2)
		{
			$selected2=" selected='selected'";
		}

		if($the_awpcp_region_type == 3)
		{
			$selected3=" selected='selected'";
		}

		if($the_awpcp_region_type == 4)
		{
			$selected4=" selected='selected'";
		}

		if($the_awpcp_region_type == 5)
		{
			$selected6=" selected='selected'";
		}
	}

	$output .= "								<p class=\"awpcp-message updated\">" . __( 'For Regions with type other than Continent, please start typing the name of the parent region and wait for the autocomplete field to show you the available options. Then choose the option you want.', 'awpcp-region-control' ) . "</p>
												<label>" . __( 'Region Type', 'awpcp-region-control' ) . " </label>
												<select name=\"regions_region_type\">
													<option value=\"-1\">" . __( 'Select Region Type', 'awpcp-region-control' ) . "</option>
													<option value=\"1\" $selected1>" . __( 'Continent', 'awpcp-region-control' ) . "</option>
													<option value=\"2\" $selected2>" . __( 'Country', 'awpcp-region-control' ) . "</option>
													<option value=\"3\" $selected3>" . __( 'State/Town', 'awpcp-region-control' ) . "</option>
													<option value=\"4\" $selected4>" . __( 'City', 'awpcp-region-control' ) . "</option>
													<option value=\"5\" $selected5>" . __( 'County/Village/Other', 'awpcp-region-control' ) . "</option>
												</select>

												<label>Region Parent </label>
												<input class=\"required\" name=\"regions_region_parent\" type=\"hidden\" />
												<input name=\"regions_region_parent_name\" type=\"text\" />";

	$output .= "								<input type=\"submit\" class=\"button\" value=\"" . __( 'Add Region(s)', 'awpcp-region-control' ) . "\" />
										</div>
									</form>
								</div>
								<!--End form add region-->

								<!--Begin form disable region-->
								<div id=\"disableregion\"";
	if( isset($activeregion) && ($activeregion==3) )
	{
		$output .= "style=\"display:block;padding:5px;\">";
	}
	else
	{
		$output .= "style=\"display:none;padding:5px;\">";
	}

	$awpcp_region_checked_disabled1='';
	$awpcp_region_checked_disabled2='';

	if(isset($awpcp_region_disable_ads_handle) && ($awpcp_region_disable_ads_handle == 1) )
	{
		$awpcp_region_checked_disabled1="checked";
	}

	elseif(isset($awpcp_region_disable_ads_handle) && ($awpcp_region_disable_ads_handle == 2) )
	{
		$awpcp_region_checked_disabled2="checked";
	}
	$output .= "<p>" . __( '', 'awpcp-region-control' ) . "</p>
									<p><a href=\"?page=Configure4&action=disableregion&regionid=all\">" . __( 'Disable All', 'awpcp-region-control' ) . "</a></p>
								<form method=\"post\" action=\"\" id=\"awpcp_opsconfig_regions\">
									<div>
									<input type=\"hidden\" name=\"regionopt\" id=\"regionopt\" />
									<input type=\"hidden\" name=\"action\" value=\"disableregion\" />
									<textarea name=\"regions_disableregions\" style=\"width:100%;height:200px;\">$the_awpcp_regions_to_disable</textarea>";
	if(isset($the_awpcp_region_type) && ! empty($the_awpcp_region_type) )
	{
		$selected1='';
		$selected2='';
		$selected3='';
		$selected4='';
		$selected5='';

		if($the_awpcp_region_type == 1)
		{
			$selected1=" selected='selected'";
		}

		elseif($the_awpcp_region_type == 2)
		{
			$selected2=" selected='selected'";
		}

		if($the_awpcp_region_type == 3)
		{
			$selected3=" selected='selected'";
		}

		if($the_awpcp_region_type == 4)
		{
			$selected4=" selected='selected'";
		}

		if($the_awpcp_region_type == 5)
		{
			$selected6=" selected='selected'";
		}
	}
	$output .= "						<p class=\"awpcp-message updated\">" . __( 'To choose a region parent, please start typing the name of the parent region and wait for the autocomplete field to show you the available options. Then choose the option you want.', 'awpcp-region-control' ) . "</p>
										<p>
											<label>" . __( 'Region Parent', 'awpcp-region-control' ) . " </label>
											<input class=\"required\" name=\"regions_region_parent\" type=\"hidden\" />
											<input name=\"regions_region_parent_name\" type=\"text\" />";

	$output .= "						</p>
										<p>
											<label>" . __( 'What should be done with ads that have this region as part of their location?', 'awpcp-region-control' ) . "</label>
										</p>
										<p>
											<input type=\"radio\" name=\"region_disable_ads_handle\" value=\"1\" $awpcp_region_checked_disabled1 /> " . __( 'Disable the ads', 'awpcp-region-control' ) . "
											<input type=\"radio\" name=\"region_disable_ads_handle\" value=\"2\" $awpcp_region_checked_disabled2 /> " . __( 'Do not disable the ads', 'awpcp-region-control' ) . "
										</p>
										<input type=\"submit\" class=\"button\" value=\"" . __( 'Disable Region(s)', 'awpcp-region-control' ) . "\" />
									</div>
								</form>
								</div>
								<!--End form disable region-->

								<!--Begin form enable region-->
								<div id=\"enableregion\"";
	if( isset($activeregion) && ($activeregion==4) )
	{
		$output .= "style=\"display:block;padding:5px;\">";
	}
	else
	{
		$output .= "style=\"display:none;padding:5px;\">";
	}

	$output .= "<p>" . __( 'You can use this form to enable a single region or to enable multiple regions. If working with multiple regions please enter each region on its own line.', 'awpcp-region-control' ) . "</p>
								<p><a href=\"?page=Configure4&action=enableregion&regionid=all\">" . __( 'Enable All', 'awpcp-region-control' ) . "</a></p>
								<form method=\"post\" action=\"\" id=\"awpcp_opsconfig_regions\">
									<div>
									<input type=\"hidden\" name=\"regionopt\" id=\"regionopt\" />
									<input type=\"hidden\" name=\"action\" value=\"enableregion\" />
									<textarea name=\"regions_disableregions\" style=\"width:100%;height:200px;\"></textarea>
									<p><label>" . __( 'What should be done with ads that have this region as part of their location?', 'awpcp-region-control' ) . "</label></p>
									<p><input type=\"radio\" name=\"region_enable_ads_handle\" value=\"1\" />" . __( 'Enable the ads if disabled', 'awpcp-region-control' ) . " <input type=\"radio\" name=\"region_disable_ads_handle\" value=\"2\" />" . __( 'Do not enable the ads if disabled', 'awpcp-region-control' ) . "</p>
									<input type=\"submit\" class=\"button\" value=\"" . __( 'Enable Region(s)', 'awpcp-region-control' ) . "\" />
									</div>
								</form>
								</div>
								<!--End form enable region-->

								<!--Begin form localize module-->
								<div id=\"localizemodule\"";
	if( isset($activeregion) && ($activeregion==5) )
	{
		$output .= "style=\"display:block;padding:5px;\">";
	}
	else
	{
		$output .= "style=\"display:none;padding:5px;\">";
	}

	$output .= "<p>" . __( 'You can use this form to localize the module. Localizing enables locations for a specific region of the world. You can localize to a specific continent, a specific country, a specific state/town a specific city or a specific county.', 'awpcp-region-control' ) . "</p>";

	if( has_localized_regions() )
	{
		$output .= "<p>" . __( 'Your module is currently localized.', 'awpcp-region-control' ) . " <a href=\"?page=Configure4&action=delocalize\">" . __( 'Unset Localization', 'awpcp-region-control' ) . "</a></p>";
	}

	$output .=  "<form name=\"myregionslocalize\" method=\"post\" action=\"\" id=\"myregionslocalize\">
									<div>
									<input type=\"hidden\" name=\"regionopt\" id=\"regionopt\" />
									<input type=\"hidden\" name=\"action\" value=\"localizemodule\" />";


	if( isset( $the_awpcp_region_parent ) && ( $the_awpcp_region_parent > 0 ) ) {
		if ( is_array( $the_awpcp_region_parent ) ) {
			$region_id = reset( $the_awpcp_region_parent );
		} else {
			$region_id = $the_awpcp_region_parent;
		}

		$theawpcpcontinent = get_theawpcpregionparentname( $region_id );
		$output .= "<p><b>$theawpcpcontinent</b></p>";
	}

	elseif( has_localized_regions() )
	{

		$the_awpcp_region_parent=get_localized_region_ids();

	}

	else
	{
		$output .= "

										<label><b>" . __( 'Select Continent', 'awpcp-region-control' ) . "</b></label><p>";

		$output .= awpcp_region_create_list_checkboxes($awpcpregiontypeval=1,$awpcp_region_for_localization='');

		$output .= "</select></p>";
	}


	if(isset($the_awpcp_region_parent) && !empty($the_awpcp_region_parent) )
	{


		$output .= "<p><input type=\"checkbox\" onclick=\"awpcp_localize_region_toggle_visibility('awpcp_country_input_showhide');\" />" . __( 'Check box to enable sub regions for the primary areas of localization', 'awpcp-region-control' ) . "</p>
												<div id=\"awpcp_country_input_showhide\" style=\"display:none;\">";

		$output .= awpcp_region_create_list_checkboxes($awpcpregiontypeval=2,$awpcp_region_for_localization=$the_awpcp_region_parent);

		$output .= "</div>";
	}

	$output .= "</div>

									<input type=\"submit\" class=\"button\" value=\"" . __( 'Continue', 'awpcp-region-control' ) . "\" />
									</div>
								</form>
								</div>
								<!--End form localize module-->

								</div><!--close div tabbedmanager-->";
	$output .= "</div><!--close div management tools-->";

	$items=array();

	if( !isset($awpcpregionsorderby) || empty($awpcpregionsorderby) )
	{
		$awpcpregionsorderby="ORDER BY region_name ASC";

	}

	if( !isset($awpcpregionsgroupby) || empty($awpcpregionsgroupby) )
	{
		$awpcpregionsgroupby="";

	}

	$query = 'SELECT region_id, region_type, region_state, region_name, region_parent, region_sidelisted ';
	$query.= 'FROM ' . AWPCP_TABLE_REGIONS . ' WHERE ';
	$query.= "$where $awpcpregionsgroupby $awpcpregionsorderby LIMIT $offset,$results";

	$query_results = $wpdb->get_results( $query );

	if ( empty( $query_results ) ) {
		$output .= "<div style=\"clear:both\"></div><p>" . __( 'There were no regions found matching your query.', 'awpcp-region-control' ) . "</p><p> <a href=\"?page=Configure4\">" . __( 'Reload Regions', 'awpcp-region-control' ) . "</a><p>";
		return $output;
	}

	foreach ( $query_results as $result ) {
		$awpcp_region_id = $result->region_id;
		$awpcp_region_type = $result->region_type;
		$awpcp_region_state = $result->region_state;
		$awpcp_region_name = "<a href=\"?page=Configure4&sortbyregion=" . $result->region_id . "\">" . stripslashes_deep( $result->region_name ) . "</a>";
		$awpcp_region_name_nolink = $result->region_name;
		$awpcp_region_parent_id = $result->region_parent;
		$awpcp_region_featured_status = $result->region_sidelisted;

		if($awpcp_region_parent_id == 0) {
			$awpcp_region_parent_name = '–';
		} else {
			$awpcp_region_parent_name=get_theawpcpregionparentname($awpcp_region_parent_id);
		}

		$params = array('offset' => $offset, 'results' => $results, 'regionid' => $awpcp_region_id);
		$href = add_query_arg($params, awpcp_current_url());

		$awpcp_region_featured_status_manage_link='';
		if($awpcp_region_featured_status == 1)
		{
			if($awpcp_region_type != 1)
			{
				$awpcp_region_featured_status_manage_link="<a href=\"" . add_query_arg('action', 'removefromsidelist', $href) . "\" title=\"" . __( 'Remove from Sidelist', 'awpcp-region-control' ) . "\"><img src=\"$awpcp_imagesurl/delete_ico.png\" alt=\"" . __( 'Remove from Sidelist', 'awpcp-region-control' ) . "\" border=\"0\"></a>";
			}
		}
		elseif($awpcp_region_featured_status == 0)
		{
			if($awpcp_region_type != 1)
			{
				$awpcp_region_featured_status_manage_link="<a href=\"" . add_query_arg('action', 'addtosidelist', $href) . "\" title=\"" . __( 'Add to Sidelist', 'awpcp-region-control' ) . "\"><img src=\"$awpcp_imagesurl/post_ico.png\" alt=\"" . __( 'Add to Sidelist', 'awpcp-region-control' ) . "\" border=\"0\"></a>";
			}
		}

		if($awpcp_region_type == 1)
		{
			$awpcp_region_type = __( 'Continent', 'awpcp-region-control' );
		}

		elseif($awpcp_region_type == 2)
		{
			$awpcp_region_type = __( 'Country', 'awpcp-region-control' );
		}

		elseif($awpcp_region_type == 3)
		{
			$awpcp_region_type = __( 'State/Town', 'awpcp-region-control' );
		}

		elseif($awpcp_region_type == 4)
		{
			$awpcp_region_type = __( 'City', 'awpcp-region-control' );
		}

		elseif($awpcp_region_type == 5)
		{
			$awpcp_region_type = __( 'County/Village/Other', 'awpcp-region-control' );
		}

		if($awpcp_region_state == 1)
		{
			$awpcp_region_state = __( 'Enabled', 'awpcp-region-control' ) . "<SUP><a style=\"font-size:x-small;\" href=\"" . add_query_arg('action', 'disableregion', $href) . "\">" . __( 'Disable', 'awpcp-region-control' ) . "</a></SUP>";
		}

		elseif($awpcp_region_state == 0)
		{
			$awpcp_region_state = __( 'Disabled', 'awpcp-region-control' ) . "<SUP><a style=\"font-size:x-small;\" href=\"" . add_query_arg('action', 'enableregion', $href) . "\">" . __( 'Enable', 'awpcp-region-control' ) . "</a></SUP>";
		}
		elseif($awpcp_region_state == 2)
		{
			/* translators: Enabled as in Region Enabled */
			$awpcp_region_state = __( 'Enabled (Localized)', 'awpcp-region-control' ) . "<SUP><a style=\"font-size:x-small;\" href=\"?page=Configure4&action=disableregion&regionid=$awpcp_region_id\">" . __( 'Disable', 'awpcp-region-control' ) . "</a></SUP>";
		}

		$awpcpregionitems[]="<tr data-region-id=\"$awpcp_region_id\">
								<td style=\"width:20%;padding:5px;border-bottom:1px dotted #dddddd;font-weight:normal;\">
									<input type=\"checkbox\" name=\"awpcp_region_to_disable_enable[]\" value=\"$awpcp_region_id\" /> $awpcp_region_name
								</td>
								<td style=\"width:20%;padding:5px;font-weight:normal;\">$awpcp_region_type</td>
								<td style=\"width:20%;padding:5px;font-weight:normal;\">$awpcp_region_state</td>
								<td style=\"width:20%;padding:5px;border-bottom:1px dotted #dddddd;font-weight:normal;\">$awpcp_region_parent_name</td>
								<td class=\"actions\" style=\"padding:5px;border-bottom:1px dotted #dddddd;font-size:smaller;font-weight:normal;\">
									<a href=\"" . add_query_arg('action', 'editregion', $href) . "\" title=\"" . __( 'Edit Region', 'awpcp-region-control' ) . "\"
										><img src=\"$awpcp_imagesurl/edit_ico.png\" alt=\"" . __( 'Edit Region', 'awpcp-region-control' ) . "\" border=\"0\"
									></a>
									<a class=\"delete\" href=\"" . add_query_arg('action', 'deleteregion', $href) . "\" title=\"" . __( 'Delete Region', 'awpcp-region-control' ) . "\"
										><img src=\"$awpcp_imagesurl/region_delete_ico.png\" alt=\"" . __( 'Delete Region', 'awpcp-region-control' ) . "\" border=\"0\"
									></a>
									$awpcp_region_featured_status_manage_link
								</td>
							</tr>";
	}

	$opentable="<table class=\"listcatsh\">
					<tr>
						<td style=\"width:20%;padding:5px;\">
							<input type=\"checkbox\" onclick=\"CheckAllRegions(document.myregions)\" /> " . __( 'Region Name', 'awpcp-region-control' ) . "
						</td>
						<td style=\"width:20%;padding:5px;\">" . __( 'Region Type', 'awpcp-region-control' ) . "</td>
						<td style=\"width:20%;padding:5px;\">" . __( 'Region State', 'awpcp-region-control' ) . "</td>
						<td style=\"width:20%;padding:5px;\">" . __( 'Region Parent', 'awpcp-region-control' ) . "</td>
						<td style=\"width:20%;padding:5px;;\">" . __( 'Action', 'awpcp-region-control' ) . "</td>
					</tr>";
	$closetable="	<tr>
						<td style=\"width:20%;padding:5px;\">" . __( 'Region Name', 'awpcp-region-control' ) . "</td>
						<td style=\"width:20%;padding:5px;\">" . __( 'Region Type','awpcp-region-control' ) . "</td>
						<td style=\"width:20%;padding:5px;\">" . __( 'Region State','awpcp-region-control' ) . "</td>
						<td style=\"width:20%;padding:5px;\">" . __( 'Region Parent','awpcp-region-control' ) . "</td>
						<td style=\"width:20%;padding:5px;\">" . __( 'Action','awpcp-region-control' ) . "</td>
					</tr>
				</table>";

	$theawpcpregionitems = smart_table2( $awpcpregionitems, intval( $results / $results ), $opentable, $closetable, false );
	$awpcpshowregions="$theawpcpregionitems";
	if( !isset($awpcp_region_id) || empty($awpcp_region_id) )
	{
		unset($theawpcpregiontypeforsort);

		if(isset($_REQUEST['sortbyregion'])  && !empty($_REQUEST['sortbyregion']) )
		{
			$output .= "<div style=\"clear:both\"></div><p>" . __( 'There were no sub-regions found for the selected region. You can add sub-regions by clicking the "Add Regions" tab above.', 'awpcp-region-control' ) . "</p><p> <a href=\"?page=Configure4\">" . __( 'Reload Regions', 'awpcp-region-control' ) . "</a><p>";
		}
		elseif( !isset($_REQUEST['sortbyregion']) )
		{
			if( has_regions() )
			{
				$output .= "<div style=\"clear:both\"></div><p>" . __( 'All regions are disabled.', 'awpcp-region-control' ) . "</p><p> <a href=\"?page=Configure4&action=enableregion&regionid=all\">" . __( 'Enable Regions', 'awpcp-region-control' ) . "</a><p>";
			}
		}

	}

	else
	{

		// If the type is a continent
		if($theawpcpregiontypeforsort == 1)
		{

			$output .= "<div style=\"margin-right:100px;padding-bottom:10px;\">
							" . __( 'Show States/Towns In:', 'awpcp-region-control' ) . "
							<select name=\"awpcp_regions_statowns_list\" OnChange=\"location.href=myregions.awpcp_regions_statowns_list.options[selectedIndex].value\"><option value=\"-1\">" . __( 'Select Region', 'awpcp-region-control' ) . "</option>";

			$output .= awpcp_region_create_list($awpcpregiontypeval='2',$awpcpregionidval=$theawpcpregionidtosortby,$the_awpcp_region_parent,$onchangeonoff=1,$toggleforlocalization='');

			$output .= "</select>

							</div><div style=\"clear:both;\"></div>";
		}


		elseif($theawpcpregiontypeforsort == 2)
		{

			$output .= "<div style=\"float:right;margin-right:100px;padding-bottom:10px;\">
							" . __( 'Show Cities In:', 'awpcp-region-control' ) . "
							<select name=\"awpcp_regions_cities_list\" OnChange=\"location.href=myregions.awpcp_regions_cities_list.options[selectedIndex].value\"><option value=\"-1\">" . __( 'Select Region', 'awpcp-region-control' ) . "</option>";

			$output .= awpcp_region_create_list($awpcpregiontypeval='3',$awpcpregionidval=$theawpcpregionidtosortby,$the_awpcp_region_parent,$onchangeonoff=1,$toggleforlocalization='');

			$output .= "</select>
							</div><div style=\"clear:both;\"></div>";
		}

		elseif($theawpcpregiontypeforsort == 3)
		{

			$output .= "<div style=\"float:right;margin-right:100px;padding-bottom:10px;\">
							" . __( 'Show Counties/Villages/Other Regions In:', 'awpcp-region-control' ) . "
							<select name=\"awpcp_regions_counties_list\" OnChange=\"location.href=myregions.awpcp_regions_counties_list.options[selectedIndex].value\"><option value=\"-1\">" . __( 'Select Region', 'awpcp-region-control' ) . "</option>";

			$output .= awpcp_region_create_list($awpcpregiontypeval='4',$awpcpregionidval=$theawpcpregionidtosortby,$the_awpcp_region_parent,$onchangeonoff=1,$toggleforlocalization='');

			$output .= "</select>
							</div><div style=\"clear:both;\"></div>";
		}

		$output .= "
					<style>
					table.listcatsh { width: 100%; padding: 0px; border: none; border: 1px solid #dddddd;}
					table.listcatsh td { width:30%; font-size: 12px; border: none; background-color: #F4F4F4;
						vertical-align: middle; font-weight: bold; }
						table.listcatsh tr.special td { border-bottom: 1px solid #ff0000;  }

					table.listcatsc { width: 100%; padding: 0px; border: none; border: 1px solid #dddddd;}
					table.listcatsc td { width:33%;border: none;
						vertical-align: middle; padding: 5px; font-weight: normal; }
					table.listcatsc tr.special td { border-bottom: 1px solid #ff0000;  }
					</style>
					<div>
					$pager1
					<form name=\"myregions\" id=\"myregions\" action=\"\" method=\"post\">";

		if(!isset($awpcpdisableenablemultiplebutton) || empty($awpcpdisableenablemultiplebutton) )
		{
			$awpcpdisableenablemultiplebutton="<p><input type=\"submit\" name=\"disablemultipleregions\" class=\"button\" value=\"" . __( 'Disable Selected Regions', 'awpcp-region-control' ) . "\" /></p>";
		}

		$count_regions_listings = add_query_arg( array( 'page' => 'Configure4', 'action' => 'calculate-regions-listings-count' ), admin_url( 'admin.php' ) );

		$output .= "
			$awpcpdisableenablemultiplebutton
					<p>
						<b>" . __( 'Icon Meanings', 'awpcp-region-control' ) . "</b>
						<img src=\"$awpcp_imagesurl/edit_ico.png\" alt=\"" . __( 'Edit Region', 'awpcp-region-control' ) . "\" border=\"0\">" . __( 'Edit Region', 'awpcp-region-control' ) . "
						<img src=\"$awpcp_imagesurl/region_delete_ico.png\" alt=\"" . __( 'Delete Region', 'awpcp-region-control' ) . "\" border=\"0\">" . __( 'Delete Region', 'awpcp-region-control' ) . "
						<img src=\"$awpcp_imagesurl/post_ico.png\" alt=\"" . __( 'Add region to sidelist', 'awpcp-region-control' ) . "\" border=\"0\">" . __( 'Add to sidelist', 'awpcp-region-control' ) . "
						<img src=\"$awpcp_imagesurl/delete_ico.png\" alt=\"" . __( 'Remove region from sidelist', 'awpcp-region-control' ) . "\" border=\"0\">" . __( 'Remove from sidelist', 'awpcp-region-control' ) . "
					<div style=\"float:right;padding:5px;\">$regions_showhideoptions</div>
					$awpcpregslocalizedmessage
					$awpcpshowregions
				 	<p style=\"float:right;padding:10px;\"><input type=\"submit\" name=\"addselectedregionstosidelist\" class=\"button\" value=\"" . __( 'Add Selected Regions to Sidelist', 'awpcp-region-control' ) . "\" /></p>
					</form>$pager2
					<h3>" . __( 'Other Tools', 'awpcp-region-control' )  . "</h3>
					<ul>
						<li>
							<a href='" . esc_url( $count_regions_listings ) . "'>" . __( 'Regions Listings Count Calculator', 'awpcp-region-control' ) . "</a><br>
							<span>" . __( 'Use it to re-calculate the number of Ads posted in each region.' ) .  "</span>
						</li>
					</ul>
					</div>";
	}
	return $output;
}


function load_region_edit_form($the_awpcp_region_to_lookup)
{
	global $wpdb;

	$output = '';

	$query = 'SELECT region_id,region_name,region_parent,region_state,region_type ';
	$query.= 'FROM ' . AWPCP_TABLE_REGIONS . ' WHERE region_id = %d';

	$region = $wpdb->get_row( $wpdb->prepare( $query, $the_awpcp_region_to_lookup ) );

	$theregionid = $region->region_id;
	$theregionname = $region->region_name;
	$theregionparent = $region->region_parent;
	$theregionstate = $region->region_state;
	$theregiontype = $region->region_type;

	$parent = awpcp_regions_api()->find_by_id($theregionparent);
	if (!is_null($parent)) {
		$parent_name = $parent->region_name;
	} else {
		$parent_name = '';
	}

	$checked1='';
	$checked2='';

	$opsitemregtypelist=awpcp_region_create_type_list($theregiontype);

	if( $theregionstate == 0 )
	{
		$checked1="checked";
	}

	if( $theregionstate == 1 || $theregionstate == 2  )
	{
		$checked2="checked";
	}

	$form_message = __( 'The information for the region <region-name> has been entered into the form below for you to edit as needed.', 'awpcp-region-control' );
	$form_message = str_replace( '<region-name>', '<strong>' . $theregionname . '</strong>', $form_message );

	$output .= "
		<p>$form_message</p>
		<form method=\"post\" action=\"\" id=\"awpcp_opsconfig_regions\">
		<div>
		<input type=\"hidden\" name=\"action\" value=\"updateregiondata\" />
		<input type=\"hidden\" name=\"regions_region_id\" value=\"$theregionid\" />
		" . awpcp_print_message( __( 'For Regions with type other than Continent, please start typing the name of the parent region and wait for the autocomplete field to show you the available options. Then choose the option you want.', 'awpcp-region-control' ) ) . "
		<p><label>" . __( 'Region Name', 'awpcp-region-control' ) . "</label> <input type=\"text\" name=\"regions_region_name\" size=\"27\" value=\"$theregionname\" /></p>
		<p><label>" . __( 'Region Type', 'awpcp-region-control' ) . "</label> <select name=\"regions_region_type\">$opsitemregtypelist";
	$output .= "
			</select>
		</p>
		<p>
			<label>" . __( 'Region Parent', 'awpcp-region-control' ) . "</label>
			<input class=\"\" name=\"regions_region_parent\" type=\"hidden\" value=\"$theregionparent\"/>
			<input name=\"regions_region_parent_name\" type=\"text\" value=\"$parent_name\"/>";

	// 		<select name=\"regions_region_parent\">$opsitemregparentlist";
	// $output .= "</select>

	$output .= "
		</p>
		<p><label>Region State</label><p><input type=\"radio\" name=\"regions_region_state\" value=\"0\" $checked1 /> " . __( 'Disabled', 'awpcp-region-control' ) . " <input type=\"radio\" name=\"regions_region_state\" value=\"1\" $checked2 /> Enabled</p>
		<input type=\"submit\" class=\"button\" value=\"" . __( 'Update Region Data' ) . "\" />
		</div>
		</form> ";

	return $output;
}


function get_theawpcpregionparentname($awpcp_region_parent_id) {
	global $wpdb;

	$query = "SELECT region_name from " . AWPCP_TABLE_REGIONS . " WHERE region_id=%d";
	$region = $wpdb->get_row( $wpdb->prepare( $query, $awpcp_region_parent_id ) );

	return is_null( $region ) ? '' : $region->region_name;
}

/**
 * @since the-beginning
 * @since 3.2.7 - updated to use $wpdb
 */
function get_theawpcpregionname( $region_id ) {
	global $wpdb;

    if ( ! is_numeric( $region_id ) ) {
        return $region_id;
    }

    $query = $wpdb->prepare( 'SELECT region_name FROM ' . AWPCP_TABLE_REGIONS . ' WHERE region_id = %d', $region_id );
    $region_name = $wpdb->get_var( $query );

    return $region_name;
}


function get_theawpcpregiontype( $theawpcpregionidtosortby ) {
	global $wpdb;

	if ( empty( $theawpcpregionidtosortby ) ) {
		return '';
	}

	$query = 'SELECT region_type FROM ' . AWPCP_TABLE_REGIONS . ' WHERE region_id = %d';
	$query = $wpdb->prepare( $query, $theawpcpregionidtosortby );

	$region_type = $wpdb->get_var( $query );

	return $region_type !== false ? $region_type : '';
}


/**
 * Finds out if a region already exists in the database
 *
 * @param $name string A already escaped (passed by addslashes_mq) value
 * @param $parent int ID of the parent region
 */
function region_already_exists($name, $parent) {
	global $wpdb;

	$query = 'SELECT region_id FROM  ' . AWPCP_TABLE_REGIONS . ' ';
	$query.= 'WHERE region_name = %s AND region_parent = %d';
	$result = $wpdb->get_results($wpdb->prepare($query, $name, $parent));

	if ($result === false || empty($result)) {
		return false;
	}
	return true;
}


function awpcp_region_create_list($awpcpregiontypeval, $awpcpregionidval, $the_awpcp_region_parent, $onchangeonoff) {
	global $wpdb;

	// get cached version
	$arguments = func_get_args();
	$transient = 'awpcp-region-control-list-' . hash('adler32', serialize($arguments));
	if ($output = get_site_transient($transient)) return $output;

	$output = '';

	if (isset($awpcpregiontypeval) && !empty($awpcpregiontypeval)) {
		$where="WHERE region_type='$awpcpregiontypeval' AND region_parent='$awpcpregionidval'";
	} else {
		$where="";
	}

	$query = 'SELECT region_id, region_name,region_type,region_parent FROM ' . AWPCP_TABLE_REGIONS . ' ';
	$query.= "$where ORDER BY region_name ASC";

	$results = $wpdb->get_results( $query );

	$region_type_names = array(
		1 => 'Continent',
		2 => 'Country',
		3 => 'State',
		4 => 'City',
		5 => 'County'
	);

	foreach ( $results as $result ) {
		if ($onchangeonoff==1) {
			$output .= '<option value="?page=Configure4&sortbyregion="' . $result->region_id . '"';
		} elseif($onchangeonoff==3) {
			$output .= '<option value="' . $result->region_id . '" onclick="awpcp_localize_region_toggle_visibility(\'awpcp_country_state_city_inputshowhide\');"';
		} else {
			$output .= '<option value="' . $result->region_id . '"';
		}

		if( isset($the_awpcp_region_parent) && !empty($the_awpcp_region_parent) ) {
			if ( $result->region_id == $the_awpcp_region_parent ) {
				$output .= " selected='selected'";
			}
		}

		$output .= '>' . $result->region_name;

		if ( is_a_duplicate_region_name( $result->region_name ) ) {
			$theregiontype = $result->region_type;
			$theregionparentname = get_theawpcpregionparentname( $result->region_parent );
			$theregiontypevalue = $region_type_names[$theregiontype];
			$output .= " ( $theregiontypevalue, $theregionparentname )";
		}

		$output .= "</option>";
	}

	set_site_transient($transient, $output);

	return $output;
}


function awpcp_region_create_type_list($awpcpselectedregion)
{
	$opsitemreglist='';

	$theawpcpregiontypesarray=array(1,'2','3','4','5');

	foreach($theawpcpregiontypesarray as $awpcp_region_type )
	{

		if($awpcp_region_type == 1)
		{
			$awpcp_region_type_name = __( 'Continent', 'awpcp-region-control' );
		}

		elseif($awpcp_region_type == 2)
		{
			$awpcp_region_type_name = __( 'Country', 'awpcp-region-control' );
		}

		elseif($awpcp_region_type == 3)
		{
			$awpcp_region_type_name = __( 'State/Town', 'awpcp-region-control' );
		}

		elseif($awpcp_region_type == 4)
		{
			$awpcp_region_type_name = __( 'City', 'awpcp-region-control' );
		}

		elseif($awpcp_region_type == 5)
		{
			$awpcp_region_type_name = __( 'County/Village/Other', 'awpcp-region-control' );
		}

		$opsitemreglist.="<option value='$awpcp_region_type'";


		if( isset($awpcpselectedregion) && !empty($awpcpselectedregion) )
		{
			if($awpcp_region_type ==  $awpcpselectedregion)
			{
				$opsitemreglist.=" selected='selected'";
			}
		}

		$opsitemreglist.=">$awpcp_region_type_name</option>";
	}

	return $opsitemreglist;

}

function awpcp_region_create_list_checkboxes( $awpcpregiontypeval, $awpcp_region_for_localization ) {
	global $wpdb;

	$output = '';

	if ( ! is_array( $awpcp_region_for_localization ) && ! empty( $awpcp_region_for_localization ) ) {
		$awpcp_region_for_localization = array( $awpcp_region_for_localization );
	}

	if(is_array($awpcp_region_for_localization) && !empty($awpcp_region_for_localization)) {
		foreach( $awpcp_region_for_localization as $awpcpregionforlocalization ) {
			$awpcplocalizeidsarrayitems[]=$awpcpregionforlocalization;

			$awpcplocalizeids=join("','",$awpcplocalizeidsarrayitems);

			$where=" WHERE region_parent IN ('$awpcplocalizeids') ";
		}
	} elseif(isset($awpcpregiontypeval) && !empty($awpcpregiontypeval)) {
		$where=" WHERE region_type='$awpcpregiontypeval' ";
	} else {
		$where="";
	}

	$query = 'SELECT region_id, region_name FROM ' . AWPCP_TABLE_REGIONS . ' ' . $where . ' ORDER BY region_name ASC';
	$results = $wpdb->get_results( $query );

	if ( ! empty( $results ) ) {
		$output .= "<p><span style=\"background:#eeeeee;padding:5px;\"><input type=\"checkbox\" onclick=\"CheckAllRegions(document.myregionslocalize)\" />" . __( 'Select All', 'awpcp-region-control' ) . "</span></p><ul style=\"float:left;list-style:none;\">";

		foreach( $results as $result ) {
			$output .= "<li style=\"float:left;list-style:none;width:275px;\"><input type=\"checkbox\" name=\"awpcp_region_for_localization[]\" value=\"" . $result->region_id . "\"/>" . stripslashes( $result->region_name ) . "</li>";
		}

		$output .= "</ul>";
	} else {
		$output .= "<p>" . __( 'There are no sub regions to display. If you need to further localize the module you will need to add the regions from  the "Add Regions" tab then localize the newly added regions.', 'awpcp-region-control' ) . "</p>";
	}

	return $output;
}


function has_localized_regions() {
	global $wpdb;

    $regions = $wpdb->get_results( 'SELECT * FROM ' . AWPCP_TABLE_REGIONS . ' WHERE region_localized = 1' );

    if ( is_array( $regions ) ) {
        return count( $regions ) > 0;
    } else {
        return false;
    }
}

function get_localized_region_ids() {
	global $wpdb;

	$query = 'SELECT region_id FROM ' . AWPCP_TABLE_REGIONS . ' WHERE region_localized = 1';

	$results = $wpdb->get_col( $query );

	return $results !== false ? $results : array();
}

function has_regions() {
	global $wpdb;

	$count = $wpdb->get_var( 'SELECT COUNT(*) FROM ' . AWPCP_TABLE_REGIONS );

	if ( $count !== false ) {
		return $count > 0;
	} else {
		return false;
	}
}

function awpcp_configure_querslink($regionid) {
    return awpcp_get_set_active_region_url( $regionid );
}

function awpcp_get_set_active_region_url( $region_id ) {
    $main_page_id = awpcp_get_page_id_by_ref( 'main-page-name' );

    if ( get_awpcp_option( 'seofriendlyurls' ) && get_option('permalink_structure') ) {
        $pagename = sprintf(
            "%s/setregion/%d/%s",
            get_page_uri( $main_page_id ),
            $region_id,
            get_theawpcpregionname( $region_id )
        );

        $url = awpcp_get_url_with_page_permastruct( $pagename );
    } else {
        $base_url = awpcp_get_page_link( $main_page_id );
        $params = array( 'a' => 'setregion', 'regionid' => $region_id );
        $url = add_query_arg( $params, $base_url );
    }

	return $url;
}

function is_a_duplicate_region_name($name) {
	global $wpdb;

	$transient = 'awpcp-region-control-duplicated-regions';
	$duplicated = get_site_transient($transient);

	if ($duplicated === false) {
		$query = 'SELECT region_name, count(region_id) AS count FROM ' . AWPCP_TABLE_REGIONS . ' ';
		$query.= 'GROUP BY region_name HAVING count > 1';
		$duplicated = awpcp_get_properties($wpdb->get_results($query), 'region_name');
		set_site_transient($transient, $duplicated);
	}

	return in_array($name, $duplicated);
}

/**
 * TODO: get Continents, Countries
 * TODO: there is a pattern here, this can be written in less lines
 * TODO: move to Regions API
 */
function awpcp_region_control_get_entries($type, $parent='', $parent_type='', $where='1 = 1', $order='ORDER BY region_name') {
	global $wpdb;

	$type = strtolower($type);
	$parent_type = strtolower($parent_type);

	if (is_numeric($parent)) {
	    $params = array('type' => $parent_type, 'id' => $parent);
	} else {
	    $params = array('type' => $parent_type, 'name' => $parent);
	}

	// Countries
	if ($type == 'country') {
		$conditions = ' region_type = 2 ';

	// States
	} else if ($type == 'state' && $parent_type == 'country') {
		$country = awpcp_region_control_get_entry($params);
		$conditions = ' region_type = 3 ';

		if (empty($country)) {
			return array();
		}

		$conditions = ' region_type = 3 AND region_parent = ' . $country->region_id . ' ';

	} else if ($type == 'state') {
		$conditions = ' region_type = 3';

	// Cities
	} else if ($type == 'city' && $parent_type == 'state') {
		$state = awpcp_region_control_get_entry($params);
		$conditions = ' region_type = 4 ';

		if (empty($state)) {
			return array();
		}

		$conditions .= 'AND region_parent = ' . $state->region_id . ' ';

	} else if ($type == 'city' && !empty($parent_type)) {
		$entries = awpcp_region_control_get_entries('state', $parent, $parent_type);
		$states = awpcp_get_properties($entries, 'region_id');
		$conditions = ' region_type = 4 ';

		if (empty($states)) {
			return array();
		}

		$conditions .= 'AND region_parent IN (' . join(',', $states) . ') ';

	} else if ($type == 'city') {
		$conditions = ' region_type = 4';

	// Counties and Villages
	} else if ($type == 'county' && $parent_type == 'city') {
		$city = awpcp_region_control_get_entry($params);
		$conditions = ' region_type = 5 ';

		if (empty($city)) {
			return array();
		}

		$conditions .= 'AND region_parent = ' . $city->region_id . ' ';

	} else if ($type == 'county' && !empty($parent_type)) {
		$entries = awpcp_region_control_get_entries('city', $parent, $parent_type);
		$cities = awpcp_get_properties($entries, 'region_id');
		$conditions = ' region_type = 5 ';

		if (empty($cities)) {
			return array();
		}

		$conditions .= 'AND region_parent IN (' . join(',', $cities) . ') ';

	} else if ($type == 'county') {
		$conditions = ' region_type = 5';

	// get all subregions
	} else if (!empty($parent) && !empty($parent_type)) {
		$parent_region = awpcp_region_control_get_entry($params);

		if (empty($parent_region)) {
			return array();
		}

		$conditions = ' region_parent = ' . $parent_region->region_id;

	} else {
		$conditions = '1 = 1';
	}

	$sql = 'SELECT * FROM ' . AWPCP_TABLE_REGIONS . ' ';
	$sql.= "WHERE  region_state=1 AND ";
	if (has_localized_regions()) {
		$sql .= "region_localized=1 AND ";
	}
	$sql.= "$conditions AND $where $order";

	$results = $wpdb->get_results($sql);

	return $results;
}


/**
 * TODO: move to Regions API.
 */
function awpcp_region_control_get_entry($params) {
	global $wpdb;

	$id = awpcp_array_data('id', 0, $params);
	$name = awpcp_array_data('name', '', $params);
	$type = awpcp_array_data('type', '', $params);

	// TODO: this should be globally defined
	$region_types = array('continent' => 1, 'country' => 2, 'state' => 3, 'city' => 4, 'county' => 5);

	$where = '1 = 1';
	if ($id > 0) {
		$where.= $wpdb->prepare(' AND region_id = %d ', $id);
	}
	if (!empty($type)) {
		$where.= $wpdb->prepare(' AND region_type = %d ', $region_types[$type]);
	}
	if (!empty($name)) {
		$where.= $wpdb->prepare(' AND region_name LIKE %s', "%$name%");
	}

	$sql = 'SELECT * FROM ' . AWPCP_TABLE_REGIONS . ' ';
	$sql.= "WHERE $where";

	$results = $wpdb->get_results($sql);

	if (!empty($results)) {
		return array_shift($results);
	}
	return null;
}

function awpcp_region_control_delete_entry($id) {
	global $wpdb;

	$region = awpcp_region_control_get_entry(array('id' => $id));
	$region_types = array(1 => 'continent', 2 => 'country', 3 => 'state', 4 => 'city', 5 => 'county');

	if (is_null($region)) {
		return __('The specified Region does not exists.', 'awpcp-region-control' );
	}

	$subregions = awpcp_region_control_get_entries('', $region->region_id, $region_types[$region->region_type]);

	if (!empty($subregions)) {
		$message = __("This Region can't be deleted because it has %d subregions.", 'awpcp-region-control' );
		return sprintf($message, count($subregions));
	}

	$regions = awpcp_regions_api();

	if ($region->count_all > 0) {
		$message = __("The Region can't be deleted because there are %d Ads using it.", 'awpcp-region-control' );
		return sprintf($message, $region->count_all);
	}

	$query = 'DELETE FROM ' . AWPCP_TABLE_REGIONS . ' WHERE region_id = %d';
	$result = $wpdb->query($wpdb->prepare($query, $id));

	if ($result === false) return $result;

	$regions->clear_cache();

	return true;
}

/* Widget to change the current region  */

function awpcp_region_control_session() {
	static $session = null;

	if (is_array($session)) return $session;

	$variables = array(
		'country' => 'regioncountryID',
		'state' => 'regionstatownID',
		'city' => 'regioncityID',
		'county' => 'region-county-id'
	);

	$api = awpcp_regions_api();
    $current_location = awpcp_region_control_module()->get_current_location_data();
	$session = array();

	foreach ($variables as $region => $key) {
        $region_id = awpcp_array_data( $key, 0, $current_location );
		if ($region_id > 0) {
			$session[$region] = $api->find_by_id($region_id);
		}
	}

	return array_filter( $session );
}

/**
 * @return array        An array of regions.
 */
function awpcp_prepare_active_region_for_region_selector() {
	$session = awpcp_region_control_session();

	if ( empty( $session ) ) {
		return array();
	}

	$api = awpcp_regions_api();
	$regions = array();

	foreach ( $session as $object ){
		$type = $api->get_region_type_slug( $object->region_type );
		$regions[0][ $type ] = $object->region_name;
	}

	return $regions;
}

function awpcp_region_control_selector() {
	wp_enqueue_script('awpcp-region-control');

	$url = awpcp_get_set_location_url();
	$selected_regions = awpcp_prepare_active_region_for_region_selector();
	$always_open = get_awpcp_option( 'region-control-keep-selector-always-open', false );

	ob_start();
		include(AWPCP_REGION_CONTROL_MODULE_DIR . '/templates/region-control-selector.tpl.php');
		$html = ob_get_contents();
	ob_end_clean();

	return $html;
}


/*-----------------------------------------------------------------------------
 * Region Selector and Sidelist endpoints
 */


function awpcp_region_control_rules() {
    $rules = get_option('rewrite_rules');
    if (!isset($rules['awpcp/(regions)/(set-location)'])) {
        global $wp_rewrite;
        $wp_rewrite->flush_rules();
    }
}

function awpcp_region_control_rewrite_rules( $rules ) {
	awpcp_rewrite_rules_helper()->add_page_rewrite_rule(
		'awpcp/(regions)/(set-location)',
		'index.php?awpcpx=1&awpcp-module=$matches[1]&awpcp-action=$matches[2]',
		'top'
	);

	return $rules;
}


function awpcp_region_control_query_vars($vars) {
    array_push($vars, 'awpcpx');
    array_push($vars, 'awpcp-module');
    array_push($vars, 'awpcp-action');
    return $vars;
}

/*-----------------------------------------------------------------------------
 * AJAX handlers
 */

function awpcp_region_control_delete_region_ajax() {
	if (isset($_POST['remove'])) {
		$result = awpcp_region_control_delete_entry(intval($_POST['id']));
        if ($result === true) {
            $response = json_encode(array('status' => 'success'));
        } else if ($result === false) {
            $response = json_encode(array('status' => 'error', 'message' => __('The element couldn\'t be deleted.', 'awpcp-region-control' )));
        } else {
            $response = json_encode(array('status' => 'error', 'message' => $result));
        }
	} else {
		$columns = intval($_POST['columns']);
        ob_start();
            include(AWPCP_DIR . '/admin/templates/delete_form.tpl.php');
            $html = ob_get_contents();
        ob_end_clean();
        $response = json_encode(array('html' => $html));
	}

	header('Content-Type: application/json');
	echo $response;
	exit();
}

} // closes if that checks if ModulesManager exists

} // closes if that checks if an inline version of the module is present
