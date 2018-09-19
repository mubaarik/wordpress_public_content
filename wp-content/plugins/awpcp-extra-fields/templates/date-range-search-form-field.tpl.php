<span class="awpcp-date-range-search">
    <span>
        <label for="<?php echo esc_attr( $html['base-id'] ); ?>-from"><?php echo esc_html( __( 'From', 'awpcp-extra-fields' ) ); ?></label>
        <input id="<?php echo esc_attr( $html['base-id'] ); ?>-from" class="awpcp-textfield inputbox <?php echo esc_attr( $html['class'] ); ?>" type="text" datepicker-placeholder value="<?php echo esc_attr( $from_date ? awpcp_datetime( 'awpcp-date', $from_date ) : '' ); ?>">
        <input type="hidden" name="<?php echo esc_attr( $html['name'] ); ?>-from" value="<?php echo esc_attr( $from_date ? awpcp_datetime( 'Y/m/d', $from_date ) : '' ); ?>" />
    </span>
    <span>
        <label for="<?php echo esc_attr( $html['base-id'] ); ?>-to"><?php echo esc_html( __( 'To', 'awpcp-extra-fields' ) ); ?></label>
        <input id="<?php echo esc_attr( $html['base-id'] ); ?>-to" class="awpcp-textfield inputbox <?php echo esc_attr( $html['class'] ); ?>" type="text" datepicker-placeholder value="<?php echo esc_attr( $to_date ? awpcp_datetime( 'awpcp-date', $to_date ) : '' ); ?>">
        <input type="hidden" name="<?php echo esc_attr( $html['name'] ); ?>-to" value="<?php echo esc_attr( $to_date ? awpcp_datetime( 'Y/m/d', $to_date ) : '' ); ?>" />
    </span>
</span>
