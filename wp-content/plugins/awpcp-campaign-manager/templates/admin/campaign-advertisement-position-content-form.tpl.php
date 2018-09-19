<div class="awpcp-one-half">
    <?php $uuid = uniqid(); ?>
    <div id="awpcp-advertisement-position-content-form-<?php echo esc_attr( $campaign_position->get_slug() ); ?>" class="awpcp-advertisement-position-content-form postbox" position-slug="<?php echo esc_attr( $campaign_position->get_slug() ); ?>">
        <?php echo awpcp_html_postbox_handle( array( 'content' => esc_html( $campaign_position->get_name() ) ) ); ?>
        <div class="inside">
            <form action="<?php echo esc_attr( admin_url( 'admin-ajax.php' ) ); ?>" method="post">
                <input type="hidden" name="action" value="update-advertisement-position-content">
                <input type="hidden" name="campaign_id" value="<?php echo esc_attr( $campaign_data['id'] ); ?>">
                <input type="hidden" name="position" value="<?php echo esc_attr( $campaign_position->get_slug() ); ?>">

                <div class="awpcp-advertisement-position-content-type-field">
                    <label><?php echo esc_html( __( 'Content Type:', 'awpcp-campaign-manager' ) ); ?></label>
                    <input id="awpcp-advertisement-position-content-type-image-<?php echo $uuid; ?>" type="radio" name="content_type" value="image"<?php echo $campaign_position->is_image() ? ' checked="checked"' : ''; ?>>
                    <label for="awpcp-advertisement-position-content-type-image-<?php echo $uuid; ?>" class="awpcp-field-label"><?php echo esc_html( __( 'Image', 'awpcp-campaign-manager' ) ); ?></label>
                    <input id="awpcp-advertisement-position-content-type-text-<?php echo $uuid; ?>" type="radio" name="content_type" value="text"<?php echo $campaign_position->is_custom_content() ? ' checked="checked"' : ''; ?>>
                    <label for="awpcp-advertisement-position-content-type-text-<?php echo $uuid; ?>" class="awpcp-field-label"><?php echo esc_html( __( 'PHP/HTML/Text', 'awpcp-campaign-manager' ) ); ?></label>
                </div>

                <div class="awpcp-advertisement-position-upload-field is-hidden">
                    <?php $image_url = $campaign_position->get_image_url(); ?>
                    <?php if ( ! empty( $image_url ) ): ?>
                    <img class="awpcp-advertisement-position-content-image" src="<?php echo esc_attr( $image_url ); ?>">
                    <div class="awpcp-advertisement-position-image-link-field">
                        <label for="awpcp-advertisement-position-image-link"><?php echo esc_html( __( 'Image Link', 'awpcp-campaign-manager' ) ); ?>:</label>
                        <input id="awpcp-advertisement-position-image-link" type="text" name="image_link" value="<?php echo esc_attr( $campaign_position->get_image_link() ); ?>">
                    </div>
                    <?php else: ?>
                    <p><?php echo esc_html( __( 'There is no image defined for this position yet.', 'awpcp-campaign-manager' ) ); ?></p>
                    <?php endif; ?>

                    <?php
                        $width = $campaign_position->get_width();
                        $height = $campaign_position->get_height();

                        $message = __( 'This advertisement position supports images with the following dimensions: <width>x<height>px. Images with different dimensions will be scaled down in the browser.', 'awpcp-campaign-manager' );
                        $message = str_replace( '<width>', $width, $message );
                        $message = str_replace( '<height>', $height, $message );
                    ?>
                    <p><?php echo esc_html( $message ); ?></p>
                    <input type="file" name="image">
                    <p class="submit">
                        <input class="button-primary" type="submit" value="<?php echo esc_attr( __( 'Update', 'awpcp-campaign-manager' ) ); ?>"><span class="spinner awpcp-inline-form-spinner">
                    </p>
                </div>

                <div class="awpcp-advertisement-position-content-field is-hidden">
                    <textarea name="content" cols="58" rows="5"><?php echo esc_textarea( $campaign_position->get_content() ); ?></textarea>
                    <input type="hidden" name="is_executable" value="0">
                    <label><input name="is_executable" type="checkbox" value="1"<?php echo $campaign_position->is_content_executable() ? ' checked="checked"' : ''; ?>><?php echo esc_html( __( 'Execute content as PHP code', 'awpcp-campaign-manager' ) ); ?></label>
                    <p class="submit">
                        <input class="button-primary" type="submit" value="<?php echo esc_attr( __( 'Update', 'awpcp-campaign-manager' ) ); ?>"><span class="spinner awpcp-inline-form-spinner">
                    </p>
                </div>
            </form>
        </div>
    </div>
</div>
