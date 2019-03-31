<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );
/**
 * Crane theme Sidebar creator page.
 * (Admin area)
 *
 * @package crane
 */


if ( ! class_exists( 'Crane_Sidebars_Creator' ) ) {

	class Crane_Sidebars_Creator {

		public static $sidebar_options;
		public static $option_name = 'crane_sb_creator_sidebars';

		public function __construct() {

			self::$sidebar_options = array();

			add_action( 'widgets_init', array( $this, 'init' ), 99 );

			if ( function_exists( 'wp_doing_ajax' ) && wp_doing_ajax() ) {
				add_action( 'wp_ajax_crane_add_sidebar', array( $this, 'add_sidebar' ) );
				add_action( 'wp_ajax_crane_rename_sidebar', array( $this, 'rename_sidebar' ) );
				add_action( 'wp_ajax_crane_delete_sidebar', array( $this, 'delete_sidebar' ) );
			}

			add_action( 'dynamic_sidebar_before', array( $this, 'dynamic_sidebar_before' ) );
			add_action( 'dynamic_sidebar_after', array( $this, 'dynamic_sidebar_after' ) );

			add_action( 'widgets_admin_page', array( $this, 'add_sidebar_creation_form' ) );
		}

		public static function init() {
			$sidebars = Crane_Sidebars_Creator::get_sidebars();

			if ( is_array( $sidebars ) ) {
				foreach ( $sidebars as $key => $sidebar ) {
					if ( ! empty( $sidebar['custom_sidebar'] ) ) {
						register_sidebar( array(
							'name'          => $sidebar['name'],
							'id'            => $key,
							'class'         => 'crane-custom-sidebar',
							'before_widget' => '<div id="%1$s" class="widget %2$s">',
							'after_widget'  => '</div>',
							'before_title'  => '<h4 class="widget-title">',
							'after_title'   => '</h4>',
						) );
					}
				}
			}
		}

		public function add_sidebar_creation_form() {
			$wp_nonce = wp_create_nonce( 'crane-sb__actions_nonce' );
			$wp_nonce = '<input name="crane-sb__actions_nonce" type="hidden" value="' . $wp_nonce . '" />';

			?>
			<div class="crane-sb__add-new-wrapper">
				<form method="POST" class="crane-sb__custom-sidebar-form">
					<div class="button button-primary crane-sb__add-new"><?php esc_html_e( 'Add a new sidebar', 'crane' ) ?></div>
					<div class="crane-sb__name-wrapper hidden">
						<label for="crane-sb__name"><?php esc_html_e( 'New Sidebar Name', 'crane' ); ?></label>
						<input id="crane-sb__name" class="crane-sb__new-name" name="crane-sb__new-name" type="text" value="">
						<?php echo crane_clear_echo( $wp_nonce ); ?>
						<div id="crane-sb__create-new-btn"
						     class="button button-primary"><?php esc_html_e( 'Create sidebar', 'crane' ) ?></div>
					</div>
				</form>
			</div>
			<?php
		}


		public static function add_sidebar() {
			check_ajax_referer( 'crane-sb__actions_nonce' );

			$sidebars             = Crane_Sidebars_Creator::get_sidebars( false );
			$is_dublicate_sidebar = false;

			$sidebar_name = esc_attr( wp_unslash( trim( $_POST['name'] ) ) );

			if ( ! $sidebar_name ) {
				wp_die( esc_html__( 'Please, add sidebar name.', 'crane' ) );
			}

			$disallow_names = [ 'Basic sidebar', 'Default sidebar', 'Basic Sidebar', 'Default Sidebar' ];
			if ( in_array( $sidebar_name, $disallow_names ) ) {
				wp_die( esc_html__( 'This name reserved. Please, use a different name.', 'crane' ) );
			}

			$id = 'crane-sb_' . crane_uniqid_base36( true );
			if ( isset( $sidebars[ $id ] ) ) {
				$id = 'crane-sb_' . crane_uniqid_base36( true );
			}

			foreach ( $sidebars as $s_id => $s_data ) {
				if ( isset( $s_data['name'] ) && $s_data['name'] === $sidebar_name ) {
					$is_dublicate_sidebar = true;
				}
			}

			if ( isset( $sidebars[ $id ] ) ) {
				$is_dublicate_sidebar = true;
			}

			if ( $is_dublicate_sidebar ) {
				wp_die( esc_html__( 'Sidebar already exists, please use a different name.', 'crane' ) );
			}

			$sidebars[ $id ] = [ 'name' => $sidebar_name, 'class' => $id ];
			Crane_Sidebars_Creator::update_sidebars( $sidebars );

			wp_die( 'ok' );
		}


		public static function rename_sidebar() {
			check_ajax_referer( 'crane-sb__actions_nonce' );

			$sidebars             = Crane_Sidebars_Creator::get_sidebars( false );
			$is_dublicate_sidebar = false;

			$sidebar_name = esc_attr( wp_unslash( trim( $_POST['name'] ) ) );

			if ( ! $sidebar_name ) {
				wp_die( esc_html__( 'Please, add sidebar name.', 'crane' ) );
			}

			$id = esc_attr( wp_unslash( trim( $_POST['sidebar_id'] ) ) );

			if ( ! isset( $sidebars[ $id ] ) ) {
				wp_die( esc_html__( 'Sidebar does not exist.', 'crane' ) );
			}

			foreach ( $sidebars as $s_id => $s_data ) {
				if ( isset( $s_data['name'] ) && $s_data['name'] === $sidebar_name ) {
					$is_dublicate_sidebar = true;
				}
			}

			if ( $is_dublicate_sidebar ) {
				wp_die( esc_html__( 'Sidebar already exists, please use a different name.', 'crane' ) );
			}

			$sidebars[ $id ] = [ 'name' => $sidebar_name, 'class' => $id ];
			Crane_Sidebars_Creator::update_sidebars( $sidebars );

			wp_die( 'ok' );
		}


		public static function delete_sidebar() {
			check_ajax_referer( 'crane-sb__actions_nonce' );

			$sidebars = Crane_Sidebars_Creator::get_sidebars( false );
			$id       = esc_attr( wp_unslash( trim( $_POST['sidebar_id'] ) ) );
			if ( ! isset( $sidebars[ $id ] ) ) {
				wp_die( esc_html__( 'Sidebar does not exist.', 'crane' ) );
			}
			unset( $sidebars[ $id ] );
			Crane_Sidebars_Creator::update_sidebars( $sidebars );

			wp_die( 'ok' );
		}

		/**
		 * called by the action get_sidebar.
		 */
		public static function get_sidebar( $name = '', $type = '' ) {
			if ( $name && is_active_sidebar( $name ) ) {
				self::$sidebar_options[ $name ] = $type;

				dynamic_sidebar( $name );

				unset( self::$sidebar_options[ $name ] );
			}

			return; //don't do anything more
		}


		public static function dynamic_sidebar_before( $index ) {
			// prevent work in admin area
			if ( ! is_admin() ) {

				if ( ! empty( self::$sidebar_options ) && isset( self::$sidebar_options[ $index ] ) && self::$sidebar_options[ $index ] ) {
					switch ( self::$sidebar_options[ $index ] ) {
						case 'aside':
							$html_class = $index;
							$sidebars   = Crane_Sidebars_Creator::get_sidebars();
							if ( is_array( $sidebars ) && ! empty( $sidebars ) && array_key_exists( $index, $sidebars ) ) {
								if ( isset( $sidebars[ $index ]['class'] ) && $sidebars[ $index ]['class'] ) {
									$html_class = $sidebars[ $index ]['class'];
								} else {
									$html_class = $index;
								}
							}

							$current_page_options = crane_get_options_for_current_page();
							$sticky_offset = 15;
							if ( isset( $current_page_options['sticky'] ) && $current_page_options['sticky'] ) {
								$html_class = 'crane-sidebar--set-sticky ' . $html_class;
								if ( isset( $current_page_options['sticky-offset'] ) ) {
									if ( is_array( $current_page_options['sticky-offset'] ) && isset( $current_page_options['sticky-offset']['padding-top'] ) ) {
										$sticky_offset = intval( $current_page_options['sticky-offset']['padding-top'] );
									} elseif ( is_numeric( $current_page_options['sticky-offset'] ) ) {
										$sticky_offset = intval( $current_page_options['sticky-offset'] );
									}
								}
							}

							echo '<aside class="crane-sidebar ' . esc_attr( $html_class ) . '" data-offset="' . esc_attr( $sticky_offset ) . '">';
							echo '<div class="crane-sidebar-inner">';

							break;

						case 'footer' :
							echo '<div class="footer-column">';
							break;
					}
				}

			}
		}

		/**
		 * called by the action get_sidebar.
		 */
		public static function dynamic_sidebar_after( $index ) {
			if ( ! is_admin() ) {

				if ( ! empty( self::$sidebar_options ) && isset( self::$sidebar_options[ $index ] ) && self::$sidebar_options[ $index ] ) {
					switch ( self::$sidebar_options[ $index ] ) {
						case 'aside':
							echo '</div>';
							echo '</aside>';

							break;

						case 'footer' :
							echo '</div>';
							break;
					}
				}

			}
		}

		/**
		 * replaces array of sidebar names
		 *
		 * @param $sidebar_array
		 */
		public static function update_sidebars( $sidebar_array ) {
			update_option( self::$option_name, $sidebar_array );
		}

		/**
		 * gets the generated sidebars
		 */
		public static function get_sidebars( $get_all = true, $add_default = false ) {
			$sidebars = array();

			if ( $add_default ) {
				$sidebars['default'] = array(
					'custom_sidebar' => false,
					'name'           => esc_html__( 'Default sidebar', 'crane' ),
				);
			}

			if ( $get_all ) {
				foreach ( crane_get_crane_sidebars_array() as $id => $sidebar ) {
					$sidebar['custom_sidebar'] = false;
					$sidebars[ $id ]           = $sidebar;
				}
			}

			$custom_sidebars = get_option( self::$option_name );
			if ( $custom_sidebars && is_array( $custom_sidebars ) ) {
				foreach ( $custom_sidebars as $id => $sidebar ) {
					$sidebar['custom_sidebar'] = true;
					$sidebars[ $id ]           = $sidebar;
				}
			}

			return $sidebars;
		}

		public static function text_cleaner( $name ) {
			$class = str_replace( array(
				' ',
				',',
				'.',
				'"',
				"'",
				'/',
				"\\",
				'+',
				'=',
				')',
				'(',
				'*',
				'&',
				'^',
				'%',
				'$',
				'#',
				'@',
				'!',
				'~',
				'`',
				'<',
				'>',
				'?',
				'[',
				']',
				'{',
				'}',
				'|',
				':',
				';',
			), '', $name );

			return $class;
		}

	}
}
$crane_sidebars = new Crane_Sidebars_Creator;

if ( ! function_exists( 'crane_generate_dynamic_sidebar' ) ) {
	/**
	 * @param string $name
	 * @param string $type
	 *
	 * @return bool true
	 */
	function crane_generate_dynamic_sidebar( $name = '', $type = '' ) {
		Crane_Sidebars_Creator::get_sidebar( $name, $type );

		return true;
	}
}
