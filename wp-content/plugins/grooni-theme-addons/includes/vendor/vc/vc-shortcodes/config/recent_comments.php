<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );
/**
 * Shortcode config Class.
 *
 * @package Grooni_Theme_Addons
 */


if ( ! class_exists( 'CT_Vc_RecentComments_Config' ) ) {

	class CT_Vc_RecentComments_Config {

		function __construct() {
		}

		/**
		 * @param $data_name
		 *
		 * @return string
		 */
		public static function get_data( $data_name ) {
			switch ( $data_name ) {
				case 'tag' :
					return 'ct_vc_recent_comments';
					break;
				case 'name' :
					return esc_html__( 'Recent comments', 'grooni-theme-addons' );
					break;
				case 'description' :
					return esc_html__( 'Grooni recent comments widget', 'grooni-theme-addons' );
					break;
			}

			return '';
		}

		/**
		 * @return array
		 */
		public static function fields() {

			return array(
				array(
					'type'        => 'textfield',
					'class'       => '',
					'heading'     => esc_html__( 'Title', 'grooni-theme-addons' ),
					'description' => '',
					'param_name'  => 'title',
					'value'       => esc_html__( 'Recent comments', 'grooni-theme-addons' ),
					'save_always' => false,
					'admin_label' => false,
				),
				array(
					'type'        => 'grooni-number',
					'heading'     => esc_html__( 'Number of comments to show', 'grooni-theme-addons' ),
					'param_name'  => 'number',
					'min'         => 1,
					'max'         => 100,
					'std'         => 5,
					'step'        => 1,
					'admin_label' => false,
					'save_always' => true,
				),
				array(
					'type'        => 'grooni-number',
					'heading'     => esc_html__( 'Excerpt height', 'grooni-theme-addons' ),
					'param_name'  => 'excerpt_height',
					'min'         => 1,
					'max'         => 500,
					'std'         => 80,
					'step'        => 1,
					'admin_label' => false,
					'save_always' => true,
				),
				array(
					'type'        => 'dropdown',
					'heading'     => esc_html__( 'Show comments from', 'grooni-theme-addons' ),
					'description' => '',
					'param_name'  => 'post_type',
					'value'       => array(
						esc_html__( 'Blog', 'grooni-theme-addons' )                => 'post',
						esc_html__( 'Pages', 'grooni-theme-addons' )               => 'page',
						esc_html__( 'Portfolio', 'grooni-theme-addons' )           => 'crane_portfolio',
						esc_html__( 'Woocommerce product', 'grooni-theme-addons' ) => 'product',
						esc_html__( 'Any post types', 'grooni-theme-addons' )      => 'any',
					),
					'std'         => 'post',
					'save_always' => false,
					'admin_label' => true
				),

			);
		}


		public static function as_parent() {
			return null;
		}

		public static function content_element() {
			return true;
		}

		public static function icon() {
			return null;
		}

	}

}
