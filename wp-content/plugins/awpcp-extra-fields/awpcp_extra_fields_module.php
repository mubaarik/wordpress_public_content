<?php

/*
 Plugin Name: AWPCP Extra Fields Module
 Plugin URI: http://www.awpcp.com
 Description: Enhances AWPCP to add extra form fields, column formatting and field ordering
 * Version: 3.6.10
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

if (preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) {
	die('You are not allowed to call this page directly.');
}

if (function_exists('awpcp_display_extra_fields')) {

	$msg = __('An old version of Extra Fields Module is already loaded. Please remove awpcp_extra_fields_module.php from your AWPCP plugin directory.', 'awpcp-extra-fields' );
	add_action('admin_notices', create_function('', 'echo \'<div class="error"><p>' . $msg . '</p></div>\';'));
	define('AWPCP_EXTRA_FIELDS_MODULE_CONFLICT', true);

} else {

global $wpdb;

define('AWPCPEXTRAFIELDSMOD', 'Another Wordpress Classifieds Plugin - Extra Fields Module');

define('AWPCP_EXTRA_FIELDS_MODULE', 'Another WordPress Classifieds Plugin - Region Control Module');
define('AWPCP_EXTRA_FIELDS_MODULE_BASENAME', basename(dirname(__FILE__)));
define('AWPCP_EXTRA_FIELDS_MODULE_DIR', rtrim( plugin_dir_path( __FILE__ ), '/' ));
define('AWPCP_EXTRA_FIELDS_MODULE_URL', rtrim( plugin_dir_url( __FILE__ ), '/' ));
define( 'AWPCP_EXTRA_FIELDS_MODULE_DB_VERSION', '3.6.10' );
define( 'AWPCP_EXTRA_FIELDS_MODULE_REQUIRED_AWPCP_VERSION', '3.7.5' );

define( 'AWPCP_TABLE_EXTRA_FIELDS', $wpdb->prefix . 'awpcp_extra_fields' );

require( AWPCP_EXTRA_FIELDS_MODULE_DIR . '/class-extra-fields-module-installer.php' );

require_once( AWPCP_EXTRA_FIELDS_MODULE_DIR . '/includes/functions/finder.php' );

require_once( AWPCP_EXTRA_FIELDS_MODULE_DIR . '/includes/class-extra-fields-collection.php' );
require_once( AWPCP_EXTRA_FIELDS_MODULE_DIR . '/includes/class-extra-fields-listings-finder.php' );
require_once( AWPCP_EXTRA_FIELDS_MODULE_DIR . '/includes/class-extra-fields-placeholders.php' );
require_once( AWPCP_EXTRA_FIELDS_MODULE_DIR . '/includes/class-extra-form-fields.php' );

require_once( AWPCP_EXTRA_FIELDS_MODULE_DIR . '/includes/views/admin/class-save-extra-field-data-step.php' );


function awpcp_extra_fields_required_awpcp_version_notice() {
    if ( current_user_can( 'activate_plugins' ) ) {
        $module_name = __( 'Extra Fields Module', 'awpcp-extra-fields' );
        $required_awpcp_version = AWPCP_EXTRA_FIELDS_MODULE_REQUIRED_AWPCP_VERSION;

        $message = __( 'The AWPCP <module-name> requires AWPCP version <awpcp-version> or newer!', 'awpcp-extra-fields' );
        $message = str_replace( '<module-name>', '<strong>' . $module_name . '</strong>', $message );
        $message = str_replace( '<awpcp-version>', $required_awpcp_version, $message );
        $message = sprintf( '<strong>%s:</strong> %s', __( 'Error', 'awpcp-extra-fields' ), $message );
        echo '<div class="error"><p>' . $message . '</p></div>';
    }
}

if ( ! class_exists( 'AWPCP_ModulesManager' )  ) {

    add_action( 'admin_notices', 'awpcp_extra_fields_required_awpcp_version_notice' );

} else {

class AWPCP_ExtraFieldsModule extends AWPCP_Module {

	public function __construct( $installer ) {
		parent::__construct(
			__FILE__,
			'Extra Fields Module',
			'extra-fields',
			AWPCP_EXTRA_FIELDS_MODULE_DB_VERSION,
			AWPCP_EXTRA_FIELDS_MODULE_REQUIRED_AWPCP_VERSION
		);

		$this->installer = $installer;
	}

	public function required_awpcp_version_notice() {
		return awpcp_extra_fields_required_awpcp_version_notice();
	}

	public function get_installed_version() {
		return get_option( 'awpcp-extra-fields-db-version' );
	}

	public function install_or_upgrade() {
		$this->installer->install_or_upgrade( $this );
	}

    public function load_dependencies() {
		require( AWPCP_EXTRA_FIELDS_MODULE_DIR . '/includes/class-extra-fields-column-manager.php' );
		require( AWPCP_EXTRA_FIELDS_MODULE_DIR . '/includes/class-extra-form-field.php' );

		require( AWPCP_EXTRA_FIELDS_MODULE_DIR . '/admin/class-extra-fields-dropdown-settings-type-renderer.php' );
		require( AWPCP_EXTRA_FIELDS_MODULE_DIR . '/admin/class-repair-extra-fields-columns-admin-page.php' );
		require( AWPCP_EXTRA_FIELDS_MODULE_DIR . '/admin/class-repair-extra-fields-columns-ajax-handler.php' );
    }

	protected function module_setup() {
		global $extrafieldsversioncompatibility, $hasextrafieldsmodule;

		parent::module_setup();

		// tell AWPCP the module is available
		$extrafieldsversioncompatibility = true;
		$hasextrafieldsmodule = true;

		add_filter( 'awpcp-get-posted-data', array( $this, 'get_posted_data' ), 10, 2);

		$finder = awpcp_extra_fields_listings_finder();
		add_filter( 'awpcp-find-listings-keyword-conditions', array( $finder, 'filter_keyword_conditions' ), 10, 2 );
		add_filter( 'awpcp-find-listings-conditions', array( $finder, 'filter_conditions' ), 10, 2 );

		$extra_form_fields = awpcp_extra_form_fields();
		add_filter( 'awpcp-form-fields', array( $extra_form_fields, 'register_extra_form_fields' ) );

		add_action('awpcp-save-ad-details', array($this, 'save_extra_fields'), 10, 2);
		add_action('awpcp_edit_ad', array($this, 'save_extra_fields'), 10, 2);

		$extra_fields_placeholders = awpcp_extra_fields_placeholders();
		add_filter( 'awpcp-content-placeholders', array( $extra_fields_placeholders, 'register_content_placeholders' ) );

		$settings_type_renderer = awpcp_extra_fields_dropdown_settings_type_renderer();
		add_filter( 'awpcp-render-setting-type-extra-fields-dropdown', array( $settings_type_renderer, 'render' ), 10, 3 );

		$handler = awpcp_repair_extra_fields_columns_ajax_handler();
		add_action( 'wp_ajax_awpcp-repair-extra-fields-columns-admin-page', array( $handler, 'ajax' ) );

		add_action( 'awpcp_register_settings', array( $this, 'register_settings' ) );

		$this->register_scripts();
	}

	public function register_scripts() {
		$version = AWPCP_EXTRA_FIELDS_MODULE_DB_VERSION;

		// remove older versions that could have been registered by the main plugin
		wp_deregister_script('awpcp-extra-fields');

		$src = AWPCP_EXTRA_FIELDS_MODULE_URL . '/resources/js/frontend.js';
		wp_register_script( 'awpcp-extra-fields', $src, array( 'awpcp' ), $version, true );
		$src = AWPCP_EXTRA_FIELDS_MODULE_URL . '/resources/js/admin.js';
		wp_register_script( 'awpcp-extra-fields-admin', $src, array('awpcp-admin-general'), $version, true );
	}

	public function register_settings($settings) {
		// TODO: use constants for group and sections slugs
		$key = $settings->add_section( 'general-settings', __( 'Extra Fields', 'awpcp-extra-fields' ), 'extra-fields-settings', 15, array( $settings, 'section' ) );
		$settings->add_setting($key, 'show-empty-extra-fields-in-ads', __('Show empty Extra Fields in Ads?', 'awpcp-extra-fields' ), 'checkbox', 1, '');

		$options = array( 1 => 1, 2 => 2, 3 => 3 );

                $settings->add_setting(
                    $key,
                    'display-extra-fields-in-columns',
                    __( 'Number of columns of Extra Fields to show.', 'awpcp-extra-fields' ),
                    'radio',
                    1,
                    __( 'Extra Fields will be shown in the selected number of columns.', 'awpcp-extra-fields' ),
                    array( 'options' => $options )
                );

		$settings->add_setting(
			$key,
			'allow-html-in-extra-field-labels',
			__( 'Allow HTML in Extra Fields Labels', 'awpcp-extra-fields' ),
			'checkbox',
			0,
			''
		);
	}

	/**
	 * @param  object $ad          the Ad being created/updated
	 * @param  object $transaction only available when handling awpcp-place-ad
	 */
	public function save_extra_fields($ad, $transaction=null) {
		global $wpdb;

		$fields = array();

		foreach (awpcp_get_extra_fields() as $field) {
			if (!isset($_POST["awpcp-{$field->field_name}"]))
				continue;

			$value = $_POST["awpcp-{$field->field_name}"];
			$fields[$field->field_name] = awpcp_extra_fields_field_value($field, $value);
		}

		$non_empty_fields = array_filter( $fields, 'strlen' );
		if ( !empty( $non_empty_fields ) ) {
			$wpdb->update(AWPCP_TABLE_ADS, $non_empty_fields, array('ad_id' => $ad->ad_id));
		}

		$empty_fields = array_diff($fields, $non_empty_fields);
		if ( !empty( $empty_fields ) ) {
			foreach ( array_keys( $empty_fields ) as $field ) {
				$assignments[] = sprintf( "`%s` = NULL", $field );
			}
			$query = 'UPDATE ' . AWPCP_TABLE_ADS . ' SET %s WHERE ad_id = %%d';
			$query = sprintf( $query, join( ',', $assignments ) );
			$wpdb->query( $wpdb->prepare( $query, $ad->ad_id ) );
		}
	}

	public function get_posted_data($data=array(), $context=false) {
		if ( $context == 'search' ) {
			$conditions = awpcp_get_extra_fields_conditions( array(
				'hide_private' => true,
				'context' => 'search',
			) );

			$fields = awpcp_get_extra_fields( 'WHERE ' . join( ' AND ', $conditions ) );

			$data['keywordphrase'] = stripslashes( awpcp_request_param( 'keywordphrase', null ) );

			foreach ($fields as $field) {
				$field_slug = "awpcp-{$field->field_name}";

				if ( isset( $_REQUEST["awpcp-{$field->field_name}-min"] ) ) {
					$min = $this->parse_field_posted_data( $field, awpcp_request_param( "awpcp-{$field->field_name}-min", null ) );
					$max = $this->parse_field_posted_data( $field, awpcp_request_param( "awpcp-{$field->field_name}-max", null ) );

					if ( !is_null( $min ) || !is_null( $max ) ) {
						$data[ $field_slug ] = array( 'min' => $min, 'max' => $max );
					}
				} else if ( isset( $_REQUEST["awpcp-{$field->field_name}-from"] ) ) {
					$from_date = $this->parse_field_posted_data( $field, awpcp_request_param( "awpcp-{$field->field_name}-from", null ) );
					$to_date = $this->parse_field_posted_data( $field, awpcp_request_param( "awpcp-{$field->field_name}-to", null ) );

					if ( !is_null( $from_date ) || !is_null( $to_date ) ) {
						$data[ $field_slug ] = array( 'from_date' => $from_date, 'to_date' => $to_date );
					}
				} else {
					$data[ $field_slug ] = $this->parse_field_posted_data( $field, awpcp_request_param( $field_slug, null ) );
				}
			}
		}

		return $data;
	}

	public function parse_field_posted_data( $field, $value ) {
        $is_array_value = is_array( $value );
		$value = stripslashes_deep( $value );
        $data_type = strtolower( $field->field_mysql_data_type );
        $parsed_values = array();

        foreach ( (array) $value as $single_value ) {
            if ( 0 == strlen( $single_value ) ) {
                continue;
            }

            $parsed_values[] = $this->parse_posted_value( $field, $single_value, $data_type );
        }

        return $is_array_value ? $parsed_values : array_pop( $parsed_values );
    }

    private function parse_posted_value( $field, $value, $data_type ) {
        if ( 'float' == $data_type ) {
            $parsed_value = awpcp_parse_number( $value );
        } elseif ( 'int' == $data_type ) {
            $parsed_value = intval( awpcp_parse_number( $value ) );
		} elseif ( $field->field_validation === 'currency' ) {
			$parsed_value = awpcp_parse_money( $value );
		} else if ( $field->field_validation == 'url' ) {
			$parsed_value = awpcp_maybe_add_http_to_url( $value );
		} else if ( $field->field_input_type == 'date' ) {
			$parsed_value = awpcp_datetime( 'mysql', $value );
		} else {
			$parsed_value = $value;
		}

		return $parsed_value;
    }
}

