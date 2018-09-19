<?php

/**
 * Plugin Name: AWPCP Category Icons Module
 * Plugin URI: http://www.awpcp.com
 * Description: Displays images next to categories on AWPCP
 * Version: 3.6.2
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
// Default Icons courtesy of http://www.famfamfam.com
/******************************************************************************/

if (preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) {
	die('You are not allowed to call this page directly.');
}

define('AWPCP_CATEGORY_ICONS_MODULE_DIR', rtrim( plugin_dir_path( __FILE__ ), '/' ) );
define('AWPCP_CATEGORY_ICONS_MODULE_URL', rtrim( plugin_dir_url( __FILE__ ), '/' ) );
define('AWPCP_CATEGORY_ICONS_MODULE_DB_VERSION', '3.6.2');
define( 'AWPCP_CATEGORY_ICONS_MODULE_REQUIRED_AWPCP_VERSION', '3.6' );

function awpcp_category_icons_required_awpcp_version_notice() {
    if ( current_user_can( 'activate_plugins' ) ) {
        $module_name = __( 'Category Icons Module', 'awpcp-category-icons' );
        $required_awpcp_version = AWPCP_CATEGORY_ICONS_MODULE_REQUIRED_AWPCP_VERSION;

        $message = __( 'The AWPCP <module-name> requires AWPCP version <awpcp-version> or newer!', 'awpcp-category-icons' );
        $message = str_replace( '<module-name>', '<strong>' . $module_name . '</strong>', $message );
        $message = str_replace( '<awpcp-version>', $required_awpcp_version, $message );
        $message = sprintf( '<strong>%s:</strong> %s', __( 'Error', 'awpcp-category-icons' ), $message );
        echo '<div class="error"><p>' . $message . '</p></div>';
    }
}

