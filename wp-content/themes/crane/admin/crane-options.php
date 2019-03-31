<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * For full documentation, please visit: http://docs.reduxframework.com/
 * For a more extensive sample-config file, you may look at:
 * https://github.com/reduxframework/redux-framework/blob/master/sample/sample-config.php
 */

if ( ! class_exists( 'Redux' ) ) {
	add_action( 'wp_enqueue_scripts', 'crane_add_default_fonts' );
	crane_set_default_options();

	return;
}

// This is your option name where all the Redux data is stored.
$crane_opt_name = 'crane_options';


// Function to test the compiler hook and demo CSS output.
// Above 10 is a priority, but 2 in necessary to include the dynamically generated CSS to be sent to the function.
add_action( 'redux/options/' . $crane_opt_name . '/compiler', 'crane_redux_compiler_action', 10, 3 );


/**
 * ---> SET ARGUMENTS
 * All the possible arguments for Redux.
 * For full documentation on arguments, please refer to: https://github.com/ReduxFramework/ReduxFramework/wiki/Arguments
 * */

$theme_data = wp_get_theme(); // For use with some settings. Not necessary.

$opt_args = array(
	'disable_tracking'          => true,
	// TYPICAL -> Change these values as you need/desire
	'opt_name'                  => $crane_opt_name,
	// This is where your data is stored in the database and also becomes your global variable name.
	'display_name'              => 'crane-theme-options',
	// Name that appears at the top of your panel
	'display_version'           => $theme_data->get( 'Version' ),
	// Version that appears at the top of your panel
	'menu_type'                 => 'submenu',
	//Specify if the admin menu should appear or not. Options: menu or submenu (Under appearance only)
	'allow_sub_menu'            => true,
	// Show the sections below the admin menu item or not
	'menu_title'                => esc_html__( 'Theme Options', 'crane' ),
	'page_title'                => esc_html__( 'Theme Options', 'crane' ),
	// You will need to generate a Google API key to use this feature.
	// Please visit: https://developers.google.com/fonts/docs/developer_api#Auth
	'google_api_key'            => '',
	// Set it you want google fonts to update weekly. A google_api_key value is required.
	'google_update_weekly'      => false,
	// Must be defined to add google fonts to the typography module
	'async_typography'          => false,
	// Use a asynchronous font on the front end or font string
	'disable_google_fonts_link' => false,
	// Disable this in case you want to create your own google fonts loader
	'admin_bar'                 => true,
	// Show the panel pages on the admin bar
	'admin_bar_icon'            => 'admin-bar-crane-icon',
	// Choose an icon for the admin bar menu
	'admin_bar_priority'        => 100000,
	// Choose an priority for the admin bar menu
	'global_variable'           => '',
	// Set a different name for your global variable other than the opt_name
	'dev_mode'                  => false,
	// Show the time the page took to load, etc
	'update_notice'             => true,
	// If dev_mode is enabled, will notify developer of updated versions available in the GitHub Repo
	'customizer'                => true,
	// Enable basic customizer support

	// OPTIONAL -> Give you extra features
	'page_priority'             => 90,
	// Order where the menu appears in the admin area. If there is any conflict, something will not show. Warning.
	'page_parent'               => function_exists( 'grooni_add_subpage_to_dashboard' ) ? 'crane-theme-dashboard' : 'themes.php',
	// For a full list of options, visit: http://codex.wordpress.org/Function_Reference/add_submenu_page#Parameters
	'page_permissions'          => 'manage_options',
	// Permissions needed to access the options panel.
	'menu_icon'                 => get_template_directory_uri() . '/assets/images/wp/groovy-theme-opts-icon.svg',
	// Specify a custom URL to an icon
	'last_tab'                  => '',
	// Force your panel to always open to a specific tab (by id)
	'page_icon'                 => 'icon-themes',
	// Icon displayed in the admin panel next to your menu_title
	'page_slug'                 => '',
	// Page slug used to denote the panel, will be based off page title then menu title then opt_name if not provided
	'save_defaults'             => true,
	// On load save the defaults to DB before user clicks save or not
	'default_show'              => true,
	// If true, shows the default value next to each field that is not the default value.
	'default_mark'              => '',
	// What to print by the field's title if the value shown is default. Suggested: *
	'show_import_export'        => false,
	// Shows the Import/Export panel when not used as a field.

	// CAREFUL -> These options are for advanced use only
	'transient_time'            => 60 * MINUTE_IN_SECONDS,
	'output'                    => true,
	// Global shut-off for dynamic CSS output by the framework. Will also disable google fonts output
	'output_tag'                => true,
	// Allows dynamic CSS to be generated for customizer and google fonts, but stops the dynamic CSS from going to the head
	//'footer_credit'        => '',
	// Disable the footer credit of Redux. Please leave if you can help it.

	// FUTURE -> Not in use yet, but reserved or partially implemented. Use at your own risk.
	'database'                  => '',
	// possible: options, theme_mods, theme_mods_expanded, transient. Not fully functional, warning!
	'system_info'               => false,
	// REMOVE
	'templates_path'            => get_parent_theme_file_path( 'admin/redux-templates/' ),
	//'compiler'             => true,

	// HINTS
	'hints'                     => array(
		'icon'          => 'el el-question-sign',
		'icon_position' => 'right',
		'icon_color'    => 'lightgray',
		'icon_size'     => 'large',
		'tip_style'     => array(
			'color'   => 'light',
			'shadow'  => true,
			'rounded' => true,
			'style'   => '',
		),
		'tip_position'  => array(
			'my' => 'top right',
			'at' => 'bottom right',
		),
		'tip_effect'    => array(
			'show' => array(
				'effect'   => 'fade',
				'duration' => 200,
				'event'    => 'mouseover',
			),
			'hide' => array(
				'effect'   => 'fade',
				'duration' => 200,
				'event'    => 'click mouseleave',
			),
		),
	),
	'show_options_object'       => false,
);


if ( '0' === Redux::getOption( $crane_opt_name, 'privacy-google_fonts' ) ) {
	$opt_args['disable_google_fonts_link'] = true;
}


Redux::setArgs( $crane_opt_name, $opt_args );

/*
 * END ARGUMENTS
 */


/*
 * START SECTIONS
 */
$upload_dir_data = wp_upload_dir();
$basedir         = get_template_directory();
if ( isset( $upload_dir_data['basedir'] ) ) {
	$basedir = $upload_dir_data['basedir'];
}
$authors = array();
foreach ( get_users() as $user ) {
	/**
	 * @var $user WP_User
	 */
	$authors[ $user->ID ] = $user->display_name;
}

// Get footers
$footer_presets = crane_get_footer_presets( array(
	'default' => esc_html__( 'Inherit from Default footer', 'crane' ),
	'0'       => esc_html__( 'No footer', 'crane' ),
) );

$footer_presets_global = $footer_presets;
unset( $footer_presets_global['default'] );

// START Options Fields

Redux::setSection( $crane_opt_name, array(
	'title'  => esc_html__( 'General settings', 'crane' ),
	'id'     => 'general-settings',
	'icon'   => 'fa fa-cog',
	'fields' => array(
		array(
			'id'   => 'logo-info',
			'type' => 'info',
			'desc' => ( function_exists( 'groovyMenu' ) ) ? wp_kses( __( 'Logo configuration can be found in section "<a href="?page=groovy_menu_settings">Groovy menu</a>"', 'crane' ), array( 'a' => array( 'href' => array() ) ) ) : '',
		),
		array(
			'id'      => 'favicon',
			'type'    => 'media',
			'title'   => esc_html__( 'Site icon', 'crane' ),
			'default' => get_option( 'site_icon' ) ? : '',
		),
		array(
			'id'       => 'wide-layout',
			'type'     => 'switch',
			'title'    => esc_html__( 'Wide layout', 'crane' ),
			'hint'     => array(
				'content' => esc_html__( "If set to 'on' (wide), container will be 100% width of browser window. If set to off (boxed), container will have max-width set in setting above", 'crane' )
			),
			'default'  => false,
			'compiler' => true,
		),
		array(
			'title'         => esc_html__( 'Right/left padding', 'crane' ),
			'id'            => 'wide-layout-padding',
			'type'          => 'spacing',
			'units'         => 'px',
			'display_units' => false,
			'top'           => false,
			'bottom'        => false,
			'compiler'      => array(
				'
                .crane-desktop .crane-container,
                .crane-desktop .crane-container .crane-container
            '
			),
			'default'       => array(
				'padding-left'  => '15px',
				'padding-right' => '15px'
			),
			'required'      => array( 'wide-layout', '=', true )
		),
		array(
			'id'       => 'main-grid-width',
			'type'     => 'slider',
			'title'    => esc_html__( 'Content width', 'crane' ),
			'hint'     => array(
				'content' => esc_html__( "This option defines the content max-width between 600px and 2000px", 'crane' )
			),
			'default'  => 1200,
			'min'      => 600,
			'max'      => 2000,
			'required' => array( 'wide-layout', 'equals', false ),
			'compiler' => true,
		),
		array(
			'id'      => 'show-back-to-top',
			'type'    => 'switch',
			'title'   => esc_html__( 'Scroll to top button', 'crane' ),
			'default' => true
		),
		array(
			'id'      => 'show_featured_placeholders',
			'type'    => 'switch',
			'title'   => esc_html__( "Show placeholders if featured image doesn't set", 'crane' ),
			'hint'    => array(
				'content' => esc_html__( "Show placeholders if featured image doesn't set in following places: blog type masonry and cell, portfolio all layout types, Grooni recent posts widget", 'crane' )
			),
			'default' => false
		),
		array(
			'id'      => 'preloader',
			'type'    => 'switch',
			'title'   => esc_html__( 'Preloader', 'crane' ),
			'default' => true
		),
		array(
			'id'       => 'preloader-type',
			'type'     => 'select',
			'title'    => esc_html__( 'Preloader type', 'crane' ),
			'options'  => array(
				'ball-pulse'                 => esc_html__( 'ball pulse', 'crane' ),
				'ball-grid-pulse'            => esc_html__( 'ball grid pulse', 'crane' ),
				'ball-clip-rotate'           => esc_html__( 'ball clip rotate', 'crane' ),
				'ball-clip-rotate-pulse'     => esc_html__( 'ball clip rotate pulse', 'crane' ),
				'square-spin'                => esc_html__( 'square spin', 'crane' ),
				'ball-clip-rotate-multiple'  => esc_html__( 'ball clip rotate multiple', 'crane' ),
				'ball-pulse-rise'            => esc_html__( 'ball pulse rise', 'crane' ),
				'ball-rotate'                => esc_html__( 'ball rotate', 'crane' ),
				'ball-zig-zag'               => esc_html__( 'ball zig-zag', 'crane' ),
				'ball-triangle-path'         => esc_html__( 'ball triangle path', 'crane' ),
				'ball-scale'                 => esc_html__( 'ball scale', 'crane' ),
				'line-scale'                 => esc_html__( 'line scale', 'crane' ),
				'ball-scale-multiple'        => esc_html__( 'ball scale multiple', 'crane' ),
				'ball-pulse-sync'            => esc_html__( 'ball pulse sync', 'crane' ),
				'ball-scale-ripple'          => esc_html__( 'ball scale ripple', 'crane' ),
				'ball-scale-ripple-multiple' => esc_html__( 'ball scale ripple multiple', 'crane' ),
				'ball-spin-fade-loader'      => esc_html__( 'ball spin fade loader', 'crane' ),
				'line-spin-fade-loader'      => esc_html__( 'line spin fade loader', 'crane' ),
				'pacman'                     => esc_html__( 'pacman', 'crane' ),
			),
			'default'  => 'ball-pulse',
			'required' => array( 'preloader', 'equals', true )
		),
		array(
			'id'          => 'preloader-color',
			'type'        => 'color',
			'transparent' => false,
			'title'       => esc_html__( 'Preloader color', 'crane' ),
			'default'     => '#93cb52',
			'required'    => array( 'preloader', 'equals', true ),
			'compiler'    => true,
		),
		array(
			'id'          => 'preloader-bg-color',
			'type'        => 'color',
			'transparent' => false,
			'title'       => esc_html__( 'Preloader background color', 'crane' ),
			'default'     => '#ffffff',
			'required'    => array( 'preloader', 'equals', true ),
			'compiler'    => true,
		),
		array(
			'id'      => 'lazyload',
			'type'    => 'switch',
			'title'   => esc_html__( 'Lazy Load', 'crane' ),
			'default' => false
		),
	)
) );


if ( ! function_exists( 'groovyMenu' ) ) {

	Redux::setSection( $crane_opt_name, array(
		'title'  => esc_html__( 'Menu', 'crane' ),
		'id'     => 'menu_section',
		'icon'   => 'fa fa-bars',
		'fields' => array(
			array(
				'id'      => 'header_logo_switcher',
				'type'    => 'switch',
				'title'   => esc_html__( 'Show image logo at header?', 'crane' ),
				'default' => false,
			),
			array(
				'id'       => 'header_logo_image',
				'type'     => 'media',
				'title'    => esc_html__( 'Image logo', 'crane' ),
				'default'  => '',
				'required' => array( 'header_logo_switcher', 'equals', true )
			),
			array(
				'id'       => 'header_logo_text',
				'type'     => 'text',
				'title'    => esc_html__( 'Text logo', 'crane' ),
				'desc'     => esc_html__( 'If left blank site title will be shown', 'crane' ),
				'default'  => '',
				'required' => array( 'header_logo_switcher', 'equals', false )
			),
		)
	) );

}

Redux::setSection( $crane_opt_name, array(
	'title' => esc_html__( 'Page title and breadcrumbs', 'crane' ),
	'id'    => 'page-title-and-breadcrumbs-section',
	'icon'  => 'fa fa-paper-plane'
) );

Redux::setSection( $crane_opt_name, array(
	'title'      => esc_html__( 'Page title settings', 'crane' ),
	'id'         => 'page-title',
	'subsection' => true,
	'fields'     => array(
		array(
			'id'       => 'page-title-dimensions',
			'type'     => 'dimensions',
			'title'    => esc_html__( 'Minimum height of title container', 'crane' ),
			'hint'     => array(
				'content' => esc_html__( "If content within page title will take more than minimum height container will expand it's height",
					'crane' )
			),
			'compiler' => array( '.crane-page-title' ),
			'width'    => false,
			'units'    => array( 'px' ),
			'default'  => array(
				'height' => '200px',
				'units'  => 'px'
			)
		),
		array(
			'id'             => 'page-title-typography',
			'type'           => 'typography',
			'title'          => esc_html__( 'Typography', 'crane' ),
			'compiler'       => array( '.crane-page-title-heading, .crane-page-title-holder ' ),
			'google'         => true,
			'line-height'    => false,
			'text-transform' => true,
			'all_styles'     => true,
			'font-backup'    => false,
			'text-align'     => false,
			'units'          => 'px',
			'default'        => array(
				'color'          => '#000000',
				'font-family'    => 'Open Sans',
				'font-weight'    => '400',
				'text-transform' => 'none',
				'font-size'      => '37px',
				'google'         => true
			),
		),
		array(
			'id'          => 'page-title-background',
			'type'        => 'background',
			'title'       => esc_html__( 'Background', 'crane' ),
			'transparent' => false,
			'compiler'    => array( '.crane-page-title' ),
			'default'     => array(
				'background-color' => '#f9f9f9',
			)
		),
		array(
			'id'       => 'page-title-line-decorators-switch',
			'type'     => 'switch',
			'title'    => esc_html__( 'Show decorators', 'crane' ),
			'default'  => true,
			'compiler' => true,
		),
		array(
			'id'       => 'page-title-border',
			'type'     => 'border',
			'title'    => esc_html__( 'Bottom Border Option', 'crane' ),
			'compiler' => array( '.crane-page-title' ),
			'all'      => false,
			'top'      => false,
			'left'     => false,
			'right'    => false,
			'default'  => array(
				'border-color'  => '#eaeaea',
				'border-style'  => 'solid',
				'border-bottom' => '1px',
			)
		),
	)
) );

Redux::setSection( $crane_opt_name, array(
	'title'      => esc_html__( 'Breadcrumbs settings', 'crane' ),
	'id'         => 'page-breadcrumbs',
	'subsection' => true,
	'fields'     => array(
		array(
			'id'      => 'breadcrumbs-text',
			'type'    => 'text',
			'title'   => esc_html__( 'Text following breadcrumbs', 'crane' ),
			'default' => '',
		),
		array(
			'id'             => 'page-breadcrumbs-typography',
			'type'           => 'typography',
			'title'          => esc_html__( 'Typography', 'crane' ),
			'compiler'       => array( '.crane-breadcrumb' ),
			'google'         => true,
			'line-height'    => false,
			'text-align'     => false,
			'text-transform' => true,
			'all_styles'     => true,
			'font-backup'    => false,
			'units'          => 'px',
			'default'        => array(
				'color'          => '#4d4d4d',
				'font-family'    => 'Open Sans',
				'font-weight'    => '600',
				'text-transform' => 'uppercase',
				'font-size'      => '12px',
				'google'         => true
			),
		),
		array(
			'id'          => 'page-breadcrumbs-delimiter-color',
			'type'        => 'color',
			'title'       => esc_html__( 'Delimiter color', 'crane' ),
			'hint'        => array(
				'content' => esc_html__( "Backslash color between breadcrumbs", 'crane' )
			),
			'transparent' => false,
			'compiler'    => array( '.crane-breadcrumb-nav__item+.crane-breadcrumb-nav__item::before' ),
			'default'     => '#b9b9b9'
		),
	)
) );

