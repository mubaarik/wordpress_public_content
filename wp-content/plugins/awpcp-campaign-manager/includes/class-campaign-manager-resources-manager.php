<?php

function awpcp_campaign_manager_resources_manager() {
    return new AWPCP_CampaignManagerResourcesManager();
}

class AWPCP_CampaignManagerResourcesManager {

    private $version;
    private $base_url;

    public function set_version( $version ) {
        $this->version = $version;
    }

    public function set_base_url( $base_url ) {
        $this->base_url = $base_url;
    }

    public function register_scripts_and_styles() {
        $this->very_version_and_base_url_are_set();
        $this->register_scripts();
        $this->register_styles();
    }

    private function very_version_and_base_url_are_set() {
        if ( empty( $this->version ) ) {
            throw new LogicException( "Version hasn't been set. Call set_version() before trying to register scripts or styles." );
        }

        if ( empty( $this->base_url ) ) {
            throw new LogicException( "Base url hasn't been set. Call set_base_url() before trying to register scripts or styles." );
        }
    }

    private function register_scripts() {
        wp_register_script( 'awpcp-campaign-manager-frontend', $this->base_url . '/resources/js/campaign-manager-frontend.min.js', array( 'awpcp' ), $this->version, true );
        wp_register_script( 'awpcp-campaign-manager-admin', $this->base_url . '/resources/js/campaign-manager-admin.min.js', array( 'awpcp', 'awpcp-table-ajax-admin', 'jquery-ui-datepicker', 'jquery-ui-button' ), $this->version, true );
    }

    private function register_styles() {
        wp_register_style( 'awpcp-campaign-manager-admin', $this->base_url . '/resources/css/admin.css', array( 'awpcp-admin-style', 'awpcp-jquery-ui' ), $this->version );
    }
}
