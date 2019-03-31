<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Theme Migrate file.
 *
 * @package crane
 */
class Crane_migrate__v1_3_9_1563 extends Crane_Migration {

	function migrate() {

		$this->db_version = '1.3.9.1563';

		$menu_preset_options = array(
			'regular-page-menu'     => 'page',
			'portfolio-menu'        => 'crane_portfolio',
			'portfolio-single-menu' => 'crane_portfolio--single',
			'blog-menu'             => 'post',
			'blog-single-menu'      => 'post--single',
			'shop-menu'             => 'product',
			'shop-single-menu'      => 'product--single',
			'search-menu'           => 'page--is_search',
			'404-menu'              => 'page--is_404',
		);

		$nav_menu_options = array(
			'regular-page-nav_menu'      => 'page',
			'portfolio-archive-nav_menu' => 'crane_portfolio',
			'portfolio-single-nav_menu'  => 'crane_portfolio--single',
			'blog-nav_menu'              => 'post',
			'blog-single-nav_menu'       => 'post--single',
			'shop-nav_menu'              => 'product',
			'shop-single-nav_menu'       => 'product--single',
			'search-nav_menu'            => 'page--is_search',
			'404-nav_menu'               => 'page--is_404',
		);

		$_redux_options      = maybe_unserialize( get_option( 'crane_options' ) );
		$post_types_presets = array();

		if ( ! empty( $_redux_options ) && is_array( $_redux_options ) ) {
			foreach ( $menu_preset_options as $redux_option => $gm_option ) {
				$redux_opt_value = isset( $_redux_options[ $redux_option ] ) ? $_redux_options[ $redux_option ] : '';
				$post_types_presets[ $gm_option ]['preset'] = $redux_opt_value;
			}
			foreach ( $nav_menu_options as $redux_option => $gm_option ) {
				$nav_menu_id = isset( $_redux_options[ $redux_option ] ) ? $_redux_options[ $redux_option ] : '';
				if ( ! empty( $nav_menu_id ) ) {
					$nav_menu = wp_get_nav_menu_object( $nav_menu_id );
					if ( is_object( $nav_menu ) && isset( $nav_menu->term_id ) ) {
						$nav_menu_id = $nav_menu->term_id;
					}
				} else {
					$nav_menu_id = '';
				}

				$post_types_presets[ $gm_option ]['menu'] = $nav_menu_id;
			}
		}

		// Update global settings.
		$post_types_result = $this->update_taxonomies_preset_global_gm( $post_types_presets );


		// For meta data of content
		foreach ( [ 'page', 'post', 'crane_portfolio', 'product' ] as $post_type ) {
			$args = array(
				'numberposts'      => - 1,
				'category'         => 0,
				'orderby'          => 'ID',
				'order'            => 'ASC',
				'include'          => array(),
				'exclude'          => array(),
				'meta_key'         => 'grooni_meta',
				'meta_value'       => '',
				'suppress_filters' => true,
			);

			$this->add_migrate_debug_log( 'Get posts post_type=' . $post_type . ' with meta_key=grooni_meta' );

			$posts = get_posts( array_merge( [ 'post_type' => $post_type ], $args ) );

			if ( ! $posts ) {
				continue;
			}

			$this->add_migrate_debug_log( 'Work with ' . count( $posts ) . ' posts' );

			foreach ( $posts as $post ) {
				$post_id = (int) $post->ID;

				$meta = get_post_custom( $post_id );
				if ( isset( $meta['grooni_meta'] ) && is_array( $meta['grooni_meta'] ) ) {
					$meta = json_decode( array_shift( $meta['grooni_meta'] ), true );
				}

				if ( empty( $meta ) ) {
					continue;
				}

				$preset_id = isset( $meta['groovy_preset'] ) ? $meta['groovy_preset'] : null;
				$menu_id   = isset( $meta['main_menu'] ) ? $meta['main_menu'] : null;

				// save to meta 'gm_custom_preset_id'.
				if ( ! empty( $preset_id ) && "0" !== $preset_id && "default" !== $preset_id ) {
					update_metadata( 'post', $post_id, 'gm_custom_preset_id', $preset_id );
				} else {
					delete_metadata( 'post', $post_id, 'gm_custom_preset_id' );
				}

				// save to meta 'gm_custom_menu_id'.
				if ( ! empty( $menu_id ) && "0" !== $menu_id && "default" !== $menu_id ) {
					update_metadata( 'post', $post_id, 'gm_custom_menu_id', $menu_id );
				} else {
					delete_metadata( 'post', $post_id, 'gm_custom_menu_id' );
				}

			}

			unset( $posts );

		}

		$this->success();

		return true;
	}


