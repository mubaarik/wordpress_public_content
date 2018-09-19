<?php

function awpcp_remove_sold_listings_cron_job() {
    return new AWPCP_RemoveSoldListingsCronJob( awpcp()->settings, $GLOBALS['wpdb'] );
}

class AWPCP_RemoveSoldListingsCronJob {

    private $settings;
    private $db;

    public function __construct( $settings, $db ) {
        $this->settings = $settings;
        $this->db = $db;
    }

    public function run() {
        if ( $this->settings->get_option( 'remove-sold-items' ) ) {
            $this->remove_sold_items_after_n_days( $this->settings->get_option( 'remove-sold-items-after-n-days', 30 ) );
        }
    }

    private function remove_sold_items_after_n_days( $days ) {
        // Yesterday at 0:00:00
        $yesterday = awpcp_datetime( 'mysql', strtotime( "today - $days days", current_time( 'timestamp' ) ) );

        // TODO: can I generate this using WP_Meta_Query class?
        $sql = 'SELECT  listings.* FROM ' . AWPCP_TABLE_ADS . ' AS listings ';
        $sql.= 'INNER JOIN ' . AWPCP_TABLE_AD_META . " AS m1 ON listings.ad_id = m1.awpcp_ad_id ";
        $sql.= 'INNER JOIN ' . AWPCP_TABLE_AD_META . " AS m2 ON listings.ad_id = m2.awpcp_ad_id ";
        $sql.= "WHERE m1.meta_key = 'is-sold' AND CAST( m1.meta_value AS BINARY ) = 1 ";
        $sql.= "AND m2.meta_key = 'sold-at' AND CAST( m2.meta_value AS DATETIME ) < %s ";

        $objects = $this->db->get_results( $this->db->prepare( $sql, $yesterday ) );

        if ( ! is_array( $objects ) ) {
            return;
        }

        $listings = array_map( array( 'AWPCP_Ad', 'from_object' ), $objects );

        foreach ( $listings as $listing ) {
            $listing->delete();
        }
    }
}
