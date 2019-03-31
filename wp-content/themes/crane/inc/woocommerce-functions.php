<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );
/**
 * Helper functions for Woocommerce support.
 *
 * @package crane
 */


add_action( 'after_setup_theme', 'crane_add_woocommerce_support' );

/**
 * Add main support things
 */
function crane_add_woocommerce_support() {
	add_theme_support( 'woocommerce' );
	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );
}

add_action( 'after_setup_theme', 'crane_vc_init_vendor_woocommerce', 999 );

/**
 * Remove default woocommerce scripts
 */
function crane_vc_init_vendor_woocommerce() {
	remove_action( 'wp_enqueue_scripts', 'vc_woocommerce_add_to_cart_script' );
}

add_action( 'wp_loaded', 'crane_woo_actions_and_filters' );
if ( ! function_exists( 'crane_woo_actions_and_filters' ) ) {

	/**
	 * Remove other default woocommerce scripts
	 */
	function crane_woo_actions_and_filters() {
		global $crane_options;

		// Disable default woocommerce styles
		add_filter( 'woocommerce_enqueue_styles', '__return_false', 999 );

		remove_action( 'woocommerce_before_shop_loop_item', 'woocommerce_template_loop_product_link_open' );
		remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_product_link_close', 5 );

		// Disable standard woocommerce archive description
		remove_action( 'woocommerce_archive_description', 'woocommerce_product_archive_description', 10 );

		if ( isset( $crane_options['shop-show-product-filter'] ) && ! $crane_options['shop-show-product-filter'] ) {
			// Disable standard woocommerce catalog ordering
			remove_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30 );
		}

	}
}

add_action( 'wp', 'crane_woo_is_catalog' );
if ( ! function_exists( 'crane_woo_is_catalog' ) ) {

	/**
	 * Function for set woocommerce in catalog state (no cart or any "buy" button)
	 */
	function crane_woo_is_catalog() {
		global $crane_options;

		if ( isset( $crane_options['shop-is-catalog'] ) && $crane_options['shop-is-catalog'] ) {
			// Disable add to cart button
			remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );
			remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
			remove_action( 'woocommerce_simple_add_to_cart', 'woocommerce_simple_add_to_cart', 30 );
			remove_action( 'woocommerce_grouped_add_to_cart', 'woocommerce_grouped_add_to_cart', 30 );

			if ( crane_is_additional_woocommerce_page() ) {
				if ( get_option( 'woocommerce_shop_page_id' ) ) {
					wp_redirect( esc_url( get_permalink( get_option( 'woocommerce_shop_page_id' ) ) ) );
					exit();
				} else {
					global $wp_query;
					$wp_query->set_404();
					status_header( 404 );
					get_template_part( 404 );
					exit();
				}
			}
		}
	}
}


add_action( 'woocommerce_product_query', 'crane_product_category_override', 1 );
if ( ! function_exists( 'crane_product_category_override' ) ) {

	/**
	 * Override frontend options with product_category meta options
	 */
	function crane_product_category_override() {
		global $crane_options;
		$current_cat = get_queried_object();
		$term_id     = isset( $current_cat->term_id ) ? $current_cat->term_id : null;

		$category_options = $term_id ? crane_get_current_category_options( $term_id ) : null;

		if ( $category_options && isset( $category_options['custom_options'] ) && $category_options['custom_options'] === '1' ) {

			foreach ( $category_options as $opt_id => $opt_data ) {

				if ( isset( $crane_options[ $opt_id ] ) && 'default' !== $opt_data && '' !== $opt_data ) {

					switch ( $opt_id ) {

						case 'shop-show-product-attributes':
							if ( is_string( $opt_data ) && $selected_attr = explode( ',', $opt_data ) ) {
								if ( ! empty( $selected_attr ) && ! in_array( 'default', $selected_attr ) ) {
									$crane_options[ $opt_id ] = $selected_attr;
								}
							}
							break;

						default:
							$crane_options[ $opt_id ] = $opt_data;
							break;
					}
				}
			}
		}
	}
}


add_filter( 'woocommerce_product_get_rating_html', 'crane_shop_show_star_rating' );
if ( ! function_exists( 'crane_shop_show_star_rating' ) ) {

	/**
	 * Show or hide star rating
	 *
	 * @param $rating_html
	 *
	 * @return string
	 */
	function crane_shop_show_star_rating( $rating_html ) {
		global $crane_options;

		if ( isset( $crane_options['shop-show-star-rating'] ) && ! $crane_options['shop-show-star-rating'] ) {
			// Disable star rating
			return '';
		}

		return $rating_html;
	}
}