Redux::setSection( $crane_opt_name, array(
	'title'      => esc_html__( 'Page title and breadcrumbs appearance', 'crane' ),
	'id'         => 'page-title-and-breadcrumbs-general',
	'subsection' => true,
	'fields'     => array(
		array(
			'id'      => 'breadcrumbs-regular',
			'type'    => 'select',
			'title'   => esc_html__( 'Regular pages', 'crane' ),
			'options' => array(
				'none'        => esc_html__( 'None', 'crane' ),
				'title'       => esc_html__( 'Title only', 'crane' ),
				'breadcrumbs' => esc_html__( 'Breadcrumbs only', 'crane' ),
				'both_before' => esc_html__( 'Breadcrums before title', 'crane' ),
				'both_within' => esc_html__( 'Breadcrums within title', 'crane' ),
				'both_after'  => esc_html__( 'Breadcrums after title', 'crane' ),
			),
			'default' => 'regular'
		),
		array(
			'id'   => 'divider_breadcrumbs_options1',
			'type' => 'divide'
		),
		array(
			'id'      => 'breadcrumbs-portfolio',
			'type'    => 'select',
			'title'   => esc_html__( 'Portfolio archive pages', 'crane' ),
			'options' => array(
				'none'        => esc_html__( 'None', 'crane' ),
				'title'       => esc_html__( 'Title only', 'crane' ),
				'breadcrumbs' => esc_html__( 'Breadcrumbs only', 'crane' ),
				'both_before' => esc_html__( 'Breadcrums before title', 'crane' ),
				'both_within' => esc_html__( 'Breadcrums within title', 'crane' ),
				'both_after'  => esc_html__( 'Breadcrums after title', 'crane' ),
			),
			'default' => 'breadcrumbs'
		),
		array(
			'id'      => 'breadcrumbs-portfolio-single',
			'type'    => 'select',
			'title'   => esc_html__( 'Portfolio single pages', 'crane' ),
			'options' => array(
				'inherit'     => esc_html__( 'Inherit from portfolio archive', 'crane' ),
				'none'        => esc_html__( 'None', 'crane' ),
				'title'       => esc_html__( 'Title only', 'crane' ),
				'breadcrumbs' => esc_html__( 'Breadcrumbs only', 'crane' ),
				'both_before' => esc_html__( 'Breadcrums before title', 'crane' ),
				'both_within' => esc_html__( 'Breadcrums within title', 'crane' ),
				'both_after'  => esc_html__( 'Breadcrums after title', 'crane' ),
			),
			'default' => 'inherit'
		),
		array(
			'id'   => 'divider_breadcrumbs_options2',
			'type' => 'divide'
		),
		array(
			'id'      => 'breadcrumbs-blog',
			'type'    => 'select',
			'title'   => esc_html__( 'Blog archive pages', 'crane' ),
			'options' => array(
				'none'        => esc_html__( 'None', 'crane' ),
				'title'       => esc_html__( 'Title only', 'crane' ),
				'breadcrumbs' => esc_html__( 'Breadcrumbs only', 'crane' ),
				'both_before' => esc_html__( 'Breadcrums before title', 'crane' ),
				'both_within' => esc_html__( 'Breadcrums within title', 'crane' ),
				'both_after'  => esc_html__( 'Breadcrums after title', 'crane' ),
			),
			'default' => 'both_within'
		),
		array(
			'id'      => 'breadcrumbs-blog-single',
			'type'    => 'select',
			'title'   => esc_html__( 'Blog single pages', 'crane' ),
			'options' => array(
				'inherit'     => esc_html__( 'Inherit from blog archive', 'crane' ),
				'none'        => esc_html__( 'None', 'crane' ),
				'title'       => esc_html__( 'Title only', 'crane' ),
				'breadcrumbs' => esc_html__( 'Breadcrumbs only', 'crane' ),
				'both_before' => esc_html__( 'Breadcrums before title', 'crane' ),
				'both_within' => esc_html__( 'Breadcrums within title', 'crane' ),
				'both_after'  => esc_html__( 'Breadcrums after title', 'crane' ),
			),
			'default' => 'inherit'
		),
		array(
			'id'   => 'divider_breadcrumbs_options3',
			'type' => 'divide'
		),
		array(
			'id'      => 'breadcrumbs-shop',
			'type'    => 'select',
			'title'   => esc_html__( 'Shop archive pages', 'crane' ),
			'options' => array(
				'none'        => esc_html__( 'None', 'crane' ),
				'title'       => esc_html__( 'Title only', 'crane' ),
				'breadcrumbs' => esc_html__( 'Breadcrumbs only', 'crane' ),
				'both_before' => esc_html__( 'Breadcrums before title', 'crane' ),
				'both_within' => esc_html__( 'Breadcrums within title', 'crane' ),
				'both_after'  => esc_html__( 'Breadcrums after title', 'crane' ),
			),
			'default' => 'breadcrumbs'
		),
		array(
			'id'      => 'breadcrumbs-shop-single',
			'type'    => 'select',
			'title'   => esc_html__( 'Shop single pages', 'crane' ),
			'options' => array(
				'inherit'     => esc_html__( 'Inherit from shop archive', 'crane' ),
				'none'        => esc_html__( 'None', 'crane' ),
				'title'       => esc_html__( 'Title only', 'crane' ),
				'breadcrumbs' => esc_html__( 'Breadcrumbs only', 'crane' ),
				'both_before' => esc_html__( 'Breadcrums before title', 'crane' ),
				'both_within' => esc_html__( 'Breadcrums within title', 'crane' ),
				'both_after'  => esc_html__( 'Breadcrums after title', 'crane' ),
			),
			'default' => 'inherit'
		),
	)
) );

Redux::setSection( $crane_opt_name, array(
	'id'     => 'crane-typography',
	'title'  => esc_html__( 'Typography', 'crane' ),
	'icon'   => 'fa fa-text-height',
	'fields' => array(
		array(
			'id'             => 'regular-txt-typography',
			'type'           => 'typography',
			'title'          => esc_html__( 'Regular text', 'crane' ),
			'hint'           => array(
				'content' => esc_html__( "Typography setting for regular text, links, list etc.",
					'crane' )
			),
			'compiler'       => array( 'body' ),
			'google'         => true,
			'line-height'    => false,
			'text-transform' => true,
			'all_styles'     => true,
			'units'          => 'px',
			'color'          => false,
			'text-align'     => false,
			'default'        => array(
				'font-family'    => 'Open Sans',
				'font-weight'    => '400',
				'text-transform' => 'initial',
				'font-size'      => '14px',
				'google'         => true
			),
		),
		array(
			'type' => 'divide',
			'id'   => 'div2'
		),
		array(
			'id'             => 'h1-typography',
			'type'           => 'typography',
			'title'          => esc_html__( 'Heading H1', 'crane' ),
			'compiler'       => array( 'h1' ),
			'google'         => true,
			'line-height'    => false,
			'text-transform' => true,
			'all_styles'     => true,
			'units'          => 'px',
			'color'          => false,
			'text-align'     => false,
			'default'        => array(
				'font-family'    => 'Open Sans',
				'font-weight'    => '700',
				'text-transform' => 'initial',
				'font-size'      => '34px',
				'google'         => true
			),
		),
		array(
			'type' => 'divide',
			'id'   => 'div3'
		),
		array(
			'id'             => 'h2-typography',
			'type'           => 'typography',
			'title'          => esc_html__( 'Heading H2', 'crane' ),
			'compiler'       => array( 'h2' ),
			'google'         => true,
			'line-height'    => false,
			'text-transform' => true,
			'all_styles'     => true,
			'units'          => 'px',
			'color'          => false,
			'text-align'     => false,
			'default'        => array(
				'font-family'    => 'Open Sans',
				'font-weight'    => '700',
				'text-transform' => 'initial',
				'font-size'      => '31px',
				'google'         => true
			),
		),
		array(
			'type' => 'divide',
			'id'   => 'div4'
		),
		array(
			'id'             => 'h3-typography',
			'type'           => 'typography',
			'title'          => esc_html__( 'Heading H3', 'crane' ),
			'compiler'       => array( 'h3' ),
			'google'         => true,
			'line-height'    => false,
			'text-transform' => true,
			'all_styles'     => true,
			'units'          => 'px',
			'color'          => false,
			'text-align'     => false,
			'default'        => array(
				'font-family'    => 'Open Sans',
				'font-weight'    => '700',
				'text-transform' => 'initial',
				'font-size'      => '23px',
				'google'         => true
			),
		),
		array(
			'type' => 'divide',
			'id'   => 'div5'
		),
		array(
			'id'             => 'h4-typography',
			'type'           => 'typography',
			'title'          => esc_html__( 'Heading H4', 'crane' ),
			'compiler'       => array( 'h4' ),
			'google'         => true,
			'line-height'    => false,
			'text-transform' => true,
			'all_styles'     => true,
			'units'          => 'px',
			'color'          => false,
			'text-align'     => false,
			'default'        => array(
				'font-family'    => 'Open Sans',
				'font-weight'    => '700',
				'text-transform' => 'initial',
				'font-size'      => '20px',
				'google'         => true
			),
		),
		array(
			'type' => 'divide',
			'id'   => 'div6'
		),
		array(
			'id'             => 'h5-typography',
			'type'           => 'typography',
			'title'          => esc_html__( 'Heading H5', 'crane' ),
			'compiler'       => array( 'h5' ),
			'google'         => true,
			'line-height'    => false,
			'text-transform' => true,
			'all_styles'     => true,
			'units'          => 'px',
			'color'          => false,
			'text-align'     => false,
			'default'        => array(
				'font-family'    => 'Open Sans',
				'font-weight'    => '700',
				'text-transform' => 'initial',
				'font-size'      => '17px',
				'google'         => true
			),
		),
		array(
			'type' => 'divide',
			'id'   => 'div7'
		),
		array(
			'id'             => 'h6-typography',
			'type'           => 'typography',
			'title'          => esc_html__( 'Heading H6', 'crane' ),
			'compiler'       => array( 'h6' ),
			'google'         => true,
			'line-height'    => false,
			'text-transform' => true,
			'all_styles'     => true,
			'units'          => 'px',
			'color'          => false,
			'text-align'     => false,
			'default'        => array(
				'font-family'    => 'Open Sans',
				'font-weight'    => '700',
				'text-transform' => 'initial',
				'font-size'      => '15px',
				'google'         => true
			),
		),
	)
) );

Redux::setSection( $crane_opt_name, array(
	'title'  => esc_html__( 'Color styles', 'crane' ),
	'id'     => 'general-colors',
	'icon'   => 'fa fa-eyedropper',
	'fields' => array(
		array(
			'id'          => 'primary-color',
			'type'        => 'color',
			'transparent' => false,
			'title'       => esc_html__( 'Primary color', 'crane' ),
			'compiler'    => array(
				'color'             => '.woocommerce-tabs .tabs>li.active,
				                        .woocommerce .products .price,
				                        .crane-portfolio-style-modern .portfolio-filters-btn.active,
				                        .comment-metadata .comment-author',
				'border-color'      => '
				    .crane-portfolio-style-flat .portfolio-filters-btn.active,
						.crane-portfolio-style-minimal .portfolio-filters-btn.active,
						blockquote
				',
				'border-top-color'  => '.woocommerce-tabs .tabs>li.active::after',
				'border-left-color' => 'blockquote:not(.crane-blockquote-main):not(.wp-block-pullquote)',
				'background-color'  => ' 
										button,
										.button,
										.wp-block-button__link,
										input[type="submit"],
										.comment-button-group a:hover,
										.btn,
										.select2-container--default .select2-results__option--highlighted[aria-selected],
										.select2-results .select2-highlighted,
										.dark-btn:hover,
										.primary-btn,
										input[type="button"]:hover,
										input[type="reset"]:hover,
										input[type="submit"]:hover,
										.woocommerce span.onsale,
										.woocommerce-tabs .tabs>li.active::before,
										.woocommerce .add_to_cart:hover,
										.ui-slider-horizontal .ui-slider-range,
										.carousel .x,
										.carousel .y,
										.page-numbers:not(.dots):hover,
										.navigation .nav-previous a:hover,
										.navigation .nav-next a:hover,
										.page-numbers.current,
										.blog-inner .page-links > span,
										.format-quote .crane-blog-header,
										.post__blockquote .crane-blockquote-main,
										.crane-blog-layout-cell .crane-blog-grid-meta .crane-blog-grid-meta__title::after,
										.crane-search-title::after,
										.product-card__tabs__nav__item.active > .product-card__tabs__nav__item__link::before,
										.crane-info-box,
										.footer-type-dark .widget-title::after,
										.instagram-pics li a::before,
										.footer-type-light .widget-title::after'
			),
			'default'     => '#93cb52'
		),
		array(
			'id'          => 'secondary-color',
			'type'        => 'color',
			'transparent' => false,
			'title'       => esc_html__( 'Secondary color', 'crane' ),
			'compiler'    => array(
				'
			    .woocommerce .star-rating span,
			    .woocommerce p.stars a:hover
			'
			),
			'default'     => '#fab710'
		),
		array(
			'id'                    => 'background-color',
			'type'                  => 'background',
			'background-color'      => true,
			'background-repeat'     => false,
			'background-attachment' => false,
			'background-position'   => false,
			'background-image'      => false,
			'background-size'       => false,
			'preview'               => false,
			'transparent'           => false,
			'title'                 => esc_html__( 'Background color', 'crane' ),
			'compiler'              => array(
				'
			    .crane-content
			'
			),
			'default'               => array(
				'background-color' => '#fff',
			)
		),
		array(
			'id'                    => 'alt-background-color',
			'type'                  => 'background',
			'background-color'      => true,
			'background-repeat'     => false,
			'background-attachment' => false,
			'background-position'   => false,
			'background-image'      => false,
			'background-size'       => false,
			'preview'               => false,
			'transparent'           => false,
			'title'                 => esc_html__( 'Alternative background color', 'crane' ),
			'compiler'              => array(
				'
					.crane-content > .crane-breadcrumb,
					.search-results article,
					.select2-results,
					code,
					pre:not(.wp-block-verse)
			'
			),
			'default'               => array(
				'background-color' => '#fbfbfb',
			),
		),
		array(
			'id'          => 'heading-color',
			'type'        => 'color',
			'transparent' => false,
			'title'       => esc_html__( 'Heading color', 'crane' ),
			'compiler'    => array( 'h1, h2, h3, h4, h5 ,h6' ),
			'default'     => '#686868'
		),
		array(
			'id'          => 'regular-txt-color',
			'title'       => esc_html__( 'Regular text color', 'crane' ),
			'type'        => 'color',
			'transparent' => false,
			'compiler'    => array( 'body' ),
			'default'     => '#686868'
		),
		array(
			'id'       => 'opt-link-color',
			'type'     => 'link_color',
			'title'    => esc_html__( 'Link color', 'crane' ),
			'visited'  => false,
			'compiler' => array( 'a' ),
			'default'  => array(
				'regular' => '#85bf43',
				'hover'   => '#6eb238',
				'active'  => '#85bf43'
			)
		),
		array(
			'id'          => 'border-color',
			'type'        => 'color',
			'transparent' => false,
			'title'       => esc_html__( 'Border color', 'crane' ),
			'compiler'    => array(
				'border-color'     => '
				          .crane-content > .crane-breadcrumb,
									input[type="text"],
									input[type="password"],
									input[type="email"],
									input[type="url"],
									input[type="tel"],
									input[type="number"],
									input[type="date"],
									input[type="search"],
									textarea,
									select,
									.wp-embed,
									.woocommerce-product-details__short-description,
									.woocommerce-tabs .tabs,
									.woocommerce #reviews #comments ol.commentlist li .comment-text,
									.woocommerce .order_details li:not(:last-of-type),
									.woocommerce-checkout .shop_table tr,
									.order-received-wrapper .order_item,
									.select2-container--default .select2-selection--single,
									.select2-dropdown,
									.select2-container .select2-choice,
									.select2-drop-active,
									.post-divider,
									hr,
									th,
									td,
									code,
									pre,
									.crane-re-comments__item,
									.crane-re-posts__item,
									.widget .cat-item,
									.widget .menu-item,
									.crane-archive-widget li,
									.cat-item .children,
									.widget .menu-item .children,
									body:not(.woocommerce) .comment,
									body:not(.woocommerce) .pingback,
                  .crane-blog-style-flat .crane-blog-grid-meta__wrapper:not(:only-child),
									.portfolio-filters-group,
									.portfolio__aside,
									.product-inner,
									.crane-portfolio__meta--border::after,
									.crane-portfolio-grid-meta .crane-portfolio-inliner:not(:only-child)',
				'background-color' => '.ui-slider-horizontal::before'
			),
			'default'     => '#dbdbdb'
		),
		array(
			'id'          => 'border-color-focus',
			'type'        => 'color',
			'transparent' => false,
			'title'       => esc_html__( 'Input focus border color', 'crane' ),
			'compiler'    => array(
				'border-color' => ' input[type="text"]:focus,
									input[type="password"]:focus,
									input[type="email"]:focus,
									input[type="url"]:focus,
									input[type="tel"]:focus,
									input[type="number"]:focus,
									input[type="date"]:focus,
									input[type="search"]:focus,
									textarea:focus,
									select:focus,
									.select2-container--default.select2-container--focus .select2-selection--single'
			),
			'default'     => '#c5c5c5'
		),
		array(
			'id'          => 'selection-color',
			'type'        => 'color',
			'transparent' => false,
			'title'       => esc_html__( 'Text selection color', 'crane' ),
			'default'     => '#ccc',
			'compiler'    => true,
		),
	)
) );

Redux::setSection( $crane_opt_name, array(
	'title'  => esc_html__( 'Regular pages', 'crane' ),
	'id'     => 'regular_pages',
	'icon'   => 'fa fa-folder-open',
	'fields' => array(
		array(
			'title'   => esc_html__( 'Sidebar position', 'crane' ),
			'id'      => 'regular-page-has-sidebar',
			'type'    => 'select',
			'options' => array(
				'none'     => esc_html__( 'Hide sidebar', 'crane' ),
				'at-right' => esc_html__( 'At right', 'crane' ),
				'at-left'  => esc_html__( 'At left', 'crane' ),
			),
			'default' => 'none'
		),
		array(
			'title'    => esc_html__( 'Sidebar content', 'crane' ),
			'id'       => 'regular-page-sidebar',
			'type'     => 'select',
			'data'     => 'sidebars',
			'default'  => 'crane_basic_sidebar',
			'required' => array( 'regular-page-has-sidebar', 'equals', array( 'at-right', 'at-left' ) )
		),
		array(
			'title'    => esc_html__( 'Sidebar width, %', 'crane' ),
			'id'       => 'regular-page-sidebar-width',
			'type'     => 'slider',
			'default'  => 25,
			'min'      => 0,
			'max'      => 100,
			'required' => array( 'regular-page-has-sidebar', 'equals', array( 'at-right', 'at-left' ) )
		),
		array(
			'title'    => esc_html__( 'Page content width, %', 'crane' ),
			'id'       => 'regular-page-content-width',
			'type'     => 'slider',
			'default'  => 75,
			'min'      => 0,
			'max'      => 100,
			'required' => array( 'regular-page-has-sidebar', 'equals', array( 'at-right', 'at-left' ) )
		),
		array(
			'title'    => esc_html__( 'Sticky sidebar', 'crane' ),
			'id'       => 'regular-page-sticky',
			'type'     => 'switch',
			'default'  => '0',
			'required' => array( 'regular-page-has-sidebar', 'equals', array( 'at-right', 'at-left' ) )
		),
		array(
			'title'    => esc_html__( 'Sticky sidebar top offset', 'crane' ),
			'id'       => 'regular-page-sticky-offset',
			'type'     => 'spacing',
			'left'     => 'false',
			'right'    => 'false',
			'bottom'   => 'false',
			'default'  => array(
				'padding-top' => '15'
			),
			'required' => array( 'regular-page-sticky', 'equals', '1' )
		),
		array(
			'title'    => esc_html__( 'Content and sidebar padding on desktop', 'crane' ),
			'subtitle' => esc_html__( 'Set top/bottom space of content area and sidebar', 'crane' ),
			'id'       => 'regular-page-padding',
			'type'     => 'spacing',
			'units'    => 'px',
			'left'     => 'false',
			'right'    => 'false',
			'compiler' => array( '.crane-regular-page .crane-content-inner, .crane-regular-page .crane-sidebar' ),
			'default'  => array(
				'padding-top'    => '80px',
				'padding-bottom' => '80px',
			)
		),
		array(
			'title'    => esc_html__( 'Content and sidebar padding on mobile', 'crane' ),
			'subtitle' => esc_html__( 'Set top/bottom space of content area and sidebar', 'crane' ),
			'id'       => 'regular-page-padding-mobile',
			'type'     => 'spacing',
			'units'    => 'px',
			'left'     => 'false',
			'right'    => 'false',
			'compiler' => true,
			'default'  => array(
				'padding-top'    => '40px',
				'padding-bottom' => '40px',
			)
		),
		array(
			'id'       => 'regular-page-footer_preset',
			'type'     => 'select',
			'title'    => esc_html__( 'Footer preset', 'crane' ),
			'subtitle' => sprintf( esc_html__( 'You can %1$s the default footer or add a new one on the %2$sFooters%3$s page.', 'crane' ), crane_get_footer_by_option( 'regular-page-footer_preset', 'theme-options' ), '<a target="_blank" href="' . admin_url() . 'edit.php?post_type=crane_footer">', '</a>' ),
			'options'  => $footer_presets,
			'default'  => 'default'
		),
		array(
			'id'       => 'regular-page-footer_appearance',
			'type'     => 'radio',
			'title'    => esc_html__( 'Footer appearance', 'crane' ),
			'options'  => array(
				'default'            => 'default',
				'appearance-regular' => 'regular',
				'appearance-fixed'   => 'fixed',
			),
			'default'  => 'default',
			'required' => array( 'regular-page-footer_preset', '!=', false ),
			'hint'     => array(
				'content' => esc_html__( "Regular footer appearance is just common footer acting like usual. Fixed footer appearance is fixed to bottom footer. It acts like sticky footer with 'moving out of the content' effect. Please note that fixed footer works great on small and medium height footers. Footers that have many content info and as a result have height more than browser height will face visibility issues. Also fixed footer will became a regular footer on mobile devices for same reasons", 'crane' )
			),
		),
	)
) );

