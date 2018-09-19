<select id="<?php echo esc_attr( $html['id'] ); ?>" class="<?php echo esc_attr( $html['class'] ); ?>" name="<?php echo esc_attr( $html['name'] ); ?>">
    <option value=""><?php echo esc_html( __( 'Select One', 'awpcp-extra-fields' ) ); ?></option>
<?php foreach ( $options as $option ): ?>
    <option<?php echo $value == $option ? ' selected="selected"' : ''; ?> value="<?php echo esc_attr( $option ); ?>"><?php echo esc_html( $option ); ?></option>
<?php endforeach; ?>
</select>
