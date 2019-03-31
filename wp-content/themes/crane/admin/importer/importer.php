<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

if ( ! class_exists( 'Crane_ContentImporter' ) ) {
	class Crane_ContentImporter {

		// save and get from JSON in wp_options
		protected $presets_info_url = '';

		protected $presets_data_array = array();

		protected $import_content_pack = array(
			'portfolio' => array(
				'title'     => 'Portfolio',
				'is_preset' => 'no'
			),
			'shop'      => array(
				'title'     => 'Shop',
				'is_preset' => 'no'
			),
			'pages'     => array(
				'title'     => 'Pages',
				'is_preset' => 'no'
			),
			'elements'  => array(
				'title'     => 'Elements',
				'is_preset' => 'no'
			),
			'blog'      => array(
				'title'     => 'Blog',
				'is_preset' => 'no'
			),
		);

		protected $presets_info_option_name = 'crane_presets_info_data';

		protected $crane_imported_flags = 'crane_imported_flags';

		/**
		 * The constructor.
		 */
		public function __construct() {

			$demo_package = apply_filters( 'grooni_addons_import_demos', array() );

			if ( isset( $demo_package['crane']['presets_info_url'] ) ) {
				$this->presets_info_url = $demo_package['crane']['presets_info_url'];
			}

		}


		public function init() {
			if ( isset( $_GET['page'] ) and $_GET['page'] === 'crane_import' and ! empty( $_POST ) ) {
				ob_start();
			}

			if ( function_exists( 'grooni_add_subpage_to_dashboard' ) && class_exists( 'Grooni_Theme_Addons_Import' ) ) {
				grooni_add_subpage_to_dashboard(
					'crane-theme-dashboard',
					esc_html__( 'Import content', 'crane' ),
					esc_html__( 'Import content', 'crane' ),
					'edit_theme_options',
					'crane_import',
					array( $this, 'show_import_page' )
				);
			}

		}


		public function get_presets_data() {

			if ( get_transient( $this->presets_info_option_name . '_savetime' ) ) {
				if ( $saved_presets = get_option( $this->presets_info_option_name ) ) {
					$this->presets_data_array = $saved_presets;

					return;
				}
			}

			$presets_info_data = wp_remote_get( $this->presets_info_url );

			if ( ! is_wp_error( $presets_info_data ) && ! empty( $presets_info_data['body'] ) ) {

				$presets_info = json_decode( $presets_info_data['body'], true );

				if ( ! empty( $presets_info ) && is_array( $presets_info ) ) {

					$current_presets = $this->presets_data_array;

					foreach ( $presets_info as $preset_name => $preset_data ) {

						if ( empty( $preset_data['preset_type'] ) ) {
							continue;
						}

						$current_presets[ $preset_name ] = array(
							'demo_url'      => esc_url( $preset_data['demo_url'] ),
							'title'         => esc_html( $preset_data['preset_name'] ),
							'thumbnail_img' => $preset_data['screenshots'],
							'type'          => esc_attr( $preset_data['preset_type'] ),
							'is_preset'     => 'yes',
						);

						if ( is_array( $current_presets[ $preset_name ]['thumbnail_img'] ) ) {
							$_images = $current_presets[ $preset_name ]['thumbnail_img'];

							if ( ! empty( $_images['local'] ) ) {
								$img_url = get_template_directory_uri() . '/assets/images/wp/admin-import/' . $_images['local'];
							} elseif ( ! empty( $_images['from_demo'] ) ) {
								$img_url = $_images['from_demo'];
							}


							$current_presets[ $preset_name ]['thumbnail_img'] = esc_url( $img_url );
						}

					}

					foreach ( $current_presets as $preset_name => $preset_data ) {
						if ( isset( $preset_data['slug'] ) ) {
							$preset_data['demo_url'] = 'http://crane.grooni.com/' . $preset_data['slug'];
						}
						if ( isset( $preset_data['img'] ) ) {
							$preset_data['thumbnail_img'] = get_template_directory_uri() . '/assets/images/wp/admin-import/' . $_images['img'];
						}
					}


					$this->presets_data_array = $current_presets;

					// Save for cache
					update_option( $this->presets_info_option_name, $current_presets, false );
					set_transient( $this->presets_info_option_name . '_savetime', true, 1 * HOUR_IN_SECONDS );

				}

			} elseif ( $saved_presets = get_option( $this->presets_info_option_name ) ) {
				$this->presets_data_array = $saved_presets;

				return;
			}

		}


		public function sort_presets_data() {
			$is_imported_before = $this->is_imported_before();

			$imported_flags = get_option( $this->crane_imported_flags );

			$steps_available_for_import = $this->import_content_pack;

			foreach ( $this->presets_data_array as $preset_name => $preset_data ) {

				if ( isset( $preset_data['type'] ) && 'page' === $preset_data['type'] ) {
					$steps_available_for_import[ $preset_name ] = $preset_data;
				}
				if ( isset( $preset_data['type'] ) && 'by_menu_parent' === $preset_data['type'] ) {
					if ( isset( $steps_available_for_import[ $preset_name ] ) ) {
						$steps_available_for_import[ $preset_name ] = $preset_data;
						$this->import_content_pack[ $preset_name ]  = $preset_data;
					}
				}

			}

			foreach ( $steps_available_for_import as $preset_name => $preset_data ) {
				if ( $is_imported_before &&
				     isset( $steps_available_for_import[ $preset_name ]['is_preset'] ) &&
				     'no' === $steps_available_for_import[ $preset_name ]['is_preset']
				) {
					unset( $steps_available_for_import[ $preset_name ] );
				}
			}


			if ( $imported_flags && is_array( $imported_flags ) ) {
				foreach ( $imported_flags as $step => $step_data ) {
					if ( isset( $steps_available_for_import[ $step ] ) ) {
						if ( isset( $steps_available_for_import[ $step ]['is_preset'] ) &&
						     'yes' === $steps_available_for_import[ $step ]['is_preset']
						) {
							continue;
						}

						unset( $steps_available_for_import[ $step ] );
					}
				}
			}


			return $steps_available_for_import;

		}


		public function get_last_table_posts_id() {
			global $wpdb;

			$last_table_id = 1;
			$table_name    = $wpdb->prefix . "posts";

			if ( $last_table_row = $wpdb->get_row( "SHOW TABLE STATUS LIKE '{$table_name}'" ) ) {
				$last_table_id = $last_table_row->Auto_increment;
			}

			return $last_table_id;
		}

		public function is_imported_before() {

			if ( intval( $this->get_last_table_posts_id() ) > 25 ) {
				return true;
			}

			if ( is_multisite() ) {
				return true;
			}

			$imported_flags = get_option( $this->crane_imported_flags );
			if ( empty( $imported_flags ) || ! is_array( $imported_flags ) ) {
				$imported_flags = array();
			}
			if ( isset( $imported_flags['theme_version'] ) ) {
				return true;
			}

			return false;
		}


		public function show_import_page() {

			if ( ! empty( $_POST ) && isset( $_POST['content'] ) ) {

				$name = $_POST['content'];
				ob_clean();
				set_time_limit( 0 );

				$name = $_POST['content'];
				$type = $_POST['type'];

				echo 'Step: ' . esc_attr( $name ) . ' - Finish, (type: ' . $type . ')';
				exit;

			}

			$this->get_presets_data();

			$is_imported_before         = $this->is_imported_before();
			$imported_flags             = get_option( $this->crane_imported_flags );
			$steps_available_for_import = $this->sort_presets_data();

			$theme         = wp_get_theme();
			$theme_version = $theme->get( 'Version' );
			if ( CRANE_THEME_VERSION !== $theme_version ) {
				$theme_version = CRANE_THEME_VERSION . ' [' . esc_html__( 'child version', 'crane' ) . ': ' . $theme_version . ']';
			}

			?>
			<div class="crane-admin-page<?php if ( empty( $steps_available_for_import ) ) {
				echo ' crane-admin-page--all-done';
			} ?>">
				<?php if ( empty( $steps_available_for_import ) ): ?>
					<div class="crane-import-all-notice">
						<p><?php esc_html_e( 'Demo content already has been imported. Importing over existing data will cause errors in the site.', 'crane' ); ?></p>
					</div>
				<?php endif; ?>
				<div class="crane-admin-header">
					<div class="crane-admin-header-logo-group">
						<img src="<?php echo get_template_directory_uri(); ?>/assets/images/wp/crane-green-white.svg" alt="crane logo">
						<?php if ( $theme_version ) { ?>
							<span class="crane-theme-version"><?php echo esc_html( $theme_version ); ?></span>
						<?php } ?>
					</div>

					<a href="http://grooni.com/" class="crane-admin-header-link">
						<img src="<?php echo get_template_directory_uri(); ?>/assets/images/wp/theme-by-grooni.svg" alt="grooni logo">
					</a>
				</div>

				<div class="crane-admin-import">

					<form method="post" action="" id="crane-import">
						<?php echo wp_nonce_field(); ?>
						<h1 class="crane-import-alpha"><?php esc_html_e( 'Demo import', 'crane' ); ?></h1>

						<p class="crane-import-txt"><?php esc_html_e( 'If you want to import all the demo data at once, please make sure you have a clean WordPress installation. It means there shouldn&#39;t be any other content, categories, sliders or other types of content there. To reset your installation we recommend WordPress Database Reset plugin. Otherwise &quot;Import all demo content&quot; functionality will be blocked.', 'crane' ); ?></p>

						<div class="crane-import-selector crane-import-selector-main">
							<label<?php if ( $is_imported_before ) {
								echo ' title="' . esc_html__( 'This content is available for import only on a new WordPress installation. Importing over existing data will cause errors in the site.', 'crane' ) . '" class="crane-import-done"';
							} ?>>
								<input type="radio" class="crane-import-radio" name="import-checkbox" value="import-all"
								<?php if ( $is_imported_before ) {
									echo 'disabled/>';
									esc_html_e( 'Import all demo content and pages', 'crane' );
								} else {
									echo 'checked/>';
									esc_html_e( 'Import all demo content and pages', 'crane' );
								} ?>
							</label>
							<label>
								<input type="radio" class="crane-import-radio crane-import-radio--custom"
								       name="import-checkbox" value="custom-import" <?php
								if ( $is_imported_before ) {
									echo 'checked';
								} ?>/>
								<?php esc_html_e( 'Custom import', 'crane' ); ?>
							</label>
						</div>
						<div class="crane-import-wrapper<?php
						if ( ! $is_imported_before ) {
							echo ' hidden';
						} ?>">
							<div class="crane-import-selector">
								<span class="crane-import-selector-title"><?php esc_html_e( 'Select:', 'crane' ); ?></span>
								<label>
									<input id="all-home" type="checkbox" class="crane-import-checkbox" name="import[]" value="all-home"/>
									<?php esc_html_e( 'All home pages', 'crane' ); ?>
								</label>
								<?php foreach ( $this->import_content_pack as $name => $value ) { ?>
									<label<?php if ( ! isset( $steps_available_for_import[ $name ] ) ) {
										echo ' title="' . esc_html__( 'This content is available for import only on a new WordPress installation. Importing over existing data will cause errors in the site.', 'crane' ) . '" class="crane-import-done"';
									} ?>>
										<input type="checkbox" class="crane-import-checkbox"
										       name="import[]" value="<?php echo esc_attr( $name ); ?>"
										       data-is_preset="<?php echo esc_attr( $value['is_preset'] ); ?>"<?php if ( ! isset( $steps_available_for_import[ $name ] ) ) { echo ' disabled'; } ?> />
										<?php echo esc_html( $value['title'] ); ?>
									</label>
								<?php } ?>
							</div>

							<div class="crane-admin-import-container">
								<div class="crane-admin-import-row">

									<?php foreach ( $this->presets_data_array as $home => $home_data ) {
										if ( 'page' !== $home_data['type'] ) {
											continue;
										}
										?>

										<div class="crane-import-page crane-import-page--<?php echo esc_js( $home ); ?><?php
											if ( ! isset( $steps_available_for_import[ $home ] ) ) {
												echo ' crane-import-done';
											} ?>" data-type="<?php echo esc_attr( $home_data['type'] ); ?>"
											data-is_preset="<?php echo esc_attr( $home_data['is_preset'] ); ?>">
											<div class="crane-import-page-inner"
											     data-page="<?php echo esc_js( $home ); ?>">
												<img src="<?php echo esc_attr( $home_data['thumbnail_img'] ); ?>" alt="demo preset image" class="crane-import-page-img">

												<div class="crane-import-page-overlay"></div>
												<div class="crane-import-page-content">
													<h5 class="crane-import-page-title"><?php echo esc_html( $home_data['title'] ); ?></h5>
													<span class="crane-import-page-content-divider"></span>

													<div class="crane-import-page-btn-group">
														<a target="_blank"
														   href="<?php echo esc_url( $home_data['demo_url'] ); ?>"
														   class="crane-import-page-btn crane-import-page-preview-btn"><?php esc_html_e( 'Preview', 'crane' ); ?></a>
														<?php if ( isset( $steps_available_for_import[ $home ] ) ) : ?>
															<button type="button" class="crane-import-page-btn crane-import-page-select-btn">
																<?php esc_html_e( 'Select', 'crane' ); ?>
															</button>
														<?php endif; ?>
													</div>
												</div>
											</div>
										</div>
									<?php } ?>
								</div>
							</div>
						</div>
						<div class="crane-import-page-confirm-block">
							<div class="crane-import-page-confirm-text">
								<p class="crane-import-page-confirm-text--plugins"></p>
								<p class="crane-import-page-confirm-text--notice"><?php esc_html_e( 'The demo data will be downloaded from the grooni.com web server. The following data will be transferred: the address of the web site hosting, the domain name of the site.', 'crane' ); ?></p>
							</div>
              <div class="crane-import__confirm-cancel-group">
                <input type="submit" class="crane-import-page-btn crane-import-page-import-selected" value="<?php esc_html_e( 'Confirm and import','crane' ); ?>">
                <div class="crane-import-page-btn crane-import-page-import-cancel"><?php esc_html_e( 'Cancel', 'crane' ); ?></div>
              </div>
							<input type="hidden" value="1" name="grooni_import_sample_data"/>
							<input type="hidden" value="crane" name="demo"/>
						</div>
					</form>
					<div class="crane-import-progress-bar-container">
						<div class="crane-import-progress-bar-warning">
							<h3><?php esc_html_e( 'Importing Demo Content...', 'crane' ); ?></h3>

							<p><?php esc_html_e( 'Please be patient and do not navigate away from this page while the import is in progress. This can take a while if your server is slow (inexpensive hosting).You will be notified via this page when the import is completed.', 'crane' ); ?></p>

							<div class="crane-import-progress-bar-wrapper">
								<div class="crane-import-progress-bar-inner">
									<span
										class="import-progress-bar-info"><?php esc_html_e( 'Progress', 'crane' ); ?></span>

									<div class="crane-import-progress-bar-bg">
										<div id="import-progress-bar"></div>
									</div>
									<div class="import-progress-bar-percentage">0%</div>
								</div>
							</div>

							<div class="box-body">
								<div id="crane-error-import-msg"></div>
								<span id="crane-import-status"></span>
							</div>

						</div>

					</div>
					<div class="import-result">
						<h3><?php esc_html_e( 'Import completed successfully!', 'crane' ); ?></h3>

						<p><?php echo wp_kses( __( 'Now you can see the result at your', 'crane' ) . ' <a href="/">' . __( 'site', 'crane' ) . '</a> ' . __( 'or start customize via', 'crane' ) . ' <a href="' . admin_url() . 'admin.php?page=crane-theme-options">' . __( 'Theme options', 'crane' ) . '</a>', array( 'a' => array( 'href' => array() ) ) );
							?></p>
					</div>
				</div>
			</div>
			<?php
		}


	} // class


}
