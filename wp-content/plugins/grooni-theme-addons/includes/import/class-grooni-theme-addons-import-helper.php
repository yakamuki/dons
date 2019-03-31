<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Import library
 *
 * @link       http://grooni.com
 * @since      1.1.19
 *
 * @package    Grooni_Theme_Addons
 * @subpackage Grooni_Theme_Addons/includes
 */

if ( ! defined( 'GROONI_IMPORT_DEBUG' ) ) {
	/** Display verbose errors */
	define( 'GROONI_IMPORT_DEBUG', false );
}

// Load Importer API
require_once( ABSPATH . 'wp-admin/includes/import.php' );

if ( ! class_exists( 'WP_Importer' ) ) {
	$class_wp_importer = ABSPATH . 'wp-admin/includes/class-wp-importer.php';
	if ( file_exists( $class_wp_importer ) ) {
		require_once( $class_wp_importer );
	}
}

require_once dirname( __FILE__ ) . '/class-grooni-theme-groovy-menu-helper.php';
require_once dirname( __FILE__ ) . '/class-grooni-theme-wie-helper.php';
require_once dirname( __FILE__ ) . '/class-grooni-theme-logger.php';
require_once dirname( __FILE__ ) . '/class-grooni-theme-logger-cli.php';
require_once dirname( __FILE__ ) . '/class-grooni-theme-upgrader-skin.php';
require_once dirname( __FILE__ ) . '/class-grooni-theme-wxr-import-info.php';


class Grooni_Theme_Addons_Import_Helper extends WP_Importer {

	/**
	 * Regular expression for checking if a post references an attachment
	 *
	 * Note: This is a quick, weak check just to exclude text-only posts. More
	 * vigorous checking is done later to verify.
	 */

	const IMPORT_STATUS_OPTION_NAME = 'grooni_addons_import_status_data';


	/**
	 * Maximum supported WXR version
	 */
	const MAX_WXR_VERSION = 1.2;

	/**
	 * Version of WXR we're importing.
	 *
	 * Defaults to 1.0 for compatibility. Typically overridden by a
	 * `<wp:wxr_version>` tag at the start of the file.
	 *
	 * @var string
	 */
	protected $version = '1.0';


	public $demo = '';
	public $demo_version = '';
	public $authors = array();
	public $imageCount = 0;
	public $totalImages = 0;

	protected $url_remap = array();
	protected $featured_images = array();

	// information to import from WXR file
	protected $categories = array();
	protected $tags = array();
	protected $base_url = '';

	protected $mapping = array();
	protected $requires_remapping = array();
	protected $exists = array();
	protected $user_slug_override = array();

	protected $import_option_flag_name = '';
	protected $import_option_flag = array();

	protected $presets_info_option_name = 'grooni_presets_info_data';

	/**
	 * Logger instance.
	 *
	 * @var Grooni_Theme_WP_Importer_Logger
	 */
	protected $logger;

	/**
	 * Constructor
	 *
	 * @param array $options {
	 *
	 * @var bool $prefill_existing_posts Should we prefill `post_exists` calls? (True prefills and uses more
	 *      memory, false checks once per imported post and takes longer. Default is true.)
	 * @var bool $prefill_existing_comments Should we prefill `comment_exists` calls? (True prefills and uses more
	 *      memory, false checks once per imported comment and takes longer. Default is true.)
	 * @var bool $prefill_existing_terms Should we prefill `term_exists` calls? (True prefills and uses more
	 *      memory, false checks once per imported term and takes longer. Default is true.)
	 * @var bool $update_attachment_guids Should attachment GUIDs be updated to the new URL? (True updates the
	 *      GUID, which keeps compatibility with v1, false doesn't update, and allows deduplication and reimporting.
	 *      Default is false.)
	 * @var bool $fetch_attachments Fetch attachments from the remote server. (True fetches and creates
	 *      attachment posts, false skips attachments. Default is false.)
	 * @var bool $aggressive_url_search Should we search/replace for URLs aggressively? (True searches all
	 *      posts' content for old URLs and replaces, false checks for `<img class="wp-image-*">` only. Default is
	 *      false.)
	 * @var int $default_author User ID to use if author is missing or invalid. (Default is null, which
	 *      leaves posts unassigned.)
	 * }
	 */
	public function __construct( $options = array() ) {
		// Initialize some important variables
		$empty_types = array(
			'post'    => array(),
			'comment' => array(),
			'term'    => array(),
			'user'    => array(),
		);

		$additional_data = apply_filters( 'grooni_addons_import_additional_data', array() );

		$this->mapping              = $empty_types;
		$this->mapping['user_slug'] = array();
		$this->mapping['term_id']   = array();
		$this->requires_remapping   = $empty_types;
		$this->exists               = $empty_types;


		$this->options = wp_parse_args( $options, array(
			'prefill_existing_posts'    => true,
			'prefill_existing_comments' => true,
			'prefill_existing_terms'    => true,
			'update_attachment_guids'   => true,
			'fetch_attachments'         => true,
			'aggressive_url_search'     => false,
			'default_author'            => null,
			'import_all_demo_data'      => true,
			'generate_thumb'            => apply_filters( 'grooni_addons_import_generate_thumb', '__return_false' ),
			'plugins'                   => isset( $additional_data['plugins'] ) ? $additional_data['plugins'] : array(),
			'fonts'                     => isset( $additional_data['fonts'] ) ? $additional_data['fonts'] : array(),
			'pages_home'                => isset( $additional_data['pages_home'] ) ? $additional_data['pages_home'] : array(),
			'menus_home'                => isset( $additional_data['menus_home'] ) ? $additional_data['menus_home'] : array(),
			'necessary_plugins'         => isset( $additional_data['necessary_plugins'] ) ? $additional_data['necessary_plugins'] : array(),
		) );

		$this->demo = isset( $_POST['demo'] ) ? $_POST['demo'] : '';
		if ( empty( $this->demo ) ) {
			$this->demo = GROONI_THEME_ADDONS_CURRENT_THEME_SLUG;
		}

		$this->demo_version = isset( $_POST['demo_version'] ) ? $_POST['demo_version'] : '';
		if ( empty( $this->demo_version ) ) {
			$this->demo_version = GROONI_THEME_ADDONS_CURRENT_THEME_VERSION;
		}

		$this->import_option_flag_name = apply_filters( 'grooni_addons_import_option_name', 'grooni_imported_flags' );
		$this->get_import_option_flag();

	}


	public function set_logger( $logger ) {
		$this->logger = $logger;
	}


	public function get_import_option_flag() {

		$this->import_option_flag = get_option( $this->import_option_flag_name, true );
		if ( ! $this->import_option_flag || ! is_array( $this->import_option_flag ) ) {
			$this->import_option_flag = [ 'theme_version' => GROONI_THEME_ADDONS_CURRENT_THEME_VERSION ];
		}

	}


	public function update_import_option_flag( $flag, $flag_param = true ) {

		if ( empty( $flag ) ) {
			return;
		}

		$_flags = $this->import_option_flag;

		if ( is_string( $flag ) ) {
			$_flags[ $flag ] = $flag_param;
		} elseif ( is_array( $flag ) ) {
			foreach ( $flag as $_flag_name => $_flag_value ) {
				$_flags[ $_flag_name ] = $_flag_value;
			}
		}

		$this->import_option_flag = $_flags;

		update_option( $this->import_option_flag_name, $this->import_option_flag, false );

	}


	public function check_import_option_flag( $flag ) {

		$is_flag_exist = false;

		if ( empty( $flag ) ) {
			return false;
		}

		$_flags = $this->import_option_flag;

		if ( is_string( $flag ) ) {
			if ( isset( $_flags[ $flag ] ) && $_flags[ $flag ] ) {
				$is_flag_exist = true;
			}
		} elseif ( is_array( $flag ) ) {
			foreach ( $flag as $_flag_name => $_flag_value ) {
				if ( isset( $_flags[ $_flag_name ] ) && $_flags[ $_flag_name ] ) {
					$is_flag_exist = true;
				}

			}
		}

		return $is_flag_exist;

	}


	public function import_xml_file( $file_name = 'content', $exclude_ids = array() ) {

		if ( $this->check_import_option_flag( str_replace( '/', '--', $file_name ) ) ) {
			$this->store_import_info( 'Skip import file' . ' ' . $file_name );

			return;
		}

		if ( ! is_file( $this->get_assets_data( 'file', $file_name ) ) ) {
			return;
		}

		$this->store_import_info( 'Import file' . ' ' . $file_name );

		$info              = $this->get_preliminary_information( $this->get_assets_data( 'file', $file_name ) );
		$this->totalImages = $info->media_count;
		$this->authors     = $info->users;

		$this->set_user_mapping();

		$this->import( $this->get_assets_data( 'file', $file_name ), $file_name, $exclude_ids );

		// Store import info for prevent double import
		$this->update_import_option_flag( str_replace( '/', '--', $file_name ) );

	}


	public function import_all_after() {

		$this->import_fonts();

		$this->import_page_options();

		$this->import_options();

	}


	public function import_options() {

		$options = apply_filters( 'grooni_addons_import_additional_options', array() );

		foreach ( $options as $opt_name => $opt_params ) {

			$current_opt_value = get_option( $opt_name, 0 );

			if ( 'integer' === $opt_params['type'] ) {
				$current_opt_value = intval( $current_opt_value );
			}


			if ( empty( $current_opt_value ) || $opt_params['rewrite'] ) {
				update_option( $opt_name, $opt_params['value'] );
			}

		}

	}


	/**
	 * Stere status string
	 *
	 * @param string $output_string output status string
	 *
	 * @param string $progress_bar
	 *
	 * @return void
	 */
	public function store_import_info( $output_string = '', $progress_bar = '', $status = 'success' ) {

		// Security check
		if ( ! defined( 'WP_LOAD_IMPORTERS' ) ) {
			return;
		}

		$data = array(
			'message'  => $output_string,
			'progress' => $progress_bar,
			'status'   => $status
		);

		$all_data = get_option( self::IMPORT_STATUS_OPTION_NAME );

		if ( empty( $all_data ) || ! is_array( $all_data ) || isset( $all_data['closed'] ) ) {
			$all_data = array();
		}

		$key = date( 'Y-m-j_h:i:s' ) . '#' . ( count( $all_data ) + 1 );
		if ( 'stop' == $status ) {
			$key = 'closed';
		}

		// assign
		$all_data[ $key ] = $data;

		if ( 'critical_error' == $status || '500' == $status ) {
			$all_data['closed'] = array(
				'message'  => esc_html__( 'End import process', 'grooni-theme-addons' ),
				'progress' => '',
				'status'   => 'stop'
			);
		}


		update_option( self::IMPORT_STATUS_OPTION_NAME, $all_data, false );


		return;
	}

	/**
	 * Store info message with fail status
	 *
	 * @param string $output_string output JS string
	 *
	 * @return void
	 */
	public function store_import_info_error( $output_string ) {

		$this->store_import_info( $output_string, '', 'fail' );

		return;
	}


	public function check_limits() {
		@ini_set( 'max_execution_time', 2400 );
		@ini_set( 'output_buffering', 'on' );
		@ini_set( 'zlib.output_compression', 0 );
		@ini_set( 'memory_limit', apply_filters( 'admin_memory_limit', WP_MAX_MEMORY_LIMIT ) );
	}


	public function get_and_activate_plugins( $procces_steps = array(), $is_preset = false ) {
		// Import wp contents
		set_time_limit( 0 );

		$this->prepare_plugins( $procces_steps, $is_preset );

		$this->after_prepare_plugins( $is_preset );
	}


	public function get_assets_data( $what, $file_name = 'content' ) {

		$_cpath = ABSPATH . 'wp-content/uploads/';

		$_tmppath = $_cpath . 'grooni-demo-presets/';

		$_x_path = $_tmppath . $this->demo . '/';

		switch ( $what ) {

			case 'file':
				return $_x_path . 'DUMP/' . $file_name . '.xml';
				break;

			case 'path':
				return $_x_path;
				break;

			case 'tmp_path':
				return $_tmppath;
				break;

			case 'content_path':
				return $_cpath;
				break;

		}

		return '';
	}


	/**
	 * Read export files
	 *
	 * @param string $file_name
	 *
	 * @param string $file_ext
	 *
	 * @param bool $unserialize
	 *
	 * @return mixed
	 */
	function get_data( $file_name, $file_ext = 'json', $unserialize = false ) {

		$file = $this->get_assets_data( 'path' ) . 'DUMP/' . $file_name . '.' . $file_ext;

		// Check file if exist.
		if ( ! file_exists( $file ) ) {
			return '';
		}

		// Init WP FileSystem
		if ( ! defined( 'FS_METHOD' ) ) {
			define( 'FS_METHOD', 'direct' );
		}

		global $wp_filesystem;
		if ( empty( $wp_filesystem ) ) {
			if ( file_exists( ABSPATH . '/wp-admin/includes/file.php' ) ) {
				require_once ABSPATH . '/wp-admin/includes/file.php';
				WP_Filesystem();
			}
		}
		if ( empty( $wp_filesystem ) ) {
			return '';
		}

		// Get Content from file
		$file_content = $wp_filesystem->get_contents( $file );


		return $unserialize ? @unserialize( $file_content ) : $file_content;

	}

	/**
	 * Read export files
	 *
	 * @param string $preset_name
	 *
	 * @param string $file_ext
	 *
	 * @param bool $unserialize
	 *
	 * @return mixed
	 */
	function get_preset_data( $preset_name, $file_name = 'preset_data.txt', $unserialize = true ) {

		if ( empty( $preset_name ) ) {
			return array();
		}

		$presets_data = array();
		$file         = $this->get_assets_data( 'tmp_path' ) . $preset_name . '/' . $file_name;

		// Check file if exist.
		if ( ! file_exists( $file ) ) {
			$this->store_import_info_error( 'Error: preset ' . $preset_name . ' data file "' . $file_name . '" not exists' );

			return array();
		}

		// Init WP FileSystem
		if ( ! defined( 'FS_METHOD' ) ) {
			define( 'FS_METHOD', 'direct' );
		}

		global $wp_filesystem;
		if ( empty( $wp_filesystem ) ) {
			if ( file_exists( ABSPATH . '/wp-admin/includes/file.php' ) ) {
				require_once ABSPATH . '/wp-admin/includes/file.php';
				WP_Filesystem();
			}
		}
		if ( empty( $wp_filesystem ) ) {
			return array();
		}

		// Get Content from file
		$file_content = $wp_filesystem->get_contents( $file );

		$presets_data = $unserialize ? @unserialize( $file_content ) : $file_content;

		return $presets_data;

	}


	/**
	 * Import theme options (Redux Framework)
	 *
	 */
	function import_theme_options() {

		if ( $this->check_import_option_flag( $this->demo . '_options' ) ) {
			$this->store_import_info( 'Skip import theme options' );

			return;
		}

		$redux = ReduxFrameworkInstances::get_instance( $this->demo . '_options' );
		try {
			if ( isset ( $redux->validation_ran ) ) {
				unset ( $redux->validation_ran );
			}

			$_options = json_decode( $this->get_data( 'redux', 'json', false ), true );

			if ( is_array( $_options ) && isset( $_options['redux-backup'] ) ) {
				unset( $_options['redux-backup'] );
			}

			$redux->set_options( $_options );


			if ( ! empty( $_options['favicon'] ) && is_array( $_options['favicon'] ) ) {
				$favicon_arr = $_options['favicon'];

				if ( ! empty( $favicon_arr['id'] ) ) {
					$image_full  = wp_get_attachment_image_src( $favicon_arr['id'], 'full' );
					$image_thumb = wp_get_attachment_image_src( $favicon_arr['id'], 'thumbnail' );
				} else {
					$image_full  = [ '', '', '' ];
					$image_thumb = [ '', '', '' ];
				}

				Redux::setOption( $this->demo . '_options', 'favicon', [
					'url'       => isset( $image_full[0] ) ? $image_full[0] : '',
					'id'        => $favicon_arr['id'],
					'height'    => isset( $image_full[2] ) ? strval( $image_full[2] ) : '',
					'width'     => isset( $image_full[1] ) ? strval( $image_full[1] ) : '',
					'thumbnail' => isset( $image_thumb[0] ) ? $image_thumb[0] : '',
				] );

				update_option( 'site_icon', $favicon_arr['id'] );
			}


			// Store import info for prevent double import
			$this->update_import_option_flag( $this->demo . '_options' );

			$output_message = 'Theme options Imported';

		} catch ( Exception $e ) {

			$output_message = 'Theme options Import error.' . ' ' . $e->getMessage();

		}


		$this->store_import_info( $output_message );

	}


	/**
	 * Before import content
	 *
	 */
	function before_import() {

		if ( ! defined( 'WP_LOAD_IMPORTERS' ) ) {
			define( 'WP_LOAD_IMPORTERS', true );
		}

		return;
	}


	/**
	 * Import meta data for taxonomies
	 *
	 * @param string $type
	 * @param array $import_taxonomy_meta_keys
	 */
	function import_taxonomy_meta( $type = '', $import_taxonomy_meta_keys = array() ) {

		if ( empty( $import_taxonomy_meta_keys ) ) {
			$import_taxonomy_meta_keys = apply_filters( 'grooni_addons_import_taxonomy_meta_keys', array() );
		}

		if ( empty( $import_taxonomy_meta_keys ) ) {
			return;
		}

		$taxonomy_meta_options_name = apply_filters( 'grooni_addons_import_taxonomy_meta_options_name', 'grooni_term_additional_meta' );

		if ( ! empty( $type ) ) {
			$taxonomy_keys =
				( isset( $import_taxonomy_meta_keys[ $type ] ) && is_array( $import_taxonomy_meta_keys[ $type ] ) )
					?
					$import_taxonomy_meta_keys[ $type ]
					:
					array();
		} else {
			$taxonomy_keys = array();
			foreach ( $import_taxonomy_meta_keys as $tax_type => $tax_data ) {
				if ( is_array( $tax_data ) ) {
					foreach ( $import_taxonomy_meta_keys as $tax_s => $tax_f ) {
						$taxonomy_keys[ $tax_s ] = $tax_f;
					}
				}
			}
		}


		if ( ! empty( $taxonomy_keys ) ) {
			foreach ( $taxonomy_keys as $tax_slug => $filename ) {

				if ( ! $this->check_import_option_flag( $tax_slug . '_taxonomy_meta' ) ) {

					$this->store_import_info( 'Add meta of Taxonomy' . ' ' . $tax_slug );

					$_custom_taxonomy_options = json_decode( $this->get_data( $filename, 'json', false ), true );

					if ( $_custom_taxonomy_options ) {
						foreach ( $_custom_taxonomy_options as $term_slug => $term_opt ) {
							$term_obj = get_term_by( 'slug', $term_slug, $tax_slug );
							if ( isset( $term_obj->term_id ) && $term_obj->term_id ) {

								update_term_meta( $term_obj->term_id, $taxonomy_meta_options_name, $term_opt );

								$this->update_import_option_flag( $tax_slug . '_taxonomy_meta' );

							}
						}
					}

				}

			}

		}

	}


	/**
	 * Import Fonts
	 *
	 */
	function import_fonts() {

		if ( empty( $this->options['fonts'] ) || empty( $this->options['fonts']['wp-Ingenicons'] ) ) {
			$this->store_import_info( 'Skip import font. Empty config params.' );

			return;
		}

		$this->import_groovy_menu_fonts();

		$this->import_fonts_AIO_Icon_Manager( $this->options['fonts']['wp-Ingenicons']['id'] );

	}


	/**
	 * Import Fonts
	 *
	 */
	public function import_fonts_AIO_Icon_Manager( $asset_font_id, $font_name = 'wp-Ingenicons' ) {


		// Wp-Ingenicons for Ultimate Addons
		if ( class_exists( 'AIO_Icon_Manager' ) ) {

			if ( $this->check_import_option_flag( 'AIO_Icon_Manager' . '__' . $font_name ) ) {
				return;
			}

			$this->store_import_info( 'Import data for AIO_Icon_Manager' );

			$ua_font_manager = new AIO_Icon_Manager;

			$ua_font_manager->delete_folder( $ua_font_manager->paths['fontdir'] . '/wp-Ingenicons' );

			//get the file path of the zip file
			$path = realpath( get_attached_file( $asset_font_id ) );

			if ( $path ) {
				$unzipped = $ua_font_manager->zip_flatten( $path, array(
					'\.eot',
					'\.svg',
					'\.ttf',
					'\.woff',
					'\.json',
					'\.css'
				) );
				// if we were able to unzip the file and save it to our temp folder extract the svg file
				if ( $unzipped ) {
					$ua_font_manager->create_config();
				}
				//if we got no name for the font then don't add it and delete the temp folder
				if ( $ua_font_manager->font_name == 'unknown' ) {
					$ua_font_manager->delete_folder( $ua_font_manager->paths['tempdir'] );
				}

				$this->update_import_option_flag( 'AIO_Icon_Manager' . '__' . $font_name );

				$this->store_import_info( 'AIO_Icon_Manager: Additional font: ' . $font_name . ' - installed' );

			}

		}

	}


