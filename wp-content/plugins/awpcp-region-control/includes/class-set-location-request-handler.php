<?php

function awpcp_set_location_request_handler() {
    return new AWPCP_SetLocationRequestHandler(
        awpcp_region_control_module(),
        awpcp_regions_api(),
        awpcp_request()
    );
}

class AWPCP_SetLocationRequestHandler {

    private $location_service;
    private $regions;
    private $request;

    public function __construct( $location_service, $regions, $request ) {
        $this->location_service = $location_service;
        $this->regions = $regions;
        $this->request = $request;
    }

    public function dispatch() {
        if ( $this->is_set_location_request() ) {
            $this->handle_set_location_request();
        }

        if ( $this->is_set_region_request() ) {
            $this->handle_set_region_request();
        }
    }

    private function is_set_location_request() {
        $awpcpx = $this->request->get_query_var( 'awpcpx' );

        if ( empty( $awpcpx ) ) {
            return false;
        }

        if ( strcmp( $this->request->get_query_var( 'awpcp-module' ), 'regions' ) !== 0 ) {
            return false;
        }

        if ( strcmp( $this->request->get_query_var( 'awpcp-action' ), 'set-location' ) !== 0 ) {
            return false;
        }

        return true;
    }

    private function handle_set_location_request() {
        try {
            if ( strlen( $this->request->post( 'set-location' ) ) > 0 ) {
                $this->set_location();
            } else if ( strlen( $this->request->post( 'clear-location' ) ) > 0 ) {
                $this->clear_location();
            }
        } catch ( AWPCP_Exception $e ) {
            // ignore exceptions, let's redirect
        }

        return $this->redirect();
    }

    private function set_location() {
        $region = $this->find_posted_region();

        if ( $this->request->post( 'set-as-default' ) === '1' ) {
            $this->location_service->set_user_default_location( $region );
            $this->location_service->set_location( $region );
        } else {
            $this->location_service->set_location( $region );
        }
    }

    private function find_posted_region() {
        return $this->regions->find_most_specific_region( $this->get_posted_region_data() );
    }

    private function get_posted_region_data() {
        $regions_data = $this->request->post( 'regions' );

        if ( is_array( $regions_data ) && count( $regions_data ) > 0 ) {
            $region_data = $regions_data[0];
        } else {
            $region_data = array();
        }

        return $region_data;
    }

    private function clear_location() {
        $this->location_service->set_location( null );
    }

    private function redirect() {
        $regex = '#setregion/\d+/.+#';

        $referer = wp_get_referer();
        $query = wp_parse_args( awpcp_array_data( 'query', array(), parse_url( $referer ) ) );

        // avoid collisions with sidelist functionality
        if ( awpcp_array_data( 'a', '', $query ) == 'setregion' ) {
            $referer = remove_query_arg( array( 'a', 'regionid' ), $referer );
        } elseif ( preg_match( $regex, $referer ) ) {
            $referer = preg_replace( $regex, '', $referer );
        }

        wp_redirect( $referer );
        exit();
    }

    private function is_set_region_request() {
        $region_id = $this->request->get_query_var( 'regionid' );
        $action = $this->request->param( 'a' );

        if ( $action == 'setregion' || ! empty( $region_id ) ) {
            return true;
        }

        return false;
    }

    private function handle_set_region_request() {
        $region_id = $this->request->param( 'regionid', $this->request->get_query_var( 'regionid' ) );

        if ( empty( $region_id ) ) {
            return;
        }

        $region = $this->regions->find_by_id( $region_id );

        if ( ! is_object( $region ) ) {
            return;
        }

        $this->location_service->set_location( $region );

        return $this->redirect();
    }
}
