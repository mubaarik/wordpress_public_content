<?php
/**
 * ======  Wp Jobs Manager Filters START  ======
 */
//change job manager post type name
if (!function_exists('robolist_change_job_into_listing')) {

function robolist_change_job_into_listing($args)
{

    $singular = esc_html__('Listing', 'robolist-lite');
    $plural = esc_html__('Listings', 'robolist-lite');

    $args['labels'] = array(
        'name' => $plural,
        'singular_name' => $singular,
        'menu_name' => $plural,
        'all_items' => sprintf(esc_html__('All %s', 'robolist-lite'), $plural),
        'add_new' => esc_html__('Add New', 'robolist-lite'),
        'add_new_item' => sprintf(esc_html__('Add %s', 'robolist-lite'), $singular),
        'edit' => esc_html__('Edit', 'robolist-lite'),
        'edit_item' => sprintf(esc_html__('Edit %s', 'robolist-lite'), $singular),
        'new_item' => sprintf(esc_html__('New %s', 'robolist-lite'), $singular),
        'view' => sprintf(esc_html__('View %s', 'robolist-lite'), $singular),
        'view_item' => sprintf(esc_html__('View %s', 'robolist-lite'), $singular),
        'search_items' => sprintf(esc_html__('Search %s', 'robolist-lite'), $plural),
        'not_found' => sprintf(esc_html__('No %s found', 'robolist-lite'), $plural),
        'not_found_in_trash' => sprintf(esc_html__('No %s found in trash', 'robolist-lite'), $plural),
        'parent' => sprintf(esc_html__('Parent %s', 'robolist-lite'), $singular)
    );
    $args['description'] = sprintf(esc_html__('This is where you can create and manage %s.', 'robolist-lite'), $plural);
    $args['supports'] = array('title', 'editor', 'custom-fields', 'publicize', 'comments', 'thumbnail');
    $args['rewrite'] = array('slug' => 'listings');

    $permalinks = get_option('robolist_permalinks_settings');
    if (isset($permalinks['listing_base']) && !empty($permalinks['listing_base'])) {
        $args['rewrite']['slug'] = $permalinks['listing_base'];
    }

    return $args;
}
}

add_filter('register_post_type_job_listing', 'robolist_change_job_into_listing');

if (!function_exists('robolist_change_taxonomy_job_listing_type_args')) {
//listing type taxonomy
function robolist_change_taxonomy_job_listing_type_args($args)
{
    $singular = esc_html__('Listing Type', 'robolist-lite');
    $plural = esc_html__('Listings Types', 'robolist-lite');

    $args['label'] = $plural;
    $args['labels'] = array(
        'name' => $plural,
        'singular_name' => $singular,
        'menu_name' => esc_html__('Types', 'robolist-lite'),
        'search_items' => sprintf(esc_html__('Search %s', 'robolist-lite'), $plural),
        'all_items' => sprintf(esc_html__('All %s', 'robolist-lite'), $plural),
        'parent_item' => sprintf(esc_html__('Parent %s', 'robolist-lite'), $singular),
        'parent_item_colon' => sprintf(esc_html__('Parent %s:', 'robolist-lite'), $singular),
        'edit_item' => sprintf(esc_html__('Edit %s', 'robolist-lite'), $singular),
        'update_item' => sprintf(esc_html__('Update %s', 'robolist-lite'), $singular),
        'add_new_item' => sprintf(esc_html__('Add New %s', 'robolist-lite'), $singular),
        'new_item_name' => sprintf(esc_html__('New %s Name', 'robolist-lite'), $singular)
    );

    if (isset($args['rewrite']) && is_array($args['rewrite'])) {
        $args['rewrite']['slug'] = _x('listing-type', 'Listing type slug - resave permalinks after changing this', 'robolist-lite');
    }

    return $args;
}
}

add_filter('register_taxonomy_job_listing_type_args', 'robolist_change_taxonomy_job_listing_type_args');

