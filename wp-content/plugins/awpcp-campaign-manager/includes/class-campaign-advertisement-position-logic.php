<?php

class AWPCP_CampaignAdvertisementPositionLogic {

    private $advertisement_position;
    private $positions_generator;
    private $settings;

    public function __construct( $advertisement_position, $positions_generator, $settings ) {
        $this->advertisement_position = $advertisement_position;
        $this->positions_generator = $positions_generator;
        $this->settings = $settings;
    }

    public function get_campaign_id() {
        return $this->advertisement_position->campaign_id;
    }

    public function get_slug() {
        return $this->advertisement_position->advertisement_position;
    }

    public function get_name() {
        return $this->positions_generator->get_position_name( $this->get_slug() );
    }

    public function get_width() {
        return $this->positions_generator->get_position_width( $this->get_slug() );
    }

    public function get_height() {
        return $this->positions_generator->get_position_height( $this->get_slug() );
    }

    public function get_content() {
        return $this->advertisement_position->content;
    }

    public function is_content_executable() {
        return $this->advertisement_position->is_executable;
    }

    public function is_image() {
        return $this->advertisement_position->content_type === 'image';
    }

    public function is_custom_content() {
        return $this->advertisement_position->content_type === 'text';
    }

    public function get_image_url() {
        $image_path = $this->advertisement_position->image_path;

        if ( empty( $image_path ) ) {
            return '';
        }

        $uploads_directory_name = $this->settings->get_option( 'uploadfoldername', 'uploads' );
        $url_parts = array( WP_CONTENT_URL, $uploads_directory_name, 'awpcp', $image_path );
        $image_url = implode( '/', $url_parts );

        return $image_url;
    }

    public function get_image_link() {
        return $this->advertisement_position->image_link;
    }
}
