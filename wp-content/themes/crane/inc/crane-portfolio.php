<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );
/**
 * Crane theme Portfolio custom post type stuff.
 *
 * @package crane
 */


if ( ! function_exists( 'crane_flush_permalinks' ) ) {
	/**
	 * Flush links stack ( friendly URL )
	 */
	function crane_flush_permalinks() {
		if ( get_transient( 'crane_need_flush_rewrite_rules' ) ) {
			flush_rewrite_rules();
			delete_transient( 'crane_need_flush_rewrite_rules' );
		}
	}
}

add_action( 'init', 'crane_flush_permalinks', 100 );

if ( ! function_exists( 'crane_after_crane_options_save' ) ) {
	/**
	 * Helper for for detect if we need crane_flush_permalinks()
	 */
	function crane_after_crane_options_save() {

		global $crane_options;

		$old_slugs = get_option( 'crane_rewrite_rules_slugs' );
		$new_slugs  = array();
		foreach ( [ 'blog', 'portfolio' ] as $type ) {
			$new_slugs[ $type . '-slug' ]      = isset( $crane_options[ $type . '-slug' ] ) ? esc_attr( $crane_options[ $type . '-slug' ] ) : '';
			$new_slugs[ $type . '_cats-slug' ] = isset( $crane_options[ $type . '_cats-slug' ] ) ? esc_attr( $crane_options[ $type . '_cats-slug' ] ) : '';
			$new_slugs[ $type . '_tags-slug' ] = isset( $crane_options[ $type . '_tags-slug' ] ) ? esc_attr( $crane_options[ $type . '_tags-slug' ] ) : '';
		}

		$need_flush = false;

		if ( ! $old_slugs || empty( $old_slugs ) || ! is_array( $old_slugs ) ) {
			$need_flush = true;
		} else {
			foreach ( [ 'blog', 'portfolio' ] as $type ) {
				if ( 'blog' !== $type && $old_slugs[ $type . '-slug' ] !== $new_slugs[ $type . '-slug' ] ) {
					$need_flush = true;
					continue;
				}
				if ( isset( $old_slugs[ $type . '_cats-slug' ] ) && $old_slugs[ $type . '_cats-slug' ] !== $new_slugs[ $type . '_cats-slug' ] ) {
					$need_flush = true;
					continue;
				}
				if ( isset( $old_slugs[ $type . '_tags-slug' ] ) && $old_slugs[ $type . '_tags-slug' ] !== $new_slugs[ $type . '_tags-slug' ] ) {
					$need_flush = true;
					continue;
				}
			}
		}

		if ( get_option( 'category_base' ) !== $new_slugs['blog_cats-slug'] ) {
			$need_flush = true;
		}
		if ( get_option( 'tag_base' ) !== $new_slugs['blog_tags-slug'] ) {
			$need_flush = true;
		}

		if ( $need_flush ) {
			global $wp_rewrite;
			$wp_rewrite->set_category_base( $new_slugs['blog_cats-slug'] );
			$wp_rewrite->set_tag_base( $new_slugs['blog_tags-slug'] );
			update_option( 'crane_rewrite_rules_slugs', $new_slugs );
			set_transient( 'crane_need_flush_rewrite_rules', true, DAY_IN_SECONDS );
		}

	}
}

add_action( 'redux/options/crane_options/saved', 'crane_after_crane_options_save' );


if ( ! function_exists( 'crane_filter_restrict_manage_posts' ) ) {
	/**
	 * Filter taxonomy for admin post edit
	 */
	function crane_filter_restrict_manage_posts() {
		global $typenow;
		$args       = array( 'public' => true, '_builtin' => false );
		$post_types = get_post_types( $args );
		if ( in_array( $typenow, $post_types ) ) {
			$filters = get_object_taxonomies( $typenow );

			foreach ( $filters as $tax_slug ) {
				$tax_obj = get_taxonomy( $tax_slug );
				wp_dropdown_categories( array(
					'show_option_all' => esc_html__( 'All', 'crane' ) . ' ' . $tax_obj->label,
					'taxonomy'        => $tax_slug,
					'name'            => $tax_obj->name,
					'orderby'         => 'term_order',
					'selected'        => isset( $_GET[ $tax_obj->query_var ] ) ? esc_attr( wp_unslash( $_GET[ $tax_obj->query_var ] ) ) : '',
					'hierarchical'    => $tax_obj->hierarchical,
					'show_count'      => false,
					'hide_empty'      => true,
					'hide_if_empty'   => true
				) );

			}

		}
	}
}