Redux::setSection( $crane_opt_name, array(
	'title' => esc_html__( 'Portfolio', 'crane' ),
	'id'    => 'portfolio-section',
	'icon'  => 'fa fa-picture-o',
) );

Redux::setSection( $crane_opt_name, array(
	'title'      => esc_html__( 'Portfolio general settings', 'crane' ),
	'id'         => 'portfolio',
	'subsection' => true,
	'fields'     => array(
		array(
			'id'      => 'portfolio-name',
			'type'    => 'text',
			'title'   => esc_html__( 'Portfolio name', 'crane' ),
			'default' => esc_html__( 'Portfolio', 'crane' ),
		),
		array(
			'id'                => 'portfolio-slug',
			'type'              => 'text',
			'title'             => esc_html__( 'Portfolio slug', 'crane' ),
			'default'           => 'portfolio',
			'validate_callback' => 'crane_validate_uniq_slug',
		),
		array(
			'id'                => 'portfolio_cats-slug',
			'type'              => 'text',
			'title'             => esc_html__( 'Portfolio category slug', 'crane' ),
			'default'           => 'portfolio-category',
			'validate_callback' => 'crane_validate_uniq_slug'
		),
		array(
			'id'                => 'portfolio_tags-slug',
			'type'              => 'text',
			'title'             => esc_html__( 'Portfolio tag slug', 'crane' ),
			'default'           => 'portfolio-tag',
			'validate_callback' => 'crane_validate_uniq_slug'
		),
	)
) );

Redux::setSection( $crane_opt_name, array(
	'title'      => esc_html__( 'Portfolio archive settings', 'crane' ),
	'id'         => 'portfolio_archive_settings',
	'subsection' => true,
	'fields'     => array(
		array(
			'title'   => esc_html__( 'Sidebar position', 'crane' ),
			'id'      => 'portfolio-archive-has-sidebar',
			'type'    => 'select',
			'options' => array(
				'none'     => esc_html__( 'Hide sidebar', 'crane' ),
				'at-right' => esc_html__( 'At right', 'crane' ),
				'at-left'  => esc_html__( 'At left', 'crane' ),
			),
			'default' => 'at-right'
		),
		array(
			'title'    => esc_html__( 'Sidebar content', 'crane' ),
			'id'       => 'portfolio-archive-sidebar',
			'type'     => 'select',
			'data'     => 'sidebars',
			'default'  => 'crane_basic_sidebar',
			'required' => array( 'portfolio-archive-has-sidebar', 'equals', array( 'at-right', 'at-left' ) )
		),
		array(
			'title'    => esc_html__( 'Sidebar width, %', 'crane' ),
			'id'       => 'portfolio-archive-sidebar-width',
			'type'     => 'slider',
			'default'  => 25,
			'min'      => 0,
			'max'      => 100,
			'required' => array( 'portfolio-archive-has-sidebar', 'equals', array( 'at-right', 'at-left' ) )
		),
		array(
			'title'    => esc_html__( 'Page content width, %', 'crane' ),
			'id'       => 'portfolio-archive-content-width',
			'type'     => 'slider',
			'default'  => 75,
			'min'      => 0,
			'max'      => 100,
			'required' => array( 'portfolio-archive-has-sidebar', 'equals', array( 'at-right', 'at-left' ) )
		),
		array(
			'title'    => esc_html__( 'Sticky sidebar', 'crane' ),
			'id'       => 'portfolio-archive-sticky',
			'type'     => 'switch',
			'default'  => '0',
			'required' => array( 'portfolio-archive-has-sidebar', 'equals', array( 'at-right', 'at-left' ) )
		),
		array(
			'title'    => esc_html__( 'Sticky sidebar top offset', 'crane' ),
			'id'       => 'portfolio-archive-sticky-offset',
			'type'     => 'spacing',
			'left'     => 'false',
			'right'    => 'false',
			'bottom'   => 'false',
			'default'  => array(
				'padding-top' => '15'
			),
			'required' => array( 'portfolio-archive-sticky', 'equals', '1' )
		),
		array(
			'title'    => esc_html__( 'Content and sidebar padding on desktop', 'crane' ),
			'subtitle' => esc_html__( 'Set top/bottom space of content area and sidebar', 'crane' ),
			'id'       => 'portfolio-archive-padding',
			'type'     => 'spacing',
			'units'    => 'px',
			'left'     => 'false',
			'right'    => 'false',
			'compiler' => array( '.crane-portfolio-archive .crane-content-inner, .crane-portfolio-archive .crane-sidebar' ),
			'default'  => array(
				'padding-top'    => '80px',
				'padding-bottom' => '80px',
			)
		),
		array(
			'title'    => esc_html__( 'Content and sidebar padding on mobile', 'crane' ),
			'subtitle' => esc_html__( 'Set top/bottom space of content area and sidebar', 'crane' ),
			'id'       => 'portfolio-archive-padding-mobile',
			'type'     => 'spacing',
			'units'    => 'px',
			'left'     => 'false',
			'right'    => 'false',
			'compiler' => true,
			'default'  => array(
				'padding-top'    => '40px',
				'padding-bottom' => '40px',
			)
		),
		array(
			'id'   => 'divider_portfolio-archive',
			'type' => 'divide'
		),
		array(
			'type'     => 'select',
			'title'    => esc_html__( 'Portfolio style', 'crane' ),
			'subtitle' => esc_html__( 'Select portfolio style', 'crane' ),
			'id'       => 'portfolio-archive-style',
			'options'  => array(
				'flat'    => esc_html__( 'Flat', 'crane' ),
				'minimal' => esc_html__( 'Minimal', 'crane' ),
				'modern'  => esc_html__( 'Modern', 'crane' )
			),
			'default'  => 'flat',
		),
		array(
			'type'     => 'select',
			'title'    => esc_html__( 'Items layout', 'crane' ),
			'subtitle' => esc_html__( 'Select portfolio layout', 'crane' ),
			'id'       => 'portfolio-archive-layout',
			'options'  => array(
				'grid'    => esc_html__( 'Grid', 'crane' ),
				'masonry' => esc_html__( 'Masonry', 'crane' ),
			),
			'default'  => 'grid',
		),
		array(
			'type'     => 'select',
			'title'    => esc_html__( 'Layout mode', 'crane' ),
			'subtitle' => esc_html__( 'Select portfolio layout mode', 'crane' ),
			'id'       => 'portfolio-archive-layout_mode',
			'options'  => array(
				'masonry' => esc_html__( 'Standard', 'crane' ),
				'fitRows' => esc_html__( 'Fit Rows', 'crane' ),
			),
			'default'  => 'masonry',
		),
		array(
			'type'     => 'select',
			'title'    => esc_html__( 'Image proportion', 'crane' ),
			'subtitle' => '',
			'id'       => 'portfolio-archive-img_proportion',
			'options'  => array(
				'4x3'      => esc_html__( '4:3', 'crane' ),
				'3x2'      => esc_html__( '3:2', 'crane' ),
				'16x9'     => esc_html__( '16:9', 'crane' ),
				'1x1'      => esc_html__( '1:1', 'crane' ),
				'3x4'      => esc_html__( '3:4', 'crane' ),
				'2x3'      => esc_html__( '2:3', 'crane' ),
				'original' => esc_html__( 'Original', 'crane' ),
			),
			'default'  => '1x1',
		),
		array(
			'type'    => 'select',
			'title'   => esc_html__( 'Basic image resolution', 'crane' ),
			'id'      => 'portfolio-archive-image_resolution',
			'options' => crane_get_image_sizes_select_values(),
			'default' => 'crane-portfolio-300',
		),
		array(
			'type'     => 'select',
			'title'    => esc_html__( 'Hover style', 'crane' ),
			'subtitle' => esc_html__( 'Select item hover style', 'crane' ),
			'id'       => 'portfolio-archive-hover_style',
			'options'  => array(
				1 => esc_html__( 'Direction-aware hover', 'crane' ),
				2 => esc_html__( 'Overlay with zoom and link icons on hover', 'crane' ),
				3 => esc_html__( 'Zoom image on hover', 'crane' ),
				4 => esc_html__( 'Just link', 'crane' ),
				5 => esc_html__( 'Shuffle text link', 'crane' ),
			),
			'default'  => 4,
		),
		array(
			'type'    => 'select',
			'title'   => esc_html__( 'Image resolution for image in lightbox', 'crane' ),
			'id'      => 'portfolio-archive-image_resolution_for_link',
			'options' => crane_get_image_sizes_select_values(),
			'default' => 'full',
			'required' => array( 'portfolio-archive-hover_style', 'equals', 2 ),
		),
		array(
			'type'     => 'color_rgba',
			'title'    => esc_html__( 'Direction-aware hover color', 'crane' ),
			'id'       => 'portfolio-archive-direction_aware_color',
			'default'  => array(
				'color' => '#000',
				'alpha' => '0.5',
				'rgba'  => 'rgba(0,0,0,0.5)'
			),
			'required' => array( 'portfolio-archive-hover_style', 'equals', 1 )
		),
		array(
			'type'     => 'text',
			'title'    => esc_html__( 'Shuffle link text', 'crane' ),
			'id'       => 'portfolio-archive-shuffle_text',
			'default'  => esc_html__( 'View project', 'crane' ),
			'required' => array( 'portfolio-archive-hover_style', 'equals', '5' )
		),
		array(
			'type'     => 'slider',
			'title'    => esc_html__( 'Space between items', 'crane' ),
			'subtitle' => esc_html__( 'Space between items in grid and masonry portfolio layout.', 'crane' ),
			'id'       => 'portfolio-archive-grid_spacing',
			'max'      => 100,
			'min'      => 0,
			'default'  => 30,
		),
		array(
			'type'     => 'slider',
			'title'    => esc_html__( 'Number of columns', 'crane' ),
			'subtitle' => esc_html__( 'Set number of columns to show in one row', 'crane' ),
			'id'       => 'portfolio-archive-columns',
			'min'      => 1,
			'max'      => 8,
			'default'  => 4,
			'step'     => 1,
		),
		array(
			'type'     => 'slider',
			'title'    => esc_html__( 'Adaptive placement of columns', 'crane' ),
			"subtitle" => esc_html__( 'Set resolution on which items wil be stacked 1 per row', 'crane' ),
			'id'       => 'portfolio-archive-max_width',
			'min'      => 100,
			'max'      => 2000,
			'default'  => 769,
			'step'     => 1,
		),
		array(
			'type'     => 'slider',
			'title'    => esc_html__( 'Amount of posts', 'crane' ),
			'subtitle' => esc_html__( 'Set amount of posts to show initially (0 means unlimited)', 'crane' ),
			'id'       => 'portfolio-archive-posts_limit',
			'default'  => 0,
			'min'      => 0,
			'max'      => 100,
			'step'     => 1,
		),
		array(
			'type'    => 'checkbox',
			'title'   => esc_html__( 'Show portfolio item title', 'crane' ),
			'id'      => 'portfolio-archive-show_title_description',
			'default' => true,
		),
		array(
			'type'    => 'checkbox',
			'title'   => esc_html__( 'Show categories', 'crane' ),
			'id'      => 'portfolio-archive-show_categories',
			'default' => true,
		),
		array(
			'type'    => 'checkbox',
			'title'   => esc_html__( 'Show custom text from meta', 'crane' ),
			'id'      => 'portfolio-archive-show_custom_text',
			'default' => false,
		),
		array(
			'type'     => 'checkbox',
			'title'    => esc_html__( 'Show excerpt', 'crane' ),
			'subtitle' => esc_html__( 'Disable this option if you do not want excerpt', 'crane' ),
			'id'       => 'portfolio-archive-show_excerpt',
			'default'  => true,
		),
		array(
			'type'     => 'checkbox',
			'title'    => esc_html__( 'Strip html in except', 'crane' ),
			'id'       => 'portfolio-archive-excerpt_strip_html',
			'required' => array( 'portfolio-archive-show_excerpt', 'equals', true ),
			'default'  => true,
		),
		array(
			'type'    => 'slider',
			'title'   => esc_html__( 'Excerpt height', 'crane' ),
			'id'      => 'portfolio-archive-excerpt_height',
			'min'     => 50,
			'max'     => 500,
			'default' => 170,
			'step'    => 1,
		),
		array(
			'id'      => 'portfolio-archive-show_read_more',
			'type'    => 'checkbox',
			'title'   => esc_html__( 'Show &quot;read more&quot; link', 'crane' ),
			'default' => false,
		),
		array(
			'type'    => 'select',
			'title'   => esc_html__( 'Portfolio tags type', 'crane' ),
			'id'      => 'portfolio-archive-show_imgtags',
			'options' => array(
				'0'     => esc_html__( 'No tags', 'crane' ),
				'text'  => esc_html__( 'Text tags', 'crane' ),
				'image' => esc_html__( 'Image tags', 'crane' ),
			),
			'default' => '0'
		),
		array(
			'type'     => 'checkbox',
			'title'    => esc_html__( 'Show filtering by category', 'crane' ),
			'subtitle' => esc_html__( 'Check this checkbox if you want to enable filtering by category', 'crane' ),
			'id'       => 'portfolio-archive-sortable',
			'default'  => false
		),
		array(
			'type'     => 'select',
			'title'    => esc_html__( 'Filtering align', 'crane' ),
			'id'       => 'portfolio-archive-sortable_align',
			'options'  => array(
				'left'   => esc_html__( 'Left', 'crane' ),
				'right'  => esc_html__( 'Right', 'crane' ),
				'center' => esc_html__( 'Center', 'crane' ),
			),
			'required' => array( 'portfolio-archive-sortable', 'equals', array( '1' ) ),
			'default'  => 'center'
		),
		array(
			'type'     => 'select',
			'title'    => esc_html__( 'Filtering style', 'crane' ),
			'subtitle' => esc_html__( 'Select style of navigation filtering', 'crane' ),
			'id'       => 'portfolio-archive-sortable_style',
			'options'  => array(
				'in_grid' => esc_html__( 'Default', 'crane' ),
				'outline' => esc_html__( 'Custom', 'crane' ),
			),
			'required' => array( 'portfolio-archive-sortable', 'equals', array( '1' ) ),
			'default'  => 'in_grid'
		),
		array(
			'type'        => 'color',
			'title'       => esc_html__( 'Filtering background color', 'crane' ),
			'id'          => 'portfolio-archive-sortable_background_color',
			'transparent' => false,
			'required'    => array( 'portfolio-archive-sortable_style', 'equals', array( 'outline' ) ),
		),
		array(
			'type'        => 'color',
			'title'       => esc_html__( 'Filtering text color', 'crane' ),
			'id'          => 'portfolio-archive-sortable_text_color',
			'transparent' => false,
			'required'    => array( 'portfolio-archive-sortable_style', 'equals', array( 'outline' ) ),
		),
		array(
			'type'    => 'select',
			'title'   => esc_html__( 'Pagination type', 'crane' ),
			'id'      => 'portfolio-archive-pagination_type',
			'options' => array(
				'0'         => esc_html__( 'No pagination', 'crane' ),
				'show_more' => esc_html__( 'Load more button', 'crane' ),
			),
			'default' => 'show_more',
		),
		array(
			'id'             => 'portfolio-archive-pagination_typography',
			'type'           => 'typography',
			'title'          => esc_html__( 'Pagination typography', 'crane' ),
			'google'         => true,
			'color'          => false,
			'line-height'    => false,
			'font-backup'    => false,
			'text-align'     => false,
			'text-transform' => true,
			'compiler'       => array( '.crane-pagination-show-more .btn-txt' ),
			'units'          => 'px',
			'default'        => array(
				'font-weight' => '600',
				'font-family' => 'Open Sans',
				'google'      => true,
				'font-size'   => '18px',
			),
			'required'       => array( 'portfolio-archive-pagination_type', 'equals', array( 'show_more', 'scroll' ) ),
		),
		array(
			'type'        => 'color',
			'title'       => esc_html__( 'Pagination button text color', 'crane' ),
			'id'          => 'portfolio-archive-pagination_color',
			'transparent' => false,
			'default'     => '#ffffff',
			'required'    => array( 'portfolio-archive-pagination_type', 'equals', array( 'show_more', 'scroll' ) ),
		),
		array(
			'type'        => 'color',
			'title'       => esc_html__( 'Pagination button background color', 'crane' ),
			'id'          => 'portfolio-archive-pagination_background',
			'transparent' => false,
			'default'     => '#393b3f',
			'required'    => array( 'portfolio-archive-pagination_type', 'equals', array( 'show_more', 'scroll' ) ),
		),
		array(
			'type'        => 'color',
			'title'       => esc_html__( 'Pagination button hover & active text color', 'crane' ),
			'id'          => 'portfolio-archive-pagination_color_hover',
			'transparent' => false,
			'default'     => '#ffffff',
			'required'    => array( 'portfolio-archive-pagination_type', 'equals', array( 'show_more', 'scroll' ) ),
		),
		array(
			'type'        => 'color',
			'title'       => esc_html__( 'Pagination button hover & active background color', 'crane' ),
			'id'          => 'portfolio-archive-pagination_background_hover',
			'transparent' => false,
			'default'     => '#93cb52',
			'required'    => array( 'portfolio-archive-pagination_type', 'equals', array( 'show_more', 'scroll' ) ),
		),
		array(
			'id'       => 'portfolio-archive-show_more_text',
			'type'     => 'text',
			'title'    => esc_html__( 'Pagination button text', 'crane' ),
			'default'  => esc_html__( 'Show more', 'crane' ),
			'required' => array( 'portfolio-archive-pagination_type', 'equals', array( 'show_more', 'scroll' ) ),
		),
		array(
			'type'    => 'select',
			'title'   => esc_html__( 'Order by', 'crane' ),
			'id'      => 'portfolio-archive-orderby',
			'options' => array(
				'post_date'     => esc_html__( 'Date', 'crane' ),
				'id'            => esc_html__( 'Post id', 'crane' ),
				'title'         => esc_html__( 'Title', 'crane' ),
				'comment_count' => esc_html__( 'Comment count', 'crane' ),
				'random'        => esc_html__( 'Random', 'crane' ),
				'author'        => esc_html__( 'Author', 'crane' ),
			),
			'default' => 'post_date'
		),
		array(
			'type'    => 'select',
			'title'   => esc_html__( 'Order', 'crane' ),
			'id'      => 'portfolio-archive-order',
			'options' => array(
				'ASC'  => esc_html__( 'Asc', 'crane' ),
				'DESC' => esc_html__( 'Desc', 'crane' ),
			),
			'default' => 'ASC'
		),

		array(
			'type'     => 'checkbox',
			'title'    => esc_html__( 'Enable Custom order?', 'crane' ),
			'subtitle' => esc_html__( 'If enable, widget shows custom ordered portfolio with second sorting set in "Order by".', 'crane' ),
			'id'       => 'portfolio-archive-custom_order',
			'default'  => false
		),
		array(
			'type'    => 'select',
			'title'   => esc_html__( 'Open on click in', 'crane' ),
			'id'      => 'portfolio-archive-target',
			'options' => array(
				'same'  => esc_html__( 'Same window', 'crane' ),
				'blank' => esc_html__( 'New window', 'crane' ),
			),
			'default' => 'same'
		),
		array(
			'id'       => 'portfolio-footer_preset',
			'type'     => 'select',
			'title'    => esc_html__( 'Footer preset', 'crane' ),
			'subtitle' => sprintf( esc_html__( 'You can %1$s the default footer or add a new one on the %2$sFooters%3$s page.', 'crane' ), crane_get_footer_by_option( 'portfolio-footer_preset', 'theme-options' ), '<a target="_blank" href="' . admin_url() . 'edit.php?post_type=crane_footer">', '</a>' ),
			'options'  => $footer_presets,
			'default'  => 'default'
		),
		array(
			'id'       => 'portfolio-footer_appearance',
			'type'     => 'radio',
			'title'    => esc_html__( 'Footer appearance', 'crane' ),
			'options'  => array(
				'default'            => 'default',
				'appearance-regular' => 'regular',
				'appearance-fixed'   => 'fixed',
			),
			'default'  => 'default',
			'required' => array( 'portfolio-footer_preset', '!=', false ),
			'hint'     => array(
				'content' => esc_html__( "Regular footer appearance is just common footer acting like usual. Fixed footer appearance is fixed to bottom footer. It acts like sticky footer with 'moving out of the content' effect. Please note that fixed footer works great on small and medium height footers. Footers that have many content info and as a result have height more than browser height will face visibility issues. Also fixed footer will became a regular footer on mobile devices for same reasons", 'crane' )
			),
		),

	)
) );