add_filter( 'woocommerce_add_to_cart_fragments', 'crane_woocommerce_add_to_cart_fragments' );
if ( ! function_exists( 'crane_woocommerce_add_to_cart_fragments' ) ) {

	/**
	 * Mini cart fix
	 *
	 * @param $fragments
	 *
	 * @return mixed
	 */
	function crane_woocommerce_add_to_cart_fragments( $fragments ) {
		ob_start();

		global $woocommerce;
		$count = $woocommerce->cart->cart_contents_count;

		?> <span id="cart-counter"><?php echo esc_html( $count ); ?></span> <?php

		$fragments['#cart-counter'] = ob_get_clean();

		return $fragments;
	}
}


if ( ! function_exists( 'crane_shop_show_description_excerpt' ) ) {

	/**
	 * Show or hide product excerpt
	 *
	 * @return null
	 */
	function crane_shop_show_description_excerpt() {
		global $crane_options;

		if ( empty( $crane_options['shop-show-description-excerpt'] ) ) {
			// Disable show product desc excerpt
			return null;
		}

		global $post;

		if ( ! $post->post_excerpt ) {
			return null;
		}

		echo '<div class="description">' . wp_kses_post( $post->post_excerpt ) . '</div>';
	}
}


if ( ! function_exists( 'crane_shop_show_cat_tag' ) ) {
	/**
	 * Show or hide product categories / tags
	 *
	 * @return null
	 */
	function crane_shop_show_cat_tag() {
		global $crane_options;
		global $post, $product, $woocommerce;;

		$id             = isset( $post->ID ) ? $post->ID : null;
		$output_escaped = '';

		if ( ! $id ) {
			return null;
		}

		$cat_all = get_the_terms( $post->ID, 'product_cat' );
		$tag_all = get_the_terms( $post->ID, 'product_tag' );

		$cat_count = $cat_all ? sizeof( $cat_all ) : 0;
		$tag_count = $tag_all ? sizeof( $tag_all ) : 0;

		if ( $cat_count && isset( $crane_options['shop-show-product-categories'] ) && $crane_options['shop-show-product-categories'] ) {
			if ( version_compare( $woocommerce->version, '3', '<' ) ) {
				$output_escaped .= $product->get_categories( ', ', '<p><span class="posted_in">' . _n( 'Category:', 'Categories:', $cat_count, 'crane' ) . '</span> ', '</p>' );
			} else {
				$output_escaped .= wc_get_product_category_list( $post->ID, ', ', '<p><span class="posted_in">' . _n( 'Category:', 'Categories:', $cat_count, 'crane' ) . '</span> ', '</p>' );
			}
		}

		if ( $tag_count && isset( $crane_options['shop-show-product-tags'] ) && $crane_options['shop-show-product-tags'] ) {
			if ( version_compare( $woocommerce->version, '3', '<' ) ) {
				$output_escaped .= $product->get_tags( ', ', '<p><span class="tagged_as">' . _n( 'Tag:', 'Tags:', $tag_count, 'crane' ) . '</span> ', '</p>' );
			} else {
				$output_escaped .= wc_get_product_tag_list( $post->ID, ', ', '<p><span class="tagged_as">' . _n( 'Tag:', 'Tags:', $tag_count, 'crane' ) . '</span> ', '</p>' );
			}
		}

		echo crane_clear_echo( $output_escaped );
	}
}


/**
 * Get the product thumbnail for the loop.
 * (Override default woocommerce function)
 */
