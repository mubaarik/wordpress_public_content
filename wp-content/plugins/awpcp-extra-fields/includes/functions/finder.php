<?php

function awpcp_get_extra_field($id) {
    $fields = awpcp_get_extra_fields('WHERE field_id = ' . intval($id));
    if (!empty($fields)) {
        return array_shift($fields);
    }
    return null;
}

function awpcp_get_extra_field_by_slug( $slug ) {
    global $wpdb;

    $fields = awpcp_get_extra_fields( $wpdb->prepare( 'WHERE field_name = %s', $slug ) );

    return array_shift( $fields );
}

/**
 * Return an array with all registered Extra Fields
 */
function awpcp_get_extra_fields($where='', $order='') {
    global $wpdb;

    $where = empty($where) ? "WHERE field_name <> ''" : $where;
    $order = empty($order) ? 'ORDER BY weight ASC, field_id ASC' : $order;

    $query = "SELECT * FROM " . AWPCP_TABLE_EXTRA_FIELDS . " $where $order";
    $results = $wpdb->get_results($query);

    // field options and cantegories may be serialized
    $extra_fields = array();
    foreach ( $results as $field ) {
        $field->field_options = array_filter( (array) maybe_unserialize( $field->field_options ), 'strlen' );
        $field->field_category = array_filter( (array) maybe_unserialize( $field->field_category ), 'strlen' );

        $extra_fields[ "awpcp-{$field->field_name}" ] = $field;
    }

    $sorted_fields = array();
    foreach ( awpcp_form_fields()->get_fields_order() as $field_slug ) {
        if ( isset( $extra_fields[ $field_slug ] ) ) {
            $sorted_fields[ $field_slug ] = $extra_fields[ $field_slug ];
            unset( $extra_fields[ $field_slug ] );
        }
    }

    return array_merge( $sorted_fields, $extra_fields );
}

function awpcp_get_extra_fields_by_category($category, $args=array()) {
    $condition = "( field_category LIKE '%%\"%d\"%%' OR field_category = 'a:0:{}' OR field_category = '' )";

    $conditions = awpcp_get_extra_fields_conditions( $args );
    $conditions[] = sprintf( $condition, absint( $category ) );

    return awpcp_get_extra_fields( 'WHERE ' . join( ' AND ', $conditions ) );
}

function awpcp_get_extra_fields_conditions($args=array()) {
    $args = wp_parse_args( $args, array(
        'hide_private' => false,
        'context' => 'listings',
    ) );

    $conditions = array();

    if ( $args['hide_private'] && is_user_logged_in() ) {
        $conditions[] = "field_privacy != 'private'";
    } else if ( $args['hide_private'] ) {
        $conditions[] = "field_privacy != 'private'";
        $conditions[] = "field_privacy != 'restricted'";
    }

    if ( $args['context'] == 'single' ) {
        $conditions[] = "( show_on_listings = '2' OR show_on_listings = '3' )";
    } else if ( $args['context'] == 'listings' ) {
        $conditions[] = "( show_on_listings = '1' OR show_on_listings = '3' )";
    } else if ( $args['context'] == 'search' ) {
        if ( is_user_logged_in() ) {
            $conditions[] = "( field_privacy = 'public' OR field_privacy = 'restricted' )";
        } else {
            $conditions[] = "field_privacy = 'public'";
        }
        $conditions[] = "(nosearch IS NULL OR nosearch = 0)";
    } else if ( $args['context'] == 'details' ) {
        // pass
    }

    return $conditions;
}

/**
 * @since 1.0.3
 */
function count_extra_fields() {
    global $wpdb;
    $count = $wpdb->get_var("SELECT count(*) FROM " . AWPCP_TABLE_EXTRA_FIELDS . " WHERE field_name <> ''");
    return $count ? $count : 0;
}
