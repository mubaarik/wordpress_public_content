<?php

function awpcp_user_default_location_handler() {
    return new AWPCP_UserDeafultLocationHandler(
        awpcp_location_service(),
        awpcp_regions_api(),
        awpcp()->settings,
        awpcp_request()
    );
}

class AWPCP_UserDeafultLocationHandler {

    private $location_service;
    private $regions;
    private $settings;
    private $request;

    public function __construct( $location_service, $regions, $settings, $request ) {
        $this->location_service = $location_service;
        $this->regions = $regions;
        $this->settings = $settings;
        $this->request = $request;
    }

    public function maybe_set_current_user_location_as_active_region() {
        $current_location = $this->location_service->get_current_location_from_request();

        if ( empty( $current_location ) && is_user_logged_in() ) {
            $current_user = $this->request->get_current_user();
            $this->maybe_set_logged_in_user_location_as_active_region( $current_user->user_login, $current_user );
        }
    }

    public function maybe_set_logged_in_user_location_as_active_region( $user_login, $user ) {
        if ( $this->settings->get_option( 'set-logged-in-user-default-location-as-active-region' ) ) {
            $this->set_logged_in_user_location_as_active_region( $user );
        }
    }

    private function set_logged_in_user_location_as_active_region( $user ) {
        $profile_location = $this->location_service->get_user_location( $user );

        try {
            $region = $this->get_user_region_from_profile_location( $profile_location );
        } catch ( AWPCP_Exception $e ) {
            $region = null;
        }

        $this->location_service->set_active_region( $region );
    }

    private function get_user_region_from_profile_location( $profile_location ) {
        return $this->regions->find_most_specific_region( $profile_location );
    }

    public function maybe_update_active_region_with_user_profile_information( $profile_information, $user_id ) {
        if ( ! $this->settings->get_option( 'set-logged-in-user-default-location-as-active-region' ) ) {
            return;
        }

        try {
            $this->update_active_region_with_user_profile_information( $profile_information, $user_id );
        } catch ( AWPCP_Exception $e ) {
            return;
        }
    }

    private function update_active_region_with_user_profile_information( $profile_information, $user_id ) {
        $profile_location = $this->get_user_location_from_profile_information( $profile_information );
        $region = $this->get_user_region_from_profile_location( $profile_location );
        $this->location_service->set_active_region( $region );
    }

    private function get_user_location_from_profile_information( $profile_information ) {
        return $this->location_service->get_user_location_from_profile_information( $profile_information );
    }

    public function set_active_region_as_default_user_location_in_listing_details_form( $user_info, $user_id ) {
        $regions = awpcp_prepare_active_region_for_region_selector();

        if ( ! empty( $regions ) ) {
            $user_info = array_merge( $user_info, array( 'regions' => $regions ) );
        }

        return $user_info;
    }

    public function set_active_region_as_default_user_location_in_search_ads_form( $posted_data, $context ) {
        if ( $context !== 'search' ) {
            return $posted_data;
        }

        if ( isset( $posted_data['regions'] ) && ! empty( $posted_data['regions'] ) ) {
            return $posted_data;
        }

        $regions = awpcp_prepare_active_region_for_region_selector();

        if ( empty( $regions ) ) {
            return $posted_data;
        }

        return array_merge( $posted_data, array( 'regions' => $regions ) );
    }
}
