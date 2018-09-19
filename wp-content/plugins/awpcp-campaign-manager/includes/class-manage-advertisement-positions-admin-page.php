<?php

function awpcp_manage_advertisement_positions_admin_page() {
    return new AWPCP_ManageAdvertisementPositionsAdminPage(
        awpcp_advertisement_positions_generator(),
        awpcp()->settings,
        awpcp_request()
    );
}

class AWPCP_ManageAdvertisementPositionsAdminPage extends AWPCP_AdminPage {

    private $positions_generator;
    private $settings;
    private $request;

    public function __construct( $positions_generator, $settings, $request ) {
        parent::__construct(
            'awpcp-manage-advertisement-positions',
            awpcp_admin_page_title( __( 'Manage Advertisement Positions', 'awpcp-campaign-manager' ) ),
            _x( 'Ad. Positions', 'Advertisement Positions menu', 'awpcp-campaign-manager' )
        );

        $this->positions_generator = $positions_generator;
        $this->settings = $settings;
        $this->request = $request;
    }

    public function show_sidebar() {
        return false;
    }

    public function on_load() {
        wp_enqueue_style( 'awpcp-campaign-manager-admin' );
    }

    public function dispatch() {
        if ( $this->request->method() == 'POST' ) {
            echo $this->try_to_save_advertisement_positions_information();
        } else {
            echo $this->show_advertisement_positions_form();
        }
    }

    private function try_to_save_advertisement_positions_information() {
        foreach ( $this->request->post( 'advertisement-positions' ) as $slug => $dimensions ) {
            $this->try_to_save_advertisement_position( $slug, $dimensions );
        }

        return $this->show_advertisement_positions_form();
    }

    private function try_to_save_advertisement_position( $slug, $dimensions ) {
        $this->settings->update_option( "advertisement-position-$slug-width", absint( $dimensions['width'] ) );
        $this->settings->update_option( "advertisement-position-$slug-height", absint( $dimensions['height'] ) );
    }

    private function show_advertisement_positions_form() {
        $template = AWPCP_CAMPAIGN_MANAGER_MODULE_DIR . '/templates/admin/manage-advertisement-positions-admin-page.tpl.php';

        $params = array(
            'page_url' => $this->url(),
            'advertisement_positions' => array_merge(
                $this->positions_generator->generate_default_positions(),
                array(
                    'middle' => __( 'Middle', 'advertisement position name', 'awpcp-campaign-manager' )
                )
            ),
            'advertisement_positions_descriptions' => array(
                'top' => __( 'The advertisements will be shown before the listings in the results page.', 'awpcp-campaign-manager' ),
                'bottom' => __( 'The advertisements will be shown after the listings in the results page.', 'awpcp-campaign-manager' ),
                'footer' => __( 'The advertisements will be shown in the widgets that are configured to show advertisements from the Footer position.', 'awpcp-campaign-manager' ),
                'sidebar-one' => __( 'The advertisements will be shown in the widgets that are configured to show advertisements from the Sidebar One position.', 'awpcp-campaign-manager' ),
                'sidebar-two' => __( 'The advertisements will be shown in the widgets that are configured to show advertisements from the Sidebar Two position.', 'awpcp-campaign-manager' ),
                'middle' => __( 'The advertisements will be shown after every five listings in the results page.', 'awpcp-campaign-manager' ),
            ),
            'advertisement_positions_dimensions' => array(
                'top' => array(
                    'width' => $this->settings->get_option( "advertisement-position-top-width" ),
                    'height' => $this->settings->get_option( "advertisement-position-top-height" ),
                ),
                'bottom' => array(
                    'width' => $this->settings->get_option( "advertisement-position-bottom-width" ),
                    'height' => $this->settings->get_option( "advertisement-position-bottom-height" ),
                ),
                'footer' => array(
                    'width' => $this->settings->get_option( "advertisement-position-footer-width" ),
                    'height' => $this->settings->get_option( "advertisement-position-footer-height" ),
                ),
                'sidebar-one' => array(
                    'width' => $this->settings->get_option( "advertisement-position-sidebar-one-width" ),
                    'height' => $this->settings->get_option( "advertisement-position-sidebar-one-height" ),
                ),
                'sidebar-two' => array(
                    'width' => $this->settings->get_option( "advertisement-position-sidebar-two-width" ),
                    'height' => $this->settings->get_option( "advertisement-position-sidebar-two-height" ),
                ),
                'middle' => array(
                    'width' => $this->settings->get_option( "advertisement-position-middle-width" ),
                    'height' => $this->settings->get_option( "advertisement-position-middle-height" ),
                ),
            ),
        );

        return $this->render( $template, $params );
    }
}
