<?php

function awpcp_attachments_settings() {
    return new AWPCP_AttachmentsSettings( awpcp_file_types() );
}

class AWPCP_AttachmentsSettings {

    private $file_types;

    public function __construct( $file_types ) {
        $this->file_types = $file_types;
    }

    public function register_settings( $settings ) {
        $group = 'attachments-settings';

        $section = $settings->add_section( $group, __( 'Attachments', 'awpcp-attachments' ), 'attachments', 60, array( $settings, 'section' ) );

        $attachment_extensions = $this->file_types->get_other_files_extensions();
        awpcp_register_allowed_extensions_setting( $settings, $section,
            array(
                'name' => 'attachments-allowed-other-files-extensions',
                'label' => __( 'Other file extensions allowed', 'awpcp-attachments' ),
                'choices' => $attachment_extensions,
                'default' => $attachment_extensions,
            )
        );

        $settings->add_setting( $section, 'attachments-max-file-size', __( 'Max Allowed File Size', 'awpcp-attachments' ), 'textfield', '10485760', __( 'Max allowed attachment file size in bytes.', 'awpcp-attachments' ) );

        $settings->add_setting( $section, 'attachments-number-of-other-files-allowed', __( 'Number of other files allowed in each listing', 'awpcp-attachments' ), 'textfield', 10, '' );
    }
}
