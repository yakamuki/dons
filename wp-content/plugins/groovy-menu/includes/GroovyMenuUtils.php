<?php defined( 'ABSPATH' ) || die( 'This script cannot be accessed directly.' );

/**
 * Class GroovyMenuUtils
 */
class GroovyMenuUtils {

	public static function add_groovy_menu_preset_post_type() {
		register_post_type( 'groovy_menu_preset',
			array(
				'labels'            => array(
					'name'          => esc_html__( 'Groovy Menu Preset', 'groovy-menu' ),
					'singular_name' => esc_html__( 'Groovy Menu Preset', 'groovy-menu' ),
					'add_new_item'  => esc_html__( 'Add New Groovy Menu Preset', 'groovy-menu' ),
					'edit_item'     => esc_html__( 'Edit Groovy Menu Preset', 'groovy-menu' ),
					'view_item'     => esc_html__( 'View Groovy Menu Preset', 'groovy-menu' ),
				),
				'public'            => false,
				'has_archive'       => false,
				'show_in_nav_menus' => false,
				'show_in_menu'      => false,
				'taxonomies'        => array(),
				'supports'          => array(
					'title',
					'editor',
					'thumbnail',
					'author',
					'custom-fields',
					'revisions',
				),
				'rewrite'           => array(
					'slug'       => 'groovy_menu_preset',
					'with_front' => false,
				),
			)
		);
	}

	/**
	 * @return array
	 */
	public static function get_old_preset_ids(){
		$preset_change_id = array();
		$all_presets      = GroovyMenuPreset::getAll();
		foreach ( $all_presets as $preset ) {
			$old_id = get_post_meta( $preset->id, 'gm_old_id', true );
			if ( ! empty( $old_id ) && intval( $old_id ) ) {
				$preset_change_id[ intval( $old_id ) ] = $preset->id;
			}
		}

		return $preset_change_id;
	}


	/**
	 * @return array
	 */
	public static function get_preset_used_in(){
		$preset_used_in = array();

		$preset_used_in = get_option( 'groovy_menu_preset_used_in_storage' );

		if ( ! is_array( $preset_used_in ) ) {
			return array();
		}

		return $preset_used_in;
	}


	/**
	 * @param      $preset_id
	 * @param bool $return_count
	 *
	 * @return array|int
	 */
	public static function get_preset_used_in_by_id( $preset_id, $return_count = false ) {

		$preset_id = empty( $preset_id ) ? null : intval( $preset_id );

		if ( empty( $preset_id ) ) {
			return array();
		}

		$used_in        = array();
		$preset_used_in = self::get_preset_used_in();
		$counter        = 0;

		if ( ! is_array( $preset_used_in ) ) {
			$preset_used_in = array();
		}

		foreach ( $preset_used_in as $place => $data ) {

			switch ( $place ) {
				case 'default':
					if ( intval( $data ) === $preset_id ) {
						$used_in['default'] = true;
						$counter ++;
					}
					break;
				case 'global':
					foreach ( $data as $post_type => $preset ) {
						if ( intval( $preset ) === $preset_id ) {
							$used_in['global'][ $post_type ] = true;
							$counter ++;
						}
					}
					break;
				case 'taxonomy':
					foreach ( $data as $taxonomy_id => $preset ) {
						if ( intval( $preset ) === $preset_id ) {
							$used_in['taxonomy'][ $taxonomy_id ] = true;
							$counter ++;
						}
					}
					break;
				case 'post':
					foreach ( $data as $post_type => $post_data ) {
						foreach ( $post_data as $post_id => $preset ) {
							if ( intval( $preset ) === $preset_id ) {
								$used_in['post'][ $post_type ][ $post_id ] = true;
								$counter ++;
							}
						}
					}
					break;
			}
		}

		if ( $return_count ) {
			return $counter;
		} else {
			return $used_in;
		}
	}


	/**
	 * @return string
	 */
	public static function getUploadDir() {
		$uploadDir = wp_upload_dir();

		return $uploadDir['basedir'] . '/groovy/';
	}


	/**
	 * @return string
	 */
	public static function getFontsDir() {
		$fonts = self::getUploadDir() . 'fonts/';

		return $fonts;
	}


	/**
	 * @return string
	 */
	public static function getUploadUri() {
		$uploadDir = wp_upload_dir();

		return $uploadDir['baseurl'] . '/groovy/';
	}


