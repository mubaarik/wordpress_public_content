<?php

class AWPCP_Comment {

    const STATUS_ACTIVE = 'active';
    const STATUS_FLAGGED = 'flagged';

    public $id;
    public $ad_id;
    public $user_id;
    public $author_name;
    public $title;
    public $comment;
    public $status;
    public $created;
    public $updated;
    public $is_spam;

    private $user = null;

    public function __construct( $attributes ) {
        $this->id = $attributes['id'];
        $this->ad_id = $attributes['ad_id'];
        $this->user_id = $attributes['user_id'];
        $this->author_name = $attributes['author_name'];
        $this->title = $attributes['title'];
        $this->comment = $attributes['comment'];
        $this->status = $attributes['status'];
        $this->created = $attributes['created'];
        $this->updated = $attributes['updated'];
        $this->is_spam = $attributes['is_spam'];
    }

    private function get_user() {
        if (is_null($this->user) && $this->user_id) {
            $this->user = get_userdata($this->user_id);
        }
        return $this->user;
    }

    public function get_author_name() {
        $this->get_user();
        if (!is_null($this->user)) {
            $name = trim($this->user->first_name . ' ' . $this->user->last_name);
            $name = empty($name) ? $this->user->display_name : $name;
            $name = empty($name) ? $this->user->user_login : $name;
            return $name;
        }
        return $this->author_name;
    }

    public function get_author_email() {
        $this->get_user();
        if (!is_null($this->user)) {
            return $this->user->user_email;
        }
        return '';
    }

    public function get_created_date($format=null) {
        $format = $format ? $format : awpcp_get_datetime_format();
        return $this->format_date($this->created, $format);
    }

    public function get_human_readable_status() {
        if ( $this->is_flagged() ) {
            return _x( 'Flagged', 'comment status', 'awpcp-comments-ratings' );
        } else if ( $this->status == self::STATUS_ACTIVE ) {
            return _x(  'Active', 'comment status', 'awpcp-comments-ratings' );
        }
    }

    public function get_status() {
        return $this->status;
    }

    public function is_flagged() {
        return $this->status == self::STATUS_FLAGGED;
    }

    /**
     * @param $date string  a MySQL Date
     */
    public function format_date($date, $format) {
        return date_i18n($format, strtotime($date));
    }
}
