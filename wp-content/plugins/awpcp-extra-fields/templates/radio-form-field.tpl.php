<?php foreach ( $options as $option ): ?>
<label class="secondary-label"><input class="<?php echo esc_attr( $html['class'] ); ?>" type="radio"<?php echo $value == $option ? ' checked="checked"' : ''; ?> name="<?php echo esc_attr( $html['name'] ); ?>" value="<?php echo esc_attr( $option ); ?>">&nbsp;<?php echo esc_html( $option ); ?></label><br>
<?php endforeach; ?>
