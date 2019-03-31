<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Add Theme Dashboard and functions.
 *
 * @package crane
 */
class Crane_Dashboard {
	public function __construct() {

		if ( ! is_admin() ) {
			return;
		}

		if ( isset( $_GET['page'] ) and $_GET['page'] === 'crane-theme-dashboard' and isset( $_GET['term'] ) ) {
			ob_start();
		}
		add_action( 'admin_menu', array( $this, 'add_menu' ), 10 );

		if ( get_option( 'grooni_do_flush_rewrite_rules' ) ) {
			delete_option( 'grooni_do_flush_rewrite_rules' );
			add_action( 'init', 'flush_rewrite_rules', 110 );
		}

		add_action( 'admin_head', array( $this, 'add_grooni_theme_addons' ), 7 );
		add_action( 'admin_head', array( $this, 'dismiss_addons_msg' ), 7 );
		add_action( 'admin_head', array( $this, 'late_start' ), 8 );

	}

	public function late_start() {
		if ( ! is_admin() ) {
			return;
		}

		if ( ! defined( 'GROONI_THEME_ADDONS_VERSION' ) ) {

			$is_install_dismissed = get_user_meta( get_current_user_id(), 'crane-install-addons-dismiss', true );

			if ( ! $is_install_dismissed ) {
				add_action( 'admin_notices', array( $this, 'show_need_plugin_message' ), 8 );
			}

		} else {

			$need_upgrade = false;

			$grooni_addons_version = crane_get_crane_plugins_array()['grooni-theme-addons']['version'];

			if ( version_compare( $grooni_addons_version, GROONI_THEME_ADDONS_VERSION, '>' ) ) {
				$need_upgrade = true;
			}

			$is_upgrade_dismissed = get_user_meta( get_current_user_id(), 'crane-upgrade-addons-dismiss', true );

			if ( ! $is_upgrade_dismissed && $need_upgrade ) {
				add_action( 'admin_notices', array( $this, 'show_grooni_theme_addons_need_upgrade' ), 8 );
			}
		}

		if ( defined( 'GROOVY_MENU_VERSION' ) ) {

			$need_upgrade = false;

			$minimum_gm_version = crane_get_crane_plugins_array()['groovy-menu']['min-version'];

			if ( version_compare( $minimum_gm_version, GROOVY_MENU_VERSION, '>' ) ) {
				$need_upgrade = true;
			}

			$is_upgrade_dismissed = get_user_meta( get_current_user_id(), 'crane-upgrade-gm-dismiss', true );

			if ( ! $is_upgrade_dismissed && $need_upgrade ) {
				add_action( 'admin_notices', array( $this, 'show_gm_need_upgrade' ), 9 );
			}
		}

	}

	/**
	 * Dismiss Addons plugin installer message
	 */
	public function dismiss_addons_msg() {
		if ( isset( $_GET['crane-install-addons-dismiss'] ) && 'yes' === $_GET['crane-install-addons-dismiss'] ) {
			update_user_meta( get_current_user_id(), 'crane-install-addons-dismiss', true );
		}
		if ( isset( $_GET['crane-upgrade-addons-dismiss'] ) && 'yes' === $_GET['crane-upgrade-addons-dismiss'] ) {
			update_user_meta( get_current_user_id(), 'crane-upgrade-addons-dismiss', true );
		}
		if ( isset( $_GET['crane-upgrade-gm-dismiss'] ) && 'yes' === $_GET['crane-upgrade-gm-dismiss'] ) {
			update_user_meta( get_current_user_id(), 'crane-upgrade-gm-dismiss', true );
		}
	}

