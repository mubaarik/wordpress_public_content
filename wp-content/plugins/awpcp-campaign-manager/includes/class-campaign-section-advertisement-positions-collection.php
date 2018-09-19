<?php

function awpcp_campaign_section_advertisement_positions_collection() {
    return new AWPCP_CampaignSectionAdvertisementPositionsCollection( $GLOBALS['wpdb'] );
}

class AWPCP_CampaignSectionAdvertisementPositionsCollection {

    private $db;

    public function __construct( $db ) {
        $this->db = $db;
    }

    public function find_campaign_section_positions( $campaign_section_id ) {
        $sql = 'SELECT DISTINCT csp.advertisement_position ';
        $sql.= 'FROM ' . AWPCP_TABLE_CAMPAIGN_SECTION_ADVERTISEMENT_POSITIONS . ' AS csp ';
        $sql.= 'WHERE csp.campaign_section_id = %d ';

        return $this->db->get_col( $this->db->prepare( $sql, $campaign_section_id ) );
    }
}
