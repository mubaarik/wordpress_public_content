<form id="awpcp-region-control-selector" class="awpcp-region-control-selector" action="<?php echo esc_attr( $url ); ?>" method="post">
    <p class="legend">
        <?php echo awpcp_region_control_module()->get_current_location() ?><a class="js-handler" href="#"><span></span></a>
    </p>

    <?php if ( $always_open ): ?>
    <div data-collapsible="true" awpcp-keep-open>
    <?php else: ?>
    <div data-collapsible="true" style="display: none">
    <?php endif; ?>
        <p class="help-text">
            <?php _e('', 'awpcp-region-control' ); ?>
        </p>

        <?php
            $options = array(
                'showTextField' => true,
                'maxRegions' => 1,
            );

            $selector = awpcp_multiple_region_selector( $selected_regions, $options );
            echo $selector->render('region-selector', array());
        ?>

        <div class="submit">
            <input class="button" name="clear-location" type="submit" value="<?php _ex('Masax goobtan', 'region selector', 'awpcp-region-control' ) ; ?>" />
            <input class="button" name="set-location" type="submit" value="<?php _ex('Dooro Goobtan', 'region selector', 'awpcp-region-control' ) ; ?>" />
        </div>
    </div>
</form>
