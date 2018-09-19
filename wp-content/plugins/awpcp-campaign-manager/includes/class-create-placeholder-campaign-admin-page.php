<?php

function awpcp_create_placeholder_campaign_admin_page() {
    return new AWPCP_CreatePlaceholderCampaignAdminPage(
        awpcp_campaigns_collection(),
        awpcp_campaign_saver(),
        awpcp_campaign_section_saver(),
        awpcp_advertisement_positions_generator(),
        awpcp()->settings,
        awpcp_request()
    );
}

class AWPCP_CreatePlaceholderCampaignAdminPage extends AWPCP_AdminPage {

    private $campaigns;
    private $campaign_saver;
    private $campaign_section_saver;
    private $positions_generator;
    private $settings;
    private $request;

    public function __construct( $campaigns, $campaign_saver, $campaign_section_saver, $positions_generator, $settings, $request ) {
        parent::__construct(
            'awpcp-create-placeholder-campaign',
            awpcp_admin_page_title( __( 'Create Placeholder Campaign', 'awpcp-campaign-manager' ) ),
            __( 'Placeholder Campaign', 'awpcp-campaign-manager' )
        );

        $this->campaigns = $campaigns;
        $this->campaign_saver = $campaign_saver;
        $this->campaign_section_saver = $campaign_section_saver;
        $this->positions_generator = $positions_generator;
        $this->settings = $settings;
        $this->request = $request;
    }

    public function show_sidebar() {
        return false;
    }

    public function on_load() {
        // if that parameter is present, admin.php will not print any of the
        // WordPress admin dashboard markup, allowing us to redirect without
        // "haders already sent" issues.
        $_GET['noheader'] = true;
    }

    public function dispatch() {
        try {
            $campaign = $this->campaigns->get_placeholder_campaign();
        } catch ( AWPCP_Exception $e ) {
            return $this->try_to_create_placeholder_campaign();
        }

        return $this->redirect_to_placeholder_campaign_page( $campaign->id );
    }

    private function try_to_create_placeholder_campaign() {
        try {
            $campaign_id = $this->create_placeholder_campaign();
            awpcp_flash( __( 'The placeholder campaign was created successfully.', 'awpcp-campaign-manager' ) );
            return $this->redirect_to_placeholder_campaign_page( $campaign_id );
        } catch ( AWPCP_Exception $e ) {
            $message = __( "The placeholder campaign couldn't be created.", 'awpcp-campaign-manager' );
            awpcp_flash( $message . ' ' . $e->format_errors(), 'error' );
            return $this->redirect_to_manage_campaigns_page();
        }
    }

    private function create_placeholder_campaign() {
        $campaign_id = $this->campaign_saver->create_campaign(
            array(
                'start_date' => awpcp_datetime( 'mysql' ),
                'end_date' => awpcp_datetime( 'mysql' ),
                'status' => 'enabled',
                'is_placeholder' => true,
            ),
            $this->request->get_current_user()
        );

        $listings_per_page = $this->settings->get_option( 'adresultsperpage' );
        $positions = $this->positions_generator->generate_advertisement_positions_for_listings_count( $listings_per_page );

        $campaign_section_id = $this->campaign_section_saver->create_campaign_section(
            array(
                'campaign_id' => $campaign_id,
                'category_id' => 1,
                'pages' => '1',
                'positions' => array_keys( $positions )
            )
        );

        return $campaign_id;
    }

    private function redirect_to_placeholder_campaign_page( $campaign_id ) {
        $query_args = array( 'page' => 'awpcp-manage-campaign', 'action' => 'edit', 'campaign' => $campaign_id );
        $this->redirect_to_plugin_page( $query_args );
    }

    private function redirect_to_plugin_page( $query_args ) {
        header( 'Location: ' . add_query_arg( $query_args, admin_url( 'admin.php' ) ) );
        exit();
    }

    private function redirect_to_manage_campaigns_page() {
        $query_args = array( 'page' => 'awpcp-manage-campaigns' );
        $this->redirect_to_plugin_page( $query_args );
    }
}
