<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );
/**
 * Sets VisualComposer settings.
 *
 * @package Grooni_Theme_Addons
 */


function ct_vc_set_as_theme() {
	// remove "Design options", "Custom CSS" tabs under WP Dashboard -> Visual Composer page
	if ( function_exists( 'vc_set_as_theme' ) ) {
		vc_set_as_theme();
	}

	if ( class_exists( 'Vc_Manager' ) && defined( 'WPB_VC_VERSION' ) ) {

		include_once __DIR__ . '/vc-shortcodes/_widget.php';

		include_once __DIR__ . '/vc-shortcodes/vc-shortcode-blog.php';
		include_once __DIR__ . '/vc-shortcodes/vc-shortcode-timeline.php';
		include_once __DIR__ . '/vc-shortcodes/vc-shortcode-timeline_item.php';
		include_once __DIR__ . '/vc-shortcodes/vc-shortcode-toggle.php';
		include_once __DIR__ . '/vc-shortcodes/vc-shortcode-progressbar.php';
		include_once __DIR__ . '/vc-shortcodes/vc-shortcode-portfolio.php';

		include_once __DIR__ . '/vc-shortcodes/vc-shortcode-banner.php';
		include_once __DIR__ . '/vc-shortcodes/vc-shortcode-images.php';
		include_once __DIR__ . '/vc-shortcodes/vc-shortcode-portfolio_category.php';
		include_once __DIR__ . '/vc-shortcodes/vc-shortcode-recent_comments.php';
		include_once __DIR__ . '/vc-shortcodes/vc-shortcode-recent_posts.php';


		vc_set_shortcodes_templates_dir( __DIR__ . '/vc-templates' );

		function grooni_require_vc_extend() {
			require_once __DIR__ . '/extend-vc.php';
		}
		add_action( 'init', 'grooni_require_vc_extend', 10 );

		function grooni_vc_change_panel_editor() {
			include_once __DIR__ . '/class-CT_Vc_Templates_Panel_Editor.php';

			visual_composer()->setTemplatesPanelEditor( new CT_Vc_Templates_Panel_Editor() );
		}

		add_action( 'vc_after_init_vc', 'grooni_vc_change_panel_editor' );

		/**
		 * Add default support post types for Visual Composer
		 */
		if ( function_exists( 'vc_set_default_editor_post_types' ) ) {
			vc_set_default_editor_post_types( array( 'page', 'crane_portfolio', 'crane_footer' ) );
		}

	}

}

add_action( 'vc_before_init', 'ct_vc_set_as_theme' );


function grooni_vc_after_init() {
	$vc_updater       = vc_manager()->updater();
	$vc_updateManager = $vc_updater->updateManager();

	remove_filter( 'upgrader_pre_download', array( $vc_updater, 'preUpgradeFilter' ) );
	remove_filter( 'pre_set_site_transient_update_plugins', array( $vc_updateManager, 'check_update' ) );
	remove_filter( 'plugins_api', array( $vc_updateManager, 'check_info' ) );
	remove_action( 'in_plugin_update_message-' . vc_plugin_name(), array(
		$vc_updateManager,
		'addUpgradeMessageLink'
	) );
	remove_action( 'admin_notices', array( vc_manager()->license(), 'adminNoticeLicenseActivation', ) );

}

add_action( 'vc_after_init', 'grooni_vc_after_init' );
