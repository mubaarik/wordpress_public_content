<?php

function awpcp_delete_campaign_section_service() {
    return new AWPCP_DeleteCampaignSectionService( $GLOBALS['wpdb'] );
}

class AWPCP_DeleteCampaignSectionService {

    private $db;

    public function __construct( $db ) {
        $this->db = $db;
    }

    public function delete_campaign_section( $campaign_section_id ) {
        $this->delete_campaign_section_information( $campaign_section_id );
        $this->delete_campaign_section_positions_information( $campaign_section_id );
    }

    private function delete_campaign_section_information( $campaign_section_id ) {
        $sql = 'DELETE FROM ' . AWPCP_TABLE_CAMPAIGN_SECTIONS . ' ';
        $sql.= 'WHERE id = %d';

        $result = $this->db->query( $this->db->prepare( $sql, $campaign_section_id ) );

        if ( $result === false ) {
            $this->throw_database_exception();
        }
    }

    private function throw_database_exception() {
        $message = __( 'There was an error trying to delete the campaign section information from the database.', 'awpcp-campaign-manager' );
        throw new AWPCP_DatabaseException( $message, $this->db->last_error );
    }

    private function delete_campaign_section_positions_information( $campaign_section_id ) {
        $sql = 'DELETE FROM ' . AWPCP_TABLE_CAMPAIGN_SECTION_ADVERTISEMENT_POSITIONS . ' ';
        $sql.= 'WHERE campaign_section_id = %d';

        $result = $this->db->query( $this->db->prepare( $sql, $campaign_section_id ) );

        if ( $result === false ) {
            $this->throw_database_exception();
        }
    }
}