	/**
	 * Import Groovy Menu plugin settings and presets
	 *
	 */
	function import_groovy_menu() {

		if ( ! class_exists( 'Grooni_Theme_Addons_Groovy_Menu_Helper' ) ) {
			$this->store_import_info( 'Not found class Grooni_Theme_Addons_Groovy_Menu_Helper' );

			return;
		}

		$gm_import = new Grooni_Theme_Addons_Groovy_Menu_Helper();

		if ( ! $this->check_import_option_flag( 'groovy_menu_setting' ) && $gm_import->groovy_menu_import_settings( $this->get_data( 'groovy_menu_settings', 'json', false ) ) === true ) {
			$this->update_import_option_flag( 'groovy_menu_setting' );

			$this->store_import_info( 'Groovy Menu plugin Settings Imported' );
		}

		if ( ! $this->check_import_option_flag( 'groovy_menu' ) ) {

			$this->import_xml_file( 'groovy_menu_preset' );

			$this->update_import_option_flag( 'groovy_menu' );

			$this->store_import_info( 'Groovy Menu plugin presets Imported' );
		}

	}


	/**
	 * Import Groovy Menu plugin preset
	 *
	 */
	function preset_import_data__groovy_menu( $plugin_data ) {

		if ( empty( $plugin_data ) || ! is_array( $plugin_data ) ) {
			return false;
		}

		if ( empty( $plugin_data['plugin_data']['presets'] ) ) {
			return false;
		}

		$import_presets =
			is_array( $plugin_data['plugin_data']['presets'] )
				?
				$plugin_data['plugin_data']['presets']
				:
				array( $plugin_data['plugin_data']['presets'] );

		if ( ! class_exists( 'Grooni_Theme_Addons_Groovy_Menu_Helper' ) ) {
			$this->store_import_info( 'Not found class Grooni_Theme_Addons_Groovy_Menu_Helper' );

			return false;
		}

		$gm_import = new Grooni_Theme_Addons_Groovy_Menu_Helper();

		foreach ( $import_presets as $key => $gm_preset_data ) {

			if ( $gm_import->groovy_menu_import_one_preset( $gm_preset_data ) === true ) {
				$this->store_import_info( 'Groovy Menu plugin preset ID ' . $gm_preset_data['id'] . ' Imported' );
			}

		}

		return true;


	}

	/**
	 * Import Groovy Menu plugin fonts
	 *
	 */
	function import_groovy_menu_fonts() {

		if ( $this->check_import_option_flag( 'groovy_menu_fonts' ) ) {
			return;
		}

		if ( ! class_exists( 'Grooni_Theme_Addons_Groovy_Menu_Helper' ) ) {
			return;
		}

		$gm_import = new Grooni_Theme_Addons_Groovy_Menu_Helper();
		$gm_import->groovy_menu_import_fonts( $this->options['fonts'] );


		$this->update_import_option_flag( 'groovy_menu_fonts' );

		$this->store_import_info( 'Groovy Menu plugin fonts Imported' );

	}

	/**
	 * Import plugins
	 *
	 * @param array $procces_steps
	 * @param bool $is_preset
	 */
	function prepare_plugins( $procces_steps = array(), $is_preset = false ) {

		if ( empty( $this->options['plugins'] ) ) {
			return;
		}

		$necessary_plugins = $this->options['necessary_plugins'];

		if ( $is_preset ) {

			$presets_info = $this->get_preset_info_data();

			$necessary_plugins = array();

			if ( ! empty( $presets_info ) ) {
				foreach ( $presets_info as $preset_name => $preset ) {
					if ( ! empty( $preset['plugins'] ) ) {
						foreach ( $preset['plugins'] as $need_plugin ) {
							$necessary_plugins[ $preset_name ][ $need_plugin ] = true;
						}
					}
				}
			}

		} // $is_preset === true


		$allowed_plugins = array();

		if ( is_array( $procces_steps ) ) {
			foreach ( $procces_steps as $step ) {
				if ( ! empty( $necessary_plugins[ $step ] ) ) {
					foreach ( $necessary_plugins[ $step ] as $plugin => $allow ) {
						if ( $allow ) {
							$allowed_plugins[ $plugin ] = $plugin;
						}
					}
				}
			}
		}

		foreach ( $this->options['plugins'] as $plugin_slug => $plugin_data ) {

			if ( ! $is_preset && isset( $plugin_data['import_install_exclude'] ) && $plugin_data['import_install_exclude'] ) {
				// ... do nothing
			} elseif ( ! empty( $allowed_plugins ) && ! in_array( $plugin_slug, $allowed_plugins ) ) {
				// ... do nothing
			} else {

				switch ( $plugin_slug ) {
					case 'woocommerce':
						update_option( 'woocommerce_admin_notices', array() );
						update_option( 'woocommerce_default_catalog_orderby', 'menu_order' );
						break;

					default:
						break;
				}

				$this->store_import_info( 'Starting Plugin' . ' ' . $plugin_slug );

				$this->start_necessary_plugin( $plugin_slug, $plugin_data['installed_path'] );

			}
		}

	}


	/**
	 * Activate plugin
	 *
	 * @param $plugin_slug
	 * @param $plugin_path
	 */
	function start_necessary_plugin( $plugin_slug, $plugin_path, $source = '', $is_upgrade = false ) {


		if ( empty( $source ) ) {
			$source = 'http://updates.grooni.com/?action=download&slug=' . $plugin_slug;
		}


		if ( class_exists( 'TGMPA_Bulk_Installer' ) && class_exists( 'TGMPA_Bulk_Installer_Skin' ) ) {

			if ( ! is_dir( WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . $plugin_slug ) ) {
				$install_type = 'install';
			} elseif ( $is_upgrade ) {
				$install_type = 'upgrade';
			}

			// Create a new instance of TGMPA_Bulk_Installer.
			$tgmpa_installer = new TGMPA_Bulk_Installer(
				new TGMPA_Bulk_Installer_Skin(
					array(
						'url'          => '',
						'nonce'        => 'bulk-plugins',
						'names'        => array( $plugin_slug ),
						'install_type' => esc_attr( $install_type ),
					)
				)
			);

			if ( 'install' === $install_type ) {
				$tgmpa_installer->install(
					$source,
					[ 'clear_update_cache' => true ]
				);
			} elseif ( 'upgrade' === $install_type ) {
				$tgmpa_installer->upgrade(
					$plugin_path,
					[ 'clear_update_cache' => true ]
				);
			}


			// Flush plugins cache so the headers of the newly installed plugins will be read correctly.
			wp_clean_plugins_cache();

			// Get the installed plugin file.
			$plugin_info = $tgmpa_installer->plugin_info();

			// Don't try to activate on upgrade of active plugin as WP will do this already.
			if ( ! is_plugin_active( $plugin_info ) ) {
				$activate = activate_plugin( $plugin_info );
			}

			return;

		}


		if ( ! defined( 'FS_METHOD' ) ) {
			define( 'FS_METHOD', 'direct' );
		}
		global $wp_filesystem;
		if ( empty( $wp_filesystem ) ) {
			if ( file_exists( ABSPATH . '/wp-admin/includes/file.php' ) ) {
				require_once ABSPATH . '/wp-admin/includes/file.php';
				WP_Filesystem();
			}
		}
		if ( empty( $wp_filesystem ) ) {
			return;
		}

		if ( ! class_exists( 'Plugin_Upgrader' ) ) {
			if ( file_exists( ABSPATH . '/wp-admin/includes/class-wp-upgrader.php' ) ) {
				require_once ABSPATH . '/wp-admin/includes/class-wp-upgrader.php';
			}
		}

		$upgrader = new Plugin_Upgrader( new Grooni_Theme_Upgrader_Skin() );

		if ( ! is_dir( WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . $plugin_slug ) ) {
			$upgrader->install(
				'http://updates.grooni.com/?action=download&slug=' . $plugin_slug,
				[ 'clear_update_cache' => true ]
			);
		} elseif ( ! is_file( WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . $plugin_path ) ) {
			$this->store_import_info( 'ERROR: Plugin' . ' ' . $plugin_slug . ' not installed. Plugin directory exist but plugin file not exist (' . WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . $plugin_path . ')', '', 'fail' );
		}

		$active_plugins = (array) get_option( 'active_plugins', array() );
		if ( is_array( $active_plugins ) ) {
			if ( ! in_array( $plugin_path, $active_plugins ) ) {
				if ( file_exists( WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . $plugin_path ) ) {
					array_push( $active_plugins, $plugin_path );
				}
			}

			update_option( 'active_plugins', $active_plugins );
		}

	}


	/**
	 * Import plugins data
	 *
	 */
	function after_prepare_plugins( $is_preset = false ) {

		$this->add_revslider_global_settings();

		if ( ! $is_preset ) {
			$selected_google_fonts = json_decode( $this->get_data( 'selected_google_fonts', 'json', false ), true );
			$this->add_ultimate_google_fonts( $selected_google_fonts );
		}

	}

	public function add_revslider_global_settings() {

		if ( ! $this->check_import_option_flag( 'revslider-global-settings' ) ) {

			$revslider_options = array(
				'role'                 => 'admin',
				'includes_globally'    => 'off',
				'pages_for_includes'   => '',
				'js_to_footer'         => 'off',
				'js_defer'             => 'on',
				'load_all_javascript'  => 'off',
				'show_dev_export'      => 'off',
				'change_font_loading'  => '',
				'width'                => '1240',
				'width_notebook'       => '1024',
				'width_tablet'         => '778',
				'width_mobile'         => '480',
				'enable_newschannel'   => 'on',
				'enable_logs'          => 'off',
				'force_activation_box' => 'off',
				'pack_page_creation'   => 'on',
				'single_page_creation' => 'off'
			);
			update_option( 'revslider-global-settings', $revslider_options );

			$this->update_import_option_flag( 'revslider-global-settings' );

		}
	}

	public function add_ultimate_google_fonts( $selected_google_fonts = array() ) {

		if ( ! $this->check_import_option_flag( 'ultimate_selected_google_fonts' ) ) {
			update_option( 'ultimate_css', 'enable' );
			update_option( 'ultimate_js', 'enable' );

			// Load and set Ultimate Google fonts
			if ( empty( get_option( 'ultimate_selected_google_fonts' ) ) ) {
				// Get file contents and decode

				$this->store_import_info( 'Import ultimate_selected_google_fonts' );

				if ( ! empty( $selected_google_fonts ) ) {
					// Update fonts list
					update_option( 'ultimate_selected_google_fonts', $selected_google_fonts );
					// prevent double import
					$this->update_import_option_flag( 'ultimate_selected_google_fonts' );
				}

			}

		}
	}



	function import_convertplug() {

		// Get file contents and decode
		$convertplug = json_decode( $this->get_data( 'convertplug', 'json', false ), true );


		if ( ! empty( $convertplug ) ) {

			foreach ( $convertplug as $form_type => $form_data_list ) {

				$exist_forms = get_option( $form_type );

				if ( $exist_forms ) {

					$new_forms = array();
					foreach ( $form_data_list as $form_data_item ) {
						$new_forms[ $form_data_item['style_name'] ] = $form_data_item;
					}

					foreach ( $exist_forms as $exist_form_data ) {
						if ( isset( $new_forms[ $exist_form_data['style_name'] ] ) ) {
							unset( $new_forms[ $exist_form_data['style_name'] ] );
						}
					}

					if ( ! empty( $new_forms ) ) {
						foreach ( $new_forms as $f_num => $f_data ) {
							$exist_forms[] = $f_data;
						}
						update_option( $form_type, $exist_forms );
					}

				} else {
					update_option( $form_type, $form_data_list );
				}

				$this->store_import_info( 'Update convertplug plugin forms.' );

			}

		}

	}




	/**
	 * Import sidebars
	 *
	 */
	function import_sidebars() {


		$import_sidebars = array();
		$custom_sidebars = array();
		$exist_sidebars  = array();


		if ( ! $this->check_import_option_flag( 'sidebars_custom_import' ) && class_exists( 'Crane_Sidebars_Creator' ) ) {

			$this->store_import_info( 'Adding Sidebars' );

			// Get file contents and decode
			$custom_sidebars = json_decode( $this->get_data( 'sidebars_custom', 'json', false ), true );

			$exist_sidebars = Crane_Sidebars_Creator::get_sidebars( false );

			if ( ! empty( $custom_sidebars ) ) {
				foreach ( $custom_sidebars as $sidebar_id => $sidebar_data ) {
					if ( ! empty( $sidebar_data['name'] ) ) {
						$exist_sidebars[ $sidebar_id ] = $sidebar_data;
					}
				}

				if ( ! empty( $exist_sidebars ) ) {
					Crane_Sidebars_Creator::update_sidebars( $exist_sidebars );
				}

				$this->update_import_option_flag( 'sidebars_custom_import' );

			}

		}


		if ( ! $this->check_import_option_flag( 'sidebars_import' ) && class_exists( 'Grooni_Theme_Addons_WIE_Helper' ) ) {

			$this->store_import_info( 'Adding Widgets' );

			// Get file contents and decode
			$widgets_data = json_decode( $this->get_data( 'sidebars', 'json', false ), true );

			if ( ! empty( $widgets_data ) ) {
				foreach ( $widgets_data as $sidebar_name => $sidebar_widgets ) {
					if ( stristr( $sidebar_name, 'orphaned_widgets_' ) !== false ) {
						continue;
					}
					if ( stristr( $sidebar_name, 'wp_inactive_widgets' ) !== false ) {
						continue;
					}
					if ( stristr( $sidebar_name, 'array_version' ) !== false ) {
						continue;
					}

					$import_sidebars[] = $sidebar_name;
				}


				if ( ! empty( $import_sidebars ) ) {

					$sidebars = Crane_Sidebars_Creator::get_sidebars( true );

					if ( is_array( $sidebars ) ) {
						foreach ( $sidebars as $key => $sidebar ) {
							if ( ! empty( $sidebar['custom_sidebar'] ) ) {
								register_sidebar( array(
									'name'          => $sidebar['name'],
									'id'            => $key,
									'class'         => 'crane-custom-sidebar',
									'before_widget' => '<div class="widget">',
									'after_widget'  => '</div>',
									'before_title'  => '<h4 class="widget-title">',
									'after_title'   => '</h4>',
								) );
							}
						}
					}

					$options    = array( 'widgets_data' => $widgets_data, 'import_sidebars' => $import_sidebars );
					$wie_import = new Grooni_Theme_Addons_WIE_Helper( $options );

					// Import
					$wie_import->wie_process_import_sidebars();
					// $result = $wie_import->get_result() );

				}

				$this->update_import_option_flag( 'sidebars_import' );

			}

		}

	}

	/**
	 * Import sidebars
	 *
	 */
	function import_preset_sidebars( $preset_data ) {

		if ( empty( $preset_data['sidebars'] ) ) {
			return;
		}

		$sidebars_to_import = $preset_data['sidebars'];

		$import_sidebars = array();
		$import_widgets  = array();
		$exist_sidebars  = array();

		if ( class_exists( 'Crane_Sidebars_Creator' ) ) {

			$exist_sidebars        = Crane_Sidebars_Creator::get_sidebars( true );
			$exist_custom_sidebars = Crane_Sidebars_Creator::get_sidebars( false );
			$import_sidebars       = $exist_custom_sidebars;

			foreach ( $sidebars_to_import as $sidebar_id => $sidebar_data ) {

				// Skip exist sidebars
				if ( isset( $exist_sidebars[ $sidebar_id ] ) || empty( $sidebar_data['params'] ) ) {
					continue;
				}

				$import_sidebars[ $sidebar_id ] = $sidebar_data['params'];
				$import_widgets[ $sidebar_id ]  = $sidebar_data['widgets'];

				$this->store_import_info( 'Adding new sidebar: ' . $sidebar_data['params']['name'] );

			}


			if ( ! empty( $import_sidebars ) && count( $import_sidebars ) != count( $exist_custom_sidebars ) ) {
				// SAVE new sidebar
				Crane_Sidebars_Creator::update_sidebars( $import_sidebars );

				// SAVE new widgets
				if ( ! empty( $import_widgets ) ) {
					foreach ( $import_widgets as $sidebar_name => $sidebar_widgets ) {
						if ( stristr( $sidebar_name, 'orphaned_widgets_' ) !== false ) {
							continue;
						}
						if ( stristr( $sidebar_name, 'wp_inactive_widgets' ) !== false ) {
							continue;
						}
						if ( stristr( $sidebar_name, 'array_version' ) !== false ) {
							continue;
						}

						$import_sidebars[] = $sidebar_name;
					}


					if ( ! empty( $import_sidebars ) ) {

						$sidebars = Crane_Sidebars_Creator::get_sidebars( true );

						if ( is_array( $sidebars ) ) {
							foreach ( $sidebars as $key => $sidebar ) {
								if ( ! empty( $sidebar['custom_sidebar'] ) ) {
									register_sidebar( array(
										'name'          => $sidebar['name'],
										'id'            => $key,
										'class'         => 'crane-custom-sidebar',
										'before_widget' => '<div class="widget">',
										'after_widget'  => '</div>',
										'before_title'  => '<h4 class="widget-title">',
										'after_title'   => '</h4>',
									) );
								}
							}
						}

						$options    = array( 'widgets_data' => $import_widgets, 'import_sidebars' => $import_sidebars );
						$wie_import = new Grooni_Theme_Addons_WIE_Helper( $options );

						// Import
						$wie_import->wie_process_import_sidebars();
						// $result = $wie_import->get_result() );

					}

				}

			}

		}


	}



	/**
	 * Import Nav Menus
	 *
	 */
	function import_preset_nav_menus( $preset_data ) {

		if ( empty( $preset_data['nav_menu'] ) ) {
			return;
		}

		foreach ( $preset_data['nav_menu'] as $nav_slug => $nav_data ) {

			$menu_exists = wp_get_nav_menu_object( $nav_data['slug'] );

			if ( ! $menu_exists ) {

				$this->add_menu_data( $nav_data, false, true );

				$this->import_posts_from_preset( array( 'nav_menu_item' => $nav_data['posts'] ) );

			}

		}


	}


	/**
	 * Add menu data
	 *
	 * @param array $menu_data The array of menu data.
	 *
	 * @return array
	 */
	function add_menu_data( $menu_data = array(), $_is_main_menu = true, $_force_add = false ) {

		// Default params
		if ( empty( $menu_data ) ) {
			$menu_data = array(
				'slug'      => 'main-menu',
				'menu-name' => 'Main Menu'
			);
		}

		if ( ! $_force_add && $this->check_import_option_flag( $menu_data['slug'] ) ) {
			return;
		}

		// old import version support (before 1.2)
		if ( 'main-menu' === $menu_data['slug'] ) {
			if ( ! $_force_add && $this->check_import_option_flag( 'main_menu' ) ) {
				return;
			}
		}

		$this->store_import_info( 'Adding Menu' . ': ' . $menu_data['slug'] );

		// Check if the menu exists
		$menu_exists = wp_get_nav_menu_object( $menu_data['slug'] );

		if ( ! $menu_exists ) {

			$main_menu_id = wp_create_nav_menu( $menu_data['slug'] );
			$main_menu_id = wp_update_nav_menu_object( $main_menu_id, array( 'menu-name' => $menu_data['menu-name'] ) );

		} else {
			$main_menu_id = $menu_exists->term_id;
		}

		if ( $_is_main_menu ) {
			$locations            = get_theme_mod( 'nav_menu_locations' );
			$locations['primary'] = (int) $main_menu_id;
			set_theme_mod( 'nav_menu_locations', $locations );
		}

		$this->update_import_option_flag( $menu_data['slug'], $main_menu_id );

		// old import version support (before 1.2)
		if ( 'main-menu' === $menu_data['slug'] ) {
			$this->update_import_option_flag( 'main_menu', $main_menu_id );
		}

	}