function awpcp_extra_fields_module() {
    return new AWPCP_ExtraFieldsModule( awpcp_extra_fields_module_installer() );
}

function awpcp_activate_extra_fields_module() {
    awpcp_extra_fields_module()->install_or_upgrade();
}
awpcp_register_activation_hook( __FILE__, 'awpcp_activate_extra_fields_module' );

function awpcp_load_extra_fields_module( $manager ) {
    $manager->load( awpcp_extra_fields_module() );
}
add_action( 'awpcp-load-modules', 'awpcp_load_extra_fields_module' );

function awpcp_extra_fields_is_reserved_name($name) {
	static $reserved = array(
		'attachment',
		'attachment_id',
		'author',
		'author_name',
		'calendar',
		'cat',
		'category',
		'category__and',
		'category__in',
		'category__not_in',
		'category_name',
		'comments_per_page',
		'comments_popup',
		'cpage',
		'day',
		'debug',
		'error',
		'exact',
		'feed',
		'hour',
		'link_category',
		'm',
		'minute',
		'monthnum',
		'more',
		'name',
		'nav_menu',
		'nopaging',
		'offset',
		'order',
		'orderby',
		'p',
		'page',
		'page_id',
		'paged',
		'pagename',
		'pb',
		'perm',
		'post',
		'post__in',
		'post__not_in',
		'post_format',
		'post_mime_type',
		'post_status',
		'post_tag',
		'post_type',
		'posts',
		'posts_per_archive_page',
		'posts_per_page',
		'preview',
		'robots',
		's',
		'search',
		'second',
		'sentence',
		'showposts',
		'static',
		'subpost',
		'subpost_id',
		'tag',
		'tag__and',
		'tag__in',
		'tag__not_in',
		'tag_id',
		'tag_slug__and',
		'tag_slug__in',
		'taxonomy',
		'tb',
		'term',
		'type',
		'w',
		'withcomments',
		'withoutcomments',
		'year',
		'city',
		'county',
		'state',
		'country'
	);
	return in_array($name, $reserved);
}


function awpcp_add_new_field() {
	$request = awpcp_request();

	if ( $request->param( 'action' ) == 'awpcp-repair-extra-fields-columns' ) {
		$page = awpcp_repair_extra_fields_columns_admin_page();
		return $page->dispatch();
	} else {
		return awpcp_manage_extra_fields_admin_page();
	}
}

