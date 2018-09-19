    <li id="awpcp-comment-<?php echo esc_attr( $item->id ); ?>" class="awpcp-comment <?php echo $i % 2 ? 'odd' : 'even'; ?> clearfix" data-comment-id="<?php echo esc_attr( $item->id ); ?>">
        <div class="awpcp-comment-container">

            <?php if (get_awpcp_option('show-comments-title')): ?>
            <div class="awpcp-comment-title"><strong><?php echo esc_html($item->title); ?></strong> by <?php echo esc_html($item->get_author_name()); ?></div>
            <?php else: ?>
            <div class="awpcp-comment-author"><?php echo esc_html($item->get_author_name()); ?></div>
            <?php endif; ?>

            <div class="awpcp-comment-content"><?php echo nl2br( esc_html( $item->comment ) ); ?></div>
            <div class="awpcp-comment-meta"><?php echo sprintf(__('Posted on %s', 'awpcp-comments-ratings' ), $item->get_created_date()); ?></div>
            <ul class="awpcp-comment-actions">
                <?php if ($comments->user_can_delete_comment($current_user->ID, $item)): ?>
                <li class="delete"><a href="#" title="<?php echo esc_attr(_x('Delete', 'comment actions', 'awpcp-comments-ratings' )); ?>"></a></li>
                <?php endif; ?>

                <?php if ($comments->user_can_edit_comment($current_user->ID, $item)): ?>
                <li class="edit"><a href="#" title="<?php echo esc_attr(_x('Edit', 'comment actions', 'awpcp-comments-ratings' )); ?>"></a></li>
                <?php endif; ?>

                <?php if (!$item->is_flagged()): ?>
                <li class="flag"><a href="#" title="<?php echo esc_attr(_x('Flag', 'comment actions', 'awpcp-comments-ratings' )); ?>"></a></li>
                <?php endif; ?>
            </ul>
        </div>
    </li>