	function register_taxonomy_helper( $type, $term_domain ) {

		if ( 'wocommerce' == $type ) {
			register_taxonomy(
				$term_domain,
				apply_filters( 'woocommerce_taxonomy_objects_' . $term_domain, array( 'product' ) ),
				apply_filters( 'woocommerce_taxonomy_args_' . $term_domain, array(
					'hierarchical' => true,
					'show_ui'      => false,
					'query_var'    => true,
					'rewrite'      => false,
				) )
			);
		}

	}


	function bump_posts_table_id( $bumped_id = 999999 ) {

		$_postarr = array(
			'import_id'    => $bumped_id,
			'post_type'    => 'page',
			'post_title'   => 'new',
			'post_content' => '',
			'post_status'  => 'draft'
		);

		$dummy_post_id = wp_insert_post( $_postarr );
		$dummy_post_id = wp_delete_post( $dummy_post_id, true );

	}


	/**
	 * Import WooCommerce pages
	 *
	 * @return array
	 */
	function import_woocommerce_pages() {

		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}

		if ( $this->check_import_option_flag( 'woocommerce_data' ) ) {
			return;
		}

		$this->store_import_info( 'Adding WooCommerce data' );

		$woopages = array(
			'woocommerce_shop_page_id'      => 'Shop',
			'woocommerce_cart_page_id'      => 'Cart',
			'woocommerce_checkout_page_id'  => 'Checkout',
			'woocommerce_myaccount_page_id' => 'My Account',
		);

		foreach ( $woopages as $woo_page_name => $woo_page_title ) {
			$woopage = get_page_by_title( $woo_page_title );
			if ( isset( $woopage ) && $woopage->ID ) {
				update_option( $woo_page_name, $woopage->ID );
			}
		}

		$notices = array_diff( get_option( 'woocommerce_admin_notices', array() ), array(
			'install',
			'update',
		) );
		update_option( 'woocommerce_admin_notices', $notices );
		delete_option( '_wc_needs_pages' );
		delete_transient( '_wc_activation_redirect' );

		$this->update_import_option_flag( 'woocommerce_data' );


