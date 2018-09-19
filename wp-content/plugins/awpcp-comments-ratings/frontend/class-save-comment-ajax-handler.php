<?php

function awpcp_save_comment_ajax_handler() {
    return new AWPCP_SaveCommentAjaxHandler(
        AWPCP_Comments_Controller::instance(),
        awpcp_comments_creator(),
        awpcp_comments_notifications(),
        awpcp_request(),
        awpcp_ajax_response()
    );
}

abstract class AWPCP_CommentActionAjaxHandler extends AWPCP_AjaxHandler {

    protected $comments;
    protected $comments_creator;
    protected $notifications;
    protected $request;

    public function __construct( $comments, $comments_creator, $notifications, $request, $response ) {
        parent::__construct( $response );

        $this->comments = $comments;
        $this->comments_creator = $comments_creator;
        $this->notifications = $notifications;
        $this->request = $request;
    }

    public function ajax() {
        $comment_id = $this->request->param( 'id' );
        $comment = $this->comments->find_by_id( $comment_id );

        if ( is_null( $comment ) ) {
            $errors = array( __( "The comment doesn't exist.", 'awpcp-comments-ratings' ) );
            return $this->multiple_errors_response( $errors );
        } else {
            return $this->handle_ajax_request( $comment );
        }
    }

    protected abstract function handle_ajax_request( $comment );
}

class AWPCP_SaveCommentAjaxHandler extends AWPCP_CommentActionAjaxHandler {

    public function handle_ajax_request( $comment ) {
        $current_user = $this->request->get_current_user();

        $data = stripslashes_deep( array(
            'id' => $this->request->param( 'comment-id', 0 ),
            'ad_id' => $this->request->param( 'comment-ad-id', 0 ),
            'user_id' => $current_user->ID,
            'author_name' => $this->request->param( 'comment-author', '' ),
            'title' => $this->request->param( 'comment-title', '' ),
            'comment' => $this->request->param( 'comment-comment', '' ),
            'status' => $this->request->param( 'comment-status', '' ),
        ) );

        $errors = array();

        try {
            $item = $modified_comment = $this->comments_creator->create_or_update( $data );
        } catch ( AWPCP_Exception $e ) {
            $errors = $e->get_errors();
        }

        if ( is_null( $modified_comment ) ) {
            return $this->multiple_errors_response( $errors );
        }

        if ( empty( $data['id'] ) ) {
            $this->notifications->send_comment_posted_notifications( $modified_comment );
        } else {
            $this->notifications->send_comment_edited_notifications( $modified_comment );
        }

        ob_start();
            $i = 1; // comments-list-item expects to be called inside a loop, $i is the index variable.
            $comments = $this->comments;
            include( AWPCP_COMMENTS_MODULE_DIR . '/frontend/templates/comments-list-item.tpl.php' );
            $html = ob_get_contents();
        ob_end_clean();

        return $this->success( array( 'html' => $html ) );
    }
}
