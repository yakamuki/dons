<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Add Theme Addons and functions.
 *
 * @package crane
 */
class Crane_Addons {
	public function __construct() {

		if ( ! is_admin() ) {
			return;
		}

		add_action( 'admin_menu', array( $this, 'add_menu' ), 70 );

		add_action( 'admin_head', array( $this, 'add_addon' ), 7 );

	}

	public function add_menu() {

		if ( function_exists( 'grooni_add_subpage_to_dashboard' ) ) {
			grooni_add_subpage_to_dashboard(
				'crane-theme-dashboard',
				esc_html_x( 'Addons', 'crane addons', 'crane' ),
				esc_html_x( 'Addons', 'crane addons', 'crane' ),
				'edit_theme_options',
				'crane-theme-addons',
				array( $this, 'addons_page' )
			);
		}

	}

	/**
	 * Add Grooni Theme Addons
	 */
	public function add_addon() {
		if ( ! empty( $_GET['crane-add-addon-action'] ) ) {

			$plugin_slug_escaped = esc_attr( $_GET['crane-add-addon-action'] );

			$plugins = array_merge( crane_get_crane_plugins_array(), crane_get_crane_plugins_array( true ) );
			if ( isset( $plugins[ $plugin_slug_escaped ] ) ) {

				$source = isset( $plugins[ $plugin_slug_escaped ]['source'] ) ? $plugins[ $plugin_slug_escaped ]['source'] : '';

				crane_run_necessary_plugins(
					$plugins[ $plugin_slug_escaped ]['slug'],
					$plugins[ $plugin_slug_escaped ]['installed_path'],
					$source
				);

				$theme_name  = wp_get_theme()->template;
				$redirect_to = network_admin_url( 'admin.php?page=' . $theme_name . '-theme-addons' );
				echo '<script type="text/javascript">window.location.href = "' . $redirect_to . '";</script>';
			}

		}
	}

