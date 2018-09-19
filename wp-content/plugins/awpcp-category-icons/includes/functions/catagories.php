<?php

/**
 * Returns the filename of the image used as icon for the given category.
 *
 * @since 3.2.5
 */
function awpcp_get_category_icon( $category ) {
    if ( isset( $category->icon ) ) {
        return $category->icon;
    } else {
        return '';
    }
}

/**
 * @since 3.2.1
 */
function awpcp_category_icon_url( $category_icon ) {
    if ( strpos( $category_icon, 'custom:' ) === 0 ) {
        $baseurl = awpcp()->settings->get_runtime_option( 'awpcp-uploads-url' ) . '/custom-icons/';
        $filename = str_replace( 'custom:', '', $category_icon );
    // } else if ( strpos( $category_icon, 'standard:' ) == 0 ) {
    } else {
        $baseurl = AWPCP_CATEGORY_ICONS_MODULE_URL . '/images/caticons/';
        $filename = str_replace( 'standard:', '', $category_icon );
    }

    return $baseurl . $filename;
}
