<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );
/**
 * Theme Migrate support.
 *
 * @package crane
 */


/**
 * Main migration class
 *
 * use next lock options:
 * _site_transient_timeout_ct_migrate_job_process_lock
 * _site_transient_ct_migrate_job_process_lock
 * ct_migrate_job_data_VERSION   where VERSION is current data for migrate version (exp 1.0.5)
 * crane_theme_db_version__report  array['cron_job'] is bool
 *
 */
class Crane_Migration {

	/** @var array DB updates and options that need to be run per version */
	private static $migrate_version_points = array(
		'1.0.1' => [ 'type' => 'ask' ], // merge breadcrumbs-position into breadcrumbs-[post_type] option.
		'1.0.5' => [ 'type' => 'ask' ], // redux options 'portfolio-archive-image_resolution', 'blog-image_resolution'.
		'1.2.8.1345' => [ 'type' => 'ask' ], // redux options 'PAGETYPE-padding-mobile'.
		'1.3.0.1468' => [ 'type' => 'ask' ], // redux options 'privacy-preferences' need same as 'privacy-embeds'.
		'1.3.8.1569' => [ 'type' => 'ask' ], // clean '1' from posts meta
		'1.3.9.1563' => [ 'type' => 'ask' ], // remove GroovyMenu plugin options from TO and META.
	);

	/**
	 * Identifier
	 *
	 * @var mixed
	 * @access protected
	 */
	protected $identifier = 'crane_migrate_job';

	/**
	 * Cron_hook_identifier
	 *
	 * @var mixed
	 * @access protected
	 */
	protected $cron_hook_identifier;

	/**
	 * Cron_interval_identifier
	 *
	 * @var mixed
	 * @access protected
	 */
	protected $cron_interval_identifier;

	public $db_version = '';
	public $migration_type = 'ask';

	public $sucsess_versions = array();


	public function __construct() {

		$this->cron_hook_identifier     = $this->identifier . '_cron';
		$this->cron_interval_identifier = $this->identifier . '_cron_interval';

		// If crane theme don't have "DB version" option
		if ( ! get_option( CRANE_THEME_DB_VER_OPTION ) ) {
			update_option( CRANE_THEME_DB_VER_OPTION, CRANE_THEME_VERSION );

			return null;
		}

		if ( function_exists( 'wp_doing_ajax' ) && wp_doing_ajax() ) {
			//call our function when initiated from JavaScript
			add_action( 'wp_ajax_crane_ajax_start_migrate', array( $this, 'crane_ajax_start_migrate' ) );

			add_action( 'wp_ajax_crane_dismissed_migration_notice_info', array(
				$this,
				'dismissed_migration_notice_info'
			) );

		}

		if ( $this->get_cron_job_marker() ) {
			add_filter( 'cron_schedules', array( $this, 'schedule_cron_add_interval' ) );
			add_action( $this->cron_hook_identifier, array( $this, 'cron_migrate_job' ) );
			add_action( 'init', array( $this, 'schedule_event' ), 100 );
		}

		add_action( 'init', array( $this, 'migrate_theme' ) );

		require_once __DIR__ . '/DebugPage.php';
		if ( class_exists( 'Crane_DebugPage' ) ) {
			Crane_DebugPage::get_instance()->createPage();
		}
		add_action( 'admin_init', array( $this, 'migrate_debug' ) );
	}

	/**
	 * Start migrate debug info page
	 */
	public function migrate_debug() {
		// Load debug data.
		if ( class_exists( 'Crane_DebugPage' ) ) {
			require_once __DIR__ . '/migrate_debug.php';
			new Crane_MigrationDebug( self::$migrate_version_points, $this->identifier );
		}
	}

	/**
	 * Add log migrate data
	 */
	public function add_migrate_debug_log( $log_data = array() ) {
		$migration_log = get_option( CRANE_THEME_DB_VER_OPTION . '__log_' . $this->db_version );
		if ( empty( $migration_log ) || ! is_array( $migration_log ) ) {
			$migration_log = array();
		}

		if ( is_string( $log_data ) ) {
			$log_data = array( $log_data );
		}

		$migration_log[] = array_merge( array( date( "Y-m-d H:i:s" ) ), $log_data );
		update_option( CRANE_THEME_DB_VER_OPTION . '__log_' . $this->db_version, $migration_log, false );
	}