if ( ! class_exists( 'AWPCP_ModulesManager' )  ) {

    add_action( 'admin_notices', 'awpcp_category_icons_required_awpcp_version_notice' );

} else {

class AWPCP_CategoryIconsModule extends AWPCP_Module {

    public function __construct() {
        parent::__construct(
            __FILE__,
            'Category Icons Module',
            'category-icons',
            AWPCP_CATEGORY_ICONS_MODULE_DB_VERSION,
            AWPCP_CATEGORY_ICONS_MODULE_REQUIRED_AWPCP_VERSION
        );
    }

    public function required_awpcp_version_notice() {
        return awpcp_category_icons_required_awpcp_version_notice();
    }

    public function get_installed_version() {
    	return $this->version;
    }

    public function load_dependencies() {
        require_once( AWPCP_CATEGORY_ICONS_MODULE_DIR . '/includes/class-custom-icon-uploader-configuration.php' );
        require_once( AWPCP_CATEGORY_ICONS_MODULE_DIR . '/includes/class-custom-icon-uploader.php' );

        require_once( AWPCP_CATEGORY_ICONS_MODULE_DIR . '/includes/functions/catagories.php' );
        require_once( AWPCP_CATEGORY_ICONS_MODULE_DIR . '/includes/functions/deprecated.php' );

        require( AWPCP_CATEGORY_ICONS_MODULE_DIR . '/admin/class-category-icon-uploader-component.php' );
        require_once( AWPCP_CATEGORY_ICONS_MODULE_DIR . '/admin/class-manage-category-icons-admin-page.php' );
        require_once( AWPCP_CATEGORY_ICONS_MODULE_DIR . '/admin/class-delete-custom-category-icon-ajax-handler.php' );
        require_once( AWPCP_CATEGORY_ICONS_MODULE_DIR . '/admin/class-upload-custom-category-icon-ajax-handler.php' );
    }

    public function module_setup() {
        global $hascaticonsmodule;
        $hascaticonsmodule = true;

        add_action( 'admin_enqueue_scripts', array( $this, 'register_admin_scripts_and_styles' ) );
        add_filter( 'awpcp-category-data', array( $this, 'filter_category_data' ), 10, 2 );

        parent::module_setup();
    }

    public function register_admin_scripts_and_styles() {
        wp_register_style(
            'awpcp-category-icons-admin',
            AWPCP_CATEGORY_ICONS_MODULE_URL . '/resources/css/category-icons-admin.css',
            array( 'awpcp-admin-style' ),
            AWPCP_CATEGORY_ICONS_MODULE_DB_VERSION
        );

        wp_register_script(
            'awpcp-category-icons-admin',
            AWPCP_CATEGORY_ICONS_MODULE_URL . '/resources/js/category-icons-admin.min.js',
            array( 'awpcp', 'jquery-ui-tabs', 'plupload-all' ),
            AWPCP_CATEGORY_ICONS_MODULE_DB_VERSION,
            true
        );
    }

    public function filter_category_data( $category_data, $category ) {
        if ( isset( $category->icon ) ) {
            $category_data['category_icon'] = $category->icon;
        }

        return $category_data;
    }

    public function ajax_setup() {
        $handler = awpcp_upload_custom_category_icon_ajax_handler();
        add_action( 'wp_ajax_awpcp-upload-custom-category-icon', array( $handler, 'ajax' ) );

        $handler = awpcp_delete_custom_category_icon_ajax_handler();
        add_action( 'wp_ajax_awpcp-delete-custom-category-icon', array( $handler, 'ajax' ) );
    }

    public function admin_setup() {
        if ( isset( $_REQUEST['page'] ) && $_REQUEST['page'] == 'awpcp-admin-categories' ) {
            $page = awpcp_manage_category_icons_admin_page();
            add_action( 'admin_enqueue_scripts', array( $page, 'enqueue_scripts' ) );
            add_filter( 'awpcp-custom-manage-categories-action', array( $page, 'dispatch' ) );
        }
    }
}

function awpcp_activate_category_icons_module() {
	if ( ! awpcp_table_exists( AWPCP_TABLE_CATEGORIES ) ) {
		return;
	}

	$column_name = 'category_icon';
	$column_definition = "varchar(255) COLLATE utf8_general_ci NOT NULL DEFAULT '' AFTER `category_name`";

	if ( awpcp_column_exists( AWPCP_TABLE_CATEGORIES, $column_name ) ) {
		return;
	}

	$column_creator = awpcp_database_column_creator();
	$column_creator->create( AWPCP_TABLE_CATEGORIES, $column_name, $column_definition );
}
awpcp_register_activation_hook( __FILE__, 'awpcp_activate_category_icons_module' );

function awpcp_load_category_icons_module( $manager ) {
    $manager->load( new AWPCP_CategoryIconsModule() );
}
add_action( 'awpcp-load-modules', 'awpcp_load_category_icons_module' );

}

function is_installed_category_icon_module() {
	return awpcp_column_exists( AWPCP_TABLE_CATEGORIES, 'category_icon' );
}

