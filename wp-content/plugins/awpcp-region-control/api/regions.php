<?php

function awpcp_regions_api() {
    static $instance = null;

    if ( is_null( $instance ) ) {
        $wpdb = $GLOBALS['wpdb'];
        $basic_regions_api = awpcp_basic_regions_api();
        $file_cache = awpcp_file_cache();

        $instance = new AWPCP_RegionsAPI( $wpdb, $basic_regions_api, $file_cache );
    }

    return $instance;
}

class AWPCP_RegionsAPI {

    const TYPE_CONTINENT = 1;
    const TYPE_COUNTRY = 2;
    const TYPE_STATE = 3;
    const TYPE_CITY = 4;
    const TYPE_COUNTY = 5;

    private $db;
    private $basic_regions_api;
    private $file_cache;

    public function __construct( $db, $basic_regions_api, $file_cache ) {
        $this->db = $db;
        $this->basic_regions_api = $basic_regions_api;
        $this->file_cache = $file_cache;
    }

    public function get_region_types() {
        static $types;

        $types = array(
            self::TYPE_CONTINENT => _x('Continent', 'regions module', 'awpcp-region-control' ),
            self::TYPE_COUNTRY => _x('Country', 'regions module', 'awpcp-region-control' ),
            self::TYPE_STATE => _x('State', 'regions module', 'awpcp-region-control' ),
            self::TYPE_CITY => _x('City', 'regions module', 'awpcp-region-control' ),
            self::TYPE_COUNTY => _x('County', 'regions module', 'awpcp-region-control' )
        );

        return $types;
    }

    public function get_region_type_const($type) {
        switch (strtolower($type)) {
            case 'continent':
                return self::TYPE_CONTINENT;
            case 'country':
                return self::TYPE_COUNTRY;
            case 'state':
                return self::TYPE_STATE;
            case 'city':
                return self::TYPE_CITY;
            case 'county':
                return self::TYPE_COUNTY;
        }
    }

    public function get_region_type_slug($type) {
        switch ($type) {
            case AWPCP_RegionsAPI::TYPE_COUNTRY:
                return 'country';
            case AWPCP_RegionsAPI::TYPE_STATE:
                return 'state';
            case AWPCP_RegionsAPI::TYPE_CITY:
                return 'city';
            case AWPCP_RegionsAPI::TYPE_COUNTY:
                return 'county';
        }
    }

    public function get_region_type_name($type) {
        $types = $this->get_region_types();
        return isset($types[$type]) ? $types[$type] : null;
    }

    public function get_region_types_names($_types) {
        $types = $this->get_region_types();

        $names = array();
        foreach ($_types as $type) {
            if (isset($types[$type])) {
                $names[] = $types[$type];
            }
        }

        return $names;
    }

    public function get_parent_region_type($region_type) {
        $hierarchy = $this->get_regions_type_hierarchy();
        $parent_type = null;

        $previous = null;
        foreach ($hierarchy as $level => $types) {
            if (in_array($region_type, $types)) {
                $parent_type = $previous;
                break;
            }
            $previous = $types;
        }

        return is_array($parent_type) ? reset($parent_type) : $parent_type;
    }

    /**
     * The Region hierarchy is flexible, is technically possible to have
     * regions of different types in the same level. However, that's an 
     * undesired situation.
     *
     * This function returns an array of the type of the regions that are
     * children of regions of a given region type.
     *
     * The level of the given region type is assigned to the highest occurrence
     * of that type in the hierarchy. The children types are those in the
     * next level.
     */
    public function get_children_region_types($region_type) {
        $region_type = (array) $region_type;
        $hierarchy = $this->get_regions_type_hierarchy();
        $bingo = false;

        $children_types = array();
        foreach ($hierarchy as $level => $types) {
            if ($bingo === true) {
                $children_types = $types;
                break;
            }

            if (array_intersect($region_type, $types)) {
                $bingo = true;
            }
        }

        return empty($children_types) ? null : $children_types;
    }

