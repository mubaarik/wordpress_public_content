<?php

function awpcp_mark_as_sold_settings() {
    return new AWPCP_MarkAsSoldSettings();
}

class AWPCP_MarkAsSoldSettings {

    public function register_settings( $settings ) {
        $settings->add_setting(
            'listings-settings:moderation',
            'remove-sold-items',
            __( 'Remove listings marked as sold after a configurable number of days', 'awpcp-mark-as-sold' ),
            'checkbox',
            0,
            __( 'Enable this setting to have the plugin remove listings marked as sold after the number of days specified in the next setting.', 'awpcp-mark-as-sold' )
        );

        $settings->add_setting(
            'listings-settings:moderation',
            'remove-sold-items-after-n-days',
            __( 'Number of days before a listing marked as sold is removed from the system', 'awpcp-mark-as-sold' ),
            'textfield',
            30,
            __( 'An integer. If the plugin is configured to remove listings marked as sold, those listings will be removed from the system if they remain marked as sold for that number of days or more.', 'awpcp-mark-as-sold' )
        );
    }
}
