<?php

if ( class_exists( 'AWPCP_AjaxHandler' ) ) {

function awpcp_add_campaign_section_ajax_handler() {
    return new AWPCP_AddCampaignSectionAjaxHandler(
        awpcp_campaign_section_saver(),
        awpcp_campaign_sections_collection(),
        awpcp_advertisement_positions_generator(),
        awpcp_campaign_sections_table_factory(),
        awpcp_request(),
        awpcp_ajax_response()
    );
}

class AWPCP_AddCampaignSectionAjaxHandler extends AWPCP_AjaxHandler {

    private $campaign_section_saver;
    private $campaign_sections;
    private $positions_generator;
    private $table_factory;
    private $request;

    public function __construct( $campaign_section_saver, $campaign_sections, $positions_generator, $table_factory, $request, $response ) {
        parent::__construct( $response );

        $this->campaign_section_saver = $campaign_section_saver;
        $this->campaign_sections = $campaign_sections;
        $this->positions_generator = $positions_generator;
        $this->table_factory = $table_factory;
        $this->request = $request;
    }

    public function ajax() {
        if ( $this->request->post( 'save' ) ) {
            $this->try_to_save_campaign_section();
        } else {
            $this->show_campaign_section_form();
        }
    }

    private function try_to_save_campaign_section() {
        $campaign_section_data = array(
            'campaign_id' => $this->request->post( 'campaign' ),
            'category_id' => $this->request->post( 'category' ),
            'pages' => $this->parse_campaign_section_pages(),
            'positions' => $this->request->post( 'positions' ),
        );

        try {
            $campaign_section_id = $this->campaign_section_saver->create_campaign_section( $campaign_section_data );
        } catch ( AWPCP_Exception $e ) {
            return $this->multiple_errors_response( $e->get_errors() );
        }

        return $this->show_campaign_section_row( $campaign_section_id );
    }

    private function parse_campaign_section_pages() {
        // allowed characters are digits, dash (-) and comma (,)
        $definition = sanitize_text_field( $this->request->post( 'pages' ) );
        $definition = preg_replace( '/[^0-9,-]/', '', $definition );

        $pages = array();

        foreach ( explode( ',', $definition ) as $part ) {
            if ( preg_match( '/^\d+$/', $part ) ) {
                $pages[] = absint( $part );
            } else if ( preg_match( '/^\d+-\d+$/', $part ) ) {
                list( $start, $end ) = explode( '-', $part );
                $pages = array_merge( $pages, range( $start, $end ) );
            }
        }

        return $pages;
    }

    private function show_campaign_section_row( $campaign_section_id ) {
        $campaign_section = $this->campaign_sections->get( $campaign_section_id );
        $campaign_sections_table = $this->table_factory->create_table();

        ob_start();
        $campaign_sections_table->single_row( $campaign_section );
        $campaign_section_row = ob_get_contents();
        ob_end_clean();

        return $this->success( array( 'html' => $campaign_section_row ) );
    }

    private function show_campaign_section_form() {
        $columns = $this->request->post( 'columns' );
        extract( $params = array(
            'columns' => $columns,
            'hidden_fields' => array(
                'campaign' => $this->request->post( 'campaign' ),
                'action' => 'awpcp-add-campaign-section',
                'columns' => $columns,
            ),
            'campaign_section_data' => array(
                'category' => '',
                'pages' => '',
                'positions' => array(),
            ),
            'advertisement_positions' => $this->positions_generator->generate_default_positions(),
        ) );

        ob_start();
        include( AWPCP_CAMPAIGN_MANAGER_MODULE_DIR . '/templates/admin/add-campaign-section-form.tpl.php' );
        $form = ob_get_contents();
        ob_end_clean();

        $this->success( array( 'html' => $form ) );
    }
}

}
