<?php

function awpcp_delete_custom_category_icon_ajax_handler() {
    return new AWPCP_DeleteCustomCategoryIconAjaxHandler(
        awpcp_uploads_manager(),
        awpcp()->settings,
        $GLOBALS['wpdb'],
        awpcp_request(),
        awpcp_ajax_response()
    );
}

class AWPCP_DeleteCustomCategoryIconAjaxHandler extends AWPCP_AjaxHandler {

    private $uploads_manager;
    private $settings;
    private $request;
    private $db;

    public function __construct( $uploads_manager, $settings, $db, $request, $response ) {
        parent::__construct( $response );

        $this->uploads_manager = $uploads_manager;
        $this->settings = $settings;
        $this->request = $request;
        $this->db = $db;
    }

    public function ajax() {
        try {
            $this->try_to_delete_custom_category_icon();
        } catch ( AWPCP_Exception $e ) {
            return $this->multiple_errors_response( $e->get_errors() );
        }
    }

    private function try_to_delete_custom_category_icon() {
        $filename = $this->request->post( 'filename' );

        if ( ! file_exists( $this->get_path_to_custom_icon( $filename ) ) ) {
            throw new AWPCP_Exception( __( "The specified custom icon doesn't exists.", 'awpcp-category-icons' ) );
        }

        if ( ! awpcp_current_user_is_admin() ) {
            throw new AWPCP_Exception( __( 'You are not authorized to delete custom category icons.', 'awpcp-category-icons' ) );
        }

        $categories = $this->get_categories_using_custom_icon( $filename );

        if ( ! empty( $categories ) ) {
            $categories_names = awpcp_get_properties( $categories, 'name' );
            $categories_names = '<strong>' . implode( '</strong>, <strong>', $categories_names ) . '</strong>';

            $message = __( 'The custom icon cannot be deleted because is being used as the category icon for the following categories: <categories>.', 'awpcp-category-icons' );
            $message = str_replace( '<categories>', $categories_names, $message );

            throw new AWPCP_Exception( $message );
        }

        return $this->delete_custom_category_icon( $filename );
    }

    private function get_path_to_custom_icon( $filename ) {
        return $this->uploads_manager->get_path_for_relative_path( '/custom-icons/' . $filename );
    }

    private function get_categories_using_custom_icon( $filename ) {
        return AWPCP_Category::query( array( 'where' => $this->db->prepare( 'category_icon = %s', "custom:$filename" ) ) );
    }

    private function delete_custom_category_icon( $filename ) {
        if ( $response = unlink( $this->get_path_to_custom_icon( $filename ) ) ) {
            return $this->success();
        } else {
            throw new AWPCP_Exception( __( "The custom icon couldn't be deleted due to an unexpected error.", 'awpcp-category-icons' ) );
        }
    }
}
