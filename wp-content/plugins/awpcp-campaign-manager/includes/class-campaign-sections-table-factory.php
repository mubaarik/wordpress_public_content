<?php

function awpcp_campaign_sections_table_factory() {
    return new AWPCP_CampaignSectionsTableFactory( awpcp_admin_page_links_builder() );
}

class AWPCP_CampaignSectionsTableFactory {

    private $links_builder;

    public function __construct( $links_builder ) {
        $this->links_builder = $links_builder;
    }

    public function create_table() {
        if ( ! class_exists( 'AWPCP_CampaignSectionsTable' ) ) {
            throw new AWPCP_Exception( 'AWPCP_CampaignSectionsTable class is not defined.' );
        }

        return new AWPCP_CampaignSectionsTable( $this->links_builder );
    }
}
