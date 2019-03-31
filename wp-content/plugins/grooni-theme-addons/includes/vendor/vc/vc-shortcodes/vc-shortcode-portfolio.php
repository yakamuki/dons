<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );
/**
 * Add VisualComposer portfolio shortcode Class.
 *
 * @package Grooni_Theme_Addons
 */


if ( ! class_exists( 'CT_Vc_Portfolio_Widget' ) ) {

	include_once '_widget.php';

	class CT_Vc_Portfolio_Widget extends CT_Vc_Widgets {

		function __construct() {

			parent::__construct();

			if ( function_exists( 'wp_doing_ajax' ) && wp_doing_ajax() ) {
				add_action( 'wp_ajax_crane_load_more_portfolio', array( $this, 'load_more_posts_callback' ) );
				add_action( 'wp_ajax_nopriv_crane_load_more_portfolio', array( $this, 'load_more_posts_callback' ) );
			}

		}

		public function init_fields() {
			$file = __DIR__ . '/config/portfolio.php';
			if ( file_exists( $file ) ) {
				require_once $file;

				$config = new CT_Vc_Portfolio_Config();

				$this->tag         = $config::get_data( 'tag' );
				$this->name        = $config::get_data( 'name' );
				$this->description = $config::get_data( 'description' );

				$this->fields          = $config::fields( crane_get_terms_by_taxonomy( 'crane_portfolio_cats' ), crane_get_terms_by_taxonomy( 'crane_portfolio_tags' ) );
				$this->as_parent       = $config::as_parent();
				$this->content_element = $config::content_element();
				$this->icon            = $config::icon();
			}
		}

		/**
		 * Front-end
		 *
		 * @param $atts
		 * @param null $content
		 *
		 * @return string
		 */
		public function render( $atts, $content = null ) {
			global $ct_query_items;

			$atts = $this->fill_empty_atts( $atts );

			$output = '';

			// for function 'load_more_portfolio_callback'
			if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
				$output .= $this->render_items( $atts );
			} else {
				$items = $this->render_items( $atts );

				$css_class = $this->get_items_wrapper_class( $atts );

				if ( ! empty( $items ) ) {
					$render_categories = $this->render_categories( $atts );

					$att_data_count = '';
					if ( empty( $render_categories ) && ! empty( $atts['pagination_type'] ) && $atts['pagination_type'] ) {
						$counts = $this->get_portfolio_counts( $atts );
						if (isset( $counts['*'])) {
							$count = count( $counts['*'] );
						} else {
							$count = '0';
						}
						$att_data_count = ' data-count="' . $count . '"';
					}

					$output .= '<div class="crane-portfolio-widget loading ' . esc_attr( $css_class ) . '" data-params="' . htmlentities( json_encode( $atts ) ) . '"' . $att_data_count . '>';
					$output .= $render_categories;
					$output .= $this->render_items( $atts );
					$output .= $this->render_paginator( $atts, $ct_query_items );
					$output .= '</div>';
				} else {
					$output .= '<div class="crane-portfolio-widget--empty ' . esc_attr( $css_class ) . '"><span>';
					$output .= esc_html__( 'No portfolio single posts found. Please add at least one.', 'grooni-theme-addons' );
					$output .= '</span></div>';
				}
			}

			return $output;
		}


		/**
		 * Back-end
		 * Set query attributes for portfolio and return portfolio items
		 *
		 * @param $atts
		 *
		 * @return array
		 */
		protected function get_portfolio_data_by_param( $atts ) {
			global $ct_query_items;

			if ( function_exists( 'crane_get_meta_data' ) ) {
				$Crane_Meta_Data = crane_get_meta_data();
			}

			$categories = array();
			if ( ! empty( $atts['category'] ) ) {
				$categories = crane_get_terms_by_taxonomy( 'crane_portfolio_cats', $atts['category'] );
			}

			$portfolio_tags = array();
			if ( ! empty( $atts['tag'] ) ) {
				$portfolio_tags = crane_get_terms_by_taxonomy( 'crane_portfolio_tags', $atts['tag'] );
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
				'post_type'      => 'crane_portfolio',
				'orderby'        => isset( $atts['orderby'] ) ? $atts['orderby'] : 'date',
				'order'          => isset( $atts['order'] ) ? $atts['order'] : 'ASC',
				'post_status'    => 'publish',
			);

			$cats = $tags = array();

			foreach ( $categories as $category ) {
				$cats[] = $category['slug'];
			}
			if ( count( $cats ) > 0 ) {
				$query['crane_portfolio_cats'] = implode( ',', $cats );
			}

			if ( ! empty( $atts['author'] ) ) {
				$query['author'] = $atts['author'];
			}

			foreach ( $portfolio_tags as $tag ) {
				$tags[] = $tag['slug'];
			}
			if ( count( $tags ) > 0 ) {
				$query['crane_portfolio_tags'] = implode( ',', $tags );
			}

			// change rule for orderby == custom
			if ( ! empty( $atts['show_custom_order'] ) && $atts['show_custom_order'] ) {

				$include_posts = array();
				$_query_ord    = $query;

				$_query_ord['posts_per_page'] = - 1;

				$loop      = new WP_Query( array_merge( $_query_ord, array( 'fields' => 'ids' ) ) );

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
					'post_type'      => 'crane_portfolio',
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

			if (
				! empty( $atts['hover_style'] ) &&
				'2' === $atts['hover_style'] &&
				! empty( $atts['image_resolution_for_link'] )
			) {
				$image_link_size = $atts['image_resolution_for_link'];
			}

			$items = array();
			$loop  = new WP_Query( $query );

			if ( $loop->have_posts() ) {

				foreach ( $loop->posts as $post ) {

					/**
					 * @var $post WP_Post;
					 */
					$item = array();

					$item['masonry'] = '';
					if ( ! empty( $Crane_Meta_Data ) ) {
						$item['masonry'] = $Crane_Meta_Data->get( 'portfolio-masonry-size', $post->ID, 'crane_portfolio', [ ], false );
					}
					$thumbSize = 'portfolio-width1-height1';
					if ( empty( $item['masonry'] ) ) {
						$item['masonry'] = 'width1-height1';
					}
					if ( isset( $atts['layout'] ) && $atts['layout'] == 'masonry' ) {
						$thumbSize = 'portfolio-' . $item['masonry'];
					}
					if ( empty( $item['excerpt_strip_html'] ) || ( ! empty( $item['excerpt_strip_html'] ) && $item['excerpt_strip_html'] ) ) {
						$item['excerpt'] = $post->post_excerpt;
					} else {
						$item['excerpt'] = do_shortcode( $post->post_content );
					}

					$size = $this->get_image_size_from_basic_resolution( $thumbSize, $atts );

					$item['thumb']       = crane_get_thumb( $post->ID, $size );
					$item['full']        = crane_get_thumb( $post->ID, 'full' );
					$item['url']         = get_post_permalink( $post->ID, false );
					$item['id']          = $post->ID;
					$item['title']       = $post->post_title;
					$item['category']    = wp_get_post_terms( $post->ID, 'crane_portfolio_cats' );
					$item['tag']         = wp_get_post_terms( $post->ID, 'crane_portfolio_tags' );
					$item['author']      = $post->post_author;
					$item['custom_text'] = '';
					if ( ! empty( $image_link_size ) ) {
						$item['image_link'] = crane_get_thumb( $post->ID, $image_link_size );
					}

					if ( ! empty( $Crane_Meta_Data ) &&
					     $atts['show_custom_text'] &&
					     ! is_null( $Crane_Meta_Data->get( 'portfolio-custom-text', $post->ID, 'crane_portfolio', [ ], false ) ) &&
					     $Crane_Meta_Data->get( 'portfolio-custom-text', $post->ID, 'crane_portfolio', [ ], false )
					) {
						$item['custom_text'] = $Crane_Meta_Data->get( 'portfolio-custom-text', $post->ID, 'crane_portfolio', [ ], false );
					}

					$items[] = $item;

				}
			} else {
				$items['error'] = esc_html__( 'Sorry, no portfolio matched your criteria.', 'grooni-theme-addons' );
			}

			$ct_query_items = $loop;

			wp_reset_postdata();

			return $items;
		}


		/**
		 * Back-end
		 * Set query attributes for portfolio and return portfolio items
		 *
		 * @return array
		 */
		public function get_portfolio_counts( $atts = array() ) {

			$categories = [ '*' => [ ] ];
			foreach ( crane_get_terms_by_taxonomy( 'crane_portfolio_cats' ) as $category ) {
				$categories[ $category['slug'] ] = [ ];
			}

			$portfolio_cats = array();
			if ( isset( $atts['category'] ) && ! empty( $atts['category'] ) ) {
				$portfolio_cats = crane_get_terms_by_taxonomy( 'crane_portfolio_cats', $atts['category'] );
			}

			$portfolio_tags = array();
			if ( isset( $atts['tag'] ) && ! empty( $atts['tag'] ) ) {
				$portfolio_tags = crane_get_terms_by_taxonomy( 'crane_portfolio_tags', $atts['tag'] );
			}


			if ( ! isset( $atts['show_custom_order'] ) || 'false' === $atts['show_custom_order'] ) {
				$atts['show_custom_order'] = false;
			}

			$query = array(
				'posts_per_page' => - 1, // For count we need all posts
				'post_type'      => 'crane_portfolio',
				'orderby'        => isset( $atts['orderby'] ) ? $atts['orderby'] : 'date',
				'order'          => isset( $atts['order'] ) ? $atts['order'] : 'ASC',
				'post_status'    => 'publish',
			);

			$cats = $tags = array();

			foreach ( $portfolio_cats as $category ) {
				$cats[] = $category['slug'];
			}
			if ( count( $cats ) > 0 ) {
				$query['crane_portfolio_cats'] = implode( ',', $cats );
			}

			if ( isset( $atts['author'] ) && ! empty( $atts['author'] ) ) {
				$query['author'] = $atts['author'];
			}

			foreach ( $portfolio_tags as $tag ) {
				$tags[] = $tag['slug'];
			}
			if ( count( $tags ) > 0 ) {
				$query['crane_portfolio_tags'] = implode( ',', $tags );
			}


			$loop = new WP_Query( $query );

			if ( $loop->have_posts() ) {
				foreach ( $loop->posts as $post ) {

					foreach ( wp_get_post_terms( $post->ID, 'crane_portfolio_cats' ) as $cat ) {
						if ( array_key_exists( $cat->slug, $categories ) ) {
							$categories[ $cat->slug ][ $post->ID ] = $categories['*'][ $post->ID ] = $post->ID;
						}
					}


				}
			}
			wp_reset_postdata();

			return $categories;
		}


		/**
		 * @param $item
		 *
		 * @return string
		 */
		protected function get_items_category_classes( $atts, $item ) {
			$classes = array();
			if ( isset( $item['category'] ) ) {
				foreach ( $item['category'] as $category ) {
					$classes[] = 'cat__' . $category->slug;
				}
			}

			if ( $atts['layout'] == 'masonry' && $item['masonry'] ) {
				$classes[] = 'crane-portfolio-grid-item-' . $item['masonry'];
			}


			return implode( ' ', $classes );
		}

		/**
		 * Filter template
		 *
		 * @param $atts
		 *
		 * @return string
		 */
		protected function render_categories( $atts ) {
			if ( ! $atts['sortable'] ) {
				return '';
			}
			$categoriesHtml = '';
			$categoriesList = '';

			$counts      = $this->get_portfolio_counts( $atts );
			$exist_cats  = crane_get_terms_by_taxonomy( 'crane_portfolio_cats', $atts['category'] );
			$_exist_cats = array();
			foreach ( $exist_cats as $key => $category ) {
				$_exist_cats[ $category['slug'] ] = $category;
			}
			$exist_cats = $_exist_cats;
			unset( $_exist_cats );

			$sorted_values = array();
			foreach ( explode( ',', $atts['category'] ) as $slug ) {
				if ( $slug && isset( $exist_cats[ $slug ] ) ) {
					$sorted_values[ $slug ] = $slug;
				}
			}

			if ( count( $exist_cats ) <= 1 ) {
				return '';
			}

			foreach ( array_merge( $sorted_values, $exist_cats ) as $category ) {
				$count = 0;
				if ( array_key_exists( $category['slug'], $counts ) ) {
					$count = count( $counts[ $category['slug'] ] );
				}
				$categoriesHtml .= '<button class="portfolio-filters-btn" data-filter=".cat__' . $category['slug'] . '" data-cat_slug="' . $category['slug'] . '" data-count="' . $count . '">' . $category['title'] . '</button>';
			}

			foreach ( array_merge( $sorted_values, $exist_cats ) as $category ) {
				$count = 0;
				if ( array_key_exists( $category['slug'], $counts ) ) {
					$count = count( $counts[ $category['slug'] ] );
				}
				$categoriesList .= '<option class="portfolio-filters-btn" data-filter=".cat__' . $category['slug'] . '" value=".cat__' . $category['slug'] . '" data-cat_slug="' . $category['slug'] . '" data-count="' . $count . '">' . $category['title'] . '</option>';
			}

			$output = '<div class="portfolio-filters portfolio-filters--width-' . $atts['sortable_width'] . ' portfolio-filters--style-' . $atts['sortable_style'] . ' portfolio-filters--align-' . $atts['sortable_align'] . '">';
			$output .= '<div class="portfolio-filters-group" > ';
			$output .= '<button class="active portfolio-filters-btn" data-filter = "*" data-count="' . count( $counts['*'] ) . '"> ' . esc_html__( 'All', 'grooni-theme-addons' ) . ' </button > ';
			$output .= $categoriesHtml;
			$output .= '	</div > ';
			$output .= '</div > ';

			$output .= '<select class="portfolio-filters-select">';
			$output .= '<option class="active portfolio-filters-btn" data-filter = "*" value="*" data-count="' . count( $counts['*'] ) . '"> ' . esc_html__( 'All', 'grooni-theme-addons' ) . ' </option > ';
			$output .= $categoriesList;
			$output .= '</select > ';

			return $output;
		}

		/**
		 * @param $atts
		 *
		 * @return string
		 */
		protected function get_items_wrapper_class( $atts ) {
			$classes   = array();
			$classes[] = 'crane-portfolio-layout-' . $atts['layout'];

			if ( isset( $atts['columns'] ) && $atts['columns'] ) {
				$classes[] = 'crane-column-' . $atts['columns'];
			}

			if ( ! isset( $atts['hover_style'] ) ) {
				$atts['hover_style'] = 1;
			}
			$classes[] = 'crane-portfolio-hover-' . $atts['hover_style'];

			$classes[] =
				(
					isset( $atts['img_proportion'] ) &&
					$atts['img_proportion'] &&
					'original' !== $atts['img_proportion']
				)
					? 'crane-portfolio-ratio_' . $atts['img_proportion'] : 'crane-portfolio-ratio_origin';

			if ( isset( $atts['style'] ) && $atts['style'] ) {
				$classes[] = 'crane-portfolio-style-' . $atts['style'];
			}

			return implode( ' ', $classes );
		}


		/**
		 * For filter by category
		 *
		 * @param $item
		 *
		 * @return string
		 */
		protected function render_item_category( $item, $target ) {
			$itemsHtml = '';

			if ( isset( $item['category'] ) ) {

				$terms     = get_the_terms( $item['id'], 'crane_portfolio_cats' );
				$links     = array();
				$term_list = '';

				if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {

					foreach ( $terms as $term ) {
						$link = get_term_link( $term, 'crane_portfolio_cats' );
						if ( is_wp_error( $link ) ) {
							return $link;
						}
						$links[] = '<a href="' . esc_url( $link ) . '" rel="tag"' . $target . '>' . $term->name . '</a>';
					}

					/**
					 * Filters the term links for a given taxonomy.
					 *
					 * The dynamic portion of the filter name, `$taxonomy`, refers
					 * to the taxonomy slug.
					 *
					 * @since 2.5.0
					 *
					 * @param array $links An array of term links.
					 */
					$term_links = apply_filters( "term_links-crane_portfolio_cats", $links );

					$term_list = join( ' / ', $term_links );

				}

				if ( empty( $term_list ) ) {
					return $itemsHtml;
				}

				$itemsHtml .= '<div class="portfolio-category">';
				$itemsHtml .= $term_list;
				$itemsHtml .= '</div>';
			}

			return $itemsHtml;

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

			$styles = $this->generateStyle( array(
				'use_custom_font' => isset( $atts['pagination_use_custom_font'] ) ? $atts['pagination_use_custom_font'] : null,
				'google_fonts'    => isset( $atts['pagination_google_fonts'] ) ? $atts['pagination_google_fonts'] : '',
				'font_container'  => isset( $atts['pagination_font_container'] ) ? $atts['pagination_font_container'] : ''
			) );

			if ( isset( $atts['pagination_text_transform'] ) && $atts['pagination_text_transform'] ) {
				$styles[] = 'text-transform:' . $atts['pagination_text_transform'];
			}

			if ( ! empty( $styles ) ) {
				$style = 'style="' . esc_attr( implode( ';', $styles ) ) . '"';
			} else {
				$style = '';
			}


			if ( $next_url ) {

				$output .= '<div class="crane-pagination crane-pagination--style-' . $atts['pagination_type'] . '" data-maxpage="' . $max_page . '"  data-load_more="0">';
				$output .= '	<div class="nav-links">';
				$output .= '		<button data-href="' . esc_url( $next_url ) . '" class="paginate-loader crane-portfolio-pagination crane-pagination-show-more">';
				$output .= '			<span class="btn-txt" ' . $style . '>' . $button_text . '</span> <span class="fa fa-refresh fa-spin hidden"></span>';
				$output .= '		</button>';
				$output .= '	</div>';
				$output .= '</div>';
			}

			return $output;
		}


		/**
		 * Image template
		 *
		 * @param $item
		 *
		 * @return string
		 *
		 */
		protected function generate_item_image( $item ) {
			if ( grooni_is_lazyload_enabled() ) {
				$image_src_attr = 'data-src="' . esc_url( $item['thumb'] ) . '"';
				$image_class_attr = ' class="crane-portfolio-grid-img lazyload"';
			} else {
				$image_src_attr = 'src="' . esc_url( $item['thumb'] ) . '"';
				$image_class_attr = ' class="crane-portfolio-grid-img"';
			}

			if ( $item['thumb'] ) {
				$itemsHtml = '<img ' . $image_src_attr . $image_class_attr . ' alt="' . $item['title'] . '">';
			} else {
				$itemsHtml = '';
			}

			return $itemsHtml;
		}


		/**
		 * Image content wrapper (title, categories, excerpt, tags)
		 *
		 * @param $atts
		 * @param $item
		 *
		 * @return string
		 *
		 */
		protected function generate_content_wrapper( $atts, $item ) {
			$target = ( isset( $atts['target'] ) && 'blank' == $atts['target'] ) ? ' target="_blank"' : '';

			$itemsHtml = '';

			if ( $atts['show_title_description'] && ! empty( $item['title'] ) ) {
				$itemsHtml .= '<h4 class="portfolio-title"><a href="' . esc_url( $item['url'] ) . '"' . $target . '>' . esc_html( $item['title'] ) . '</a></h4>';
			}
			if ( $atts['show_categories'] && ! empty( $item['category'] ) ) {
				$itemsHtml .= $this->render_item_category( $item, $target );
			}
			if ( $atts['show_excerpt'] && ! empty( $item['excerpt'] ) ) {
				$itemsHtml .= '<div class="portfolio-excerpt"><p>' . $item['excerpt'] . '</p></div>';
			}
			if ( $atts['show_read_more'] && 'false' !== $atts['show_read_more'] ) {
				$itemsHtml .= '<a class="post__readmore" href="' . esc_url(
					$item['url'] ) . '" ' . $target . '><span>' . esc_html__(
						'Read more', 'grooni-theme-addons' ) . '</span></a>';
			}
			if ( $item['custom_text'] || ( $atts['show_imgtags'] && ! empty( $item['tag'] ) ) ) {
				$itemsHtml .= '<div class="crane-portfolio-inliner">';
			}
			$itemsHtml .= $this->generate_item_tags( $item, $atts['show_imgtags'] );
			$itemsHtml .= $item['custom_text'] ? '<div class="crane-portfolio-custom-txt">' . $item['custom_text'] . '</div>' : '';
			if ( $item['custom_text'] || ( $atts['show_imgtags'] && ! empty( $item['tag'] ) ) ) {
				$itemsHtml .= '</div>';
			}

			if ( $itemsHtml ) {
				$itemsHtml = '<div class="crane-portfolio-grid-meta">' . $itemsHtml . '</div>';
			}

			return $itemsHtml;
		}

		/**
		 * Hover style template
		 *
		 * @param $atts
		 * @param $item
		 *
		 * @return string
		 */
		protected function generate_hover_style( $atts, $item ) {
			$target = ( isset( $atts['target'] ) && 'blank' == $atts['target'] ) ? ' target="_blank"' : '';

			$itemsHtml = '';

			switch ( $atts['hover_style'] ) {
				case 1: // Direction-aware hover
					$itemsHtml .= '<div class="portfolio-hover"><div class="portfolio-hover-inner">';
					$itemsHtml .= '<h4 class="portfolio-title"><a href="' . esc_url( $item['url'] ) . '"' . $target . '>' . esc_html( $item['title'] ) . '</a></h4>';
					$itemsHtml .= $this->render_item_category( $item, $target );
					$itemsHtml .= '</div></div>';
					break;

				case 2: // Overlay with zoom and link icons on hover
					$itemsHtml .= '<div class="portfolio-hover"><div class="portfolio-hover-inner">';
					$itemsHtml .= '
					<a class="portfolio-permalink" href="' . esc_url( $item['url'] ) . '" ' . ( ( $atts['target'] == 'blank' ) ? 'target="_blank"' : '' ) . '>
						<i class="icon-Link"></i>
					</a>';
					$image_link = empty( $item['image_link'] ) ? $item['full'] : $item['image_link'];
					if ( $image_link ) {
						$itemsHtml .= '<a class="portfolio-zoomlink" href="' . esc_url( $image_link ) . '" ><i class="icon-Loop"></i></a>';
					}
					$itemsHtml .= '</div></div>';
					break;

				case 3: // Zoom image on hover
					// ... empty
					break;

				default: // 4 // Just link
					$itemsHtml .= '<div class="portfolio-hover"><div class="portfolio-hover-inner">';
					$itemsHtml .= '
					<a class="portfolio-permalink" href="' . esc_url( $item['url'] ) . '" ' . ( ( $atts['target'] == 'blank' ) ? 'target="_blank"' : '' ) . '>
						<i class="icon-Link"></i>
					</a>';
					$itemsHtml .= '</div></div>';
					break;

				case 5: // Shuffle text link
					$itemsHtml .= '<div class="portfolio-hover"><div class="portfolio-hover-inner">';
					$itemsHtml .= '
					<a class="portfolio-shuffle-link"
					href="' . esc_url( $item['url'] ) . '" ' . ( ( $atts['target'] == 'blank' ) ? 'target="_blank"' : '' ) . '>
						<span class="chaffle" data-lang="en">' . $atts['shuffle_text'] . '</span>
					</a>';
					$itemsHtml .= '</div></div>';
					break;
			}

			return $itemsHtml;
		}

		/**
		 * Image tags template
		 *
		 * @param $item
		 *
		 * @return string
		 */
		protected function generate_item_tags( $item, $type ) {
			if ( ! $type ) {
				return '';
			}

			if ( ! $item['tag'] ) {
				return '';
			}

			$tags      = array();
			$itemsHtml = '';

			foreach ( $item['tag'] as $tag ) {
				$t_id      = (int) $tag->term_id;
				$term_link = get_term_link( $t_id, $tag->taxonomy );

				if ( 'text' == $type ) {
					$tags[] = '<div class="crane-portfolio-tag-item"><a href="' . esc_url( $term_link ) . '">' . esc_html( $tag->name ) . '</a></div>';
				} elseif ( 'image' == $type ) {
					$term_meta = maybe_unserialize( get_term_meta( $t_id, 'crane_term_additional_meta', true ) );
					$image_id  = isset( $term_meta['imgtag'] ) ? $term_meta['imgtag'] : '';

					if ( $image_id ) {
						$image_html = wp_get_attachment_image( $image_id, 'thumbnail', false, [
							'title' => $tag->name,
							'alt'   => $tag->slug
						] );
					} else {
						$image_html = '';
					}

					$tags[] = '<div class="crane-portfolio-tag-item' . crane_get_placeholder_html_class( $image_html ) . '"><a href="' . esc_url( $term_link ) . '">' .
					          $image_html .
					          '</a></div>';

				}
			}

			if ( 'text' == $type ) {
				$tags = array( implode( ', ', $tags ) );
			}

			if ( ! empty( $tags ) ) {
				$itemsHtml = '<div class="crane-portfolio-tag-list crane-portfolio-tag-list-type-' . esc_attr( $type ) . '">';
				foreach ( $tags as $tag ) {
					$itemsHtml .= $tag;
				}
				$itemsHtml .= '</div>';
			}

			return $itemsHtml;
		}

		/**
		 * Get image size
		 *
		 * @param $thumb_size
		 * @param $atts
		 *
		 * @return array
		 */
		protected function get_image_size_from_basic_resolution( $thumb_size, $atts ) {

			$basic_resolution = isset( $atts['image_resolution'] ) ? $atts['image_resolution'] : 'crane-portfolio-300';

			if ( is_numeric( $basic_resolution ) ) {
				$res = intval( $basic_resolution );
				if ( $res <= 300 ) {
					$basic_resolution = 'crane-portfolio-300';
				} elseif ( $res > 300 && $res <= 600 ) {
					$basic_resolution = 'crane-portfolio-600';
				} elseif ( $res > 600 && $res <= 900 ) {
					$basic_resolution = 'crane-portfolio-900';
				} else {
					$basic_resolution = 'crane-portfolio-900';
				}
			}

			$width = self::get_image_width( $basic_resolution );
			if ( ! $width ) {
				$width = 300;
			}
			$height = self::get_image_height( $basic_resolution );
			if ( ! $height ) {
				$height = 300;
			}


			$return_size = $basic_resolution;


			if ( 'masonry' == $atts['layout'] ) {
				if ( preg_match( '!portfolio-width([0-9])-height([0-9])!', $thumb_size, $result ) ) {
					if ( $result[1] > 1 || $result[2] > 1 ) {

						$aspect2 = 600;
						if ( $width <= 300 ) {
							$aspect2 = 'crane-portfolio-600';
						} elseif ( $width > 300 && $width <= 600 ) {
							$aspect2 = 'crane-portfolio-900';
						} elseif ( $width > 600 ) {
							$aspect2 = 'full';
						}

						$return_size = $aspect2;

					}
				}
			}

			return $return_size;
		}

		/**
		 * Front-end render (wrapper)
		 *
		 * @param $atts
		 *
		 * @return string
		 */
		protected function render_items( $atts ) {
			$atts['exclude_posts_ids'] = [ ];

			$exclude = isset( $_REQUEST['existItems'] ) ? explode( ',', esc_attr( wp_unslash( $_REQUEST['existItems'] ) ) ) : null;

			if ( $exclude && count( $exclude ) > 0 ) {
				foreach ( $exclude as $exclude_post_id ) {
					$exclude_post_id = intval( $exclude_post_id );
					if ( $exclude_post_id ) {
						$atts['exclude_posts_ids'][] = $exclude_post_id;
					}
				}
			}

			$itemsHtml = '';

			// Get portfolio items
			$items = $this->get_portfolio_data_by_param( $atts );

			if ( ! isset( $items['error'] ) ) {
				foreach ( $items as $item ) {
					$itemsHtml .= $this->generate_items_layout( $atts, $item );
				}
			}

			if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
				return $itemsHtml;
			}

			$output = '';
			if ( $itemsHtml ) {
				$output .= '<div class="crane-portfolio-grid-wrapper"><div class="crane-portfolio-grid">';
				$output .= $itemsHtml;
				$output .= '</div></div>';
			}

			return $output;
		}


		/**
		 * Items template
		 *
		 * @param $atts
		 * @param $item
		 *
		 * @return string
		 */
		protected function generate_items_layout( $atts, $item ) {
			if ( grooni_is_lazyload_enabled() ) {
				$image_src_attr = ( $item['thumb'] ? ' data-bg="' . esc_url( $item['thumb'] ) . '"' : '' );
				$image_class_attr = 'class="crane-portfolio-grid-item-placeholder lazyload' . crane_get_placeholder_html_class( $item['thumb'] ) . '" ';
			} else {
				$image_src_attr = ( $item['thumb'] ? ' style="background-image: url(\'' . esc_url( $item['thumb'] ) . '\');"' : '' );
				$image_class_attr = 'class="crane-portfolio-grid-item-placeholder' . crane_get_placeholder_html_class( $item['thumb'] ) . '" ';
			}

			$itemsHtml = '';
			$itemsHtml .= '<div class="crane-portfolio-grid-item ' . esc_attr( $this->get_items_category_classes( $atts, $item ) ) . '" data-id="' . esc_attr( $item['id'] ) . '">';
			$itemsHtml .= '<div class="crane-portfolio-grid-item-wrapper">';

			$itemsHtml .= '<div class="crane-portfolio-grid-item-placeholder-wrapper">';
			$itemsHtml .= '<div ' . $image_class_attr . $image_src_attr . '>';
			$itemsHtml .= $this->generate_item_image( $item ); // Changed to inline-css a line higher
			$itemsHtml .= $this->generate_hover_style( $atts, $item );
			$itemsHtml .= '</div>';
			$itemsHtml .= '</div>';

			$itemsHtml .= $this->generate_content_wrapper( $atts, $item );

			$itemsHtml .= '</div>';
			$itemsHtml .= '</div>';

			return $itemsHtml;
		}


	}

	new CT_Vc_Portfolio_Widget();

}
