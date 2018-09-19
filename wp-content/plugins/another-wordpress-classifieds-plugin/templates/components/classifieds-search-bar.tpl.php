<div class="awpcp-classifieds-search-bar" data-breakpoints='{"tiny": [0,450]}' data-breakpoints-class-prefix="awpcp-classifieds-search-bar">
    <form action="<?php echo esc_url( $action_url ); ?>" method="GET">
        <input type="hidden" name="awpcp-step" value="dosearch" />
        <div class="awpcp-classifieds-search-bar--query-field">
            <input type="text" name="keywordphrase" />
        </div>
        <div class="awpcp-classifieds-search-bar--submit-button">
            <input class="button" type="submit" value="<?php echo esc_attr( __( 'Raaddi', 'another-wordpress-classifieds-plugin' ) ); ?>" />
        </div>
    </form>
</div>
