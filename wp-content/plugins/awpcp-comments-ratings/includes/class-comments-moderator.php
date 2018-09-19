<?php

function awpcp_comments_moderator() {
    return new AWPCP_CommentsModerator( awpcp_akismet_wrapper_factory()->get_akismet_wrapper() );
}

class AWPCP_CommentsModerator {

    private $akismet;

    public function __construct( $akismet ) {
        $this->akismet = $akismet;
    }

    public function moderate_comment( $comment ) {
        $request = http_build_query( $this->get_request_data( $comment ) );
        $response = $this->akismet->http_post( $request, 'comment-check' );

        if ( $response[1] == 'true' ) {
            $comment->is_spam = true;
        } else {
            $comment->is_spam = false;
        }
    }

    private function get_request_data( $comment ) {
        return array_merge( $this->get_comment_data( $comment ), $this->akismet->get_user_data() );
    }

    private function get_comment_data( $comment ) {
        return array(
            'comment_type' => 'comment',
            'comment_author' => $comment->get_author_name(),
            'comment_author_email' => $comment->get_author_email(),
            'comment_content' => $comment->comment,
            'permalink' => url_showad( $comment->ad_id ),
        );
    }
}
