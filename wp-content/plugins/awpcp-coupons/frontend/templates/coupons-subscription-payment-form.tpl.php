<p><?php _e('If you have a Coupon code please enter it below and click the Use Coupon button.', 'awpcp-coupons' ) ?>
<?php if (!is_null($awpcp_coupon)): ?>
<br/><?php _e('Please be aware that you are already using the following Coupon:', 'awpcp-coupons' ) ?> 
<?php echo $awpcp_coupon->code ?></b> (<?php echo $awpcp_coupon->format_discount() ?>).</p>
<?php endif ?>
<form method="post">
	<input type="hidden" name="subscription-plan" value="<?php echo $plan_id ?>" />
	<input type="hidden" name="payment-method" value="<?php echo $payment_method ?>" />
	<input type="hidden" name="step" value="<?php echo $step ?>" />
	<label for="awpcp-coupons-code"><b><?php _e('Coupon Code', 'awpcp-coupons' ); ?></b></label>
	<input id="awpcp-coupons-code" type="text" name="coupon" value="" />
	<input class="button" type="submit" value="<?php _e('Use Coupon', 'awpcp-coupons' ); ?>" />
</form>
<br/>
