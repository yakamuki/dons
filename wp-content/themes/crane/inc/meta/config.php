<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );
/**
 * Config file for Posts Meta.
 *
 * @package crane
 */


$default_types = array( 'page', 'post', 'crane_portfolio', 'product' );

// Get footers
$footer_presets = crane_get_footer_presets( array(
	'default' => esc_html__( 'Inherit from Theme Options', 'crane' ),
	'0'       => esc_html__( 'No footer', 'crane' )
) );


Crane_Meta_Data_Settings::addMetaGroup(
	'crane-settings', esc_html__( 'Settings', 'crane' ), array(
		( new Crane_Meta_Data_Field_Checkbox( 'override_global', esc_html__( 'Override global settings', 'crane' ), false ) )
			->setPostType( $default_types )
			->setDescription( esc_html__( 'Override options defined in Theme options', 'crane' ) ),
		( new Crane_Meta_Data_Field_Select( 'breadcrumbs', esc_html__( 'Page title and breadcrumbs appearance', 'crane' ), 'default', array(
			'default'     => esc_html__( 'Default', 'crane' ),
			'none'        => esc_html__( 'None', 'crane' ),
			'title'       => esc_html__( 'Title only', 'crane' ),
			'breadcrumbs' => esc_html__( 'Breadcrumbs only', 'crane' ),
			'both_before' => esc_html__( 'Breadcrums before title', 'crane' ),
			'both_within' => esc_html__( 'Breadcrums within title', 'crane' ),
			'both_after'  => esc_html__( 'Breadcrums after title', 'crane' ),
		) ) )
			->setPostType( $default_types )
			->setConditions( [ 'override_global' => true ] ),
		( new Crane_Meta_Data_Field_Text( 'title', esc_html__( 'Custom page title', 'crane' ), '' ) )
			->setConditions( [
				'override_global' => true,
				'breadcrumbs'     => array(
					'default',
					'both_before',
					'both_within',
					'both_after',
					'title'
				)
			] )
			->setPostType( $default_types )
			->setDescription( esc_html__( 'If empty, the title of the page will be used', 'crane' ) ),
		( new Crane_Meta_Data_Field_Radio( 'show-prev-next-post', esc_html__( 'Show next/prev posts navigation', 'crane' ), 'default',
			array(
				'0'       => esc_html__( 'OFF', 'crane' ),
				'default' => esc_html__( 'Default', 'crane' ),
				'1'       => esc_html__( 'ON', 'crane' )
			) ) )
			->setPostType( [ 'post', 'crane_portfolio' ] )
			->setConditions( [ 'override_global' => true, ] ),
		( new Crane_Meta_Data_Field_Select( 'footer_preset_global', esc_html__( 'Footer preset', 'crane' ), 'default', $footer_presets ) )
			->setDescription( sprintf( esc_html__( 'You can %1$s this footer or add a new one on the %2$sFooters%3$s page.', 'crane' ), crane_get_footer_by_option( 'footer_preset_global', 'meta' ), '<a target="_blank" href="' . admin_url() . 'edit.php?post_type=crane_footer">', '</a>' ) )
			->setPostType( $default_types )
			->setConditions( [ 'override_global' => true ] ),
		( new Crane_Meta_Data_Field_Radio( 'footer_appearance', esc_html__( 'Footer appearance', 'crane' ), 'default',
			array(
				'appearance-regular' => esc_html__( 'Regular', 'crane' ),
				'default'            => esc_html__( 'Default', 'crane' ),
				'appearance-fixed'   => esc_html__( 'Fixed', 'crane' )
			)
		) )
			->setPostType( $default_types )
			->setConditions( [ 'override_global' => true ] ),
		( new Crane_Meta_Data_Field_Select( 'single-has-sidebar', esc_html__( 'Sidebar position', 'crane' ), 'default', array(
			'default'  => esc_html__( 'Default sidebar setting', 'crane' ),
			'none'     => esc_html__( 'Hide sidebar', 'crane' ),
			'at-right' => esc_html__( 'At right', 'crane' ),
			'at-left'  => esc_html__( 'At left', 'crane' ),
		) ) )->setPostType( $default_types )
		     ->setConditions( [ 'override_global' => true ] ),
		( new Crane_Meta_Data_Field_Select_Sidebar( 'single-sidebar', esc_html__( 'Select Custom Sidebar', 'crane' ), 'crane_basic_sidebar' ) )
			->setPostType( $default_types )
			->setConditions( [ 'override_global' => true, 'single-has-sidebar' => [ 'at-left', 'at-right' ] ] )
			->setDescription( wp_kses( __( 'Create a new sidebar, you can on the page', 'crane' ) . ' <a href="' . admin_url() . 'widgets.php">' . __( 'Widgets', 'crane' ) . '</a>', array( 'a' => array( 'href' => array() ) ) ) ),
		( new Crane_Meta_Data_Field_Checkbox( 'override_sidebar_content_width', esc_html__( 'Override sidebar and content width', 'crane' ), false ) )
			->setPostType( $default_types )
			->setConditions( [ 'override_global' => true, 'single-has-sidebar' => [ 'at-left', 'at-right' ] ] ),
		( new Crane_Meta_Data_Field_Text( 'single-sidebar-width', esc_html__( 'Sidebar width, %', 'crane' ), '25' ) )
			->setPostType( $default_types )
			->setConditions( [
				'override_global'                => true,
				'override_sidebar_content_width' => true,
				'single-has-sidebar'             => [ 'at-left', 'at-right' ]
			] ),
		( new Crane_Meta_Data_Field_Text( 'single-content-width', esc_html__( 'Page content width, %', 'crane' ), '75' ) )
			->setPostType( $default_types )
			->setConditions( [
				'override_global'                => true,
				'override_sidebar_content_width' => true,
				'single-has-sidebar'             => [ 'at-left', 'at-right' ]
			] ),
	)
);

