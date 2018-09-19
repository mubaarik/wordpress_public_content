<?php
/**
 * Job listing preview when submitting job listings.
 *
 * This template can be overridden by copying it to yourtheme/job_manager/job-preview.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @author      Automattic
 * @package     WP Job Manager
 * @category    Template
 * @version     1.27.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
global $post;
$robolist_lite_setting = robolist_lite_get_theme_options();
$contact_form_enable = $robolist_lite_setting['single_joblist_form_enable'];
$contact_form_sc = $robolist_lite_setting['single_joblist_form'];
$contact_form_title = $robolist_lite_setting['single_form_title'];
$gallery = get_post_meta($form->get_job_id(), '_main_image');
$location = get_post_meta($post->ID, '_job_location');
$phone = get_post_meta($post->ID, '_company_phone');
$rating = '';
if (in_array('comments-ratings/comments-ratings.php', apply_filters('active_plugins', get_option('active_plugins')))) {

    global $pixreviews_plugin;
    $rating = $pixreviews_plugin->get_average_rating($post->ID);
}
$gallery_images = $gallery[0];
if($gallery_images[0]){
    $attach_id = listable_get_attachment_id_from_url($gallery_images[0]);
    set_post_thumbnail(get_the_ID(), $attach_id);
}
if (is_array($phone) && !empty($phone)){
    $phone = $phone[0] ? $phone[0] : '';
}
if (is_array($location)  && !empty($location)) {
    $location = $location[0] ? $location[0] : '';
}

?>
<form method="post" id="job_preview" action="<?php echo esc_url( $form->get_action() ); ?>">
    <div class="container">
        <div class="job_listing_preview_title">
            <input type="submit" name="continue" id="job_preview_submit_button" class="button job-manager-button-submit-listing" value="<?php echo esc_attr(apply_filters( 'submit_job_step_preview_submit_text', esc_html__( 'Submit Listing', 'robolist-lite' ) )); ?>" />
            <input type="submit" name="edit_job" class="button job-manager-button-edit-listing" value="<?php echo esc_html__( 'Edit listing', 'robolist-lite' ); ?>" />
            <h2><?php echo esc_html__( 'Preview', 'robolist-lite' ); ?></h2>
        </div>
    </div>
	<div class="job_listing_preview single_job_listing">
        <div class="listing-single-slider section">

            <?php if(count($gallery_images)>2){?>
                <div class="container-fluid">
                    <div class="row">
                        <div class="listing-gallery-wrap">
                            <?php
                            foreach($gallery_images as $g){
                                $image_style = "style='background-image:url(" . esc_url($g) . ")'";
                                echo '<div class="listing-gallery-holder" '.wp_kses_post($image_style).'"></div>';
                            }
                            ?>
                        </div>
                    </div>
                </div>
            <?php  }
            elseif(count($gallery_images)>0 && count($gallery_images)<=2 ){
                $bg_img = $gallery_images[0];
                ?>
                <div class="container-fluid">
                    <div class="row">
                        <?php
                        $image_style = "style='background-image:url(" . esc_url($bg_img) . ")'";
                        echo '<div class="listing-img-wrap" '.wp_kses_post($image_style).'"></div>';
                        ?>
                    </div>
                </div>
            <?php }
            else {
                ?>
                <div class="container-fluid">
                    <div class="row">
                        <?php
                        $image_style = "style='background-image:url(" . esc_url($feature_image) . ")'";
                        echo '<div class="listing-img-wrap" '.wp_kses_post($image_style).'"></div>';
                        ?>
                    </div>
                </div>
                <?php
            }
            ?>

            <div class="listing-detail-wrap">
                <div class="container">
                    <div class="col-md-7">
                        <div class="rl-single-author">
                            <a href="<?php echo esc_url(get_author_posts_url(get_the_author_meta('ID'))); ?>">

                                <?php echo get_avatar(get_the_author_meta('ID'), 32, '', '', array('class' => 'listing-auth')); ?>
                            </a>
                        </div>
                        <div class="rl-single-meta">
                            <h2><?php the_title(); ?></h2>
                            <?php
                            if($rating) {
                                echo '<div class="cat-star">';
                                for ($i = 1; $i <= 5; $i++) {
                                    if ($i <= $rating)
                                        echo '<i class="fa fa-star"></i>';
                                    else
                                        echo '<i class="fa fa-star rl-blank-star"></i>';
                                }
                                printf(
                                /* translators: %s: review count */
                                _n( '<span>(%s Review)</span>', '<span>(%s Reviews</span>)', get_comments_number($post->ID), 'robolist-lite' ), absint(number_format_i18n( get_comments_number($post->ID) )) );
                                echo '</div>';
                            }else{
                                echo '<div class="cat-star">';
                                echo '<i class="fa fa-star rl-blank-star"></i>';
                                echo '<i class="fa fa-star rl-blank-star"></i>';
                                echo '<i class="fa fa-star rl-blank-star"></i>';
                                echo '<i class="fa fa-star rl-blank-star"></i>';
                                echo '<i class="fa fa-star rl-blank-star"></i>';
                                printf(
                                /* translators: %s: review count */
                                _n( '<span>(%s Review)</span>', '<span>(%s Reviews</span>)', get_comments_number($post->ID), 'robolist-lite' ), absint(number_format_i18n( get_comments_number($post->ID)) ));
                                echo '</div>';
                            }
                            ?>
                            <?php
                            if($location)
                                echo '<span class="listing-loc"><i class="ion-ios-location-outline"></i>'.esc_html($location).'</span>';
                            if($phone)
                                echo '<span class="listing-tel"><i class="fa fa-phone"></i><a href="tel:'.esc_attr($phone).'">'.esc_html($phone).'</a></span>';
                            ?>
                        </div>

                    </div>
                </div>
            </div>

            <div class="listing-content section">
                <div class="container">
                    <div class="row">
                        <div class="col-md-8">
                            <?php
                            echo wp_kses_post(robolist_lite_strip_url_content(get_the_ID()));
                            wp_link_pages( array(
                                'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'robolist-lite' ),
                                'after'  => '</div>',
                            ) );
                            if (comments_open() || get_comments_number()) :
                                comments_template();
                            endif;

                            ?>
                        </div>
                        <div class="col-md-4">
                            <?php if($contact_form_enable==1): ?>
                                <div class="login-page">
                                    <div class="form">

                                        <?php
                                        if($contact_form_title && $contact_form_sc)
                                            echo '<h2>'.esc_html($contact_form_title).'</h2>';
                                        if($contact_form_sc)
                                            echo do_shortcode($contact_form_sc);
                                        ?>
                                    </div>
                                </div>
                            <?php endif;
                            if (is_active_sidebar('single-sidebar')) {
                                dynamic_sidebar('single-sidebar');
                            }

                            ?>
                        </div>

                    </div>
                </div>
            </div>
        </div>
		<input type="hidden" name="job_id" value="<?php echo esc_attr( $form->get_job_id() ); ?>" />
		<input type="hidden" name="step" value="<?php echo esc_attr( $form->get_step() ); ?>" />
		<input type="hidden" name="job_manager_form" value="<?php echo esc_attr($form->get_form_name()); ?>" />
	</div>
</form>
