<?php

/**
 * @since 3.2.0
 */
function awpcp_regions_sidelist_handler() {
    return new AWPCP_RegionsSidelistHandler( awpcp_request(), awpcp_regions_sidelist_builder() );
}

/**
 * @since 3.2.0
 */
class AWPCP_RegionsSidelistHandler {

    /**
     * @since 3.2.0
     */
    public function __construct( $request, $sidelist_builder ) {
        $this->request = $request;
        $this->sidelist_builder = $sidelist_builder;
    }

    /**
     * @since 3.2.0
     */
    public function dispatch() {
        if ( get_awpcp_option( 'showregionssidelist' ) && ! $this->is_regenerate_regions_sidelist_page() ) {
            $this->check_if_regions_sidelist_is_ready();
        }
    }

    /**
     * @since 3.2.0
     */
    private function is_regenerate_regions_sidelist_page() {
        return $this->request->param( 'action' ) == 'regenerate-regions-sidelist';
    }

    /**
     * @since 3.2.0
     */
    private function check_if_regions_sidelist_is_ready() {
        try {
            $sidelist = $this->sidelist_builder->build( false );
        } catch ( AWPCP_Exception $e ) {
            if ( awpcp_current_user_is_admin() ) {
                add_action( 'admin_notices', array( $this, 'regions_sidelist_is_not_ready_notice' ) );
            }
        }
    }

    /**
     * @since 3.2.0
     */
    public function regions_sidelist_is_not_ready_notice() {
        $url = awpcp_get_regenerate_sidelist_url();
        $link = sprintf( '<a href="%s">%s</a>', $url, __( 'Regenerate Regions Sidelist', 'awpcp-region-control' ) );

        $message = __( 'The Regions Sidelist needs to be rebuilt because the Regions data has changed, the cache expired or it has never been build before. Please go to the %s page.', 'awpcp-region-control' );
        $message = sprintf( $message, $link );

        echo awpcp_print_message( $message );
    }
}
