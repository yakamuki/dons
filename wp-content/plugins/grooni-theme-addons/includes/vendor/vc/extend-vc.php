<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );
/**
 * Add new param to vc_row settings.
 *
 * @package Grooni_Theme_Addons
 */

vc_remove_param( 'vc_row', 'full_width' );
vc_remove_param( 'vc_row', 'video_bg' );
vc_remove_param( 'vc_row', 'video_bg_url' );
vc_remove_param( 'vc_row', 'video_bg_parallax' );
vc_remove_param( 'vc_row', 'parallax_speed_video' );
vc_remove_param( 'vc_row', 'parallax_speed_bg' );
if ( ! vc_is_page_editable() ) {
	vc_remove_param( 'vc_row', 'parallax' );
	vc_remove_param( 'vc_row', 'parallax_image' );
}

vc_add_params(
	'vc_row', array(
		array(
			'heading'     => esc_html__( 'Row stretch', 'grooni-theme-addons' ),
			'param_name'  => 'ct_row_stretch',
			'type'        => 'dropdown',
			'save_always' => true,
			'value'       => array(
				esc_html__( 'Default', 'grooni-theme-addons' )                               => '',
				esc_html__( 'Stretch row', 'grooni-theme-addons' )                           => 'stretch_row',
				esc_html__( 'Stretch row and content', 'grooni-theme-addons' )               => 'stretch_row_content',
				esc_html__( 'Stretch row and content (no paddings)', 'grooni-theme-addons' ) => 'stretch_row_content_no_spaces',
			),
			'weight'      => 100
		),
		array(
			'heading'            => esc_html__( 'Background Color', 'grooni-theme-addons' ),
			'param_name'         => 'ct_bg_color',
			'type'               => 'colorpicker',
			'param_holder_class' => 'vc_colored-dropdown vc_btn3-colored-dropdown',
			'value'              => '',
			'edit_field_class'   => 'vc_col-sm-12',
			'group'              => esc_html__( 'Design Options', 'grooni-theme-addons' ),
		),
		array(
			'heading'    => esc_html__( 'Background Image', 'grooni-theme-addons' ),
			'param_name' => 'ct_bg_image',
			'type'       => 'attach_image',
			'edit_field_class'   => 'vc_col-sm-12',
			'std'        => '',
			'group'      => esc_html__( 'Design Options', 'grooni-theme-addons' ),
		),
		array(
			'heading'            => esc_html__( 'Overlay Color', 'grooni-theme-addons' ),
			'param_name'         => 'ct_bg_color_overlay',
			'type'               => 'colorpicker',
			'param_holder_class' => 'vc_colored-dropdown vc_btn3-colored-dropdown',
			'value'              => '',
			'edit_field_class'   => 'vc_col-sm-12',
			'group'              => esc_html__( 'Design Options', 'grooni-theme-addons' ),
		),
		array(
			'heading'    => esc_html__( 'Background Repeat', 'grooni-theme-addons' ),
			'param_name' => 'ct_bg_repeat',
			'type'       => 'dropdown',
			'edit_field_class'   => 'vc_col-sm-12',
			'value'      => array(
				esc_html__( 'Theme defaults', 'grooni-theme-addons' )      => '',
				esc_html__( 'No Repeat', 'grooni-theme-addons' )           => 'no-repeat',
				esc_html__( 'Repeat', 'grooni-theme-addons' )              => 'repeat',
				esc_html__( 'Repeat Horizontally', 'grooni-theme-addons' ) => 'repeat-x',
				esc_html__( 'Repeat Vertically', 'grooni-theme-addons' )   => 'repeat-y',
			),
			'group'      => esc_html__( 'Design Options', 'grooni-theme-addons' ),
			'dependency' => array( 'element' => 'ct_bg_image', 'not_empty' => true ),
		),
		array(
			'heading'    => esc_html__( 'Background Size', 'grooni-theme-addons' ),
			'param_name' => 'ct_bg_size',
			'type'       => 'dropdown',
			'edit_field_class'   => 'vc_col-sm-12',
			'value'      => array(
				esc_html__( 'Theme defaults', 'grooni-theme-addons' ) => '',
				esc_html__( 'Cover', 'grooni-theme-addons' )          => 'cover',
				esc_html__( 'Contain', 'grooni-theme-addons' )        => 'contain',
				esc_html__( 'Initial', 'grooni-theme-addons' )        => 'initial',
			),
			'group'      => esc_html__( 'Design Options', 'grooni-theme-addons' ),
			'dependency' => array( 'element' => 'ct_bg_image', 'not_empty' => true ),
		),
		array(
			'heading'    => esc_html__( 'Background Position', 'grooni-theme-addons' ),
			'param_name' => 'ct_bg_position',
			'type'       => 'dropdown',
			'edit_field_class'   => 'vc_col-sm-12',
			'value'      => array(
				esc_html__( 'Theme defaults', 'grooni-theme-addons' ) => '',
				esc_html__( 'Center Left', 'grooni-theme-addons' )    => 'center left',
				esc_html__( 'Center Center', 'grooni-theme-addons' )  => 'center center',
				esc_html__( 'Center Right', 'grooni-theme-addons' )   => 'center right',
				esc_html__( 'Top Center', 'grooni-theme-addons' )     => 'top center',
				esc_html__( 'Top Left', 'grooni-theme-addons' )       => 'top left',
				esc_html__( 'Top Right', 'grooni-theme-addons' )      => 'top right',
				esc_html__( 'Bottom Center', 'grooni-theme-addons' )  => 'bottom center',
				esc_html__( 'Bottom Left', 'grooni-theme-addons' )    => 'bottom left',
				esc_html__( 'Bottom Right', 'grooni-theme-addons' )   => 'bottom right',
			),
			'group'      => esc_html__( 'Design Options', 'grooni-theme-addons' ),
			'dependency' => array( 'element' => 'ct_bg_image', 'not_empty' => true ),
		),
		array(
			'heading'    => esc_html__( 'Parallax style', 'grooni-theme-addons' ),
			'param_name' => 'ct_bg_parallax',
			'type'       => 'dropdown',
			'edit_field_class'   => 'vc_col-sm-12',

			'value'      => array(
				esc_html__( 'No parallax', 'grooni-theme-addons' ) => '',
				esc_html__( 'Fixed', 'grooni-theme-addons' )          => 'fixed',
			),
			'group'      => esc_html__( 'Design Options', 'grooni-theme-addons' ),
			'dependency' => array( 'element' => 'ct_bg_image', 'not_empty' => true ),
		),

	)
);

