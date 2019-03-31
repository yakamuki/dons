<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );
/**
 * Shortcode config Class.
 *
 * @package Grooni_Theme_Addons
 */


if ( ! class_exists( 'CT_Vc_Blog_Config' ) ) {

	class CT_Vc_Blog_Config {

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
					return 'ct_vc_blog';
					break;
				case 'name' :
					return esc_html__( 'Blog', 'grooni-theme-addons' );
					break;
				case 'description' :
					return esc_html__( 'Blog widget', 'grooni-theme-addons' );
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
		public static function fields() {

			$categories = array();
			foreach ( get_categories() as $category ) {
				$categories[ $category->slug ] = $category->name;
			}

			$tags = array();
			foreach ( get_tags() as $tag ) {
				$tags[ $tag->slug ] = $tag->name;
			}

			$image_sizes_select_values = array_flip( CT_Vc_Blog_Widget::get_image_sizes_select_values() );

			return array(
				array(
					'type'        => 'dropdown',
					'heading'     => esc_html__( 'Layout', 'grooni-theme-addons' ),
					'description' => esc_html__( 'Select blog layout', 'grooni-theme-addons' ),
					'param_name'  => 'layout',
					'value'       => array(
						esc_html__( 'Cell', 'grooni-theme-addons' )     => 'cell',
						esc_html__( 'Masonry', 'grooni-theme-addons' )  => 'masonry',
						esc_html__( 'Standard', 'grooni-theme-addons' ) => 'standard',
					),
					'std'         => 'cell',
					'save_always' => true,
					'admin_label' => true
				),
				array(
					'type'        => 'dropdown',
					'heading'     => esc_html__( 'Style', 'grooni-theme-addons' ),
					'param_name'  => 'style',
					'admin_label' => true,
					'value'       => array(
						esc_html__( 'Corporate', 'grooni-theme-addons' ) => 'corporate',
						esc_html__( 'Flat', 'grooni-theme-addons' )      => 'flat',
					),
					'save_always' => true,
					'std'         => 'corporate',
					'dependency'  => array(
						'element' => 'layout',
						'value'   => array( 'masonry' )
					)
				),
				array(
					'type'        => 'dropdown',
					'heading'     => esc_html__( 'Top content proportion', 'grooni-theme-addons' ),
					'description' => '',
					'param_name'  => 'img_proportion',
					'value'       => array(
						esc_html__( '1:1', 'grooni-theme-addons' )           => '1x1',
						esc_html__( '4:3', 'grooni-theme-addons' )           => '4x3',
						esc_html__( '3:2', 'grooni-theme-addons' )           => '3x2',
						esc_html__( '16:9', 'grooni-theme-addons' )          => '16x9',
						esc_html__( '3:4', 'grooni-theme-addons' )           => '3x4',
						esc_html__( '2:3', 'grooni-theme-addons' )           => '2x3',
						esc_html__( 'Original', 'grooni-theme-addons' ) => 'original',
					),
					'std'         => '1x1',
                    'dependency'  => array(
                        'element' => 'layout',
                        'value'   => array( 'masonry' )
                    ),
					'save_always' => true,
					'admin_label' => false
				),
				array(
					'type'        => 'dropdown',
					'heading'     => esc_html__( 'Basic image resolution', 'grooni-theme-addons' ),
					'param_name'  => 'image_resolution',
					'admin_label' => false,
					'value'       => $image_sizes_select_values,
					'save_always' => true,
					'std'         => 'crane-featured',
				),
				array(
					'type'        => 'checkbox',
					'heading'     => esc_html__( 'Show title', 'grooni-theme-addons' ),
					'param_name'  => 'show_title_description',
					'admin_label' => false,
					'std'         => true,
					'save_always' => true,
					'dependency'  => array(
						'element' => 'layout',
						'value'   => array( 'cell', 'masonry' )
					)
				),
				array(
					'type'        => 'checkbox',
					'heading'     => esc_html__( 'Show publication date', 'grooni-theme-addons' ),
					'param_name'  => 'show_pubdate',
					'admin_label' => false,
					'std'         => true,
					'save_always' => true,
					'dependency'  => array(
						'element' => 'layout',
						'value'   => array( 'standard' )
					)
				),
				array(
					'type'        => 'checkbox',
					'heading'     => esc_html__( 'Show author', 'grooni-theme-addons' ),
					'param_name'  => 'show_author',
					'admin_label' => false,
					'std'         => true,
					'save_always' => true,
					'dependency'  => array(
						'element' => 'layout',
						'value'   => array( 'standard' )
					)
				),
				array(
					'type'        => 'checkbox',
					'heading'     => esc_html__( 'Show categories', 'grooni-theme-addons' ),
					'param_name'  => 'show_cats',
					'admin_label' => false,
					'std'         => true,
					'save_always' => true,
					'dependency'  => array(
						'element' => 'layout',
						'value'   => array( 'standard' )
					)
				),
				array(
					'type'        => 'checkbox',
					'heading'     => esc_html__( 'Show Tags', 'grooni-theme-addons' ),
					'param_name'  => 'show_tags',
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
					'save_always' => true,
					'dependency'  => array(
						'element' => 'layout',
						'value'   => array( 'standard', 'cell', 'masonry' )
					)
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
					)
				),
				array(
					'type'        => 'grooni-number',
					'heading'     => esc_html__( 'Excerpt height', 'grooni-theme-addons' ),
					'param_name'  => 'excerpt_height',
					'min'         => 50,
					'max'         => 500,
					'std'         => 170,
					'step'        => 1,
					'admin_label' => false,
					'save_always' => true,
					'dependency'  => array(
						'element' => 'layout',
						'value'   => array( 'cell', 'masonry' )
					)
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
					'type'        => 'colorpicker',
					'heading'     => esc_html__( 'Item background color', 'grooni-theme-addons' ),
					'description' => '',
					'param_name'  => 'blog_cell_item_bg_color',
					'admin_label' => false,
					'std'         => '#f8f7f5',
					'save_always' => true,
					'dependency'  => array(
						'element' => 'layout',
						'value'   => 'cell'
					)
				),
				array(
					'type'        => 'checkbox',
					'heading'     => esc_html__( 'Show comments link icon?', 'grooni-theme-addons' ),
					'description' => '',
					'param_name'  => 'show_comment_link',
					'admin_label' => false,
					'std'         => false,
					'save_always' => true,
					'dependency'  => array(
						'element' => 'layout',
						'value'   => array( 'standard', 'masonry' )
					)
				),
				array(
					'type'        => 'checkbox',
					'heading'     => esc_html__( 'Show social share button', 'grooni-theme-addons' ),
					'description' => '',
					'param_name'  => 'show_share_button',
					'admin_label' => false,
					'std'         => false,
					'save_always' => true,
					'dependency'  => array(
						'element' => 'layout',
						'value'   => array( 'standard', 'masonry' )
					)
				),
				array(
					'type'        => 'dropdown',
					'heading'     => esc_html__( 'Show post meta', 'grooni-theme-addons' ),
					'param_name'  => 'show_post_meta',
					'admin_label' => false,
					'value'       => array(
						esc_html__( 'Show author info and post date', 'grooni-theme-addons' ) => 'author-and-date',
						esc_html__( 'Show post date', 'grooni-theme-addons' )                 => 'date',
						esc_html__( 'Do not show', 'grooni-theme-addons' )                    => 'none',
					),
					'save_always' => true,
					'std'         => 'author-and-date',
					'dependency'  => array(
						'element' => 'layout',
						'value'   => array( 'masonry' )
					)
				),
				array(
					'type'        => 'grooni-number',
					'heading'     => esc_html__( 'Space between items', 'grooni-theme-addons' ),
					'description' => esc_html__( 'Space between items in grid and masonry blog layout', 'grooni-theme-addons' ),
					'param_name'  => 'grid_spacing',
					'max'         => 50,
					'min'         => 0,
					'step'        => 1,
					'std'         => 0,
					'admin_label' => false,
					'save_always' => true,
					'dependency'  => array(
						'element' => 'layout',
						'value'   => array( 'cell', 'masonry' )
					)
				),
				array(
					'type'        => 'grooni-number',
					'heading'     => esc_html__( 'How many Columns?', 'grooni-theme-addons' ),
					'description' => esc_html__( 'How many columns to show in one row?', 'grooni-theme-addons' ),
					'param_name'  => 'columns',
					'min'         => 2,
					'max'         => 8,
					'step'        => 1,
					'std'         => 4,
					'admin_label' => false,
					'save_always' => true,
					'dependency'  => array(
						'element' => 'layout',
						'value'   => array( 'masonry' )
					)
				),
				array(
					'type'        => 'grooni-number',
					'heading'     => esc_html__( 'How many Columns?', 'grooni-theme-addons' ),
					'description' => esc_html__( 'How many columns to show in one row?', 'grooni-theme-addons' ),
					'param_name'  => 'columns_cell',
					'min'         => 1,
					'max'         => 3,
					'step'        => 1,
					'std'         => 2,
					'admin_label' => false,
					'save_always' => true,
					'dependency'  => array(
						'element' => 'layout',
						'value'   => array( 'cell' )
					)
				),
				array(
					'type'        => 'grooni-number',
					'heading'     => esc_html__( 'Items Stacking Width', 'grooni-theme-addons' ),
					'description' => esc_html__( 'Set Resolution on which items wil be stacked 1 per row', 'grooni-theme-addons' ),
					'param_name'  => 'max_width',
					'min'         => 300,
					'max'         => 1500,
					'std'         => 768,
					'step'        => 1,
					'admin_label' => false,
					'save_always' => true,
					'dependency'  => array(
						'element' => 'layout',
						'value'   => array( 'cell', 'masonry' )
					)
				),
				array(
					'type'        => 'grooni-number',
					'heading'     => esc_html__( 'Post height on desktop', 'grooni-theme-addons' ),
					'param_name'  => 'post_height_desktop',
					'min'         => 150,
					'max'         => 750,
					'std'         => 350,
					'step'        => 1,
					'admin_label' => false,
					'save_always' => true,
					'dependency'  => array(
						'element' => 'layout',
						'value'   => 'cell'
					)
				),
				array(
					'type'        => 'grooni-number',
					'heading'     => esc_html__( 'Post height on mobile', 'grooni-theme-addons' ),
					'param_name'  => 'post_height_mobile',
					'min'         => 150,
					'max'         => 750,
					'std'         => 350,
					'step'        => 1,
					'admin_label' => false,
					'save_always' => true,
					'dependency'  => array(
						'element' => 'layout',
						'value'   => 'cell'
					)
				),
				array(
					'type'        => 'grooni-number',
					'heading'     => esc_html__( 'How many posts', 'grooni-theme-addons' ),
					'description' => esc_html__( 'How many Posts would you like to show? (0 means unlimited)', 'grooni-theme-addons' ),
					'param_name'  => 'posts_limit',
					'admin_label' => false,
					'min'         => 0,
					'max'         => 100,
					'step'        => 1,
					'std'         => 4,
					'save_always' => true,
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
					'std'         => 'ASC'
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
					'std'         => 'post_date'
				),
				array(
					'type'        => 'checkbox',
					'heading'     => esc_html__( 'Enable Custom order?', 'grooni-theme-addons' ),
					'description' => esc_html__( 'If enable, widget shows custom ordered posts with second sorting set in "Order by".', 'grooni-theme-addons' ),
					'param_name'  => 'show_custom_order',
					'admin_label' => false,
					'std'         => false,
					'save_always' => false,
				),
				array(
					'type'        => 'dropdown',
					'heading'     => esc_html__( 'Open on click in', 'grooni-theme-addons' ),
					'param_name'  => 'target',
					'admin_label' => false,
					'value'       => array(
						esc_html__( 'New window', 'grooni-theme-addons' )  => 'blank',
						esc_html__( 'Same window', 'grooni-theme-addons' ) => 'same',
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
