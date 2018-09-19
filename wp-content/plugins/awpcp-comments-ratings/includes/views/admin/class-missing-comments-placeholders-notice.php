<?php

function awpcp_missing_comments_placeholders_notice() {
    return new AWPCP_MissingCommentsPlaceholdersNotice( awpcp()->settings );
}

class AWPCP_MissingCommentsPlaceholdersNotice {

    private $settings;

    public function __construct( $settings ) {
        $this->settings = $settings;
    }

    public function maybe_render() {
        $layout = $this->settings->get_option( 'awpcpshowtheadlayout' );

        $ratings_placeholder_is_required = false;
        $comments_placeholder_is_required = false;

        if ( $this->settings->get_option( 'enable-user-comments' ) ) {
            $comments_placeholder_is_required = $this->placeholder_exists( '$comments', $layout ) === false;
        }

        if ( $this->settings->get_option( 'enable-user-ratings' ) ) {
            $ratings_placeholder_is_required = $this->ratings_placeholder_exists( $layout ) === false;
        }

        if ( $ratings_placeholder_is_required && $comments_placeholder_is_required ) {
            $this->render_comments_and_ratings_notice();
        } else if ( $comments_placeholder_is_required ) {
            $this->render_comments_notice();
        } else if ( $ratings_placeholder_is_required ) {
            $this->render_ratings_notice();
        }
    }

    private function ratings_placeholder_exists( $content ) {
        return $this->placeholder_exists( '$ratings', $content ) || $this->placeholder_exists( '$rating', $content );
    }

    private function placeholder_exists( $placeholder, $content ) {
        return strpos( $content, $placeholder ) !== false;
    }

    private function render_comments_and_ratings_notice() {
        $message = __( "Comments & Ratings are enabled for your Ads, but the required placeholders haven't been added to the template. Please go to the <listings-settings-link>Ad/Listing settings page</a> and enter both '\$comments' and '\$ratings' placeholders as part of the Single Ad page layout option.", 'another-wordpress-classifieds-plugin' );
        $this->render_notice( $message );
    }

    private function render_notice( $message ) {
        $message = str_replace( '<listings-settings-link>' , sprintf( '<a href="%s">', awpcp_get_admin_settings_url( 'listings-settings' ) ), $message );
        echo awpcp_print_error( $message );
    }

    private function render_comments_notice() {
        $message = __( "Comments are enabled for your Ads, but the required placeholder hasn't been added to the template. Please go to the <listings-settings-link>Ad/Listing settings page</a> and enter '\$comments' placeholder as part of the Single Ad page layout option.", 'another-wordpress-classifieds-plugin' );
        $this->render_notice( $message );
    }

    private function render_ratings_notice() {
        $message = __( "Ratings are enabled for your Ads, but the required placeholder hasn't been added to the template. Please go to the <listings-settings-link>Ad/Listing settings page</a> and enter '\$ratings' placeholder as part of the Single Ad page layout option.", 'another-wordpress-classifieds-plugin' );
        $this->render_notice( $message );
    }
}