function awpcp_manage_extra_fields_admin_page() {
	global $wpdb, $tbl_ads;

	$output = '';
	$show_settings = true;

	$action = awpcp_request_param('action');

	if( $action == 'addnewfield' ) {
		$show_settings = false;
		$output .= load_the_extra_fields_form(
						$awpcp_x_field_id='',
						$awpcp_x_field_name='',
						$awpcp_x_field_label='',
						$awpcp_x_field_label_view='',
						$awpcp_x_field_input_type='',
						$awpcp_x_field_mysqldata_type='',
						$awpcp_x_field_options='',
						$awpcp_x_field_validation='',
						$awpcp_x_field_privacy='',
						$awpcp_x_field_category=array(),
						$x_error_msg='');

	} elseif ( $action == 'edit' ) {
		if(isset($_REQUEST['id']) && !empty($_REQUEST['id']))
		{
			$x_id=$_REQUEST['id'];
		}

		if( !isset($x_id) || empty($x_id) )
		{

			$x_msg=__("There was no ID supplied for the field you would like to edit",'awpcp-extra-fields' );

			$output .= awpcp_display_extra_fields($x_msg);
		} else {
			$field = awpcp_get_extra_field($x_id);

			$show_settings = false;
			$output .= load_the_extra_fields_form($field->field_id,
												  $field->field_name,
												  $field->field_label,
												  $field->field_label_view,
												  $field->field_input_type,
												  $field->field_mysql_data_type,
												  $field->field_options,
												  $field->field_validation,
												  $field->field_privacy,
												  $field->field_category,
												  '',
												  $field->nosearch,
												  $field->show_on_listings,
												  $field->required);
		}

	} elseif ( $action == 'delete' ) {
		if(isset($_REQUEST['id']) && !empty($_REQUEST['id']))
		{
			$x_id=$_REQUEST['id'];
		}

		if( !isset($x_id) || empty($x_id) )
		{

			$x_msg=__("There was no ID supplied for the field you would like to delete",'awpcp-extra-fields' );

			$output .= awpcp_display_extra_fields($x_msg);
		} else {
			$query = 'SELECT field_name FROM ' . AWPCP_TABLE_EXTRA_FIELDS . ' WHERE field_id = %d';
			$query = $wpdb->prepare( $query , $x_id );

			$x_field_name = $wpdb->get_var( $query );

			$query = 'DELETE FROM ' . AWPCP_TABLE_EXTRA_FIELDS . ' WHERE field_id = %d';
			$query = $wpdb->prepare( $query, $x_id );

			if ( $wpdb->query( $query ) !== false ) {
				$wpdb->query("ALTER TABLE " . AWPCP_TABLE_ADS . " DROP `".$x_field_name."`");
				$x_msg=__("The field has been successfully deleted",'awpcp-extra-fields' );
			}

			$output .= awpcp_display_extra_fields($x_msg);
		}
	} elseif ( $action == 'savefielddata') {
		// TODO: is this really necessary?
		// we are passing the variable as a parameter to another function.
		global $x_msg;

		$step = awpcp_save_extra_field_data_step();

		try {
			$x_msg = $step->post();
			$output .= awpcp_display_extra_fields( $x_msg );
		} catch ( AWPCP_Exception $e ) {
			$show_settings = false;
			$output .= $e->getMessage();
		}

	} else {
		global $x_msg;
		$output .= awpcp_display_extra_fields($x_msg);

	}

	$output = awpcp_extra_fields_admin_head($show_settings) . $output . awpcp_extra_fields_admin_foot();

	echo $output;
}


function awpcp_extra_fields_admin_head($show_settings=false) {
	global $awpcp;

    $heading_params = array(
        'attributes' => array(
            'class' => 'awpcp-page-header',
        ),
        'content' => awpcp_admin_page_title( __( 'Extra Fields', 'awpcp-extra-fields' ) ),
    );

	$output = '';
	$output .= "<div class=\"wrap\"><div id=\"icon-edit-pages\" class=\"icon32\"><br></div>";
	$output .= awpcp_html_admin_first_level_heading( $heading_params );

    $output .= "<div>";

	// TODO: move this to a template when Extra Fields becomes a plugin
	if ($show_settings) {

		ob_start();
?>
        <div class="metabox-holder" style="float: left; width: 77%;">
        <div class="postbox">
            <?php echo awpcp_html_postbox_handle( array( 'content' => __( 'Extra Field Settings', 'awpcp-extra-fields' ) ) ); ?>
            <div class="inside">
            <form action="<?php echo admin_url('options.php') ?>" method="post">
            	<table class="form-table">
            	<?php do_settings_fields('general-settings', 'extra-fields-settings') ?>
            	</table>
				<?php settings_fields( $awpcp->settings->setting_name ); ?>
				<input type="hidden" name="group" value="<?php echo 'general-settings' ?>" />

				<p class="submit">
					<input type="submit" value="<?php _e( 'Save Changes', 'awpcp-extra-fields' ); ?>" class="button-primary" id="submit" name="submit">
				</p>
            </form>
            </div>
        </div>
        </div>
<?php
		$output .= ob_get_contents();
		ob_end_clean();
	}

	$sidebar = awpcp_admin_sidebar();
	$output .= $sidebar;

	if (empty($sidebar)) {
		$output .= '<div class="postbox" style="padding:5px">';
	} else {
		$output .= '<div class="postbox" style="padding:5px; width:76%; float:left">';
	}
	$output .= '<div class="inside">';

	return $output;
}

function awpcp_extra_fields_admin_foot()
{
	$output = '</div>'; // .inside
	$output.= '</div>'; // .postbox

	$params = array(
		'page' => 'Configure5',
		'action' => 'awpcp-repair-extra-fields-columns',
	);

	$repair_extra_fields_columns_admin_page_url = add_query_arg( $params, admin_url( 'admin.php' ) );

	$content = __( 'Click <a>here to verify that all extra fields have a corresponding column in the ads table</a> and create missing columns.', 'awpcp-extra-fields' );
	$content = str_replace( '<a>', '<a href="' . $repair_extra_fields_columns_admin_page_url . '">', $content);

	$output.= '<div class="awpcp-clearleft"><p>' . $content . '</p></div>';
	$output.= '</div>'; // #dashboard-widgets-wrap
	$output.= '</div>'; // .wrap
	return $output;
}

