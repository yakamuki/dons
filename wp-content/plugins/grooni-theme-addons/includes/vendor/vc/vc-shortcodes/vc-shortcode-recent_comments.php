<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );
/**
 * Add VisualComposer blog shortcode Class.
 *
 * @package Grooni_Theme_Addons
 */


if ( ! class_exists( 'CT_Vc_RecentComments_Widget' ) ) {

	include_once '_widget.php';

	class CT_Vc_RecentComments_Widget extends CT_Vc_Widgets {

		function __construct() {

			parent::__construct();

		}

		public function init_fields() {

			$file = __DIR__ . '/config/recent_comments.php';
			if ( file_exists( $file ) ) {
				require_once $file;

				$config = new CT_Vc_RecentComments_Config();

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

			$title          = ( ! empty( $atts['title'] ) ) ? esc_html( $atts['title'] ) : '';
			$number         = ( ! empty( $atts['number'] ) ) ? absint( $atts['number'] ) : 5;
			$post_type      = ( ! empty( $atts['post_type'] ) ) ? esc_attr( $atts['post_type'] ) : 'post';
			$excerpt_height = ( ! empty( $atts['excerpt_height'] ) ) ? absint( $atts['excerpt_height'] ) : 80;

			$arg = array(
				'number'      => $number,
				'status'      => 'approve',
				'post_status' => 'publish'
			);

			if ( 'any' !== $post_type ) {
				$arg['post_type'] = $post_type;
			}

			$comments = get_comments( $arg );

			$output .= $args['before_widget'];
			if ( $title ) {
				$output .= $args['before_title'] . $title . $args['after_title'];
			}

			$output .= '<ul class="crane-re-comments__list">';
			if ( is_array( $comments ) && $comments ) {
				// Prime cache for associated posts. (Prime post term cache if we need it for permalinks.)
				$post_ids = array_unique( wp_list_pluck( $comments, 'comment_post_ID' ) );
				_prime_post_caches( $post_ids, strpos( get_option( 'permalink_structure' ), '%category%' ), false );

				foreach ( (array) $comments as $comment ) {
					$output .= '<li class="crane-re-comments__item">';
					$output .= '	<div class="crane-re-comments__text" data-height ="'.$excerpt_height.'">';
					$output .= '<p>';
					$output .= $comment->comment_content;
					$output .= '</p>';
					$output .= '	</div>';

					$output .= '	<div class="crane-re-comments__meta">';
					/* translators: comments widget: 1: comment author, 2: post link */
					$output .= sprintf( esc_html_x( '%1$s on %2$s', 'widgets', 'grooni-theme-addons' ),
						'<span class="crane-re-comments__author">' . get_comment_author_link( $comment ) . '</span>',
						'<a href="' . esc_url( get_comment_link( $comment ) ) . '">' . get_the_title( $comment->comment_post_ID ) . '</a>'
					);
					$output .= '	</div>';
					$output .= '</li>';
				}
			}
			$output .= '</ul>';
			$output .= $args['after_widget'];


			return $output;
		}


	}

	new CT_Vc_RecentComments_Widget();

}
