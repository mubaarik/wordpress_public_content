<?php

/**
 * @deprecated 3.2.5
 */
function get_category_icon( $category_id ) {
    _deprecated_function( __FUNCTION__, '3.2.5', 'awpcp_get_category_icon' );
    global $wpdb;

    if ( ! awpcp_column_exists( AWPCP_TABLE_CATEGORIES, 'category_icon' ) ) {
        return '';
    }

    $query = "SELECT category_icon FROM " . AWPCP_TABLE_CATEGORIES . " WHERE category_id = %d";
    $category_icon = $wpdb->get_var( $wpdb->prepare( $query, $category_id ) );

    return $category_icon;
}
