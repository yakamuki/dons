<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Shortcode attributes
 * @var $atts
 * @var $el_class
 * @var $el_id
 * @var $width
 * @var $css
 * @var $offset
 * @var $content - shortcode content
 * Shortcode class
 * @var $this WPBakeryShortCode_VC_Column_Inner
 */
$el_class = $width = $el_id = $css = $offset = '';
$output = '';
$atts = vc_map_get_attributes( $this->getShortcode(), $atts );
extract( $atts );

$width = wpb_translateColumnWidthToSpan( $width );
$width = vc_column_offset_class_merge( $offset, $width );

$css_classes = array(
	$this->getExtraClass( $el_class ),
	'wpb_column',
	'vc_column_container',
	$width,
);

if ( vc_shortcode_custom_css_has_property( $css, array(
	'border',
	'background',
) ) ) {
	$css_classes[] = 'vc_col-has-fill';
}

$wrapper_attributes = array();

$css_class = preg_replace( '/\s+/', ' ', apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, implode( ' ', array_filter( $css_classes ) ), $this->settings['base'], $atts ) );
$wrapper_attributes[] = 'class="' . esc_attr( trim( $css_class ) ) . '"';
if ( ! empty( $el_id ) ) {
	$wrapper_attributes[] = 'id="' . esc_attr( $el_id ) . '"';
}

$ct_wrap_url        = '';
$ct_wrap_url_target = '';
if ( isset( $atts['ct_wrap_link_type'] ) && isset( $atts['ct_wrap_link'] ) && 'yes' === $atts['ct_wrap_link'] ) {
	if ( 'id' === $atts['ct_wrap_link_type'] ) {
		if ( ! empty( $atts['ct_wrap_link_id'] ) ) {
			$ct_wrap_url = esc_url( get_permalink( $atts['ct_wrap_link_id'] ) );
		}
	} elseif ( 'custom' === $atts['ct_wrap_link_type'] ) {
		if ( ! empty( $atts['ct_wrap_link_custom'] ) ) {
			$ct_wrap_url = esc_url( $atts['ct_wrap_link_custom'] );
		}
	}

	$ct_wrap_url_target = empty( $atts['ct_wrap_link_target'] ) ? '' : ' target="' . esc_attr( $atts['ct_wrap_link_target'] ) . '"';
}


$output .= '<div ' . implode( ' ', $wrapper_attributes ) . '>';

$output .= $ct_wrap_url ? '<a href="' . esc_url( $ct_wrap_url ) . '"' . $ct_wrap_url_target . '>' : '';

$output .= '<div class="vc_column-inner ' . esc_attr( trim( vc_shortcode_custom_css_class( $css ) ) ) . '">';
$output .= '<div class="wpb_wrapper">';
$output .= wpb_js_remove_wpautop( $content );
$output .= '</div>';
$output .= '</div>';

$output .= $ct_wrap_url ? '</a>' : '';

$output .= '</div>';

echo $output;
