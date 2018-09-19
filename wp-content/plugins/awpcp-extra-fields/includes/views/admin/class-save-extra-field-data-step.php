<?php

function awpcp_save_extra_field_data_step() {
    return new AWPCP_SaveExtraFieldDataStep(
        awpcp_extra_fields_column_manager(),
        awpcp_request(),
        $GLOBALS['wpdb']
    );
}

class AWPCP_SaveExtraFieldDataStep {

    private $column_manager;
    private $request;
    private $wpdb;

    public function __construct( $column_manager, $request, $wpdb ) {
        $this->column_manager = $column_manager;
        $this->request = $request;
        $this->wpdb = $wpdb;
    }

    public function post() {
        $data = $this->get_posted_data();

        $field_id = $this->request->param( 'awpcp_extra_field_id' );
        if ( ! empty( $field_id ) ) {
            $previous_field = awpcp_get_extra_field( $field_id );
        } else {
            $previous_field = null;
        }

        try {
            // TODO: do not return output, render it
            // The above is not currently possible, because the whole admin section is
            // being handled in a single function. That code needs to be converted into
            // a page class.
            return $this->save_field_data( $data, $previous_field );
        } catch ( AWPCP_Exception $e ) {
            $output = load_the_extra_fields_form(
                $previous_field ? $previous_field->field_id : 0,
                $data['field_name'],
                $data['field_label'],
                $data['field_label_view'],
                $data['field_input_type'],
                $data['field_mysql_data_type'],
                $data['field_options'],
                $data['field_validation'],
                $data['field_privacy'],
                $data['field_category'],
                $e->getMessage(),
                $data['nosearch'],
                $data['show_on_listings'],
                $data['required']
            );

            throw new AWPCP_Exception( $output );
        }
    }

    private function get_posted_data() {
        $data = stripslashes_deep( array(
            'field_name' => $this->request->param( 'awpcp_extra_field_name' ),
            'field_label' => $this->request->param( 'awpcp_extra_field_label' ),
            'field_label_view' => $this->request->param( 'awpcp_extra_field_label_view' ),
            'field_options' => $this->request->param( 'awpcp_extra_field_options' ),
            'field_input_type' => $this->request->param( 'awpcp_extra_field_input_type' ),
            'field_mysql_data_type' => $this->request->param( 'awpcp_extra_field_mysqldata_type' ),
            'field_validation' => $this->request->param( 'awpcp_extra_field_validation' ),
            'field_privacy' => $this->request->param( 'awpcp_extra_field_privacy' ),
            'field_category' => $this->request->param( 'awpcp_extra_field_category', array() ),
            'nosearch' => $this->request->param( 'awpcp_extra_field_nosearch' ),
            'show_on_listings' => $this->request->param( 'awpcp_extra_field_listings' ),
            'required' => $this->request->param( 'awpcp-extra-field-required' ),
        ) );

        $data['field_name'] = $data['field_name'];

        $data['field_options'] = array_map( 'trim', explode( "\n", $data['field_options'] ) );
        $data['field_options'] = array_filter( $data['field_options'], 'strlen' );

        $data['field_category'] = array_filter( (array) $data['field_category'], 'strlen' );

        $data['nosearch'] = absint( $data['nosearch'] );
        $data['required'] = absint( $data['required'] );

        return $data;
    }

    private function save_field_data( $data, $previous_field ) {
        $this->validate_data( $data, $previous_field );

        $data['field_options'] = maybe_serialize( $data['field_options'] );
        $data['field_category'] = maybe_serialize( $data['field_category'] );

        if ( ! is_null( $previous_field ) ) {
            return $this->update_field( $previous_field, $data );
        } else {
            return $this->create_field( $data );
        }
    }

