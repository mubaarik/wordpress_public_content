<?php

function awpcp_campaign_advertisement_positions_saver() {
    return new AWPCP_CampaignAdvertisementPositionsSaver(
        awpcp_campaign_advertisement_positions_collection(),
        $GLOBALS['wpdb']
    );
}

class AWPCP_CampaignAdvertisementPositionsSaver {

    private $campaign_advertisement_positions;
    private $db;

    public function __construct( $campaign_advertisement_positions, $db ) {
        $this->campaign_advertisement_positions = $campaign_advertisement_positions;
        $this->db = $db;
    }

    public function update_campaign_advertisment_positions( $campaign_id, $selected_positions ) {
        $missing_campaign_positions = $this->get_missing_campaign_advertisement_positions( $campaign_id, $selected_positions );
        $this->create_campaign_advertisement_positions( $campaign_id, $missing_campaign_positions );
    }

    private function get_missing_campaign_advertisement_positions( $campaign_id, $selected_positions ) {
        $campaign_positions = $this->campaign_advertisement_positions->find_by_campaign_id( $campaign_id );
        $existing_campaign_positions = array();

        foreach ( $campaign_positions as $campaign_position ) {
            $existing_campaign_positions[] = $campaign_position->get_slug();
        }

        $missing_campaign_positions = array_diff( $selected_positions, $existing_campaign_positions );

        return $missing_campaign_positions;
    }

    private function create_campaign_advertisement_positions( $campaign_id, $advertisement_position ) {
        foreach ( $advertisement_position as $advertisement_position ) {
            $this->db->insert( AWPCP_TABLE_CAMPAIGN_ADVERTISEMENT_POSITIONS, array(
                'campaign_id' => $campaign_id,
                'advertisement_position' => $advertisement_position
            ) );
        }
    }

    public function update_campaign_advertisment_position( $campaign_id, $advertisement_position, $position_data ) {
        $sanitized_data = $this->sanitize_data( $position_data );

        $where = array(
            'campaign_id' => $campaign_id,
            'advertisement_position' => $advertisement_position,
        );

        $result = $this->db->update( AWPCP_TABLE_CAMPAIGN_ADVERTISEMENT_POSITIONS, $sanitized_data, $where );

        if ( $result === false ) {
            $message = __( 'There was an error trying to save the campaign advertisement position information.', 'awpcp-campaign-manager' );
            throw new AWPCP_DatabaseException( $message, $this->db->last_error );
        }

        return $result;
    }

    private function sanitize_data( $position_data ) {
        $sanitized_data['content_type'] = $position_data['content_type'];
        $sanitized_data['content'] = $position_data['content'];
        $sanitized_data['is_executable'] = $position_data['is_executable'];
        $sanitized_data['image_link'] = trim( $position_data['image_link'] );

        if ( ! empty( $position_data['image_path'] ) ) {
            $sanitized_data['image_path'] = $position_data['image_path'];
        }

        return $sanitized_data;
    }
}
