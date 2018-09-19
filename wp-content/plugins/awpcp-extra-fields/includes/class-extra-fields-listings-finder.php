<?php

function awpcp_extra_fields_listings_finder() {
    return new AWPCP_ExtraFieldsListingsFinder(
        awpcp_extra_fields_collection(),
        $GLOBALS['wpdb']
    );
}

class AWPCP_ExtraFieldsListingsFinder {

    private $separated_fields;

    private $extra_fields;
    private $db;

    public function __construct( $extra_fields, $db ) {
        $this->extra_fields = $extra_fields;
        $this->db = $db;
    }

    public function filter_keyword_conditions( $conditions, $query ) {
        if ( empty( $query['keyword'] ) ) {
            return $conditions;
        }

        $fields = $this->get_fields_not_in_query( $query );

        foreach ( $fields as $field ) {
            if ( strlen( $query['keyword'] ) <= 3 ) {
                $conditions[] = $this->db->prepare( " `{$field->field_name}` = '%s' ", $query['keyword'] );
            } else {
                $keyword = '%' . $this->db->esc_like( $query['keyword'] ) . '%';
                $conditions[] = $this->db->prepare( " `{$field->field_name}` LIKE %s ", $keyword );
            }
        }

        return $conditions;
    }

    private function get_fields_not_in_query( $query ) {
        $separated_fields = $this->get_separated_fields( $query );
        return $separated_fields['fields-not-in-query'];
    }

    /**
     * TODO: test fails because the static cache is initialized with an empty array
     * beforea ny fields are defined. That could cause bugs in the real world.
     */
    private function get_separated_fields( $query ) {
        if ( is_null( $this->separated_fields ) ) {
            $this->separated_fields = array(
                'fields-in-query' => array(),
                'fields-not-in-query' => array(),
            );

            foreach ( $this->get_fields() as $field ) {
                if ( isset( $query[ "awpcp-{$field->field_name}" ] ) && ! empty( $query[ "awpcp-{$field->field_name}" ] ) ) {
                    $this->separated_fields['fields-in-query'][] = $field;
                } else {
                    $this->separated_fields['fields-not-in-query'][] = $field;
                }
            }
        }

        return $this->separated_fields;
    }

    private function get_fields() {
        $conditions = awpcp_get_extra_fields_conditions( array(
            'hide_private' => true,
            'context' => 'search'
        ) );

        return $this->extra_fields->find_fields_with_conditions( $conditions );
    }

    public function filter_conditions( $conditions, $query ) {
        foreach ( $this->get_fields_in_query( $query ) as $field ) {
            $value = awpcp_array_data( "awpcp-{$field->field_name}", '', $query );
            $value = preg_replace( '/\*+$/', '', preg_replace( '/^\*+/', '', $value ) );

            if ( is_array( $value ) && ( isset( $value['min'] ) || isset( $value['max'] ) ) ) {
                $field_conditions = $this->create_conditions_for_range_field(
                    $field, $value, 'min', 'max',
                    '`%1$s` >= %2$f',
                    '`%1$s` <= %2$f'
                );

                $conditions = array_merge( $conditions, $field_conditions );
            } else if ( is_array( $value ) && ( isset( $value['from_date'] ) || isset( $value['to_date'] ) ) ) {
                $field_conditions = $this->create_conditions_for_range_field(
                    $field, $value, 'from_date', 'to_date',
                    '`%1$s` >= \'%2$s\'',
                    '`%1$s` <= \'%2$s\''
                );

                $conditions = array_merge( $conditions, $field_conditions );
            } else if ( is_array( $value ) )  {
                $field_conditions = array();

                foreach ( $value as $key => $val ) {
                    $field_conditions[] = $this->db->prepare( "`{$field->field_name}` LIKE '%%%s%%'", trim( $val ) );
                }

                if ( ! empty( $field_conditions ) ) {
                    $conditions[] = '(' . implode(' OR ', $field_conditions) . ' )';
                }
            } else if ( strlen( $value ) > 0 && strlen( $value ) <=4 ) {
                $conditions[] = $this->db->prepare( "`{$field->field_name}` = '%s'", $value );
            } else if ( '' != $value ) {
                // MATCH AGAINST requires fulltext indexes, we don't have that for
                // Extra Fields columns in Ads table. Switching to LIKE until
                // Extra Fields information is stored in a better place: a
                // *meta table for Ads.

                // // A phrase that is enclosed within double quote (“"”)
                // // characters matches only rows that contain the phrase
                // // literally, as it was typed. The full-text engine splits the
                // // phrase into words and performs a search in the FULLTEXT
                // // index for the words. Nonword characters need not be matched
                // // exactly: Phrase searching requires only that matches contain
                // // exactly the same words as the phrase and in the same order.
                // // For example, "test phrase" matches "test, phrase".

                // // If the phrase contains no words that are in the index, the
                // // result is empty. For example, if all words are either
                // // stopwords or shorter than the minimum length of indexed
                // // words, the result is empty.
                // $sql = ' AND MATCH (' . `{$field->field_name}` . ') AGAINST (\'"%s"\' IN BOOLEAN MODE) ';

                // Using LIKE since it does not requires index, although not having them
                // can cause performance issues if the table has too many rows (>10k)
                $conditions[] = $this->db->prepare( "`{$field->field_name}` LIKE '%%%s%%'", $value );
            }
        }

        return $conditions;
    }

    private function create_conditions_for_range_field( $field, $value, $left_key, $right_key, $left_condition, $right_condition ) {
        $field_conditions = array();

        if ( strlen( $value[ $left_key ] ) > 0 ) {
            $field_conditions[] = sprintf( $left_condition, esc_sql( $field->field_name ), esc_sql( $value[ $left_key ] ) );
        }
        if ( strlen( $value[ $right_key ] ) > 0 ) {
            $field_conditions[] = sprintf( $right_condition, esc_sql( $field->field_name ), esc_sql( $value[ $right_key ] ) );
        }

        if ( ! empty( $field_conditions ) ) {
            return array( '(' . implode( ' AND ', $field_conditions ) . ' )' );
        } else {
            return array();
        }
    }

    private function get_fields_in_query( $query ) {
        $separated_fields = $this->get_separated_fields( $query );
        return $separated_fields['fields-in-query'];
    }
}