if (!function_exists('robolist_lite_change_taxonomy_job_listing_category_args')) {
function robolist_lite_change_taxonomy_job_listing_category_args($args)
{
    $singular = esc_html__('Listing Category', 'robolist-lite');
    $plural = esc_html__('Listings Categories', 'robolist-lite');

    $args['label'] = $plural;

    $args['labels'] = array(
        'name' => $plural,
        'singular_name' => $singular,
        'menu_name' => esc_html__('Categories', 'robolist-lite'),
        'search_items' => sprintf(esc_html__('Search %s', 'robolist-lite'), $plural),
        'all_items' => sprintf(esc_html__('All %s', 'robolist-lite'), $plural),
        'parent_item' => sprintf(esc_html__('Parent %s', 'robolist-lite'), $singular),
        'parent_item_colon' => sprintf(esc_html__('Parent %s:', 'robolist-lite'), $singular),
        'edit_item' => sprintf(esc_html__('Edit %s', 'robolist-lite'), $singular),
        'update_item' => sprintf(esc_html__('Update %s', 'robolist-lite'), $singular),
        'add_new_item' => sprintf(esc_html__('Add New %s', 'robolist-lite'), $singular),
        'new_item_name' => sprintf(esc_html__('New %s Name', 'robolist-lite'), $singular)
    );

    if (isset($args['rewrite']) && is_array($args['rewrite'])) {
        $args['rewrite']['slug'] = _x('listing-category', 'Listing category slug - resave permalinks after changing this', 'robolist-lite');
    }

    $permalinks = get_option('robolist_permalinks_settings');
    if (isset($permalinks['category_base']) && !empty($permalinks['category_base'])) {
        $args['rewrite']['slug'] = $permalinks['category_base'];
    }

    return $args;
}
}

add_filter('register_taxonomy_job_listing_category_args', 'robolist_lite_change_taxonomy_job_listing_category_args');

/**
 * Add new fields
 * && remove some
 */
add_filter( 'job_manager_job_listing_data_fields', 'robolist_lite_add_extra_field', 1 );

if (!function_exists('robolist_lite_add_extra_field')) {

    function robolist_lite_add_extra_field( $fields ) {
        // reorder fields
        $image = get_post_meta(get_the_ID(), '_main_image');
        $gallery_images = '';
        if(!empty($image[0])){
            $gallery_images = $image[0];
        }
        if(is_array($gallery_images)){
            $all_images = implode(',',$gallery_images);
        }
        else{
            $all_images = $gallery_images;
        }


        $fields['_company_tagline']['priority'] = 2.1;
        $fields['_job_location']['priority'] = 2.2;
        $fields['_company_website']['priority'] = 2.5;
        $fields['_company_twitter']['priority'] = 2.6;

        $fields['_company_phone'] = array(
            'label' => esc_html__('Phone', 'robolist-lite'),
            'placeholder' => esc_html__('e.g +42-898-4364', 'robolist-lite'),
            'priority' => 2.4
        );
        $fields['_price_field'] = array(
            'label' => esc_html__('Price', 'robolist-lite'),
            'type' => 'text',
            'placeholder' => esc_html__("$20", 'robolist-lite'),
            'priority' => 2.5
        );
        $fields['_main_image'] = array(
            'label' => esc_html__('Gallery', 'robolist-lite'),
            'type' => 'textarea',
            'placeholder' => esc_html__("Requires at least 3 images. Enter Image Ids with separated ','", 'robolist-lite'),
            'priority' => 2.5,
            'value' => $all_images
        );

        $fields['_application']['label'] = esc_html__('Listed By', 'robolist-lite');

        unset( $fields['_company_tagline'] );
        unset( $fields['_company_twitter'] );
        unset( $fields['_company_website'] );
        unset( $fields['_company_name'] );
        unset( $fields['_company_video'] );
        unset( $fields['_filled'] );
        unset( $fields['_featured'] );
        unset( $fields['_job_expires'] );
        return $fields;
    }
}


