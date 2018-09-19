<?php

/**
 * @since 3.2.0
 */
function awpcp_regions_sidelist_builder() {
    return new AWPCP_RegionsSidelistBuilder( 1000, awpcp_file_cache(), awpcp_regions_api() );
}

/**
 * @since 3.2.0
 */
class AWPCP_RegionsSidelistBuilder {

    private $items_per_step;

    private $regions_api;
    private $cache;

    /**
     * @since 3.2.0
     */
    public function __construct( $items_per_step, $cache, $regions_api ) {
        $this->items_per_step = $items_per_step;

        $this->cache = $cache;
        $this->regions_api = $regions_api;
    }

    /**
     * @since 3.2.0
     */
    public function build( $render = true ) {
        $this->metadata = $this->get_regions_sidelist_metadata();

        if ( $this->metadata[ 'complete' ] ) {
            $sidelist = $this->regions_sidelist();
        } else if ( $render ) {
            try {
                $sidelist = $this->build_regions_sidelist();
                $this->save_regions_sidelist_metadata();
            } catch ( AWPCP_UnknownTopLevelRegionInSidelistException $e ) {
                $sidelist = $this->empty_regions_sidelist();
                $this->save_regions_sidelist_metadata();
            } catch ( AWPCP_Exception $e ) {
                $this->save_regions_sidelist_metadata();
                throw $e;
            }
        } else {
            if ( is_null( $this->metadata['regions_left'] ) ) {
                $this->metadata['regions_left'] = $this->regions_api->count_sidelisted_regions_with_id_greater_than( $this->metadata['last_region'] );
                $this->save_regions_sidelist_metadata();
            }

            if ( $this->metadata['regions_left'] === 0 ) {
                $sidelist = $this->empty_regions_sidelist();
            } else {
                throw new AWPCP_TooManyRecordsLeftException(
                    'Regions Sidelist is not ready.',
                    $this->metadata['regions_processed'],
                    $this->metadata['regions_left'] );
            }
        }

        return $sidelist;
    }

    /**
     * @since 3.2.0
     */
    private function get_regions_sidelist_metadata() {
        try {
            $metadata = json_decode( $this->cache->get( 'regions-sidelist' ), true );
        } catch ( AWPCP_Exception $e ) {
            $metadata = array();
        }

        return $this->normalize_regions_sidelist_metadata( $metadata );
    }

    /**
     * @since 3.2.8
     */
    private function normalize_regions_sidelist_metadata( $metadata ) {
        $metadata = wp_parse_args( $metadata, array(
            'complete' => false,
            'regions_processed' => null,
            'regions_left' => null,
            'last_region' => 0,
            'top_level_type' => null,
            'html' => '',
            'top_level_items' => array(),
            'children_items' => array(),
        ) );

        if ( ! is_null( $metadata['regions_processed'] ) ) {
            $metadata['regions_processed'] = intval( $metadata['regions_processed'] );
        }

        if ( ! is_null( $metadata['regions_left'] ) ) {
            $metadata['regions_left'] = intval( $metadata['regions_left'] );
        }

        $metadata['last_region'] = intval( $metadata['last_region'] );

        return $metadata;
    }

    /**
     * @since 3.2.0
     */
    private function save_regions_sidelist_metadata() {
        if ( $this->metadata['complete'] ) {
            $this->metadata['top_level_items'] = null;
            unset( $this->metadata['top_level_items'] );
            $this->metadata['children_items'] = null;
            unset( $this->metadata['children_items'] );
        }

        $this->cache->set( 'regions-sidelist', json_encode( $this->metadata ) );
    }

    /**
     * @since 3.2.0
     */
    private function regions_sidelist() {
        return new AWPCP_RegionsSidelist( $this->metadata['html'], $this->metadata['regions_processed'] );
    }

    /**
     * @since 3.2.0
     */
    private function empty_regions_sidelist() {
        return new AWPCP_RegionsSidelist();
    }

