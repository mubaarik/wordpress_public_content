<?php

function awpcp_buddypress_listings_url_filter() {
    return new AWPCP_BuddyPressListingsURLFilter(
        awpcp_buddypress_wrapper(),
        awpcp()->settings
    );
}

class AWPCP_BuddyPressListingsURLFilter {

    private $buddypress;
    private $settings;

    public function __construct( $buddypress, $settings ) {
        $this->buddypress = $buddypress;
        $this->settings = $settings;
    }

    public function filter_listing_url( $url, $listing ) {
        return $this->filter_url( $url, $listing, 'view' );
    }

    private function filter_url( $url, $listing, $action ) {
        if ( ! $this->settings->get_option( 'show-listings-buddypress-in-members-profile' ) ) {
            return $url;
        }

        $module = $this->buddypress->listings();

        if ( is_null( $module ) ) {
            return $url;
        }

        $new_url = $this->get_listing_url( $listing, $action );

        return empty( $new_url ) ? $url : $new_url;
    }

    private function get_listing_url( $listing, $action ) {
        $user_domain = $this->buddypress->get_user_domain( $listing->user_id );
        $component_slug = $this->buddypress->listings()->slug;

        if ( empty( $user_domain ) || empty( $component_slug ) ) {
            $url = '';
        } else {
            $base_url = trailingslashit( $user_domain . $component_slug ) . $action . '/';
            $url = $base_url . $listing->ad_id . '/' . sanitize_title( $listing->get_title() ) . '/';
        }

        return  $url;
    }

    public function filter_edit_listing_url( $url, $listing ) {
        return $this->filter_url( $url, $listing, 'edit' );
    }

    public function filter_delete_listing_url( $url, $listing ) {
        return $this->filter_url( $url, $listing, 'delete' );
    }
}
