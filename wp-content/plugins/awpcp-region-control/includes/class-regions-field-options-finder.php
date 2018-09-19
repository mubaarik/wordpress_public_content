<?php

function awpcp_regions_field_options_finder() {
    return new AWPCP_RegionsFieldOptionsFinder( awpcp_regions_api() );
}

class AWPCP_RegionsFieldOptionsFinder {

    private $regions;

    public function __construct( $regions ) {
        $this->regions = $regions;
    }

    public function get_field_options( $region_type, $selected_parents, $hide_empty_regions, $hide_regions_ad_count ) {
        $region_type_const = $this->regions->get_region_type_const( $region_type );

        try {
            $entries = $this->find_regions_by_parent( $region_type_const, $selected_parents, $hide_empty_regions );
        } catch ( AWPCP_Exception $e ) {
            $entries = $this->regions->find_regions( array( 'type' => $region_type_const, 'hide_empty' => $hide_empty_regions ) );
        }

        return $this->format_regions_entries( $entries, $hide_regions_ad_count );
    }

    private function find_regions_by_parent( $region_type_const, $selected_parents, $hide_empty_regions ) {
        $parent_type_const = $region_type_const;
        $entries = null;

        // attempt to find Regions of $region_type filtering by a parent region
        // if a region of higher level is selected ($parent_id > 0).
        do {
            $parent_type_const = $this->regions->get_parent_region_type( $parent_type_const );
            $parent_type_slug = $this->regions->get_region_type_slug( $parent_type_const );

            $parent_identifier = awpcp_array_data( $parent_type_slug, null, $selected_parents );

            if ( ! empty( $parent_identifier ) ) {
                $entries = $this->regions->find_regions_by_parent( $region_type_const, $parent_type_const, $parent_identifier, $hide_empty_regions );
            }
        } while ( ! is_null( $parent_type_const ) && is_null( $entries ) );

        if ( is_null( $entries ) ) {
            $region_type_slug = $this->regions->get_region_type_slug( $region_type_const );
            $message = sprintf( 'We could not find any regions of type %s based on its parent.', $region_type_slug );
            throw new AWPCP_Exception( $message );
        } else {
            return $entries;
        }
    }

    private function format_regions_entries( $entries, $hide_regions_ad_count ) {
        $name_template = $hide_regions_ad_count ? '%s' : '%s (%d)';

        $options = array();
        foreach ( $entries as $entry ) {
            $options[] = array(
                'id' => $entry->region_name,
                'name' => sprintf( $name_template, $entry->region_name, $entry->count_enabled ),
            );
        }

        return $options;
    }
}
