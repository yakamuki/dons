<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );
/**
 * Texarea field for Posts Meta.
 *
 * @package crane
 */


class Crane_Meta_Data_Field_Textarea extends Crane_Meta_Data_Field {

	public function renderField( $post_id ) {
		return '<textarea name="' . $this->getName() . '">' . $this->getValue( $post_id ) . '</textarea>';
	}

	public function getValueNew() {
		if ( isset( $_POST[ $this->getName() ] ) ) {
			$value = esc_attr( wp_unslash( $_POST[ $this->getName() ] ) );

			if ( function_exists( 'mb_convert_encoding' ) && function_exists( 'mb_detect_encoding' ) ) {
				$value = mb_convert_encoding( $value, 'utf-8', mb_detect_encoding( $value ) );
			}

			return $value;

		}

		return null;
	}

}