    /**
     * TODO: improve to do only one SQL query
     * Returns numbered array indicating the type of Regions found
     * in each level of the Regions hierarchy
     * @return array
     */
    public function get_regions_type_hierarchy() {
        global $wpdb;

        $levels = get_option('awpcp-regions-type-hierarchy');
        if (is_array($levels)) return $levels;

        $levels = array();
        $hierarchy = $this->get_regions_hierarchy();
        $level = 1;

        $_query = 'SELECT region_id, region_type FROM ' . AWPCP_TABLE_REGIONS . ' ';
        $regions = $wpdb->get_results($_query . 'WHERE region_parent = 0 ');

        while (!empty($regions)) {
            $children = array();
            foreach ($regions as $region) {
                if (isset($hierarchy[$region->region_id]))
                    $children = array_merge($children, $hierarchy[$region->region_id]);

                if (isset($levels[$level][$region->region_type]))
                    $levels[$level][$region->region_type] += 1;
                else
                    $levels[$level][$region->region_type] = 1;
            }

            if (!empty($children)) {
                $query = $_query . ' WHERE region_id IN (' . join(',', $children) . ')';
                $regions = $wpdb->get_results($query);
            } else {
                $regions = array();
            }

            $level = $level + 1;
        }

        foreach ($levels as $level => $types) {
            arsort($types, SORT_NUMERIC);
            $levels[$level] = array_keys($types);
        }

        update_option('awpcp-regions-type-hierarchy', $levels);

        return $levels;
    }

    private function _get_regions_hierarchy($option, $conditions=array()) {
        global $wpdb;

        $children = get_option($option);
        if (is_array($children)) return $children;


        $query = 'SELECT region_id, region_parent FROM ' . AWPCP_TABLE_REGIONS . ' ';
        if ( ! empty( $conditions ) ) {
            $query.= 'WHERE ' . join( ' AND ', $conditions ) . ' ';
        }
        $query.= 'ORDER BY region_name ASC';

        $results = $wpdb->get_results($query);

        $children = array();
        foreach ($results as $region) {
            if ($region->region_parent > 0) {
                $children[$region->region_parent][] = $region->region_id;
            } else if (!isset($children[$region->region_id])) {
                $children[$region->region_id] = array();
            }
        }


        update_option($option, $children);

        return $children;
    }

    /**
     *
     * @since 2.0.7-8
     */
    public function get_regions_hierarchy() {
        // global $wpdb;

        // $children = get_option('awpcp-regions-children');
        // if (is_array($children)) return $children;

        // $query = 'SELECT region_id, region_parent FROM ' . AWPCP_TABLE_REGIONS . ' ';
        // $results = $wpdb->get_results($query);

        // $children = array();
        // foreach ($results as $region) {
        //     if ($region->region_parent > 0) {
        //         $children[$region->region_parent][] = $region->region_id;
        //     } else if (!isset($children[$region->region_id])) {
        //         $children[$region->region_id] = array();
        //     }
        // }

        // update_option('awpcp-regions-children', $children);

        // return $children;
        return $this->_get_regions_hierarchy('awpcp-regions-children');
    }

    /**
     * @since 3.3.4
     */
    private function get_regions_type_hierarchy_in_reverse_order() {
        $types_hierarchy = $this->get_regions_type_hierarchy();
        $reverse_types_hierarchy = array();

        foreach ( array_reverse( $types_hierarchy ) as $level => $types ) {
            $reverse_types_hierarchy[] = $types[0];
        }

        return $reverse_types_hierarchy;
    }

    /**
     * @deprecated since 3.2.0
     */
    public function get_sidelisted_regions_hierarchy() {
        return $this->_get_regions_hierarchy('awpcp-sidelist-regions-children', array('region_sidelisted = 1'));
    }

