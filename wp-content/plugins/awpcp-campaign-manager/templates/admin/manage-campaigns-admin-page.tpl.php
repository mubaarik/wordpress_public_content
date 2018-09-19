<form method="post">
    <?php $url = $this->url( array( 'page' => 'awpcp-manage-campaign' ) ); ?>
    <?php $label = __( 'Create Campaign', 'awpcp-campaign-manager' ); ?>
    <div><a class="button-primary" title="<?php echo esc_attr( $label ); ?>" href="<?php echo esc_attr( $url ); ?>" accesskey="c"><?php echo esc_html( $label ); ?></a></div>

    <?php //echo $table->views(); ?>
    <?php //echo $table->search_box( __( 'Search Campaigns', 'awpcp-campaign-manager' ), 'campaigns' ); ?>
    <?php echo $table->display(); ?>
</form>
