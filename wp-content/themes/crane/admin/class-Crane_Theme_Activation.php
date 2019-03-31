<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );
/**
 * Theme Activation class.
 *
 * @package crane
 */


if ( ! class_exists( 'Crane_Theme_Activation' ) ) {

	/**
	 * Theme Activation class
	 */
	class Crane_Theme_Activation {
		const OPTION_PURCHASE_NAME = 'grooni-crane-purchase';
		const OPTION_INSTALL_NAME = 'crane_theme_install_and_active';

		public function __construct() {

			add_action( 'after_switch_theme', function () {
				update_user_meta( get_current_user_id(), 'crane-install-addons-dismiss', false );
				update_user_meta( get_current_user_id(), 'crane-upgrade-addons-dismiss', false );
				update_user_meta( get_current_user_id(), 'crane-upgrade-gm-dismiss', false );
				add_action( 'after_switch_theme', '_wp_sidebars_changed', 20 );
				add_action( 'init', 'flush_rewrite_rules', 50 );
				$this->do_activation();
			} );

			$this->theme_preview();

			add_action( 'switch_theme', array( $this, 'do_deactivation' ) );
			add_action( 'upgrader_process_complete', array( $this, 'after_upgrader_process' ) );
			add_action( 'init', array( $this, 'add_default_footer' ) );
		}

		public function theme_preview() {

			$current_theme = wp_get_theme();
			if ( 'crane' === $current_theme->get_template() && ! get_option( self::OPTION_INSTALL_NAME ) ) {

				crane_is_theme_preview( true );

				if ( function_exists( 'grooni_footer_add_post_type' ) ) {
					add_action( 'init', 'grooni_footer_add_post_type' );
					$this->add_default_footer();
				}

				update_option( self::OPTION_INSTALL_NAME, true );
			}

		}

		public function after_upgrader_process( $upgrader = '' ) {

			if ( function_exists( 'grooni_footer_add_post_type' ) ) {
				add_action( 'init', 'grooni_footer_add_post_type' );
				$this->add_default_footer();
			}

			if ( method_exists( $upgrader, 'theme_info' ) ) {

				$theme_info = $upgrader->theme_info();

				if ( isset( $theme_info->stylesheet ) ) {

					if ( 'crane' === $theme_info->stylesheet ) {
						update_option( 'crane_need_custom_css_update', true );
						update_user_meta( get_current_user_id(), 'crane-install-addons-dismiss', false );
						update_user_meta( get_current_user_id(), 'crane-upgrade-addons-dismiss', false );
					}

				}
			}

		}

		public function add_menu() {

			if ( function_exists( 'grooni_add_subpage_to_dashboard' ) ) {
				grooni_add_subpage_to_dashboard(
					'crane-theme-dashboard',
					esc_html__( 'Activate theme', 'crane' ),
					esc_html__( 'Activate theme', 'crane' ),
					'edit_theme_options',
					'crane_activate',
					array( $this, 'page' )
				);
			}

		}

		public function page() {
			$purchase = get_option( self::OPTION_PURCHASE_NAME );

			$theme         = wp_get_theme();
			$theme_version = $theme->get( 'Version' );
			if ( CRANE_THEME_VERSION !== $theme_version ) {
				$theme_version = CRANE_THEME_VERSION . ' [' . esc_html__( 'child version', 'crane' ) . ': ' . $theme_version . ']';
			}
			?>
			<div class="crane-admin-page">
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
				<div class="crane-activation-inner">
					<div class="crane-activation-form-wrapper">
						<form class="crane-activation-form" method="post">
							<i class="fa fa-barcode"></i>
							<?php echo wp_nonce_field(); ?>
							<div class="crane-activation-from-group">
								<label class="crane-activation-label"
								       for="purchase"><?php esc_html_e( 'Please enter your purchase code', 'crane' ); ?></label>
								<input class="crane-activation-pass" type="password" name="purchase" id="purchase"
								       value="<?php echo esc_attr( $purchase ); ?>">
								<input class="crane-activation-save" type="submit" value="ACTIVATE">

								<p class="crane-activation-info">
									<i class="fa fa-question-circle"></i> <?php esc_html_e( 'How to get my', 'crane' ); ?>
									<a href="#"><?php esc_html_e( 'purchase key', 'crane' ); ?></a>
								</p>
							</div>

						</form>
					</div>
				</div>
			</div>
			<?php
		}

		/**
		 * After switching template checks and install the necessary plugins
		 *
		 * @param bool $with_redirect
		 */
		public function do_activation( $with_redirect = true ) {
			update_option( self::OPTION_INSTALL_NAME, true );

			update_option( 'ultimate_theme_support', 'enable' );
			update_option( 'revslider-valid', true );

			get_option( 'layerslider-authorized-site', true );

			update_option( 'crane_need_custom_css_update', true );

			if ( function_exists( 'grooni_footer_add_post_type' ) ) {
				add_action( 'init', 'grooni_footer_add_post_type' );
				$this->add_default_footer();
			}

			if ( $with_redirect ) {
				update_option( 'grooni_do_flush_rewrite_rules', true );
				add_action( 'admin_init', array( $this, 'do_activation_redirect' ), 1 );
			}

		}

		public function do_activation_redirect() {
			if ( current_user_can( 'edit_theme_options' ) && class_exists( 'Grooni_Theme_Addons' ) ) {
				$redirect_to = admin_url( 'admin.php?page=crane-theme-dashboard' );
				wp_redirect( esc_url( $redirect_to ), 302 );
			}
		}

		public function do_deactivation() {
			update_option( self::OPTION_INSTALL_NAME, false );
		}

		/**
		 * Add default footer preset if not exist
		 *
		 * @return bool
		 */
		public function add_default_footer() {

			if ( get_option( 'crane_default_footer_added') ) {
				return;
			}

			global $crane_options;

			$footer_name = empty( $crane_options['footer_preset_global'] ) ? 'basic-footer' : $crane_options['footer_preset_global'];

			if ( ! empty( $footer_name ) ) {
				$args = array(
					'name'        => $footer_name,
					'post_type'   => 'crane_footer',
					'post_status' => 'publish',
					'numberposts' => 1,
				);

				$footer_post = get_posts( $args );
				if ( ! empty( $footer_post ) ) {
					update_option( 'crane_need_custom_css_update', true );
					update_option( 'crane_default_footer_added', true, true );
					return false;
				}
			}

			$footer_content = '<div class="crane-base-footer"><p class="text-center">&copy; Copyright ' . date( "Y" ) . '. All Rights Reserved</p></div>';
			$footer_args    = array(
				'post_type'    => 'crane_footer',
				'post_name'    => 'basic-footer',
				'post_title'   => esc_html__( 'Basic Footer', 'crane' ),
				'post_content' => $footer_content,
				'post_date'    => date( 'Y-m-d H:i', time() ),
				'post_status'  => 'publish',
			);

			$footer_post_id = wp_insert_post( $footer_args );

			$crane_options['footer_preset_global'] = 'basic-footer';
			$this->save_redux_options( $crane_options );

			update_option( 'crane_need_custom_css_update', true );
			update_option( 'crane_default_footer_added', true, true );

		}


		/**
		 * Save options for redux framework
		 *
		 * @param array $_options
		 *
		 * @return null
		 *
		 */
		public function save_redux_options( $_options ) {
			if ( ! class_exists( 'Redux' ) || ! class_exists( 'ReduxFrameworkInstances' ) ) {
				return;
			}

			$redux = ReduxFrameworkInstances::get_instance( 'crane_options' );
			try {
				if ( isset ( $redux->validation_ran ) ) {
					unset ( $redux->validation_ran );
				}

				if ( is_array( $_options ) && isset( $_options['redux-backup'] ) ) {
					unset( $_options['redux-backup'] );
				}

				if ( method_exists( $redux, 'set_options' ) ) {
					$redux->set_options( $_options );
				}

				if ( ! empty( $_options['favicon'] ) && is_array( $_options['favicon'] ) ) {
					$favicon_arr = $_options['favicon'];

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

			} catch ( Exception $e ) {
				$error_message = array( 'status' => $e->getMessage() );
			}

		}


	} // class Crane_Theme_Activation
}

$theme_activation = new Crane_Theme_Activation();
