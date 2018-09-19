<?php

/**
 * @since 3.2.0
 */
class AWPCP_RegionsSidelist {

    private $size = 0;
    public $html = '';

    /**
     * @since 3.2.0
     */
    public function __construct( $html = '', $size = 0 ) {
        $this->html = $html;
        $this->size = $size;
    }

    /**
     * @since 3.2.0
     */
    public function render() {
        return $this->size() === 0 ? '' : $this->html;
    }

    /**
     * @since 3.2.0
     */
    public function size() {
        return $this->size;
    }
}