function awpcp_display_extra_fields($x_msg) {
	$output = '';
	$count_extra_fields=count_extra_fields();

	$add_label = __( 'Add New Field', 'awpcp-extra-fields' );
	$add_params = array( 'page' => 'Configure5', 'action' => 'addnewfield' );
	$add_url = add_query_arg( $add_params, admin_url('admin.php') );
	$add_button = '<p><a class="button-primary" title="%1$s" href="%2$s"" accesskey="s">%1$s</a></p>';
	$add_button = sprintf( $add_button, $add_label, $add_url );

	if ($count_extra_fields > 0) {
		if (isset($x_msg) && !empty($x_msg)) {
			$output .= awpcp_print_message( $x_msg );
		}

		$output .= $add_button;

		$order_fields_url = awpcp_get_admin_form_fields_url();
		$order_fields_link = sprintf( '<a href="%s">', $order_fields_url );

		$message = __( 'If you want to order the fields in a particular way, go <order-fields-link>here</a> to <order-fields-link>customize the order of all AWPCP fields</a>.', 'awpcp-extra-fields' );
		$message = str_replace( '<order-fields-link>', $order_fields_link, $message );

		$output .= '<p>' . $message . '</p>';

		// Setup the table
		$output .= "<table class=\"widefat\" cellspacing=\"0\">";
		$output .= "<thead>";
		$output .= "<tr>";
		$output .= "<th scope=\"col\" class=\"manage-column\">";
		$output .= __("Name",'awpcp-extra-fields' );
		$output .= "</th>";
		$output .= "<th scope=\"col\" class=\"manage-column\">";
		$output .= __("Post Label",'awpcp-extra-fields' );
		$output .= "</th>";
		$output .= "<th scope=\"col\" class=\"manage-column\">";
		$output .= __("View Label",'awpcp-extra-fields' );
		$output .= "</th>";
		$output .= "<th scope=\"col\" class=\"manage-column\">";
		$output .= __("Input Type",'awpcp-extra-fields' );
		$output .= "</th>";
		$output .= "<th scope=\"col\" class=\"manage-column\">";
		$output .= __("Data Type",'awpcp-extra-fields' );
		$output .= "</th>";
		$output .= "<th scope=\"col\" class=\"manage-column\">";
		$output .= __("Options",'awpcp-extra-fields' );
		$output .= "</th>";
		$output .= "<th scope=\"col\" class=\"manage-column\">";
		$output .= __("Validation",'awpcp-extra-fields' );
		$output .= "</th>";
		$output .= "<th scope=\"col\" class=\"manage-column\">";
		$output .= __("Privacy",'awpcp-extra-fields' );
		$output .= "</th>";
		$output .= "<th scope=\"col\" class=\"manage-column\">";
		$output .= __("Categories",'awpcp-extra-fields' );
		$output .= "</th>";
		$output .= "<th scope=\"col\" class=\"manage-column\">";
		$output .= __("Action",'awpcp-extra-fields' );
		$output .= "</th>";
		$output .= "</tr>";
		$output .= "</thead>";
		$output .= "<tfoot>";
		$output .= "<tr>";

		$output .= "<th scope=\"col\" class=\"manage-column\">";
		$output .= __("Name",'awpcp-extra-fields' );
		$output .= "</th>";
		$output .= "<th scope=\"col\" class=\"manage-column\">";
		$output .= __("Post Label",'awpcp-extra-fields' );
		$output .= "</th>";
		$output .= "<th scope=\"col\" class=\"manage-column\">";
		$output .= __("View Label",'awpcp-extra-fields' );
		$output .= "</th>";
		$output .= "<th scope=\"col\" class=\"manage-column\">";
		$output .= __("Input Type",'awpcp-extra-fields' );
		$output .= "</th>";
		$output .= "<th scope=\"col\" class=\"manage-column\">";
		$output .= __("Data Type",'awpcp-extra-fields' );
		$output .= "</th>";
		$output .= "<th scope=\"col\" class=\"manage-column\">";
		$output .= __("Options",'awpcp-extra-fields' );
		$output .= "</th>";
		$output .= "<th scope=\"col\" class=\"manage-column\">";
		$output .= __("Validation",'awpcp-extra-fields' );
		$output .= "</th>";
		$output .= "<th scope=\"col\" class=\"manage-column\">";
		$output .= __("Privacy",'awpcp-extra-fields' );
		$output .= "</th>";
		$output .= "<th scope=\"col\" class=\"manage-column\">";
		$output .= __("Category",'awpcp-extra-fields' );
		$output .= "</th>";
		$output .= "<th scope=\"col\" class=\"manage-column\">";
		$output .= __("Action",'awpcp-extra-fields' );
		$output .= "</th>";
		$output .= "</tr>";
		$output .= "</tfoot>";
		$output .= "<tbody>";

		$fields = awpcp_get_extra_fields();

		foreach ($fields as $field) {
			if (strcasecmp($field->field_mysql_data_type, 'VARCHAR') == 0) {
				$input_type = 'Short Text';
			} else if (strcasecmp($field->field_mysql_data_type, 'TEXT') == 0) {
				$input_type = 'Long Text';
			} else if (strcasecmp($field->field_mysql_data_type, 'INT') == 0) {
				$input_type = 'Whole Number';
			} else if (strcasecmp($field->field_mysql_data_type, 'FLOAT') === 0) {
				$input_type = 'Decimal Number';
			}
			$input_type = $field->field_input_type;

			$output .= "<tr><td>{$field->field_name}</td>";
			$output .= "<td>" . stripslashes_deep($field->field_label) . "</td>";
			$output .= "<td>" . stripslashes_deep($field->field_label_view) . "</td>";
			$output .= "<td>{$input_type}</td>";
			$output .= "<td>{$field->field_mysql_data_type}</td>";
			$output .= "<td>";
			$output .= awpcp_get_comma_separated_list( (array) $field->field_options );
			$output .= "</td>";

			$required_text = __('required', 'awpcp-extra-fields' );
			if ($field->required && $field->field_validation) {
				$output .= "<td>{$field->field_validation} ({$required_text})</td>";
			} else if ($field->required) {
				$output .= "<td>{$required_text}</td>";
			} else {
				$output .= "<td>{$field->field_validation}</td>";
			}

			$output .= "<td>{$field->field_privacy}</td>";

			$output .= '<td>';
			$categories = awpcp_extra_fields_get_field_categoires( $field );

			if ( count( $categories ) == countcategories() ) {
				$output .= __( 'All', 'awpcp-extra-fields' );
			} else {
				$output .= awpcp_get_comma_separated_categories_list( $categories );
			}
			$output .= '</td>';

			$output .= "<td><a href=\"?page=Configure5&action=edit&id={$field->field_id}\">";
			$output .= __("Edit",'awpcp-extra-fields' );
			$output .= "</a> | <a href=\"?page=Configure5&action=delete&id={$field->field_id}\">";
			$output .= __("Delete",'awpcp-extra-fields' );
			$output .= "</a>";

			$output .= "</td></tr>";
		}

		// while ($rsrow=mysql_fetch_row($res)) {
		// 	if($rsrow[5] == 'VARCHAR'){ $rsrow[4] = "Short text";}
		// 	if($rsrow[5] == 'TEXT'){ $rsrow[4] = "Long text";}
		// 	if($rsrow[5] == 'INT'){ $rsrow[4] = "Number";}
		// 	if($rsrow[5] == 'float'){ $rsrow[4] = "Money";}

		// 	$output .= "<tr><td>$rsrow[1]</td>";
		// 	$output .= "<td>$rsrow[2]</td>";
		// 	$output .= "<td>$rsrow[3]</td>";
		// 	$output .= "<td>$rsrow[4]</td>";
		// 	$output .= "<td>$rsrow[5]</td>";
		// 	$output .= "<td>$rsrow[6]</td>";
		// 	$output .= "<td>$rsrow[7]</td>";
		// 	$output .= "<td>$rsrow[8]</td>";
		// 	$output .= "<td>$rsrow[9]</td>";
		// 	$output .= "<td><a href=\"?page=Configure5&action=edit&id=$rsrow[0]\">";
		// 	$output .= __("Edit",'awpcp-extra-fields' );
		// 	$output .= "</a> | <a href=\"?page=Configure5&action=delete&id=$rsrow[0]\">";
		// 	$output .= __("Delete",'awpcp-extra-fields' );
		// 	$output .= "</a>";
		// 	$output .= "</td></tr>";
		// }

		$output .= "</tbody></table>";
	}
	else
	{

		$output .= __("It appears you have not added any extra fields yet. Start adding extra fields using the link below",'awpcp-extra-fields' );
		$output .= $add_button;
		$output .= "</p>";
	}

	// Get the fields
	return $output;
}

function awpcp_extra_fields_get_field_categoires($field) {
	$categories = array_filter( $field->field_category );
	if ( empty( $categories ) ) {
		return array();
	} else {
		return AWPCP_Category::find( array( 'id' => $field->field_category ) );
	}
}

function awpcp_extra_fields_max_field_weight() {
	global $wpdb;
	$sql = "SELECT MAX(weight) FROM " . AWPCP_TABLE_EXTRA_FIELDS;
	$weight = $wpdb->get_var($sql);
	return is_null($weight) ? 0 : $weight;
}

/**
 * @deprecated 3.4.1
 */
function awpcp_extra_fields_get_previous_field($field) {
	_deprecated_function( __FUNCTION__, '3.4.1', 'AWPCP_FormFields::get_fields_order' );
	global $wpdb;

	$query = 'SELECT field_id FROM ' . AWPCP_TABLE_EXTRA_FIELDS . ' ';
	$query.= 'WHERE weight < %d ORDER BY weight DESC, field_id DESC LIMIT 0, 1';
	$query = $wpdb->prepare($query, $field->weight);

	$results = $wpdb->get_row($query);

	if (count($results) > 0)
		return awpcp_get_extra_field($results->field_id);
	return null;
}

/**
 * @deprecated 3.4.1
 */
