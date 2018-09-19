<?php

require_once( AWPCP_DIR . '/includes/helpers/admin-page.php' );

require_once(AWPCP_COUPONS_MODULE_DIR . '/admin/admin-panel-coupons-table.php');

class AWPCP_AdminCoupons extends AWPCP_AdminPageWithTable {

    public function __construct() {
        $page = 'awpcp-admin-coupons';
        $title = awpcp_admin_page_title( __( 'Manage Coupons', 'awpcp-coupons' ) );
        parent::__construct($page, $title, __('Coupons/Discounts', 'awpcp-coupons' ));

        add_action('wp_ajax_awpcp-add-coupon', array($this, 'ajax'));
        add_action('wp_ajax_awpcp-edit-coupon', array($this, 'ajax'));
        add_action('wp_ajax_awpcp-delete-coupon', array($this, 'ajax'));
    }

    public function scripts() {
        wp_enqueue_style('awpcp-coupons-admin');
        wp_enqueue_script('awpcp-coupons-admin');
    }

    public function get_table() {
        if ( is_null( $this->table ) ) {
            $this->table = new AWPCP_CouponsTable( $this, array( 'screen' => 'classifieds_page_awpcp-admin-coupons' ) );
        }
        return $this->table;
    }

    public function actions($fee, $filter=false) {
        $actions = array();
        $actions['edit'] = array(__('Edit', 'awpcp-coupons' ), $this->url(array('action' => 'edit', 'id' => $fee->id)));
        $actions['trash'] = array(__('Delete', 'awpcp-coupons' ), $this->url(array('action' => 'delete', 'id' => $fee->id)));

        if ( is_array( $filter ) ) {
            $actions = array_intersect_key( $actions, array_combine( $filter, $filter ) );
        }

        return $actions;
    }

    public function dispatch() {
        global $wpdb, $awpcp;

        if (strcmp(awpcp_post_param('action'), 'update-settings') == 0) {
            $enabled = awpcp_post_param('use-coupon-system', 0);
            $awpcp->settings->update_option(AWPCP_OPTION_USE_COUPON_SYSTEM, intval($enabled));
        }

        $is_coupons_system_enabled = get_awpcp_option(AWPCP_OPTION_USE_COUPON_SYSTEM) == 1;

        // normal behaviour: show table of coupons

        $table = $this->get_table();
        $table->prepare_items();

        $template = AWPCP_COUPONS_MODULE_DIR . '/admin/templates/admin-panel-coupons.tpl.php';

        echo $this->render( $template, compact( 'is_coupons_system_enabled', 'table' ) );
    }

    public function ajax() {
        $action = awpcp_post_param('action');

        if (isset($_POST['remove'])) {
            $result = AWPCP_Coupon::delete($_POST['id']);
            if ($result === true) {
                $response = json_encode(array('status' => 'success'));
            } else if ($result === false) {
                $response = json_encode(array('status' => 'error', 'message' => __('The element couldn\'t be deleted.', 'awpcp-coupons' )));
            } else {
                $response = json_encode(array('status' => 'error', 'message' => $result));
            }

        } else if (strcmp($action, 'awpcp-delete-coupon') == 0) {
            $columns = 8;

            ob_start();
                include(AWPCP_DIR . '/admin/templates/delete_form.tpl.php');
                $html = ob_get_contents();
            ob_end_clean();

            $response = json_encode(array('status' => 'success', 'html' => $html));

        } else if (isset($_POST['save'])) {
            $errors = array();

            $entry = AWPCP_Coupon::from_array( $_POST );

            if ($entry && $entry->save($errors) !== false) {
                $this->get_table();

                ob_start();
                    $this->table->single_row($entry);
                    $html = ob_get_contents();
                ob_end_clean();

                $response = json_encode(array('status' => 'success', 'html' => $html));
            } else {
                $response = json_encode(array(
                                'status' => 'error',
                                'message' => __('The form has errors', 'awpcp-coupons' ),
                                'errors' => $errors));
            }

        } else {
            $entry = isset($_POST['id']) ? AWPCP_Coupon::find_by_id($_POST['id']) : null;
            ob_start();
                include(AWPCP_COUPONS_MODULE_DIR . '/admin/templates/coupons-form.tpl.php');
                $html = ob_get_contents();
            ob_end_clean();
            $response = json_encode(array('html' => $html));
        }

        header('Content-Type: application/json');
        echo $response;
        exit();
    }
}
