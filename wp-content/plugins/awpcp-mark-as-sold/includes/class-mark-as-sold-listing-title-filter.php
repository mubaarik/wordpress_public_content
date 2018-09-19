<?php
/**
 * @package AWPCP\MarkAsSold
 */

/**
 * Mark as Sold filters for listing title.
 */
class AWPCP_MarkAsSoldListingTitleFilter {

    /**
     * @var object  An instance of Listing Metadata.
     */
    private $metadata;

    public function __construct( $metadata ) {
        $this->metadata = $metadata;
    }

    public function filter_title_link_placeholder( $title_link, $listing, $title, $url ) {
        if ( ! $this->metadata->get( $listing->ad_id, 'is-sold' ) ) {
            return $title_link;
        }

        $title_link = sprintf(
            '<a href="%s">%s <span class="screen-reader-text">%s</span></a>',
            esc_attr( $url ),
            esc_html( $title ),
            __( 'This listing is marked as sold.', 'awpcp-mark-as-sold' )
        );

        return $title_link;
    }
}
