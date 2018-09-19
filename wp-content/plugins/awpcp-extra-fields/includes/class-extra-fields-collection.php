<?php

function awpcp_extra_fields_collection() {
    return new AWPCP_Extra_Fields_Collection();
}

class AWPCP_Extra_Fields_Collection {

    public function find_fields_with_conditions( $conditions ) {
        return awpcp_get_extra_fields( 'WHERE ' . join( ' AND ', $conditions ) );
    }
}