function awpcp_extra_fields_get_next_field($field) {
	_deprecated_function( __FUNCTION__, '3.4.1', 'AWPCP_FormFields::get_fields_order' );
	global $wpdb;

	$query = 'SELECT field_id FROM ' . AWPCP_TABLE_EXTRA_FIELDS . ' ';
	$query.= 'WHERE weight > %d ORDER BY weight ASC, field_id ASC LIMIT 0, 1';
	$query = $wpdb->prepare($query, $field->weight);

	$results = $wpdb->get_row($query);

	if (count($results) > 0)
		return awpcp_get_extra_field($results->field_id);
	return null;
}

function load_the_extra_fields_form($awpcp_x_field_id, $awpcp_x_field_name, $awpcp_x_field_label,
	$awpcp_x_field_label_view, $awpcp_x_field_input_type, $awpcp_x_field_mysqldata_type,
	$awpcp_x_field_options, $awpcp_x_field_validation, $awpcp_x_field_privacy, 
	$awpcp_x_field_category, $x_error_msg, $awpcp_x_field_nosearch=0, $show_on_listings=false, $required=0)
{
	global $wpdb, $message;

	$output = '';

	if (isset($x_error_msg) && !empty($x_error_msg)) {
		$output .= awpcp_print_error( $x_error_msg );
	}

	wp_enqueue_script('awpcp-extra-fields-admin');

	$output .= "<p><a href=\"?page=Configure5\">";
	$output .= __("View Current Fields",'awpcp-extra-fields' );
	$output .= "</a>";

	$output .= "<form class=\"awpcp-extra-fields-form\" method=\"post\">";
	$output .= "<p>";
    $output .= '<label for="awpcp-extra-field-name">' . __( 'Field Name', 'awpcp-extra-fields' ) . '</label><br/>';
    $output .= "<input id=\"awpcp-extra-field-name\" type=\"text\" name=\"awpcp_extra_field_name\" style=\"width:50%;\" value=\"$awpcp_x_field_name\" />";
    $output .= '<br/>';
    $output .= '<span>' . __( 'An internal name for the field that will not be shown to users. Alphanumeric and underscore charcteres are allowed only (a-z, A-Z, 0-9, _) and it must start with a letter or an underscore.', 'awpcp-extra-fields' ) . '</span>';
	$output .= "</p>";
	$output .= "<p>";
	$output .= __("Field Post Label",'awpcp-extra-fields' );
	$output .= " (";
	$output .= __("Text that tells the user what they need to enter or select in the form.",'awpcp-extra-fields' );
	$output .= ")";
	$output .= "<br/>";
	$output .= "<input type=\"text\" name=\"awpcp_extra_field_label\" style=\"width:50%;\" value=\"$awpcp_x_field_label\" />";
	$output .= "</p>";
	$output .= "<p>";
	$output .= __("Field View Label",'awpcp-extra-fields' );
	$output .= " (";
	$output .= __("Text to use when displaying the field data on the ad view page.",'awpcp-extra-fields' );
	$output .= ")";
	$output .= "<br/>";
	$output .= "<input type=\"text\" name=\"awpcp_extra_field_label_view\" style=\"width:50%;\" value=\"$awpcp_x_field_label_view\" />";
	$output .= "</p>";
	$output .= "<p>";
	$output .= __("Field Input Element Type",'awpcp-extra-fields' );
	$output .= " (";
	$output .= __("What input element type should be used to collect the data from user?",'awpcp-extra-fields' );
	$output .= ")";
	$output .= "</p><p>";
	$output .= " <select name=\"awpcp_extra_field_input_type\">";
	$output .= "<option value=\"\">";
	$output .= __("Select Field Input Type",'awpcp-extra-fields' );
	$output .= "</option>";

	if($awpcp_x_field_input_type == 'Input Box'){ $aef_inputtype_selected1=" selected='selected'";} else { $aef_inputtype_selected1=''; }
	if($awpcp_x_field_input_type == 'Select'){ $aef_inputtype_selected2=" selected='selected'";} else { $aef_inputtype_selected2=''; }
	if($awpcp_x_field_input_type == 'Select Multiple'){ $aef_inputtype_selected3=" selected='selected'";} else { $aef_inputtype_selected3=''; }
	if($awpcp_x_field_input_type == 'Radio Button'){ $aef_inputtype_selected4=" selected='selected'";} else { $aef_inputtype_selected4=''; }
	if($awpcp_x_field_input_type == 'Checkbox'){ $aef_inputtype_selected5=" selected='selected'";} else { $aef_inputtype_selected5=''; }
	if($awpcp_x_field_input_type == 'Textarea Input'){ $aef_inputtype_selected6=" selected='selected'";} else { $aef_inputtype_selected6=''; }
	if($awpcp_x_field_input_type == 'DatePicker'){ $aef_inputtype_selected7=' selected="selected"';} else { $aef_inputtype_selected7=''; }


	$output .= "<option value=\"Input Box\" $aef_inputtype_selected1>";
	$output .= __("Input Text Box",'awpcp-extra-fields' );
	$output .= "</option>";
	$output .= "<option value=\"Select\" $aef_inputtype_selected2>";
	$output .= __("Select List",'awpcp-extra-fields' );
	$output .= "</option>";
	$output .= "<option value=\"Select Multiple\" $aef_inputtype_selected3>";
	$output .= __("Multiple Select List",'awpcp-extra-fields' );
	$output .= "</option>";
	$output .= "<option value=\"Radio Button\" $aef_inputtype_selected4>";
	$output .= __("Radio Button",'awpcp-extra-fields' );
	$output .= "</option>";
	$output .= "<option value=\"Checkbox\" $aef_inputtype_selected5>";
	$output .= __("Checkbox",'awpcp-extra-fields' );
	$output .= "</option>";
	$output .= "<option value=\"Textarea Input\"  $aef_inputtype_selected6>";
	$output .= __("Textarea",'awpcp-extra-fields' );
	$output .= "</option>";
	$output .= "<option value=\"DatePicker\"  $aef_inputtype_selected7>";
	$output .= __("DatePicker",'awpcp-extra-fields' );
	$output .= "</option>";
	$output .= "</select>";
	$output .= "</p><p>";
	$output .= __("Field MYSQL Data Type", 'awpcp-extra-fields' );
	$output .= " (";
	$output .= __("Select Number for values that must save as numbers, money for money values, short text for string values under 300 characters and long text for string values over 300 characters",'awpcp-extra-fields' );
	$output .= ")";
	$output .= "</p><p>";
	$output .= " <select name=\"awpcp_extra_field_mysqldata_type\">";
	$output .= "<option value=\"\">";
	$output .= __("Select Field MYSQL Data Type",'awpcp-extra-fields' );
	$output .= "</option>";

	if($awpcp_x_field_mysqldata_type == 'INT'){ $aef_mysqldt_selected1=" selected='selected'";} else {$aef_mysqldt_selected1='';}
	if($awpcp_x_field_mysqldata_type == 'FLOAT'){ $aef_mysqldt_selected2=" selected='selected'";} else {$aef_mysqldt_selected2='';}
	if($awpcp_x_field_mysqldata_type == 'VARCHAR'){ $aef_mysqldt_selected3=" selected='selected'";} else {$aef_mysqldt_selected3='';}
	if($awpcp_x_field_mysqldata_type == 'TEXT'){ $aef_mysqldt_selected4=" selected='selected'";} else {$aef_mysqldt_selected4='';}

	$output .= "<option value=\"INT\" $aef_mysqldt_selected1>";
	$output .= __("Whole Number",'awpcp-extra-fields' );
	$output .= "</option>";
	$output .= "<option value=\"FLOAT\" $aef_mysqldt_selected2>";
	$output .= __("Decimal (Money and other floating values. Uses FLOAT)",'awpcp-extra-fields' );
	$output .= "</option>";
	$output .= "<option value=\"VARCHAR\" $aef_mysqldt_selected3>";
	$output .= __("Short Text",'awpcp-extra-fields' );
	$output .= "</option>";
	$output .= "<option value=\"TEXT\" $aef_mysqldt_selected4>";
	$output .= __("Long Text",'awpcp-extra-fields' );
	$output .= "</option>";
	$output .= "</select>";
	$output .= "</p><p>";
	$output .= __("Field Options",'awpcp-extra-fields' );
	$output .= __(" for drop down lists, radio buttons, checkboxes ",'awpcp-extra-fields' );
	$output .= "(";
	$output .= __("type an option in each line",'awpcp-extra-fields' );
	$output .= ")<br/>";
	$output .= "<textarea name=\"awpcp_extra_field_options\" style=\"width:90%;\" rows=\"7\"/>";
	$output .= join("\n", (array) $awpcp_x_field_options);
	$output .= "</textarea>";

	$output .= "<p>";
	$output .= '<label for="awpcp-extra-field-validator">' . __("Validate Against", 'awpcp-extra-fields' ) . '</label>';
	$output .= ':&nbsp;<select id="awpcp-extra-field-validator" name="awpcp_extra_field_validation">';
	$output .= "<option value=\"\">";
	$output .= __("Select Option",'awpcp-extra-fields' );
	$output .= "</option>";

	if($awpcp_x_field_validation == 'email'){ $aef_validation_selected1=" selected='selected'";} else {$aef_validation_selected1='';}
	if($awpcp_x_field_validation == 'url'){ $aef_validation_selected2=" selected='selected'";} else {$aef_validation_selected2='';}
	if($awpcp_x_field_validation == 'missing'){ $aef_validation_selected3=" selected='selected'";} else {$aef_validation_selected3='';}
	if($awpcp_x_field_validation == 'numericdeci'){ $aef_validation_selected4=" selected='selected'";} else {$aef_validation_selected4='';}
	if($awpcp_x_field_validation == 'numericnodeci'){ $aef_validation_selected5=" selected='selected'";} else {$aef_validation_selected5='';}
	if($awpcp_x_field_validation == 'currency'){ $aef_validation_selected6=" selected='selected'";} else {$aef_validation_selected6='';}

	$output .= "<option value=\"email\" $aef_validation_selected1>";
	$output .= __( 'Email','awpcp-extra-fields' );
	$output .= "</option>";
	$output .= "<option value=\"url\" $aef_validation_selected2>";
	$output .= __( 'URL','awpcp-extra-fields' );
	$output .= "</option>";
	$output .= "<option value=\"currency\" $aef_validation_selected6>";
	$output .= __( 'Currency','awpcp-extra-fields' );
	$output .= "</option>";
	$output .= "<option value=\"missing\" $aef_validation_selected3>";
	$output .= __("Missing Value",'awpcp-extra-fields' );
	$output .= "</option>";
	$output .= "<option value=\"numericdeci\" $aef_validation_selected4>";
	$output .= __("Numeric decimal allowed",'awpcp-extra-fields' );
	$output .= "</option>";
	$output .= "<option value=\"numericnodeci\" $aef_validation_selected5>";
	$output .= __("Numeric no decimal allowed",'awpcp-extra-fields' );
	$output .= "</option>";
	$output .= "</select>";
	$output .= "</p>";

	$output .= '</p><input type="hidden" value="0" name="awpcp-extra-field-required">';
	if ($required) {
		$output .= '<input id="awpcp-extra-field-required" type="checkbox" value="1" name="awpcp-extra-field-required" checked="checked">';
	} else {
		$output .= '<input id="awpcp-extra-field-required" type="checkbox" value="1" name="awpcp-extra-field-required">';
	}
	$output .= '&nbsp;<label for="awpcp-extra-field-required">' . __("Required (Check if this field should always have a value).", 'awpcp-extra-fields' ) . '</label>';
	$output .= "</p>";

	$output .= "<p>";
	$output .= '<label for="awpcp-extra-field-privacy">' . __("Privacy", 'awpcp-extra-fields' ) . '</label>';
	$output .= ':&nbsp;<select id="awpcp-extra-field-privacy" name="awpcp_extra_field_privacy">';
	$output .= '<option value="public"' . ( $awpcp_x_field_privacy == 'public' ? ' selected="selected"' : '' ) . '>' . __("Public",'awpcp-extra-fields' ) . '</option>';
    $output .= '<option value="restricted"' . ( $awpcp_x_field_privacy == 'restricted' ? ' selected="selected"' : '' ) . '>' . __( 'Restricted', 'awpcp-extra-fields' ). '</option>';
	$output .= '<option value="private"' . ( $awpcp_x_field_privacy == 'private' ? ' selected="selected"' : '' ) . '>' . __("Private",'awpcp-extra-fields' ). '</option>';
	$output .= "</select>";
	$output .= '<br><em>' . __("If you want to collect information from users but don't want it to be displayed publicly, choose Private. Choose Restricted if you want to show the information to logged in users only.", 'awpcp-extra-fields' ) . '</em>';
	$output .= "</p>";

	$output .= '<div>';
	$output .= '<label>' . __( 'Categories', 'awpcp-extra-fields' ) . '</label>:';
	$output .= '&nbsp;<a href="#" data-categories="all">' . _x( 'All', 'all categories', 'awpcp-extra-fields' ) . '</a>';
	$output .= '&nbsp;<a href="#" data-categories="none">' . _x( 'None', 'no categories', 'awpcp-extra-fields' ) . '</a>';
	$output .= '<div class="category-checklist">';

    $params = array(
    	'field_name' => 'awpcp_extra_field_category',
        'selected' => $awpcp_x_field_category,
    );
    $output .= awpcp_categories_checkbox_list_renderer()->render( $params );

	$output .= '</div>';
	$output .= '<em>' . __( 'This field will appear only in the selected categories.', 'awpcp-extra-fields' ) . '</em>';
	$output .= '</div>';

	if ('' == $show_on_listings) {
		$show_on_listings = 2;
	}
	$output .= "<p>";
	$output .= '<label for="awpcp-extra-field-show-on">' . __("Show this field to the user on",'awpcp-extra-fields' ) . '</label>';
	$output .= ":&nbsp;<select id=\"awpcp-extra-field-show-on\" name=\"awpcp_extra_field_listings\">";
	$output .= '<option value="1"'; if (1 == $show_on_listings) $output .= ' selected="selected" '; $output .= ">";
	$output .= __("Ad Listing Display",'awpcp-extra-fields' );
	$output .= "</option>";
	$output .= "<option value='2'"; if (2 == $show_on_listings) $output .= ' selected="selected" '; $output .= ">";
	$output .= __("Single Ad Display",'awpcp-extra-fields' );
	$output .= "</option>";
	$output .= "<option value='3'"; if (3 == $show_on_listings) $output .= ' selected="selected" '; $output .= ">";
	$output .= __("Both",'awpcp-extra-fields' );
	$output .= "</option>";
	$output .= "</select>.</p>";

	$output .= "<p>";
	if (0 != $awpcp_x_field_nosearch) {
		$checked = "checked='checked'";
	} else {
		$checked = '';
	}
	$output .= '<input type="hidden" name="awpcp_extra_field_nosearch" value="0">';
	$output .= '<input id="awpcp-extra-field-do-not-show-in-search" type="checkbox" name="awpcp_extra_field_nosearch" value="1" ' . $checked . '>';
	$output .= '&nbsp;<label for="awpcp-extra-field-do-not-show-in-search">' . __('Do not show this field on the search form.', 'awpcp-extra-fields' ) . '</label>';
	$output .= "</p>";

	$output .= "<br/>";
	$output .= "<input type=\"hidden\" name=\"action\" value=\"savefielddata\" />";
	$output .= "<input type=\"hidden\" name=\"awpcp_extra_field_id\" value=\"$awpcp_x_field_id\" />";
	$output .= "<input type=\"hidden\" name=\"awpcp_x_field_name_old\" value=\"$awpcp_x_field_name\" />";

	$submit = empty($awpcp_x_field_id) ? __("Add New Field",'awpcp-extra-fields' ) : __("Update Field",'awpcp-extra-fields' );
	$output .= '<input type="submit" value="' . $submit . '" class="button-primary" id="submit" name="updateextrafield">';

	$output .= "</form>";

	return $output;
}

