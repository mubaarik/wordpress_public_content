<?php

if ( class_exists( 'AWPCP_AjaxHandler' ) ) {

/**
 * @since 3.2.0
 */
function awpcp_calculate_regions_listings_count_ajax_handler() {
    return new AWPCP_CalculateRegionsListingsCountAjaxHandler( awpcp_regions_listings_count_repairer(), awpcp_ajax_response() );
}

/**
 * @since 3.2.0
 */
class AWPCP_CalculateRegionsListingsCountAjaxHandler extends AWPCP_AjaxHandler {

    /**
     * @since 3.2.0
     */
    public function __construct( $listings_count_repairer, $response ) {
        parent::__construct( $response );
        $this->listings_count_repairer = $listings_count_repairer;
    }

    /**
     * @since 3.2.0
     */
    public function ajax() {
        try {
            $records_repaired = $this->listings_count_repairer->repair();
            return $this->progress_response( $records_repaired, 0 );
        } catch( AWPCP_TooManyRecordsLeftException $e ) {
            return $this->progress_response( $e->records_processed + $e->records_left, $e->records_left );
        }
    }
}

}
