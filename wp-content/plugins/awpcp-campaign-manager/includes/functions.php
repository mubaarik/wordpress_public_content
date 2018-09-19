<?php

function awpcp_insert_advertisement_position_placeholder( $position_slug ) {
    $service = awpcp_insert_advertesiment_placeholder_service();
    return $service->get_advertisement_placeholder( $position_slug );
}
