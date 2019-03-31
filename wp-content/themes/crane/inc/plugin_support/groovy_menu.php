<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );


if ( ! function_exists( 'crane_show_alternate_menu_in_primary' ) ) {
	/**
	 * Show Groovy Menu in primary menu area.
	 */
	function crane_show_alternate_menu_in_primary() {

		if ( function_exists( 'groovyMenu' ) ) {

			remove_action( 'crane_primary_menu_area', 'crane_show_primary_menu_area' );


			$args = array(
				'theme_location' => 'primary',
				'menu_class'     => 'nav-menu',
			);


			if ( defined( 'GROOVY_MENU_DB_VER_OPTION' ) ) {
				$db_version = get_option( GROOVY_MENU_DB_VER_OPTION );
			}

			if ( ! empty( $db_version ) && version_compare( $db_version, '1.4.4.403', '<' ) ) {
				$current_page_options = crane_get_options_for_current_page();
				$nav_menu             = apply_filters( 'crane_primary_nav_menu', isset( $current_page_options['nav_menu'] ) ? $current_page_options['nav_menu'] : '' );
				$menu_preset          = apply_filters( 'crane_primary_menu_preset', isset( $current_page_options['groovy_menu'] ) ? $current_page_options['groovy_menu'] : '' );

				if ( ! empty( $nav_menu ) ) {
					$args['menu'] = $nav_menu;
				}

				if ( ! empty( $menu_preset ) ) {
					$args['gm_preset_id'] = $menu_preset;
				}
			}

			// Call Groovy Menu plugin.
			groovyMenu( $args );

		}

	}
}

add_action( 'crane_before_primary_menu_area', 'crane_show_alternate_menu_in_primary', 20 );
