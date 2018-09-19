<tr style="" class="inline-edit-row quick-edit-row alternate inline-editor" id="edit-1">
    <td class="colspanchange" colspan="8">
        <form action="<?php echo esc_attr( admin_url( 'admin-ajax.php' ) ); ?>" method="post">
        <fieldset class="inline-edit-col-left"><div class="inline-edit-col">
                <h4><?php isset($_POST['id']) ? __('Edit', 'awpcp-coupons' ) : __('Add', 'awpcp-coupons' ) ?></h4>

                <label>
                    <span class="title"><?php _e('Coupon Code', 'awpcp-coupons' ) ?></span>
                    <span class="input-text-wrap"><input type="text" value="<?php echo esc_attr(awpcp_get_property($entry, 'code')) ?>" name="code"></span>
                </label>

                <label>
                    <span class="title"><?php _e('Discount Value', 'awpcp-coupons' ) ?></span>
                </label>
                <div>
                    <?php
                        $discount = awpcp_get_property($entry, 'discount', 10);
                        $type = awpcp_get_property($entry, 'type', 'percent');

                        if (strcasecmp($type, 'amount')  == 0) {
                            $discount = sprintf("%0.2f", $discount);
                        }
                    ?>
                    <input style="width: 60px" type="text" value="<?php echo esc_attr($discount) ?>" name="discount">
                    <select name="type">
                        <?php
                            $types = array(
                                AWPCP_COUPON_TYPE_PERCENT => __('Percent', 'awpcp-coupons' ),
                                AWPCP_COUPON_TYPE_AMOUNT => __('Amount', 'awpcp-coupons' )
                            );
                        ?>
                        <?php foreach ($types as $value => $option): ?>
                        <?php $selected = strcasecmp($type, $option) == 0 ? 'selected="selected"' : '' ?>
                        <option value="<?php echo esc_attr($value) ?>" <?php echo $selected ?>>
                            <?php echo esc_html( $option ); ?></option>
                        <?php endforeach ?>
                    </select>
                </div>

                <label>
                    <span class="title"><?php _e('Redemption Limit', 'awpcp-coupons' ) ?></span>
                    <span class="input-text-wrap"><input style="width: 60px" type="text" value="<?php echo esc_attr(awpcp_get_property($entry, 'redemption_limit', 0)) ?>" name="redemption_limit">
                        <span class="checkbox-title"><?php echo __('0 means no limit.', 'awpcp-coupons' ) ?></span></span>
                </label>

                <label>
                    <span class="title"><?php _e('Expire Date', 'awpcp-coupons' ) ?></span>
                    <span class="input-text-wrap">
                        <?php $date = $entry ? $entry->get_expire_date() : ''; ?>
                        <?php $raw_date = $entry ? awpcp_datetime( 'Y/m/d', $entry->expire_date ) : ''; ?>
                        <input type="text" datepicker-placeholder value="<?php echo esc_attr( $date ); ?>" />
                        <input type="hidden" name="expire_date" value="<?php echo esc_attr( $raw_date ); ?>" />
                    </span>
                </label>

                <label>
                    <span class="title"><?php _e('Active', 'awpcp-coupons' ); ?></span>
                    <input type="hidden" value="0" name="featured" />
                    <input type="checkbox" value="1"<?php echo esc_attr(awpcp_get_property($entry, 'enabled', 1) ? ' checked' : '') ?> name="enabled" />
                    <span class="checkbox-title"><?php _e('is Coupon active?', 'awpcp-coupons' ); ?></span>
                </label>
        </fieldset>

        <p class="submit inline-edit-save">
            <?php $cancel = __( 'Cancel', 'awpcp-coupons' ); ?>
            <?php $label = isset($_POST['id']) ? __('Update', 'awpcp-coupons' ) : __('Add', 'awpcp-coupons' ); ?>
            <a class="button-secondary cancel alignleft" title="<?php echo esc_attr( $cancel ); ?>" href="#inline-edit" accesskey="c"><?php echo esc_html( $cancel ); ?></a>
            <a class="button-primary save alignright" title="<?php echo esc_attr( $label ); ?>" href="#inline-edit" accesskey="s"><?php echo esc_html( $label ); ?></a>
            <img alt="" src="http://local.wordpress.org/wp-admin/images/wpspin_light.gif" style="display: none;" class="waiting">
            <input type="hidden" value="<?php echo esc_attr( awpcp_get_property( $entry, 'id' ) ); ?>" name="id">
            <input type="hidden" value="<?php echo esc_attr( awpcp_get_property( $entry, 'redemption_count' ) ); ?>" name="redemption_count">
            <input type="hidden" value="<?php echo esc_attr( $_POST['action'] ); ?>" name="action">
            <br class="clear">
        </p>
        </form>
    </td>
</tr>