	/**
	 * Add Grooni Theme Addons
	 */
	public function add_grooni_theme_addons() {
		if ( isset( $_GET['crane-install-addons-action'] ) && 'yes' === $_GET['crane-install-addons-action'] ) {

			$plugins = crane_get_crane_plugins_array();
			if ( isset( $plugins['grooni-theme-addons'] ) ) {
				crane_run_necessary_plugins(
					$plugins['grooni-theme-addons']['slug'],
					$plugins['grooni-theme-addons']['installed_path'],
					$plugins['grooni-theme-addons']['source']
				);

				$theme_name  = wp_get_theme()->template;
				$redirect_to = admin_url( 'admin.php?page=' . $theme_name . '-theme-dashboard' );
				echo '<script type="text/javascript">window.location.href = "' . $redirect_to . '";</script>';
			}

		}

		if ( isset( $_GET['crane-upgrade-addons-action'] ) && 'yes' === $_GET['crane-upgrade-addons-action'] ) {

			$plugins = crane_get_crane_plugins_array();
			if ( isset( $plugins['grooni-theme-addons'] ) ) {
				crane_run_necessary_plugins(
					$plugins['grooni-theme-addons']['slug'],
					$plugins['grooni-theme-addons']['installed_path'],
					$plugins['grooni-theme-addons']['source'],
					true // param to upgrade
				);

				$theme_name  = wp_get_theme()->template;
				$redirect_to = admin_url( 'admin.php?page=' . $theme_name . '-theme-dashboard' );
				echo '<script type="text/javascript">window.location.href = "' . $redirect_to . '";</script>';
			}

		}

		if ( isset( $_GET['crane-upgrade-gm-action'] ) && 'yes' === $_GET['crane-upgrade-gm-action'] ) {

			$plugins = crane_get_crane_plugins_array();
			if ( isset( $plugins['groovy-menu'] ) ) {
				crane_run_necessary_plugins(
					$plugins['groovy-menu']['slug'],
					$plugins['groovy-menu']['installed_path'],
					$plugins['groovy-menu']['source'],
					true // param to upgrade
				);

				$theme_name  = wp_get_theme()->template;
				$redirect_to = admin_url( 'admin.php?page=' . $theme_name . '-theme-dashboard' );
				echo '<script type="text/javascript">window.location.href = "' . $redirect_to . '";</script>';
			}

		}
	}

	public function show_need_plugin_message() {
		$screen = get_current_screen();
		if ( 'appearance_page_tgmpa-install-plugins' !== $screen->id ) :

			$need_plugins = array();
			if ( ! defined( 'GROONI_THEME_ADDONS_VERSION' ) ) {
				$need_plugins['grooni-theme-addons'] = '<a href="' . get_admin_url( null, 'themes.php?page=tgmpa-install-plugins', 'relative' ) . '">' . esc_html__( 'Grooni Theme Addons', 'crane' ) . '</a> ' . esc_html__( 'plugin', 'crane' );
			}
			?>

			<div id="crane-error-notice" class="notice-warning settings-error notice is-dismissible">
				<p class="crane-install-addons-text-block"><?php esc_html_e( 'You need to install and activate the Grooni Theme Addons plugin for installing plugins and Crane theme demo data.', 'crane' ); ?>
					<?php echo implode( ' ' . esc_html__( 'and', 'crane' ) . ' ', $need_plugins ); ?>
				</p>
				<p class="crane-install-addons-buttons-block">
					<a href="<?php echo esc_url( add_query_arg( 'crane-install-addons-action', 'yes' ) ); ?>" class="button-primary"><?php esc_html_e( 'Install Grooni Theme Addons Plugin', 'crane' ); ?></a>
					&nbsp;|&nbsp;
					<a href="<?php echo esc_url( add_query_arg( 'crane-install-addons-dismiss', 'yes' ) ); ?>"><?php esc_html_e( 'Dismiss this notice', 'crane' ); ?></a>
				</p>

			</div>

			<?php
		endif;
	}

	public function show_grooni_theme_addons_need_upgrade() {
		?>

		<div id="crane-upgrade-notice" class="notice-warning settings-error notice is-dismissible">
			<p class="crane-install-addons-text-block"><?php echo sprintf(
			    esc_html__( 'You need to update %s. There are major improvements related to Crane settings.', 'crane' ), '<a href="' . get_admin_url( null, 'themes.php?page=tgmpa-install-plugins', 'relative' ) . '">' . esc_html__( 'Grooni Theme Addons', 'crane' ) . '</a> ' . esc_html__( 'plugin', 'crane' ) ); ?>
				<br>
			</p>

			<p class="crane-install-addons-buttons-block">
				<a href="<?php echo esc_url( add_query_arg( 'crane-upgrade-addons-action', 'yes' ) ); ?>" class="button-primary"><?php esc_html_e( 'Update Grooni Theme Addons Plugin', 'crane' ); ?></a>
				&nbsp;|&nbsp;
				<a href="<?php echo esc_url( add_query_arg( 'crane-upgrade-addons-dismiss', 'yes' ) ); ?>"><?php esc_html_e( 'Dismiss this notice', 'crane' ); ?></a>
			</p>

		</div>

		<?php
	}

