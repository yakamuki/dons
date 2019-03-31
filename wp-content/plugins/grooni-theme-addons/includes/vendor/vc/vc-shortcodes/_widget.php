<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Add VisualComposer additional shortcodes support.
 *
 * @package Grooni_Theme_Addons
 */
abstract class CT_Vc_Widgets {
	protected $tag = 'ct_vc_shortcode';
	protected $name = 'name';
	protected $description = 'description';
	protected $fields = array();
	protected $as_parent = null;
	protected $as_child = null;
	protected $content_element = true;
	protected $is_container = null;
	protected $js_view = null;
	protected $icon = null;

	function __construct() {

		$this->init_fields();

		add_action( 'init', array( $this, 'init' ) );
		remove_shortcode( $this->tag ); // for what?
		add_shortcode( $this->tag, array( $this, 'render' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'load_css_and_js' ) );
	}

	public function init() {
		vc_add_shortcode_param( 'grooni-number', array( $this, 'number' ) );
		vc_add_shortcode_param( 'grooni-multiple-select', array( $this, 'select2_multiple' ) );
		vc_add_shortcode_param( 'grooni-uniq-id', array( $this, 'uniqid' ) );

		if ( ! defined( 'WPB_VC_VERSION' ) ) {
			add_action( 'admin_notices', array( $this, 'showVcVersionNotice' ) );

			return;
		}

		vc_map( array(
			'name'            => $this->name,
			'description'     => $this->description,
			'base'            => $this->tag,
			'class'           => '',
			'controls'        => 'full',
			'icon'            => $this->icon,
			'category'        => esc_html__( 'Crane', 'grooni-theme-addons' ),
			'params'          => $this->fields,
			'as_parent'       => $this->as_parent,
			'is_container'    => $this->is_container,
			'as_child'        => $this->as_child,
			'content_element' => $this->content_element,
			'js_view'         => $this->js_view
		) );
	}

	abstract public function render( $atts, $content = null );

	public function init_fields() {
		return array();
	}

	public function load_css_and_js() {

	}

	/**
	 * Extra field type for vc_map
	 *
	 * @param $settings
	 * @param $value
	 *
	 * @return string
	 */
	public function number( $settings, $value ) {

		return '<div class="grooni-vc-field grooni-vc-field-number">' .
		       '<div class="grooni-vc-number-range"></div>' .
		       '<input name="' . esc_attr( $settings['param_name'] ) . '" class="wpb_vc_param_value grooni-vc-number-input ' .
		       esc_attr( $settings['type'] ) . '_field ' . esc_attr( $settings['param_name'] ) . '_fieldname" type="number"
                value="' . esc_attr( $value ) . '" max="' . esc_attr( $settings['max'] ) . '"
                min="' . esc_attr( $settings['min'] ) . '" step="1" data-option=""  />' .
		       '</div>';
	}


	/**
	 * Extra field type for vc_map
	 *
	 * @param $settings
	 * @param $value
	 *
	 * @return string
	 */
	public function select2_multiple( $settings, $value ) {

		$sorted_values = array();
		foreach ( explode( ',', $value ) as $slug ) {
			if ( $slug && isset( $settings['value'][ $slug ] ) ) {
				$sorted_values[ $slug ] = $slug;
			}
		}

		$html = '<div class="grooni-vc-field">';
		$html .= '<select multiple="multiple" class="' . esc_attr( $settings['param_name'] ) . '_multiselect">';

		foreach ( array_merge( $sorted_values, $settings['value'] ) as $slug => $name ) {
			$html .= '<option value="' . esc_attr( $slug ) . '"' . ( in_array( $slug, $sorted_values ) ? ' selected="selected"' : '' ) . '>' . $name . '</option>';
		}

		$html .= '</select>';
		$html .= '<input name="' . esc_attr( $settings['param_name'] ) . '" class="wpb_vc_param_value ' .
		         esc_attr( $settings['type'] ) . '_field ' . esc_attr( $settings['param_name'] ) . '_fieldname" type="hidden"
                value="' . esc_attr( $value ) . '" />';

		$html .= '<script type="text/javascript">
						jQuery(\'.' . esc_attr( $settings['param_name'] ) . '_multiselect\').select2({
							closeOnSelect: false,
							width: "100%"
						});
						jQuery(\'.' . esc_attr( $settings['param_name'] ) . '_multiselect\').on("change",function(){
							jQuery(\'.' . esc_attr( $settings['param_name'] ) . '_fieldname\').val(jQuery(this).val());
						});
				</script>';
		$html .= '</div>';

