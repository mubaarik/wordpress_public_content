<?php

if ( class_exists( 'AWPCP_AjaxHandler' ) ) {

function awpcp_ratings_ajax_handler() {
    return new AWPCP_RatingsAjaxHandler( AWPCP_Ratings_Controller::instance(), awpcp_request(), awpcp_ajax_response() );
}

/**
 * @since 3.2.3
 */
class AWPCP_RatingsAjaxHandler extends AWPCP_AjaxHandler {

    private $ratings;
    private $request;

    public function __construct( $ratings, $request, $response ) {
        parent::__construct( $response );

        $this->ratings = $ratings;
        $this->request = $request;
    }

    public function ajax() {
        $action = $this->request->param( 'action' );
        $ad_id = $this->request->param( 'ad' );
        $rating = $this->request->param( 'rating' );

        if ( $this->ratings->current_user_can_rate_ad( $ad_id ) ) {
            return $this->handle_ajax_request( $action, $ad_id, $rating );
        } else {
            return $this->error();
        }
    }

    private function handle_ajax_request( $action, $ad_id, $rating ) {
        if ( is_user_logged_in() ) {
            $user_id = get_current_user_id();
            $user_ip = '';
        } else {
            $user_id = 0;
            $user_ip = $_SERVER["REMOTE_ADDR"];
        }

        if ( $action == 'awpcp-ratings-rate' ) {
            $success = $this->ratings->rate_ad( $user_id, $user_ip, $ad_id, $rating );
        } else {
            $success = $this->ratings->delete_ad_rating( $user_id, $user_ip, $ad_id );
        }

        if ( $success ) {
            return $this->success( array(
                'rating' => $this->ratings->get_ad_rating( $ad_id ),
                'count' => $this->ratings->get_ad_ratings_count( $ad_id ),
            ) );
        } else {
            return $this->error();
        }
    }
}

}
