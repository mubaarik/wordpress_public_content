<?php

function awpcp_upload_custom_category_icon_ajax_handler() {
    return new AWPCP_UploadCustomCategoryIconAjaxHandler(
        awpcp_custom_icon_uploader(),
        awpcp_uploaded_file_logic_factory(),
        awpcp_uploads_manager(),
        awpcp()->settings,
        awpcp_ajax_response()
    );
}

class AWPCP_UploadCustomCategoryIconAjaxHandler extends AWPCP_AjaxHandler {

    private $uploader;
    private $uploaded_file_logic_factory;
    private $uploads_manager;
    private $settings;

    public function __construct( $uploader, $uploaded_file_logic_factory, $uploads_manager, $settings, $response ) {
        parent::__construct( $response );

        $this->uplaoder = $uploader;
        $this->uploaded_file_logic_factory = $uploaded_file_logic_factory;
        $this->uploads_manager = $uploads_manager;
        $this->settings = $settings;
    }

    public function ajax() {
        try {
            $this->try_to_process_uploaded_file();
        } catch ( AWPCP_Exception $e ) {
            return $this->multiple_errors_response( $e->get_errors() );
        }
    }

    private function try_to_process_uploaded_file() {
        if ( ! awpcp_current_user_is_admin() ) {
            throw new AWPCP_Exception( __( 'You are not authorized to upload custom category icons.', 'awpcp-category-icons' ) );
        }

        return $this->process_uploaded_file();
    }

    private function process_uploaded_file() {
        $uploaded_file_info = $this->uplaoder->get_uploaded_file();

        if ( $uploaded_file_info->is_complete ) {
            $custom_icon = $this->save_custom_icon( $uploaded_file_info );
            return $this->success( array( 'file' => $custom_icon ) );
        } else {
            return $this->success();
        }
    }

    private function save_custom_icon( $uploaded_file_info ) {
        $uploaded_file = $this->uploaded_file_logic_factory->create_file_logic( $uploaded_file_info );

        if ( ! in_array( $uploaded_file->get_mime_type(), $this->settings->get_runtime_option( 'image-mime-types' ) ) ) {
            $message = _x( 'The mime type (<mime-type>) of the custom icon is not allowed or supported.', 'upload custom icon', 'awpcp-category-icons' );
            $message = str_replace( '<mime-type>', $uploaded_file->get_mime_type(), $message );

            throw new AWPCP_Exception( $message );
        }

        $this->uploads_manager->move_file_to( $uploaded_file, 'custom-icons' );

        return $this->get_custom_icon_information_from_uploaded_file( $uploaded_file );
    }

    private function get_custom_icon_information_from_uploaded_file( $uploaded_file ) {
        return array(
            'id' => $uploaded_file->get_name(),
            'name' => $uploaded_file->get_name(),
            'url' => $this->uploads_manager->get_url_for_relative_path( $uploaded_file->get_relative_path() ),
        );
    }
}
