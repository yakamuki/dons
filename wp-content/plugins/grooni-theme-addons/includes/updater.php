<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );
/**
 * Theme update checker.
 *
 * @package Grooni_Theme_Addons
 */

if ( class_exists( 'Puc_v4_Factory' ) ) {

	$themes = array( 'crane' );

	if ( in_array( GROONI_THEME_ADDONS_CURRENT_THEME_SLUG, $themes ) ) {
		$theme_update_checker = Puc_v4_Factory::buildUpdateChecker(
			'http://updates.grooni.com/?action=get_metadata&slug=' . GROONI_THEME_ADDONS_CURRENT_THEME_SLUG,
			trailingslashit( GROONI_THEME_ADDONS_CURRENT_THEME_DIR ) . 'functions.php',
			GROONI_THEME_ADDONS_CURRENT_THEME_SLUG
		);
	}

	$plugins = [
		'grooni-theme-addons' => '/grooni-theme-addons/grooni-theme-addons.php',
		'js_composer'         => '/js_composer/js_composer.php',
		'Ultimate_VC_Addons'  => '/Ultimate_VC_Addons/Ultimate_VC_Addons.php',
		'revslider'           => '/revslider/revslider.php',
		'layerslider'         => '/LayerSlider/layerslider.php',
		'convertplug'         => '/convertplug/convertplug.php',
		'mpc-massive'         => '/mpc-massive/mpc-massive.php',
	];

	foreach ( $plugins as $plugin => $path ) {
		$plugins_root_dir = untrailingslashit( WP_PLUGIN_DIR );

		if ( file_exists( $plugins_root_dir . $path ) ) {
			if ( 'layerslider' == $plugin ) {
				$plugin = 'LayerSlider';
			}
			$plugin_update_checker = Puc_v4_Factory::buildUpdateChecker(
				'http://updates.grooni.com/?action=get_metadata&slug=' . $plugin,
				$plugins_root_dir . $path,
				$plugin
			);
		}
	}

}
