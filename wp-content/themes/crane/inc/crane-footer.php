<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Crane theme Footer custom post type.
 *
 * @package crane
 */


/**
 *  Get footers
 *
 * @param array $default
 *
 * @return array
 *
 */
function crane_get_footer_presets( $default = array() ) {
	static $footer_presets = array();

	if ( ! empty( $footer_presets ) ) {
		return array_merge( $default, $footer_presets );
	}

	global $wpdb;
	$crane_footers = $wpdb->get_results( "SELECT ID, post_title, post_name FROM $wpdb->posts WHERE post_status LIKE 'publish' AND post_type LIKE 'crane_footer' ORDER BY post_date DESC", ARRAY_A );

	foreach ( $crane_footers as $footer ) {
		$footer_presets[ $footer['post_name'] ] = $footer['post_title'];
	}

	return array_merge( $default, $footer_presets );
}


/**
 * Return footer html code or footer post id
 *
 * @param string $return_type can be 'html' or 'id'
 *
 * @return mixed
 */
function crane_get_footer_data( $return_type = '' ) {

	global $crane_options;
	global $post;

	static $footer_html = '';
	static $footer_id   = '';

	if ( 'html' === $return_type && ! empty( $footer_html ) ) {
		return $footer_html;
	}
	if ( 'id' === $return_type && ! empty( $footer_id ) ) {
		return $footer_id;
	}

	$Crane_Meta_Data = crane_get_meta_data();

	$post_id = get_the_ID();

	$page_options      = crane_get_options_for_current_page();
	$footer_name       = isset( $page_options['footer_preset'] ) ? $page_options['footer_preset'] : null;
	$footer_appearance = isset( $page_options['footer_appearance'] ) ? $page_options['footer_appearance'] : 'appearance-regular';

	$meta_post_types = array(
		'regular-page',
		'shop-single',
		'portfolio-single',
		'blog-single',
	);

	if ( in_array( $page_options['type'], $meta_post_types, true ) && $Crane_Meta_Data->get( 'override_global', $post_id ) ) {
		if ( 'default' !== $Crane_Meta_Data->get( 'footer_preset_global', $post_id ) ) {
			$footer_name = $Crane_Meta_Data->get( 'footer_preset_global', $post_id );
		}
		if ( 'default' !== $Crane_Meta_Data->get( 'footer_appearance', $post_id ) ) {
			$footer_appearance = $Crane_Meta_Data->get( 'footer_appearance', $post_id );
		}
	}

	if ( $footer_name ) {
		$footer = get_page_by_path( $footer_name, OBJECT, 'crane_footer' );
	}

	if ( isset( $footer ) && $footer ) {

		$wpml_footer_id = apply_filters( 'wpml_object_id', $footer->ID, 'crane_footer', true );
		$footer_id = $wpml_footer_id;

		// Copy global $post exemplar
		$_post = $post;
		$post  = get_post( $wpml_footer_id );
		if ( class_exists( 'Ultimate_VC_Addons' ) ) {
			global $wp_query;

			$page_is = null;

			// Copy $wp_query
			$_wp_query = $wp_query;
			if ( is_404() ) {
				$wp_query->is_404 = false;
				$page_is = 'is_404';
			}
			if ( is_search() ) {
				$wp_query->is_search = false;
				$page_is = 'is_search';
			}

			if ( class_exists( 'Crane_Ultimate_VC_Addons' ) ) {
				$instance = new Crane_Ultimate_VC_Addons;
				$instance->aio_front_scripts();
			}

			if ( function_exists( 'enquque_ultimate_google_fonts_optimzed' ) ) {
				$post_content = apply_filters( 'ultimate_front_scripts_post_content', $post->post_content, $post );

				if ( stripos( $post_content, 'font_call:' ) ) {
					preg_match_all( '/font_call:(.*?)"/', $post_content, $display );

					crane_enqueue_ultimate_google_fonts_optimzed( $display[1] );
				}
			}

			if ( 'is_404' === $page_is ) {
				$wp_query->is_404 = true;
			}
			if ( 'is_search' === $page_is ) {
				$wp_query->is_search = true;
			}

			// Revert $wp_query
			$wp_query = $_wp_query;

		}
		$footer_content = apply_filters( 'the_content', $post->post_content );

		// Recovery global $post exemplar
		$post = $_post;


		$footer_html .= '<footer class="footer footer-' . $footer_appearance . '">';
		$footer_html .= apply_filters( 'crane_footer_the_content', $footer_content );
		$footer_html .= '</footer>';

	}

	return $footer_id;
}

add_action( 'wp_enqueue_scripts', 'crane_get_footer_data', 10200 );


