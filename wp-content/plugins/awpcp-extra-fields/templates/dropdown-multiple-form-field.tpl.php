<select id="<?php echo esc_attr( $html['id'] ); ?>" class="<?php echo esc_attr( $html['class'] ); ?>" multiple name="<?php echo esc_attr( $html['name'] ); ?>[]" style="width:25%%;height:100px">
<?php foreach ( $options as $option ): ?>
    <option<?php echo in_array( $option, $value ) ? ' selected="selected"' : ''; ?> value="<?php echo esc_attr( $option ); ?>"><?php echo esc_html( $option ); ?></option>
<?php endforeach; ?>
</select>
