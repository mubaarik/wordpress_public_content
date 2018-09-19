<?php

if ( class_exists( 'AWPCP_Exception' ) ) {

/**
 * @since 3.2.0
 */
class AWPCP_UnknownTopLevelRegionInSidelistException extends AWPCP_Exception {}

/**
 * @since 3.2.0
 */
class AWPCP_TooManyRecordsLeftException extends AWPCP_Exception {

    public $records_processed = 0;
    public $records_left = 0;

    /**
     * @since 3.2.0
     */
    public function __construct( $message, $records_processed, $records_left ) {
        parent::__construct( $message );

        $this->records_processed = $records_processed;
        $this->records_left = $records_left;
    }
}

}