    /**
     * @since 3.2.0
     */
    public function count_sidelisted_regions_with_id_greater_than( $region_id ) {
        global $wpdb;

        $localized = has_localized_regions();
        $state = true;

        $sql = 'SELECT COUNT(*) FROM ' . AWPCP_TABLE_REGIONS . ' ';
        $sql.= 'WHERE region_id > %d AND region_sidelisted = 1 AND region_state = %d AND region_localized = %d ';
        $sql.= 'ORDER BY region_id ';

        $result = $wpdb->get_var( $wpdb->prepare( $sql, $region_id, $state, $localized ) );

        return $result !== false ? intval( $result ) : $result;
    }

    /**
     * @since 3.2.0
     */
    public function get_sidelisted_regions_with_id_greather_than( $region_id, $limit = 1000 ) {
        global $wpdb;

        $localized = has_localized_regions();
        $state = true;

        $sql = 'SELECT * FROM ' . AWPCP_TABLE_REGIONS . ' ';
        $sql.= 'WHERE region_id > %d AND region_sidelisted = 1 AND region_state = %d AND region_localized = %d ';
        $sql.= 'ORDER BY region_id ';
        $sql.= 'LIMIT %d ';

        return $wpdb->get_results( $wpdb->prepare( $sql, $region_id, $state, $localized, $limit ) );
    }

    /**
     * @since 3.2.8
     */
    public function count_sidelisted_regions_of_type( $region_type ) {
        $localized_regions_only = has_localized_regions();
        $enabled_regions_only = true;

        $query = 'SELECT COUNT(*) FROM ' . AWPCP_TABLE_REGIONS . ' ';
        $query.= 'WHERE region_type = %d AND region_sidelisted = 1  AND region_state = %d AND region_localized = %d';
        $query = $this->db->prepare( $query, $region_type, $enabled_regions_only, $localized_regions_only );

        $result = $this->db->get_var( $query );

        return $result !== false ? intval( $result ) : $result;
    }

    /**
     *
     * @since 2.0.7-8
     */
    public function get_children($region_id) {
        $hierarchy = $this->get_regions_hierarchy();

        if (!isset($hierarchy[$region_id]))
            return array();

        $children = array();
        foreach ($hierarchy[$region_id] as $region) {
            $children[] = $region;
            $children = array_merge($children, $this->get_children($region));
        }

        return $children;
    }

    /**
     * Finds the direct children of a Region or array of Regions.
     *
     * @param  mixed    Region ID or array of Regions IDs
     * @return array    Array of Regions IDs
     * @since  2.0.7-8
     */
    public function get_direct_children($regions) {
        if ($regions || is_array($regions))
            $regions = (array) $regions;

        if (empty($regions)) return array();

        $hierarchy = $this->get_regions_hierarchy();

        $children = array();
        foreach ($regions as $region_id) {
            if (isset($hierarchy[$region_id]) && !empty($hierarchy[$region_id]))
                $children = array_merge($children, $hierarchy[$region_id]);
        }

        return $children;
    }