add_action('admin_init','robolist_remove_job_types');
if (!function_exists('robolist_remove_job_types')) {

    function robolist_remove_job_types()
    {
        if (get_option('job_manager_enable_types')) {
            update_option('job_manager_enable_types', '');
        }
        if (get_option('job_manager_enable_categories') == 0) {
            update_option('job_manager_enable_categories', 1);
        }
        update_option('display_on_post_types[post]', 'of23');

    }
}

add_action('admin_init','robolist_lite_custom_comment_rating');
function robolist_lite_custom_comment_rating(){
    $unserialized = array(
        # Hidden fields
        'settings_saved_once'      => '0',
        # General
        'enable_selective_ratings' => true,
        'default_rating'           => 4,
        'display_on_post_types'    => array( 'job_listing' => 'on'),
        'review_rating_label'      => esc_html__( 'Your overall rating of this listing:', 'robolist-lite' ),
        'review_title_label'       => esc_html__( 'Title of your review:', 'robolist-lite' ),
        'review_title_placeholder' => esc_html__( 'Summarize your opinion or highlight an interesting detail', 'robolist-lite' ),
        'review_label'             => esc_html__( 'Your Review', 'robolist-lite' ),
        'review_placeholder'       => esc_html__( 'Tell about your experience or leave a tip for others', 'robolist-lite' ),
        'review_submit_button'     => esc_html__( 'Submit your Review', 'robolist-lite' ),

    );

    update_option('pixreviews_settings',$unserialized);
}

add_filter('submit_job_form_fields', 'robolist_lite_front_submit_job_form_fields', 1);
if (!function_exists('robolist_lite_front_submit_job_form_fields')) {

    function robolist_lite_front_submit_job_form_fields($fields)
    {


        $fields['job']['job_title']['label'] = esc_html__('Listing Title', 'robolist-lite');
        $fields['job']['application']['label'] = esc_html__('Contact Email/Url', 'robolist-lite');
        $fields['job']['job_category']['label'] = esc_html__('Listing Category', 'robolist-lite');

        $fields['job']['company_phone'] = array(
            'label' => esc_html__('Phone', 'robolist-lite'),
            'type' => 'text',
            'placeholder' => esc_html__('e.g +42-898-4364', 'robolist-lite'),
            'required' => true,
            'priority' => 2.8
        );
        $fields['job']['price_field'] = array(
            'label' => esc_html__('Price', 'robolist-lite'),
            'type' => 'text',
            'placeholder' => esc_html__("$20", 'robolist-lite'),
            'required' => true,
            'priority' => 2.5
        );

        $fields['job']['main_image']['label']              = esc_html__( 'Gallery Images', 'robolist-lite' );
        $fields['job']['main_image']['description']              = esc_html__( 'Note: Requires at least 3 images. First image will be the feature image', 'robolist-lite' );
        $fields['job']['main_image']['priority']           = 2.6;
        $fields['job']['main_image']['required']           = false;
        $fields['job']['main_image']['type']               = 'file';
        $fields['job']['main_image']['ajax']               = true;
        $fields['job']['main_image']['required']           = true;
        $fields['job']['main_image']['placeholder']        = esc_html__( 'Recommended 3 Images', 'robolist-lite' );
        $fields['job']['main_image']['allowed_mime_types'] = $fields['company']['company_logo']['allowed_mime_types'];
        $fields['job']['main_image']['multiple']           = true;


        unset($fields['company']['company_name']);
        unset($fields['company']['company_website']);
        unset($fields['company']['company_tagline']);
        unset($fields['company']['company_video']);
        unset($fields['company']['company_twitter']);
        unset($fields['company']['company_logo']);
        return $fields;
    }
}
if (!function_exists('robolist_get_listings_page_url')) {

    function robolist_get_listings_page_url($default_link = null)
    {
        //if there is a page set in the Listings settings use that
        $listings_page_id = get_option('job_manager_jobs_page_id', false);
        if (!empty($listings_page_id)) {
            return get_permalink($listings_page_id);
        }

        if ($default_link !== null) {
            return $default_link;
        }
        return get_post_type_archive_link('job_listing');
    }
}


