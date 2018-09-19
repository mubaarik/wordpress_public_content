<?php
$robolist_lite_setting = robolist_lite_get_theme_options();

$wp_customize->add_setting('robolist_list_theme_options[banner_title]',
    array(
        'type' => 'option',
        'default' => $robolist_lite_setting['banner_title'],
        'sanitize_callback' => 'sanitize_text_field',
    )
);
$wp_customize->add_control('robolist_list_theme_options[banner_title]',
    array(
        'type' => 'text',
        'section' => 'robolist_lite_banner_option',
        'label' => esc_html__('Banner Title', 'robolist-lite'),
        'settings' => 'robolist_list_theme_options[banner_title]'
    )
);
$wp_customize->add_setting('robolist_list_theme_options[banner_description]',
    array(
        'type' => 'option',
        'sanitize_callback' => 'sanitize_textarea_field',
        'default' => $robolist_lite_setting['banner_description'],
    )
);
$wp_customize->add_control('robolist_list_theme_options[banner_description]',
    array(
        'type' => 'text',
        'section' => 'robolist_lite_banner_option',
        'label' => esc_html__('Banner Description', 'robolist-lite'),
        'settings' => 'robolist_list_theme_options[banner_description]'
    )
);
$wp_customize->add_setting('robolist_list_theme_options[banner_image]',
    array(
        'type' => 'option',
        'default' => $robolist_lite_setting['banner_image'],
        'sanitize_callback' => 'esc_url_raw',
    )
);
$wp_customize->add_control(
    new WP_Customize_Image_Control(
        $wp_customize,
        'robolist_list_theme_options[banner_image]',
        array(
            'label' => esc_html__('Add Background Image', 'robolist-lite'),
            'section' => 'robolist_lite_banner_option',
            'settings' => 'robolist_list_theme_options[banner_image]',
        ))
);
