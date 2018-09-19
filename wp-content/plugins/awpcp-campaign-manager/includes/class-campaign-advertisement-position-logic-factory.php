<?php

function awpcp_campaign_advertisement_position_logic_factory() {
    return new AWPCP_CampaignAdvertisementPositionsLogicFactory(
        awpcp_advertisement_positions_generator(),
        awpcp()->settings
    );
}

class AWPCP_CampaignAdvertisementPositionsLogicFactory {

    private $positions_generator;
    private $settings;

    public function __construct( $positions_generator, $settings ) {
        $this->positions_generator = $positions_generator;
        $this->settings = $settings;
    }

    public function create_campaign_advertisement_position_logic( $model ) {
        return new AWPCP_CampaignAdvertisementPositionLogic( $model, $this->positions_generator, $this->settings );
    }
}
