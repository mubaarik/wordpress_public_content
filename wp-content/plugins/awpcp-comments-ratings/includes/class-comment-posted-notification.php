<?php

function awpcp_comment_posted_notification() {
    return new AWPCP_CommentPostedNotification( awpcp()->settings );
}

class AWPCP_CommentPostedNotification extends AWPCP_CommentNotification {

    protected function get_notification_subject( $listing, $comment ) {
        $subject = $this->settings->get_option( 'comment-posted-message-subject' );
        return $this->replace_placeholders( $subject, $listing, $comment );
    }

    protected function get_body_for_listing_owner_notification( $listing, $comment ) {
        $template = $this->settings->get_option( 'comment-posted-message-body-for-listing-owner' );
        return $this->replace_placeholders( $template, $listing, $comment );
    }

    protected function get_body_for_administrator_nofitication( $listing, $comment ) {
        $template = $this->settings->get_option( 'comment-posted-message-body-for-administrator' );
        return $this->replace_placeholders( $template, $listing, $comment );
    }
}
