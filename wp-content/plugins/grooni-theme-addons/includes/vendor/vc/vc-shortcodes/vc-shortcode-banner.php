<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );
/**
 * Add VisualComposer blog shortcode Class.
 *
 * @package Grooni_Theme_Addons
 */


if ( ! class_exists( 'CT_Vc_Banner_Widget' ) ) {

	include_once '_widget.php';

	class CT_Vc_Banner_Widget extends CT_Vc_Widgets {

		function __construct() {

			parent::__construct();

		}

		public function init_fields() {

			$file = __DIR__ . '/config/banner.php';
			if ( file_exists( $file ) ) {
				require_once $file;

				$config = new CT_Vc_Banner_Config();

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

			$atts = $this->fill_empty_atts( $atts );

			$output = $this->render_items( $atts );

			return $output;

		}


		/**
		 * Front-end render (wrapper)
		 *
		 * @param $atts
		 *
		 * @return string
		 */
		protected function render_items( $atts ) {

			$args = array(
				'before_widget' => '<div class="widget">',
				'after_widget'  => '</div>',
				'before_title'  => '<h4 class="widget-title">',
				'after_title'   => '</h4>'
			);

			$output = '';


			$output .= $args['before_widget'];
			$output .= '<div class="crane-widget-banner">';

			if ( ! empty( $atts['title'] ) ) {
				$output .= $args['before_title'];
				$output .= esc_html( $atts['title'] );
				$output .= $args['after_title'];
			}

			$image = wp_get_attachment_image_src(
				$atts['image'],
				'full'
			);

			$image_src = isset( $image[0] ) ? $image[0] : '';

			$output_img = '';

			if ( $image_src ) {
				$output_img .= '<img class="crane-widget-banner-img" src="' . esc_url( $image_src ) . '">';
			}

			if ( ! empty( $atts['link'] ) && ! empty( $atts['action'] ) && 'url' == $atts['action'] ) {
				$output_img = '<a class="crane-widget-banner-link" href="' . esc_url( $atts['link'] ) . '" target="_blank">' . $output_img . '</a>';
			}

			$output .= $output_img;

			$output .= '</div>';
			$output .= $args['after_widget'];


			return $output;
		}





	}

	new CT_Vc_Banner_Widget();

}
