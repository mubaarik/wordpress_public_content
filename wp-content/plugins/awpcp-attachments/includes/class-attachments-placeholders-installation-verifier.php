<?php

function awpcp_attachments_placeholders_installation_verifier() {
    return new AWPCP_AttachmentsPlaceholdersInstallationVerifier( awpcp()->settings );
}

class AWPCP_AttachmentsPlaceholdersInstallationVerifier extends AWPCP_PlaceholdersInstallationVerifier {

    public function check_placeholder_installation() {
        if ( $this->is_placeholder_missing( '$attachments' ) ) {
            $warning_message = __( "The AWPCP Attachments module requires that you add the '\$attachments' placeholder to the Single Ad layout. The placeholder will be replaced with a list of the attachments for the current Ad. Without the placeholder, the attachments won't be shown in the Ad's single view.", 'awpcp-attachments' );
            $this->show_missing_placeholder_notice( $warning_message );
        }
    }
}
