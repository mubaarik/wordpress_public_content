<div class="awpcp-region-selector-popup">
    <div class="awpcp-region-selector-popup-current-location">
        <a href="#">
            <span class="awpcp-region-selector-popup-icon ab-icon dashicons-location"></span>
            <span class="awpcp-region-selector-popup-current-location-name"><?php echo $current_location; ?></span>
            <span class="awpcp-region-selector-popup-current-location-change-action"><?php echo esc_html( _x( '(change)', '[change] default location in region selector popup', 'awpcp-region-control' ) ); ?></span>
        </a>
    </div>
    <div class="awpcp-region-selector-popup-selector-container is-hidden">
        <form action="<?php echo esc_attr( $form_url ); ?>" method="post">
            <div class="awpcp-region-selector-popup-form-title"><?php echo esc_html( __( 'Change Location', 'awpcp-region-control' ) ); ?></div>

            <?php echo $region_selector->render( 'region-selector-popup', array(), array() ); ?>

            <input type="hidden" name="set-as-default" value="0">
            <input id="awpcp-region-selector-popup-set-as-default-checkbox" type="checkbox" name="set-as-default" value="1">
            <label for="awpcp-region-selector-popup-set-as-default-checkbox"><?php echo esc_html( __( 'Set as default location.', 'awpcp-region-control' ) ); ?></label>

            <div class="submit">
                <input class="button" name="set-location" type="submit" value="<?php _ex('Set Location', 'region selector', 'awpcp-region-control' ) ; ?>" />
            </div>
        </form>
    </div>
</div>
