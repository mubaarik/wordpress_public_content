<?php

if ( class_exists( 'WP_List_Table' ) ) {

class AWPCP_CampaignsTable extends WP_List_Table {

    private $total_items_count = 0;

    private $page;
    private $users;
    private $links_builder;
    private $request;

    public function __construct( $page, $users, $links_builder, $request ) {
        parent::__construct( array(
            'plural' => 'awpcp-campaigns',
        ) );

        $this->page = $page;
        $this->users = $users;
        $this->links_builder = $links_builder;
        $this->request = $request;
    }

    public function set_items( $items ) {
        $this->items = $items;
    }

    public function set_total_items_count( $items_count ) {
        $this->total_items_count = $items_count;
    }

    public function prepare_items() {
        if ( is_null( $this->items ) ) {
            throw new AWPCP_Exception( 'Table is not ready! Please set table items using set_items() method.', 'awpcp-campaign-manager' );
        }

        $this->set_pagination_args( array(
            'total_items' => $this->total_items_count,
            'per_page' => $this->get_items_per_page( 'campaigns-items-per-page' ),
        ) );

        $this->_column_headers = array( $this->get_columns(), array(), $this->get_sortable_columns() );
    }

    public function get_items_per_page( $option, $default = 20 ) {
        $stored_valued = $this->get_items_per_page_from_user_option( $option, $default );
        $items_per_page = $this->request->param( 'items-perp-page', $stored_valued );

        update_user_option( $this->request->get_current_user()->ID, $option, $items_per_page );

        return $items_per_page;
    }

    /**
     * An exact copy of the get_items_per_page method of WP_List_Table that is
     * available since 3.1.0.
     *
     * Once WP 3.1.0 becomes the minimum supported version this method can go away.
     */
    private function get_items_per_page_from_user_option( $option, $default = 20 ) {
        $per_page = (int) get_user_option( $option );
        if ( empty( $per_page ) || $per_page < 1 )
            $per_page = $default;

        return (int) apply_filters( $option, $per_page );
    }

    public function get_columns() {
        $columns = array();

        $columns['cb'] = '<input type="checkbox">';
        $columns['campaign_id'] = __( 'Campaign ID', 'awpcp-campaign-manager' );
        $columns['sales_representative'] = __( 'Sales Rep.', 'awpcp-campaign-manager' );
        $columns['sales_representative_id'] = __( 'Sales Rep. ID', 'awpcp-campaign-manager' );
        $columns['start_date'] = __( 'Start Date', 'awpcp-campaign-manager' );
        $columns['end_date'] = __( 'End Date', 'awpcp-campaign-manager' );
        $columns['status'] = __( 'Status', 'awpcp-campaign-manager' );

        return $columns;
    }

    public function get_sortable_columns() {
        // $columns = array_diff_key( $this->get_columns(), array( 'cb' => null ) );

        // $sortable_columns = array();
        // foreach ( $columns as $column_id => $column_name ) {
        //     $sortable_columns[ $column_id ] = array( $column_id, true );
        // }

        // return $sortable_columns;
        return array();
    }

    public function column_cb( $item ) {
        return '<input type="checkbox" value="' . $item->id . '" name="selected[]" />';
    }

    public function column_campaign_id( $item ) {
        $links = array(
            'edit' => array(
                _x( 'Edit', 'manage-campaign-actions', 'awpcp-campaign-manager' ),
                $this->page->url( array(
                    'page' => 'awpcp-manage-campaign',
                    'action' => 'edit',
                    'campaign' => $item->id
                ) ),
            ),
            'trash' => array( _x( 'Delete', 'manage-campaign-actions', 'awpcp-campaign-manager' ), '#' ),
        );

        $row_links = $this->links_builder->build_links( $links );
        $row_actions = $this->row_actions( $row_links );

        if ( $item->is_placeholder ) {
            return sprintf( '%d - %s %s', $item->id, '<strong>' . __( 'Placeholder', 'awpcp-campaign-manager' ) . '</strong>', $row_actions );
        } else {
            return sprintf( '%d %s', $item->id, $row_actions );
        }
    }

    public function column_sales_representative( $item ) {
        return esc_html( $item->sales_representative_id );
    }

    public function column_sales_representative_id( $item ) {
        try {
            $sales_representative = $this->users->get( $item->sales_representative_id, array( 'public_name' ) );
            $sales_representative_name = esc_html( $sales_representative->public_name );
        } catch ( AWPCP_Exception $e ) {
            $sales_representative_name = esc_html( __( "User not found!", 'awpcp-campaign-manager' ) );
            $sales_representative_name = '<em>' . $sales_representative_name . '</em>';
        }

        return $sales_representative_name;
    }

    public function column_start_date( $item ) {
        return awpcp_datetime( 'awpcp-date', $item->start_date );
    }

    public function column_end_date( $item ) {
        return awpcp_datetime( 'awpcp-date', $item->end_date );
    }

    public function column_status( $item ) {
        return $item->status == 'enabled' ? _x( 'Enabled', 'campaign-status', 'awpcp-campaign-manager' ) : _x( 'Disabled', 'campaign-status', 'awpcp-campaign-manager' );
    }

    /**
     * TODO: Create an AWPCP_ListTable that already implements this method
     *      so that we don't have to duplicate the same code in every table
     *      we use.
     */
    public function single_row( $item ) {
        static $row_class = '';

        $row_class = ( $row_class == '' ? ' class="alternate"' : '' );

        echo '<tr' . $row_class . ' data-id="' . esc_attr( $item->id ) . '">';
        echo $this->single_row_columns( $item );
        echo '</tr>';
    }
}

}
