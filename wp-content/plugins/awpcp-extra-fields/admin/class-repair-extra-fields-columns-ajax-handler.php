<?php

/**
 * @since 3.5.9
 */
function awpcp_repair_extra_fields_columns_ajax_handler() {
    return new AWPCP_Repair_Extra_Fields_Columns_Ajax_Handler(
        awpcp_extra_fields_column_manager(),
        awpcp_ajax_response()
    );
}

/**
 * @since 3.5.9
 */
class AWPCP_Repair_Extra_Fields_Columns_Ajax_Handler extends AWPCP_AjaxHandler {

    private $column_manager;

    public function __construct( $column_manager, $response ) {
        parent::__construct( $response );

        $this->column_manager = $column_manager;
    }

    public function ajax() {
        $extra_fields = awpcp_get_extra_fields();

        foreach ( $extra_fields as $field ) {
            try {
                $this->column_manager->create_field_column_if_necessary( $field );
            } catch ( AWPCP_Exception $e ) {
                return $this->error_response( $e->getMessage() );
            }
        }

        return $this->progress_response( count( $extra_fields ), 0 );
    }
}
