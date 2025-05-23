<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Shortcode attributes
 * @var $atts
 * @var $el_class
 * @var $full_width
 * @var $full_height
 * @var $equal_height
 * @var $columns_placement
 * @var $content_placement
 * @var $parallax
 * @var $parallax_image
 * @var $css
 * @var $el_id
 * @var $video_bg
 * @var $video_bg_url
 * @var $video_bg_parallax
 * @var $parallax_speed_bg
 * @var $parallax_speed_video
 * @var $content - shortcode content
 * @var $css_animation
 * Shortcode class
 * @var $this WPBakeryShortCode_VC_Row
 */
$el_class        = $full_height = $parallax_speed_bg = $parallax_speed_video = $full_width = $equal_height = $flex_row = $columns_placement = $content_placement = $parallax = $parallax_image = $css = $el_id = $video_bg = $video_bg_url = $video_bg_parallax = $css_animation = $fixed_width_container = '';
$disable_element = '';
$output          = $after_output = '';
$atts            = vc_map_get_attributes( $this->getShortcode(), $atts );
extract( $atts );

wp_enqueue_script( 'wpb_composer_front_js' );

$el_class = $this->getExtraClass( $el_class ) . $this->getCSSAnimation( $css_animation );

$css_classes = array(
	'vc_row',
	'wpb_row',
	//deprecated
	'vc_row-fluid',
	$el_class,
	vc_shortcode_custom_css_class( $css ),
);

if ( 'yes' === $disable_element ) {
	if ( vc_is_page_editable() ) {
		$css_classes[] = 'vc_hidden-lg vc_hidden-xs vc_hidden-sm vc_hidden-md';
	} else {
		return '';
	}
}

if ( vc_shortcode_custom_css_has_property( $css, array(
		'border',
		'background',
	) ) || $video_bg || $parallax
) {
	$css_classes[] = 'vc_row-has-fill';
}

if ( ! empty( $atts['gap'] ) ) {
	$css_classes[] = 'vc_column-gap-' . $atts['gap'];
}

$wrapper_attributes = array();
// build attributes for wrapper
if ( ! empty( $el_id ) ) {
	$wrapper_attributes[] = 'id="' . esc_attr( $el_id ) . '"';
}
if ( ! empty( $full_width ) ) {
	$wrapper_attributes[] = 'data-vc-full-width="true"';
	$wrapper_attributes[] = 'data-vc-full-width-init="false"';
	if ( 'stretch_row_content' === $full_width ) {
		$wrapper_attributes[] = 'data-vc-stretch-content="true"';
	} elseif ( 'stretch_row_content_no_spaces' === $full_width ) {
		$wrapper_attributes[] = 'data-vc-stretch-content="true"';
		$css_classes[]        = 'vc_row-no-padding';
	}
	$after_output .= '<div class="vc_row-full-width vc_clearfix"></div>';
}

if ( ! empty( $full_height ) ) {
	$css_classes[] = 'vc_row-o-full-height';
	if ( ! empty( $columns_placement ) ) {
		$flex_row      = true;
		$css_classes[] = 'vc_row-o-columns-' . $columns_placement;
		if ( 'stretch' === $columns_placement ) {
			$css_classes[] = 'vc_row-o-equal-height';
		}
	}
}

if ( ! empty( $equal_height ) ) {
	$flex_row      = true;
	$css_classes[] = 'vc_row-o-equal-height';
}

if ( ! empty( $content_placement ) ) {
	$flex_row      = true;
	$css_classes[] = 'vc_row-o-content-' . $content_placement;
}

if ( ! empty( $flex_row ) ) {
	$css_classes[] = 'vc_row-flex';
}

$has_video_bg = ( ! empty( $video_bg ) && ! empty( $video_bg_url ) && vc_extract_youtube_id( $video_bg_url ) );

$parallax_speed = $parallax_speed_bg;
if ( $has_video_bg ) {
	$parallax       = $video_bg_parallax;
	$parallax_speed = $parallax_speed_video;
	$parallax_image = $video_bg_url;
	$css_classes[]  = 'vc_video-bg-container';
	wp_enqueue_script( 'vc_youtube_iframe_api_js' );
}

if ( ! empty( $parallax ) ) {
	wp_enqueue_script( 'vc_jquery_skrollr_js' );
	$wrapper_attributes[] = 'data-vc-parallax="' . esc_attr( $parallax_speed ) . '"'; // parallax speed
	$css_classes[]        = 'vc_general vc_parallax vc_parallax-' . $parallax;
	if ( false !== strpos( $parallax, 'fade' ) ) {
		$css_classes[]        = 'js-vc_parallax-o-fade';
		$wrapper_attributes[] = 'data-vc-parallax-o-fade="on"';
	} elseif ( false !== strpos( $parallax, 'fixed' ) ) {
		$css_classes[] = 'js-vc_parallax-o-fixed';
	}
}