function woocommerce_get_product_thumbnail_lazyload( $size = 'shop_catalog' ) {
	global $post;

	$dimensions = wc_get_image_size( $size );
	$thumb_size = apply_filters( 'single_product_archive_thumbnail_size', $size );
	$thumb_id = get_post_thumbnail_id( $post->ID );
	$thumb_url = wp_get_attachment_image_src( $thumb_id, $thumb_size );

	if ( crane_is_lazyload_enabled() ) {
		$image_src_attr = 'data-src="' . $thumb_url[0] . '"';
		$image_class_attr = 'class="attachment-shop_catalog size-shop_catalog wp-post-image lazyload"';
	} else {
		$image_src_attr = 'src="' . $thumb_url[0] . '"';
		$image_class_attr = 'class="attachment-shop_catalog size-shop_catalog wp-post-image"';
	}

	$thumb = '<img ' . $image_src_attr . $image_class_attr . ' alt="' . $post->post_title . '" width="' . $dimensions['width'] . '" height="' . $dimensions['height'] . '">';
	$placeholder = '<img src="'. wc_placeholder_img_src() . '" class="woocommerce-placeholder wp-post-image" alt="Placeholder" width="' . $dimensions['width'] . '" height="' . $dimensions['height'] . '">';

	return $thumb_url ? $thumb : $placeholder;
}

function woocommerce_template_loop_product_thumbnail() {
	global $crane_options;

	if ( crane_is_product_woocommerce_page() ) {
		echo woocommerce_get_product_thumbnail();

		return null;
	}

	if ( isset( $crane_options['shop-show-image-type'] ) && 'carousel' !== $crane_options['shop-show-image-type'] ) {
		echo woocommerce_get_product_thumbnail_lazyload();

		return null;
	}

	global $product;
	if ( ! $product ) {
		return null;
	}

	$attachments = $product->get_gallery_image_ids();

	if ( count( $attachments ) < 1 ) {
		echo woocommerce_get_product_thumbnail();
	}
}

if ( ! function_exists( 'crane_shop_show_image_carousel' ) ) {

	/**
	 * Show or hide carousel images
	 *
	 * @return bool|null
	 */
	function crane_shop_show_image_carousel() {
		global $crane_options;

		if ( isset( $crane_options['shop-show-image-type'] ) && 'carousel' !== $crane_options['shop-show-image-type'] ) {
			return null;
		}

		global $product;
		if ( ! $product ) {
			return null;
		}

		$attachments     = $product->get_gallery_image_ids();
		$attachments_img = count( $attachments );

		if ( $attachments_img ) {
			$attachments_img = array( $product->get_image( 'shop_catalog' ) );
			foreach ( $attachments as $attach ) {
				$attachments_img[] = wp_get_attachment_image(
					$attach,
					'shop_catalog',
					false,
					array(
						'class' => 'crane-product-carousel-item',
						'title' => esc_attr( $product->get_title() ),
					)
				);
			}
		}

		if ( ! empty( $attachments_img ) ) {
			echo '
			<div class="crane-product-carousel-wrapper">
			  <div class="crane-product-carousel">
			    ' . implode( $attachments_img ) . '
			  </div>
			  <span class="crane-product-carousel-close"></span>
			</div>';
		}

		return false;
	}
}

if ( ! function_exists( 'crane_shop_show_product_attributes' ) ) {

	/**
	 * Show or hide product attributes
	 *
	 * @return null
	 */
	function crane_shop_show_product_attributes() {
		global $crane_options;

		// Disable show product desc excerpt
		if ( empty( $crane_options['shop-show-product-attributes'] ) ) {
			return null;
		}

		if ( is_string( $crane_options['shop-show-product-attributes'] ) ) {
			$crane_options['shop-show-product-attributes'] = array( $crane_options['shop-show-product-attributes'] );
		}

		global $product;

		$crane_wc_terms = crane_wc_get_attribute_taxonomies();


		$output = '';

		foreach ( $crane_options['shop-show-product-attributes'] as $attribute ) {
			if ( ! is_string( $attribute ) ) {
				continue;
			}

			if ( ! isset( $crane_wc_terms[ $attribute ] ) ) {
				continue;
			}

			$product_terms = get_the_terms( get_the_ID(), $attribute );

			$term_list = array();

			if ( ! empty( $product_terms ) && ! is_wp_error( $product_terms ) ) {
				foreach ( $product_terms as $term ) {
					if (
						is_object( $term ) &&
						isset( $term->name ) &&
						isset( $term->taxonomy ) &&
						array_key_exists( $term->taxonomy, $crane_wc_terms )
					) {
						$term_list[ $term->taxonomy ][] = $term->name;
					}
				}

				if ( ! empty( $term_list ) ) {
					foreach ( $term_list as $term_name => $terms ) {
						$output .= '<li><strong>' . $crane_wc_terms[ $term_name ] . ':</strong> ' . implode( ', ', $terms ) . '</li>';
					}
				}

			}
		}

		if ( ! empty( $output ) ) {
			echo '<ul class="product-attributes">' . $output . '</ul>';
		}

	}
}


