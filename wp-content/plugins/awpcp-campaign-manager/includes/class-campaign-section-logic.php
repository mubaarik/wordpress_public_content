<?php

class AWPCP_CampaignSectionLogic {

    private $campaign_section;

    private $section_category = null;
    private $section_positions = null;
    private $section_pages = null;

    private $categories;
    private $positions_collection;
    private $positions_generator;

    public function __construct( $campaign_section, $positions_collection, $positions_generator, $categories ) {
        $this->campaign_section = $campaign_section;
        $this->positions_collection = $positions_collection;
        $this->positions_generator = $positions_generator;
        $this->categories = $categories;
    }

    public function get_id() {
        return $this->campaign_section->id;
    }

    public function get_category_id() {
        return $this->campaign_section->category_id;
    }

    public function get_category_name() {
        return $this->get_category()->name;
    }

    public function get_category() {
        if ( is_null( $this->section_category ) ) {
            try {
                $this->section_category = $this->categories->get( $this->campaign_section->category_id );
            } catch ( AWPCP_Exception $e ) {
                $this->section_category = new stdClass();
                $this->section_category->name = '';
            }
        }

        return $this->section_category;
    }

    public function get_list_of_positions() {
        $position_names = array();

        foreach( $this->get_positions() as $position ) {
            $position_names[] = $this->positions_generator->get_position_name( $position );
        }

        return implode( ', ', $position_names );
    }

    public function get_positions() {
        if ( is_null( $this->section_positions ) ) {
            $this->section_positions = $this->positions_collection->find_campaign_section_positions( $this->campaign_section->id );
        }

        return $this->section_positions;
    }

    public function get_list_of_pages() {
        return implode( ', ', $this->get_pages() );
    }

    private function get_pages() {
        if ( is_null( $this->section_pages ) ) {
            $this->section_pages = maybe_unserialize( $this->campaign_section->pages );
        }

        return $this->section_pages;
    }
}