	/**
	 * @param string $saved_tax
	 *
	 * @return bool
	 */
	public function update_taxonomies_preset_global_gm( $saved_tax = '' ) {

		if ( empty( $saved_tax ) ) {
			return false;
		}

		if ( ! class_exists( 'GroovyMenuStyle' ) || ! class_exists( 'GroovyMenuUtils' ) ) {
			return false;
		}

		// update taxonomies settings for Global Settings.
		$style           = new GroovyMenuStyle();
		$global_settings = get_option( GroovyMenuStyle::OPTION_NAME );

		$new_saved_tax_string                               = $this->setTaxonomiesPresets( $saved_tax );
		$global_settings['taxonomies']['taxonomies_preset'] = $new_saved_tax_string;
		$global_settings['taxonomies']['override_for_tax'] = '1';

		// Update settings.
		$style->updateGlobal( $global_settings );

		return true;
	}

	/**
	 * Implode values for taxonomy specific field
	 *
	 * @param array $taxonomies takes a value for processing as an argument.
	 *
	 * @return string
	 */
	public function setTaxonomiesPresets( $taxonomies = array() ) {

		if ( empty( $taxonomies ) || ! is_array( $taxonomies ) ) {
			return '';
		}

		$saved_value    = '';
		$saved_tax      = array();
		$default_values = array(
			'preset' => 'default',
			'menu'   => 'default',
		);

		foreach ( $this->getTaxonomiesPresets() as $post_type => $settings ) {
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
	 * Explode values for taxonomy specific field
	 *
	 * @param string $raw_value takes a value for processing as an argument.
	 *
	 * @return array
	 */
	public function getTaxonomiesPresets( $raw_value = '' ) {

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

		$post_types = $this->getPostTypesExtended();
		foreach ( $post_types as $type_name => $type_label ) {
			if ( empty( $saved_tax[ $type_name ] ) ) {
				$saved_tax[ $type_name ] = $default_values;
			}
		}


		return $saved_tax;

	}

	/**
	 * @param bool $name_as_key
	 *
	 * @param bool $get_custom_types
	 *
	 * @return array
	 */
	public function getPostTypesExtended( $name_as_key = true, $get_custom_types = true ) {

		$post_types     = $this->getPostTypes( true );
		$post_types_ext = array();

		if ( ! is_array( $post_types ) ) {
			return $post_types_ext;
		}

		foreach ( $post_types as $type => $name ) {
			$post_types_ext[ $type ] = $name;

			if ( $get_custom_types ) {
				switch ( $type ) {
					case 'crane_portfolio':
						$post_types_ext['crane_portfolio--single'] = $name . ' (' . esc_html__( 'single pages', 'crane' ) . ')';
						break;
					case 'post':
						$post_types_ext['post--single'] = $name . ' (' . esc_html__( 'single pages', 'crane' ) . ')';
						break;
					case 'product':
						$post_types_ext['product--single'] = $name . ' (' . esc_html__( 'single pages', 'crane' ) . ')';
						break;
					case 'page':
						$post_types_ext['page--is_search'] = esc_html__( 'Search page', 'crane' );
						$post_types_ext['page--is_404']    = esc_html__( '404 Not Found Page', 'crane' );
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
	 * @param bool $name_as_key
	 *
	 * @return array
	 */
	public function getPostTypes( $name_as_key = true ) {

		$post_types = array();

		// get the registered data about each post type with get_post_type_object
		foreach ( get_post_types( array( 'public' => true, 'show_in_nav_menus' => true ) ) as $type ) {
			$type_obj = get_post_type_object( $type );
			if ( $name_as_key ) {
				$post_types[ $type_obj->name ] = $type_obj->label;
			} else {
				$post_types[ $type_obj->label ] = $type_obj->name;
			}
		}

		return $post_types;

	}

}
