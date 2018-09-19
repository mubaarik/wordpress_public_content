<?php

if ( defined( 'AWPCP_DIR' ) && file_exists( AWPCP_DIR . '/includes/helpers/page.php' ) ) {
    require_once( AWPCP_DIR . '/includes/helpers/page.php' );
}

if ( class_exists( 'AWPCP_AdminPage' ) ) {

/**
 * @since 3.2.0
 */
class AWPCP_RegenerateRegionsSidelistPage extends AWPCP_AdminPage {

    /**
     * @since 3.2.0
     */
    public function __construct() {
        parent::__construct( 'awpcp-admin-regenerate-regions-sidelist', __( 'Regenerate Regions Sidelist', 'awpcp-region-control' ), null );
    }

    /**
     * @since 3.2.0
     */
    public function dispatch() {
        wp_enqueue_script( 'awpcp-admin-manual-upgrade' );

        $tasks = array(
            array(
                'name' => __( 'Regenerate Regions Sidelist', 'awpcp-region-control' ),
                'action' => 'awpcp-regenerate-regions-sidelist'
            ),
        );

        $messages = array(
            'introduction' => __('The Regions Sidelist needs to be rebuilt because the Regions data has changed, the cache expired or it has never been build before. Please press the Regenerate Sidelist button shown below to start the process.', 'awpcp-region-control' ),
            'success' => sprintf( __('Congratulations. The Regions Sidelist was successfully regenerated. Go back to %s section.', 'awpcp-region-control' ), sprintf( '<a href="%s">%s</a>', awpcp_get_manage_regions_url(), __( 'Manage Regions', 'awpcp-region-control' ) ) ),
            'button' => __( 'Regenerate Sidelist', 'awpcp-region-control' ),
        );

        $tasks = new AWPCP_AsynchronousTasksComponent( $tasks, $messages );
        $content = $tasks->render();

        echo $this->render( 'content', $content );
    }
}

}
