<?php

class AWPCP_Ratings_Controller {

    private static $instance = null;

    private $wpdb;
    private $settings;

    public static function instance() {
        global $wpdb;

        if (is_null(self::$instance)) {
            self::$instance = new AWPCP_Ratings_Controller( $wpdb, awpcp()->settings );
        }
        return self::$instance;
    }

    public function __construct( $wpdb, $settings ) {
        $this->wpdb = $wpdb;
        $this->settings = $settings;
    }

    public function rate_ad($user_id, $user_ip, $ad_id, $rating) {
        global $wpdb;

        $where = array(
            'user_id' => intval($user_id),
            'user_ip' => $user_ip,
            'ad_id' => intval($ad_id)
        );

        $data = array_merge($where, array('rating' => floatval($rating)));

        if ($this->user_has_rated_ad($user_id, $user_ip, $ad_id)) {
            $result = $wpdb->update(AWPCP_TABLE_USER_RATINGS, $data, $where);
        } else {
            $result = $wpdb->insert(AWPCP_TABLE_USER_RATINGS, $data);
        }

        return $result !== false;
    }

    public function delete_ad_rating($user_id, $user_ip, $ad_id) {
        global $wpdb;

        $query = 'DELETE FROM ' . AWPCP_TABLE_USER_RATINGS . ' ';
        $query.= 'WHERE user_id = %d AND user_ip = %s AND ad_id = %d';

        return $wpdb->query($wpdb->prepare($query, $user_id, $user_ip, $ad_id)) !== false;
    }

    public function get_ad_rating($ad_id) {
        global $wpdb;

        $query = "SELECT COUNT('rating') AS votes, SUM(rating) AS total ";
        $query.= 'FROM ' . AWPCP_TABLE_USER_RATINGS . ' WHERE ad_id = %d';

        $rating = $wpdb->get_row($wpdb->prepare($query, $ad_id));

        if ($rating->votes > 0)
            return floatval($rating->total) / intval($rating->votes);
        return 0;
    }

    public function user_has_rated_ad($user_id, $user_ip, $ad_id) {
        global $wpdb;

        $query = 'SELECT COUNT(rating) FROM ' . AWPCP_TABLE_USER_RATINGS . ' ';
        $query.= 'WHERE user_id = %d AND user_ip = %s AND ad_id = %d';

        $results = $wpdb->get_var($wpdb->prepare($query, $user_id, $user_ip, $ad_id));

        return $results === false ? false : $results > 0;
    }

    public function get_ad_ratings_count( $ad_id ) {
        $query = 'SELECT COUNT(rating) FROM ' . AWPCP_TABLE_USER_RATINGS . ' WHERE ad_id = %d';
        return $this->wpdb->get_var( $this->wpdb->prepare( $query, $ad_id ) );
    }

    /**
     * @since 3.2.3
     */
    public function current_user_can_rate_ad( $ad_id ) {
        if ( $this->settings->get_option( 'ratings-require-user-registration' ) ) {
            return is_user_logged_in();
        }
        return true;
    }
}