vc_remove_param( 'vc_section', 'full_width' );
vc_remove_param( 'vc_section', 'video_bg' );
vc_remove_param( 'vc_section', 'video_bg_url' );
vc_remove_param( 'vc_section', 'video_bg_parallax' );
vc_remove_param( 'vc_section', 'parallax_speed_video' );
vc_remove_param( 'vc_section', 'parallax_speed_bg' );
if ( ! vc_is_page_editable() ) {
	vc_remove_param( 'vc_section', 'parallax' );
	vc_remove_param( 'vc_section', 'parallax_image' );
}

vc_add_params(
	'vc_section', array(
		array(
			'heading'     => esc_html__( 'Section stretch', 'grooni-theme-addons' ),
			'param_name'  => 'ct_section_stretch',
			'type'        => 'dropdown',
			'save_always' => true,
			'value'       => array(
				esc_html__( 'Default', 'grooni-theme-addons' )                               => '',
				esc_html__( 'Stretch section', 'grooni-theme-addons' )                           => 'stretch_section',
				esc_html__( 'Stretch section and content', 'grooni-theme-addons' )               => 'stretch_section_content',
				esc_html__( 'Stretch section and content (no paddings)', 'grooni-theme-addons' ) => 'stretch_section_content_no_spaces',
			),
			'weight'      => 100
		),
		array(
			'heading'            => esc_html__( 'Background Color', 'grooni-theme-addons' ),
			'param_name'         => 'ct_bg_color',
			'type'               => 'colorpicker',
			'param_holder_class' => 'vc_colored-dropdown vc_btn3-colored-dropdown',
			'value'              => '',
			'edit_field_class'   => 'vc_col-sm-12',
			'group'              => esc_html__( 'Design Options', 'grooni-theme-addons' ),
		),
		array(
			'heading'    => esc_html__( 'Background Image', 'grooni-theme-addons' ),
			'param_name' => 'ct_bg_image',
			'type'       => 'attach_image',
			'edit_field_class'   => 'vc_col-sm-12',
			'std'        => '',
			'group'      => esc_html__( 'Design Options', 'grooni-theme-addons' ),
		),
		array(
			'heading'            => esc_html__( 'Overlay Color', 'grooni-theme-addons' ),
			'param_name'         => 'ct_bg_color_overlay',
			'type'               => 'colorpicker',
			'edit_field_class'   => 'vc_col-sm-12',
			'param_holder_class' => 'vc_colored-dropdown vc_btn3-colored-dropdown',
			'value'              => '',
			'group'              => esc_html__( 'Design Options', 'grooni-theme-addons' ),
		),
		array(
			'heading'    => esc_html__( 'Background Repeat', 'grooni-theme-addons' ),
			'param_name' => 'ct_bg_repeat',
			'type'       => 'dropdown',
			'edit_field_class'   => 'vc_col-sm-12',
			'value'      => array(
				esc_html__( 'Theme defaults', 'grooni-theme-addons' )      => '',
				esc_html__( 'No Repeat', 'grooni-theme-addons' )           => 'no-repeat',
				esc_html__( 'Repeat', 'grooni-theme-addons' )              => 'repeat',
				esc_html__( 'Repeat Horizontally', 'grooni-theme-addons' ) => 'repeat-x',
				esc_html__( 'Repeat Vertically', 'grooni-theme-addons' )   => 'repeat-y',
			),
			'group'      => esc_html__( 'Design Options', 'grooni-theme-addons' ),
			'dependency' => array( 'element' => 'ct_bg_image', 'not_empty' => true ),
		),
		array(
			'heading'    => esc_html__( 'Background Size', 'grooni-theme-addons' ),
			'param_name' => 'ct_bg_size',
			'type'       => 'dropdown',
			'edit_field_class'   => 'vc_col-sm-12',
			'value'      => array(
				esc_html__( 'Theme defaults', 'grooni-theme-addons' ) => '',
				esc_html__( 'Cover', 'grooni-theme-addons' )          => 'cover',
				esc_html__( 'Contain', 'grooni-theme-addons' )        => 'contain',
				esc_html__( 'Initial', 'grooni-theme-addons' )        => 'initial',
			),
			'group'      => esc_html__( 'Design Options', 'grooni-theme-addons' ),
			'dependency' => array( 'element' => 'ct_bg_image', 'not_empty' => true ),
		),
		array(
			'heading'    => esc_html__( 'Background Position', 'grooni-theme-addons' ),
			'param_name' => 'ct_bg_position',
			'type'       => 'dropdown',
			'edit_field_class'   => 'vc_col-sm-12',
			'value'      => array(
				esc_html__( 'Theme defaults', 'grooni-theme-addons' ) => '',
				esc_html__( 'Center Left', 'grooni-theme-addons' )    => 'center left',
				esc_html__( 'Center Center', 'grooni-theme-addons' )  => 'center center',
				esc_html__( 'Center Right', 'grooni-theme-addons' )   => 'center right',
				esc_html__( 'Top Center', 'grooni-theme-addons' )     => 'top center',
				esc_html__( 'Top Left', 'grooni-theme-addons' )       => 'top left',
				esc_html__( 'Top Right', 'grooni-theme-addons' )      => 'top right',
				esc_html__( 'Bottom Center', 'grooni-theme-addons' )  => 'bottom center',
				esc_html__( 'Bottom Left', 'grooni-theme-addons' )    => 'bottom left',
				esc_html__( 'Bottom Right', 'grooni-theme-addons' )   => 'bottom right',
			),
			'group'      => esc_html__( 'Design Options', 'grooni-theme-addons' ),
			'dependency' => array( 'element' => 'ct_bg_image', 'not_empty' => true ),
		),

	)
);