function load_category_icon_management_page( $cat_ID, $offset, $results ) {
    try {
        $category = awpcp_categories_collection()->get( $cat_ID );

        $infocuscategory = $category->name;

        if ( function_exists( 'awpcp_get_category_icon' ) ) {
            $infocuscategory_icon = awpcp_get_category_icon( $category );
        }
    } catch ( AWPCP_Exception $e ) {
        $infocuscategory = null;
        $infocuscategory_icon = null;
    }

	$output = "You are managing the icon associated with the category <b>$infocuscategory</b>
	<p>Select an icon from below to set or change the icon associated with <b>$infocuscategory</b></p>";

	if ( isset( $infocuscategory_icon ) && !empty( $infocuscategory_icon ) ) {
		$infocuscategoryiconurl = AWPCP_CATEGORY_ICONS_MODULE_URL . "/images/caticons/$infocuscategory_icon";
		$output .= "<p>Current Icon in use: <img src=\"$infocuscategoryiconurl\" alt=\"$infocuscategory\" border=\"0\"></p>";
	}

	$output .= "<p>If you have icons other than the ones included in the package you need to upload them via FTP into the images/caticons folder. For best results keep your icons under 32 by 32 pixels. <b>Note</b>: There are about 1000 icons in the <a href=\"http://famfamfam.com\">famfamfam</a> Silk set included in the package. Loading will therefore be impacted. If you will be using your own icons, you might want to first delete the current icons to speed up the page loading process and reduce any load on your server.";
	$output .= "<form method=\"post\" id=\"awpcp_launch\"><p><input class=\"button button-primary\" type=\"submit\" value=\"Set Category Icon\">&nbsp;&nbsp;<input class=\"button button-secondary\" type=\"submit\" value=\"Clear Category Icon\" name=\"clear_icon\"></p><ul>";

	$iconcodeshow = array();
	$iconsdirlocation = AWPCP_CATEGORY_ICONS_MODULE_DIR . '/images/caticons';
	$icons_url_location = AWPCP_CATEGORY_ICONS_MODULE_URL . '/images/caticons';

	if (is_dir($iconsdirlocation)) {
		$awpcpgeticonfiles=opendir($iconsdirlocation);

		if ($awpcpgeticonfiles) {
			while($theiconfile = readdir($awpcpgeticonfiles)) {
				if(is_valid_icon_image($theiconfile)) {
					$iconcodeshow[]="<li style=\"width:50px;text-align:left;float:left;list-style:none;\"><input type=\"radio\" name=\"category_icon\" value=\"$theiconfile\"><img src=\"$icons_url_location/$theiconfile\" border=\"0\" alt=\"$theiconfile\"></li>";
				}
			}
		}
		closedir($awpcpgeticonfiles);
	}

    $output .= implode( '', $iconcodeshow );

	$output .= "</ul><div style=\"clear:both\"></div><br/><input type=\"hidden\" name=\"offset\" value=\"$offset\"><input type=\"hidden\" name=\"results\" value=\"$results\"><input type=\"hidden\" name=\"cat_ID\" value=\"$cat_ID\"><input type=\"hidden\" name=\"action\" value=\"setcategoryicon\"><br/><p><input class=\"button button-primary\" type=\"submit\"  value=\"Set Category Icon\">&nbsp;&nbsp;<input class=\"button button-secondary\" type=\"submit\" value=\"Clear Category Icon\" name=\"clear_icon\"></p></form>";

	echo $output;
}

function is_valid_icon_image( $theiconfile ) {
	if( $theiconfile == '.' || $theiconfile == '..'  ) {
		$isvalidiconimage=0;
	} else {
		$isvalidiconimage=1;
	}
	return $isvalidiconimage;
}

/**
 * Set Category Icon Step 2
 */
function set_category_icon( $thecategory_id, $theiconfile, $offset, $results ) {
	global $wpdb;

	$message = '';

	if ( ! empty( $theiconfile ) ) {
        $query = "UPDATE " . AWPCP_TABLE_CATEGORIES . " SET category_icon=%s WHERE category_id=%d";
        $result = $wpdb->query( $wpdb->prepare( $query, $theiconfile, $thecategory_id ) );

        if ( $result !== false ) {
            $thecategoryname = get_adcatname( $thecategory_id );
            $message = __( 'The icon for the category <category-name> has been set/reset.', 'awpcp-category-icons' );
            $message = str_replace( '<category-name>', '<strong>' . $thecategoryname . '</strong>', $message );
            $message = awpcp_print_message( $message );
        }
	} elseif ( $theiconfile === null ) {
        $query = 'UPDATE ' . AWPCP_TABLE_CATEGORIES . ' SET category_icon = %s WHERE category_id = %d';
        $result = $wpdb->query( $wpdb->prepare( $query, '', $thecategory_id ) );

        if ( $result !== false ) {
            $thecategoryname = get_adcatname( $thecategory_id );
            $message = __( 'The icon for the category <category-name> has been cleared.', 'awpcp-category-icons' );
            $message = str_replace( '<category-name>', '<strong>' . $thecategoryname . '</strong>', $message );
            $message = awpcp_print_message( $message );
        }
	} else {
		$message = __( 'No icon was selected', 'awpcp-category-icons' );
        $message = awpcp_print_error( $message );
	}

	return $message;
}