	/**
	 * Called via AJAX. Start migration process
	 */
	public function crane_ajax_start_migrate() {

		if ( ! $this->get_next_queue() ) {
			$this->set_cron_job_marker( false );
			wp_die( json_encode( array(
				'code'    => 0,
				'message' => esc_html__( 'You already have the latest version. No update required.', 'crane' )
			) ) );
		}

		$this->update_dismissed_info( false );

		$this->set_cron_job_marker( true );

		$output = array(
			'message' => '<p><strong>' .
			             esc_html__( 'Crane theme data update:', 'crane' ) . '</strong> ' .
			             esc_html__( 'Updating start in the background job.', 'crane' ) .
			             '</p>',
			'code'    => 1
		);
		wp_die( json_encode( $output ) );
	}

	/**
	 * @param boolean $flag
	 */
	public function set_cron_job_marker( $flag ) {
		$migration_report = get_option( CRANE_THEME_DB_VER_OPTION . '__report' );
		if ( ! is_array( $migration_report ) || empty( $migration_report ) ) {
			$migration_report = array( 'cron_job' => $flag );
		} else {
			$migration_report['cron_job'] = $flag;
		}

		update_option( CRANE_THEME_DB_VER_OPTION . '__report', $migration_report );
	}

	public function get_cron_job_marker() {
		$migration_report = get_option( CRANE_THEME_DB_VER_OPTION . '__report' );

		return isset( $migration_report['cron_job'] ) ? $migration_report['cron_job'] : false;
	}

	/**
	 * Restart the background process if not already running
	 */
	public function cron_migrate_job() {

		if ( $this->is_process_running() ) {
			// Background process already running.
			exit;
		}

		if ( ! $this->get_next_queue() ) {
			// No data to process.
			$this->clear_scheduled_event();
			exit;
		}

		$this->do_migrate_process();

		exit;
	}


	protected function do_migrate_process() {
		$this->lock_process();

		$next_version    = $this->get_next_queue();
		$version_points  = self::$migrate_version_points;
		$migrate_options = isset( $version_points[ $next_version ] ) ? $version_points[ $next_version ] : null;

		if ( isset( $migrate_options['type'] ) && 'ask' === $migrate_options['type'] ) {
			// Callback function must return true on success
			$migration_proccess = $this->start( $next_version );
		}

		$this->unlock_process();

		if ( ! $this->get_next_queue() ) {
			$this->complete();
		}
	}


	/**
	 * Complete.
	 */
	protected function complete() {
		// Unschedule.
		$this->clear_scheduled_event();
	}


	/**
	 * Check jobs and return next migrate job
	 */
	protected function get_next_queue() {

		$db_version = get_option( CRANE_THEME_DB_VER_OPTION );
		$db_report  = get_option( CRANE_THEME_DB_VER_OPTION . '__report' );

		foreach ( self::$migrate_version_points as $version => $migrate_options ) {
			if ( version_compare( $version, $db_version, '>' ) && empty( $db_report[ $version ] ) ) {
				return $version;
			}
		}

		return false;
	}


	/**
	 * Schedule fallback event.
	 */
	public function schedule_event() {
		if ( ! wp_next_scheduled( $this->cron_hook_identifier ) && $this->get_next_queue() ) {
			wp_schedule_event( time(), $this->cron_interval_identifier, $this->cron_hook_identifier );
		}
	}


	/**
	 * Schedule cron new interval
	 *
	 * @access public
	 *
	 * @param mixed $schedules Schedules.
	 *
	 * @return mixed
	 */
	public function schedule_cron_add_interval( $schedules ) {
		$interval = apply_filters( $this->cron_interval_identifier, 1 );

		// Adds every 1 minute to the existing schedules.
		$schedules[ $this->cron_interval_identifier ] = array(
			'interval' => MINUTE_IN_SECONDS * $interval,
			'display'  => sprintf( esc_html__( 'Every %d minute', 'crane' ), $interval ),
		);

		return $schedules;
	}


	/**
	 * Lock process
	 *
	 * Lock the process so that multiple instances can't run simultaneously.
	 * Override if applicable, but the duration should be greater than that
	 * defined in the time_exceeded() method.
	 */
	protected function lock_process() {

		$lock_timer = 60; // 1 min
		$lock_timer = apply_filters( $this->identifier . '_queue_lock_time', $lock_timer );

		set_site_transient( $this->identifier . '_process_lock', time(), $lock_timer );
	}

