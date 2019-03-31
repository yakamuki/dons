<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );
/**
 * Add VisualComposer blog shortcode Class.
 *
 * @package Grooni_Theme_Addons
 */


if ( ! class_exists( 'CT_Vc_RecentPosts_Widget' ) ) {

	include_once '_widget.php';

	class CT_Vc_RecentPosts_Widget extends CT_Vc_Widgets {

		function __construct() {

			parent::__construct();

		}

		public function init_fields() {

			$file = __DIR__ . '/config/recent_posts.php';
			if ( file_exists( $file ) ) {
				require_once $file;

				$config = new CT_Vc_RecentPosts_Config();

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

			$title = ( ! empty( $atts['title'] ) ) ? esc_html( $atts['title'] ) : '';

			$number = ( ! empty( $atts['number'] ) ) ? absint( $atts['number'] ) : 5;
			if ( ! $number ) {
				$number = 5;
			}
			$show_date = empty( $atts['show_date'] ) ? false : true;

			$request_args = array(
				'numberposts'      => $number,
				'offset'           => 0,
				'category'         => 0,
				'orderby'          => 'post_date',
				'order'            => 'DESC',
				'include'          => '',
				'exclude'          => '',
				'meta_key'         => '',
				'meta_value'       => '',
				'post_type'        => 'post',
				'post_status'      => 'publish',
				'suppress_filters' => true
			);

			$recent_posts = wp_get_recent_posts( $request_args );

			if ( ! empty( $recent_posts ) && is_array( $recent_posts ) ) {
				$output .= $args['before_widget'];
				if ( $title ) {
					$output .= $args['before_title'] . $title . $args['after_title'];
				}


				$output .= '<ul class="crane-re-posts">';
				foreach ( $recent_posts as $recent ) {

					if ( ! isset( $recent['ID'] ) || ! $recent['ID'] ) {
						continue;
					}

					$post_title = $recent['post_title'] ? : esc_html__( 'Post:', 'grooni-theme-addons' ) . ' ' . $recent['ID'];
					$the_date   = mysql2date( get_option( 'date_format' ), $recent['post_date'] );
					$post_img   = get_the_post_thumbnail( $recent['ID'], 'thumbnail' );

					if ( ! $post_img ) {
						$post_img = crane_get_thumb( $recent['ID'], 'thumbnail' );
						if ( $post_img ) {
							$post_img = '<img src="' . esc_url( $post_img ) . '" class="attachment-thumbnail size-thumbnail wp-post-image" alt="">';
						} else {
							$post_img = '';
						}
					}


					$output .= '<li class="crane-re-posts__item">';
					global $crane_options;
					if ( $post_img || ( isset( $crane_options['show_featured_placeholders'] ) && $crane_options['show_featured_placeholders'] ) ) {
						$output .= '<div class="crane-re-posts__img' . crane_get_placeholder_html_class( $post_img ) . '">' . $post_img . '</div>';
					}
					$output .= '<div class="crane-re-posts__meta">';
					$output .= '<a class="crane-re-posts__link" href="' . get_the_permalink( $recent['ID'] ) . '">' . esc_html( $post_title ) . '</a>';
					if ( $show_date ) {
						$output .= '<span class="crane-re-posts__date">' . esc_html( $the_date ) . '</span>';
					}
					$output .= '</div>';
					$output .= '</li>';
				}
				$output .= '</ul>';
				$output .= $args['after_widget'];

			}

			return $output;
		}


	}

	new CT_Vc_RecentPosts_Widget();

}
