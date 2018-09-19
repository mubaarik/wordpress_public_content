<?php

function awpcp_insert_advertesiment_placeholder_service() {
    return new AWPCP_InsertAdvertisementPlaceholderService(
        awpcp_listings_collection(),
        awpcp()->settings,
        awpcp_request()
    );
}

class AWPCP_InsertAdvertisementPlaceholderService {

    private $advertisement_positions;

    private $listings;
    private $settings;
    private $request;

    public function __construct( $listings, $settings, $request ) {
        $this->listings = $listings;
        $this->settings = $settings;
        $this->request = $request;
    }

    public function get_advertisement_placeholder( $position_slug ) {
        wp_enqueue_script( 'awpcp-campaign-manager-frontend' );

        $category_id = $this->get_current_category_id();
        $page = $this->get_current_page();

        return $this->render_advertisement_placeholder( $category_id, $page, $position_slug );
    }

    private function get_current_category_id() {
        $category_id = $this->request->get_category_id();

        if ( $category_id ) {
            return $category_id;
        }

        $listing_id = $this->request->get_ad_id();

        if ( $listing_id === 0 ) {
            return 0;
        }

        try {
            $listing = $this->listings->get( $listing_id );
        } catch( AWPCP_Exception $e ) {
            return 0;
        }

        return $listing->ad_category_id;
    }

    private function get_current_page() {
        $offset = intval( $this->request->param( 'offset' ) );
        $items_per_page = intval( $this->request->param( 'results', 10/* TODO: put here default results per page */ ) );

        if ( $items_per_page != 0 ) {
            $page = floor( $offset / $items_per_page ) + 1;
        } else {
            $page = 1;
        }

        return $page;
    }

    private function render_advertisement_placeholder( $category_id, $page, $position_slug ) {
        $dimensions = $this->get_advertisement_position_dimensons( $position_slug );

        $attributes = array(
            'class' => "awpcp-advertisement-placeholder awpcp-advertisement-placeholder-$position_slug",
            'data-position' => $position_slug,
            'data-category' => absint( $category_id ),
            'data-page' => absint( $page ),
            'style' => implode( ' ', array(
                'clear:both;',
                "max-height:{$dimensions['height']}px;",
                'overflow:hidden;',
                "max-width:{$dimensions['width']}px;",
            ) ),
        );

        return str_replace( '<attrs>', awpcp_html_attributes( $attributes ), '<div <attrs>></div>' );
    }

    private function get_advertisement_position_dimensons( $slug ) {
        $slug = preg_match( '/middle-\d+/', $slug ) ? 'middle' : $slug;

        $width = $this->settings->get_option( "advertisement-position-$slug-width" );
        $height = $this->settings->get_option( "advertisement-position-$slug-height" );

        return array( 'width' => $width, 'height' => $height );
    }

    public function insert_top_advertisement_placeholder( $page_content ) {
        $placeholder = $this->get_advertisement_placeholder( 'top' );
        return "$placeholder $page_content";
    }

    public function insert_middle_advertisement_placeholder( $rendered_listing, $listing, $position_in_page ) {
        if ( $position_in_page % 5 == 0 ) {
            $segment = $position_in_page / 5;
            $placeholder = $this->get_advertisement_placeholder( "middle-$segment" );
        } else {
            $placeholder = '';
        }

        return "$rendered_listing $placeholder";
    }

    public function insert_bottom_advertisement_placeholder( $page_content ) {
        $placeholder = $this->get_advertisement_placeholder( 'bottom' );
        return "$page_content $placeholder";
    }
}