	public function show_gm_need_upgrade() {
		?>

		<div id="crane-upgrade-notice" class="notice-error settings-error notice is-dismissible">
			<p class="crane-install-addons-text-block"><?php echo sprintf(
			    esc_html__( 'You need to update %s. There are major improvements related to Crane settings.', 'crane' ), '<a href="' . get_admin_url( null, 'themes.php?page=tgmpa-install-plugins', 'relative' ) . '">' . esc_html__( 'Groovy Menu', 'crane' ) . '</a> ' . esc_html__( 'plugin', 'crane' ) ); ?>
				<br>
			</p>

			<p class="crane-install-addons-buttons-block">
				<a href="<?php echo esc_url( add_query_arg( 'crane-upgrade-gm-dismiss', 'yes' ) ); ?>"><?php esc_html_e( 'Dismiss this notice', 'crane' ); ?></a>
			</p>

		</div>

		<?php
	}

	public function add_menu() {
		if ( function_exists( 'grooni_add_page_to_dashboard' ) ) {
			grooni_add_page_to_dashboard(
				esc_html__( 'Crane theme', 'crane' ),
				esc_html__( 'Crane theme', 'crane' ),
				'edit_theme_options',
				'crane-theme-dashboard',
				array( $this, 'dashboard' ),
				'',
				94
			);
		}

		if ( function_exists( 'grooni_add_subpage_to_dashboard' ) ) {
			grooni_add_subpage_to_dashboard(
				'crane-theme-dashboard',
				esc_html_x( 'Dashboard', 'crane dashboard', 'crane' ),
				esc_html_x( 'Dashboard', 'crane dashboard', 'crane' ),
				'edit_theme_options',
				'crane-theme-dashboard',
				array( $this, 'dashboard' )
			);
		}

	}


	public function dashboard() {

		if ( isset( $_GET['page'] ) && $_GET['page'] === 'crane-theme-dashboard' && isset( $_GET['refresh'] ) ) {
			wp_redirect( 'admin.php?page=crane-theme-dashboard' );
		}

		wp_enqueue_script( 'jquery-ui-autocomplete' );
		if ( isset( $_GET['term'] ) ) {
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
				exit;
			}

			ob_clean();

			$url  = 'http://grooni.com/docs/crane/wp-admin/admin-ajax.php?action=autocompleteCallback&term=' . esc_attr( wp_unslash( $_GET['term'] ) );
			$json_data = $wp_filesystem->get_contents( $url );
			$data = json_decode($json_data);

			// return json for ajax call
			echo json_encode( json_decode( $json_data ), JSON_UNESCAPED_SLASHES );

			exit;
		}

		$theme         = wp_get_theme();
		$theme_version = $theme->get( 'Version' );
		if ( CRANE_THEME_VERSION !== $theme_version ) {
			$theme_version = CRANE_THEME_VERSION . ' ['. esc_html__( 'child version', 'crane' ).': ' . $theme_version . ']';
		}

		?>
		<div class="crane-dashboard">
			<div class="crane-dashboard__header">
				<div class="crane-dashboard__header__top">
					<div class="crane-dashboard__theme-logo">
						<img src="<?php echo get_template_directory_uri() ?>/assets/images/wp/crane-green-white.svg" alt="crane logo" class="crane-dashboard-logo">
						<span class="crane-theme-version"><?php echo esc_html( $theme_version ); ?></span>
					</div>
					<a href="http://grooni.com/" class="crane-dashboard-link">
						<img src="<?php echo get_template_directory_uri() ?>/assets/images/wp/theme-by-grooni.svg" alt="grooni logo">
					</a>
				</div>
				<form method="post" id="crane-dashboard-search" action="http://grooni.com/docs/crane/">
					<label for="s"><?php esc_html_e( 'Knowledge Base', 'crane' ); ?></label>
					<input placeholder="Quick Search" type="text" name="s"/>
					<button type="submit"><?php esc_html_e( 'Search', 'crane' ); ?></button>
				</form>
			</div>
			<div class="crane-dashboard__body">
				<div class="crane-dashboard__info-box">
					<h1 class="crane-dashboard__info-box__title"><?php esc_html_e( 'Welcome to Crane Theme', 'crane' ); ?></h1>

