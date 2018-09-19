<?php

class AWPCP_AdvertisementPlaceholderWidget extends WP_Widget {

    public function __construct() {
        $id = 'awpcp-advertisement-placeholder';
        $name = __( 'Advertisement Placeholder', 'awpcp-campaign-manager' );
        $description = __( 'Shows advertisements in Sidebar 1, Sidebar 2 or Footer advertisement positions.', 'awpcp-campaign-manager' );

        parent::__construct( $id, $name, array( 'description' => $description) );
    }

    public function widget( $args, $instance ) {
        if ( isset( $instance['position'] ) && ! empty( $instance['position'] ) ) {
            $placeholder_service = awpcp_insert_advertesiment_placeholder_service();
            $placeholder = $placeholder_service->get_advertisement_placeholder( $instance['position'] );
        } else {
            $placeholder = '';
        }

        echo $args['before_widget'] . $placeholder . $args['after_widget'];
    }

    public function update( $new_instance, $old_instance ) {
        $allowed_positions = array( 'sidebar-one', 'sidebar-two', 'footer' );

        if ( ! in_array( $new_instance['position'], $allowed_positions ) ) {
            $new_instance['position'] = $old_instance['position'];
        }

        return $new_instance;
    }

    public function form( $instance ) {
        $instance = wp_parse_args( $instance, array( 'position' => '' ) );
        include( AWPCP_CAMPAIGN_MANAGER_MODULE_DIR . '/templates/admin/advertisement-placeholder-widget-form.tpl.php' );
    }
}
