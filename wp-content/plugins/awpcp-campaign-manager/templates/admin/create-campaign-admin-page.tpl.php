<div class="metabox-holder">
    <div class="awpcp-campaign-information postbox">
        <?php echo awpcp_html_postbox_handle( array( 'content' => esc_html( __( 'Campaign Information', 'awpcp-campaign-manager' ) ) ) ); ?>
        <div class="inside clearfix">
            <form method="post">
                <div class="awpcp-row">
                    <div class="awpcp-one-third">
                        <?php if ( $campaign_data['id'] ): ?>
                        <div class="awpcp-campaign-name"><?php echo esc_html( sprintf( __( 'Campaign No. %d', 'awpcp-campaign-manager' ), $campaign_data['id'] ) ); ?></div>
                        <?php else: ?>
                        <div class="awpcp-campaign-name"><?php echo esc_html( __( 'New Campaign', 'awpcp-campaign-manager' ) ); ?></div>
                        <?php endif; ?>
                        <div class="awpcp-sales-representative-information">
                            <dl><?php
                                ?><dt><?php echo esc_html( __( 'Sales Rep. Name', 'awpcp-campaign-manager' ) ); ?>:</dt><?php
                                ?><dd><?php echo esc_html( $current_user->display_name ); ?></dd><?php
                                ?><dt><?php echo esc_html( __( 'Sales Rep. ID', 'awpcp-campaign-manager' ) ); ?>:</dt><?php
                                ?><dd><?php echo esc_html( $current_user->ID ); ?></dd><?php
                          ?></dl>
                        </div>
                    </div>

                    <fieldset class="awpcp-campaign-daterange awpcp-one-third">
                        <div class="awpcp-campaign-start-date-container input-text-wrap">
                            <label for="awpcp-campaign-start-date"><?php echo esc_html( __( 'Start Date', 'awpcp-campaign-manager' ) ); ?>:</label>
                            <input id="awpcp-campaign-start-date" type="text" name="start_date" value="<?php echo esc_attr( awpcp_datetime( 'awpcp-date', $campaign_data['start_date'] ) ); ?>">
                        </div>
                        <div class="awpcp-campaign-end-date-container input-text-wrap">
                            <label for="awpcp-campaign-end-date"><?php echo esc_html( __( 'End Date', 'awpcp-campaign-manager' ) ); ?>:</label>
                            <input id="awpcp-campaign-end-date" type="text" name="end_date" value="<?php echo esc_attr( awpcp_datetime( 'awpcp-date', $campaign_data['end_date'] ) ); ?>">
                        </div>
                    </fieldset>

                    <fieldset class="awpcp-one-third">
                        <div class="awpcp-campaign-status">
                        <label class="awpcp-campaign-status-legend"><?php echo esc_html( __( 'Campaign Status', 'awpcp-campaign-manager' ) ); ?>:</label>
                        <input id="awpcp-campaign-status-enabled" type="radio" name="status" value="enabled" <?php echo $campaign_data['status'] === 'enabled' ? 'checked' : ''; ?>><label class="awpcp-campaign-status-label" for="awpcp-campaign-status-enabled"><?php echo esc_html( _x( 'Enabled', 'Campaign Status', 'awpcp-campaign-manager' ) ); ?></label>
                        <input id="awpcp-campaign-status-disabled" type="radio" name="status" value="disabled" <?php echo $campaign_data['status'] === 'disabled' ? 'checked' : ''; ?>><label class="awpcp-campaign-status-label" for="awpcp-campaign-status-disabled"><?php echo esc_html( _x( 'Disabled', 'Campaign Status', 'awpcp-campaign-manager' ) ); ?></label>
                        </div>
                    </fieldset>
                </div>

                <?php foreach ( $hidden as $field => $value ): ?>
                <input type="hidden" name="<?php echo esc_attr( $field ); ?>" value="<?php echo esc_attr( $value ); ?>">
                <?php endforeach; ?>

                <p class="submit">
                    <?php if ( $campaign_data['id'] ): ?>
                    <input  class="button button-primary" type="submit" name="update-campaign-information" value="<?php echo esc_attr( __( 'Save Campaign Information', 'awpcp-campaign-manager' ) ); ?>">
                    <?php else: ?>
                    <input  class="button button-primary" type="submit" name="update-campaign-information" value="<?php echo esc_attr( __( 'Create Campaign', 'awpcp-campaign-manager' ) ); ?>">
                    <?php endif; ?>
                </p>
            </form>
        </div>
    </div>
</div>

<?php if ( is_null( $campaign_data['id'] ) ): ?>
    <?php echo awpcp_print_message( __( 'You have to save the campaign information before being able to enter information about the category, sub-category and ad positions included in this campaign.', 'awpcp-campaign-manager' ) ); ?>
<?php else: ?>

    <?php if ( ! $campaign_data['is_placeholder'] ): ?>
<div class="awpcp-campaign-sections" campaign-id="<?php echo esc_attr( $campaign_data['id'] ); ?>">
    <?php echo awpcp_html_admin_second_level_heading( array( 'content' => esc_html( __( 'Campaign Sections', 'awpcp-campaign-manager' ) ) ) ); ?>

    <?php $label = __( 'Add Campaign Section', 'another-wordpress-classifieds-plugin' ); ?>
    <a class="button-primary add" title="<?php echo esc_attr( $label ); ?>" href="#" accesskey="p"><?php echo esc_html( $label ); ?></a>

    <form method="post">
        <?php echo $campaign_sections_table->display(); ?>
    </form>
</div>
    <?php endif; ?>

<div class="awpcp-campaign-content" campaign-id="<?php echo esc_attr( $campaign_data['id'] ); ?>">
    <?php $heading_content = esc_html( __( 'Campaign Content', 'awpcp-campaign-manager' ) ) . '<span class="awpcp-spinner spinner"></span>'; ?>
    <?php echo awpcp_html_admin_second_level_heading( array( 'content' => $heading_content ) ); ?>

    <?php if ( $campaign_data['is_placeholder'] ): ?>
    <p><?php echo esc_html( __( 'This is a placeholder campaign. Use the forms below to define the default content for the available advertisement positions. The content of the 1st 5 Listings position will be used for all midle positions.', 'awpcp-campaign-manager' ) ); ?></p>
    <?php endif; ?>

    <div class="metabox-holder">
        <div class="awpcp-row">
        <?php $campaign_positions_count = count( $campaign_positions ); ?>

    <?php if ( $campaign_positions_count > 0 ): ?>
        <?php
        for ( $list_index = 0; $list_index < $campaign_positions_count; $list_index = $list_index + 2 ) {
            for ( $column_index = 0; $column_index <= 1; $column_index = $column_index + 1 ) {
                if ( isset( $campaign_positions[ $list_index + $column_index ] ) ) {
                    $campaign_position = $campaign_positions[ $list_index + $column_index ];
                    include( AWPCP_CAMPAIGN_MANAGER_MODULE_DIR . '/templates/admin/campaign-advertisement-position-content-form.tpl.php' );
                } else {
                    echo '<div class="awpcp-one-half"></div>';
                }
            }
        }
        ?>
    <?php else: ?>
            <div class="awpcp-one-half">
                <div class="awpcp-advertisement-position-content-form">
                    <?php echo esc_html( __( 'Please create one campaign section before attempting to add content to this campaign.', 'awpcp-campaign-manager' ) ); ?>
                </div>
            </div>
    <?php endif; ?>
        </div>
    </div>
</div>

<?php endif; ?>
