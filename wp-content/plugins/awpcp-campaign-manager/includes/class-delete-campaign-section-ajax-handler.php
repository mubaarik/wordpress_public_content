<?php

if ( class_exists( 'AWPCP_AjaxHandler' ) ) {

function awpcp_delete_campaign_section_ajax_handler() {
    return new AWPCP_DeleteCampaignSectionAjaxHandler(
        awpcp_delete_campaign_section_service(),
        awpcp_request(),
        awpcp_ajax_response()
    );
}

class AWPCP_DeleteCampaignSectionAjaxHandler extends AWPCP_AjaxHandler {

    private $delete_campaign_section_service;
    private $request;

    public function __construct( $delete_campaign_section_service, $request, $response ) {
        parent::__construct( $response );

        $this->delete_campaign_section_service = $delete_campaign_section_service;
        $this->request = $request;
    }

    public function ajax() {
        if ( $this->request->post( 'remove', false ) ) {
            $this->try_to_remove_campaign_section( $this->request->post( 'id' ) );
        } else {
            $this->show_remove_campagin_section_form();
        }
    }

    private function try_to_remove_campaign_section( $campaign_section_id ) {
        try {
            $this->delete_campaign_section_service->delete_campaign_section( $campaign_section_id );
        } catch ( AWPCP_Exception $e ) {
            return $this->multiple_errors_response( $e->get_errors() );
        }

        return $this->success();
    }

    private function show_remove_campagin_section_form() {
        $columns = $this->request->post( 'columns' );

        ob_start();
        include( AWPCP_DIR . '/admin/templates/delete_form.tpl.php' );
        $form = ob_get_contents();
        ob_end_clean();

        return $this->success( array( 'html' => $form ) );
    }
}

}
