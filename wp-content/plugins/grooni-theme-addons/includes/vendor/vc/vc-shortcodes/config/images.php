<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );
/**
 * Shortcode config Class.
 *
 * @package Grooni_Theme_Addons
 */


if ( ! class_exists( 'CT_Vc_Images_Config' ) ) {

	class CT_Vc_Images_Config {

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
					return 'ct_vc_images';
					break;
				case 'name' :
					return esc_html__( 'Images', 'grooni-theme-addons' );
					break;
				case 'description' :
					return esc_html__( 'Grooni images widget', 'grooni-theme-addons' );
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
					'value'       => esc_html__( 'Images', 'grooni-theme-addons' ),
					'save_always' => false,
					'admin_label' => false,
				),
				array(
					'type'        => 'dropdown',
					'heading'     => esc_html__( 'How many rows to show? (min 1, max 6)', 'grooni-theme-addons' ),
					'description' => '',
					'param_name'  => 'rows',
					'value'       => array(
						'1' => '1',
						'2' => '2',
						'3' => '3',
						'4' => '4',
						'5' => '5',
						'6' => '6',
					),
					'std'         => '2',
					'save_always' => false,
					'admin_label' => false
				),
				array(
					'type'        => 'dropdown',
					'heading'     => esc_html__( 'How many images in row? (min 1, max 4)', 'grooni-theme-addons' ),
					'description' => '',
					'param_name'  => 'count',
					'value'       => array(
						'1' => '1',
						'2' => '2',
						'3' => '3',
						'4' => '4',
					),
					'std'         => '3',
					'save_always' => false,
					'admin_label' => false
				),
				array(
					'type'        => 'dropdown',
					'heading'     => esc_html__( 'Images style', 'grooni-theme-addons' ),
					'description' => '',
					'param_name'  => 'style',
					'value'       => array(
						esc_html__( 'Classic', 'grooni-theme-addons' ) => 'classic',
					),
					'std'         => 'classic',
					'save_always' => false,
					'admin_label' => false
				),
				array(
					'type'        => 'dropdown',
					'heading'     => esc_html__( 'Image size', 'grooni-theme-addons' ),
					'description' => '',
					'param_name'  => 'size',
					'value'       => array(
						esc_html__( 'Thumbnail', 'grooni-theme-addons' ) => 'thumbnail',
						esc_html__( 'Medium', 'grooni-theme-addons' ) => 'medium',
						esc_html__( 'Large', 'grooni-theme-addons' ) => 'large',
						esc_html__( 'Original', 'grooni-theme-addons' ) => 'original',
					),
					'std'         => 'thumbnail',
					'save_always' => false,
					'admin_label' => true
				),
				array(
					'type'        => 'dropdown',
					'heading'     => esc_html__( 'Show from:', 'grooni-theme-addons' ),
					'description' => '',
					'param_name'  => 'from',
					'value'       => array(
						esc_html__( 'Show images from media library', 'grooni-theme-addons' ) => '1',
						esc_html__( 'Show featured images of blogs', 'grooni-theme-addons' )    => '2',
						esc_html__( 'Show featured image from Single portfolio', 'grooni-theme-addons' )     => '3',
					),
					'std'         => '1',
					'save_always' => false,
					'admin_label' => true
				),
				array(
					'type'        => 'dropdown',
					'heading'     => esc_html__( 'Action on click', 'grooni-theme-addons' ),
					'description' => '',
					'param_name'  => 'action',
					'value'       => array(
						esc_html__( 'Open in lightbox', 'grooni-theme-addons' ) => '1',
						esc_html__( 'Open attachment page', 'grooni-theme-addons' )  => '2',
						esc_html__( 'Open post', 'grooni-theme-addons' ) => '3',
					),
					'std'         => '1',
					'save_always' => false,
					'admin_label' => false
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
