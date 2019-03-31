<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );


include_once get_parent_theme_file_path( 'admin/importer/importer.php' );


if ( ! class_exists( 'Crane_Import_Settings' ) ) {
	/**
	 * Initial OneClick import for this theme
	 *
	 * @package InsightFramework
	 */
	class Crane_Import_Settings {

		/**
		 * The constructor.
		 */
		public function __construct() {
			// empty
		}


		/**
		 * Start actions and filters
		 */
		public function init() {
			// Import Demo
			add_filter( 'grooni_addons_import_demos', array( 'Crane_Import_Settings', 'import_demos' ) );

			// Add info about taxonomies
			add_filter( 'grooni_addons_import_taxonomy_meta_keys', array(
				'Crane_Import_Settings',
				'taxonomy_meta_keys'
			) );

			// Add info about taxonomies
			add_filter( 'grooni_addons_import_taxonomy_meta_options_name', function () {
				return 'crane_term_additional_meta';
			} );

			// Add info about import info store option
			add_filter( 'grooni_addons_import_option_name', function () {
				return 'crane_imported_flags';
			} );

			// Import package url
			add_filter( 'grooni_addons_import_generate_thumb', function () {
				return false;
			} );

			// Import additional options
			add_filter( 'grooni_addons_import_additional_options', array(
				'Crane_Import_Settings',
				'additional_options'
			) );

			// Additional import data
			add_filter( 'grooni_addons_import_additional_data', array( 'Crane_Import_Settings', 'additional_data' ) );

			// Add info about RevSlider plugin
			add_filter( 'grooni_addons_import_revslider', array( 'Crane_Import_Settings', 'revslider_keys' ) );

			$importer = new Crane_ContentImporter();
			add_action( 'admin_menu', array( $importer, 'init' ), 50 );

			add_action( 'wp_ajax_crane_check_necessary_plugins', array( $this, 'check_necessary_plugins' ) );

		}


		public function check_necessary_plugins() {

			$parts = array();
			$need = array();

			if ( ! empty( $_POST['steps'] ) ) {
				$parts = esc_attr( $_POST['steps'] );
				if ( $parts ) {
					$parts = json_decode( str_replace( '\"', '"', wp_specialchars_decode( $parts, ENT_COMPAT ) ), true );
				}
			}

			$importer = new Crane_ContentImporter();
			$importer->get_presets_data();
			$steps_available_for_import = $importer->sort_presets_data();

			if ( empty( $steps_available_for_import['all-home'] ) ) {
				$steps_available_for_import['all-home'] = array();
			}

			$all_plugins       = self::additional_data()['plugins'];
			$necessary_plugins = self::additional_data()['necessary_plugins'];
			foreach ( $steps_available_for_import as $import_step => $import_step_data ) {
				$plugins_for_step = array();
				if ( isset( $necessary_plugins[ $import_step ] ) ) {
					foreach ( $necessary_plugins[ $import_step ] as $need_plugin_slug => $need_plugin ) {
						if ( $need_plugin && isset( $all_plugins[ $need_plugin_slug ] ) ) {
							$plugins_for_step[] = $all_plugins[ $need_plugin_slug ]['name'];
						}
					}
				}
				$steps_available_for_import[ $import_step ]['plugins_for_step'] = $plugins_for_step;
			}

			foreach ( $parts as $part ) {
				if (!empty( $steps_available_for_import[ $part]['plugins_for_step'])) {
					foreach ( $steps_available_for_import[ $part ]['plugins_for_step'] as $plugin ) {
						if ( ! in_array( $plugin, $need ) ) {
							$need[] = $plugin;
						}
					}
				}
			}

			if ( empty( $need ) ) {
				$answer = '';
			} else {
				$answer = esc_html__( 'The following plugins will be installed during demo import', 'crane' ) .
				          ': <span>' .
				          esc_attr( implode( ', ', $need ) ) .
				          '</span>';
			}

			wp_send_json( $answer );

		}

		/**
		 * Import Demo
		 */
		static function import_demos() {
			return array(
				'crane' => array(
					'screenshot'       => get_template_directory_uri() . '/screenshot.png',
					'name'             => 'crane',
					'url'              => 'http://updates.grooni.com/theme-demos/crane/crane-demo-preset__' . CRANE_THEME_VERSION . '.zip',
					'presets_url'      => 'http://updates.grooni.com/theme-demos/crane/presets/' . CRANE_THEME_VERSION . '/',
					'presets_info_url' => 'http://updates.grooni.com/theme-demos/crane/presets/' . CRANE_THEME_VERSION . '/presets.json',
				)
			);
		}


		/**
		 * Return info about taxonomies keys
		 */
		static function taxonomy_meta_keys() {

			$taxonomies_keys = array(
				'blog'      => array(
					'category' => 'blog-category-meta',
					'post_tag' => 'blog-tag-meta',
				),
				'portfolio' => array(
					'crane_portfolio_tags' => 'portfolio-tag-meta'
				),
				'shop'      => array(
					'product_cat' => 'shop-category-meta',
				),
			);

			return $taxonomies_keys;
		}


		/**
		 * Return info about additional options
		 */
		static function additional_options() {
			return array(
				'mc4wp_default_form_id' => array(
					'type'    => 'integer',
					'value'   => 6031,
					'rewrite' => false
				),
				'sb_instagram_settings' => array(
					'type'    => 'array',
					'value'   => array(),
					'rewrite' => false
				),
			);
		}


		/**
		 * Return info about Revolution Slider Demo
		 */
		static function revslider_keys() {
			return array(
				'pages'         => 'Crane_About_Us',
				'shop'          => 'Crane_Shop_Home',
				'portfolio'     => 'Crane_Interior_Design',
				'blog'          => array( 'Crane_Blog_Badge', 'Crane_Blog' ),
				'pages_home-7'  => 'Crane_Intro_Home',
				'pages_home-8'  => 'Crane_Corporate_Home',
				'pages_home-11' => 'Crane_Dark_Home',
				'pages_home-6'  => 'Crane_Hipster_Home',
				'pages_home-3'  => 'Crane_Portfolio_Home',
				'pages_home-1'  => 'Crane_Original_Home',
				'pages_home-4'  => 'Crane_Interior_Home',
				'pages_home-5'  => 'Crane_Light_Home',
				'pages_home-2'  => 'Crane_Portfolio_With_Map_Home',
				'pages_home-9'  => 'Crane_One_Page_Home',
				'pages_home-10' => 'Crane_Shop_Home',
				'yoga'          => 'Crane_Yoga',
				'all-home' => array(
					'Crane_Intro_Home',
					'Crane_Corporate_Home',
					'Crane_Dark_Home',
					'Crane_Hipster_Home',
					'Crane_Portfolio_Home',
					'Crane_Original_Home',
					'Crane_Interior_Home',
					'Crane_Light_Home',
					'Crane_Portfolio_With_Map_Home',
					'Crane_One_Page_Home',
					'Crane_Shop_Home',
					'Crane_Yoga'
				),
			);
		}


		/**
		 * Return info about additional options
		 */
		static function additional_data() {
			return array(
				'pages_home'        => array(
					'all-home'      => 67,
					'pages_home-1'  => 67,
					'pages_home-2'  => 240,
					'pages_home-3'  => 362,
					'pages_home-4'  => 594,
					'pages_home-5'  => 619,
					'pages_home-6'  => 651,
					'pages_home-7'  => 671,
					'pages_home-8'  => 692,
					'pages_home-9'  => 705,
					'pages_home-10' => 831,
					'pages_home-11' => 863,
					'cargo'         => 8550,
					'barber'        => 8559,
					'education'     => 8562,
					'yoga'          => 8721, // yoga
					'wedding'       => 8711, // wedding
				),
				'menus_home'        => array(
					'pages_home-1'      => 82,
					'pages_home-2'      => 241,
					'pages_home-3'      => 363,
					'pages_home-4'      => 595,
					'pages_home-5'      => 620,
					'pages_home-6'      => 652,
					'pages_home-7'      => 672,
					'pages_home-8'      => 694,
					'pages_home-9'      => 707,
					'pages_home-10'     => 849,
					'pages_home-11'     => 923,
					'sub_menu_home_new' => 8609,
					'cargo'             => 8595, // Cargo
					'barber'            => 8594, // barber
					'education'         => 8593, // education
					'yoga'              => 8730, // yoga
					'wedding'           => 8731, // wedding
				),
				'fonts'             => array(
					'wp-Ingenicons'     => array(
						'old_name' => 'groovy-28328',
						'id'       => 126
					),
					'Simple-line-icons' => array(
						'old_name' => 'groovy-69018',
						'id'       => 3927
					)
				),
				'plugins'           => crane_get_crane_plugins_array(),
				'necessary_plugins' => array(
					'pages'         => array(
						'js_composer'           => true,
						'revslider'             => true,
						'groovy-menu'           => true,
						'grooni-theme-addons'   => true,
						'instagram-feed'        => true,
						//'woocommerce'           =>true,
						//'LayerSlider'           =>true,
						'Ultimate_VC_Addons'    => true,
						'grooni_twitter_widget' => true,
						'contact-form-7'        => true,
						//'convertplug'           =>true,
						'mailchimp-for-wp'      => true,
					),
					'elements'      => array(
						'js_composer'           => true,
						'revslider'             => true,
						'groovy-menu'           => true,
						'grooni-theme-addons'   => true,
						'instagram-feed'        => true,
						//'woocommerce'           =>true,
						//'LayerSlider'           =>true,
						'Ultimate_VC_Addons'    => true,
						'grooni_twitter_widget' => true,
						'contact-form-7'        => true,
						//'convertplug'           =>true,
						'mailchimp-for-wp'      => true,
					),
					'portfolio'     => array(
						'js_composer'           => true,
						'revslider'             => true,
						'groovy-menu'           => true,
						'grooni-theme-addons'   => true,
						'instagram-feed'        => true,
						//'woocommerce'           =>true,
						//'LayerSlider'           =>true,
						'Ultimate_VC_Addons'    => true,
						'grooni_twitter_widget' => true,
						'contact-form-7'        => true,
						//'convertplug'           =>true,
						'mailchimp-for-wp'      => true,
					),
					'blog'          => array(
						'js_composer'           => true,
						'revslider'             => true,
						'groovy-menu'           => true,
						'grooni-theme-addons'   => true,
						'instagram-feed'        => true,
						//'woocommerce'           =>true,
						//'LayerSlider'           => true,
						'Ultimate_VC_Addons'    => true,
						'grooni_twitter_widget' => true,
						'contact-form-7'        => true,
						//'convertplug'           =>true,
						'mailchimp-for-wp'      => true,
					),
					'shop'          => array(
						'js_composer'           => true,
						'revslider'             => true,
						'groovy-menu'           => true,
						'grooni-theme-addons'   => true,
						'instagram-feed'        => true,
						'woocommerce'           => true,
						//'LayerSlider'           =>true,
						'Ultimate_VC_Addons'    => true,
						'grooni_twitter_widget' => true,
						'contact-form-7'        => true,
						//'convertplug'           =>true,
						'mailchimp-for-wp'      => true,
					),
					'all-home'      => array(
						'js_composer'           => true,
						'revslider'             => true,
						'groovy-menu'           => true,
						'grooni-theme-addons'   => true,
						'instagram-feed'        => true,
						'woocommerce'           => true,
						//'LayerSlider'           => true,
						'Ultimate_VC_Addons'    => true,
						'grooni_twitter_widget' => true,
						'contact-form-7'        => true,
						'convertplug'           => true,
						'mailchimp-for-wp'      => true,
					),
					'pages_home-1'  => array(
						'js_composer'           => true,
						'revslider'             => true,
						'groovy-menu'           => true,
						'grooni-theme-addons'   => true,
						'instagram-feed'        => true,
						//'woocommerce'           =>true,
						//'LayerSlider'           =>true,
						'Ultimate_VC_Addons'    => true,
						'grooni_twitter_widget' => true,
						'contact-form-7'        => true,
						//'convertplug'           =>true,
						'mailchimp-for-wp'      => true,
					),
					'pages_home-2'  => array(
						'js_composer'           => true,
						'revslider'             => true,
						'groovy-menu'           => true,
						'grooni-theme-addons'   => true,
						'instagram-feed'        => true,
						//'woocommerce'           =>true,
						//'LayerSlider'           =>true,
						'Ultimate_VC_Addons'    => true,
						'grooni_twitter_widget' => true,
						'contact-form-7'        => true,
						//'convertplug'           =>true,
						'mailchimp-for-wp'      => true,
					),
					'pages_home-3'  => array(
						'js_composer'           => true,
						'revslider'             => true,
						'groovy-menu'           => true,
						'grooni-theme-addons'   => true,
						'instagram-feed'        => true,
						//'woocommerce'           =>true,
						//'LayerSlider'           =>true,
						'Ultimate_VC_Addons'    => true,
						'grooni_twitter_widget' => true,
						'contact-form-7'        => true,
						//'convertplug'           =>true,
						'mailchimp-for-wp'      => true,
					),
					'pages_home-4'  => array(
						'js_composer'           => true,
						'revslider'             => true,
						'groovy-menu'           => true,
						'grooni-theme-addons'   => true,
						'instagram-feed'        => true,
						//'woocommerce'           =>true,
						//'LayerSlider'           =>true,
						'Ultimate_VC_Addons'    => true,
						'grooni_twitter_widget' => true,
						'contact-form-7'        => true,
						//'convertplug'           =>true,
						'mailchimp-for-wp'      => true,
					),
					'pages_home-5'  => array(
						'js_composer'           => true,
						'revslider'             => true,
						'groovy-menu'           => true,
						'grooni-theme-addons'   => true,
						'instagram-feed'        => true,
						//'woocommerce'           =>true,
						//'LayerSlider'           =>true,
						'Ultimate_VC_Addons'    => true,
						'grooni_twitter_widget' => true,
						'contact-form-7'        => true,
						//'convertplug'           =>true,
						'mailchimp-for-wp'      => true,
					),
					'pages_home-6'  => array(
						'js_composer'           => true,
						'revslider'             => true,
						'groovy-menu'           => true,
						'grooni-theme-addons'   => true,
						'instagram-feed'        => true,
						//'woocommerce'           =>true,
						//'LayerSlider'           =>true,
						'Ultimate_VC_Addons'    => true,
						'grooni_twitter_widget' => true,
						'contact-form-7'        => true,
						//'convertplug'           =>true,
						'mailchimp-for-wp'      => true,
					),
					'pages_home-7'  => array(
						'js_composer'           => true,
						'revslider'             => true,
						'groovy-menu'           => true,
						'grooni-theme-addons'   => true,
						'instagram-feed'        => true,
						//'woocommerce'           =>true,
						//'LayerSlider'           =>true,
						'Ultimate_VC_Addons'    => true,
						'grooni_twitter_widget' => true,
						'contact-form-7'        => true,
						//'convertplug'           =>true,
						'mailchimp-for-wp'      => true,
					),
					'pages_home-8'  => array(
						'js_composer'           => true,
						'revslider'             => true,
						'groovy-menu'           => true,
						'grooni-theme-addons'   => true,
						'instagram-feed'        => true,
						//'woocommerce'           =>true,
						//'LayerSlider'           =>true,
						'Ultimate_VC_Addons'    => true,
						'grooni_twitter_widget' => true,
						'contact-form-7'        => true,
						//'convertplug'           =>true,
						'mailchimp-for-wp'      => true,
					),
					'pages_home-9'  => array(
						'js_composer'           => true,
						'revslider'             => true,
						'groovy-menu'           => true,
						'grooni-theme-addons'   => true,
						'instagram-feed'        => true,
						//'woocommerce'           =>true,
						//'LayerSlider'           =>true,
						'Ultimate_VC_Addons'    => true,
						'grooni_twitter_widget' => true,
						'contact-form-7'        => true,
						//'convertplug'           =>true,
						'mailchimp-for-wp'      => true,
					),
					'pages_home-10' => array(
						'js_composer'           => true,
						'revslider'             => true,
						'groovy-menu'           => true,
						'grooni-theme-addons'   => true,
						'instagram-feed'        => true,
						'woocommerce'           => true,
						//'LayerSlider'           =>true,
						'Ultimate_VC_Addons'    => true,
						'grooni_twitter_widget' => true,
						'contact-form-7'        => true,
						//'convertplug'           =>true,
						'mailchimp-for-wp'      => true,
					),
					'pages_home-11' => array(
						'js_composer'           => true,
						'revslider'             => true,
						'groovy-menu'           => true,
						'grooni-theme-addons'   => true,
						'instagram-feed'        => true,
						//'woocommerce'           =>true,
						//'LayerSlider'           =>true,
						'Ultimate_VC_Addons'    => true,
						'grooni_twitter_widget' => true,
						'contact-form-7'        => true,
						//'convertplug'           =>true,
						'mailchimp-for-wp'      => true,
					),
					'cargo'         => array(
						'js_composer'           => true,
						'revslider'             => true,
						'groovy-menu'           => true,
						'grooni-theme-addons'   => true,
						'instagram-feed'        => true,
						//'woocommerce'           =>true,
						//'LayerSlider'           =>true,
						'Ultimate_VC_Addons'    => true,
						'grooni_twitter_widget' => true,
						'contact-form-7'        => true,
						'convertplug'           => true,
						'mailchimp-for-wp'      => true,
					),
					'education'     => array(
						'js_composer'           => true,
						'revslider'             => true,
						'groovy-menu'           => true,
						'grooni-theme-addons'   => true,
						'instagram-feed'        => true,
						//'woocommerce'           =>true,
						//'LayerSlider'           =>true,
						'Ultimate_VC_Addons'    => true,
						'grooni_twitter_widget' => true,
						'contact-form-7'        => true,
						'convertplug'           => true,
						'mailchimp-for-wp'      => true,
					),
					'barber'        => array(
						'js_composer'           => true,
						'revslider'             => true,
						'groovy-menu'           => true,
						'grooni-theme-addons'   => true,
						'instagram-feed'        => true,
						//'woocommerce'           =>true,
						//'LayerSlider'           =>true,
						'Ultimate_VC_Addons'    => true,
						'grooni_twitter_widget' => true,
						'contact-form-7'        => true,
						'convertplug'           => true,
						'mailchimp-for-wp'      => true,
					),
					'yoga'          => array(
						'js_composer'         => true,
						'revslider'           => true,
						'groovy-menu'         => true,
						'grooni-theme-addons' => true,
						//'instagram-feed'   => true,
						//'woocommerce'           =>true,
						//'LayerSlider'           =>true,
						'Ultimate_VC_Addons'  => true,
						//'grooni_twitter_widget' => true,
						//'contact-form-7'        => true,
						//'convertplug'           => true,
						//'mailchimp-for-wp'      => true,
					),
					'wedding'       => array(
						'js_composer'         => true,
						'revslider'           => true,
						'groovy-menu'         => true,
						'grooni-theme-addons' => true,
						//'instagram-feed'   => true,
						//'woocommerce'           =>true,
						//'LayerSlider'           =>true,
						'Ultimate_VC_Addons'  => true,
						//'grooni_twitter_widget' => true,
						'contact-form-7'      => true,
						//'convertplug'           => true,
						//'mailchimp-for-wp'      => true,
					),

				)
			);
		}

	}

}
