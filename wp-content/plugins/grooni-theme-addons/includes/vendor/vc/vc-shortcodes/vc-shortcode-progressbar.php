<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );
/**
 * Add VisualComposer progressbar shortcode Class.
 *
 * @package Grooni_Theme_Addons
 */


if ( ! class_exists( 'CT_Vc_Progressbar_Widget' ) ) {

	include_once '_widget.php';

	class CT_Vc_Progressbar_Widget extends CT_Vc_Widgets {

		public function init_fields() {
			$file = __DIR__ . '/config/progressbar.php';
			if ( file_exists( $file ) ) {
				require_once $file;

				$config = new CT_Vc_Progressbar_Config();

				$this->tag         = $config::get_data( 'tag' );
				$this->name        = $config::get_data( 'name' );
				$this->description = $config::get_data( 'description' );

				$this->fields          = $config::fields();
				$this->as_parent       = $config::as_parent();
				$this->content_element = $config::content_element();
				$this->icon            = $config::icon();
			}
		}

		public function render( $atts, $content = null ) {

			$output            = "";
			$title             = ( isset( $atts['title'] ) ? $atts['title'] : '' );
			$value             = ( isset( $atts['value'] ) ? $atts['value'] : '' );
			$tooltip           = ( ! empty( $atts['tooltip'] ) );
			$tooltip_color     = ( isset( $atts['tooltip_color'] ) ? $atts['tooltip_color'] : '' );
			$classes           = ( $tooltip ) ? 'progress-bar--tooltip' : '';
			$background        = ( isset( $atts['background'] ) ? $atts['background'] : '' );
			$backgroundBase    = ( isset( $atts['background_base'] ) ? $atts['background_base'] : '' );
			$tooltipBackground = ( $tooltip && $tooltip_color ) ? $tooltip_color : '';

			$styles_title = $this->generateStyle( array(
				'use_custom_font' => isset( $atts['use_custom_font'] ) ? $atts['use_custom_font'] : null,
				'google_fonts'    => isset( $atts['google_fonts'] ) ? $atts['google_fonts'] : '',
				'font_container'  => isset( $atts['font_container'] ) ? $atts['font_container'] : ''
			) );

			if ( ! empty( $styles_title ) ) {
				$styles_title_css = esc_attr( implode( ';', $styles_title ) ) . ';';
			} else {
				$styles_title_css = '';
			}

			$styles_value = $this->generateStyle( array(
				'use_custom_font' => isset( $atts['use_custom_font_value'] ) ? $atts['use_custom_font_value'] : null,
				'google_fonts'    => isset( $atts['google_fonts_value'] ) ? $atts['google_fonts_value'] : '',
				'font_container'  => isset( $atts['font_container_value'] ) ? $atts['font_container_value'] : ''
			) );

			if ( $tooltipBackground ) {
				$styles_value[] = 'background-color:' . $tooltipBackground;
			}

			if ( ! empty( $styles_value ) ) {
				$styles_value_css = esc_attr( implode( ';', $styles_value ) ) . ';';
			} else {
				$styles_value_css = '';
			}

			$class = 'progressbar-widget-' . $this->get_new_uniqid( true );

			if ( ! empty( $styles_title_css ) ) {
				$output .= ".{$class} .progress-bar__title {{$styles_title_css}}";
			}

			if ( ! empty( $backgroundBase ) ) {
				$output .= ".{$class} .progress-bar__bar-wrapper {background-color: {$backgroundBase};}";
			}

			if ( ! empty( $value ) ) {
				$output .= ".{$class} .progress-bar__bar {width: {$value}%;background-color: {$background};}";
			}

			if ( ! empty( $styles_value_css ) ) {
				$output .= ".{$class} .progress-bar__value {{$styles_value_css}}";
			}

			if ( ! empty( $tooltipBackground ) ) {
				$output .= ".{$class}.progress-bar--tooltip .progress-bar__value:after {border-top-color: {$tooltipBackground};}";
			}

			if ( !empty($output) ) {
				$output = '<style>' . $output . '</style>';
			}


			$progressbar_class = esc_attr(
				implode( ' ',
					[ 'progress-bar', 'progress-bar--green', $classes, $class ]
				) );

			$output .= '<div class="' . $progressbar_class . '">';
			$output .= '    <span class="progress-bar__title">' . $title . '</span>';
			$output .= '    <div class="progress-bar__bar-wrapper">';
			$output .= '        <div class="progress-bar__bar progress-bar__bar0">';
			$output .= '            <span class="progress-bar__value">' . $value . '%</span>';
			$output .= '        </div>';
			$output .= '    </div>';
			$output .= '</div>';


			return $output;
		}


	}

	new CT_Vc_Progressbar_Widget();

}
