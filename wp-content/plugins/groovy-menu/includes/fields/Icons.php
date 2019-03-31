<?php defined( 'ABSPATH' ) || die( 'This script cannot be accessed directly.' );

/**
 * Class GroovyMenuFieldIcons
 */
class GroovyMenuFieldIcons extends GroovyMenuFieldField {
	public function renderField() {
		?>
		<div class="gm-gui__module__ui gm-gui__module__import">
			<button class="groovy-upload-icon-pack"
				type="button"><?php esc_html_e( 'Upload icon pack', 'groovy-menu' ); ?></button>
			<input type="hidden" class="groovy-upload-icon" name="icons">
			<?php

			$i = 1;
			foreach ( self::getFonts() as $fontName => $font ) {
				?>
				<div class="groovy-iconset" data-name="<?php echo esc_attr( $fontName ); ?>">
					<span class="groovy-iconset-name"><?php echo esc_html( $font['name'] ); ?></span>
					<a class="groovy-delete-font" href="#"><?php esc_html_e( 'delete', 'groovy-menu' ); ?></a>
					<div class="groovy-icons">
						<?php
						foreach ( $font['icons'] as $icon ) {
							echo '<span class="' . esc_attr( $fontName ) . '-' . esc_attr( $icon['name'] ) . '"></span>';
						}
						?>
					</div>
				</div>
				<?php
				$i ++;
			}
			?>
		</div>
		<?php
	}

	/**
	 * @return array
	 */
	public static function getIcons() {
		$icons = array();
		foreach ( self::getFonts() as $name => $font ) {
			foreach ( $font['icons'] as $icon ) {
				$icon['class'] = $name . '-' . $icon['name'];
				$icons[]       = $icon;
			}
		}

		return $icons;
	}

	/**
	 * @return string
	 */
	public static function getStyles() {
		$styles = '';
		foreach ( self::getFonts() as $name => $font ) {
			$styles .= '<link rel="stylesheet" href="' . esc_url( GroovyMenuUtils::getUploadUri() . 'fonts/' . $name . '.css' ) . '?fontname=1" />';
		}

		return $styles;
	}

	/**
	 * @return mixed|void
	 */
	public static function getFonts() {
		return get_option( GroovyMenuStyle::OPTION_NAME . '_fonts', array() );
	}

	/**
	 * @param $fonts
	 */
	public static function setFonts( $fonts ) {
		update_option( GroovyMenuStyle::OPTION_NAME . '_fonts', $fonts );
	}

	/**
	 * @param $fontName
	 *
	 * @return null
	 */
	public static function getFontByName( $fontName ) {
		$fonts = self::getFonts();
		if ( isset( $fonts[ $fontName ] ) ) {
			return $fonts[ $fontName ][0];
		}

		return null;
	}

}
