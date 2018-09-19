<?php
/*
 Plugin Name: AWPCP Attachments
 Plugin URI: http://www.awpcp.com
 Description: Allow user to upload attachments to their Ads.
 Version: 3.6
 Author: D. Rodenbaugh
 Author URI: http://www.skylineconsult.com
 */

///////////////////////////////////////////////////////////////////////////////
// This module is not included in the core of Another Wordpress Classifieds Plugin.
// It is a separate add-on premium module and is not subject to the terms of
// the GPL license  used in the core package.
//
// This module cannot be redistributed or resold in any modified versions of
// the core Another Wordpress Classifieds Plugin product. If you have this
// module in your possession but did not purchase it via awpcp.com or otherwise
// obtain it through awpcp.com please be aware that you have obtained it
// through unauthorized means and cannot be given technical support through awpcp.com.
///////////////////////////////////////////////////////////////////////////////

define('AWPCP_ATTACHMENTS_MODULE', 'Another WordPress Classifieds Plugin - Attachments Module');
define('AWPCP_ATTACHMENTS_MODULE_BASENAME', basename(dirname(__FILE__)));
define('AWPCP_ATTACHMENTS_MODULE_DIR', rtrim( plugin_dir_path( __FILE__ ), '/' ));
define('AWPCP_ATTACHMENTS_MODULE_URL', rtrim( plugin_dir_url( __FILE__ ), '/' ));
define( 'AWPCP_ATTACHMENTS_MODULE_DB_VERSION', '3.6' );
define( 'AWPCP_ATTACHMENTS_MODULE_REQUIRED_AWPCP_VERSION', '3.6' );

function awpcp_attachments_required_awpcp_version_notice() {
    if ( current_user_can( 'activate_plugins' ) ) {
        $module_name = __( 'Attachments Module', 'awpcp-attachments' );
        $required_awpcp_version = AWPCP_ATTACHMENTS_MODULE_REQUIRED_AWPCP_VERSION;

        $message = __( 'The AWPCP <module-name> requires AWPCP version <awpcp-version> or newer!', 'awpcp-attachments' );
        $message = str_replace( '<module-name>', '<strong>' . $module_name . '</strong>', $message );
        $message = str_replace( '<awpcp-version>', $required_awpcp_version, $message );
        $message = sprintf( '<strong>%s:</strong> %s', __( 'Error', 'awpcp-attachments' ), $message );
        echo '<div class="error"><p>' . $message . '</p></div>';
    }
}

