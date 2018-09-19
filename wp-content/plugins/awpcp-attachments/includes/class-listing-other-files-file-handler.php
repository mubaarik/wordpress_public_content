<?php

function awpcp_listing_other_files_file_handler() {
    return new AWPCP_ListingFileHandler(
        awpcp_listing_other_files_file_validator(),
        awpcp_listing_other_files_file_mover(),
        awpcp_listing_other_files_file_processor(),
        awpcp_listing_media_creator()
    );
}

function awpcp_listing_other_files_file_mover() {
    return new AWPCP_Listing_Other_Files_File_Mover( awpcp_uploads_manager() );
}

class AWPCP_Listing_Other_Files_File_Mover {

    private $uploads_manager;

    public function __construct( $uploads_manager ) {
        $this->uploads_manager = $uploads_manager;
    }

    public function move_file( $file ) {
        $this->uploads_manager->move_file_to( $file, 'others' );
    }
}
