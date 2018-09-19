<?php

abstract class AWPCP_CommentNotification {

    protected $settings;

    public function __construct( $settings ) {
        $this->settings = $settings;
    }

    public function send_notification_to_listing_owner( $listing, $comment ) {
        $subject = $this->get_notification_subject( $listing, $comment );
        $body = $this->get_body_for_listing_owner_notification( $listing, $comment );
        $recipient_address = $this->get_recipient_address_for_listing_owner_notification( $listing, $comment );

        return $this->send_notification( $recipient_address, $subject, $body );
    }

    protected abstract function get_notification_subject( $listing, $comment );
    protected abstract function get_body_for_listing_owner_notification( $listing, $comment );

    protected function get_recipient_address_for_listing_owner_notification( $listing, $comment ) {
        return awpcp_format_recipient_address( $listing->ad_contact_email );
    }

    protected function send_notification( $recipient_address, $subject, $body ) {
        $email = new AWPCP_Email();

        $email->to = $recipient_address;
        $email->subject = $subject;
        $email->body = $body;

        return $email->send();
    }

    public function send_notification_to_administrator( $listing, $comment ) {
        $subject = $this->get_notification_subject( $listing, $comment );
        $body = $this->get_body_for_administrator_nofitication( $listing, $comment );
        $recipient_address = $this->get_recipient_address_for_administrator_notification( $listing, $comment );

        return $this->send_notification( $recipient_address, $subject, $body );
    }

    protected abstract function get_body_for_administrator_nofitication( $listing, $comment );

    protected function get_recipient_address_for_administrator_notification( $listing, $comment ) {
        return awpcp_admin_recipient_email_address();
    }

    protected function replace_placeholders( $template, $listing, $comment ) {
        $template = str_replace( '$listing_owner', $listing->ad_contact_name, $template );
        $template = str_replace( '$listing_title', $listing->get_title(), $template );
        $template = str_replace( '$comment_author', $comment->get_author_name(), $template );
        $template = str_replace( '$comment_title', $comment->title, $template );
        $template = str_replace( '$comment_content', $comment->comment, $template );
        $template = str_replace( '$comment_url', $this->get_comment_url( $listing, $comment ), $template );
        $template = str_replace( '$comment_creation_date', awpcp_datetime( 'awpcp', $comment->created ), $template );
        $template = str_replace( '$comment_modification_date', awpcp_datetime( 'awpcp', $comment->updated ), $template );

        return $template;
    }

    private function get_comment_url( $listing, $comment ) {
        return sprintf( '%s#awpcp-comment-%d', url_showad( $listing->ad_id ), $comment->id );
    }
}
