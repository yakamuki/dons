<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );
/**
 * Crane Theme Customizer
 *
 * @package crane
 */

if ( ! function_exists( 'crane_customize_register' ) ) {
	/**
	 * Add postMessage support for site title and description for the Theme Customizer.
	 *
	 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
	 */
	function crane_customize_register( $wp_customize ) {
		$wp_customize->get_setting( 'blogname' )->transport         = 'postMessage';
		$wp_customize->get_setting( 'blogdescription' )->transport  = 'postMessage';
		$wp_customize->get_setting( 'header_textcolor' )->transport = 'postMessage';
	}
}
add_action( 'customize_register', 'crane_customize_register' );


if ( ! function_exists( 'crane_customize_preview_js' ) ) {
	/**
	 * Binds JS handlers to make Theme Customizer preview reload changes asynchronously.
	 */
	function crane_customize_preview_js() {
		global $crane_options;

		$min_js = '';
		if ( isset( $crane_options['minify-js'] ) && $crane_options['minify-js'] ) {
			$min_js = '.min';
		}

		wp_enqueue_script( 'crane-customizer', get_template_directory_uri() . '/assets/js/admin/dist/customizer' . $min_js . '.js', array( 'customize-preview' ), CRANE_THEME_VERSION, true );
	}
}
add_action( 'customize_preview_init', 'crane_customize_preview_js' );


/**
 * Enqueue script for customize control.
 */
function crane_customize_enqueue() {

	global $crane_options;

	$min_css = '';
	if ( isset( $crane_options['minify-css'] ) && $crane_options['minify-css'] ) {
		$min_css = '.min';
	}

	wp_enqueue_style( 'crane-customize', get_template_directory_uri() . '/assets/css/customize' . $min_css . '.css', array(), CRANE_THEME_VERSION );
	wp_style_add_data( 'crane-customize', 'rtl', 'replace' );

}

add_action( 'customize_controls_enqueue_scripts', 'crane_customize_enqueue' );

