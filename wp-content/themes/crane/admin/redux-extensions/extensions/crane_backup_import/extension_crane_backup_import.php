<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

if ( ! class_exists( 'ReduxFramework_extension_crane_backup_import' ) ) {

	/**
	 * CT backup import extension class for ReduxFramework
	 */
	class ReduxFramework_extension_crane_backup_import extends ReduxFramework {

		// Protected vars
		protected $parent;
		public $extension_url;
		public $extension_dir;
		public static $theInstance;
		public static $version = "1";


		/**
		 * Class Constructor. Defines the args for the extions class
		 *
		 * @param array $parent
		 */
		public function __construct( $parent ) {

			$this->parent = $parent;
			if ( empty( $this->extension_dir ) ) {
				$this->extension_dir = trailingslashit( str_replace( '\\', '/', dirname( __FILE__ ) ) );
			}
			$this->field_name = 'crane_backup_import';

			self::$theInstance = $this;

			if ( function_exists( 'wp_doing_ajax' ) && wp_doing_ajax() ) {
				add_action( 'wp_ajax_crane_backup_btn_save', array(
					$this,
					"crane_backup_btn_save_callback"
				) );

				add_action( 'wp_ajax_crane_backup_btn_restore', array(
					$this,
					"crane_backup_btn_restore_callback"
				) );

				add_action( 'wp_ajax_crane_backup_btn_import', array(
					$this,
					"crane_backup_btn_import_callback"
				) );
			}

			add_filter( 'redux/' . $this->parent->args['opt_name'] . '/field/class/' . $this->field_name, array(
				$this,
				'overload_field_path'
			) ); // Adds the local field

		}

		/**
		 * @access public
		 * @return ReduxFramework_extension_crane_backup_import
		 */
		public function getInstance() {
			return self::$theInstance;
		}


		/**
		 * Save backup options to the WordPress update_option(). Use for Ajax call only
		 */
		function crane_backup_btn_save_callback() {
			if ( ! isset( $_POST['crane_salt'] ) || $_POST['crane_salt'] != md5( md5( AUTH_KEY . SECURE_AUTH_SALT ) . ':' . $this->parent->args['opt_name'] ) ) {
				wp_send_json_error( array(
					'message' => esc_html__( 'Error. Invalid Secret for options use', 'crane' ),
				) ); // Send a JSON response back to an AJAX request, and die().
			}
			if ( isset( $_POST['action'] ) ) {
				if ( $_POST['action'] !== 'crane_backup_btn_save' ) {
					wp_send_json_error( array(
						'message' => esc_html__( 'Error. Bad request, please refresh the page', 'crane' ),
					) ); // Send a JSON response back to an AJAX request, and die().
				}
			}

			global $crane_options;
			$current_options = $crane_options;

			$theme = wp_get_theme();
			if ( is_child_theme() ) {
				$theme = wp_get_theme( $theme->get( 'Template' ) );
			}
			$theme_name = str_replace( ' ', '', trim( mb_strtolower( $theme->get( 'Name' ), 'UTF-8' ) ) );


			$backup          = array(
				'datetime'       => current_time( 'mysql', true ),
				'crane_bkp_options' => $current_options,
			);
			$backup_datetime = strtotime( $backup['datetime'] ) + get_option( 'gmt_offset' ) * HOUR_IN_SECONDS;

			update_option( 'crane_bkp_opt_' . $theme_name, $backup, false );

			wp_send_json_success( array(
				'message' => esc_html__( 'Backup was created', 'crane' ),
				'status'  => esc_html__( 'Last Backup time', 'crane' ) . ': <span class="crane-bkp_datetime">' . date_i18n( 'F j, Y, G:i:s', $backup_datetime ) . '</span>',
			) );

		}


		/**
		 * * Restore backup from the WordPress get_option(). Use for Ajax call only
		 */
		function crane_backup_btn_restore_callback() {
			if ( ! isset( $_POST['crane_salt'] ) || $_POST['crane_salt'] != md5( md5( AUTH_KEY . SECURE_AUTH_SALT ) . ':' . $this->parent->args['opt_name'] ) ) {
				wp_send_json_error( array(
					'message' => esc_html__( 'Error. Invalid Secret for options use', 'crane' ),
				) ); // Send a JSON response back to an AJAX request, and die().
			}
			if ( isset( $_POST['action'] ) ) {
				if ( $_POST['action'] !== 'crane_backup_btn_restore' ) {
					wp_send_json_error( array(
						'message' => esc_html__( 'Error. Bad request, please refresh the page', 'crane' ),
					) ); // Send a JSON response back to an AJAX request, and die().
				}
			}
			if ( ! current_user_can( $this->parent->args['page_permissions'] ) ) {
				wp_send_json_error( array(
					'message' => esc_html__( 'Error. Invalid user capability.  Please refresh the page and try again.', 'crane' ),
				) ); // Send a JSON response back to an AJAX request, and die().
			}

			$theme = wp_get_theme();
			if ( is_child_theme() ) {
				$theme = wp_get_theme( $theme->get( 'Template' ) );
			}
			$theme_name    = str_replace( ' ', '', trim( mb_strtolower( $theme->get( 'Name' ), 'UTF-8' ) ) );
			$backup        = maybe_unserialize( get_option( 'crane_bkp_opt_' . $theme_name ) );
			$error_message = esc_html__( 'We get error, when try restore options. Options not change.', 'crane' ); // default

			if ( isset( $backup['crane_bkp_options'] ) && is_array( $backup['crane_bkp_options'] ) ) {
				$backup['crane_bkp_options']['redux-backup'] = '1';

				$redux = ReduxFrameworkInstances::get_instance( $this->parent->args['opt_name'] );

				try {
					if ( isset ( $redux->validation_ran ) ) {
						unset ( $redux->validation_ran );
					}
					$valid_options = $redux->_validate_options( array(
						'import_code' => json_encode( $backup['crane_bkp_options'] ),
					) );

					if ( is_array( $valid_options ) && isset( $valid_options['redux-backup'] ) ) {
						unset( $valid_options['redux-backup'] );
					}


					// Save redux options
					$redux->set_options( $valid_options );


					if ( ! empty( $backup['crane_bkp_options']['favicon'] ) && is_array( $backup['crane_bkp_options']['favicon'] ) ) {
						$favicon_arr = $backup['crane_bkp_options']['favicon'];

						if ( ! empty( $favicon_arr['id'] ) ) {
							$image_full  = wp_get_attachment_image_src( $favicon_arr['id'], 'full' );
							$image_thumb = wp_get_attachment_image_src( $favicon_arr['id'], 'thumbnail' );
						} else {
							$image_full  = [ '', '', '' ];
							$image_thumb = [ '', '', '' ];
						}

						Redux::setOption( 'crane_options', 'favicon', [
							'url'       => isset( $image_full[0] ) ? $image_full[0] : '',
							'id'        => $favicon_arr['id'],
							'height'    => isset( $image_full[2] ) ? strval( $image_full[2] ) : '',
							'width'     => isset( $image_full[1] ) ? strval( $image_full[1] ) : '',
							'thumbnail' => isset( $image_thumb[0] ) ? $image_thumb[0] : '',
						] );

						update_option( 'site_icon', $favicon_arr['id'] );
					}


					wp_send_json_success( array(
						'message' => esc_html__( 'Backup was restored', 'crane' ),
						'options' => json_encode( $valid_options )
					) ); // Send a JSON response back to an AJAX request, and die().

				} catch ( Exception $e ) {
					$error_message = array( 'status' => $e->getMessage() );
				}

			} else {
				$error_message = esc_html__( 'Error. Invalid backup.', 'crane' );
			}

			wp_send_json_error( array(
				'message' => $error_message,
			) ); // Send a JSON response back to an AJAX request, and die().

		}


		/**
		 * Import options from user front-end textarea field. Use for Ajax call only
		 */
		function crane_backup_btn_import_callback() {
			if ( ! isset( $_POST['crane_salt'] ) || $_POST['crane_salt'] != md5( md5( AUTH_KEY . SECURE_AUTH_SALT ) . ':' . $this->parent->args['opt_name'] ) ) {
				wp_send_json_error( array(
					'message' => esc_html__( 'Error. Invalid Secret for options use', 'crane' ),
				) ); // Send a JSON response back to an AJAX request, and die().
			}
			if ( isset( $_POST['action'] ) ) {
				if ( $_POST['action'] !== 'crane_backup_btn_import' ) {
					wp_send_json_error( array(
						'message' => esc_html__( 'Error. Bad request, please refresh the page', 'crane' ),
					) ); // Send a JSON response back to an AJAX request, and die().
				}
			}
			if ( ! isset( $_POST['import_data'] ) || empty ( $_POST['import_data'] ) ) {
				wp_send_json_error( array(
					'message' => esc_html__( 'Error. Invalid import data', 'crane' ),
				) ); // Send a JSON response back to an AJAX request, and die().
			}
			if ( ! current_user_can( $this->parent->args['page_permissions'] ) ) {
				wp_send_json_error( array(
					'message' => esc_html__( 'Error. Invalid user capability.  Please refresh the page and try again.', 'crane' ),
				) ); // Send a JSON response back to an AJAX request, and die().
			}
			$error_message = esc_html__( 'We get error, when try restore options. Options not change.', 'crane' ); // default

			$redux = ReduxFrameworkInstances::get_instance( $this->parent->args['opt_name'] );
			if ( ! empty ( $_POST['import_data'] ) && ! empty ( $redux->args['opt_name'] ) ) {

				$new_options = stripslashes( $_POST['import_data'] );
				$new_options = json_decode( $new_options, true );

				if ( function_exists( 'get_magic_quotes_gpc' ) && get_magic_quotes_gpc() ) {
					$new_options = array_map( 'stripslashes_deep', $new_options );
				}

				if ( ! empty ( $new_options ) && is_array( $new_options ) ) {
					try {
						if ( isset ( $redux->validation_ran ) ) {
							unset ( $redux->validation_ran );
						}

						$backup = $redux->_validate_options( $new_options );

						// Save new options
						$redux->set_options( $backup );


						if ( ! empty( $backup['favicon'] ) && is_array( $backup['favicon'] ) ) {
							$favicon_arr = $backup['favicon'];

							if ( ! empty( $favicon_arr['id'] ) ) {
								$image_full  = wp_get_attachment_image_src( $favicon_arr['id'], 'full' );
								$image_thumb = wp_get_attachment_image_src( $favicon_arr['id'], 'thumbnail' );
							} else {
								$image_full  = [ '', '', '' ];
								$image_thumb = [ '', '', '' ];
							}

							Redux::setOption( 'crane_options', 'favicon', [
								'url'       => isset( $image_full[0] ) ? $image_full[0] : '',
								'id'        => $favicon_arr['id'],
								'height'    => isset( $image_full[2] ) ? strval( $image_full[2] ) : '',
								'width'     => isset( $image_full[1] ) ? strval( $image_full[1] ) : '',
								'thumbnail' => isset( $image_thumb[0] ) ? $image_thumb[0] : '',
							] );

							update_option( 'site_icon', $favicon_arr['id'] );
						}



						wp_send_json_success( array(
							'message' => esc_html__( 'New options was imported', 'crane' ),
							'options' => json_encode( $new_options )
						) ); // Send a JSON response back to an AJAX request, and die().

					} catch ( Exception $e ) {
						$error_message = array( 'status' => $e->getMessage() );
					}
				} else {
					$error_message = esc_html__( 'Error. New option is invalid.', 'crane' );
				}

			} else {
				$error_message = esc_html__( 'Error. Invalid backup.', 'crane' );
			}

			wp_send_json_error( array(
				'message' => $error_message,
			) ); // Send a JSON response back to an AJAX request, and die().

		}


		/**
		 * Forces the use of the embeded field path vs what the core typically would use
		 *
		 * @param $field
		 *
		 * @access public
		 * @return string
		 */
		public function overload_field_path( $field ) {
			return dirname( __FILE__ ) . '/field_' . $this->field_name . '.php';
		}

	}
}