	/**
	 * Unlock process
	 *
	 * Unlock the process so that other instances can spawn.
	 *
	 * @return $this
	 */
	protected function unlock_process() {
		delete_site_transient( $this->identifier . '_process_lock' );

		return $this;
	}

	/**
	 * Is process running
	 *
	 * Check whether the current process is already running
	 * in a background process.
	 */
	protected function is_process_running() {
		if ( get_site_transient( $this->identifier . '_process_lock' ) ) {
			// Process already running.
			return true;
		}

		return false;
	}


	/**
	 * Clear scheduled event
	 */
	protected function clear_scheduled_event() {
		$timestamp = wp_next_scheduled( $this->cron_hook_identifier );

		if ( $timestamp ) {
			wp_unschedule_event( $timestamp, $this->cron_hook_identifier );
		}

		$this->set_cron_job_marker( false );
		$this->unlock_process();
		$this->update_dismissed_info( false );
	}


	public function migrate_theme() {

		if ( function_exists( 'wp_doing_ajax' ) && ! wp_doing_ajax() && version_compare( CRANE_THEME_VERSION, get_option( CRANE_THEME_DB_VER_OPTION ), '>' ) && get_option( CRANE_THEME_DB_VER_OPTION ) && ! defined( 'CRANE_DOING_MIGRATE_JOB' ) ) {

			$need_notice        = false;
			$migration_proccess = false;

			foreach ( self::$migrate_version_points as $version => $migrate_options ) {

				if ( version_compare( get_option( CRANE_THEME_DB_VER_OPTION ), $version, '<' ) ) {

					switch ( $migrate_options['type'] ) {
						case 'now':

							$migration_report = get_option( CRANE_THEME_DB_VER_OPTION . '__report' );
							if ( ! is_array( $migration_report ) || empty( $migration_report[ $version ] ) || ! $migration_report[ $version ] ) {
								$migration_now = $this->start( $version, $migrate_options['type'] );
							}
							break;

						case 'ask':
							$need_notice = true;
							break;

					}

				}

			}

			if ( $migration_proccess ) {
				$this->show_success_notice();
			}

			if ( $need_notice && $this->get_next_queue() && ( ! defined( 'CRANE_DOING_MIGRATE_JOB' ) || ! CRANE_DOING_MIGRATE_JOB ) ) {
				$this->show_notice();
			}

		}

	}

	/**
	 * @param string $version
	 *
	 * @return mixed null|boolean
	 */
	public function start( $version, $type = 'ask' ) {
		if ( empty( $version ) || ! is_string( $version ) ) {
			return null;
		}

		$this->migration_type = $type;
		$this->db_version     = $version;
		$version_str          = str_replace( '.', '_', $this->db_version );

		global $wp_filesystem;
		if ( empty( $wp_filesystem ) ) {
			if ( file_exists( ABSPATH . '/wp-admin/includes/file.php' ) ) {
				require_once ABSPATH . '/wp-admin/includes/file.php';
				WP_Filesystem();
			}
		}
		if ( empty( $wp_filesystem ) ) {
			return null;
		}

		$migration_file = trailingslashit( get_template_directory() ) . 'admin/migration/migrate__v' . $version_str . '.php';
		// check for existence
		if ( $wp_filesystem->exists( $migration_file ) ) {
			include_once $migration_file;
			$class_name = 'Crane_Migrate__v' . $version_str;
			if ( ! class_exists( $class_name ) ) {
				return null;
			}

			$this->add_migrate_debug_log( sprintf( esc_html__( 'Start migration DB version: %s', 'crane' ), $version ) );

			$instance = new $class_name();

			$this->custom_iniset();

			if ( ! defined( 'CRANE_DOING_MIGRATE_JOB' ) ) {
				define( 'CRANE_DOING_MIGRATE_JOB', true );
			}

			$result = $instance->migrate();
			if ( $result ) {
				$this->sucsess_versions[] = $this->db_version;
			}

			return $result;
		}

		return null;
	}

	/**
	 * Set max PHP server params for migrate process
	 */
	public function custom_iniset() {
		set_time_limit( 1800 ); // 30 min
	}