if ( ! function_exists( 'crane_get_woo_shop_description' ) ) {
	/**
	 * Get shop description
	 *
	 * @param string $desc
	 *
	 * @return string
	 */
	function crane_get_woo_shop_description( $desc = '' ) {

		static $woo_shop_description = '';

		if ( ! empty( $desc ) ) {
			$woo_shop_description = $desc;
		}

		return $woo_shop_description;
	}
}


/**
 * Show a shop page description on product archives.
 * (Override default woocommerce function)
 */
function woocommerce_product_archive_description() {

	if ( ( is_shop() || is_product_taxonomy() ) || crane_is_shop_search() ) {

		if ( ! empty( crane_get_woo_shop_description() ) && is_string( crane_get_woo_shop_description() ) ) {
			// echo formatted html
			echo crane_get_woo_shop_description();
		}

	}

}


if ( ! function_exists( 'crane_get_product_archive_description' ) ) {

	/**
	 * Save to global var main shop page content
	 */
	function crane_get_product_archive_description() {
		if ( ! function_exists( 'is_shop' ) || ! function_exists( 'is_product_taxonomy' ) ) {
			return;
		}

		if ( ( is_shop() || is_product_taxonomy() ) || crane_is_shop_search() ) {

			$query = new WP_Query( array( 'page_id' => wc_get_page_id( 'shop' ), 'post_type' => 'any' ) );
			while ( $query->have_posts() ) {
				$query->the_post();
			}

			$description = get_the_content();

			if ( $description ) {
				crane_get_woo_shop_description( do_shortcode( $description ) );
			}
		}

		return;
	}
}
add_action( 'get_header', 'crane_get_product_archive_description', 1 );


if ( ! function_exists( 'crane_get_shop_sidebar' ) ) {

	/**
	 * Return sidebar for shop page
	 *
	 * @param string $position inspect two string 'at-left' and 'at-right'
	 * @param bool $check return true if sidebar must echo
	 *
	 * @return mixed string|bool
	 */
	function crane_get_shop_sidebar( $position, $check = false ) {

		$current_page_options = crane_get_options_for_current_page();

		$sidebar      = $current_page_options['has-sidebar'];
		$sidebar_name = $current_page_options['sidebar'];

		if ( $position !== $sidebar ) {
			return false;
		}

		if ( $sidebar ) {
			if ( $check ) {
				return true;
			} else {
				crane_generate_dynamic_sidebar( $sidebar_name, 'aside' );
			}
		}

		return false;
	}
}


/**
 * It determines whether the current page belongs to woocommerce
 * (cart and checkout are standard pages with shortcodes and which are also included)
 *
 * @return bool
 */
function crane_is_additional_woocommerce_page() {

	if ( function_exists( 'is_cart' ) && is_cart() ) {
		return true;
	}
	if ( function_exists( 'is_checkout' ) && is_checkout() ) {
		return true;
	}
	if ( function_exists( 'is_account_page' ) && is_account_page() ) {
		return true;
	}
	if ( function_exists( 'is_checkout_pay_page' ) && is_checkout_pay_page() ) {
		return true;
	}
	if ( function_exists( 'is_wc_endpoint_url' ) && is_wc_endpoint_url() ) {
		return true;
	}

	return false;
}


/**
 * Detect current page
 *
 * @return bool
 */
function crane_is_shop_and_category_woocommerce_page() {

	if (
		function_exists( 'is_woocommerce' ) &&
		function_exists( 'is_product' ) &&
		function_exists( 'is_shop' ) &&
		function_exists( 'is_product_tag' )
	) {
		if ( ! is_product() && ( is_woocommerce() || is_shop() || is_product_tag() ) ) {
			return true;
		}
	}

	return false;
}


/**
 * Detect current page
 *
 * @return bool
 */
function crane_is_product_woocommerce_page() {

	if ( function_exists( 'is_product' ) ) {
		if ( is_product() ) {
			return true;
		}
	}

	return false;
}


