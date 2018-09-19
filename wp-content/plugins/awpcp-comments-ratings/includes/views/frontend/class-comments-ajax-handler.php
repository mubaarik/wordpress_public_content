<?php

if ( class_exists( 'AWPCP_AjaxHandler' ) ) {

/**
 * @since 3.2.3
 */
function awpcp_comments_ajax_handler() {
    return new AWPCP_CommentsAjaxHandler( awpcp_request(),
                                          awpcp_ajax_response(),
                                          AWPCP_Comments_Controller::instance(),
                                          awpcp_comments_creator(),
                                          awpcp_comments_renderer() );
}

/**
 * @since 3.2.3
 */
class AWPCP_CommentsAjaxHandler extends AWPCP_AjaxHandler {

    private $request;
    private $comments;
    private $comments_renderer;
    private $comments_creator;

    public function __construct( $request, $response, $comments, $comments_creator, $comments_renderer ) {
        parent::__construct( $response );

        $this->request = $request;
        $this->comments = $comments;
        $this->comments_creator = $comments_creator;
        $this->comments_renderer = $comments_renderer;
    }

    public function ajax() {
        $action = $this->request->param( 'action' );
        $id = $this->request->param( 'id' );

        $comment = $this->comments->find_by_id( $id );

        if ( is_null( $comment ) ) {
            $errors = array( __( "The comment doesn't exist.", 'awpcp-comments-ratings' ) );
            return $this->multiple_errors_response( $errors );
        } else {
            switch( $action ) {
                case 'awpcp-comments-flag-comment':
                    $response = $this->ajax_flag();
                    break;
                case 'awpcp-comments-edit-comment':
                    $response = $this->ajax_edit();
                    break;
                case 'awpcp-comments-delete-comment':
                    $response = $this->ajax_delete();
                    break;
            }
        }

        return $response;
    }

    public function ajax_flag() {
        $id = $this->request->param( 'id' );
        $comment = $this->comments->find_by_id( $id );

        if ( $this->comments->flag( $comment ) ) {
            return $this->success();
        } else {
            return $this->error();
        }
    }

    public function ajax_edit() {
        $current_user = wp_get_current_user();

        $id = $this->request->param( 'id' );
        $comment = $this->comments->find_by_id( $id );
        $errors = array();

        if ( ! $this->comments->user_can_edit_comment( $current_user->ID, $comment ) ) {
            $errors[] = __( 'You are not allowed to edit this comment.', 'awpcp-comments-ratings' );
            $response = $this->multiple_errors_response( $errors );
        } else {
            $response = $this->success( array( 'html' => $this->comments_renderer->render_comments_form( $comment ) ) );
        }

        return $response;
    }

    public function ajax_delete() {
        $current_user = wp_get_current_user();

        $id = $this->request->param( 'id' );
        $confirmed = $this->request->param( 'confirmed', false );

        $comment = $this->comments->find_by_id( $id );
        $errors = array();

        if ( ! $this->comments->user_can_delete_comment( $current_user->ID, $comment ) ) {
            $errors[] = __( 'You are not allowed to delete this Comment.', 'awpcp-comments-ratings' );
            return $this->multiple_errors_response( $errors);
        }

        if ( $confirmed && $this->comments->delete( $comment, $errors ) ) {
            return $this->success();
        } else if ($confirmed) {
            $errors = array( __( 'There was an error trying to delete this Comment.', 'awpcp-comments-ratings' ) );
            return $this->multiple_errors_response( $errors);
        }

        ob_start();
            include( AWPCP_COMMENTS_MODULE_DIR . '/frontend/templates/comments-delete-form.tpl.php' );
            $html = ob_get_contents();
        ob_end_clean();

        return $this->success( array( 'html' => $html ) );
    }
}

}
