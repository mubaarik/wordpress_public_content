<?php

if ( defined( 'AWPCP_DIR' ) && file_exists( AWPCP_DIR . '/includes/helpers/page.php' ) ) {
    require_once( AWPCP_DIR . '/includes/helpers/page.php' );
}

if ( class_exists( 'AWPCP_AdminPage' ) ) {

/**
 * @since 3.2.3
 */
class AWPCP_CalculateRegionsListingsCountPage extends AWPCP_AdminPage {

    /**
     * @since 3.2.3
     */
    public function __construct() {
        parent::__construct( 'awpcp-admin-calculate-regions-listings-count', __( 'Calculate Regions Listings Count', 'awpcp-region-control' ), null );
    }

    /**
     * @since 3.2.3
     */
    public function dispatch() {
        wp_enqueue_script( 'awpcp-admin-manual-upgrade' );

        $tasks = array(
            array(
                'name' => __( 'Calculate Regions Listings Count', 'awpcp-region-control' ),
                'action' => 'awpcp-calculate-regions-listings-count',
            ),
        );

        $messages = array(
            'introduction' => __( 'Click the button below if you want to re-calculate the number of Ads posted in each region. Click the button to start the process.', 'awpcp-region-control' ),
            'success' => sprintf( __('Congratulations. We finished counting all Ads posted in existing Regions. Go back to %s section.', 'awpcp-region-control' ), sprintf( '<a href="%s">%s</a>', awpcp_get_manage_regions_url(), __( 'Manage Regions', 'awpcp-region-control' ) ) ),
            'button' => __( 'Count Listings in Regions', 'awpcp-region-control' ),
        );

        $tasks = new AWPCP_AsynchronousTasksComponent( $tasks, $messages );
        $content = $tasks->render();

        echo $this->render( 'content', $content );
    }
}

}
