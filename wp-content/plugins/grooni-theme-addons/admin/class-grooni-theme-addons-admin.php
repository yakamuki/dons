<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * The dashboard-specific functionality of the plugin.
 *
 * @link       http://grooni.com
 * @since      1.0.0
 *
 * @package    Grooni_Theme_Addons
 * @subpackage Grooni_Theme_Addons/includes
 */

/**
 * The dashboard-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the dashboard-specific stylesheet and JavaScript.
 *
 * @package    Grooni_Theme_Addons
 * @subpackage Grooni_Theme_Addons/admin
 * @author     Di_Skyer <diskyer@gmail.com>
 */
class Grooni_Theme_Addons_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $name The ID of this plugin.
	 */
	private $name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;


	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @var      string $name The name of this plugin.
	 * @var      string $version The version of this plugin.
	 */
	public function __construct( $name, $version ) {

		$this->name      = $name;
		$this->version   = $version;

		require_once GROONI_THEME_ADDONS_INC_DIR . '/includes/import/class-grooni-theme-addons-import.php';

	}

	/**
	 * Register the stylesheets for the Dashboard.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Grooni_Theme_Addons_Admin_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Grooni_Theme_Addons_Admin_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 *
		 * for example:
		 * wp_enqueue_style( $this->name, plugin_dir_url( __FILE__ ) . 'css/grooni-theme-addons-admin.css', array(), $this->version, 'all' );
		 */

	}

	/**
	 * Register the JavaScript for the dashboard.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Grooni_Theme_Addons_Admin_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Grooni_Theme_Addons_Admin_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 *
		 * for example:
		 * wp_enqueue_script( $this->name, plugin_dir_url( __FILE__ ) . 'js/grooni-theme-addons-admin.js', array( 'jquery' ), $this->version, false );
		 */

		wp_enqueue_script( $this->name, plugin_dir_url( __FILE__ ) . 'js/grooni-theme-addons-admin.js', array( 'jquery' ), $this->version, false );

	}


	/**
	 * Draw admin setup page.
	 *
	 * @since    1.0.0
	 */
	public function draw_setup_page() {
		/**
		 * for example:
		 * $this->update_setup_page();
		 * require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/grooni-theme-addons-admin-display.php';
		 */

	}


	/**
	 * Save "Grooni Theme Addons" options
	 *
	 * @since 1.0.0
	 */
	public function update_setup_page() {
		if ( isset( $_POST['grooni-theme-addons_output_page'] ) ) {
			check_admin_referer( $this->name . '__update-options' );

			$options = Grooni_Theme_Addons::get_options();

			return Grooni_Theme_Addons::update_options( $options );
		}
	}


	/**
	 * Redirect to theme dashboard
	 *
	 * @since 1.1.24
	 */
	public function check_for_redirect_dashboard() {

		if ( ! defined( 'WP_LOAD_IMPORTERS' ) && is_admin() && get_transient( 'grooni_theme_addons_activation_action' ) ) {
			set_transient( 'grooni_theme_addons_activation_action', false );

			$theme_name = wp_get_theme()->template;

			$redirect_to = admin_url( 'admin.php?page=' . $theme_name . '-theme-dashboard' );
			wp_redirect( esc_url( $redirect_to ), 302 );
		}

	}


	/**
	 * Dismiss Addons plugin installer message
	 */
	public function dismiss_addons_msg() {
		if ( isset( $_GET['grooni-upgrade-crane-theme-dismiss'] ) && 'yes' === $_GET['grooni-upgrade-crane-theme-dismiss'] ) {
			update_user_meta( get_current_user_id(), 'grooni-upgrade-crane-theme-dismiss', true );
		}
	}


	/**
	 * Show upgrade notice, when
	 *
	 * @since 1.2
	 */
	public function check_theme_version_notice() {

		if ( ! is_admin() ) {
			return;
		}

		$need_upgrade = false;

		if ( 'crane' === GROONI_THEME_ADDONS_CURRENT_THEME_SLUG && version_compare( '1.1', GROONI_THEME_ADDONS_CURRENT_THEME_VERSION, '>=' ) ) {
			$need_upgrade = true;
		}

		$is_update_dismissed  = get_user_meta( get_current_user_id(), 'grooni-upgrade-crane-theme-dismiss', true );

		if ( ! $is_update_dismissed && $need_upgrade ) {
			add_action( 'admin_notices', array( $this, 'show_grooni_theme_need_upgrade' ), 8 );
		}


	}


	public function show_grooni_theme_need_upgrade() {
		?>

		<div id="grooni-theme-addons-upgrade-notice" class="notice-warning settings-error notice is-dismissible">
			<p class="grooni-theme-addons-text-block"><?php echo sprintf( esc_html__( 'You need to upgrade %s there are major improvements related with Crane settings.', 'grooni-theme-addons' ), '<a href="' . get_admin_url( null, 'tupdate-core.php', 'relative' ) . '">' . GROONI_THEME_ADDONS_CURRENT_THEME_NAME . '</a> ' . esc_html__( 'theme', 'grooni-theme-addons' ) ); ?>
				<br>
			</p>

			<p class="grooni-theme-addons-buttons-block">
				<a href="<?php echo esc_url( add_query_arg( 'grooni-upgrade-crane-theme-action', 'yes' ) ); ?>"
				   class="button-primary"><?php echo sprintf( esc_html__( 'Upgrade %s Theme', 'grooni-theme-addons' ), GROONI_THEME_ADDONS_CURRENT_THEME_NAME ); ?></a>
				&nbsp;|&nbsp;
				<a href="<?php echo esc_url( add_query_arg( 'grooni-upgrade-crane-theme-dismiss', 'yes' ) ); ?>"><?php esc_html_e( 'Dismiss this notice', 'grooni-theme-addons' ); ?></a>
			</p>

		</div>

		<?php
	}

	public function upgrade_theme_addons() {

		if ( isset( $_GET['grooni-upgrade-crane-theme-action'] ) && 'yes' === $_GET['grooni-upgrade-crane-theme-action'] ) {

			include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
			$upgrader = new Theme_Upgrader();

			$upgrade_result = $upgrader->upgrade( GROONI_THEME_ADDONS_CURRENT_THEME_SLUG );

			if ( $upgrade_result ) {
				$redirect_to = admin_url( 'admin.php?page=' . GROONI_THEME_ADDONS_CURRENT_THEME_SLUG . '-theme-dashboard' );
				echo '<script type="text/javascript">window.location.href = "' . $redirect_to . '";</script>';
			}

		}
	}


}
