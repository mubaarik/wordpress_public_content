<?php
/**
 * Job listing in the loop.
 *
 * This template can be overridden by copying it to yourtheme/job_manager/content-job_listing.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @author      Automattic
 * @package     WP Job Manager
 * @category    Template
 * @since       1.0.0
 * @version     1.27.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $post;
$image = wp_get_attachment_url(get_post_thumbnail_id(get_the_ID()));
$category = get_the_terms($post->ID, 'job_listing_category');
$price = get_post_meta($post->ID, '_price_field');
$location = get_post_meta($post->ID, '_job_location');
if (is_array($location)) {
    $location = $location[0] ? $location[0] : '';
}
$price = $price[0] ? $price[0] : '';
$rating = '';
if (in_array('comments-ratings/comments-ratings.php', apply_filters('active_plugins', get_option('active_plugins')))) {
    global $pixreviews_plugin;

    $rating = $pixreviews_plugin->get_average_rating( $post->ID );
}
$number ='';
$page_id = get_option('job_manager_jobs_page_id');
$row_num = $GLOBALS['robolist_lite_row_count'];
if($GLOBALS['robolist_lite_row_count']==0)
    echo '<div class="row">';
$count = $row_num+1;
if($count > 1 && $count <4){
    $number = $count;
    $GLOBALS['robolist_lite_row_count'] = $GLOBALS['robolist_lite_row_count'] + 1;
}
else{
    $number =1;
    $GLOBALS['robolist_lite_row_count'] = 1;
}

?>

    <div class="col-md-4">

        <li <?php job_listing_class(); ?> data-longitude="<?php echo esc_attr( $post->geolocation_lat ); ?>" data-latitude="<?php echo esc_attr( $post->geolocation_long ); ?>">

            <div class="listing-content-wrap">
                <div class="listing-thumb" style="background-image:url(<?php echo esc_url($image); ?>)">
                    <?php if (is_array($category) && !empty($page_id)) {
                        $cat_data = $category[0];
                        ?>
                        <a href="<?php echo esc_url(get_the_permalink($page_id).'?search_category='.$cat_data->term_id); ?>" class="listing-cat"><?php echo esc_attr($cat_data->name); ?></a>
                    <?php } ?>
                    <a href="<?php echo esc_url(get_author_posts_url(get_the_author_meta('ID')).'?rl_post_type=listing'); ?>">

                        <?php echo get_avatar(get_the_author_meta('ID'), 32, '', '', array('class' => 'listing-auth')); ?>
                    </a>
                </div>
                <div class="listing-desc-wrapper">
                    <h3><?php echo '<a href="' . esc_url(get_the_permalink()) . '">' . get_the_title() . '</a>'; ?></h3>
                    <div class="row">
                        <?php
                        echo '<div class="listing-meta">';

                        if($rating) {
                            echo '<div class="cat-star">';
                            for ($i = 1; $i <= 5; $i++) {
                                if ($i <= $rating)
                                    echo '<i class="fa fa-star"></i>';
                                else
                                    echo '<i class="fa fa-star rl-blank-star"></i>';
                            }
                            echo '</div>';
                        }else{
                            echo '<div class="cat-star">';
                                    echo '<i class="fa fa-star rl-blank-star"></i>';
                                    echo '<i class="fa fa-star rl-blank-star"></i>';
                                    echo '<i class="fa fa-star rl-blank-star"></i>';
                                    echo '<i class="fa fa-star rl-blank-star"></i>';
                                    echo '<i class="fa fa-star rl-blank-star"></i>';
                            echo '</div>';
                        }
                            printf(
                            /* translators: %s: get reviews */
                                _n( '<span>(%s Review)</span>', '<span>(%s Reviews</span>)', get_comments_number($post->ID), 'robolist-lite' ), number_format_i18n( get_comments_number($post->ID) ));
                            echo '</div>';
                        ?>
                        <?php if ($price): ?>
                            <div class="listing-price">
                                <span><?php echo esc_html($price); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="listing-footer">
                                            <span class="listing-loc">
                                                <?php if ($location)
                                                    echo '<i class="ion-ios-location-outline"></i>';
                                                ?>
                                                <?php echo esc_html($location); ?></span>
                        <?php
                        if (in_array('favorites/favorites.php', apply_filters('active_plugins', get_option('active_plugins')))) {
                            echo do_shortcode('[favorite_button]');
                        }?>
                        <a href="<?php the_permalink() ?>"><span><i class="ion-forward"></i></span></a>
                    </div>
                </div>
            </div>


        </li>
    </div>
<?php
if($number == 3){
    echo '</div><div class="row">';
}
