<?php
/**
 * Lite Manager
 *
 * @package Robolist Lite
 * @since 1.0.12
 */

$config = array(
	// Menu name under Appearance.
	'menu_name'           => apply_filters( 'robolist_lite_about_page_filter', __( 'About Robolist Lite', 'robolist-lite' ), 'menu_name' ),
	// Page title.
	'page_name'           => apply_filters( 'robolist_lite_about_page_filter', __( 'About Robolist Lite', 'robolist-lite' ), 'page_name' ),
	// Main welcome title
	/* translators: s - theme name */
	'welcome_title'       => apply_filters( 'robolist_lite_about_page_filter', sprintf( __( 'Welcome to %s! - Version ', 'robolist-lite' ), 'Robolist Lite' ), 'welcome_title' ),
	// Main welcome content
	'welcome_content'     => apply_filters( 'robolist_lite_about_page_filter', esc_html__( 'Robolist Lite is modern, feature rich and free WordPress theme for listing and directory sites. It is integrated with the well built WP Job Manager Plugin, which enables you to add a list of any professions. Such as business agencies, corporate sites, hotels, real estate and so on. The core objective of this theme is to give the information to the visitors as much as it can. Robolist Lite is loaded with the all the necessary extension which means tons of possibilities. The complete out of the box BootStrap Framework has made Robolist Lite fully responsive theme. Its clean and pixel perfect design is powerful enough to grab the attention of the visitors. Launch your directory website with this awesome WordPress listing theme, Robolist Lite.', 'robolist-lite' ), 'welcome_content' ),
	/**
	 * Tabs array.
	 *
	 * The key needs to be ONLY consisted from letters and underscores. If we want to define outside the class a function to render the tab,
	 * the will be the name of the function which will be used to render the tab content.
	 */
	'tabs'                => array(
		'getting_started'     => __( 'Getting Started', 'robolist-lite' ),
		'recommended_actions' => __( 'Required Actions', 'robolist-lite' ),
		'demo_import'         => __( 'Demo Import', 'robolist-lite' ),
		'recommended_plugins' => __( 'Useful Plugins', 'robolist-lite' ),
		'free_pro'             => __( 'Free Vs Pro', 'robolist-lite' ),
        'support'             => __( 'Support', 'robolist-lite' ),
        'changelog'           => __( 'Request Customization Support', 'robolist-lite' ),
		
	),
	// Support content tab.
	'support_content'     => array(
		'first'  => array(
			'title'        => esc_html__( 'Contact Support', 'robolist-lite' ),
			'icon'         => 'dashicons dashicons-sos',
			'text'         => esc_html__( 'We want to make sure you have the best experience using Robolist Lite, and that is why we have gathered all the necessary information here for you. We hope you will enjoy using Robolist Lite as much as we enjoy creating great products.', 'robolist-lite' ),
			'button_label' => esc_html__( 'Contact Support', 'robolist-lite' ),
			'button_link'  => esc_url( 'https://codethemes.co/support/' ),
			'is_button'    => true,
			'is_new_tab'   => true,
		),
		'second' => array(
			'title'        => esc_html__( 'Documentation', 'robolist-lite' ),
			'icon'         => 'dashicons dashicons-book-alt',
			'text'         => esc_html__( 'Need more details? Please check our full documentation for detailed information on how to use Robolist Lite.', 'robolist-lite' ),
			'button_label' => esc_html__( 'Read full documentation', 'robolist-lite' ),
			'button_link'  => esc_url('https://codethemes.co/wp-content/uploads/2018/05/Robolist-Lite-Documentation.docx'),
			'is_button'    => false,
			'is_new_tab'   => true,
		),
		'third'  => array(
			'title'        => esc_html__( 'Request Customization Support', 'robolist-lite' ),
			'icon'         => 'dashicons dashicons-portfolio',
			'text'         => esc_html__( 'Want to get the gist on the latest theme changes? Just consult our changelog below to get a taste of the recent fixes and features implemented.', 'robolist-lite' ),
			'button_label' => esc_html__( 'Request Customization Support', 'robolist-lite' ),
			'button_link'  => esc_url( 'https://codethemes.co/support/#customization_support' ),
			'is_button'    => false,
			'is_new_tab'   => false,
		),
	),
	// Getting started tab
	'getting_started'     => array(
		'first'  => array(
			'title'               => esc_html__( 'Required actions', 'robolist-lite' ),
			'text'                => esc_html__( 'We have compiled a list of steps for you to take so we can ensure that the experience you have using one of our products is very easy to follow.', 'robolist-lite' ),
			'button_label'        => esc_html__( 'Required actions', 'robolist-lite' ),
			'button_link'         => esc_url( admin_url( 'themes.php?page=robolist-lite-welcome&tab=recommended_actions' ) ),
			'is_button'           => false,
			'recommended_actions' => true,
			'is_new_tab'          => false,
		),
		'second' => array(
			'title'               => esc_html__( 'Read full documentation', 'robolist-lite' ),
			'text'                => esc_html__( 'Need more details? Please check our full documentation for detailed information on how to use Robolist Lite.', 'robolist-lite' ),
			'button_label'        => esc_html__( 'Documentation', 'robolist-lite' ),
			'button_link'         => esc_url('https://codethemes.co/wp-content/uploads/2018/05/Robolist-Lite-Documentation.docx'),
			'is_button'           => false,
			'recommended_actions' => false,
			'is_new_tab'          => true,
		),
		'third'  => array(
			'title'               => esc_html__( 'Go to the Customizer', 'robolist-lite' ),
			'text'                => esc_html__( 'Using the WordPress Customizer you can easily customize every aspect of the theme.', 'robolist-lite' ),
			'button_label'        => esc_html__( 'Go to the Customizer', 'robolist-lite' ),
			'button_link'         => esc_url( admin_url( 'customize.php' ) ),
			'is_button'           => true,
			'recommended_actions' => false,
			'is_new_tab'          => true,
		),
	),
	// Free vs PRO array.
    'free_pro'            => array(
        'free_theme_name'     => 'Robolist Lite',
        'pro_theme_name'      => 'Robolist Pro',
        'pro_theme_link'      => 'https://codethemes.co/product/robolist/',
        /* translators: s - theme name */
        'get_pro_theme_label' => sprintf( __( 'Get %s now!', 'robolist-lite' ), 'Robolist Pro' ),
        'banner_link'         => '',
        'banner_src'          => '',
        'features'            => array(
            array(
                'title'       => __( 'Mobile friendly', 'robolist-lite' ),
                'is_in_lite'  => 'true',
                'is_in_pro'   => 'true',
            ),
            array(
                'title'       => __( 'WooCommerce Compatible', 'robolist-lite' ),
                'is_in_lite'  => 'true',
                'is_in_pro'   => 'true',
            ),
            array(
                'title'       => __( 'Elementor Support', 'robolist-lite' ),
                'is_in_lite'  => 'true',
                'is_in_pro'   => 'true',
            ),
            array(
                'title'       => __( 'Get Elementor Pro Elements Worth "$49" With Theme', 'robolist-lite' ),
                'is_in_lite'  => 'false',
                'is_in_pro'   => 'true',
            ),
            array(
                'title'       => __( 'Get Elementor Custom Elements', 'robolist-lite' ),
                'is_in_lite'  => 'false',
                'is_in_pro'   => 'true',
            ),
            array(
                'title'       => __( 'Free Demo Import', 'robolist-lite' ),
                'is_in_lite'  => 'true',
                'is_in_pro'   => 'true',
            ),
            array(
                'title'       => __( 'Get WP Job Manager Addons Worth "$58" With Theme', 'robolist-lite' ),
                'is_in_lite'  => 'false',
                'is_in_pro'   => 'true',
            ),
            array(
                'title'       => __( 'Fully Customizable Colors', 'robolist-lite' ),
                'is_in_lite'  => 'false',
                'is_in_pro'   => 'true',
            ),
            array(
                'title'       => __( 'Jetpack Portfolio', 'robolist-lite' ),
                'is_in_lite'  => 'true',
                'is_in_pro'   => 'true',
            ),
            array(
                'title'       => __( 'Paid Listing', 'robolist-lite' ),
                'is_in_lite'  => 'false',
                'is_in_pro'   => 'true',
            ),
            array(
                'title'       => __( 'Post Free Listing', 'robolist-lite' ),
                'is_in_lite'  => 'true',
                'is_in_pro'   => 'true',
            ),
            array(
                'title'       => __( 'Listing Map', 'robolist-lite' ),
                'is_in_lite'  => 'false',
                'is_in_pro'   => 'true',
            ),
            array(
                'title'       => __( 'Quality Support', 'robolist-lite' ),
                'is_in_lite'  => 'false',
                'is_in_pro'   => 'true',
            ),
        ),
    ),
	// Plugins array.
	'recommended_plugins' => array(
		'already_activated_message' => esc_html__( 'Already activated', 'robolist-lite' ),
		'version_label'             => esc_html__( 'Version: ', 'robolist-lite' ),
		'install_label'             => esc_html__( 'Install and Activate', 'robolist-lite' ),
		'activate_label'            => esc_html__( 'Activate', 'robolist-lite' ),
		'deactivate_label'          => esc_html__( 'Deactivate', 'robolist-lite' ),
		'content'                   => array(
           
            array(
                'slug' => 'jetpack',
            ),
            array(
                'slug' => 'loco-translate',
            ),
		),
	),
	// Required actions array.
	'recommended_actions' => array(
		'install_label'    => esc_html__( 'Install and Activate', 'robolist-lite' ),
		'activate_label'   => esc_html__( 'Activate', 'robolist-lite' ),
		'deactivate_label' => esc_html__( 'Deactivate', 'robolist-lite' ),
		'content'          => array(

            'one-click-demo-import'           => array(
                'title'       => 'One Click Demo Import',
                'description' => robolist_lite_get_wporg_plugin_description( 'one-click-demo-import' ),
                'check'       => ( defined( 'OCDM_VERSION' ) || ! robolist_lite_check_passed_time( '259200' ) ),
                'plugin_slug' => 'one-click-demo-import',
                'id'          => 'one-click-demo-import',
                'network'     => 'live',
            ),

            'wp-job-manager'           => array(
                'title'       => 'Contact Form 7',
                'description' => robolist_lite_get_wporg_plugin_description( 'wp-job-manager' ),
                'check'       => ( defined( 'RL_WP_JOB_MANAGER' ) || ! robolist_lite_check_passed_time( '259200' ) ),
                'plugin_slug' => 'wp-job-manager',
                'id'          => 'wp-job-manager',
                'network'     => 'live',
            ),
            'contact-form-7'           => array(
                'title'       => 'Wp Job Manager',
                'description' => robolist_lite_get_wporg_plugin_description( 'contact-form-7' ),
                'check'       => ( defined( 'RL_CONTACT_FORM' ) || ! robolist_lite_check_passed_time( '259200' ) ),
                'plugin_slug' => 'contact-form-7',
                'id'          => 'contact-form-7',
                'network'     => 'live',
            ),
            'comments-ratings'           => array(
                'title'       => 'Comments Rating',
                'description' => robolist_lite_get_wporg_plugin_description( 'comments-ratings' ),
                'check'       => ( defined( 'RL_COMMENTS_RATING' ) || ! robolist_lite_check_passed_time( '259200' ) ),
                'plugin_slug' => 'comments-ratings',
                'id'          => 'comments-ratings',
                'network'     => 'live',
            ),
            'favorites'           => array(
                'title'       => 'Favorites',
                'description' => robolist_lite_get_wporg_plugin_description( 'favorites' ),
                'check'       => ( defined( 'RL_FAVORITES' ) || ! robolist_lite_check_passed_time( '259200' ) ),
                'plugin_slug' => 'favorites',
                'id'          => 'favorites',
                'network'     => 'live',
            ),

		),
	),
);
robolist_lite_About_Page::init( apply_filters( 'robolist_lite_about_page_array', $config ) );

/*
 * Notifications in customize
 */
require get_template_directory() . '/information/class-robolist-lite-customizer-notify.php';

$config_customizer = array(
	'recommended_plugins'       => array(
		'robolist-lite-companion' => array(
			'recommended' => true,
			/* translators: s - Orbit Fox Companion */
			'description' => sprintf( esc_html__( 'If you want to take full advantage of the options this theme has to offer, please install and activate %s.', 'robolist-lite' ), sprintf( '<strong>%s</strong>', 'Orbit Fox Companion' ) ),
		),
	),
	'recommended_actions'       => array(),
	'recommended_actions_title' => esc_html__( 'Recommended Actions', 'robolist-lite' ),
	'recommended_plugins_title' => esc_html__( 'Recommended Plugins', 'robolist-lite' ),
	'install_button_label'      => esc_html__( 'Install and Activate', 'robolist-lite' ),
	'activate_button_label'     => esc_html__( 'Activate', 'robolist-lite' ),
	'deactivate_button_label'   => esc_html__( 'Deactivate', 'robolist-lite' ),
);
robolist_lite_Customizer_Notify::init( apply_filters( 'robolist_lite_customizer_notify_array', $config_customizer ) );