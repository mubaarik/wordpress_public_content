<?php

function awpcp_manage_category_icons_admin_page() {
    return new AWPCP_ManageCategoryIconsAdminPage(
        awpcp_categories_collection(),
        awpcp()->js,
        awpcp()->settings,
        awpcp_request()
    );
}

class AWPCP_ManageCategoryIconsAdminPage extends AWPCP_AdminPage {

    private $categories;
    private $javascript;
    private $settings;
    private $request;

    public function __construct( $categories, $javascript, $settings, $request ) {
        parent::__construct(
            'awpcp-admin-category-icons',
            awpcp_admin_page_title( __( 'Manage Category Icons', 'another-wordpress-classifieds-plugin' ) ),
            __( 'Manage Category Icons', 'another-wordpress-classifieds-plugin' )
        );

        $this->javascript = $javascript;
        $this->categories = $categories;
        $this->settings = $settings;
        $this->request = $request;
    }

    public function enqueue_scripts() {
        wp_enqueue_style( 'awpcp-category-icons-admin' );
        wp_enqueue_script( 'awpcp-category-icons-admin' );
    }

    public function dispatch() {
        try {
            echo $this->handle_category_icon_action();
        } catch ( AWPCP_Exception $e ) {
            debugp( $e );
            awpcp_flash( $e->format_errors(), 'error' );
        }

        return true; // returns true to stop Manage Category page handler
    }

    private function handle_category_icon_action() {
        $category = $this->get_current_category();

        if ( $this->request->post( 'set-category-icon' ) ) {
            return $this->set_category_icon( $category );
        } else if ( $this->request->post( 'clear-category-icon' ) ) {
            return $this->clear_category_icon( $category );
        } else {
            return $this->render_form( $category );
        }
    }

    private function get_current_category() {
        $category_id = $this->request->param( 'cat_ID' );

        if ( empty( $category_id ) ) {
            $message = __( 'The cat_ID parameter is empty. Please provide the ID of the category you want to modify.', 'awpcp-category-icons' );
            throw new AWPCP_Exception( $message );
        }

        return $this->categories->get( $category_id );
    }

    private function set_category_icon( $category ) {
        $category->icon = $this->request->post( 'selected-icon' );

        if ( $this->categories->save( $category ) ) {
            do_action( 'awpcp-category-edited', $category );
        }

        $message = __( 'The icon for category <category-name> was successfully set.', 'awpcp-category-icons' );
        $message = str_replace( '<category-name>', '<strong>' . $category->name . '</strong>', $message );
        awpcp_flash( $message );

        return $this->render_form( $category );
    }

    private function clear_category_icon( $category ) {
        $category->icon = '';

        if ( $this->categories->save( $category ) ) {
            do_action( 'awpcp-category-edited', $category );
        }

        $message = __( 'The icon for category <category-name> was successfully cleared.', 'awpcp-category-icons' );
        $message = str_replace( '<category-name>', '<strong>' . $category->name . '</strong>', $message );
        awpcp_flash( $message );

        return $this->render_form( $category );
    }

    private function render_form( $category ) {
        $this->javascript->set( 'category-icons-data', $this->get_category_icons_information( $category ) );
        $this->javascript->set( 'manage-category-icons-options', array(
            'standard_icon_base_url' => AWPCP_CATEGORY_ICONS_MODULE_URL . '/images/caticons/',
            'custom_icon_base_url' => $this->settings->get_runtime_option( 'awpcp-uploads-url' ) . '/custom-icons/',
            'flash_swf_url'       => includes_url( 'js/plupload/plupload.flash.swf' ),
            'silverlight_xap_url' => includes_url( 'js/plupload/plupload.silverlight.xap' ),
        ) );

        $params = array(
            'category' => $category,
            'standard_icons' => $this->get_standard_icons(),
        );
        $template = AWPCP_CATEGORY_ICONS_MODULE_DIR . '/templates/admin/manage-category-icons-admin-page.tpl.php';

        return $this->render( $template, $params );
    }

    private function get_category_icons_information( $category ) {
        return array(
            'selectedIcon' => $category->icon,
            'customIcons' => $this->get_custom_icons(),
        );
    }

