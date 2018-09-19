<?php

function awpcp_campaign_manager_settings() {
    return new AWPCP_CampaignManagerSettings();
}

class AWPCP_CampaignManagerSettings {

    public function register_settings( $settings ) {
        $settings->add_setting( 'private:campaign-manager', 'advertisement-position-top-width', '', 'textfield', 605, '' );
        $settings->add_setting( 'private:campaign-manager', 'advertisement-position-top-height', '', 'textfield', 150, '' );

        $settings->add_setting( 'private:campaign-manager', 'advertisement-position-bottom-width', '', 'textfield', 605, '' );
        $settings->add_setting( 'private:campaign-manager', 'advertisement-position-bottom-height', '', 'textfield', 150, '' );

        $settings->add_setting( 'private:campaign-manager', 'advertisement-position-footer-width', '', 'textfield', 605, '' );
        $settings->add_setting( 'private:campaign-manager', 'advertisement-position-footer-height', '', 'textfield', 150, '' );

        $settings->add_setting( 'private:campaign-manager', 'advertisement-position-sidebar-one-width', '', 'textfield', 250, '' );
        $settings->add_setting( 'private:campaign-manager', 'advertisement-position-sidebar-one-height', '', 'textfield', 150, '' );

        $settings->add_setting( 'private:campaign-manager', 'advertisement-position-sidebar-two-width', '', 'textfield', 250, '' );
        $settings->add_setting( 'private:campaign-manager', 'advertisement-position-sidebar-two-height', '', 'textfield', 150, '' );

        $settings->add_setting( 'private:campaign-manager', 'advertisement-position-middle-width', '', 'textfield', 605, '' );
        $settings->add_setting( 'private:campaign-manager', 'advertisement-position-middle-height', '', 'textfield', 150, '' );
    }
}
