<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

if ( ! class_exists( 'ReduxFramework_crane_backup_import' ) ) {

	/**
	 * Main ReduxFramework_crane_backup_import class
	 */
	class ReduxFramework_crane_backup_import extends ReduxFramework {

		function __construct( $field = array(), $value = '', $parent ) {

			$this->parent = $parent;
			$this->field  = $field;
			$this->value  = $value;

			if ( empty( $this->extension_dir ) ) {
				$this->extension_dir = trailingslashit( str_replace( '\\', '/', dirname( __FILE__ ) ) );
				$this->extension_url = site_url( str_replace( trailingslashit( str_replace( '\\', '/', ABSPATH ) ), '', $this->extension_dir ) );
			}

			// Set default args for this field to avoid bad indexes. Change this to anything you use.
			$defaults    = array(
				'options'          => array(),
				'stylesheet'       => '',
				'output'           => true,
				'enqueue'          => false,
				'enqueue_frontend' => false
			);
			$this->field = wp_parse_args( $this->field, $defaults );

		}

		/**
		 * Field Render Function.
		 * Takes the vars and outputs the HTML for the field in the settings
		 *
		 * @access      public
		 * @return      void
		 */
		public function render() {
			$this->field = wp_parse_args(
				$this->field,
				array(
					'full_width' => true,
					'overflow'   => 'inherit',
				)
			);

			$theme = wp_get_theme();
			if ( is_child_theme() ) {
				$theme = wp_get_theme( $theme->get( 'Template' ) );
			}
			$theme_name = str_replace( ' ', '', trim( mb_strtolower( $theme->get( 'Name' ), 'UTF-8' ) ) );

			$backup = maybe_unserialize( get_option( 'crane_bkp_opt_' . $theme_name ) );

			$this->parent->get_options();
			$current_options                 = $this->parent->options;
			$current_options['redux-backup'] = '1';

			$visible_content = '';
			$backup_exist    = true;
			if ( ! $backup || ! is_array( $backup ) || ! isset( $backup['crane_bkp_options'] ) ) {
				$backup_exist = false;
			}

			// Data: $this->parent->args['opt_name'] & $this->field['id'] & $this->field['type'] are sanitized in the ReduxFramework class, no need to re-sanitize it.
			$id = $this->parent->args['opt_name'] . '-' . $this->field['id'];

			?>
			<div class="crane-sub_section-wrap">
				<div class="crane-sub_section-right">
					<div
						class="crane-backup_copy_status<?php echo( $backup_exist ? '' : ' crane_backup_is_empty' ); ?>">
						<?php
						if ( $backup && is_array( $backup ) && isset( $backup['datetime'] ) ) {
							$backup_datetime = strtotime( $backup['datetime'] ) + get_option( 'gmt_offset' ) * HOUR_IN_SECONDS;
							echo esc_html__( 'Last Backup time', 'crane' ) . ': <span class="crane-bkp_datetime">' . date_i18n( 'F j, Y, G:i:s', $backup_datetime ) . '</span>';
						} else {
							echo esc_html__( 'No any backups yet', 'crane' );
						}
						?>
					</div>
					<a href="javascript:void(0);" class="crane-backup_btn_save button-secondary">
						<?php esc_html_e( 'Save reserve copy', 'crane' ); ?>
					</a>
					<a href="javascript:void(0);"
					   class="crane-backup_btn_restore button-secondary <?php echo( $backup_exist ? '' : 'hidden' ); ?>">
						<?php esc_html_e( 'Restore reserve copy', 'crane' ); ?>
					</a>
				</div>
			</div>

			<div class="crane-sub_section-wrap">
				<h4><?php esc_html_e( 'Import theme options', 'crane' ); ?></h4>

				<div class="crane-sub_section-right">
					<textarea class="large-text noUpdate" id="crane_backup_import_code_textarea"
					          rows="5"><?php echo json_encode( $current_options ); ?></textarea>
					<a href="javascript:void(0);" class="crane-backup_btn_import button-primary">
						<?php esc_html_e( 'Import and Save the options', 'crane' ); ?>
					</a>
				</div>
			</div>
			<input type="hidden" id="crane_backup_import_salt" name="crane_data"
			       value="<?php echo md5( md5( AUTH_KEY . SECURE_AUTH_SALT ) . ':' . $this->parent->args['opt_name'] ); ?>">
			<?php
		}

		/**
		 * Output Function.
		 * Used to enqueue to the front-end
		 * @access      public
		 * @return      void
		 */
		public function output() {

			if ( $this->field['enqueue_frontend'] ) {

			}

		}
	}
}
