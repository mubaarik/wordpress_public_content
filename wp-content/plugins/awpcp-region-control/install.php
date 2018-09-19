<?php

function awpcp_region_control_module_installer() {
    return new AWPCP_RegionControlModuleInstaller();
}

class AWPCP_RegionControlModuleInstaller {

    public function install_or_upgrade( $module ) {
        if ( $this->is_new_installation() ) {
            $this->install_module( $module );
        } else {
            $this->upgrade_module( $module );
        }
    }

    protected function is_new_installation() {
        return awpcp_table_exists( AWPCP_TABLE_REGIONS ) ? false : true;
    }

    protected function install_module( $module ) {
        global $wpdb;

        // First create and populate the regions table if it does not exist
        if (!checkfortable(AWPCP_TABLE_REGIONS)) {
            $wpdb->query("CREATE TABLE " . AWPCP_TABLE_REGIONS . " (
              `region_id` int(10) NOT NULL AUTO_INCREMENT,
              `region_type` int(10) NOT NULL,
              `region_state` tinyint(2) NOT NULL,
              `region_localized` tinyint(2) NOT NULL DEFAULT 0,
              `region_sidelisted` tinyint(2) NOT NULL DEFAULT 0,
              `region_name` varchar(255) COLLATE utf8_general_ci NOT NULL DEFAULT '',
              `region_parent` int(10) NOT NULL,
              `count_all` INT(10) NOT NULL DEFAULT 0,
              `count_enabled` INT(10) NOT NULL DEFAULT 0,
              PRIMARY KEY (`region_id`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

            ");

            $this->populate();
        }

        $this->after_upgrade_or_install( $module->version );
    }

    private function after_upgrade_or_install($newversion) {
        update_option( 'awpcp-flush-rewrite-rules', true );
        update_option('awpcp-region-control-db-version', $newversion);
    }

    protected function upgrade_module( $module ) {
        $oldversion = $module->get_installed_version();

        if (version_compare($oldversion, '2.0.1') < 0) {
            $this->upgrade_to_2_0_1($oldversion);
        }
        if (version_compare($oldversion, '2.1.1') < 0) {
            $this->upgrade_to_2_1_1($oldversion);
        }
        if (version_compare($oldversion, '3.1.0') < 0) {
            $this->upgrade_to_3_1_0($oldversion);
        }
        if ( version_compare( $oldversion, '3.2.19' ) < 0 ) {
            $this->upgrade_to_3_2_19( $oldversion );
        }

        $this->after_upgrade_or_install( $module->version );
    }

    private function upgrade_to_2_0_1($oldversion) {
        awpcp_fix_table_charset_and_collate(AWPCP_TABLE_REGIONS);
    }

    private function upgrade_to_2_1_1($oldversion) {
        global $wpdb;

        $wpdb->hide_errors();
        $result = $wpdb->query("SELECT `count_all` FROM " . AWPCP_TABLE_REGIONS);
        $wpdb->show_errors();

        if ($result === false) {
            $wpdb->query("ALTER TABLE " . AWPCP_TABLE_REGIONS . "  ADD `count_all` INT(10) NOT NULL DEFAULT 0");
        }

        $wpdb->hide_errors();
        $result = $wpdb->query("SELECT `count_enabled` FROM " . AWPCP_TABLE_REGIONS);
        $wpdb->show_errors();

        if ($result === false) {
            $wpdb->query("ALTER TABLE " . AWPCP_TABLE_REGIONS . "  ADD `count_enabled` INT(10) NOT NULL DEFAULT 0");
        }

        $wpdb->query('UPDATE ' . AWPCP_TABLE_REGIONS . ' SET `count_all` = 0, `count_enabled` = 0');

        update_option('awpcp-region-control-update-ad-count', true);
        delete_option('awpcp-region-control-ad-count-index');

        awpcp_regions_api()->clear_cache();
    }

    private function upgrade_to_3_1_0($oldversion) {
        awpcp_regions_api()->clear_cache();
    }

    private function upgrade_to_3_2_19( $oldversion ) {
        awpcp_regions_api()->clear_cache();
    }

    /**
     * For reference region type: 1 = continent 2 = country 3 = state/town 4 = city 5 = County/Village/Other
     * For reference region state: 1 = enabled 2 = disabled
     * For reference region localized: 1 = localized 0 = not localized
     * For reference region sidelisted: 1 = added to sidelist 0 = not added to sidelist
     * */
    public function populate( $base_region_id = 0 ) {
        global $wpdb;

        // Continents
        $continentslist = array('Africa','Asia','Australia & Oceania','Europe','North America','South America');
        foreach ($continentslist as $continent) {
            $continent = addslashes_mq($continent);
            $wpdb->query("INSERT INTO " . AWPCP_TABLE_REGIONS . " SET `region_type`=1, region_state=1, region_name='".$continent."', region_parent=0" );
        }

        // Countries Africa
        $countriesafrica=array('Algeria','Angola','Benin','Botswana','Burkina Faso','Burundi','Cameroon','Cape Verde','Central African Republic','Chad','Comoros',"Côte d'Ivoire",'Djibouti','Egypt','Equatorial Guinea','Eritrea','Ethiopia','Gabon','Gambia','Ghana','Guinea','Guinea-Bissau','Kenya','Lesotho','Liberia','Libya','Madagascar','Malawi','Mali','Mauritania','Mauritius','Morocco','Mozambique','Namibia','Niger','Nigeria','Republic of the Congo','Rwanda','Sao Tome and Principe','Senegal','Seychelles','Sierra Leone','Somalia','South Africa','Sudan','Swaziland','Tanzania','Togo','Tunisia','Uganda','Western Sahara','Zambia','Zimbabwe');
        foreach ($countriesafrica as $countryafrica) {
            $countryafrica = addslashes_mq($countryafrica);
            $wpdb->query("INSERT INTO " . AWPCP_TABLE_REGIONS . " SET `region_type`=2, region_state=1, region_name='".$countryafrica."', region_parent=" . ( $base_region_id + 1 ) );
        }

        // Countries Asia
        $countriesasia=array('Afghanistan','Armenia','Azerbaijan','Bahrain','Bangladesh','Bhutan','Brunei','Burma (Myanmar)','Cambodia','China','Georgia','Hong Kong','India','Indonesia','Iran','Iraq','Israel','Japan','Jordan','Kazakhstan','Korea, North','Korea, South','Kuwait','Kyrgyzstan','Laos','Lebanon','Malaysia','Maldives','Mongolia','Myanmar','Nepal','Oman','Pakistan','Philippines','Qatar','Russia','Saudi Arabia','Singapore','Sri Lanka','Syria','Taiwan','Tajikistan','Thailand','Turkey','Turkmenistan','United Arab Emirates','Uzbekistan','Vietnam','Yemen');
        foreach ($countriesasia as $countryasia) {
            $countryasia=addslashes_mq($countryasia);
            $wpdb->query("INSERT INTO " . AWPCP_TABLE_REGIONS . " SET `region_type`=2,region_state=1,region_name='".$countryasia."',region_parent=" . ( $base_region_id + 2 ) );
        }

        // Countries Australia
        $countriesaustralia=array('Australia','Fiji','Kiribati','Marshall Islands','Micronesia','Nauru','New Zealand','Palau','Papua New Guinea','Samoa','Solomon Islands','Tonga','Tuvalu','Vanuatu');
        foreach ($countriesaustralia as $countryaustralia) {
            $countryaustralia=addslashes_mq($countryaustralia);
            $wpdb->query("INSERT INTO " . AWPCP_TABLE_REGIONS . " SET `region_type`=2,region_state=1,region_name='".$countryaustralia."',region_parent=" . ( $base_region_id + 3 ) );
        }

        // Countries Europe
        $countrieseurope=array('Albania','Andorra','Austria','Belarus','Belgium','Bosnia and Herzegovina','Bulgaria','Croatia','Cyprus','Czech Republic','Denmark','Estonia','Finland','France','Germany','Greece','Hungary','Iceland','Ireland','Italy','Latvia','Liechtenstein','Lithuania','Luxembourg','Macedonia','Malta','Moldova','Monaco','Netherlands','Norway','Poland','Portugal','Romania','Russia','San Marino','Serbia and Montenegro','Slovakia (Slovak Republic)','Slovenia','Spain','Sweden','Switzerland','Turkey','Ukraine','United Kingdom','Vatican City');
        foreach ($countrieseurope as $countryeurope) {
            $countryeurope=addslashes_mq($countryeurope);
            $wpdb->query("INSERT INTO " . AWPCP_TABLE_REGIONS . " SET `region_type`=2,region_state=1,region_name='".$countryeurope."',region_parent=" . ( $base_region_id + 4 ) );
        }

        // Countries  North America
        $countriesnorthamerica=array('Antigua and Barbuda','The Bahamas','Barbados','Belize','Canada','Costa Rica','Cuba','Dominica','Dominican Republic','El Salvador','Greenland (Kalaallit Nunaat)','Grenada','Guatemala','Haiti','Honduras','Jamaica','Mexico','Nicaragua','Panama','Saint Kitts and Nevis','Saint Lucia','Saint Vincent and the Grenadines','Trinidad and Tobago','USA');
        foreach ($countriesnorthamerica as $countrynorthamerica) {
            $countrynorthamerica=addslashes_mq($countrynorthamerica);
            $wpdb->query("INSERT INTO " . AWPCP_TABLE_REGIONS . " SET `region_type`=2,region_state=1,region_name='".$countrynorthamerica."',region_parent=" . ( $base_region_id + 5 ) );
        }

        // Countries South America
        $countriessouthamerica=array('Argentina','Bolivia','Brazil','Chile','Colombia','Ecuador','French Guiana','Guyana','Paraguay','Peru','Suriname','Uruguay','Venezuela');
        foreach ($countriessouthamerica as $countrysouthamerica) {
            $countrysouthamerica=addslashes_mq($countrysouthamerica);
            $wpdb->query("INSERT INTO " . AWPCP_TABLE_REGIONS . " SET `region_type`=2,region_state=1,region_name='".$countrysouthamerica."',region_parent=" . ( $base_region_id + 6 ) );
        }
    }

    /**
     * XXX: probably unused?
     */
    public function uninstall_module() {
        global $wpdb;

        $wpdb->query("DROP TABLE IF EXISTS " . AWPCP_TABLE_REGIONS);

        delete_option('awpcp-region-control-db-version');
    }
}
