<?php

if ( class_exists( 'AWPCP_AjaxHandler' ) ) {

/**
 * @since 3.2.0
 */
function awpcp_regenerate_regions_sidelist_ajax_handler() {
    return new AWPCP_RegenerateRegionsSidelistAjaxHandler( awpcp_regions_sidelist_builder(), awpcp_ajax_response() );
}

/**
 * @since 3.2.0
 */
class AWPCP_RegenerateRegionsSidelistAjaxHandler extends AWPCP_AjaxHandler {

    /**
     * @since 3.2.0
     */
    public function __construct( $sidelist_builder, $response ) {
        parent::__construct( $response );

        $this->sidelist_builder = $sidelist_builder;
    }

    /**
     * @since 3.2.0
     */
    public function ajax() {
        try {
            $sidelist = $this->sidelist_builder->build();
            return $this->progress_response( $sidelist->size(), 0 );
        } catch ( AWPCP_IOError $e ) {
            return $this->error_response( $e->getMessage() );
        } catch ( AWPCP_TooManyRecordsLeftException $e ) {
            return $this->progress_response( $e->records_left + $e->records_processed, $e->records_left );
        }
    }
}

}
