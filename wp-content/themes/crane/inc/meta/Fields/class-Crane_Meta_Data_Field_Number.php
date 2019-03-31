<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Text field for Posts Meta.
 *
 * @package crane
 */
class Crane_Meta_Data_Field_Number extends Crane_Meta_Data_Field {

	public function renderField( $post_id ) {
		$value = $this->getValue( $post_id );

		$attr = array();

		$attr_arr = $this->getInputAttr();
		if (!empty($attr_arr)) {
			foreach ( $attr_arr as $attr_key => $attr_val ) {
				$attr[] = $attr_key . '="' . $attr_val . '"';
			}
		}

		$attr = empty( $attr ) ? '' : ' ' . implode( ' ', $attr ) . ' ';

		$output = '<div class="crane-meta-field-number">';
		$output .= '<input type="number" class="crane-meta-field-number__front" value="' . $value . '"' . $attr . '>';
		$output .= '<input type="hidden" class="crane-meta-field-number__value" name="' . $this->getName() . '" value="' . $value . '">';
		$output .= '</div>';

		return $output;
	}

	public function getValueNew() {
		if ( isset( $_POST[ $this->getName() ] ) ) {
			$value = esc_attr( wp_unslash( $_POST[ $this->getName() ] ) );

			if ( function_exists( 'mb_convert_encoding' ) && function_exists( 'mb_detect_encoding' ) ) {
				$value = mb_convert_encoding( $value, 'utf-8', mb_detect_encoding( $value ) );
			}

			return intval( $value );
		}

		return null;
	}

}