if ( ! function_exists( 'crane_woocommerce_loop_result_count' ) ) {

	/**
	 * @return array
	 */
	function crane_woocommerce_loop_result_count() {

		$counter = array();
		if ( function_exists( 'woocommerce_products_will_display' ) && woocommerce_products_will_display() ) {
			global $wp_query;

			$counter['paged']    = max( 1, $wp_query->get( 'paged' ) );
			$counter['per_page'] = $wp_query->get( 'posts_per_page' );
			$counter['total']    = $wp_query->found_posts;
			$counter['first']    = ( $counter['per_page'] * $counter['paged'] ) - $counter['per_page'] + 1;
			$counter['last']     = min( $counter['total'], $wp_query->get( 'posts_per_page' ) * $counter['paged'] );

			// Theme Check fix
			$total = $counter['total'];

			if ( $counter['total'] <= $counter['per_page'] || - 1 === $counter['per_page'] ) {
				$counter['text'] = sprintf( _n( 'Showing the single result', 'Showing all %d results', $total, 'crane' ), $counter['total'] );
			} else {
				$counter['text'] = sprintf( _nx( 'Showing the single result', 'Showing <span class="count">%1$d&ndash;%2$d</span> of %3$d results', $total, '%1$d = first; %2$d = last; %3$d = total', 'crane' ), $counter['first'], $counter['last'], $counter['total'] );
			}

		}

		return $counter;
	}
}


// Adjust wrapper for all woocommerce pages
remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
add_action( 'woocommerce_before_main_content', 'crane_woocommerce_before_content', 5 );
if ( ! function_exists( 'crane_woocommerce_before_content' ) ) {

	/**
	 * Front-end for product card
	 */
	function crane_woocommerce_before_content() {

		if (
			( function_exists( 'is_shop' ) && is_shop() )
			||
			( function_exists( 'is_product_taxonomy' ) && is_product_taxonomy() )
			||
			crane_is_shop_search()
		) {
			woocommerce_product_archive_description();
		}
		// fix for "Related products" & "Cross-sells"
		$columns_css_class = '';
		if (
			( is_single() && function_exists( 'is_product' ) && is_product() )
			||
			( function_exists( 'is_cart' ) && is_cart() )
		) {
			global $crane_options;
			if ( isset( $crane_options['shop-columns-related'] ) && $crane_options['shop-columns-related'] ) {
				$columns_css_class = ' columns-' . $crane_options['shop-columns-related'];
			}
		}

		woocommerce_breadcrumb();

		echo '<div class="crane-container' . esc_attr( $columns_css_class ) . '">';
		echo '<div class="crane-row-flex">';

		crane_get_shop_sidebar( 'at-left' );

		echo '<div class="crane-content-inner shop-inner">';
	}
}


remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );
add_action( 'woocommerce_after_main_content', 'crane_woocommerce_after_content', 20 );
if ( ! function_exists( 'crane_woocommerce_after_content' ) ) {

	/**$id
	 * Front-end for product card
	 */
	function crane_woocommerce_after_content() {
		echo '</div>';

		crane_get_shop_sidebar( 'at-right' );

		echo '</div>';
		echo '</div>';

		if ( is_single() && function_exists( 'is_product' ) && is_product() ) {
			get_template_part( 'template-parts/prev_next_links' );
		}

	}
}


// Adjust markup for product in list
add_action( 'woocommerce_before_shop_loop_item', 'crane_woocommerce_before_shop_loop_item', 20 );
if ( ! function_exists( 'crane_woocommerce_before_shop_loop_item' ) ) {

	/**
	 * Front-end for product card
	 */
	function crane_woocommerce_before_shop_loop_item() {
		global $crane_options;

		if ( ! isset( $crane_options['shop-columns'] ) ) {
			$crane_options['shop-design'] = 'simple';
		}

		switch ( $crane_options['shop-design'] ) {

			case 'simple':
				echo '<div class="product-inner style-basic">';
				break;

			case 'float':
				echo '<div class="product-inner style-float">';
				break;

			default:
				echo '<div class="product-inner style-basic">';
				break;
		}
		if ( ! crane_is_product_woocommerce_page() ) {
			crane_shop_show_image_carousel();
		}
		echo '<a href="' . get_the_permalink() . '" class="woocommerce-LoopProduct-link">';

	}
}


add_action( 'woocommerce_after_shop_loop_item', 'crane_woocommerce_after_shop_loop_item', 20 );
if ( ! function_exists( 'crane_woocommerce_after_shop_loop_item' ) ) {

	/**
	 * Front-end for product card
	 */
	function crane_woocommerce_after_shop_loop_item() {
		echo '</div>';
	}
}


