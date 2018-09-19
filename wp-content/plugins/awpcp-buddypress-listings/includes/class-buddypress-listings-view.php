<?php

function awpcp_buddypress_all_user_listings_view() {
    return new AWPCP_BuddyPressAllUserListingsView( awpcp_listings_collection(), awpcp_listing_renderer(), awpcp_buddypress_wrapper(), awpcp()->settings );
}

function awpcp_buddypress_user_enabled_listings_view() {
    return new AWPCP_BuddyPressUserEnabledListingsView( awpcp_listings_collection(), awpcp_listing_renderer(), awpcp_buddypress_wrapper(), awpcp()->settings );
}

function awpcp_buddypress_user_disabled_listings_view() {
    return new AWPCP_BuddyPressUserDisabledListingsView( awpcp_listings_collection(), awpcp_listing_renderer(), awpcp_buddypress_wrapper(), awpcp()->settings );
}

function awpcp_buddypress_enabled_listings_view() {
    return new AWPCP_BuddyPressEnabledListingsView( awpcp_listings_collection(), awpcp_listing_renderer(), awpcp_buddypress_wrapper(), awpcp()->settings );
}

abstract class AWPCP_BuddyPressListingsView {

    protected $listings;
    protected $listing_renderer;
    protected $buddypress;
    protected $settings;

    protected $items_per_page = 10;
    protected $page = 1;

    protected $current_item_index = 0;

    public $items = array();
    public $total_items = 0;

    public function __construct( $listings, $listing_renderer, $buddypress, $settings ) {
        $this->listings = $listings;
        $this->listing_renderer = $listing_renderer;
        $this->buddypress = $buddypress;
        $this->settings = $settings;
    }

    public function prepare_items( $params = array() ) {
        $params = wp_parse_args( $params, array(
            'user_id' => null,
            'items_per_page' => $this->items_per_page,
            'page' => $this->page,
        ) );

        $this->items_per_page = $params['items_per_page'];
        $this->page = $params['page'];

        $this->items = $this->find_items( $params );
        $this->total_items = $this->count_items( $params );
        $this->items_count = count( $this->items );
    }

    protected abstract function find_items( $params );

    protected abstract function count_items( $params );

    public function render_pagination_count() {
        $start_num  = intval( ( $this->page - 1 ) * $this->items_per_page ) + 1;
        $from_num   = bp_core_number_format( $start_num );
        $to_num     = bp_core_number_format( ( $start_num + ( $this->items_per_page - 1 ) > $this->total_items ) ? $this->total_items : $start_num + ( $this->items_per_page - 1 ) );
        $total      = bp_core_number_format( $this->total_items );
        $pagination = sprintf( _n( 'Viewing %1$s to %2$s (of %3$s listing)', 'Viewing %1$s to %2$s (of %3$s listings)', $total, 'awpcp-buddypress-listings' ), $from_num, $to_num, $total );

        return apply_filters( 'awpcp-buddypress-listings-pagination-count', $pagination );
    }

    public function render_pagination_links() {
        if ( $this->total_items == 0 || $this->items_per_page == 0 ) {
            return '';
        }

        $pagination_links = paginate_links( array(
            'base'      => add_query_arg( 'apage', '%#%' ),
            'format'    => '',
            'total'     => ceil( (int) $this->total_items / (int) $this->items_per_page ),
            'current'   => $this->page,
            'prev_text' => _x( '&larr;', 'Listings pagination previous text', 'awpcp-buddypress-listings' ),
            'next_text' => _x( '&rarr;', 'Listings pagination next text',     'awpcp-buddypress-listings' ),
            'mid_size'  => 1,
        ) );

        return apply_filters( 'awpcp-buddypress-listings-pagination-links', $pagination_links );
    }

    public function render_listing_excerpt( $listing ) {
        $layout = $this->settings->get_option( 'displayadlayoutcode' );

        if ( empty( $layout ) ) {
            $layout = $this->settings->get_option_default_value( 'displayadlayoutcode' );
        }

        return awpcp_do_placeholders( $listing, $layout, 'listings' );
    }

    public function render_user_actions( $listing ) {
        $loggedin_user_id = $this->buddypress->loggedin_user_id();

        $url = $this->listing_renderer->get_view_listing_url( $listing );
        $link = '<a href="%s" class="button view-listing bp-primary-action" id="view-listing-%d">%s</a>';
        $actions[] = sprintf( $link, $url, $listing->ad_id, __( 'View', 'awpcp-buddypress-listings' ) );

        if ( $loggedin_user_id && ( awpcp_user_is_admin( $loggedin_user_id ) || $loggedin_user_id == $listing->user_id ) ) {
            $url = $this->listing_renderer->get_edit_listing_url( $listing );
            $link = '<a href="%s" class="button edit-listing bp-primary-action" id="edit-listing-%d">%s</a>';
            $actions[] = sprintf( $link, $url, $listing->ad_id, __( 'Edit', 'awpcp-buddypress-listings' ) );

            $url = $url = $this->listing_renderer->get_delete_listing_url( $listing );
            $nonce = wp_create_nonce( 'buddypress-delete-listing-' . $listing->ad_id );
            $link = '<a href="%s" class="button delete-listing bp-primary-action" id="delete-listing-%d" data-nonce="%s">%s</a>';
            $actions[] = sprintf( $link, $url, $listing->ad_id, $nonce, __( 'Delete', 'awpcp-buddypress-listings' ) );
        }

        return implode( '', $actions );
    }

    public function has_listings() {
        return $this->items_count > 0 && $this->current_item_index < $this->items_count;
    }

    public function next_listing() {
        if ( $this->has_listings() ) {
            return $this->items[ $this->current_item_index++ ];
        }
    }
}

class AWPCP_BuddyPressAllUserListingsView extends AWPCP_BuddyPressListingsView {

    protected function find_items( $params ) {
        return $this->listings->find_user_listings( $params['user_id'], $params );
    }

    protected function count_items( $params ) {
        return $this->listings->count_user_listings( $params['user_id'] );
    }
}

class AWPCP_BuddyPressUserEnabledListingsView extends AWPCP_BuddyPressListingsView {

    protected function find_items( $params ) {
        return $this->listings->find_user_enabled_listings( $params['user_id'], $params );
    }

    protected function count_items( $params ) {
        return $this->listings->count_user_enabled_listings( $params['user_id'] );
    }
}

class AWPCP_BuddyPressUserDisabledListingsView extends AWPCP_BuddyPressListingsView {

    protected function find_items( $params ) {
        return $this->listings->find_user_disabled_listings( $params['user_id'], $params );
    }

    protected function count_items( $params ) {
        return $this->listings->count_user_disabled_listings( $params['user_id'] );
    }
}

class AWPCP_BuddyPressEnabledListingsView extends AWPCP_BuddyPressListingsView {

    protected function find_items( $params ) {
        return $this->listings->find_enabled_listings( $params );
    }

    protected function count_items( $params ) {
        return $this->listings->count_enabled_listings();
    }
}