	/**
	 * @param bool $name_as_key
	 *
	 * @return array
	 */
	public static function getPostTypes( $name_as_key = true ) {

		$post_types = array();

		// get the registered data about each post type with get_post_type_object.
		$post_types_query = get_post_types(
			array(
				'public'            => true,
				'show_in_nav_menus' => true,
			)
		);

		if ( empty( $post_types_query ) ) {
			return $post_types;
		}

		foreach ( $post_types_query as $type ) {
			$type_obj = get_post_type_object( $type );

			$name  = isset( $type_obj->name ) ? $type_obj->name : null;
			$label = isset( $type_obj->label ) ? $type_obj->label : '';

			if ( empty( $name ) || empty( $label ) ) {
				continue;
			}

			if ( $name_as_key ) {
				$post_types[ $name ] = $label;
			} else {
				$post_types[ $label ] = $name;
			}
		}

		return $post_types;

	}

	/**
	 * @param bool $name_as_key
	 *
	 * @param bool $get_custom_types
	 *
	 * @return array
	 */
	public static function getPostTypesExtended( $name_as_key = true, $get_custom_types = true  ) {

		$post_types     = self::getPostTypes( true );
		$post_types_ext = array();

		if ( ! is_array( $post_types ) ) {
			return $post_types_ext;
		}

		foreach ( $post_types as $type => $name ) {

			$post_types_ext[ $type ] = $name;

			if ( $get_custom_types ) {
				switch ( $type ) {
					case 'post':
						$post_types_ext['post--single'] = $name . ' (' . esc_html__( 'single pages', 'groovy-menu' ) . ')';
						break;
					case 'page':
						$post_types_ext['page--is_search'] = esc_html__( 'Search page', 'groovy-menu' );
						$post_types_ext['page--is_404']    = esc_html__( '404 Not Found Page', 'groovy-menu' );
						break;
					default:
						$type_obj = get_post_type_object( $type );
						// Post type can has archive and single pages.
						if ( is_object( $type_obj ) && ! empty( $type_obj->has_archive ) && $type_obj->has_archive ) {
							$post_types_ext[ $type . '--single' ] = $name . ' (' . esc_html__( 'single pages', 'groovy-menu' ) . ')';
						}
						break;
				}
			}
		}

		if ( ! $name_as_key ) {
			$_post_types_ext = array();
			foreach ( $post_types_ext as $type => $name ) {
				$_post_types_ext[ $name ] = $type;
			}
			$post_types_ext = $_post_types_ext;
		}

		return $post_types_ext;

	}


	/**
	 * Uses for many custom options
	 *
	 * @return string
	 */
	public static function get_current_page_type() {

		$type = get_post_type() ? : 'page';

		if ( self::is_shop_search() ) {

			$type = 'product';

		} elseif ( is_search() ) {

			$type = 'page--is_search';

		} elseif ( is_404() ) {

			$type = 'page--is_404';

		} elseif ( is_attachment() ) {

			$type = 'attachment';

		} elseif ( self::is_product_woocommerce_page() ) {

			$type = 'product--single';

		} elseif ( self::is_shop_and_category_woocommerce_page() || self::is_additional_woocommerce_page() || self::is_product_woocommerce_page() ) {

			$type = 'product';

		} elseif ( is_page_template( 'template-blog.php' ) || is_home() ) {

			$type = 'post';

		} elseif ( is_page_template( 'template-portfolio.php' ) ) {

			$type = 'crane_portfolio';

		} elseif ( ( is_single() && 'crane_portfolio' === get_post_type() ) || ( is_archive() && 'crane_portfolio' === get_post_type() ) || 'crane_portfolio_cats' === get_query_var( 'taxonomy' ) || 'crane_portfolio_tags' === get_query_var( 'taxonomy' ) ) {

			$type = is_single() ? 'crane_portfolio--single' : 'crane_portfolio';

		} elseif ( ( is_single() && 'post' === get_post_type() ) || ( is_archive() && 'post' === get_post_type() ) || is_archive() ) {

			$type = is_single() ? 'post--single' : 'post';

		} elseif ( is_page() && 'page' === get_post_type() ) {

			$type = 'page';

		} elseif ( 'posts' === get_option( 'show_on_front' ) ) {
			// Check if the blog page is the front page.
			$type = 'post';

		} elseif ( is_single() ) {

			$type = $type . '--single';

		}

		return $type;
	}

