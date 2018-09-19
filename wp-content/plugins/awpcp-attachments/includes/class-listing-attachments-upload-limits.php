<?php

function awpcp_listing_attachments_upload_limits() {
    return new AWPCP_ListingAttachmentsUploadLimits(
        awpcp_file_types(),
        awpcp_media_api(),
        awpcp()->settings
    );
}

class AWPCP_ListingAttachmentsUploadLimits {

    private $file_types;
    private $media_api;
    private $settings;

    public function __construct( $file_types, $media_api, $settings ) {
        $this->file_types = $file_types;
        $this->media_api = $media_api;
        $this->settings = $settings;
    }

    public function filter_listing_upload_limits( $upload_limits, $listing, $payment_term ) {
        $other_files_upload_limits = $this->get_other_files_upload_limits( $listing );

        if ( ! empty( $other_files_upload_limits['extensions'] ) ) {
            $upload_limits['others'] = $other_files_upload_limits;
        }

        return $upload_limits;
    }

    private function get_other_files_upload_limits( $listing ) {
        return array(
            'mime_types' => $this->file_types->get_other_allowed_files_mime_types(),
            'extensions' => $this->file_types->get_other_allowed_files_extensions(),
            'allowed_file_count' => $this->settings->get_option( 'attachments-number-of-other-files-allowed' ),
            'uploaded_file_count' => $this->count_uploaded_files_by_mime_type( $listing, $this->file_types->get_other_files_mime_types() ),
            'min_file_size' => 0,
            'max_file_size' => $this->settings->get_option( 'attachments-max-file-size' ),
        );
    }

    private function count_uploaded_files_by_mime_type( $listing, $mime_types ) {
        return $this->media_api->query( array(
            'fields' => 'count',
            'ad_id' => $listing->ad_id,
            'mime_type' => $mime_types,
        ) );
    }
}
