<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );
/**
 * Radio button field for Posts Meta.
 *
 * @package crane
 */


class Crane_Meta_Data_Field_Radio extends Crane_Meta_Data_Field {

	protected $text;

	public function __construct( $name, $title, $default, $text = array() ) {
		parent::__construct( $name, $title, $default );
		if ( empty( $text ) ) {
			$text = array(
				'0' => esc_html__( 'OFF', 'crane' ),
				'2' => esc_html__( 'Default', 'crane' ),
				'1' => esc_html__( 'ON', 'crane' ),
			);
		}
		$this->text = $text;
	}

	public function renderField( $post_id ) {
		$checked     = ' checked';
		$check_value = $this->getValue( $post_id );

		$return = '<div class="crane-radio-group">';

		foreach ( $this->text as $key => $text ) {
			$key = strval( $key );

			$return .= '
			    <label class="crane-label--radio" for="' . $this->getName() . '-state-'. $key .'">
			        <input type="radio" id="' . $this->getName() . '-state-' . $key . '" class="crane-radio crane-radio__triple" name="triple-name__' . $this->getName() . '" value="' . $key . '"' . ( $check_value === $key ? $checked : '' ) . '>
			        <span>' . $text . '</span>
			    </label>
			';
		}

		$return .= '</div>';
		$return .= '<input type="hidden" name="' . $this->getName() . '" value="' . $check_value . '" />';

		return $return;
	}

	public function getValue( $post_id = null ) {
		$value = parent::getValue( $post_id );

		if ( ! is_string( $value ) || $value === '' ) {
			return $this->getDefault();
		}

		return $value;
	}

	public function getValueNew() {
		$value = null;

		if ( isset( $_POST[ $this->getName() ] ) ) {
			$value = esc_attr( wp_unslash( $_POST[ $this->getName() ] ) );
			if ( empty( $value ) && '0' !== $value ) {
				return $this->getDefault();
			} else {
				return $value;
			}
		}

		return null;
	}
}
