<?php

function awpcp_campaign_section_saver() {
    return new AWPCP_CampaignSectionSaver(
        awpcp_campaign_advertisement_positions_saver(),
        $GLOBALS['wpdb']
    );
}

class AWPCP_CampaignSectionSaver {

    private $campaign_advertisement_positions_saver;
    private $db;

    public function __construct( $campaign_advertisement_positions_saver, $db ) {
        $this->campaign_advertisement_positions_saver = $campaign_advertisement_positions_saver;
        $this->db = $db;
    }

    public function create_campaign_section( $campaign_section_data ) {
        $sanitized_data = $this->sanitize_campaign_section_data( $campaign_section_data );

        $this->validate_campaign_section_data( $sanitized_data );

        $insert_data = array(
            'campaign_id' => $sanitized_data['campaign_id'],
            'category_id' => $sanitized_data['category_id'],
            'pages' => maybe_serialize( $sanitized_data['pages'] ),
        );

        $result = $this->db->insert( AWPCP_TABLE_CAMPAIGN_SECTIONS, $insert_data );

        if ( $result === false ) {
            $this->throw_database_exception();
        }

        $campaign_section_id = $this->db->insert_id;

        $this->campaign_advertisement_positions_saver->update_campaign_advertisment_positions( $sanitized_data['campaign_id'], $sanitized_data['positions'] );
        $this->update_campaign_section_advertisement_positions( $campaign_section_id, $sanitized_data['positions'] );

        return $campaign_section_id;
    }

    private function sanitize_campaign_section_data( $campaign_section_data ) {
        $sanitized_data['campaign_id'] = absint( $campaign_section_data['campaign_id'] );
        $sanitized_data['category_id'] = absint( $campaign_section_data['category_id'] );

        $sanitized_data['pages'] = array_map( 'absint', (array) $campaign_section_data['pages'] );
        $sanitized_data['pages'] = array_filter( $sanitized_data['pages'] );

        $sanitized_data['positions'] = array();

        foreach ( $campaign_section_data['positions'] as $slug ) {
            if ( preg_match( '/top|bottom|footer|sidebar-one|sidebar-two|middle-\d+/', $slug ) ) {
                $sanitized_data['positions'][] = $slug;
            }
        }

        return $sanitized_data;
    }

    private function validate_campaign_section_data( $campaign_section_data ) {
        if ( empty( $campaign_section_data['campaign_id'] ) ) {
            throw new AWPCP_Exception( __( 'The ID of the campaign is missing.', 'awpcp-campaign-manager' ) );
        }

        if ( empty( $campaign_section_data['category_id'] ) ) {
            throw new AWPCP_Exception( __( 'No category was selected. The campaign section must be assocaited to a category.', 'awpcp-campaign-manager' ) );
        }

        if ( empty( $campaign_section_data['pages'] ) ) {
            throw new AWPCP_Exception( __( 'No pages were selected. The campaign must be associated to at least one page of results.', 'awpcp-campaign-manager' ) );
        }

        if ( empty( $campaign_section_data['positions'] ) ) {
            throw new AWPCP_Exception( __( 'No advertisment positions were selected. The campaign must be associated to at least one advertisment position.', 'awpcp-campaign-manager' ) );
        }
    }

    private function throw_database_exception() {
        $message = __( 'There was an error trying to save the campaign section to the database.', 'awpcp-campaign-manager' );
        throw new AWPCP_DatabaseException( $message, $this->db->last_error );
    }

    private function update_campaign_section_advertisement_positions( $campaign_section_id, $selected_positions ) {
        $sql = 'DELETE FROM ' . AWPCP_TABLE_CAMPAIGN_SECTION_ADVERTISEMENT_POSITIONS . ' ';
        $sql.= 'WHERE campaign_section_id = %d';

        $this->db->query( $this->db->prepare( $sql, $campaign_section_id ) );

        foreach ( $selected_positions as $advertisement_position ) {
            $this->db->insert( AWPCP_TABLE_CAMPAIGN_SECTION_ADVERTISEMENT_POSITIONS, array(
                'campaign_section_id' => $campaign_section_id,
                'advertisement_position' => $advertisement_position
            ) );
        }
    }

    public function update_campaign_section( $campaign_section_id, $campaign_section_data ) {
        $sanitized_data = $this->sanitize_campaign_section_data( $campaign_section_data );

        $this->validate_campaign_section_data( $sanitized_data );

        $update_data = array(
            'category_id' => $sanitized_data['category_id'],
            'pages' => maybe_serialize( $sanitized_data['pages'] ),
        );
        $where = array( 'id' => $campaign_section_id );

        $result = $this->db->update( AWPCP_TABLE_CAMPAIGN_SECTIONS, $update_data, $where );

        if ( $result === false ) {
            $this->throw_database_exception();
        }

        $this->campaign_advertisement_positions_saver->update_campaign_advertisment_positions( $sanitized_data['campaign_id'], $sanitized_data['positions'] );
        $this->update_campaign_section_advertisement_positions( $campaign_section_id, $sanitized_data['positions'] );

        return true;
    }
}
