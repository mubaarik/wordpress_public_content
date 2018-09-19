<?php

function undo_mark_listing_as_sold_admin_action_handler() {
    return new AWPCP_UndoMarkListingAsSoldAdminActionHandler( awpcp_listings_metadata() );
}

class AWPCP_UndoMarkListingAsSoldAdminActionHandler extends AWPCP_UndoMarkListingAsSoldActionHandler  {

    protected function redirect() {
        return array( 'redirect' => 'index' );
    }
}
