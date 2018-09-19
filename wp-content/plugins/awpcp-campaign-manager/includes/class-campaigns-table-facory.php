<?php

function awpcp_campaigns_table_factory() {
    return new AWPCP_CampaignsTableFactory( awpcp_users_collection(), awpcp_admin_page_links_builder(), awpcp_request() );
}

class AWPCP_CampaignsTableFactory {

    private $users;
    private $links_builder;
    private $request;

    public function __construct( $users, $links_builder, $request ) {
        $this->users = $users;
        $this->links_builder = $links_builder;
        $this->request = $request;
    }

    public function create_table( $page ) {
        if ( ! class_exists( 'AWPCP_CampaignsTable' ) ) {
            throw new AWPCP_Exception( 'AWPCP_CampaignsTable class is not defined.' );
        }

        return new AWPCP_CampaignsTable( $page, $this->users, $this->links_builder, $this->request );
    }
}