if ( ! function_exists( 'crane_filter_convert_restrict' ) ) {
	/**
	 * Apply Filter taxonomy for admin edit.php
	 */
	function crane_filter_convert_restrict( $query ) {
		global $pagenow;
		global $typenow;
		if ( $pagenow === 'edit.php' ) {
			$filters = get_object_taxonomies( $typenow );
			foreach ( $filters as $tax_slug ) {
				$var = &$query->query_vars[ $tax_slug ];
				if ( ! empty( $var ) && $var ) {
					if ( $var === strval( intval( $var ) ) && intval( $var ) > 0 ) {
						$term = get_term_by( 'id', $var, $tax_slug );
					} else {
						$term = get_term_by( 'slug', $var, $tax_slug );
					}

					$var = $term->slug;
				}
			}
		}

		return $query;
	}
}
add_action( 'restrict_manage_posts', 'crane_filter_restrict_manage_posts' );
add_filter( 'parse_query', 'crane_filter_convert_restrict' );


if ( ! function_exists( 'crane_add_portfolio_type' ) ) {

	/**
	 * Write <body> class is current page portfolio post type
	 *
	 * @param $classes
	 *
	 * @return array
	 */
	function crane_add_portfolio_type( $classes ) {
		if ( is_single() && 'crane_portfolio' === get_post_type() ) {
			$Crane_Meta_Data = crane_get_meta_data();
			if ( $Crane_Meta_Data->get( 'portfolio-type', get_the_ID(), 'crane_portfolio' ) ) {
				$classes[] = 'single-portfolio-' . esc_attr( $Crane_Meta_Data->get( 'portfolio-type', get_the_ID(), 'crane_portfolio' ) );
			}
		}

		return $classes;
	}
}
add_filter( 'body_class', 'crane_add_portfolio_type' );


if ( ! function_exists( 'crane_portfolio_tags_add_image_column' ) ) {

	/**
	 * Add image column for portfolio tags
	 *
	 * @param $existing_columns
	 *
	 * @return array
	 */
	function crane_portfolio_tags_add_image_column( $existing_columns ) {
		if ( empty( $existing_columns ) && ! is_array( $existing_columns ) ) {
			$existing_columns = array();
		}

		$columns          = array();
		$columns['cb']    = isset( $existing_columns['cb'] ) ? $existing_columns['cb'] : '<input type="checkbox" />';
		$columns['thumb'] = '<span class="crane-portfolio-tags-image" data-tip="' . esc_attr__( 'Image', 'crane' ) . '">' . esc_html__( 'Image', 'crane' ) . '</span>';

		return array_merge( $columns, $existing_columns );
	}
}

if ( ! function_exists( 'crane_portfolio_tags_set_column_image' ) ) {

	/**
	 * Show portfolio tags image (admin area list)
	 *
	 * @param $content
	 * @param $column_name
	 * @param $post_id
	 */
	function crane_portfolio_tags_set_column_image( $content, $column_name, $post_id ) {
		$term_meta = maybe_unserialize( get_term_meta( $post_id, 'crane_term_additional_meta', true ) );
		$image_id  = isset( $term_meta['imgtag'] ) ? $term_meta['imgtag'] : '';

		if ( $image_id && $wp_image = wp_get_attachment_image( $image_id, array( 38, 38 ) ) ) {
			echo crane_clear_echo( $wp_image );
		} else {
			echo '<i>' . esc_html__( 'No Image Set.', 'crane' ) . '</i>';
		}
	}
}


if ( ! function_exists( 'crane_portfolio_add_custom_order_column' ) ) {

	/**
	 * Add [custom_order] column for portfolio
	 *
	 * @param $existing_columns
	 *
	 * @return array
	 */
	function crane_portfolio_add_custom_order_column( $existing_columns ) {
		if ( empty( $existing_columns ) && ! is_array( $existing_columns ) ) {
			$existing_columns = array();
		}

		$columns = array();

		foreach ( $existing_columns as $col_name => $col_val ) {
			$columns[ $col_name ] = $col_val;
			if ( 'taxonomy-crane_portfolio_tags' === $col_name ) {
				$columns[ 'grooni_custom_order' ] = esc_attr__( 'Custom order', 'crane' );
			}
		}

		return array_merge( $columns, $existing_columns );
	}
}

if ( ! function_exists( 'crane_portfolio_set_custom_order_value' ) ) {

	/**
	 * Show portfolio custom_order (admin area posts list)
	 *
	 * @param $column_name
	 * @param $post_id
	 */
	function crane_portfolio_set_custom_order_value( $column_name, $post_id ) {

		switch ( $column_name ) {

			case 'grooni_custom_order' :
				$meta_value = intval( get_post_meta( $post_id, 'grooni_custom_order', true ) );

				if ( $meta_value && $meta_value > 0 ) {
					echo esc_attr( $meta_value );
				} else {
					echo '';
				}
				break;
		}
	}

}