add_filter( 'vc_autocomplete_vc_column_ct_wrap_link_id_callback', 'grooni_find_page_callback', 10, 1 ); // Get suggestion(find)
add_filter( 'vc_autocomplete_vc_column_inner_ct_wrap_link_id_callback', 'grooni_find_page_callback', 10, 1 ); // Get suggestion(find)


/**
 * @param $search_string
 *
 * @return array
 */
function grooni_find_page_callback( $search_string ) {
	$query = $search_string;
	$data  = array();
	$args  = array(
		's'         => $query,
		'post_type' => 'any',
		'suppress_filters' => false
	);

	$args['vc_search_by_title_only'] = true;
	$args['numberposts']             = - 1;
	if ( 0 === strlen( $args['s'] ) ) {
		unset( $args['s'] );
	}

	add_filter( 'posts_search', 'vc_search_by_title_only', 500, 2 );

	$posts = get_posts( $args );

	if ( is_array( $posts ) && ! empty( $posts ) ) {
		foreach ( $posts as $post ) {
			$data[] = array(
				'value' => $post->ID,
				'label' => $post->post_title,
				'group' => $post->post_type,
			);
		}
	}

	return $data;
}


$column_params = array(
	array(
		'type'        => 'checkbox',
		'heading'     => esc_html__( 'Wrap column with a link', 'grooni-theme-addons' ),
		'param_name'  => 'ct_wrap_link',
		'description' => esc_html__( 'If checked, the column will be wrap with a link.', 'grooni-theme-addons' ),
		'value'       => array( __( 'Yes', 'grooni-theme-addons' ) => 'yes' ),
	),
	array(
		'heading'          => esc_html__( 'Link type', 'grooni-theme-addons' ),
		'param_name'       => 'ct_wrap_link_type',
		'type'             => 'dropdown',
		'edit_field_class' => 'vc_col-sm-12',
		'value'            => array(
			esc_html__( 'Custom URL', 'grooni-theme-addons' )             => 'custom',
			esc_html__( 'Select from existing pages', 'grooni-theme-addons' ) => 'id',
		),
		'dependency'       => array( 'element' => 'ct_wrap_link', 'not_empty' => true ),
	),
	array(
		'type'       => 'textarea',
		'heading'    => esc_html__( 'Custom URL', 'grooni-theme-addons' ),
		'param_name' => 'ct_wrap_link_custom',
		'dependency' => array( 'element' => 'ct_wrap_link_type', 'value' => array( 'custom' ), ),
	),
	array(
		'type'        => 'autocomplete',
		'heading'     => esc_html__( 'Page title of existing page', 'grooni-theme-addons' ),
		'param_name'  => 'ct_wrap_link_id',
		'description' => esc_html__( 'Start typing a page title', 'grooni-theme-addons' ),
		'settings'    => array(
			'multiple' => false,
			'sortable' => true,
			'groups'   => true,
		),
		'dependency'  => array( 'element' => 'ct_wrap_link_type', 'value' => array( 'id' ) ),
	),
	array(
		'heading'          => esc_html__( 'Open link in', 'grooni-theme-addons' ),
		'param_name'       => 'ct_wrap_link_target',
		'type'             => 'dropdown',
		'edit_field_class' => 'vc_col-sm-12',
		'value'            => array(
			esc_html__( 'Same page', 'grooni-theme-addons' )             => '',
			esc_html__( 'New page', 'grooni-theme-addons' ) => '_blank',
		),
		'dependency'       => array( 'element' => 'ct_wrap_link', 'not_empty' => true ),
	),
);

vc_add_params( 'vc_column', $column_params);
vc_add_params( 'vc_column_inner', $column_params);