	/**
	 * Detect current page
	 *
	 * @return bool
	 */
	public static function is_product_woocommerce_page() {

		if ( function_exists( 'is_product' ) ) {
			if ( is_product() ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Return true if current search page is post_type === 'product'
	 *
	 * @return bool
	 */
	public static function is_shop_search() {
		if ( get_search_query() && get_query_var( 'post_type' ) && 'product' === get_query_var( 'post_type' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Detect current page
	 *
	 * @return bool
	 */
	public static function is_shop_and_category_woocommerce_page() {

		if (
			function_exists( 'is_woocommerce' ) &&
			function_exists( 'is_product' ) &&
			function_exists( 'is_shop' ) &&
			function_exists( 'is_product_tag' )
		) {
			if ( ! is_product() && ( is_woocommerce() || is_shop() || is_product_tag() ) ) {
				return true;
			}
		}

		return false;
	}


	/**
	 * It determines whether the current page belongs to woocommerce
	 * (cart and checkout are standard pages with shortcodes and which are also included)
	 *
	 * @return bool
	 */
	public static function is_additional_woocommerce_page() {

		if ( function_exists( 'is_cart' ) && is_cart() ) {
			return true;
		}
		if ( function_exists( 'is_checkout' ) && is_checkout() ) {
			return true;
		}
		if ( function_exists( 'is_account_page' ) && is_account_page() ) {
			return true;
		}
		if ( function_exists( 'is_checkout_pay_page' ) && is_checkout_pay_page() ) {
			return true;
		}
		if ( function_exists( 'is_wc_endpoint_url' ) && is_wc_endpoint_url() ) {
			return true;
		}

		return false;
	}


	/**
	 * @return array
	 */
	public static function getNavMenus() {

		$nav_menus = array();
		$menus     = wp_get_nav_menus();

		if ( ! empty( $menus ) ) {
			foreach ( $menus as $menu ) {
				if ( isset( $menu->term_id ) && isset( $menu->name ) ) {
					$nav_menus[ $menu->term_id ] = $menu->name;
				}
			}
		}

		return $nav_menus;

	}


	/**
	 * Get default menu
	 *
	 * @return string
	 */
	public static function getDefaultMenu() {

		$menu_location = self::getNavMenuLocations( true );
		$locations     = get_nav_menu_locations();
		if ( ! empty( $menu_location ) && ! empty( $locations ) ) {
			$menu_id = $locations[ $menu_location ];
			if ( ! empty( $menu_id ) ) {
				$menu_obj = wp_get_nav_menu_object( $menu_id );
			}
		}

		if ( ! empty( $menu_obj ) && isset( $menu_obj->term_id ) ) {
			$menu = $menu_obj->term_id;
		} else {
			$menu = '';
		}

		return strval( $menu );

	}


	/**
	 * Return registered nav_menu locations.
	 *
	 * @param string $name check exists location name.
	 *
	 * @return array|string
	 */
	public static function getRegisteredLocations( $name = '' ) {

		global $_wp_registered_nav_menus;

		$return_value = empty( $_wp_registered_nav_menus ) ? array() : $_wp_registered_nav_menus;

		if ( ! empty( $name ) ) {
			$return_value = empty( $return_value[ $name ] ) ? '' : $return_value[ $name ];
		}

		return $return_value;

	}


	/**
	 * @param bool $return_first
	 *
	 * @return array|int|null|string
	 */
	public static function getNavMenuLocations( $return_first = false ) {

		$locations      = array();
		$menu_locations = get_nav_menu_locations();

		foreach ( $menu_locations as $location => $location_id ) {
			$locations[ $location ] = wp_get_nav_menu_name( $location ) . ' (' . $location . ')';
		}

		if ( $return_first ) {
			if ( isset( $locations['gm_primary'] ) ) {
				return 'gm_primary';
			}

			if ( empty( $locations ) ) {
				$locations = '';
			} else {
				reset( $locations );
				$locations = key( $locations );
			}
		}

		return $locations;

	}


	/**
	 * @return null|string
	 */
	public static function getMasterPreset() {
		$styles = new GroovyMenuStyle();

		return $styles->getGlobal( 'taxonomies', 'default_master_preset' );
	}


	/**
	 * @return int|string
	 */
	public static function getMasterNavmenu() {

		$styles = new GroovyMenuStyle();

		$master_navmenu = $styles->getGlobal( 'taxonomies', 'default_master_menu' );
		if ( ! empty( $master_navmenu ) ) {
			return $master_navmenu;
		}

		$master_location = self::getMasterLocation();

		if ( empty( $master_location ) ) {
			$locations = self::getNavMenuLocations();
			if ( ! empty( $locations ) && is_array( $locations ) ) {
				foreach ( $locations as $key => $val ) {
					$master_location = $key;
					break;
				}
			}
		}

		$master_navmenu  = '';
		$theme_locations = get_nav_menu_locations();

		if ( ! empty( $master_location ) && ! empty( $theme_locations ) && ! empty( $theme_locations[ $master_location ] ) ) {
			$menu_obj = get_term( $theme_locations[ $master_location ], 'nav_menu' );
		}
		if ( ! empty( $menu_obj ) && isset( $menu_obj->term_id ) ) {
			$master_navmenu = $menu_obj->term_id;
		}

		if ( empty( $master_navmenu ) ) {
			$master_navmenu = self::getDefaultMenu();
		}

		return $master_navmenu;

	}


	/**
	 * @return null|string
	 */
	public static function getMasterLocation() {
		$master_location = 'gm_primary';
		$all_locations   = self::getNavMenuLocations();

		if ( empty( $all_locations[ $master_location ] ) ) {
			$master_location = self::getNavMenuLocations( true );
		}

		return $master_location;
	}


	/**
	 * Explode values for taxonomy specific field
	 *
	 * @param string $raw_value takes a value for processing as an argument.
	 *
	 * @return array
	 */
	public static function getTaxonomiesPresets( $raw_value = '' ) {

		if ( empty( $raw_value ) ) {
			$styles    = new GroovyMenuStyle();
			$raw_value = $styles->getGlobal( 'taxonomies', 'taxonomies_preset' );
		}

		if ( empty( $raw_value ) ) {
			return array();
		}

		if ( is_array( $raw_value ) ) {
			return $raw_value;
		}

		if ( is_string( $raw_value ) ) {
			$saved_value = explode( ',', $raw_value );
		}

		$saved_tax      = array();
		$default_values = array(
			'preset' => 'default',
			'menu'   => 'default',
		);

		if ( ! empty( $raw_value ) && is_array( $saved_value ) ) {
			foreach ( $saved_value as $tax_opt ) {
				$key_value = explode( ':::', $tax_opt );
				if ( is_array( $key_value ) && isset( $key_value[0] ) && isset( $key_value[1] ) ) {
					$tax    = $key_value[0];
					$params = explode( '@', $key_value[1] );
					if ( is_array( $params ) && isset( $params[0] ) && isset( $params[1] ) ) {
						$saved_tax[ $tax ] = array(
							'preset' => $params[0],
							'menu'   => $params[1],
						);
					} else {
						$saved_tax[ $tax ] = $default_values;
					}
				}
			}
		}

		$post_types = self::getPostTypesExtended();
		foreach ( $post_types as $type_name => $type_label ) {
			if ( empty( $saved_tax[ $type_name ] ) ) {
				$saved_tax[ $type_name ] = $default_values;
			}
		}


		return $saved_tax;

	}

	/**
	 * Implode values for taxonomy specific field
	 *
	 * @param array $taxonomies takes a value for processing as an argument.
	 *
	 * @return string
	 */
	public static function setTaxonomiesPresets( $taxonomies = array() ) {

		if ( empty( $taxonomies ) || ! is_array( $taxonomies ) ) {
			return '';
		}

		$saved_value    = '';
		$saved_tax      = array();
		$default_values = array(
			'preset' => 'default',
			'menu'   => 'default',
		);

		foreach ( self::getTaxonomiesPresets() as $post_type => $settings ) {
			if ( empty( $taxonomies[ $post_type ] ) ) {
				$taxonomies[ $post_type ] = $settings;
			}
		}

		foreach ( $taxonomies as $post_type => $settings ) {
			$value_preset = empty( $settings['preset'] ) ? $default_values['preset'] : $settings['preset'];
			$value_menu   = empty( $settings['menu'] ) ? $default_values['menu'] : $settings['menu'];

			$saved_tax[] = $post_type . ':::' . $value_preset . '@' . $value_menu;
		}

		if ( ! empty( $saved_tax ) ) {
			$saved_value = implode( ',', $saved_tax );
		}


		return $saved_value;

	}


	/**
	 * @param $post_type
	 *
	 * @return array|mixed
	 */
	public static function getTaxonomiesPresetByPostType( $post_type ) {

		$styles           = new GroovyMenuStyle();
		$override_for_tax = $styles->getGlobal( 'taxonomies', 'override_for_tax' );
		$return_values    = array(
			'preset' => 'default',
			'menu'   => 'default',
		);

		if ( ! $override_for_tax ) {
			return $return_values;
		}

		$saved_tax = self::getTaxonomiesPresets();

		if ( ! empty( $saved_tax[ $post_type ] ) ) {
			$return_values = $saved_tax[ $post_type ];
		}

		return $return_values;

	}

	/**
	 * Get all the registered image sizes along with their dimensions
	 *
	 * @global array $_wp_additional_image_sizes
	 *
	 * @link http://core.trac.wordpress.org/ticket/18947 Reference ticket
	 *
	 * @return array $image_sizes The image sizes
	 */
	public static function get_all_image_sizes() {
		global $_wp_additional_image_sizes;

		$default_image_sizes = get_intermediate_image_sizes();

		$image_sizes = array( 'full' => array() );

		foreach ( $default_image_sizes as $size ) {
			$image_sizes[ $size ]['width']  = intval( get_option( "{$size}_size_w" ) );
			$image_sizes[ $size ]['height'] = intval( get_option( "{$size}_size_h" ) );
			$image_sizes[ $size ]['crop']   = get_option( "{$size}_crop" ) ? get_option( "{$size}_crop" ) : false;
		}

		if ( isset( $_wp_additional_image_sizes ) && count( $_wp_additional_image_sizes ) ) {
			$image_sizes = array_merge( $image_sizes, $_wp_additional_image_sizes );
		}

		return $image_sizes;
	}

	/**
	 * Get all the registered image sizes for key value
	 *
	 * @return array $image_sizes_array The image sizes.
	 */
	public static function get_all_image_sizes_for_select() {
		$image_sizes_array = array( 'full' => esc_html__( 'Original full size', 'groovy-menu' ) );
		$image_sizes       = self::get_all_image_sizes();

		foreach ( $image_sizes as $size => $size_data ) {
			$image_sizes_array[ $size ] = $size;
		}

		return $image_sizes_array;
	}

	public static function groovy_wpml_register_single_string( GroovyMenuStyle $styles ) {

		$logo_text     = $styles->getGlobal( 'logo', 'logo_text' );
		$toolbar_email = $styles->getGlobal( 'toolbar', 'toolbar_email' );
		$toolbar_phone = $styles->getGlobal( 'toolbar', 'toolbar_phone' );

		//WPML
		/**
		 * register strings for translation.
		 */
		do_action( 'wpml_register_single_string', 'groovy-menu', 'Global settings - logo text', $logo_text );
		do_action( 'wpml_register_single_string', 'groovy-menu', 'Global settings - toolbar email text', $toolbar_email );
		do_action( 'wpml_register_single_string', 'groovy-menu', 'Global settings - toolbar phone text', $toolbar_phone );
		//WPML

	}

	public static function getAutoIntegrationOptionName() {
		return 'gm_auto_integrate_locations_';
	}

	/**
	 * Check if in auto-integration mode
	 *
	 * @return bool
	 */
	public static function getAutoIntegration() {
		global $gm_supported_module;

		if ( isset( $gm_supported_module['GroovyMenuShowIntegration'] ) && ! $gm_supported_module['GroovyMenuShowIntegration'] ) {
			return false;
		}

		$theme_name     = empty( $gm_supported_module['theme'] ) ? wp_get_theme()->get_template() : $gm_supported_module['theme'];
		$integrate_data = get_option( self::getAutoIntegrationOptionName() . $theme_name );
		$return_value   = ( ! empty( $integrate_data ) && $integrate_data ) ? true : false;

		return $return_value;
	}


	public static function get_posts_fields( $args = array() ) {
		$valid_fields = array(
			'ID'             => '%d',
			'post_author'    => '%d',
			'post_type'      => '%s',
			'post_mime_type' => '%s',
			'post_title'     => false,
			'post_name'      => '%s',
			'post_date'      => '%s',
			'post_modified'  => '%s',
			'menu_order'     => '%d',
			'post_parent'    => '%d',
			'post_excerpt'   => false,
			'post_content'   => false,
			'post_status'    => '%s',
			'comment_status' => false,
			'ping_status'    => false,
			'to_ping'        => false,
			'pinged'         => false,
			'comment_count'  => '%d'
		);
		$defaults     = array(
			'post_type'      => 'groovy_menu_preset',
			'post_status'    => 'publish',
			'orderby'        => 'post_date',
			'order'          => 'DESC',
			'posts_per_page' => - 1,
		);

		$order = $orderby = $posts_per_page = '';

		global $wpdb;
		$args  = wp_parse_args( $args, $defaults );
		$where = "";
		foreach ( $valid_fields as $field => $can_query ) {
			if ( isset( $args[ $field ] ) && $can_query ) {
				if ( '' !== $where ) {
					$where .= ' AND ';
				}
				$where .= $wpdb->prepare( $field . " = " . $can_query, $args[ $field ] );
			}
		}
		if ( isset( $args['search'] ) && is_string( $args['search'] ) ) {
			if ( '' !== $where ) {
				$where .= ' AND ';
			}
			$where .= $wpdb->prepare( "post_title LIKE %s", "%" . $args['search'] . "%" );
		}
		if ( isset( $args['include'] ) ) {
			if ( is_string( $args['include'] ) ) {
				$args['include'] = explode( ',', $args['include'] );
			}
			if ( is_array( $args['include'] ) ) {
				$args['include'] = array_map( 'intval', $args['include'] );
				if ( '' !== $where ) {
					$where .= ' OR ';
				}
				$where .= "ID IN (" . implode( ',', $args['include'] ) . ")";
			}
		}
		if ( isset( $args['exclude'] ) ) {
			if ( is_string( $args['exclude'] ) ) {
				$args['exclude'] = explode( ',', $args['exclude'] );
			}
			if ( is_array( $args['exclude'] ) ) {
				$args['exclude'] = array_map( 'intval', $args['exclude'] );
				if ( '' !== $where ) {
					$where .= ' AND ';
				}
				$where .= "ID NOT IN (" . implode( ',', $args['exclude'] ) . ")";
			}
		}
		extract( $args );
		$iscol = false;
		if ( isset( $fields ) ) {
			if ( is_string( $fields ) ) {
				$fields = explode( ',', $fields );
			}
			if ( is_array( $fields ) ) {
				$fields = array_intersect( $fields, array_keys( $valid_fields ) );
				if ( count( $fields ) === 1 ) {
					$iscol = true;
				}
				$fields = implode( ',', $fields );
			}
		}
		if ( empty( $fields ) ) {
			$fields = '*';
		}
		if ( ! in_array( $orderby, $valid_fields ) ) {
			$orderby = 'post_date';
		}
		if ( ! in_array( strtoupper( $order ), array( 'ASC', 'DESC' ) ) ) {
			$order = 'DESC';
		}
		if ( ! intval( $posts_per_page ) && $posts_per_page != - 1 ) {
			$posts_per_page = $defaults['posts_per_page'];
		}
		if ( '' === $where ) {
			$where = '1';
		}
		$q = "SELECT $fields FROM $wpdb->posts WHERE " . $where;
		$q .= " ORDER BY $orderby $order";
		if ( $posts_per_page != - 1 ) {
			$q .= " LIMIT $posts_per_page";
		}

		return $iscol ? $wpdb->get_col( $q ) : $wpdb->get_results( $q );
	}

	/**
	 * Adds meta links to the plugin in the WP Admin > Plugins screen
	 *
	 * @param array  $links
	 * @param string $file
	 *
	 * @return array
	 */
	public static function gm_plugin_meta_links( $links, $file ) {
		if ( 'groovy-menu/groovy-menu.php' !== $file ) {
			return $links;
		}

		$links[] = '<a href="https://grooni.ticksy.com/" target="_blank"">' . esc_html__( 'Get Support', 'groovy-menu' ) . '</a>';

		return $links;
	}

}