Redux::setSection( $crane_opt_name, array(
	'title'      => esc_html__( 'Portfolio single post settings', 'crane' ),
	'id'         => 'crane_portfolio_single_settings',
	'subsection' => true,
	'fields'     => array(
		array(
			'id'      => 'portfolio-single-breadcrumbs_view',
			'type'    => 'select',
			'title'   => esc_html__( 'Breadcrumbs view', 'crane' ),
			'options' => array(
				'only_name'       => esc_html__( 'Home &gt; Single portfolio name', 'crane' ),
				'with_category'   => esc_html__( 'Home &gt; Portfolio first category &gt; Single portfolio name', 'crane' ),
				'with_categories' => esc_html__( 'Home &gt; Portfolio categories, separated by comma &gt; Single portfolio name', 'crane' ),
			),
			'default' => 'only_name'
		),
		array(
			'title'   => esc_html__( 'Sidebar position', 'crane' ),
			'id'      => 'portfolio-single-has-sidebar',
			'type'    => 'select',
			'options' => array(
				'none'     => esc_html__( 'Hide sidebar', 'crane' ),
				'at-right' => esc_html__( 'At right', 'crane' ),
				'at-left'  => esc_html__( 'At left', 'crane' ),
			),
			'default' => 'none'
		),
		array(
			'title'    => esc_html__( 'Sticky sidebar', 'crane' ),
			'id'       => 'portfolio-single-sticky',
			'type'     => 'switch',
			'default'  => '0',
			'required' => array( 'portfolio-single-has-sidebar', 'equals', array( 'at-right', 'at-left' ) )
		),
		array(
			'title'    => esc_html__( 'Sticky sidebar top offset', 'crane' ),
			'id'       => 'portfolio-single-sticky-offset',
			'type'     => 'spacing',
			'left'     => 'false',
			'right'    => 'false',
			'bottom'   => 'false',
			'default'  => array(
				'padding-top' => '15'
			),
			'required' => array( 'portfolio-single-sticky', 'equals', '1' )
		),
		array(
			'title'    => esc_html__( 'Content and sidebar padding on desktop', 'crane' ),
			'subtitle' => esc_html__( 'Set top/bottom space of content area and sidebar', 'crane' ),
			'id'       => 'portfolio-single-padding',
			'type'     => 'spacing',
			'units'    => 'px',
			'left'     => 'false',
			'right'    => 'false',
			'compiler' => array( '.crane-portfolio-single .crane-content-inner, .crane-portfolio-single .crane-sidebar' ),
			'default'  => array(
				'padding-top'    => '80px',
				'padding-bottom' => '80px',
			)
		),
		array(
			'title'    => esc_html__( 'Content and sidebar padding on mobile', 'crane' ),
			'subtitle' => esc_html__( 'Set top/bottom space of content area and sidebar', 'crane' ),
			'id'       => 'portfolio-single-padding-mobile',
			'type'     => 'spacing',
			'units'    => 'px',
			'left'     => 'false',
			'right'    => 'false',
			'compiler' => true,
			'default'  => array(
				'padding-top'    => '40px',
				'padding-bottom' => '40px',
			)
		),
		array(
			'id'   => 'divider_portfolio_single',
			'type' => 'divide'
		),
		array(
			'id'      => 'portfolio-single-show-featured-image',
			'type'    => 'checkbox',
			'default' => '0',
			'title'   => esc_html__( 'Show featured image', 'crane' )
		),
		array(
			'type'    => 'select',
			'title'   => esc_html__( 'Featured image size', 'crane' ),
			'id'      => 'portfolio-single-featured-image-size',
			'options' => array(
				'default'    => esc_html__( 'Original size', 'crane' ),
				'fullscreen' => esc_html__( 'Full screen', 'crane' ),
				'custom'     => esc_html__( 'Custom', 'crane' ),
				'fullwidth'  => esc_html__( 'Full width + custom height', 'crane' ),
			),
			'default' => 'fullscreen',
			'required' => array( 'portfolio-single-show-featured-image', 'equals', true ),
		),
		array(
			'id'       => 'portfolio-single-featured-image-width',
			'type'     => 'slider',
			'title'    => esc_html__( 'Featured image Width', 'crane' ),
			'default'  => 0,
			'min'      => 0,
			'max'      => 8192, // 8K UHD.
			'required' => array( 'portfolio-single-featured-image-size', 'equals', array( 'custom' ) ),
		),
		array(
			'id'       => 'portfolio-single-featured-image-height',
			'type'     => 'slider',
			'title'    => esc_html__( 'Featured image Height', 'crane' ),
			'default'  => 0,
			'min'      => 0,
			'max'      => 8192, // 8K UHD.
			'required' => array( 'portfolio-single-featured-image-size', 'equals', array( 'custom', 'fullwidth' ) ),
		),
		array(
			'id'      => 'portfolio-single-show-prev-next-post',
			'type'    => 'checkbox',
			'default' => '1',
			'title'   => esc_html__( 'Show portfolio navigation', 'crane' )
		),
		array(
			'title'    => esc_html__( 'Sidebar content', 'crane' ),
			'id'       => 'portfolio-single-sidebar',
			'type'     => 'select',
			'data'     => 'sidebars',
			'default'  => 'crane_basic_sidebar',
			'required' => array( 'portfolio-single-has-sidebar', 'equals', array( 'at-right', 'at-left' ) )
		),
		array(
			'title'    => esc_html__( 'Sidebar width, %', 'crane' ),
			'id'       => 'portfolio-single-sidebar-width',
			'type'     => 'slider',
			'default'  => 25,
			'min'      => 0,
			'max'      => 100,
			'required' => array( 'portfolio-single-has-sidebar', 'equals', array( 'at-right', 'at-left' ) )
		),
		array(
			'title'    => esc_html__( 'Page content width, %', 'crane' ),
			'id'       => 'portfolio-single-content-width',
			'type'     => 'slider',
			'default'  => 75,
			'min'      => 0,
			'max'      => 100,
			'required' => array( 'portfolio-single-has-sidebar', 'equals', array( 'at-right', 'at-left' ) )
		),
		array(
			'id'      => 'portfolio-single-show-title',
			'type'    => 'checkbox',
			'default' => '1',
			'title'   => esc_html__( 'Show portfolio title', 'crane' )
		),
		array(
			'id'      => 'portfolio-single-show-border',
			'type'    => 'checkbox',
			'default' => '1',
			'title'   => esc_html__( 'Show border before portfolio meta data', 'crane' )
		),
		array(
			'id'      => 'portfolio-single-show-tags',
			'type'    => 'checkbox',
			'default' => '1',
			'title'   => esc_html__( 'Show portfolio tags', 'crane' )
		),
		array(
			'id'      => 'portfolio-single-show-date',
			'type'    => 'checkbox',
			'default' => '1',
			'title'   => esc_html__( 'Show portfolio publication date', 'crane' )
		),
		array(
			'id'      => 'portfolio-single-show-cats',
			'type'    => 'checkbox',
			'default' => '1',
			'title'   => esc_html__( 'Show portfolio categories', 'crane' )
		),
		array(
			'id'      => 'portfolio-single-show-share',
			'type'    => 'checkbox',
			'default' => '1',
			'title'   => esc_html__( 'Show social share button', 'crane' )
		),
		array(
			'id'       => 'portfolio-single-footer_preset',
			'type'     => 'select',
			'title'    => esc_html__( 'Footer preset', 'crane' ),
			'subtitle' => sprintf( esc_html__( 'You can %1$s the default footer or add a new one on the %2$sFooters%3$s page.', 'crane' ), crane_get_footer_by_option( 'portfolio-footer_preset', 'theme-options' ), '<a target="_blank" href="' . admin_url() . 'edit.php?post_type=crane_footer">', '</a>' ),
			'options'  => $footer_presets,
			'default'  => 'default'
		),
		array(
			'id'       => 'portfolio-single-footer_appearance',
			'type'     => 'radio',
			'title'    => esc_html__( 'Footer appearance', 'crane' ),
			'options'  => array(
				'default'            => 'default',
				'appearance-regular' => 'regular',
				'appearance-fixed'   => 'fixed',
			),
			'default'  => 'default',
			'required' => array( 'portfolio-footer_preset', '!=', false ),
			'hint'     => array(
				'content' => esc_html__( "Regular footer appearance is just common footer acting like usual. Fixed footer appearance is fixed to bottom footer. It acts like sticky footer with 'moving out of the content' effect. Please note that fixed footer works great on small and medium height footers. Footers that have many content info and as a result have height more than browser height will face visibility issues. Also fixed footer will became a regular footer on mobile devices for same reasons", 'crane' )
			),
		),
	)
) );

Redux::setSection( $crane_opt_name, array(
	'title' => esc_html__( 'Blog', 'crane' ),
	'id'    => 'blog-section',
	'icon'  => 'fa fa-pencil',
) );

Redux::setSection( $crane_opt_name, array(
	'title'      => esc_html__( 'Blog general settings', 'crane' ),
	'id'         => 'blog',
	'subsection' => true,
	'fields'     => array(
		array(
			'id'                => 'blog_cats-slug',
			'type'              => 'text',
			'title'             => esc_html__( 'Blog category slug', 'crane' ),
			'default'           => 'category',
			'validate_callback' => 'crane_validate_uniq_slug'
		),
		array(
			'id'                => 'blog_tags-slug',
			'type'              => 'text',
			'title'             => esc_html__( 'Blog tag slug', 'crane' ),
			'default'           => 'tag',
			'validate_callback' => 'crane_validate_uniq_slug'
		),
	)
) );

Redux::setSection( $crane_opt_name, array(
	'title'      => esc_html__( 'Blog archive settings', 'crane' ),
	'id'         => 'blog_archive_settings',
	'subsection' => true,
	'fields'     => array(
		array(
			'id'      => 'blog-archive-title',
			'type'    => 'text',
			'title'   => esc_html__( 'Blog archive page title', 'crane' ),
			'default' => esc_js( 'Archive', 'crane' ),
		),
		array(
			'id'      => 'blog-has-sidebar',
			'type'    => 'select',
			'title'   => esc_html__( 'Sidebar position', 'crane' ),
			'options' => array(
				'none'     => esc_html__( 'Hide sidebar', 'crane' ),
				'at-right' => esc_html__( 'At right', 'crane' ),
				'at-left'  => esc_html__( 'At left', 'crane' ),
			),
			'default' => 'at-right'
		),
		array(
			'id'       => 'blog-sidebar',
			'type'     => 'select',
			'title'    => esc_html__( 'Sidebar content', 'crane' ),
			'data'     => 'sidebars',
			'default'  => 'crane_basic_sidebar',
			'required' => array( 'blog-has-sidebar', 'equals', array( 'at-left', 'at-right' ) )
		),
		array(
			'title'    => esc_html__( 'Sidebar width, %', 'crane' ),
			'id'       => 'blog-sidebar-width',
			'type'     => 'slider',
			'default'  => 25,
			'min'      => 0,
			'max'      => 100,
			'required' => array( 'blog-has-sidebar', 'equals', array( 'at-left', 'at-right' ) )
		),
		array(
			'title'    => esc_html__( 'Page content width, %', 'crane' ),
			'id'       => 'blog-content-width',
			'type'     => 'slider',
			'default'  => 75,
			'min'      => 0,
			'max'      => 100,
			'required' => array( 'blog-has-sidebar', 'equals', array( 'at-left', 'at-right' ) )
		),
		array(
			'title'    => esc_html__( 'Sticky sidebar', 'crane' ),
			'id'       => 'blog-archive-sticky',
			'type'     => 'switch',
			'default'  => '0',
			'required' => array( 'blog-has-sidebar', 'equals', array( 'at-right', 'at-left' ) )
		),
		array(
			'title'    => esc_html__( 'Sticky sidebar top offset', 'crane' ),
			'id'       => 'blog-archive-sticky-offset',
			'type'     => 'spacing',
			'left'     => 'false',
			'right'    => 'false',
			'bottom'   => 'false',
			'default'  => array(
				'padding-top' => '15'
			),
			'required' => array( 'blog-archive-sticky', 'equals', '1' )
		),
		array(
			'title'    => esc_html__( 'Content and sidebar padding on desktop', 'crane' ),
			'subtitle' => esc_html__( 'Set top/bottom space of content area and sidebar', 'crane' ),
			'id'       => 'blog-archive-padding',
			'type'     => 'spacing',
			'units'    => 'px',
			'left'     => 'false',
			'right'    => 'false',
			'compiler' => array( '.crane-blog-archive .crane-content-inner, .crane-blog-archive .crane-sidebar' ),
			'default'  => array(
				'padding-top'    => '80px',
				'padding-bottom' => '80px',
			)
		),
		array(
			'title'    => esc_html__( 'Content and sidebar padding on mobile', 'crane' ),
			'subtitle' => esc_html__( 'Set top/bottom space of content area and sidebar', 'crane' ),
			'id'       => 'blog-archive-padding-mobile',
			'type'     => 'spacing',
			'units'    => 'px',
			'left'     => 'false',
			'right'    => 'false',
			'compiler' => true,
			'default'  => array(
				'padding-top'    => '40px',
				'padding-bottom' => '40px',
			)
		),
		array(
			'id'   => 'divider_blog-archive',
			'type' => 'divide'
		),
		array(
			'id'      => 'blog-template',
			'type'    => 'select',
			'title'   => esc_html__( 'Layout', 'crane' ),
			'options' => array(
				'standard' => esc_html__( 'Standard', 'crane' ),
				'cell'     => esc_html__( 'Cell', 'crane' ),
				'masonry'  => esc_html__( 'Masonry', 'crane' )
			),
			'default' => 'standard'
		),
		array(
			'type'    => 'select',
			'title'   => esc_html__( 'Basic image resolution', 'crane' ),
			'id'      => 'blog-image_resolution',
			'options' => crane_get_image_sizes_select_values(),
			'default' => 'crane-featured',
		),
		array(
			'id'       => 'blog-show_pubdate',
			'type'     => 'checkbox',
			'default'  => '1',
			'title'    => esc_html__( 'Show publication date', 'crane' ),
			'required' => array( 'blog-template', 'equals', 'standard' )
		),
		array(
			'id'       => 'blog-show_author',
			'type'     => 'checkbox',
			'default'  => '1',
			'title'    => esc_html__( 'Show author', 'crane' ),
			'required' => array( 'blog-template', 'equals', 'standard' )
		),
		array(
			'id'       => 'blog-show_cats',
			'type'     => 'checkbox',
			'default'  => '1',
			'title'    => esc_html__( 'Show categories', 'crane' ),
			'required' => array( 'blog-template', 'equals', 'standard' )
		),
		array(
			'type'     => 'select',
			'title'    => esc_html__( 'Style', 'crane' ),
			'subtitle' => esc_html__( 'Select blog style', 'crane' ),
			'id'       => 'blog-style',
			'options'  => array(
				'flat'      => esc_html__( 'Flat', 'crane' ),
				'corporate' => esc_html__( 'Corporate', 'crane' )
			),
			'default'  => 'corporate',
			'required' => array( 'blog-template', 'equals', 'masonry' )
		),
		array(
			'type'     => 'select',
			'title'    => esc_html__( 'Top content proportion', 'crane' ),
			'subtitle' => '',
			'id'       => 'blog-img_proportion',
			'options'  => array(
				'1x1'      => esc_html__( '1:1', 'crane' ),
				'4x3'      => esc_html__( '4:3', 'crane' ),
				'3x2'      => esc_html__( '3:2', 'crane' ),
				'16x9'     => esc_html__( '16:9', 'crane' ),
				'3x4'      => esc_html__( '3:4', 'crane' ),
				'2x3'      => esc_html__( '2:3', 'crane' ),
				'original' => esc_html__( 'Original', 'crane' ),
			),
			'default'  => '1x1',
			'required' => array( 'blog-template', 'equals', array( 'masonry' ) )
		),
		array(
			'id'       => 'blog-masonry-columns',
			'type'     => 'select',
			'title'    => esc_html__( 'Number of columns', 'crane' ),
			'subtitle' => esc_html__( 'Set number of columns to show in one row', 'crane' ),
			'options'  => array( '2' => 2, '3' => 3, '4' => 4, '5' => 5, '6' => 6, '7' => 7, '8' => 8 ),
			'default'  => 4,
			'required' => array( 'blog-template', 'equals', 'masonry' )
		),
		array(
			'id'       => 'blog-cell-columns',
			'type'     => 'select',
			'title'    => esc_html__( 'Number of columns', 'crane' ),
			'subtitle' => esc_html__( 'Set number of columns to show in one row', 'crane' ),
			'options'  => array( '1' => 1, '2' => 2, '3' => 3 ),
			'default'  => 2,
			'required' => array( 'blog-template', 'equals', 'cell' )
		),
		array(
			'type'     => 'slider',
			'title'    => esc_html__( 'Adaptive placement of columns', 'crane' ),
			"subtitle" => esc_html__( 'Set resolution on which items wil be stacked 1 per row', 'crane' ),
			'id'       => 'blog-max_width',
			'min'      => 300,
			'max'      => 1500,
			'default'  => 768,
			'step'     => 1,
			'required' => array( 'blog-template', 'equals', array( 'cell', 'masonry' ) )
		),
		array(
			'type'     => 'slider',
			'title'    => esc_html__( 'Space between items', 'crane' ),
			'subtitle' => esc_html__( 'Space between items in grid and masonry blog layout.', 'crane' ),
			'id'       => 'blog-grid_spacing',
			'max'      => 50,
			'min'      => 0,
			'default'  => 30,
			'required' => array( 'blog-template', 'equals', array( 'cell', 'masonry' ) )
		),
		array(
			'type'     => 'slider',
			'title'    => esc_html__( 'Post height on desktop', 'crane' ),
			'id'       => 'blog-post_height_desktop',
			'min'      => 150,
			'max'      => 750,
			'default'  => 350,
			'step'     => 1,
			'required' => array( 'blog-template', 'equals', 'cell' )
		),
		array(
			'type'     => 'slider',
			'title'    => esc_html__( 'Post height on mobile', 'crane' ),
			'id'       => 'blog-post_height_mobile',
			'min'      => 150,
			'max'      => 750,
			'default'  => 350,
			'step'     => 1,
			'required' => array( 'blog-template', 'equals', 'cell' )
		),
		array(
			'type'     => 'checkbox',
			'title'    => esc_html__( 'Show title', 'crane' ),
			'id'       => 'blog-show_title_description',
			'default'  => false,
			'required' => array( 'blog-template', 'equals', array( 'cell', 'masonry' ) ),
		),
		array(
			'type'    => 'checkbox',
			'title'   => esc_html__( 'Show tags', 'crane' ),
			'id'      => 'blog-show_tags',
			'default' => true
		),
		array(
			'type'     => 'checkbox',
			'title'    => esc_html__( 'Show excerpt', 'crane' ),
			'subtitle' => esc_html__( 'Disable this option if you do not want excerpt', 'crane' ),
			'id'       => 'blog-show_excerpt',
			'default'  => false,
			'required' => array( 'blog-template', 'equals', array( 'standard', 'cell', 'masonry' ) )
		),
		array(
			'type'     => 'checkbox',
			'title'    => esc_html__( 'Strip html in except', 'crane' ),
			'id'       => 'blog-excerpt_strip_html',
			'required' => array( 'blog-show_excerpt', 'equals', true ),
			'default'  => true,
		),
		array(
			'type'     => 'slider',
			'title'    => esc_html__( 'Excerpt height', 'crane' ),
			'id'       => 'blog-excerpt_height',
			'min'      => 50,
			'max'      => 500,
			'default'  => 170,
			'step'     => 1,
			'required' => array( 'blog-template', 'equals', array( 'cell', 'masonry' ) )
		),
		array(
			'type'        => 'color',
			'id'          => 'blog-cell-item-bg-color',
			'transparent' => false,
			'title'       => esc_html__( 'Item background color', 'crane' ),
			'compiler'    => array(
				'background-color' => '.crane-blog-archive-layout-cell .crane-blog-layout-cell .crane-blog-grid-meta',
				'border-color'     => '.crane-blog-archive-layout-cell .crane-blog-layout-cell .crane-blog-grid-meta'
			),
			'default'     => '#f8f7f5',
			'required'    => array( 'blog-template', 'equals', 'cell' )
		),
		array(
			'id'       => 'blog-show_read_more',
			'type'     => 'checkbox',
			'title'    => esc_html__( 'Show &quot;read more&quot; link', 'crane' ),
			'default'  => false,
			'required' => array( 'blog-template', 'equals', array( 'cell', 'standard', 'masonry' ) )
		),
		array(
			'id'       => 'blog-show-comment-counter',
			'type'     => 'checkbox',
			'title'    => esc_html__( 'Show comment counter', 'crane' ),
			'default'  => true,
			'required' => array( 'blog-template', 'equals', array( 'standard', 'masonry' ) )
		),
		array(
			'type'     => 'checkbox',
			'title'    => esc_html__( 'Show social share button', 'crane' ),
			'subtitle' => '',
			'id'       => 'blog-show_share_button',
			'default'  => true,
			'required' => array( 'blog-template', 'equals', array( 'standard', 'masonry' ) )
		),
		array(
			'type'     => 'select',
			'title'    => esc_html__( 'Show post meta', 'crane' ),
			'subtitle' => '',
			'id'       => 'blog-show_post_meta',
			'options'  => array(
				'author-and-date' => esc_html__( 'Show author info and post date', 'crane' ),
				'date'            => esc_html__( 'Show post date', 'crane' ),
				'none'            => esc_html__( 'Do not show', 'crane' ),
			),
			'default'  => 'author-and-date',
			'required' => array( 'blog-template', 'equals', 'masonry' )
		),
		array(
			'type'    => 'select',
			'title'   => esc_html__( 'Open on click in', 'crane' ),
			'id'      => 'blog-target',
			'options' => array(
				'same'  => esc_html__( 'Same window', 'crane' ),
				'blank' => esc_html__( 'New window', 'crane' ),
			),
			'default' => 'same',
		),
		array(
			'id'       => 'blog-footer_preset',
			'type'     => 'select',
			'title'    => esc_html__( 'Footer preset', 'crane' ),
			'subtitle' => sprintf( esc_html__( 'You can %1$s the default footer or add a new one on the %2$sFooters%3$s page.', 'crane' ), crane_get_footer_by_option( 'blog-footer_preset', 'theme-options' ), '<a target="_blank" href="' . admin_url() . 'edit.php?post_type=crane_footer">', '</a>' ),
			'options'  => $footer_presets,
			'default'  => 'default'
		),
		array(
			'id'       => 'blog-footer_appearance',
			'type'     => 'radio',
			'title'    => esc_html__( 'Footer appearance', 'crane' ),
			'options'  => array(
				'default'            => 'default',
				'appearance-regular' => 'regular',
				'appearance-fixed'   => 'fixed',
			),
			'default'  => 'default',
			'required' => array( 'blog-footer_preset', '!=', false ),
			'hint'     => array(
				'content' => esc_html__( "Regular footer appearance is just common footer acting like usual. Fixed footer appearance is fixed to bottom footer. It acts like sticky footer with 'moving out of the content' effect. Please note that fixed footer works great on small and medium height footers. Footers that have many content info and as a result have height more than browser height will face visibility issues. Also fixed footer will became a regular footer on mobile devices for same reasons", 'crane' )
			),
		),

	)
) );

