<?php

function awpcp_custom_icon_uploader_configuration() {
    return new AWPCP_CustomIconUplaoderConfiguration();
}

class AWPCP_CustomIconUplaoderConfiguration {

    public function get_allowed_file_extensions() {
        return array( 'jpg', 'png', 'gif' );
    }
}
