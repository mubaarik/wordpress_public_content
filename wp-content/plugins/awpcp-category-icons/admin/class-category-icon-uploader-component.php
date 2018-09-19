<?php

function awpcp_category_icon_uploader_component() {
    return new AWPCP_Category_Icon_Uploader_Component(
        awpcp_media_uploader_component()
    );
}

class AWPCP_Category_Icon_Uploader_Component {

    private $media_uploader_component;

    public function __construct( $media_uploader_component ) {
        $this->media_uploader_component = $media_uploader_component;
    }

    public function render( $configuration ) {
        return $this->media_uploader_component->render( $configuration );
    }
}
