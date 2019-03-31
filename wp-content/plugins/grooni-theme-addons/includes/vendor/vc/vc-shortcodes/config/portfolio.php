<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );
/**
 * Shortcode config Class.
 *
 * @package Grooni_Theme_Addons
 */


if ( ! class_exists( 'CT_Vc_Portfolio_Config' ) ) {

	// remember: it uses in migration process
	class CT_Vc_Portfolio_Config {

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
					return 'ct_vc_portfolio';
					break;
				case 'name' :
					return esc_html__( 'Portfolio', 'grooni-theme-addons' );
					break;
				case 'description' :
					return esc_html__( 'Widget for portfolio', 'grooni-theme-addons' );
					break;
			}

			return '';
		}

		/**
		 * @param array $categories
		 * @param array $tags
		 *
		 * @return array
		 */
		public static function fields( $categories = array(), $tags = array() ) {

			$categories_select = [];
			foreach ( $categories as $category ) {
				$categories_select[ $category['slug'] ] = $category['title'];
			}
			$categories = $categories_select;
			unset( $categories_select );

			$tags_select = [];
			foreach ( $tags as $tag ) {
				$tags_select[ $tag['slug'] ] = $tag['title'];
			}
			$tags = $tags_select;
			unset( $tags_select );

			$image_sizes_select_values = array_flip( CT_Vc_Portfolio_Widget::get_image_sizes_select_values() );

			return array(
				array(
					'type'        => 'dropdown',
					'heading'     => esc_html__( 'Layout', 'grooni-theme-addons' ),
					'description' => esc_html__( 'Select portfolio layout', 'grooni-theme-addons' ),
					'param_name'  => 'layout',
					'value'       => array(
						esc_html__( 'Grid', 'grooni-theme-addons' )    => 'grid',
						esc_html__( 'Masonry', 'grooni-theme-addons' ) => 'masonry',
					),
					'std'         => 'grid',
					'save_always' => true,
					'admin_label' => true
				),
				array(
					'type'        => 'dropdown',
					'heading'     => esc_html__( 'Layout mode', 'grooni-theme-addons' ),
					'description' => esc_html__( 'Select portfolio layout mode', 'grooni-theme-addons' ),
					'param_name'  => 'layout_mode',
					'value'       => array(
						esc_html__( 'Standard', 'grooni-theme-addons' ) => 'masonry',
						esc_html__( 'Fit rows', 'grooni-theme-addons' ) => 'fitRows',
					),
					'std'         => 'masonry',
					'save_always' => true,
					'admin_label' => true
				),
				array(
					'type'        => 'dropdown',
					'heading'     => esc_html__( 'Image proportion', 'grooni-theme-addons' ),
					'description' => '',
					'param_name'  => 'img_proportion',
					'value'       => array(
						esc_html__( '4:3', 'grooni-theme-addons' )      => '4x3',
						esc_html__( '3:2', 'grooni-theme-addons' )      => '3x2',
						esc_html__( '16:9', 'grooni-theme-addons' )     => '16x9',
						esc_html__( '1:1', 'grooni-theme-addons' )      => '1x1',
						esc_html__( '3:4', 'grooni-theme-addons' )      => '3x4',
						esc_html__( '2:3', 'grooni-theme-addons' )      => '2x3',
						esc_html__( 'Original', 'grooni-theme-addons' ) => 'original',
					),
					'std'         => '1x1',
					'save_always' => true,
					'admin_label' => false
				),
				array(
					'type'        => 'dropdown',
					'heading'     => esc_html__( 'Style', 'grooni-theme-addons' ),
					'param_name'  => 'style',
					'admin_label' => false,
					'value'       => array(
						esc_html__( 'Flat', 'grooni-theme-addons' )    => 'flat',
						esc_html__( 'Minimal', 'grooni-theme-addons' ) => 'minimal',
						esc_html__( 'Modern', 'grooni-theme-addons' )  => 'modern'
					),
					'save_always' => true,
					'std'         => 'flat',
				),
				array(
					'type'        => 'dropdown',
					'heading'     => esc_html__( 'Basic image resolution', 'grooni-theme-addons' ),
					'param_name'  => 'image_resolution',
					'admin_label' => false,
					'value'       => $image_sizes_select_values,
					'save_always' => true,
					'std'         => 'crane-portfolio-300',
				),
				array(
					'type'        => 'dropdown',
					'heading'     => esc_html__( 'Hover style', 'grooni-theme-addons' ),
					'description' => esc_html__( 'Select item hover style', 'grooni-theme-addons' ),
					'param_name'  => 'hover_style',
					'value'       => array(
						esc_html__( 'Direction-aware hover', 'grooni-theme-addons' )                     => 1,
						esc_html__( 'Overlay with zoom and link icons on hover', 'grooni-theme-addons' ) => 2,
						esc_html__( 'Zoom image on hover', 'grooni-theme-addons' )                       => 3,
						esc_html__( 'Just link', 'grooni-theme-addons' )                                 => 4,
						esc_html__( 'Shuffle text link', 'grooni-theme-addons' )                         => 5
					),
					'std'         => 4,
					'admin_label' => false,
					'save_always' => true,
				),
				array(
					'type'        => 'dropdown',
					'heading'     => esc_html__( 'Image resolution for image in lightbox',	'grooni-theme-addons' ),
					'param_name'  => 'image_resolution_for_link',
					'admin_label' => false,
					'value'       => $image_sizes_select_values,
					'save_always' => true,
					'std'         => 'full',
					'dependency'  => array(
						'element' => 'hover_style',
						'value'   => '2',
					),
				),
				array(
					'type'        => 'colorpicker',
					'heading'     => esc_html__( 'Direction-aware hover color', 'grooni-theme-addons' ),
					'param_name'  => 'direction_aware_color',
					'admin_label' => false,
					'save_always' => true,
					'std'         => 'rgba(0,0,0,0.5)',
					'dependency'  => array(
						'element' => 'hover_style',
						'value'   => '1'
					),
				),
				array(
					'type'        => 'textfield',
					'heading'     => esc_html__( 'Shuffle link text', 'grooni-theme-addons' ),
					'param_name'  => 'shuffle_text',
					'save_always' => true,
					'admin_label' => false,
					'dependency'  => array(
						'element' => 'hover_style',
						'value'   => '5'
					),
					'std'         => esc_html__( 'View project', 'crane' ),
				),
				array(
					'type'        => 'grooni-number',
					'heading'     => esc_html__( 'Space between items', 'grooni-theme-addons' ),
					'description' => esc_html__( 'Space between items in grid and masonry portfolio layout.', 'grooni-theme-addons' ),
					'param_name'  => 'grid_spacing',
					'max'         => 100,
					'min'         => 0,
					'std'         => 30,
					'admin_label' => false,
					'save_always' => true
				),
				array(
					'type'        => 'grooni-number',
					'heading'     => esc_html__( 'How many Columns?', 'grooni-theme-addons' ),
					'description' => esc_html__( 'How many columns to show in one row?', 'grooni-theme-addons' ),
					'param_name'  => 'columns',
					'min'         => 2,
					'max'         => 8,
					'std'         => 4,
					'step'        => 1,
					'admin_label' => false,
					'save_always' => true,
				),
				array(
					'type'        => 'grooni-number',
					'heading'     => esc_html__( 'Items Stacking Width', 'grooni-theme-addons' ),
					'description' => esc_html__( 'Set Resolution on which items wil be stacked 1 per row', 'grooni-theme-addons' ),
					'param_name'  => 'max_width',
					'min'         => 100,
					'max'         => 2000,
					'std'         => 768,
					'step'        => 1,
					'admin_label' => false,
					'save_always' => true,
				),
				array(
					'type'        => 'grooni-number',
					'heading'     => esc_html__( 'How many posts', 'grooni-theme-addons' ),
					'description' => esc_html__( 'How many Posts would you like to show? (0 means unlimited)', 'grooni-theme-addons' ),
					'param_name'  => 'posts_limit',
					'admin_label' => false,
					'std'         => 0,
					'min'         => 0,
					'max'         => 100,
					'step'        => 1,
					'save_always' => true
				),
				array(
					'type'        => 'checkbox',
					'heading'     => esc_html__( 'Show title', 'grooni-theme-addons' ),
					'param_name'  => 'show_title_description',
					'admin_label' => false,
					'std'         => true,
					'save_always' => true
				),
				array(
					'type'        => 'checkbox',
					'heading'     => esc_html__( 'Show categories', 'grooni-theme-addons' ),
					'param_name'  => 'show_categories',
					'admin_label' => false,
					'std'         => true,
					'save_always' => true
				),
				array(
					'type'        => 'checkbox',
					'heading'     => esc_html__( 'Show custom text from meta?', 'grooni-theme-addons' ),
					'description' => '',
					'param_name'  => 'show_custom_text',
					'admin_label' => false,
					'std'         => false,
					'save_always' => true
				),
				array(
					'type'        => 'checkbox',
					'heading'     => esc_html__( 'Show Excerpt?', 'grooni-theme-addons' ),
					'description' => esc_html__( 'Disable this option if you do not want excerpt', 'grooni-theme-addons' ),
					'param_name'  => 'show_excerpt',
					'admin_label' => false,
					'std'         => true,
					'save_always' => true
				),
				array(
					'type'        => 'checkbox',
					'heading'     => esc_html__( 'Strip html in except?', 'grooni-theme-addons' ),
					'param_name'  => 'excerpt_strip_html',
					'admin_label' => false,
					'std'         => true,
					'save_always' => true,
					'dependency'  => array(
						'element' => 'show_excerpt',
						'value'   => 'true'
					),
				),
				array(
					'type'        => 'grooni-number',
					'heading'     => esc_html__( 'Excerpt height', 'grooni-theme-addons' ),
					'param_name'  => 'excerpt_height',
					'min'         => '50',
					'max'         => '500',
					'std'         => '170',
					'step'        => '1',
					'admin_label' => false,
					'save_always' => true,
					'dependency'  => array(
						'element' => 'show_excerpt',
						'value'   => 'true'
					),
				),
				array(
					'type'        => 'checkbox',
					'heading'     => esc_html__( 'Show &quot;read more&quot; link', 'grooni-theme-addons' ),
					'param_name'  => 'show_read_more',
					'admin_label' => false,
					'std'         => false,
					'save_always' => false,
				),
				array(
					'type'        => 'dropdown',
					'heading'     => esc_html__( 'Portfolio tags type', 'grooni-theme-addons' ),
					'description' => '',
					'param_name'  => 'show_imgtags',
					'admin_label' => false,
					'std'         => '0',
					'value'       => array(
						esc_html__( 'No tags', 'grooni-theme-addons' )    => '0',
						esc_html__( 'Text tags', 'grooni-theme-addons' )  => 'text',
						esc_html__( 'Image tags', 'grooni-theme-addons' ) => 'image',
					),
					'save_always' => true
				),
				array(
					'type'        => 'checkbox',
					'heading'     => esc_html__( 'Show filtering by category', 'grooni-theme-addons' ),
					'description' => esc_html__( 'Check this checkbox if you want to enable filtering by category.', 'grooni-theme-addons' ),
					'param_name'  => 'sortable',
					'admin_label' => false,
					'save_always' => true,
					'std'         => true,
					'group'       => esc_html__( 'Filtering and sorting', 'grooni-theme-addons' )
				),
				array(
					'type'        => 'dropdown',
					'heading'     => esc_html__( 'Filtering style', 'grooni-theme-addons' ),
					'description' => esc_html__( 'Select style of navigation filtering', 'grooni-theme-addons' ),
					'param_name'  => 'sortable_style',
					'admin_label' => false,
					'value'       => array(
						esc_html__( 'Default', 'grooni-theme-addons' ) => 'in_grid',
						esc_html__( 'Custom', 'grooni-theme-addons' )  => 'outline'
					),
					'save_always' => true,
					'dependency'  => array(
						'element' => 'sortable',
						'value'   => 'true'
					),
					'std'         => 'in_grid',
					'group'       => esc_html__( 'Filtering and sorting', 'grooni-theme-addons' )

				),
				array(
					'type'        => 'dropdown',
					'heading'     => esc_html__( 'Filtering align', 'grooni-theme-addons' ),
					'param_name'  => 'sortable_align',
					'admin_label' => false,
					'value'       => array(
						esc_html__( 'Left', 'grooni-theme-addons' )   => 'left',
						esc_html__( 'Right', 'grooni-theme-addons' )  => 'right',
						esc_html__( 'Center', 'grooni-theme-addons' ) => 'center',
					),
					'save_always' => true,
					'dependency'  => array(
						'element' => 'sortable_style',
						'value'   => 'outline'
					),
					'std'         => 'center',
					'group'       => esc_html__( 'Filtering and sorting', 'grooni-theme-addons' )
				),
				array(
					'type'        => 'dropdown',
					'heading'     => esc_html__( 'Filtering width', 'grooni-theme-addons' ),
					'description' => esc_html__( 'Select style of navigation filtering', 'grooni-theme-addons' ),
					'param_name'  => 'sortable_width',
					'admin_label' => false,
					'value'       => array(
						esc_html__( 'Default', 'grooni-theme-addons' )                 => 'default',
						esc_html__( '100% width of container', 'grooni-theme-addons' ) => 'fullwidth'
					),
					'save_always' => true,
					'dependency'  => array(
						'element' => 'sortable_style',
						'value'   => 'outline'
					),
					'group'       => esc_html__( 'Filtering and sorting', 'grooni-theme-addons' )
				),
				array(
					'type'        => 'colorpicker',
					'heading'     => esc_html__( 'Filtering background color    ', 'grooni-theme-addons' ),
					'param_name'  => 'sortable_background_color',
					'admin_label' => false,
					'save_always' => true,
					'dependency'  => array(
						'element' => 'sortable_style',
						'value'   => 'outline'
					),
					'group'       => esc_html__( 'Filtering and sorting', 'grooni-theme-addons' )
				),
				array(
					'type'        => 'colorpicker',
					'heading'     => esc_html__( 'Filtering text color', 'grooni-theme-addons' ),
					'param_name'  => 'sortable_text_color',
					'admin_label' => false,
					'save_always' => true,
					'dependency'  => array(
						'element' => 'sortable_style',
						'value'   => 'outline'
					),
					'group'       => esc_html__( 'Filtering and sorting', 'grooni-theme-addons' )
				),
				array(
					'type'        => 'dropdown',
					'heading'     => esc_html__( 'Pagination type', 'grooni-theme-addons' ),
					'param_name'  => 'pagination_type',
					'admin_label' => false,
					'value'       => array(
						esc_html__( 'No pagination', 'grooni-theme-addons' )    => '',
						esc_html__( 'Load More button', 'grooni-theme-addons' ) => 'show_more',
					),
					'save_always' => true,
					'std'         => 'show_more',
					'group'       => esc_html__( 'Pagination', 'grooni-theme-addons' )
				),
				array(
					'type'        => 'textfield',
					'class'       => '',
					'heading'     => esc_html__( 'Pagination button text', 'grooni-theme-addons' ),
					'description' => esc_html__( 'If left blank "show more" text will be shown', 'grooni-theme-addons' ),
					'param_name'  => 'show_more_text',
					'value'       => esc_html__( 'Show more', 'grooni-theme-addons' ),
					'save_always' => true,
					'admin_label' => false,
					'dependency'  => array(
						'element' => 'pagination_type',
						'value'   => array( 'show_more', 'scroll' )
					),
					'std'         => esc_html__( 'Show more', 'grooni-theme-addons' ),
					'group'       => esc_html__( 'Pagination', 'grooni-theme-addons' )
				),
				array(
					'type'        => 'checkbox',
					'heading'     => esc_html__( 'Use custom font family and style?', 'grooni-theme-addons' ),
					'param_name'  => 'pagination_use_custom_font',
					'admin_label' => false,
					'std'         => false,
					'save_always' => true,
					'dependency'  => array(
						'element' => 'pagination_type',
						'value'   => array( 'show_more', 'scroll' )
					),
					'group'       => esc_html__( 'Pagination', 'grooni-theme-addons' )
				),
				array(
					'type'       => 'google_fonts',
					'param_name' => 'pagination_google_fonts',
					'value'      => 'font_family:Open%20Sans%3A300%2C300italic%2Cregular%2Citalic%2C600%2C600italic%2C700%2C700italic%2C800%2C800italic|font_style:400%20regular%3A400%3Anormal',
					'settings'   => array(
						'fields' => array(
							'font_family_description' => esc_html__( 'Select font family.', 'grooni-theme-addons' ),
							'font_style_description'  => esc_html__( 'Select font styling.', 'grooni-theme-addons' ),
						),
					),
					'dependency' => array(
						'element' => 'pagination_use_custom_font',
						'value'   => 'true',
					),
					'group'      => esc_html__( 'Pagination', 'grooni-theme-addons' )
				),
				array(
					'type'       => 'font_container',
					'param_name' => 'pagination_font_container',
					'value'      => 'font_size:18',
					'settings'   => array(
						'fields' => array(
							'font_size',
							'font_size_description' => esc_html__( 'Enter font size.', 'grooni-theme-addons' ),
						),
					),
					'dependency' => array(
						'element' => 'pagination_use_custom_font',
						'value'   => 'true',
					),
					'group'      => esc_html__( 'Pagination', 'grooni-theme-addons' )
				),
				array(
					'type'        => 'dropdown',
					'heading'     => esc_html__( 'Pagination Text Transform', 'grooni-theme-addons' ),
					'param_name'  => 'pagination_text_transform',
					'admin_label' => false,
					'value'       => array(
						esc_html__( 'None', 'grooni-theme-addons' )       => 'none',
						esc_html__( 'Initial', 'grooni-theme-addons' )    => 'initial',
						esc_html__( 'Inherit', 'grooni-theme-addons' )    => 'inherit',
						esc_html__( 'Lowercase', 'grooni-theme-addons' )  => 'lowercase',
						esc_html__( 'Uppercase', 'grooni-theme-addons' )  => 'uppercase',
						esc_html__( 'Capitalize', 'grooni-theme-addons' ) => 'capitalize',
					),
					'std'         => 'none',
					'dependency'  => array(
						'element' => 'pagination_use_custom_font',
						'value'   => 'true',
					),
					'group'       => esc_html__( 'Pagination', 'grooni-theme-addons' )
				),
				array(
					'type'        => 'colorpicker',
					'heading'     => esc_html__( 'Pagination button text color', 'grooni-theme-addons' ),
					'param_name'  => 'pagination_color',
					'admin_label' => false,
					'value'       => '#ffffff',
					'save_always' => true,
					'dependency'  => array(
						'element' => 'pagination_type',
						'value'   => array( 'show_more', 'scroll' )
					),
					'group'       => esc_html__( 'Pagination', 'grooni-theme-addons' )
				),
				array(
					'type'        => 'colorpicker',
					'heading'     => esc_html__( 'Pagination button background color', 'grooni-theme-addons' ),
					'param_name'  => 'pagination_background',
					'admin_label' => false,
					'value'       => '#393b3f',
					'save_always' => true,
					'dependency'  => array(
						'element' => 'pagination_type',
						'value'   => array( 'show_more', 'scroll' )
					),
					'group'       => esc_html__( 'Pagination', 'grooni-theme-addons' )
				),
				array(
					'type'        => 'colorpicker',
					'heading'     => esc_html__( 'Pagination button hover & active text color', 'grooni-theme-addons' ),
					'param_name'  => 'pagination_color_hover',
					'admin_label' => false,
					'value'       => '#ffffff',
					'save_always' => true,
					'dependency'  => array(
						'element' => 'pagination_type',
						'value'   => array( 'show_more', 'scroll' )
					),
					'group'       => esc_html__( 'Pagination', 'grooni-theme-addons' )
				),
				array(
					'type'        => 'colorpicker',
					'heading'     => esc_html__( 'Pagination button hover & active background color', 'grooni-theme-addons' ),
					'param_name'  => 'pagination_background_hover',
					'admin_label' => false,
					'value'       => '#93cb52',
					'save_always' => true,
					'dependency'  => array(
						'element' => 'pagination_type',
						'value'   => array( 'show_more', 'scroll' )
					),
					'group'       => esc_html__( 'Pagination', 'grooni-theme-addons' )
				),
				array(
					'type'        => 'grooni-multiple-select',
					'heading'     => esc_html__( 'Select specific Authors', 'grooni-theme-addons' ),
					'description' => esc_html__( 'If left blank all will be shown', 'grooni-theme-addons' ),
					'param_name'  => 'author',
					'admin_label' => false,
					'value'       => grooni_get_users_array(),
					'save_always' => true,
					'std'         => '',
					'group'       => esc_html__( 'Filtering and sorting', 'grooni-theme-addons' )
				),
				array(
					'type'        => 'grooni-multiple-select',
					'heading'     => esc_html__( 'Select specific Categories', 'grooni-theme-addons' ),
					'description' => esc_html__( 'If left blank all will be shown', 'grooni-theme-addons' ),
					'param_name'  => 'category',
					'admin_label' => false,
					'value'       => $categories,
					'std'         => '',
					'save_always' => true,
					'group'       => esc_html__( 'Filtering and sorting', 'grooni-theme-addons' )
				),
				array(
					'type'        => 'grooni-multiple-select',
					'heading'     => esc_html__( 'Select specific Tags', 'grooni-theme-addons' ),
					'description' => esc_html__( 'If left blank all will be shown', 'grooni-theme-addons' ),
					'param_name'  => 'tag',
					'admin_label' => false,
					'value'       => $tags,
					'std'         => '',
					'save_always' => true,
					'group'       => esc_html__( 'Filtering and sorting', 'grooni-theme-addons' )
				),
				array(
					'type'        => 'dropdown',
					'heading'     => esc_html__( 'Order', 'grooni-theme-addons' ),
					'param_name'  => 'order',
					'admin_label' => false,
					'value'       => array(
						esc_html__( 'Asc', 'grooni-theme-addons' )  => 'ASC',
						esc_html__( 'Desc', 'grooni-theme-addons' ) => 'DESC',
					),
					'save_always' => true,
					'std'         => 'ASC',
					'group'       => esc_html__( 'Filtering and sorting', 'grooni-theme-addons' )
				),
				array(
					'type'        => 'dropdown',
					'heading'     => esc_html__( 'Order by', 'grooni-theme-addons' ),
					'param_name'  => 'orderby',
					'admin_label' => false,
					'value'       => array(
						esc_html__( 'Date', 'grooni-theme-addons' )          => 'post_date',
						esc_html__( 'Post ID', 'grooni-theme-addons' )       => 'ID',
						esc_html__( 'Title', 'grooni-theme-addons' )         => 'title',
						esc_html__( 'Comment count', 'grooni-theme-addons' ) => 'comment_count',
						esc_html__( 'Random', 'grooni-theme-addons' )        => 'rand',
						esc_html__( 'Author', 'grooni-theme-addons' )        => 'author',
					),
					'save_always' => true,
					'std'         => 'post_date',
					'group'       => esc_html__( 'Filtering and sorting', 'grooni-theme-addons' )
				),
				array(
					'type'        => 'checkbox',
					'heading'     => esc_html__( 'Enable Custom order?', 'grooni-theme-addons' ),
					'description' => esc_html__( 'If enable, widget shows custom ordered portfolio with second sorting set in "Order by".', 'grooni-theme-addons' ),
					'param_name'  => 'show_custom_order',
					'admin_label' => false,
					'std'         => false,
					'save_always' => false,
					'group'       => esc_html__( 'Filtering and sorting', 'grooni-theme-addons' )
				),
				array(
					'type'        => 'dropdown',
					'heading'     => esc_html__( 'Open on click in', 'grooni-theme-addons' ),
					'param_name'  => 'target',
					'admin_label' => false,
					'value'       => array(
						esc_html__( 'Same window', 'grooni-theme-addons' ) => 'same',
						esc_html__( 'New window', 'grooni-theme-addons' )  => 'blank',
					),
					'save_always' => true,
					'std'         => 'blank'
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
