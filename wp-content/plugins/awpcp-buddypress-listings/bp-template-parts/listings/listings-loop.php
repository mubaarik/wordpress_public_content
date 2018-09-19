<?php $current_view = awpcp_buddypress_wrapper()->listings()->current_view; ?>

<div class="awpcp-buddypress-listings">
    <?php if ( $current_view->has_listings() ): ?>
    <div id="pag-top" class="pagination no-ajax">
        <div class="pag-count" id="awpcp-buddypress-listings-count-top">
            <?php echo $current_view->render_pagination_count(); ?>
        </div>
        <div class="pagination-links" id="awpcp-buddypress-listings-links-top">
            <?php echo $current_view->render_pagination_links(); ?>
        </div>
    </div>
    <ul id="awpcp-buddypress-listings-list" class="item-list" role="main">
        <?php while( $current_view->has_listings() ): $listing = $current_view->next_listing(); ?>
        <li id="listing-<?php echo esc_attr( $listing->ad_id ); ?>" data-id=<?php echo esc_attr( $listing->ad_id ); ?>>
            <?php echo $current_view->render_listing_excerpt( $listing ); ?>
            <div class="awpcp-buddypress-listings-actions">
                <?php echo $current_view->render_user_actions( $listing ); ?>
            </div>
        </li>
        <?php endwhile; ?>
    </ul>

    <div class="awpcp-buddypress-delete-prompt">
        <p>
            <?php _e( 'Are you sure you want to delete this listing?', 'awpcp-buddypress-listings' ); ?>
            <a class="button cancel bp-secondary-action"><?php _e( 'Cancel', 'awpcp-buddypress-listings' ); ?></a>
            <a class="button delete-listing bp-primary-action"><?php _e( 'Delete', 'awpcp-buddypress-listings' ); ?></a>
        </p>
        <p><span class="spinner"></span></p>
    </div>

    <div id="pag-bottom" class="pagination no-ajax">
        <div class="pag-count" id="awpcp-buddypress-listings-count-bottom">
            <?php echo $current_view->render_pagination_count(); ?>
        </div>
        <div class="pagination-links" id="awpcp-buddypress-listings-links-bottom">
            <?php echo $current_view->render_pagination_links(); ?>
        </div>
    </div>
    <?php else: ?>

    <div id="message" class="info">
        <p><?php _e( 'There were no listings found.', 'awpcp-buddypress-listings' ); ?></p>
    </div>

    <?php endif; ?>
</div>