Redux::setSection( $crane_opt_name, array(
	'title'      => esc_html__( 'Blog single post settings', 'crane' ),
	'id'         => 'blog_single_settings',
	'subsection' => true,
	'fields'     => array(
		array(
			'id'      => 'blog-single-breadcrumbs_view',
			'type'    => 'select',
			'title'   => esc_html__( 'Breadcrumbs view', 'crane' ),
			'options' => array(
				'only_name'       => esc_html__( 'Home &gt; Single blog name', 'crane' ),
				'with_category'   => esc_html__( 'Home &gt; First Category &gt; Single blog name', 'crane' ),
				'with_categories' => esc_html__( 'Home &gt; Categories, separated by comma &gt; Single blog name', 'crane' ),
			),
			'default' => 'with_category'
		),
		array(
			'id'      => 'blog-single-has-sidebar',
			'type'    => 'select',
			'title'   => esc_html__( 'Sidebar position', 'crane' ),
			'options' => array(
				'none'     => esc_html__( 'Hide sidebar', 'crane' ),
				'at-right' => esc_html__( 'At right', 'crane' ),
				'at-left'  => esc_html__( 'At left', 'crane' ),
			),
			'default' => 'at-right'
		),
		array(
			'id'       => 'blog-single-sidebar',
			'type'     => 'select',
			'title'    => esc_html__( 'Sidebar content', 'crane' ),
			'data'     => 'sidebars',
			'default'  => 'crane_basic_sidebar',
			'required' => array( 'blog-single-has-sidebar', 'equals', array( 'at-left', 'at-right' ) )
		),
		array(
			'title'    => esc_html__( 'Sidebar width, %', 'crane' ),
			'id'       => 'blog-single-sidebar-width',
			'type'     => 'slider',
			'default'  => 25,
			'min'      => 0,
			'max'      => 100,
			'required' => array( 'blog-single-has-sidebar', 'equals', array( 'at-left', 'at-right' ) )
		),
		array(
			'title'    => esc_html__( 'Page content width, %', 'crane' ),
			'id'       => 'blog-single-content-width',
			'type'     => 'slider',
			'default'  => 75,
			'min'      => 0,
			'max'      => 100,
			'required' => array( 'blog-single-has-sidebar', 'equals', array( 'at-left', 'at-right' ) )
		),
		array(
			'title'    => esc_html__( 'Sticky sidebar', 'crane' ),
			'id'       => 'blog-single-sticky',
			'type'     => 'switch',
			'default'  => '0',
			'required' => array( 'blog-single-has-sidebar', 'equals', array( 'at-right', 'at-left' ) )
		),
		array(
			'title'    => esc_html__( 'Sticky sidebar top offset', 'crane' ),
			'id'       => 'blog-single-sticky-offset',
			'type'     => 'spacing',
			'left'     => 'false',
			'right'    => 'false',
			'bottom'   => 'false',
			'default'  => array(
				'padding-top' => '15'
			),
			'required' => array( 'blog-single-sticky', 'equals', '1' )
		),
		array(
			'title'    => esc_html__( 'Content and sidebar padding on desktop', 'crane' ),
			'subtitle' => esc_html__( 'Set top/bottom space of content area and sidebar', 'crane' ),
			'id'       => 'blog-single-padding',
			'type'     => 'spacing',
			'units'    => 'px',
			'left'     => 'false',
			'right'    => 'false',
			'compiler' => array( '.crane-blog-single .crane-content-inner, .crane-blog-single .crane-sidebar' ),
			'default'  => array(
				'padding-top'    => '80px',
				'padding-bottom' => '80px',
			)
		),
		array(
			'title'    => esc_html__( 'Content and sidebar padding on mobile', 'crane' ),
			'subtitle' => esc_html__( 'Set top/bottom space of content area and sidebar', 'crane' ),
			'id'       => 'blog-single-padding-mobile',
			'type'     => 'spacing',
			'units'    => 'px',
			'left'     => 'false',
			'right'    => 'false',
			'compiler' => true,
			'default'  => array(
				'padding-top'    => '40px',
				'padding-bottom' => '40px',
			)
		),
		array(
			'id'   => 'divider_blog_single',
			'type' => 'divide'
		),
		array(
			'id'      => 'blog-single-show-content-title',
			'type'    => 'checkbox',
			'title'   => esc_html__( 'Show post title in content?', 'crane' ),
			'default' => true,
		),
		array(
			'id'      => 'blog-single-show-meta-in-featured',
			'type'    => 'checkbox',
			'title'   => esc_html__( 'Show meta info inside featured image block?', 'crane' ),
			'default' => false,
		),
		array(
			'id'      => 'blog-single-show-featured',
			'type'    => 'checkbox',
			'title'   => esc_html__( 'Show Featured Image', 'crane' ),
			'default' => true,
		),
		/**
		 * @migration
		 * task initiator: CRANE-931
		 * before migration: .blog-single-post__top-wrapper__txt-wrapper__header
		 * after migration: .crane-featured-block__page-title
		 */

		array(
			'id'          => 'blog-fib-title-typography',
			'type'        => 'typography',
			'title'       => esc_html__( 'Featured image title typography', 'crane' ),
			'google'      => true,
			'font-backup' => false,
			'compiler'    => array( '.crane-featured-block__page-title' ),
			'units'       => 'px',
			'default'     => array(
				'color'       => '#fff',
				'font-weight' => '600',
				'font-family' => 'Open Sans',
				'google'      => true,
				'font-size'   => '46px',
				'line-height' => '60px'
			),
		),
		/**
		 * @migration
		 * task initiator: CRANE-931
		 * before migration: .blog-single-post__top-wrapper__txt-wrapper--right__txt--top
		 * after migration: .crane-featured-block__categories li
		 */

		array(
			'id'          => 'blog-fib-category-typography',
			'type'        => 'typography',
			'title'       => esc_html__( 'Featured image category typography', 'crane' ),
			'google'      => true,
			'font-backup' => false,
			'compiler'    => array( '.crane-featured-block__categories li a' ),
			'units'       => 'px',
			'default'     => array(
				'color'       => '#fff',
				'font-weight' => '600',
				'font-family' => 'Open Sans',
				'google'      => true,
				'font-size'   => '16px',
				'line-height' => '25px'
			)
		),
		array(
			'id'          => 'blog-fib-divider-color',
			'type'        => 'color_rgba',
			'transparent' => true,
			'title'       => esc_html__( 'Featured image divider color', 'crane' ),
			'default'     => array( 'color' => 'rgba(211,211,211,0.65)' ),
			'compiler'    => true,
		),
		array(
			'id'      => 'blog-single-show-comment-counter',
			'type'    => 'checkbox',
			'title'   => esc_html__( 'Show comment counter', 'crane' ),
			'default' => true,
		),
		array(
			'id'      => 'blog-single-show_share_button',
			'type'    => 'checkbox',
			'title'   => esc_html__( 'Show social share button', 'crane' ),
			'default' => true,
		),
		array(
			'id'      => 'blog-single-show-author-info',
			'type'    => 'checkbox',
			'default' => true,
			'title'   => esc_html__( 'Show author info block', 'crane' )
		),
		array(
			'id'      => 'blog-single-show-prev-next-post',
			'type'    => 'checkbox',
			'default' => true,
			'title'   => esc_html__( 'Show navigation between posts', 'crane' )
		),
		array(
			'id'      => 'blog-single-show-related-posts',
			'type'    => 'checkbox',
			'default' => true,
			'title'   => esc_html__( 'Show related posts block', 'crane' )
		),
		array(
			'id'       => 'blog-single-related-posts-hover-type',
			'type'     => 'select',
			'title'    => esc_html__( 'Hover type of related posts', 'crane' ),
			'options'  => array(
				'standard'       => esc_html__( 'Just link', 'crane' ),
				'hover-gradient' => esc_html__( 'Hover with gradient', 'crane' )
			),
			'compiler' => true,
			'default'  => 'hover-gradient',
			'required' => array(
				'blog-single-show-related-posts',
				'equals',
				true
			)
		),
		array(
			'id'          => 'blog-single-related-posts-gradient',
			'type'        => 'color_gradient',
			'title'       => esc_html__( 'Gradient color', 'crane' ),
			'compiler'    => true,
			'transparent' => false,
			'default'     => array(
				'from' => '#7ad4f1',
				'to'   => '#cef17a',
			),
			'required'    => array( 'blog-single-related-posts-hover-type', 'equals', array( 'hover-gradient' ) ),
		),
		array(
			'id'      => 'blog-single-show-tags',
			'type'    => 'checkbox',
			'default' => true,
			'title'   => esc_html__( 'Show tags block', 'crane' )
		),
		array(
			'id'       => 'blog-single-footer_preset',
			'type'     => 'select',
			'title'    => esc_html__( 'Footer preset', 'crane' ),
			'subtitle' => sprintf( esc_html__( 'You can %1$s the default footer or add a new one on the %2$sFooters%3$s page.', 'crane' ), crane_get_footer_by_option( 'blog-footer_preset', 'theme-options' ), '<a target="_blank" href="' . admin_url() . 'edit.php?post_type=crane_footer">', '</a>' ),
			'options'  => $footer_presets,
			'default'  => 'default'
		),
		array(
			'id'       => 'blog-single-footer_appearance',
			'type'     => 'radio',
			'title'    => esc_html__( 'Footer appearance', 'crane' ),
			'options'  => array(
				'default'            => 'default',
				'appearance-regular' => 'regular',
				'appearance-fixed'   => 'fixed',
			),
			'default'  => 'default',
			'required' => array( 'blog-footer_preset', '!=', false ),
			'hint'     => array(
				'content' => esc_html__( "Regular footer appearance is just common footer acting like usual. Fixed footer appearance is fixed to bottom footer. It acts like sticky footer with 'moving out of the content' effect. Please note that fixed footer works great on small and medium height footers. Footers that have many content info and as a result have height more than browser height will face visibility issues. Also fixed footer will became a regular footer on mobile devices for same reasons", 'crane' )
			),
		),
	)
) );


