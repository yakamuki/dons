<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );
/**
 * Portfolio custom post type.
 *
 * @package Grooni_Theme_Addons
 */


if ( ! function_exists( 'crane_portfolio_add_post_type' ) ) {
	/**
	 * Register portfolio post type and necessary taxonomy
	 */
	function crane_portfolio_add_post_type() {

		$portfolio_slug      = 'portfolio';
		$portfolio_cats_slug = 'portfolio-category';
		$portfolio_tags_slug = 'portfolio-tag';

		$portfolio_slugs = get_option( 'crane_rewrite_rules_slugs' );
		if ( $portfolio_slugs && is_array( $portfolio_slugs ) ) {
			$portfolio_slug      = isset( $portfolio_slugs['portfolio-slug'] ) ? $portfolio_slugs['portfolio-slug'] : $portfolio_slug;
			$portfolio_cats_slug = isset( $portfolio_slugs['portfolio_cats-slug'] ) ? $portfolio_slugs['portfolio_cats-slug'] : $portfolio_cats_slug;
			$portfolio_tags_slug = isset( $portfolio_slugs['portfolio_tags-slug'] ) ? $portfolio_slugs['portfolio_tags-slug'] : $portfolio_tags_slug;
		}

		register_taxonomy( 'crane_portfolio_cats', 'crane_portfolio', array(
			// Hierarchical taxonomy (like categories)
			'hierarchical'      => true,
			'show_admin_column' => true,
			'show_in_nav_menus' => true,
			'show_in_menu'      => true,
			// This array of options controls the labels displayed in the WordPress Admin UI
			'labels'            => array(
				'name'              => esc_html_x( 'Portfolio Categories', 'taxonomy general name', 'grooni-theme-addons' ),
				'singular_name'     => esc_html_x( 'Portfolio-Category', 'taxonomy singular name', 'grooni-theme-addons' ),
				'search_items'      => esc_html__( 'Search Portfolio Categories', 'grooni-theme-addons' ),
				'all_items'         => esc_html__( 'All Portfolio Categories', 'grooni-theme-addons' ),
				'parent_item'       => esc_html__( 'Parent Portfolio Category', 'grooni-theme-addons' ),
				'parent_item_colon' => esc_html__( 'Parent Portfolio Category:', 'grooni-theme-addons' ),
				'edit_item'         => esc_html__( 'Edit Portfolio Category', 'grooni-theme-addons' ),
				'update_item'       => esc_html__( 'Update Portfolio Category', 'grooni-theme-addons' ),
				'add_new_item'      => esc_html__( 'Add New Portfolio Category', 'grooni-theme-addons' ),
				'new_item_name'     => esc_html__( 'New Portfolio-Category Name', 'grooni-theme-addons' ),
				'menu_name'         => esc_html__( 'Portfolio Categories', 'grooni-theme-addons' ),
			),
			// Control the slugs used for this taxonomy
			'rewrite'           => array(
				'slug'         => $portfolio_cats_slug,
				'with_front'   => true,
				'hierarchical' => false
			),
		) );

		register_taxonomy( 'crane_portfolio_tags', 'crane_portfolio', array(
			// non hierarchical taxonomy (like tags)
			'hierarchical'      => false,
			'show_admin_column' => true,
			'show_in_nav_menus' => true,
			'show_in_menu'      => true,
			// This array of options controls the labels displayed in the WordPress Admin UI
			'labels'            => array(
				'name'          => esc_html_x( 'Portfolio Tags', 'taxonomy general name', 'grooni-theme-addons' ),
				'singular_name' => esc_html_x( 'Portfolio-Tag', 'taxonomy singular name', 'grooni-theme-addons' ),
				'search_items'  => esc_html__( 'Search Portfolio Tags', 'grooni-theme-addons' ),
				'all_items'     => esc_html__( 'All Portfolio Tags', 'grooni-theme-addons' ),
				'edit_item'     => esc_html__( 'Edit Portfolio Tag', 'grooni-theme-addons' ),
				'update_item'   => esc_html__( 'Update Portfolio Tag', 'grooni-theme-addons' ),
				'add_new_item'  => esc_html__( 'Add New Portfolio Tag', 'grooni-theme-addons' ),
				'new_item_name' => esc_html__( 'New Portfolio-Tag Name', 'grooni-theme-addons' ),
				'menu_name'     => esc_html__( 'Portfolio Tags', 'grooni-theme-addons' ),
			),
			// Control the slugs used for this taxonomy
			'rewrite'           => array(
				'slug'         => $portfolio_tags_slug,
				'with_front'   => true,
				'hierarchical' => false
			),
		) );

		register_post_type( 'crane_portfolio',
			array(
				'labels'            => array(
					'name'          => esc_html__( 'Portfolio', 'grooni-theme-addons' ),
					'singular_name' => esc_html__( 'Portfolio', 'grooni-theme-addons' ),
					'add_new_item'  => esc_html__( 'Add New Portfolio', 'grooni-theme-addons' ),
					'edit_item'     => esc_html__( 'Edit Portfolio', 'grooni-theme-addons' ),
					'view_item'     => esc_html__( 'View Portfolio', 'grooni-theme-addons' )

				),
				'public'            => true,
				'has_archive'       => true,
				'show_in_nav_menus' => true,
				'show_in_menu'      => true,
				'taxonomies'        => array( 'crane_portfolio_cats', 'crane_portfolio_tags' ),
				'supports'          => array(
					'title',
					'editor',
					'thumbnail',
					'author',
					'excerpt',
					'comments',
					'custom-fields',
					'revisions'
				),
				'rewrite'           => array( 'slug' => $portfolio_slug, 'with_front' => false ),

			)
		);

	}
}

add_action( 'init', 'crane_portfolio_add_post_type', 8 );