add_filter( 'favorites/button/html', 'robolist_favorites_button_html', 10, 4 );

if (!function_exists('robolist_favorites_button_html')) {
    function robolist_favorites_button_html($html, $post_id, $favorited, $site_id)
    {
        $html = '<span><i class="fa fa-heart-o"></i></span>';
        return $html;
    }
}

add_action( 'admin_init', 'robolist_lite_remove_menu_pages' );
if (!function_exists('robolist_lite_remove_menu_pages')) {

    function robolist_lite_remove_menu_pages()
    {
        remove_submenu_page('options-general.php', 'simple-favorites');
    }
}

function listable_get_attachment_id_from_url( $attachment_url = '' ) {

    global $wpdb;
    $attachment_id = false;

    // If there is no url, bail.
    if ( '' == $attachment_url ) {
        return false;
    }

    // Get the upload directory paths
    $upload_dir_paths = wp_upload_dir();

    // Make sure the upload path base directory exists in the attachment URL, to verify that we're working with a media library image
    if ( false !== strpos( $attachment_url, $upload_dir_paths['baseurl'] ) ) {

        // If this is the URL of an auto-generated thumbnail, get the URL of the original image
        $attachment_url = preg_replace( '/-\d+x\d+(?=\.(jpg|jpeg|png|gif)$)/i', '', $attachment_url );

        // Remove the upload path base directory from the attachment URL
        $attachment_url = str_replace( $upload_dir_paths['baseurl'] . '/', '', $attachment_url );

        // Finally, run a custom database query to get the attachment ID from the modified attachment URL
        $attachment_id = $wpdb->get_var( $wpdb->prepare( "SELECT wposts.ID FROM $wpdb->posts wposts, $wpdb->postmeta wpostmeta WHERE wposts.ID = wpostmeta.post_id AND wpostmeta.meta_key = '_wp_attached_file' AND wpostmeta.meta_value = '%s' AND wposts.post_type = 'attachment'", $attachment_url ) );

    }

    return $attachment_id;
}


