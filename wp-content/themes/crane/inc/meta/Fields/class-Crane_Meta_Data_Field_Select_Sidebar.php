<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Sidebar selector field for Posts Meta.
 *
 * @package crane
 */
class Crane_Meta_Data_Field_Select_Sidebar extends Crane_Meta_Data_Field {

	protected $options;

	public function __construct( $name, $title, $default ) {
		parent::__construct( $name, $title, $default );

		$this->options = Crane_Sidebars_Creator::get_sidebars();
	}

	public function renderField( $post_id ) {
		return '<select name="' . $this->getName() . '">' . $this->renderOptions() . '</select>';
	}

	protected function renderOptions() {
		$html = $this->renderOption( '0', esc_html__( 'Default sidebar', 'crane' ) );

		if ( ! empty( $this->options ) ) {
			foreach ( $this->options as $value => $option ) {
				$html .= $this->renderOption( $value, $option['name'] );
			}
		}

		return $html;
	}

	protected function renderOption( $value, $text ) {
		$selected = '';

		if ( $value === $this->getValue() ) {
			$selected = ' selected="selected"';
		}

		return '<option value="' . $value . '"' . $selected . '>' . $text . '</option>';
	}

}
