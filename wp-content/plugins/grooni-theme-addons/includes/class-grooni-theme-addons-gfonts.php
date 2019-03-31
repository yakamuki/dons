<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Work with google fonts
 *
 *
 * @link       http://grooni.com
 * @since      1.2.9
 *
 * @package    Grooni_Theme_Addons
 * @subpackage Grooni_Theme_Addons/includes
 * @author     Di_Skyer <diskyer@gmail.com>
 */
class Grooni_Theme_Addons_GFonts {

	/**
	 * Google Fonts API URL
	 *
	 * @since    1.2.9
	 * @access   protected
	 * @var      string $g_font_url
	 */
	protected $g_font_url = 'https://google-webfonts-helper.herokuapp.com/';

	/**
	 * Options name for cache Google Fonts
	 *
	 * @since    1.2.9
	 * @access   protected
	 * @var      string $g_font_opt_name
	 */
	protected $g_font_opt_name = 'Grooni_Theme_Addons_GFonts_cache';

	/**
	 * Var for cache downloaded font before
	 *
	 * @since    1.2.9
	 * @access   protected
	 * @var      array $downloaded
	 */
	protected $downloaded = array();

	/**
	 * All fonts list
	 *
	 * @since    1.2.9
	 * @access   protected
	 * @var      array $g_fonts
	 */
	protected $g_fonts = array();


	/**
	 * All current fonts list
	 *
	 * @since    1.2.9
	 * @access   protected
	 * @var      array $g_fonts_current
	 */
	protected $g_fonts_current = array();


	public function __construct() {
		// ...
	}


	public function get_all_gfonts() {

		if ( ! empty( $this->g_fonts ) && is_array( $this->g_fonts ) ) {
			return $this->g_fonts;
		} elseif ( false !== get_transient( $this->g_font_opt_name ) ) {
			$this->g_fonts = get_transient( $this->g_font_opt_name );

			return $this->g_fonts;
		}


		// GET GFonts from API
		$response = wp_remote_get( $this->g_font_url . 'api/fonts', array( 'timeout' => 30, 'httpversion' => '1.1' ) );

		// Check if correct response
		if ( ! is_wp_error( $response ) && wp_remote_retrieve_response_code( $response ) === 200 ) {
			$response_body = json_decode( wp_remote_retrieve_body( $response ), true );

			if ( ! empty( $response_body ) && is_array( $response_body ) ) {
				$this->g_fonts = $response_body;
				set_transient( $this->g_font_opt_name, $response_body, WEEK_IN_SECONDS );
			}
		}

		return $this->g_fonts;
	}


	public function get_font_data( $font_search, $search_type = 'family' ) {

		if ( empty( $font_search ) ) {
			return array();
		}

		foreach ( $this->get_all_gfonts() as $font_data ) {
			if ( isset( $font_data[ $search_type ] ) && $font_data[ $search_type ] === $font_search ) {
				return $font_data;
			}
		}

		return array();

	}


	public function get_opt_name( ) {
		return $this->g_font_opt_name;
	}


	public function get_font_info( $font_search, $search_type = 'family' ) {

		if ( empty( $font_search ) ) {
			return array();
		}

		$font_data = $this->get_font_data( $font_search, $search_type );

		if ( empty( $font_data ) || ! isset( $font_data['id'] ) ) {
			return array();
		}

		if ( false !== get_transient( $this->g_font_opt_name . '__current' ) ) {
			$this->g_fonts_current = get_transient( $this->g_font_opt_name . '__current' );
		}

		if ( ! empty( $this->g_fonts_current[ $font_data['id'] ] ) && is_array( $this->g_fonts_current[ $font_data['id'] ] ) ) {
			return $this->g_fonts_current[ $font_data['id'] ];
		}

		// GET google font from API
		$response = wp_remote_get( $this->g_font_url . 'api/fonts/' . $font_data['id'], array( 'timeout'     => 30,
		                                                                                       'httpversion' => '1.1'
		) );

		// Check if correct response
		if ( ! is_wp_error( $response ) && wp_remote_retrieve_response_code( $response ) === 200 ) {
			$response_body = json_decode( wp_remote_retrieve_body( $response ), true );

			if ( ! empty( $response_body ) && is_array( $response_body ) ) {
				$this->g_fonts_current[ $font_data['id'] ] = $response_body;
				set_transient( $this->g_font_opt_name . '__current', $this->g_fonts_current, WEEK_IN_SECONDS );

				return $response_body;
			}
		}

		return array();

	}


