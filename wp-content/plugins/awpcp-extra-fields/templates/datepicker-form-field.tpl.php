<input id="<?php echo esc_attr( $html['id'] ); ?>" class="awpcp-textfield inputbox <?php echo esc_attr( $html['class'] ); ?>" type="text" datepicker-placeholder value="<?php echo awpcp_datetime( 'awpcp-date', $value ); ?>">
<input type="hidden" name="<?php echo esc_attr( $html['name'] ); ?>" value="<?php echo esc_attr( awpcp_datetime( 'Y/m/d', $value ) ); ?>" />
