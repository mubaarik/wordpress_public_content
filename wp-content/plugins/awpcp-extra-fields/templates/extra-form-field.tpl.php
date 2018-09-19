<div class="<?php echo esc_attr( $html['container_class'] ); ?>" data-category="<?php echo esc_attr( $categories ); ?>">
    <?php $field_label = get_awpcp_option( 'allow-html-in-extra-field-labels' ) ? $label : esc_html( $label ); ?>
    <label class="awpcp-block-label" for="<?php echo esc_attr( $html['id'] ); ?>"><?php echo $field_label; ?><?php echo $required ? '*' : ''; ?></label>
    <?php echo $inner_content; ?>
    <?php awpcp_form_error( $html['name'], $errors ); ?>
</div>
