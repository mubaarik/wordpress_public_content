<?php

function awpcp_attachments_placeholder(){
    return new AWPCP_AttachmentsPlaceholder(
        awpcp_media_api(),
        awpcp_file_types()
    );
}

class AWPCP_AttachmentsPlaceholder {

    private $media;
    private $file_types;

    public function __construct( $media, $file_types ) {
        $this->media = $media;
        $this->file_types = $file_types;
    }

    public function do_placeholder( $listing, $placeholder, $context ) {
        $files = $this->get_listing_attachments( $listing );

        ob_start();
            include( AWPCP_ATTACHMENTS_MODULE_DIR . '/frontend/templates/placeholder-attachments.tpl.php' );
            $content = ob_get_contents();
        ob_end_clean();

        return $content;
    }

    private function get_listing_attachments( $listing ) {
        return $this->media->query( array(
            'ad_id' => $listing->ad_id,
            'mime_type' => $this->file_types->get_other_files_mime_types(),
            'enabled' => true,
        ) );
    }
}