		if ( ! $this->check_import_option_flag( 'woocommerce_install_pages' ) ) {
			if ( class_exists( 'WC_Install' ) ) {
				WC_Install::create_pages();

				$this->update_import_option_flag( 'woocommerce_install_pages' );

			}
		}


	}


	/**
	 * Import WooCommerce Attributes
	 *
	 * @return array
	 */
	function import_woocommerce_attributes() {

		if ( $this->check_import_option_flag( 'shop_attributes' ) ) {
			return;
		}

		if ( ! function_exists( 'wc_sanitize_taxonomy_name' ) || ! function_exists( 'wc_check_if_attribute_name_is_reserved' ) ) {
			return;
		}

		global $wpdb;
		$attributes = json_decode( $this->get_data( 'shop_attributes', 'json', false ), true );

		$this->store_import_info( 'Import Woocommerce attributes' );

		foreach ( $attributes as $attr ) {

			$attribute_name = wc_sanitize_taxonomy_name( $attr['attribute_name'] );
			if ( wc_check_if_attribute_name_is_reserved( $attribute_name ) ) {
				continue;
			}
			if ( taxonomy_exists( $attribute_name ) ) {
				continue;
			}
			// Create the taxonomy
			if ( ! in_array( 'pa_' . $attribute_name, wc_get_attribute_taxonomy_names() ) ) {
				$attribute = array(
					'attribute_name'    => $attribute_name,
					'attribute_label'   => wc_sanitize_taxonomy_name( $attr['attribute_label'] ),
					'attribute_type'    => wc_sanitize_taxonomy_name( $attr['attribute_type'] ),
					'attribute_orderby' => wc_sanitize_taxonomy_name( $attr['attribute_orderby'] ),
					'attribute_public'  => wc_sanitize_taxonomy_name( $attr['attribute_public'] ),
				);
				$wpdb->insert( $wpdb->prefix . 'woocommerce_attribute_taxonomies', $attribute );
				delete_transient( 'wc_attribute_taxonomies' );
			}

			// Register the taxonomy now so that the import works!
			$this->register_taxonomy_helper( 'woocommerce', $attribute_name );

		}


		$this->update_import_option_flag( 'shop_attributes' );

	}


	/**
	 * Import Revolution sliders
	 *
	 */
	function import_rev_sliders( $page_name = '' ) {

		if ( ! class_exists( 'RevSliderAdmin' ) ) {
			return;
		}

		$rev_files             = glob( $this->get_assets_data( 'path' ) . '/revslider/*.zip' );
		$rev_sliders_page_keys = apply_filters( 'grooni_addons_import_revslider', array() );

		$slider = new RevSlider();

		// grab the "alias" names of the sliders
		$all_sliders_array = $slider->getAllSliderAliases();


		// Check if no sliders in RevSlider - then import pack with DB
		if ( empty( $page_name ) && empty( $all_sliders_array ) ) {

			global $wpdb;
			$escaped_uri = str_replace( '/', '\\\\/', GROONI_THEME_ADDONS_SITE_URI ) . '\\\\/';

			$_tmp_line = '';
			$sql_lines = $this->get_data( 'revslider_all_data', 'txt', false );

			if ( ! empty( $sql_lines ) ) {

				$this->store_import_info( 'Adding Revolution Sliders by DB' );

				$sql_lines = explode( "\n", $sql_lines );

				foreach ( $sql_lines as $line ) {
					if ( substr( $line, 0, 2 ) == '--' || $line == '' ) {
						continue;
					}

					$_tmp_line .= $line;
					if ( substr( trim( $line ), - 1, 1 ) == ';' ) {
						ob_start();
						$wpdb->query( str_replace( array(
							'http:\\\\/\\\\/' . GROONI_THEME_ADDONS_CURRENT_THEME_SLUG . '-test-demo.grooni.com\\\\/',
							'http:\\\\/\\\\/' . GROONI_THEME_ADDONS_CURRENT_THEME_SLUG . '-demo.grooni.com\\\\/',
							GROONI_THEME_ADDONS_CURRENT_THEME_SLUG . '_',
						), array(
							$escaped_uri,
							$escaped_uri,
							$wpdb->prefix,
						), $_tmp_line ), false );
						$_tmp_line = '';
						ob_end_clean();
					}
				}

				// update info about slider import
				foreach ( $rev_files as $rev_file ) {
					$rev_file_name = basename( $rev_file, ".zip" );
					$this->update_import_option_flag( 'revslider_slide_' . $rev_file_name );
				}

			}

		}


		if ( ! empty( $rev_files ) ) {
			foreach ( $rev_files as $rev_file ) {

				if ( ! is_file( $rev_file ) ) {
					continue;
				}

				$rev_file_name = basename( $rev_file, ".zip" );

				if ( empty( $rev_sliders_page_keys ) && ! empty( $page_name ) && $rev_file_name !== $page_name ) {
					continue;
				}

				if ( ! empty( $rev_sliders_page_keys ) && ! empty( $page_name ) ) {
					if ( isset( $rev_sliders_page_keys[ $page_name ] ) ) {
						if ( is_array( $rev_sliders_page_keys[ $page_name ] ) && ! in_array( $rev_file_name, $rev_sliders_page_keys[ $page_name ] ) ) {
							continue;
						} elseif ( ! is_array( $rev_sliders_page_keys[ $page_name ] ) && $rev_file_name !== $rev_sliders_page_keys[ $page_name ] ) {
							continue;
						}
					} else {
						continue;
					}
				}


				if ( $this->check_import_option_flag( 'revslider_slide_' . $rev_file_name ) ) {
					continue;
				}

				$this->store_import_info( 'Adding Revolution Slider:' . ' ' . $rev_file_name );

				//$slider = new RevSlider();

				if ( is_file( $rev_file ) ) {
					// Import slider archive
					$slider->importSliderFromPost( true, true, $rev_file );
					// Prevent double import
					$this->update_import_option_flag( 'revslider_slide_' . $rev_file_name );
				}

			}
		}

	}


	public function preset_import_data__revslider( $preset_name, $plugin_data ) {

		if ( ! class_exists( 'RevSliderAdmin' ) ) {
			return false;
		}

		$rev_sliders = empty( $plugin_data['plugin_data']['sliders'] ) ? array() : $plugin_data['plugin_data']['sliders'];

		if ( empty( $rev_sliders ) || ! is_array( $rev_sliders ) ) {
			return false;
		}

		$slider = new RevSlider();

		// grab the "alias" names of the sliders
		$all_sliders_array = $slider->getAllSliderAliases();
		if ( empty( $all_sliders_array ) ) {
			$all_sliders_array = array();
		}

		// Check if no sliders in RevSlider - then import pack with DB
		foreach ( $rev_sliders as $rev_slide_name ) {

			$rev_file = $this->get_assets_data( 'tmp_path' ) . $preset_name . '/plugins_data/revslider/' . $rev_slide_name . '.zip';

			if ( ! is_file( $rev_file ) ) {
				$this->store_import_info( 'Revolution Slider:' . ' ' . $rev_slide_name . ' archive not exist. Skipped it.' );
				continue;
			}

			//if ( $this->check_import_option_flag( 'revslider_slide_' . $rev_slide_name ) ) {
			if ( in_array( $rev_slide_name, $all_sliders_array ) ) {
				$this->store_import_info( 'Revolution Slider:' . ' ' . $rev_slide_name . ' exist. Skipped it.' );
				continue;
			}

			$this->store_import_info( 'Adding Revolution Slider:' . ' ' . $rev_slide_name );

			// Import slider archive
			$slider->importSliderFromPost( true, true, $rev_file );
			// Prevent double import
			$this->update_import_option_flag( 'revslider_slide_' . $rev_slide_name );

		}

		return true;

	}


	/**
	 * Page Options
	 *
	 * @return array
	 */
	function import_page_options( $home_id = null ) {

		if ( $this->check_import_option_flag( 'home_page_id' ) || $this->check_import_option_flag( 'is_home_imported' ) ) {
			return;
		}

		if ( ! empty( $home_id ) ) {
			$_pages_home = $this->options['pages_home'];
			if ( isset( $_pages_home[ $home_id ] ) ) {
				$home_id = $_pages_home[ $home_id ];
			}
		}

		if ( empty( $home_id ) ) {
			$home_id = json_decode( $this->get_data( 'home_id', 'json', false ), true );
		}

		$homepage = get_post( $home_id );

		if ( $homepage ) {
			// Set home page
			update_option( 'show_on_front', 'page' );
			update_option( 'page_on_front', $homepage->ID );

			$this->update_import_option_flag( 'is_home_imported' );
			$this->update_import_option_flag( array( 'home_page_id' => $home_id ) );

			$this->store_import_info( 'Set home page' . ': ' . $home_id );

			//// Move "Hello World" post to trash
			wp_trash_post( 1 );

			//// Move "Sample Page" to trash
			wp_trash_post( 2 );

		}

	}


	/**
	 * Get a stream reader for the file.
	 *
	 * @param string $file Path to the XML file.
	 *
	 * @return XMLReader|WP_Error Reader instance on success, error otherwise.
	 */
	protected function get_reader( $file ) {
		// Avoid loading external entities for security
		$old_value = null;
		if ( function_exists( 'libxml_disable_entity_loader' ) ) {
			// $old_value = libxml_disable_entity_loader( true );
		}

		$reader = new XMLReader();
		$status = $reader->open( $file );

		if ( ! is_null( $old_value ) ) {
			// libxml_disable_entity_loader( $old_value );
		}

		if ( ! $status ) {
			return new WP_Error( 'wxr_importer.cannot_parse', __( 'Could not open the file for parsing', 'grooni-theme-addons' ) );
		}

		return $reader;
	}

	/**
	 * The main controller for the actual import stage.
	 *
	 * @param string $file Path to the WXR file for importing
	 *
	 * @return WP_Error|WXR_Import_Info|XMLReader
	 *
	 */
	public function get_preliminary_information( $file ) {
		// Let's run the actual importer
		$reader = $this->get_reader( $file );
		if ( is_wp_error( $reader ) ) {
			return $reader;
		}

		// Set the version to compatibility mode first
		$this->version = '1.0';

		// Start parsing!
		$data = new WXR_Import_Info();
		while ( $reader->read() ) {
			// Only deal with element opens
			if ( $reader->nodeType !== XMLReader::ELEMENT ) {
				continue;
			}

			switch ( $reader->name ) {
				case 'wp:wxr_version':
					// Upgrade to the correct version
					$this->version = $reader->readString();

					if ( version_compare( $this->version, self::MAX_WXR_VERSION, '>' ) && WP_DEBUG ) {
						$this->logger->warning( sprintf(
							__( 'This WXR file (version %s) is newer than the importer (version %s) and may not be supported. Please consider updating.', 'grooni-theme-addons' ),
							$this->version,
							self::MAX_WXR_VERSION
						) );
					}

					// Handled everything in this node, move on to the next
					$reader->next();
					break;

				case 'generator':
					$data->generator = $reader->readString();
					$reader->next();
					break;

				case 'title':
					$data->title = $reader->readString();
					$reader->next();
					break;

				case 'wp:base_site_url':
					$data->siteurl = $reader->readString();
					$reader->next();
					break;

				case 'wp:base_blog_url':
					$data->home = $reader->readString();
					$reader->next();
					break;

				case 'wp:author':
					$node = $reader->expand();

					$parsed = $this->parse_author_node( $node );

					if ( is_wp_error( $parsed ) ) {
						$this->log_error( $parsed );

						// Skip the rest of this post
						$reader->next();
						break;
					}

					$data->users[] = $parsed;

					// Handled everything in this node, move on to the next
					$reader->next();
					break;

				case 'item':
					$node   = $reader->expand();
					$parsed = $this->parse_post_node( $node );
					if ( is_wp_error( $parsed ) ) {
						$this->log_error( $parsed );

						// Skip the rest of this post
						$reader->next();
						break;
					}

					if ( $parsed['data']['post_type'] === 'attachment' ) {
						$data->media_count ++;
					} else {
						$data->post_count ++;
					}
					$data->comment_count += count( $parsed['comments'] );

					// Handled everything in this node, move on to the next
					$reader->next();
					break;

				case 'wp:category':
				case 'wp:tag':
				case 'wp:term':
					$data->term_count ++;

					// Handled everything in this node, move on to the next
					$reader->next();
					break;
			}
		}

		$data->version = $this->version;

		return $data;
	}

	/**
	 *  The main controller for the actual import stage.
	 *
	 * @param string $file Path to the WXR file for importing
	 *
	 * @return WP_Error|XMLReader
	 */
	public function import( $file, $file_name = 'content', $exclude_ids = array() ) {

		add_filter( 'import_post_meta_key', array( $this, 'is_valid_meta_key' ) );
		add_filter( 'http_request_timeout', array( $this, 'bump_request_timeout' ) );

		$result = $this->import_start( $file );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		// Let's run the actual importer now, woot
		$reader = $this->get_reader( $file );
		if ( is_wp_error( $reader ) ) {
			return $reader;
		}

		// Set the version to compatibility mode first
		$this->version = '1.0';

		// Reset other variables
		$this->base_url = '';

		// Start parsing!
		while ( $reader->read() ) {
			// Only deal with element opens
			if ( $reader->nodeType !== XMLReader::ELEMENT ) {
				continue;
			}

			switch ( $reader->name ) {
				case 'wp:wxr_version':
					// Upgrade to the correct version
					$this->version = $reader->readString();

					if ( version_compare( $this->version, self::MAX_WXR_VERSION, '>' ) && WP_DEBUG ) {
						$this->logger->warning( sprintf(
							__( 'This WXR file (version %s) is newer than the importer (version %s) and may not be supported. Please consider updating.', 'grooni-theme-addons' ),
							$this->version,
							self::MAX_WXR_VERSION
						) );
					}

					// Handled everything in this node, move on to the next
					$reader->next();
					break;

				case 'wp:base_site_url':
					$this->base_url = $reader->readString();

					// Handled everything in this node, move on to the next
					$reader->next();
					break;

				case 'item':

					@ob_flush();
					@flush();


					$node   = $reader->expand();
					$parsed = $this->parse_post_node( $node );
					if ( is_wp_error( $parsed ) ) {
						$this->log_error( $parsed );

						// Skip the rest of this post
						$reader->next();
						break;
					}

					$do_process = true;
					if ( ! empty( $exclude_ids ) ) {
						$original_id = isset( $parsed['data']['post_id'] ) ? (int) $parsed['data']['post_id'] : 0;
						if ( in_array( $original_id, $exclude_ids ) ) {
							$do_process = false;
						}
					}

					if ( $do_process ) {
						// Add post
						$this->process_post( $parsed['data'], $parsed['meta'], $parsed['comments'], $parsed['terms'] );
					}

					// Handled everything in this node, move on to the next
					$reader->next();
					break;

				case 'wp:author':

					$this->store_import_info( 'Get author mapping' );
					@ob_flush();
					@flush();


					$node = $reader->expand();

					$parsed = $this->parse_author_node( $node );

					if ( is_wp_error( $parsed ) ) {
						$this->log_error( $parsed );

						// Skip the rest of this post
						$reader->next();
						break;
					}

					$status = $this->process_author( $parsed['data'], $parsed['meta'] );
					if ( is_wp_error( $status ) ) {
						$this->log_error( $status );
					}

					// Handled everything in this node, move on to the next
					$reader->next();
					break;

				case 'wp:category':

					@ob_flush();
					@flush();


					$node = $reader->expand();

					$parsed = $this->parse_term_node( $node, 'category' );
					if ( is_wp_error( $parsed ) ) {
						$this->log_error( $parsed );

						// Skip the rest of this post
						$reader->next();
						break;
					}

					$status = $this->process_term( $parsed['data'], $parsed['meta'] );

					// Handled everything in this node, move on to the next
					$reader->next();
					break;

				case 'wp:tag':

					@ob_flush();
					@flush();


					$node = $reader->expand();

					$parsed = $this->parse_term_node( $node, 'tag' );
					if ( is_wp_error( $parsed ) ) {
						$this->log_error( $parsed );

						// Skip the rest of this post
						$reader->next();
						break;
					}

					$status = $this->process_term( $parsed['data'], $parsed['meta'] );

					// Handled everything in this node, move on to the next
					$reader->next();
					break;

				case 'wp:term':

					@ob_flush();
					@flush();

					$node = $reader->expand();

					$parsed = $this->parse_term_node( $node );
					if ( is_wp_error( $parsed ) ) {
						$this->log_error( $parsed );

						// Skip the rest of this post
						$reader->next();
						break;
					}

					$status = $this->process_term( $parsed['data'], $parsed['meta'] );

					// Handled everything in this node, move on to the next
					$reader->next();
					break;

				default:
					// Skip this node, probably handled by something already
					break;
			}
		}

		// Now that we've done the main processing, do any required
		// post-processing and remapping.
		$this->post_process();

		$this->store_import_info( 'Updating information in the database...' );
		@ob_flush();
		@flush();


		if ( $this->options['aggressive_url_search'] ) {
			$this->replace_attachment_urls_in_content();
		}
		$this->remap_featured_images();

		$this->import_end( $file_name );
	}

	/**
	 * Log an error instance to the logger.
	 *
	 * @param WP_Error $error Error instance to log.
	 */
	protected function log_error( WP_Error $error ) {

		if ( WP_DEBUG ) {
			$this->logger->warning( $error->get_error_message() );
		}

		// Log the data as debug info too
		$data = $error->get_error_data();
		if ( ! empty( $data ) && WP_DEBUG ) {
			$this->logger->debug( var_export( $data, true ) );
		}
	}

	/**
	 * Parses the WXR file and prepares us for the task of processing parsed data
	 *
	 * @param string $file Path to the WXR file for importing
	 */
	protected function import_start( $file ) {
		if ( ! is_file( $file ) ) {
			return new WP_Error( 'wxr_importer.file_missing', __( 'The file does not exist, please try again.', 'grooni-theme-addons' ) );
		}

		// Suspend bunches of stuff in WP core
		wp_defer_term_counting( true );
		wp_defer_comment_counting( true );
		wp_suspend_cache_invalidation( true );

		// Prefill exists calls if told to
		if ( $this->options['prefill_existing_posts'] ) {
			$this->prefill_existing_posts();
		}
		if ( $this->options['prefill_existing_comments'] ) {
			$this->prefill_existing_comments();
		}
		if ( $this->options['prefill_existing_terms'] ) {
			$this->prefill_existing_terms();
		}

		/**
		 * Begin the import.
		 *
		 * Fires before the import process has begun. If you need to suspend
		 * caching or heavy processing on hooks, do so here.
		 */
		do_action( 'import_start' );
	}

	/**
	 * Performs post-import cleanup of files and the cache
	 */
	protected function import_end( $file_name = 'content' ) {
		// Re-enable stuff in core
		wp_suspend_cache_invalidation( false );
		wp_cache_flush();
		foreach ( get_taxonomies() as $tax ) {
			delete_option( "{$tax}_children" );
			_get_term_hierarchy( $tax );
		}

		wp_defer_term_counting( false );
		wp_defer_comment_counting( false );


		if ( strrpos( $file_name, "pages_home" ) !== false ) {
			$this->import_page_options( $file_name );
		}


		/**
		 * Complete the import.
		 *
		 * Fires after the import process has finished. If you need to update
		 * your cache or re-enable processing, do so here.
		 */
		do_action( 'import_end' );

		$this->store_import_info( 'Lead-up to the next import step...', '1' );

	}

	/**
	 * Set the user mapping.
	 *
	 * @param array $mapping List of map arrays (containing `old_slug`, `old_id`, `new_id`)
	 */
	public function set_user_mapping() {

		$mapping = array();
		$i       = 0;

		foreach ( $this->authors as $author ) {

			$mapping[ $i ] = array(
				'old_slug' => $author['data']['user_login'],
				'old_id'   => $author['data']['ID'],
				'new_id'   => get_current_user_id(),
			);

			$i ++;
		}

		foreach ( $mapping as $map ) {
			if ( ( empty( $map['old_slug'] ) || empty( $map['old_id'] ) || empty( $map['new_id'] ) ) ) {
				if ( WP_DEBUG ) {
					$this->logger->warning( __( 'Invalid author mapping', 'grooni-theme-addons' ) );
					$this->logger->debug( var_export( $map, true ) );
				}
				continue;
			}

			$old_slug = $map['old_slug'];
			$old_id   = $map['old_id'];

			$this->mapping['user'][ $old_id ]        = get_current_user_id();
			$this->mapping['user_slug'][ $old_slug ] = get_current_user_id();
		}
	}

	/**
	 * Set the user slug overrides.
	 *
	 * Allows overriding the slug in the import with a custom/renamed version.
	 *
	 * @param string[] $overrides Map of old slug to new slug.
	 */
	public function set_user_slug_overrides( $overrides ) {
		foreach ( $overrides as $original => $renamed ) {
			$this->user_slug_override[ $original ] = $renamed;
		}
	}


	/**
	 * Create new posts based on import information
	 *
	 * Posts marked as having a parent which doesn't exist will become top level items.
	 * Doesn't create a new post if: the post type doesn't exist, the given post ID
	 * is already noted as imported or a post with the same title and date already exists.
	 * Note that new/updated terms, comments and meta are imported for the last of the above.
	 */
	protected function process_post( $data, $meta, $comments, $terms ) {
		/**
		 * Pre-process post data.
		 *
		 * @param array $data Post data. (Return empty to skip.)
		 * @param array $meta Meta data.
		 * @param array $comments Comments on the post.
		 * @param array $terms Terms on the post.
		 */
		$data = apply_filters( 'wxr_importer.pre_process.post', $data, $meta, $comments, $terms );
		if ( empty( $data ) ) {

			return false;
		}

		$original_id = isset( $data['post_id'] ) ? (int) $data['post_id'] : 0;
		$parent_id   = isset( $data['post_parent'] ) ? (int) $data['post_parent'] : 0;

		// Have we already processed this?
		if ( isset( $this->mapping['post'][ $original_id ] ) ) {

			return;
		}

		$post_type_object = get_post_type_object( $data['post_type'] );

		// Is this type even valid?
		if ( ! $post_type_object ) {
			if ( WP_DEBUG ) {
				$err_reason = sprintf(
					__( 'Failed to import "%s": Invalid post type %s', 'grooni-theme-addons' ),
					$data['post_title'],
					$data['post_type']
				);
				$this->logger->warning( $err_reason );
				$this->store_import_info_error( $err_reason );
			}

			return false;
		}

		$post_exists = $this->post_exists( $data );

		if ( $post_exists ) {
			if ( WP_DEBUG ) {
				$this->logger->info( sprintf(
					__( '%s "%s" already exists.', 'grooni-theme-addons' ),
					$post_type_object->labels->singular_name,
					$data['post_title']
				) );
			}

			// Even though this post already exists, new comments might need importing
			$this->process_comments( $comments, $original_id, $data, $post_exists );

			return false;
		}

		// Map the parent post, or mark it as one we need to fix
		$requires_remapping = false;
		if ( $parent_id ) {
			if ( isset( $this->mapping['post'][ $parent_id ] ) ) {
				$data['post_parent'] = $this->mapping['post'][ $parent_id ];
			} else {
				$meta[]             = array( 'key' => '_wxr_import_parent', 'value' => $parent_id );
				$requires_remapping = true;

				//$data['post_parent'] = 0; // leave 'post_parent' from import file
			}
		}


		// Map the author, or mark it as one we need to fix
		$author = sanitize_user( $data['post_author'], true );

		if ( empty( $author ) ) {
			// Missing or invalid author, use default if available.
			$data['post_author'] = $this->options['default_author'];
		} elseif ( isset( $this->mapping['user_slug'][ $author ] ) ) {
			$data['post_author'] = $this->mapping['user_slug'][ $author ];
		} else {
			$meta[]             = array( 'key' => '_wxr_import_user_slug', 'value' => $author );
			$requires_remapping = true;

			$data['post_author'] = (int) get_current_user_id();
		}

		$tax_input = array();
		foreach ( $terms as $term_item ) {
			$taxonomy = ( isset( $term_item['taxonomy'] ) ) ? $term_item['taxonomy'] : '';
			$slug     = ( isset( $term_item['slug'] ) ) ? $term_item['slug'] : '';

			if ( ! empty( $taxonomy ) && ! empty( $slug ) ) {

				$_term = get_term_by( 'slug', $slug, $taxonomy, ARRAY_A );

				$tax_input[ $taxonomy ][] = intval( $_term['term_id'] );
			}
		}

		/**
		 * @param array $postdata {
		 *  An array of elements that make up a post to update or insert.
		 *
		 * @type int $ID The post ID. If equal to something other than 0,
		 *                                         the post with that ID will be updated. Default 0.
		 * @type int $post_author The ID of the user who added the post. Default is
		 *                                         the current user ID.
		 * @type string $post_date The date of the post. Default is the current time.
		 * @type string $post_date_gmt The date of the post in the GMT timezone. Default is
		 *                                         the value of `$post_date`.
		 * @type mixed $post_content The post content. Default empty.
		 * @type string $post_content_filtered The filtered post content. Default empty.
		 * @type string $post_title The post title. Default empty.
		 * @type string $post_excerpt The post excerpt. Default empty.
		 * @type string $post_status The post status. Default 'draft'.
		 * @type string $post_type The post type. Default 'post'.
		 * @type string $comment_status Whether the post can accept comments. Accepts 'open' or 'closed'.
		 *                                         Default is the value of 'default_comment_status' option.
		 * @type string $ping_status Whether the post can accept pings. Accepts 'open' or 'closed'.
		 *                                         Default is the value of 'default_ping_status' option.
		 * @type string $post_password The password to access the post. Default empty.
		 * @type string $post_name The post name. Default is the sanitized post title
		 *                                         when creating a new post.
		 * @type string $to_ping Space or carriage return-separated list of URLs to ping.
		 *                                         Default empty.
		 * @type string $pinged Space or carriage return-separated list of URLs that have
		 *                                         been pinged. Default empty.
		 * @type string $post_modified The date when the post was last modified. Default is
		 *                                         the current time.
		 * @type string $post_modified_gmt The date when the post was last modified in the GMT
		 *                                         timezone. Default is the current time.
		 * @type int $post_parent Set this for the post it belongs to, if any. Default 0.
		 * @type int $menu_order The order the post should be displayed in. Default 0.
		 * @type string $post_mime_type The mime type of the post. Default empty.
		 * @type string $guid Global Unique ID for referencing the post. Default empty.
		 * @type array $post_category Array of category names, slugs, or IDs.
		 *                                         Defaults to value of the 'default_category' option.
		 * @type array $tags_input Array of tag names, slugs, or IDs. Default empty.
		 * @type array $tax_input Array of taxonomy terms keyed by their taxonomy name. Default empty.
		 * @type array $meta_input Array of post meta values keyed by their post meta key. Default empty.
		 * }
		 *
		 */
		$postdata = array(
			'tax_input' => $tax_input,
		);

		// Whitelist to just the keys we allow
		$allowed = array(
			'post_author'    => true,
			'post_date'      => true,
			'post_date_gmt'  => true,
			'post_content'   => true,
			'post_excerpt'   => true,
			'post_title'     => true,
			'post_status'    => true,
			'post_name'      => true,
			'comment_status' => true,
			'ping_status'    => true,
			'guid'           => true,
			'post_parent'    => true,
			'menu_order'     => true,
			'post_type'      => true,
			'post_password'  => true,
		);


		$postdata['import_id'] = $data['post_id'];

		foreach ( $data as $key => $value ) {
			if ( ! isset( $allowed[ $key ] ) ) {
				continue;
			}

			$postdata[ $key ] = $data[ $key ];
		}

		$postdata = apply_filters( 'wp_import_post_data_processed', $postdata, $data );

		if ( 'attachment' === $postdata['post_type'] ) {
			if ( ! $this->options['fetch_attachments'] ) {
				if ( WP_DEBUG ) {
					$this->logger->notice( sprintf(
						__( 'Skipping attachment: "%s", fetching attachments disabled', 'grooni-theme-addons' ),
						$data['post_title']
					) );
				}

				return false;
			}
			$remote_url = ! empty( $data['attachment_url'] ) ? $data['attachment_url'] : $data['guid'];
			$post_id    = $this->process_attachment( $postdata, $meta, $remote_url );
		} elseif ( 'page' === $postdata['post_type'] ) {
			$post_id = wp_insert_post( $postdata, true );
			$this->store_import_info( sprintf( 'Adding Page: %s', $postdata['post_title'] ) );

		} elseif ( 'post' == $postdata['post_type'] ) {
			$post_id = wp_insert_post( $postdata, true );
			do_action( 'wp_import_insert_post', $post_id, $original_id, $postdata, $data );

			$this->store_import_info( sprintf( 'Adding Post: %s', $postdata['post_title'] ) );

		} elseif ( 'product' == $postdata['post_type'] ) {
			$post_id = wp_insert_post( $postdata, true );

			wc_delete_product_transients( $post_id );
			wp_cache_delete( 'product-' . $post_id, 'products' );
			$this->store_import_info( sprintf( 'Adding Product: %s', $postdata['post_title'] ) );

		} elseif ( 'product_variation' == $postdata['post_type'] ) {
			$post_id = wp_insert_post( $postdata, true );

			wc_delete_product_transients( wp_get_post_parent_id( $post_id ) );
			$this->store_import_info( sprintf( 'Adding Product Variation: %s', $postdata['post_title'] ) );

		} else {
			$post_id = wp_insert_post( $postdata, true );

			$this->store_import_info( sprintf( 'Adding Post Type: %s', $postdata['post_type'] ) );

		}

		@ob_flush();
		@flush();

		if ( is_wp_error( $post_id ) ) {

			$err_reason = sprintf(
				__( 'Failed to import: "%s" (%s)', 'grooni-theme-addons' ),
				$data['post_title'],
				$post_type_object->labels->singular_name
			);
			$this->logger->error( $err_reason );
			$this->logger->debug( $post_id->get_error_message() );
			$this->store_import_info_error( $err_reason . ' ERR_MSG: ' . $post_id->get_error_message() );

			/**
			 * Post processing failed.
			 *
			 * @param WP_Error $post_id Error object.
			 * @param array $data Raw data imported for the post.
			 * @param array $meta Raw meta data, already processed by {@see process_post_meta}.
			 * @param array $comments Raw comment data, already processed by {@see process_comments}.
			 * @param array $terms Raw term data, already processed.
			 */
			do_action( 'wxr_importer.process_failed.post', $post_id, $data, $meta, $comments, $terms );

			return false;
		}

		// Ensure stickiness is handled correctly too
		if ( $data['is_sticky'] === '1' ) {
			stick_post( $post_id );
		}

		// map pre-import ID to local ID
		$this->mapping['post'][ $original_id ] = (int) $post_id;
		if ( $requires_remapping ) {
			$this->requires_remapping['post'][ $post_id ] = true;
		}
		$this->mark_post_exists( $data, $post_id );

		if ( WP_DEBUG ) {
			$this->logger->info( sprintf(
				__( 'Imported: "%s" (%s)', 'grooni-theme-addons' ),
				$data['post_title'],
				$post_type_object->labels->singular_name
			) );
			$this->logger->debug( sprintf(
				__( 'Post %d remapped to %d', 'grooni-theme-addons' ),
				$original_id,
				$post_id
			) );
		}

		// Handle the terms too
		$terms = apply_filters( 'wp_import_post_terms', $terms, $post_id, $data );

		if ( ! empty( $terms ) ) {
			$term_ids = array();

			if ( empty( $term_ids ) ) {
				$term_ids = $tax_input;
			}

			foreach ( $term_ids as $tax => $ids ) {
				$added_ids = wp_set_post_terms( $post_id, $ids, $tax );
				do_action( 'wp_import_set_post_terms', $added_ids, $ids, $tax, $post_id, $data );
			}
		}


		$this->process_comments( $comments, $post_id, $data );
		$this->process_post_meta( $meta, $post_id, $data );

		if ( 'nav_menu_item' === $data['post_type'] ) {
			$this->process_menu_item_meta( $post_id, $data, $meta );
		}

		/**
		 * Post processing completed.
		 *
		 * @param int $post_id New post ID.
		 * @param array $data Raw data imported for the post.
		 * @param array $meta Raw meta data, already processed by {@see process_post_meta}.
		 * @param array $comments Raw comment data, already processed by {@see process_comments}.
		 * @param array $terms Raw term data, already processed.
		 */
		do_action( 'wxr_importer.processed.post', $post_id, $data, $meta, $comments, $terms );
	}


	/**
	 * Parse a post node into post data.
	 *
	 * @param DOMElement $node Parent node of post data (typically `item`).
	 *
	 * @return array|WP_Error Post data array on success, error otherwise.
	 */
	protected function parse_post_node( $node ) {
		$data     = array();
		$meta     = array();
		$comments = array();
		$terms    = array();

		foreach ( $node->childNodes as $child ) {
			// We only care about child elements
			if ( $child->nodeType !== XML_ELEMENT_NODE ) {
				continue;
			}

			switch ( $child->tagName ) {
				case 'wp:post_type':
					$data['post_type'] = $child->textContent;
					break;

				case 'title':
					$data['post_title'] = $child->textContent;
					break;

				case 'guid':
					$data['guid'] = $child->textContent;
					break;

				case 'dc:creator':
					$data['post_author'] = $child->textContent;
					break;

				case 'content:encoded':
					$data['post_content'] = $child->textContent;
					break;

				case 'excerpt:encoded':
					$data['post_excerpt'] = $child->textContent;
					break;

				case 'wp:post_id':
					$data['post_id'] = $child->textContent;
					break;

				case 'wp:post_date':
					$data['post_date'] = $child->textContent;
					break;

				case 'wp:post_date_gmt':
					$data['post_date_gmt'] = $child->textContent;
					break;

				case 'wp:comment_status':
					$data['comment_status'] = $child->textContent;
					break;

				case 'wp:ping_status':
					$data['ping_status'] = $child->textContent;
					break;

				case 'wp:post_name':
					$data['post_name'] = $child->textContent;
					break;

				case 'wp:status':
					$data['post_status'] = $child->textContent;

					if ( $data['post_status'] === 'auto-draft' ) {
						// Bail now
						return new WP_Error(
							'wxr_importer.post.cannot_import_draft',
							__( 'Cannot import auto-draft posts', 'grooni-theme-addons' ),
							$data
						);
					}
					break;

				case 'wp:post_parent':
					$data['post_parent'] = $child->textContent;
					break;

				case 'wp:menu_order':
					$data['menu_order'] = $child->textContent;
					break;

				case 'wp:post_password':
					$data['post_password'] = $child->textContent;
					break;

				case 'wp:is_sticky':
					$data['is_sticky'] = $child->textContent;
					break;

				case 'wp:attachment_url':
					$data['attachment_url'] = $child->textContent;
					break;

				case 'wp:postmeta':
					$meta_item = $this->parse_meta_node( $child );
					if ( ! empty( $meta_item ) ) {
						$meta[] = $meta_item;
					}
					break;

				case 'wp:comment':
					$comment_item = $this->parse_comment_node( $child );
					if ( ! empty( $comment_item ) ) {
						$comments[] = $comment_item;
					}
					break;

				case 'category':
					$term_item = $this->parse_category_node( $child );
					if ( ! empty( $term_item ) ) {
						$terms[] = $term_item;
					}
					break;
			}
		}

		return compact( 'data', 'meta', 'comments', 'terms' );
	}


	/**
	 * Parse a meta node into meta data.
	 *
	 * @param DOMElement $node Parent node of meta data (typically `wp:postmeta` or `wp:commentmeta`).
	 *
	 * @return array|null Meta data array on success, or null on error.
	 */
	protected function parse_meta_node( $node ) {
		foreach ( $node->childNodes as $child ) {
			// We only care about child elements
			if ( $child->nodeType !== XML_ELEMENT_NODE ) {
				continue;
			}

			switch ( $child->tagName ) {
				case 'wp:meta_key':
					$key = $child->textContent;
					break;

				case 'wp:meta_value':
					$value = $child->textContent;
					break;
			}
		}

		if ( empty( $key ) || ( empty( $value ) && '0' !== $value ) ) {
			return null;
		}

		return compact( 'key', 'value' );
	}

	/**
	 * Process and import post meta items.
	 *
	 * @param array $meta List of meta data arrays
	 * @param int $post_id Post to associate with
	 * @param array $post Post data
	 *
	 * @return int|WP_Error Number of meta items imported on success, error otherwise.
	 */
	protected function process_post_meta( $meta, $post_id, $post ) {
		if ( empty( $meta ) ) {
			return true;
		}

		foreach ( $meta as $meta_item ) {
			/**
			 * Pre-process post meta data.
			 *
			 * @param array $meta_item Meta data. (Return empty to skip.)
			 * @param int $post_id Post the meta is attached to.
			 */
			$meta_item = apply_filters( 'wxr_importer.pre_process.post_meta', $meta_item, $post_id );
			if ( empty( $meta_item ) ) {
				return false;
			}

			$key   = apply_filters( 'import_post_meta_key', $meta_item['key'], $post_id, $post );
			$value = apply_filters( 'grooni_import_post_meta', $meta_item['value'], $post_id, $key, $post );

			if ( '_edit_last' === $key ) {
				$value = intval( $meta_item['value'] );
				if ( ! isset( $this->mapping['user'][ $value ] ) ) {
					// Skip!
					continue;
				}

				$value = $this->mapping['user'][ $value ];
			}

			if ( $key ) {
				// export gets meta straight from the DB so could have a serialized string
				$value = maybe_unserialize( $value );

				if ( $key == 'grooni_meta' && in_array( $post['post_type'], array(
						'post',
						'page',
						'product',
						$this->demo . '_portfolio',
					) )
				) {

					if ( ! is_array( $value ) && is_string( $value ) ) {
						$value = json_decode( $value, true );

					} else {
						$value = null;
					}

					if ( $value ) {
						update_metadata( 'post', $post_id, $key, json_encode( $value, JSON_UNESCAPED_UNICODE ) );
					}

				} else {

					update_metadata( 'post', $post_id, $key, $value );

				}

				do_action( 'import_post_meta', $post_id, $key, $value );

				// if the post has a featured image, take note of this in case of remap
				if ( '_thumbnail_id' === $key ) {
					$this->featured_images[ $post_id ] = (int) $value;
				}
			}

		}

		wp_cache_set( 'last_changed', microtime(), 'posts' );

		return true;
	}

	/**
	 * Parse a comment node into comment data.
	 *
	 * @param DOMElement $node Parent node of comment data (typically `wp:comment`).
	 *
	 * @return array Comment data array.
	 */
	protected function parse_comment_node( $node ) {
		$data = array(
			'commentmeta' => array(),
		);

		foreach ( $node->childNodes as $child ) {
			// We only care about child elements
			if ( $child->nodeType !== XML_ELEMENT_NODE ) {
				continue;
			}

			switch ( $child->tagName ) {
				case 'wp:comment_id':
					$data['comment_id'] = $child->textContent;
					break;
				case 'wp:comment_author':
					$data['comment_author'] = $child->textContent;
					break;

				case 'wp:comment_author_email':
					$data['comment_author_email'] = $child->textContent;
					break;

				case 'wp:comment_author_IP':
					$data['comment_author_IP'] = $child->textContent;
					break;

				case 'wp:comment_author_url':
					$data['comment_author_url'] = $child->textContent;
					break;

				case 'wp:comment_user_id':
					$data['comment_user_id'] = $child->textContent;
					break;

				case 'wp:comment_date':
					$data['comment_date'] = $child->textContent;
					break;

				case 'wp:comment_date_gmt':
					$data['comment_date_gmt'] = $child->textContent;
					break;

				case 'wp:comment_content':
					$data['comment_content'] = $child->textContent;
					break;

				case 'wp:comment_approved':
					$data['comment_approved'] = $child->textContent;
					break;

				case 'wp:comment_type':
					$data['comment_type'] = $child->textContent;
					break;

				case 'wp:comment_parent':
					$data['comment_parent'] = $child->textContent;
					break;

				case 'wp:commentmeta':
					$meta_item = $this->parse_meta_node( $child );
					if ( ! empty( $meta_item ) ) {
						$data['commentmeta'][] = $meta_item;
					}
					break;
			}
		}

		return $data;
	}

	/**
	 * Process and import comment data.
	 *
	 * @param array $comments List of comment data arrays.
	 * @param int $post_id Post to associate with.
	 * @param array $post Post data.
	 *
	 * @return int|WP_Error Number of comments imported on success, error otherwise.
	 */
	protected function process_comments( $comments, $post_id, $post, $post_exists = false ) {

		$comments = apply_filters( 'wp_import_post_comments', $comments, $post_id, $post );
		if ( empty( $comments ) ) {
			return 0;
		}

		$num_comments = 0;

		// Sort by ID to avoid excessive remapping later
		usort( $comments, array( $this, 'sort_comments_by_id' ) );

		foreach ( $comments as $key => $comment ) {
			/**
			 * Pre-process comment data
			 *
			 * @param array $comment Comment data. (Return empty to skip.)
			 * @param int $post_id Post the comment is attached to.
			 */
			$comment = apply_filters( 'wxr_importer.pre_process.comment', $comment, $post_id );
			if ( empty( $comment ) ) {
				return false;
			}

			$original_id = isset( $comment['comment_id'] ) ? (int) $comment['comment_id'] : 0;
			$parent_id   = isset( $comment['comment_parent'] ) ? (int) $comment['comment_parent'] : 0;
			$author_id   = isset( $comment['comment_user_id'] ) ? (int) $comment['comment_user_id'] : 0;

			// if this is a new post we can skip the comment_exists() check
			// TODO: Check comment_exists for performance
			if ( $post_exists ) {
				$existing = $this->comment_exists( $comment );
				if ( $existing ) {
					$this->mapping['comment'][ $original_id ] = $existing;
					continue;
				}
			}

			// Remove meta from the main array
			$meta = isset( $comment['commentmeta'] ) ? $comment['commentmeta'] : array();
			unset( $comment['commentmeta'] );

			// Map the parent comment, or mark it as one we need to fix
			$requires_remapping = false;
			if ( $parent_id ) {
				if ( isset( $this->mapping['comment'][ $parent_id ] ) ) {
					$comment['comment_parent'] = $this->mapping['comment'][ $parent_id ];
				} else {
					// Prepare for remapping later
					$meta[]             = array( 'key' => '_wxr_import_parent', 'value' => $parent_id );
					$requires_remapping = true;

					// Wipe the parent for now
					$comment['comment_parent'] = 0;
				}
			}

			// Map the author, or mark it as one we need to fix
			if ( $author_id ) {
				if ( isset( $this->mapping['user'][ $author_id ] ) ) {
					$comment['user_id'] = $this->mapping['user'][ $author_id ];
				} else {
					// Prepare for remapping later
					$meta[]             = array( 'key' => '_wxr_import_user', 'value' => $author_id );
					$requires_remapping = true;

					// Wipe the user for now
					$comment['user_id'] = 0;
				}
			}

			// Run standard core filters
			$comment['comment_post_ID'] = $post_id;
			$comment                    = wp_filter_comment( $comment );

			// wp_insert_comment expects slashed data
			$comment_id                               = wp_insert_comment( wp_slash( $comment ) );
			$this->mapping['comment'][ $original_id ] = $comment_id;
			if ( $requires_remapping ) {
				$this->requires_remapping['comment'][ $comment_id ] = true;
			}
			$this->mark_comment_exists( $comment, $comment_id );

			/**
			 * Comment has been imported.
			 *
			 * @param int $comment_id New comment ID
			 * @param array $comment Comment inserted (`comment_id` item refers to the original ID)
			 * @param int $post_id Post parent of the comment
			 * @param array $post Post data
			 */
			do_action( 'wp_import_insert_comment', $comment_id, $comment, $post_id, $post );

			// Process the meta items
			foreach ( $meta as $meta_item ) {
				$value = maybe_unserialize( $meta_item['value'] );
				add_comment_meta( $comment_id, wp_slash( $meta_item['key'] ), wp_slash( $value ) );
			}

			/**
			 * Post processing completed.
			 *
			 * @param int $post_id New post ID.
			 * @param array $comment Raw data imported for the comment.
			 * @param array $meta Raw meta data, already processed by {@see process_post_meta}.
			 * @param array $post_id Parent post ID.
			 */
			do_action( 'wxr_importer.processed.comment', $comment_id, $comment, $meta, $post_id );

			$num_comments ++;
		}

		return $num_comments;
	}

	protected function parse_category_node( $node ) {
		$data = array(
			// Default taxonomy to "category", since this is a `<category>` tag
			'taxonomy' => 'category',
		);

		if ( $node->hasAttribute( 'domain' ) ) {
			$data['taxonomy'] = $node->getAttribute( 'domain' );
		}
		if ( $node->hasAttribute( 'nicename' ) ) {
			$data['slug'] = $node->getAttribute( 'nicename' );
		}

		$data['name'] = $node->textContent;

		if ( empty( $data['slug'] ) ) {
			return null;
		}

		// Just for extra compatibility
		if ( $data['taxonomy'] === 'tag' ) {
			$data['taxonomy'] = 'post_tag';
		}

		return $data;
	}

	/**
	 * Callback for `usort` to sort comments by ID
	 *
	 * @param array $a Comment data for the first comment
	 * @param array $b Comment data for the second comment
	 *
	 * @return int
	 */
	public static function sort_comments_by_id( $a, $b ) {
		if ( empty( $a['comment_id'] ) ) {
			return 1;
		}

		if ( empty( $b['comment_id'] ) ) {
			return - 1;
		}

		return $a['comment_id'] - $b['comment_id'];
	}

	protected function parse_author_node( $node ) {
		$data = array();
		$meta = array();
		foreach ( $node->childNodes as $child ) {
			// We only care about child elements
			if ( $child->nodeType !== XML_ELEMENT_NODE ) {
				continue;
			}

			switch ( $child->tagName ) {
				case 'wp:author_login':
					$data['user_login'] = $child->textContent;
					break;

				case 'wp:author_id':
					$data['ID'] = $child->textContent;
					break;

				case 'wp:author_email':
					$data['user_email'] = $child->textContent;
					break;

				case 'wp:author_display_name':
					$data['display_name'] = $child->textContent;
					break;

				case 'wp:author_first_name':
					$data['first_name'] = $child->textContent;
					break;

				case 'wp:author_last_name':
					$data['last_name'] = $child->textContent;
					break;
			}
		}

		return compact( 'data', 'meta' );
	}

	protected function process_author( $data, $meta ) {
		/**
		 * Pre-process user data.
		 *
		 * @param array $data User data. (Return empty to skip.)
		 * @param array $meta Meta data.
		 */
		$data = apply_filters( 'wxr_importer.pre_process.user', $data, $meta );
		if ( empty( $data ) ) {
			return false;
		}

		// Have we already handled this user?
		$original_id   = isset( $data['ID'] ) ? $data['ID'] : 0;
		$original_slug = $data['user_login'];

		if ( isset( $this->mapping['user'][ $original_id ] ) ) {
			$existing = $this->mapping['user'][ $original_id ];

			// Note the slug mapping if we need to too
			if ( ! isset( $this->mapping['user_slug'][ $original_slug ] ) ) {
				$this->mapping['user_slug'][ $original_slug ] = $existing;
			}

			return false;
		}

		if ( isset( $this->mapping['user_slug'][ $original_slug ] ) ) {
			$existing = $this->mapping['user_slug'][ $original_slug ];

			// Ensure we note the mapping too
			$this->mapping['user'][ $original_id ] = $existing;

			return false;
		}

		// Allow overriding the user's slug
		$login = $original_slug;
		if ( isset( $this->user_slug_override[ $login ] ) ) {
			$login = $this->user_slug_override[ $login ];
		}

		$userdata = array(
			'user_login' => sanitize_user( $login, true ),
			'user_pass'  => wp_generate_password(),
		);

		$allowed = array(
			'user_email'   => true,
			'display_name' => true,
			'first_name'   => true,
			'last_name'    => true,
		);
		foreach ( $data as $key => $value ) {
			if ( ! isset( $allowed[ $key ] ) ) {
				continue;
			}

			$userdata[ $key ] = $data[ $key ];
		}

		$user_id = wp_insert_user( wp_slash( $userdata ) );

		if ( is_wp_error( $user_id ) ) {

			if ( ! $user_id->errors['existing_user_login'] ) {
				$err_reason = sprintf(
					__( 'Failed to import user "%s"', 'grooni-theme-addons' ),
					$userdata['user_login']
				);
				$this->logger->error( $err_reason );
				$this->logger->debug( $user_id->get_error_message() );
				$this->store_import_info_error( $err_reason . ' ERR_MSG: ' . $user_id->get_error_message() );
			}
			/**
			 * User processing failed.
			 *
			 * @param WP_Error $user_id Error object.
			 * @param array $userdata Raw data imported for the user.
			 */
			do_action( 'wxr_importer.process_failed.user', $user_id, $userdata );

			return false;
		}

		if ( $original_id ) {
			$this->mapping['user'][ $original_id ] = $user_id;
		}
		$this->mapping['user_slug'][ $original_slug ] = $user_id;

		if ( WP_DEBUG ) {
			$this->logger->info( sprintf(
				__( 'Imported user "%s"', 'grooni-theme-addons' ),
				$userdata['user_login']
			) );
			$this->logger->debug( sprintf(
				__( 'User %d remapped to %d', 'grooni-theme-addons' ),
				$original_id,
				$user_id
			) );
		}

		/**
		 * User processing completed.
		 *
		 * @param int $user_id New user ID.
		 * @param array $userdata Raw data imported for the user.
		 */
		do_action( 'wxr_importer.processed.user', $user_id, $userdata );
	}

	protected function parse_term_node( $node, $type = 'term' ) {
		$data = array();
		$meta = array();

		$tag_name = array(
			'id'          => 'wp:term_id',
			'taxonomy'    => 'wp:term_taxonomy',
			'slug'        => 'wp:term_slug',
			/* note: WP 4.5+ exports the slug of the parent term, not the id */
			'parent_slug' => 'wp:term_parent',
			'name'        => 'wp:term_name',
			'description' => 'wp:term_description',
			'meta'        => 'wp:termmeta',
		);
		$taxonomy = null;

		// Special casing!
		switch ( $type ) {
			case 'category':
				$tag_name['slug']        = 'wp:category_nicename';
				$tag_name['parent_slug'] = 'wp:category_parent';
				$tag_name['name']        = 'wp:cat_name';
				$tag_name['description'] = 'wp:category_description';
				$tag_name['taxonomy']    = null;

				$data['taxonomy'] = 'category';
				break;

			case 'tag':
				$tag_name['slug']        = 'wp:tag_slug';
				$tag_name['parent_slug'] = null;
				$tag_name['name']        = 'wp:tag_name';
				$tag_name['description'] = 'wp:tag_description';
				$tag_name['taxonomy']    = null;

				$data['taxonomy'] = 'post_tag';
				break;
		}

		foreach ( $node->childNodes as $child ) {
			// We only care about child elements
			if ( $child->nodeType !== XML_ELEMENT_NODE ) {
				continue;
			}

			if ( $child->tagName == $tag_name['meta'] ) {
				$result = $this->parse_meta_node( $child );

				if ( ! empty( $result ) && isset( $result['key'] ) && isset( $result['value'] ) ) {
					$meta[] = array( 'key' => $result['key'], 'value' => $result['value'] );
				}
			}

			$key = array_search( $child->tagName, $tag_name );
			if ( $key ) {
				$data[ $key ] = $child->textContent;
			}
		}

		if ( empty( $data['taxonomy'] ) ) {
			return null;
		}

		// Compatibility with WXR 1.0
		if ( $data['taxonomy'] === 'tag' ) {
			$data['taxonomy'] = 'post_tag';
		}

		return compact( 'data', 'meta' );
	}

	protected function process_term( $data, $meta ) {
		/**
		 * Pre-process term data.
		 *
		 * @param array $data Term data. (Return empty to skip.)
		 * @param array $meta Meta data.
		 */
		$data = apply_filters( 'wxr_importer.pre_process.term', $data, $meta );
		if ( empty( $data ) ) {
			return false;
		}

		$original_id = isset( $data['id'] ) ? $data['id'] : 0;
		$term_slug   = isset( $data['slug'] ) ? $data['slug'] : '';

		/* As of WP 4.5, export.php returns the SLUG for the term's parent,
		 * rather than an integer ID (this differs from a post_parent)
		 * wp_insert_term and wp_update_term use the key: 'parent' and an integer value 'id'
		 * use both keys: 'parent' and 'parent_slug'
		 */
		$parent_slug = isset( $data['parent_slug'] ) ? $data['parent_slug'] : '';

		$mapping_key = sha1( $data['taxonomy'] . ':' . $data['slug'] );
		$existing    = $this->term_exists( $data );
		if ( $existing ) {
			$this->mapping['term'][ $mapping_key ]    = $existing;
			$this->mapping['term_id'][ $original_id ] = $existing;
			$this->mapping['term_slug'][ $term_slug ] = $existing;

			return false;
		}

		// WP really likes to repeat itself in export files
		if ( isset( $this->mapping['term'][ $mapping_key ] ) ) {
			return false;
		}

		$termdata = array();
		$allowed  = array(
			'slug'        => true,
			'description' => true,
			'parent'      => true,
		);

		// Map the parent term, or mark it as one we need to fix
		$requires_remapping = false;
		if ( $parent_slug ) {
			if ( isset( $this->mapping['term_slug'][ $parent_slug ] ) ) {
				$data['parent'] = $this->mapping['term_slug'][ $parent_slug ];
			} else {
				// Prepare for remapping later
				$meta[]             = array( 'key' => '_wxr_import_parent', 'value' => $parent_slug );
				$requires_remapping = true;

				// Wipe the parent for now
				$data['parent'] = 0;
			}
		}

		foreach ( $data as $key => $value ) {
			if ( ! isset( $allowed[ $key ] ) ) {
				continue;
			}

			$termdata[ $key ] = $data[ $key ];
		}

		$result = wp_insert_term( $data['name'], $data['taxonomy'], $termdata );

		if ( is_wp_error( $result ) ) {

			$err_reason = sprintf(
				__( 'Failed to import term:%s of taxonomy:%s', 'grooni-theme-addons' ),
				$data['name'],
				$data['taxonomy']
			);
			$this->logger->warning( $err_reason );
			$this->logger->debug( $result->get_error_message() );
			$this->store_import_info_error( $err_reason . ' ERR_MSG: ' . $result->get_error_message() );

			do_action( 'wp_import_insert_term_failed', $result, $data );

			/**
			 * Term processing failed.
			 *
			 * @param WP_Error $result Error object.
			 * @param array $data Raw data imported for the term.
			 * @param array $meta Meta data supplied for the term.
			 */
			do_action( 'wxr_importer.process_failed.term', $result, $data, $meta );

			return false;
		} else {
			$this->store_import_info( 'Adding Term: ' . $term_slug . ' of taxonomy: ' . $data['taxonomy'] );
		}

		$term_id = $result['term_id'];
		// now prepare to map this new term
		$this->mapping['term'][ $mapping_key ]    = $term_id;
		$this->mapping['term_id'][ $original_id ] = $term_id;
		$this->mapping['term_slug'][ $term_slug ] = $term_id;

		/* the parent will be updated later in post_process_terms
		 * we will need both the term_id AND the term_taxonomy to retrieve existing
		 * term attributes. Those attributes will be returned with the corrected parent,
		 * using wp_update_term.
		 * Pass both the term_id along with the term_taxonomy as key=>value
		 * in the requires_remapping['term'] array.
		 */
		if ( $requires_remapping ) {
			$this->requires_remapping['term'][ $term_id ] = $data['taxonomy'];
		}

		/* insert termmeta, if any, including the flag to remap the parent '_wxr_import_parent' */
		if ( ! empty( $meta ) ) {
			foreach ( $meta as $meta_item ) {
				$result = add_term_meta( $term_id, $meta_item['key'], $meta_item['value'] );
				if ( is_wp_error( $result ) ) {

					$this->logger->warning( sprintf(
						__( 'Failed to add metakey: %s, metavalue: %s to term_id: %d', 'grooni-theme-addons' ),
						$meta_item['key'], $meta_item['value'], $term_id ) );

					do_action( 'wxr_importer.process_failed.termmeta', $result, $data, $meta );
				} else {

					$this->logger->debug( sprintf(
						__( 'Meta for term_id %d : %s => %s ; successfully added!', 'grooni-theme-addons' ),
						$term_id, $meta_item['key'], $meta_item['value'] ) );

				}
			}
		}


		$this->logger->info( sprintf(
			__( 'Imported "%s" (%s)', 'grooni-theme-addons' ),
			$data['name'],
			$data['taxonomy']
		) );
		$this->logger->debug( sprintf(
			__( 'Term %d remapped to %d', 'grooni-theme-addons' ),
			$original_id,
			$term_id
		) );


		do_action( 'wp_import_insert_term', $term_id, $data );

		/**
		 * Term processing completed.
		 *
		 * @param int $term_id New term ID.
		 * @param array $data Raw data imported for the term.
		 */
		do_action( 'wxr_importer.processed.term', $term_id, $data );
	}

	/**
	 * Process and import term meta items.
	 *
	 * @param array $meta List of meta data arrays
	 * @param int $term_id Term ID to associate with
	 * @param array $term Term data
	 *
	 * @return int|WP_Error Number of meta items imported on success, error otherwise.
	 */
	protected function process_term_meta( $meta, $term_id, $term ) {
		if ( empty( $meta ) ) {
			return true;
		}

		foreach ( $meta as $meta_item ) {
			/**
			 * Pre-process term meta data.
			 *
			 * @param array $meta_item Meta data. (Return empty to skip.)
			 * @param int $term_id Term the meta is attached to.
			 */
			$meta_item = apply_filters( 'wxr_importer.pre_process.term_meta', $meta_item, $term_id );
			if ( empty( $meta_item ) ) {
				return false;
			}

			$key   = apply_filters( 'import_term_meta_key', $meta_item['key'], $term_id, $term );
			$value = false;
			if ( $key ) {
				// export gets meta straight from the DB so could have a serialized string
				if ( ! $value ) {
					$value = maybe_unserialize( $meta_item['value'] );
				}

				add_term_meta( $term_id, $key, $value );
				do_action( 'import_term_meta', $term_id, $key, $value );
			}
		}

		return true;
	}


	/**
	 * Attempt to create a new menu item from import data
	 *
	 * Fails for draft, orphaned menu items and those without an associated nav_menu
	 * or an invalid nav_menu term. If the post type or term object which the menu item
	 * represents doesn't exist then the menu item will not be imported (waits until the
	 * end of the import to retry again before discarding).
	 *
	 * @param $post_id
	 * @param $data
	 * @param $meta
	 */
	protected function process_menu_item_meta( $post_id, $data, $meta ) {

		$item_type          = get_post_meta( $post_id, '_menu_item_type', true );
		$original_object_id = get_post_meta( $post_id, '_menu_item_object_id', true );
		$object_id          = null;

		if ( WP_DEBUG ) {
			$this->logger->debug( sprintf( 'Processing menu item %s', $item_type ) );
		}

		$requires_remapping = false;
		switch ( $item_type ) {
			case 'taxonomy':
				if ( isset( $this->mapping['term_id'][ $original_object_id ] ) ) {
					$object_id = $this->mapping['term_id'][ $original_object_id ];
				} else {
					add_post_meta( $post_id, '_wxr_import_menu_item', wp_slash( $original_object_id ) );
					$requires_remapping = true;
				}
				break;

			case 'post_type':
				if ( isset( $this->mapping['post'][ $original_object_id ] ) ) {
					$object_id = $this->mapping['post'][ $original_object_id ];
				} else {
					add_post_meta( $post_id, '_wxr_import_menu_item', wp_slash( $original_object_id ) );
					$requires_remapping = true;
				}
				break;

			case 'custom':
				// Custom refers to itself, wonderfully easy.
				$object_id = $post_id;
				break;

			default:
				// associated object is missing or not imported yet, we'll retry later
				//$this->missing_menu_items[] = $original_object_id;

				if ( WP_DEBUG ) {
					$this->logger->debug( sprintf( 'Unknown menu item type. [id# %s]', $post_id ) );
				}
				break;
		}

		if ( $requires_remapping ) {
			$this->requires_remapping['post'][ $post_id ] = true;
		}

		if ( empty( $object_id ) ) {
			// Nothing needed here.
			return;
		}

		if ( WP_DEBUG ) {
			$this->logger->debug( sprintf( 'Menu item %d mapped to %d', $original_object_id, $object_id ) );
		}
		update_post_meta( $post_id, '_menu_item_object_id', wp_slash( $object_id ) );

		do_action( 'import_menu_item_meta_new', $post_id );
	}

	/**
	 * If fetching attachments is enabled then attempt to create a new attachment
	 *
	 * @param array $post Attachment post details from WXR
	 * @param string $url URL to fetch attachment from
	 *
	 * @return int|WP_Error Post ID on success, WP_Error otherwise
	 */
	protected function process_attachment( $post, $meta, $remote_url ) {

		$this->imageCount ++;
		$attachment_metadata = array();

		// try to use _wp_attached file for upload folder placement to ensure the same location as the export site
		// e.g. location is 2003/05/image.jpg but the attachment post_date is 2010/09, see media_handle_upload()
		$post['upload_date'] = $post['post_date'];
		foreach ( $meta as $meta_item ) {
			if ( $meta_item['key'] === '_wp_attached_file' ) {
				if ( preg_match( '%^[0-9]{4}/[0-9]{2}%', $meta_item['value'], $matches ) ) {
					$post['upload_date'] = $matches[0];
				}

			}
			if ( $meta_item['key'] === '_wp_attachment_metadata' ) {
				$attachment_metadata = maybe_unserialize( $meta_item['value'] );
				if ( ! is_array( $attachment_metadata ) ) {
					$attachment_metadata = array();
				}
			}
		}

		// if the URL is absolute, but does not contain address, then upload it assuming base_site_url
		if ( preg_match( '|^/[\w\W]+$|', $remote_url ) ) {
			$remote_url = rtrim( $this->base_url, '/' ) . $remote_url;
		}

		// get attachments in media package. If it is not exist, download remote file
		$_urlxz = explode( 'wp-content', $remote_url );
		$_urlxc = ABSPATH . 'wp-content' . $_urlxz[1];

		if ( file_exists( $_urlxc ) ) {
			$upload = array(
				'file' => $_urlxc,
				'url'  => GROONI_THEME_ADDONS_SITE_URI . '/wp-content' . $_urlxz[1],
			);

		} else {
			$upload = $this->fetch_remote_file( $remote_url, $post );
		}


		if ( is_wp_error( $upload ) ) {

			if ( $this->options['import_all_demo_data'] ) {
				if ( WP_DEBUG || ( isset( $_GET['debug'] ) && $_GET['debug'] == 'true' ) ) {
					$this->logger->debug( $upload->get_error_message() );
					$this->store_import_info_error( 'File upload error: ' . $upload->get_error_message() );
				}
			}

			return $upload;
		}

		$info = wp_check_filetype( $upload['file'] );
		if ( ! $info ) {
			return new WP_Error( 'attachment_processing_error', __( 'Invalid file type', 'grooni-theme-addons' ) );
		}

		$post['post_mime_type'] = $info['type'];

		// WP really likes using the GUID for display. Allow updating it.
		// See https://core.trac.wordpress.org/ticket/33386
		if ( $this->options['update_attachment_guids'] ) {
			$post['guid'] = $upload['url'];
		}

		// as per wp-admin/includes/upload.php
		$post_id = wp_insert_attachment( $post, $upload['file'] );

		if ( $this->options['generate_thumb'] ) {
			wp_update_attachment_metadata( $post_id, wp_generate_attachment_metadata( $post_id, $upload['file'] ) );

		} else {
			wp_update_attachment_metadata( $post_id, $attachment_metadata );
		}

		if ( is_wp_error( $post_id ) ) {
			return $post_id;
		}

		// Map this image URL later if we need to
		$this->url_remap[ $remote_url ] = $upload['url'];

		// If we have a HTTPS URL, ensure the HTTP URL gets replaced too
		if ( substr( $remote_url, 0, 8 ) === 'https://' ) {
			$insecure_url                     = 'http' . substr( $remote_url, 5 );
			$this->url_remap[ $insecure_url ] = $upload['url'];
		}

		if ( $this->options['aggressive_url_search'] ) {
			// remap resized image URLs, works by stripping the extension and remapping the URL stub.
			/*if ( preg_match( '!^image/!', $info['type'] ) ) {
				$parts = pathinfo( $remote_url );
				$name = basename( $parts['basename'], ".{$parts['extension']}" ); // PATHINFO_FILENAME in PHP 5.2

				$parts_new = pathinfo( $upload['url'] );
				$name_new = basename( $parts_new['basename'], ".{$parts_new['extension']}" );

				$this->url_remap[$parts['dirname'] . '/' . $name] = $parts_new['dirname'] . '/' . $name_new;
			}*/
		}

		return $post_id;
	}


	/**
	 * Attempt to download a remote file attachment
	 *
	 * @param string $url URL of item to fetch
	 * @param array $post Attachment details
	 *
	 * @return array|WP_Error Local file location details on success, WP_Error otherwise
	 */
	protected function fetch_remote_file( $url, $post ) {
		// extract the file name and extension from the url
		$file_name = basename( $url );

		// get placeholder file in the upload dir with a unique, sanitized filename
		$upload = wp_upload_bits( $file_name, 0, '', $post['upload_date'] );
		if ( $upload['error'] ) {
			return new WP_Error( 'upload_dir_error', $upload['error'] );
		}

		if ( $this->options['import_all_demo_data'] ) {
			if ( WP_DEBUG || ( isset( $_GET['debug'] ) && $_GET['debug'] == 'true' ) ) {
				var_dump( $url );
			}
		}

		// fetch the remote url and write it to the placeholder file
		$response = wp_remote_get( $url, array(
			'stream'   => true,
			'filename' => $upload['file'],
		) );

		// request failed
		if ( is_wp_error( $response ) ) {
			unlink( $upload['file'] );

			return $response;
		}

		$code = (int) wp_remote_retrieve_response_code( $response );

		// make sure the fetch was successful
		if ( $code !== 200 ) {
			unlink( $upload['file'] );

			return new WP_Error(
				'import_file_error',
				sprintf(
					__( 'Remote server returned %1$d %2$s for %3$s', 'grooni-theme-addons' ),
					$code,
					get_status_header_desc( $code ),
					$url
				)
			);
		}

		$filesize = filesize( $upload['file'] );
		$headers  = wp_remote_retrieve_headers( $response );

		if ( isset( $headers['content-length'] ) && $filesize !== (int) $headers['content-length'] ) {
			@unlink( $upload['file'] );

			return new WP_Error( 'import_file_error', __( 'Remote file is incorrect size', 'grooni-theme-addons' ) );
		}

		if ( 0 === $filesize ) {
			@unlink( $upload['file'] );

			return new WP_Error( 'import_file_error', __( 'Zero size file downloaded', 'grooni-theme-addons' ) );
		}

		$max_size = (int) $this->max_attachment_size();
		if ( ! empty( $max_size ) && $filesize > $max_size ) {
			@unlink( $upload['file'] );
			$message = sprintf( __( 'Remote file is too large, limit is %s', 'grooni-theme-addons' ), size_format( $max_size ) );

			return new WP_Error( 'import_file_error', $message );
		}

		return $upload;
	}


	function check_writeable() {

		ob_start();

		$passed = true;

		if ( ! is_writable( $this->get_assets_data( 'content_path' ) ) ) {
			$passed = false;
		}

		$notice = ob_get_contents();
		ob_end_clean();

		if ( $passed === false ) {
			print ( $notice );
		}

		return $passed;
	}


	/**
	 * Download the media package
	 *
	 * @param string $_tmppath
	 * @param string $preset_name
	 */
	function download_package( $_tmppath, $preset_name = '' ) {

		if ( ! defined( 'FS_METHOD' ) ) {
			define( 'FS_METHOD', 'direct' );
		}

		global $wp_filesystem;
		if ( empty( $wp_filesystem ) ) {
			if ( file_exists( ABSPATH . '/wp-admin/includes/file.php' ) ) {
				require_once ABSPATH . '/wp-admin/includes/file.php';
				WP_Filesystem();
			}
		}
		if ( empty( $wp_filesystem ) ) {
			$this->store_import_info( 'WP_Filesystem() load library error', '', 'critical_error' );

			@ob_clean();

			wp_send_json( array(
				'status'  => 'critical_error',
				'message' => esc_html__( 'WP_Filesystem() load library error', 'grooni-theme-addons' )
			), 500 );
		}


		$package = null;


		$demo_package = apply_filters( 'grooni_addons_import_demos', array() );

		if ( ! empty( $preset_name ) && isset( $demo_package[ $this->demo ]['presets_url'] ) ) {
			$url = $demo_package[ $this->demo ]['presets_url'] . $preset_name . '.zip';
		} elseif ( isset( $demo_package[ $this->demo ]['url'] ) ) {
			$url = $demo_package[ $this->demo ]['url'];
		}

		if ( empty( $url ) ) {
			$this->store_import_info( 'Can not download assets. URL not set by theme config.', '', 'critical_error' );
			@ob_clean();
			wp_send_json( array(
				'status'  => 'critical_error',
				'message' => esc_html__( 'Can not download assets. URL not set by theme config.', 'grooni-theme-addons' )
			), 500 );
		}

		$this->store_import_info( 'Downloading assets package...', '0' );

		// create temp folder
		$_tmp = wp_tempnam( $url );
		@unlink( $_tmp );

		if ( add_option( 'grooni_addons_download_tmp_package', $_tmp ) === false ) {
			update_option( 'grooni_addons_download_tmp_package', $_tmp );
		}

		@ob_flush();

		$package = download_url( $url, 18000 );

		if ( ! is_dir( $_tmppath ) ) {
			@mkdir( $_tmppath, 0755 );
		}

		if ( ! is_wp_error( $package ) || ! is_dir( $_tmppath ) ) {

			$unzip = unzip_file( $package, $_tmppath );

			if ( is_wp_error( $unzip ) ) {

				$this->store_import_info( sprintf( 'ERROR %s. Could not extract demo media package. Please contact our support staff.', $unzip->get_error_code() ), '', 'critical_error' );

				@ob_clean();
				wp_send_json( array(
					'status'  => 'critical_error',
					'message' => sprintf( __( 'ERROR %s. Could not extract demo media package. Please contact our support staff.', 'grooni-theme-addons' ), $unzip->get_error_code() )
				), 500 );

			}

			@unlink( $package );
			delete_option( 'grooni_addons_download_tmp_package' );

			if ( ! empty( $preset_name ) ) {
				$this->store_import_info( 'The preset package is downloaded.' );
			} else {
				$this->store_import_info( 'The assets package is downloaded. We are preparing to extract the archive...' );
			}


			@unlink( $package );


		} else {

			@ob_clean();

			$this->store_import_info( sprintf( 'ERROR %s. Demo media package is not download. Please contact our support staff.', $package->get_error_code() ), '', 'critical_error' );

			wp_send_json( array(
				'status'  => 'critical_error',
				'message' => sprintf( __( 'ERROR %s. Demo media package is not download. Please contact our support staff.', 'grooni-theme-addons' ), $package->get_error_code() ),
				'data'    => array(
					'wp_doing_ajax' => wp_doing_ajax(),
				)
			), 500 );

		}

		delete_option( 'grooni_addons_download_tmp_package' );

	}

	public function download_and_install_assets() {
		if ( ! $this->check_writeable() ) {

			$this->store_import_info( sprintf( 'Could not write demo package files into directory: %s', str_replace( '\\', '/', $this->get_assets_data( 'content_path' ) ) ), '', 'critical_error' );

			@ob_clean();
			wp_send_json( array(
				'status'  => 'critical_error',
				'message' => sprintf( __( 'Could not write demo package files into directory: %s . Check folder, we need write permission.', 'grooni-theme-addons' ), str_replace( '\\', '/', $this->get_assets_data( 'content_path' ) ) )
			), 500 );

		}

		$this->check_limits();

		@ob_implicit_flush();

		delete_option( 'grooni_addons_download_tmp_package' );

		// Start download assets
		$this->download_package( $this->get_assets_data( 'tmp_path' ) );

		//  Unpack and finish assets
		$this->unpackage( $this->get_assets_data( 'content_path' ), $this->get_assets_data( 'path' ) );

		if ( $this->options['import_all_demo_data'] ) {
			$this->store_import_info( 'Preparing for add media...', '0' );
		}

		@ob_flush();
		@flush();
	}


	/**
	 * Unpack the media package
	 *
	 * @param $_content_path
	 * @param $_path
	 *
	 * @return bool
	 */
	function unpackage( $_content_path, $_path ) {

		if ( is_dir( $_path ) ) {

			$_current = $this->list_files( $_content_path );
			$_new     = $this->list_files( $_path . 'uploads' );

			foreach ( $_current as $key => $value ) {
				if ( isset( $_new[ $key ] ) ) {
					unset( $_new[ $key ] );
				}
			}

			foreach ( $_new as $key => $value ) {

				if ( $value == 4 ) {
					@mkdir( $_content_path . urldecode( $key ), 0755 );
				} else if ( strpos( $key, '.DS_Store' ) === false ) {

					@copy( $_path . 'uploads/' . urldecode( $key ), $_content_path . '/' . urldecode( $key ) );

					@flush();
					@ob_flush();
				}

			}

		} else {

			$this->store_import_info( sprintf( 'ERROR %s. Temporary folder is not found. Please contact our support staff.', 'temp_dir_not_found' ), '', 'critical_error' );

			@ob_clean();
			wp_send_json( array(
				'status'  => 'critical_error',
				'message' => sprintf( __( 'ERROR %s. Temporary folder is not found. Please contact our support staff.', 'grooni-theme-addons' ), 'temp_dir_not_found' )
			), 500 );

		}
	}


	/**
	 * Download and unpackage preset by name
	 *
	 * @param $preset_name
	 */
	public function download_preset( $preset_name ) {

		if ( empty( $preset_name ) ) {
			$this->store_import_info( 'Set empty preset', '', 'alert' );

			@ob_clean();
			wp_send_json( array(
				'status'  => 'alert',
				'message' => 'Set empty preset'
			) );
		}


		if ( ! $this->check_writeable() ) {

			$this->store_import_info( sprintf( 'Could not write preset files into directory: %s', str_replace( '\\', '/', $this->get_assets_data( 'content_path' ) ) ), '', 'critical_error' );

			@ob_clean();
			wp_send_json( array(
				'status'  => 'critical_error',
				'message' => sprintf( __( 'Could not write preset files into directory: %s . Check folder, we need write permission.', 'grooni-theme-addons' ), str_replace( '\\', '/', $this->get_assets_data( 'content_path' ) ) )
			), 500 );

		}

		$this->check_limits();

		@ob_implicit_flush();

		delete_option( 'grooni_addons_download_tmp_package' );

		// Start download assets
		$this->download_package( $this->get_assets_data( 'tmp_path' ), $preset_name );

		@ob_flush();
		@flush();
	}


	/**
	 * Remove temporary unpacked dir with files
	 *
	 * @param $_tmppath
	 */
	function delete_tmp_unpackege_dir( $_tmppath ) {

		if ( ! is_dir( $_tmppath ) ) {
			return;
		}

		// Remove temp unpacked files
		$tmp_dir_i   = new RecursiveDirectoryIterator( $_tmppath, RecursiveDirectoryIterator::SKIP_DOTS );
		$tmp_files_i = new RecursiveIteratorIterator( $tmp_dir_i, RecursiveIteratorIterator::CHILD_FIRST );
		foreach ( $tmp_files_i as $file ) {
			if ( $file->isDir() ) {
				rmdir( $file->getPathname() );
			} elseif ( $file->isFile() ) {
				unlink( $file->getPathname() );
			}
		}
		rmdir( $_tmppath );
	}


	/**
	 * List all files in downloaded folder
	 *
	 * @param      $dir
	 * @param null $DF
	 *
	 * @return array
	 */
	function list_files( $dir, $DF = null ) {

		if ( $DF == null ) {
			$DF = $dir;
		}

		$stack = array();

		if ( is_dir( $dir ) ) {
			$dh = opendir( $dir );
			while ( false !== ( $file = @readdir( $dh ) ) ) {

				$path = $dir . '/' . $file;

				if ( $file == '.DS_Store' ) {
					unlink( $dir . '/' . $file );
				} else if ( is_file( $path ) ) {

					$stack[ urlencode( str_replace( $DF . '/', '', $path ) ) ] = 1;

				} else if ( is_dir( $path ) && $file != '.' && $file != '..' && $file != 'grooni-demo-presets' ) {

					$stack[ urlencode( str_replace( $DF . '/', '', $path ) ) ] = 4;

					$stack = $stack + self::list_files( $dir . '/' . $file, $DF );
				}
			}

		}

		return $stack;
	}


	protected function post_process() {
		// Time to tackle any left-over bits
		if ( ! empty( $this->requires_remapping['post'] ) ) {
			$this->post_process_posts( $this->requires_remapping['post'] );
		}
		if ( ! empty( $this->requires_remapping['comment'] ) ) {
			$this->post_process_comments( $this->requires_remapping['comment'] );
		}
		if ( ! empty( $this->requires_remapping['term'] ) ) {
			$this->post_process_terms( $this->requires_remapping['term'] );
		}
	}

	protected function post_process_posts( $todo ) {
		foreach ( $todo as $post_id => $_ ) {

			if ( WP_DEBUG ) {
				$this->logger->debug( sprintf(
				// Note: title intentionally not used to skip extra processing
				// for when debug logging is off
					__( 'Running post-processing for post %d', 'grooni-theme-addons' ),
					$post_id
				) );
			}

			$data = array();

			$parent_id = get_post_meta( $post_id, '_wxr_import_parent', true );
			if ( ! empty( $parent_id ) ) {
				// Have we imported the parent now?
				if ( isset( $this->mapping['post'][ $parent_id ] ) ) {
					$data['post_parent'] = $this->mapping['post'][ $parent_id ];
				} else {
					if ( WP_DEBUG ) {
						$this->logger->warning( sprintf(
							__( 'Could not find the post parent for "%s" [post_id: #%d]', 'grooni-theme-addons' ),
							get_the_title( $post_id ),
							$post_id
						) );
						$this->logger->debug( sprintf(
							__( 'Post %d was imported with parent %d, but could not be found', 'grooni-theme-addons' ),
							$post_id,
							$parent_id
						) );
					}
				}
			}

			$author_slug = get_post_meta( $post_id, '_wxr_import_user_slug', true );

			if ( ! empty( $author_slug ) ) {
				// Have we imported the user now?
				if ( isset( $this->mapping['user_slug'][ $author_slug ] ) ) {
					$data['post_author'] = $this->mapping['user_slug'][ $author_slug ];
				} else {
					if ( WP_DEBUG ) {
						$this->logger->warning( sprintf(
							__( '[post_id: #%d] Could not find the author for "%s"', 'grooni-theme-addons' ),
							get_the_title( $post_id ),
							$post_id
						) );
						$this->logger->debug( sprintf(
							__( 'Post %d was imported with author "%s", but could not be found', 'grooni-theme-addons' ),
							$post_id,
							$author_slug
						) );
					}
				}
			}

			$has_attachments = get_post_meta( $post_id, '_wxr_import_has_attachment_refs', true );
			if ( ! empty( $has_attachments ) ) {
				$post    = get_post( $post_id );
				$content = $post->post_content;

				// Replace all the URLs we've got
				$new_content = str_replace( array_keys( $this->url_remap ), $this->url_remap, $content );
				if ( $new_content !== $content ) {
					$data['post_content'] = $new_content;
				}
			}

			if ( get_post_type( $post_id ) === 'nav_menu_item' ) {
				$this->post_process_menu_item( $post_id );
			}

			// Do we have updates to make?
			if ( empty( $data ) ) {
				if ( WP_DEBUG ) {
					$this->logger->debug( sprintf(
						__( 'Post %d was marked for post-processing, but none was required.', 'grooni-theme-addons' ),
						$post_id
					) );
				}
				continue;
			}

			// Run the update
			$data['ID'] = $post_id;
			$result     = wp_update_post( $data, true );
			if ( is_wp_error( $result ) ) {
				if ( WP_DEBUG ) {
					$this->logger->warning( sprintf(
						__( '[post_id: #%d] Could not update "%s" with mapped data', 'grooni-theme-addons' ),
						get_the_title( $post_id ),
						$post_id
					) );
					$this->logger->debug( $result->get_error_message() );
				}
				continue;
			}

			// Clear out our temporary meta keys
			delete_post_meta( $post_id, '_wxr_import_parent' );
			delete_post_meta( $post_id, '_wxr_import_user_slug' );
			delete_post_meta( $post_id, '_wxr_import_has_attachment_refs' );
		}
	}

	protected function post_process_menu_item( $post_id ) {
		$menu_object_id = get_post_meta( $post_id, '_wxr_import_menu_item', true );

		if ( empty( $menu_object_id ) ) {
			// No processing needed!
			return;
		}

		$menu_item_type   = get_post_meta( $post_id, '_menu_item_type', true );
		$menu_item_object = get_post_meta( $post_id, '_menu_item_object', true );

		$menu_object = null;

		switch ( $menu_item_type ) {
			case 'taxonomy':
				if ( isset( $this->mapping['term_id'][ $menu_object_id ] ) ) {
					$menu_object = $this->mapping['term_id'][ $menu_object_id ];
				} else {

					$taxonomies = json_decode( $this->get_data( 'taxonomies', 'json', false ), true );
					if ( ! empty( $taxonomies[ $menu_item_object ] ) && isset( $taxonomies[ $menu_item_object ][ $menu_object_id ] ) ) {
						$term_data = $taxonomies[ $menu_item_object ][ $menu_object_id ];

						$_exist_term = get_term_by( 'slug', $term_data['slug'], $menu_item_object );

						if ( ! empty( $_exist_term->term_id ) ) {
							$menu_object = $_exist_term->term_id;
						}
					}

				}
				break;

			case 'post_type':
				if ( isset( $this->mapping['post'][ $menu_object_id ] ) ) {
					$menu_object = $this->mapping['post'][ $menu_object_id ];
				}
				break;

			default:
				// Cannot handle this.
				return;
		}

		if ( ! empty( $menu_object ) ) {
			update_post_meta( $post_id, '_menu_item_object_id', wp_slash( $menu_object ) );
		} else {
			if ( WP_DEBUG ) {
				$this->logger->warning( sprintf(
					__( '[post_id: #%d] Could not find the menu object for "%s"', 'grooni-theme-addons' ),
					get_the_title( $post_id ),
					$post_id
				) );
				$this->logger->debug( sprintf(
					__( 'Post %d was imported with object "%d" of type "%s", but could not be found', 'grooni-theme-addons' ),
					$post_id,
					$menu_object_id,
					$menu_item_type
				) );
			}
		}

		delete_post_meta( $post_id, '_wxr_import_menu_item' );
	}

	protected function post_process_terms( $terms_to_be_remapped ) {

		/* There is no explicit 'top' or 'root' for a hierarchy of WordPress terms
		  * Terms without a parent, or parent=0 are either unconnected (orphans)
		  * or top-level siblings without an explicit root parent
		  * An unconnected term (orphan) should have a null parent_slug
		  * Top-level siblings without an explicit root parent, shall be identified
		  * with the parent_slug: top
		  * [we'll map parent_slug: top into parent 0]
		  */
		$this->mapping['term_slug']['top'] = 0;

		// the term_id and term_taxonomy are passed-in with $this->requires_remapping['term']
		foreach ( $terms_to_be_remapped as $termid => $term_taxonomy ) {
			// basic check
			if ( empty( $termid ) or ! ( is_numeric( $termid ) ) ) {
				if ( WP_DEBUG ) {
					$this->logger->warning( sprintf(
						__( 'Faulty term_id provided in terms-to-be-remapped array %s', 'grooni-theme-addons' ),
						$termid
					) );
				}
				continue;
			}
			/* this cast to integer may be unnecessary */
			$term_id = (int) $termid;

			if ( empty( $term_taxonomy ) ) {
				if ( WP_DEBUG ) {
					$this->logger->warning( sprintf(
						__( 'No taxonomy provided in terms-to-be-remapped array for term #%d', 'grooni-theme-addons' ),
						$term_id
					) );
				}
				continue;
			}

			$parent_slug = get_term_meta( $term_id, '_wxr_import_parent', true );

			if ( empty( $parent_slug ) ) {
				if ( WP_DEBUG ) {
					$this->logger->warning( sprintf(
						__( 'No parent_slug identified in remapping-array for term: %d', 'grooni-theme-addons' ),
						$term_id
					) );
				}
				continue;
			}

			if ( ! isset( $this->mapping['term_slug'][ $parent_slug ] ) or ! is_numeric( $this->mapping['term_slug'][ $parent_slug ] ) ) {
				if ( WP_DEBUG ) {
					$this->logger->warning( sprintf(
						__( 'The term(%d)"s parent_slug (%s) is not found in the remapping-array.', 'grooni-theme-addons' ),
						$term_id,
						$parent_slug
					) );
				}
				continue;
			}

			$mapped_parent = (int) $this->mapping['term_slug'][ $parent_slug ];

			$termattributes = get_term_by( 'id', $term_id, $term_taxonomy, ARRAY_A );
			// note: the default OBJECT return results in a reserved-word clash with 'parent' [$termattributes->parent], so instead return an associative array

			if ( empty( $termattributes ) ) {
				if ( WP_DEBUG ) {
					$this->logger->warning( sprintf(
						__( 'No data returned by get_term_by for term_id #%d', 'grooni-theme-addons' ),
						$term_id
					) );
				}
				continue;
			}
			// check if the correct parent id is already correctly mapped
			if ( isset( $termattributes['parent'] ) && $termattributes['parent'] == $mapped_parent && WP_DEBUG ) {
				// Clear out our temporary meta key
				delete_term_meta( $term_id, '_wxr_import_parent' );
				continue;
			}

			// otherwise set the mapped parent and update the term
			$termattributes['parent'] = $mapped_parent;

			$result = wp_update_term( $term_id, $termattributes['taxonomy'], $termattributes );

			if ( is_wp_error( $result ) ) {
				if ( WP_DEBUG ) {
					$this->logger->warning( sprintf(
						__( 'Could not update "%s" (term #%d) with mapped data', 'grooni-theme-addons' ),
						$termattributes['name'],
						$term_id
					) );
					$this->logger->debug( $result->get_error_message() );
				}
				continue;
			}
			// Clear out our temporary meta key
			delete_term_meta( $term_id, '_wxr_import_parent' );

			if ( WP_DEBUG ) {
				$this->logger->debug( sprintf(
					__( 'Term %d was successfully updated with parent %d', 'grooni-theme-addons' ),
					$term_id,
					$mapped_parent
				) );
			}
		}
	}

	protected function post_process_comments( $todo ) {
		foreach ( $todo as $comment_id => $_ ) {
			$data = array();

			$parent_id = get_comment_meta( $comment_id, '_wxr_import_parent', true );
			if ( ! empty( $parent_id ) ) {
				// Have we imported the parent now?
				if ( isset( $this->mapping['comment'][ $parent_id ] ) ) {
					$data['comment_parent'] = $this->mapping['comment'][ $parent_id ];
				} else {
					if ( WP_DEBUG ) {
						$this->logger->warning( sprintf(
							__( 'Could not find the comment parent for comment #%d', 'grooni-theme-addons' ),
							$comment_id
						) );
						$this->logger->debug( sprintf(
							__( 'Comment %d was imported with parent %d, but could not be found', 'grooni-theme-addons' ),
							$comment_id,
							$parent_id
						) );
					}
				}
			}

			$author_id = get_comment_meta( $comment_id, '_wxr_import_user', true );
			if ( ! empty( $author_id ) && WP_DEBUG ) {
				// Have we imported the user now?
				if ( isset( $this->mapping['user'][ $author_id ] ) ) {
					$data['user_id'] = $this->mapping['user'][ $author_id ];
				} else {
					if ( WP_DEBUG ) {
						$this->logger->warning( sprintf(
							__( 'Could not find the author for comment #%d', 'grooni-theme-addons' ),
							$comment_id
						) );
						$this->logger->debug( sprintf(
							__( 'Comment %d was imported with author %d, but could not be found', 'grooni-theme-addons' ),
							$comment_id,
							$author_id
						) );
					}
				}
			}

			// Do we have updates to make?
			if ( empty( $data ) ) {
				continue;
			}

			// Run the update
			$data['comment_ID'] = $comment_id;
			$result             = wp_update_comment( wp_slash( $data ) );
			if ( empty( $result ) ) {
				if ( WP_DEBUG ) {
					$this->logger->warning( sprintf(
						__( 'Could not update comment #%d with mapped data', 'grooni-theme-addons' ),
						$comment_id
					) );
				}
				continue;
			}

			// Clear out our temporary meta keys
			delete_comment_meta( $comment_id, '_wxr_import_parent' );
			delete_comment_meta( $comment_id, '_wxr_import_user' );
		}
	}

	/**
	 * Use stored mapping information to update old attachment URLs
	 */
	protected function replace_attachment_urls_in_content() {
		global $wpdb;
		// make sure we do the longest urls first, in case one is a substring of another
		uksort( $this->url_remap, array( $this, 'cmpr_strlen' ) );

		foreach ( $this->url_remap as $from_url => $to_url ) {
			// remap urls in post_content
			$query = $wpdb->prepare( "UPDATE {$wpdb->posts} SET post_content = REPLACE(post_content, %s, %s)", $from_url, $to_url );
			$wpdb->query( $query );

			// remap enclosure urls
			$query  = $wpdb->prepare( "UPDATE {$wpdb->postmeta} SET meta_value = REPLACE(meta_value, %s, %s) WHERE meta_key='enclosure'", $from_url, $to_url );
			$result = $wpdb->query( $query );
		}
	}

	/**
	 * Update _thumbnail_id meta to new, imported attachment IDs
	 */
	function remap_featured_images() {
		// cycle through posts that have a featured image
		foreach ( $this->featured_images as $post_id => $value ) {
			if ( isset( $this->processed_posts[ $value ] ) ) {
				$new_id = $this->processed_posts[ $value ];

				// only update if there's a difference
				if ( $new_id !== $value ) {
					update_post_meta( $post_id, '_thumbnail_id', $new_id );
				}
			}
		}
	}

	/**
	 * Decide if the given meta key maps to information we will want to import
	 *
	 * @param string $key The meta key to check
	 *
	 * @return string|bool The key if we do want to import, false if not
	 */
	public function is_valid_meta_key( $key ) {
		// skip attachment metadata since we'll regenerate it from scratch
		// skip _edit_lock as not relevant for import
		if ( in_array( $key, array( '_wp_attached_file', '_wp_attachment_metadata', '_edit_lock' ) ) ) {
			return false;
		}

		return $key;
	}

	/**
	 * Decide what the maximum file size for downloaded attachments is.
	 * Default is 0 (unlimited), can be filtered via import_attachment_size_limit
	 *
	 * @return int Maximum attachment file size to import
	 */
	protected function max_attachment_size() {
		return apply_filters( 'import_attachment_size_limit', 0 );
	}

	/**
	 * Added to http_request_timeout filter to force timeout at 60 seconds during import
	 *
	 * @access protected
	 * @return int 60
	 */
	function bump_request_timeout( $val ) {
		return 60;
	}

