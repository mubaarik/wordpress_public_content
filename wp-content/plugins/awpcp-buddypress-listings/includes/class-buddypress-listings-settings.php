<?php

function awpcp_buddypress_listings_settings() {
    return new AWPCP_BuddyPress_Listings_Settings();
}

class AWPCP_BuddyPress_Listings_Settings {

    public function register_settings( $settings ) {
        $group = $settings->add_group( 'BuddyPress', 'awpcp-buddypress-listings', 200 );

        $section = $settings->add_section(
            $group,
            __( 'General', 'awpcp-buddypress-listings' ),
            'general',
            10,
            array( $settings, 'section' )
        );

        $settings->add_setting(
            $section,
            'listings-tab-title',
            __( "Listing's Tab Title", 'awpcp-buddypress-listings' ),
            'textfield',
            __( 'Listings', 'awpcp-buddypress-listings' ),
            __( "The default title for the Listings tab shown in each member's profile.", 'awpcp-buddypress-listings' )
        );

        $section = $settings->add_section(
            $group,
            __( 'Listings URL', 'awpcp-buddypress-listings' ),
            'listings-url',
            20,
            array( $settings, 'section' )
        );

        $settings->add_setting(
            $section,
            'show-listings-buddypress-in-members-profile',
            __( "Show listings in BuddyPress member's profile", 'awpcp-buddypress-listings' ),
            'checkbox',
            1,
            __( "If checked, clicking a link to the individual listing view will take the visitor to th listing's owner profile page in BuddyPress. The listing URL will point to that page in the member's profile instead of the Single Ad page. Listing's URLs will change from something like http://example.com/awpcp/show-ad/4/test-listing/city/state/country/category/ to http://example.com/members/john/listings/view/4/test-listing/." )
        );
    }
}
