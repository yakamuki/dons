<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );
/**
 * Shortcode config Class.
 *
 * @package Grooni_Theme_Addons
 */


if ( ! class_exists( 'CT_Vc_PortfolioCategory_Config' ) ) {

	class CT_Vc_PortfolioCategory_Config {

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
					return 'ct_vc_portfolio_cat';
					break;
				case 'name' :
					return esc_html__( 'Portfolio categories', 'grooni-theme-addons' );
					break;
				case 'description' :
					return esc_html__( 'Grooni portfolio categories widget', 'grooni-theme-addons' );
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
					'value'       => esc_html__( 'Portfolio categories', 'grooni-theme-addons' ),
					'save_always' => false,
					'admin_label' => false,
				),
				array(
					'type'        => 'checkbox',
					'heading'     => esc_html__( 'Display as dropdown', 'grooni-theme-addons' ),
					'param_name'  => 'dropdown',
					'admin_label' => false,
					'std'         => false,
					'save_always' => true
				),
				array(
					'type'        => 'checkbox',
					'heading'     => esc_html__( 'Show post counts', 'grooni-theme-addons' ),
					'param_name'  => 'count',
					'admin_label' => false,
					'std'         => false,
					'save_always' => true
				),
				array(
					'type'        => 'checkbox',
					'heading'     => esc_html__( 'Show hierarchy', 'grooni-theme-addons' ),
					'param_name'  => 'hierarchical',
					'admin_label' => false,
					'std'         => false,
					'save_always' => true
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