    private function get_custom_icons() {
        $custom_icons_dir = $this->settings->get_runtime_option( 'awpcp-uploads-dir' ) . '/custom-icons/';
        $custom_icons_base_url = $this->settings->get_runtime_option( 'awpcp-uploads-url' ) . '/custom-icons/';
        $custom_icons = array();

        if ( file_exists( $custom_icons_dir ) && $handle = opendir( $custom_icons_dir ) ) {
            do {
                $filename = readdir( $handle );

                if ( $filename && $filename !== '.' && $filename !== '..' ) {
                    $custom_icons[] = array(
                        'id' => $filename,
                        'name' => $filename,
                        'url' => $custom_icons_base_url . $filename,
                    );
                }
            } while( $filename !== false );

            closedir( $handle );
        }

        return $custom_icons;
    }

    private function get_standard_icons() {
        return array(
            'accept',
            'add',
            'anchor',
            'application',
            'application_add',
            'application_cascade',
            'application_delete',
            'application_double',
            'application_edit',
            'application_error',
            'application_form',
            'application_form_add',
            'application_form_delete',
            'application_form_edit',
            'application_form_magnify',
            'application_get',
            'application_go',
            'application_home',
            'application_key',
            'application_lightning',
            'application_link',
            'application_osx',
            'application_osx_terminal',
            'application_put',
            'application_side_boxes',
            'application_side_contract',
            'application_side_expand',
            'application_side_list',
            'application_side_tree',
            'application_split',
            'application_tile_horizontal',
            'application_tile_vertical',
            'application_view_columns',
            'application_view_detail',
            'application_view_gallery',
            'application_view_icons',
            'application_view_list',
            'application_view_tile',
            'application_xp',
            'application_xp_terminal',
            'arrow_branch',
            'arrow_divide',
            'arrow_down',
            'arrow_in',
            'arrow_inout',
            'arrow_join',
            'arrow_left',
            'arrow_merge',
            'arrow_out',
            'arrow_redo',
            'arrow_refresh',
            'arrow_refresh_small',
            'arrow_right',
            'arrow_rotate_anticlockwise',
            'arrow_rotate_clockwise',
            'arrow_switch',
            'arrow_turn_left',
            'arrow_turn_right',
            'arrow_undo',
            'arrow_up',
            'asterisk_orange',
            'asterisk_yellow',
            'attach',
            'award_star_add',
            'award_star_bronze_1',
            'award_star_bronze_2',
            'award_star_bronze_3',
            'award_star_delete',
            'award_star_gold_1',
            'award_star_gold_2',
            'award_star_gold_3',
            'award_star_silver_1',
            'award_star_silver_2',
            'award_star_silver_3',
            'basket',
            'basket_add',
            'basket_delete',
            'basket_edit',
            'basket_error',
            'basket_go',
            'basket_put',
            'basket_remove',
            'bell',
            'bell_add',
            'bell_delete',
            'bell_error',
            'bell_go',
            'bell_link',
            'bin',
            'bin_closed',
            'bin_empty',
            'bomb',
            'book',
            'book_add',
            'book_addresses',
            'book_delete',
            'book_edit',
            'book_error',
            'book_go',
            'book_key',
            'book_link',
            'book_next',
            'book_open',
            'book_previous',
            'box',
            'brick',
            'brick_add',
            'brick_delete',
            'brick_edit',
            'brick_error',
            'brick_go',
            'brick_link',
            'bricks',
            'briefcase',
            'bug',
            'bug_add',
            'bug_delete',
            'bug_edit',
            'bug_error',
            'bug_go',
            'bug_link',
            'building',
            'building_add',
            'building_delete',
            'building_edit',
            'building_error',
            'building_go',
            'building_key',
            'building_link',
            'bullet_add',
            'bullet_arrow_bottom',
            'bullet_arrow_down',
            'bullet_arrow_top',
            'bullet_arrow_up',
            'bullet_black',
            'bullet_blue',
            'bullet_delete',
            'bullet_disk',
            'bullet_error',
            'bullet_feed',
            'bullet_go',
            'bullet_green',
            'bullet_key',
            'bullet_orange',
            'bullet_picture',
            'bullet_pink',
            'bullet_purple',
            'bullet_red',
            'bullet_star',
            'bullet_toggle_minus',
            'bullet_toggle_plus',
            'bullet_white',
            'bullet_wrench',
            'bullet_yellow',
            'cake',
            'calculator',
            'calculator_add',
            'calculator_delete',
            'calculator_edit',
            'calculator_error',
            'calculator_link',
            'calendar',
            'calendar_add',
            'calendar_delete',
            'calendar_edit',
            'calendar_link',
            'calendar_view_day',
            'calendar_view_month',
            'calendar_view_week',
            'camera',
            'camera_add',
            'camera_delete',
            'camera_edit',
            'camera_error',
            'camera_go',
            'camera_link',
            'camera_small',
            'cancel',
            'car',
            'car_add',
            'car_delete',
            'cart',
            'cart_add',
            'cart_delete',
            'cart_edit',
            'cart_error',
            'cart_go',
            'cart_put',
            'cart_remove',
            'cd',
            'cd_add',
            'cd_burn',
            'cd_delete',
            'cd_edit',
            'cd_eject',
            'cd_go',
            'chart_bar',
            'chart_bar_add',
            'chart_bar_delete',
            'chart_bar_edit',
            'chart_bar_error',
            'chart_bar_link',
            'chart_curve',
            'chart_curve_add',
            'chart_curve_delete',
            'chart_curve_edit',
            'chart_curve_error',
            'chart_curve_go',
            'chart_curve_link',
            'chart_line',
            'chart_line_add',
            'chart_line_delete',
            'chart_line_edit',
            'chart_line_error',
            'chart_line_link',
            'chart_organisation',
            'chart_organisation_add',
            'chart_organisation_delete',
            'chart_pie',
            'chart_pie_add',
            'chart_pie_delete',
            'chart_pie_edit',
            'chart_pie_error',
            'chart_pie_link',
            'clock',
            'clock_add',
            'clock_delete',
            'clock_edit',
            'clock_error',
            'clock_go',
            'clock_link',
            'clock_pause',
            'clock_play',
            'clock_red',
            'clock_stop',
            'cog',
            'cog_add',
            'cog_delete',
            'cog_edit',
            'cog_error',
            'cog_go',
            'coins',
            'coins_add',
            'coins_delete',
            'color_swatch',
            'color_wheel',
            'comment',
            'comment_add',
            'comment_delete',
            'comment_edit',
            'comments',
            'comments_add',
            'comments_delete',
            'compress',
            'computer',
            'computer_add',
            'computer_delete',
            'computer_edit',
            'computer_error',
            'computer_go',
            'computer_key',
            'computer_link',
            'connect',
            'contrast',
            'contrast_decrease',
            'contrast_high',
            'contrast_increase',
            'contrast_low',
            'control_eject',
            'control_eject_blue',
            'control_end',
            'control_end_blue',
            'control_equalizer',
            'control_equalizer_blue',
            'control_fastforward',
            'control_fastforward_blue',
            'control_pause',
            'control_pause_blue',
            'control_play',
            'control_play_blue',
            'control_repeat',
            'control_repeat_blue',
            'control_rewind',
            'control_rewind_blue',
            'control_start',
            'control_start_blue',
            'control_stop',
            'control_stop_blue',
            'controller',
            'controller_add',
            'controller_delete',
            'controller_error',
            'creditcards',
            'cross',
            'css',
            'css_add',
            'css_delete',
            'css_go',
            'css_valid',
            'cup',
            'cup_add',
            'cup_delete',
            'cup_edit',
            'cup_error',
            'cup_go',
            'cup_key',
            'cup_link',
            'cursor',
            'cut',
            'cut_red',
            'database',
            'database_add',
            'database_connect',
            'database_delete',
            'database_edit',
            'database_error',
            'database_gear',
            'database_go',
            'database_key',
            'database_lightning',
            'database_link',
            'database_refresh',
            'database_save',
            'database_table',
            'date',
            'date_add',
            'date_delete',
            'date_edit',
            'date_error',
            'date_go',
            'date_link',
            'date_magnify',
            'date_next',
            'date_previous',
            'delete',
            'disconnect',
            'disk',
            'disk_multiple',
            'door',
            'door_in',
            'door_open',
            'door_out',
            'drink',
            'drink_empty',
            'drive',
            'drive_add',
            'drive_burn',
            'drive_cd',
            'drive_cd_empty',
            'drive_delete',
            'drive_disk',
            'drive_edit',
            'drive_error',
            'drive_go',
            'drive_key',
            'drive_link',
            'drive_magnify',
            'drive_network',
            'drive_rename',
            'drive_user',
            'drive_web',
            'dvd',
            'dvd_add',
            'dvd_delete',
            'dvd_edit',
            'dvd_error',
            'dvd_go',
            'dvd_key',
            'dvd_link',
            'email',
            'email_add',
            'email_attach',
            'email_delete',
            'email_edit',
            'email_error',
            'email_go',
            'email_link',
            'email_open',
            'email_open_image',
            'emoticon_evilgrin',
            'emoticon_grin',
            'emoticon_happy',
            'emoticon_smile',
            'emoticon_surprised',
            'emoticon_tongue',
            'emoticon_unhappy',
            'emoticon_waii',
            'emoticon_wink',
            'error',
            'error_add',
            'error_delete',
            'error_go',
            'exclamation',
            'eye',
            'feed',
            'feed_add',
            'feed_delete',
            'feed_disk',
            'feed_edit',
            'feed_error',
            'feed_go',
            'feed_key',
            'feed_link',
            'feed_magnify',
            'female',
            'film',
            'film_add',
            'film_delete',
            'film_edit',
            'film_error',
            'film_go',
            'film_key',
            'film_link',
            'film_save',
            'find',
            'flag_blue',
            'flag_green',
            'flag_orange',
            'flag_pink',
            'flag_purple',
            'flag_red',
            'flag_yellow',
            'folder',
            'folder_add',
            'folder_bell',
            'folder_brick',
            'folder_bug',
            'folder_camera',
            'folder_database',
            'folder_delete',
            'folder_edit',
            'folder_error',
            'folder_explore',
            'folder_feed',
            'folder_find',
            'folder_go',
            'folder_heart',
            'folder_image',
            'folder_key',
            'folder_lightbulb',
            'folder_link',
            'folder_magnify',
            'folder_page',
            'folder_page_white',
            'folder_palette',
            'folder_picture',
            'folder_star',
            'folder_table',
            'folder_user',
            'folder_wrench',
            'font',
            'font_add',
            'font_delete',
            'font_go',
            'group',
            'group_add',
            'group_delete',
            'group_edit',
            'group_error',
            'group_gear',
            'group_go',
            'group_key',
            'group_link',
            'heart',
            'heart_add',
            'heart_delete',
            'help',
            'hourglass',
            'hourglass_add',
            'hourglass_delete',
            'hourglass_go',
            'hourglass_link',
            'house',
            'house_go',
            'house_link',
            'html',
            'html_add',
            'html_delete',
            'html_go',
            'html_valid',
            'image',
            'image_add',
            'image_delete',
            'image_edit',
            'image_link',
            'images',
            'information',
            'ipod',
            'ipod_cast',
            'ipod_cast_add',
            'ipod_cast_delete',
            'ipod_sound',
            'joystick',
            'joystick_add',
            'joystick_delete',
            'joystick_error',
            'key',
            'key_add',
            'key_delete',
            'key_go',
            'keyboard',
            'keyboard_add',
            'keyboard_delete',
            'keyboard_magnify',
            'layers',
            'layout',
            'layout_add',
            'layout_content',
            'layout_delete',
            'layout_edit',
            'layout_error',
            'layout_header',
            'layout_link',
            'layout_sidebar',
            'lightbulb',
            'lightbulb_add',
            'lightbulb_delete',
            'lightbulb_off',
            'lightning',
            'lightning_add',
            'lightning_delete',
            'lightning_go',
            'link',
            'link_add',
            'link_break',
            'link_delete',
            'link_edit',
            'link_error',
            'link_go',
            'lock',
            'lock_add',
            'lock_break',
            'lock_delete',
            'lock_edit',
            'lock_go',
            'lock_open',
            'lorry',
            'lorry_add',
            'lorry_delete',
            'lorry_error',
            'lorry_flatbed',
            'lorry_go',
            'lorry_link',
            'magifier_zoom_out',
            'magnifier',
            'magnifier_zoom_in',
            'male',
            'map',
            'map_add',
            'map_delete',
            'map_edit',
            'map_go',
            'map_magnify',
            'medal_bronze_1',
            'medal_bronze_2',
            'medal_bronze_3',
            'medal_bronze_add',
            'medal_bronze_delete',
            'medal_gold_1',
            'medal_gold_2',
            'medal_gold_3',
            'medal_gold_add',
            'medal_gold_delete',
            'medal_silver_1',
            'medal_silver_2',
            'medal_silver_3',
            'medal_silver_add',
            'medal_silver_delete',
            'money',
            'money_add',
            'money_delete',
            'money_dollar',
            'money_euro',
            'money_pound',
            'money_yen',
            'monitor',
            'monitor_add',
            'monitor_delete',
            'monitor_edit',
            'monitor_error',
            'monitor_go',
            'monitor_lightning',
            'monitor_link',
            'mouse',
            'mouse_add',
            'mouse_delete',
            'mouse_error',
            'music',
            'new',
            'newspaper',
            'newspaper_add',
            'newspaper_delete',
            'newspaper_go',
            'newspaper_link',
            'note',
            'note_add',
            'note_delete',
            'note_edit',
            'note_error',
            'note_go',
            'overlays',
            'package',
            'package_add',
            'package_delete',
            'package_go',
            'package_green',
            'package_link',
            'page',
            'page_add',
            'page_attach',
            'page_code',
            'page_copy',
            'page_delete',
            'page_edit',
            'page_error',
            'page_excel',
            'page_find',
            'page_gear',
            'page_go',
            'page_green',
            'page_key',
            'page_lightning',
            'page_link',
            'page_paintbrush',
            'page_paste',
            'page_red',
            'page_refresh',
            'page_save',
            'page_white',
            'page_white_acrobat',
            'page_white_actionscript',
            'page_white_add',
            'page_white_c',
            'page_white_camera',
            'page_white_cd',
            'page_white_code',
            'page_white_code_red',
            'page_white_coldfusion',
            'page_white_compressed',
            'page_white_copy',
            'page_white_cplusplus',
            'page_white_csharp',
            'page_white_cup',
            'page_white_database',
            'page_white_delete',
            'page_white_dvd',
            'page_white_edit',
            'page_white_error',
            'page_white_excel',
            'page_white_find',
            'page_white_flash',
            'page_white_freehand',
            'page_white_gear',
            'page_white_get',
            'page_white_go',
            'page_white_h',
            'page_white_horizontal',
            'page_white_key',
            'page_white_lightning',
            'page_white_link',
            'page_white_magnify',
            'page_white_medal',
            'page_white_office',
            'page_white_paint',
            'page_white_paintbrush',
            'page_white_paste',
            'page_white_php',
            'page_white_picture',
            'page_white_powerpoint',
            'page_white_put',
            'page_white_ruby',
            'page_white_stack',
            'page_white_star',
            'page_white_swoosh',
            'page_white_text',
            'page_white_text_width',
            'page_white_tux',
            'page_white_vector',
            'page_white_visualstudio',
            'page_white_width',
            'page_white_word',
            'page_white_world',
            'page_white_wrench',
            'page_white_zip',
            'page_word',
            'page_world',
            'paintbrush',
            'paintcan',
            'palette',
            'paste_plain',
            'paste_word',
            'pencil',
            'pencil_add',
            'pencil_delete',
            'pencil_go',
            'phone',
            'phone_add',
            'phone_delete',
            'phone_sound',
            'photo',
            'photo_add',
            'photo_delete',
            'photo_link',
            'photos',
            'picture',
            'picture_add',
            'picture_delete',
            'picture_edit',
            'picture_empty',
            'picture_error',
            'picture_go',
            'picture_key',
            'picture_link',
            'picture_save',
            'pictures',
            'pilcrow',
            'pill',
            'pill_add',
            'pill_delete',
            'pill_go',
            'plugin',
            'plugin_add',
            'plugin_delete',
            'plugin_disabled',
            'plugin_edit',
            'plugin_error',
            'plugin_go',
            'plugin_link',
            'printer',
            'printer_add',
            'printer_delete',
            'printer_empty',
            'printer_error',
            'rainbow',
            'report',
            'report_add',
            'report_delete',
            'report_disk',
            'report_edit',
            'report_go',
            'report_key',
            'report_link',
            'report_magnify',
            'report_picture',
            'report_user',
            'report_word',
            'resultset_first',
            'resultset_last',
            'resultset_next',
            'resultset_previous',
            'rosette',
            'rss',
            'rss_add',
            'rss_delete',
            'rss_go',
            'rss_valid',
            'ruby',
            'ruby_add',
            'ruby_delete',
            'ruby_gear',
            'ruby_get',
            'ruby_go',
            'ruby_key',
            'ruby_link',
            'ruby_put',
            'script',
            'script_add',
            'script_code',
            'script_code_red',
            'script_delete',
            'script_edit',
            'script_error',
            'script_gear',
            'script_go',
            'script_key',
            'script_lightning',
            'script_link',
            'script_palette',
            'script_save',
            'server',
            'server_add',
            'server_chart',
            'server_compressed',
            'server_connect',
            'server_database',
            'server_delete',
            'server_edit',
            'server_error',
            'server_go',
            'server_key',
            'server_lightning',
            'server_link',
            'server_uncompressed',
            'shading',
            'shape_align_bottom',
            'shape_align_center',
            'shape_align_left',
            'shape_align_middle',
            'shape_align_right',
            'shape_align_top',
            'shape_flip_horizontal',
            'shape_flip_vertical',
            'shape_group',
            'shape_handles',
            'shape_move_back',
            'shape_move_backwards',
            'shape_move_forwards',
            'shape_move_front',
            'shape_rotate_anticlockwise',
            'shape_rotate_clockwise',
            'shape_square',
            'shape_square_add',
            'shape_square_delete',
            'shape_square_edit',
            'shape_square_error',
            'shape_square_go',
            'shape_square_key',
            'shape_square_link',
            'shape_ungroup',
            'shield',
            'shield_add',
            'shield_delete',
            'shield_go',
            'sitemap',
            'sitemap_color',
            'sound',
            'sound_add',
            'sound_delete',
            'sound_low',
            'sound_mute',
            'sound_none',
            'spellcheck',
            'sport_8ball',
            'sport_basketball',
            'sport_football',
            'sport_golf',
            'sport_raquet',
            'sport_shuttlecock',
            'sport_soccer',
            'sport_tennis',
            'star',
            'status_away',
            'status_busy',
            'status_offline',
            'status_online',
            'stop',
            'style',
            'style_add',
            'style_delete',
            'style_edit',
            'style_go',
            'sum',
            'tab',
            'tab_add',
            'tab_delete',
            'tab_edit',
            'tab_go',
            'table',
            'table_add',
            'table_delete',
            'table_edit',
            'table_error',
            'table_gear',
            'table_go',
            'table_key',
            'table_lightning',
            'table_link',
            'table_multiple',
            'table_refresh',
            'table_relationship',
            'table_row_delete',
            'table_row_insert',
            'table_save',
            'table_sort',
            'tag',
            'tag_blue',
            'tag_blue_add',
            'tag_blue_delete',
            'tag_blue_edit',
            'tag_green',
            'tag_orange',
            'tag_pink',
            'tag_purple',
            'tag_red',
            'tag_yellow',
            'telephone',
            'telephone_add',
            'telephone_delete',
            'telephone_edit',
            'telephone_error',
            'telephone_go',
            'telephone_key',
            'telephone_link',
            'television',
            'television_add',
            'television_delete',
            'text_align_center',
            'text_align_justify',
            'text_align_left',
            'text_align_right',
            'text_allcaps',
            'text_bold',
            'text_columns',
            'text_dropcaps',
            'text_heading_1',
            'text_heading_2',
            'text_heading_3',
            'text_heading_4',
            'text_heading_5',
            'text_heading_6',
            'text_horizontalrule',
            'text_indent',
            'text_indent_remove',
            'text_italic',
            'text_kerning',
            'text_letter_omega',
            'text_letterspacing',
            'text_linespacing',
            'text_list_bullets',
            'text_list_numbers',
            'text_lowercase',
            'text_padding_bottom',
            'text_padding_left',
            'text_padding_right',
            'text_padding_top',
            'text_replace',
            'text_signature',
            'text_smallcaps',
            'text_strikethrough',
            'text_subscript',
            'text_superscript',
            'text_underline',
            'text_uppercase',
            'textfield',
            'textfield_add',
            'textfield_delete',
            'textfield_key',
            'textfield_rename',
            'thumb_down',
            'thumb_up',
            'tick',
            'time',
            'time_add',
            'time_delete',
            'time_go',
            'timeline_marker',
            'transmit',
            'transmit_add',
            'transmit_blue',
            'transmit_delete',
            'transmit_edit',
            'transmit_error',
            'transmit_go',
            'tux',
            'user',
            'user_add',
            'user_comment',
            'user_delete',
            'user_edit',
            'user_female',
            'user_go',
            'user_gray',
            'user_green',
            'user_orange',
            'user_red',
            'user_suit',
            'vcard',
            'vcard_add',
            'vcard_delete',
            'vcard_edit',
            'vector',
            'vector_add',
            'vector_delete',
            'wand',
            'weather_clouds',
            'weather_cloudy',
            'weather_lightning',
            'weather_rain',
            'weather_snow',
            'weather_sun',
            'webcam',
            'webcam_add',
            'webcam_delete',
            'webcam_error',
            'world',
            'world_add',
            'world_delete',
            'world_edit',
            'world_go',
            'world_link',
            'wrench',
            'wrench_orange',
            'xhtml',
            'xhtml_add',
            'xhtml_delete',
            'xhtml_go',
            'xhtml_valid',
            'zoom',
            'zoom_in',
            'zoom_out',
        );
    }
}
