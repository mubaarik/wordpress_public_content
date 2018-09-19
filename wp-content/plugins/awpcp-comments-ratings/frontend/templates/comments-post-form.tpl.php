<h2><?php _e('Leave a Comment', 'awpcp-comments-ratings' ) ?></h2>
<form class="awpcp-comment-form" method="post" action="<?php echo esc_attr( url_showad( $comment->ad_id ) ); ?>">

    <?php echo awpcp_form_error('form', $errors) ?>

    <?php $author = awpcp_get_property($comment, 'author_name', '') ?>
    <?php if (!$current_user->ID): ?>
    <p class="awpcp-comment-form-author">
        <label><?php _e('Author', 'awpcp-comments-ratings' ) ?></label>
        <input type="text" value="<?php echo esc_attr($author) ?>" name="comment-author" size="30" />
        <?php echo awpcp_form_error('author', $errors) ?>
    </p>
    <?php else: ?>
        <input type="hidden" value="<?php echo esc_attr($author)?>" name="comment-author" />
    <?php endif ?>

    <?php if (get_awpcp_option('show-comments-title')): ?>
    <p class="awpcp-comment-form-title">
        <label><?php _e('Title', 'awpcp-comments-ratings' ) ?></label>
        <?php $title = awpcp_get_property($comment, 'title', '') ?>
        <input type="text" value="<?php echo esc_attr($title) ?>" name="comment-title" size="30" />
        <?php echo awpcp_form_error('title', $errors) ?>
    </p>
    <?php endif; ?>

    <p class="awpcp-comment-form-comment">
        <label><?php _e('Comment', 'awpcp-comments-ratings' ) ?></label>
        <?php $text = awpcp_get_property($comment, 'comment', ''); ?>
        <?php echo awpcp_form_error('comment', $errors) ?>
        <textarea name="comment-comment" rows="8" cols="40"><?php echo esc_textarea($text) ?></textarea>
    </p>

    <p class="awpcp-form-submit">
        <?php if (awpcp_get_property($comment, 'id', 0) > 0): ?>
        <input type="submit" class="button button-primary submit" value="<?php _e('Edit Comment', 'awpcp-comments-ratings' ) ?>" name="submit">
        <input type="button" class="button cancel" value="<?php _e('Cancel', 'awpcp-comments-ratings' ) ?>" name="cancel">
        <input type="hidden" value="<?php echo esc_attr($comment->id) ?>" name="comment-id">
        <?php else: ?>
        <input type="submit" class="button button-primary submit" value="<?php _e('Post Comment', 'awpcp-comments-ratings' ) ?>" name="submit">
        <?php endif ?>

        <?php $ad = awpcp_get_property($comment, 'ad_id', 0) ?>
        <input type="hidden" value="<?php echo esc_attr($ad) ?>" name="comment-ad-id">

        <?php $status = awpcp_get_property($comment, 'status', 'active') ?>
        <input type="hidden" value="<?php echo esc_attr($status) ?>" name="comment-status">
        <input type="hidden" value="post-comment" name="comment-action">
    </p>
</form>
