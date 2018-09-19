<?php

function awpcp_advertisement_positions_generator() {
    return new AWPCP_AdvertisementPositionsGenerator(
        awpcp_listings_collection(),
        awpcp()->settings
    );
}

class AWPCP_AdvertisementPositionsGenerator {

    private $listings;
    private $settings;

    public function __construct( $listings, $settings ) {
        $this->listings = $listings;
        $this->settings = $settings;
    }

    public function generate_default_positions() {
        return array(
            'top' => _x( 'Top', 'advertisement position name', 'awpcp-campaign-manager' ),
            'bottom' => _x( 'Bottom', 'advertisement position name', 'awpcp-campaign-manager' ),
            'footer' => _x( 'Footer', 'advertisement position name', 'awpcp-campaign-manager' ),
            'sidebar-one' => _x( 'Sidebar One', 'advertisement position name', 'awpcp-campaign-manager' ),
            'sidebar-two' => _x( 'Sidebar Two', 'advertisement position name', 'awpcp-campaign-manager' ),
        );
    }

    public function generate_advertisement_positions_for_category( $category_id ) {
        $listings_count = $this->listings->count_enabled_listings_in_category( $category_id );
        return $this->generate_advertisement_positions_for_listings_count( $listings_count );
    }

    public function generate_advertisement_positions_for_listings_count( $listings_count ) {
        $results_per_page = $this->settings->get_option( 'adresultsperpage' );
        $real_results_per_page = min( $results_per_page, $listings_count );
        $middle_positions_count = ceil( $real_results_per_page / 5 ) - 1;

        $positions = $this->generate_default_positions();

        for ( $i = 1; $i <= $middle_positions_count; $i = $i + 1 ) {
            $positions[ "middle-$i" ] = $this->generate_middle_position_name( $i );
        }

        return $positions;
    }

    private function generate_middle_position_name( $index ) {
        $position_name = _x( '<ordinal-number> 5 Listings', 'advertisement position name, as in "1st 5 Listings"', 'awpcp-campaign-manager' );
        $position_name = str_replace( '<ordinal-number>', awpcp_ordinalize( $index ), $position_name );
        return $position_name;
    }

    public function get_position_name( $slug ) {
        if ( preg_match( '/middle-(\d+)/', $slug, $matches ) ) {
            return $this->generate_middle_position_name( $matches[1] );
        } else {
            return $this->get_default_position_name( $slug );
        }
    }

    private function get_default_position_name( $slug ) {
        $default_positions = $this->generate_default_positions();

        if ( isset( $default_positions[ $slug ] ) ) {
            return $default_positions[ $slug ];
        } else {
            return '';
        }
    }

    public function get_position_width( $slug ) {
        return $this->get_position_dimension( $slug, 'width' );
    }

    private function get_position_dimension( $slug, $dimension ) {
        if ( preg_match( '/middle-(\d+)/', $slug, $matches ) ) {
            $real_slug = 'middle';
        } else {
            $real_slug = $slug;
        }

        return $this->settings->get_option( "advertisement-position-$real_slug-$dimension" );
    }

    public function get_position_height( $slug ) {
        return $this->get_position_dimension( $slug, 'height' );
    }
}