    /**
     * @since 3.2.0
     */
    private function build_regions_sidelist() {
        global $wpdb;

        $last_region_id = $this->metadata['last_region'];

        $pending_regions_count = $this->regions_api->count_sidelisted_regions_with_id_greater_than( $last_region_id );
        $regions = $this->regions_api->get_sidelisted_regions_with_id_greather_than( $last_region_id, $this->items_per_step );

        if ( ! empty( $regions ) ) {
            $last_region_id = $this->build_regions_sidelist_items( $regions );
            $regions_left_count = $this->regions_api->count_sidelisted_regions_with_id_greater_than( $last_region_id );
        } else {
            $regions_left_count = $pending_regions_count;
        }

        $this->metadata['regions_processed'] += $pending_regions_count - $regions_left_count;
        $this->metadata['regions_left'] = $regions_left_count;
        $this->metadata['last_region'] = $last_region_id;

        if ( $this->metadata['regions_left'] > 0 ) {
            $message = sprintf( 'Regions Sidelist is not ready. There are %d left to be processed.', $this->metadata['regions_left'] );
            throw new AWPCP_TooManyRecordsLeftException( $message, $this->metadata['regions_processed'], $this->metadata['regions_left'] );;
        }

        if ( ! empty( $this->metadata['regions_processed'] ) ) {
            $this->metadata['html'] = $this->render_regions_sidelist();
        }

        $this->metadata['complete'] = true;

        return $this->regions_sidelist();
    }

    /**
     * @since 3.2.8
     */
    private function build_regions_sidelist_items( $regions ) {
        $top_level_type = $this->get_top_level_type( $this->metadata );
        $last_region_id = null;

        foreach ( $regions as $region ) {
            if ( $region->region_type == $top_level_type ) {
                $this->metadata['top_level_items'][ $region->region_id ] = $this->render_item( $region );
            } else {
                $this->metadata['children_items'][ $region->region_parent ][ $region->region_id ] = $this->render_item( $region );
            }

            $last_region_id = $region->region_id;
        }

        return $last_region_id;
    }

    /**
     * @since 3.2.0
     */
    private function get_top_level_type() {
        if ( is_null( $this->metadata['top_level_type'] ) ) {
            $this->metadata['top_level_type'] = $this->find_top_level_type();
        }

        return $this->metadata['top_level_type'];
    }

    /**
     * @since 3.2.0
     */
    private function find_top_level_type() {
        global $wpdb;

        $top_level_type = null;

        $type_hierarchy = $this->regions_api->get_regions_type_hierarchy();
        foreach ( $type_hierarchy as $level => $types ) {
            // we don't show continents in the Sidelist
            if ( in_array( AWPCP_RegionsAPI::TYPE_CONTINENT, $types ) ) {
                continue;
            }

            $first_type = reset( $types );
            $regions_count = $this->regions_api->count_sidelisted_regions_of_type( $first_type );

            if ( $regions_count > 0 ) {
                $top_level_type = $first_type;
                break;
            }
        }

        if ( is_null( $top_level_type ) ) {
            throw new AWPCP_UnknownTopLevelRegionInSidelistException( 'Unknown top level region type for sidelist.' );
        }

        return $top_level_type;
    }

    /**
     * @since 3.2.0
     */
    private function render_item( $region ) {
        $template = '<li id="region-%d" class="%s"><a href="%s">%s</a>%%s%%s</li>';

        $region_id = $region->region_id;
        $region_type = $region->region_type;
        $region_url = awpcp_configure_querslink( $region->region_id );
        $region_name = $region->region_name;

        return array(
            'html' => sprintf( $template, $region_id, $region_type, $region_url, $region_name ),
            'name' => $region_name,
        );
    }

    /**
     * @since 3.2.0
     */
    private function render_regions_sidelist() {
        $items = $this->concatenate_regions_items( $this->metadata['top_level_items'] );
        $template = '<div class="awpcpcatlayoutright"><ul class="awpcp-region-control-sidelist">%s</ul></div>';

        return sprintf( $template, $items );
    }

    /**
     * @since 3.2.0
     */
    private function concatenate_regions_items( $items ) {
        $handler = '<a class="js-handler" href="#"><span></span></a>';
        $output = array();

        if ( version_compare( phpversion(), '5.3', '>=' ) ) {
            uasort( $items, function( $a, $b ) {
                return strcmp( $a['name'], $b['name'] );
            } );
        } else {
            uasort( $items, create_function( '$a, $b', 'return strcmp( $a["name"], $b["name"] );' ) );
        }

        foreach ( $items as $region_id => $item ) {
            if ( isset( $this->metadata['children_items'][ $region_id ] ) ) {
                $children_items = $this->concatenate_regions_items( $this->metadata['children_items'][ $region_id ] );
                $output[] = sprintf( $item['html'], $handler, sprintf( '<ul data-collapsible="true">%s</ul>', $children_items ) );

            } else {
                $output[] = sprintf( $item['html'], '', '' );
            }
        }

        return join( "\n", $output );
    }
}