	public function addons_page() {

		if ( isset( $_GET['page'] ) && $_GET['page'] == 'crane-theme-addons' && isset( $_GET['refresh'] ) ) {
			wp_redirect( 'admin.php?page=crane-theme-addons' );
		}

		$theme         = wp_get_theme();
		$theme_version = $theme->get( 'Version' );
		if ( CRANE_THEME_VERSION !== $theme_version ) {
			$theme_version = CRANE_THEME_VERSION . ' [' . esc_html__( 'child version', 'crane' ) . ': ' . $theme_version . ']';
		}

		$gta_path = defined( 'GROONI_THEME_ADDONS_URL' ) ? untrailingslashit( GROONI_THEME_ADDONS_URL ) : '/wp-content/plugins/grooni-theme-addons';

		?>
		<div class="crane-addons">
			<div class="crane-admin-header">
				<div class="crane-admin-header-logo-group">
					<img src="<?php echo get_template_directory_uri(); ?>/assets/images/wp/crane-green-white.svg"
					     alt="crane logo">
					<?php if ( $theme_version ) { ?>
						<span class="crane-theme-version"><?php echo esc_html( $theme_version ); ?></span>
					<?php } ?>
				</div>

				<a href="http://grooni.com/" class="crane-admin-header-link">
					<img src="<?php echo get_template_directory_uri(); ?>/assets/images/wp/theme-by-grooni.svg"
					     alt="grooni logo">
				</a>
			</div>
			<div class="crane-addons__body">

				<div class="crane-addons-container">

					<div class="crane-addons-list-container crane-addons-list--premium">
						<div class="crane-addons-list-header">
							<h1 class="crane-addons-list-title">
								<?php esc_html_e( 'Crane Addons', 'crane' ); ?>
							</h1>

							<h2 class="crane-addons-list-subtitle">
								<span><?php esc_html_e( 'Premium plugins', 'crane' ); ?></span>
								<?php esc_html_e( 'available for free with the Crane Theme', 'crane' ); ?>
							</h2>
						</div>
						<?php
						foreach ( array_merge( crane_get_crane_plugins_array(), crane_get_crane_plugins_array( true ) ) as $plug_name => $plug_data ) {
							if ( isset( $plug_data['grooni_premium'] ) && $plug_data['grooni_premium'] ) {
								echo $this->get_block_tile( $plug_data );
							}
						}
						?>
						<div class="crane-addons-tile">
							<div class="crane-addons-tile__inner">
								<span class="crane-addons-tile__cover-img
"></span>
								<img class="crane-addons-tile__cover-txt"
									width="232"
									height="82"
									src="<?php echo esc_attr( $gta_path ); ?>/admin/assets/placeholder-txt.png"
									alt="placeholder text cover">
							</div>
						</div>
					</div>

					<div class="crane-addons-list-container crane-addons-list--free">
						<div class="crane-addons-list-header">
							<h2 class="crane-addons-list-subtitle">
								<span><?php esc_html_e( 'Free plugins', 'crane' ); ?></span>
								<?php esc_html_e( 'compatible with the Crane Theme', 'crane' ); ?>
							</h2>
						</div>
						<?php
						foreach ( array_merge( crane_get_crane_plugins_array(), crane_get_crane_plugins_array( true ) ) as $plug_name => $plug_data ) {

							if ( ! isset( $plug_data['grooni_premium'] ) || ! $plug_data['grooni_premium'] ) {
								echo $this->get_block_tile( $plug_data );
							}

						}
						?>
						<div class="crane-addons-tile">
							<div class="crane-addons-tile__inner">
								<span class="crane-addons-tile__cover-img
"></span>
								<img class="crane-addons-tile__cover-txt"
								     width="232"
								     height="82"
								     src="<?php echo esc_attr( $gta_path ); ?>/admin/assets/placeholder-txt.png"
								     alt="placeholder text cover">
							</div>
						</div>
						<div class="crane-addons-tile">
							<div class="crane-addons-tile__inner">
								<span class="crane-addons-tile__cover-img
"></span>
								<img class="crane-addons-tile__cover-txt"
								     width="232"
								     height="82"
								     src="<?php echo esc_attr( $gta_path ); ?>/admin/assets/placeholder-txt.png"
								     alt="placeholder text cover">
							</div>
						</div>
					</div>

				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Get block tile template
	 *
	 * @param array $plug_data
	 *
	 * @return string
	 */
	public function get_block_tile( $plug_data ) {

		$slug                = isset( $plug_data['slug'] ) ? $plug_data['slug'] : '';
		$name                = isset( $plug_data['name'] ) ? $plug_data['name'] : '';
		$description         = isset( $plug_data['plugin_description'] ) ? $plug_data['plugin_description'] : '';
		$gta_assets_file_dir = defined( 'GROONI_THEME_ADDONS_INC_DIR' ) ? GROONI_THEME_ADDONS_INC_DIR . '/admin/assets/' . $slug : '';
		$gta_assets_file_url = defined( 'GROONI_THEME_ADDONS_URL' ) ? GROONI_THEME_ADDONS_URL . '/admin/assets/' . $slug : '';
		$warn_tooltip        = '';
		$additional_class    = array();

		if ( empty( $plug_data['plugin_icon'] ) ) {

			$file_ext = '.png';
			if ( ! is_file( $gta_assets_file_dir . $file_ext ) ) {
				$file_ext = '.jpg';
			}

			if ( $gta_assets_file_url && is_file( $gta_assets_file_dir . $file_ext ) ) {
				$icon = $gta_assets_file_url . $file_ext;
			} else {
				$icon = get_template_directory_uri() . '/assets/images/placeholders/splash84x84.png';
			}
		} else {
			$icon = $plug_data['plugin_icon'];
		}

		if ( ! empty( $plug_data['conflicted_with'] ) ) {
			$conflicted_with = array();
			$all_plugins     = array_merge( crane_get_crane_plugins_array(), crane_get_crane_plugins_array( true ) );
			foreach ( $plug_data['conflicted_with'] as $plugin_conflicted_name ) {
				if ( isset( $all_plugins[ $plugin_conflicted_name ] ) ) {
					if ( is_plugin_active( $all_plugins[ $plugin_conflicted_name ]['installed_path'] ) ) {
						$conflicted_with[] = $all_plugins[ $plugin_conflicted_name ]['name'];
					}
				}
			}
		}

		if ( ! is_plugin_active( $plug_data['installed_path'] ) ) {
			$link_text                = esc_html__( 'Install', 'crane' );
			$link_url                 = esc_url( add_query_arg( 'crane-add-addon-action', $slug ) );
			$additional_class['link'] = 'crane-addons-tile__link';

			if ( ! empty( $conflicted_with ) ) {
				$action_button = '<i class="fa fa-exclamation-circle"></i><span>' .
				                 esc_html__( 'Conflicting plugin',
					                 'crane' ) . '</span>';
			} else {
				$action_button = '<a href="' . $link_url . '">' . $link_text . '</a>';
			}

		} else {
			$link_text                  = esc_html__( 'Activated', 'crane' );
			$action_button              = '<span>' . $link_text . '</span>';
			$additional_class['active'] = 'crane-addons-tile__active';
		}

		if ( ! empty( $conflicted_with ) ) {
			$additional_class['conflict'] = 'crane-addons-tile__can-conflict';

			$warn_tooltip = sprintf(
				esc_html__( 'This plugin conflicts with %s. Please use only one of them.', 'crane' ),
				implode( ' ', $conflicted_with )
			);
			$warn_tooltip = 'data-tooltip="' . $warn_tooltip . '"';

			unset( $additional_class['link'] );
		}

		$html_otput = '';

		$html_otput .= '<div class="crane-addons-tile">';
		$html_otput .= '    <div class="crane-addons-tile__inner">';
		$html_otput .= '	    <div class="crane-addons-tile__icon-wrapper">';
		$html_otput .= '		    <img width="84" height="84" src="' .
		               $icon . '" alt="' . $name . '">';
		$html_otput .= '	    </div>';
		$html_otput .= '	    <div class="crane-addons-tile__info-wrapper">';
		$html_otput .= '		    <h3 class="crane-addons-tile__header">' . $name . '</h3>';

		$html_otput .= '		    <p class="crane-addons-tile__txt">' . $description . '</p>';
		$html_otput .= '		    <div class="crane-addons-tile__action ' . implode( ' ', $additional_class ) . '" ' . $warn_tooltip . '>' . $action_button . '</div>';
		$html_otput .= '	    </div>';
		$html_otput .= '    </div>';
		$html_otput .= '</div>';

		return $html_otput;
	}

}
