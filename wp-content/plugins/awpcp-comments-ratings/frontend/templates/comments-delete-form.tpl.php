<form class="awpcp-comment-form" method="post" action="<?php echo esc_attr( url_showad( $comment->ad_id ) ); ?>">
    <p class="awpcp-form-submit">
        <?php _e('Are you sure you want to delete this comment?'); ?>
        <input type="submit" class="button button-primary submit" value="<?php _e('Delete Comment', 'awpcp-comments-ratings' ); ?>" name="submit">
        <input type="button" class="button cancel" value="<?php _e('Cancel', 'awpcp-comments-ratings' ); ?>" name="cancel">
        <input type="hidden" value="<?php echo esc_attr($comment->id); ?>" name="comment-id">
    </p>
</form>
