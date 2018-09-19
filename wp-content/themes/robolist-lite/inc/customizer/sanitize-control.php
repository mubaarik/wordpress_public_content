<?php
if( ! class_exists('WP_Customize_Control') ){
  return NULL;
}


function robolist_lite_reset_alls( $input ) {
    if ( $input == 1 ) {
        delete_option( 'robolist_list_theme_options');
        $input=0;
        return $input;
    }
    else {
        return '';
    }
}

if(!function_exists('robolist_lite_sanitize_checkbox')):
    function robolist_lite_sanitize_checkbox( $input ) {
        return ( ( isset( $input ) && true == $input ) ? true : false );
    }
endif;

function robolist_sanitize_page( $input ) {
  if(  get_post( $input ) ){
    return $input;
  }
  else {
    return '';
  }
}


