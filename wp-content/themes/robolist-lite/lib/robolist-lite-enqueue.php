<?php
/**
 * Enqueue scripts and styles.
 */
function robolist_lite_scripts() {
    wp_enqueue_style( 'robolist-lite-style', get_stylesheet_uri() );

    wp_enqueue_style( 'robolist-custom-lite-style', get_template_directory_uri() . '/assets/css/robolist.css' );

    wp_enqueue_script( 'robolist-lite-navigation', get_template_directory_uri() . '/js/navigation.js', array(), '20151215', true );

    wp_enqueue_script( 'robolist-lite-skip-link-focus-fix', get_template_directory_uri() . '/js/skip-link-focus-fix.js', array(), '20151215', true );

    wp_enqueue_script( 'bootstrap', get_template_directory_uri() . '/assets/js/bootstrap.min.js', array('jquery'), '20151215', true );
    wp_enqueue_script( 'slick', get_template_directory_uri() . '/assets/js/slick.min.js', array('jquery'), '20151215', true );
    wp_enqueue_script( 'tabs', get_template_directory_uri() . '/assets/js/tabs.js', array(), '20151215', true );
    wp_enqueue_script( 'fitvids', get_template_directory_uri() . '/assets/js/fitvids.js', array(), '20151215', true );
    wp_enqueue_script( 'sticky-js', get_template_directory_uri() . '/assets/js/sticky-header.js', array(), '20151215', true );
    wp_enqueue_script( 'jarallax', get_template_directory_uri() . '/assets/js/jarallax.js', array(), '20151215', true );
    wp_enqueue_script( 'sidr-js', get_template_directory_uri() . '/assets/js/sidr.js', array(), '20151215', true );
    wp_enqueue_script( 'selectpicker', get_template_directory_uri() . '/assets/js/bootstrap-select.min.js', array(), '20151215', true );
    wp_enqueue_script( 'robolist-lite-app', get_template_directory_uri() . '/assets/js/app.js', array('jquery'), '20151215', true );

    wp_localize_script( 'robolist-lite-app', 'robolist_lite_params', array(
        'login_url' => rtrim( esc_url( wp_login_url() ) , '/'),
        'listings_page_url' => robolist_get_listings_page_url(),
        'strings' => array(
            'wp-job-manager-file-upload' => esc_html__( 'Add Photo', 'robolist-lite' ),
            'no_job_listings_found' => esc_html__( 'No results', 'robolist-lite' ),
            'results-no' => esc_html__( 'Results', 'robolist-lite'),
        )
    ) );

    if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
        wp_enqueue_script( 'comment-reply' );
    }
}
add_action( 'wp_enqueue_scripts', 'robolist_lite_scripts' );
