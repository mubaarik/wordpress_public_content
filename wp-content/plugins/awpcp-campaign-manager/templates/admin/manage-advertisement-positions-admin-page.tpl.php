<form action="<?php echo esc_attr( $page_url ); ?>" method="post">
    <?php foreach ( $advertisement_positions as $position => $name ): ?>
    <div class="awpcp-advertisement-position awpcp-row clearfix">
        <div class="awpcp-one-half">
            <h3 class="awpcp-advertisement-position-title"><?php echo esc_html( $name ); ?></h3>
            <p class="awpcp-advertisement-position-description"><?php echo esc_html( $advertisement_positions_descriptions[ $position ] ); ?></p>
        </div>
        <div class="awpcp-one-half">
            <h4 class="awpcp-advertisement-position-banner-dimensions-title"><?php echo esc_html( __( 'Banner Dimensions', 'awpcp-campaign-manager' ) ); ?></h4>
            <label><?php echo esc_html( __( 'Width', 'awpcp-campaign-manager' ) ); ?></label>
            <input type="text" name="advertisement-positions[<?php echo esc_attr( $position ); ?>][width]" value="<?php echo esc_attr( $advertisement_positions_dimensions[ $position ][ 'width' ] ); ?>">
            <label><?php echo esc_html( __( 'Height', 'awpcp-campaign-manager' ) ); ?></label>
            <input type="text" name="advertisement-positions[<?php echo esc_attr( $position ); ?>][height]" value="<?php echo esc_attr( $advertisement_positions_dimensions[ $position ][ 'height' ] ); ?>">
        </div>
    </div>
    <?php endforeach; ?>
    <p class="submit">
        <input class="button-primary" type="submit" value="Save Changes">
    </p>
</form>