    private function validate_data( $data, $previous_field=null ) {
        if ( empty( $data['field_name'] ) ) {
            throw new AWPCP_Exception( __( 'You did not provide a name for the field.', 'awpcp-extra-fields' ) );
        }

        if ( awpcp_extra_fields_is_reserved_name( $data['field_name'] ) ) {
            $message = __("You can't use <strong>%s</strong> as the name for the field. It is a WordPress Reserved Term.", 'awpcp-extra-fields' );
            $message = sprintf( $message, $data['field_name'] );
            throw new AWPCP_Exception( $message );
        }

        if ( ! preg_match( '/^[a-zA-Z_][a-zA-Z0-9_]*$/', $data['field_name'] ) ) {
            $message = __( "The name of the field must include alphanumeric or underscore characters only (a-z, A-Z, 0-9, _) and it must start with a letter or an underscore.", 'awpcp-extra-fields' );
            throw new AWPCP_Exception( $message );
        }

        if ( empty( $data['field_label'] ) ) {
            throw new AWPCP_Exception( __( "You did not provide a post form label for the field.", 'awpcp-extra-fields' ) );
        }

        if ( empty( $data['field_label_view'] ) ) {
            throw new AWPCP_Exception( __( "You did not provide an ad view label for the field.", 'awpcp-extra-fields' ) );
        }

        if ( empty( $data['field_input_type'] ) ) {
            throw new AWPCP_Exception( __( "You did not indicate the input element type for the field.", 'awpcp-extra-fields' ) );
        }

        if ( in_array( $data['field_input_type'], array( 'Select', 'Select Multiple', 'Radio Button', 'Checkbox' ) ) ) {
            if ( empty( $data['field_options'] ) ) {
                throw new AWPCP_Exception( __( "You have indicated the field input type is either a checkbox, radio button or drop down element, however you did not provide any options for the field. You either need to change the input type to input box or textarea input or provide some options.", 'awpcp-extra-fields' ) );
            }
        }

        if( empty( $data['field_mysql_data_type'] ) ) {
            throw new AWPCP_Exception( __( "You did not indicate the field MYSQL data type.", 'awpcp-extra-fields' ) );
        }

        if ( is_null( $previous_field ) && awpcp_column_exists( AWPCP_TABLE_ADS, $data['field_name'] ) ) {
            throw new AWPCP_Exception( __( "Duplicate field name. You cannot use the same field name more than once.", 'awpcp-extra-fields' ) );
        }
    }

    private function update_field( $field, $data ) {
        $result = $this->wpdb->update( AWPCP_TABLE_EXTRA_FIELDS, $data, array( 'field_id' => $field->field_id ) );

        if ( $result === false ) {
            throw new AWPCP_Exception( 'The was an error trying to save the Extra Field information to the database.', 'awpcp-extra-fields' );
        }

        $mysql_data_type = $this->get_appropriate_mysql_data_type( $data['field_input_type'], $data['field_mysql_data_type'] );

        try {
            $this->column_manager->update_field_column_if_necessary( $field, $data['field_name'], $mysql_data_type );
        } catch ( AWPCP_Exception $e )  {
            $this->wpdb->update( AWPCP_TABLE_EXTRA_FIELDS, (array)$this->get_field_data( $field ), array( 'field_id' => $field->field_id ) );
            throw $e;
        }

        return __( 'The field has been updated successfully.', 'awpcp-extra-fields' );
    }

    private function get_appropriate_mysql_data_type( $input_type, $mysql_data_type ) {
        switch ( $input_type ) {
            case 'Select Multiple':
            case 'Checkbox':
                // We need to serialize and store multiple values. We'll
                // always use TEXT data type, regardless of what the user has
                // selected.
                $mysql_data_type = 'TEXT';
                break;
            case 'Input Box':
            case 'Textarea Input':
            case 'Select':
            case 'Radio Button':
                break;
        }

        return $mysql_data_type;
    }

    private function get_field_data( $field ) {
        return array(
            'field_id' => $field->field_id,
            'field_name' => $field->field_name,
            'field_label' => $field->field_label,
            'field_label_view' => $field->field_label_view,
            'field_options' => maybe_serialize( $field->field_options ),
            'field_input_type' => $field->field_input_type,
            'field_mysql_data_type' => $field->field_mysql_data_type,
            'field_validation' => $field->field_validation,
            'field_privacy' => $field->field_privacy,
            'field_category' => maybe_serialize( $field->field_category ),
            'nosearch' => $field->nosearch,
            'show_on_listings' => $field->show_on_listings,
            'required' => $field->required,
            'weight' => $field->weight,
        );
    }

    private function create_field( $data ) {
        $data['weight'] = awpcp_extra_fields_max_field_weight() + 1;
        $result = $this->wpdb->insert( AWPCP_TABLE_EXTRA_FIELDS, $data );

        if ( $result === false ) {
            throw new AWPCP_Exception( 'The was an error trying to save the Extra Field information to the database.', 'awpcp-extra-fields' );
        }

        $new_field_id = $this->wpdb->insert_id;
        $mysql_data_type = $this->get_appropriate_mysql_data_type( $data['field_input_type'], $data['field_mysql_data_type'] );

        try {
            $this->column_manager->create_field_column( $data['field_name'], $mysql_data_type );
        } catch ( AWPCP_Exception $e )  {
            $sql = 'DELETE FROM ' . AWPCP_TABLE_EXTRA_FIELDS . ' WHERE field_id = %d';
            $this->wpdb->query( $this->wpdb->prepare( $sql, $new_field_id ) );
            throw $e;
        }

        return __( 'The new field has been added successfully.', 'awpcp-extra-fields' );
    }
}
