<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );
/**
 * Checkbox field for Posts Meta.
 *
 * @package crane
 */


class Crane_Meta_Data_Field_Checkbox extends Crane_Meta_Data_Field {

	public function renderField( $post_id ) {
		$checked = '';
		if ( $this->getValue( $post_id ) ) {
			$checked = ' checked="true"';
		}

		return '<input type="hidden" name="' . $this->getName() . '" value="0" />
		<input type="checkbox" class="switch" name="' . $this->getName() . '" value="1" ' . $checked . ' />';
	}

	public function getValue( $post_id = null ) {
		$value = parent::getValue( $post_id );
		if ( $value === '' ) {
			return $this->getDefault();
		}

		return ( $value === "1" );
	}

	public function getValueNew() {
		$value = null;
		if ( isset( $_POST[ $this->getName() ] ) ) {
			$value = esc_attr( wp_unslash( $_POST[ $this->getName() ] ) );
		}
		if ( ! empty( $value ) && is_array( $value ) ) {
			return $value[1];
		}

		return $value;
	}
}