function build_x_field_item_vars($x_field_item_array)
{
	$sanitized_array = array();
	foreach($x_field_item_array as $var) {
		$sanitized_array[$var] = clean_field($_REQUEST[$var]);
	}
}


/**
 * Renders a single value of the given Extra Field applying
 * proper format depending on the fields validator.
 *
 * Only URL validator is supported so far.
 *
 * @since  2.0.5
 */
function awpcp_extra_fields_render_field_single_value($field, $value) {
	$v = stripslashes_deep( trim( $value ) );

	if ($field->field_validation == 'url') {
		return sprintf('<a href="%s" target="_blank">%s</a>', esc_attr($value), esc_html($value));
	} else if ($field->field_validation == 'email') {
		return sprintf('<a href="mailto:%s" target="_blank">%s</a>', esc_attr($value), esc_html($value));
	} else if ( $field->field_validation == 'currency' ) {
		return awpcp_format_money( $v );
	} else if ( $field->field_input_type == 'DatePicker' ) {
		return awpcp_datetime( 'awpcp-date', $v );
	} else {
		return nl2br( esc_html( $v ) );
	}
}


function awpcp_extra_fields_render_field($field, $value, $category, $context, $errors=array()) {
	$label = stripslashes_deep($field->field_label);
	$name = "awpcp-{$field->field_name}";
	$type = $field->field_input_type;
	$id = $name;

	$classes = array_filter(array(
		'awpcp-form-spacer',
		'awpcp-extra-field',
		"awpcp-extra-field-{$field->field_name}",
	));

	foreach ( $field->field_category as $category_id ) {
		$classes[] = "awpcp-extra-field-category-{$category_id}";
	}

	$all_categories = awpcp_get_categories_ids();

	// always show field if no category is selected or all categories are selected
	if ( empty( $field->field_category ) || count( array_diff( $all_categories, $field->field_category ) ) == 0 ) {
		$classes[] = 'awpcp-extra-field-always-visible';
	// hide fields that belong to a different category
	} else if ( !empty( $category ) && !in_array( $category, $field->field_category ) ) {
		$classes[] = 'awpcp-extra-field-hidden';
	}

	$validators = array();
	switch ($field->field_validation) {
		case 'email':
			$validators[] = 'email';
			break;
		case 'url':
			$validators[] = 'url';
			break;
		case 'currency':
			$validators[] = 'money';
			break;
		case 'missing':
			$validators[] = 'required';
			break;
		case 'numericdeci':
			$validators[] = 'number';
			break;
		case 'numericnodeci':
			$validators[] = 'integer';
			break;
	}

	if ($context != 'search' && $field->required && $field->field_validation != 'missing') {
		$validators[] = 'required';
	}

	$validator = join(' ', $validators);

	/* Range Search support */

	$has_numeric_data_type = in_array( $field->field_mysql_data_type, array( 'INT', 'FLOAT' ) );
	$has_numeric_validator = in_array( $field->field_validation, array( 'numericdeci', 'numericnodeci' ) );
	$is_single_valued = in_array( $type, array( 'Input Box', 'Select', 'Radio Button' ) );

	// TODO: pass min and max values
	if ( $context == 'search' && $is_single_valued && ( $has_numeric_validator || $has_numeric_data_type ) ) {
		$id = "awpcp-extra-field-{$name}-min";

		$body = '<span class="awpcp-range-search">';
		$body.= '<label for="%1$s-min">' . __( 'Min', 'awpcp-extra-fields' ) . '</label>';
		$body.= '<input id="%1$s-min" class="awpcp-textfield inputbox %2$s" type="text" name="%3$s-min" value="%4$s">';
		$body.= '<label for="%1$s-max">' . __( 'Max', 'awpcp-extra-fields' ) . '</label>';
		$body.= '<input id="%1$s-max" class="awpcp-textfield inputbox %2$s" type="text" name="%3$s-max" value="%5$s">';
		$body.= '</span>';

		if ( !is_array( $value ) ) {
			$value = stripslashes_deep( array( 'min' => $value, 'max' => $value ) );
		}

		$body = sprintf($body, "awpcp-extra-field-{$name}", $validator, $name, $value['min'], $value['max']);

	/* Normal Input */

	} else if ($type == 'Input Box') {
		$body = '<input id="%s" class="awpcp-textfield inputbox %s" type="text" name="%s" value="%s">';
		$body = sprintf($body, $name, $validator, $name, stripslashes($value));

	} else if ($type == 'Select') {
		$options = array(sprintf('<option value="">%s</option>', __('Select One', 'awpcp-extra-fields' )));

		foreach ($field->field_options as $option) {
			$option = trim($option);
			$_html = '<option %s value="%s">%s</option>';
			$_html = sprintf($_html, $value == $option ? 'selected' : '',
									 awpcp_esc_attr($option),
									 stripslashes($option));
			$options[] = $_html;
		}

		$body = sprintf('<select id="%s" class="%s" name="%s">%s</select>', $name, $validator, $name, join('', $options));

	} else if ($type == 'Textarea Input') {
		$body = '<textarea id="%s" class="awpcp-textarea %s" name="%s" rows="10" cols="50">%s</textarea>';
		$body = sprintf($body, $name, $validator, $name, awpcp_esc_textarea($value));

	} else if ($type == 'Radio Button') {
		$options = array();

		foreach ($field->field_options as $option) {
			$_html = '<label class="secondary-label"><input class="%s" type="radio" %s name="%s" value="%s">%s</label><br>';
			$_html = sprintf($_html, $validator,
									 $value == $option ? 'checked' : '',
									 $name,
									 awpcp_esc_attr($option),
									 stripslashes($option));
			$options[] = $_html;
		}

		$body = join('', $options);

	} else if ($type == 'Select Multiple') {
		$value = is_array($value) ? $value : explode(',', $value);
		$options = array();

		foreach ($field->field_options as $option) {
			$option = trim($option);
			$_html = '<option %s value="%s">%s</option>';
			$options[] = sprintf($_html, in_array($option, $value) ? 'selected' : '',
										 awpcp_esc_attr($option),
										 stripslashes($option));
		}

		$body = '<select id="%s" class="%s" name="%s[]" multiple style="width:25%%;height:100px">%s</select>';
		$body = sprintf($body, $name, $validator, $name, join('', $options));

	} else if ($type == 'Checkbox') {
		$value = is_array($value) ? $value : explode(',', $value);
		$options = array();

		foreach ($field->field_options as $option) {
			$_html = '<label class="secondary-label"><input class="%s" type="checkbox" %s name="%s[]" value="%s">%s</label><br>';
			$options[] = sprintf($_html, $validator,
										 in_array($option, $value) ? 'checked' : '',
										 $name,
										 awpcp_esc_attr($option),
										 stripslashes($option));
		}

		$body = join('', $options);
	}

	$error = awpcp_form_error($field->field_name, $errors);

	$html = '<p class="%s" data-category="%s"><label for="%s">%s</label>%s %s</p>';
	return sprintf( $html, join( ' ', $classes ), join( ',', $field->field_category ), $id, $label, $body, $error );
}