if ( ! empty( $parallax_image ) ) {
	if ( $has_video_bg ) {
		$parallax_image_src = $parallax_image;
	} else {
		$parallax_image_id  = preg_replace( '/[^\d]/', '', $parallax_image );
		$parallax_image_src = wp_get_attachment_image_src( $parallax_image_id, 'full' );
		if ( ! empty( $parallax_image_src[0] ) ) {
			$parallax_image_src = $parallax_image_src[0];
		}
	}
	$wrapper_attributes[] = 'data-vc-parallax-image="' . esc_attr( $parallax_image_src ) . '"';
}
if ( ! $parallax && $has_video_bg ) {
	$wrapper_attributes[] = 'data-vc-video-bg="' . esc_attr( $video_bg_url ) . '"';
}
$css_class            = preg_replace( '/\s+/', ' ', apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, implode( ' ', array_filter( array_unique( $css_classes ) ) ), $this->settings['base'], $atts ) );
$wrapper_attributes[] = 'class="' . esc_attr( trim( $css_class ) ) . '"';
$containerClass       = '';
$bg_opts_to_container = false;

$ct_row_stretch = isset( $atts['ct_row_stretch'] ) ? $atts['ct_row_stretch'] : '';

switch ( $ct_row_stretch ) {
	case 'stretch_row_content':
		$containerClass = 'crane-container-fluid';
		break;
	case 'stretch_row_content_no_spaces':
		$containerClass = 'crane-container-fluid crane-container--no-padding';
		break;
	case 'stretch_row':
		$containerClass = 'crane-container';
		break;
	default:
		$containerClass = 'crane-container';
		break;
}

if ( ! $ct_row_stretch ) { // default
	$bg_opts_to_container = true;
}


$bg_opts = '';
if ( ! empty( $atts['ct_bg_color'] ) ) {
	$bg_opts .= 'background-color: ' . esc_js( $atts['ct_bg_color'] ) . ';';
}

if ( ! empty( $atts['ct_bg_image'] ) ) {
	$bg_image_url = '';
	if ( is_numeric( $atts['ct_bg_image'] ) ) {
		$wp_image = wp_get_attachment_image_src( intval( $atts['ct_bg_image'] ), 'full' );
		if ( $wp_image != null ) {
			$bg_image_url = $wp_image[0];
		}
	} else {
		$bg_image_url = esc_js( $atts['ct_bg_image'] );
	}

	$bg_opts .= 'background-image: url(\'' . $bg_image_url . '\');';

	if ( ! empty( $atts['ct_bg_size'] ) ) {
		$bg_opts .= 'background-size: ' . esc_js( $atts['ct_bg_size'] ) . ';';
	}
	if ( ! empty( $atts['ct_bg_repeat'] ) ) {
		$bg_opts .= 'background-repeat: ' . esc_js( $atts['ct_bg_repeat'] ) . ';';
	}
	if ( ! empty( $atts['ct_bg_position'] ) ) {
		$bg_opts .= 'background-position: ' . esc_js( $atts['ct_bg_position'] ) . ';';
	}

}


$section_additional_class = '';
$ct_bg_parallax           = isset( $atts['ct_bg_parallax'] ) ? $atts['ct_bg_parallax'] : '';
if ( ! empty( $ct_bg_parallax ) ) {
	$section_additional_class = ' crane-parallax-' . esc_attr( $ct_bg_parallax );
}


if ( ! empty( $bg_opts ) ) {
	$bg_opts             = '<div class="crane-background" style="' . $bg_opts . '"></div>';
	$ct_bg_color_overlay = isset( $atts['ct_bg_color_overlay'] ) ? $atts['ct_bg_color_overlay'] : '';
	if ( ! empty( $ct_bg_color_overlay ) ) {
		$bg_opts .= '<div class="crane-background-overlay" style="background-color: ' . esc_js( $atts['ct_bg_color_overlay'] ) . '"></div>';
	}
}

$output .= '<div class="crane-section' . $section_additional_class . '">';
if ( ! $bg_opts_to_container && $bg_opts ) {
	$output .= $bg_opts;
}
$output .= '<div class="' . $containerClass . '">';
if ( $bg_opts_to_container && $bg_opts ) {
	$output .= $bg_opts;
}
$output .= '<div ' . implode( ' ', $wrapper_attributes ) . '>';
$output .= wpb_js_remove_wpautop( $content );
$output .= '</div>';
$output .= $after_output;
$output .= '</div>';
$output .= '</div>';


echo $output;
