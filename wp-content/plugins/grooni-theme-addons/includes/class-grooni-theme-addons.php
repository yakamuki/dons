<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the dashboard.
 *
 * @link       http://grooni.com
 * @since      1.0.0
 *
 * @package    Grooni_Theme_Addons
 * @subpackage Grooni_Theme_Addons/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, dashboard-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Grooni_Theme_Addons
 * @subpackage Grooni_Theme_Addons/includes
 * @author     Di_Skyer <diskyer@gmail.com>
 */
class Grooni_Theme_Addons {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Grooni_Theme_Addons_Loader $loader Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $plugin_name The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $version The current version of the plugin.
	 */
	protected $version;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $version The current version of the plugin.
	 */
	static $option_name = 'grooni-theme-addons__options';

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the Dashboard and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		$this->plugin_name = 'grooni-theme-addons';
		$this->version     = GROONI_THEME_ADDONS_VERSION;

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Grooni_Theme_Addons_Loader. Orchestrates the hooks of the plugin.
	 * - Grooni_Theme_Addons_i18n. Defines internationalization functionality.
	 * - Grooni_Theme_Addons_Admin. Defines all hooks for the dashboard.
	 * - Grooni_Theme_Addons_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-grooni-theme-addons-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-grooni-theme-addons-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the Dashboard.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-grooni-theme-addons-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-grooni-theme-addons-public.php';

		/**
		 * Load the embedded Redux Framework
		 */
		add_filter( 'redux/_url', [ $this, 'redux_sym_link_url' ], 10, 1 );
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/vendor/redux-framework/framework.php';

		/**
		 * Add WP widgets support
		 */
		include_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/wp-widgets/init-widgets.php';

		/**
		 * Add shortcodes support
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/shortcodes/init.php';

		/**
		 * Add Portfolio custom post type.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/crane-portfolio.php';

		/**
		 * Add Footer custom post type.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/crane-footer.php';

		/**
		 * Sets VisualComposer plugin settings.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/vendor/vc/vc-config.php';

		/**
		 * Include gfonts class.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-grooni-theme-addons-gfonts.php';

		$this->loader = new Grooni_Theme_Addons_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Grooni_Theme_Addons_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Grooni_Theme_Addons_i18n();
		$plugin_i18n->set_domain( $this->get_plugin_name() );

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the dashboard functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Grooni_Theme_Addons_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		$this->loader->add_action( 'after_setup_theme', $plugin_admin, 'check_for_redirect_dashboard' );

		$this->loader->add_action( 'admin_head', $plugin_admin, 'upgrade_theme_addons', 7 );
		$this->loader->add_action( 'admin_head', $plugin_admin, 'dismiss_addons_msg', 7 );
		$this->loader->add_action( 'after_setup_theme', $plugin_admin, 'check_theme_version_notice' );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {
		/**
		 * for example:
		 * $plugin_public = new Grooni_Theme_Addons_Public( $this->get_plugin_name(), $this->get_version(), self::get_options() );
		 * $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		 * $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
		 * $this->loader->add_action( 'init', $plugin_public, 'rewrite' );
		 */

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Grooni_Theme_Addons_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}


	/**
	 * @since 1.0.0
	 */
	public static function get_options() {
		return get_option( self::$option_name );
	}


	/**
	 * @since 1.0.0
	 */
	public static function update_options( $options ) {

		if (!empty( $options)) {

			update_option( self::$option_name, $options, true );

		}

	}


	/**
	 * @param string $url
	 *
	 * @return string
	 */
	public static function redux_sym_link_url( $url = '' ) {

		if ( is_admin() || is_customize_preview() ) {
			$url = plugin_dir_url( __FILE__ ) . 'vendor/redux-framework/';
		}

		return $url;
	}


}
