<?php

function awpcp_mark_as_sold_image_placeholders_filter() {
    return new AWPCP_MarkAsSoldImagePlaceholdersFilter( awpcp_listings_metadata() );
}

class AWPCP_MarkAsSoldImagePlaceholdersFilter {

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
        return $this->build_sold_ribbon_image( $image_url );
    }

    private function build_sold_ribbon_image( $image_url ) {
        $sold_ribbon = '<img class="awpcp-sold-ribbon" src="<image-url>" style="position: absolute; top: 0; left: 0;">';
        $sold_ribbon = str_replace( '<image-url>', $image_url, $sold_ribbon );
        return $sold_ribbon;
    }

    private function get_small_sold_ribbon_image() {
        $image_url = AWPCP_MARK_AS_SOLD_MODULE_URL . '/resources/images/sold-ribbon-small.png';
        return $this->build_sold_ribbon_image( $image_url );
    }
}
