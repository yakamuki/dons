<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

define( 'GROONI_THEME_ADDONS_IMPORT_URL', untrailingslashit( plugins_url( '/', __FILE__ ) ) );
define( 'GROONI_THEME_ADDONS_IMPORT_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) );

class Grooni_Theme_Addons_Import {

	public $demos = array();
	public $style = array();
	public $support = array();

	private $response = array();
	private $importer;


	public function __construct() {

		if ( ! function_exists( 'is_user_logged_in' ) ) {
			require_once ABSPATH . 'wp-includes/pluggable.php';
		}

		$this->response  = array( 'status' => 'fail', 'message' => '', 'data' => array() );

		add_action( 'init', array( $this, 'init' ) );

		if ( function_exists( 'wp_doing_ajax' ) && wp_doing_ajax() ) {

			// Install demo data
			add_action( 'wp_ajax_grooni_theme_addons_import_part', array( $this, 'ajax_import_procces' ) );

			// Install preset data
			add_action( 'wp_ajax_grooni_theme_addons_import_preset', array( $this, 'ajax_import_preset' ) );

			// Check and send import status
			add_action( 'wp_ajax_grooni_theme_addons_check_import_status', array( $this, 'check_import_status' ) );

			// Check and send import status
			add_action( 'wp_ajax_grooni_theme_addons_get_import_info', array( $this, 'get_import_info' ) );

		}

	}


	public function init() {
		$this->demos = apply_filters( 'grooni_addons_import_demos', array() );
	}


	function ajax_import_procces() {

		$procces_content = ( isset( $_POST['content'] ) ) ? (string) $_POST['content'] : '';
		$procces_steps   = ( isset( $_POST['steps'] ) ) ? (array) $_POST['steps']  : array();
		$procces_type    = ( isset( $_POST['type'] ) ) ? (string) $_POST['type'] : '';

		if ( empty( $procces_content ) ) {
			$this->send_fail_msg( esc_html__( 'Illegal ajax action', 'grooni-theme-addons' ) );
		}


		$this->load_importers();

		$option = array();

		$grooni_importer = new Grooni_Theme_Addons_Import_Helper( $option );
		$logger          = new Grooni_Theme_WP_Importer_Logger_CLI();
		$grooni_importer->set_logger( $logger );
		$grooni_importer->before_import();
		$grooni_importer->check_limits();


		$start = microtime( true );

		$grooni_importer->store_import_info( 'STEP Start: ' . $procces_content );

		switch ( $procces_content ) {

			case 'dummy':
				$grooni_importer->update_import_status();
				$grooni_importer->add_menu_data(); // Main menu by default
				//$grooni_importer->bump_posts_table_id( 99999 ); // No need with new import (after 1.2.8 version)
				break;

			case 'plugins':
				$grooni_importer->download_and_install_assets();
				$grooni_importer->get_and_activate_plugins( $procces_steps );

				if ( 'import-all' === $procces_type ) {
					set_transient( 'grooni_import_step--mc4wp-form', true, 4 * HOUR_IN_SECONDS );
					set_transient( 'grooni_import_step--wpcf7_contact_form', true, 4 * HOUR_IN_SECONDS );
				} else {
					$necessary_plugins = $grooni_importer->options['necessary_plugins'];
					if ( ! empty( $necessary_plugins ) ) {
						if ( is_array( $procces_steps ) ) {
							foreach ( $procces_steps as $step ) {
								if ( ! empty( $necessary_plugins[ $step ] ) ) {
									foreach ( $necessary_plugins[ $step ] as $plugin => $allow ) {
										if ( $allow ) {
											switch ( $plugin ) {
												case 'mailchimp-for-wp':
													set_transient( 'grooni_import_step--mc4wp-form', true, 4 * HOUR_IN_SECONDS );
													break;
												case 'contact-form-7':
													set_transient( 'grooni_import_step--wpcf7_contact_form', true, 4 * HOUR_IN_SECONDS );
													break;
											}
										}
									}
								}
							}
						}
					}
				}

				break;

			case 'prexml':
				$grooni_importer->import_groovy_menu();
				$grooni_importer->import_theme_options();
				$grooni_importer->import_xml_file( 'menu/home' );
				$grooni_importer->import_xml_file( 'crane_footer' );
				$grooni_importer->import_sidebars();

				break;

			case 'attachment':
				$grooni_importer->import_xml_file( $procces_content );
				break;

			case 'additional_menus':
				$grooni_importer->add_menu_data( array(
					'slug'      => 'single-page-menu',
					'menu-name' => 'Single page menu'
				), false );
				$grooni_importer->import_xml_file( 'menu/second_menu' );

				break;

			case 'groovyPresets':
				$grooni_importer->import_groovy_menu();
				break;

			case 'redux':
				$grooni_importer->import_theme_options();
				break;

			case 'sidebars':
				$grooni_importer->import_sidebars();
				break;

			case 'convertplug':
				$grooni_importer->import_convertplug();
				break;

			case 'revslider':
				$grooni_importer->import_rev_sliders();
				break;

			case 'shop':

				if ( ! class_exists( 'WooCommerce' ) ) {
					break;
				}

				//$grooni_importer->import_woocommerce_attributes();
				$grooni_importer->import_xml_file( 'shop-category' );

				$grooni_importer->import_xml_file( $procces_content );
				$grooni_importer->import_xml_file( 'product_variation' );

				$grooni_importer->import_woocommerce_pages();

				if ( is_file( $grooni_importer->get_assets_data( 'file', 'menu/' . $procces_content ) ) ) {
					$grooni_importer->import_xml_file( 'menu/' . $procces_content );
				}

				$grooni_importer->import_taxonomy_meta( $procces_content );

				$grooni_importer->import_rev_sliders( $procces_content );

				break;

			case 'shop-attributes':

				if ( ! class_exists( 'WooCommerce' ) ) {
					break;
				}

				$grooni_importer->import_woocommerce_attributes();

				//$grooni_importer->import_xml_file( 'shop-category' );

				$grooni_importer->import_xml_file( 'shop-page' );

				break;

			case 'portfolio':
			case 'blog':

				$grooni_importer->import_xml_file( $procces_content . '-category' );
				$grooni_importer->import_xml_file( $procces_content . '-page' );
				$grooni_importer->import_xml_file( $procces_content );

				if ( is_file( $grooni_importer->get_assets_data( 'file', 'menu/' . $procces_content ) ) ) {
					$grooni_importer->import_xml_file( 'menu/' . $procces_content );
				}

				$grooni_importer->import_taxonomy_meta( $procces_content );

				$grooni_importer->import_rev_sliders( $procces_content );

				break;

			case 'menu/second_menu':
				$grooni_importer->add_menu_data( array(
					'slug'      => 'single-page-menu',
					'menu-name' => 'Single page menu'
				), false );
				$grooni_importer->import_xml_file( $procces_content );
				break;

			case 'all-home':

				$exclude_ids = array();
				$pages_home = $grooni_importer->options['pages_home'];
				foreach ( $pages_home as $page_name => $page_id ) {
					if ( $grooni_importer->check_import_option_flag( $page_name ) ) {
						$exclude_ids[ $page_id ] = $page_id;
					}
				}
				$menus_home = $grooni_importer->options['menus_home'];
				foreach ( $menus_home as $page_name => $menu_id ) {
					if ( $grooni_importer->check_import_option_flag( 'menu--' . $page_name ) ) {
						$exclude_ids[ $menu_id ] = $menu_id;
					}
				}

				$grooni_importer->import_xml_file( $procces_content, $exclude_ids );

				if ( is_file( $grooni_importer->get_assets_data( 'file', 'menu/' . $procces_content ) ) ) {
					$grooni_importer->import_xml_file( 'menu/' . $procces_content, $exclude_ids );
				}

				foreach ( $pages_home as $page_name => $page_id ) {
					$grooni_importer->update_import_option_flag( $page_name );
					$grooni_importer->update_import_option_flag( 'menu--' . $page_name );
				}

				$procces_content_revslider = $procces_content;
				if ( 'import-all' === $procces_type ) {
					$procces_content_revslider = '';
				}
				$grooni_importer->import_rev_sliders( $procces_content_revslider );

				break;

			case 'import_all_after':
				$grooni_importer->import_all_after();

				foreach ( array( 'mc4wp-form', 'wpcf7_contact_form' ) as $step ) {
					if ( get_transient( 'grooni_import_step--' . $step ) ) {
						$grooni_importer->import_xml_file( $step );
						delete_transient( 'grooni_import_step--' . $step );
					}
				}

				$grooni_importer->cleanup();
				break;


			default:
				if ( ! $grooni_importer->check_import_option_flag( 'menu--sub_menu_home_new' ) && $procces_content == 'cargo' || $procces_content == 'education' || $procces_content == 'barber' ) {
					$grooni_importer->import_xml_file( 'menu/sub_menu_home_new' );
				}

				$grooni_importer->import_xml_file( $procces_content );

				if ( is_file( $grooni_importer->get_assets_data( 'file', 'menu/' . $procces_content ) ) ) {
					$grooni_importer->import_xml_file( 'menu/' . $procces_content );
				}

				$grooni_importer->import_rev_sliders( $procces_content );

				break;

		}


		$time_elapsed_secs = $this->format_import_time( microtime( true ) - $start );

		$this->update_gm_used_in();

		$this->send_success_msg(
			esc_html__( 'Done. Used time is:', 'grooni-theme-addons' ) . ' ' . $time_elapsed_secs,
			array( 'module' => $procces_content )
		);

	}


	function ajax_import_preset() {

		$procces_content = ( isset( $_POST['content'] ) ) ? (string) $_POST['content'] : '';
		$procces_steps   = ( isset( $_POST['steps'] ) ) ? (array) $_POST['steps'] : array();

		if ( empty( $procces_content ) ) {
			$this->send_fail_msg( esc_html__( 'Illegal ajax action. Missing preset_name.', 'grooni-theme-addons' ) );
		}

		$this->load_importers();

		$option = array();

		$grooni_importer = new Grooni_Theme_Addons_Import_Helper( $option );
		$logger          = new Grooni_Theme_WP_Importer_Logger_CLI();
		$grooni_importer->set_logger( $logger );
		$grooni_importer->before_import();
		$grooni_importer->check_limits();


		$start = microtime( true );


		switch ( $procces_content ) {

			case 'dummy':
				$grooni_importer->update_import_status();
				break;

			case 'plugins':
				$grooni_importer->get_and_activate_plugins( $procces_steps, true );
				break;

			case 'import_all_after':
				$grooni_importer->cleanup( true );
				break;

			case 'all-home':
				break;

			default:

				$grooni_importer->store_import_info( 'START import demo data preset: ' . $procces_content );

				if ( ! $grooni_importer->is_preset_exist( $procces_content ) ) {
					$grooni_importer->store_import_info( 'Error: preset [' . $procces_content . '] not exist. Skipped.' );
					break;
				}

				$grooni_importer->download_preset( $procces_content );
				$preset_data = $grooni_importer->get_preset_data( $procces_content );

				if ( ! empty( $preset_data['assets'] ) ) {
					$preset_assets = $grooni_importer->upload_preset_assets( $procces_content, $preset_data['assets'] );

					if ( ! empty( $preset_assets ) ) {
						$preset_data['assets'] = $preset_assets;
					}

					$preset_data['posts'] = $grooni_importer->replace_assets_patterns( $preset_data );

				}

				// IMPORT posts
				$grooni_importer->import_posts_from_preset( $preset_data['posts'] );


				$grooni_importer->import_plugins_related_data( $preset_data );


				$grooni_importer->import_preset_sidebars( $preset_data );


				$grooni_importer->import_preset_nav_menus( $preset_data );


				break;

		}

		$time_elapsed_secs = $this->format_import_time( microtime( true ) - $start );

		$this->update_gm_used_in();

		$this->send_success_msg(
			esc_html__( 'Done. Used time is:', 'grooni-theme-addons' ) . ' ' . $time_elapsed_secs,
			array( 'module' => $procces_content )
		);

	}

	/**
	 * Format import time to human readable
	 *
	 * @param $time
	 *
	 * @return string
	 */
	public function format_import_time( $time ) {
		return gmdate( 'H:i:s', $time );
	}


	public function check_import_status() {

		$this->load_importers();
		$option          = array();
		$grooni_importer = new Grooni_Theme_Addons_Import_Helper( $option );

		$data = get_option( $grooni_importer::IMPORT_STATUS_OPTION_NAME );

		if ( ! $data || ! is_array( $data ) ) {
			$this->send_msg( 'clean', '', array() );
		}

		$_data = end( $data );

		$status   = isset( $_data['status'] ) ? $_data['status'] : 'success';
		$message  = isset( $_data['message'] ) ? $_data['message'] : '';
		$progress = isset( $_data['progress'] ) ? $_data['progress'] : '';

		$data = array();
		if ( $progress ) {
			$data = array( 'progress' => $progress );
		}

		$this->send_msg( $status, $message, $data );

	}


	public function get_import_info() {

		$this->load_importers();
		$option          = array();
		$grooni_importer = new Grooni_Theme_Addons_Import_Helper( $option );

		$data = get_option( $grooni_importer::IMPORT_STATUS_OPTION_NAME );

		if ( ! $data || ! is_array( $data ) ) {
			$this->send_msg( 'clean', '', array() );
		}

		$output_html = '';

		foreach ( $data as $index => $dat ) {
			$output_html .= '<p class="log-element">';
			$output_html .= '	<span class="log-element--index">' . $index . '</span>';
			$output_html .= '	<span class="log-element--status">' . $dat["status"] . '</span>';
			$output_html .= '	<span class="log-element--message">' . $dat["message"] . '</span>';
			$output_html .= '</p>';
		}

		$this->send_msg( 'log', $output_html, array() );

	}

	public function is_import_job_exists() {

		$this->load_importers();
		$option          = array();
		$grooni_importer = new Grooni_Theme_Addons_Import_Helper( $option );

		$data = get_option( $grooni_importer::IMPORT_STATUS_OPTION_NAME );

		return ( ! $data || ! is_array( $data ) ) ? false : true;

	}

	private function load_importers() {

		require_once GROONI_THEME_ADDONS_IMPORT_PATH . '/class-grooni-theme-addons-import-helper.php';

		if ( ! class_exists( 'Grooni_Theme_Addons_Import_Helper' ) ) {

			@ob_clean();
			wp_send_json( array(
				'status'  => 'critical_error',
				'message' => __( 'Can\'t find Grooni_Theme_Addons_Import_Helper class', 'grooni-theme-addons' )
			), 500 );

		}
	}


	private function send_response() {

		@ob_clean();

		if ( ! empty( $this->response ) ) {
			wp_send_json( $this->response );
		} else {
			wp_send_json( array( 'message' => 'empty response' ) );
		}
	}

	private function send_success_msg( $msg, $data = array() ) {

		$this->send_msg( 'success', $msg, $data );

	}


	private function send_fail_msg( $msg, $data = array() ) {

		$this->send_msg( 'fail', $msg, $data );

	}

	private function send_msg( $status, $message, $data = array() ) {
		$this->response = array(
			'status'  => $status,
			'message' => $message,
			'data'    => $data,
		);

		$this->send_response();
	}

	private function update_gm_used_in() {
		if ( class_exists( 'GroovyMenuUtils' ) ) {
			if ( method_exists( 'GroovyMenuUtils', 'update_preset_used_in' ) ) {
				GroovyMenuUtils::update_preset_used_in();
			}
		}
	}

}


new Grooni_Theme_Addons_Import();
