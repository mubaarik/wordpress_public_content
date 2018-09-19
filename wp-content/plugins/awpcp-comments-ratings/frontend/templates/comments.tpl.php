<div class="awpcp-comments-container<?php echo empty( $items ) ? ' no-comments' : ''; ?>">
    <?php if ( $user_can_post_comment ): ?>
        <?php echo $form; ?>
    <?php elseif ( ! empty( $items ) && $user_has_to_log_in_to_post_comment ): ?>
        <p><?php echo $messages['login-required']; ?></p>
    <?php endif; ?>

    <?php if ( ! empty( $items ) ): ?>

    <h2><?php _e('Comments', 'awpcp-comments-ratings' ) ?></h2>
    <ul class="awpcp-comments">
    <?php foreach($items as $i => $item): ?>
        <?php include('comments-list-item.tpl.php') ?>
    <?php endforeach ?>
    </ul>

    <?php elseif ( $user_has_to_log_in_to_post_comment ): ?>

    <p><?php echo $messages['no-comments-login-required']; ?></p>

    <?php else: ?>

    <p><?php echo $messages['no-comments']; ?></p>

    <?php endif; ?>
</div>
