<?php
$robolist_lite_setting = robolist_lite_get_theme_options();
$listing_title = $robolist_lite_setting['listing_des'];
$args = array(
    'post_type' => 'job_listing',
    'posts_per_page' => 6,
);

$query = new WP_Query($args);
if ($query->have_posts()) {
    ?>
    <!-- Start of feature section -->
    <div class="listing-section section">
        <div class="container">
            <?php
             $listing_posts_args = array(
                'post_type' => 'page',
                'posts_per_page' => 1,
                'post__in' => (array)$listing_title,
            );
                 $listing_variable = get_posts($listing_posts_args);
                echo '<div class="section-title">';

                 foreach ($listing_variable as $key => $listing_value) {
                    echo '<h2>' .  wp_kses_post(wp_trim_words($listing_value->post_title, 16)) . '</h2>';
               
                    echo '<p>' . wp_kses_post($listing_value->post_content) . '</p>';
                echo '</div>';
            }
            ?>

                <?php $count = 0;
                while ($query->have_posts()):$query->the_post();
                    global $post;
                    if($count == 0){
                        echo '<div class="row">';
                        $count = 1;
                    }
                    $image = wp_get_attachment_image_url(get_post_thumbnail_id($post->ID), 'full');
                    $category = get_the_terms($post->ID, 'job_listing_category');
                    $price = get_post_meta($post->ID, '_price_field');
                    $location = get_post_meta($post->ID, '_job_location');
                    $phone = get_post_meta($post->ID, '_company_phone');
                    if (is_array($price) && !empty($price)){
                        $price = $price[0] ? $price[0] : '';
                    }
                    if (is_array($phone) && !empty($phone)){
                        $phone = $phone[0] ? $phone[0] : '';
                    }
                    if (is_array($location)  && !empty($location)) {
                        $location = $location[0] ? $location[0] : '';
                    }
                    $rating = '';
                    if (in_array('comments-ratings/comments-ratings.php', apply_filters('active_plugins', get_option('active_plugins')))) {
                        global $pixreviews_plugin;

                        $rating = $pixreviews_plugin->get_average_rating( $post->ID );
                    }
                    $page_id = get_option('job_manager_jobs_page_id');
                    ?>
                    <div class="col-md-4 col-sm-12">
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
                                    /* translators: %s: comment number */
                                    _n( '<span>(%s Review)</span>', '<span>(%s Reviews</span>)', get_comments_number($post->ID), 'robolist-lite' ), number_format_i18n( get_comments_number($post->ID) ) );
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
                    </div>
                <?php
                    $count++;
                    if($count == 4){
                        $count = 1;
                        echo '</div><div class="row">';
                    }
                endwhile; ?>
            </div>
        </div>
    </div>
    <!-- End of feature section -->
<?php }

