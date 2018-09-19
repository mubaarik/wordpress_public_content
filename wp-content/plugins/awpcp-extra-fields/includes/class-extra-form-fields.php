<?php

function awpcp_extra_form_fields() {
    return new AWPCP_ExtraFormFields();
}

class AWPCP_ExtraFormFields {

    public function register_extra_form_fields( $fields ) {
        $extra_fields = awpcp_get_extra_fields();

        foreach ( $extra_fields as $field ) {
            $fields[ "awpcp-{$field->field_name}" ] = 'awpcp_extra_form_field';
        }

        return $fields;
    }
}
