<?php

function awpcp_attachment_action_handler() {
    return new AWPCP_AttachmentActionHandler( awpcp_media_api(), awpcp_file_types() );
}

class AWPCP_AttachmentActionHandler {

    private $media;
    private $file_types;

    public function __construct( $media, $file_types ) {
        $this->media = $media;
        $this->file_types = $file_types;
    }

    public function set_file_as_primary( $success, $file, $listing ) {
        $other_files_mime_types = $this->file_types->get_other_files_mime_types();

        if ( in_array( $file->mime_type, $other_files_mime_types ) ) {
            return $this->media->set_media_as_primary( $listing, $file, $other_files_mime_types );
        }

        return $success;
    }
}