if ( ! function_exists( 'crane_add_portfolio_quickedit_fields' ) ) {

	/**
	 * Show portfolio custom_order in quickedit form
	 *
	 * @param $column_name
	 * @param $post_type
	 */
	function crane_add_portfolio_quickedit_fields( $column_name, $post_type ) {

		if ( 'crane_portfolio' != $post_type ) {
			return;
		}

		if ( 'grooni_custom_order' != $column_name ) {
			return;
		}

		?>
		<fieldset class="inline-edit-col-right">
			<div class="inline-edit-col">
				<label>
					<span class="title"><?php esc_attr_e( 'Custom order', 'crane' ); ?></span>
					<span class="input-text-wrap">
						<input type="number" name="crane-quickedit-grooni_custom_order" class="crane-grooni_custom_order__edit" value="" step="1" min="0" placeholder="<?php esc_html_e( 'Input number', 'crane' ); ?>">
					</span>
				</label>
			</div>
		</fieldset>
		<?php
	}
}

if ( ! function_exists( 'crane_quick_edit_crane_portfolio_save_post' ) ) {

	/**
	 * Save portfolio custom_order in quickedit form
	 *
	 * @param $post_id
	 * @param $post
	 */
	function crane_quick_edit_crane_portfolio_save_post( $post_id, $post ) {
		// prevent save if called by autosave
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! empty( $post->post_type ) && $post->post_type != 'crane_portfolio' ) {
			return;
		}

		// does this user have permissions?
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// update post meta
		if ( isset( $_POST['crane-quickedit-grooni_custom_order'] ) ) {

			$new_custom_order = intval( $_POST['crane-quickedit-grooni_custom_order'] );
			update_post_meta( $post_id, 'grooni_custom_order', $new_custom_order );

			$grooni_meta = json_decode( get_metadata( 'post', $post_id, 'grooni_meta', true ), true );
			if ( ! empty( $grooni_meta ) && is_array( $grooni_meta ) ) {
				$grooni_meta['grooni_custom_order'] = $new_custom_order;
				update_metadata( 'post', $post_id, 'grooni_meta', json_encode( $grooni_meta, JSON_UNESCAPED_UNICODE ) );
			}

		}
	}

}

if ( ! function_exists( 'crane_quick_edit_javascript' ) ) {
	/**
	 * Add JS for custom_order in quickedit form
	 */
	function crane_quick_edit_javascript() {
		$current_screen = get_current_screen();

		if ( $current_screen->id != 'edit-crane_portfolio' || $current_screen->post_type != 'crane_portfolio' ) {
			return;
		}

		$js = 'jQuery(function ($) {
				$("#the-list").on("click", "a.editinline", function (e) {
					e.preventDefault();
					var custom_order = $(this).data("grooni_custom_order");
					inlineEditPost.revert();
					$(".crane-grooni_custom_order__edit").val( custom_order ? custom_order : "" );
				});
			});';

		wp_add_inline_script( 'crane-admin-js', $js );

	}
}

if ( ! function_exists( 'crane_quick_edit_set_data' ) ) {

	/**
	 * Set portfolio custom_order in quickedit form
	 *
	 * @param $actions
	 * @param $post
	 */
	function crane_quick_edit_set_data( $actions, $post ) {

		$custom_order = intval( get_post_meta( $post->ID, 'grooni_custom_order', true ) );

		if ( $custom_order ) {
			if ( isset( $actions['inline hide-if-no-js'] ) ) {
				$new_attributes                  = sprintf( 'data-grooni_custom_order="%s"', esc_attr( $custom_order ) );
				$actions['inline hide-if-no-js'] = str_replace( 'class=', "$new_attributes class=", $actions['inline hide-if-no-js'] );
			}
		}

		return $actions;
	}
}


/**
 * Add portfolio tags image support
 */
function crane_portfolio_admin_init() {

	add_filter( 'manage_edit-crane_portfolio_tags_columns', 'crane_portfolio_tags_add_image_column' );
	add_action( 'manage_crane_portfolio_tags_custom_column', 'crane_portfolio_tags_set_column_image', 10, 3 );

	add_filter( 'manage_crane_portfolio_posts_columns', 'crane_portfolio_add_custom_order_column' );
	add_action( 'manage_crane_portfolio_posts_custom_column', 'crane_portfolio_set_custom_order_value', 10, 2 );
	add_action( 'quick_edit_custom_box', 'crane_add_portfolio_quickedit_fields', 10, 2 );
	add_action( 'save_post', 'crane_quick_edit_crane_portfolio_save_post', 10, 2 );
	add_action( 'admin_enqueue_scripts', 'crane_quick_edit_javascript', 99 );
	add_filter( 'post_row_actions', 'crane_quick_edit_set_data', 10, 2 );

}

add_action( 'admin_init', 'crane_portfolio_admin_init' );
