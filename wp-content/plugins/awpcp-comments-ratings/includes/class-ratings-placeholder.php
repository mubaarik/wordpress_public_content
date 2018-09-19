<?php

/**
 * @since 3.2.3
 */
function awpcp_ratings_placeholder() {
    return new AWPCP_RatingsPlaceholder( AWPCP_Ratings_Controller::instance(),
                                         awpcp()->settings,
                                         awpcp()->js );
}

/**
 * @since 3.2.3
 */
class AWPCP_RatingsPlaceholder {

    private $ratings;
    private $settings;
    private $js;

    public function __construct( $ratings, $settings, $js ) {
        $this->ratings = $ratings;
        $this->settings = $settings;
        $this->js = $js;
    }

    public function placeholder( $ad, $placeholder, $context ) {
        $this->enqueue_scripts_and_styles();

        $message = _x( 'Thanks!', 'raty js', 'awpcp-comments-ratings' );
        $this->js->localize( 'raty', 'thank-you-message', $message );

        $message = _x( "Your rating couldn't be saved.", 'raty js', 'awpcp-comments-ratings' );
        $this->js->localize( 'raty', 'error-message', $message );

        return $this->render_rating( $ad->ad_id, $placeholder );
    }

    private function enqueue_scripts_and_styles() {
        wp_enqueue_style( 'awpcp-comments-ratings' );
        wp_enqueue_script( 'awpcp-comments-ratings' );
    }

    private function render_rating( $ad_id, $placeholder ) {

        if( $placeholder == 'ratings_readonly' ){
            $read_only = true;
        }else{
            $read_only = ! $this->ratings->current_user_can_rate_ad( $ad_id );
        }

        $data = array(
            'ad-id' => $ad_id,
            'rating' => $this->ratings->get_ad_rating( $ad_id ),
            'count' => $this->ratings->get_ad_ratings_count( $ad_id ),
            'images-path' => AWPCP_COMMENTS_MODULE_IMAGES_URL,
            'images-base' => $this->settings->get_option( 'ratings-iconset' ),
            'read-only' => $read_only,
        );

        foreach ( $data as $name => $value ) {
            $attributes[] = sprintf( 'data-%s="%s"', $name, $value );
        }

        $template = '<div class="awpcp-ad-rating"><login-required><span class="awpcp-ad-rating-stars" %s></span><ratings-count></ratings-count><span class="awpcp-ad-rating-message"></span></div>';

        if ( $this->settings->get_option( 'ratings-show-count' ) ) {
            $template = str_replace( '<ratings-count></ratings-count>', '<span class="awpcp-ad-rating-count-container">(<span class="awpcp-ad-rating-count"></span>)</span>', $template );
        } else {
            $template = str_replace( '<ratings-count></ratings-count>', '', $template );
        }

        $template = sprintf( $template, join( ' ', $attributes ) );

        if ( $this->settings->get_option( 'ratings-require-user-registration' ) && ! is_user_logged_in() && $placeholder != 'ratings_readonly' ) {
            $link = sprintf( '<a href="%s" title="%s">', wp_login_url( awpcp_current_url() ), __( 'Login', 'awpcp-comments-ratings' ) );

            $message = '<span class="awpcp-ad-rating-login-required">%s</span>';
            $message = sprintf( $message, __( 'You have to be <login-link>logged in</a> to rate.', 'awpcp-comments-ratings' ) );
            $message = str_replace( '<login-link>', $link, $message );

            $template = str_replace( '<login-required>', $message, $template );
        } else {
            $template = str_replace( '<login-required>', '', $template );
        }

        return $template;
    }
}