if ( class_exists( 'WooCommerce' ) ) {

	Redux::setSection( $crane_opt_name, array(
		'title' => esc_html__( 'Shop', 'crane' ),
		'id'    => 'shop-section',
		'icon'  => 'fa fa-shopping-cart',
	) );


	Redux::setSection( $crane_opt_name, array(
		'title'      => esc_html__( 'Shop general settings', 'crane' ),
		'id'         => 'shop-general-settings',
		'subsection' => true,
		'fields'     => array(
			array(
				'id'      => 'shop-is-catalog',
				'type'    => 'switch',
				'title'   => esc_html__( 'Switch shop to catalog', 'crane' ),
				'default' => false,
			),
			array(
				'id'      => 'ajax-add-to-cart',
				'type'    => 'switch',
				'title'   => esc_html__( 'Enable Ajax Add to cart buttons on archive ans single product',
					'crane' ),
				'default' => '1',
			),
		)
	) );


	Redux::setSection( $crane_opt_name, array(
		'title'      => esc_html__( 'Product archive settings', 'crane' ),
		'id'         => 'product-archive-settings',
		'subsection' => true,
		'fields'     => array(
			array(
				'id'      => 'shop-has-sidebar',
				'type'    => 'select',
				'title'   => esc_html__( 'Sidebar position', 'crane' ),
				'options' => array(
					'none'     => esc_html__( 'Hide sidebar', 'crane' ),
					'at-right' => esc_html__( 'At right', 'crane' ),
					'at-left'  => esc_html__( 'At left', 'crane' ),
				),
				'default' => 'at-right'
			),
			array(
				'id'       => 'shop-sidebar',
				'type'     => 'select',
				'title'    => esc_html__( 'Sidebar content', 'crane' ),
				'data'     => 'sidebars',
				'default'  => 'crane_basic_sidebar',
				'required' => array( 'shop-has-sidebar', 'equals', array( 'at-left', 'at-right' ) )
			),
			array(
				'title'    => esc_html__( 'Sidebar width, %', 'crane' ),
				'id'       => 'shop-sidebar-width',
				'type'     => 'slider',
				'default'  => 25,
				'min'      => 0,
				'max'      => 100,
				'required' => array( 'shop-has-sidebar', 'equals', array( 'at-left', 'at-right' ) )
			),
			array(
				'title'    => esc_html__( 'Page content width, %', 'crane' ),
				'id'       => 'shop-content-width',
				'type'     => 'slider',
				'default'  => 75,
				'min'      => 0,
				'max'      => 100,
				'required' => array( 'shop-has-sidebar', 'equals', array( 'at-left', 'at-right' ) )
			),
			array(
				'title'    => esc_html__( 'Sticky sidebar', 'crane' ),
				'id'       => 'shop-archive-sticky',
				'type'     => 'switch',
				'default'  => '0',
				'required' => array( 'shop-has-sidebar', 'equals', array( 'at-right', 'at-left' ) )
			),
			array(
				'title'    => esc_html__( 'Sticky sidebar top offset', 'crane' ),
				'id'       => 'shop-archive-sticky-offset',
				'type'     => 'spacing',
				'left'     => 'false',
				'right'    => 'false',
				'bottom'   => 'false',
				'default'  => array(
					'padding-top' => '15'
				),
				'required' => array( 'shop-archive-sticky', 'equals', '1' )
			),
			array(
				'title'    => esc_html__( 'Content and sidebar padding on desktop', 'crane' ),
				'subtitle' => esc_html__( 'Set top/bottom space of content area and sidebar', 'crane' ),
				'id'       => 'shop-archive-padding',
				'type'     => 'spacing',
				'units'    => 'px',
				'left'     => 'false',
				'right'    => 'false',
				'compiler' => array( '.crane-shop-archive .crane-content-inner, .crane-shop-archive .crane-sidebar' ),
				'default'  => array(
					'padding-top'    => '80px',
					'padding-bottom' => '80px',
				)
			),
			array(
				'title'    => esc_html__( 'Content and sidebar padding on mobile', 'crane' ),
				'subtitle' => esc_html__( 'Set top/bottom space of content area and sidebar', 'crane' ),
				'id'       => 'shop-archive-padding-mobile',
				'type'     => 'spacing',
				'units'    => 'px',
				'left'     => 'false',
				'right'    => 'false',
				'compiler' => true,
				'default'  => array(
					'padding-top'    => '40px',
					'padding-bottom' => '40px',
				)
			),
			array(
				'id'   => 'divider_product_archive',
				'type' => 'divide'
			),
			array(
				'title'   => esc_html__( 'Number of columns', 'crane' ),
				'id'      => 'shop-columns',
				'type'    => 'select',
				'options' => array(
					'2' => '2',
					'3' => '3',
					'4' => '4'
				),
				'default' => '3',
			),
			array(
				'title'   => esc_html__( 'Products per page', 'crane' ),
				'id'      => 'shop-per-page',
				'type'    => 'slider',
				'default' => 12,
				'min'     => 1,
				'max'     => 100,
			),
			array(
				'id'      => 'crane-shop-paginator',
				'type'    => 'select',
				'title'   => esc_html__( 'Pagination', 'crane' ),
				'options' => [
					'numbers' => esc_html__( 'Numeric pagination', 'crane' ),
				],
				'default' => 'numbers'
			),
			array(
				'id'       => 'shop-pagination-prev_next-type',
				'type'     => 'select',
				'title'    => esc_html__( 'Pagination type', 'crane' ),
				'subtitle' => esc_html__( 'Select type of next/prev buttons', 'crane' ),
				'options'  => array(
					'arrows' => esc_html__( 'Arrows', 'crane' ),
					'text'   => esc_html__( 'Text', 'crane' ),
				),
				'default'  => 'arrows',
				'required' => array( 'crane-shop-paginator', 'equals', array( 'numbers', 'show_more' ) )
			),
			array(
				'id'      => 'shop-design',
				'type'    => 'select',
				'title'   => esc_html__( 'Shop design', 'crane' ),
				'options' => array(
					'simple' => esc_html__( 'Simple', 'crane' ),
					'float'  => esc_html__( 'Float', 'crane' ),
				),
				'default' => 'simple'
			),
			array(
				'id'      => 'shop-show-image-type',
				'type'    => 'select',
				'title'   => esc_html__( 'Product image type', 'crane' ),
				'options' => array(
					'single'   => esc_html__( 'Single image', 'crane' ),
					'carousel' => esc_html__( 'Carousel from gallery', 'crane' ),
				),
				'default' => 'single'
			),
			array(
				'id'      => 'shop-show-star-rating',
				'type'    => 'switch',
				'title'   => esc_html__( 'Show product rating', 'crane' ),
				'default' => true,
			),
			array(
				'id'      => 'shop-show-description-excerpt',
				'type'    => 'switch',
				'title'   => esc_html__( 'Show product description', 'crane' ),
				'default' => false,
			),
			array(
				'id'      => 'shop-show-product-categories',
				'type'    => 'switch',
				'title'   => esc_html__( 'Show product categories', 'crane' ),
				'default' => false,
			),
			array(
				'id'      => 'shop-show-product-tags',
				'type'    => 'switch',
				'title'   => esc_html__( 'Show product tags', 'crane' ),
				'default' => false,
			),
			array(
				'id'      => 'shop-show-product-attributes',
				'type'    => 'select',
				'multi'   => true,
				'title'   => esc_html__( 'Select product attributes to show (if exist)', 'crane' ),
				'options' => crane_wc_get_attribute_taxonomies(),
				'default' => null,
			),
			array(
				'id'      => 'shop-show-product-filter',
				'type'    => 'switch',
				'title'   => esc_html__( 'Show products sorting filter', 'crane' ),
				'default' => true,
			),
			array(
				'id'       => 'shop-footer_preset',
				'type'     => 'select',
				'title'    => esc_html__( 'Footer preset', 'crane' ),
				'subtitle' => sprintf( esc_html__( 'You can %1$s the default footer or add a new one on the %2$sFooters%3$s page.', 'crane' ), crane_get_footer_by_option( 'shop-footer_preset', 'theme-options' ), '<a target="_blank" href="' . admin_url() . 'edit.php?post_type=crane_footer">', '</a>' ),
				'options'  => $footer_presets,
				'default'  => 'default'
			),
			array(
				'id'       => 'shop-footer_appearance',
				'type'     => 'radio',
				'title'    => esc_html__( 'Footer appearance', 'crane' ),
				'options'  => array(
					'default'            => 'default',
					'appearance-regular' => 'regular',
					'appearance-fixed'   => 'fixed',
				),
				'default'  => 'default',
				'required' => array( 'shop-footer_preset', '!=', false ),
				'hint'     => array(
					'content' => esc_html__( "Regular footer appearance is just common footer acting like usual. Fixed footer appearance is fixed to bottom footer. It acts like sticky footer with 'moving out of the content' effect. Please note that fixed footer works great on small and medium height footers. Footers that have many content info and as a result have height more than browser height will face visibility issues. Also fixed footer will became a regular footer on mobile devices for same reasons", 'crane' )
				),
			),
		)
	) );

	Redux::setSection( $crane_opt_name, array(
		'title'      => esc_html__( 'Product page settings', 'crane' ),
		'id'         => 'product-single-settings',
		'subsection' => true,
		'fields'     => array(
			array(
				'id'      => 'shop-single-breadcrumbs_view',
				'type'    => 'select',
				'title'   => esc_html__( 'Breadcrumbs view', 'crane' ),
				'options' => array(
					'only_name'     => esc_html__( 'Home &gt; Single product name', 'crane' ),
					'with_category' => esc_html__( 'Home &gt; Shop page &gt; Product category &gt; Single product name', 'crane' ),
				),
				'default' => 'with_category'
			),
			array(
				'title'   => esc_html__( 'Sidebar position', 'crane' ),
				'id'      => 'shop-single-has-sidebar',
				'type'    => 'select',
				'options' => array(
					'none'     => esc_html__( 'Hide sidebar', 'crane' ),
					'at-right' => esc_html__( 'At right', 'crane' ),
					'at-left'  => esc_html__( 'At left', 'crane' ),
				),
				'default' => 'none'
			),
			array(
				'title'    => esc_html__( 'Sidebar content', 'crane' ),
				'id'       => 'shop-single-sidebar',
				'type'     => 'select',
				'data'     => 'sidebars',
				'default'  => 'crane_basic_sidebar',
				'required' => array( 'shop-single-has-sidebar', 'equals', array( 'at-right', 'at-left' ) )
			),
			array(
				'title'    => esc_html__( 'Sidebar width, %', 'crane' ),
				'id'       => 'shop-single-sidebar-width',
				'type'     => 'slider',
				'default'  => 25,
				'min'      => 0,
				'max'      => 100,
				'required' => array( 'shop-single-has-sidebar', 'equals', array( 'at-right', 'at-left' ) )
			),
			array(
				'title'    => esc_html__( 'Page content width, %', 'crane' ),
				'id'       => 'shop-single-content-width',
				'type'     => 'slider',
				'default'  => 75,
				'min'      => 0,
				'max'      => 100,
				'required' => array( 'shop-single-has-sidebar', 'equals', array( 'at-right', 'at-left' ) )
			),
			array(
				'title'    => esc_html__( 'Sticky sidebar', 'crane' ),
				'id'       => 'shop-single-sticky',
				'type'     => 'switch',
				'default'  => '0',
				'required' => array( 'shop-single-has-sidebar', 'equals', array( 'at-right', 'at-left' ) )
			),
			array(
				'title'    => esc_html__( 'Sticky sidebar top offset', 'crane' ),
				'id'       => 'shop-single-sticky-offset',
				'type'     => 'spacing',
				'left'     => 'false',
				'right'    => 'false',
				'bottom'   => 'false',
				'default'  => array(
					'padding-top' => '15'
				),
				'required' => array( 'shop-single-sticky', 'equals', '1' )
			),
			array(
				'title'    => esc_html__( 'Content and sidebar padding on desktop', 'crane' ),
				'subtitle' => esc_html__( 'Set top/bottom space of content area and sidebar', 'crane' ),
				'id'       => 'shop-single-padding',
				'type'     => 'spacing',
				'units'    => 'px',
				'left'     => 'false',
				'right'    => 'false',
				'compiler' => array( '.crane-shop-single .crane-content-inner, .crane-shop-single .crane-sidebar' ),
				'default'  => array(
					'padding-top'    => '80px',
					'padding-bottom' => '80px',
				)
			),
			array(
				'title'    => esc_html__( 'Content and sidebar padding on mobile', 'crane' ),
				'subtitle' => esc_html__( 'Set top/bottom space of content area and sidebar', 'crane' ),
				'id'       => 'shop-single-padding-mobile',
				'type'     => 'spacing',
				'units'    => 'px',
				'left'     => 'false',
				'right'    => 'false',
				'compiler' => true,
				'default'  => array(
					'padding-top'    => '40px',
					'padding-bottom' => '40px',
				)
			),
			array(
				'id'      => 'shop-show-related',
				'type'    => 'switch',
				'title'   => esc_html__( 'Show related products', 'crane' ),
				'default' => false,
			),
			array(
				'title'    => esc_html__( 'Related products rows', 'crane' ),
				'id'       => 'shop-rows-related',
				'type'     => 'select',
				'options'  => array(
					'1' => '1',
					'2' => '2',
					'3' => '3',
					'4' => '4',
					'5' => '5'
				),
				'default'  => '2',
				'required' => array( 'shop-show-related', 'equals', array( '1' ) ),
			),
			array(
				'title'    => esc_html__( 'Related products columns', 'crane' ),
				'id'       => 'shop-columns-related',
				'type'     => 'select',
				'options'  => array(
					'3' => '3',
					'4' => '4'
				),
				'default'  => '4',
				'required' => array( 'shop-show-related', 'equals', array( '1' ) ),
			),
			array(
				'id'      => 'shop-single-show-prev-next-post',
				'type'    => 'checkbox',
				'default' => '0',
				'title'   => esc_html__( 'Show product side navigation', 'crane' )
			),
			array(
				'id'       => 'shop-single-footer_preset',
				'type'     => 'select',
				'title'    => esc_html__( 'Footer preset', 'crane' ),
				'subtitle' => sprintf( esc_html__( 'You can %1$s the default footer or add a new one on the %2$sFooters%3$s page.', 'crane' ), crane_get_footer_by_option( 'shop-footer_preset', 'theme-options' ), '<a target="_blank" href="' . admin_url() . 'edit.php?post_type=crane_footer">', '</a>' ),
				'options'  => $footer_presets,
				'default'  => 'default'
			),
			array(
				'id'       => 'shop-single-footer_appearance',
				'type'     => 'radio',
				'title'    => esc_html__( 'Footer appearance', 'crane' ),
				'options'  => array(
					'default'            => 'default',
					'appearance-regular' => 'regular',
					'appearance-fixed'   => 'fixed',
				),
				'default'  => 'default',
				'required' => array( 'shop-footer_preset', '!=', false ),
				'hint'     => array(
					'content' => esc_html__( "Regular footer appearance is just common footer acting like usual. Fixed footer appearance is fixed to bottom footer. It acts like sticky footer with 'moving out of the content' effect. Please note that fixed footer works great on small and medium height footers. Footers that have many content info and as a result have height more than browser height will face visibility issues. Also fixed footer will became a regular footer on mobile devices for same reasons", 'crane' )
				),
			),
		)
	) );

}


Redux::setSection( $crane_opt_name, array(
	'title'  => esc_html__( 'Search page', 'crane' ),
	'id'     => 'search',
	'icon'   => 'fa fa-search',
	'fields' => array(
		array(
			'id'      => 'search-has-sidebar',
			'type'    => 'select',
			'title'   => esc_html__( 'Sidebar position', 'crane' ),
			'options' => array(
				'none'     => esc_html__( 'Hide sidebar', 'crane' ),
				'at-right' => esc_html__( 'At right', 'crane' ),
				'at-left'  => esc_html__( 'At left', 'crane' ),
			),
			'default' => 'at-right'
		),
		array(
			'id'       => 'search-sidebar',
			'type'     => 'select',
			'title'    => esc_html__( 'Sidebar content', 'crane' ),
			'data'     => 'sidebars',
			'default'  => 'crane_basic_sidebar',
			'required' => array( 'search-has-sidebar', 'equals', array( 'at-left', 'at-right' ) )
		),
		array(
			'title'    => esc_html__( 'Sidebar width, %', 'crane' ),
			'id'       => 'search-sidebar-width',
			'type'     => 'slider',
			'default'  => 25,
			'min'      => 0,
			'max'      => 100,
			'required' => array( 'search-has-sidebar', 'equals', array( 'at-left', 'at-right' ) )
		),
		array(
			'title'    => esc_html__( 'Page content width, %', 'crane' ),
			'id'       => 'search-content-width',
			'type'     => 'slider',
			'default'  => 75,
			'min'      => 0,
			'max'      => 100,
			'required' => array( 'search-has-sidebar', 'equals', array( 'at-left', 'at-right' ) )
		),
		array(
			'title'    => esc_html__( 'Sticky sidebar', 'crane' ),
			'id'       => 'search-sticky',
			'type'     => 'switch',
			'default'  => '0',
			'required' => array( 'search-has-sidebar', 'equals', array( 'at-right', 'at-left' ) )
		),
		array(
			'title'    => esc_html__( 'Sticky sidebar top offset', 'crane' ),
			'id'       => 'search-sticky-offset',
			'type'     => 'spacing',
			'left'     => 'false',
			'right'    => 'false',
			'bottom'   => 'false',
			'default'  => array(
				'padding-top' => '15'
			),
			'required' => array( 'search-sticky', 'equals', '1' )
		),
		array(
			'title'    => esc_html__( 'Content and sidebar padding on desktop', 'crane' ),
			'subtitle' => esc_html__( 'Set top/bottom space of content area and sidebar', 'crane' ),
			'id'       => 'search-padding',
			'type'     => 'spacing',
			'units'    => 'px',
			'left'     => 'false',
			'right'    => 'false',
			'compiler' => array( '.crane-search-page .crane-content-inner, .crane-search-page .crane-sidebar' ),
			'default'  => array(
				'padding-top'    => '80px',
				'padding-bottom' => '80px',
			)
		),
		array(
			'title'    => esc_html__( 'Content and sidebar padding on mobile', 'crane' ),
			'subtitle' => esc_html__( 'Set top/bottom space of content area and sidebar', 'crane' ),
			'id'       => 'search-padding-mobile',
			'type'     => 'spacing',
			'units'    => 'px',
			'left'     => 'false',
			'right'    => 'false',
			'compiler' => true,
			'default'  => array(
				'padding-top'    => '40px',
				'padding-bottom' => '40px',
			)
		),
		array(
			'id'      => 'crane-search-paginator',
			'type'    => 'select',
			'title'   => esc_html__( 'Pagination', 'crane' ),
			'options' => [
				'numbers'   => esc_html__( 'Numeric pagination', 'crane' ),
				'show_more' => esc_html__( 'With [Show more] button', 'crane' ),
				'scroll'    => esc_html__( 'Infinity Scroll', 'crane' ),
			],
			'default' => 'numbers'
		),
		array(
			'id'       => 'search-pagination-prev_next-type',
			'type'     => 'select',
			'title'    => esc_html__( 'Pagination type', 'crane' ),
			'subtitle' => esc_html__( 'Select type of next/prev buttons', 'crane' ),
			'options'  => array(
				'arrows' => esc_html__( 'Arrows', 'crane' ),
				'text'   => esc_html__( 'Text', 'crane' ),
			),
			'default'  => 'text',
			'required' => array( 'crane-search-paginator', 'equals', array( 'numbers', 'show_more' ) )
		),
		array(
			'id'       => 'search-footer_preset',
			'type'     => 'select',
			'title'    => esc_html__( 'Footer preset', 'crane' ),
			'subtitle' => sprintf( esc_html__( 'You can %1$s the default footer or add a new one on the %2$sFooters%3$s page.', 'crane' ), crane_get_footer_by_option( 'blog-footer_preset', 'theme-options' ), '<a target="_blank" href="' . admin_url() . 'edit.php?post_type=crane_footer">', '</a>' ),
			'options'  => $footer_presets,
			'default'  => 'default'
		),
		array(
			'id'       => 'search-footer_appearance',
			'type'     => 'radio',
			'title'    => esc_html__( 'Footer appearance', 'crane' ),
			'options'  => array(
				'default'            => 'default',
				'appearance-regular' => 'regular',
				'appearance-fixed'   => 'fixed',
			),
			'default'  => 'default',
			'required' => array( 'search-footer_preset', '!=', false ),
			'hint'     => array(
				'content' => esc_html__( "Regular footer appearance is just common footer acting like usual. Fixed footer appearance is fixed to bottom footer. It acts like sticky footer with 'moving out of the content' effect. Please note that fixed footer works great on small and medium height footers. Footers that have many content info and as a result have height more than browser height will face visibility issues. Also fixed footer will became a regular footer on mobile devices for same reasons", 'crane' )
			),
		),
	)
) );

Redux::setSection( $crane_opt_name, array(
	'title'  => esc_html__( 'Footer', 'crane' ),
	'id'     => 'footer-general',
	'icon'   => 'fa fa-laptop',
	'fields' => array(
		array(
			'id'     => 'global-section-footer',
			'type'   => 'section',
			'title'  => esc_html__( 'Default footer', 'crane' ),
			'indent' => true
		),
		array(
			'id'       => 'footer_preset_global',
			'type'     => 'select',
			'title'    => esc_html__( 'Footer default preset', 'crane' ),
			'subtitle' => sprintf( esc_html__( 'You can %1$s the default footer or add a new one on the %2$sFooters%3$s page.', 'crane' ), crane_get_footer_by_option( 'footer_preset_global', 'theme-options' ), '<a target="_blank" href="' . admin_url() . 'edit.php?post_type=crane_footer">', '</a>' ),
			'options'  => $footer_presets_global,
			'default'  => 'basic-footer'
		),
		array(
			'id'       => 'footer_appearance',
			'type'     => 'radio',
			'title'    => esc_html__( 'Footer appearance', 'crane' ),
			'options'  => array(
				'appearance-regular' => 'regular',
				'appearance-fixed'   => 'fixed',
			),
			'default'  => 'appearance-regular',
			'required' => array( 'footer_preset_global', '!=', false ),
			'hint'     => array(
				'content' => esc_html__( "Regular footer appearance is just common footer acting like usual. Fixed footer appearance is fixed to bottom footer. It acts like sticky footer with 'moving out of the content' effect. Please note that fixed footer works great on small and medium height footers. Footers that have many content info and as a result have height more than browser height will face visibility issues. Also fixed footer will became a regular footer on mobile devices for same reasons", 'crane' )
			),
		),
		array(
			'id'     => 'global-section-footer-end',
			'type'   => 'section',
			'indent' => false,
		),

	)
) );

