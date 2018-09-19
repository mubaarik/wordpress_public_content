<?php

function awpcp_location_service() {
    return new AWPCP_LocationService( awpcp_regions_api(), awpcp_cookie_manager(), awpcp_request() );
}

class AWPCP_LocationService {

    private $regions;
    private $cookies;
    private $request;

    public function __construct( $regions, $cookies, $request ) {
        $this->regions = $regions;
        $this->cookies = $cookies;
        $this->request = $request;
    }

    public function set_active_region( $region ) {
        if ( is_object( $region ) ) {
            $current_location = $this->save_current_location( $region );
        } else {
            $current_location = $this->save_empty_location();
        }

        return $current_location;
    }

    private function save_current_location( $region ) {
        $current_location = $this->build_current_location_from_active_region( $region );

        $this->save_location( $current_location );
        $this->create_cache_flag();

        return $current_location;
    }

    private function save_location( $location ) {
        $this->remove_current_location_from_session();

        $cookie_value = array_merge(
            array( 'timestamp' => current_time( 'timestamp' ) ),
            $this->normalize_location_array( $location )
        );

        $this->cookies->set_cookie( 'awpcp-regions-current-location', $cookie_value );
    }

    private function create_cache_flag() {
        $this->cookies->set_cookie( 'awpcp-using-session-cookies', true );
    }

    private function save_empty_location() {
        $current_location = $this->build_empty_location();

        $this->save_location( $current_location );
        $this->remove_cache_flag();

        return $current_location;
    }

    private function remove_cache_flag() {
        $this->cookies->clear_cookie( 'awpcp-using-session-cookies' );
    }

    private function build_current_location_from_active_region( $region ) {
        $current_location = array();

        $parent = $region;

        do {
            switch ($parent->region_type) {
                case AWPCP_RegionsAPI::TYPE_COUNTRY:
                    $current_location['regioncountryID'] = $parent->region_id;
                    break;
                case AWPCP_RegionsAPI::TYPE_STATE:
                    $current_location['regionstatownID'] = $parent->region_id;
                    break;
                case AWPCP_RegionsAPI::TYPE_CITY:
                    $current_location['regioncityID'] = $parent->region_id;
                    break;
                case AWPCP_RegionsAPI::TYPE_COUNTY:
                    $current_location['region-county-id'] = $parent->region_id;
                    break;
            }

            $parent = $this->regions->find_by_id( $parent->region_parent );
        } while( ! is_null( $parent ) );

        $current_location['theactiveregionid'] = $region->region_id;

        return $this->normalize_location_array( $current_location );
    }

    private function build_empty_location() {
        return $this->normalize_location_array( array() );
    }

    private function normalize_location_array( $data_source ) {
        return array(
            'theactiveregionid' => awpcp_array_data( 'theactiveregionid', '', $data_source ),
            'regioncountryID' => awpcp_array_data( 'regioncountryID', '', $data_source ),
            'regionstatownID' => awpcp_array_data( 'regionstatownID', '', $data_source ),
            'regioncityID' => awpcp_array_data( 'regioncityID', '', $data_source ),
            'region-county-id' => awpcp_array_data( 'region-county-id', '', $data_source ),
        );
    }

    private function remove_current_location_from_session() {
        $empty_location = $this->build_empty_location();

        foreach ( $empty_location as $index => $value ) {
            unset( $_SESSION[ $index ] );
        }
    }

    public function get_current_location() {
        return $this->get_current_location_from_request();
    }

    public function get_current_location_from_request() {
        $current_location = $this->get_current_location_from_cookie();

        if ( ! empty( $current_location ) ) {
            return $current_location;
        }

        return $this->get_current_location_from_session();
    }

    private function get_current_location_from_cookie() {
        $current_location = $this->cookies->get_cookie( 'awpcp-regions-current-location' );
        return array_filter( (array) $current_location );
    }

    private function get_current_location_from_session() {
        $current_location = $this->normalize_location_array( isset( $_SESSION ) ? $_SESSION : array() );
        return array_filter( $current_location );
    }

    public function get_user_location( $user ) {
        $profile_information = (array) get_user_meta( $user->ID, 'awpcp-profile', true );
        return $this->get_user_location_from_profile_information( $profile_information );
    }

    public function get_user_location_from_profile_information( $profile_information ) {
        return array(
            'country' => awpcp_array_data( 'country', '', $profile_information ),
            'state' => awpcp_array_data( 'state', '', $profile_information ),
            'city' => awpcp_array_data( 'city', '', $profile_information ),
            'county' => awpcp_array_data( 'county', '', $profile_information ),
        );
    }
}