// return the difference in length between two strings
	function cmpr_strlen( $a, $b ) {
		return strlen( $b ) - strlen( $a );
	}

	/**
	 * Prefill existing post data.
	 *
	 * This preloads all GUIDs into memory, allowing us to avoid hitting the
	 * database when we need to check for existence. With larger imports, this
	 * becomes prohibitively slow to perform SELECT queries on each.
	 *
	 * By preloading all this data into memory, it's a constant-time lookup in
	 * PHP instead. However, this does use a lot more memory, so for sites doing
	 * small imports onto a large site, it may be a better tradeoff to use
	 * on-the-fly checking instead.
	 */
	protected function prefill_existing_posts() {
		global $wpdb;
		$posts = $wpdb->get_results( "SELECT ID, guid FROM {$wpdb->posts}" );

		foreach ( $posts as $item ) {
			$this->exists['post'][ $item->guid ] = $item->ID;
		}
	}

	/**
	 * Does the post exist?
	 *
	 * @param array $data Post data to check against.
	 *
	 * @return int|bool Existing post ID if it exists, false otherwise.
	 */
	protected function post_exists( $data ) {

		if ( $data['post_type'] == 'product_variation' ) {
			return post_exists( $data['post_title'], $data['post_content'], $data['post_date'] );
		}

		if ( $data['post_type'] == 'nav_menu_item' ) {
			return false;
		}

		// Constant-time lookup if we prefilled
		$exists_key = $data['guid'];

		if ( $this->options['prefill_existing_posts'] ) {
			return isset( $this->exists['post'][ $exists_key ] ) ? $this->exists['post'][ $exists_key ] : false;
		}

		// No prefilling, but might have already handled it
		if ( isset( $this->exists['post'][ $exists_key ] ) ) {
			return $this->exists['post'][ $exists_key ];
		}

		// Still nothing, try post_exists, and cache it
		$exists                              = post_exists( $data['post_title'], $data['post_content'], $data['post_date'] );
		$this->exists['post'][ $exists_key ] = $exists;

		return $exists;
	}

	/**
	 * Mark the post as existing.
	 *
	 * @param array $data Post data to mark as existing.
	 * @param int $post_id Post ID.
	 */
	protected function mark_post_exists( $data, $post_id ) {
		$exists_key                          = $data['guid'];
		$this->exists['post'][ $exists_key ] = $post_id;
	}

	/**
	 * Prefill existing comment data.
	 *
	 * @see self::prefill_existing_posts() for justification of why this exists.
	 */
	protected function prefill_existing_comments() {
		global $wpdb;
		$posts = $wpdb->get_results( "SELECT comment_ID, comment_author, comment_date FROM {$wpdb->comments}" );

		foreach ( $posts as $item ) {
			$exists_key                             = sha1( $item->comment_author . ':' . $item->comment_date );
			$this->exists['comment'][ $exists_key ] = $item->comment_ID;
		}
	}

	/**
	 * Does the comment exist?
	 *
	 * @param array $data Comment data to check against.
	 *
	 * @return int|bool Existing comment ID if it exists, false otherwise.
	 */
	protected function comment_exists( $data ) {
		$exists_key = sha1( $data['comment_author'] . ':' . $data['comment_date'] );

		// Constant-time lookup if we prefilled
		if ( $this->options['prefill_existing_comments'] ) {
			return isset( $this->exists['comment'][ $exists_key ] ) ? $this->exists['comment'][ $exists_key ] : false;
		}

		// No prefilling, but might have already handled it
		if ( isset( $this->exists['comment'][ $exists_key ] ) ) {
			return $this->exists['comment'][ $exists_key ];
		}

		// Still nothing, try comment_exists, and cache it
		$exists                                 = comment_exists( $data['comment_author'], $data['comment_date'] );
		$this->exists['comment'][ $exists_key ] = $exists;

		return $exists;
	}

	/**
	 * Mark the comment as existing.
	 *
	 * @param array $data Comment data to mark as existing.
	 * @param int $comment_id Comment ID.
	 */
	protected function mark_comment_exists( $data, $comment_id ) {
		$exists_key                             = sha1( $data['comment_author'] . ':' . $data['comment_date'] );
		$this->exists['comment'][ $exists_key ] = $comment_id;
	}

	/**
	 * Prefill existing term data.
	 *
	 * @see self::prefill_existing_posts() for justification of why this exists.
	 */
	protected function prefill_existing_terms() {
		global $wpdb;
		$query = "SELECT t.term_id, tt.taxonomy, t.slug FROM {$wpdb->terms} AS t";
		$query .= " JOIN {$wpdb->term_taxonomy} AS tt ON t.term_id = tt.term_id";
		$terms = $wpdb->get_results( $query );

		foreach ( $terms as $item ) {
			$exists_key                          = sha1( $item->taxonomy . ':' . $item->slug );
			$this->exists['term'][ $exists_key ] = $item->term_id;
		}
	}

	/**
	 * Does the term exist?
	 *
	 * @param array $data Term data to check against.
	 *
	 * @return int|bool Existing term ID if it exists, false otherwise.
	 */
	protected function term_exists( $data ) {
		$exists_key = sha1( $data['taxonomy'] . ':' . $data['slug'] );

		// Constant-time lookup if we prefilled
		if ( $this->options['prefill_existing_terms'] ) {
			return isset( $this->exists['term'][ $exists_key ] ) ? $this->exists['term'][ $exists_key ] : false;
		}

		// No prefilling, but might have already handled it
		if ( isset( $this->exists['term'][ $exists_key ] ) ) {
			return $this->exists['term'][ $exists_key ];
		}

		// Still nothing, try term_exists, and cache it
		$exists = term_exists( $data['slug'], $data['taxonomy'] );
		if ( is_array( $exists ) ) {
			$exists = $exists['term_id'];
		}

		$this->exists['term'][ $exists_key ] = $exists;

		return $exists;
	}


	/**
	 * Mark the term as existing.
	 *
	 * @param array $data Term data to mark as existing.
	 * @param int $term_id Term ID.
	 */
	protected function mark_term_exists( $data, $term_id ) {
		$exists_key                          = sha1( $data['taxonomy'] . ':' . $data['slug'] );
		$this->exists['term'][ $exists_key ] = $term_id;
	}


	/**
	 * Before import content
	 *
	 */
	function update_import_status() {

		$this->store_import_info( 'Start import', '', 'start' );

		return;
	}

	public function cleanup( $delete_tmp = true ) {

		$this->store_import_info( 'End import process', '', 'stop' );

		if ( $delete_tmp ) {
			// Remove temp unpacked files
			$this->delete_tmp_unpackege_dir( $this->get_assets_data( 'tmp_path' ) );
		}

	}


	public function replace_assets_patterns( $preset_data ) {

		$search_pattern  = array();
		$replace_pattern = array();

		foreach ( $preset_data['assets'] as $export_id => $asset_info ) {

			if ( empty( $asset_info['pattern'] ) ) {
				continue;
			}

			foreach ( $asset_info['pattern'] as $export_type => $elements ) {
				foreach ( $elements as $el_num => $one_pattern ) {

					if ( empty( $one_pattern ) ) {
						continue;
					}

					$search_pattern[] = $one_pattern;

					if ( ! empty( $asset_info['data']['import_id'] ) ) {

						if ( 'ASSET_URL' === $export_type ) {
							$replace_pattern[] = $asset_info['data']['import_url'];
						} else {
							$replace_pattern[] = $asset_info['data']['import_id'];
						}

					} else {
						$replace_pattern[] = $export_id;
					}

				}
			}
		}

		$search_pattern[] = 'http%3A%2F%2Fcrane-test-demo.grooni.com';
		$replace_pattern[] = urlencode( GROONI_THEME_ADDONS_SITE_URI );
		$search_pattern[] = 'http%3A%2F%2Fcrane-demo.grooni.com';
		$replace_pattern[] = urlencode( GROONI_THEME_ADDONS_SITE_URI );
		$search_pattern[] = 'http%3A%2F%2Fcrane.grooni.com';
		$replace_pattern[] = urlencode( GROONI_THEME_ADDONS_SITE_URI );

		$search_pattern[] = 'http://crane-test-demo.grooni.com';
		$replace_pattern[] = GROONI_THEME_ADDONS_SITE_URI;
		$search_pattern[] = 'http://crane-demo.grooni.com';
		$replace_pattern[] = GROONI_THEME_ADDONS_SITE_URI;
		$search_pattern[] = 'http://crane.grooni.com';
		$replace_pattern[] = GROONI_THEME_ADDONS_SITE_URI;


		if ( ! empty( $search_pattern ) && ! empty( $replace_pattern ) ) {
			// REPLACE assets patterns
			$preset_data['posts'] = json_decode(
				str_replace(
					$search_pattern,
					$replace_pattern,
					json_encode( $preset_data['posts'] )
				),
				true
			);
		}

		return $preset_data['posts'];

	}


	/**
	 * Get attachment data by filename
	 *
	 * @param $filename
	 *
	 * @return mixed
	 */
	public function get_attachment_by_grooni_meta( $filename ) {
		$args           = array(
			'posts_per_page' => 1,
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'meta_key'       => '_grooni_import_asset_name',
			'meta_value'     => trim( $filename ),
		);
		$get_attachment = new WP_Query( $args );

		if ( ! empty( $get_attachment ) && isset( $get_attachment->posts[0] ) && $get_attachment->posts[0] ) {
			return $get_attachment->posts[0];
		} else {
			return false;
		}
	}


	/**
	 * Upload attachments (assets) from preset data to upload folder and WP media library
	 *
	 * @param $preset_name
	 * @param $preset_assets
	 *
	 * @return array
	 */
	function upload_preset_assets( $preset_name, $preset_assets ) {

		if ( empty( $preset_assets ) || ! is_array( $preset_assets ) ) {
			return array();
		}

		// Gives us access to the download_url() and wp_handle_sideload() functions
		require_once( ABSPATH . 'wp-admin/includes/file.php' );

		foreach ( $preset_assets as $export_id => $asset_info ) {

			// Check, if this asset imported before
			$exist_attachment = $this->get_attachment_by_grooni_meta( $asset_info['data']['filename'] );
			if ( ! empty( $exist_attachment->ID ) ) {

				$exist_parsed = parse_url( wp_get_attachment_url( $exist_attachment->ID ) );
				$exist_url    = dirname( $exist_parsed['path'] ) . '/' . rawurlencode( basename( $exist_parsed['path'] ) );

				// Set new imported asset id
				$preset_assets[ $export_id ]['data']['import_id']  = $exist_attachment->ID;
				$preset_assets[ $export_id ]['data']['import_url'] = $exist_url;

				$this->store_import_info( 'Asset ' . $asset_info['data']['filename'] . ' exist with ID:' . $exist_attachment->ID . ' . Used this asset.' );
				// Skip to next asset
				continue;
			}

			// ok, it's new asset, and it must be import as new
			$tmp_asset_file = $this->get_assets_data( 'tmp_path' ) . $preset_name . '/assets/' . $asset_info['data']['filename'];
			if ( is_file( $tmp_asset_file ) ) {

				$wp_filetype = wp_check_filetype( $tmp_asset_file, null );

				// preload file params
				$file_params = array(
					'name'     => $asset_info['data']['filename'],
					'type'     => $wp_filetype['type'],
					'tmp_name' => $tmp_asset_file,
					'error'    => 0,
					'size'     => filesize( $tmp_asset_file ),
				);

				$overrides = array(
					'test_form'   => false,
					'test_size'   => false,
					'test_upload' => true,
				);


				// move temp asset to wp uploads
				$load_results = wp_handle_sideload( $file_params, $overrides );


				if ( ! empty( $load_results['error'] ) ) {

					// TODO add error handler

				} else {

					$attachment = array(
						'post_mime_type' => $load_results['type'],
						'post_title'     => basename( $load_results['file'] ),
						'post_content'   => '',
						'post_status'    => 'inherit'
					);

					$imported_attach_id = wp_insert_attachment( $attachment, $load_results['file'] );

					$image_new     = get_post( $imported_attach_id );
					$fullsize_path = get_attached_file( $image_new->ID );
					$attach_data   = wp_generate_attachment_metadata( $imported_attach_id, $fullsize_path );
					wp_update_attachment_metadata( $imported_attach_id, $attach_data );
					update_post_meta( $imported_attach_id, '_grooni_import_asset_name', $asset_info['data']['filename'] );

					// Set new imported asset id
					$preset_assets[ $export_id ]['data']['import_id']  = $imported_attach_id;
					$preset_assets[ $export_id ]['data']['import_url'] = $load_results['url'];

					$this->store_import_info( 'Asset imported with new id [' . $imported_attach_id . ']: ' . basename( $load_results['file'] ) );
				}


			}

		}

		return $preset_assets;

	}


	/**
	 *   Determine if a post exists based on post_name and post_type
	 *
	 * @param $post_name string unique post name
	 * @param $post_type string post type (defaults to 'post')
	 *
	 * @return null|string
	 */
	public function post_exists_by_post_name( $post_name, $post_type = 'post' ) {
		global $wpdb;

		$query = "SELECT ID FROM $wpdb->posts WHERE 1=1";
		$args  = array();

		if ( ! empty ( $post_name ) ) {
			$query .= " AND post_name LIKE '%s' ";
			$args[] = $post_name;
		}
		if ( ! empty ( $post_type ) ) {
			$query .= " AND post_type = '%s' ";
			$args[] = $post_type;
		}

		if ( ! empty ( $args ) ) {
			return $wpdb->get_var( $wpdb->prepare( $query, $args ) );
		}

		return false;
	}


	/**
	 * Process and import posts with post types
	 */
	public function import_posts_from_preset( $posts ) {

		// First, sorting the required types of posts
		$pre_post_import = array(
			'mc4wp-form'         => array(),
			'wpcf7_contact_form' => array(),
			'groovy_menu_preset' => array(),
		);
		foreach ( $pre_post_import as $type => $data ) {
			if ( array_key_exists( $type, $posts ) ) {
				$_posts_to_import          = array();
				$_posts_to_import[ $type ] = $posts[ $type ];

				foreach ( $posts[ $type ] as $export_post_id => $export_post ) {
					$pre_post_import[ $type ][ $export_post_id ]['export_post_id'] = $export_post['ID'];
				}

				unset( $posts[ $type ] );
				$posts = array_merge( $_posts_to_import, $posts );
				unset( $_posts_to_import );
			} else {
				unset( $pre_post_import[ $type ] );
			}
		}

		// Processing all post types and his posts $pre_post_import
		foreach ( $posts as $post_type => $post_type_data ) {

			$timer_shift = count( $post_type_data ) + 5;

			foreach ( $post_type_data as $export_post_id => $export_post ) {

				// Prevent doubled crane_footer
				if ( 'crane_footer' === $export_post['post_type'] ) {
					// $export_post['post_name']
					if ( $this->post_exists_by_post_name( $export_post['post_name'], 'crane_footer' ) ) {
						$this->store_import_info( 'We skip adding a new post, because this already exists [name:' . $export_post['post_name'] . ']' . ', [post_type:' . $export_post['post_type'] . ']' );
						continue;
					}
				}

				// Prepare content for new ids from
				if ( ! empty( $pre_post_import ) && ! array_key_exists( $post_type, $pre_post_import ) ) {

					foreach ( $pre_post_import as $pre_post_type => $pre_posts ) {
						if ( empty( $pre_posts ) ) {
							continue;
						}

						foreach ( $pre_posts as $pre_post_id => $pre_post_data ) {

							if ( empty( $pre_post_data['new_post_id'] ) ) {
								continue;
							}

							$_post_content  = $export_post['post_content'];
							$_post_meta     = $export_post['POST_META'];
							$post_meta_flag = 0;

							switch ( $pre_post_type ) {

								case 'mc4wp-form':
									// [mc4wp_form id="OLD_ID"]
									$pattern       = '#\[mc4wp_form id="(\d+)"#im';
									$replacement   = '[mc4wp_form id="' . $pre_post_data['new_post_id'] . '"';
									$_post_content = preg_replace( $pattern, $replacement, $_post_content );

									update_option( 'mc4wp_default_form_id', $pre_post_data['new_post_id'] );

									break;

								case 'wpcf7_contact_form':
									// [contact-form-7 id="OLD_ID"]
									$pattern       = '#\[contact\-form\-7 id="(\d+)"#im';
									$replacement   = '[contact-form-7 id="' . $pre_post_data['new_post_id'] . '"';
									$_post_content = preg_replace( $pattern, $replacement, $_post_content );
									break;

								case 'groovy_menu_preset':
									if ( isset( $_post_meta['gm_custom_preset_id'] ) ) {
										$old_custom_preset_id = $_post_meta['gm_custom_preset_id'];
										if ( intval( $old_custom_preset_id ) === intval( $pre_post_id ) ) {
											$_post_meta['gm_custom_preset_id'] = strval( $pre_post_data['new_post_id'] );
											$post_meta_flag ++;
										}
									}

									if ( isset( $_post_meta['grooni_meta'] ) ) {
										$grooni_meta = json_decode( $_post_meta['grooni_meta'], true );
										if ( $grooni_meta && is_array( $grooni_meta ) ) {
											$old_custom_preset_id = $grooni_meta['groovy_preset'];
											if ( intval( $old_custom_preset_id ) === intval( $pre_post_id ) ) {
												$grooni_meta['groovy_preset'] = strval( $pre_post_data['new_post_id'] );
												$_post_meta['grooni_meta']    = wp_json_encode( $grooni_meta );
												$post_meta_flag ++;
											}
										}
									}
									break;

							}

							if ( ! empty( $_post_content ) && $_post_content !== $export_post['post_content'] ) {
								$export_post['post_content'] = $_post_content;
							}
							if ( $post_meta_flag > 0 && ! empty( $_post_meta) ) {
								$export_post['POST_META'] = $_post_meta;
							}

						}
					}

				}


				if ( in_array( $export_post['post_type'], array( 'post', 'crane_portfolio' ) ) ) {
					$timer_shift --;
					$post_date = date( "Y-m-d H:i:s", intval( current_time( 'timestamp' ) ) - $timer_shift );
				} else {
					$post_date = date( "Y-m-d H:i:s", current_time( 'timestamp' ) );
				}


				$new_post_args = array(
					'post_author'  => get_current_user_id(),
					'post_content' => $export_post['post_content'],
					'post_excerpt' => $export_post['post_excerpt'],
					'post_name'    => $export_post['post_name'],
					'post_parent'  => $export_post['post_parent'],
					'post_status'  => 'publish',
					// 'draft' | 'publish' | 'pending'| 'future' | 'private'
					'post_title'   => $export_post['post_title'],
					'post_type'    => $export_post['post_type'],
					//'post_category'  => array( "<category id>, <...>" ),
					//'tags_input'     => array('<tag>, <tag>, <...>'), // waiting tag slug
					//'tax_input'    => array( 'taxonomy_name' => array( 'term', 'term2', 'term3' ) ), // waiting id for terms
					'meta_input'   => $export_post['POST_META'],
					'post_date'    => $post_date
				);


				// Inset post
				$new_post_id = wp_insert_post( $new_post_args );


				// Store new post ID for $pre_post_import
				if ( array_key_exists( $post_type, $pre_post_import ) ) {
					$pre_post_import[ $post_type ][ $export_post_id ]['new_post_id'] = $new_post_id;
				}


				// Implements Taxonomies
				if ( ! empty( $export_post['POST_TAXONOMIES'] ) ) {

					$search_tax_args      = array(
						'public'   => true,
						'_builtin' => true
					);
					$registred_taxonomies = get_taxonomies( $search_tax_args, 'names', 'or' );

					$post_taxonomies = array(
						'taxonomies' => array(),
						'terms_meta' => array(),
					);

					foreach ( $export_post['POST_TAXONOMIES'] as $post_tax_name => $post_tax_data ) {

						// Do not work wiht not registered taxonomies
						if ( empty( $registred_taxonomies[ $post_tax_name ] ) ) {
							continue;
						}

						// get_term_by( $field, $value, $taxonomy, $output, $filter )
						foreach ( $post_tax_data['post_terms'] as $post_term_name => $post_term_data ) {

							if ( empty( $post_term_data['slug'] ) ) {
								continue;
							}


							$searched_term = term_exists( $post_term_data['slug'], $post_tax_name );


							$current_term_id = null;
							$current_term_update_meta = false;


							if ( ! is_wp_error( $searched_term ) && ! empty( $searched_term['term_id'] ) ) {
								$current_term_id = intval( $searched_term['term_id'] );
							} else {

								$insert_term_data = wp_insert_term(
									$post_term_data['name'],
									$post_tax_name,
									array(
										'description' => $post_term_data['description'],
										'slug'        => $post_term_data['slug'],
									)
								);

								if ( ! is_wp_error( $insert_term_data ) && ! empty( $insert_term_data['term_id'] ) ) {
									$current_term_id = intval( $insert_term_data['term_id'] );
									$current_term_update_meta = true;
								}
							}


							if ( ! empty( $current_term_id ) ) {
								$post_taxonomies['taxonomies'][ $post_tax_name ][] = $current_term_id;
								if ( $current_term_update_meta && ! empty( $post_term_data['TERM_META_DATA'] ) ) {
									$post_taxonomies['terms_meta'][ $post_tax_name ][ $current_term_id ] = $post_term_data['TERM_META_DATA'];
								}
							}

							// TAXONOMIES implement
							if ( ! empty( $post_taxonomies['taxonomies'] ) && $new_post_id ) {
								foreach ( $post_taxonomies['taxonomies'] as $tax_name => $terms ) {

									$set_terms = wp_set_post_terms( $new_post_id, $terms, $tax_name );

									foreach ( $terms as $term_id ) {
										if ( ! empty( $post_taxonomies['terms_meta'][ $tax_name ][ $term_id ] ) ) {

											foreach ( $post_taxonomies['terms_meta'][ $tax_name ][ $term_id ] as $tag_meta_key => $tag_meta_value ) {

												add_term_meta( $term_id, $tag_meta_key, $tag_meta_value, true );

											}

										}

									}

								}

							}

						}

					}

				}


				// implement Comments
				if ( ! empty( $export_post['POST_COMMENT'] ) && $new_post_id ) {

					foreach ( $export_post['POST_COMMENT'] as $comment ) {

						$comment_args = array(
							'comment_post_ID'      => $new_post_id,
							'comment_author'       => $comment['comment_author'],
							'comment_author_email' => $comment['comment_author_email'],
							'comment_author_url'   => $comment['comment_author_url'],
							'comment_content'      => $comment['comment_content'],
							'comment_type'         => '',
							'comment_parent'       => 0, // Not implemented yet
							'comment_date'         => $comment['comment_date'],
							'comment_approved'     => $comment['comment_approved'],
						);

						wp_insert_comment( wp_slash( $comment_args ) );
					}

				}


				$this->store_import_info( 'Adding new post ' . ' [id:' . $new_post_id . ']' . ', [name:' . $export_post['post_name'] . ']' . ', [post_type:' . $export_post['post_type'] . ']' );


			}
		}

	}


	public function import_plugins_related_data( $preset_data ) {

		$plugin_return_info = array();

		foreach ( $preset_data['plugins'] as $plugin_name => $plugin_data ) {

			switch ( $plugin_name ) {

				case 'revslider' :
					$plugin_return_info[ $plugin_name ] = $this->preset_import_data__revslider( $preset_data['preset_name'], $plugin_data );
					break;

				case 'Ultimate_VC_Addons' :
					$font_options = $this->options['fonts'];
					if ( ! empty( $plugin_data['plugin_data']['fonts'] ) && is_array( $plugin_data['plugin_data']['fonts'] ) ) {
						foreach ( $plugin_data['plugin_data']['fonts'] as $font_name => $font_data ) {
							if ( ! empty( $preset_data['assets'][ $font_data['id'] ]['data']['import_id'] ) ) {

								$this->import_fonts_AIO_Icon_Manager( $preset_data['assets'][ $font_options['wp-Ingenicons']['id'] ]['data']['import_id'], $font_name );

							}
						}
					}

					$font_google_filename  = 'plugins_data/Ultimate_VC_Addons/selected_google_fonts.json';
					$selected_google_fonts = json_decode( $this->get_preset_data( $preset_data['preset_name'], $font_google_filename, false ), true );
					if ( ! empty( $selected_google_fonts ) && is_array( $selected_google_fonts ) ) {
						$this->add_ultimate_google_fonts( $selected_google_fonts );
					}

					break;

				case 'groovy-menu' :

					if ( ! empty( $plugin_data['plugin_data']['fonts'] ) && class_exists( 'Grooni_Theme_Addons_Groovy_Menu_Helper' ) ) {

						foreach ( $plugin_data['plugin_data']['fonts'] as $font_name => $font_data ) {
							if ( isset( $preset_data['assets'][ $font_data['id'] ] ) ) {
								$plugin_data['plugin_data']['fonts'][ $font_name ]['id'] = $preset_data['assets'][ $font_data['id'] ]['data']['import_id'];
							}
						}

						$gm_import = new Grooni_Theme_Addons_Groovy_Menu_Helper();
						$gm_import->groovy_menu_import_fonts( $plugin_data['plugin_data']['fonts'] );
					}


					$plugin_return_info[ $plugin_name ] = $this->preset_import_data__groovy_menu( $plugin_data );
					break;

				case 'convertplug' :

					if ( ! empty( $plugin_data['plugin_data']['forms'] ) ) {

						foreach ( $plugin_data['plugin_data']['forms'] as $form_type => $form_data_list ) {

							$exist_forms = get_option( $form_type );

							if ( $exist_forms ) {

								$new_forms = array();
								foreach ( $form_data_list as $form_data_item ) {
									$new_forms[ $form_data_item['style_name'] ] = $form_data_item;
								}

								foreach ( $exist_forms as $exist_form_data ) {
									if ( isset( $new_forms[ $exist_form_data['style_name'] ] ) ) {
										unset( $new_forms[ $exist_form_data['style_name'] ] );
									}
								}

								if ( ! empty( $new_forms ) ) {
									foreach ( $new_forms as $f_num => $f_data ) {
										$exist_forms[] = $f_data;
									}
									update_option( $form_type, $exist_forms );
								}

							} else {
								update_option( $form_type, $form_data_list );
							}

							$this->store_import_info( 'Update convertplug plugin forms.' );

						}

					}

					break;

				default:
					break;
			}

		}

		return $plugin_return_info;

	}


	public function is_preset_exist( $preset_name ) {

		if ( empty( $preset_name ) ) {
			return false;
		}

		$presets_info = $this->get_preset_info_data( true );

		if ( is_array( $presets_info ) && array_key_exists( $preset_name, $presets_info ) ) {
			return true;
		}

		return false;

	}


	public function get_preset_info_data( $check_exists = false ) {

		$presets_info = array();

		if ( get_transient( $this->presets_info_option_name . '_savetime' ) ) {
			if ( $saved_presets = get_option( $this->presets_info_option_name ) ) {
				$presets_info = $saved_presets;
			}
		}

		if ( empty( $presets_info ) ) {
			$demo_package = apply_filters( 'grooni_addons_import_demos', array() );

			if ( isset( $demo_package[ $this->demo ]['presets_info_url'] ) ) {
				$presets_info_url = $demo_package[ $this->demo ]['presets_info_url'];
			}

			if ( empty( $presets_info_url ) ) {
				$this->store_import_info( 'Can not download presets info file. Check template import config.' );
				if ( $check_exists ) {
					return false;
				}
			} else {
				$this->store_import_info( 'Downloading presets info package...', '0' );
				$presets_info_data = wp_remote_get( $presets_info_url );
			}

			if ( is_wp_error( $presets_info_data ) ) {
				$this->store_import_info( sprintf( 'ERROR %s. Could not get demo package from server. Please, try again after some minutes.', $presets_info_data->get_error_code() ), '', 'critical_error' );

				@ob_clean();
				wp_send_json( array(
					'status'  => 'critical_error',
					'message' => sprintf( __( 'ERROR %s. Could not get demo package from server. Please, try again after some minutes.', 'grooni-theme-addons' ), $presets_info_data->get_error_code() )
				), 500 );
			}

			if ( ! empty( $presets_info_data['body'] ) ) {
				$presets_info = json_decode( $presets_info_data['body'], true );

				update_option( $this->presets_info_option_name, $presets_info, false );
				set_transient( $this->presets_info_option_name . '_savetime', $presets_info, 1 * HOUR_IN_SECONDS );
			}

		}

		return $presets_info;

	}


}
