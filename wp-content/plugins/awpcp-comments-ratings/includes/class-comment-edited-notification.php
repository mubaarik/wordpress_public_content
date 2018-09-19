<?php

function awpcp_comment_edited_notification() {
    return new AWPCP_CommentEditedNotification( awpcp()->settings );
}

class AWPCP_CommentEditedNotification extends AWPCP_CommentNotification {

    protected function get_notification_subject( $listing, $comment ) {
        $subject = $this->settings->get_option( 'comment-edited-message-subject' );
        return $this->replace_placeholders( $subject, $listing, $comment );
    }

    protected function get_body_for_listing_owner_notification( $listing, $comment ) {
        $template = $this->settings->get_option( 'comment-edited-message-body-for-listing-owner' );
        return $this->replace_placeholders( $template, $listing, $comment );
    }

    protected function get_body_for_administrator_nofitication( $listing, $comment ) {
        $template = $this->settings->get_option( 'comment-edited-message-body-for-administrator' );
        return $this->replace_placeholders( $template, $listing, $comment );
    }
}
