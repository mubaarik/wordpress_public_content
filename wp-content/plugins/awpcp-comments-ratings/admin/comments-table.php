<?php

if (class_exists('WP_List_Table')) {

class AWPCP_Comments_Table extends WP_List_Table {

    public function __construct($url=null) {
        $this->url = is_null($url) ? add_query_arg(array()) : $url;
        parent::__construct(array(
            'singular' => __('Comment', 'awpcp-comments-ratings' ),
            'plural' => __('Comments', 'awpcp-comments-ratings' ),
            'ajax' => true,
        ));
    }

    public function get_bulk_actions() {
        return array();
        return array(
            'spam' => _x('Mark as SPAM', 'comments bulk actions', 'awpcp-comments-ratings' ),
            'unspam' => _x('Mark as not SPAM', 'comments bulk actions','awpcp-comments-ratings' )
        );
    }

    public function get_columns() {
        return array(
            'title' => __('Title', 'awpcp-comments-ratings' ),
            'content' => __('Comment', 'awpcp-comments-ratings' ),
            'status' => __('Status', 'awpcp-comments-ratings' ),
            'spam' => __('SPAM', 'awpcp-comments-ratings' ),
            'user' => __('User', 'awpcp-comments-ratings' ),
            'created' => __('Date Created', 'awpcp-comments-ratings' )
        );
    }

    public function get_sortable_columns() {
        return array(
            'status' => 'status',
            'spam' => 'pam',
            'user' => 'user_id',
            'created' => 'created'
        );
    }

    public function get_views() {
        $link = '<a href="%s">%s</a>';

        $views = array();

        $name = _x('All', 'comments table view', 'awpcp-comments-ratings' );
        if (awpcp_request_param('status') == '' && awpcp_request_param('is_spam') === '')
            $views['all'] = sprintf('<strong>%s</strong>', $name);
        else
            $views['all'] = sprintf($link, remove_query_arg(array('status', 'is_spam'), $this->url), $name);

        $name = _x('Active', 'comments table view', 'awpcp-comments-ratings' );
        if (awpcp_request_param('status') == 'active')
            $views['active'] = sprintf('<strong>%s</strong>', $name);
        else
            $views['active'] = sprintf($link, add_query_arg('status', 'active', $this->url), $name);

        $name = _x('Flagged', 'comments table view', 'awpcp-comments-ratings' );
        if (awpcp_request_param('status') == 'flagged')
            $views['flagged'] = sprintf('<strong>%s</strong>', $name);
        else
            $views['flagged'] = sprintf($link, add_query_arg('status', 'flagged', $this->url), $name);

        $name = _x('SPAM', 'comments table view', 'awpcp-comments-ratings' );
        if (awpcp_request_param('is_spam', '') == 1)
            $views['is_spam'] = sprintf('<strong>%s</strong>', $name);
        else
            $views['is_spam'] = sprintf($link, add_query_arg('is_spam', 1, $this->url), $name);

        return $views;
    }

    public function prepare_items() {
        $this->_column_headers = array($this->get_columns(), array(), $this->get_sortable_columns());

        $items_per_page = 10;

        $orders = array('s' => 'status', 'p' => 'is_spam', 'u' => 'user_id', 'c' => 'created');
        $orderby = awpcp_request_param('orderby');

        $params = array(
            'limit' => $items_per_page,
            'offset' => $items_per_page * (awpcp_request_param('paged', 1) - 1),
            'status' => awpcp_request_param('status'),
            'is_spam' => awpcp_request_param('is_spam'),
            'orderby' => empty($orderby) ? false : $orders[$orderby],
            'order' => awpcp_request_param('order')
        );

        if (!awpcp_current_user_is_admin()) {
            $params['user'] = get_current_user_id();
        }

        $comments = AWPCP_Comments_Controller::instance();
        $this->items = $comments->find(array_filter($params));

        $this->set_pagination_args(array('total_items' => $comments->count(), 'per_page' => $items_per_page));
    }

    public function column_title($item) {
        $comments = AWPCP_Comments_Controller::instance();
        $user = wp_get_current_user();

        // $link = '<a href="%s" target="_blank">%s</a>';
        $actions = array();

        if ($item->status == AWPCP_Comment::STATUS_ACTIVE) {
            $actions['flag'] = sprintf('<a href="%s">%s</a>',
                                         add_query_arg('flag', $item->id, $this->url),
                                         _x('Flag', 'comments table', 'awpcp-comments-ratings' ));
        } else {
            $actions['unflag'] = sprintf('<a href="%s">%s</a>',
                                         add_query_arg('unflag', $item->id, $this->url),
                                         _x('Unflag', 'comments table', 'awpcp-comments-ratings' ));
        }

        if ($item->is_spam) {
            $actions['unspam'] = sprintf('<a href="%s">%s</a>',
                                       add_query_arg('unspam', $item->id, $this->url),
                                       _x('Not SPAM', 'comments table', 'awpcp-comments-ratings' ));
        } else {
            $actions['spam'] = sprintf('<a href="%s">%s</a>',
                                       add_query_arg('spam', $item->id, $this->url),
                                       _x('SPAM', 'comments table', 'awpcp-comments-ratings' ));
        }

        $actions['view-ad'] = sprintf('<a href="%s" target="_blank">%s</a>', url_showad($item->ad_id), _x('View Ad', 'comments table', 'awpcp-comments-ratings' ));

        if ($comments->user_can_edit_comment($user->ID, $item)) {
            $actions['edit'] = sprintf('<a href="%s">%s</a>', add_query_arg('edit', $item->id, $this->url), _x('Edit', 'comments table', 'awpcp-comments-ratings' ));
        }

        if ($comments->user_can_delete_comment($user->ID, $item)) {
            $actions['trash'] = sprintf('<a href="%s">%s</a>', add_query_arg('trash', $item->id, $this->url), _x('Delete', 'comments table', 'awpcp-comments-ratings' ));
        }

        return $item->title . $this->row_actions($actions);
    }

    public function column_content($item) {
        // TODO: return excerpt
        return wp_trim_words($item->comment, 10);
    }

    public function column_status($item) {
        return $item->get_human_readable_status();
    }

    public function column_spam($item) {
        if ($item->is_spam)
            return _x('Marked as SPAM', 'comments table column value', 'awpcp-comments-ratings' );
        return _x('Not SPAM', 'comments table column value', 'awpcp-comments-ratings' );
    }

    public function column_user($item) {
        return $item->get_author_name();
    }

    public function column_created($item) {
        return $item->get_created_date();
    }

    public function column_default($item, $column_name) {
        echo $column_name;
    }

    public function single_row($item) {
        static $row_class = '';
        $row_class = ( $row_class == '' ? ' class="alternate"' : '' );

        echo sprintf("<tr id=\"comment-%d\" %s data-id=\"%d\" >", $item->id, $row_class, $item->id);
        echo $this->single_row_columns( $item );
        echo '</tr>';
    }
}

}
