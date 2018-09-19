<?php
$robolist_lite_setting = robolist_lite_get_theme_options();

 $wp_customize->add_setting('robolist_list_theme_options[listing_des]', array(
            'default' => $robolist_lite_setting['listing_des'],
            'capability' => 'edit_theme_options',
            'sanitize_callback' => 'robolist_sanitize_page',
            'type' => 'option',
        ));
        $wp_customize->add_control('robolist_list_theme_options[listing_des]', array(
            'label' => esc_html__('Select A Pages To Show Listing Title & Description', 'robolist-lite'),
            'section' => 'robolist_lite_listing_option',
            'priority' => 3,
            'type'     => 'dropdown-pages'

        ));


$wp_customize->add_setting('robolist_list_theme_options[blog_description]',
    array(
        'type' => 'option',
        'sanitize_callback' => 'robolist_sanitize_page',
        'default' => $robolist_lite_setting['blog_description'],
    )
);
$wp_customize->add_control('robolist_list_theme_options[blog_description]',
    array(
        'type' => 'dropdown-pages',
        'section' => 'robolist_lite_blog_option',
        'label' => esc_html__('Select Page For Blog Title & Description', 'robolist-lite'),
        'settings' => 'robolist_list_theme_options[blog_description]'
    )
);


$wp_customize->add_setting('robolist_list_theme_options[cta_description]',
    array(
        'type' => 'option',
        'default' => $robolist_lite_setting['cta_description'],
        'sanitize_callback' => 'robolist_sanitize_page',
    )
);
$wp_customize->add_control('robolist_list_theme_options[cta_description]',
    array(
        'type' => 'dropdown-pages',
        'section' => 'robolist_lite_cta_option',
        'label' => esc_html__('Select Page For CTA Title And Description', 'robolist-lite'),
        'settings' => 'robolist_list_theme_options[cta_description]'
    )
);
$wp_customize->add_setting('robolist_list_theme_options[cta_button]',
    array(
        'type' => 'option',
        'default' => $robolist_lite_setting['cta_button'],
        'sanitize_callback' => 'sanitize_text_field',
    )
);
$wp_customize->add_control('robolist_list_theme_options[cta_button]',
    array(
        'type' => 'text',
        'section' => 'robolist_lite_cta_option',
        'label' => esc_html__('CTA Button Text', 'robolist-lite'),
        'settings' => 'robolist_list_theme_options[cta_button]'
    )
);
$wp_customize->add_setting('robolist_list_theme_options[cta_link]',
    array(
        'type' => 'option',
        'default' => $robolist_lite_setting['cta_link'],
        'sanitize_callback' => 'esc_url_raw',
    )
);
$wp_customize->add_control('robolist_list_theme_options[cta_link]',
    array(
        'type' => 'text',
        'section' => 'robolist_lite_cta_option',
        'label' => esc_html__('CTA Button Link', 'robolist-lite'),
        'settings' => 'robolist_list_theme_options[cta_link]'
    )
);
$wp_customize->add_setting('robolist_list_theme_options[robolist_list_reset_all]',
    array(
        'default' => 0,
        'capability' => 'edit_theme_options',
        'sanitize_callback' => 'robolist_lite_reset_alls',
        'transport' => 'postMessage',
    ));

$wp_customize->add_control('robolist_list_theme_options[robolist_list_reset_all]',
    array(
        'priority' => 1,
        'label' => esc_html__('Reset all default settings. (Refresh it to view the effect)', 'robolist-lite'),
        'section' => 'robolist_lite_general_option',
        'type' => 'checkbox',
    ));

$wp_customize->add_setting('robolist_list_theme_options[single_joblist_form_enable]',
    array(
        'default' => $robolist_lite_setting['single_joblist_form_enable'],
        'type' => 'option',
        'capability' => 'edit_theme_options',
        'sanitize_callback' => 'robolist_lite_sanitize_checkbox',
    ));
$wp_customize->add_control('robolist_list_theme_options[single_joblist_form_enable]',
    array(
        'priority' => 1,
        'label' => esc_html__('Enable Contact Form', 'robolist-lite'),
        'section' => 'robolist_lite_single_listing_option',
        'type' => 'checkbox',
    ));
$wp_customize->add_setting('robolist_list_theme_options[single_joblist_form]',
    array(
        'default' => $robolist_lite_setting['single_joblist_form'],
        'type' => 'option',
        'capability' => 'edit_theme_options',
        'sanitize_callback' => 'sanitize_text_field',
    ));
$wp_customize->add_control('robolist_list_theme_options[single_joblist_form]',
    array(
        'priority' => 1,
        'label' => esc_html__('Contact Form Shortcode', 'robolist-lite'),
        'section' => 'robolist_lite_single_listing_option',
        'type' => 'text',
    ));
$wp_customize->add_setting('robolist_list_theme_options[single_form_title]',
    array(
        'default' => $robolist_lite_setting['single_form_title'],
        'type' => 'option',
        'capability' => 'edit_theme_options',
        'sanitize_callback' => 'sanitize_text_field',
    ));
$wp_customize->add_control('robolist_list_theme_options[single_form_title]',
    array(
        'priority' => 1,
        'label' => esc_html__('Contact Form Title', 'robolist-lite'),
        'description' => esc_html__('Form title will only be displayed if above shortcode is not empty.', 'robolist-lite'),
        'section' => 'robolist_lite_single_listing_option',
        'type' => 'text',
    ));