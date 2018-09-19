<?php

if ( class_exists( 'WP_List_Table' ) ) {

class AWPCP_CampaignSectionsTable extends WP_List_Table {

    private $total_items_count = 0;

    private $links_builder;

    public function __construct( $links_builder ) {
        parent::__construct( array(
            'plural' => 'awpcp-campaign-sections-table',
            'screen' => 'awpcp-manage-campaign-sections',
        ) );

        $this->links_builder = $links_builder;
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
            'per_page' => $this->total_items_count,
        ) );

        $this->_column_headers = array( $this->get_columns(), array(), $this->get_sortable_columns() );
    }

    public function get_columns() {
        $columns = array();

        $columns['cb'] = '<input type="checkbox">';
        $columns['category'] = __( 'Category', 'awpcp-campaign-manager' );
        $columns['pages'] = __( 'Pages', 'awpcp-campaign-manager' );
        $columns['positions'] = __( 'Positions', 'awpcp-campaign-manager' );

        return $columns;
    }

    /**
     * TODO: CampaignsTable has an exact copy of this method!
     */
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
        return '<input type="checkbox" value="' . $item->get_id() . '" name="selected[]" />';
    }

    public function column_category( $item ) {
        $category = $item->get_category();
        $category_name = $item->get_category_name();

        if ( $category->parent > 0 ) {
            $parent_category_name = get_adparentcatname( $category->parent );
            $column_value = "$parent_category_name > $category_name";
        } else {
            $column_value = $category_name;
        }

        $links = array(
            'edit' => array(
                _x( 'Edit', 'campaign section actions', 'awpcp-campaign-manager' ),
                '#'
            ),
            'trash' => array(
                _x( 'Delete', 'campaign section actions', 'awpcp-campaign-manager' ),
                '#'
            )
        );

        $row_links = $this->links_builder->build_links( $links );
        $row_actions = $this->row_actions( $row_links );

        return sprintf( '%s %s', esc_html( $column_value ), $row_actions );
    }

    public function column_pages( $item ) {
        return esc_html( $item->get_list_of_pages() );
    }

    public function column_positions( $item ) {
        return esc_html( $item->get_list_of_positions() );
    }

    /**
     * TODO: Create an AWPCP_ListTable that already implements this method
     *      so that we don't have to duplicate the same code in every table
     *      we use.
     */
    public function single_row( $item ) {
        static $row_class = '';

        $row_class = ( $row_class == '' ? ' class="alternate"' : '' );

        echo '<tr' . $row_class . ' data-id="' . esc_attr( $item->get_id() ) . '">';
        echo $this->single_row_columns( $item );
        echo '</tr>';
    }
}

}