if (!function_exists('robolist_lite_blog_post_format')) {
    function robolist_lite_blog_post_format($post_format, $post_id)
    {

        global $post;
        if ($post_format == 'video') {
            $content = strip_tags(get_post_field('post_content', $post->ID));
            $content = strip_shortcodes($content);
            $ori_url = explode("\n", esc_html($content));
            $url = $ori_url[0];
            $url_type = explode(" ", $url);
            if (isset($url_type[1])) {
                $url_type_shortcode = $url_type[1];
            }
            $new_content = get_shortcode_regex();
            if (isset($url_type[1])) {
                if (preg_match_all('/' . $new_content . '/s', $post->post_content, $matches)
                    && array_key_exists(2, $matches)
                    && in_array($url_type_shortcode, $matches[2])

                ) {
                    echo do_shortcode($matches[0][0]);
                }
            } else {
                echo wp_oembed_get(esc_html(robolist_lite_the_featured_video($content)));
            }
            if(is_page_template('page-templates/template-home.php')){
                echo '</div>';
            }
        } elseif ($post_format == 'audio') {
            $html = "";
            $content = trim(get_post_field('post_content', $post_id));
            $ori_url = explode("\n", esc_html($content));
            $new_content = preg_match_all('/\[[^\]]*\]/', $content, $matches);
            if ($new_content) {
                echo do_shortcode($matches[0][0]);
            } elseif (preg_match('#^<(script|iframe|embed|object)#i', $content)) {
                $regex = '/https?\:\/\/[^\" ]+/i';
                preg_match_all($regex, $ori_url[0], $matches);
                $urls = ($matches[0]);
                $html .= ('<iframe src="' . $urls[0] . '" width="100%" height="240" frameborder="no" scrolling="no"></iframe>');
            } elseif (0 === strpos($content, 'https://')) {
                $embed_url = wp_oembed_get(esc_url($ori_url[0]));
                $html .= ($embed_url);
            }
            echo esc_html($html);
        } elseif ($post_format == 'gallery') {
            add_filter( 'shortcode_atts_gallery', 'robolist_lite_shortcode_atts_gallery' );

            $image_url = get_post_gallery_images($post_id);
            $background_style ='';
            $post_thumbnail_id = get_post_thumbnail_id($post_id);
            $attachment = get_post($post_thumbnail_id);
            $image = wp_get_attachment_image_url(get_post_thumbnail_id($post->ID), 'full');
            $category = get_the_category();
            if (!empty($image)) {
                $background_style = "style='background-image:url(" . esc_url($image) . ")'";
            } else {
                $background_style = "";
            }
            if ($image_url) {
                ?>

                <div class="post-gallery">
                    <?php if(is_page_template('page-templates/template-home.php')): ?>
                        <div class="post-img-meta">
                            <a href="<?php echo esc_url(get_category_link($category[0]->term_id)); ?>"
                               class="post-cat"><?php echo esc_attr($category[0]->name); ?></a>
                            <h3 class="post-title"><a href="<?php echo esc_url(get_the_permalink()); ?>"><?php the_title(); ?></a>
                            </h3>
                        </div>
                    <?php endif; ?>
                    <div class="post-format-gallery">
                        <?php foreach ($image_url as $key => $images) { ?>
                            <div class="slider-item" style="background-image: url('<?php echo esc_url($images); ?>');" alt="<?php echo esc_attr($attachment->post_excerpt); ?>">
                            </div>
                        <?php } ?>
                    </div>

                </div>
            <?php }
        } else {
            $image = wp_get_attachment_image_url(get_post_thumbnail_id($post->ID), 'full');
            $category = get_the_category();
            if (!empty($image)) {
                $background_style = "style='background-image:url(" . esc_url($image) . ")'";
            } else {
                $background_style = "";
            }
            if (has_post_thumbnail() && !is_single() && is_page_template('page-templates/template-home.php')) {
                ?>
                <div class="post-img" <?php echo wp_kses_post($background_style); ?>>

                    <div class="post-img-meta">
                        <a href="<?php echo esc_url(get_category_link($category[0]->term_id)); ?>"
                           class="post-cat"><?php echo esc_attr($category[0]->name); ?></a>
                        <h3 class="post-title"><a href="<?php echo esc_url(get_the_permalink()); ?>"><?php the_title(); ?></a>
                        </h3>
                    </div>
                </div>
                <?php

            } else {
                    the_post_thumbnail();
            }

        }

    }
}
if (!function_exists('robolist_lite_the_featured_video')) {
    function robolist_lite_the_featured_video($content)
    {
        $ori_url = explode("\n", $content);
        $url = $ori_url[0];
        $w = get_option('embed_size_w');
        if (!is_single()) {
            $url = str_replace('448', $w, $url);

            return $url;
        }

        if (0 === strpos($url, 'https://') || 0 == strpos($url, 'http://')) {
            echo esc_url(wp_oembed_get($url));
            $content = trim(str_replace($url, '', $content));
        } elseif (preg_match('#^<(script|iframe|embed|object)#i', $url)) {
            $h = get_option('embed_size_h');
            echo esc_url($url);
            if (!empty($h)) {

                if ($w === $h) $h = ceil($w * 0.75);
                $url = preg_replace(
                    array('#height="[0-9]+?"#i', '#height=[0-9]+?#i'),
                    array(sprintf('height="%d"', $h), sprintf('height=%d', $h)),
                    $url
                );
                echo esc_url($url);
            }

            $content = trim(str_replace($url, '', $content));

        }
    }
}