if ( ! class_exists( 'AWPCP_ModulesManager' ) ) {

    add_action( 'admin_notices', 'awpcp_attachments_required_awpcp_version_notice' );

} else {

class AWPCP_AttachmentsModule extends AWPCP_Module {

    public function __construct() {
        parent::__construct(
            __FILE__,
            'Attachments Module',
            'attachments',
            AWPCP_ATTACHMENTS_MODULE_DB_VERSION,
            AWPCP_ATTACHMENTS_MODULE_REQUIRED_AWPCP_VERSION
        );
    }

    public function required_awpcp_version_notice() {
        return awpcp_attachments_required_awpcp_version_notice();
    }

    protected function module_setup() {
        parent::module_setup();

        if ( is_admin() && awpcp_current_user_is_admin() ) {
            $handler = awpcp_attachments_placeholders_installation_verifier();
            add_action( 'admin_notices', array( $handler, 'check_placeholder_installation' ) );
        }

        $attachments_file_types = awpcp_attachments_file_types();
        add_filter( 'awpcp-file-types', array( $attachments_file_types, 'get_file_types' ) );

        $attachments_settings = awpcp_attachments_settings();
        add_action( 'awpcp_register_settings', array( $attachments_settings, 'register_settings' ) );

        add_filter( 'awpcp-upload-file-constraints', array( $this, 'upload_file_constraints' ), 10, 1 );
        add_filter( 'awpcp-ad-uploaded-files-stats', array( $this, 'ad_uploaded_files_stats' ), 10, 2 );
        add_filter( 'awpcp-can-upload-file-to-ad', array( $this, 'can_upload_file_to_ad' ), 10, 4 );

        $attachment_action_handler = awpcp_attachment_action_handler();
        add_filter( 'awpcp-set-file-as-primary', array( $attachment_action_handler, 'set_file_as_primary' ), 10, 3 );

        add_filter( 'awpcp-content-placeholders', array( $this, 'content_placeholders' ), 10, 1 );

        add_filter( 'awpcp-admin-listings-table-actions', array( $this, 'listings_table_actions' ), 10, 3 );
        add_filter( 'awpcp-media-manager-page-title', array( $this, 'media_manager_page_title' ), 10, 1 );

        $upload_limits = awpcp_listing_attachments_upload_limits();
        add_filter( 'awpcp-listing-upload-limits', array( $upload_limits, 'filter_listing_upload_limits' ), 10, 3 );

        add_filter( 'awpcp-file-handlers', array( $this, 'register_file_handlers' ) );
    }

    public function load_dependencies() {
        require_once( AWPCP_ATTACHMENTS_MODULE_DIR . '/includes/class-attachments-file-types.php' );
        require_once( AWPCP_ATTACHMENTS_MODULE_DIR . '/includes/class-attachments-placeholders-installation-verifier.php' );
        require_once( AWPCP_ATTACHMENTS_MODULE_DIR . '/includes/class-attachments-settings.php' );
        require_once( AWPCP_ATTACHMENTS_MODULE_DIR . '/includes/class-attachment-action-handler.php' );
        require_once( AWPCP_ATTACHMENTS_MODULE_DIR . '/includes/class-listing-attachments-upload-limits.php' );
        require_once( AWPCP_ATTACHMENTS_MODULE_DIR . '/includes/class-listing-other-files-file-handler.php' );
        require_once( AWPCP_ATTACHMENTS_MODULE_DIR . '/includes/class-listing-other-files-file-processor.php' );
        require_once( AWPCP_ATTACHMENTS_MODULE_DIR . '/includes/class-listing-other-files-file-validator.php' );

        require_once( AWPCP_ATTACHMENTS_MODULE_DIR . '/frontend/class-attachments-placeholder.php' );
    }

    public function upload_file_constraints( $constraints ) {
        $mime_types = array_merge( $constraints['mime_types'], $this->get_allowed_mime_types() );

        return array_merge( $constraints, array(
            'mime_types' => array_unique( $mime_types ),
            'max_attachment_size' => get_awpcp_option( 'attachments-max-file-size' ),
        ) );
    }

    public function ad_uploaded_files_stats( $stats, $ad ) {
        $files_uploaded = $this->count_ad_non_image_files( $ad );

        return array_merge( $stats, array(
            'files_allowed' => 1000,
            'files_uploaded' => $files_uploaded,
            'files_left' => 1,
        ) );
    }

    /**
     * Handler for the awpcp-can-upload-file-to-ad filter.
     *
     * Returns true if another file with the mime_type of the given file
     * can be attached to the given Ad. The purpose of this filter is to
     * control the number of files attached to a particular Ad.
     *
     * It is not the purpose of this function to reject files of invalid
     * or not allowed mime types.
     *
     * @since 1.0
     */
    public function can_upload_file_to_ad( $can, $file, $ad, $stats ) {
        if ( $can !== true ) return $can;

        $image_mime_types = awpcp_get_image_mime_types();
        $allowed_mime_types = $this->get_allowed_mime_types();

        $files_allowed = $stats['files_allowed'];
        $files_uploaded = $stats['files_uploaded'];

        if ( in_array( $file['type'], $image_mime_types ) ) {
            // let the plugin handle images
        } else if ( in_array( $file['type'], $allowed_mime_types ) ) {
            if ( $files_allowed > $files_uploaded ) {
                $can = true;
            } else {
                $can = _x( "You can't add more files to this Ad. There are not remaining attachment slots.", 'upload files', 'awpcp-attachments' );
            }
        } else {
            // not a file we allow at this time, let awpcp_upload_files decide if
            // the file can be uploaded or not.
            $can = true;
        }

        return $can;
    }

    public function listings_table_actions( $actions, $ad, $page ) {
        unset( $actions['manage-images'] );
        unset( $actions['add-image'] );

        $files = awpcp_media_api()->find_by_ad_id( $ad->ad_id );

        if ( count( $files ) > 0 ) {
            $url = $page->url( array( 'action' => 'manage-images', 'id' => $ad->ad_id ) );
            $params = array( '', $url, sprintf( ' (%d)', count( $files ) ) );
            $actions['manage-images'] = array( __( 'Images/Attachments', 'awpcp-attachments' ), $params );
        } else {
            $url = $page->url( array( 'action' => 'add-image', 'id' => $ad->ad_id ) );
            $actions['add-image'] = array( __( 'Add Image/Attachment', 'awpcp-attachments' ), $url );
        }

        return $actions;
    }

    public function media_manager_page_title( $title ) {
        return awpcp_admin_page_title( __( 'Manage Images/Attachments', 'awpcp-attachments' ) );
    }

    public function get_mime_types_by_extension() {
        return awpcp_file_types()->get_mime_types_by_extension();
    }


    public function get_allowed_mime_types_by_extension() {
        $ext_to_mime = $this->get_mime_types_by_extension();

        $mime_types = array();
        foreach ( get_awpcp_option( 'attachments-allowed-types' ) as $ext ) {
            if ( isset( $ext_to_mime[ $ext ] ) ) {
                $mime_types[ $ext ] = $ext_to_mime[ $ext ];
            }
        }

        return $mime_types;
    }

    public function get_allowed_mime_types() {
        return $this->get_non_image_mime_types();
    }

    public function get_non_image_mime_types() {
        $file_types = awpcp_file_types();

        return array_merge( $file_types->get_video_mime_types(), $file_types->get_other_files_mime_types() );
    }

    public function count_ad_non_image_files( $ad ) {
        return awpcp_media_api()->query( array(
            'fields' => 'count',
            'ad_id' => $ad->ad_id,
            'mime_type' => $this->get_non_image_mime_types(),
        ) );
    }

    public function get_ad_non_image_files( $ad, $enabled_only=false ) {
        return awpcp_media_api()->query( array(
            'ad_id' => $ad->ad_id,
            'mime_type' => $this->get_non_image_mime_types(),
            'enabled' => $enabled_only ? true : null,
        ) );
    }

    public function content_placeholders( $placeholders ) {
        $placeholder = awpcp_attachments_placeholder();
        $placeholders[ 'attachments' ] = array( 'callback' => array( $placeholder, 'do_placeholder' ) );

        return $placeholders;
    }

    public function register_file_handlers( $file_handlers ) {
        $file_handlers['attachments'] = array(
            'mime_types' => awpcp_file_types()->get_other_files_mime_types(),
            'constructor' => 'awpcp_listing_other_files_file_handler',
        );

        return $file_handlers;
    }
}

function awpcp_activate_attachments_module() {
    // pass
}
awpcp_register_activation_hook( __FILE__, 'awpcp_activate_attachments_module' );

function awpcp_load_attachments_module( $manager ) {
    $manager->load( new AWPCP_AttachmentsModule() );
}
add_action( 'awpcp-load-modules', 'awpcp_load_attachments_module' );

}
