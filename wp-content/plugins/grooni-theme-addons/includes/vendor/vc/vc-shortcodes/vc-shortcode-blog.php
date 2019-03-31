<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );
/**
 * Add VisualComposer blog shortcode Class.
 *
 * @package Grooni_Theme_Addons
 */


if ( ! class_exists( 'CT_Vc_Blog_Widget' ) ) {

	include_once '_widget.php';

	class CT_Vc_Blog_Widget extends CT_Vc_Widgets {

		function __construct() {

			parent::__construct();

			if ( function_exists( 'wp_doing_ajax' ) && wp_doing_ajax() ) {
				add_action( 'wp_ajax_crane_load_more_blog', array( $this, 'load_more_posts_callback' ) );
				add_action( 'wp_ajax_nopriv_crane_load_more_blog', array( $this, 'load_more_posts_callback' ) );
			}

		}

		public function init_fields() {

			$file = __DIR__ . '/config/blog.php';
			if ( file_exists( $file ) ) {
				require_once $file;

				$config = new CT_Vc_Blog_Config();

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
			/**
			 * @var $posts WP_Post[]
			 */

			global $ct_query_items;

			$atts = $this->fill_empty_atts( $atts );

			$output = '';

			// for function 'load_more_blog_callback'
			if ( defined( 'DOING_AJAX' ) ) {
				$output .= $this->render_items( $atts );
			} else {
				$items = $this->render_items( $atts );

				$css_class = $this->get_items_wrapper_class( $atts );

				if ( ! empty( $items ) ) {
					$output .= '<div class="crane-blog-widget ' . esc_attr( $css_class ) . '" data-params="' . htmlentities( json_encode( $atts ) ) . '">';
					$output .= $this->render_items( $atts );
					$output .= '</div>';
				} else {
					$output .= '<div class="crane-blog-widget--empty ' . esc_attr( $css_class ) . '"><span>';
					$output .= esc_html__( 'No blog single posts found. Please add at least one.', 'grooni-theme-addons' );
					$output .= '</span></div>';
				}
			}

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
			$layout = ( ! empty( $atts['layout'] ) ? $atts['layout'] : 'cell' );

			$atts['exclude_posts_ids'] = [ ];
			$exclude                   = isset( $_REQUEST['existItems'] ) ? explode( ',', esc_attr( wp_unslash( $_REQUEST['existItems'] ) ) ) : null;
			if ( $exclude && count( $exclude ) > 0 ) {
				foreach ( $exclude as $exclude_post_id ) {
					$exclude_post_id = intval( $exclude_post_id );
					if ( $exclude_post_id ) {
						$atts['exclude_posts_ids'][] = $exclude_post_id;
					}
				}
			}

			$itemsHtml = '';

			// Get blog items
			$items = $this->get_posts_data_by_param( $atts );

			if ( function_exists( 'crane_override_options' ) ) {
				crane_override_options( array( 'ct_vc_blog' => $atts ) );
			}

			if ( ! isset( $items['error'] ) ) {
				$alt_layout  = false;
				$items_count = 0;
				foreach ( $items as $item ) {
					if ( 'cell' == $layout ) {
						if ( $items_count >= $atts['columns_cell'] ) {
							$alt_layout  = ! $alt_layout;
							$items_count = 0;
						}
					}

					$itemsHtml .= $this->generate_items_layout( $atts, $item, $alt_layout );

					$items_count ++;
				}
			}

			if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
				return $itemsHtml;
			}

			$output = '';
			if ( $itemsHtml ) {
				$output .= '<div class="crane-blog-grid loading">';
				$output .= '<div class="crane-blog-grid-sizer"></div>';
				$output .= $itemsHtml;
				$output .= '</div>';
			}

			return $output;
		}


		/**
		 * Back-end
		 * Set query attributes for blog and return blog items
		 *
		 * @param $atts
		 *
		 * @return array
		 */
		protected function get_posts_data_by_param( $atts ) {
			global $ct_query_items;

			$categories = array();
			if ( ! empty( $atts['category'] ) ) {
				$categories = crane_get_terms_by_taxonomy( 'category', $atts['category'] );
			}

			$post_tags = array();
			if ( ! empty( $atts['tag'] ) ) {
				$post_tags = crane_get_terms_by_taxonomy( 'post_tag', $atts['tag'] );
			}

			if ( ! isset( $atts['posts_limit'] ) || ! $atts['posts_limit'] ) {
				$atts['posts_limit'] = - 1;
			}

			if ( isset( $atts['orderby'] ) ) {
				$atts['orderby'] = sanitize_sql_orderby( $atts['orderby'] );
				if ( ! $atts['orderby'] ) {
					unset( $atts['orderby'] );
				}
			}

			if ( ! isset( $atts['show_custom_order'] ) || 'false' === $atts['show_custom_order'] ) {
				$atts['show_custom_order'] = false;
			}

			$query = array(
				'posts_per_page' => $atts['posts_limit'],
				'post_type'      => 'post', // blog posts
				'orderby'        => $atts['orderby'],
				'order'          => $atts['order'],
				'post_status'    => 'publish',
			);

			$cats = $tags = array();

			foreach ( $categories as $category ) {
				$cats[] = $category['id'];
			}
			if ( count( $cats ) > 0 ) {
				$query['category__in'] = $cats;
			}

			if ( ! empty( $atts['author'] ) ) {
				$query['author'] = $atts['author'];
			}

			foreach ( $post_tags as $tag ) {
				$tags[] = $tag['slug'];
			}
			if ( count( $tags ) > 0 ) {
				$query['tag_slug__in'] = $tags;
			}

			// change rule for orderby == custom
			if ( ! empty( $atts['show_custom_order'] ) && $atts['show_custom_order'] ) {

				$include_posts = array();
				$_query_ord    = $query;

				$_query_ord['posts_per_page'] = - 1;

				$loop = new WP_Query( array_merge( $_query_ord, array( 'fields' => 'ids' ) ) );

				if ( ! empty( $loop->posts ) ) {
					$posts_ids = implode( ',', $loop->posts );

					global $wpdb;
					// Get from meta table.
					$custom_order_query = $wpdb->get_results(
						"SELECT post_id, meta_value FROM {$wpdb->postmeta} WHERE post_id IN ({$posts_ids}) AND meta_key LIKE 'grooni_custom_order'",
						ARRAY_A
					);

					$custom_order = array();
					if ( ! empty( $custom_order_query ) ) {
						foreach ( $custom_order_query as $meta_item ) {
							if ( ! empty( $meta_item['meta_value'] ) && '0' !== $meta_item['meta_value'] ) {
								$custom_order[ strval( $meta_item['post_id'] ) ] = $meta_item['meta_value'];
							}
						}
					}

					uasort( $custom_order, function ( $a, $b ) {
						if ( $a == $b ) {
							return 0;
						}

						return ( $a < $b ) ? - 1 : 1;
					} );

					// include custom ordered ids first.
					foreach ( $custom_order as $index => $item ) {
						$include_posts[ 'p_' . $index ] = $index;
					}

					// add all other sorted by orther order.
					foreach ( $loop->posts as $post_id ) {
						$include_posts[ 'p_' . $post_id ] = $post_id;
					}
				}

				// clear data.
				unset( $loop );


				if ( ! isset( $atts['exclude_posts_ids'] ) ) {
					$atts['exclude_posts_ids'] = null;
				}

				if ( ! empty( $atts['exclude_posts_ids'] ) && is_array( $atts['exclude_posts_ids'] ) ) {

					foreach ( $atts['exclude_posts_ids'] as $exclude_id ) {
						if ( isset( $include_posts[ 'p_' . $exclude_id ] ) ) {
							unset( $include_posts[ 'p_' . $exclude_id ] );
						}
					}

				}

				$post__in = array();
				foreach ( $include_posts as $include_post_id ) {
					$post__in[] = $include_post_id;
				}

				// REWRITE main query
				$query = array(
					'post_type'      => 'post',
					'posts_per_page' => $atts['posts_limit'],
					'post__in'       => $post__in ? $post__in : null,
					'orderby'        => $post__in ? 'post__in' : null,
					'post__not_in'   => $post__in ? null : $atts['exclude_posts_ids'],
				);

			} else {

				if ( ! empty( $atts['exclude_posts_ids'] ) ) {
					$query['post__not_in'] = $atts['exclude_posts_ids'];
				}

			}

			$show_read_more = ( ! $atts['show_read_more'] || 'false' === $atts['show_read_more'] ) ? false : true;

			$items = array();

			$loop = new WP_Query( $query );

			if ( $loop->have_posts() ) {
				foreach ( $loop->posts as $post ) {

					/**
					 * @var $post WP_Post;
					 */
					$item = array();

					$item['thumb']          = crane_get_thumb( $post->ID, $atts['image_resolution'] );
					$item['full']           = crane_get_thumb( $post->ID, 'full' );
					$item['url']            = get_post_permalink( $post->ID, false );
					$item['id']             = $post->ID;
					$item['title']          = $post->post_title;
					$item['category']       = wp_get_post_terms( $post->ID, 'category' );
					$item['tag']            = wp_get_post_terms( $post->ID, 'post_tag' );
					$item['excerpt']        = $post->post_excerpt;
					$item['show_read_more'] = $show_read_more;
					$item['author']         = $post->post_author;
					$item['custom_text']    = '';

					$items[] = $item;

				}
			} else {
				$items['error'] = esc_html__( 'Sorry, no blog posts matched your criteria.', 'grooni-theme-addons' );
			}

			$ct_query_items = $loop;

			wp_reset_postdata();

			return $items;
		}


		/**
		 * Items layout template
		 *
		 * @param $atts
		 * @param $item
		 *
		 * @return string
		 */
		protected function generate_items_layout( $atts, $item, $alt_layout = false ) {
			global $post;
			$_post = $post;
			$post  = get_post( $item['id'], OBJECT );

			$alt = $alt_layout ? '_alt' : '';

			$layout = ( ! empty( $atts['layout'] ) ? $atts['layout'] : 'cell' );

			ob_start();
			if ( 'standard' == $layout ) {
				get_template_part( 'template-parts/format/standard/content', get_post_format() );
			} else {
				get_template_part( 'template-parts/format/masonry/content' . $alt, get_post_format() );
			}
			$html = ob_get_clean();

			$post = $_post;

			return $html;
		}


		/**
		 * Render pagination template
		 *
		 * @param $atts
		 * @param WP_Query $query
		 *
		 * @return string
		 */
		protected function render_paginator( $atts, WP_Query $query ) {

			if ( isset( $atts['pagination_type'] ) && ( ! $atts['pagination_type'] || empty( $atts['pagination_type'] ) ) ) {
				return '';
			}

			$next_url = $output = '';

			$page = isset( $query->query_vars ) ? $query->query_vars['paged'] : 0;

			$next_page = intval( $page ) + 1;
			$max_page  = isset( $query->max_num_pages ) ? $query->max_num_pages : 1;
			if ( $next_page <= $max_page ) {
				$next_url = get_pagenum_link( $next_page );
			}

			$button_text = ( isset( $atts['show_more_text'] ) && $atts['show_more_text'] ) ? $atts['show_more_text'] : esc_html__( 'Show more', 'grooni-theme-addons' );

			if ( $next_url ) {
				$output = '<div class="crane-pagination crane-pagination--style-' . esc_attr( $atts['pagination_type'] ) . '" data-maxpage="' . absint( $max_page ) . '"  data-load_more="0">';
				$output .= '	<div class="nav-links">';
				$output .= '		<button href="' . esc_url( $next_url ) . '" class="paginate-loader crane-blog-pagination crane-pagination-show-more">';
				$output .= '			<span class="btn-txt">' . $button_text . '</span> <span class="fa fa-refresh fa-spin hidden"></span>';
				$output .= '		</button>';
				$output .= '	</div>';
				$output .= '</div>';
			}

			return $output;
		}

		/**
		 * @param $atts
		 *
		 * @return string
		 */
		protected function get_items_wrapper_class( $atts ) {
			$classes   = array();
			$classes[] = 'crane-blog-layout-' . $atts['layout'];

			if ( 'masonry' == $atts['layout'] && $atts['columns'] ) {
				$classes[] = 'crane-column-' . $atts['columns'];
			} elseif ( 'cell' == $atts['layout'] && $atts['columns_cell'] ) {
				$classes[] = 'crane-column-' . $atts['columns_cell'];
			} elseif ( 'standard' == $atts['layout'] ) {
				$classes[] = 'crane-column-1';
			} else {
				$classes[] = 'crane-column-2';
			}

			if ( 'masonry' == $atts['layout'] && isset( $atts['style'] ) && $atts['style'] ) {
				$classes[] = 'crane-blog-style-' . $atts['style'];
			}

			if ( 'masonry' == $atts['layout'] ) {
				$classes[] =
					(
						isset( $atts['img_proportion'] ) &&
						$atts['img_proportion'] &&
						'original' !== $atts['img_proportion']
					)
						? 'crane-blog-ratio_' . $atts['img_proportion'] : 'crane-blog-ratio_origin';
			}

			return implode( ' ', $classes );
		}

		/**
		 * @param $item
		 *
		 * @return string
		 */
		protected function get_items_category_classes( $item ) {
			$classes = array();
			if ( isset( $item['category'] ) ) {
				foreach ( $item['category'] as $category ) {
					$classes[] = 'cat__' . $category->slug;
				}
			}

			return implode( ' ', $classes );
		}

	}

	new CT_Vc_Blog_Widget();

}
