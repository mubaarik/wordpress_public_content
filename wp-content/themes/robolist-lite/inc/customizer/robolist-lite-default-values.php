<?php
if(!function_exists('robolist_lite_get_option_defaults_values')):
	function robolist_lite_get_option_defaults_values() {
		global $robolist_lite_default_values;
        $robolist_lite_default_values = array(
			'banner_title'			        => '',
			'banner_description'			=> '',
            'blog_description'			    => '',
            'cta_description'			    => '',
            'cta_button'			        => '',
            'cta_link'			            => '',
            'listing_des'			        => '',
            'single_joblist_form'			=> '',
            'single_form_title'			    => '',
            'single_joblist_form_enable'    => 0,
            'banner_image'                  => '',
			);
		return apply_filters( 'robolist_lite_get_option_defaults_values', $robolist_lite_default_values );
	}
endif;
?>