<?php

function awpcp_campaign_advertisement_position_image_uploader() {
    // Required to set permission on main upload directory
    require_once( AWPCP_DIR . '/includes/class-fileop.php' );
    return new AWPCP_CampaignAdvertisementPositionImageUploader( new fileop(), awpcp()->settings );
}

class AWPCP_CampaignAdvertisementPositionImageUploader {

    private $fs;
    private $settings;

    public function __construct( $fs, $settings ) {
        $this->fs = $fs;
        $this->settings = $settings;
    }

    public function upload_file( $uploaded_file, $campaign_id, $adverisement_position ) {
        $extension = pathinfo( $uploaded_file['name'], PATHINFO_EXTENSION );
        $filename = "image-$campaign_id-$adverisement_position.$extension";

        $dest_dir = $this->upload_dir( 'awpcp/campaigns' );

        $constraints = array(
            'mime_types' => awpcp_get_image_mime_types(),
            'min_image_size' => 0,
            'max_image_size' => 100000000, // 100 MB
            'min_image_width' => 0,
            'min_image_height' => 0,
        );

        $image = $this->try_to_upload_file( $uploaded_file, $filename, $dest_dir, $constraints );

        return $image['path'];
    }

    private function upload_dir( $subpath ) {
        $uploads_directory_name = $this->settings->get_option( 'uploadfoldername', 'uploads' );
        $path_parts = array( WP_CONTENT_DIR, $uploads_directory_name, $subpath );
        return implode( DIRECTORY_SEPARATOR, $path_parts );
    }

    private function try_to_upload_file( $uploaded_file, $filename, $dest_dir, $constraints ) {
        $this->validate_uploaded_file( $uploaded_file, $constraints );
        list( $filepath, $unique_filename ) = $this->store_uploaded_file( $uploaded_file, $filename, $dest_dir );
        $this->process_uploaded_file( $filename, $unique_filename, $filepath, $dest_dir );

        return array(
            'original' => $filename,
            'filename' => awpcp_utf8_basename( $filepath ),
            'path' => ltrim( str_replace( $this->upload_dir( 'awpcp' ), '', $filepath ), DIRECTORY_SEPARATOR ),
            'mime_type' => $uploaded_file['type'],
        );
    }

    private function validate_uploaded_file( $uploaded_file, $constraints ) {
        if ( $uploaded_file['error'] !== 0 ) {
            throw new AWPCP_Exception( awpcp_uploaded_file_error( $uploaded_file['error'] ) );
        }

        if ( ! is_uploaded_file( $uploaded_file['tmp_name'] ) ) {
            $message = __( 'There was an unknown error trying to upload your file <filename>.', 'awpcp-campaign-manager' );
            $this->throw_file_validation_exception( $uploaded_file, $message );
        }

        if ( ! file_exists( $uploaded_file['tmp_name'] ) ) {
            $message = __( 'The file <filename> was not found in the temporary uploads directory.', 'awpcp-campaign-manager' );
            $this->throw_file_validation_exception( $uploaded_file, $message );
        }

        if ( ! in_array( $uploaded_file['type'], $constraints['mime_types'] ) ) {
            $message = __( 'The type of the uploaded file <filename> is not allowed.', 'awpcp-campaign-manager' );
            $this->throw_file_validation_exception( $uploaded_file, $message );
        }

        $filesize = filesize( $uploaded_file['tmp_name'] );

        if ( empty( $filesize ) || $filesize <= 0 ) {
            $message = _x( 'There was an error trying to find out the file size of the image %s.', 'upload files', 'another-wordpress-classifieds-plugin' );
            $message = sprintf( $message, '<strong>' . $uploaded_file['name'] . '</strong>' );
            throw new AWPCP_Exception( $message );
        }

        if ( $filesize > $constraints['max_image_size'] ) {
            $message = _x( 'The file %s was larger than the maximum allowed file size of %s bytes. The file was not uploaded.', 'upload files', 'another-wordpress-classifieds-plugin' );
            $message = sprintf( $message, '<strong>' . $uploaded_file['name'] . '</strong>', $constraints['max_image_size'] );
            throw new AWPCP_Exception( $message );
        }

        if ( $filesize < $constraints['min_image_size'] ) {
            $message = __( 'The file <filename> is smaller than the minimum allowed file size of <bytes-count> bytes. The file was not uploaded.', 'awpcp-campaign-manager' );
            $message = str_replace( '<bytes-count>', $constraints['min_image_size'], $message );
            $this->throw_file_validation_exception( $uploaded_file, $message );
        }

        $img_info = getimagesize( $uploaded_file['tmp_name'] );

        if ( ! isset( $img_info[ 0 ] ) && ! isset( $img_info[ 1 ] ) ) {
            $message = _x( 'The size of %1$s was too small. The file was not uploaded. File size must be greater than %2$d bytes.', 'upload files', 'another-wordpress-classifieds-plugin' );
            $message = sprintf( $message, '<strong>' . $uploaded_file['name'] . '</strong>', $constraints['min_image_size'] );
            throw new AWPCP_Exception( $message );
        }

        if ( $img_info[ 0 ] < $constraints['min_image_width'] ) {
            $message = _x( 'The image %s did not meet the minimum width of %s pixels. The file was not uploaded.', 'upload files', 'another-wordpress-classifieds-plugin');
            $message = sprintf( $message, '<strong>' . $uploaded_file['name'] . '</strong>', $constraints['min_image_width'] );
            throw new AWPCP_Exception( $message );
        }

        if ( $img_info[ 1 ] < $constraints['min_image_height'] ) {
            $message = _x( 'The image %s did not meet the minimum height of %s pixels. The file was not uploaded.', 'upload files', 'another-wordpress-classifieds-plugin');
            $message = sprintf( $message, '<strong>' . $uploaded_file['name'] . '</strong>', $constraints['min_image_height'] );
            throw new AWPCP_Exception( $message );
        }
    }

    private function throw_file_validation_exception( $uploaded_file, $message ) {
        $message = str_replace( '<filename>', $uploaded_file['name'], $message );
        throw new AWPCP_Exception( $message );
    }

    private function store_uploaded_file( $uploaded_file, $filename, $dest_dir ) {
        $this->prepare_destination_directory( $dest_dir );

        $unique_filename = wp_unique_filename( $dest_dir, $filename );
        $filepath = trailingslashit( $dest_dir ) . $unique_filename;

        if ( ! @move_uploaded_file( $uploaded_file['tmp_name'], $filepath ) ) {
            $message = _x( 'The file %s could not be moved to the destination directory.', 'upload files', 'another-wordpress-classifieds-plugin' );
            $message = sprintf( $message, '<strong>' . $filename . '</strong>' );
            throw new AWPCP_Exception( $message );
        }

        return array( $filepath, $unique_filename );
    }

    private function prepare_destination_directory( $dest_dir ) {
        $permissions = awpcp_directory_permissions();

        if ( ! is_dir( $dest_dir ) && is_writable( WP_CONTENT_DIR ) ) {
            umask( 0 );
            mkdir( $dest_dir, $permissions );
            chown( $dest_dir, fileowner( WP_CONTENT_DIR ) );
        }

        $this->fs->set_permission( $dest_dir, $permissions );
    }

    private function process_uploaded_file( $filename, $unique_filename, $filepath, $dest_dir ) {
        @chmod( $filepath, 0644 );
    }
}
