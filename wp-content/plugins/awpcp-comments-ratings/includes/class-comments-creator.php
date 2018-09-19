<?php

function awpcp_comments_creator() {
    return new AWPCP_CommentsCreator( AWPCP_Comments_Controller::instance(), awpcp_comments_moderator() );
}

class AWPCP_CommentsCreator {

    private $comments;
    private $comments_moderator;

    public function __construct( $comments, $comments_moderator ) {
        $this->comments = $comments;
        $this->comments_moderator = $comments_moderator;
    }

    /**
     * @since 3.2.3
     */
    public function create_or_update( $data ) {
        $errors = array();
        $sanitized = array();

        $comment = $this->comments->create_from_array( $data, $sanitized, $errors );

        if ( ! empty( $errors ) ) {
            $message = __( 'There was an error trying to save the comment.', 'awpcp-comments-ratings' );
            throw new AWPCP_Exception( $message, $errors );
        }

        $this->comments_moderator->moderate_comment( $comment );
        $result = $this->comments->save( $comment );

        if ( is_array( $result ) || $result === false ) {
            $message = __( 'There was an error trying to save the comment to the database.', 'awpcp-comments-ratings' );
            throw new AWPCP_Exception( $message, $result );
        }

        return $comment;
    }
}
