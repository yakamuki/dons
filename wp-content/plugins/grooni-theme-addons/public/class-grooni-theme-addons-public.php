<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://grooni.com
 * @since      1.0.0
 *
 * @package    Grooni_Theme_Addons
 * @subpackage Grooni_Theme_Addons/includes
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the dashboard-specific stylesheet and JavaScript.
 *
 * @package    Grooni_Theme_Addons
 * @subpackage Grooni_Theme_Addons/admin
 * @author     Di_Skyer <diskyer@gmail.com>
 */
class Grooni_Theme_Addons_Public {

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
	 * All options
	 *
	 * @since 1.0.0
	 * @access private
	 * @var array $options
	 *
	 */
	private $options;


	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @var      string $name The name of the plugin.
	 * @var      string $version The version of this plugin.
	 * @var      int $redirect_page The main page of redirect.
	 */
	public function __construct( $name, $version, $options ) {

		$this->name    = $name;
		$this->version = $version;
		$this->options = $options;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Grooni_Theme_Addons_Public_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Grooni_Theme_Addons_Public_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 *
		 * for example:
		 * wp_enqueue_style( $this->name, plugin_dir_url( __FILE__ ) . 'css/grooni-theme-addons-public.css', array(), $this->version, 'all' );
		 *
		 */

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Grooni_Theme_Addons_Public_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Grooni_Theme_Addons_Public_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 *
		 * for example:
		 * wp_enqueue_script( $this->name, plugin_dir_url( __FILE__ ) . 'js/grooni-theme-addons-public.js', array( 'jquery' ), $this->version, false );
		 *
		 */

	}


	/**
	 * Add rewrite endpoints.
	 *
	 * @since    1.0.0
	 */
	function rewrite() {

	}


	/**
	 * Redirect to URL of redirect point if needed.
	 *
	 * @since    1.0.0
	 */
	public function do_redirect() {

	}



}
