<div class="metabox-holder">
    <div class="postbox">
        <?php echo awpcp_html_postbox_handle( array( 'content' => __( 'Coupons Settings', 'awpcp-coupons' ) ) ); ?>
        <div class="inside">
        <form method="post">
            <label for="awpcp-use-coupon-system">
                <b><?php _e('Use Coupon System', 'awpcp-coupons' ); ?></b>
                <?php if ($is_coupons_system_enabled) {
                    $checked = 'checked="checked"';
                } else {
                    $checked = '';
                } ?>
                <input type="hidden" name="use-coupon-system" value="0" />
                <input id="awpcp-use-coupon-system" type="checkbox" name="use-coupon-system" value="1" <?php echo $checked ?> />
            </label>
            <p><?php _e('When checked your customers will be able to enter a coupon code in the shopping cart before checkout', 'awpcp-coupons' ) ?></p>
            <input type="hidden" name="action" value="update-settings" />
            <input class="button" type="submit" value="<?php _e('Update', 'awpcp-coupons' ); ?>" />
        </form>
        </div>
    </div>
</div>

<form method="get" action="<?php echo esc_attr($this->url(array('action' => false))) ?>">
    <?php echo awpcp_html_hidden_fields( $this->params ); ?>

    <?php $url = $this->url( array( 'action' => 'add-coupon' ) ); ?>
    <?php $label = __( 'Add Coupon', 'another-wordpress-classifieds-plugin' ); ?>
    <a class="add button-primary" title="<?php echo esc_attr( $label ); ?>" href="<?php echo esc_attr( $url ); ?>"><?php echo $label; ?></a>

    <?php echo $table->display() ?>
</form>
