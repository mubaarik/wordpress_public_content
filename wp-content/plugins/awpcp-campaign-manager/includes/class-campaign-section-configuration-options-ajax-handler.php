<?php

if ( class_exists( 'AWPCP_AjaxHandler' ) ) {

function awpcp_campaign_section_configuration_options_ajax_handler() {
    return new AWPCP_CampaignSectionConfigurationOptionsAjaxHandler(
        awpcp_listings_collection(),
        awpcp_advertisement_positions_generator(),
        awpcp()->settings,
        awpcp_request(),
        awpcp_ajax_response()
    );
}

class AWPCP_CampaignSectionConfigurationOptionsAjaxHandler extends AWPCP_AjaxHandler {

    private $listings;
    private $positions_generator;
    private $settings;
    private $request;

    public function __construct( $listings, $positions_generator, $settings, $request, $response ) {
        parent::__construct( $response );

        $this->listings = $listings;
        $this->positions_generator = $positions_generator;
        $this->settings = $settings;
        $this->request = $request;
    }

    public function ajax() {
        $listings_count = $this->get_listings_count_in_selected_category();
        $pages_count = $this->calculate_pages_count( $listings_count );

        $positions = $this->positions_generator->generate_advertisement_positions_for_listings_count( $listings_count );

        return $this->success( array( 'count' => $pages_count, 'positions' => $positions ) );
    }

    private function get_listings_count_in_selected_category() {
        $category = $this->request->param( 'category' );
        return $this->listings->count_enabled_listings_in_category( $category );
    }

    private function calculate_pages_count( $listings_count ) {
        $results_per_page = $this->settings->get_option( 'adresultsperpage' );
        $pages_count = ceil( $listings_count / $results_per_page );
        return $pages_count;
    }
}

}
