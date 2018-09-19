<?php

if ( class_exists( 'AWPCP_FormField' ) ) {

function awpcp_extra_form_field( $slug ) {
    $extra_field = awpcp_get_extra_field_by_slug( preg_replace( '/^awpcp-/', '', $slug ) );
    return new AWPCP_ExtraFormField( $slug, $extra_field, awpcp_request() );
}

class AWPCP_ExtraFormField extends AWPCP_FormField {

    private $extra_field;
    private $request;

    public function __construct( $slug, $extra_field, $request ) {
        parent::__construct( $slug );

        $this->extra_field = $extra_field;
        $this->request = $request;
    }

    public function get_name() {
        return $this->extra_field->field_label;
    }

    public function format_value( $value ) {
        if ( $this->extra_field->field_validation == 'currency' ) {
            return awpcp_format_money_without_currency_symbol( stripslashes_deep( $value ) );
        } else {
            return stripslashes_deep( $value );
        }
    }

    protected function is_required() {
        return $this->extra_field->required;
    }

    public function is_allowed_in_context( $context ) {
        if ( $context['action'] == 'single' ) {
            return $this->extra_field->show_on_listings == 2 || $this->extra_field->show_on_listings == 3;
        }

        if ( $context['action'] == 'listings' ) {
            return $this->extra_field->show_on_listings == 1 || $this->extra_field->show_on_listings == 3;
        }

        if ( $context['action'] == 'search' ) {
            $allowed_in_search = intval( $this->extra_field->nosearch ) != 1;

            if ( is_user_logged_in() ) {
                $allowed_to_show = $this->extra_field->field_privacy == 'public' || $this->extra_field->field_privacy == 'restricted';
            } else {
                $allowed_to_show = $this->extra_field->field_privacy == 'public';
            }

            return $allowed_to_show && $allowed_in_search;
        }

        return true;
    }

    private function get_field_value( $posted_value, $listing ) {
        $field_name = $this->extra_field->field_name;

        if ( $this->request->param( "awpcp-{$field_name}-min", null ) !== null || $this->request->param( "awpcp-{$field_name}-max", null ) !== null ) {
            return array(
                'min' => $this->request->param( "awpcp-{$field_name}-min", null ),
                'max' => $this->request->param( "awpcp-{$field_name}-max", null )
            );
        } else if ( $this->request->param( "awpcp-{$field_name}-from", null ) !== null || $this->request->param( "awpcp-{$field_name}-to", null ) !== null ) {
            return array(
                'from_date' => $this->request->param( "awpcp-{$field_name}-from", null ),
                'to_date' => $this->request->param( "awpcp-{$field_name}-to", null )
            );
        } else if ( ! empty( $posted_value ) ) {
            return maybe_unserialize( $posted_value );
        } else if ( is_a( $listing, 'AWPCP_Ad' ) ) {
            return isset( $listing->$field_name ) ? $listing->$field_name : $posted_value;
        } else {
            return '';
        }
    }

    public function render( $value, $errors, $listing, $context ) {
        if ( isset( $context['action'] ) && 'search' == $context['action'] ) {
            $field_required = false;
        } else {
            $field_required = $this->is_required();
        }

        $params = array(
            'categories' => implode( ',', array_map( 'esc_attr', $this->extra_field->field_category ) ),

            'required' => $field_required,
            'value' => $this->format_value( $this->get_field_value( $value, $listing ) ),
            'errors' => $errors,

            'label' => $this->get_label(),
            'help_text' => '',

            'html' => array(
                'id' => "awpcp-{$this->extra_field->field_name}",
                'container_class' => $this->get_class_attribute_for_container( $context ),
                'class' => $this->get_class_attribute_for_field( $context ),
                'name' => $this->get_slug(),
            ),
        );

        if ( $this->should_render_number_range_search_fields( $context ) ) {
            return $this->render_number_range_search_fields( $params, $errors );
        } else if ( $this->should_render_date_range_search_fields( $context ) ) {
            return $this->render_date_range_search_fields( $params, $errors );
        } else if ( $this->extra_field->field_input_type == 'Input Box' ) {
            return $this->render_text_field( $params, $errors );
        } else if ( $this->extra_field->field_input_type == 'Select' ) {
            return $this->render_dropdown_field( $params, $errors );
        } else if ( $this->extra_field->field_input_type == 'Textarea Input' ) {
            return $this->render_textarea_field( $params, $errors );
        } else if ( $this->extra_field->field_input_type == 'Radio Button' ) {
            return $this->render_radio_field( $params, $errors );
        } else if ( $this->extra_field->field_input_type == 'Select Multiple' ) {
            return $this->render_dropdown_field_with_multiple_selection( $params, $errors );
        } else if ( $this->extra_field->field_input_type == 'Checkbox' ) {
            return $this->render_checkbox_field( $params, $errors );
        } else if ( $this->extra_field->field_input_type == 'DatePicker' ) {
            return $this->render_datepicker_field( $params, $errors );
        }

        return sprintf( 'The input type of field %s is not supported.', $this->extra_field->field_name );
    }

    private function get_class_attribute_for_container( $context ) {
        $classes = array( 'awpcp-form-spacer', 'awpcp-extra-field', "awpcp-extra-field-{$this->extra_field->field_name}" );

        foreach ( $this->extra_field->field_category as $category_id ) {
            $classes[] = "awpcp-extra-field-category-{$category_id}";
        }

        if ( $this->should_be_always_visible() ) {
            $classes[] = 'awpcp-extra-field-always-visible';
        } else if ( $this->should_be_hidden( $context ) ) {
            $classes[] = 'awpcp-extra-field-hidden';
        }

        return implode( ' ', $classes );
    }

    /**
     * TODO: make dependency on the list of categories explicit
     */
    private function should_be_always_visible() {
        if ( empty( $this->extra_field->field_category ) ) {
            return true;
        }

        if ( count( array_diff( awpcp_get_categories_ids(), $this->extra_field->field_category ) ) == 0 ) {
            return true;
        }

        return false;
    }

    private function should_be_hidden( $context ) {
        $current_category = isset( $context['category'] ) ? $context['category'] : null;

        if ( ! empty( $current_category ) && ! in_array( $current_category, $this->extra_field->field_category ) ) {
            return true;
        }

        return false;
    }

    private function get_class_attribute_for_field( $context ) {
        $context_action = isset( $context['action'] ) ? $context['action'] : '';

        if ( $context_action != 'search' && $this->extra_field->required && $this->extra_field->field_validation != 'missing') {
            $validators = array( 'required' );
        } else {
            $validators = array();
        }

        switch ( $this->extra_field->field_validation ) {
            case 'email':
                $validators[] = 'email';
                break;
            case 'url':
                $validators[] = 'classifiedsurl';
                break;
            case 'currency':
                $validators[] = 'money';
                break;
            case 'missing':
                $validators[] = 'required';
                break;
            case 'numericdeci':
                $validators[] = 'number';
                break;
            case 'numericnodeci':
                $validators[] = 'integer';
                break;
        }

        return implode( ' ', $validators );
    }

    private function should_render_number_range_search_fields( $context ) {
        $has_numeric_data_type = in_array( $this->extra_field->field_mysql_data_type, array( 'INT', 'FLOAT' ) );
        $has_numeric_validator = in_array( $this->extra_field->field_validation, array( 'numericdeci', 'numericnodeci' ) );
        $uses_single_value = in_array( $this->extra_field->field_input_type, array( 'Input Box', 'Select', 'Radio Button' ) );

        return $context['action'] == 'search' && $uses_single_value && ( $has_numeric_validator || $has_numeric_data_type );
    }

    private function render_number_range_search_fields( $params, $errors ) {
        $params['html']['base-id'] = "awpcp-extra-field-{$this->extra_field->field_name}";
        $params['html']['id'] = $params['html']['base-id'] . '-min';

        if ( is_array( $params['value'] ) ) {
            $params['min_value'] = stripslashes( $params['value']['min'] );
            $params['max_value'] = stripslashes( $params['value']['max'] );
        } else {
            $params['min_value'] = $params['max_value'] = stripslashes( $params['value'] );
        }

        $template = AWPCP_EXTRA_FIELDS_MODULE_DIR . '/templates/number-range-search-form-field.tpl.php';

        return $this->render_extra_field( awpcp_render_template( $template, $params ), $params );
    }

    private function should_render_date_range_search_fields( $context ) {
        return $context['action'] == 'search' && $this->extra_field->field_input_type == 'DatePicker';
    }

    private function render_date_range_search_fields( $params, $errors ) {
        $params['html']['base-id'] = "awpcp-extra-field-{$this->extra_field->field_name}";
        $params['html']['id'] = $params['html']['base-id'] . '-from';

        if ( is_array( $params['value'] ) ) {
            $params['from_date'] = stripslashes( $params['value']['from_date'] );
            $params['to_date'] = stripslashes( $params['value']['to_date'] );
        } else {
            $params['from_date'] = $params['to_date'] = stripslashes( $params['value'] );
        }

        $template = AWPCP_EXTRA_FIELDS_MODULE_DIR . '/templates/date-range-search-form-field.tpl.php';

        return $this->render_extra_field( awpcp_render_template( $template, $params ), $params );
    }

    private function render_extra_field( $inner_content, $params ) {
        $template = AWPCP_EXTRA_FIELDS_MODULE_DIR . '/templates/extra-form-field.tpl.php';
        return awpcp_render_template( $template, array_merge( $params, compact( 'inner_content' ) ) );
    }

    private function render_text_field( $params, $errors ) {
        $template = AWPCP_EXTRA_FIELDS_MODULE_DIR . '/templates/text-form-field.tpl.php';
        return $this->render_extra_field( awpcp_render_template( $template, $params ), $params );
    }

    private function render_dropdown_field( $params, $errors ) {
        $params['options'] = $this->extra_field->field_options;

        $template = AWPCP_EXTRA_FIELDS_MODULE_DIR . '/templates/dropdown-form-field.tpl.php';

        return $this->render_extra_field( awpcp_render_template( $template, $params ), $params );
    }

    private function render_textarea_field( $params, $errors ) {
        $template = AWPCP_EXTRA_FIELDS_MODULE_DIR . '/templates/textarea-form-field.tpl.php';
        return $this->render_extra_field( awpcp_render_template( $template, $params ), $params );
    }

    private function render_radio_field( $params, $errors ) {
        $params['options'] = $this->extra_field->field_options;

        $template = AWPCP_EXTRA_FIELDS_MODULE_DIR . '/templates/radio-form-field.tpl.php';

        return $this->render_extra_field( awpcp_render_template( $template, $params ), $params );
    }

    private function render_dropdown_field_with_multiple_selection( $params, $errors ) {
        $params['value'] = is_array( $params['value'] ) ? $params['value'] : explode( ',', $params['value'] );
        $params['options'] = $this->extra_field->field_options;

        $template = AWPCP_EXTRA_FIELDS_MODULE_DIR . '/templates/dropdown-multiple-form-field.tpl.php';

        return $this->render_extra_field( awpcp_render_template( $template, $params ), $params );
    }

    private function render_checkbox_field( $params, $errors ) {
        $params['value'] = (array) $params['value'];
        $params['options'] = $this->extra_field->field_options;

        $template = AWPCP_EXTRA_FIELDS_MODULE_DIR . '/templates/checkbox-form-field.tpl.php';

        return $this->render_extra_field( awpcp_render_template( $template, $params ), $params );
    }

    private function render_datepicker_field( $params, $errors ) {
        $template = AWPCP_EXTRA_FIELDS_MODULE_DIR . '/templates/datepicker-form-field.tpl.php';
        return $this->render_extra_field( awpcp_render_template( $template, $params ), $params );
    }
}

}