Redux::setSection( $crane_opt_name, array(
	'id'     => 'crane-social',
	'title'  => esc_html__( 'Social share links', 'crane' ),
	'desc'   => esc_html__( 'Social share links to show on click to social share button. Button can be shown at Blog archive (standard layout) page, Blog single post or Portfolio single page. Button visibility is controlled from Portfolio and Blog settings.', 'crane' ),
	'icon'   => 'fa fa-share-alt',
	'fields' => array(
		array(
			'id'      => 'share-social-facebook',
			'type'    => 'checkbox',
			'default' => '1',
			'title'   => esc_html__( 'Facebook', 'crane' )
		),
		array(
			'id'      => 'share-social-twitter',
			'type'    => 'checkbox',
			'default' => '1',
			'title'   => esc_html__( 'Twitter', 'crane' )
		),
		array(
			'id'      => 'share-social-googleplus',
			'type'    => 'checkbox',
			'default' => '1',
			'title'   => esc_html__( 'Google+', 'crane' )
		),
		array(
			'id'      => 'share-social-pinterest',
			'type'    => 'checkbox',
			'default' => '1',
			'title'   => esc_html__( 'Pinterest', 'crane' )
		),
		array(
			'id'      => 'share-social-linkedin',
			'type'    => 'checkbox',
			'default' => '1',
			'title'   => esc_html__( 'Linkedin', 'crane' )
		),
	)
) );

Redux::setSection( $crane_opt_name, array(
	'title'  => esc_html__( '404 page', 'crane' ),
	'id'     => 'crane-404-error-page',
	'icon'   => 'fa fa-question-circle',
	'fields' => array(
		array(
			'title'   => esc_html__( '404 page type', 'crane' ),
			'id'      => '404-type',
			'type'    => 'select',
			'hint'    => array(
				'content' => esc_html__( "You can easily create any page with page builder or choose from existing pages and set it to be shown as 404 page. Note: The 'Default 404 page' comes bundled with theme and won't be shown at 'Pages'. Also if you choose to set your custom 404 page all settings such as menu, footer and etc will be controlled within that page.", 'crane' )
			),
			'options' => array(
				'default' => esc_html__( 'Default 404 page', 'crane' ),
				'page'    => esc_html__( 'Existing page with its contents ', 'crane' ),
			),
			'default' => 'default'
		),
		array(
			'title'    => esc_html__( 'Select the page', 'crane' ),
			'id'       => '404-page',
			'type'     => 'select',
			'data'     => 'pages',
			'default'  => '',
			'required' => array( '404-type', 'equals', array( 'page' ) )
		),
		array(
			'id'       => '404-title',
			'type'     => 'text',
			'title'    => esc_html__( 'Title', 'crane' ),
			'hint'     => array(
				'content' => esc_html__( "Title for default 404 page.", 'crane' )
			),
			'default'  => esc_html__( 'Oops, This Page Could Not Be Found!', 'crane' ),
			'required' => array( '404-type', 'equals', array( 'default' ) )
		),
		array(
			'id'       => '404-text',
			'type'     => 'textarea',
			'title'    => esc_html__( 'Text', 'crane' ),
			'hint'     => array(
				'content' => esc_html__( "Text for default 404 page.", 'crane' )
			),
			'default'  => esc_html__( 'Unfortunately, the page was not found. It was deleted or moved and is now at another address.', 'crane' ),
			'required' => array( '404-type', 'equals', array( 'default' ) )
		),
		array(
			'id'       => '404-footer_preset',
			'type'     => 'select',
			'title'    => esc_html__( 'Footer preset', 'crane' ),
			'desc'     => sprintf( esc_html__( 'You can edit the default footer or add a new one on the %sFooters%s page.', 'crane' ), '<a target="_blank" href="' . admin_url() . 'edit.php?post_type=crane_footer">', '</a>' ),
			'options'  => $footer_presets,
			'default'  => 'default',
			'required' => array( '404-type', 'equals', array( 'default' ) )
		),
		array(
			'id'       => '404-footer_appearance',
			'type'     => 'radio',
			'title'    => esc_html__( 'Footer appearance', 'crane' ),
			'hint'     => array(
				'content' => esc_html__( "Regular footer appearance is just common footer acting like usual. Fixed footer appearance is fixed to bottom footer. It acts like sticky footer with 'moving out of the content' effect. Please note that fixed footer works great on small and medium height footers. Footers that have many content info and as a result have height more than browser height will face visibility issues. Also fixed footer will became a regular footer on mobile devices for same reasons", 'crane' )
			),
			'options'  => array(
				'default'            => 'default',
				'appearance-regular' => 'regular',
				'appearance-fixed'   => 'fixed',
			),
			'default'  => 'default',
			'required' => array( '404-type', 'equals', array( 'default' ) )
		),
	)
) );


Redux::setSection( $crane_opt_name, array(
	'title'  => esc_html__( 'Custom code', 'crane' ),
	'id'     => 'crane-custom-code',
	'icon'   => 'fa fa-code',
	'fields' => array(
		array(
			'id'                => 'custom-css',
			'type'              => 'crane_custom_css',
			'class'             => 'craneCodeMirrorArea langType-css',
			'validate'          => 'validate_crane_custom_css',
			'validate_callback' => 'validate_crane_custom_css_empty',
			'title'             => esc_html__( 'Custom CSS', 'crane' ),
			'subtitle'          => esc_html__( 'Can be used to output additional CSS.', 'crane' ),
			'sub_type'          => 'codemirror',
			'content_type'      => 'css',
		),
		array(
			'id'           => 'custom-html_head',
			'type'         => 'textarea',
			'class'        => 'craneCodeMirrorArea langType-html',
			'title'        => esc_html__( 'Custom HTML (Head)', 'crane' ),
			'subtitle'     => esc_html__( 'Can be used to output additional HTML code. For example, it can be used to insert Google Analytics. The output will be inserted into the &lt;HEAD&gt; of every webpage. Allowed HTML tags: &lt;style&gt;, &lt;meta&gt;, &lt;link&gt;, &lt;script&gt;', 'crane' ),
			'sub_type'     => 'codemirror',
			'content_type' => 'html',
		),
		array(
			'id'           => 'custom-html',
			'type'         => 'textarea',
			'class'        => 'craneCodeMirrorArea langType-html',
			'title'        => esc_html__( 'Custom HTML (Footer)', 'crane' ),
			'subtitle'     => esc_html__( 'Can be used to output additional HTML code. The output will be inserted in the footer before the closing tag &lt;/BODY&gt;', 'crane' ),
			'sub_type'     => 'codemirror',
			'content_type' => 'html',
		)

	)
) );


Redux::setSection( $crane_opt_name, array(
	'title'  => esc_html__( 'Advanced settings', 'crane' ),
	'id'     => 'crane-advanced-settings',
	'icon'   => 'el el-cog-alt',
	'fields' => array(
		array(
			'id'      => 'maintenance-mode',
			'type'    => 'checkbox',
			'default' => '0',
			'title'   => esc_html__( 'Maintenance Mode', 'crane' ),
		),
		array(
			'title'    => esc_html__( 'Maintenance page', 'crane' ),
			'desc'     => esc_html__( 'Show for site visitors only specific page', 'crane' ),
			'id'       => 'maintenance-page',
			'type'     => 'select',
			'data'     => 'pages',
			'default'  => '',
			'required' => array( 'maintenance-mode', 'equals', array( '1' ) )
		),
		array(
			'id'       => 'maintenance-503',
			'type'     => 'checkbox',
			'default'  => '0',
			'title'    => esc_html__( 'Send 503 status', 'crane' ),
			'desc'     => esc_html__( 'Respond with 503 status (Service Temporarily Unavailable)', 'crane' ),
			'required' => array( 'maintenance-mode', 'equals', array( '1' ) )
		),
		array(
			'id'      => 'minify-js',
			'type'    => 'checkbox',
			'default' => '0',
			'title'   => esc_html__( 'Minify theme JavaScript files', 'crane' )
		),
		array(
			'id'      => 'minify-css',
			'type'    => 'checkbox',
			'default' => '0',
			'title'   => esc_html__( 'Minify theme CSS files', 'crane' )
		),
		array(
			'id'   => 'divider_advanced-settings_1',
			'type' => 'divide'
		),
		array(
			'id'    => 'custom-image-sizes',
			'type'  => 'crane_add_image_sizes',
			'title' => esc_html__( 'Custom image sizes', 'crane' )
		),
	)
) );


Redux::setSection( $crane_opt_name, array(
	'title'  => esc_html__( 'Privacy Preferences', 'crane' ),
	'id'     => 'crane-privacy',
	'icon'   => 'el el-book',
	'fields' => array(
		array(
			'id'       => 'privacy-google_fonts',
			'type'     => 'switch',
			'title'    => esc_html__( 'Google Fonts location', 'crane' ),
			'subtitle' => esc_html__( 'When set to [Local], the Google fonts will be connected from current upload folder. Set to [CDN] to use the Google CDN service.', 'crane' ),
			'on'       => esc_html__( 'CDN', 'crane' ),
			'off'      => esc_html__( 'Local', 'crane' ),
			'default'  => true,
		),
		array(
			'id'       => 'privacy-preferences',
			'type'     => 'switch',
			'default'  => false,
			'title'    => esc_html__( 'Privacy Preferences', 'crane' ),
			'subtitle' => esc_html__( 'Turning on shows Privacy Preferences toolbar and other embeds privacy options', 'crane' ),
		),
		array(
			'id'                    => 'privacy-toolbar-bg-color',
			'type'                  => 'background',
			'title'                 => esc_html__( 'Privacy toolbar background color', 'crane' ),
			'background-repeat'     => false,
			'background-attachment' => false,
			'background-position'   => false,
			'background-image'      => false,
			'background-size'       => false,
			'preview'               => false,
			'transparent'           => false,
			'compiler'              => array( '.crane-privacy-toolbar' ),
			'required'              => array( 'privacy-preferences', 'equals', true ),
			'default'               => array(
				'background-color' => '#1b1f26',
			),
		),
		array(
			'id'             => 'privacy-toolbar-typography',
			'type'           => 'typography',
			'title'          => esc_html__( 'Privacy toolbar text typography', 'crane' ),
			'compiler'       => array( '.crane-privacy-toolbar__text' ),
			'google'         => true,
			'line-height'    => false,
			'text-transform' => true,
			'all_styles'     => true,
			'font-backup'    => false,
			'text-align'     => false,
			'units'          => 'px',
			'required'       => array( 'privacy-preferences', 'equals', true ),
			'default'        => array(
				'color'          => '#fff',
				'font-family'    => 'Open Sans',
				'font-weight'    => '400',
				'text-transform' => 'none',
				'font-size'      => '14px',
				'google'         => true
			),
		),
		array(
			'id'                    => 'privacy-placeholder-bg-color',
			'type'                  => 'background',
			'title'                 => esc_html__( 'Privacy placeholder background color', 'crane' ),
			'background-repeat'     => false,
			'background-attachment' => false,
			'background-position'   => false,
			'background-image'      => false,
			'background-size'       => false,
			'preview'               => false,
			'transparent'           => false,
			'compiler'              => array( '.crane-privacy-blocked-content' ),
			'required'              => array( 'privacy-preferences', 'equals', true ),
			'default'               => array(
				'background-color' => '#424242',
			),
		),
		array(
			'id'             => 'privacy-placeholder-typography',
			'type'           => 'typography',
			'title'          => esc_html__( 'Privacy placeholder text typography', 'crane' ),
			'compiler'       => array( '.crane-privacy-blocked-content__txt' ),
			'google'         => true,
			'line-height'    => false,
			'text-transform' => true,
			'all_styles'     => true,
			'font-backup'    => false,
			'text-align'     => false,
			'units'          => 'px',
			'required'       => array( 'privacy-preferences', 'equals', true ),
			'default'        => array(
				'color'          => '#fff',
				'font-family'    => 'Open Sans',
				'font-weight'    => '400',
				'text-transform' => 'none',
				'font-size'      => '14px',
				'google'         => true
			),
		),
		array(
			'id'       => 'privacy-embeds',
			'type'     => 'switch',
			'default'  => false,
			'title'    => esc_html__( 'Embeds Privacy', 'crane' ),
			'subtitle' => esc_html__( 'Turning on prevent embeds from loading until user consent is given', 'crane' ),
			'required' => array( 'privacy-preferences', 'equals', true ),
		),
		array(
			'id'       => 'privacy-expiration',
			'type'     => 'slider',
			'title'    => esc_html__( 'Embeds Cookie Expiration', 'crane' ),
			'subtitle' => esc_html__( 'Control how long the cookie will be stored, in days', 'crane' ),
			'default'  => 30,
			'min'      => 1,
			'max'      => 365,
			'required' => array( 'privacy-embeds', 'equals', true ),
		),
		array(
			'id'       => 'privacy-services',
			'type'     => 'select',
			'multi'    => true,
			'title'    => esc_html__( 'Embeds Types', 'crane' ),
			'subtitle' => esc_html__( 'Select the types of embeds which you would like to require consent', 'crane' ),
			'options'  => crane_get_privacy_elements( true ),
			'required' => array( 'privacy-embeds', 'equals', true ),
		),
	)
) );


Redux::setSection( $crane_opt_name, array(
	'title'  => esc_html__( 'Backup and Import settings', 'crane' ),
	'id'     => 'crane-backup-options',
	'icon'   => 'el el-wrench',
	'fields' => array(
		array(
			'id'         => 'crane-backup-import',
			'type'       => 'crane_backup_import',
			'full_width' => true,
			'compiler'   => false,
		),
		array(
			'id'         => 'crane-import-log',
			'type'       => 'crane_import_log',
			'full_width' => true,
			'compiler'   => false,
		),
	)
) );

/*
 * <--- END SECTIONS
 */


/**
 * Custom function for the callback validation
 **/
if ( ! function_exists( 'crane_validate_uniq_slug' ) ) {

	function crane_validate_uniq_slug( $field, $value, $existing_value ) {
		global $wpdb, $wp_rewrite;

		$error = false;
		$value = trim( $value );

		$clean_value = str_replace( array(
			' ',
			',',
			'.',
			'"',
			"'",
			'/',
			"\\",
			'+',
			'=',
			')',
			'(',
			'*',
			'&',
			'^',
			'%',
			'$',
			'#',
			'@',
			'!',
			'~',
			'`',
			'<',
			'>',
			'?',
			'[',
			']',
			'{',
			'}',
			'|',
			':',
			';',
		), '', $value );

		if ( $clean_value !== $value ) {
			$field['msg'] = esc_html__( 'Special characters are not supported.', 'crane' );
		}

		$value = $slug = $clean_value;

		$feeds = $wp_rewrite->feeds;
		if ( ! is_array( $feeds ) ) {
			$feeds = array();
		}

		// Post slugs must be unique across all posts.
		$check_sql       = "SELECT post_name FROM $wpdb->posts WHERE post_name = '%s' AND post_type IN ('page','post') LIMIT 1";
		$post_name_check = $wpdb->get_var( $wpdb->prepare( $check_sql, esc_sql( $slug ) ) );

		$rewrite_rules_slugs = get_option( 'crane_rewrite_rules_slugs' );
		if ( isset( $rewrite_rules_slugs[ $field['id'] ] ) ) {
			unset( $rewrite_rules_slugs[ $field['id'] ] );
		}

		if (
			$post_name_check ||
			in_array( $slug, $feeds ) ||
			apply_filters( 'wp_unique_post_slug_is_bad_attachment_slug', false, $slug ) ||
			( ! empty( $slug ) && is_array( $rewrite_rules_slugs ) && in_array( $slug, $rewrite_rules_slugs ) )
		) {
			$value          = $existing_value;
			$ununiq_message = sprintf( wp_kses( __( 'URL slug <code>%s</code> is in use, please choose another.', 'crane' ), array(
				'code'   => array(),
				'strong' => array()
			) ), $slug );
			$field['msg']   = ( ! empty( $field['msg'] ) ) ? $field['msg'] . ' ' . $ununiq_message : $ununiq_message;
			$error          = true;
		}

		$return['value'] = $value;
		if ( $error === true ) {
			$return['error'] = $field;
		}

		return $return;
	}

}


function crane_custom_css_style( $action, $css = '' ) {

	$opt_name = 'crane_theme_custom_css_style';

	switch ( $action ) {

		case 'get':
			return get_option( $opt_name );
			break;

		case 'delete':
			delete_option( $opt_name );
			crane_custom_css_style( 'set_time' );
			break;

		case 'update':
			update_option( $opt_name, $css );
			crane_custom_css_style( 'set_time' );
			break;

		case 'get_time':
			$_time = get_option( $opt_name . '__time' );
			if ( empty( $_time ) ) {
				$_time = time();
				crane_custom_css_style( 'set_time' );
			}

			return $_time;
			break;

		case 'set_time':
			return update_option( $opt_name . '__time', time() );
			break;


	}

}


/**
 * @param $options
 * @param $css
 * @param $changed_values
 *
 * @return bool
 */
function crane_redux_compiler_action( $options, $css, $changed_values = '' ) {

	$output_css = crane_redux_compiler_css( $options, $css );


	// Write to the file or DB
	if ( ! defined( 'FS_METHOD' ) ) {
		define( 'FS_METHOD', 'direct' );
	}
	global $wp_filesystem;
	if ( empty( $wp_filesystem ) ) {
		if ( file_exists( ABSPATH . '/wp-admin/includes/file.php' ) ) {
			require_once ABSPATH . '/wp-admin/includes/file.php';
			WP_Filesystem();
		}
	}
	if ( empty( $wp_filesystem ) ) {
		crane_custom_css_style( 'update', $output_css );

		return;
	}

	$filename = get_template_directory() . '/assets/css/custom-style.css';

	if ( file_exists( $filename && ! is_writable( $filename ) ) ) {
		$wp_filesystem->chmod( $filename, 0666 );
	}

	if ( is_writable( $filename ) || ( ! file_exists( $filename ) && is_writable( dirname( $filename ) ) ) ) {

		// Set the permission constants if not already set.
		if ( defined( 'FS_CHMOD_FILE' ) ) {
			$chmod = FS_CHMOD_FILE;
		} else {
			$chmod = 0664;
		}

		if ( $wp_filesystem->put_contents( $filename, $output_css, $chmod ) ) {
			crane_custom_css_style( 'file' );
		} else {
			crane_custom_css_style( 'db' );
		}

		crane_custom_css_style( 'update', $output_css );

	} else {

		crane_custom_css_style( 'update', $output_css );

	}

}

/**
 * @param $options
 * @param $option_name
 *
 * @return string
 */
