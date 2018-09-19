<?php
/*
 * Template Name: Frontpage
 *
 */

get_header();
if (in_array('wp-job-manager/wp-job-manager.php', apply_filters('active_plugins', get_option('active_plugins')))){
    if(!isset($_GET['rl_favorite'])){
        get_template_part('template-parts/homepage/listing','section');
    }
    else{
        get_template_part('template-parts/content', 'user_listing');
    }
}
if(!isset($_GET['rl_favorite']))
get_template_part('template-parts/homepage/blog','section');
get_footer();