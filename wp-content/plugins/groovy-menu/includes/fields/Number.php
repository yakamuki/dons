<?php defined( 'ABSPATH' ) || die( 'This script cannot be accessed directly.' );

/**
 * Class GroovyMenuFieldNumber
 */
class GroovyMenuFieldNumber extends GroovyMenuFieldField {
	/**
	 * @return mixed
	 */
	public function getMax() {
		return $this->field['range'][1];
	}

	/**
	 * @return mixed
	 */
	public function getMin() {
		return $this->field['range'][0];
	}

	/**
	 * @return int
	 */
	public function getStep() {
		if ( isset( $this->field['step'] ) ) {
			return $this->field['step'];
		}

		return 1;
	}

	public function renderField() {
		?>
		<div class="gm-gui__module__ui gm-gui__module__number-wrapper">
			<input data-name="<?php echo esc_attr( $this->name ); ?>" class="gm-gui__module__number__input"
			       name="<?php echo esc_attr( $this->getName() ); ?>" type="number" value="<?php echo esc_attr( $this->getValue() ); ?>"
			       data-default="<?php echo esc_attr( $this->getDefault() ); ?>" max="<?php echo esc_attr( $this->getMax() ); ?>"
			       min="<?php echo esc_attr( $this->getMin() ); ?>" step="<?php echo esc_attr( $this->getStep() ); ?>">
			<span class="value">px</span>
		</div>
		<?php
	}

	/**
	 * Get value
	 *
	 * @return false|null|string
	 */
	public function getValue() {
		$value = intval( parent::getValue() );

		return $value;
	}

}