function crane_redux_get_paddings( $options, $option_name ) {

	$paddings   = [];
	$paddings[] = isset( $options[ $option_name ]['padding-top'] ) ? 'padding-top: ' . $options[ $option_name ]['padding-top'] . ';' : '';
	$paddings[] = isset( $options[ $option_name ]['padding-bottom'] ) ? 'padding-bottom: ' . $options[ $option_name ]['padding-bottom'] . ';' : '';

	return implode( '', $paddings );

}


/**
 * @param $options
 * @param $css
 *
 * @return string
 */
function crane_redux_compiler_css( $options, $css ) {

	global $crane_mobile_width;

	$output_css = '/* Custom style from admin panel. Please, DO NOT edit this file, because it updates automatically. */
';
	$output_css .= $css;

	if ( ! isset( $options['page-title-line-decorators-switch'] ) ) {
		$options['page-title-line-decorators-switch'] = true;
	}
	if ( ! isset( $options['wide-layout'] ) ) {
		$options['wide-layout'] = false;
	}
	if ( ! isset( $options['main-grid-width'] ) ) {
		$options['main-grid-width'] = 1200;
	}
	if ( ! isset( $options['preloader-bg-color'] ) ) {
		$options['preloader-bg-color'] = '#ffffff';
	}
	if ( ! isset( $options['preloader-color'] ) ) {
		$options['preloader-color'] = '#93cb52';
	}

	if (
		! empty( $options['blog-single-related-posts-hover-type'] ) &&
		'hover-gradient' === $options['blog-single-related-posts-hover-type'] &&
		! empty( $options['blog-single-related-posts-gradient'] )
	) {
		$related_gradient = $options['blog-single-related-posts-gradient'];
		if ( ! empty( $related_gradient['from'] ) && ! empty( $related_gradient['to'] ) ) {
			$output_css .= '.crane-related-post__img-wrapper::before { background-image: linear-gradient(128deg, ' . $related_gradient['from'] . ' 0%, ' . $related_gradient['from'] . ' 33%, ' . $related_gradient['to'] . ' 98%, ' . $related_gradient['to'] . ' 100%);}';
		}
	}

	if ( ! ( $options['wide-layout'] ) ) {
		$output_css .= '.crane-container { max-width: ' . $options['main-grid-width'] . 'px;}';
	}

	if ( ! ( $options['page-title-line-decorators-switch'] ) ) {
		$output_css .= '.crane-page-title-heading::after, .crane-page-title-heading::before {content: none;}';
	}

	if ( ! empty( $options['selection-color'] ) ) {
		$output_css .= '
			::-moz-selection {background: ' . $options['selection-color'] . ';}';
		$output_css .= '
			::selection {background: ' . $options['selection-color'] . ';}';
	}

	$output_css .= '.preloader {background-color: ' . $options['preloader-bg-color'] . ';}';

	$output_css .= '
		.square-spin > div,
		.ball-pulse > div,
		.ball-pulse-sync > div,
		.ball-scale > div,
		.ball-rotate > div,
		.ball-rotate > div::before,
		.ball-rotate > div::after,
		.ball-scale-multiple > div,
		.ball-pulse-rise > div,
		.ball-grid-pulse > div,
		.ball-spin-fade-loader > div,
		.ball-zig-zag > div,
		.line-scale > div,
		.line-spin-fade-loader > div,
		.ball-clip-rotate-pulse > div:first-child,
		.pacman > div:nth-child(3),
		.pacman > div:nth-child(4),
		.pacman > div:nth-child(5),
		.pacman > div:nth-child(6) {
			background: ' . $options['preloader-color'] . ';
		}';

	$output_css .= '
		.ball-clip-rotate > div,
		.ball-clip-rotate-multiple > div,
		.ball-scale-ripple > div,
		.ball-scale-ripple-multiple > div {
			border: 2px solid ' . $options['preloader-color'] . ';
		}';

	$output_css .= '
		.pacman > div:first-of-type,
		.pacman > div:nth-child(2) {
			border-top-color: ' . $options['preloader-color'] . ';
			border-bottom-color: ' . $options['preloader-color'] . ';
			border-left-color: ' . $options['preloader-color'] . ';
		}';

	$output_css .= '
		.ball-clip-rotate-pulse > div:last-child,
		.ball-clip-rotate-multiple > div:last-child {
			border-color: ' . $options['preloader-color'] . ' transparent ' . $options['preloader-color'] . ' transparent !important;
		}';

	$output_css .= '
		.ball-triangle-path > div {
			border: 1px solid ' . $options['preloader-color'] . ';
		}';

	/**
	 * @migration
	 * task initiator: CRANE-931
	 * before migration: .blog-single-post__top-wrapper__txt-wrapper--left::after
	 * after migration: .crane-featured-block__page-title::after
	 */

	$divider_color = isset( $options['blog-fib-divider-color']['color'] ) ? $options['blog-fib-divider-color']['color'] : '';
	$divider_color = isset( $options['blog-fib-divider-color']['rgba'] ) ? $options['blog-fib-divider-color']['rgba'] : $divider_color;

	if ( $divider_color ) {
		$output_css .= '
		.crane-featured-block__page-title::after {
		background-color: ' . $divider_color . ';}
		';
	}

	if ( ! empty( $options['regular-txt-color'] ) ) {
		if ( $value = isset( $options['regular-txt-color'] )
			?
			$options['regular-txt-color']
			:
			null
		) {
			$output_css .= '.cat-item a::after, .widget .menu-item a::after, .crane-archive-widget li a::after, .widget .page_item a::after {background-color: ' . $value . '}';
		}
	}

	if ( ! empty( $options['footer-type-custom-regular-text-typography'] ) ) {
		if ( $value = isset( $options['footer-type-custom-regular-text-typography']['color'] )
			?
			$options['footer-type-custom-regular-text-typography']['color']
			:
			null
		) {
			$output_css .= '.footer-type-custom .cat-item a::after, .footer-type-custom .widget .menu-item a::after {background-color: ' . $value . '}';
		}
	}

	if ( ! empty( $options['footer-type-custom-list-border'] ) ) {
		$border   = array();
		$border[] = isset( $options['footer-type-custom-list-border']['border-bottom'] ) ? $options['footer-type-custom-list-border']['border-bottom'] : '';
		$border[] = isset( $options['footer-type-custom-list-border']['border-style'] ) ? $options['footer-type-custom-list-border']['border-style'] : '';
		$border[] = isset( $options['footer-type-custom-list-border']['border-color'] ) ? $options['footer-type-custom-list-border']['border-color'] : '';

		if ( ! empty( $border ) ) {
			$output_css .= '.footer-type-custom .cat-item .children, .footer-type-custom .widget .menu-item .children {border-top: ' . implode( ' ', $border ) . '}';
		}
	}

	if ( ! empty( $options['page-title-dimensions'] ) ) {
		if ( isset( $options['page-title-dimensions']['height'] ) ) {
			$output_css .= '.crane-page-title{height: auto;min-height: ' . $options['page-title-dimensions']['height'] . ';}';
		}
	}

	if ( ! empty( $options['primary-color'] ) ) {
		$output_css .= '.sticky .post__main__txt-wrapper{background-color:' . crane_hex2rgba( $options['primary-color'], .2 ) . ' !important;}';
	}

	if ( ! empty( $options['regular-page-padding-mobile'] ) ) {
		$output_css .= '@media (max-width: ' . $crane_mobile_width . 'px) { .crane-regular-page .crane-content-inner, .crane-regular-page .crane-sidebar {' . crane_redux_get_paddings( $options, 'regular-page-padding-mobile' ) . '} }';
	}

	if ( ! empty( $options['portfolio-archive-padding-mobile'] ) ) {
		$output_css .= '@media (max-width: ' . $crane_mobile_width . 'px) { .crane-portfolio-archive .crane-content-inner, .crane-portfolio-archive .crane-sidebar {' . crane_redux_get_paddings( $options, 'portfolio-archive-padding-mobile' ) . '} }';
	}

	if ( ! empty( $options['portfolio-single-padding-mobile'] ) ) {
		$output_css .= '@media (max-width: ' . $crane_mobile_width . 'px) { .crane-portfolio-single .crane-content-inner, .crane-portfolio-single .crane-sidebar {' . crane_redux_get_paddings( $options, 'portfolio-single-padding-mobile' ) . '} }';
	}

	if ( ! empty( $options['blog-archive-padding-mobile'] ) ) {
		$output_css .= '@media (max-width: ' . $crane_mobile_width . 'px) { .crane-blog-archive .crane-content-inner, .crane-blog-archive .crane-sidebar {' . crane_redux_get_paddings( $options, 'blog-archive-padding-mobile' ) . '} }';
	}

	if ( ! empty( $options['blog-single-padding-mobile'] ) ) {
		$output_css .= '@media (max-width: ' . $crane_mobile_width . 'px) { .crane-blog-single .crane-content-inner, .crane-blog-single .crane-sidebar {' . crane_redux_get_paddings( $options, 'blog-single-padding-mobile' ) . '} }';
	}

	if ( ! empty( $options['shop-archive-padding-mobile'] ) ) {
		$output_css .= '@media (max-width: ' . $crane_mobile_width . 'px) { .crane-shop-archive .crane-content-inner, .crane-shop-archive .crane-sidebar {' . crane_redux_get_paddings( $options, 'shop-archive-padding-mobile' ) . '} }';
	}

	if ( ! empty( $options['shop-single-padding-mobile'] ) ) {
		$output_css .= '@media (max-width: ' . $crane_mobile_width . 'px) { .crane-shop-single .crane-content-inner, .crane-shop-single .crane-sidebar {' . crane_redux_get_paddings( $options, 'shop-single-padding-mobile' ) . '} }';
	}

	if ( ! empty( $options['search-padding-mobile'] ) ) {
		$output_css .= '@media (max-width: ' . $crane_mobile_width . 'px) { .crane-search-page .crane-content-inner, .crane-search-page .crane-sidebar {' . crane_redux_get_paddings( $options, 'search-padding-mobile' ) . '} }';
	}


	return $output_css;

}


/**
 * Set global favicon for WP after theme options change
 */
function crane_add_favicon_from_redux() {

	$favicon_arr = Redux::getOption( 'crane_options', 'favicon' );
	if ( ! empty( $favicon_arr ) && ! empty( $favicon_arr['id'] ) ) {
		// for get_site_icon_url() function
		update_option( 'site_icon', $favicon_arr['id'] );
	} else {
		update_option( 'site_icon', '0' );
	}

}

/**
 * Enable or Disable google fonts loading from local directory
 */
function crane_check_local_google_fonts() {

	if ( class_exists( 'Grooni_Theme_Addons_GFonts' ) && class_exists( 'Redux' ) ) {

		$google_fonts = new Grooni_Theme_Addons_GFonts();

		$g_fonts_option = Redux::getOption( 'crane_options', 'privacy-google_fonts' );

		if ( ! empty( $g_fonts_option ) && $g_fonts_option ) {
			// ON - is CDN

			delete_transient( $google_fonts->get_opt_name() );
			delete_transient( $google_fonts->get_opt_name() . '__current' );
			delete_option( $google_fonts->get_opt_name() . '__downloaded' );

		} else {
			// OFF - is LOCAL

			$redux_need_fonts = $google_fonts->get_specific_fonts( 'redux', crane_get_redux_fields_with_gfonts() );
			$ua_need_fonts    = $google_fonts->get_specific_fonts( 'ultimate_addons' );

			$need_fonts = $ua_need_fonts + $redux_need_fonts;

			foreach ( $need_fonts as $_font ) {
				if ( ! empty( $_font['zip_url'] ) ) {
					$google_fonts->download_font( $_font['zip_url'] );
				}
			}

		}

	}

}

/**
 * Collect field with google fonts from redux options
 *
 */
function crane_get_redux_fields_with_gfonts() {

	$collected = array();

	if ( class_exists( 'Redux' ) ) {

		$fields_with_gfonts = array(
			'page-title-typography',
			'page-breadcrumbs-typography',
			'regular-txt-typography',
			'h1-typography',
			'h2-typography',
			'h3-typography',
			'h4-typography',
			'h5-typography',
			'h6-typography',
			'portfolio-archive-pagination_typography',
			'blog-fib-title-typography',
			'blog-fib-category-typography',
		);

		foreach ( $fields_with_gfonts as $field_name ) {
			$field_data = Redux::getOption( 'crane_options', $field_name );

			if ( ! empty( $field_data['font-family'] ) && ! empty( $field_data['google'] ) && $field_data['google'] ) {

				$variant = empty( $field_data['font-weight'] ) ? '400' : $field_data['font-weight'];
				$variant .= empty( $field_data['font-style'] ) ? '' : $field_data['font-style'];

				$collected[ $field_data['font-family'] ] = array(
					'font-family' => $field_data['font-family'],
					'variants'    => array( $variant ),
					'subsets'     => empty( $field_data['subsets'] ) ? array() : array( $field_data['subsets'] ),
				);
			}
		}

	}

	return $collected;

}


/**
 * Add font-fase style
 */
function crane_add_local_font_face() {

	$output = '';

	if ( class_exists( 'Grooni_Theme_Addons_GFonts' ) && class_exists( 'Redux' ) ) {

		$google_fonts = new Grooni_Theme_Addons_GFonts();

		$g_fonts_option = Redux::getOption( 'crane_options', 'privacy-google_fonts' );

		if ( '0' === $g_fonts_option ) {
			// OFF - is LOCAL

			$redux_need_fonts = $google_fonts->get_specific_fonts( 'redux', crane_get_redux_fields_with_gfonts() );
			$ua_need_fonts    = $google_fonts->get_specific_fonts( 'ultimate_addons' );

			$need_fonts = $ua_need_fonts + $redux_need_fonts;

			foreach ( $need_fonts as $_font ) {
				if ( ! empty( $_font['variants_css'] ) && is_array( $_font['variants_css'] ) ) {
					foreach ( $_font['variants_css'] as $variant => $css_data ) {
						$output .= $google_fonts->generate_font_face( $css_data );
					}
				}
			}

		}

	}

	return $output;

}


/**
 * Run functions if redux do save action
 */
function crane_add_functions_with_save_redux() {
	crane_add_favicon_from_redux();
	crane_check_local_google_fonts();
}


add_action( 'redux/options/' . $crane_opt_name . '/saved', 'crane_add_functions_with_save_redux' );
add_action( 'redux/options/' . $crane_opt_name . '/section/reset', 'crane_add_functions_with_save_redux' );
add_action( 'redux/options/' . $crane_opt_name . '/reset', 'crane_add_functions_with_save_redux' );


/**
 * Set global favicon for WP after theme options change
 */
function crane_add_favicon_from_redux_customize( $obj ) {

	$changeset_data = $obj->changeset_data();

	if ( ! isset( $changeset_data['crane::crane_options[favicon]'] ) || ! isset( $changeset_data['crane::crane_options[favicon]']['value'] ) ) {
		return;
	}

	$favicon_arr = $changeset_data['crane::crane_options[favicon]']['value'];
	$favicon_opt = get_option( 'site_icon' );

	if ( isset( $favicon_arr['id'] ) && $favicon_opt !== $favicon_arr['id'] ) {

		if ( $favicon_arr['id'] ) {
			$image_full  = wp_get_attachment_image_src( $favicon_arr['id'], 'full' );
			$image_thumb = wp_get_attachment_image_src( $favicon_arr['id'], 'thumbnail' );
		} else {
			$image_full  = [ '', '', '' ];
			$image_thumb = [ '', '', '' ];
		}

		Redux::setOption( 'crane_options', 'favicon', [
			'url'       => isset( $image_full[0] ) ? $image_full[0] : '',
			'id'        => $favicon_arr['id'],
			'height'    => isset( $image_full[2] ) ? strval( $image_full[2] ) : '',
			'width'     => isset( $image_full[1] ) ? strval( $image_full[1] ) : '',
			'thumbnail' => isset( $image_thumb[0] ) ? $image_thumb[0] : '',
		] );

		update_option( 'site_icon', $favicon_arr['id'] );
	}


}

add_action( 'customize_save_after', 'crane_add_favicon_from_redux_customize', 100, 1 );


/**
 * Check if favicon changed
 */
function crane_check_favicon() {
	if ( ! is_admin() ) {
		return;
	}

	$favicon_opt   = get_option( 'site_icon' );
	$favicon_redux = Redux::getOption( 'crane_options', 'favicon' );


	if ( isset( $favicon_redux['id'] ) && $favicon_opt !== $favicon_redux['id'] ) {

		if ( $favicon_opt ) {
			$image_full  = wp_get_attachment_image_src( $favicon_opt, 'full' );
			$image_thumb = wp_get_attachment_image_src( $favicon_opt, 'thumbnail' );
		} else {
			$image_full  = [ '', '', '' ];
			$image_thumb = [ '', '', '' ];
		}

		Redux::setOption( 'crane_options', 'favicon', [
			'url'       => isset( $image_full[0] ) ? $image_full[0] : '',
			'id'        => $favicon_opt,
			'height'    => isset( $image_full[2] ) ? strval( $image_full[2] ) : '',
			'width'     => isset( $image_full[1] ) ? strval( $image_full[1] ) : '',
			'thumbnail' => isset( $image_thumb[0] ) ? $image_thumb[0] : '',
		] );

	}


}

add_action( 'redux/construct', 'crane_check_favicon' );


if ( ! function_exists( 'crane_include_code_editor' ) ) {
	/**
	 * Enqueue scripts and styles of codemirror for textarea.
	 *
	 * @param $hook_suffix
	 */
	function crane_include_code_editor( $hook_suffix = '' ) {

		$allow_pages = array(
			'crane-theme_page_crane-theme-options',
			'customize_preview',
		);

		if ( empty( $hook_suffix ) && is_admin() && is_customize_preview() ) {
			$hook_suffix = 'customize_preview';
		}

		if ( ! in_array( $hook_suffix, $allow_pages ) ) {
			return;
		}

		$output   = '';
		$settings = false;

		foreach ( array( 'css', 'html' ) as $type ) {

			$codemirror_params = array( 'autoRefresh' => true, 'closeBrackets' => true );

			$settings = false;
			// function wp_enqueue_code_editor() since WP 4.9
			if ( function_exists( 'wp_enqueue_code_editor' ) ) {
				$settings = wp_enqueue_code_editor( array(
					'type'       => 'text/' . $type,
					'codemirror' => $codemirror_params
				) );
			}

			if ( false !== $settings ) {

				$output .= sprintf( '
					var craneMenuCodeMirror%3$sAreas = $("textarea.craneCodeMirrorArea.langType-%2$s");
						if (craneMenuCodeMirror%3$sAreas.length > 0) {
						$.each(craneMenuCodeMirror%3$sAreas, function(key, element) {
							var codeEditorObj = wp.codeEditor.initialize( element, %1$s );
							codeEditorObj.codemirror.on("change", function( cm ) {
								cm.save();
							});
						});
					}',
					wp_json_encode( $settings ),
					$type,
					strtoupper( $type )
				);

			}

		}


		// Add inline js
		if ( $output ) {
			wp_add_inline_script(
				'code-editor',
				'(function ($) { $(document).ready(function () {
				' . $output . '
				});})(jQuery)'
			);
		}

	}
}

add_action( 'admin_enqueue_scripts', 'crane_include_code_editor', 10, 1 );
