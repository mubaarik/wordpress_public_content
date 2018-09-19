<?php

function awpcp_campaign_sections_collection() {
    return new AWPCP_CampaignSectionsCollection( awpcp_campaign_section_logic_factory(), $GLOBALS['wpdb'] );
}

class AWPCP_CampaignSectionsCollection {

    private $campaign_section_logic_factory;
    private $db;

    public function __construct( $campaign_section_logic_factory, $db ) {
        $this->campaign_section_logic_factory = $campaign_section_logic_factory;
        $this->db = $db;
    }

    public function get( $campaing_section_id ) {
        $sql = 'SELECT * FROM ' . AWPCP_TABLE_CAMPAIGN_SECTIONS . ' WHERE id = %d';
        $sql = $this->db->prepare( $sql, $campaing_section_id );

        $campaign_section = $this->db->get_row( $sql );

        if ( is_null( $campaign_section ) ) {
            $message = __( 'No campaign section was found with ID: %d.', 'awpcp-campaign-manager' );
            throw new AWPCP_Exception( sprintf( $message, $campaing_section_id ) );
        }

        return $this->campaign_section_logic_factory->create_campaign_section_logic( $campaign_section );
    }

    public function get_campaign_sections( $campaign_id ) {
        $sql = 'SELECT * FROM ' . AWPCP_TABLE_CAMPAIGN_SECTIONS . ' WHERE campaign_id = %d';

        $results = $this->db->get_results( $this->db->prepare( $sql, $campaign_id ) );
        $campaign_sections = array();

        foreach ( $results as $campaign_section ) {
            $campaign_sections[] = $this->campaign_section_logic_factory->create_campaign_section_logic( $campaign_section );
        }

        return $campaign_sections;
    }
}
