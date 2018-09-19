<?php

function awpcp_featured_listings_finder() {
    return new AWPCP_FeaturedListingsFinder();
}

class AWPCP_FeaturedListingsFinder {

    public function filter_conditions( $conditions, $query ) {
        if ( isset( $query['featured'] ) && $query['featured'] ) {
            $conditions[] = 'is_featured_ad = 1';
        }

        return $conditions;
    }

    public function filter_order_conditions( $conditions, $orderby, $order, $query ) {
        if ( in_array( 'latest-listings-widget', $query['context'] ) ) {
            return $conditions;
        }

        if ( ! in_array( 'public-listings', $query['context'] ) ) {
            return $conditions;
        }

        array_unshift( $conditions, 'is_featured_ad DESC' );

        return $conditions;
    }
}
