<?php

if ( class_exists( 'AWPCP_AjaxHandler' ) ) {

function awpcp_load_campaigns_ajax_handler() {
    return new AWPCP_LoadCampaignsAjaxHandler(
        awpcp_advertisement_content_generator(),
        awpcp_request(),
        awpcp_ajax_response()
    );
}

class AWPCP_LoadCampaignsAjaxHandler extends AWPCP_AjaxHandler {

    private $content_generator;
    private $request;

    public function __construct( $content_generator, $request, $response ) {
        parent::__construct( $response );

        $this->content_generator = $content_generator;
        $this->request = $request;
    }

    public function ajax() {
        $campaigns = $this->request->param( 'campaigns', array() );
        $advertisements = array();

        foreach ( $campaigns as $campaign ) {
            $advertisements[ $campaign['position'] ] = $this->generate_content( $campaign );
        }

        return $this->success( array( 'advertisements' => $advertisements ) );
    }

    private function generate_content( $campaign ) {
        $category = $campaign['category'];
        $page = $campaign['page'];
        $position = $campaign['position'];

        return $this->content_generator->generate_advertisement_content( $category, $page, $position );
    }
}

}
