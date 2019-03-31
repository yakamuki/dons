<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );
/**
 * Shortcode config Class.
 *
 * @package Grooni_Theme_Addons
 */


if ( ! class_exists( 'CT_Vc_Timeline_item_Config' ) ) {

	class CT_Vc_Timeline_item_Config {

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
					return 'ct_vc_timeline_item';
					break;
				case 'name' :
					return esc_html__( 'imeline item', 'grooni-theme-addons' );
					break;
				case 'description' :
					return esc_html__( 'Widget for timeline item', 'grooni-theme-addons' );
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
				),
				array(
					'type'        => 'textfield',
					'holder'      => 'div',
					'class'       => '',
					'heading'     => esc_html__( 'Text', 'grooni-theme-addons' ),
					'param_name'  => 'text',
					'value'       => esc_html__( 'Text', 'grooni-theme-addons' ),
					'save_always' => true,
					'admin_label' => false,
				),
			);
		}


		public static function as_child() {
			return array( 'only' => 'ct_vc_timeline' );
		}

	}

}
