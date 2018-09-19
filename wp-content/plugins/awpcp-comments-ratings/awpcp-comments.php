<?php

/**
 * Plugin Name: AWPCP Comments & Ratings Module
 * Plugin URI: http://www.awpcp.com
 * Description: Comments and Ratings module for AWPCP Ads
 * Version: 3.6.6
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

define('AWPCP_COMMENTS_MODULE', 'Another WordPress Calssifieds Plugin - Comments Module');

define('AWPCP_COMMENTS_MODULE_BASENAME', basename(dirname(__FILE__)));
define('AWPCP_COMMENTS_MODULE_DIR', rtrim( plugin_dir_path( __FILE__ ), '/' ));
define('AWPCP_COMMENTS_MODULE_URL', rtrim( plugin_dir_url( __FILE__ ), '/' ));
define('AWPCP_COMMENTS_MODULE_VERSION', '3.6.6');
define( 'AWPCP_COMMENTS_MODULE_REQUIRED_AWPCP_VERSION', '3.6' );

define('AWPCP_TABLE_USER_RATINGS', $wpdb->prefix . 'awpcp_user_ratings');
define('AWPCP_TABLE_COMMENTS', $wpdb->prefix . 'awpcp_comments');

define('AWPCP_COMMENTS_MODULE_IMAGES_DIR', AWPCP_COMMENTS_MODULE_DIR . '/resources/images');
define('AWPCP_COMMENTS_MODULE_IMAGES_URL', AWPCP_COMMENTS_MODULE_URL . '/resources/images');


require_once(AWPCP_COMMENTS_MODULE_DIR . '/install.php');
require_once(AWPCP_COMMENTS_MODULE_DIR . '/includes/controllers/comments.php');
require_once(AWPCP_COMMENTS_MODULE_DIR . '/includes/controllers/ratings.php');
require_once(AWPCP_COMMENTS_MODULE_DIR . '/includes/models/comment.php');
require_once(AWPCP_COMMENTS_MODULE_DIR . '/includes/views/admin/class-missing-comments-placeholders-notice.php');
require_once(AWPCP_COMMENTS_MODULE_DIR . '/includes/class-comments-moderator.php');
require_once(AWPCP_COMMENTS_MODULE_DIR . '/includes/class-comments-creator.php');
require_once( AWPCP_COMMENTS_MODULE_DIR . '/includes/class-comments-notifications.php');
require_once(AWPCP_COMMENTS_MODULE_DIR . '/includes/class-comments-placeholder.php');
require_once(AWPCP_COMMENTS_MODULE_DIR . '/includes/class-comments-renderer.php');
require_once( AWPCP_COMMENTS_MODULE_DIR . '/includes/class-comment-notification.php');
require_once( AWPCP_COMMENTS_MODULE_DIR . '/includes/class-comment-posted-notification.php');
require_once( AWPCP_COMMENTS_MODULE_DIR . '/includes/class-comment-edited-notification.php');
require_once(AWPCP_COMMENTS_MODULE_DIR . '/includes/class-ratings-placeholder.php');
require_once(AWPCP_COMMENTS_MODULE_DIR . '/admin/admin.php');
require_once(AWPCP_COMMENTS_MODULE_DIR . '/admin/comments-table.php');

function awpcp_comments_ratings_required_awpcp_version_notice() {
    if ( current_user_can( 'activate_plugins' ) ) {
        $module_name = __( 'Comments & Ratings Module', 'awpcp-comments-ratings' );
        $required_awpcp_version = AWPCP_COMMENTS_MODULE_REQUIRED_AWPCP_VERSION;

        $message = __( 'The AWPCP <module-name> requires AWPCP version <awpcp-version> or newer!', 'awpcp-comments-ratings' );
        $message = str_replace( '<module-name>', '<strong>' . $module_name . '</strong>', $message );
        $message = str_replace( '<awpcp-version>', $required_awpcp_version, $message );
        $message = sprintf( '<strong>%s:</strong> %s', __( 'Error', 'awpcp-comments-ratings' ), $message );
        echo '<div class="error"><p>' . $message . '</p></div>';
    }
}

if ( ! class_exists( 'AWPCP_ModulesManager' ) ) {

    add_action( 'admin_notices', 'awpcp_comments_ratings_required_awpcp_version_notice' );

} else {

class AWPCP_CommentsRatingsModule extends AWPCP_Module {

    private $installer;

    public function __construct( $installer ) {
        parent::__construct(
            __FILE__,
            'Comments & Ratings Module',
            'comments',
            AWPCP_COMMENTS_MODULE_VERSION,
            AWPCP_COMMENTS_MODULE_REQUIRED_AWPCP_VERSION,
            'awpcp-comments-ratings'
        );

		$this->installer = $installer;
    }

    public function required_awpcp_version_notice() {
        return awpcp_comments_ratings_required_awpcp_version_notice();
    }

    public function get_installed_version() {
        return get_option( 'awpcp-comments-ratings-db-version', get_option( 'awpcp-comments-db-version' ) );
    }

    public function install_or_upgrade() {
        $this->installer->install_or_upgrade( $this );
    }

    public function load_dependencies() {
        require_once( AWPCP_COMMENTS_MODULE_DIR . '/includes/views/frontend/class-comments-ajax-handler.php');
        require_once( AWPCP_COMMENTS_MODULE_DIR . '/includes/views/frontend/class-ratings-ajax-handler.php');

        require_once( AWPCP_COMMENTS_MODULE_DIR . '/frontend/class-save-comment-ajax-handler.php');
    }

    protected function module_setup() {
        parent::module_setup();

        $this->admin = new AWPCP_Comments_Module_Admin();

        add_action( 'awpcp_register_settings', array( $this, 'register_settings' ) );
        add_action( 'awpcp-content-placeholders', array( $this, 'register_placeholders' ) );

        $this->register_scripts();
    }

    protected function ajax_setup() {
        $handler = awpcp_comments_ajax_handler();

        foreach ( array( 'flag', 'edit', 'delete' ) as $action ) {
            add_action( 'wp_ajax_awpcp-comments-' . $action . '-comment', array( $handler, 'ajax' ) );
            add_action( 'wp_ajax_nopriv_awpcp-comments-' . $action . '-comment', array( $handler, 'ajax' ) );
        }

        $handler = awpcp_save_comment_ajax_handler();
        add_action( 'wp_ajax_awpcp-comments-save-comment', array( $handler, 'ajax' ) );
        add_action( 'wp_ajax_nopriv_awpcp-comments-save-comment', array( $handler, 'ajax' ) );

        $handler = awpcp_ratings_ajax_handler();

        foreach ( array( 'rate', 'delete' ) as $action ) {
            add_action( 'wp_ajax_awpcp-ratings-' . $action, array( $handler, 'ajax' ) );
            add_action( 'wp_ajax_nopriv_awpcp-ratings-' . $action, array( $handler, 'ajax' ) );
        }
    }

    protected function admin_setup() {
        $notice = awpcp_missing_comments_placeholders_notice();
        add_action( 'admin_notices', array( $notice, 'maybe_render' ) );
    }

    public function register_scripts() {
        $version = AWPCP_COMMENTS_MODULE_VERSION;

        $js = AWPCP_COMMENTS_MODULE_URL . '/resources/js';
        $css = AWPCP_COMMENTS_MODULE_URL . '/resources/css';

        // frontend CSS
        wp_register_style( 'awpcp-comments-ratings', "$css/awpcp-comments-ratings.css", array( 'awpcp-frontend-style' ), $version );

        // frontend JS
        wp_register_script( 'awpcp-comments-ratings', "$js/comments-ratings.min.js", array( 'jquery-form', 'awpcp' ), $version, true );

        // admin JS
        wp_register_script( 'admin-awpcp-comments', "$js/admin-awpcp-comments.js", array( 'awpcp-table-ajax-admin', 'awpcp' ), $version, true );
    }

    public function register_settings() {
        global $awpcp;

        $settings = $awpcp->settings;

        /* Comments Settings */

        $group = $settings->add_group( __( 'Comments & Ratings', 'awpcp-comments-ratings' ), 'comments-settings', 31 );

        // Default Section
        $key = $settings->add_section($group, __('Comments', 'awpcp-comments-ratings' ),
                                      'comments-settings', 10, array($settings, 'section'));

        $settings->add_setting($key, 'enable-user-comments',
                    __('Display Comments in Ads?', 'awpcp-comments-ratings' ), 'checkbox', 1,
                    __('Check to enable User Comments.', 'awpcp-comments-ratings' ));
        $settings->add_setting($key, 'only-admin-can-place-comments',
                    __('Limit comments to Admin users', 'awpcp-comments-ratings' ), 'checkbox', 1,
                    __('Only Admin users can place Ad comments.', 'awpcp-comments-ratings' ));
        $settings->add_setting($key, 'comments-require-user-registration',
                    __('Limit comments to registered users', 'awpcp-comments-ratings' ), 'checkbox', 0,
                    __('Only registered users can place Ad comments.', 'awpcp-comments-ratings' ));
        $settings->add_setting($key, 'comments-can-be-edited',
                    __('Allow comments to be edited by poster user?', 'awpcp-comments-ratings' ), 'checkbox', 0,
                    __('Comment poster will be allowed to edit their comments.', 'awpcp-comments-ratings' ));

        $settings->add_setting(
            $key,
            'notify-listing-owner-about-comments-actions',
            __( 'Notify listing owner when a comment is edited or posted', 'awpcp-comments-ratings' ),
            'checkbox',
            1,
            ''
        );

        $settings->add_setting(
            $key,
            'notify-administrator-about-comments-actions',
            __( 'Notify website administrator when a comment is edited or posted', 'awpcp-comments-ratings' ),
            'checkbox',
            1,
            ''
        );

        // Layout
        $key = $settings->add_section($group, __('Comments Layout', 'awpcp-comments-ratings' ), 'comments-layout', 20, array($settings, 'section'));

        $options = array('oldest-first' => __('Oldest First', 'awpcp-comments-ratings' ), 'newest-first' => __('Newest First', 'awpcp-comments-ratings' ));

        $settings->add_setting(
            $key,
            'comments-order',
            __( 'Comments Order', 'awpcp-comments-ratings' ),
            'radio',
            'newest-first',
            '',
            array( 'options' => $options )
        );

        $settings->add_setting($key, 'show-comments-title', __('Show comments title', 'awpcp-comments-ratings' ), 'checkbox', 1, '');

        /* Ratings Settings */

        $key = $settings->add_section($group, __('User Ratings', 'awpcp-comments-ratings' ), 'user-ratings', 40, array($settings, 'section'));

        $settings->add_setting($key, 'enable-user-ratings',
                   __('Enable User Ratings', 'awpcp-comments-ratings' ), 'checkbox', 1,
                   __('Check to enable User Ratings.', 'awpcp-comments-ratings' ));

        $settings->add_setting( $key, 'ratings-require-user-registration', __( 'User must be registerd to rate an Ad', 'awpcp-comments-ratings' ), 'checkbox', 0, __( 'If checked, only registered users will be able to rate Ads.', 'awpcp-comments-ratings' ) );
        $settings->add_setting( $key, 'ratings-show-count', __( 'Show ratings count', 'awpcp-comments-ratings' ), 'checkbox', 1, __( "If checked, the number of ratings used to calculate the Ad's score will be shown between parentheses.", 'awpcp-comments-ratings' ) );

        $text = __('Use your own. Create custom-on.png, custom-off.png and custom-half.png 16x16 icons in %s.', 'awpcp-comments-ratings' );
        $text = sprintf($text, str_replace(WP_CONTENT_DIR, '', AWPCP_COMMENTS_MODULE_IMAGES_DIR));

        $img = sprintf('<img src="%s/%%s.png" />', AWPCP_COMMENTS_MODULE_IMAGES_URL);

        $options = array(
            'raty-star' => sprintf($img, 'raty-star'),
            'ledicons-heart' => sprintf($img, 'ledicons-heart'),
            'splashyfish-heart' => sprintf($img, 'splashyfish-heart'),
            'custom' => $text
        );

        $settings->add_setting( $key, 'ratings-iconset', __( 'Rating Icons', 'awpcp-comments-ratings' ), 'radio', 'raty-star', __( 'Choose the icons you want to use to show Ad Ratings.', 'awpcp-comments-ratings' ), array('options' => $options ) );


        /* Comments Email Settings */

        $group = 'email-settings';

        $key = $settings->add_section( $group, __( 'Comment Posted Message', 'awpcp-comments-ratings' ), 'comment-posted-message', 10, array( $settings, 'section' ) );

        $settings->add_setting(
            $key,
            'comment-posted-message-subject',
            __('Subject for Comment Posted email', 'awpcp-comments-ratings' ),
            'textfield',
            __( '$comment_author posted a new comment to listing $listing_title', 'awpcp-comments-ratings' ),
            __('Subject line for email sent out when a comment is posted to a listing. $comment_author and $listing_title will be replaced with the name of the author of the comment and the title of the listing, respectivley.', 'awpcp-comments-ratings' )
        );

        $helptext = __( 'You can use the following placeholders to personalize the body of the message: $listing_owner, $listing_title, $comment_author, $comment_title, $comment_content, $comment_url, $comment_creation_date, $commment_modification_date.', 'awpcp-comments-ratings' );

        $settings->add_setting(
            $key,
            'comment-posted-message-body-for-listing-owner',
            __( 'Body for Comment Posted email sent to listing owner', 'awpcp-comments-ratings' ),
            'textarea',
            _x( "Hello \$listing_owner,\n\nA new comment was posted to your listing \$listing_title. The details are shown below:\n\nAuthor: \$comment_author.\nTitle: \$comment_title.\nCreation Date: \$comment_creation_date.\nModification Date: \$comment_modification_date.\nContent: \$comment_content\n\nFollow this link to see the comment directly on the listing's page: \$comment_url.",  'body for message sent to listing owner when a new comment is posted','awpcp-comments-ratings' ),
            $helptext
        );

        $settings->add_setting(
            $key,
            'comment-posted-message-body-for-administrator',
            __( 'Body for Comment Posted email sent to administrator', 'awpcp-comments-ratings' ),
            'textarea',
            _x( "Hello Administrator,\n\nA new comment was posted to the listing \$listing_title. The details are shown below:\n\nAuthor: \$comment_author.\nTitle: \$comment_title.\nCreation Date: \$comment_creation_date.\nModification Date: \$comment_modification_date.\nContent: \$comment_content\n\nFollow this link to see the comment directly on the listing's page: \$comment_url.",  'body for message sent to listing owner when a new comment is posted', 'awpcp-comments-ratings' ),
            $helptext
        );

        $key = $settings->add_section( $group, __( 'Comment Edited Message', 'awpcp-comments-ratings' ), 'comment-edited-message', 10, array( $settings, 'section' ) );

        $settings->add_setting(
            $key,
            'comment-edited-message-subject',
            __('Subject for Comment Edited email', 'awpcp-comments-ratings' ),
            'textfield',
            __( '$comment_author edited a comment in listing $listing_title', 'awpcp-comments-ratings' ),
            __('Subject line for email sent out when a comment is edited in a listing. $comment_author and $listing_title will be replaced with the name of the author of the comment and the title of the listing, respectivley.', 'awpcp-comments-ratings' )
        );

        $helptext = __( 'You can use the following placeholders to personalize the body of the message: $listing_owner, $listing_title, $comment_author, $comment_title, $comment_content, $comment_url, $comment_creation_date, $commment_modification_date.', 'awpcp-comments-ratings' );

        $settings->add_setting(
            $key,
            'comment-edited-message-body-for-listing-owner',
            __( 'Body for Comment Edited email sent to listing owner', 'awpcp-comments-ratings' ),
            'textarea',
            _x( "Hello \$listing_owner,\n\n\$comment_author just edited a comment in your listing \$listing_title. The details are shown below:\n\nAuthor: \$comment_author.\nTitle: \$comment_title.\nCreation Date: \$comment_creation_date.\nModification Date: \$comment_modification_date.\nContent: \$comment_content\n\nFollow this link to see the comment directly on the listing's page: \$comment_url.",  'body for message sent to listing owner when a comment is edited', 'awpcp-comments-ratings' ),
            $helptext
        );

        $settings->add_setting(
            $key,
            'comment-edited-message-body-for-administrator',
            __( 'Body for Comment Edited email sent to administrator', 'awpcp-comments-ratings' ),
            'textarea',
            _x( "Hello Administrator,\n\n\$comment_author just edited a comment in listing \$listing_title. The details are shown below:\n\nAuthor: \$comment_author.\nTitle: \$comment_title.\nCreation Date: \$comment_creation_date.\nModification Date: \$comment_modification_date.\nContent: \$comment_content\n\nFollow this link to see the comment directly on the listing's page: \$comment_url.",  'body for message sent to listing owner when a comment is edited', 'awpcp-comments-ratings' ),
            $helptext
        );
    }

    /**
     * @since 3.2.3
     */
    public function register_placeholders( $placeholders ) {
        if ( get_awpcp_option( 'enable-user-comments' ) && ! isset( $placeholders['comments'] ) ) {
            $placeholders['comments'] = array( 'callback' => array( awpcp_comments_placeholder(), 'placeholder' ) );
        }

        if ( ! isset( $placeholders['rating'] ) ) {
            $placeholder = awpcp_ratings_placeholder();
            $placeholders['rating'] = array( 'callback' => array( $placeholder, 'placeholder' ) );
            $placeholders['ratings'] = array( 'callback' => array( $placeholder, 'placeholder' ) );
            $placeholders['ratings_readonly'] = array( 'callback' => array( $placeholder, 'placeholder' ) );
        }

        return $placeholders;
    }
}

function awpcp_comments_ratings_module() {
    return new AWPCP_CommentsRatingsModule( awpcp_comments_ratings_module_installer() );
}

function awpcp_activate_comments_and_ratings_module() {
    awpcp_comments_ratings_module()->install_or_upgrade();
}
awpcp_register_activation_hook( __FILE__, 'awpcp_activate_comments_and_ratings_module' );

function awpcp_load_comments_and_ratings_module( $manager ) {
    $manager->load( awpcp_comments_ratings_module() );
}
add_action( 'awpcp-load-modules', 'awpcp_load_comments_and_ratings_module' );

}
