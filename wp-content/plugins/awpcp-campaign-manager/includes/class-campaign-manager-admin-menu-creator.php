<?php

function awpcp_campaign_manager_admin_menu_creator() {
    return new AWPCP_CampaignManagerAdminMenuCreator();
}

class AWPCP_CampaignManagerAdminMenuCreator {

    public function create_menu() {
        $user_capability = AWPCP_CAMPAIGN_MANAGER_MODULE_SALES_REPRESENTATIVE_CAPABILITY;
        $admin_capability = awpcp_admin_capability();

        $parent_page = awpcp_manage_campaigns_admin_page();
        $hook = add_menu_page( $parent_page->title, $parent_page->menu, $user_capability, $parent_page->page, array( $parent_page, 'dispatch' ) );
        $this->try_to_register_on_load_action( $parent_page, $hook );

        $page = awpcp_create_campaign_admin_page();
        $hook = add_submenu_page( $parent_page->page, $page->title, $page->menu, $user_capability, $page->page, array( $page, 'dispatch' ) );
        $this->try_to_register_on_load_action( $page, $hook );

        $page = awpcp_create_placeholder_campaign_admin_page();
        $hook = add_submenu_page( $parent_page->page, $page->title, $page->menu, $admin_capability, $page->page, array( $page, 'dispatch' ) );
        $this->try_to_register_on_load_action( $page, $hook );

        $page = awpcp_manage_advertisement_positions_admin_page();
        $hook = add_submenu_page( $parent_page->page, $page->title, $page->menu, $user_capability, $page->page, array( $page, 'dispatch' ) );
        $this->try_to_register_on_load_action( $page, $hook );
    }

    private function try_to_register_on_load_action( $page, $hook ) {
        if ( method_exists( $page, 'on_load' ) ) {
            add_action( "load-{$hook}", array( $page, 'on_load' ) );
        }
    }
}
