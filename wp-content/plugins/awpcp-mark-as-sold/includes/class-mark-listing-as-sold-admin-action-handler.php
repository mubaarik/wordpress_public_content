<?php

function mark_listing_as_sold_admin_action_handler() {
    return new AWPCP_MarkListingAsSoldAdminActionHandler( awpcp_listings_metadata() );
}

class AWPCP_MarkListingAsSoldAdminActionHandler extends AWPCP_MarkListingAsSoldActionHandler  {

    protected function redirect() {
        return array( 'redirect' => 'index' );
    }
}
