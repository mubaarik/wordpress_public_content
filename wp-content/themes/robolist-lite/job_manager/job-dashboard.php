<?php
/**
 * Job dashboard shortcode content.
 *
 * This template can be overridden by copying it to yourtheme/job_manager/job-dashboard.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @author      Automattic
 * @package     WP Job Manager
 * @category    Template
 * @version     1.27.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>
<div id="job-manager-job-dashboard">
	<p><?php echo esc_html__( 'Your listings are shown in the table below.', 'robolist-lite' ); ?></p>
	<table class="job-manager-jobs">
		<thead>
			<tr>
				<?php foreach ( $job_dashboard_columns as $key => $column ) :
                if($key!='filled'):
                ?>
					<th class="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $column ); ?></th>
				<?php endif; endforeach; ?>
			</tr>
		</thead>
		<tbody>
			<?php if ( ! $jobs ) : ?>
				<tr>
					<td colspan="6"><?php echo  esc_html__( 'You do not have any active listings.', 'robolist-lite' ); ?></td>
				</tr>
			<?php else : ?>
				<?php foreach ( $jobs as $job ) : ?>
					<tr>
						<?php foreach ( $job_dashboard_columns as $key => $column ) :
                           if($key!='filled'):
                            ?>
							<td class="<?php echo esc_attr( $key ); ?>">
								<?php if ('job_title' === $key ) : ?>
									<?php if ( $job->post_status == 'publish' ) : ?>
										<a href="<?php echo esc_url(get_permalink( $job->ID )); ?>"><?php wpjm_the_job_title( $job ); ?></a>
									<?php else : ?>
										<?php wpjm_the_job_title( $job ); ?> <small>(<?php the_job_status( $job ); ?>)</small>
									<?php endif; ?>
									<ul class="job-dashboard-actions">
										<?php
											$actions = array();

											switch ( $job->post_status ) {
												case 'publish' :
													$actions['edit'] = array( 'label' => __( 'Edit', 'robolist-lite' ), 'nonce' => false );
													$actions['duplicate'] = array( 'label' => __( 'Duplicate', 'robolist-lite' ), 'nonce' => true );
													break;
												case 'expired' :
													if ( job_manager_get_permalink( 'submit_job_form' ) ) {
														$actions['relist'] = array( 'label' => __( 'Relist', 'robolist-lite' ), 'nonce' => true );
													}
													break;
												case 'pending_payment' :
												case 'pending' :
													if ( job_manager_user_can_edit_pending_submissions() ) {
														$actions['edit'] = array( 'label' => __( 'Edit', 'robolist-lite' ), 'nonce' => false );
													}
												break;
											}

											$actions['delete'] = array( 'label' => __( 'Delete', 'robolist-lite' ), 'nonce' => true );
											$actions           = apply_filters( 'job_manager_my_job_actions', $actions, $job );

											foreach ( $actions as $action => $value ) {
												$action_url = add_query_arg( array( 'action' => $action, 'job_id' => $job->ID ) );
												if ( $value['nonce'] ) {
													$action_url = wp_nonce_url( $action_url, 'job_manager_my_job_actions' );
												}
												echo '<li><a href="' . esc_url( $action_url ) . '" class="job-dashboard-action-' . esc_attr( $action ) . '">' . esc_html( $value['label'] ) . '</a></li>';
											}
										?>
									</ul>
								<?php elseif ('date' === $key ) : ?>
									<?php echo esc_html(date_i18n( get_option( 'date_format' ), strtotime( $job->post_date ) )); ?>
								<?php elseif ('expires' === $key ) : ?>
									<?php echo esc_html($job->_job_expires ? date_i18n( get_option( 'date_format' ), strtotime( $job->_job_expires ) ) : '&ndash;'); ?>
								<?php else : ?>
									<?php do_action( 'job_manager_job_dashboard_column_' . $key, $job ); ?>
								<?php endif; ?>
							</td>
						<?php endif; endforeach; ?>
					</tr>
				<?php endforeach; ?>
			<?php endif; ?>
		</tbody>
	</table>
	<?php get_job_manager_template( 'pagination.php', array( 'max_num_pages' => $max_num_pages ) ); ?>
</div>