function awpcp_extra_fields_render_form($params, $values, $context='normal', $errors=array()) {
	wp_enqueue_script('awpcp-extra-fields');

	$params = wp_parse_args($params, array(
		'category' => 0,
		'ad' => 0,
	));

	$conditions = awpcp_get_extra_fields_conditions( array( 'context' => $context ) );
	if ( !empty( $conditions ) ) {
		$where = 'WHERE ' . join(' AND ', $conditions);
	} else {
		$where = false;
	}

	$order = 'ORDER BY weight ASC, field_id ASC';

	// TODO: sort fields by weight, category id and id
	$fields = awpcp_get_extra_fields( $where, $order );

	$html = array();
	foreach ($fields as $field) {
		$name = $field->field_name;

		if ( isset( $values[ $name ] ) ) {
			$value = maybe_unserialize($values[$name]);
		} else if ( isset( $_POST[ "awpcp-{$name}-min" ] ) || isset( $_GET[ "awpcp-{$name}-min" ] ) ) {
			$value = array(
				'min' => awpcp_request_param( "awpcp-{$name}-min", null ),
				'max' => awpcp_request_param( "awpcp-{$name}-max", null ),
			);
		} else if ( isset( $_POST[ "awpcp-{$name}" ] ) || isset( $_GET[ "awpcp-{$name}" ] ) ) {
			$value = awpcp_request_param( "awpcp-{$name}" );
		} else if ( $params['ad'] ) {
			$value = get_field_value($params['ad'], $name);
		} else {
			$value = '';
		}

		$html[] = awpcp_extra_fields_render_field($field, $value, $params['category'], $context, $errors);
	}

	return join("\n", $html);
}


