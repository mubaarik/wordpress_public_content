<?php

function awpcp_campaign_advertisement_positions_collection() {
    return new AWPCP_CampaingAdvertisementPositionsCollection(
        awpcp_campaign_advertisement_position_logic_factory(),
        $GLOBALS['wpdb']
    );
}

class AWPCP_CampaingAdvertisementPositionsCollection {

    private $slug_order = array( 'top', 'bottom', 'footer', 'sidebar-one', 'sidebar-two' );

    private $logic_factory;
    private $db;

    public function __construct( $logic_factory, $db ) {
        $this->logic_factory = $logic_factory;
        $this->db = $db;
    }

    public function get( $campaign_id, $advertisement_position ) {
        $sql = 'SELECT * FROM ' . AWPCP_TABLE_CAMPAIGN_ADVERTISEMENT_POSITIONS . ' ';
        $sql.= 'WHERE campaign_id = %d AND advertisement_position = %s';

        $campaign_positon = $this->db->get_row( $this->db->prepare( $sql, $campaign_id, $advertisement_position ) );

        if ( is_null( $campaign_positon ) ) {
            $message = __( 'No campaign advertisement position was found with Campaign ID %d and position slug %s.', 'awpcp-campaign-manager' );
            throw new AWPCP_DatabaseException( $message, $this->db->last_error );
        }

        return $this->logic_factory->create_campaign_advertisement_position_logic( $campaign_positon );
    }

    public function find_by_campaign_id( $campaign_id ) {
        $sql = 'SELECT * FROM ' . AWPCP_TABLE_CAMPAIGN_ADVERTISEMENT_POSITIONS . ' ';
        $sql.= 'WHERE campaign_id = %d';

        $results = $this->db->get_results( $this->db->prepare( $sql, $campaign_id ) );

        return $this->create_campaign_advertisement_position_logic_from_results( $results );
    }

    private function create_campaign_advertisement_position_logic_from_results( $results ) {
        $success = usort( $results, array( $this, 'campaign_section_comparator' ) );

        $campaign_positons = array();
        foreach ( $results as $result ) {
            $campaign_positons[] = $this->logic_factory->create_campaign_advertisement_position_logic( $result );
        }

        return $campaign_positons;
    }

    private function campaign_section_comparator( $a, $b ) {
        $a_index = array_search( $a->advertisement_position, $this->slug_order );
        $b_index = array_search( $b->advertisement_position, $this->slug_order );

        if ( $a_index === false && $b_index === false ) {
            return strcmp( $a->advertisement_position, $b->advertisement_position );
        } else if ( $a_index === false ) {
            return 1;
        } else if ( $b_index === false ) {
            return -1;
        } else {
            return $a_index - $b_index;
        }
    }

    public function find_active_positions_by_campaign_id( $campaign_id ) {
        $sql = 'SELECT DISTINCT cp.* FROM ' . AWPCP_TABLE_CAMPAIGN_ADVERTISEMENT_POSITIONS . ' AS cp ';
        $sql.= 'JOIN ' . AWPCP_TABLE_CAMPAIGN_SECTION_ADVERTISEMENT_POSITIONS . ' AS csp ON ( csp.advertisement_position = cp.advertisement_position ) ';
        $sql.= 'JOIN ' . AWPCP_TABLE_CAMPAIGN_SECTIONS . ' AS cs ON ( csp.campaign_section_id = cs.id ) ';
        $sql.= 'WHERE cp.campaign_id = %d AND cs.campaign_id = %d';

        $results = $this->db->get_results( $this->db->prepare( $sql, $campaign_id, $campaign_id ) );

        return $this->create_campaign_advertisement_position_logic_from_results( $results );
    }

    public function find_placeholder_positions() {
        $sql = 'SELECT DISTINCT cp.* ';
        $sql.= 'FROM ' . AWPCP_TABLE_CAMPAIGNS . ' AS c ';
        $sql.= 'JOIN ' . AWPCP_TABLE_CAMPAIGN_ADVERTISEMENT_POSITIONS . ' AS cp ON ( c.id = cp.campaign_id ) ';
        $sql.= "WHERE c.is_placeholder = 1 AND c.status = 'enabled'";

        $results = $this->db->get_results( $sql );

        return $this->create_campaign_advertisement_position_logic_from_results( $results );
    }
}
