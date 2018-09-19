<?php

/**
 * @since 3.2.3
 */
function awpcp_regions_listings_count_repairer() {
    return new AWPCP_RegionsListingsCountRepairer( 50, awpcp_regions_listings_record_finder(), awpcp_regions_api() );
}

/**
 * @since 3.2.3
 */
class AWPCP_RegionsListingsCountRepairer {

    private $records_per_step;
    private $records_finder;

    /**
     * @since 3.2.3
     */
    public function __construct( $records_per_step, $records_finder, $regions_api ) {
        $this->records_per_step = $records_per_step;
        $this->records_finder = $records_finder;
        $this->regions_api = $regions_api;
    }

    /**
     * @since 3.2.3
     */
    public function repair() {
        $status = $this->get_status();

        if ( ! $status['last_record'] ) {
            $this->regions_api->clear_listings_count();
        }

        $records = $this->get_grouped_records_with_ad_id_greater_than( $status['last_record'], $this->records_per_step );
        $pending_records_count = $this->records_finder->count_records_with_ad_id_greater_than( $status['last_record'] );

        foreach ( $records as $ad_id => $entries ) {
            foreach ( $entries as $id => $records ) {
                $this->update_listings_count( $records );
            }

            $status['last_record'] = $ad_id;
        }

        $status['records_left'] = $this->records_finder->count_records_with_ad_id_greater_than( $status['last_record'] );
        $status['records_processed'] = $status['records_processed'] + $pending_records_count - $status['records_left'];

        if ( $status['records_left'] > 0 ) {
            $this->save_status( $status );

            $message = sprintf( 'There are still %d regions that need their listings count updated.', $status['records_left'] );
            throw new AWPCP_TooManyRecordsLeftException( $message, $status['records_processed'], $status['records_left'] );
        } else {
            $this->clear_status();
        }

        return $status['records_processed'];
    }

    /**
     * @since 3.2.3
     */
    private function get_status() {
        $default = array(
            'records_processed' => 0,
            'records_left' => null,
            'last_record' => null
        );

        return get_option( 'awpcp-regions-listings-count', $default );
    }

    /**
     * @since 3.2.3
     */
    private function save_status( $status ) {
        update_option( 'awpcp-regions-listings-count', $status );
    }

    /**
     * @since 3.2.3
     */
    private function clear_status() {
        delete_option( 'awpcp-regions-listings-count' );
    }

    /**
     * @since 3.2.3
     */
    private function get_grouped_records_with_ad_id_greater_than( $ad_id, $limit ) {
        $records = $this->records_finder->get_records_with_ad_id_greater_than( $ad_id, $limit );
        $grouped = array();

        foreach ( $records as $record ) {
            $grouped[ $record->ad_id ][ $record->id ][] = $record;
        }

        // drop records for last Ad if the number of results matches the query limit.
        // there may be other records for that Ad that weren't returned and we need to process those too.
        if ( count( $records ) === $limit ) {
            array_pop( $grouped );
        }

        return $grouped;
    }

    /**
     * @since 3.2.3
     */
    private function update_listings_count( $records ) {
        foreach ( $this->regions_api->filter_regions( $records ) as $record ) {
            $this->regions_api->update_ad_count( $record, 1, $record->disabled ? 0 : 1 );
        }
    }
}
