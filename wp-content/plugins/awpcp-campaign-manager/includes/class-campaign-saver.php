<?php

function awpcp_campaign_saver() {
    return new AWPCP_CampaignSaver( $GLOBALS['wpdb'] );
}

class AWPCP_CampaignSaver {

    private $db;

    public function __construct( $db ) {
        $this->db = $db;
    }

    public function create_campaign( $campaign_data, $sales_representative ) {
        $sanitized_data = array_merge( $this->sanitize_data( $campaign_data ), array(
            'sales_representative_id' => $sales_representative->ID,
            'creation_date' => current_time( 'mysql' )
        ) );

        try {
            $this->validate_create_data( $sanitized_data );
        } catch ( AWPCP_Exception $e ) {
            $message = __( "The campaign couldn't be created because the information contains errors: <errors>.", 'awpcp-campaign-manager' );
            $message = str_replace( '<errors>', $e->format_errors(), $message );
            throw new AWPCP_Exception( $message );
        }

        $result = $this->db->insert( AWPCP_TABLE_CAMPAIGNS, $sanitized_data );

        if ( $result === false ) {
            $this->throw_database_exception();
        }

        return $this->db->insert_id;
    }

    private function sanitize_data( $campaign_data ) {
        $campaign_data['start_date'] = awpcp_datetime( 'mysql', strtotime( $campaign_data['start_date'] ) );
        $campaign_data['end_date'] = awpcp_datetime( 'mysql', strtotime( $campaign_data['end_date'] ) );

        return $campaign_data;
    }

    private function validate_create_data( $campaign_data ) {
        if ( empty( $campaign_data['sales_representative_id'] ) ) {
            throw new AWPCP_Exception( __( 'sales_representative_id field is empty. The campaign must be associated to a Sales Representative', 'awpcp-campaign-manager' ) );
        }

        return $this->validate_common_data( $campaign_data );
    }

    private function validate_common_data( $campaign_data ) {
        if ( empty( $campaign_data['start_date'] ) ) {
            throw new AWPCP_Exception( __( 'The Start Date is a requiered field' ) );
        }

        if ( empty( $campaign_data['end_date'] ) ) {
            throw new AWPCP_Exception( __( 'The End Date is a requiered field' ) );
        }

        $start_date = strtotime( $campaign_data['start_date'] );
        $end_date = strtotime( $campaign_data['end_date'] );

        if ( $start_date > $end_date ) {
            throw new AWPCP_Exception( __( 'The Start Date should occur before End Date' ) );
        }

        if ( ! in_array( $campaign_data['status'], array( 'enabled', 'disabled' ) ) ) {
            throw new AWPCP_Exception( 'Status must be either enabled or disabled' );
        }
    }

    private function throw_database_exception() {
        $message = __( 'There was an error trying to save the campaign to the database.', 'awpcp-campaign-manager' );
        throw new AWPCP_DatabaseException( $message, $this->db->last_error );
    }

    public function update_campaign( $campaign_data ) {
        $sanitized_data = $this->sanitize_data( $campaign_data );

        try {
            $this->validate_common_data( $sanitized_data );
        } catch ( AWPCP_Exception $e ) {
            $message = __( "The campaign couldn't be updated because the information contains errors: <errors>.", 'awpcp-campaign-manager' );
            $message = str_replace( '<errors>', $e->format_errors(), $message );
            throw new AWPCP_Exception( $message );
        }

        $result = $this->db->update( AWPCP_TABLE_CAMPAIGNS, $sanitized_data, array( 'id' => $campaign_data['id'] ) );

        if ( $result === false ) {
            $this->throw_database_exception();
        }

        return $result;
    }
}
