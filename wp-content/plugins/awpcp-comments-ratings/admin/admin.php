<?php

class AWPCP_Comments_Module_Admin {

    const PAGE_SLUG = 'awpcp-panel-comments';

    public function __construct() {
        add_action('awpcp_panel_add_submenu_page', array($this, 'panel_submenu'), 10, 2);
        add_action('admin_init', array($this, 'admin_init'));
    }

    public function panel_submenu($slug, $capability='read') {
        $title = __('Comments', 'awpcp-comments-ratings' );
        $page = add_submenu_page($slug, $title, $title, $capability,  self::PAGE_SLUG, array($this, 'dispatch'));
        add_action('admin_print_styles-' . $page, array($this, 'print_styles'));

        // now let's try to place the Comments menu in the right place
        awpcp_insert_submenu_item_after($slug, self::PAGE_SLUG, 'Manage1');
    }

    public function print_styles() {
        wp_enqueue_script('admin-awpcp-comments');
    }

    public function admin_init() {
        add_action('wp_ajax_awpcp-edit-comment', array($this, 'process_ajax_action'));
        add_action('wp_ajax_awpcp-delete-comment', array($this, 'process_ajax_action'));
        $this->process_action();
    }

    public function process_action() {
        $comments = AWPCP_Comments_Controller::instance();
        $params = array();

        if ($id = awpcp_request_param('spam')) {
            $comments->spam($id);
            awpcp_flash(__('Comment has been marked as SPAM.', 'awpcp-comments-ratings' ));
            $params[] = 'spam';

        } else if ($id = awpcp_request_param('unspam')) {
            $comments->unspam($id);
            awpcp_flash(__('Comment is no longer marked as SPAM.', 'awpcp-comments-ratings' ));
            $params[] = 'unspam';

        } else if ($id = awpcp_request_param('flag')) {
            if ($comments->flag($id))
                awpcp_flash(__('Comment has been flagged.', 'awpcp-comments-ratings' ));
            $params[] = 'flag';

        } else if ($id = awpcp_request_param('unflag')) {
            if ($comments->unflag($id))
                awpcp_flash(__('Comment is no longer flagged.', 'awpcp-comments-ratings' ));
            $params[] = 'unflag';
        }

        if (empty($params))
            return;

        wp_redirect(remove_query_arg($params));
        exit();
    }

    public function process_ajax_action() {
        $action = awpcp_post_param('action');
        $comments = AWPCP_Comments_Controller::instance();

        if (isset($_POST['remove'])) {
            // TODO: verify current user can delete comment
            $result = $comments->delete($_POST['id']);
            if ($result === true) {
                $response = json_encode(array('status' => 'success'));
            } else if ($result === false) {
                $response = json_encode(array('status' => 'error', 'message' => __('The element couldn\'t be deleted.', 'awpcp-comments-ratings' )));
            } else {
                $response = json_encode(array('status' => 'error', 'message' => $result));
            }

        } else if (strcmp($action, 'awpcp-delete-comment') == 0) {
            $columns = 6;
            ob_start();
                include(AWPCP_DIR . '/admin/templates/delete_form.tpl.php');
                $html = ob_get_contents();
            ob_end_clean();
            $response = json_encode( array( 'status' => 'ok', 'html' => $html ) );

        } else if (isset($_POST['save'])) {
            // TODO: verify current user can edit comment
            $sanitized = array();
            $errors = array();

            $request = stripslashes_deep($_POST);
            $entry = $comments->create_from_array($request, $sanitized, $errors);

            // XXX: We need $current_screen initialized to a WP_Screen object 
            // to avoid E_NOTICE and E_WARNING errors when generating the row's 
            // HTML
            global $current_screen;
            $current_screen = convert_to_screen('blah!');

            if (empty($errors) && $comments->save($entry) !== false) {
                $url = add_query_arg('page', self::PAGE_SLUG, admin_url('admin.php?page'));
                $table = new AWPCP_Comments_Table($url);
                ob_start();
                    // include(AWPCP_COMMENTS_MODULE_DIR . '/admin/templates/comments-entry.tpl.php');
                    $table->single_row($entry);
                    $html = ob_get_contents();
                ob_end_clean();
                $response = json_encode(array('status' => 'success', 'html' => $html));
            } else {
                $response = json_encode(array(
                                'status' => 'error',
                                'message' => __('The form has errors', 'awpcp-comments-ratings' ),
                                'errors' => $errors));
            }

        } else {
            $entry = isset($_POST['id']) ? $comments->find_by_id($_POST['id']) : null;
            ob_start();
                include(AWPCP_COMMENTS_MODULE_DIR. '/admin/templates/comment-form.tpl.php');
                $html = ob_get_contents();
            ob_end_clean();
            $response = json_encode(array('html' => $html));
        }

        header('Content-Type: application/json');
        echo $response;
        exit();
    }

    public function dispatch() {
        $table = new AWPCP_Comments_Table();
        $table->prepare_items();

        ob_start();
            include(AWPCP_COMMENTS_MODULE_DIR . '/admin/templates/admin-comments-table.tpl.php');
            $html = ob_get_contents();
        ob_end_clean();

        echo $html;
    }
}
