<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );
/**
 * Crane theme pagination functions.
 *
 * @package crane
 */


if ( ! function_exists( 'crane_get_the_posts_pagination' ) ) {

	/**
	 * Return a paginated navigation to next/previous set of posts,
	 * when applicable.
	 *
	 * @param string $where
	 * @param array $options
	 *
	 * @return string
	 */
	function crane_get_the_posts_pagination( $where = 'blog', $options = array(), $custom_query = null ) {

		$output = '';
		$paginate_data = array();

		if ( ! $options || empty( $options ) ) {
			global $crane_options;
			$options         = $crane_options;
			$pagination_type = isset( $options[ 'crane-' . $where . '-paginator' ] ) ? $options[ 'crane-' . $where . '-paginator' ] : 'wordpress';
			$show_more_text = isset( $options[ $where . '-show_more_text' ] ) ? $options[ $where . '-show_more_text' ] : esc_html__( 'Show more', 'crane' );
		} else {
			$pagination_type = isset( $options['pagination_type'] ) ? $options['pagination_type'] : 'show_more';
			$show_more_text = isset( $options['show_more_text' ] ) ? $options['show_more_text' ] : esc_html__( 'Show more', 'crane' );
		}

		$elem_class = 'navigation--type-' . $pagination_type;
		$paginate_data[] = 'data-where="' . $where . '"';

		$prev_next_type = isset( $options[ $where . '-pagination-prev_next-type' ] ) ? $options[ $where . '-pagination-prev_next-type' ] : 'text';

		if ( $prev_next_type === 'arrows' ) {
			$prev_text  = '<i class="fa fa-chevron-left"></i>';
			$next_text  = '<i class="fa fa-chevron-right"></i>';
			$elem_class = $elem_class . '-arrows';
		} else {
			$prev_text  = esc_html__( 'Previous page', 'crane' );
			$next_text  = esc_html__( 'Next page', 'crane' );
			$elem_class = $elem_class . '-text';
		}

		$args = array(
			'show_all'           => false,
			'prev_next'          => true,
			'prev_text'          => $prev_text,
			'next_text'          => $next_text,
			'end_size'           => 1,
			'mid_size'           => 2,
			'type'               => 'array',
			'add_fragment'       => '',
		);

		// default wordpress pagination
		if ( 'wordpress' === $pagination_type ) {
			return '<div class="crane-pagination crane-pagination-wp">' . get_the_posts_pagination( $args ) . '</div>';
		}

		if ( 'more' === $pagination_type || 'show_more' === $pagination_type || 'scroll' === $pagination_type ) {

			$button_type = ( 'show_more' === $pagination_type ) ? ' paginate-click' : '';

			$output .= '<button class="paginate-loader' . $button_type . ' ' . sanitize_html_class( $elem_class ) . '">';
			$output .= '	<span class="btn-txt">' . $show_more_text . '</span> <span class="fa fa-refresh fa-spin hidden"></span>';
			$output .= '</button>';
			$output .= '<div class="invisible">';

			if ( $custom_query && isset( $custom_query->max_num_pages ) ) {
				global $wp_query;
				$orig_query = $wp_query; // fix for pagination to work
				$wp_query   = null;
				$wp_query   = $custom_query;
			}

			if ( 'shop' === $where ) {
				$counter = crane_woocommerce_loop_result_count();
				if ( ! empty( $counter ) ) {
					$paginate_data[] = 'data-first="' . $counter['first'] . '"';
					$paginate_data[] = 'data-last="' . $counter['last'] . '"';
					$paginate_data[] = 'data-total="' . $counter['total'] . '"';
					$paginate_data[] = 'data-text="' . htmlspecialchars( $counter['text'], ENT_QUOTES ) . '"';
				}
			}

			$output .= get_the_posts_navigation();

			if ( $custom_query && isset( $orig_query ) ) {
				$wp_query = null;
				$wp_query = $orig_query; // fix for pagination to work
			}

			$output .= '</div>';

		}

		// Don't print empty markup if there's only one page.
		if ( 'scroll' !== $pagination_type && $GLOBALS['wp_query']->max_num_pages > 1 ) {

			// Set up paginated links.
			$links = paginate_links( $args );

			if ( count( $links ) ) {
				$template =  '<nav class="navigation %1$s" role="navigation">';
				$template .= '	<div class="nav-links">%2$s</div>';
				$template .= '</nav>';

				$output .= sprintf(
					$template,
					sanitize_html_class( $elem_class ),
					join( "\n", $links )
				);
			}
		}

		return '<div class="crane-pagination crane-pagination-numbers" ' . implode( ' ', $paginate_data ) . '>' . $output . '</div>';

	}

}

if ( ! function_exists( 'crane_the_posts_pagination' ) ) {

	/**
	 * Alias function for crane_get_the_posts_pagination()
	 *
	 * @param string $where
	 * @param array $options
	 */
	function crane_the_posts_pagination( $where = 'blog', $options = array() ) {
		echo crane_get_the_posts_pagination( $where, $options );
	}
}
