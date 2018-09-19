<?php
/**
 * The template for displaying all pages
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site may use a
 * different template.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package robolist_lite
 */

get_header();
$col = 8;
$pageid ='';
$class = '';

if (in_array('wp-job-manager/wp-job-manager.php', apply_filters('active_plugins', get_option('active_plugins')))) {
    $pageid = get_option('job_manager_jobs_page_id');
    if(($pageid == get_the_ID())||get_option('job_manager_submit_job_form_page_id') == get_the_ID()||get_option('job_manager_job_dashboard_page_id') == get_the_ID()){
        $col = 12;
    }
}
if( in_array('wp-job-manager/wp-job-manager.php', apply_filters('active_plugins', get_option('active_plugins'))) && (get_option('job_manager_submit_job_form_page_id') == get_the_ID()) ) {
    $class = 'rl-job-preview';
    get_template_part('template-parts/content', 'job_preview');
}
else {
    ?>
    <div class="section page-section <?php echo esc_attr($class); ?>">
        <div class="container">
            <div class="row">
                <div class="col-md-<?php echo esc_attr($col); ?>">
                    <div id="primary" class="content-area">
                        <main id="main" class="site-main">

                            <?php
                            while (have_posts()) : the_post();

                                get_template_part('template-parts/content', 'page');

                                // If comments are open or we have at least one comment, load up the comment template.
                                if (comments_open() || get_comments_number()) :
                                    comments_template();
                                endif;

                            endwhile; // End of the loop.
                            ?>

                        </main><!-- #main -->
                    </div><!-- #primary -->
                </div>


                <?php if ($col == 8): ?>
                    <div class="col-md-4">
                        <?php
                        get_sidebar();
                        ?>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
    <?php
}
get_footer();
