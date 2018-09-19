<?php

function awpcp_comments_placeholder() {
    return new AWPCP_CommentsPlaceholder(
        awpcp_request(),
        awpcp()->settings,
        awpcp_comments_creator(),
        awpcp_comments_renderer(),
        awpcp_comments_notifications()
    );
}

class AWPCP_CommentsPlaceholder {

    private $request;
    private $settings;
    private $comments_creator;
    private $comments_renderer;
    private $notifications;

    public function __construct( $request, $settings, $comments_creator, $comments_renderer, $notifications ) {
        $this->request = $request;
        $this->settings = $settings;

        $this->comments_creator = $comments_creator;
        $this->comments_renderer = $comments_renderer;

        $this->notifications = $notifications;
    }

    public function placeholder( $ad, $placeholder, $context ) {
        $this->enqueue_scripts_and_styles();

        $action = $this->request->param( 'comment-action' );
        if ( strcmp( $action, 'post-comment' ) === 0 ) {
            $html = $this->handle_post_comment_action( $ad->ad_id );
        } else {
            $html = $this->comments_renderer->render_comments_list( $ad->ad_id );
        }

        return $html;
    }

    private function enqueue_scripts_and_styles() {
        wp_enqueue_style( 'awpcp-comments-ratings' );
        wp_enqueue_script( 'awpcp-comments-ratings' );
    }

    private function handle_post_comment_action( $ad_id ) {
        $data = stripslashes_deep( array(
            'id' => $this->request->param( 'comment-id', 0 ),
            'ad_id' => $ad_id,
            'user_id' => $this->request->get_current_user()->ID,
            'author_name' => $this->request->param( 'comment-author', '' ),
            'title' => $this->request->param( 'comment-title', '' ),
            'comment' => $this->request->param( 'comment-comment', '' ),
            'status' => $this->request->param( 'comment-status', '' ),
        ) );

        $errors = array();

        try {
            $comment = $this->comments_creator->create_or_update( $data );
            $this->notifications->send_comment_posted_notifications( $comment );
            $data = null;
        } catch ( AWPCP_Exception $e ) {
            $errors = $e->get_errors();
        }

        return $this->comments_renderer->render_comments_list( $ad_id, $data, $errors );
    }
}
