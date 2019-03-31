<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );
/**
 * Shortcode config Class.
 *
 * @package Grooni_Theme_Addons
 */


if ( ! class_exists( 'CT_Vc_Banner_Config' ) ) {

	class CT_Vc_Banner_Config {

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
					return 'ct_vc_banner';
					break;
				case 'name' :
					return esc_html__( 'Banner', 'grooni-theme-addons' );
					break;
				case 'description' :
					return esc_html__( 'Grooni banner widget', 'grooni-theme-addons' );
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
					'value'       => '',
					'save_always' => false,
					'admin_label' => false,
					'std'         => '',
				),
				array(
					'type'        => 'dropdown',
					'heading'     => esc_html__( 'Action on click', 'grooni-theme-addons' ),
					'description' => '',
					'param_name'  => 'action',
					'value'       => array(
						esc_html__( 'Without action', 'grooni-theme-addons' )     => 'none',
						esc_html__( 'Go to the URL', 'grooni-theme-addons' )  => 'url',
					),
					'std'         => 'none',
					'save_always' => false,
					'admin_label' => false
				),
				array(
					'type'        => 'textfield',
					'class'       => '',
					'heading'     => esc_html__( 'Link', 'grooni-theme-addons' ),
					'description' => '',
					'param_name'  => 'link',
					'value'       => '',
					'save_always' => false,
					'admin_label' => true,
					'dependency'  => array(
						'element' => 'action',
						'value'   => array( 'url' )
					),
					'std'         => '',
				),
				array(
					'type'        => 'attach_image',
					'class'       => '',
					'heading'     => esc_html__( 'Image', 'grooni-theme-addons' ),
					'description' => '',
					'param_name'  => 'image',
					'value'       => '',
					'save_always' => true,
					'admin_label' => true,
					'std'         => '',
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