		return $html;

	}

	/**
	 * Extra field type for vc_map
	 *
	 * @param $settings
	 * @param $value
	 *
	 * @return string
	 */
	public function uniqid( $settings, $value ) {

		$uniqid_value = $settings['value'];

		if ( empty( $uniqid_value ) || is_array( $uniqid_value ) ) {
			$uniqid_value = $this->get_new_uniqid( true );
		}

		return '<div style="display:none;">' .
		       '<input name="' . esc_attr( $settings['param_name'] ) . '" class="wpb_vc_param_value ' .
		       esc_attr( $settings['type'] ) . '_field ' . esc_attr( $settings['param_name'] ) . '_fieldname" type="text"
                value="' . esc_attr( $uniqid_value ) . '" />' .
		       '</div>';
	}

	/**
	 * @param bool|false $more_entropy
	 *
	 * @return string
	 */
	public function get_new_uniqid( $more_entropy = false ) {
		$s = uniqid( '', $more_entropy );
		if ( ! $more_entropy ) {
			return base_convert( $s, 16, 36 );
		}
		$hex = substr( $s, 0, 13 );
		$dec = $s[13] . substr( $s, 15 ); // skip the dot
		return base_convert( $hex, 16, 36 ) . base_convert( $dec, 10, 36 );
	}

	/**
	 * Fill empty atts by default values
	 *
	 * @param array $atts
	 *
	 * @return array
	 */
	public function fill_empty_atts( $atts = array() ) {
		foreach ( $this->fields as $field ) {
			$field_name = $field['param_name'];
			$field_std  = isset( $field['std'] ) ? $field['std'] : '';
			if ( '' !== $field_std && ! $field_std && isset( $field['value'] ) ) {
				if ( is_array( $field['value'] ) ) {
					$field_std = empty( $field['value'] ) ? '' : reset( $field['value'] );
				} else {
					$field_std = empty( $field['value'] ) ? '' : $field['value'];
				}
			}

			$atts[ $field_name ] = isset( $atts[ $field_name ] ) ? $atts[ $field_name ] : $field_std;
		}

		return $atts;
	}


	public function getGoogleFonts() {
		$google_fonts = array();

		if ( class_exists( 'Redux' ) && class_exists( 'ReduxFrameworkInstances' ) ) {
			$redux = ReduxFrameworkInstances::get_instance( 'crane_options' );
			$redux->_enqueue_output();
			$typography = $redux->fonts;
		}

		if ( isset( $typography['google'] ) && is_array( $typography['google'] ) ) {
			foreach ( $typography['google'] as $font_name => $font_param ) {
				$google_fonts[ $font_name ] = $font_param;
			}
		}

		return $google_fonts;
	}


	/**
	 * @param $atts
	 *
	 * @return array
	 */
	public function generateStyle( $atts ) {
		if ( empty( $atts ) || ! is_array( $atts ) ) {
			return array();
		}

		if ( ! class_exists( 'Vc_Font_Container' ) && ! class_exists( 'Vc_Google_Fonts' ) && ! function_exists( 'vc_build_safe_css_class' ) ) {
			return array();
		}

		$use_custom_font = isset( $atts['use_custom_font'] ) ? $atts['use_custom_font'] : null;
		$google_fonts    = isset( $atts['google_fonts'] ) ? $atts['google_fonts'] : '';
		$font_container  = isset( $atts['font_container'] ) ? $atts['font_container'] : '';

		if ( ! $use_custom_font || 'true' !== $use_custom_font ) {
			return array();
		}


		$styles = array();


		$font_container_obj  = new Vc_Font_Container();
		$google_fonts_obj    = new Vc_Google_Fonts();
		$font_container_data = strlen( $font_container ) > 0 ? $font_container_obj->_vc_font_container_parse_attributes( array(), $font_container ) : '';
		$google_fonts_data   = strlen( $google_fonts ) > 0 ? $google_fonts_obj->_vc_google_fonts_parse_attributes( array(), $google_fonts ) : '';

		if ( ! empty( $font_container_data ) && isset( $font_container_data['values'] ) ) {
			foreach ( $font_container_data['values'] as $key => $value ) {
				if ( 'tag' !== $key && strlen( $value ) ) {
					if ( preg_match( '/description/', $key ) ) {
						continue;
					}
					if ( 'font_size' === $key || 'line_height' === $key ) {
						$value = preg_replace( '/\s+/', '', $value );
					}
					if ( 'font_size' === $key ) {
						$pattern = '/^(\d*(?:\.\d+)?)\s*(px|\%|in|cm|mm|em|rem|ex|pt|pc|vw|vh|vmin|vmax)?$/';
						// allowed metrics: http://www.w3schools.com/cssref/css_units.asp
						$regexr = preg_match( $pattern, $value, $matches );
						$value  = isset( $matches[1] ) ? (float) $matches[1] : (float) $value;
						$unit   = isset( $matches[2] ) ? $matches[2] : 'px';
						$value  = $value . $unit;
					}
					if ( strlen( $value ) > 0 ) {
						$styles[] = str_replace( '_', '-', $key ) . ': ' . $value;
					}
				}
			}
		}
		if ( ! empty( $google_fonts_data ) && isset( $google_fonts_data['values'], $google_fonts_data['values']['font_family'], $google_fonts_data['values']['font_style'] ) ) {
			$google_fonts_family = explode( ':', $google_fonts_data['values']['font_family'] );
			$styles[]            = 'font-family:' . $google_fonts_family[0];
			$google_fonts_styles = explode( ':', $google_fonts_data['values']['font_style'] );
			$styles[]            = 'font-weight:' . $google_fonts_styles[1];
			$styles[]            = 'font-style:' . $google_fonts_styles[2];

			$settings = get_option( 'wpb_js_google_fonts_subsets' );
			if ( is_array( $settings ) && ! empty( $settings ) ) {
				$subsets = '&subset=' . implode( ',', $settings );
			} else {
				$subsets = '';
			}

			wp_enqueue_style( 'vc_google_fonts_' . vc_build_safe_css_class( $google_fonts_data['values']['font_family'] ), '//fonts.googleapis.com/css?family=' . $google_fonts_data['values']['font_family'] . $subsets );

		}


		return $styles;
	}

	/**
	 * AJAX function for paginate
	 */
	public function load_more_posts_callback() {
		if ( ! is_admin() ) {
			wp_send_json( [ "status" => 0, "error" => "security check" ] );
			wp_die();
		}
		// check nonce security
		check_ajax_referer( 'crane_sec_string', 'security' );

		$params = $output = [ ];
		if ( isset( $_POST['params'] ) && ! empty( $_POST['params'] ) ) {
			$params = (array) wp_unslash( $_POST['params'] );
		}

		if ( empty( $params ) || ! is_array( $params ) ) {
			wp_send_json( [ "status" => 0, "error" => "empty params" ] );
			wp_die();
		}

		if ( isset( $_POST['existItems'] ) && ! empty( $_POST['existItems'] ) ) {
			$params['existItems'] = sanitize_text_field( wp_unslash( $_POST['existItems'] ) );
		}

		foreach ( $params as $param_name => $param_data ) {
			$params[ $param_name ] = sanitize_text_field( $param_data );
		}

		$output['html']   = $this->render( $params );
		$output['status'] = $output['html'] ? 1 : 0;

		wp_send_json( $output );
		wp_die();

	}


	public static function get_image_sizes() {
		global $_wp_additional_image_sizes;

		$sizes = array();

		foreach ( get_intermediate_image_sizes() as $_size ) {
			if ( in_array( $_size, array( 'thumbnail', 'medium', 'medium_large', 'large' ) ) ) {
				$sizes[ $_size ]['width']  = get_option( "{$_size}_size_w" );
				$sizes[ $_size ]['height'] = get_option( "{$_size}_size_h" );
				$sizes[ $_size ]['crop']   = (bool) get_option( "{$_size}_crop" );
			} elseif ( isset( $_wp_additional_image_sizes[ $_size ] ) ) {
				$sizes[ $_size ] = array(
					'width'  => $_wp_additional_image_sizes[ $_size ]['width'],
					'height' => $_wp_additional_image_sizes[ $_size ]['height'],
					'crop'   => $_wp_additional_image_sizes[ $_size ]['crop'],
				);
			}
		}

		return $sizes;
	}


	/**
	 * Get size information for a specific image size.
	 *
	 * @uses   get_image_sizes()
	 *
	 * @param  string $size The image size for which to retrieve data.
	 *
	 * @return bool|array $size Size data about an image size or false if the size doesn't exist.
	 */
	public static function get_image_size( $size ) {
		$sizes = self::get_image_sizes();

		if ( isset( $sizes[ $size ] ) ) {
			return $sizes[ $size ];
		}

		return false;
	}


	public static function get_image_sizes_select_values() {

		$_sizes = array();

		foreach ( self::get_image_sizes() as $size_name => $size_data ) {

			if ( $size_name == 'full' ) {
				$title = __( 'Full Size', 'grooni-theme-addons' );
			} else {

				$title = ( ( $size_data['width'] == 0 ) ? __( 'Any', 'grooni-theme-addons' ) : $size_data['width'] );
				$title .= ' x ';
				$title .= ( $size_data['height'] == 0 ) ? __( 'Any', 'grooni-theme-addons' ) : $size_data['height'];

				if ( $size_data['crop'] ) {
					$title .= ' ' . __( 'cropped', 'grooni-theme-addons' );
				}

			}

			$_sizes[ $size_name ] = $title;

		}

		$_sizes['full'] = esc_html__( 'Full size (original)', 'grooni-theme-addons' );


		return $_sizes;
	}

	/**
	 * Get the width of a specific image size.
	 *
	 * @uses   get_image_size()
	 *
	 * @param  string $size The image size for which to retrieve data.
	 *
	 * @return bool|string $size Width of an image size or false if the size doesn't exist.
	 */
	public static function get_image_width( $size ) {
		if ( ! $size = self::get_image_size( $size ) ) {
			return false;
		}

		if ( isset( $size['width'] ) ) {
			return $size['width'];
		}

		return false;
	}


	/**
	 * Get the height of a specific image size.
	 *
	 * @uses   get_image_size()
	 *
	 * @param  string $size The image size for which to retrieve data.
	 *
	 * @return bool|string $size Height of an image size or false if the size doesn't exist.
	 */
	public static function get_image_height( $size ) {
		if ( ! $size = self::get_image_size( $size ) ) {
			return false;
		}

		if ( isset( $size['height'] ) ) {
			return $size['height'];
		}

		return false;
	}

}
