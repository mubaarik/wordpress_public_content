<?php

function awpcp_create_campaign_admin_page() {
    return new AWPCP_CreateCampaignAdminPage(
        awpcp_campaigns_collection(),
        awpcp_campaign_saver(),
        awpcp_campaign_sections_collection(),
        awpcp_campaign_sections_table_factory(),
        awpcp_campaign_advertisement_positions_collection(),
        awpcp_request()
    );
}

class AWPCP_CreateCampaignAdminPage extends AWPCP_AdminPage {

    private $form_template;

    private $campaigns;
    private $campaign_saver;
    private $campaign_sections;
    private $campaign_sections_table_factory;
    private $campaign_positions;
    private $request;

    public function __construct( $campaigns, $campaign_saver, $campaign_sections, $campaign_sections_table_factory, $campaign_positions, $request ) {
        $this->request = $request;

        parent::__construct( 'awpcp-manage-campaign', $this->generate_title(), $this->generate_menu_title() );

        $this->form_template = AWPCP_CAMPAIGN_MANAGER_MODULE_DIR . '/templates/admin/create-campaign-admin-page.tpl.php';

        $this->campaigns = $campaigns;
        $this->campaign_saver = $campaign_saver;
        $this->campaign_sections = $campaign_sections;
        $this->campaign_sections_table_factory = $campaign_sections_table_factory;
        $this->campaign_positions = $campaign_positions;
    }

    private function generate_title() {
        if ( $this->request->param( 'campaign', false ) ) {
            return awpcp_admin_page_title( __( 'Manage Campaign', 'awpcp-campaign-manager' ) );
        } else {
            return awpcp_admin_page_title( __( 'Create Campaign', 'awpcp-campaign-manager' ) );
        }
    }

    private function generate_menu_title() {
        if ( $this->request->param( 'campaign', false ) ) {
            return __( 'Manage Campaign', 'awpcp-campaign-manager' );
        } else {
            return __( 'Add New Campaign', 'awpcp-campaign-manager' );
        }
    }

    public function show_sidebar() {
        return false;
    }

    public function on_load() {
        wp_enqueue_style( 'awpcp-campaign-manager-admin' );
        wp_enqueue_script( 'awpcp-campaign-manager-admin' );
    }

    public function dispatch() {
        $action = $this->get_current_action();

        switch( $action ) {
            case 'create':
                $response = $this->handle_create_campaign_request();
                break;
            case 'edit':
                $response = $this->handle_edit_campaign_request();
                break;
        }

        echo $response;
    }

    public function get_current_action( $default = 'create' ) {
        return $this->request->param( 'action', $default );
    }

    private function handle_create_campaign_request() {
        if ( $this->request->method() != 'POST' ) {
            return $this->show_create_campaign_form( $this->get_posted_campaign_information() );
        } else {
            return $this->try_to_create_campaign();
        }
    }

    private function get_posted_campaign_information() {
        return array(
            'id' => $this->request->param( 'campaign', null ),
            'start_date' => stripslashes( $this->request->post( 'start_date' ) ),
            'end_date' => stripslashes( $this->request->post( 'end_date' ) ),
            'status' => $this->request->post( 'status', 'enabled' ),
        );
    }

    private function show_create_campaign_form( $campaign_data = array() ) {
        $params = array(
            'campaign_data' => array_merge(
                $campaign_data,
                array(
                    'is_placeholder' => false,
                )
            ),
            'current_user' => $this->request->get_current_user(),
            'hidden' => array( 'action' => 'create' ),
        );

        return $this->render( $this->form_template, $params );
    }

    private function try_to_create_campaign() {
        $campaign_data = $this->get_posted_campaign_information();

        try {
            $campaign_id = $this->campaign_saver->create_campaign( $campaign_data, $this->request->get_current_user() );
            awpcp_flash( __( 'The campaign was created successfully.', 'awpcp-campaign-manager' ) );
            return $this->show_edit_campaign_form( $campaign_id );
        } catch( AWPCP_Exception $e ) {
            awpcp_flash( $e->getMessage(), 'error' );
            return $this->show_create_campaign_form( $campaign_data );
        }
    }

    private function show_edit_campaign_form( $campaign_id, $campaign_data = array() ) {
        try {
            $campaign = $this->campaigns->get( $campaign_id );
        } catch ( AWPCP_Exception $e ) {
            awpcp_flash( $e->getMessage(), 'error' );
            return $this->render( 'content', '' );
        }

        $campaign_sections = $this->campaign_sections->get_campaign_sections( $campaign_id );
        $campaign_positions = $this->campaign_positions->find_active_positions_by_campaign_id( $campaign_id );

        $campaign_sections_table = $this->campaign_sections_table_factory->create_table( $this );
        $campaign_sections_table->set_items( $campaign_sections );
        $campaign_sections_table->prepare_items();

        $params = array(
            'campaign_data' => array_merge(
                array(
                    'id' => $campaign->id,
                    'start_date' => $campaign->start_date,
                    'end_date' => $campaign->end_date,
                    'status' => $campaign->status,
                    'is_placeholder' => $campaign->is_placeholder,
                ),
                $campaign_data
            ),
            'campaign_sections_table' => $campaign_sections_table,
            'campaign_positions' => $campaign_positions,
            'current_user' => $this->request->get_current_user(),
            'hidden' => array( 'action' => 'edit' ),
        );

        return $this->render( $this->form_template, $params );
    }

    private function handle_edit_campaign_request() {
        if ( $this->request->method() != 'POST' ) {
            return $this->show_edit_campaign_form( $this->request->param( 'campaign', null ) );
        } else {
            return $this->try_to_update_campaign();
        }
    }

    private function try_to_update_campaign() {
        $campaign_data = $this->get_posted_campaign_information();

        try {
            $this->campaign_saver->update_campaign( $campaign_data );
            awpcp_flash( __( 'The campaign was updated successfully.', 'awpcp-campaign-manager' ) );
            return $this->show_edit_campaign_form( $campaign_data['id'] );
        } catch ( AWPCP_Exception $e ) {
            awpcp_flash( $e->getMessage(), 'error' );
            return $this->show_edit_campaign_form( $campaign_data['id'], $campaign_data );
        }
    }
}
