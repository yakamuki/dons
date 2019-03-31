<?php defined( 'ABSPATH' ) || die( 'This script cannot be accessed directly.' );

/**
 * Class GroovyMenuCategoryPreset
 */
class GroovyMenuCategoryPreset {

	const meta_name = 'gm_custom_preset_id';

	protected $taxanomies = array();

	/**
	 * GroovyMenuCategoryPreset constructor.
	 *
	 * @param $taxanonies
	 */
	public function __construct( $taxanonies ) {
		$this->taxanomies = $taxanonies;
		foreach ( $taxanonies as $tax ) {
			add_action( $tax . '_edit_form_fields', array( $this, 'fields' ), 20 );
			add_action( 'edited_' . $tax, array( $this, 'save' ) );
		}
	}

	/**
	 * @param $tag
	 */
	public function fields( $tag ) {
		$savedPreset = get_term_meta( $tag->term_id, self::meta_name, true );

		$presets = GroovyMenuPreset::getAll();
		?>
		<tr class="form-field term_meta__custom_options__field">
			<th scope="row" valign="top"><label><?php esc_html_e( 'Menu preset', 'groovy-menu' ); ?></label></th>
			<td>
				<select id="groovy-preset" name="groovy_preset">
					<option value=""><?php esc_html_e( 'default', 'groovy-menu' ); ?></option>
					<?php foreach ( $presets as $preset ) { ?>
						<option <?php echo ( ! empty( $savedPreset ) && $savedPreset === $preset->id ) ? 'selected' : '' ?>
							value="<?php echo esc_attr( $preset->id ); ?>"><?php echo esc_html( $preset->name ); ?></option>
					<?php } ?>
				</select>
			</td>
		</tr>

		<?php
	}

	/**
	 * @param $term_id
	 */
	public function save( $term_id ) {
		if ( isset( $_POST['groovy_preset'] ) ) {
			$preset = trim( $_POST['groovy_preset'] );

			update_term_meta( $term_id, self::meta_name, $preset );

			$used_in_storage = get_option( 'groovy_menu_preset_used_in_storage' );
			if ( empty( $used_in_storage ) ) {
				$used_in_storage = array();
			}
			if ( 'default' === $preset && isset( $used_in_storage['taxonomy'][ intval( $term_id ) ] ) ) {
				unset( $used_in_storage['taxonomy'][ intval( $term_id ) ] );
			} elseif ( intval( $preset ) ) {
				$used_in_storage['taxonomy'][ intval( $term_id ) ] = intval( $preset );
			}
			update_option( 'groovy_menu_preset_used_in_storage', $used_in_storage, false );
		}
	}

	/**
	 * @param null $term_id
	 *
	 * @return mixed|null
	 */
	public static function getCurrentPreset( $term_id = null ) {
		if ( empty( $term_id ) ) {

			$current_cat     = get_queried_object();
			$current_term_id = isset( $current_cat->term_id ) ? $current_cat->term_id : null;

			if ( $current_term_id ) {
				$term_id = $current_term_id;
			} else {
				return null;
			}
		}

		return get_term_meta( $term_id, self::meta_name, true );

	}
}
