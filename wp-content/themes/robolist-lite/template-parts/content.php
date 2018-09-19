<?php
/**
 * Template part for displaying posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package robolist_lite
 */

?>
<?php
global $post;
$post_format = get_post_format($post->ID);
?>
<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<header class="entry-header">
		<?php
        robolist_lite_blog_post_format($post_format, $post->ID);

		if ( is_singular() ) :
			the_title( '<h1 class="entry-title">', '</h1>' );
		else :
			the_title( '<h2 class="entry-title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h2>' );
		endif;

		if ( 'post' === get_post_type() ) : ?>
		    <?php robolist_lite_posted_by(); ?>
		<?php
		endif; ?>
	</header><!-- .entry-header -->

	<div class="entry-content">
		<?php

            echo wp_kses_post(robolist_lite_get_excerpt($post->ID,400 ));
			wp_link_pages( array(
				'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'robolist-lite' ),
				'after'  => '</div>',
			) );
		?>
	</div><!-- .entry-content -->

	<footer class="entry-footer">
		<?php robolist_lite_entry_footer(); ?>
	</footer><!-- .entry-footer -->
</article><!-- #post-<?php the_ID(); ?> -->
