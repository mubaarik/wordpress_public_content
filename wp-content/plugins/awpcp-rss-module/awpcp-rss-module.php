<?php

/*
 Plugin Name: AWPCP RSS Feeds Module
 Plugin URI: http://www.awpcp.com
 Description: This module allows you to offer RSS syndication of your classifieds ads.
 Version: 3.6.2
 Author: D. Rodenbaugh
 Author URI: http://www.skylineconsult.com
 */

/******************************************************************************/
// Sold via: http://www.awpcp.com
// A premium module for use with Another Wordpress Classifieds Plugin
// This module allows you to offer RSS syndication of your classifieds ads
// Original Implementation: Adela Lewis
//
// -----------------------------------------------------------------------------
//
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

if (!defined('ABSPATH')) {
    die( 'You are not allowed to call this file directly.');
}

if (function_exists('awpcp_rss_setup')) {

    $msg = __('An old version of RSS Module is already loaded. Please remove awpcp_rss_module.php from your AWPCP plugin directory.', 'awpcp-rss-module' );
    add_action('admin_notices', create_function('', 'echo \'<div class="error"><p>' . $msg . '</p></div>\';'));
    define('AWPCP_RSS_MODULE_CONFLICT', true);

} else {

define('AWPCP_RSS_MODULE', 'Another WordPress Classifieds Plugin - RSS Module');

define('AWPCP_RSS_MODULE_BASENAME', basename(dirname(__FILE__)));
define('AWPCP_RSS_MODULE_DIR', rtrim( plugin_dir_path( __FILE__ ), '/' ));
define('AWPCP_RSS_MODULE_URL', rtrim( plugin_dir_url( __FILE__ ), '/' ));
define('AWPCP_RSS_MODULE_DB_VERSION', '3.6.2');
define('AWPCP_RSS_MODULE_REQUIRED_AWPCP_VERSION', '3.7.6dev1');

function awpcp_rss_required_awpcp_version_notice() {
    if ( current_user_can( 'activate_plugins' ) ) {
        $module_name = __( 'RSS Feeds Module', 'awpcp-rss-module' );
        $required_awpcp_version = AWPCP_RSS_MODULE_REQUIRED_AWPCP_VERSION;

        $message = __( 'The AWPCP <module-name> requires AWPCP version <awpcp-version> or newer!', 'awpcp-rss-module' );
        $message = str_replace( '<module-name>', '<strong>' . $module_name . '</strong>', $message );
        $message = str_replace( '<awpcp-version>', $required_awpcp_version, $message );
        $message = sprintf( '<strong>%s:</strong> %s', __( 'Error', 'awpcp-rss-module' ), $message );
        echo '<div class="error"><p>' . $message . '</p></div>';
    }
}

if ( ! class_exists( 'AWPCP_ModulesManager' )  ) {

    add_action( 'admin_notices', 'awpcp_rss_required_awpcp_version_notice' );

} else {

class AWPCP_RSSModule extends AWPCP_Module {

    public function __construct() {
        parent::__construct(
            __FILE__,
            'RSS Feeds Module',
            'rss',
            AWPCP_RSS_MODULE_DB_VERSION,
            AWPCP_RSS_MODULE_REQUIRED_AWPCP_VERSION,
            'awpcp-rss-module'
        );
    }

    public function required_awpcp_version_notice() {
        return awpcp_rss_required_awpcp_version_notice();
    }

    protected function module_setup() {
        parent::module_setup();

        global $hasrssmodule;

        // tell AWPCP the module is available
        $hasrssmodule = true;

        add_action('awpcp_register_settings', array($this, 'register_settings'));
        add_action('template_redirect', array($this, 'template_redirect'));
        add_filter( 'awpcp_menu_items', array( $this, 'menu_items' ) );
    }

    public function register_settings() {
        $api = awpcp()->settings;

        $key = $api->add_section('general-settings', __('RSS Settings', 'awpcp-rss-module' ), 'rss-settings', 10, array($api, 'section'));
        $api->add_setting($key, 'numfeedstoshow', __('Number of items to show in RSS feed', 'awpcp-rss-module' ), 'textfield', '20', '');

        $options = array(
            'no' => __( 'Do not show images', 'awpcp-rss-module' ),
            'thumbnail' => __( 'Show images (thumbnail size)', 'awpcp-rss-module' ),
            'primary' => __( 'Show images (primary image size)', 'awpcp-rss-module' ),
            'large' => __( 'Show images (large size)', 'awpcp-rss-module' ),
            'original' => __( 'Show images (original size)', 'awpcp-rss-module' ),
        );

        $api->add_setting( $key, 'show-images-in-feeds', __("Show images in RSS feeds?", 'awpcp-rss-module' ), 'select', 'thumbnails', '', array( 'options' => $options ) );
        $api->add_setting($key, 'include-children-category', __('Include Ads in children categories.', 'awpcp-rss-module' ), 'checkbox', 0, __('If checked, the RSS feed for specific categories will include Ads in children categories.', 'awpcp-rss-module' ));
        $api->add_setting( $key, 'show-rss-icon', __( 'Show RSS icon?', 'awpcp-rss-module' ), 'checkbox', 1, __( 'If checked, an RSS icon will be shown as part of the menu items at the top of the plugin pages.', 'awpcp-rss-module' ) );
    }

    private function get_category_name($category_id) {
        static $categories = null;

        if ( is_null( $categories ) ) {
            $all_categories = awpcp_categories_collection()->get_all();
            $categories = awpcp_organize_categories_by_id( $all_categories );
        }

        $category_hierarchy = awpcp_get_category_hierarchy( $category_id, $categories );

        return implode( ' - ', awpcp_get_properties( array_reverse( $category_hierarchy ), 'name' ) );
    }

    public function template_redirect() {
        global $awpcpthumbsurl;

        $action = awpcp_request_param('a', get_query_var('awpcp-action'));
        $category_id = absint(awpcp_request_param('category_id', get_query_var('cid')));

        if ($action != 'rss') return;

        $conditions[] = "ad_title != ''";
        $conditions[] = "ad_details != ''";

        if ($category_id > 0 && get_awpcp_option('include-children-category')) {
            $conditions[] = sprintf('(ad_category_id = %1$d OR ad_category_parent_id = %1$d)', $category_id);
        } else if ($category_id > 0) {
            $conditions[] = sprintf('ad_category_id = %1$d', $category_id);
        }

        $ads = AWPCP_Ad::get_enabled_ads( array(
            'order' => array( 'ad_id DESC' ),
            'limit' => get_awpcp_option('numfeedstoshow', 20),
        ), $conditions );

        // possible values are 'no', or an image size to show
        $image_size = get_awpcp_option( 'show-images-in-feeds' );

        $items = array();

        foreach ($ads as $ad) {
            $title = $ad->get_title();
            $excerpt = wp_trim_words($ad->ad_details, 1000);

            if ( $image_size != 'no' ) {
                $image_object = awpcp_media_api()->get_ad_primary_image( $ad );
            } else {
                $image_object = null;
            }

            if ( ! is_null( $image_object ) ) {
                $image_src = $image_object->get_url( $image_size );
                $image = '<img src="%s" style="float:left; margin-right:20px;" border="0" width="auto" alt="%s" />';
                $image = sprintf( $image, $image_src, esc_attr( $title ) );
            } else {
                $image = '';
            }

            $items[] = array(
                'link' => esc_url(url_showad($ad->ad_id)),
                'title' => esc_html($title),
                'category_name' => esc_html($this->get_category_name($ad->ad_category_id)),
                'excerpt' => esc_html($excerpt),
                'extra-fields' => awpcp_do_placeholders( $ad, '$extra_fields', 'listings' ),
                'image' => $image,
                'start_date' => $ad->ad_startdate,
            );
        }

        if ($category_id > 0) {
            $title = sprintf('%s: %s', get_bloginfo_rss('name'), get_adcatname($category_id));
        } else {
            $title = get_bloginfo_rss('name');
        }

        //IMPORTANT: Send feed as XML. not HTML. Must do this first.
        header('Content-Type: application/xml');
        include(AWPCP_RSS_MODULE_DIR . '/templates/rss.tpl.php');
        exit();
    }

    public function menu_items( $items ) {
        global $awpcp_imagesurl;

        if ( ! get_awpcp_option( 'show-rss-icon' ) ) {
            return $items;
        }

        $category_id = absint(awpcp_request_param('category_id', get_query_var('cid')));
        $main_page_id = awpcp_get_page_id_by_ref( 'main-page-name' );
        $base_url = get_page_link( $main_page_id, true );

        if ( get_option('permalink_structure') ) {
            $page_uri = get_page_uri( $main_page_id );

            if ( $category_id ) {
                $pagename = sprintf( '%s/classifiedsrss/%d', $page_uri, $category_id );
            } else {
                $pagename = sprintf( '%s/classifiedsrss', $page_uri );
            }

            $url = str_replace( '%pagename%', $pagename, $base_url );
        } else {
            if ( $category_id ) {
                $params = array( 'a' => 'rss', 'category_id' => $category_id );
            } else {
                $params = array( 'a' => 'rss', 'category_id' => $category_id );
            }

            $url = add_query_arg( $params, $base_url );
        }

        if ($category_id > 0) {
            $title = __('RSS feed for classifieds in category "%s"', 'awpcp-rss-module' );
            $title = sprintf($title, get_adcatname($category_id));
        } else {
            $title = __("RSS feed for classifieds", 'awpcp-rss-module' );
        }

        $image_url = AWPCP_RSS_MODULE_URL . '/images/rssicon.png';
        $image = sprintf( '<img style="border:none; height: 1em; padding: 0; background: transparent;" alt="%s" src="%s"/>', esc_attr( $title ), $image_url );

        if ( $items ) {
            $items = awpcp_array_insert_first( $items, 'rss', array( 'url' => $url, 'title' => $image ) );
        } else {
            $items = array( 'rss' => array( 'url' => $url, 'title' => $image ) );
        }

        return $items;
    }
}

function awpcp_rss_module() {
    return new AWPCP_RSSModule();
}

function awpcp_activate_rss_module() {
    awpcp_rss_module()->install_or_upgrade();
}
awpcp_register_activation_hook( __FILE__, 'awpcp_activate_rss_module' );

function awpcp_load_rss_module( $manager ) {
    $manager->load( awpcp_rss_module() );
}
add_action( 'awpcp-load-modules', 'awpcp_load_rss_module' );

}

}
