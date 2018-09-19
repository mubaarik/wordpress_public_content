<p class="align-text-right">
    <?php if (is_null($coupon)): ?>
    <label for="coupon-code"><?php _ex('Do you have a coupon code?', 'awpcp coupons', 'awpcp-coupons' ); ?></label>
    <?php else: ?>
    <?php sprintf(_x('You are already using the following coupon: %s', 'awpcp coupons', 'awpcp-coupons' ), $coupon->code); ?><br/>
    <label for="coupon-code"><?php _ex('Use a different coupon code:', 'awpcp coupons', 'awpcp-coupons' ); ?></label>
    <?php endif; ?>
    <input type="text" id="coupon-code" name="coupon-code" />
    <input type="submit" name="apply-coupon" value="<?php _ex('Apply', 'awpcp coupons', 'awpcp-coupons' ); ?>" />
</p>
