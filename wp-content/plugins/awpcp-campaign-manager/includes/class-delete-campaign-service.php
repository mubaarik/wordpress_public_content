<?php

function awpcp_delete_campaign_service() {
    return new AWPCP_DeleteCampaignService(
        awpcp_delete_campaign_section_service(),
        awpcp_campaign_sections_collection(),
        awpcp()->settings,
        $GLOBALS['wpdb']
    );
}

class AWPCP_DeleteCampaignService {

    private $delete_campaign_section_service;
    private $campaign_sections;
    private $settings;
    private $db;

    public function __construct( $delete_campaign_section_service, $campaign_sections, $settings, $db ) {
        $this->delete_campaign_section_service = $delete_campaign_section_service;
        $this->campaign_sections = $campaign_sections;
        $this->settings = $settings;
        $this->db = $db;
    }

    public function delete_campaign( $campaign_id ) {
        $this->delete_campaign_sections_information( $campaign_id );
        $this->delete_adevertisement_positions_information( $campaign_id );
        $this->delete_images( $campaign_id );
        $this->delete_campaign_information( $campaign_id );
    }

    private function delete_campaign_sections_information( $campaign_id ) {
        $campaign_sections = $this->campaign_sections->get_campaign_sections( $campaign_id );

        foreach ( $campaign_sections as $campaign_section ) {
            $this->delete_campaign_section_service->delete_campaign_section( $campaign_section->get_id() );
        }
    }

    private function delete_adevertisement_positions_information( $campaign_id ) {
        $sql = 'DELETE FROM ' . AWPCP_TABLE_CAMPAIGN_ADVERTISEMENT_POSITIONS . ' ';
        $sql.= 'WHERE campaign_id = %d';

        $result = $this->db->query( $this->db->prepare( $sql, $campaign_id ) );

        if ( $result === false ) {
            $this->throw_database_exception();
        }
    }

    private function throw_database_exception() {
        $message = __( 'There was an error trying to delete the campaign information from the database.', 'awpcp-campaign-manager' );
        throw new AWPCP_DatabaseException( $message, $this->db->last_error );
    }

    private function delete_images( $campaign_id ) {
        $uploads_directory_name = $this->settings->get_option( 'uploadfoldername', 'uploads' );
        $pattern_parts = array( WP_CONTENT_DIR, $uploads_directory_name, 'awpcp', 'campaigns', "image-$campaign_id-*" );
        $pattern = implode( DIRECTORY_SEPARATOR, $pattern_parts );

        $campaign_images = glob( $pattern );

        foreach ( $campaign_images as $image_path ) {
            unlink( $image_path );
        }
    }

    private function delete_campaign_information( $campaign_id ) {
        $sql = 'DELETE FROM ' . AWPCP_TABLE_CAMPAIGNS . ' WHERE id = %d';

        $result = $this->db->query( $this->db->prepare( $sql, $campaign_id ) );

        if ( $result === false ) {
            $this->throw_database_exception();
        }

        return $result;
    }
}
