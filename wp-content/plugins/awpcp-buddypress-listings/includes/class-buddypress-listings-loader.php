<?php

function awpcp_buddypress_listings_loader() {
    return new AWPCP_BuddyPressListingsLoader( awpcp_buddypress_wrapper() );
}

class AWPCP_BuddyPressListingsLoader {

    public static $bp_version_required = '2.0.1';

    private $buddypress;

    private $plugin_file;
    private $network_configuration;

    public function __construct( $buddypress ) {
        $this->buddypress = $buddypress;
    }

    public function load( $plugin_file ) {
        $this->plugin_file = $plugin_file;
        $this->network_configuration = $this->get_network_configuration();

        $this->setup_hooks();
    }

    /**
     * Copied from network_check() function in
     * https://github.com/buddypress/bp-attachments/blob/667cd6039cf6d9244c7a704bece4ece61b83744c/loader.php
     */
    private function get_network_configuration() {
        $plugin_basename = plugin_basename( $this->plugin_file );

        /*
         * network_active : BP Attachments is activated on the network
         * network_status : BuddyPress & BP Attachments share the same network status
         */
        $config = array( 'network_active' => false, 'network_status' => true );
        $network_plugins = get_site_option( 'active_sitewide_plugins', array() );

        // No Network plugins
        if ( empty( $network_plugins ) ) {
            return $config;
        }

        $check = array( buddypress()->basename, $plugin_basename );
        $network_active = array_diff( $check, array_keys( $network_plugins ) );

        if ( count( $network_active ) == 1 ) {
            $config['network_status'] = false;
        }

        $config['network_active'] = isset( $network_plugins[ $plugin_basename ] );

        return $config;
    }

    private function setup_hooks() {
        if ( $this->current_blog_matches_buddypress_blog() && $this->network_configuration['network_status'] ) {
            add_filter( 'bp_optional_components', array( $this, 'add_as_optional_component' ), 10, 1 );
            add_filter( 'bp_active_components', array( $this, 'add_as_active_component' ), 10, 1 );
            add_filter( 'bp_core_admin_get_components', array( $this, 'add_component_description' ), 10, 2 );
            add_action( 'bp_core_components_included', array( $this, 'include_component' ), 10 );
        } else {
            $hook_name = $this->network_configuration['network_active'] ? 'network_admin_notices' : 'admin_notices';
            add_action( $hook_name, array( $this, 'admin_notices' ) );
        }
    }

    private function current_blog_matches_buddypress_blog() {
        if ( ! function_exists( 'bp_get_root_blog_id' ) ) {
            return false;
        }

        if ( get_current_blog_id() != bp_get_root_blog_id() ) {
            return false;
        }

        return true;
    }

    public function add_as_optional_component( $optional_components = array() ) {
        $optional_components[] = AWPCP_BUDDYPRESS_LISTINGS_MODULE_COMPONENT_ID;
        return $optional_components;
    }

    public function add_as_active_component( $active_components = array() ) {
        $active_components[ AWPCP_BUDDYPRESS_LISTINGS_MODULE_COMPONENT_ID ] = 1;
        return $active_components;
    }

    public function add_component_description( $components, $type ) {
        if ( 'optional' != $type ) {
            return $components;
        }

        return array_merge( $components, array(
            AWPCP_BUDDYPRESS_LISTINGS_MODULE_COMPONENT_ID => array(
                'title' => __( 'Listings', 'awpcp-buddypress-listings' ),
                'description' => __( 'Integrate AWPCP capabilities into BuddyPress.', 'awpcp-buddypress-listings' ),
            )
        ) );
    }

    public function include_component() {
        if ( bp_is_active( AWPCP_BUDDYPRESS_LISTINGS_MODULE_COMPONENT_ID ) ) {
            add_action( 'bp_setup_components', array( $this, 'setup_component' ) );
            bp_register_template_stack( array( $this, 'template_stack' ), 14 );
            bp_register_template_stack( array( $this, 'template_parts' ), 14 );
        }
    }

    public function setup_component() {
        require_once( AWPCP_BUDDYPRESS_LISTINGS_MODULE_DIR . '/bp-listings/bp-listings-loader.php' );

        $buddypress_listings = awpcp_buddypress_listings_component();
        buddypress()->{AWPCP_BUDDYPRESS_LISTINGS_MODULE_COMPONENT_ID} = $buddypress_listings;

        add_action( 'redirect_canonical', array( $buddypress_listings, 'redirect_canonical' ), 10, 2 );
    }

    public function template_stack() {
        if ( $this->buddypress->should_use_theme_compat_with_current_theme() ) {
            return AWPCP_BUDDYPRESS_LISTINGS_MODULE_DIR . '/bp-templates/bp-legacy/';
        } else {
            return AWPCP_BUDDYPRESS_LISTINGS_MODULE_DIR . '/bp-themes/bp-default/';
        }
    }

    public function template_parts() {
        return AWPCP_BUDDYPRESS_LISTINGS_MODULE_DIR . '/bp-template-parts/';
    }

    /**
     * Mostly copied from BP Attachments
     * https://github.com/buddypress/bp-attachments/blob/667cd6039cf6d9244c7a704bece4ece61b83744c/loader.php#L214
     */
    public function admin_notices() {
        $warnings = array();

        if( ! $this->is_minimum_bp_version_installed() ) {
            $message = __( 'BP Listings requires at least version %s of BuddyPress.', 'awpcp-buddypress-listings' );
            $required_version = AWPCP_BUDDYPRESS_LISTINGS_MODULE_REQUIRED_BP_VERSION;
            $warnings[] = sprintf( $message, $required_version );
        }

        if ( ! bp_core_do_network_admin() && ! $this->current_blog_matches_buddypress_blog() ) {
            $warnings[] = __( 'BP Listings requires to be activated on the blog where BuddyPress is activated.', 'awpcp-buddypress-listings' );
        }

        $plugin_basename = plugin_basename( $this->plugin_file );

        if ( bp_core_do_network_admin() && ! is_plugin_active_for_network( $plugin_basename ) ) {
            $warnings[] = __( 'BP Listings and BuddyPress need to share the same network configuration.', 'awpcp-buddypress-listings' );
        }

        if ( ! empty( $warnings ) ) {
            $template = '<div id="message" class="error"><p>%s</p></div>';
            echo sprintf( $template, implode( '</p><p>', array_map( 'esc_html', $warnings ) ) );
        }
    }

    private function is_minimum_bp_version_installed() {
        if ( ! defined( 'BP_VERSION' ) ) {
            return false;
        }

        return version_compare( BP_VERSION, AWPCP_BUDDYPRESS_LISTINGS_MODULE_REQUIRED_BP_VERSION, '>=' );
    }
}