Crane_Meta_Data_Settings::addMetaGroup(
	'crane-portfolio', esc_html__( 'Portfolio', 'crane' ), array(
		( new Crane_Meta_Data_Field_Select( 'portfolio-type', esc_html__( 'Portfolio Type', 'crane' ), 'top', array(
			'top'    => esc_html__( 'Image at top', 'crane' ),
			'left'   => esc_html__( 'Image at left', 'crane' ),
			'right'  => esc_html__( 'Image at right', 'crane' ),
			'slider' => esc_html__( 'Slider', 'crane' ),
			'grid'   => esc_html__( 'Grid images', 'crane' )
		) ) )->setPostType( 'crane_portfolio' ),
		( new Crane_Meta_Data_Field_Select( 'portfolio-grid-columns', esc_html__( 'Number of columns', 'crane' ), '4', array(
			'1' => '1',
			'2' => '2',
			'3' => '3',
			'4' => '4'
		) ) )->setPostType( 'crane_portfolio' )->setConditions( [ 'portfolio-type' => 'grid' ] ),
		( new Crane_Meta_Data_Field_Media( 'portfolio-image', esc_html__( 'Images', 'crane' ), '' ) )->setPostType( 'crane_portfolio' ),
		( new Crane_Meta_Data_Field_Radio( 'portfolio-show-featured', esc_html__( 'Show Featured Image', 'crane' ), 'default',
			array(
				'0'       => esc_html__( 'OFF', 'crane' ),
				'default' => esc_html__( 'Default', 'crane' ),
				'1'       => esc_html__( 'ON', 'crane' )
			) ) )
			->setPostType( 'crane_portfolio' ),
		( new Crane_Meta_Data_Field_Select( 'portfolio-featured-size', esc_html__( 'Featured Image size', 'crane' ), 'fullscreen', array(
			'default'    => esc_html__( 'Original size', 'crane' ),
			'fullscreen' => esc_html__( 'Full screen', 'crane' ),
			'custom'     => esc_html__( 'Custom', 'crane' ),
			'fullwidth'  => esc_html__( 'Full width + custom height', 'crane' ),
		) ) )->setPostType( 'crane_portfolio' )->setConditions( [ 'portfolio-show-featured' => true ] ),
		( new Crane_Meta_Data_Field_Text( 'portfolio-featured-width', esc_html__( 'Width', 'crane' ), '' ) )
			->setPostType( 'crane_portfolio' )
			->setConditions( [ 'portfolio-featured-size' => 'custom', 'portfolio-show-featured' => true ] ),
		( new Crane_Meta_Data_Field_Text( 'portfolio-featured-height', esc_html__( 'Height', 'crane' ), '' ) )
			->setPostType( 'crane_portfolio' )
			->setConditions( [
				'portfolio-featured-size' => [ 'custom', 'fullwidth' ],
				'portfolio-show-featured' => true
			] ),
		( new Crane_Meta_Data_Field_Checkbox( 'portfolio-show-return', esc_html__( 'Show "return to Portfolio gallery" button', 'crane' ), false ) )
			->setConditions( [ 'portfolio-show-featured' => true ] )
			->setPostType( 'crane_portfolio' ),
		( new Crane_Meta_Data_Field_Text( 'portfolio-return-url', esc_html__( 'URL portfolio gallery', 'crane' ), true ) )
			->setConditions( [ 'portfolio-show-featured' => true, 'portfolio-show-return' => true ] )
			->setPostType( 'crane_portfolio' ),
		( new Crane_Meta_Data_Field_Select( 'portfolio-masonry-size', esc_html__( 'Masonry image size', 'crane' ), 'width1-height1', array(
			'width1-height1' => 'X*X',
			'width2-height1' => '2X*X',
			'width3-height1' => '3X*X',
			'width4-height1' => '4X*X',
			'width1-height2' => 'X*2X',
			'width1-height3' => 'X*3X',
			'width1-height4' => 'X*4X',
			'width2-height2' => '2X*2X',
			'width2-height3' => '2X*3X',
			'width2-height4' => '2X*4X',
			'width3-height2' => '3X*2X',
			'width3-height3' => '3X*3X',
			'width3-height4' => '3X*4X',
			'width4-height2' => '4X*2X',
			'width4-height3' => '4X*3X',
			'width4-height4' => '4X*4X',
		) ) )
			->setPostType( 'crane_portfolio' ),
		( new Crane_Meta_Data_Field_Text( 'portfolio-custom-text', esc_html__( 'Custom text', 'crane' ), '' ) )
			->setPostType( 'crane_portfolio' ),
		( new Crane_Meta_Data_Field_Radio( 'portfolio-single-show-title', esc_html__( 'Show portfolio title', 'crane' ), 'default', array(
			'0'       => esc_html__( 'OFF', 'crane' ),
			'default' => esc_html__( 'Default', 'crane' ),
			'1'       => esc_html__( 'ON', 'crane' )
		) ) )
			->setPostType( 'crane_portfolio' ),
		( new Crane_Meta_Data_Field_Radio( 'portfolio-single-show-border', esc_html__( 'Show border before portfolio meta data', 'crane' ), 'default', array(
			'0'       => esc_html__( 'OFF', 'crane' ),
			'default' => esc_html__( 'Default', 'crane' ),
			'1'       => esc_html__( 'ON', 'crane' )
		) ) )
			->setPostType( 'crane_portfolio' ),
		( new Crane_Meta_Data_Field_Radio( 'portfolio-single-show-tags', esc_html__( 'Show portfolio tags', 'crane' ), 'default', array(
			'0'       => esc_html__( 'OFF', 'crane' ),
			'default' => esc_html__( 'Default', 'crane' ),
			'1'       => esc_html__( 'ON', 'crane' )
		) ) )
			->setPostType( 'crane_portfolio' ),
		( new Crane_Meta_Data_Field_Radio( 'portfolio-single-show-date', esc_html__( 'Show portfolio publication date', 'crane' ), 'default', array(
			'0'       => esc_html__( 'OFF', 'crane' ),
			'default' => esc_html__( 'Default', 'crane' ),
			'1'       => esc_html__( 'ON', 'crane' )
		) ) )
			->setPostType( 'crane_portfolio' ),
		( new Crane_Meta_Data_Field_Radio( 'portfolio-single-show-cats', esc_html__( 'Show portfolio categories', 'crane' ), 'default', array(
			'0'       => esc_html__( 'OFF', 'crane' ),
			'default' => esc_html__( 'Default', 'crane' ),
			'1'       => esc_html__( 'ON', 'crane' )
		) ) )
			->setPostType( 'crane_portfolio' ),
		( new Crane_Meta_Data_Field_Radio( 'portfolio-single-show-share', esc_html__( 'Show social share button', 'crane' ), 'default', array(
			'0'       => esc_html__( 'OFF', 'crane' ),
			'default' => esc_html__( 'Default', 'crane' ),
			'1'       => esc_html__( 'ON', 'crane' )
		) ) )
			->setPostType( 'crane_portfolio' ),
		( new Crane_Meta_Data_Field_Number( 'grooni_custom_order', esc_html__( 'Custom order for displaying in archive/widget', 'crane' ), '' ) )
			->setDescription( esc_html__( 'Set number for custom sorting in widgets. Zero values are not sorting. If the order is empty, then it is considered as 0.', 'crane' ) )
			->setInputAttr( array(
				'step'        => '1',
				'min'         => '0',
				'placeholder' => esc_html__( 'Input number', 'crane' )
			) )
			->SetIndependentMeta( true )
			->setPostType( [ 'crane_portfolio' ] )

	)
);

