<?php defined( 'ABSPATH' ) || die( 'This script cannot be accessed directly.' );

/**
 * Class GroovyMenuFieldPostTypes
 */
class GroovyMenuFieldPostTypes extends GroovyMenuFieldField {
	public function renderField() {
		$post_types = GroovyMenuUtils::getPostTypesExtended( true, false );

		$saved_types   = explode( ',', $this->getValue() );
		$default_types = explode( ',', $this->getDefault() );

		?>
		<div class="gm-gui__module__ui gm-gui__module__post_types">

			<?php
			foreach ( $post_types as $type_name => $type_label ) {
				$checkbox_state = in_array( $type_name, $saved_types ) ? 'checked' : '';
				$default_state  = in_array( $type_name, $default_types ) ? $type_name : '';
				?>

				<div class="gm-gui__module__ui gm-gui__module__switch-wrapper">
					<div class="gm-gui__module__switch__label"><?php echo esc_html( $type_label ) . ' (' . esc_html( $type_name ) . ')'; ?></div>
					<span class="gm-gui__module__switch__info"><?php esc_html_e( 'off', 'groovy-menu' ); ?></span>
					<input type="checkbox" class="switch" value="<?php echo esc_attr( $type_name ); ?>"
					       data-default="<?php echo esc_attr( $default_state ); ?>" <?php echo esc_attr( $checkbox_state ); ?>>
					<span class="gm-gui__module__switch__info"><?php esc_html_e( 'on', 'groovy-menu' ); ?></span>
				</div>

			<?php } ?>

			<input type="hidden" class="switch gm-post_types" value="<?php echo esc_attr( $this->getValue() ); ?>"
			       name="<?php echo esc_attr( $this->getName() ); ?>" data-default="<?php echo esc_attr( $this->getDefault() ); ?>">

		</div>
		<?php
	}


}
