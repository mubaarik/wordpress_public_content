<?php

require_once(AWPCP_DIR . '/frontend/widget-latest-ads.php');


class featured_ads_widget extends AWPCP_LatestAdsWidget {

    function __construct() {
        parent::__construct(false, __('AWPCP Featured Ads','awpcp-featured-ads' ), __('Display a list of featured Ads.'));
    }

    protected function defaults() {
        return wp_parse_args( array(
            'title' => __('Featured Ads', 'awpcp-featured-ads' ),
        ), parent::defaults() );
    }

    /**
     * Translate old settings to use the names of the settings being
     * used in the Lates Ads Widget.
     *
     * @since  3.0-beta
     */
    protected function translate($instance) {
        $translations = array(
            'featured_title' => 'title',
            'number' => 'limit',
            'thumbs' => 'show-images',
            'show_no_image' => 'show-blank',
        );

        foreach ($translations as $old => $new) {
            if (isset($instance[$old])) {
                $instance[$new] = $instance[$old];
            }
        }

        // discard entries with old names
        return array_intersect_key($instance, $this->defaults());
    }

    protected function query($instance) {
        $query = array_merge( parent::query( $instance ), array(
            'featured' => true,
            'orderby' => 'random',
            'order' => 'ASC',
        ) );

        return $query;
    }

    /**
     * [render description]
     * @param  [type] $items      [description]
     * @param  [type] $instance   [description]
     * @param  string $html_class CSS class for each LI element.
     * @since  3.0-beta
     * @return string             HTML
     */
    protected function render($items, $instance, $html_class='') {
        return parent::render($items, $instance, 'featured_ad_item');
    }

    public function form($instance) {
        return parent::form($this->translate($instance));
    }
}
