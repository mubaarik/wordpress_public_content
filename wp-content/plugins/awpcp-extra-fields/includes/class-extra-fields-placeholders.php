<?php

function awpcp_extra_fields_placeholders() {
    return new AWPCP_Extra_Fields_Placeholders( awpcp()->settings );
}

class AWPCP_Extra_Fields_Placeholders {

    private $settings;

    public function __construct( $settings ) {
        $this->settings = $settings;
    }

    public function register_content_placeholders( $placeholders ) {
        $definition = array( 'callback' => array( $this, 'render_content_placeholder' ) );

        foreach ( awpcp_get_extra_fields() as $extra_field ) {
            $placeholders[ "x_{$extra_field->field_name}_label" ] = $definition;
            $placeholders[ "x_{$extra_field->field_name}_value" ] = $definition;
            $placeholders[ "x_{$extra_field->field_name}_raw" ] = $definition;
        }

        return $placeholders;
    }

    public function render_content_placeholder( $listing, $placeholder, $context ) {
        if ( ! preg_match( '/x_([a-zA-Z0-9_-]*?)_(label|value|raw)/', $placeholder, $matches ) ) {
            return;
        }

        $field_slug = $matches[1];
        $field_attribute = $matches[2];

        return $this->render_field_attribute( $listing, $field_slug, $field_attribute );
    }

    private function render_field_attribute( $listing, $field_slug, $field_attribute ) {
        $field = awpcp_get_extra_field_by_slug( $field_slug );

        switch ( $field_attribute ) {
            case 'label':
                $output = $field->field_label_view;
                break;
            case 'value':
                $output = $this->render_field_value( $field, get_field_value( $listing->ad_id, $field_slug ) );
                break;
            case 'raw':
                $output = $this->render_field_raw_value( $field, get_field_value( $listing->ad_id, $field_slug ) );
                break;
        }

        return $output;
    }

    private function render_field_label( $field ) {
        $field_label = stripslashes( $field->field_label_view );

        if ( $this->settings->get_option( 'allow-html-in-extra-field-labels' ) ) {
            return $field_label;
        } else {
            return esc_html( $field_label );
        }
    }

    private function render_field_value( $field, $value ) {
        $output = array();

        foreach ( (array) $value as $v ) {
            $output[] = awpcp_extra_fields_render_field_single_value( $field, $v );
        }

        return implode( ', ', $output );
    }

    private function render_field_raw_value( $field, $value ) {
        return implode( ', ', (array) $value );
    }
}
