<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );


/**
 * Add [soundcloud] shortcode support
 *
 * @param $atts
 *
 * @return string
 */
function grooni_souncloud_shortcode( $atts ) {
	$atts = shortcode_atts( array(
		'url'    => '',
		'height' => '128',
		'width'  => '100%',
		'iframe' => 'true'
	), $atts );

	$output = '';

	$service_domain_name = 'soundcloud.com';

	if ( $atts['url'] ) {

		$privacy_block_this_shortcode = false;

		$theme_options = $GLOBALS[ GROONI_THEME_ADDONS_CURRENT_THEME_SLUG . '_options' ];

		if (
			function_exists( 'crane_get_privacy_cookie' ) &&
			isset( $theme_options['privacy-preferences'] ) &&
			$theme_options['privacy-preferences'] &&
			isset( $theme_options['privacy-embeds'] ) &&
			$theme_options['privacy-embeds']
		) {
			$privacy_force_agree = crane_get_privacy_cookie( 'force-agree', false, false );
			$url_embeds          = stristr( $atts['url'], $service_domain_name, true );
			if ( ! $privacy_force_agree && false !== $url_embeds ) {
				$privacy_cookie_data = crane_get_privacy_cookie( 'embeds' );
				$privacy_services    = empty( $theme_options['privacy-services'] ) ? array() : $theme_options['privacy-services'];

				if ( in_array( $service_domain_name, $privacy_services ) ) {
					if ( isset( $privacy_cookie_data[ $service_domain_name ] ) ) {
						if ( ! $privacy_cookie_data[ $service_domain_name ] ) {
							$privacy_block_this_shortcode = true;
						}
					} elseif ( in_array( $service_domain_name, $privacy_services ) ) {
						$privacy_block_this_shortcode = true;
					}
				}
			}
		}

		if ( $privacy_block_this_shortcode ) {
			if ( function_exists( 'crane_get_privacy_of_embeds_text' ) ) {
				$output = crane_get_privacy_of_embeds_text();
			}
		} else {

			//$output = wp_oembed_get( $atts['url'], array( 'class' => 'embeded-content' ) );
			$output = '<iframe width="' . $atts['width'] . '" height="' . $atts['height'] . '" scrolling="no" frameborder="no" src="https://w.' . $service_domain_name . '/player/?url=' . str_replace( ':', '%3A', $atts['url'] ) . '" ></iframe>';

		}

	}

	return $output;
}

add_shortcode( 'soundcloud', 'grooni_souncloud_shortcode' );