/**
 * @since 1.0
 */
function get_field_value($adid, $x_field_name) {
	global $wpdb;
	$query = "SELECT `{$x_field_name}` FROM " . AWPCP_TABLE_ADS . " WHERE ad_id = %d";
	$value = $wpdb->get_var($wpdb->prepare($query, $adid));
	return maybe_unserialize($value);
}


function validate_extra_fields_form($category = 0) {
	$fields = awpcp_get_extra_fields_by_category($category, array( 'context' => 'details' ));

	$data = array();
	foreach ($fields as $field) {
		$value = awpcp_extra_fields_clean_field_value( $field, awpcp_post_param( "awpcp-{$field->field_name}" ) );
		$data[ $field->field_name ] = $value;
	}

	$errors = array();

	foreach ($fields as $field) {
		// a Field is required if the Required checkbox has been marked or the
		// Missing validator being assigned to that field.
		$required = $field->required || $field->field_validation == 'missing';

		// skip unused fields for current category
		if (!in_array($category, $field->field_category) && !in_array('root', $field->field_category)) {
			continue;
		}

		$validation = $field->field_validation;
		$label = $field->field_label;
		$values = (array) awpcp_array_data($field->field_name, '', $data);

		foreach ($values as $k => $item) {
			if ($required && empty($item)) {
				$errors[ $field->field_name ] = sprintf( __( '%s is required.', 'awpcp-extra-fields' ), $label );
				continue;
			} else if (!$required && empty($item)) {
				continue;
			}

			if ($validation == 'missing') {
				if (empty($item)) {
					$errors[ $field->field_name ] = sprintf( __( '%s is required.', 'awpcp-extra-fields' ), $label );
				}

			} elseif ($validation == 'url') {
				if ( !isValidURL($item) ) {
					$message = __("%s is badly formatted. Valid URL format required. Include http://", 'awpcp-extra-fields' );
					$errors[$field->field_name] = sprintf($message, $label);
				}

			} elseif ($validation == 'email') {
				if ( ! awpcp_is_valid_email_address( $item ) ) {
					$message = __("%s is badly formatted. Valid Email format required.", 'awpcp-extra-fields' );
					$errors[$field->field_name] = sprintf($message, $label);
				}

			} elseif ($validation == 'numericdeci') {
				if ( !is_numeric($item) ) {
					$message = __("%s must be a number.", 'awpcp-extra-fields' );
					$errors[$field->field_name] = sprintf($message, $label);
				}

			} elseif ($validation == 'numericnodeci') {
				if ( !ctype_digit($item) ) {
					$message = __("%s must be a number. Decimal values not allowed.", 'awpcp-extra-fields' );
					$errors[$field->field_name] = sprintf($message, $label);
				}
			}
		}
	}

	return $errors;
}

function x_fields_fetch_fields() {
	$fields = awpcp_get_extra_fields();
	return awpcp_get_properties($fields, 'field_name');
}

/**
 * Prepares an extra field's value for being stored in the database.
 */
function awpcp_extra_fields_field_value($field, $value) {
    $field_type = strtolower( $field->field_input_type );
    $value = awpcp_extra_fields_clean_field_value( $field, $value );

    if ( in_array( $field_type, array( 'checkbox', 'select multiple' ) ) ) {
        $value = maybe_serialize( $value );
    }

    return $value;
}

/**
 * @since 3.6.3
 */
function awpcp_extra_fields_clean_field_value( $field, $value ) {
    $field_type = strtolower( $field->field_input_type );

    switch ( $field_type ) {
        case 'checkbox':
        case 'select multiple':
            // strip slashes added by WP to the $_REQUEST array
            $value = stripslashes_deep( $value );

            if ( is_array( $value ) ) {
                // reset numeric indexes
                $value = array_merge( array(), array_filter( $value, 'strlen' ) );
            } else if ( ! empty( $value ) ) {
                $value = explode( ',', $value );
            }

            break;

        case 'input box':
        case 'textarea input':
        case 'select':
        case 'radio button':
        case 'datepicker':
        default:
            $value = stripslashes_deep( awpcp_extra_fields_module()->parse_field_posted_data( $field, $value ) );
            break;
    }

    return $value;
}

/**
 * @deprecated since 2.0.4-1
 */
function do_x_fields_update() {
	$fields = awpcp_get_extra_fields();

	$update_x_fields = '';
	foreach ($fields as $field) {
		$value = awpcp_request_param($field->field_name);
		$value = awpcp_extra_fields_field_value($field, $value);
		$update_x_fields .= "`{$field->field_name}`='$value',";
	}

	return $update_x_fields;
}

function display_x_fields_data($adid, $single=true) {
	global $wpdb;

	$ad = AWPCP_Ad::find_by_id($adid);

	$fields = awpcp_get_extra_fields_by_category( $ad->ad_category_id, array(
		'hide_private' => true,
		'context' => $single ? 'single' : 'listings'
	) );

	$show_empty = get_awpcp_option('show-empty-extra-fields-in-ads');

	$visible = array();
	foreach ($fields as $i => $field) {
		$value = (array) get_field_value($adid, $field->field_name);
		if ($show_empty || (!empty($value) && strlen($value[0]) > 0)) {
			$visible[] = array('field' => $field, 'value' => $value);
		}
	}

	$allow_html_in_labels = get_awpcp_option( 'allow-html-in-extra-field-labels' );

	$count = count($visible);
	$columns = get_awpcp_option('display-extra-fields-in-columns', 1);
	$rows = ceil($count / $columns);
	$shown = 0;

	$classes = array(
		'awpcp-extra-fields',
		'awpcp-extra-fields-columns-' . $columns,
		'clearfix',
	);

	$html = '<div class="' . join( ' ', $classes ) . '">';

	foreach ($visible as $i => $data) {
		$field = $data['field'];
		$value = $data['value'];

		$classes = array( 'cladinfo', 'awpcp-extra-field-' . $field->field_name );
		$css = awpcp_get_grid_item_css_class( $classes, $shown, $columns, $rows);

		$field_label = stripslashes( $field->field_label_view );
		$field_label = $allow_html_in_labels ? $field_label : esc_html( $field_label );

		if ($show_empty || (!empty($value) && strlen($value[0]) > 0)) {
			$html .= '<div class="' . esc_attr( join( ' ', $css ) ) . '">';
			$html .= '<label>' . $field_label  . ':</label> ';

			if (count($value) > 1) {
				$html .= '<ul class="awpcp-extra-field-value-list">';
				foreach ($value as $v) {
					$v = awpcp_extra_fields_render_field_single_value($field, $v);
					$html .= '<li>' . $v . '</li>';
				}
				$html .= '</ul>';
			} else if (count($value) > 0) {
                $value = awpcp_extra_fields_render_field_single_value( $field, reset( $value ) );
                if ( $field->field_input_type === 'Textarea Input' ) {
                    $html .= sprintf( '<div class="awpcp-extra-field-value">%s</div>', $value );
                } else {
                    $html .= sprintf( '<span class="awpcp-extra-field-value">%s</span>', $value );   
                }
			}

			$html .= '</div>';

			$shown++;
		}
	}

	$html .= '</div>';

	return $html;
}

} // end verification for AWPCP_ModulesManager class

} // end verification for legacy versions of this module
