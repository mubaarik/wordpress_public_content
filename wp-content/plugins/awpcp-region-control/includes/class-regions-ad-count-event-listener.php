<?php

/**
 * @since 3.2.3
 */
function awpcp_regions_ad_count_event_listener() {
    return new AWPCP_RegionsAdCountEventListener( awpcp_regions_api() );
}

/**
 * @since 3.2.3
 */
class AWPCP_RegionsAdCountEventListener {

    /**
     * @since 3.2.3
     */
    public function __construct( $regions_api ) {
        $this->regions_api = $regions_api;
    }

    /**
     * Update Region Ad count when a new Ad is placed.
     *
     * @since 3.2.3
     */
    public function on_place_ad( $ad ) {
        foreach ( $this->regions_api->get_ad_regions( $ad ) as $region ) {
            $this->regions_api->update_ad_count( $region, 1, $ad->disabled ? 0 : 1 );
        }
    }

    /**
     * @since 3.2.3
     */
    public function on_before_edit_ad( $ad ) {
        // save old regions to be updated when we are sure the Ad was updated
        $this->old_regions = awpcp_get_properties( $this->regions_api->get_ad_regions( $ad ), 'region_id' );
    }

    /**
     * @since 3.2.3
     */
    public function on_edit_ad( $ad ) {
        $delta = $ad->disabled ? 0 : -1;

        // decrease Ad count in old Regions
        if ( isset( $this->old_regions ) && is_array( $this->old_regions ) ) {
            foreach ( $this->regions_api->find_regions( array( 'id' => $this->old_regions ) ) as $region ) {
                $this->regions_api->update_ad_count( $region, -1, $delta );
            }
        }

        $this->on_place_ad( $ad );
    }

    /**
     * @since 3.2.3
     */
    public function on_approve_ad( $ad ) {
        foreach ($this->regions_api->get_ad_regions( $ad)  as $region ) {
            $this->regions_api->update_ad_count( $region, 0, 1 );
        }
    }

    /**
     * @since 3.2.3
     */
    public function on_disable_ad( $ad ) {
        foreach ( $this->regions_api->get_ad_regions( $ad ) as $region ) {
            $this->regions_api->update_ad_count( $region, 0, -1 );
        }
    }

    /**
     * @since 3.2.3
     */
    public function on_before_delete_ad( $ad ) {
        $delta = $ad->disabled ? 0 : -1;

        foreach ( $this->regions_api->get_ad_regions( $ad ) as $region ) {
            $this->regions_api->update_ad_count( $region, -1, $delta );
        }
    }
}
