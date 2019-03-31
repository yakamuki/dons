<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );
/**
 * Shortcode config Class.
 *
 * @package Grooni_Theme_Addons
 */


if ( ! class_exists( 'CT_Vc_RecentPosts_Config' ) ) {

	class CT_Vc_RecentPosts_Config {

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
					return 'ct_vc_recent_posts';
					break;
				case 'name' :
					return esc_html__( 'Recent posts', 'grooni-theme-addons' );
					break;
				case 'description' :
					return esc_html__( 'Grooni recent posts widget', 'grooni-theme-addons' );
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
					'value'       => esc_html__( 'Recent posts', 'grooni-theme-addons' ),
					'save_always' => false,
					'admin_label' => false,
				),
				array(
					'type'        => 'grooni-number',
					'heading'     => esc_html__( 'Number of posts to show', 'grooni-theme-addons' ),
					'param_name'  => 'number',
					'min'         => 1,
					'max'         => 100,
					'std'         => 5,
					'step'        => 1,
					'admin_label' => false,
					'save_always' => true,
				),
				array(
					'type'        => 'checkbox',
					'heading'     => esc_html__( 'Display post date?', 'grooni-theme-addons' ),
					'description' => '',
					'param_name'  => 'show_date',
					'admin_label' => false,
					'std'         => false,
					'save_always' => false,
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
