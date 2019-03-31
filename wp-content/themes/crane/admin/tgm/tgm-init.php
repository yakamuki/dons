<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Include the TGM_Plugin_Activation class.
 */
require_once get_template_directory() . '/inc/vendor/tgm/class-tgm-plugin-activation.php';

if ( ! function_exists( 'crane_require_plugins' ) && function_exists( 'tgmpa' ) ) {
	/**
	 * Register the required plugins for this theme.
	 *
	 * In this example, we register two plugins - one included with the TGMPA library
	 * and one from the .org repo.
	 *
	 * The variable passed to tgmpa_register_plugins() should be an array of plugin
	 * arrays.
	 *
	 * This function is hooked into tgmpa_init, which is fired within the
	 * TGM_Plugin_Activation class constructor.
	 */
	function crane_require_plugins() {

		$plugins = crane_get_crane_plugins_array();

		$config = array(
			'id'           => 'crane-theme',
			// Unique ID for hashing notices for multiple instances of TGMPA.
			'default_path' => '',
			// Default absolute path to bundled plugins.
			'menu'         => 'tgmpa-install-plugins',
			// Menu slug.
			'parent_slug'  => 'themes.php',
			// Parent menu slug.
			'capability'   => 'edit_theme_options',
			// Capability needed to view plugin install page, should be a capability associated with the parent menu used.
			'has_notices'  => true,
			// Show admin notices or not.
			'dismissable'  => true,
			// If false, a user cannot dismiss the nag message.
			'dismiss_msg'  => '',
			// If 'dismissable' is false, this message will be output at top of nag.
			'is_automatic' => true,
			// Automatically activate plugins after installation or not.
			'message'      => '',
			// Message to output right before the plugins table.
			'strings'      => array(
				'page_title'                      => esc_html__( 'Install Required Plugins', 'crane' ),
				'menu_title'                      => esc_html__( 'Install Plugins', 'crane' ),
				/* translators: %s: plugin name. */
				'installing'                      => esc_html__( 'Installing Plugin: %s', 'crane' ),
				/* translators: %s: plugin name. */
				'updating'                        => esc_html__( 'Updating Plugin: %s', 'crane' ),
				'oops'                            => esc_html__( 'Something went wrong with the plugin API.', 'crane' ),
				'notice_can_install_required'     => _n_noop(
				/* translators: 1: plugin name(s). */
					'This theme requires the following plugin: %1$s.',
					'This theme requires the following plugins: %1$s.',
					'crane'
				),
				'notice_can_install_recommended'  => _n_noop(
				/* translators: 1: plugin name(s). */
					'This theme recommends the following plugin: %1$s.',
					'This theme recommends the following plugins: %1$s.',
					'crane'
				),
				'notice_ask_to_update'            => _n_noop(
				/* translators: 1: plugin name(s). */
					'The following plugin needs to be updated to its latest version to ensure maximum compatibility with this theme: %1$s.',
					'The following plugins need to be updated to their latest version to ensure maximum compatibility with this theme: %1$s.',
					'crane'
				),
				'notice_ask_to_update_maybe'      => _n_noop(
				/* translators: 1: plugin name(s). */
					'There is an update available for: %1$s.',
					'There are updates available for the following plugins: %1$s.',
					'crane'
				),
				'notice_can_activate_required'    => _n_noop(
				/* translators: 1: plugin name(s). */
					'The following required plugin is currently inactive: %1$s.',
					'The following required plugins are currently inactive: %1$s.',
					'crane'
				),
				'notice_can_activate_recommended' => _n_noop(
				/* translators: 1: plugin name(s). */
					'The following recommended plugin is currently inactive: %1$s.',
					'The following recommended plugins are currently inactive: %1$s.',
					'crane'
				),
				'install_link'                    => _n_noop(
					'Begin installing plugin',
					'Begin installing plugins',
					'crane'
				),
				'update_link'                     => _n_noop(
					'Begin updating plugin',
					'Begin updating plugins',
					'crane'
				),
				'activate_link'                   => _n_noop(
					'Begin activating plugin',
					'Begin activating plugins',
					'crane'
				),
				'return'                          => esc_html__( 'Return to Required Plugins Installer', 'crane' ),
				'dashboard'                       => esc_html__( 'Return to the Dashboard', 'crane' ),
				'plugin_activated'                => esc_html__( 'Plugin activated successfully.', 'crane' ),
				'activated_successfully'          => esc_html__( 'The following plugin was activated successfully:', 'crane' ),
				/* translators: 1: plugin name. */
				'plugin_already_active'           => esc_html__( 'No action taken. Plugin %1$s was already active.', 'crane' ),
				/* translators: 1: plugin name. */
				'plugin_needs_higher_version'     => esc_html__( 'Plugin not activated. A higher version of %s is needed for this theme. Please update the plugin.', 'crane' ),
				/* translators: 1: dashboard link. */
				'complete'                        => esc_html__( 'All plugins installed and activated successfully. %1$s', 'crane' ),
				'dismiss'                         => esc_html__( 'Dismiss this notice', 'crane' ),
				'notice_cannot_install_activate'  => esc_html__( 'There are one or more required or recommended plugins to install, update or activate.', 'crane' ),
				'contact_admin'                   => esc_html__( 'Please contact the administrator of this site for help.', 'crane' ),
			),

		);

		tgmpa( $plugins, $config );
	}

	add_action( 'tgmpa_register', 'crane_require_plugins' );

}

if ( ! function_exists( 'crane_automatic_plugins_loading' ) ) {
	function crane_automatic_plugins_loading() {

		// Stop execution if not in the tgmpa-install-plugins page
		if ( empty( $_GET['page'] ) || $_GET['page'] !== 'tgmpa-install-plugins' || empty( $_GET['autoaction'] ) ) {
			return;
		}

		?>

		<div id="plugins-loading-box"><?php esc_html_e( 'Installing plugins...', 'crane' ); ?></div>

		<?php
	}
}

add_action( 'admin_head', 'crane_automatic_plugins_loading' );