    public function find_regions($args) {
        global $wpdb;

        extract(wp_parse_args($args, array(
            'fields' => '*',
            'id' => null,
            'name' => null,
            'like' => null,
            'type' => null,
            'parent' => null,
            'state' => 1,
            'localized' => has_localized_regions() ? 1 : null,
            'sidelisted' => null,
            'order' => array('region_name ASC'),
            'offset' => null,
            'limit' => null,
            'hide_empty' => false,
        )));

        $conditions = array();

        if (!is_null($id) && is_array($id) && !empty($id)) {
            $id = array_filter( array_map( 'intval', $id ) );

            // hack to prevent World Anhiliation when trying to get
            // all cities in USA.
            //
            // It doesn't really makes sense to query 33000 rows,
            // nobody is going to use that amount of data. The module needs to
            // change to avoid having to do such large queries.
            if (count($id) > 1000) $id = array_splice($id, 0, 1000);

            if ( ! empty( $id ) ) {
                $conditions[] = sprintf('region_id IN (%s)', join(',', $id));
            }
        } else if (!is_null($id) && !is_array($id)) {
            $conditions[] = $wpdb->prepare( 'region_id = %d', $id );
        }

        if (!is_null($name))
            $conditions[] = $wpdb->prepare( 'region_name = %s', $name );

        if (!is_null($like))
            $conditions[] = sprintf( "region_name LIKE '%%%s%%'", esc_sql( $like ) );

        if (!is_null($type))
            $conditions[] = $wpdb->prepare( 'region_type = %d', $type );

        if ( !is_null( $parent ) && is_array( $parent ) && !empty( $parent ) ) {
            $parent = array_filter( array_map( 'intval', $parent ) );
            if ( !empty( $parent ) ) {
                $conditions[] = sprintf('region_parent IN (%s)', join(',', $parent));
            }
        } else if ( !is_null( $parent ) && !is_array( $parent ) ) {
            $conditions[] = $wpdb->prepare( 'region_parent = %d', $parent );
        }

        if (!is_null($state) && $state)
            $conditions[] = 'region_state = 1';
        else if (!is_null($state))
            $conditions[] = 'region_state = 0';

        if (!is_null($localized) && $localized)
            $conditions[] = 'region_localized = 1';
        else if (!is_null($localized))
            $conditions[] = 'region_localized = 0';

        if (!is_null($sidelisted) && $sidelisted)
            $conditions[] = 'region_sidelisted = 1';
        else if (!is_null($sidelisted))
            $conditions[] = 'region_sidelisted = 0';

        if ( $hide_empty ) {
            $conditions[] = 'count_enabled > 0';
        }

        $query = sprintf('SELECT %s FROM ' . AWPCP_TABLE_REGIONS . ' ', $fields);
        if (!empty($conditions))
            $query.= sprintf(' WHERE %s ', join(' AND ', $conditions));
        if (!empty($order))
            $query.= sprintf(' ORDER BY %s ', join(',', (array) $order));
        if (!is_null($offset) && !is_null($limit))
            $query.= sprintf(' LIMIT %d, %d ', absint( $offset ), absint( $limit ) );

        $results = $wpdb->get_results($query);

        return $results ? $results : array();
    }

    public function find_regions_by_parent( $region_type, $parent_type, $search, $hide_empty = false ) {
        if (is_numeric($search)) {
            $params = array('type' => $parent_type, 'id' => $search);
        } else {
            $params = array('type' => $parent_type, 'name' => $search);
        }

        $parent_region = $this->find_region($params);
        $children = array();

        if ($parent_region) {
            $children = array($parent_region->region_id);
            $children_types = array($parent_type);

            do {
                $children = $this->get_direct_children($children);
                $children_types = $this->get_children_region_types($children_types);
            } while (!empty($children) && !is_null($children_types) && !in_array($region_type, $children_types));
        }

        if (!empty($children))
            return $this->find_regions( array( 'id' => $children, 'hide_empty' => $hide_empty ) );
        return array();
    }

    public function find_region($args) {
        $regions = $this->find_regions($args);
        return !empty($regions) ? $regions[0] : null;
    }

    /**
     * @since 3.3.4
     */
    public function find_most_specific_region( $region_names ) {
        $region_types = $this->get_regions_type_hierarchy_in_reverse_order();

        $best_candidate = null;
        $candidates = array();

        foreach ( $region_types as $type ) {
            $type_slug = $this->get_region_type_slug( $type );
            $region_name = awpcp_array_data( $type_slug, '', $region_names );

            if ( empty( $region_name ) ) {
                continue;
            }

            $regions = $this->find_regions( array( 'name' => $region_name ) );

            if ( empty( $regions ) ) {
                continue;
            } elseif ( 1 == count( $regions ) && isset( $candidates[ $regions[0]->region_id ] ) ) {
                return $candidates[ $regions[0]->region_id ];
            } elseif ( 1 == count( $regions ) ) {
                return $regions[0];
            }elseif ( $best_candidate ) {
                return $best_candidate;
            }

            $best_candidate = $regions[0];

            foreach ( $regions as $region ) {
                $candidates[ $region->region_parent ] = $region;
            }
        }

        throw new AWPCP_Exception( 'No region was found.' );
    }

    /**
     * @since 2.0.7-1
     */
    public function find_by_id($id) {
        if ( absint( $id ) > 0 ) {
            return $this->find_region( array( 'id' => absint( $id ) ) );
        }
        return null;
    }