if (!function_exists('robolist_lite_get_excerpt')) :
    function robolist_lite_get_excerpt($post_id, $count)
    {
        $title = get_the_title($post_id);
        $content_post = get_post($post_id);
        $excerpt = $content_post->post_content;

        $excerpt = strip_shortcodes($excerpt);
        $excerpt = strip_tags($excerpt);


        $excerpt = preg_replace('/\s\s+/', ' ', $excerpt);
        $excerpt = preg_replace('#\[[^\]]+\]#', ' ', $excerpt);
        $strip = explode(' ', $excerpt);
        foreach ($strip as $key => $single) {
            if (!filter_var($single, FILTER_VALIDATE_URL) === false) {
                unset($strip[$key]);
            }
        }
        $excerpt = implode(' ', $strip);

        $excerpt = substr($excerpt, 0, $count);
        if (strlen($excerpt) >= $count) {
            $excerpt = substr($excerpt, 0, strripos($excerpt, ' '));
            $excerpt = $excerpt . '...';
        }
        if($title)
            return $excerpt;
        else
            return '<a href="'.esc_url(get_the_permalink($post_id)).'">'.$excerpt.'</a>';

    }
endif;

add_action('robolist_lite_header_search', 'robolist_lite_job_aearch');
if (!function_exists('robolist_lite_job_aearch')) {
    function robolist_lite_job_aearch()
    {
        if (in_array('wp-job-manager/wp-job-manager.php', apply_filters('active_plugins', get_option('active_plugins')))) {

            $page_id = get_option('job_manager_jobs_page_id');
            ?>
            <form role="search" method="get" action="<?php echo esc_url(get_the_permalink($page_id)); ?>">
                <div class="banner-search-input">

                    <div class="banner-search-input-item search-key">
                        <input type="text" name="search_keywords"
                               placeholder="<?php echo esc_attr__('What are you looking for ?', 'robolist-lite'); ?>"
                               value=""/>
                    </div>
                    <div class="banner-search-input-item location">
                        <input type="text" name="search_location"
                               placeholder="<?php echo esc_attr__('Enter Location', 'robolist-lite'); ?>" value=""/>
                    </div>
                    <div class="banner-search-input-item category">
                        <select class="selectpicker" data-live-search="true" name="search_category">
                            <option value=""><?php echo esc_html__('All Categories', 'robolist-lite'); ?></option>
                            <?php if (count(get_job_listing_categories()) >= 1): ?>

                                <?php foreach (get_job_listing_categories() as $cat) { ?>
                                    <option value="<?php echo esc_attr($cat->term_id); ?>"><?php echo esc_html($cat->name); ?></option>
                                <?php } ?>
                            <?php endif; ?>

                        </select>
                    </div>
                    <button class="button btn-default"><i
                                class="ion-ios-search"></i><?php echo esc_html__('Search', 'robolist-lite'); ?></button>
                </div>
            </form>

        <?php }
    }
}

