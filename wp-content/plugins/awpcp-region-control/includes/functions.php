<?php

/**
 * @since 3.2.0
 */
function awpcp_get_manage_regions_url() {
    return user_trailingslashit( add_query_arg( 'page', 'Configure4', admin_url( 'admin.php' ) ) );
}

/**
 * @since 3.2.0
 */
function awpcp_get_regenerate_sidelist_url() {
    $manage_regions = rtrim( awpcp_get_manage_regions_url(), '/' );
    return add_query_arg( 'action', 'regenerate-regions-sidelist', $manage_regions );
}

/**
 * @since 3.3.0
 */
function awpcp_get_set_location_url() {
    if ( get_option( 'permalink_structure' ) ) {
        $url = awpcp_get_url_with_page_permastruct( '/awpcp/regions/set-location' );
    } else {
        $url = add_query_arg( array(
            'awpcpx' => true,
            'awpcp-module' => 'regions',
            'awpcp-action' => 'set-location'
        ), home_url( '/' ) );
    }

    return $url;
}

/**
 * @since 3.2.0
 */
function awpcp_region_control_render_sidelist() {
    $builder = awpcp_regions_sidelist_builder();

    try {
        $sidelist = $builder->build();

        $active_region = awpcp_region_control_module()->get_active_region();
        if ( $active_region && isset( $active_region->region_id ) ) {
            $active_region_id = $active_region->region_id;
        } else {
            $active_region_id = 0;
        }

        awpcp_enqueue_main_script();

        $container = '<div class="awpcp-regions-sidelist" data-url="%s" data-active-region="%d"></div>';
        $output = sprintf( $container, awpcp_file_cache()->url( 'regions-sidelist' ), $active_region_id );
    } catch ( AWPCP_Exception $e ) {
        $output = '';
    }

    return $output;
}

