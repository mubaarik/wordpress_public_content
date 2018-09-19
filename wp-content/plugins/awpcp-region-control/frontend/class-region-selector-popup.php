<?php

function awpcp_region_selector_popup() {
    return new AWPCP_RegionSelectorPopup( awpcp_region_control_module() );
}

class AWPCP_RegionSelectorPopup {

    private $location_service;

    public function __construct( $location_service ) {
        $this->location_service = $location_service;
    }

    public function render() {
        if ( is_user_logged_in() ) {
            return $this->render_popup();
        } else {
            return '';
        }
    }

    private function render_popup() {
        $this->enqueue_scripts_and_styles();

        $current_location = $this->get_current_location_most_specific_region_name();

        $selected_regions = awpcp_prepare_active_region_for_region_selector();
        $region_selector = awpcp_multiple_region_selector( $selected_regions, array() );

        $form_url = awpcp_get_set_location_url();

        ob_start();
        include( AWPCP_REGION_CONTROL_MODULE_DIR . '/templates/frontend/region-selector-popup.tpl.php' );
        $content = ob_get_contents();
        ob_end_clean();

        return $content;
    }

    private function enqueue_scripts_and_styles() {
        wp_enqueue_script( 'awpcp-region-control' );
        wp_enqueue_script( 'awpcp-multiple-region-selector' );

        wp_enqueue_style('awpcp-region-control');
    }

    private function get_current_location_most_specific_region_name() {
        $current_location_names = $this->location_service->get_current_location_names();

        if ( empty( $current_location_names ) ) {
            $most_specific_region = __( 'No location selected', 'awpcp-region-control' );
        } else {
            $most_specific_region = array_pop( $current_location_names );
        }

        return $most_specific_region;
    }
}