if (!function_exists('robolist_lite_breadcrumb')) {

function robolist_lite_breadcrumb()
{
    $header_image = get_header_image();
    if ((get_post_type() == 'portfolio') && !is_archive()) {
        $post_thumbnail_id = get_post_thumbnail_id(get_the_ID());
        $image = wp_get_attachment_image_src($post_thumbnail_id, 'recent-image');
        $header_image = $image[0];

    }
    $blog = get_option('show_on_front');
    $blog_page = get_option('page_for_posts');
    $current_author = get_user_by( 'slug', get_query_var( 'author_name' ) );
    ?>
    <div class="inner-banner-wrap"
         <?php if ($header_image) { ?>style="background-image:url(<?php echo esc_url($header_image); ?>)"<?php } ?>>
        <div class="container">
            <div class="row">
                <div class="inner-banner-content">
                    <?php
                    if (is_archive()) {
                        the_archive_title('<h2>', '</h2>');
                    }
                    if ((is_single() || is_page()) && !isset($_GET['rl_favorite'])) {
                        the_title('<h2>', '</h2>');
                    }
                    ?>

                    <div class="header-breadcrumb">

                        <?php

                        if (isset($_GET['rl_favorite'])) {
                            echo '<h2>'.esc_html__('My Favorite Listings','robolist-lite').'</h2>';

                        }
                        elseif(($blog=='page') && !is_front_page() && is_home()){
                            echo '<h2>'.esc_html(get_the_title($blog_page)).'</h2>';
                        }

                        else {

                            $delimiter = '';
                            $home = esc_html__('Home', 'robolist-lite'); // text for the 'Home' link
                            $before = '<li>'; // tag before the current crumb
                            $after = '</li>'; // tag after the current crumb
                            $comment_count = get_comments_number();
                            echo '<ul class="breadcrumb">';
                            global $post;
                            $homeLink = home_url();
                            echo '<li><a href="' . esc_url($homeLink) . '">' . esc_html($home) . '</a></li>' . wp_kses_post($delimiter) . ' ';

                            if( is_home() &&  !is_front_page() ){
                                echo '<li>' . esc_html(get_the_title($blog)) . '</li> ';
                            }
                            elseif (is_category()) {
                                global $wp_query;
                                $cat_obj = $wp_query->get_queried_object();
                                $thisCat = $cat_obj->term_id;
                                $thisCat = get_category($thisCat);
                                $parentCat = get_category($thisCat->parent);
                                if ($thisCat->parent != 0)
                                    echo(get_category_parents(esc_html($parentCat), TRUE, ' ' . wp_kses_post($delimiter) . ' '));
                                echo wp_kses_post($before) . single_cat_title('', false) . wp_kses_post($after);
                            } elseif (is_day()) {
                                echo '<li><a href="' . esc_url(get_year_link(get_the_time('Y'))) . '">' . esc_attr(get_the_time('Y')) . '</a></li> ' . wp_kses_post($delimiter) . ' ';
                                echo '<li><a href="' . esc_url(get_month_link(get_the_time('Y'), get_the_time('m'))) . '">' . esc_attr(get_the_time('F')) . '</a></li> ' . wp_kses_post($delimiter) . ' ';
                                echo wp_kses_post($before) . esc_attr(get_the_time('d')) . wp_kses_post($after);
                            } elseif (is_month()) {
                                echo '<li><a href="' . esc_url(get_year_link(get_the_time('Y'))) . '">' . esc_attr(get_the_time('Y')) . '</a></li> ' . wp_kses_post($delimiter) . ' ';
                                echo wp_kses_post($before) . esc_attr(get_the_time('F')) . wp_kses_post($after);
                            } elseif (is_year()) {
                                echo wp_kses_post($before) . esc_attr(get_the_time('Y')) . wp_kses_post($after);
                            } elseif (is_single() && !is_attachment()) {
                                while(have_posts()):
                                    the_post();
                                $archive_year  = get_the_time('Y');
                                $archive_month = get_the_time('m');
                                echo '<div class="entry-meta"><div class="author"><a href="'.esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ).'" title="'.esc_attr(get_the_author()).'">';
                                ?><span class="author-img"><?php echo get_avatar( get_the_author_meta( 'ID' ), 32, '', 'author-image', '' )?></span>

                            <?php echo '<span>'.get_the_author().'</span></a></div>';
                                 if ($comment_count >= 0 && comments_open()) {
                                     echo '<div class="comments"><a href="#respond"><i class="fa fa-comments-o"></i>';
                                     printf( _n( '<span>%s Comment</span>', '<span>%s Comments</span>', get_comments_number($post->ID), 'robolist-lite' ), number_format_i18n( get_comments_number($post->ID) ) );
                                     echo '</a></div>';
                                 }
                                echo '<div class="date"><a href="'.esc_url(get_month_link($archive_year,$archive_month)).'"><i class="fa fa-clock-o" aria-hidden="true"></i><span>'.esc_html(get_the_date()).'</span></a></div></div>';

                                endwhile;

                            } elseif (!is_single() &&!isset($_GET['rl_post_type']) && !is_page() && get_post_type() != 'post') {

                                $post_type = get_post_type_object(get_post_type());
                                if (!empty($post_type)) {
                                    echo wp_kses_post($before) . esc_html($post_type->labels->singular_name) . wp_kses_post($after);
                                }
                            } elseif (is_attachment()) {
                                $parent = get_post($post->post_parent);
                                $cat = get_the_category($parent->ID);
                                echo '<li><a href="' . esc_url(get_permalink($parent)) . '">' . esc_html($parent->post_title) . '</a></li> ' . wp_kses_post($delimiter) . ' ';
                                echo wp_kses_post($before) . esc_attr(get_the_title()) . wp_kses_post($after);
                            } elseif (is_page() && !$post->post_parent) {
                                echo wp_kses_post($before) . esc_attr(get_the_title()) . wp_kses_post($after);
                            } elseif (is_page() && $post->post_parent) {
                                $parent_id = $post->post_parent;
                                $breadcrumbs = array();
                                while ($parent_id) {
                                    $page = get_page($parent_id);
                                    $breadcrumbs[] = '<li><a href="' . esc_url(get_permalink($page->ID)) . '">' . get_the_title($page->ID) . '</a></li>';
                                    $parent_id = $page->post_parent;
                                }
                                $breadcrumbs = array_reverse($breadcrumbs);
                                foreach ($breadcrumbs as $crumb)
                                    echo wp_kses_post($crumb) . ' ' . wp_kses_post($delimiter) . ' ';
                                echo wp_kses_post($before) . get_the_title() . wp_kses_post($after);
                            } elseif (is_search()) {
                                echo wp_kses_post($before) . esc_html__("Search results for", "robolist-lite") . esc_html(get_search_query()) . '"' . wp_kses_post($after);

                            } elseif (is_tag()) {
                                echo wp_kses_post($before) . esc_html__('Tag', 'robolist-lite') . single_tag_title('', false) . wp_kses_post($after);
                            } elseif (is_author() ) {
                                global $author;
                                $userdata = get_userdata($author);
                                if((get_post_type() == 'post') && !isset($_GET['rl_post_type']))
                                echo wp_kses_post($before) . esc_html__("Articles posted by", "robolist-lite") . ' ' . esc_html($userdata->display_name) . wp_kses_post($after);
                                else
                                echo wp_kses_post($before) . esc_html__("Listing posted by", "robolist-lite") . ' ' . esc_html($userdata->display_name) . wp_kses_post($after);

                            } elseif (is_404()) {
                                echo wp_kses_post($before) . esc_html__("Error 404", "robolist-lite") . wp_kses_post($after);
                            } elseif (is_page_template('page-templates/template-contact.php')) {
                                echo wp_kses_post($before) . esc_attr(get_the_title()) . wp_kses_post($after);
                            }
                        }
                        echo '</ul>';
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
}
}

add_filter( 'shortcode_atts_gallery', 'robolist_lite_shortcode_atts_gallery' );

if (!function_exists('robolist_lite_shortcode_atts_gallery')) {

    function robolist_lite_shortcode_atts_gallery($out)
    {
        remove_filter(current_filter(), __FUNCTION__);
        $out['size'] = 'full';
        return $out;
    }
}

if (!function_exists('robolist_lite_strip_url_content')) {

    function robolist_lite_strip_url_content($post_id)
    {
        $content_post = get_post($post_id);
        $excerpt = $content_post->post_content;

        $excerpt = strip_shortcodes($excerpt);
        remove_shortcode('gallery');
        $excerpt = strip_tags($excerpt);
        $excerpt = apply_filters('the_content', $excerpt);


        $excerpt = preg_replace('/\s\s+/', ' ', $excerpt);
        $excerpt = preg_replace('#\[[^\]]+\]#', ' ', $excerpt);
        $strip = explode(' ', $excerpt);
        foreach ($strip as $key => $single) {
            if (!filter_var($single, FILTER_VALIDATE_URL) === false) {
                unset($strip[$key]);
            }
        }
        $excerpt = implode(' ', $strip);
        return $excerpt;
    }
}