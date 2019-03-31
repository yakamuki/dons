<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );
/**
 * Shortcode config Class.
 *
 * @package Grooni_Theme_Addons
 */


if ( ! class_exists( 'CT_Vc_Timeline_Config' ) ) {

	class CT_Vc_Timeline_Config {

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
					return 'ct_vc_timeline';
					break;
				case 'name' :
					return esc_html__( 'Timeline', 'grooni-theme-addons' );
					break;
				case 'description' :
					return esc_html__( 'Widget for timeline', 'grooni-theme-addons' );
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
					'holder'      => 'div',
					'class'       => '',
					'heading'     => esc_html__( 'Title', 'grooni-theme-addons' ),
					'param_name'  => 'title',
					'value'       => esc_html__( 'Title', 'grooni-theme-addons' ),
					'save_always' => true,
					'admin_label' => true,
				)
			);
		}


		public static function as_parent() {
			return array( 'only' => 'ct_vc_timeline_item' );
		}

		public static function content_element() {
			return true;
		}

		public static function is_container() {
			return true;
		}

		public static function js_view() {
			return 'VcColumnView';
		}

		public static function icon() {
			return 'vc_timeline_icon';
		}

	}

}
