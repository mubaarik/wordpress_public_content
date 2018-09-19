<?php

function awpcp_manage_campaigns_admin_page() {
    return new AWPCP_ManageCampaignsAdminPage( awpcp_campaigns_collection(), awpcp_campaigns_table_factory() );
}

class AWPCP_ManageCampaignsAdminPage extends AWPCP_AdminPageWithTable {

    private $campaigns;
    private $table_factory;

    public function __construct( $campaigns, $table_factory ) {
        parent::__construct(
            'awpcp-manage-campaigns',
            awpcp_admin_page_title( __( 'Manage Campaigns', 'awpcp-campaign-manager' ) ),
            __( 'Campaigns', 'awpcp-campaign-manager' )
        );

        $this->campaigns = $campaigns;
        $this->table_factory = $table_factory;
    }

    public function show_sidebar() {
        return false;
    }

    public function on_load() {
        wp_enqueue_style( 'awpcp-campaign-manager-admin' );
        wp_enqueue_script( 'awpcp-campaign-manager-admin' );
    }

    public function dispatch() {
        $table = $this->table_factory->create_table( $this );

        $table->set_items( $this->campaigns->all() );
        $table->prepare_items();

        $template = AWPCP_CAMPAIGN_MANAGER_MODULE_DIR . '/templates/admin/manage-campaigns-admin-page.tpl.php';
        $params = array( 'table' => $table );

        echo $this->render( $template, $params );
    }
}