	public function show_notice() {

		if ( $this->is_process_running() || $this->get_cron_job_marker() ) {

			if ( ! $this->get_dismissed_info() ) {
				add_action( 'admin_notices', [ $this, 'process_notice' ], 50, 1 );
			}

		} else {
			add_action( 'admin_notices', [ $this, 'needed_notice' ], 50 );
		}

	}


	public function needed_notice() {
		$output_escaped_html = '<div class="notice notice-warning crane-theme-migrate__notice-wrapper"><p><strong>' .
		                       esc_html__( 'Crane theme data update:', 'crane' ) . '</strong> ' .
		                       esc_html__( 'We need to update your theme database to the latest version of template.', 'crane' ) .
		                       '<br>' .
		                       '<button class="button crane-theme-migrate__button">' . esc_html__( 'Update theme DB Data', 'crane' ) . '</button>' .
		                       '</p></div>';

		echo crane_clear_echo( $output_escaped_html );

	}


	public function process_notice() {
		$output_escaped_html = '<div class="notice notice-info is-dismissible crane-theme-migrate__notice-info"><p><strong>' .
		                       esc_html__( 'Crane theme data update:', 'crane' ) . '</strong> ' .
		                       esc_html__( 'Updating still in the background job.', 'crane' ) .
		                       '</p></div>';

		echo crane_clear_echo( $output_escaped_html );

	}

	/**
	 * AJAX handler to store the state of dismissible notices.
	 */
	function dismissed_migration_notice_info() {
		$this->update_dismissed_info( true );
	}

	/**
	 * @param bool $flag
	 */
	public function update_dismissed_info( $flag ) {

		if ( ! is_bool( $flag ) ) {
			return;
		}

		$migration_report = get_option( CRANE_THEME_DB_VER_OPTION . '__report' );
		if ( empty( $migration_report ) || ! is_array( $migration_report ) ) {
			$migration_report = array();
		}
		$migration_report['dismissed_info'] = $flag;

		update_option( CRANE_THEME_DB_VER_OPTION . '__report', $migration_report );

	}

	public function get_dismissed_info() {

		$migration_report = get_option( CRANE_THEME_DB_VER_OPTION . '__report' );
		if ( empty( $migration_report ) || ! is_array( $migration_report ) || ! isset( $migration_report['dismissed_info'] ) ) {
			return false;
		}

		return $migration_report['dismissed_info'];

	}


	public function success() {

		if ( 'now' === $this->migration_type ) {
			$this->migration_type = 'ask';
			$this->add_migrate_debug_log( sprintf( esc_html__( 'Automatic background migration: compleate. Version: %s', 'crane' ), $this->db_version ) );
		}

		$this->update_db_version( $this->db_version );
		$this->do_migrate_process();

	}

	public function show_success_notice() {
		add_action( 'admin_notices', [ $this, 'success_notice' ], 50, 1 );
	}


	public function success_notice() {
		$output_escaped_html = '<div class="notice notice-success"><h4><strong>' .
		                       esc_html__( 'Crane theme data update:', 'crane' ) . '</strong> ' .
		                       '</h4><p>' .
		                       sprintf( esc_html__( 'Update DB version %s compleate.', 'crane' ), implode( ' &amp; ', $this->sucsess_versions ) ) .
		                       '</p></div>';

		echo crane_clear_echo( $output_escaped_html );

	}

	/**
	 * @param string $version migrate DB version
	 */
	public function update_db_version( $version = '' ) {
		$version = $version ? : $this->db_version;

		if ( $version ) {

			$this->update_db_version__report( $version );

			update_option( CRANE_THEME_DB_VER_OPTION, $version );

			$this->add_migrate_debug_log( sprintf( esc_html__( 'Migrate COMPLETE. New DB version: %s', 'crane' ), $version ) );

		} else {
			$this->add_migrate_debug_log( esc_html__( 'ERROR. Migration complete, but DB version is not set!', 'crane' ) );
		}
	}

	/**
	 * @param string $version migrate DB version
	 */
	public function update_db_version__report( $version ) {

		if ( empty( $version ) ) {
			return;
		}

		$migration_report = get_option( CRANE_THEME_DB_VER_OPTION . '__report' );
		if ( empty( $migration_report ) || ! is_array( $migration_report ) ) {
			$migration_report = array();
		}
		$migration_report[ $version ] = 'done';

		update_option( CRANE_THEME_DB_VER_OPTION . '__report', $migration_report );

	}


} // Crane_Migration

new Crane_Migration();
