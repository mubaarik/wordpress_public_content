<?php

function awpcp_comments_renderer() {
    return new AWPCP_CommentsRenderer( awpcp()->settings, AWPCP_Comments_Controller::instance() );
}

class AWPCP_CommentsRenderer {

    private $settings;
    private $comments;

    public function __construct( $settings, $comments ) {
        $this->settings = $settings;
        $this->comments = $comments;
    }

    public function render_comments_list( $ad_id, $comment = null, $errors = array() ) {
        $current_user = wp_get_current_user(); // required in comments-list-item.tpl.php

        if ( is_null( $comment ) ) {
            $comment = new stdClass();
            $comment->ad_id = $ad_id;
        }

        $user_has_to_log_in_to_post_comment = $this->user_has_to_log_in_to_post_comment();
        $user_can_post_comment = $this->user_can_post_comment();
        $messages = $this->get_template_messages();

        $form = $this->render_comments_form( $comment, $errors );
        $items = $this->comments->find_by_ad_id( $ad_id );

        ob_start();
            $comments = $this->comments;
            include( AWPCP_COMMENTS_MODULE_DIR . '/frontend/templates/comments.tpl.php' );
            $html = ob_get_contents();
        ob_end_clean();

        return $html;
    }

    private function user_has_to_log_in_to_post_comment() {
        if ( $this->settings->get_option( 'comments-require-user-registration' ) ) {
            return is_user_logged_in() ? false : true;
        } else {
            return false;
        }
    }

    public function render_comments_form( $comment, $errors=array() ) {
        $current_user = wp_get_current_user(); // required in comments-post-form.tpl.php

        $comment = (object) $comment;

        if ( $this->user_can_post_comment() ) {
            ob_start();
                include(AWPCP_COMMENTS_MODULE_DIR . '/frontend/templates/comments-post-form.tpl.php');
                $html = ob_get_contents();
            ob_end_clean();
        } else {
            $html = '';
        }

        return $html;
    }

    private function user_can_post_comment() {
        if ( $this->settings->get_option( 'only-admin-can-place-comments' ) ) {
            return awpcp_current_user_is_admin();
        } else if ( $this->settings->get_option( 'comments-require-user-registration' ) ) {
            return is_user_logged_in();
        } else {
            return true;
        }
    }

    private function get_template_messages() {
        $messages = array(
            'no-comments' => __( 'There are no comments yet.', 'awpcp-comments-ratings' ),
            'no-comments-login-required' => __( 'There are no comments yet. You have to be <login-link>logged in</a> to write a comment.', 'awpcp-comments-ratings' ),
            'login-required' => __( 'You have to be <login-link>logged in</a> to write a comment.' ),
        );

        $link = sprintf( '<a href="%s" title="%s">', wp_login_url( awpcp_current_url() ), __( 'Login', 'awpcp-comments-ratings' ) );

        $messages['no-comments-login-required'] = str_replace( '<login-link>', $link, $messages['no-comments-login-required'] );
        $messages['login-required'] = str_replace( '<login-link>', $link, $messages['login-required'] );

        return $messages;
    }
}