					<p class="crane-dashboard__info-box__txt"><?php esc_html_e( 'Get ready to become acquainted with Crane - versatile and highly functional WordPress theme which is perfect for corporate and creative projects implementation! Crane contains everything you need to launch your website in the course of the coming next several days or even within several hours!', 'crane' ); ?></p>
				</div>
				<div class="crane-dashboard-container">


					<?php
					echo $this->get_block_tile(
						'fa fa-download',
						esc_html__( 'Import Demo Content', 'crane' ),
						esc_html__( 'Having installed the theme at the first time, you can import the demo-content. It is the great way to start creating your website.', 'crane' ),
						'admin.php?page=crane_import',
						esc_html__( 'Import Content', 'crane' )
					);
					?>

					<?php
					echo $this->get_block_tile(
						'fa fa-cogs',
						esc_html__( 'Theme Options', 'crane' ),
						esc_html__( 'Here you can find the essential global settings of your website which allow to customize its appearance, layout as well as to choose the preferable typography and general colors and apply the essential settings for your blog or online-shop.', 'crane' ),
						'admin.php?page=crane-theme-options',
						esc_html__( 'Theme Options', 'crane' )
					);
					?>

					<?php
					echo $this->get_block_tile(
						'fa fa-book',
						esc_html__( 'Knowledge Base', 'crane' ),
						esc_html__( 'We have worked out the expanded and well-structured documentation concerning working with Crane. The database has been elaborated as the separate website; quick search on dashboard pane is also available.', 'crane' ),
						'http://grooni.com/docs/crane/',
						esc_html__( 'Go to Knowledge Base', 'crane' ),
						true
					);
					?>

					<?php
					if ( function_exists( 'groovyMenu' ) ) :
						echo $this->get_block_tile(
							'fa fa-bars',
							esc_html__( 'Menu Options', 'crane' ),
							esc_html__( 'We are happy to propose our helpful plugin included into the Crane package which helps to operate the menu in the more flexible manner. A plenty of presets with pre-made menu layouts worked out by our team are attainable for smooth operating the menu.', 'crane' ),
							'admin.php?page=groovy_menu_settings',
							esc_html__( 'Go to Menu Options', 'crane' )
						);
					endif;
					?>

					<?php
					echo $this->get_block_tile(
						'fa fa-life-ring',
						esc_html__( 'Get a Support', 'crane' ),
						esc_html__( 'In case any problems emerge while working with our product, you are always capable to register on our portal and create the ticket. We will certainly provide you with all the necessary assistance.', 'crane' ),
						'https://grooni.ticksy.com/',
						esc_html__( 'Get a Support', 'crane' ),
						true
					);
					?>

					<?php
					echo $this->get_block_tile(
						'fa fa-newspaper-o',
						esc_html__( 'Theme Changelog', 'crane' ),
						esc_html__( 'Theme Changelog page always helps you to keep track of current updates we would like to propose you in the latest versions of the website. You will also be aware of all the ways of how our products are being developed.', 'crane' ),
						'http://grooni.com/docs/crane/changelog/',
						esc_html__( 'See changelog', 'crane' ),
						true
					);
					?>

				</div>
			</div>
		</div>
		<?php
	}


	/**
	 * Get block tile template
	 *
	 * @param string $icon
	 * @param string $title
	 * @param string $description
	 * @param string $link_url
	 * @param string $link_text
	 *
	 * @return string
	 */
	public function get_block_tile( $icon, $title, $description, $link_url, $link_text, $link_target_blank = false ) {

		if ( $link_target_blank ) {
			$link_target_blank = ' target="_blank" ';
		}

		$html_otput = '';

		$html_otput .= '<div class="crane-dashboard-tile">';
		$html_otput .= '    <div class="crane-dashboard-tile__inner">';
		$html_otput .= '	    <div class="crane-dashboard-tile__icon-wrapper">';
		$html_otput .= '		    <i class="' . $icon . '"></i>';
		$html_otput .= '	    </div>';
		$html_otput .= '	    <div class="crane-dashboard-tile__info-wrapper">';
		$html_otput .= '		    <h3 class="crane-dashboard-tile__header">' . $title . '</h3>';

		$html_otput .= '		    <p class="crane-dashboard-tile__txt">' . $description . '</p>';
		$html_otput .= '		    <a href="' . $link_url . '" class="crane-dashboard-tile__link"' . $link_target_blank . '>' . $link_text . '</a>';
		$html_otput .= '	    </div>';
		$html_otput .= '    </div>';
		$html_otput .= '</div>';

		return $html_otput;
	}

}
