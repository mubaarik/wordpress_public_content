<?php

/**
 * @since 3.2.3
 */
function awpcp_regions_listings_record_finder() {
    return new AWPCP_RegionsListingsRecordFinder( $GLOBALS['wpdb'] );
}

/**
 * @since 3.2.3
 */
class AWPCP_RegionsListingsRecordFinder {

    private $db;

    public function __construct( $db ) {
        $this->db = $db;
    }

    /**
     * @since 3.2.3
     */
    public function get_records_with_ad_id_greater_than( $ad_id, $limit ) {
        $query = $this->db->prepare( $this->records_query(), $ad_id, $limit );
        return $this->db->get_results( $query );
    }

    /**
     * @since 3.2.3
     */
    private function records_query() {
        return 'SELECT ar.ad_id, ar.id, a.disabled, r.* ' . $this->partial_query() . ' LIMIT %d ';
    }

    /**
     * @since 3.2.3
     */
    private function partial_query() {
        $query = '
        FROM ' . AWPCP_TABLE_ADS . ' AS a
        LEFT JOIN ' . AWPCP_TABLE_AD_REGIONS . ' AS ar ON ( a.ad_id = ar.ad_id )
        LEFT JOIN ' . AWPCP_TABLE_REGIONS . ' AS r ON (
            (r.region_name = ar.country AND r.region_type = 2) OR
            (r.region_name = ar.state AND r.region_type = 3) OR
            (r.region_name = ar.city AND r.region_type = 4) OR
            (r.region_name = ar.county AND r.region_type = 5)
        )
        WHERE ar.ad_id > %d
        ORDER BY ar.ad_id, ar.id ';

        return preg_replace( '/\s+/', ' ', trim( $query ) );
    }


    /**
     * @since 3.2.3
     */
    public function count_records_with_ad_id_greater_than( $ad_id ) {
        $query = $this->db->prepare( 'SELECT COUNT(*) ' . $this->partial_query(), $ad_id );
        return $this->db->get_var( $query );
    }
}
