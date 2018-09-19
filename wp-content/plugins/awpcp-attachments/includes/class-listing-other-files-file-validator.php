<?php

function awpcp_listing_other_files_file_validator() {
    return new AWPCP_ListingOtherFilesFileValidator( awpcp_listing_upload_limits(), awpcp_file_validation_errors() );
}

class AWPCP_ListingOtherFilesFileValidator extends AWPCP_ListingFileValidator {

    protected function get_listing_upload_limits( $listing ) {
        return $this->upload_limits->get_listing_upload_limits_by_file_type( $listing, 'others' );
    }
}
