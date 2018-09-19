<?php
/**
 * Custom template tags for this theme
 *
 * Eventually, some of the functionality here could be replaced by core features.
 *
 * @package robolist_lite
 */


if ( ! function_exists( 'robolist_lite_posted_by' ) ) :
	/**
	 * Prints HTML with meta information for the current author.
	 */
	function robolist_lite_posted_by() {
	    global $post;
        $comment_count = get_comments_number();
        $archive_year  = get_the_time('Y');
        $archive_month = get_the_time('m');
        echo '<div class="entry-meta"><div class="author"><a href="'.esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ).'" title="'.esc_attr(get_the_author()).'">';
        ?><span class="author-img"><?php echo get_avatar( get_the_author_meta( 'ID' ), 32, '', 'author-image', '' )?></span>

        <?php echo '<span>'.get_the_author().'</span></a></div>';
        if ($comment_count >= 0 && comments_open()) {
            echo '<div class="comments"><a href="'.esc_url(get_comments_link( $post->ID )).'"><i class="fa fa-comments-o"></i><span>';
            printf(// WPCS: XSS OK.
            /* translators: 1: comment count number, 2: title. */
                    esc_html(_n( '%s comment', '%s comments', esc_attr(get_comments_number($post->ID)), 'robolist-lite' )), absint(number_format_i18n( get_comments_number($post->ID) ) ) );
            echo '</a></div>';
        }
        echo '</span><div class="date"><a href="'.esc_url(get_month_link($archive_year,$archive_month)).'"><i class="fa fa-clock-o" aria-hidden="true"></i><span>'.esc_html(get_the_date()).'</span></a></div></div>';
    }
endif;

if ( ! function_exists( 'robolist_lite_entry_footer' ) ) :
	/**
	 * Prints HTML with meta information for the categories, tags and comments.
	 */
	function robolist_lite_entry_footer() {
		// Hide category and tag text for pages.
		if ( 'post' === get_post_type() ) {
			/* translators: used between list items, there is a space after the comma */
			$categories_list = get_the_category_list( esc_html__( ' ', 'robolist-lite' ) );
			if ( $categories_list ) {
				/* translators: 1: list of categories. */
				printf( '<span class="cat-links">' . esc_html__( 'Posted in %1$s', 'robolist-lite' ) . '</span>', $categories_list ); // WPCS: XSS OK.
			}

			/* translators: used between list items, there is a space after the comma */
			$tags_list = get_the_tag_list( '', esc_html_x( ' ', 'list item separator', 'robolist-lite' ) );
			if ( $tags_list ) {
				/* translators: 1: list of tags. */
				printf( '<span class="tags-links">' . esc_html__( 'Tagged %1$s', 'robolist-lite' ) . '</span>', $tags_list ); // WPCS: XSS OK.
			}
		}

		if ( ! is_single() && ! post_password_required() && ( comments_open() || get_comments_number() ) ) {
			echo '<span class="comments-link">';
			comments_popup_link(
				sprintf(
					wp_kses(
						/* translators: %s: post title */
						__( 'Leave a Comment<span class="screen-reader-text"> on %s</span>', 'robolist-lite' ),
						array(
							'span' => array(
								'class' => array(),
							),
						)
					),
					get_the_title()
				)
			);
			echo '</span>';
		}

		edit_post_link(
			sprintf(
				wp_kses(
					/* translators: %s: Name of current post. Only visible to screen readers */
					__( 'Edit <span class="screen-reader-text">%s</span>', 'robolist-lite' ),
					array(
						'span' => array(
							'class' => array(),
						),
					)
				),
				get_the_title()
			),
			'<span class="edit-link">',
			'</span>'
		);
	}
endif;

if ( ! function_exists( 'robolist_lite_post_thumbnail' ) ) :
/**
 * Displays an optional post thumbnail.
 *
 * Wraps the post thumbnail in an anchor element on index views, or a div
 * element when on single views.
 */
function robolist_lite_post_thumbnail() {
	if ( post_password_required() || is_attachment() || ! has_post_thumbnail() ) {
		return;
	}

	if ( is_singular() ) :
	?>

	<div class="post-thumbnail">
		<?php the_post_thumbnail(); ?>
	</div><!-- .post-thumbnail -->

	<?php else : ?>

	<a class="post-thumbnail" href="<?php the_permalink(); ?>" aria-hidden="true">
		<?php
			the_post_thumbnail( 'post-thumbnail', array(
				'alt' => the_title_attribute( array(
					'echo' => false,
				) ),
			) );
		?>
	</a>

	<?php endif; // End is_singular().
}
endif;
