<?php

if ( class_exists( 'AWPCP_AjaxHandler' ) ) {

function awpcp_load_advertisement_positions_content_forms_ajax_handler() {
    return new AWPCP_LoadAdvertisementPositionsContentFormsAjaxHandler(
        awpcp_campaign_advertisement_positions_collection(),
        awpcp_request(),
        awpcp_ajax_response()
    );
}

class AWPCP_LoadAdvertisementPositionsContentFormsAjaxHandler extends AWPCP_AjaxHandler {

    private $campaign_positions;
    private $request;

    public function __construct( $campaign_positions, $request, $response ) {
        parent::__construct( $response );

        $this->campaign_positions = $campaign_positions;
        $this->request = $request;
    }

    public function ajax() {
        $campaign_id = $this->request->param( 'campaign' );
        $active_campaign_positions = $this->campaign_positions->find_active_positions_by_campaign_id( $campaign_id );

        $content_forms = array();

        foreach ( $active_campaign_positions as $campaign_position ) {
            $content_forms[] = array(
                'slug' => $campaign_position->get_slug(),
                'form' => $this->render_advertisement_position_content_form( $campaign_position ),
            );
        }

        return $this->success( array( 'forms' => $content_forms ) );
    }

    private function render_advertisement_position_content_form( $campaign_position ) {
        $campaign_data = array( 'id' => $campaign_position->get_campaign_id() );

        ob_start();
        include( AWPCP_CAMPAIGN_MANAGER_MODULE_DIR . '/templates/admin/campaign-advertisement-position-content-form.tpl.php' );
        $content_form = ob_get_contents();
        ob_end_clean();

        return $content_form;
    }
}

}
