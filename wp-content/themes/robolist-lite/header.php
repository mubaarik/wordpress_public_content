<?php
/**
 * The header for our theme
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package robolist-lite
 */

?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="http://gmpg.org/xfn/11">

	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php
$robolist_lite_setting  = robolist_lite_get_theme_options();
$banner_title           = $robolist_lite_setting['banner_title'];
$banner_description     = $robolist_lite_setting['banner_description'];
$banner_image           = $robolist_lite_setting['banner_image'];
if (in_array('wp-job-manager/wp-job-manager.php', apply_filters('active_plugins', get_option('active_plugins')))) {
    $post_job = get_option('job_manager_submit_job_form_page_id');
    $post_dashboard = get_option('job_manager_job_dashboard_page_id');
}
$current_user = wp_get_current_user();
$head_class = '';
if(isset($_GET['rl_favorite']))
$head_class = 'my-favorite';
?>
<!-- Header -->

<div id="sidr" class="mobile-menu">
    <div class="menu-close">
        <i class="fa fa-close"></i>
    </div>
    <?php
    wp_nav_menu(array(
        'theme_location' => 'primary',
        'container' => 'ul',
        'menu_class' => '',
        'walker' => new Robolist_Lite_Nav_Walker(),
        'fallback_cb' => 'Robolist_Lite_Nav_Walker::fallback'
    ));
    ?>
</div>


