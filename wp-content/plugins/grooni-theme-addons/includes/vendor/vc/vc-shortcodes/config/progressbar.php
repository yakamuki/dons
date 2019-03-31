<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );
/**
 * Shortcode config Class.
 *
 * @package Grooni_Theme_Addons
 */


if ( ! class_exists( 'CT_Vc_Progressbar_Config' ) ) {

	class CT_Vc_Progressbar_Config {

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
					return 'ct_vc_progressbar';
					break;
				case 'name' :
					return esc_html__( 'Progressbar', 'grooni-theme-addons' );
					break;
				case 'description' :
					return esc_html__( 'Widget for progress bar', 'grooni-theme-addons' );
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
					'admin_label' => false,
				),
				array(
					'type'        => 'textfield',
					'holder'      => 'div',
					'class'       => '',
					'heading'     => esc_html__( 'Value (in %)', 'grooni-theme-addons' ),
					'param_name'  => 'value',
					'value'       => esc_html__( '58', 'grooni-theme-addons' ),
					'save_always' => true,
					'admin_label' => false,
				),
				array(
					'type'        => 'checkbox',
					'holder'      => 'div',
					'class'       => '',
					'heading'     => esc_html__( 'Tooltip', 'grooni-theme-addons' ),
					'param_name'  => 'tooltip',
					'save_always' => true,
					'value'       => esc_html__( '0', 'grooni-theme-addons' ),
					'admin_label' => false,
				),
				array(
					'type'        => 'colorpicker',
					'holder'      => 'div',
					'class'       => '',
					'heading'     => esc_html__( 'Tooltip color', 'grooni-theme-addons' ),
					'param_name'  => 'tooltip_color',
					'dependency'  => array( 'element' => 'tooltip', 'not_empty' => true ),
					'save_always' => true,
					'value'       => '',
					'admin_label' => false,
				),
				array(
					'type'        => 'colorpicker',
					'holder'      => 'div',
					'class'       => '',
					'heading'     => esc_html__( 'Progress bar color', 'grooni-theme-addons' ),
					'param_name'  => 'background',
					'value'       => '',
					'save_always' => true,
					'admin_label' => false,
				),
				array(
					'type'        => 'colorpicker',
					'holder'      => 'div',
					'class'       => '',
					'heading'     => esc_html__( 'Progress bar base color', 'grooni-theme-addons' ),
					'param_name'  => 'background_base',
					'value'       => '',
					'save_always' => true,
					'admin_label' => false,
				),
				array(
					'type'        => 'checkbox',
					'heading'     => esc_html__( 'Use custom font family and style for Title?', 'grooni-theme-addons' ),
					'param_name'  => 'use_custom_font',
					'admin_label' => false,
					'std'         => false,
					'save_always' => true,
					'group'       => esc_html__( 'Typography', 'grooni-theme-addons' )
				),
				array(
					'type'       => 'google_fonts',
					'param_name' => 'google_fonts',
					'value'      => 'font_family:Open%20Sans%3A300%2C300italic%2Cregular%2Citalic%2C600%2C600italic%2C700%2C700italic%2C800%2C800italic|font_style:400%20regular%3A400%3Anormal',
					'settings'   => array(
						'fields' => array(
							'font_family_description' => esc_html__( 'Select font family.', 'grooni-theme-addons' ),
							'font_style_description'  => esc_html__( 'Select font styling.', 'grooni-theme-addons' ),
						),
					),
					'dependency' => array(
						'element' => 'use_custom_font',
						'value'   => 'true',
					),
					'group'      => esc_html__( 'Typography', 'grooni-theme-addons' )
				),
				array(
					'type'       => 'font_container',
					'param_name' => 'font_container',
					'value'      => 'font_size:14|color:%23333333',
					'settings'   => array(
						'fields' => array(
							'font_size',
							'color',
							'font_size_description' => esc_html__( 'Enter font size.', 'grooni-theme-addons' ),
							'color_description'     => esc_html__( 'Select font color.', 'grooni-theme-addons' ),
						),
					),
					'dependency' => array(
						'element' => 'use_custom_font',
						'value'   => 'true',
					),
					'group'      => esc_html__( 'Typography', 'grooni-theme-addons' )
				),
				array(
					'type'        => 'checkbox',
					'heading'     => esc_html__( 'Use custom font family and style for Value?', 'grooni-theme-addons' ),
					'param_name'  => 'use_custom_font_value',
					'admin_label' => false,
					'std'         => false,
					'save_always' => true,
					'group'       => esc_html__( 'Typography', 'grooni-theme-addons' )
				),
				array(
					'type'       => 'google_fonts',
					'param_name' => 'google_fonts_value',
					'value'      => 'font_family:Open%20Sans%3A300%2C300italic%2Cregular%2Citalic%2C600%2C600italic%2C700%2C700italic%2C800%2C800italic|font_style:400%20regular%3A400%3Anormal',
					'settings'   => array(
						'fields' => array(
							'font_family_description' => esc_html__( 'Select font family.', 'grooni-theme-addons' ),
							'font_style_description'  => esc_html__( 'Select font styling.', 'grooni-theme-addons' ),
						),
					),
					'dependency' => array(
						'element' => 'use_custom_font_value',
						'value'   => 'true',
					),
					'group'      => esc_html__( 'Typography', 'grooni-theme-addons' )
				),
				array(
					'type'       => 'font_container',
					'param_name' => 'font_container_value',
					'value'      => 'font_size:16|color:%23333333',
					'settings'   => array(
						'fields' => array(
							'font_size',
							'color',
							'font_size_description' => esc_html__( 'Enter font size.', 'grooni-theme-addons' ),
							'color_description'     => esc_html__( 'Select font color.', 'grooni-theme-addons' ),
						),
					),
					'dependency' => array(
						'element' => 'use_custom_font_value',
						'value'   => 'true',
					),
					'group'      => esc_html__( 'Typography', 'grooni-theme-addons' )
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
