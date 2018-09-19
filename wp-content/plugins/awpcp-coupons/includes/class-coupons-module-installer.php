<?php

function awpcp_coupons_module_installer() {
    return new AWPCP_CouponsModuleInstaller();
}

class AWPCP_CouponsModuleInstaller {

    public function install_or_upgrade( $module ) {
        if ( $this->is_new_installation() ) {
            $this->install_module( $module );
        } else {
            $this->upgrade_module( $module );
        }
    }

    protected function is_new_installation() {
        return awpcp_table_exists( AWPCP_TABLE_COUPONS ) ? false : true;
    }

    protected function install_module( $module ) {
        $plugin_version = $module->version;
        $installed_version = $module->get_installed_version();

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

        $sql = "CREATE TABLE " . AWPCP_TABLE_COUPONS . " (
            id INT(10) AUTO_INCREMENT,
            code VARCHAR(255) COLLATE utf8_general_ci NOT NULL,
            discount DECIMAL(10,2) NOT NULL,
            type VARCHAR(50) COLLATE utf8_general_ci NOT NULL DEFAULT 'amount',
            redemption_limit INT(10) NOT NULL DEFAULT 0,
            redemption_count INT(10) NOT NULL DEFAULT 0,
            expire_date DATE NOT NULL,
            enabled TINYINT(1) NOT NULL DEFAULT 1,
            PRIMARY KEY  (id)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;";
        dbDelta($sql);

        $this->after_upgrade_or_install( $plugin_version );
    }

    private function after_upgrade_or_install( $new_version ) {
        update_option( 'awpcp_coupons_db_version', $new_version );
    }

    protected function upgrade_module( $module ) {
        $this->after_upgrade_or_install( $module->version );
    }
}