add_action( 'woocommerce_before_shop_loop_item_title', 'crane_woocommerce_before_shop_loop_item_title', 20 );
if ( ! function_exists( 'crane_woocommerce_before_shop_loop_item_title' ) ) {

	/**
	 * Front-end for product card
	 */
	function crane_woocommerce_before_shop_loop_item_title() {
		echo '</a><div class="product-info">';
	}
}


add_action( 'woocommerce_after_shop_loop_item_title', 'crane_woocommerce_after_shop_loop_item_title', 20 );
if ( ! function_exists( 'crane_woocommerce_after_shop_loop_item_title' ) ) {

	/**
	 * Front-end for product card
	 */
	function crane_woocommerce_after_shop_loop_item_title() {
		crane_shop_show_cat_tag();
		crane_shop_show_description_excerpt();
		crane_shop_show_product_attributes();
		echo '</div>';
	}
}


if ( ! function_exists( 'woocommerce_template_loop_product_title' ) ) {

	/**
	 * Show the product title in the product loop. By default this is an H3.
	 */
	function woocommerce_template_loop_product_title() {
		echo '<h3><a href="' . get_the_permalink() . '" class="woocommerce-LoopProduct-link">';
		echo get_the_title();
		echo '</a></h3>';
	}
}


// Move cross sells bellow the shipping
remove_action( 'woocommerce_cart_collaterals', 'woocommerce_cross_sell_display' );
add_action( 'woocommerce_after_cart', 'woocommerce_cross_sell_display', 10 );


// Change location of badge "onsale"
//remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_sale_flash', 10 );
//add_action( 'woocommerce_product_thumbnails', 'woocommerce_show_product_sale_flash', 10 );


add_filter( 'woocommerce_placeholder_img_src', function () {
	return get_template_directory_uri() . '/assets/images/wp/shop_cap.png';
} );


add_filter( 'body_class', 'crane_wc_body_class' );
if ( ! function_exists( 'crane_wc_body_class' ) ) {

	/**
	 * Add proper body class
	 *
	 * @param $classes
	 *
	 * @return array
	 */
	function crane_wc_body_class( $classes ) {
		global $crane_options;

		if ( ! isset( $crane_options['shop-columns'] ) ) {
			$crane_options['shop-columns'] = '3';
		}

		if ( function_exists( 'is_shop' ) && ( is_shop() || is_product_tag() || ( is_woocommerce() && is_product_category() ) ) ) {
			$classes[] = 'columns-' . esc_attr( $crane_options['shop-columns'] );
		}

		return $classes;
	}
}


add_filter( 'loop_shop_per_page', 'crane_woocommerce_product_query' );
if ( ! function_exists( 'crane_woocommerce_product_query' ) ) {

	/**
	 * Show product per page count
	 *
	 */
	function crane_woocommerce_product_query() {

		global $crane_options;

		$products_per_page_number = 12;
		if ( isset( $crane_options['shop-per-page'] ) && ! empty( $crane_options['shop-per-page'] ) ) {
			$products_per_page_number = $crane_options['shop-per-page'];
		}

		return $products_per_page_number;

	}
}


remove_action( 'woocommerce_pagination', 'woocommerce_pagination', 10 );
add_action( 'woocommerce_pagination', 'woocommerce_pagination', 10 );
if ( ! function_exists( 'woocommerce_pagination' ) ) {

	/**
	 * Woocommerce pagination.
	 * (Override default woocommerce function)
	 */
	function woocommerce_pagination() {
		crane_the_posts_pagination( 'shop' );
	}
}


if ( ! function_exists( 'crane_woocommerce_comment_pagination_args' ) ) {
	/**
	 * Woocommerce pagination.
	 * (Override default woocommerce comment args)
	 */
	function crane_woocommerce_comment_pagination_args( $args ) {
		global $crane_options;

		$prev_next_type = isset( $crane_options['shop-pagination-prev_next-type'] ) ? $crane_options['shop-pagination-prev_next-type'] : 'text';

		if ( $prev_next_type === 'arrows' ) {
			$prev_text = '<i class="fa fa-chevron-left"></i>';
			$next_text = '<i class="fa fa-chevron-right"></i>';
		} else {
			$prev_text = esc_html__( 'Previous page', 'crane' );
			$next_text = esc_html__( 'Next page', 'crane' );
		}


		$args['prev_text'] = $prev_text;
		$args['next_text'] = $next_text;

		$args['type']     = 'plain';
		$args['end_size'] = 1;
		$args['mid_size'] = 2;

		$args['before_page_number'] = '';
		$args['after_page_number']  = '';

		return $args;

	}
}
add_filter( 'woocommerce_comment_pagination_args', 'crane_woocommerce_comment_pagination_args' );



remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20 );
if ( ! function_exists( 'woocommerce_breadcrumb' ) ) {

	/**
	 * Output the WooCommerce Breadcrumb.
	 * (Override default woocommerce function)
	 *
	 * @param array $args
	 */
	function woocommerce_breadcrumb( $args = array() ) {
		$current_page_options = crane_get_options_for_current_page();

		crane_breadcrumbs( esc_attr( $current_page_options['breadcrumbs'] ) );
	}
}


if ( ! function_exists( 'woocommerce_output_related_products' ) ) {
	/**
	 * Output the related products.
	 * (Override default woocommerce function)
	 *
	 */
	function woocommerce_output_related_products() {
		global $crane_options;
		$Crane_Meta_Data = crane_get_meta_data();

		$meta_related = $Crane_Meta_Data->get( 'product-related', get_the_ID() );

		if ( 'default' === $meta_related ) {
			if ( isset( $crane_options['shop-show-related'] ) && $crane_options['shop-show-related'] ) {
				$show_related = true;
			} else {
				$show_related = false;
			}
		} else {
			$show_related = $meta_related;
		}

		if ( ! isset( $crane_options['shop-columns-related'] ) ) {
			$crane_options['shop-columns-related'] = 4;
		}
		if ( ! isset( $crane_options['shop-rows-related'] ) ) {
			$crane_options['shop-rows-related'] = 2;
		}

		if ( $show_related ) {
			$args = array(
				'posts_per_page' => intval( $crane_options['shop-rows-related'] * $crane_options['shop-columns-related'] ),
				'columns'        => intval( $crane_options['shop-columns-related'] ),
				'orderby'        => 'rand'
			);
			woocommerce_related_products( apply_filters( 'woocommerce_output_related_products_args', $args ) );
		}
	}
}

if ( ! function_exists( 'crane_wc_add_to_cart_variable_callback' ) ) {

	/**
	 * Add to cart variable product
	 */
	function crane_wc_add_to_cart_variable_callback() {

		ob_start();

		$product_id        = empty( $_POST['product_id'] ) ? null : apply_filters( 'woocommerce_add_to_cart_product_id', absint( wp_unslash( $_POST['product_id'] ) ) );
		$quantity          = empty( $_POST['quantity'] ) ? 1 : apply_filters( 'woocommerce_stock_amount', absint( wp_unslash( $_POST['quantity'] ) ) );
		$variation_id      = isset( $_POST['variation_id'] ) ? absint( wp_unslash( $_POST['variation_id'] ) ) : null;
		$variation         = isset( $_POST['variation'] ) ? wp_unslash( $_POST['variation'] ): null;
		$passed_validation = apply_filters( 'woocommerce_add_to_cart_validation', true, $product_id, $quantity );

		if ( $passed_validation && WC()->cart->add_to_cart( $product_id, $quantity, $variation_id, $variation ) ) {
			do_action( 'woocommerce_ajax_added_to_cart', $product_id );
			if ( get_option( 'woocommerce_cart_redirect_after_add' ) === 'yes' ) {
				wc_add_to_cart_message( $product_id );
			}

			// Return fragments
			WC_AJAX::get_refreshed_fragments();
		} else {
			// If there was an error adding to the cart, redirect to the product page to show any errors
			$data = array(
				'error'       => true,
				'product_url' => apply_filters( 'woocommerce_cart_redirect_after_error', esc_url( get_permalink( $product_id ) ), $product_id )
			);
			echo json_encode( $data );
		}
		die();
	}
}
if ( function_exists( 'wp_doing_ajax' ) && wp_doing_ajax() ) {
	add_action( 'wp_ajax_woocommerce_add_to_cart_variable_rc', 'crane_wc_add_to_cart_variable_callback' );
	add_action( 'wp_ajax_nopriv_woocommerce_add_to_cart_variable_rc', 'crane_wc_add_to_cart_variable_callback' );
}
