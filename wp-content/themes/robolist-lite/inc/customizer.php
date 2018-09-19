<?php
/**
 * robolist lite Theme Customizer
 *
 * @package robolist_lite
 */

/**
 * Add postMessage support for site title and description for the Theme Customizer.
 *
 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
 */
function robolist_lite_customize_register( $wp_customize ) {
	$wp_customize->get_setting( 'blogname' )->transport         = 'postMessage';
	$wp_customize->get_setting( 'blogdescription' )->transport  = 'postMessage';
	$wp_customize->get_setting( 'header_textcolor' )->transport = 'postMessage';

	if ( isset( $wp_customize->selective_refresh ) ) {
		$wp_customize->selective_refresh->add_partial( 'blogname', array(
			'selector'        => '.site-title a',
			'render_callback' => 'robolist_lite_customize_partial_blogname',
		) );
		$wp_customize->selective_refresh->add_partial( 'blogdescription', array(
			'selector'        => '.site-description',
			'render_callback' => 'robolist_lite_customize_partial_blogdescription',
		) );
	}
    $wp_customize->add_panel('robolist_lite_theme_option',array(
        'title' => esc_html__('Theme Options','robolist-lite'),
        'priority' => 3,
    ));
    $wp_customize->add_section('robolist_lite_general_option', array(
            'title' => esc_html__('General Options', 'robolist-lite'),
            'panel' => 'robolist_lite_theme_option',
            'priority' => 1,
        )
    );
    $wp_customize->add_section('robolist_lite_banner_option', array(
            'title' => esc_html__('Banner Options', 'robolist-lite'),
            'panel' => 'robolist_lite_theme_option',
            'priority' => 2,
        )
    );
    $wp_customize->add_section('robolist_lite_listing_option', array(
            'title' => esc_html__('Listing Section', 'robolist-lite'),
            'panel' => 'robolist_lite_theme_option',
            'priority' => 3,
        )
    );
    $wp_customize->add_section('robolist_lite_blog_option', array(
            'title' => esc_html__('Blog Options', 'robolist-lite'),
            'panel' => 'robolist_lite_theme_option',
            'priority' => 5,
        )
    );
    $wp_customize->add_section('robolist_lite_cta_option', array(
            'title' => esc_html__('CTA Options', 'robolist-lite'),
            'panel' => 'robolist_lite_theme_option',
            'priority' => 4,
        )
    );

    $wp_customize->add_section('robolist_lite_single_listing_option', array(
            'title' => esc_html__('Single Page Listing Options', 'robolist-lite'),
            'panel' => 'robolist_lite_theme_option',
            'priority' => 10,
        )
    );
    require get_template_directory() . '/inc/customizer/banner-options.php';
    require get_template_directory() . '/inc/customizer/theme-options.php';

}
add_action( 'customize_register', 'robolist_lite_customize_register' );

/**
 * Render the site title for the selective refresh partial.
 *
 * @return void
 */
function robolist_lite_customize_partial_blogname() {
	bloginfo( 'name' );
}

/**
 * Render the site tagline for the selective refresh partial.
 *
 * @return void
 */
function robolist_lite_customize_partial_blogdescription() {
	bloginfo( 'description' );
}

/**
 * Binds JS handlers to make Theme Customizer preview reload changes asynchronously.
 */
function robolist_lite_customize_preview_js() {
	wp_enqueue_script( 'robolist-lite-customizer', get_template_directory_uri() . '/js/customizer.js', array( 'customize-preview' ), '20151215', true );
}
add_action( 'customize_preview_init', 'robolist_lite_customize_preview_js' );
