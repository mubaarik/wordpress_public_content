<?php

function awpcp_comments_ratings_module_installer() {
    return new AWPCP_CommentsRatingsModuleInstaller();
}

class AWPCP_CommentsRatingsModuleInstaller {

    public function install_or_upgrade( $module ) {
        if ( $this->is_new_installation() ) {
            $this->install_module( $module );
        } else {
            $this->upgrade_module( $module );
        }
    }

    protected function is_new_installation() {
        $comments_table_exists = awpcp_table_exists( AWPCP_TABLE_COMMENTS );
        $ratings_table_exists = awpcp_table_exists( AWPCP_TABLE_USER_RATINGS );
        return $comments_table_exists && $ratings_table_exists ? false : true;
    }

    protected function install_module( $module ) {
        global $wpdb;

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

        $sql = "CREATE TABLE IF NOT EXISTS " . AWPCP_TABLE_COMMENTS . " (
            id INT(10) AUTO_INCREMENT,
            ad_id INT(10) NOT NULL,
            user_id INT(10) NOT NULL,
            author_name VARCHAR(255) COLLATE utf8_general_ci NOT NULL DEFAULT '',
            author_site VARCHAR(255) COLLATE utf8_general_ci NOT NULL DEFAULT '',
            author_mail VARCHAR(255) COLLATE utf8_general_ci NOT NULL DEFAULT '',
            author_phone VARCHAR(255) COLLATE utf8_general_ci NOT NULL DEFAULT '',
            title VARCHAR(255) COLLATE utf8_general_ci NOT NULL DEFAULT '',
            comment LONGTEXT NOT NULL,
            status VARCHAR(50) COLLATE utf8_general_ci NOT NULL DEFAULT '',
            created DATETIME NOT NULL,
            updated DATETIME NOT NULL,
            is_spam TINYINT(1) NOT NULL DEFAULT 0,
            PRIMARY KEY  (id)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;";
        dbDelta($sql);

        $sql = "CREATE TABLE IF NOT EXISTS " . AWPCP_TABLE_USER_RATINGS . " (
            id INT(10) AUTO_INCREMENT,
            user_id INT(10) NOT NULL,
            user_ip VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
            ad_id INT(10) NOT NULL,
            rating DECIMAL(2,1),
            PRIMARY KEY  (id)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;";
        dbDelta($sql);

        return update_option( 'awpcp-comments-ratings-db-version', $module->version );
    }

    protected function upgrade_module( $module ) {
        $installed_version = $module->get_installed_version();

        if ( version_compare( $installed_version, '1.0.0' ) < 0) {
            $this->upgrade_to_1_0_0( $installed_version );
        }

        return update_option( 'awpcp-comments-ratings-db-version', $module->version );
    }

    private function upgrade_to_1_0_0($version) {
        global $wpdb;

        $tables = array(AWPCP_TABLE_COMMENTS, AWPCP_TABLE_USER_RATINGS);
        awpcp_fix_table_charset_and_collate($tables);

        if (!awpcp_column_exists(AWPCP_TABLE_USER_RATINGS, 'id')) {
            $wpdb->query('ALTER TABLE ' . AWPCP_TABLE_USER_RATINGS . ' DROP PRIMARY KEY');
            $wpdb->query('ALTER TABLE ' . AWPCP_TABLE_USER_RATINGS . ' ADD COLUMN id INT(10) AUTO_INCREMENT FIRST, ADD PRIMARY KEY (id)');
            $wpdb->query('ALTER TABLE ' . AWPCP_TABLE_USER_RATINGS . ' ADD INDEX users_ratings (user_id, ad_id), ADD INDEX guest_ratings (user_ip, ad_id)');
        }

        if (!awpcp_column_exists(AWPCP_TABLE_USER_RATINGS, 'user_ip')) {
            $wpdb->query("ALTER TABLE " . AWPCP_TABLE_USER_RATINGS . " ADD COLUMN user_ip VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' AFTER user_id");
        }
    }
}
