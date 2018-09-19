<?php

if ( class_exists( 'AWPCP_AjaxHandler' ) ) {

function awpcp_update_advertisement_position_content_ajax_handler() {
    return new AWPCP_UpdateAdvertisementPositionContentAjaxHandler(
        awpcp_campaign_advertisement_positions_saver(),
        awpcp_campaign_advertisement_positions_collection(),
        awpcp_campaign_advertisement_position_image_uploader(),
        awpcp_request(),
        awpcp_ajax_response()
    );
}

class AWPCP_UpdateAdvertisementPositionContentAjaxHandler extends AWPCP_AjaxHandler {

    private $campaign_positions_saver;
    private $campaign_positions;
    private $image_uploader;
    private $request;

    public function __construct( $campaign_positions_saver, $campaign_positions, $image_uploader, $request, $response ) {
        parent::__construct( $response );

        $this->campaign_positions_saver = $campaign_positions_saver;
        $this->campaign_positions = $campaign_positions;
        $this->image_uploader = $image_uploader;
        $this->request = $request;
    }

    public function ajax() {
        try {
            $this->try_to_update_advertisement_positions_content( $this->get_posted_data() );
        } catch ( AWPCP_Exception $e ) {
            $this->multiple_errors_response( $e->get_errors() );
        }
    }

    private function get_posted_data() {
        $posted_data = array(
            'campaign_id' => $this->request->post( 'campaign_id' ),
            'advertisement_position' => $this->request->post( 'position' ),
            'content_type' => $this->request->post( 'content_type' ),
            'content' => stripslashes( $this->request->post( 'content' ) ),
            'is_executable' => $this->request->post( 'is_executable' ),
            'image_file' => awpcp_array_data( 'image', '', $_FILES ),
            'image_link' => $this->request->post( 'image_link' ),
        );

        return $posted_data;
    }

    private function try_to_update_advertisement_positions_content( $posted_data ) {
        $this->update_advertisement_positions_content( $posted_data );
        $this->show_campaign_advertisement_position_content_form( $posted_data['campaign_id'], $posted_data['advertisement_position'] );
    }

    private function update_advertisement_positions_content( $posted_data ) {
        if ( ! empty( $posted_data['image_file'] ) ) {
            $image_path = $this->image_uploader->upload_file(
                $posted_data['image_file'],
                $posted_data['campaign_id'],
                $posted_data['advertisement_position']
            );
        } else {
            $image_path = '';
        }

        $this->campaign_positions_saver->update_campaign_advertisment_position(
            $posted_data['campaign_id'],
            $posted_data['advertisement_position'],
            array(
                'content_type' => $posted_data['content_type'],
                'content' => $posted_data['content'],
                'is_executable' => $posted_data['is_executable'],
                'image_path' => $image_path,
                'image_link' => $posted_data['image_link'],
            )
        );
    }

    private function show_campaign_advertisement_position_content_form( $campaign_id, $advertisement_position ) {
        $campaign_data = array( 'id' => $campaign_id );
        $campaign_position = $this->campaign_positions->get( $campaign_id, $advertisement_position );

        ob_start();
        include( AWPCP_CAMPAIGN_MANAGER_MODULE_DIR . '/templates/admin/campaign-advertisement-position-content-form.tpl.php' );
        $html = ob_get_contents();
        ob_end_clean();

        $this->success( array( 'html' => $html ) );
    }
}

}
