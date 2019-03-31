<?php defined( 'ABSPATH' ) || die( 'This script cannot be accessed directly.' );


/**
 * Class Crane_DebugPage
 */
class Crane_DebugPage {
	/**
	 * Self object instance
	 *
	 * @var null|object
	 */
	private static $instance = null;


	/**
	 * Singleton self instance
	 *
	 * @return Crane_DebugPage
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	private function __clone() {
	}

	private function __construct() {
		add_action( 'crane_inside_debug_page_section', array( $this, 'infoVersions' ), 10 );
	}

	public function createPage() {
		if ( isset( $_GET['page'] ) && 'crane_debug_page' === $_GET['page'] ) {
			add_action( 'admin_menu', array( $this, 'addDebugPage' ), 100 );
		}
	}

	public function addDebugPage() {

		// Add admin subpage.
		add_submenu_page(
			'tools.php',
			__( 'Crane theme debug page', 'crane' ),
			__( 'Crane theme debug page', 'crane' ),
			'edit_theme_options',
			'crane_debug_page',
			array( $this, 'debugPage' )
		);

	}

	public function addSection( $title, $decription, $content ) {
		?>
		<div class="crane-debug-section">
			<?php if ( ! empty( $title ) ): ?>
				<h3 class="crane-debug-section-title"><?php echo sprintf( '%s', $title ); ?></h3>
			<?php endif; ?>
			<?php if ( ! empty( $decription ) ): ?>
				<div class="crane-debug-section-desc"><?php echo sprintf( '%s', $decription ); ?></div>
			<?php endif; ?>
			<?php if ( ! empty( $content ) ): ?>
				<div class="crane-debug-section-content"><?php echo sprintf( '%s', $content ); ?></div>
			<?php endif; ?>
		</div>
		<?php
	}

	public function addList( $list ) {

		$output = '';

		if ( ! is_array( $list ) ) {
			return $output;
		}

		$output = '<div class="crane-debug-section-list">';
		foreach ( $list as $index => $value ) {

			if ( is_array( $value ) ) {
				$value = implode( ' ; ', $value );
			}

			$output .= '<div class="crane-debug-section-list-item crane-elem-index">' . sprintf( '%s', $index ) . '</div>';
			$output .= '<div class="crane-debug-section-list-item crane-elem-value">' . sprintf( '%s', $value ) . '</div>';
		}
		$output .= '</div>';

		return $output;

	}

	public function addListWithActions( $list ) {

		$output = '';

		if ( ! is_array( $list ) ) {
			return $output;
		}

		$output = '<div class="crane-debug-section-list">';
		foreach ( $list as $index => $value ) {

			if ( is_array( $value ) ) {
				$value = implode( ' ; ', $value );
			}

			$output .= '<div class="crane-debug-section-list-item crane-elem-index">' . sprintf( '%s', $index ) . '</div>';
			$output .= '<div class="crane-debug-section-list-item crane-elem-value">' . sprintf( '%s', $value ) . '</div>';
		}
		$output .= '</div>';

		return $output;

	}

	public function infoVersions() {

		global $wp_version;
		global $wp_db_version;

		$versions = array(
			'Current WordPress version'            => $wp_version,
			'Current WordPress DataBase version'   => $wp_db_version,
			'Current Crane theme version'          => CRANE_THEME_VERSION,
			'Current Crane theme DataBase version' => get_option( CRANE_THEME_DB_VER_OPTION ),
		);

		$content = $this->addList( $versions );

		$this->addSection(
			esc_html__( 'Versions info', 'crane' ),
			'',
			$content
		);

	}

	public function debugPage() {
		/**
		 * Fires before the debug page output.
		 *
		 * @since 1.4.6
		 */
		do_action( 'crane_before_debug_page_output' );

		?>

		<div id="crane-debug-page" class="crane-debug-container">
			<div class="crane-debug-body">
				<h2><?php esc_html_e( 'Crane theme debug page', 'crane' ); ?></h2>
				<div class="crane-debug-body_inner">


					<?php

					/**
					 * Fires inside the debug page output.
					 *
					 * @since 1.4.6
					 */
					do_action( 'crane_inside_debug_page_section' );

					?>


					<?php
					$this->addSection(
						esc_html__( 'Support', 'crane' ),
						'',
						sprintf( esc_html__( 'If you encounter migration problems or find any bugs, please create a ticket on our %s', 'crane' ),
							sprintf( '<a href="https://grooni.ticksy.com/" target="_blank">%s</a>', esc_html__( 'Support Portal', 'crane' ) )
						)
					);
					?>


				</div>
			</div>
		</div>


		<?php
		/**
		 * Fires after the debug page output.
		 *
		 * @since 1.4.6
		 */
		do_action( 'crane_after_debug_page_output' );

	}


}
