<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );
/**
 * Footer custom post type.
 *
 * @package Grooni_Theme_Addons
 */


if ( ! function_exists( 'grooni_footer_add_post_type' ) ) {
	/**
	 * Register footer post type
	 */
	function grooni_footer_add_post_type() {

		register_post_type( 'crane_footer', array(
				'labels'              => array(
					'name'          => __( 'Footers', 'grooni-theme-addons' ),
					'singular_name' => __( 'Footer', 'grooni-theme-addons' ),
					'add_new'       => __( 'Add New Footer', 'grooni-theme-addons' ),
					'add_new_item'  => __( 'Add New Footer', 'grooni-theme-addons' ),
					'edit_item'     => __( 'Edit Footer', 'grooni-theme-addons' ),
				),
				'public'              => true,
				'show_in_menu'        => current_user_can( 'administrator' ) ? 'crane-theme-dashboard' : false,
				'show_in_admin_bar'   => false,
				'show_in_nav_menus'   => false,
				'supports'            => array(
					'title',
					'editor',
					'revisions'
				),
				'publicly_queryable'  => true,
				'exclude_from_search' => true,
				'show_ui'             => true,
				'show_in_rest'        => false,
				'rest_base'           => null,
				'menu_position'       => 62,
				'menu_icon'           => null,
				'hierarchical'        => false,
				'taxonomies'          => array(),
				'has_archive'         => false,
				'rewrite'             => false,
				'query_var'           => true,
			)
		);

	}
}

add_action( 'init', 'grooni_footer_add_post_type' );



function grooni_footer_pages_redirect() {
	global $wp_query;

	if ( ! is_preview() && 'crane_footer' === get_post_type() ) {
		wp_redirect( esc_url_raw( home_url() ), 301 );
		exit();
	}
}

add_action( 'template_redirect', 'grooni_footer_pages_redirect', 1 );


function grooni_footer_remove_view_link( $messages ) {

	if ( 'crane_footer' === get_post_type() && is_array( $messages ) ) {

		foreach ( $messages as $post_type => $post_data ) {

			foreach ( $post_data as $key => $data ) {
				preg_match( '# ?<a(.+)crane_footer=(.+)<\/a>#im', $data, $matches );
				if ( ! empty( $matches[0] ) ) {
					$messages[ $post_type ][ $key ] = str_replace( $matches[0], '', $messages[ $post_type ][ $key ] );
				}
			}

		}

	}

	return $messages;

}

add_filter( 'post_updated_messages', 'grooni_footer_remove_view_link' );


function grooni_footer_remove_post_row_view_link( $actions, $post = 0 ) {
	if ( 'crane_footer' === get_post_type() ) {
		if ( isset( $actions['view'] ) ) {
			unset( $actions['view'] );
		}
	}

	return $actions;
}

add_filter( 'post_row_actions', 'grooni_footer_remove_post_row_view_link' );


function grooni_add_vc_custom_css() {
	if ( function_exists( 'crane_get_footer_data' ) ) {
		$footer_id = crane_get_footer_data( 'id' );
	}

	if ( empty( $footer_id ) ) {
		return;
	}

	if ( is_preview() && 'crane_footer' === get_post_type() ) {
		return;
	}

	$post_custom_css = get_post_meta( $footer_id, '_wpb_post_custom_css', true );
	if ( ! empty( $post_custom_css ) ) {
		$post_custom_css = strip_tags( $post_custom_css );
		echo '<style type="text/css" data-type="vc_custom-css">';
		echo $post_custom_css;
		echo '</style>';
	}

	$shortcodes_custom_css = get_post_meta( $footer_id, '_wpb_shortcodes_custom_css', true );
	if ( ! empty( $shortcodes_custom_css ) ) {
		$shortcodes_custom_css = strip_tags( $shortcodes_custom_css );
		echo '<style type="text/css" data-type="vc_shortcodes-custom-css">';
		echo $shortcodes_custom_css;
		echo '</style>';
	}

}

add_action( 'wp_footer', 'grooni_add_vc_custom_css' );
