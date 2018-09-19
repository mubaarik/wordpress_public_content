<?php

function awpcp_attachments_file_types() {
    return new AWPCP_AttachmentsFileTypes();
}

class AWPCP_AttachmentsFileTypes {

    public function get_file_types( $file_types ) {
        $file_types['others'] = array(
            'pdf' => array(
                'name' => 'PDF',
                'extensions' => array( 'pdf' ),
                'mime_types' => array( 'application/pdf', 'application/x-pdf', 'application/vnd.pdf' ),
            ),
            'rtf' => array(
                'name' => 'RTF',
                'extensions' => array( 'rtf' ),
                'mime_types' => array( 'application/rtf', 'application/x-rtf', 'text/richtext' ),
            ),
            'txt' => array(
                'name' => 'TXT',
                'extensions' => array( 'txt' ),
                'mime_types' => array( 'text/plain' ),
            ),
        );

        return $file_types;
    }
}