    /**
     * @since 2.1.0-30
     */
    public function find_by_name($name) {
        return $this->find_region( array(
            'name' => $name,
            'state' => null,
        ) );
    }

    /**
     * Returns an array of all region objects associated to the given Ad.
     */
    public function get_ad_regions($ad) {
        global $wpdb;

        $entries = $this->basic_regions_api->find_by_ad_id( $ad->ad_id );
        $regions = array();

        foreach ( $entries as $entry ) {
            $conditions = array();

            if ( $entry->region_id > 0 ) {
                $conditions[] = $this->db->prepare( 'region_id = %d', $entry->region_id );
            } else {
                $params = array(
                    array(self::TYPE_CITY, $entry->city),
                    array(self::TYPE_COUNTY, $entry->county),
                    array(self::TYPE_STATE, $entry->state),
                    array(self::TYPE_COUNTRY, $entry->country)
                );

                foreach ( $params as $pair ) {
                    if ( strlen( $pair[1] ) > 0 ) {
                        $conditions[] = $this->db->prepare('(region_name = %s AND region_type = %d)', $pair[1], $pair[0]);
                    }
                }

                if ( ! empty( $conditions ) ) {
                    $regions = array_merge( $regions, $this->find_ad_regions( $entry, $conditions ) );
                }
            }
        }

        return $regions;
    }

    private function find_ad_regions( $template, $conditions = array() ) {
        $query = 'SELECT * FROM ' . AWPCP_TABLE_REGIONS . ' WHERE ' . join( ' OR ', $conditions ) . ' ORDER BY region_type ASC, region_name ASC';
        $results = $this->db->get_results( $query );

        return $this->filter_regions( $results );
    }

    /**
     * @since 3.2.3
     */
    public function filter_regions( $regions ) {
        $grouped = $this->group_regions_by_type( $regions );

        $types_hierarchy = $this->get_regions_type_hierarchy();
        arsort( $types_hierarchy );

        $regions = array();

        foreach ( $types_hierarchy as $level => $types ) {
            if ( empty( $types ) ) continue;

            $regions_of_current_type = isset( $grouped[ $types[0] ] ) ? $grouped[ $types[0] ] : array();

            if ( count( $regions_of_current_type ) > 1 ) {
                $parents_type = $this->get_parent_region_type( $types[0] );

                do {
                    $parents = isset( $grouped[ $parents_type ] ) ? $grouped[ $parents_type ] : array();

                    if ( ! empty( $parents ) ) {
                        $parents_ids = awpcp_get_properties( $parents, 'region_id' );
                        foreach ( $regions_of_current_type as $region ) {
                            if ( in_array( $region->region_parent, $parents_ids ) ) {
                                $regions[] = $region;
                                break 2;
                            }
                        }
                    }

                    $parents_type = $this->get_parent_region_type( $parents_type );
                } while ( ! is_null( $parents_type ) );

                if ( is_null( $parents_type ) ) {
                    $regions[] = $regions_of_current_type[0];
                }
            } else if ( count( $regions_of_current_type ) == 1 ) {
                $regions[] = $regions_of_current_type[0];
            }
        }

        return $regions;
    }

    public function group_regions_by_type($regions) {
        $grouped = array();

        foreach ( $regions as $region ) {
            if ( isset( $grouped[ $region->region_type ] ) ) {
                $grouped[ $region->region_type ][] = $region;
            } else {
                $grouped[ $region->region_type ] = array( $region );
            }
        }

        return $grouped;
    }

    // public function set_ad_region($ad, $region) {
    //     global $wpdb;

    //     $allowed_types = array(
    //         self::TYPE_COUNTRY,
    //         self::TYPE_STATE,
    //         self::TYPE_COUNTY,
    //         self::TYPE_CITY
    //     );

    //     $ad_regions = awpcp_basic_regions_api();

