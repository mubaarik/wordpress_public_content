<?php

function awpcp_custom_icon_uploader() {
    return new AWPCP_FileUploader(
        awpcp_custom_icon_uploader_configuration(),
        awpcp_mime_types(),
        awpcp_request(),
        awpcp()->settings
    );
}
