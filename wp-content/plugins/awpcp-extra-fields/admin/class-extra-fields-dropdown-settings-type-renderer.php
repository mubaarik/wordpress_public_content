<?php

function awpcp_extra_fields_dropdown_settings_type_renderer() {
    return new AWPCP_Extra_Fields_Dropdown_Settings_Type_Renderer();
}

class AWPCP_Extra_Fields_Dropdown_Settings_Type_Renderer {

    public function render( $content, $args, $settings ) {
        $setting = $args['setting'];

        $select_params = array(
            'options' => $this->get_select_options( $args ),
            'current-value' => $settings->get_option( $setting->name ),
            'attributes' => array(
                'id' => $setting->name,
                'name' => "awpcp-options[{$setting->name}]",
            ),
        );

        return awpcp_html_select( $select_params );
    }

    private function get_select_options( $args ) {
        if ( isset( $args['default-option'] ) && is_array( $args['default-option'] ) ) {
            $options = array( $args['default-option']['value'] => $args['default-option']['label'] );
        } else {
            $options = array();
        }

        foreach ( awpcp_get_extra_fields() as $field ) {
            $options[ "awpcp-{$field->field_name}" ] = $field->field_label;
        }

        return $options;
    }
}
