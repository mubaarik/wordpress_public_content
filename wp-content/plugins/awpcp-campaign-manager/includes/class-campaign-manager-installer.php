<?php

function awpcp_campaign_manager_module_installer() {
    return new AWPCP_CampaignManagerModuleInstaller( $GLOBALS['wpdb'] );
}

class AWPCP_CampaignManagerModuleInstaller {

    private $db;

    private $campaigns_table;
    private $campaign_sections_table;
    private $campaign_section_positions_table;

    public function __construct( $db ) {
        $this->db = $db;

        $this->campaigns_table =
        "CREATE TABLE " . AWPCP_TABLE_CAMPAIGNS . " (
            `id` INT(10) NOT NULL AUTO_INCREMENT,
            `sales_representative_id` INT(10) NOT NULL,
            `start_date` DATETIME NOT NULL,
            `end_date` DATETIME NOT NULL,
            `creation_date` DATETIME NOT NULL,
            `status` VARCHAR(25) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'enabled',
            `is_placeholder` TINYINT(1) NOT NULL DEFAULT 0,
            PRIMARY KEY  (`id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;";

        $this->campaign_positions_table =
        "CREATE TABLE " . AWPCP_TABLE_CAMPAIGN_ADVERTISEMENT_POSITIONS . " (
            `campaign_id` INT(10) NOT NULL,
            `advertisement_position` VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
            `content_type` VARCHAR(25) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'text',
            `content` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
            `is_executable` TINYINT(1) NOT NULL DEFAULT 0,
            `image_path` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
            `image_link` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
            PRIMARY KEY  (`campaign_id`, `advertisement_position`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;";

        $this->campaign_sections_table =
        "CREATE TABLE " . AWPCP_TABLE_CAMPAIGN_SECTIONS . " (
            `id` INT(10) NOT NULL AUTO_INCREMENT,
            `campaign_id` INT(10) NOT NULL,
            `category_id` INT(10) NOT NULL,
            `pages` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
            PRIMARY KEY  (`id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;";

        $this->campaign_section_positions_table =
        "CREATE TABLE " . AWPCP_TABLE_CAMPAIGN_SECTION_ADVERTISEMENT_POSITIONS . " (
            `campaign_section_id` INT(10) NOT NULL,
            `advertisement_position` VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
            PRIMARY KEY  (`campaign_section_id`, `advertisement_position`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;";
    }

    public function install_or_upgrade( $module ) {
        if ( $this->is_new_installation( $module ) ) {
            $this->install_module( $module );
        } else {
            $this->upgrade_module( $module );
        }

        $this->create_sales_representative_role_if_not_exists();
        $this->add_manage_campaign_capability_to_administrators();

        update_option( 'awpcp-campaign-manager-installed-version', $module->version );
    }

    protected function is_new_installation( $module ) {
        return awpcp_table_exists( AWPCP_TABLE_CAMPAIGNS ) ? false : true;
    }

    protected function install_module( $module ) {
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

        dbDelta( $this->campaigns_table );
        dbDelta( $this->campaign_positions_table );
        dbDelta( $this->campaign_sections_table );
        dbDelta( $this->campaign_section_positions_table );

        // $this->insert_advertisement_positions();
    }

    protected function upgrade_module( $module ) {
    }

    private function create_sales_representative_role_if_not_exists() {
        add_role(
            'awpcp-sales-representative',
            __( 'Sales Representative', 'awpcp-campaign-manager' ),
            array_merge(
                get_role( 'subscriber' )->capabilities,
                array( AWPCP_CAMPAIGN_MANAGER_MODULE_SALES_REPRESENTATIVE_CAPABILITY => true )
            )
        );
    }

    private function add_manage_campaign_capability_to_administrators() {
        foreach ( awpcp_admin_roles_names() as $role_name ) {
            get_role( $role_name )->add_cap( AWPCP_CAMPAIGN_MANAGER_MODULE_SALES_REPRESENTATIVE_CAPABILITY );
        }
    }

    /**
     * TODO: use this to register the module settings
     */
    private function insert_advertisement_positions() {
        $this->db->insert( AWPCP_TABLE_ADVERTISEMENT_POSITIONS, array(
            'slug' => 'top',
            'name' => _x( 'Top', 'advertisement position name', 'awpcp-campaign-manager' ),
            'description' => __( 'The advertisements will be shown before the listings in the results page.', 'awpcp-campaign-manager' ),
            'width' => 960,
            'height' => 150,
        ) );

        $this->db->insert( AWPCP_TABLE_ADVERTISEMENT_POSITIONS, array(
            'slug' => 'middle',
            'name' => _x( 'Middle', 'advertisement position name', 'awpcp-campaign-manager' ),
            'description' => __( 'The advertisements will be shown after every five listings in the results page.', 'awpcp-campaign-manager' ),
            'width' => 960,
            'height' => 150,
        ) );

        $this->db->insert( AWPCP_TABLE_ADVERTISEMENT_POSITIONS, array(
            'slug' => 'bottom',
            'name' => _x( 'Bottom', 'advertisement position name', 'awpcp-campaign-manager' ),
            'description' => __( 'The advertisements will be shown after the listings in the results page.', 'awpcp-campaign-manager' ),
            'width' => 960,
            'height' => 150,
        ) );

        $this->db->insert( AWPCP_TABLE_ADVERTISEMENT_POSITIONS, array(
            'slug' => 'footer',
            'name' => _x( 'Footer', 'advertisement position name', 'awpcp-campaign-manager' ),
            'description' => __( 'The advertisements will be shown in the widgets that are configured to show advertisements from the Footer position.', 'awpcp-campaign-manager' ),
            'width' => 960,
            'height' => 150,
        ) );

        $this->db->insert( AWPCP_TABLE_ADVERTISEMENT_POSITIONS, array(
            'slug' => 'sidebar-one',
            'name' => _x( 'Sidebar One', 'advertisement position name', 'awpcp-campaign-manager' ),
            'description' => __( 'The advertisements will be shown in the widgets that are configured to show advertisements from the Sidebar One position.', 'awpcp-campaign-manager' ),
            'width' => 960,
            'height' => 150,
        ) );

        $this->db->insert( AWPCP_TABLE_ADVERTISEMENT_POSITIONS, array(
            'slug' => 'sidebar-two',
            'name' => _x( 'Sidebar Two', 'advertisement position name', 'awpcp-campaign-manager' ),
            'description' => __( 'The advertisements will be shown in the widgets that are configured to show advertisements from the Sidebar Two position.', 'awpcp-campaign-manager' ),
            'width' => 960,
            'height' => 150,
        ) );
    }
}
