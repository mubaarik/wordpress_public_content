<?php
/**
 * Social Share widget.
 *
 * @package OceanWP WordPress theme
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Ocean_Extra_Social_Share_Widget' ) ) {
	class Ocean_Extra_Social_Share_Widget extends WP_Widget {

		/**
		 * Register widget with WordPress.
		 *
		 * @since 1.0.0
		 */
		public function __construct() {
			// Start up widget
			parent::__construct(
				'ocean_social_share',
				esc_html__( '&raquo; Social Share', 'ocean-extra' ),
				array(
					'classname'   => 'widget-oceanwp-social-share social-share',
					'description' => esc_html__( 'Display social sharing buttons on your sidebar.', 'ocean-extra' ),
					'customize_selective_refresh' => true,
				)
			);

			add_action( 'admin_head-widgets.php', array( $this, 'social_share_style' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
			add_action( 'admin_footer-widgets.php', array( $this, 'print_scripts' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'scripts' ) );
		}

		/**
		 * Custom widget style
		 *
		 * @since 1.3.8
		 *
		 * @param string $hook_suffix
		 */
		public function social_share_style() { ?>
			<style>
				.oceanwp-social-share { padding-top: 10px; }
				.oceanwp-social-share li { cursor: move; background: #fafafa; padding: 10px; border: 1px solid #e5e5e5; margin-bottom: 10px; }
				.oceanwp-social-share li p { margin: 0 }
				.oceanwp-social-share li label { margin-bottom: 3px; display: block; color: #222; }
				.oceanwp-social-share li label span.fa { margin-right: 10px }
				.oceanwp-social-share .placeholder { border: 1px dashed #e3e3e3 }
				.oceanwp-share-select { width: 100% }
				.color-label { display: block; margin-bottom: 5px; }
			</style>
		<?php
		}

		/**
		 * Enqueue scripts.
		 *
		 * @since 1.3.8
		 *
		 * @param string $hook_suffix
		 */
		public function enqueue_scripts( $hook_suffix ) {
			if ( 'widgets.php' !== $hook_suffix ) {
				return;
			}

			wp_enqueue_style( 'wp-color-picker' );
			wp_enqueue_script( 'wp-color-picker' );
			wp_enqueue_script( 'underscore' );
		}

		/**
		 * Print scripts.
		 *
		 * @since 1.3.8
		 */
		public function print_scripts() { ?>
			<script>
				( function( $ ){
					$(document).ajaxSuccess(function(e, xhr, settings) {
						var widget_id_base = 'ocean_social_share';
						if ( settings.data.search( 'action=save-widget' ) != -1 && settings.data.search( 'id_base=' + widget_id_base) != -1 ) {
							oceanwpSocialShare();
						}
					} );

					function oceanwpSocialShare() {
						$( '.oceanwp-social-share' ).each( function() {
							var id = $(this).attr( 'id' );
							$( '#'+ id ).sortable( {
								placeholder : "placeholder",
								opacity     : 0.6
							} );
						} );
					}

					oceanwpSocialShare();

					function initSocialShareColorPicker( widget ) {
						widget.find( '.color-picker' ).wpColorPicker( {
							change: _.throttle( function() { // For Customizer
								$(this).trigger( 'change' );
							}, 3000 )
						});
					}

					function onSocialShareFormUpdate( event, widget ) {
						initSocialShareColorPicker( widget );
					}

					$( document ).on( 'widget-added widget-updated', onSocialShareFormUpdate );

					$( document ).ready( function() {
						$( '#widgets-right .widget:has(.color-picker)' ).each( function () {
							initSocialShareColorPicker( $( this ) );
						} );
					} );
				}( jQuery ) );
			</script>
		<?php
		}

		/**
		 * Enqueue scripts.
		 *
		 * @since 1.0.0
		 */
		public function social_array() {
			$post_id  	= get_the_ID();
			$url      	= get_permalink( $post_id );
			$title    	= get_the_title();

			// Get SEO meta and use instead if they exist
			if ( defined( 'WPSEO_VERSION' ) ) {
				if ( $meta = get_post_meta( $post_id, '_yoast_wpseo_twitter-title', true ) ) {
					$title = $meta;
				}
				if ( $meta = get_post_meta( $post_id, '_yoast_wpseo_twitter-description', true ) ) {
					$title = $title .': '. $meta;
					$title = $title;
				}
			}

			// Array
			$return = apply_filters( 'ocean_social_share_buttons', array(
				'twitter' => array(
					'name' 	=> 'Twitter',
					'title' => esc_html__( 'Share on Twitter', 'ocean-extra' ),
					'url'  	=> 'https://twitter.com/share?text='. wp_strip_all_tags( $title ) .'>&amp;url='. rawurlencode( esc_url( $url ) ),
				),
				'facebook' => array(
					'name' 	=> 'Facebook',
					'title' => esc_html__( 'Share on Facebook', 'ocean-extra' ),
					'url'  	=> 'https://www.facebook.com/sharer.php?u='. rawurlencode( esc_url( $url ) ),
				),
				'googleplus' => array(
					'name' 	=> 'Google+',
					'title' => esc_html__( 'Share on Google+', 'ocean-extra' ),
					'url'  	=> 'https://plus.google.com/share?url='. rawurlencode( esc_url( $url ) ),
				),
				'pinterest' => array(
					'name' 	=> 'Pinterest',
					'title' => esc_html__( 'Share on Pinterest', 'ocean-extra' ),
					'url'  	=> 'https://www.pinterest.com/pin/create/button/?url='. rawurlencode( esc_url( $url ) ) .'&amp;media='. wp_get_attachment_url( get_post_thumbnail_id( $post_id ) ) .'&amp;description='. urlencode( wp_trim_words( strip_shortcodes( get_the_content( $post_id ) ), 40 ) ),
				),
				'linkedin' => array(
					'name' 	=> 'LinkedIn',
					'title' => esc_html__( 'Share on LinkedIn', 'ocean-extra' ),
					'url'  	=> 'https://www.linkedin.com/shareArticle?mini=true&amp;url='. rawurlencode( esc_url( $url ) ) .'&amp;title='. wp_strip_all_tags( $title ) .'&amp;summary='. urlencode( wp_trim_words( strip_shortcodes( get_the_content( $post_id ) ), 40 ) ) .'&amp;source='. esc_url( home_url( '/' ) ),
				),
				'viber' => array(
					'name' 	=> 'Viber',
					'title' => esc_html__( 'Share on Viber', 'ocean-extra' ),
					'url'  	=> 'viber://forward?text='. rawurlencode( esc_url( $url ) ),
				),
				'vk' => array(
					'name' 	=> 'VK',
					'title' => esc_html__( 'Share on VK', 'ocean-extra' ),
					'url'  	=> 'https://vk.com/share.php?url='. rawurlencode( esc_url( $url ) ),
				),
				'reddit' => array(
					'name' 	=> 'Reddit',
					'title' => esc_html__( 'Share on Reddit', 'ocean-extra' ),
					'url'  	=> 'https://www.reddit.com/submit?url='. rawurlencode( esc_url( $url ) ) .'&amp;title='. wp_strip_all_tags( $title ),
				),
				'tumblr' => array(
					'name' 	=> 'Tumblr',
					'title' => esc_html__( 'Share on Tumblr', 'ocean-extra' ),
					'url'  	=> 'https://www.tumblr.com/widgets/share/tool?canonicalUrl='. rawurlencode( esc_url( $url ) ),
				),
				'viadeo' => array(
					'name' 	=> 'Viadeo',
					'title' => esc_html__( 'Share on Viadeo', 'ocean-extra' ),
					'url'  	=> 'https://partners.viadeo.com/share?url='. rawurlencode( esc_url( $url ) ),
				),
			) );

			return $return;
		}

		/**
		 * Front-end display of widget.
		 *
		 * @see WP_Widget::widget()
		 * @since 1.0.0
		 *
		 * @param array $args     Widget arguments.
		 * @param array $instance Saved values from database.
		 */
		public function widget( $args, $instance ) {

			// Get social share and 
			$social_share = isset( $instance['social_share'] ) ? $instance['social_share'] : '';

			// Return if no social defined
			if ( ! $social_share ) {
				return;
			}

			// Get social share and 
			$social_share = isset( $instance['social_share'] ) ? $instance['social_share'] : '';

			// Define vars
			$title         	= isset( $instance['title'] ) ? apply_filters( 'widget_title', $instance['title'] ) : '';
			$style   		= isset( $instance['style'] ) ? $instance['style'] : '';
			$font_size     	= isset( $instance['font_size'] ) ? $instance['font_size'] : '';
			$border_radius 	= isset( $instance['border_radius'] ) ? $instance['border_radius'] : '';

			// Sanitize vars
			$font_size     = $font_size ? $font_size : '';
			$border_radius = $border_radius ? $border_radius  : '';

			// Inline style
			$add_style = '';
			if ( $font_size ) {
				$add_style .= 'font-size:'. esc_attr( $font_size ) .';';
			}
			if ( $border_radius && 'simple' != $style ) {
				$add_style .= 'border-radius:'. esc_attr( $border_radius ) .';';
			}
			if ( $add_style ) {
				$add_style = ' style="' . esc_attr( $add_style ) . '"';
			}

			// Before widget hook
			echo $args['before_widget'];

				// Display title
				if ( $title ) {
					echo $args['before_title'] . esc_html( $title ) . $args['after_title'];
				}

				// Display the social share. ?>
				<ul class="owp-social-share style-<?php echo esc_attr( $style ); ?>">
					<?php
					// Original Array
					$social_array = $this->social_array();

					// Loop through each item in the array
					foreach( $social_share as $key => $val ) {
						$name    = $social_array[$key]['name'];
						$title   = $social_array[$key]['title'];
						$url     = $social_array[$key]['url'];

						$icon = $key;
						$icon = 'pinterest' == $key ? 'pinterest-p' : $icon;

						echo '<li class="'. esc_attr( $key ) .'">';

							echo '<a href="'. $url .'" title="'. esc_attr( $title ) .'" '. wp_kses_post( $add_style ) . ' onclick="owpShareOnClick( this.href );return false;">';

								echo '<span class="owp-icon-wrap">';
									echo '<i class="fa fa-'. esc_attr( $icon ) .'"></i>';
								echo '</span>';

							echo '</a>';

						echo '</li>';
					} ?>
				</ul>

				<?php $this->colors( $args, $instance ); ?>

			<?php
			// After widget hook
			echo $args['after_widget'];

		}

		/**
		 * Sanitize widget form values as they are saved.
		 *
		 * @see WP_Widget::update()
		 * @since 1.0.0
		 *
		 * @param array $new_instance Values just sent to be saved.
		 * @param array $old_instance Previously saved values from database.
		 *
		 * @return array Updated safe values to be saved.
		 */
		public function update( $new_instance, $old_instance ) {
			// Sanitize data
			$instance = $old_instance;
			$instance['title']           	= ! empty( $new_instance['title'] ) ? strip_tags( $new_instance['title'] ) : null;
			$instance['style'] 				= ! empty( $new_instance['style'] ) ? strip_tags( $new_instance['style'] ) : 'light';
			$instance['border_radius']   	= ! empty( $new_instance['border_radius'] ) ? strip_tags( $new_instance['border_radius'] ) : '';
			$instance['border_color']   	= ! empty( $new_instance['border_color'] ) ? sanitize_hex_color( $new_instance['border_color'] ) : '';
			$instance['bg_color']   	    = ! empty( $new_instance['bg_color'] ) ? sanitize_hex_color( $new_instance['bg_color'] ) : '';
			$instance['color']   	        = ! empty( $new_instance['color'] ) ? sanitize_hex_color( $new_instance['color'] ) : '';
			$instance['font_size']       	= ! empty( $new_instance['font_size'] ) ? strip_tags( $new_instance['font_size'] ) : '';
			$instance['social_share'] 		= $new_instance['social_share'];
			return $instance;
		}

		/**
		 * Back-end widget form.
		 *
		 * @see WP_Widget::form()
		 * @since 1.0.0
		 *
		 * @param array $instance Previously saved values from database.
		 */
		public function form( $instance ) {

			$instance = wp_parse_args( ( array ) $instance, array(
				'title'           	 => esc_attr__( 'Please share this', 'ocean-extra' ),
				'style' 	  		 => esc_html__( 'Minimal', 'ocean-extra' ),
				'font_size'       	 => '',
				'border_radius'   	 => '',
				'border_color'   	 => '',
				'bg_color'   	     => '',
				'color'   	         => '',
				'social_share' 	 	 => $this->social_array()[0]
			) ); ?>

			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title', 'ocean-extra' ); ?>:</label> 
				<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $instance['title'] ); ?>" />
			</p>

			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'style' ) ); ?>"><?php esc_html_e( 'Style:', 'ocean-extra' ); ?></label>
				<select class='widefat' name="<?php echo esc_attr( $this->get_field_name( 'style' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'style' ) ); ?>">
					<option value="minimal" <?php selected( $instance['style'], 'minimal' ) ?>><?php esc_html_e( 'Minimal', 'ocean-extra' ); ?></option>
					<option value="colored" <?php selected( $instance['style'], 'colored' ) ?>><?php esc_html_e( 'Colored', 'ocean-extra' ); ?></option>
					<option value="dark" <?php selected( $instance['style'], 'dark' ) ?>><?php esc_html_e( 'Dark', 'ocean-extra' ); ?></option>
				</select>
			</p>

			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'font_size' ) ); ?>"><?php esc_html_e( 'Font Size', 'ocean-extra' ); ?>:</label>
				<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'font_size' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'font_size' ) ); ?>" type="text" value="<?php echo esc_attr( $instance['font_size'] ); ?>" />
				<small><?php esc_html_e( 'Example:', 'ocean-extra' ); ?> 18px</small>
			</p>

			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'border_radius' ) ); ?>"><?php esc_html_e( 'Border Radius', 'ocean-extra' ); ?></label>
				<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'border_radius' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'border_radius' ) ); ?>" type="text" value="<?php echo esc_attr( $instance['border_radius'] ); ?>" />
				<small><?php esc_html_e( 'Example:', 'ocean-extra' ); ?> 4px</small>
			</p>

			<p>
				<label class="color-label" for="<?php echo esc_attr( $this->get_field_id( 'border_color' ) ); ?>"><?php esc_html_e( 'Minimal Style: Border Color', 'ocean-extra' ); ?></label>
				<input class="color-picker" id="<?php echo esc_attr( $this->get_field_id( 'border_color' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'border_color' ) ); ?>" type="text" value="<?php echo esc_attr( $instance['border_color'] ); ?>" />
			</p>

			<p>
				<label class="color-label" for="<?php echo esc_attr( $this->get_field_id( 'bg_color' ) ); ?>"><?php esc_html_e( 'Minimal Style: Background Color', 'ocean-extra' ); ?></label>
				<input class="color-picker" id="<?php echo esc_attr( $this->get_field_id( 'bg_color' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'bg_color' ) ); ?>" type="text" value="<?php echo esc_attr( $instance['bg_color'] ); ?>" />
			</p>

			<p>
				<label class="color-label" for="<?php echo esc_attr( $this->get_field_id( 'color' ) ); ?>"><?php esc_html_e( 'Minimal Style: Color', 'ocean-extra' ); ?></label>
				<input class="color-picker" id="<?php echo esc_attr( $this->get_field_id( 'color' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'color' ) ); ?>" type="text" value="<?php echo esc_attr( $instance['color'] ); ?>" />
			</p>

			<?php
			$field_id_share   = $this->get_field_id( 'social_share' );
			$field_name_share = $this->get_field_name( 'social_share' ); ?>
			<h3 style="margin-top:20px;margin-bottom:0;"><?php esc_html_e( 'Social Share','ocean-extra' ); ?></h3>
			<ul id="<?php echo esc_attr( $field_id_share ); ?>" class="oceanwp-social-widget-share-list">
				<input type="hidden" id="<?php echo esc_attr( $field_name_share ); ?>" value="<?php echo esc_attr( $field_name_share ); ?>">
				<input type="hidden" id="<?php echo esc_attr( wp_create_nonce( 'oceanwp_fontawesome_social_widget_nonce' ) ); ?>">
				<?php
				// Social array
				$social_array = $this->social_array();
				// Get current share display
				$display_share = isset ( $instance['social_share'] ) ? $instance['social_share']: '';
				// Loop through social share to display inputs
				foreach( $display_share as $key => $val ) {
					$url  = ! empty( $display_share[$key]['url'] ) ? $display_share[$key]['url'] : null;
					$name = $social_array[$key]['name']; ?>
					<li id="<?php echo esc_attr( $field_id_share ); ?>_0<?php echo esc_attr( $key ); ?>">
						<p>
							<label for="<?php echo esc_attr( $field_id_share ); ?>-<?php echo esc_attr( $key ); ?>-name"><?php echo esc_attr( strip_tags( $name ) ); ?>:</label>
							<input type="hidden" id="<?php echo esc_attr( $field_id_share ); ?>-<?php echo esc_attr( $key ); ?>-url" name="<?php echo esc_attr( $field_name_share .'['.$key.'][name]' ); ?>" value="<?php echo esc_attr( $name ); ?>">
							<input type="text" class="widefat" id="<?php echo esc_attr( $field_id_share ); ?>-<?php echo esc_attr( $key ); ?>-url" name="<?php echo esc_attr( $field_name_share .'['.$key.'][url]' ); ?>" value="<?php echo esc_attr( $url ); ?>" />
						</p>
					</li>
				<?php } ?>
			</ul>

		<?php

		}

		/**
		 * Colors
		 *
		 * @since 1.3.8
		 *
		 * @param array $instance Previously saved values from database.
		 */
		public function colors( $args, $instance ) {
			// get the widget ID
			$id = $args['widget_id'];

			// Define vars
			$border_color       = isset( $instance['border_color'] ) ? sanitize_hex_color( $instance['border_color'] ) : '';
			$bg_color           = isset( $instance['bg_color'] ) ? sanitize_hex_color( $instance['bg_color'] ) : '';
			$color              = isset( $instance['color'] ) ? sanitize_hex_color( $instance['color'] ) : '';

			if ( $bg_color
				|| $color
				|| $border_color ) : ?>
				<style>
					#<?php echo $id; ?>.widget-oceanwp-social ul li a { 
						<?php if ( $bg_color ) { echo 'background-color:' . $bg_color; } ?>;
						<?php if ( $color ) { echo 'color:' . $color; } ?>;
						<?php if ( $border_color ) { echo 'border-color:' . $border_color; } ?>;
					}
				</style>
			<?php endif; ?>

		<?php
		}

        /**
         * Scripts
         */
        public function scripts() {
            wp_enqueue_script( 'oe-social-share', OE_URL . '/includes/widgets/js/share.min.js', array( 'jquery' ) );
        }

	}
}
register_widget( 'Ocean_Extra_Social_Share_Widget' );