if ( ! function_exists( 'crane_enqueue_ultimate_google_fonts_optimzed' ) ) {

	/**
	 * Modified copy of function 'enquque_ultimate_google_fonts_optimzed'. Ultimate addons plugin for WPBackery.
	 *
	 * @param $enqueue_fonts
	 *
	 * @return string
	 */
	function crane_enqueue_ultimate_google_fonts_optimzed( $enqueue_fonts ) {

		static $font_stack = array();

		$selected_fonts    = apply_filters(
			'enquque_selected_ultimate_google_fonts',
			get_option( 'ultimate_selected_google_fonts' )
		);
		$skip_font_enqueue = apply_filters(
			'enquque_ultimate_google_fonts_skip',
			false
		);

		if ( true === boolval( $skip_font_enqueue ) ) {
			return '';
		}

		$main              = array();
		$subset_main_array = array();
		$fonts             = array();
		$subset_call       = '';

		if ( ! empty( $enqueue_fonts ) ) {
			$font_count = 0;
			foreach ( $enqueue_fonts as $key => $efont ) {
				if ( empty( $efont ) ) {
					continue;
				}
				$font_name = $font_call = $font_variant = '';
				$font_arr  = $font_call_arr = $font_weight_arr = array();
				$font_arr  = explode( '|', $efont );

				$font_name = trim( $font_arr[0] );

				if ( ! isset( $main[ $font_name ] ) ) {
					$main[ $font_name ] = array();
				}

				if ( ! empty( $font_name ) ):

					$font_count ++;
					if ( isset( $font_arr[1] ) ) {
						$font_call_arr = explode( ':', $font_arr[1] );

						if ( isset( $font_arr[2] ) ) {
							$font_weight_arr = explode( ':', $font_arr[2] );
						}

						if ( isset( $font_call_arr[1] ) && '' !== $font_call_arr[1] ) {
							$font_variant  = $font_call_arr[1];
							$pre_font_call = $font_name;

							if ( '' !== $font_variant && 'regular' !== $font_variant ) {
								$main[ $font_name ]['varients'][] = $font_variant;
								array_push( $main[ $font_name ]['varients'], $font_variant );
								if ( ! empty( $main[ $font_name ]['varients'] ) ) {
									$main[ $font_name ]['varients'] = array_values( array_unique( $main[ $font_name ]['varients'] ) );
								}
							}
						}
					}

					foreach ( $selected_fonts as $sfont ) {
						if ( $sfont['font_family'] == $font_name ) {
							if ( ! empty( $sfont['subsets'] ) ) {
								$subset_array = array();
								foreach ( $sfont['subsets'] as $tsubset ) {
									if ( $tsubset['subset_selected'] == 'true' ) {
										array_push( $subset_array, $tsubset['subset_value'] );
									}
								}
								if ( ! empty( $subset_array ) ) :
									$subset_call = '';
									$j           = count( $subset_array );
									foreach ( $subset_array as $subkey => $subset ) {
										$subset_call .= $subset;
										if ( ( $j - 1 ) != $subkey ) {
											$subset_call .= ',';
										}
									}
									array_push( $subset_main_array, $subset_call );
								endif;
							}
						}
					}
				endif;
			}

			$link          = 'https://fonts.googleapis.com/css?family=';
			$main_count    = count( $main );
			$mcount        = 0;
			$subset_string = '';

			foreach ( $main as $font => $font_data ) {
				if ( '' !== $font ) {
					$link .= $font;
					if ( 'Open+Sans+Condensed' === $font && empty( $font_data['varients'] ) ) {
						$link .= ':300';
					}
					if ( ! empty( $font_data['varients'] ) ) {
						$link          .= ':regular,';
						$varient_count = count( $font_data['varients'] );
						foreach ( $font_data['varients'] as $vkey => $varient ) {
							$link .= $varient;
							if ( ( $varient_count - 1 ) != $vkey ) {
								$link .= ',';
							}
						}
					}

					if ( ! empty( $font_data['subset'] ) ) {
						$subset_string .= '&subset=' . $font_data['subset'];
					}

					if ( $mcount != ( $main_count - 1 ) ) {
						$link .= '|';
					}
					$mcount ++;
				}
			}

			if ( ! empty( $subset_array ) ) {
				$subset_main_array = array_unique( $subset_main_array );

				$subset_string     = '&subset=';
				$subset_count      = count( $subset_main_array );
				$subset_main_array = array_values( $subset_main_array );

				foreach ( $subset_main_array as $skey => $subset ) {
					if ( $subset !== '' ) {
						$subset_string .= $subset;
						if ( ( $subset_count - 1 ) != $skey ) {
							$subset_string .= ',';
						}
					}
				}
			}

			$font_api_call = $link . $subset_string;
			$stack_key     = md5( $font_api_call );

			if ( $font_count > 0 && empty( $font_stack[ $stack_key ] ) ) {

				$font_stack[ $stack_key ] = $font_api_call;

				wp_enqueue_style( 'ultimate-google-fonts-' . $stack_key, $font_api_call, array(), null );
			}
		}
	}
}