    //     while ( ! is_null( $region ) ) {
    //         if ( in_array( $region->region_type, $allowed_types ) ) {
    //             $ad_regions->save( array( 'ad_id' => $ad->ad_id, 'region_id' => $region->region_id ) );
    //             $this->update_ad_count( $region, 1, $ad->disabled ? 0 : 1 );
    //         }

    //         $region = $this->find_by_id($region->region_parent);
    //     }
    // }

    public function update_ad_count($region, $all, $enabled) {
        global $wpdb;

        $set = array();

        if ($all > 0)
            $set[] = 'count_all = GREATEST(count_all + 1, 0)';
        else if ($all < 0)
            $set[] = 'count_all = GREATEST(count_all - 1, 0)';

        if ($enabled > 0)
            $set[] = 'count_enabled = GREATEST(count_enabled + 1, 0)';
        else if ($enabled < 0)
            $set[] = 'count_enabled = GREATEST(count_enabled - 1, 0)';

        $query = 'UPDATE ' . AWPCP_TABLE_REGIONS . ' SET ' . join(', ', $set) . ' WHERE region_id = %d';
        $wpdb->query($wpdb->prepare($query, $region->region_id));
    }

    /**
     * Builds a SQL WHERE part of a query to find Ads in the given region and its
     * child regions.
     *
     * This function only cares to match Ads with Regions, it doesn't consider the
     * status of the Ad or the Regions.
     *
     * In 2.0.7-1 the function was introduced to produce a SQL WHERE statement that
     * considered the active region and its children. That's a very expensive query
     * to build and caues major performance issues when thousands or more regions
     * are defined.
     *
     * As of version 2.1.1, the function will consider the active region only and
     * assumes that any Ad associated with one of its children is ALWAYS associated
     * with the parent region as well.
     *
     * @since 2.0.7-1
     */
    public function sql_where($region_id) {
        global $wpdb;

        $columns = array(
            1 => '', // continent
            2 => 'country',
            3 => 'state',
            4 => 'city',
            5 => 'county'
        );

        $region = $this->find_by_id( $region_id );

        if ( ! is_null( $region ) && isset( $columns[ $region->region_type ] ) ) {
            $sql = 'SELECT DISTINCT ad_id FROM ' . AWPCP_TABLE_AD_REGIONS . ' WHERE %s = %%s';
            $sql = sprintf( $sql, $columns[ $region->region_type ] );
            return sprintf( '`ad_id` IN ( %s )', $wpdb->prepare( $sql, $region->region_name ) );
        } else {
            return '1 = 1';
        }
    }

    /**
     * @since 3.2.3
     */
    public function clear_listings_count() {
        return $this->db->query( 'UPDATE ' . AWPCP_TABLE_REGIONS . ' SET count_all = 0, count_enabled = 0' );
    }

    /**
     * @since 2.0.7-1
     */
    public function clear_cache() {
        $this->clear_transients();
        $this->clear_options();
        $this->clear_file_cache();
    }

    /**
     * @since 3.2.0
     */
    private function clear_transients() {
        global $wpdb;

        $transients = array(
            'awpcp-region-control-list-',
            'awpcp-region-control-duplicated-regions'
        );

        foreach ($transients as $transient) {
            $conditions[] = sprintf("option_name LIKE '%%%s%%'", $transient);
        }

        if (!empty($conditions)) {
            $query = "SELECT option_name FROM {$wpdb->options} WHERE " . join(' OR ', $conditions);
            $transients = $wpdb->get_col($query);
        } else {
            $transients = array();
        }

        foreach ($transients as $transient) {
            delete_site_transient(str_replace('_site_transient_', '', $transient));
        }
    }

    /**
     * @since 3.2.0
     */
    private function clear_options() {
        delete_option( 'awpcp-regions-count' );
        delete_option( 'awpcp-regions-children' );
        delete_option( 'awpcp-regions-type-hierarchy' );

        // deprecated options
        delete_option( 'awpcp-sidelist-regions-children' );
    }

    /**
     * @since 3.2.0
     */
    private function clear_file_cache() {
        $this->file_cache->remove( 'regions-sidelist' );
    }
}
