<?php

function awpcp_campaign_section_logic_factory() {
    return new AWPCP_CampaignSectionLogicFactory(
        awpcp_campaign_section_advertisement_positions_collection(),
        awpcp_advertisement_positions_generator(),
        awpcp_categories_collection()
    );
}

class AWPCP_CampaignSectionLogicFactory {

    private $positions_collection;
    private $positions_generator;
    private $categories;

    public function __construct( $positions_collection, $positions_generator, $categories ) {
        $this->positions_collection = $positions_collection;
        $this->positions_generator = $positions_generator;
        $this->categories = $categories;
    }

    public function create_campaign_section_logic( $campaign_section ) {
        return new AWPCP_CampaignSectionLogic(
            $campaign_section,
            $this->positions_collection,
            $this->positions_generator,
            $this->categories
        );
    }
}