	public function get_specific_fonts( $source = 'redux', $raw_data = array() ) {

		$need_fonts = array();

		$source_fonts = array();

		if ( 'ultimate_addons' === $source ) {
			// UltimateAddons fonts
			$source_fonts = get_option( 'ultimate_selected_google_fonts' );
		} elseif ( 'redux' === $source ) {
			// Redux framework options fonts
			$source_fonts = $raw_data;
		}


		if ( ! empty( $source_fonts ) ) {
			foreach ( $source_fonts as $source_gfont_data ) {

				if ( 'ultimate_addons' === $source ) {
					$font_name = $source_gfont_data['font_name'];
				} else {
					$font_name = $source_gfont_data['font-family'];
				}

				$font_info = $this->get_font_info( $font_name );

				if ( empty( $font_info ) ) {
					continue;
				}

				$need_fonts[ $font_name ] = array(
					'id'           => $font_info['id'],
					'family'       => $font_info['family'],
					'version'      => $font_info['version'],
					'defSubset'    => isset( $font_info['defSubset'] ) ? $font_info['defSubset'] : 'latin',
					'defVariant'   => isset( $font_info['defVariant'] ) ? $font_info['defVariant'] : 'regular',
					'variants'     => null,
					'subsets'      => null,
					'zip_url'      => null,
					'variants_css' => null,
				);

				if ( ! empty( $source_gfont_data['variants'] ) ) {
					foreach ( $source_gfont_data['variants'] as $variant ) {
						if ( isset( $variant['variant_selected'] ) && 'true' === $variant['variant_selected'] ) {
							$need_fonts[ $font_name ]['variants'][] = $variant['variant_value'];
						}
					}
				}

				if ( ! empty( $source_gfont_data['subsets'] ) ) {
					foreach ( $source_gfont_data['subsets'] as $subset ) {
						if ( isset( $subset['subset_selected'] ) && 'true' === $subset['subset_selected'] ) {
							$need_fonts[ $font_name ]['subsets'][] = $subset['subset_value'];
						}
					}
				}

				if ( ! empty( $need_fonts[ $font_name ]['variants'] ) ) {
					foreach ( $need_fonts[ $font_name ]['variants'] as $variant ) {
						$need_fonts[ $font_name ]['variants_css'][ $variant ] = $this->prepare_variant_css( $variant, $font_info, $need_fonts[ $font_name ]['subsets'] );
					}
				} else {
					$need_fonts[ $font_name ]['variants_css'][ $font_info['defVariant'] ] = $this->prepare_variant_css( $font_info['defVariant'], $font_info, $need_fonts[ $font_name ]['subsets'] );
				}


				$font_zip_url = 'https://google-webfonts-helper.herokuapp.com/api/fonts/' . $need_fonts[ $font_name ]['id'];
				$font_zip_url = add_query_arg( 'download', 'zip', $font_zip_url );
				$font_zip_url = add_query_arg( 'formats', 'woff,woff2', $font_zip_url );

				if ( ! empty( $need_fonts[ $font_name ]['variants'] ) ) {
					$variants = implode( ',', $need_fonts[ $font_name ]['variants'] );
				} elseif ( ! empty( $font_info['defVariant'] ) ) {
					$variants = $font_info['defVariant'];
				}

				if ( ! empty( $need_fonts[ $font_name ]['subsets'] ) ) {
					$subsets = implode( ',', $need_fonts[ $font_name ]['subsets'] );
				} elseif ( ! empty( $font_info['defSubset'] ) ) {
					$subsets = $font_info['defSubset'];
				}

				if ( ! empty( $variants ) ) {
					$font_zip_url = add_query_arg( 'variants', $variants, $font_zip_url );
				}
				if ( ! empty( $subsets ) ) {
					$font_zip_url = add_query_arg( 'subsets', $subsets, $font_zip_url );
				}

				$need_fonts[ $font_name ]['zip_url'] = $font_zip_url;

			}
		}


		return $need_fonts;

	}


