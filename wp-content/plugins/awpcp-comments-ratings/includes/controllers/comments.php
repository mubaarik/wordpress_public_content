<?php

class AWPCP_Comments_Controller {

    private static $instance = null;

    private $listings;

    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new AWPCP_Comments_Controller( awpcp_listings_collection() );
        }
        return self::$instance;
    }

    public function __construct( $listings ) {
        $this->listings = $listings;
    }

    private function sanitize($data, &$errors=array()) {
        $data['id'] = intval($data['id']);
        $data['ad_id'] = intval($data['ad_id']);
        $data['user_id'] = intval($data['user_id']);
        $data['author_name'] = wp_kses($data['author_name'], array());
        $data['title'] = wp_kses($data['title'], array());
        $data['comment'] = wp_kses($data['comment'], array());

        $allowed = array('active', 'flagged');
        if (!in_array($data['status'], $allowed)) {;
            $data['status'] = 'active';
        }

        return $data;
    }

    private function validate($comment, &$errors=array()) {
        $must_be_admin = get_awpcp_option('only-admin-can-place-comments');
        if ($must_be_admin && !awpcp_current_user_is_admin()) {
            $errors['form'] = __('Only admin users are allowed to edit or post commments.', 'awpcp-comments-ratings' );
        }

        $ad = AWPCP_Ad::find_by_id($comment->ad_id);
        if (is_null($ad)) {
            $errors['ad_id'] = __("The Ad doesn't exists.", 'awpcp-comments-ratings' );
        }

        if (empty($comment->comment)) {
            $errors['comment'] = __("The Comment text can't be empty. HTML is not allowed.", 'awpcp-comments-ratings' );
        }

        return empty($errors);
    }

    private function get_data_from_array($data) {
        return $this->get_data_from_object((object) $data);
    }

    private function get_data_from_object($object) {
        $data = array(
            'id' => intval( awpcp_get_property( $object, 'id', 0 ) ),
            'ad_id' => intval( awpcp_get_property( $object, 'ad_id', 0 ) ),
            'user_id' => intval( awpcp_get_property( $object, 'user_id', 0 ) ),
            'author_name' => awpcp_get_property($object, 'author_name', ''),
            'title' => awpcp_get_property($object, 'title', ''),
            'comment' => awpcp_get_property($object, 'comment', ''),
            'status' => awpcp_get_property($object, 'status', 'active'),
            'created' => awpcp_get_property($object, 'created', ''),
            'updated' => awpcp_get_property($object, 'updated', ''),
            'is_spam' => intval( awpcp_get_property( $object, 'is_spam', false ) ),
        );

        return $data;
    }

    /**
     * Returns a Comment instance created from
     * data in the passed array.
     */
    public function create_from_array($data, &$sanitized=array(), &$errors=array()) {
        $data = $this->get_data_from_array($data);
        $sanitized = $this->sanitize($data, $errors);

        // for existing Comment, grab original data and overwrite with new data
        if (isset($sanitized['id']) && $sanitized['id'] > 0) {
            $comment = $this->find_by_id($sanitized['id']);
            $sanitized = array_merge($this->get_data_from_object($comment), array_filter($sanitized));
        }

        $comment = new AWPCP_Comment($sanitized);

        if ($this->validate($comment, $errors)) {
            return $comment;
        }
        return $errors;
    }

    /**
     * 1) Comments can be deleted after posting, and edited.
     * We will allow a setting in the admin panel that says
     * "Allow Comment Editing after posting?", if true, they can edit.
     *
     * 2) Comments should be able to be flagged, like ads.
     * Flagged comments should be marked as such, but not deleted.
     *
     * 3) Comments can be deleted by: admin, comment poster (requires registration),
     * and ad owner (requires registration). Comments can be edited by admin (anytime),
     * and by comment poster (with the setting above is true).
     */
    public function user_can_edit_comment($user_id, $comment) {
        $created = strtotime($comment->created);
        $limit = strtotime('+24 hour', $created);
        $now = strtotime(current_time('mysql'));

        // comment can always be edited by admin
        if (awpcp_user_is_admin($user_id))
            return true;

        // editing comments is disabled by setting value
        if (!get_awpcp_option('comments-can-be-edited'))
            return false;

        // comment can be edited by comment poster
        // within 24 hours after being posted
        if ($comment->user_id == $user_id && $now < $limit)
            return true;

        return false;
    }

    public function user_can_delete_comment($user_id, $comment) {
        // comment can always be edited or deleted by admin
        if (awpcp_user_is_admin($user_id))
            return true;

        // Comment can be deleted by comment poster.
        if ( $comment->user_id && $comment->user_id == $user_id ) {
            return true;
        }

        try {
            $ad = $this->listings->get( $comment->ad_id );
        } catch ( AWPCP_Exception $e ) {
            return false;
        }

        // Comment can always be deleted by Ad owner.
        if ( $ad->user_id && $ad->user_id == $user_id ) {
            return true;
        }

        return false;
    }

    public function find_by_ad_id($id, $spam=false) {
        return $this->find(array('ad_id' => intval($id), 'is_spam' => $spam));
    }

    public function find_by_id($id) {
        $comments = $this->find(array('id' => intval($id)));
        if (!empty($comments))
            return array_shift($comments);
        return null;
    }

    public function find($params=array()) {
        global $wpdb;

        $defaults = array(
            'id' => false,
            'ad_id' => false,
            'user'=> false,
            'status' => null,
            'is_spam' => null,
            'user_id' => false,
            'limit' => false,
            'offset' => 0,
            'orderby' => 'created',
            'order' => get_awpcp_option('comments-order') == 'oldest-first' ? 'ASC' : 'DESC',
        );
        $params = wp_parse_args($params, $defaults);

        $conditions = array();
        $valid = array(AWPCP_Comment::STATUS_ACTIVE, AWPCP_Comment::STATUS_FLAGGED);

        if ($params['id']) {
            $conditions[] = 'id = ' . intval($params['id']);
        } else if ($params['ad_id']) {
            $conditions[] = 'ad_id = ' . intval($params['ad_id']);
        }

        if (in_array($params['status'], $valid)) {
            $conditions[] = $wpdb->prepare('status = %s', $params['status']);
        }

        if ($params['is_spam'] === true) {
            $conditions[] = 'is_spam = 1';
        } else if ($params['is_spam'] === false) {
            $conditions[] = 'is_spam = 0';
        }

        if ($params['user']) {
            $query = 'SELECT * FROM ' . AWPCP_TABLE_COMMENTS . ' AS c ';
            $query.= 'LEFT JOIN ' . AWPCP_TABLE_ADS . ' AS a ';
            $query.= 'ON (c.ad_id = a.ad_id) ';

            $conditions[] = $wpdb->prepare('(c.user_id = %1$d OR a.user_id = %1$d)', $params['user']);
        } else {
            $query = 'SELECT * FROM ' . AWPCP_TABLE_COMMENTS . ' ';
            // SELECT c.* FROM wp_awpcp_comments AS c LEFT JOIN wp_awpcp_ads AS a ON (c.ad_id = a.ad_id) WHERE (c.user_id = 2 OR a.user_id = 2)
        }

        if (!empty($conditions)) {
            $query .= 'WHERE ' . join(' AND ', $conditions) . ' ';
        }

        $query.= sprintf("ORDER BY %s %s ", $params['orderby'], $params['order']);

        if ($params['limit'] !== false) {
            $query .= $wpdb->prepare( "LIMIT %d OFFSET %d", $params['limit'], $params['offset'] );
        }

        $results = $wpdb->get_results($query, 'ARRAY_A');

        $comments = array();
        foreach ($results as $item) {
            // we assume comments stored in the database to be both valid and sanitized
            $comments[] = new AWPCP_Comment($item);
        }

        return $comments;
    }

    public function count() {
        global $wpdb;
        return $wpdb->get_var('SELECT COUNT(*) FROM ' . AWPCP_TABLE_COMMENTS);
    }

    public function save($comment) {
        global $wpdb;

        $now = current_time('mysql');

        $errors = array();
        if (!$this->validate($comment, $errors)) {
            return $errors;
        }

        if ($comment->id) {
            $comment->updated = $now;
            $data = $this->get_data_from_object($comment);
            $result = $wpdb->update(AWPCP_TABLE_COMMENTS, $data, array('id' => $comment->id));
        } else {
            $comment->created = $now;
            $comment->updated = $now;
            $data = $this->get_data_from_object($comment);
            $result = $wpdb->insert(AWPCP_TABLE_COMMENTS, $data);
            $comment->id = $wpdb->insert_id;
        }

        return $result !== false;
    }

    public function delete($comment) {
        global $wpdb;

        if (!is_object($comment))
            $comment = $this->find_by_id($comment);

        if (is_null($comment))
            return null;

        $sql = 'DELETE FROM ' . AWPCP_TABLE_COMMENTS . ' WHERE id = %d';
        return $wpdb->query($wpdb->prepare($sql, $comment->id)) !== false;
    }

    private function set_spam_status($comment, $is_spam=false) {
        global $wpdb;

        if (is_object($comment))
            $id = $comment->id;
        else
            $id = $comment;

        $sql = 'UPDATE ' . AWPCP_TABLE_COMMENTS . ' SET is_spam = %d WHERE id = %d';
        $sql = $wpdb->prepare($sql, $is_spam, $id);
        return $wpdb->query($sql) !== false;
    }

    public function spam($comment) {
        $akismet = $this->akismet($comment);

        if (is_null($akismet))
            return;

        $this->set_spam_status($comment, true);
        $akismet->submitSpam();
    }

    public function unspam($comment) {
        $akismet = $this->akismet($comment);

        if (is_null($akismet))
            return;

        $this->set_spam_status($comment, false);
        $akismet->submitHam();
    }

    private function set_comment_status($comment, $status) {
        global $wpdb;

        if (is_object($comment))
            $id = $comment->id;
        else
            $id = $comment;

        $sql = 'UPDATE ' . AWPCP_TABLE_COMMENTS . ' SET status = %s WHERE id = %d';
        $sql = $wpdb->prepare($sql, $status, $id);
        return $wpdb->query($sql) !== false;
    }

    public function flag($comment) {
        return $this->set_comment_status($comment, AWPCP_Comment::STATUS_FLAGGED);
    }

    public function unflag($comment) {
        return $this->set_comment_status($comment, AWPCP_Comment::STATUS_ACTIVE);
    }
}
