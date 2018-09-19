<?php

function awpcp_campaigns_collection() {
    return new AWPCP_CampaignsCollection( $GLOBALS['wpdb'] );
}

/**
 * @since 1.0
 */
class AWPCP_CampaignsCollection {

    private $db;

    public function __construct( $db ) {
        $this->db = $db;
    }

    public function all() {
        $sql = 'SELECT * FROM ' . AWPCP_TABLE_CAMPAIGNS . ' ORDER BY is_placeholder DESC, id ASC';
        return $this->db->get_results( $sql );
    }

    public function get( $campaign_id ) {
        $sql = 'SELECT * FROM ' . AWPCP_TABLE_CAMPAIGNS . ' WHERE id = %d';
        $campaign = $this->db->get_row( $this->db->prepare( $sql, $campaign_id ) );

        if ( is_null( $campaign ) ) {
            $message = __( 'No Campaign was found with ID: <campaign-id>', 'awpcp-campaign-manager' );
            $message = str_replace( '<campaign-id>', $campaign_id, $message );

            throw new AWPCP_Exception( $message );
        }

        return $campaign;
    }

    public function get_placeholder_campaign() {
        $campaign = $this->db->get_row( 'SELECT * FROM ' . AWPCP_TABLE_CAMPAIGNS . ' WHERE is_placeholder = 1' );

        if ( is_null( $campaign ) ) {
            $message = __( 'There is currently no placeholder campaign.', 'awpcp-campaign-manager' );
            throw new AWPCP_Exception( $message );
        }

        return $campaign;
    }
}
