<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );
/**
 * Select field for Posts Meta.
 *
 * @package crane
 */


class Crane_Meta_Data_Field_Select extends Crane_Meta_Data_Field {

	protected $options;

	public function __construct( $name, $title, $default, $options ) {
		parent::__construct( $name, $title, $default );
		$this->options = $options;
	}

	public function renderField( $post_id ) {
		return '<select name="' . $this->getName() . '">' . $this->renderOptions() . '</select>';
	}

	protected function renderOptions() {
		$html = '';
		foreach ( $this->options as $value => $option ) {
			$html .= $this->renderOption( $value, $option );
		}

		return $html;
	}

	protected function renderOption( $value, $text ) {
		$selected = '';

		if ( strval( $value ) === strval( $this->getValue() ) ) {
			$selected = ' selected="selected"';
		}

		return '<option value="' . $value . '"' . $selected . '>' . $text . '</option>';
	}

}
