<?php

function awpcp_comments_notifications() {
    return new AWPCP_CommentsNotifications(
        awpcp_listings_collection(),
        awpcp_request(),
        awpcp()->settings
    );
}

class AWPCP_CommentsNotifications {

    private $listings;
    private $request;
    private $settings;

    public function __construct( $listings, $request, $settings ) {
        $this->listings = $listings;
        $this->request = $request;
        $this->settings = $settings;
    }

    public function send_comment_posted_notifications( $comment ) {
        if ( $this->notifications_are_disabled() ) {
            return false;
        }

        $notification_helper = awpcp_comment_posted_notification();

        return $this->send_notifications( $comment, $notification_helper );
    }

    private function notifications_are_disabled() {
        if ( $this->should_send_notification_to_listing_owner() ) {
            return false;
        }

        if ( $this->should_send_notification_to_administrator() ) {
            return false;
        }

        return true;
    }

    private function should_send_notification_to_listing_owner() {
        return $this->settings->get_option( 'notify-listing-owner-about-comments-actions' );
    }

    private function should_send_notification_to_administrator() {
        return $this->settings->get_option( 'notify-administrator-about-comments-actions' );
    }

    private function send_notifications( $comment, $notification_helper ) {
        try {
            $listing = $this->listings->get( $comment->ad_id );
        } catch ( AWPCP_Exception $e ) {
            return false;
        }

        $notifications_sent = 0;

        if ( $this->maybe_send_notification_to_listing_owner( $notification_helper, $listing, $comment ) ) {
            $notifications_sent = $notifications_sent + 1;
        }

        if ( $this->maybe_send_notification_to_administrator( $notification_helper, $listing, $comment ) ) {
            $notifications_sent = $notifications_sent + 1;
        }

        return $notifications_sent ? $notifications_sent : false;
    }

    private function maybe_send_notification_to_listing_owner( $notification_helper, $listing, $comment ) {
        if ( ! $this->should_send_notification_to_listing_owner() ) {
            return false;
        }

        $current_user = $this->request->get_current_user();

        if ( is_user_logged_in() && $current_user->ID == $listing->user_id ) {
            return false;
        }

        return $notification_helper->send_notification_to_listing_owner( $listing, $comment );
    }

    private function maybe_send_notification_to_administrator( $notification_helper, $listing, $comment ) {
        if ( ! $this->should_send_notification_to_administrator() ) {
            return false;
        }

        if ( awpcp_current_user_is_admin() ) {
            return false;
        }

        return $notification_helper->send_notification_to_administrator( $listing, $comment );
    }

    public function send_comment_edited_notifications( $comment ) {
        if ( $this->notifications_are_disabled() ) {
            return false;
        }

        $notification_helper = awpcp_comment_edited_notification();

        return $this->send_notifications( $comment, $notification_helper );
    }
}
