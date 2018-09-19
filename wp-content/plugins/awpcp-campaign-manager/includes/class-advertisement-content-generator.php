<?php

function awpcp_advertisement_content_generator() {
    return new AWPCP_AdvertisementContentGenerator(
        awpcp_campaign_advertisement_position_logic_factory(),
        awpcp_campaign_advertisement_positions_collection(),
        $GLOBALS['wpdb']
    );
}

class AWPCP_AdvertisementContentGenerator {

    private $active_campaigns_information;
    private $placeholder_positions;

    private $position_logic_factory;
    private $advertisement_positions;
    private $db;

    public function __construct( $position_logic_factory, $advertisement_positions, $db ) {
        $this->position_logic_factory = $position_logic_factory;
        $this->advertisement_positions = $advertisement_positions;
        $this->db = $db;
    }

    public function generate_advertisement_content( $category, $page, $position ) {
        $active_campaigns_information = $this->get_active_campaigns_information( $category, $page );

        if ( ! isset( $active_campaigns_information[ $position ] ) ) {
            return $this->generate_default_advertisement_content( $position );
        }

        $position_campaigns_information = $active_campaigns_information[ $position ];

        if ( count( $position_campaigns_information ) > 1 ) {
            $position_information = $position_campaigns_information[ array_rand( $position_campaigns_information ) ];
        } else {
            $position_information = $position_campaigns_information[ 0 ];
        }

        return $this->generate_content_from_information( $position_information );
    }

    private function get_active_campaigns_information( $category, $page ) {
        if ( is_null( $this->active_campaigns_information ) ) {
            $this->active_campaigns_information = $this->get_active_campaigns_information_from_database( $category, $page );
        }

        return $this->active_campaigns_information;
    }

    private function get_active_campaigns_information_from_database( $category, $page ) {
        $sql = 'SELECT DISTINCT c.*, cs.pages, cp.* ';
        $sql.= 'FROM ' . AWPCP_TABLE_CAMPAIGNS . ' AS c ';
        $sql.= 'JOIN ' . AWPCP_TABLE_CAMPAIGN_SECTIONS . ' AS cs ON ( c.id = cs.campaign_id ) ';
        $sql.= 'JOIN ' . AWPCP_TABLE_CAMPAIGN_SECTION_ADVERTISEMENT_POSITIONS . ' AS csp ON ( cs.id = csp.campaign_section_id ) ';
        $sql.= 'JOIN ' . AWPCP_TABLE_CAMPAIGN_ADVERTISEMENT_POSITIONS . ' AS cp ON ( csp.advertisement_position = cp.advertisement_position AND c.id = cp.campaign_id ) ';
        $sql.= "WHERE c.start_date <= CURDATE() AND c.end_date >= CURDATE() AND c.status = 'enabled' AND cs.category_id = %d";

        $campaigns_information = $this->db->get_results( $this->db->prepare( $sql, $category ) );

        $active_campaigns_information = array();
        foreach ( $campaigns_information as $campaign_information ) {
            $campaign_pages = maybe_unserialize( $campaign_information->pages );

            if ( ! in_array( $page, $campaign_pages ) ) {
                continue;
            }

            $active_campaigns_information[ $campaign_information->advertisement_position ][] = $campaign_information;
        }

        return $active_campaigns_information;
    }

    private function generate_default_advertisement_content( $position ) {
        $placeholder_positions = $this->get_placeholders_positions();

        if ( isset( $placeholder_positions[ $position ] ) ) {
            $content = $this->generate_content_from_advertisement_position( $placeholder_positions[ $position ] );
        } else {
            $content = '';
        }

        return $content;
    }

    private function get_placeholders_positions() {
        if ( ! is_null( $this->placeholder_positions ) ) {
            return $this->placeholder_positions;
        } else {
            $this->placeholder_positions = array();
        }

        $placeholder_positions = $this->advertisement_positions->find_placeholder_positions();

        foreach( $placeholder_positions as $placeholder_position ) {
            $this->placeholder_positions[ $placeholder_position->get_slug() ] = $placeholder_position;
        }

        return $this->placeholder_positions;
    }

    private function get_placeholders_positions_information_from_database() {
        $sql = 'SELECT DISTINCT cp.* ';
        $sql.= 'FROM ' . AWPCP_TABLE_CAMPAIGNS . ' AS c ';
        $sql.= 'JOIN ' . AWPCP_TABLE_CAMPAIGN_ADVERTISEMENT_POSITIONS . ' AS cp ON ( c.id = cp.campaign_id ) ';
        $sql.= "WHERE c.is_placeholder = 1 AND c.status = 'enabled'";

        $campaigns = $this->db->get_results( $this->db->prepare( $sql, $category ) );

        $active_campaigns_information = array();
        foreach ( $campaigns as $campaign ) {
            $campaign_pages = maybe_unserialize( $campaign->pages );

            if ( ! in_array( $page, $campaign_pages ) ) {
                continue;
            }

            $active_campaigns_information[ $campaign->advertisement_position ][] = $campaign;
        }

        return $active_campaigns_information;
    }

    private function generate_content_from_information( $campaign ) {
        $advertisement_position = $this->position_logic_factory->create_campaign_advertisement_position_logic( $campaign );
        return $this->generate_content_from_advertisement_position( $advertisement_position );
    }

    private function generate_content_from_advertisement_position( $advertisement_position ) {
        if ( $advertisement_position->is_custom_content() && $advertisement_position->is_content_executable() ) {
            return $this->execute_campaign_content( $advertisement_position );
        } else if ( $advertisement_position->is_custom_content() ){
            return $this->print_campaign_content( $advertisement_position );
        } else {
            return $this->render_campaign_banner( $advertisement_position );
        }
    }

    private function execute_campaign_content( $advertisement_position ) {
        ob_start();
        eval( '?>' . $advertisement_position->get_content() );
        $output = ob_get_contents();
        ob_end_clean();

        return $output;
    }

    private function print_campaign_content( $advertisement_position ) {
        return $advertisement_position->get_content();
    }

    private function render_campaign_banner( $advertisement_position ) {
        $image_link = $this->get_image_link( $advertisement_position );
        $image_url = $advertisement_position->get_image_url();

        $image_tag = '<img src="' . esc_attr( $image_url ) . '" style="max-width: 100%; width: 100%;">';

        if ( empty( $image_link ) ) {
            $content = $image_tag;
        } else {
            $content = '<a href="' . esc_attr( $image_link ) .'" target="_blank" rel="nofollow">' . $image_tag . '</a>';
        }

        return $content;
    }

    private function get_image_link( $advertisement_position ) {
        $link = $advertisement_position->get_image_link();

        if ( ! preg_match( '#^https?://#', $link ) ) {
            $link = "//$link";
        }

        return $link;
    }
}
