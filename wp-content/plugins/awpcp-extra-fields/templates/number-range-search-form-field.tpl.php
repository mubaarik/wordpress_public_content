<span class="awpcp-range-search">
    <label for="<?php echo esc_attr( $html['base-id'] ); ?>-min"><?php echo esc_html( __( 'Min', 'awpcp-extra-fields' ) ); ?></label>
    <input id="<?php echo esc_attr( $html['base-id'] ); ?>-min" class="awpcp-textfield inputbox <?php echo esc_attr( $html['class'] ); ?>" type="text" name="<?php echo esc_attr( $html['name'] ); ?>-min" value="<?php echo esc_attr( $min_value ); ?>">
    <label for="<?php echo esc_attr( $html['base-id'] ); ?>-max"><?php echo esc_html( __( 'Max', 'awpcp-extra-fields' ) ); ?></label>
    <input id="<?php echo esc_attr( $html['base-id'] ); ?>-max" class="awpcp-textfield inputbox <?php echo esc_attr( $html['class'] ); ?>" type="text" name="<?php echo esc_attr( $html['name'] ); ?>-max" value="<?php echo esc_attr( $max_value ); ?>">
</span>
