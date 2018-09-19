<?php

class AWPCP_CouponsTable extends WP_List_Table {

    private $page;
    private $items_per_page;
    private $total_items;

    public function __construct($page, $args=array()) {
        parent::__construct(wp_parse_args($args, array('plural' => 'awpcp-coupons')));
        $this->page = $page;
    }

    private function parse_query() {
        global $wpdb;

        $user = wp_get_current_user();
        $ipp = (int) get_user_meta($user->ID, 'coupons-items-per-page', true);
        $this->items_per_page = awpcp_request_param('items-per-page', $ipp === 0 ? 10 : $ipp);
        update_user_meta($user->ID, 'coupons-items-per-page', $this->items_per_page);

        $params = shortcode_atts(array(
            'orderby' => '',
            'order' => 'desc',
            'paged' => 1,
        ), $_REQUEST);

        $params['order'] = strcasecmp($params['order'], 'DESC') === 0 ? 'DESC' : 'ASC';
        $params['pages'] = intval( $params['paged'] );

        switch($params['orderby']) {
            case 'id':
                $orderby = sprintf('id %1$s, code', $params['order']);
                break;

            case 'coupon':
                $orderby = 'code';
                break;

            case 'discount':
                $orderby = sprintf('type %1$s, discount %1$s, code', $params['order']);
                break;

            case 'redemption-limit':
                $orderby = sprintf( 'redemption_limit %1$s, code', $params['order'] );
                break;

            case 'redemption-count':
                $orderby = sprintf('redemption_count %1$s, code', $params['order']);
                break;

            case 'expire':
                $orderby = sprintf('expire_date %1$s, code', $params['order']);
                break;

            case 'active':
                $orderby = sprintf('enabled %1$s, code', $params['order']);
                break;

            default:
                $orderby = 'code';
                break;
        }

        return array(
            'orderby' => $orderby,
            'order' => $params['order'],
            'offset' => $this->items_per_page * ($params['paged'] - 1),
            'limit' => $this->items_per_page
        );
    }

    public function prepare_items() {
        $query = $this->parse_query();
        $this->items = AWPCP_Coupon::query( $query );
        $this->total_items = AWPCP_Coupon::query( array_merge( $query, array( 'fields' => 'count' ) ) );

        $this->set_pagination_args(array(
            'total_items' => $this->total_items,
            'per_page' => $this->items_per_page
        ));

        $this->_column_headers = array($this->get_columns(), array(), $this->get_sortable_columns());
    }

    public function has_items() {
        return count($this->items) > 0;
    }

    public function get_columns() {
        $columns = array();

        $columns['cb'] = '<input type="checkbox" />';
        $columns['id'] = __( 'Coupon ID', 'awpcp-coupons' );
        $columns['coupon'] = __( 'Coupon Code', 'awpcp-coupons' );
        $columns['discount'] = __( 'Discount Value', 'awpcp-coupons' );
        $columns['redemption_limit'] = __( 'Redemption Limit', 'awpcp-coupons' );
        $columns['redemption_count'] = __( 'Redemption Count', 'awpcp-coupons' );
        $columns['expire'] = __( 'Expire Date', 'awpcp-coupons' );
        $columns['active'] = __( 'Active', 'awpcp-coupons' );

        return $columns;
    }

    public function get_sortable_columns() {
        $columns = array(
            'id' => array('id', true),
            'coupon' => array('coupon', true),
            'discount' => array('discount', true),
            'redemption_limit' => array('redemption-limit', true),
            'redemption_count' => array('redemption-count', true),
            'expire' => array('expire', true),
            'active' => array('active', true),
        );

        return $columns;
    }

    private function get_row_actions($item) {
        $actions = $this->page->actions($item);
        return $this->page->links($actions);
    }

    public function column_cb($item) {
        return '<input type="checkbox" value="' . $item->id . '" name="selected[]" />';
    }

    public function column_id($item) {
        return $item->id;
    }

    public function column_coupon($item) {
        return $item->code . $this->row_actions( $this->get_row_actions( $item ) );
    }

    public function column_discount($item) {
        if ( strcmp( $item->type, 'amount' ) == 0 ) {
            return sprintf("%0.2f", $item->discount);
        } else {
            echo sprintf("%0.1f%%", $item->discount);
        }
    }

    public function column_redemption_limit($item) {
        return $item->redemption_limit;
    }

    public function column_redemption_count($item) {
        return $item->redemption_count;
    }

    public function column_expire($item) {
        return $item->get_expire_date();
    }

    public function column_active($item) {
        return $item->enabled ? __('Yes', 'awpcp-coupons' ) : __('No', 'awpcp-coupons' );
    }

    public function single_row($item) {
        static $row_class = '';
        $row_class = ( $row_class == '' ? ' class="alternate"' : '' );

        echo '<tr id="coupon-' . $item->id . '" data-id="' . $item->id . '"' . $row_class . '>';
        echo $this->single_row_columns( $item );
        echo '</tr>';
    }
}