	/**
	 * @param $variant
	 * @param $font_info
	 * @param $subsets
	 * @param string $fonts_path
	 *
	 * @return array
	 */
	public function prepare_variant_css( $variant, $font_info, $subsets, $fonts_path = '' ) {

		if ( empty( $fonts_path ) ) {
			$upload_dir = wp_upload_dir();
			$fonts_path = $upload_dir['baseurl'] . '/grooni-local-fonts/';
		}

		$font_face = array(
			'font-family' => $font_info['family'],
			'font-style'  => 'normal',
			'font-weight' => '400',
			'src'         => ''
		);

		$parse_variant = stristr( $variant, 'italic', true );

		if ( false !== $parse_variant ) {
			$font_face['font-style'] = 'italic';
			if ( '' !== $parse_variant ) {
				$font_face['font-weight'] = esc_attr( $parse_variant );
			}
		} else {
			if ( 'regular' !== $variant ) {
				$font_face['font-weight'] = esc_attr( $variant );
			}
		}

		if ( ! empty( $font_info['variants'] ) ) {

			foreach ( $font_info['variants'] as $_variant ) {

				if ( $_variant['id'] !== $variant ) {
					continue;
				}

				if ( ! empty( $_variant['fontStyle'] ) ) {
					$font_face['font-style'] = $_variant['fontStyle'];
				}
				if ( ! empty( $_variant['fontWeight'] ) ) {
					$font_face['font-weight'] = $_variant['fontWeight'];
				}
				if ( ! empty( $_variant['local'] ) && is_array( $_variant['local'] ) ) {
					$locals = array();
					foreach ( $_variant['local'] as $local_f ) {
						$locals[] = "local('" . $local_f . "')";
					}
					if ( ! empty( $locals ) ) {
						$font_face['src'] .= implode( ', ', $locals );
					}
				}

				break; // no need search more in that loop

			}

		}

		$subset = '-' . $font_info['defSubset'];
		if ( ! empty( $subsets ) ) {
			sort( $subsets, SORT_STRING );
			$subset = '-' . implode( '_', $subsets );
		}
		$font_face['src'] .= ", url('{$fonts_path}{$font_info['id']}-{$font_info['version']}{$subset}-{$variant}.woff2') format('woff2')";
		$font_face['src'] .= ", url('{$fonts_path}{$font_info['id']}-{$font_info['version']}{$subset}-{$variant}.woff') format('woff')";

		return $font_face;
	}


	/**
	 * Generate css style with font-face param
	 *
	 * @param array $css_data
	 *
	 * @return string
	 */
	public function generate_font_face($css_data ) {

		$font_face = '';

		if ( ! empty( $css_data['font-family'] ) && ! empty( $css_data['font-style'] ) && ! empty( $css_data['font-weight'] ) && ! empty( $css_data['src'] ) ) {
			$font_face .= "
			@font-face {
			  font-family: '{$css_data['font-family']}';
			  font-style: {$css_data['font-style']};
			  font-weight: {$css_data['font-weight']};
			  src: {$css_data['src']};
			}
			";
		}

		return $font_face;
	}


	/**
	 * Download font by URL
	 *
	 * @param string $font_url
	 * @param string $_tmppath
	 *
	 * @return bool
	 */
	public function download_font( $font_url, $_tmppath = '' ) {

		if ( empty( $this->downloaded ) && false !== get_option( $this->g_font_opt_name . '__downloaded' ) ) {
			$this->downloaded = get_option( $this->g_font_opt_name . '__downloaded' );
		}

		if ( empty( $font_url ) ) {
			// if err ...
			return false;
		}

		if ( in_array( $font_url, $this->downloaded, true ) ) {
			return true;
		}

		if ( empty( $_tmppath ) ) {
			$_cpath   = ABSPATH . 'wp-content/uploads/';
			$_tmppath = $_cpath . 'grooni-local-fonts/';
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
			// if err ...
			return false;
		}

		$font_file = null;

		// create temp folder
		$_tmp = wp_tempnam( $font_url );

		@unlink( $_tmp );

		$font_file = download_url( $font_url, 30 );

		if ( ! is_dir( $_tmppath ) ) {
			@mkdir( $_tmppath, 0755 );
		}

		if ( ! is_wp_error( $font_file ) ) {
			$unzip = unzip_file( $font_file, $_tmppath );

			if ( is_wp_error( $unzip ) ) {
				// if err ...
				return false;
			}

			@unlink( $font_file );

			$this->downloaded[] = $font_url;
			update_option( $this->g_font_opt_name . '__downloaded', $this->downloaded, false );

		} else {
			// if err...
			return false;
		}


	}


}
