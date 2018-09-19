<?php

function awpcp_buddypress_listings_navigation_builder() {
    return new AWPCP_BuddyPressListingsNavigationBuilder(
        awpcp_listings_collection(),
        awpcp()->settings,
        awpcp_buddypress_wrapper()
    );
}

class AWPCP_BuddyPressListingsNavigationBuilder {

    private $listings;
    private $settings;
    private $buddypress;

    public function __construct( $listings, $settings, $buddypress ) {
        $this->listings = $listings;
        $this->settings = $settings;
        $this->buddypress = $buddypress;
    }

    public function build_navigation( $component_id, $component_slug ) {
        try {
            $parent_url = $this->get_parent_url( $component_slug );
        } catch ( AWPCP_Exception $e ) {
            return $this->build_empty_navigation();
        }

        if ( $this->current_user_can_edit_listings() ) {
            return $this->build_authorized_user_navigation( $parent_url, $component_id, $component_slug );
        } else if ( $this->buddypress->displayed_user_id() > 0 || $this->buddypress->loggedin_user_id() > 0 ) {
            return $this->build_other_user_navigation( $parent_url, $component_id, $component_slug );
        } else {
            return $this->build_empty_navigation();
        }
    }

    private function get_parent_url( $component_slug ) {
        if ( $this->buddypress->displayed_user_domain() ) {
            $user_domain = $this->buddypress->displayed_user_domain();
        } elseif ( $this->buddypress->loggedin_user_domain() ) {
            $user_domain = $this->buddypress->loggedin_user_domain();
        } else {
            throw new AWPCP_Exception();
        }

        return trailingslashit( $user_domain . $component_slug );
    }

    private function build_empty_navigation() {
        return array( 'main_nav' => array(), 'sub_nav' => array() );
    }

    private function current_user_can_edit_listings() {
        $displayed_user_id = $this->buddypress->displayed_user_id();
        $loggedin_user_id = $this->buddypress->loggedin_user_id();

        if ( awpcp_user_is_admin( $loggedin_user_id ) ) {
            return true;
        }

        if ( $displayed_user_id > 0 && $displayed_user_id === $loggedin_user_id ) {
            return true;
        }

        return false;
    }

    private function build_authorized_user_navigation( $parent_url, $component_id, $component_slug ) {
        $current_user_id = $this->buddypress->displayed_user_id();
        $listings_count = $this->listings->count_user_listings( $current_user_id );

        $main_navigation = $this->build_main_navigation_skeleton( $component_id, $component_slug, $listings_count );
        $main_navigation['screen_function'] = 'awpcp_buddypress_load_all_listings_page';

        $navigation_name = $this->build_navigation_name_with_count( __( 'All <count>', 'awpcp-buddypress-listings' ), $listings_count );
        $callback = 'awpcp_buddypress_load_all_listings_page';
        $secondary_navigation[] = $this->build_secondary_navigation_item( 'all', $navigation_name, $parent_url, $component_slug, $callback, 10 );

        $listings_count = $this->listings->count_user_enabled_listings( $current_user_id );
        $navigation_name = $this->build_navigation_name_with_count( __( 'Enabled <count>', 'awpcp-buddypress-listings' ), $listings_count );
        $callback = 'awpcp_buddypress_load_enabled_listings_page';
        $secondary_navigation[] = $this->build_secondary_navigation_item( 'enabled', $navigation_name, $parent_url, $component_slug, $callback, 20 );

        $listings_count = $this->listings->count_user_disabled_listings( $current_user_id );
        $navigation_name = $this->build_navigation_name_with_count( __( 'Disabled <count>', 'awpcp-buddypress-listings' ), $listings_count );
        $callback = 'awpcp_buddypress_load_disabled_listings_page';
        $secondary_navigation[] = $this->build_secondary_navigation_item( 'disabled', $navigation_name, $parent_url, $component_slug, $callback, 30 );

        $navigation_name = __( 'Create', 'awpcp-buddypress-listings' );
        $callback = 'awpcp_buddypress_load_create_listing_page';
        $secondary_navigation[] = $this->build_secondary_navigation_item( 'create', $navigation_name, $parent_url, $component_slug, $callback, 40 );

        $secondary_navigation = $this->maybe_add_view_navigation_item( $secondary_navigation, $parent_url, $component_slug, 50 );
        $secondary_navigation = $this->maybe_add_edit_navigation_item( $secondary_navigation, $parent_url, $component_slug, 50 );

        return array( 'main_nav' => $main_navigation, 'sub_nav' => $secondary_navigation );
    }

    private function build_main_navigation_skeleton( $component_id, $component_slug, $listings_count ) {
        $navigation_name = $this->settings->get_option( 'listings-tab-title' ) . ' <count>';
        $navigation_name = $this->build_navigation_name_with_count( $navigation_name, $listings_count );

        return array(
            'name' => $navigation_name,
            'slug' => $component_slug,
            'position' => 10,
            'default_subnav_slug' => 'all',
            'item_css_id' => $component_id,
        );
    }

    private function build_navigation_name_with_count( $template, $count ) {
        $name = str_replace( '<count>', '<span class="%s">%s</span>', $template );
        $name = sprintf( $name, $count == 0 ? 'no-count' : 'count', number_format_i18n( $count ) );
        return $name;
    }

    private function build_secondary_navigation_item( $slug, $label, $parent_url, $component_slug, $callback, $priority ) {
        return array(
            'name' => $label,
            'slug' => $slug,
            'parent_url' => $parent_url,
            'parent_slug' => $component_slug,
            'screen_function' => $callback,
            'position' => $priority,
        );
    }

    private function maybe_add_view_navigation_item( $navigation, $parent_url, $component_slug, $priority ) {
        if ( $this->is_component_action( $component_slug, 'view' ) ) {
            $navigation[] = $this->build_secondary_navigation_item(
                'view', __( 'View', 'awpcp-buddypress-listings' ),
                $parent_url, $component_slug,
                'awpcp_buddypress_load_view_listing_page',
                $priority
            );
        }

        return $navigation;
    }

    private function is_component_action( $component_slug, $action ) {
        if ( ! $this->buddypress->is_current_component( $component_slug ) ) {
            return false;
        }

        if ( $this->buddypress->current_action() != $action ) {
            return false;
        }

        return true;
    }

    private function maybe_add_edit_navigation_item( $navigation, $parent_url, $component_slug, $priority ) {
        if ( $this->is_component_action( $component_slug, 'edit' ) ) {
            $navigation[] = $this->build_secondary_navigation_item(
                'edit', __( 'Edit', 'awpcp-buddypress-listings' ),
                $parent_url, $component_slug,
                'awpcp_buddypress_load_edit_listing_page',
                $priority
            );
        }

        return $navigation;
    }

    private function build_other_user_navigation( $parent_url, $component_id, $component_slug ) {
        $current_user_id = $this->buddypress->displayed_user_id();
        $listings_count = $this->listings->count_user_enabled_listings( $current_user_id );

        $main_navigation = $this->build_main_navigation_skeleton( $component_id, $component_slug, $listings_count );
        $main_navigation['screen_function'] = 'awpcp_buddypress_load_enabled_listings_page';

        $secondary_navigation = $this->maybe_add_view_navigation_item( array(), $parent_url, $component_slug, 10 );

        return array( 'main_nav' => $main_navigation, 'sub_nav' => $secondary_navigation );
    }
}