<header id="top" class="header hero <?php echo esc_attr($head_class); ?>">
    <!-- Start of Naviation -->
    <div class="nav-wrapper">
        <div class="container">
            <div class="col-md-2">

                <div class="site-branding">
                    <?php
                    the_custom_logo();
                    ?>
                        <h1 class="site-title"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></h1>
                        <?php
                    $description = get_bloginfo( 'description', 'display' );
                    if ( $description || is_customize_preview() ) : ?>
                        <p class="site-description"><?php echo $description; /* WPCS: xss ok. */ ?></p>
                        <?php
                    endif; ?>
                </div><!-- .site-branding -->
                <div class="show-mobile">

                    <ul class="nav navbar-nav">
                        <?php
                        if(is_user_logged_in()){
                            if( in_array('wp-job-manager/wp-job-manager.php', apply_filters('active_plugins', get_option('active_plugins'))) ) {
                                echo '<li id="sign-out" class="signout">';
                                echo '<a class="dropdown-toggle" data-toggle="dropdown" href="#">';
                                if(get_option('job_manager_job_dashboard_page_id'))
                                    echo '<span data-toggle="tooltip" data-placement="left" title="' . esc_attr__('Hey,', 'robolist-lite') . esc_attr($current_user->display_name) . '">';
                                echo get_avatar($current_user->user_email, 32);
                                echo '</span></a>';
                                echo '<ul class="dropdown-menu">';
                                if (in_array('wp-job-manager/wp-job-manager.php', apply_filters('active_plugins', get_option('active_plugins'))) && (get_option('job_manager_job_dashboard_page_id'))) {
                                    echo '<li><a href="' . esc_url(get_page_link($post_dashboard)) . '">' . esc_html__('Your Listings', 'robolist-lite') . '</a></li>';
                                }
                                if( in_array('favorites/favorites.php', apply_filters('active_plugins', get_option('active_plugins'))))
                                    echo '<li><a href="' . esc_url(get_site_url() . '/?rl_favorite=my-favorites') . '">' . esc_html__('My Favorites', 'robolist-lite') . '</a></li>';
                                echo '<li><a href="' . esc_url(get_edit_user_link()) . '">' . esc_html__('Edit Profile', 'robolist-lite') . '</a></li>';
                                echo '<li><a href="' . esc_url(wp_logout_url()) . '">' . esc_html__('Sign Out', 'robolist-lite') . '</a></li>';
                                echo '</ul></li>';
                            }
                        }else{
                            echo '<li class="signin"><a href="'.esc_url(site_url('/wp-admin')).'"><i class="fa fa-user" aria-hidden="true"></i>'.esc_html__('Sign In','robolist-lite').'</a></li>';
                        }
                        ?>
                        <?php
                        if (in_array('wp-job-manager/wp-job-manager.php', apply_filters('active_plugins', get_option('active_plugins')))):
                            ?>
                            <li class="addlisting"><a href="<?php echo esc_url(get_page_link($post_job)); ?>"><i class="fa fa-plus-circle" aria-hidden="true"></i><?php echo esc_html__('Add Listing','robolist-lite') ?></a></li>
                        <?php endif; ?>
                    </ul>
                </div>
                <div class="navbar-header">
                    <a id="simple-menu" class="ninja-btn menu-btn pull-right" href="#sidr"><span></span></a>
                </div>
            </div>
            <div class="col-md-7">
                <nav id="primary-nav" class="navbar navbar-default">
                    <!-- Brand and toggle get grouped for better mobile display -->

                    <!-- Collect the nav links, forms, and other content for toggling -->
                    <div class="collapse navbar-collapse" id="navbar-collapse">

                        <?php wp_nav_menu( array(
                            'theme_location' 		=> 'primary',
                            'menu_class' 			=> 'nav navbar-nav',
                            'container'  			=> 'ul',
                            'fallback_cb'       	=> 'Robolist_Lite_Nav_Walker::fallback',
                            'walker'            	=> new Robolist_Lite_Nav_Walker()
                        )); ?>
                    </div><!-- End navbar-collapse -->
                </nav>
            </div>
            <div class="col-md-3">
                <ul class="nav navbar-nav">
                    <?php
                    if(is_user_logged_in()){
                        if( in_array('wp-job-manager/wp-job-manager.php', apply_filters('active_plugins', get_option('active_plugins'))) ) {
                            echo '<li id="sign-out" class="signout">';
                            echo '<a class="dropdown-toggle" data-toggle="dropdown" href="#">';
                            if(get_option('job_manager_job_dashboard_page_id'))
                                echo '<span data-toggle="tooltip" data-placement="left" title="' . esc_attr__('Hey,', 'robolist-lite') . esc_attr($current_user->display_name) . '">';
                            echo get_avatar($current_user->user_email, 32);
                            echo '</span></a>';
                            echo '<ul class="dropdown-menu">';
                            if (in_array('wp-job-manager/wp-job-manager.php', apply_filters('active_plugins', get_option('active_plugins'))) && (get_option('job_manager_job_dashboard_page_id'))) {
                                echo '<li><a href="' . esc_url(get_page_link($post_dashboard)) . '">' . esc_html__('Your Listings', 'robolist-lite') . '</a></li>';
                            }
                            if( in_array('favorites/favorites.php', apply_filters('active_plugins', get_option('active_plugins'))))
                                echo '<li><a href="' . esc_url(get_site_url() . '/?rl_favorite=my-favorites') . '">' . esc_html__('My Favorites', 'robolist-lite') . '</a></li>';
                            echo '<li><a href="' . esc_url(get_edit_user_link()) . '">' . esc_html__('Edit Profile', 'robolist-lite') . '</a></li>';
                            echo '<li><a href="' . esc_url(wp_logout_url()) . '">' . esc_html__('Sign Out', 'robolist-lite') . '</a></li>';
                            echo '</ul></li>';
                        }
                    }else{
                        echo '<li class="signin"><a href="'.esc_url(site_url('/wp-admin')).'"><i class="fa fa-user" aria-hidden="true"></i>'.esc_html__('Sign In','robolist-lite').'</a></li>';
                    }
                    ?>
                    <?php
                    if (in_array('wp-job-manager/wp-job-manager.php', apply_filters('active_plugins', get_option('active_plugins')))):
                    ?>
                    <li class="addlisting"><a href="<?php echo esc_url(get_page_link($post_job)); ?>"><i class="fa fa-plus-circle" aria-hidden="true"></i><?php echo esc_html__('Add Listing','robolist-lite') ?></a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
    <!-- End of Navigation -->

    <?php
    if(is_page_template('page-templates/template-home.php') && !isset($_GET['rl_favorite']) ){
        if($banner_image){
            $background_style = "style='background-image:url(" . esc_url($banner_image) . ")'";
        }
        else{
            $background_style = "";
        }
    ?>

    <div class="banner-wrapper parallax" <?php echo wp_kses_post($background_style); ?>>
        <div class="row">
            <div class="banner-text-wrap">
                <?php
                if($banner_title || $banner_description){
                    if($banner_title)
                        echo '<h2>'.esc_html($banner_title).'</h2>';
                    if($banner_description)
                        echo '<p>'.esc_html($banner_description).'</p>';
                }
                ?>
                    <?php  do_action('robolist_lite_header_search'); ?>
            </div>
        </div>
    </div>
    <?php }
    else{
        if(!is_singular( 'job_listing' )  && (!is_page_template('page-templates/template-home.php') || isset($_GET['rl_favorite']) ) && (get_option('job_manager_submit_job_form_page_id') != get_the_ID()) ){
            robolist_lite_breadcrumb();
        }
    }?>
</header>