global $wp_registered_sidebars;

Crane_Meta_Data_Settings::addMetaGroup(
	'crane-post', esc_html__( 'Post settings', 'crane' ), array(
		( new Crane_Meta_Data_Field_Radio( 'post-show-featured', esc_html__( 'Show Featured Image', 'crane' ), 'default',
			array(
				'0'       => esc_html__( 'OFF', 'crane' ),
				'default' => esc_html__( 'Default', 'crane' ),
				'1'       => esc_html__( 'ON', 'crane' )
			) ) )
			->setPostType( [ 'post' ] ),
		( new Crane_Meta_Data_Field_Select( 'post-featured-size', esc_html__( 'Featured Image size', 'crane' ), 'fullscreen', array(
			'fullscreen' => esc_html__( 'Full screen', 'crane' ),
			'fullwidth'  => esc_html__( 'Full width + custom height', 'crane' ),
			'default'    => esc_html__( 'Original size', 'crane' ),
			'custom'     => esc_html__( 'Custom', 'crane' ),
		) ) )->setPostType( 'post' )->setConditions( [ 'post-show-featured' => true ] ),
		( new Crane_Meta_Data_Field_Text( 'post-featured-width', esc_html__( 'Width', 'crane' ), '' ) )
			->setPostType( 'post' )
			->setConditions( [ 'post-featured-size' => 'custom', 'post-show-featured' => '1' ] ),
		( new Crane_Meta_Data_Field_Text( 'post-featured-height', esc_html__( 'Height', 'crane' ), '' ) )
			->setPostType( 'post' )
			->setConditions( [ 'post-featured-size' => [ 'custom', 'fullwidth' ], 'post-show-featured' => '1' ] ),
		( new Crane_Meta_Data_Field_Radio( 'post-show-content-title', esc_html__( 'Show post title in content?', 'crane' ), 'default',
			array(
				'0'       => esc_html__( 'OFF', 'crane' ),
				'default' => esc_html__( 'Default', 'crane' ),
				'1'       => esc_html__( 'ON', 'crane' )
			) ) )
			->setPostType( [ 'post' ] ),
		( new Crane_Meta_Data_Field_Radio( 'post-show-meta-in-featured', esc_html__( 'Show meta info inside featured image block?', 'crane' ), 'default',
			array(
				'0'       => esc_html__( 'OFF', 'crane' ),
				'default' => esc_html__( 'Default', 'crane' ),
				'1'       => esc_html__( 'ON', 'crane' )
			) ) )
			->setPostType( [ 'post' ] ),
		( new Crane_Meta_Data_Field_Radio( 'show-author-info', esc_html__( 'Show author info block', 'crane' ), 'default',
			array(
				'0'       => esc_html__( 'OFF', 'crane' ),
				'default' => esc_html__( 'Default', 'crane' ),
				'1'       => esc_html__( 'ON', 'crane' )
			) ) )
			->setPostType( [ 'post' ] ),
		( new Crane_Meta_Data_Field_Radio( 'show-related-posts', esc_html__( 'Show related posts block', 'crane' ), 'default',
			array(
				'0'       => esc_html__( 'OFF', 'crane' ),
				'default' => esc_html__( 'Default', 'crane' ),
				'1'       => esc_html__( 'ON', 'crane' )
			) ) )
			->setPostType( [ 'post' ] ),
		( new Crane_Meta_Data_Field_Number( 'grooni_custom_order', esc_html__( 'Custom order for displaying in archive/widget', 'crane' ), '' ) )
			->setDescription( esc_html__( 'Set number for custom sorting in widgets. Zero values are not sorting. If the order is empty, then it is considered as 0.', 'crane' ) )
			->setInputAttr( array(
				'step'        => '1',
				'min'         => '0',
				'placeholder' => esc_html__( 'Input number', 'crane' )
			) )
			->SetIndependentMeta( true )
			->setPostType( [ 'post' ] )
	)
);


Crane_Meta_Data_Settings::addMetaGroup(
	'crane-product', esc_html__( 'Product settings', 'crane' ), array(
		( new Crane_Meta_Data_Field_Radio( 'product-related', esc_html__( 'Show related products', 'crane' ), 'default',
			array(
				'0'       => esc_html__( 'OFF', 'crane' ),
				'default' => esc_html__( 'Default', 'crane' ),
				'1'       => esc_html__( 'ON', 'crane' )
			) ) )
			->setPostType( [ 'product' ] ),
	)
);
