<?php
/**
 * In job listing creation flow, this template shows above the job creation form.
 *
 * This template can be overridden by copying it to yourtheme/job_manager/account-signin.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @author      Automattic
 * @package     WP Job Manager
 * @category    Template
 * @version     1.29.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>
<?php if ( is_user_logged_in() ) : ?>

	<fieldset>
		<label><?php echo esc_html__( 'Your account', 'robolist-lite' ); ?></label>
		<div class="field account-sign-in">
			<?php
				$user = wp_get_current_user();
				printf(
                /* translators: %s: user name */
                    wp_kses_post( 'You are currently signed in as <strong>%s</strong>.', 'robolist-lite' ), esc_html($user->user_login)
                );
			?>

			<a class="button" href="<?php echo esc_url(apply_filters( 'submit_job_form_logout_url', wp_logout_url( get_permalink() ) )); ?>"><?php echo esc_html__( 'Sign out', 'robolist-lite' ); ?></a>
		</div>
	</fieldset>

<?php else :
	$account_required            = job_manager_user_requires_account();
	$registration_enabled        = job_manager_enable_registration();
	$registration_fields         = wpjm_get_registration_fields();
	$use_standard_password_email = wpjm_use_standard_password_setup_email();
	?>
	<fieldset>
		<label><?php esc_html__( 'Have an account?', 'robolist-lite' ); ?></label>
		<div class="field account-sign-in">
			<a class="button" href="<?php echo esc_url(apply_filters( 'submit_job_form_login_url', wp_login_url( get_permalink() )) ); ?>"><?php echo esc_html__( 'Sign in', 'robolist-lite' ); ?></a>

			<?php if ( $registration_enabled ) : ?>

				<?php printf(
                /* translators: %s: create account */
                    esc_html__( 'If you don&rsquo;t have an account you can %screate one below by entering your email address/username.', 'robolist-lite' ), $account_required ? '' : esc_html__( 'optionally', 'robolist-lite' ) . ' '
                ); ?>
				<?php if ( $use_standard_password_email ) : ?>
					<?php printf( esc_html__( 'Your account details will be confirmed via email.', 'robolist-lite' ) ); ?>
				<?php endif; ?>

			<?php elseif ( $account_required ) : ?>

				<?php echo wp_kses_post(apply_filters( 'submit_job_form_login_required_message',  esc_html__('You must sign in to create a new listing.', 'robolist-lite' ) )); ?>

			<?php endif; ?>
		</div>
	</fieldset>
	<?php
	if ( ! empty( $registration_fields ) ) {
		foreach ( $registration_fields as $key => $field ) {
			?>
			<fieldset class="fieldset-<?php echo esc_attr( $key ); ?>">
				<label
					for="<?php echo esc_attr( $key ); ?>"><?php echo wp_kses_post($field[ 'label' ] . apply_filters( 'submit_job_form_required_label', $field[ 'required' ] ? '' : ' <small>' . esc_html__( '(optional)', 'robolist-lite' ) . '</small>', wp_kses_post($field ))); ?></label>
				<div class="field <?php echo $field[ 'required' ] ? 'required-field' : ''; ?>">
					<?php get_job_manager_template( 'form-fields/' . $field[ 'type' ] . '-field.php', array( 'key'   => $key, 'field' => $field ) ); ?>
				</div>
			</fieldset>
			<?php
		}
		do_action( 'job_manager_register_form' );
	}
	?>
<?php endif; ?>
