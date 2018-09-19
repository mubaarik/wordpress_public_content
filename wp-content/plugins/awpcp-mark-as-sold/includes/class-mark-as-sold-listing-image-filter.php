<?php

function awpcp_mark_as_sold_listing_image_filter() {
    return new AWPCP_MarkAsSoldListingImageFilter( awpcp_listings_metadata() );
}

class AWPCP_MarkAsSoldListingImageFilter {

    private $metadata;

    public function __construct( $metadata ) {
        $this->metadata = $metadata;
    }

    public function filter_image_placeholders( $placehoders, $listing ) {
        if ( ! $this->metadata->get( $listing->ad_id, 'is-sold' ) ) {
            return $placehoders;
        }

        $sold_ribbon = $this->get_sold_ribbon_image();
        $placehoders['featured_image'] = str_replace( '<img', "$sold_ribbon<img", $placehoders['featured_image'] );
        $placehoders['featureimg'] = str_replace( '<img', "$sold_ribbon<img", $placehoders['featureimg'] );

        $sold_ribbon = $this->get_small_sold_ribbon_image();
        $placehoders['awpcp_image_name_srccode'] = str_replace( '<img', "$sold_ribbon<img", $placehoders['awpcp_image_name_srccode'] );

        return $placehoders;
    }

    private function get_sold_ribbon_image() {
        $image_url = AWPCP_MARK_AS_SOLD_MODULE_URL . '/resources/images/sold-ribbon.png';
        return $this->build_sold_ribbon_image( $image_url, 76, 76 );
    }

    private function build_sold_ribbon_image( $image_url, $width, $height ) {
        $html_attributes = array(
            'attributes' => array(
                'class' => 'awpcp-sold-ribbon',
                'src' => $image_url,
                'alt' => __( 'Listing marked as sold.', 'awpcp-mark-as-sold' ),
                'width' => $width,
                'height' => $height,
                'style' => 'position: absolute; top: 0; left: 0; box-shadow: none;',
            ),
        );

        return awpcp_html_image( $html_attributes );
    }

    private function get_small_sold_ribbon_image() {
        $image_url = AWPCP_MARK_AS_SOLD_MODULE_URL . '/resources/images/sold-ribbon-small.png';
        return $this->build_sold_ribbon_image( $image_url, 46, 46 );
    }

    public function filter_listing_thumbnail_in_widget( $thubmnail, $listing ) {
        if ( ! $this->metadata->get( $listing->ad_id, 'is-sold' ) ) {
            return $thubmnail;
        }

        return str_replace( '<img', sprintf( "%s<img", $this->get_small_sold_ribbon_image() ), $thubmnail );
    }
}
