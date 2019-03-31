<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );
/**
 * Add support for taxonomy meta options.
 *
 * @package crane
 */

add_action( 'category_edit_form_fields', 'crane_extra_blog_cat_fields' );
add_action( 'edited_category', 'crane_save_extra_fields_callback' );

add_action( 'post_tag_edit_form_fields', 'crane_extra_blog_tag_fields' );
add_action( 'edited_post_tag', 'crane_save_extra_fields_callback' );

add_action( 'product_cat_edit_form_fields', 'crane_extra_tax_fields_product_cat', 15 );
add_action( 'edited_product_cat', 'crane_save_extra_fields_callback' );

add_action( 'crane_portfolio_cats_edit_form_fields', 'crane_extra_tax_fields_portfolio_cats' );
add_action( 'edited_crane_portfolio_cats', 'crane_save_extra_fields_callback' );

add_action( 'crane_portfolio_tags_edit_form_fields', 'crane_extra_tax_fields_portfolio_imgtags' );
add_action( 'crane_portfolio_tags_add_form_fields', 'crane_add_extra_tax_fields_portfolio_imgtags', 10, 2 );
add_action( 'created_crane_portfolio_tags', 'crane_save_portfolio_imgtags_new_add', 10, 2 );
add_action( 'edited_crane_portfolio_tags', 'crane_save_extra_fields_callback' );

if ( ! function_exists( 'crane_get_current_category_options' ) ) {
	/**
	 * Get options data bu category Id or current category
	 *
	 * @param null $categoryId
	 *
	 * @return mixed|null|void
	 */
	function crane_get_current_category_options( $categoryId = null ) {
		static $cat_options = array();

		if ( empty( $categoryId ) ) {
			$category = get_category( get_query_var( 'cat' ) );
			if ( ! isset( $category->errors ) ) {
				$categoryId = $category->cat_ID;
			} else {
				return null;
			}
		}

		if ( ! empty( $cat_options[ $categoryId ] ) ) {
			return $cat_options[ $categoryId ];
		}

		$cat_options[ $categoryId ] = maybe_unserialize( get_term_meta( $categoryId, 'crane_term_additional_meta', true ) ) ? : array();

		return $cat_options[ $categoryId ];
	}
}

/**
 * @param $field_params
 */
function crane_add_taxonomy_meta_field__number( $field_params ) {
	$default_params = array(
		'id'      => '',
		'label'   => '',
		'class'   => '',
		'value'   => '',
		'min'     => '',
		'max'     => '',
		'default' => '',
	);
	$field_params   = array_merge( $default_params, $field_params );

	if ( 'default' === $field_params['value'] ) {
		$value = intval( $field_params['default'] );
	} else {
		$value = ( intval( $field_params['value'] ) >= intval( $field_params['min'] ) ) ? intval( $field_params['value'] ) : intval( $field_params['default'] );
	}

	?>
	<tr class="form-field crane-form-meta-type__number <?php echo esc_attr( $field_params['class'] ); ?>"
	    data-term_meta="<?php echo esc_attr( $field_params['id'] ); ?>">
		<th scope="row" valign="top"><span><?php echo esc_html( $field_params['label'] ); ?></span></th>
		<td>
			<input type="number" name="term_meta[<?php echo esc_attr( $field_params['id'] ); ?>]"
			       value="<?php echo esc_attr( $value ); ?>"
			       min="<?php echo intval( $field_params['min'] ); ?>"
			       max="<?php echo intval( $field_params['max'] ); ?>" step="1"
			       class="crane-form-meta-type__number-value">
			<label><input type="checkbox"
			              class="crane-form-meta-type__number-default"> <?php esc_html_e( 'default', 'crane' ); ?>
			</label>
			<input type="hidden" value="<?php echo esc_attr( $field_params['value'] ); ?>"
			       name="term_meta[<?php echo esc_attr( $field_params['id'] ); ?>]"
			       class="crane-form-meta-type__number-main">
		</td>
	</tr>
	<?php
}

/**
 * @param $field_params
 */
function crane_add_taxonomy_meta_field__color( $field_params ) {
	$default_params = array(
		'id'      => '',
		'label'   => '',
		'class'   => '',
		'value'   => '',
		'default' => '',
	);
	$field_params   = array_merge( $default_params, $field_params );

	if ( 'default' === $field_params['value'] ) {
		$value = $field_params['default'];
	} else {
		$value = $field_params['value'];
	}

	?>
    <tr class="form-field crane-form-meta-type__color <?php echo esc_attr( $field_params['class'] ); ?>"
        data-term_meta="<?php echo esc_attr( $field_params['id'] ); ?>">
        <th scope="row" valign="top"><span><?php echo esc_html( $field_params['label'] ); ?></span></th>
        <td>
            <label>
                <input type="checkbox"
                       class="crane-form-meta-type__color-default"> <?php esc_html_e( 'default', 'crane' ); ?>
            </label>
            <div class="crane-form-meta-type__color-value-wrapper">
                <input type="text" name="term_meta[<?php echo esc_attr( $field_params['id'] ); ?>]"
                       value="<?php echo esc_attr( $value ); ?>"
                       class="crane-form-meta-type__color-value crane-color-picker">
            </div>
        </td>
    </tr>
	<?php
}


/**
 * @param $field_params
 */
function crane_add_taxonomy_meta_field__text( $field_params ) {
	$default_params = array(
		'id'      => '',
		'label'   => '',
		'class'   => '',
		'value'   => '',
		'default' => '',
	);
	$field_params   = array_merge( $default_params, $field_params );

	if ( 'default' === $field_params['value'] ) {
		$value = $field_params['default'];
	} else {
		$value = $field_params['value'];
	}

	?>
    <tr class="form-field crane-form-meta-type__text <?php echo esc_attr( $field_params['class'] ); ?>"
        data-term_meta="<?php echo esc_attr( $field_params['id'] ); ?>">
        <th scope="row" valign="top"><span><?php echo esc_html( $field_params['label'] ); ?></span></th>
        <td>
            <label>
                <input type="checkbox"
                       class="crane-form-meta-type__text-default"> <?php esc_html_e( 'default', 'crane' ); ?>
            </label>
            <div class="crane-form-meta-type__text-value-wrapper">
                <input type="text" name="term_meta[<?php echo esc_attr( $field_params['id'] ); ?>]"
                       value="<?php echo esc_attr( $value ); ?>"
                       class="crane-form-meta-type__text-value">
            </div>
        </td>
    </tr>
	<?php
}


/**
 * @param $field_params
 */
function crane_add_taxonomy_meta_field__select( $field_params ) {
	$default_params = array(
		'id'      => '',
		'label'   => '',
		'class'   => '',
		'value'   => '',
		'options' => '',
		'opt_prm' => '',
	);
	$field_params   = array_merge( $default_params, $field_params );

	?>

	<tr class="form-field crane-form-meta-type__select <?php echo esc_attr( $field_params['class'] ); ?>"
	    data-term_meta="<?php echo esc_attr( $field_params['id'] ); ?>">
		<th scope="row" valign="top">
            <label for="crane_term_meta__<?php echo esc_attr( $field_params['id'] ); ?>"><?php echo esc_html( $field_params['label'] ); ?></label>
		</th>
		<td>
			<select name="term_meta[<?php echo esc_attr( $field_params['id'] ); ?>]"
			        id="crane_term_meta__<?php echo esc_attr( $field_params['id'] ); ?>">
				<?php foreach ( $field_params['options'] as $option => $label ) { ?>
					<option <?php echo ( $field_params['value'] === strval( $option ) ) ? 'selected' : '' ?>
						value="<?php echo esc_attr( strval( $option ) ); ?>"><?php echo esc_html( $field_params['opt_prm'] ? $label[ $field_params['opt_prm'] ] : $label ); ?></option>
				<?php } ?>
			</select>
		</td>
	</tr>

	<?php
}


/**
 * @param $field_params
 */
function crane_add_taxonomy_meta_field__padding( $field_params ) {
	$default_params = array(
		'id'      => '',
		'label'   => '',
		'class'   => '',
		'units'   => array( '%', 'px' ),
		'value'   => '',
		'default' => array(
			'padding-top'    => '',
			'padding-bottom' => '',
			'units'          => '%'
		)
	);
	$field_params   = array_merge( $default_params, $field_params );

	$value_explode = is_string( $field_params['value'] ) ? explode( '|', $field_params['value'] ) : array();

	$field_params_value = array();

	if ( count( $value_explode ) !== 3 ) {
		$field_params_value = array(
			'padding-top'    => $field_params['default']['padding-top'],
			'padding-bottom' => $field_params['default']['padding-bottom'],
			'units'          => $field_params['default']['units']
		);
	} else {
		foreach ( $value_explode as $key => $param ) {
			switch ( $key ) {
				case 0 :
					$field_params_value['padding-top'] = $param;
					break;
				case 1 :
					$field_params_value['padding-bottom'] = $param;
					break;
				case 2 :
					$field_params_value['units'] = $param;
					break;
			}
		}
	}


	?>
	<tr class="form-field crane-form-meta-type__padding <?php echo esc_attr( $field_params['class'] ); ?>"
	    data-term_meta="<?php echo esc_attr( $field_params['id'] ); ?>">
		<th scope="row" valign="top"><label
				for="crane_term_meta__<?php echo esc_attr( $field_params['id'] ); ?>"><?php echo esc_html( $field_params['label'] ); ?></label>
		</th>
		<td>
			<div class="crane-form-meta-type__padding-values">
				<input type="number" name="term_meta[<?php echo esc_attr( $field_params['id'] ); ?>]"
				       value="<?php echo intval( $field_params_value['padding-top'] ) ? : intval( $field_params['default']['padding-top'] ); ?>"
				       min="0" max="2000" step="1" class="crane-form-meta-type__padding-value--top">

				<input type="number" name="term_meta[<?php echo esc_attr( $field_params['id'] ); ?>]"
				       value="<?php echo intval( $field_params_value['padding-bottom'] ) ? : intval( $field_params['default']['padding-bottom'] ); ?>"
				       min="0" max="2000" step="1" class="crane-form-meta-type__padding-value--bottom">

				<select class="crane-form-meta-type__padding-value--units">
					<?php foreach ( $field_params['units'] as $units ) { ?>
						<option <?php echo ( $field_params_value['units'] === strval( $units ) ) ? 'selected' : '' ?>
							value="<?php echo esc_attr( strval( $units ) ); ?>"><?php echo esc_html( $units ); ?></option>
					<?php } ?>
				</select>
			</div>

			<label><input type="checkbox"
			              class="crane-form-meta-type__padding-default"> <?php esc_html_e( 'default', 'crane' ); ?>
			</label>
			<input type="hidden" value="<?php echo esc_attr( $field_params['value'] ); ?>"
			       name="term_meta[<?php echo esc_attr( $field_params['id'] ); ?>]"
			       class="crane-form-meta-type__padding-main">
		</td>
	</tr>
	<?php
}


if ( ! function_exists( 'crane_extra_blog_cat_fields' ) ) {
	/**
	 * Extra field for wp taxonomy
	 *
	 * @param $tag
	 */
	function crane_extra_blog_cat_fields( $tag ) {
		// We check for existing taxonomy meta for term ID
		$t_id      = $tag->term_id;
		$term_meta = maybe_unserialize( get_term_meta( $t_id, 'crane_term_additional_meta', true ) );
		if ( empty( $term_meta ) || ! $term_meta ) {
			$term_meta = array();
		}

		$templates = [
			'standard' => esc_html__( 'Standard', 'crane' ),
			'cell'     => esc_html__( 'Cell', 'crane' ),
			'masonry'  => esc_html__( 'Masonry', 'crane' ),
		];

		$has_sidebar = array(
			'default'  => esc_html__( 'Default sidebar', 'crane' ),
			'none'     => esc_html__( 'Hide sidebar', 'crane' ),
			'at-right' => esc_html__( 'At right', 'crane' ),
			'at-left'  => esc_html__( 'At left', 'crane' ),
		);

		$sidebars = Crane_Sidebars_Creator::get_sidebars( true, true );

		$masonry_columns = [
			'default' => esc_html__( 'Default', 'crane' ),
			'2'       => '2',
			'3'       => '3',
			'4'       => '4',
			'5'       => '5',
			'6'       => '6',
			'7'       => '7',
			'8'       => '8',
        ];

		$cell_columns = [
			'default' => esc_html__( 'Default', 'crane' ),
			'1'       => '1',
			'2'       => '2',
			'3'       => '3',
		];

		$styles  = [ 'flat' => esc_html__( 'Flat', 'crane' ), 'corporate' => esc_html__( 'Corporate', 'crane' ), ];

		$paginators = [
			'numbers'   => esc_html__( 'Numeric pagination', 'crane' ),
			'show_more' => esc_html__( 'Numeric pagination with Show more button', 'crane' ),
			'scroll'    => esc_html__( 'Infinity Scroll', 'crane' ),
		];

		$show_post_meta = [
			'author-and-date' => esc_html__( 'Show author info and post date', 'crane' ),
			'date'            => esc_html__( 'Show post date', 'crane' ),
			'none'            => esc_html__( 'Do not show', 'crane' ),
		];

		$target = [
			'same'  => esc_html__( 'Same window', 'crane' ),
			'blank' => esc_html__( 'New window', 'crane' ),
		];

		$checkbox_select = [
			'default' => esc_html__( 'Default', 'crane' ),
			'1'       => esc_html__( 'On', 'crane' ),
			'0'       => esc_html__( 'Off', 'crane' )
		];

		$img_proportion = [
			'default'  => esc_html__( 'Default', 'crane' ),
			'1x1'      => esc_html__( '1:1', 'crane' ),
			'4x3'      => esc_html__( '4:3', 'crane' ),
			'3x2'      => esc_html__( '3:2', 'crane' ),
			'16x9'     => esc_html__( '16:9', 'crane' ),
			'3x4'      => esc_html__( '3:4', 'crane' ),
			'2x3'      => esc_html__( '2:3', 'crane' ),
			'original' => esc_html__( 'Original', 'crane' ),
		];

		$image_sizes_select_values =
			array_merge(
				array( 'default' => esc_html__( 'Default', 'crane' ) ),
				crane_get_image_sizes_select_values()
			);

		?>

		<tr class="form-field">
			<th scope="row" valign="top"><label
					for="term_meta__custom_options"><?php esc_html_e( 'Override global settings', 'crane' ); ?></label>
			</th>
			<td>
				<input type="hidden"
				       value="<?php echo ( ! empty( $term_meta['custom_options'] ) && $term_meta['custom_options'] ) ? '1' : '0' ?>"
				       name="term_meta[custom_options]" id="term_meta__custom_options__val">
				<input type="checkbox" id="term_meta__custom_options">
				<span><?php esc_html_e( 'Override options defined in Theme options', 'crane' ); ?></span>
			</td>
		</tr>

		<tr class="form-field term_meta__custom_options__field">
			<th scope="row" valign="top"><label
					for="term_meta__has_sidebar"><?php esc_html_e( 'Sidebar position', 'crane' ); ?></label>
			</th>
			<td>
				<select name="term_meta[has-sidebar]" id="term_meta__has_sidebar">
					<?php foreach ( $has_sidebar as $key => $position ) { ?>
						<option <?php echo ( ! empty( $term_meta['has-sidebar'] ) && $term_meta['has-sidebar'] === $key ) ? 'selected' : '' ?>
							value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $position ); ?></option>
					<?php } ?>
				</select>
			</td>
		</tr>

		<tr class="form-field term_meta__custom_options__field term_meta__sidebar">
			<th scope="row" valign="top"><label
					for="term_meta__sidebar"><?php esc_html_e( 'Sidebar', 'crane' ); ?></label>
			</th>
			<td>
				<select name="term_meta[sidebar]" id="term_meta__sidebar">
					<?php foreach ( $sidebars as $key => $sidebar ) { ?>
						<option <?php echo ( ! empty( $term_meta['sidebar'] ) && $term_meta['sidebar'] === $key ) ? 'selected' : '' ?>
							value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $sidebar['name'] ); ?></option>
					<?php } ?>
				</select>
			</td>
		</tr>

		<?php
		crane_add_taxonomy_meta_field__number( array(
			'id'      => 'sidebar-width',
			'label'   => esc_html__( 'Sidebar width, %', 'crane' ),
			'class'   => 'term_meta__custom_options__field term_meta__sidebar',
			'value'   => ( isset( $term_meta['sidebar-width'] ) ) ? $term_meta['sidebar-width'] : 'default',
			'min'     => '0',
			'max'     => '100',
			'default' => '25',
		) );
		?>

		<?php
		crane_add_taxonomy_meta_field__number( array(
			'id'      => 'content-width',
			'label'   => esc_html__( 'Page content width, %', 'crane' ),
			'class'   => 'term_meta__custom_options__field term_meta__sidebar',
			'value'   => ( isset( $term_meta['content-width'] ) ) ? $term_meta['content-width'] : 'default',
			'min'     => '0',
			'max'     => '100',
			'default' => '75',
		) );
		?>

		<?php
		crane_add_taxonomy_meta_field__select( array(
			'id'      => 'sticky',
			'label'   => esc_html__( 'Sticky sidebar', 'crane' ),
			'class'   => 'term_meta__custom_options__field term_meta__sidebar',
			'value'   => ( isset( $term_meta['sticky'] ) ) ? $term_meta['sticky'] : 'default',
			'options' => $checkbox_select,
		) );
		?>

		<?php
		crane_add_taxonomy_meta_field__number( array(
			'id'      => 'sticky-offset',
			'label'   => esc_html__( 'Sticky sidebar top offset', 'crane' ),
			'class'   => 'term_meta__custom_options__field term_meta__sidebar',
			'value'   => ( isset( $term_meta['sticky-offset'] ) ) ? $term_meta['sticky-offset'] : 'default',
			'min'     => '0',
			'max'     => '1000',
			'default' => '15',
		) );
		?>

		<?php
		crane_add_taxonomy_meta_field__padding( array(
			'id'      => 'padding',
			'label'   => esc_html__( 'Set top/bottom space of content area and sidebar on desktop', 'crane' ),
			'class'   => 'term_meta__custom_options__field',
			'units'   => array( 'px' ),
			'value'   => ( isset( $term_meta['padding'] ) ) ? $term_meta['padding'] : 'default',
			'default' => array(
				'padding-top'    => '80',
				'padding-bottom' => '80',
				'units'          => 'px'
			)
		) );
		?>

		<?php
		crane_add_taxonomy_meta_field__padding( array(
			'id'      => 'padding-mobile',
			'label'   => esc_html__( 'Set top/bottom space of content area and sidebar on mobile', 'crane' ),
			'class'   => 'term_meta__custom_options__field',
			'units'   => array( 'px' ),
			'value'   => ( isset( $term_meta['padding-mobile'] ) ) ? $term_meta['padding-mobile'] : 'default',
			'default' => array(
				'padding-top'    => '40',
				'padding-bottom' => '40',
				'units'          => 'px'
			)
		) );
		?>

		<tr class="form-field term_meta__custom_options__field">
			<th scope="row" valign="top"><label
					for="term_meta__template"><?php esc_html_e( 'Layout', 'crane' ); ?></label>
			</th>
			<td>
				<select name="term_meta[template]" id="term_meta__template">
					<option value=""><?php esc_html_e( 'Default', 'crane' ); ?></option>
					<?php foreach ( $templates as $key => $template ) { ?>
						<option <?php echo ( ! empty( $term_meta['template'] ) && $term_meta['template'] === $key ) ? 'selected' : '' ?>
							value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $template ); ?></option>
					<?php } ?>
				</select>
			</td>
		</tr>

		<tr class="form-field term_meta__custom_options__field term_meta__template_depend crane-is-masonry">
			<th scope="row" valign="top"><label
					for="term_meta__style"><?php esc_html_e( 'Style', 'crane' ); ?></label></th>
			<td>
				<select name="term_meta[style]" id="term_meta__style">
					<option value=""><?php esc_html_e( 'Default', 'crane' ); ?></option>
					<?php foreach ( $styles as $style => $name ) { ?>
						<option <?php echo ( ! empty( $term_meta['style'] ) && $term_meta['style'] === $style ) ? 'selected' : '' ?>
							value="<?php echo esc_attr( $style ); ?>"><?php echo esc_html( $name ); ?></option>
					<?php } ?>
				</select>
			</td>
		</tr>

		<?php
		crane_add_taxonomy_meta_field__select( array(
			'id'      => 'img_proportion',
			'label'   => esc_html__( 'Top content proportion', 'crane' ),
			'class'   => 'term_meta__custom_options__field term_meta__template_depend crane-is-masonry',
			'value'   => ( isset( $term_meta['img_proportion'] ) ) ? $term_meta['img_proportion'] : 'default',
			'options' => $img_proportion,
		) );
		?>

		<?php
		crane_add_taxonomy_meta_field__select( array(
			'id'      => 'image_resolution',
			'label'   => esc_html__( 'Basic image resolution', 'crane' ),
			'class'   => 'term_meta__custom_options__field',
			'value'   => ( isset( $term_meta['image_resolution'] ) ) ? $term_meta['image_resolution'] : 'default',
			'options' => $image_sizes_select_values,
		) );
		?>

		<?php
		crane_add_taxonomy_meta_field__select( array(
			'id'      => 'masonry_columns',
			'label'   => esc_html__( 'Masonry columns', 'crane' ),
			'class'   => 'term_meta__custom_options__field term_meta__template_depend crane-is-masonry',
			'value'   => ( isset( $term_meta['masonry_columns'] ) ) ? $term_meta['masonry_columns'] : 'default',
			'options' => $masonry_columns,
		) );
		?>

		<?php
		crane_add_taxonomy_meta_field__select( array(
			'id'      => 'cell_columns',
			'label'   => esc_html__( 'Cell columns', 'crane' ),
			'class'   => 'term_meta__custom_options__field term_meta__template_depend crane-is-cell',
			'value'   => ( isset( $term_meta['cell_columns'] ) ) ? $term_meta['cell_columns'] : 'default',
			'options' => $cell_columns,
		) );
		?>

		<?php
		crane_add_taxonomy_meta_field__number( array(
			'id'      => 'grid_spacing',
			'label'   => esc_html__( 'Space between items', 'crane' ),
			'class'   => 'term_meta__custom_options__field term_meta__template_depend crane-is-masonry crane-is-cell',
			'value'   => ( isset( $term_meta['grid_spacing'] ) ) ? $term_meta['grid_spacing'] : 'default',
			'min'     => '0',
			'max'     => '50',
			'default' => '30',
		) );
		?>

		<?php
		crane_add_taxonomy_meta_field__number( array(
			'id'      => 'max_width',
			'label'   => esc_html__( 'Items Stacking Width', 'crane' ),
			'class'   => 'term_meta__custom_options__field term_meta__template_depend crane-is-masonry crane-is-cell',
			'value'   => ( isset( $term_meta['max_width'] ) ) ? $term_meta['max_width'] : 'default',
			'min'     => '300',
			'max'     => '1500',
			'default' => '768',
		) );
		?>

		<?php
		crane_add_taxonomy_meta_field__number( array(
			'id'      => 'post_height_desktop',
			'label'   => esc_html__( 'Post height on desktop', 'crane' ),
			'class'   => 'term_meta__custom_options__field term_meta__template_depend crane-is-cell',
			'value'   => ( isset( $term_meta['post_height_desktop'] ) ) ? $term_meta['post_height_desktop'] : 'default',
			'min'     => '150',
			'max'     => '750',
			'default' => '350',
		) );
		?>

		<?php
		crane_add_taxonomy_meta_field__number( array(
			'id'      => 'post_height_mobile',
			'label'   => esc_html__( 'Post height on mobile', 'crane' ),
			'class'   => 'term_meta__custom_options__field term_meta__template_depend crane-is-cell',
			'value'   => ( isset( $term_meta['post_height_mobile'] ) ) ? $term_meta['post_height_mobile'] : 'default',
			'min'     => '150',
			'max'     => '750',
			'default' => '350',
		) );
		?>

		<?php
		crane_add_taxonomy_meta_field__select( array(
			'id'      => 'show_title_description',
			'label'   => esc_html__( 'Show title?', 'crane' ),
			'class'   => 'term_meta__custom_options__field term_meta__template_depend crane-is-masonry crane-is-cell',
			'value'   => ( isset( $term_meta['show_title_description'] ) ) ? $term_meta['show_title_description'] : 'default',
			'options' => $checkbox_select,
		) );
		?>

		<?php
		crane_add_taxonomy_meta_field__select( array(
			'id'      => 'show_pubdate',
			'label'   => esc_html__( 'Show publication date', 'crane' ),
			'class'   => 'term_meta__custom_options__field term_meta__template_depend crane-is-standard',
			'value'   => ( isset( $term_meta['show_pubdate'] ) ) ? $term_meta['show_pubdate'] : 'default',
			'options' => $checkbox_select,
		) );
		?>

		<?php
		crane_add_taxonomy_meta_field__select( array(
			'id'      => 'show_author',
			'label'   => esc_html__( 'Show author', 'crane' ),
			'class'   => 'term_meta__custom_options__field term_meta__template_depend crane-is-standard',
			'value'   => ( isset( $term_meta['show_author'] ) ) ? $term_meta['show_author'] : 'default',
			'options' => $checkbox_select,
		) );
		?>

		<?php
		crane_add_taxonomy_meta_field__select( array(
			'id'      => 'show_cats',
			'label'   => esc_html__( 'Show categories', 'crane' ),
			'class'   => 'term_meta__custom_options__field term_meta__template_depend crane-is-standard',
			'value'   => ( isset( $term_meta['show_cats'] ) ) ? $term_meta['show_cats'] : 'default',
			'options' => $checkbox_select,
		) );
		?>

		<?php
		crane_add_taxonomy_meta_field__select( array(
			'id'      => 'show_tags',
			'label'   => esc_html__( 'Show tags', 'crane' ),
			'class'   => 'term_meta__custom_options__field',
			'value'   => ( isset( $term_meta['show_tags'] ) ) ? $term_meta['show_tags'] : 'default',
			'options' => $checkbox_select,
		) );
		?>

		<?php
		crane_add_taxonomy_meta_field__select( array(
			'id'      => 'show_excerpt',
			'label'   => esc_html__( 'Show excerpt', 'crane' ),
			'class'   => 'term_meta__custom_options__field term_meta__template_depend crane-is-standard crane-is-masonry crane-is-cell',
			'value'   => ( isset( $term_meta['show_excerpt'] ) ) ? $term_meta['show_excerpt'] : 'default',
			'options' => $checkbox_select,
		) );
		?>

		<?php
		crane_add_taxonomy_meta_field__select( array(
			'id'      => 'excerpt_strip_html',
			'label'   => esc_html__( 'Strip html in except', 'crane' ),
			'class'   => 'term_meta__custom_options__field term_meta__template_depend crane-is-standard crane-is-masonry crane-is-cell',
			'value'   => ( isset( $term_meta['excerpt_strip_html'] ) ) ? $term_meta['excerpt_strip_html'] : 'default',
			'options' => $checkbox_select,
		) );
		?>

		<?php
		crane_add_taxonomy_meta_field__number( array(
			'id'      => 'excerpt_height',
			'label'   => esc_html__( 'Excerpt height', 'crane' ),
			'class'   => 'term_meta__custom_options__field term_meta__template_depend crane-is-masonry crane-is-cell',
			'value'   => ( isset( $term_meta['excerpt_height'] ) ) ? $term_meta['excerpt_height'] : 'default',
			'min'     => '50',
			'max'     => '500',
			'default' => '170',
		) );
		?>

		<?php
		crane_add_taxonomy_meta_field__color( array(
			'id'      => 'cell-item-bg-color',
			'label'   => esc_html__( 'Item background color', 'crane' ),
			'class'   => 'term_meta__custom_options__field term_meta__template_depend crane-is-cell',
			'value'   => ( isset( $term_meta['cell-item-bg-color'] ) ) ? $term_meta['cell-item-bg-color'] : 'default',
			'default' => 'default',
		) );
		?>

		<?php
		crane_add_taxonomy_meta_field__select( array(
			'id'      => 'show_read_more',
			'label'   => esc_html__( 'Show &quot;read more&quot; link', 'crane' ),
			'class'   => 'term_meta__custom_options__field term_meta__template_depend crane-is-standard crane-is-masonry crane-is-cell',
			'value'   => ( isset( $term_meta['show_read_more'] ) ) ? $term_meta['show_read_more'] : 'default',
			'options' => $checkbox_select,
		) );
		?>

		<?php
		crane_add_taxonomy_meta_field__select( array(
			'id'      => 'comment_counter',
			'label'   => esc_html__( 'Show comment counter', 'crane' ),
			'class'   => 'term_meta__custom_options__field term_meta__template_depend crane-is-masonry crane-is-standard',
			'value'   => ( isset( $term_meta['comment_counter'] ) ) ? $term_meta['comment_counter'] : 'default',
			'options' => $checkbox_select,
		) );
		?>

		<?php
		crane_add_taxonomy_meta_field__select( array(
			'id'      => 'show_share_button',
			'label'   => esc_html__( 'Show social share button', 'crane' ),
			'class'   => 'term_meta__custom_options__field term_meta__template_depend crane-is-masonry crane-is-standard',
			'value'   => ( isset( $term_meta['show_share_button'] ) ) ? $term_meta['show_share_button'] : 'default',
			'options' => $checkbox_select,
		) );
		?>

		<tr class="form-field term_meta__custom_options__field term_meta__template_depend crane-is-masonry">
			<th scope="row" valign="top"><label
					for="term_meta__show_post_meta"><?php esc_html_e( 'Show post meta', 'crane' ); ?></label></th>
			<td>
				<select name="term_meta[show_post_meta]" id="term_meta__show_post_meta">
					<option value=""><?php esc_html_e( 'Default', 'crane' ); ?></option>
					<?php foreach ( $show_post_meta as $val => $name ) { ?>
						<option <?php echo ( ! empty( $term_meta['show_post_meta'] ) && $term_meta['show_post_meta'] === $val ) ? 'selected' : '' ?>
							value="<?php echo esc_attr( $val ); ?>"><?php echo esc_html( $name ); ?></option>
					<?php } ?>
				</select>
			</td>
		</tr>

		<tr class="form-field term_meta__custom_options__field">
			<th scope="row" valign="top"><label
					for="term_meta__target"><?php esc_html_e( 'Open on click in', 'crane' ); ?></label></th>
			<td>
				<select name="term_meta[target]" id="term_meta__target">
					<option value=""><?php esc_html_e( 'Default', 'crane' ); ?></option>
					<?php foreach ( $target as $val => $name ) { ?>
						<option <?php echo ( ! empty( $term_meta['target'] ) && $term_meta['target'] === $val ) ? 'selected' : '' ?>
							value="<?php echo esc_attr( $val ); ?>"><?php echo esc_html( $name ); ?></option>
					<?php } ?>
				</select>
			</td>
		</tr>


		<?php
		crane_add_taxonomy_meta_field__select( array(
			'id'      => 'nav_menu',
			'label'   => esc_html__( 'Navigation menu', 'crane' ),
			'class'   => 'term_meta__custom_options__field',
			'value'   => ( isset( $term_meta['nav_menu'] ) ) ? $term_meta['nav_menu'] : '0',
			'options' => crane_get_nav_menus(),
		) );

	}
}


if ( ! function_exists( 'crane_extra_blog_tag_fields' ) ) {
	/**
	 * Extra field for wp taxonomy
	 *
	 * @param $tag
	 */
	function crane_extra_blog_tag_fields( $tag ) {
		// We check for existing taxonomy meta for term ID
		$t_id      = $tag->term_id;
		$term_meta = maybe_unserialize( get_term_meta( $t_id, 'crane_term_additional_meta', true ) );
		if ( empty( $term_meta ) || ! $term_meta ) {
			$term_meta = array();
		}

		$colors = [
			'grey'   => esc_html__( 'grey', 'crane' ),
			'green'  => esc_html__( 'green', 'crane' ),
			'blue'   => esc_html__( 'blue', 'crane' ),
			'orange' => esc_html__( 'orange', 'crane' ),
			'red'    => esc_html__( 'red', 'crane' ),
			'custom' => esc_html__( 'custom', 'crane' ),
		];

		?>

		<tr class="form-field">
			<th scope="row" valign="top"><label><?php esc_html_e( 'Label Color', 'crane' ); ?></label></th>
			<td>
				<select id="category-color-set" name="term_meta[color]">
					<?php foreach ( $colors as $key => $color ) { ?>
						<option <?php echo ( ! empty( $term_meta['color'] ) && $term_meta['color'] === $key ) ? 'selected' : '' ?>
							value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $color ); ?></option>
					<?php } ?>
				</select>

				<span class="crane-custom_category__color" id="category-color-wrap">
					<div class="crane-custom_category__color_item">
						<label for="category-color"><?php esc_html_e( 'Background color', 'crane' ); ?></label><br>
						<input type="text" id="category-color" class="crane-color-picker" name="term_meta[color_custom]"
						       value="<?php echo ! empty( $term_meta['color_custom'] ) ? esc_attr( $term_meta['color_custom'] ) : '' ?>"/>
					</div>
					<div class="crane-custom_category__color_item">
						<label for="category-color-text"><?php esc_html_e( 'Text color', 'crane' ); ?></label><br>
						<input type="text" id="category-color-text" class="crane-color-picker"
						       name="term_meta[color_text_custom]"
						       value="<?php echo ! empty( $term_meta['color_text_custom'] ) ? esc_attr( $term_meta['color_text_custom'] ) : '' ?>"/>
					</div>
				</span>
			</td>
		</tr>
		<?php
		crane_add_taxonomy_meta_field__select( array(
			'id'      => 'nav_menu',
			'label'   => esc_html__( 'Navigation menu', 'crane' ),
			'class'   => 'term_meta__custom_options__field',
			'value'   => ( isset( $term_meta['nav_menu'] ) ) ? $term_meta['nav_menu'] : '0',
			'options' => crane_get_nav_menus(),
		) );
	}
}


if ( ! function_exists( 'crane_extra_tax_fields_product_cat' ) ) {
	/**
	 * Extra field for product taxonomy
	 *
	 * @param $tag
	 */
	function crane_extra_tax_fields_product_cat( $tag ) {

		if ( ! class_exists( 'Redux' ) ) {
			return;
		}

		$has_sidebar = array(
			'default'  => esc_html__( 'Default sidebar', 'crane' ),
			'none'     => esc_html__( 'Hide sidebar', 'crane' ),
			'at-right' => esc_html__( 'At right', 'crane' ),
			'at-left'  => esc_html__( 'At left', 'crane' ),
		);

		$sidebars = Crane_Sidebars_Creator::get_sidebars( true, true );

		// We check for existing taxonomy meta for term ID
		$t_id      = $tag->term_id;
		$term_meta = maybe_unserialize( get_term_meta( $t_id, 'crane_term_additional_meta', true ) ) ? : array();

		$term_meta = [
			'custom_options'                 => isset( $term_meta['custom_options'] ) ? $term_meta['custom_options'] : '',
			'has-sidebar'                    => isset( $term_meta['has-sidebar'] ) ? $term_meta['has-sidebar'] : 'default',
			'sidebar'                        => isset( $term_meta['sidebar'] ) ? $term_meta['sidebar'] : 'default',
			'sidebar-width'                  => isset( $term_meta['sidebar-width'] ) ? $term_meta['sidebar-width'] : 'default',
			'content-width'                  => isset( $term_meta['content-width'] ) ? $term_meta['content-width'] : 'default',
			'sticky'                         => isset( $term_meta['sticky'] ) ? $term_meta['sticky'] : 'default',
			'sticky-offset'                  => isset( $term_meta['sticky-offset'] ) ? $term_meta['sticky-offset'] : 'default',
			'padding'                        => isset( $term_meta['padding'] ) ? $term_meta['padding'] : 'default',
			'padding-mobile'                 => isset( $term_meta['padding-mobile'] ) ? $term_meta['padding-mobile'] : 'default',
			'shop-columns'                   => isset( $term_meta['shop-columns'] ) ? $term_meta['shop-columns'] : 'default',
			'shop-per-page'                  => isset( $term_meta['shop-per-page'] ) ? $term_meta['shop-per-page'] : '',
			'crane-shop-paginator'           => isset( $term_meta['crane-shop-paginator'] ) ? $term_meta['crane-shop-paginator'] : 'default',
			'shop-pagination-prev_next-type' => isset( $term_meta['shop-pagination-prev_next-type'] ) ? $term_meta['shop-pagination-prev_next-type'] : 'default',
			'shop-design'                    => isset( $term_meta['shop-design'] ) ? $term_meta['shop-design'] : 'default',
			'shop-show-star-rating'          => isset( $term_meta['shop-show-star-rating'] ) ? $term_meta['shop-show-star-rating'] : 'default',
			'shop-show-description-excerpt'  => isset( $term_meta['shop-show-description-excerpt'] ) ? $term_meta['shop-show-description-excerpt'] : 'default',
			'shop-show-product-categories'   => isset( $term_meta['shop-show-product-categories'] ) ? $term_meta['shop-show-product-categories'] : 'default',
			'shop-show-product-tags'         => isset( $term_meta['shop-show-product-tags'] ) ? $term_meta['shop-show-product-tags'] : 'default',
			'shop-show-image-type'           => isset( $term_meta['shop-show-image-type'] ) ? $term_meta['shop-show-image-type'] : 'default',
			'shop-show-product-attributes'   => isset( $term_meta['shop-show-product-attributes'] ) ? $term_meta['shop-show-product-attributes'] : '',
		];

		$redux_data   = Redux::getField( 'crane_options', 'shop-columns' );
		$_shop_columns = isset( $redux_data['options'] ) ? $redux_data['options'] : array();
		$shop_columns = array( 'default' => esc_html__( 'Default', 'crane' ) );
		foreach ( $_shop_columns as $num ) {
			$shop_columns[ strval( $num ) ] = $num;
		}

		$redux_data           = Redux::getField( 'crane_options', 'crane-shop-paginator' );
		$crane_shop_paginator = isset( $redux_data['options'] ) ? $redux_data['options'] : array();
		$crane_shop_paginator = array_merge( [ 'default' => esc_html__( 'Default', 'crane' ) ], $crane_shop_paginator );

		$redux_data                     = Redux::getField( 'crane_options', 'shop-pagination-prev_next-type' );
		$crane_shop_paginator_prev_next = isset( $redux_data['options'] ) ? $redux_data['options'] : array();
		$crane_shop_paginator_prev_next = array_merge( [ 'default' => esc_html__( 'Default', 'crane' ) ], $crane_shop_paginator_prev_next );

		$redux_data  = Redux::getField( 'crane_options', 'shop-design' );
		$shop_design = isset( $redux_data['options'] ) ? $redux_data['options'] : array();
		$shop_design = array_merge( [ 'default' => esc_html__( 'Default', 'crane' ) ], $shop_design );

		$redux_data           = Redux::getField( 'crane_options', 'shop-show-image-type' );
		$shop_show_image_type = isset( $redux_data['options'] ) ? $redux_data['options'] : array();
		$shop_show_image_type = array_merge( [ 'default' => esc_html__( 'Default', 'crane' ) ], $shop_show_image_type );

		$checkbox_select = [
			'default' => esc_html__( 'Default', 'crane' ),
			'1'       => esc_html__( 'On', 'crane' ),
			'0'       => esc_html__( 'Off', 'crane' )
		];

		?>

		<tr class="form-field">
			<th scope="row" valign="top"><label
					for="term_meta__custom_options"><?php esc_html_e( 'Override global settings', 'crane' ); ?></label>
			</th>
			<td>
				<input type="hidden" value="<?php echo ( $term_meta['custom_options'] ) ? '1' : '0' ?>"
				       name="term_meta[custom_options]" id="term_meta__custom_options__val">
				<input type="checkbox" id="term_meta__custom_options">
				<span><?php esc_html_e( 'Override options defined in Theme options', 'crane' ); ?></span>
			</td>
		</tr>

		<tr class="form-field term_meta__custom_options__field">
			<th scope="row" valign="top"><label
					for="term_meta__has_sidebar"><?php esc_html_e( 'Sidebar position', 'crane' ); ?></label>
			</th>
			<td>
				<select name="term_meta[has-sidebar]" id="term_meta__has_sidebar">
					<?php foreach ( $has_sidebar as $key => $position ) { ?>
						<option <?php echo ( ! empty( $term_meta['has-sidebar'] ) && $term_meta['has-sidebar'] === $key ) ? 'selected' : '' ?>
							value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $position ); ?></option>
					<?php } ?>
				</select>
			</td>
		</tr>

		<?php
		crane_add_taxonomy_meta_field__select( array(
			'id'      => 'sidebar',
			'label'   => esc_html__( 'Sidebar', 'crane' ),
			'class'   => 'term_meta__custom_options__field term_meta__sidebar',
			'value'   => ( isset( $term_meta['sidebar'] ) ) ? $term_meta['sidebar'] : 'default',
			'options' => $sidebars,
			'opt_prm' => 'name'
		) );
		?>

		<?php
		crane_add_taxonomy_meta_field__number( array(
			'id'      => 'sidebar-width',
			'label'   => esc_html__( 'Sidebar width, %', 'crane' ),
			'class'   => 'term_meta__custom_options__field term_meta__sidebar',
			'value'   => ( isset( $term_meta['sidebar-width'] ) ) ? $term_meta['sidebar-width'] : 'default',
			'min'     => '0',
			'max'     => '100',
			'default' => '25',
		) );
		?>

		<?php
		crane_add_taxonomy_meta_field__number( array(
			'id'      => 'content-width',
			'label'   => esc_html__( 'Page content width, %', 'crane' ),
			'class'   => 'term_meta__custom_options__field term_meta__sidebar',
			'value'   => ( isset( $term_meta['content-width'] ) ) ? $term_meta['content-width'] : 'default',
			'min'     => '0',
			'max'     => '100',
			'default' => '75',
		) );
		?>

		<?php
		crane_add_taxonomy_meta_field__select( array(
			'id'      => 'sticky',
			'label'   => esc_html__( 'Sticky sidebar', 'crane' ),
			'class'   => 'term_meta__custom_options__field term_meta__sidebar',
			'value'   => ( isset( $term_meta['sticky'] ) ) ? $term_meta['sticky'] : 'default',
			'options' => $checkbox_select,
		) );
		?>

		<?php
		crane_add_taxonomy_meta_field__number( array(
			'id'      => 'sticky-offset',
			'label'   => esc_html__( 'Sticky sidebar top offset', 'crane' ),
			'class'   => 'term_meta__custom_options__field term_meta__sidebar',
			'value'   => ( isset( $term_meta['sticky-offset'] ) ) ? $term_meta['sticky-offset'] : 'default',
			'min'     => '0',
			'max'     => '1000',
			'default' => '15',
		) );
		?>

		<?php
		crane_add_taxonomy_meta_field__padding( array(
			'id'      => 'padding',
			'label'   => esc_html__( 'Set top/bottom space of content area and sidebar on desktop', 'crane' ),
			'class'   => 'term_meta__custom_options__field',
			'units'   => array( 'px' ),
			'value'   => ( isset( $term_meta['padding'] ) ) ? $term_meta['padding'] : 'default',
			'default' => array(
				'padding-top'    => '80',
				'padding-bottom' => '80',
				'units'          => 'px'
			)
		) );
		?>

		<?php
		crane_add_taxonomy_meta_field__padding( array(
			'id'      => 'padding-mobile',
			'label'   => esc_html__( 'Set top/bottom space of content area and sidebar on mobile', 'crane' ),
			'class'   => 'term_meta__custom_options__field',
			'units'   => array( 'px' ),
			'value'   => ( isset( $term_meta['padding-mobile'] ) ) ? $term_meta['padding-mobile'] : 'default',
			'default' => array(
				'padding-top'    => '40',
				'padding-bottom' => '40',
				'units'          => 'px'
			)
		) );
		?>

		<?php
		crane_add_taxonomy_meta_field__select( array(
			'id'      => 'shop-columns',
			'label'   => esc_html__( 'Number of columns', 'crane' ),
			'class'   => 'term_meta__custom_options__field',
			'value'   => ( isset( $term_meta['shop-columns'] ) ) ? $term_meta['shop-columns'] : 'default',
			'options' => $shop_columns,
		) );
		?>

		<?php
		crane_add_taxonomy_meta_field__number( array(
			'id'      => 'shop-per-page',
			'label'   => esc_html__( 'Products per page', 'crane' ),
			'class'   => 'term_meta__custom_options__field',
			'value'   => ( isset( $term_meta['shop-per-page'] ) ) ? $term_meta['shop-per-page'] : 'default',
			'min'     => '1',
			'max'     => '100',
			'default' => '12',
		) );
		?>

		<?php
		crane_add_taxonomy_meta_field__select( array(
			'id'      => 'crane-shop-paginator',
			'label'   => esc_html__( 'Pagination', 'crane' ),
			'class'   => 'term_meta__custom_options__field',
			'value'   => ( isset( $term_meta['crane-shop-paginator'] ) ) ? $term_meta['crane-shop-paginator'] : 'default',
			'options' => $crane_shop_paginator,
		) );
		?>

		<?php
		crane_add_taxonomy_meta_field__select( array(
			'id'      => 'shop-pagination-prev_next-type',
			'label'   => esc_html__( 'Select type of next/prev buttons', 'crane' ),
			'class'   => 'term_meta__custom_options__field',
			'value'   => ( isset( $term_meta['shop-pagination-prev_next-type'] ) ) ? $term_meta['shop-pagination-prev_next-type'] : 'default',
			'options' => $crane_shop_paginator_prev_next,
		) );
		?>

		<?php
		crane_add_taxonomy_meta_field__select( array(
			'id'      => 'shop-design',
			'label'   => esc_html__( 'Shop design', 'crane' ),
			'class'   => 'term_meta__custom_options__field',
			'value'   => ( isset( $term_meta['shop-design'] ) ) ? $term_meta['shop-design'] : 'default',
			'options' => $shop_design,
		) );
		?>

		<?php
		crane_add_taxonomy_meta_field__select( array(
			'id'      => 'shop-show-star-rating',
			'label'   => esc_html__( 'Show product rating', 'crane' ),
			'class'   => 'term_meta__custom_options__field',
			'value'   => ( isset( $term_meta['shop-show-star-rating'] ) ) ? $term_meta['shop-show-star-rating'] : 'default',
			'options' => $checkbox_select,
		) );
		?>

		<?php
		crane_add_taxonomy_meta_field__select( array(
			'id'      => 'shop-show-description-excerpt',
			'label'   => esc_html__( 'Show product description', 'crane' ),
			'class'   => 'term_meta__custom_options__field',
			'value'   => ( isset( $term_meta['shop-show-description-excerpt'] ) ) ? $term_meta['shop-show-description-excerpt'] : 'default',
			'options' => $checkbox_select,
		) );
		?>

		<?php
		crane_add_taxonomy_meta_field__select( array(
			'id'      => 'shop-show-product-categories',
			'label'   => esc_html__( 'Show product categories', 'crane' ),
			'class'   => 'term_meta__custom_options__field',
			'value'   => ( isset( $term_meta['shop-show-product-categories'] ) ) ? $term_meta['shop-show-product-categories'] : 'default',
			'options' => $checkbox_select,
		) );
		?>

		<?php
		crane_add_taxonomy_meta_field__select( array(
			'id'      => 'shop-show-product-tags',
			'label'   => esc_html__( 'Show product tags', 'crane' ),
			'class'   => 'term_meta__custom_options__field',
			'value'   => ( isset( $term_meta['shop-show-product-tags'] ) ) ? $term_meta['shop-show-product-tags'] : 'default',
			'options' => $checkbox_select,
		) );
		?>

		<?php
		crane_add_taxonomy_meta_field__select( array(
			'id'      => 'shop-show-image-type',
			'label'   => esc_html__( 'Product image type', 'crane' ),
			'class'   => 'term_meta__custom_options__field',
			'value'   => ( isset( $term_meta['shop-show-image-type'] ) ) ? $term_meta['shop-show-image-type'] : 'default',
			'options' => $shop_show_image_type,
		) );
		?>

		<tr class="form-field term_meta__custom_options__field">
			<th scope="row" valign="top">
				<label><?php esc_html_e( 'Select product attributes to show (if exist)', 'crane' ); ?></label>
			</th>
			<td>
				<input type="hidden" value="<?php echo esc_attr( $term_meta['shop-show-product-attributes'] ); ?>"
				       name="term_meta[shop-show-product-attributes]" id="term_meta__product_attributes__val">
				<select id="term_meta__product_attributes" multiple>
					<option <?php echo ( empty( $term_meta['shop-show-product-attributes'] ) ) ? 'selected' : ''; ?>
						value="default"><?php esc_html_e( 'default', 'crane' ); ?></option>
					<?php foreach ( crane_wc_get_attribute_taxonomies() as $key => $product_attributes ) { ?>
						<option
							value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $product_attributes ); ?></option>
					<?php } ?>
				</select>
			</td>
		</tr>

		<?php
		crane_add_taxonomy_meta_field__select( array(
			'id'      => 'nav_menu',
			'label'   => esc_html__( 'Navigation menu', 'crane' ),
			'class'   => 'term_meta__custom_options__field',
			'value'   => ( isset( $term_meta['nav_menu'] ) ) ? $term_meta['nav_menu'] : '0',
			'options' => crane_get_nav_menus(),
		) );

	}
}


if ( ! function_exists( 'crane_extra_tax_fields_portfolio_cats' ) ) {
	/**
	 * Extra field for wp taxonomy
	 *
	 * @param $tag
	 */
	function crane_extra_tax_fields_portfolio_cats( $tag ) {
		// We check for existing taxonomy meta for term ID
		$t_id      = $tag->term_id;
		$term_meta = maybe_unserialize( get_term_meta( $t_id, 'crane_term_additional_meta', true ) );
		if ( empty( $term_meta ) || ! $term_meta ) {
			$term_meta = array();
		}

		$layout = [
			'grid'    => esc_html__( 'Grid', 'crane' ),
			'masonry' => esc_html__( 'Masonry', 'crane' ),
		];

		$layout_mode = [
			'default' => esc_html__( 'Default', 'crane' ),
			'masonry' => esc_html__( 'Standard', 'crane' ),
			'fitRows' => esc_html__( 'Fit rows', 'crane' ),
		];

		$style = [
			'default' => esc_html__( 'Default', 'crane' ),
			'flat'    => esc_html__( 'Flat', 'crane' ),
			'minimal' => esc_html__( 'Minimal', 'crane' ),
			'modern'  => esc_html__( 'Modern', 'crane' )
		];

		$hover_style = [
			'default' => esc_html__( 'Default', 'crane' ),
			1         => esc_html__( 'Direction-aware hover', 'crane' ),
			2         => esc_html__( 'Overlay with zoom and link icons on hover', 'crane' ),
			3         => esc_html__( 'Zoom image on hover', 'crane' ),
			4         => esc_html__( 'Just link', 'crane' ),
			5         => esc_html__( 'Shuffle text link', 'crane' ),
		];

		$show_imgtags = [
			'default' => esc_html__( 'Default', 'crane' ),
			'0'       => esc_html__( 'No tags', 'crane' ),
			'text'    => esc_html__( 'Text tags', 'crane' ),
			'image'   => esc_html__( 'Image tags', 'crane' ),
		];

		$sortable_align = [
			'default' => esc_html__( 'Default', 'crane' ),
			'left'    => esc_html__( 'Left', 'crane' ),
			'right'   => esc_html__( 'Right', 'crane' ),
			'center'  => esc_html__( 'Center', 'crane' ),
		];

		$sortable_style = [
			'default' => esc_html__( 'Default (as in theme options)', 'crane' ),
			'in_grid' => esc_html__( 'Default', 'crane' ),
			'outline' => esc_html__( 'Custom', 'crane' ),
		];

		$img_proportion = [
			'default'  => esc_html__( 'Default', 'crane' ),
			'4x3'      => esc_html__( '4:3', 'crane' ),
			'3x2'      => esc_html__( '3:2', 'crane' ),
			'16x9'     => esc_html__( '16:9', 'crane' ),
			'1x1'      => esc_html__( '1:1', 'crane' ),
			'3x4'      => esc_html__( '3:4', 'crane' ),
			'2x3'      => esc_html__( '2:3', 'crane' ),
			'original' => esc_html__( 'Original', 'crane' ),
		];

		$image_sizes_select_values =
			array_merge(
				array( 'default' => esc_html__( 'Default', 'crane' ) ),
				crane_get_image_sizes_select_values()
			);

		$has_sidebar = array(
			'default'  => esc_html__( 'Default sidebar', 'crane' ),
			'none'     => esc_html__( 'Hide sidebar', 'crane' ),
			'at-right' => esc_html__( 'At right', 'crane' ),
			'at-left'  => esc_html__( 'At left', 'crane' ),
		);

		$sidebars = Crane_Sidebars_Creator::get_sidebars( true, true );

		$columns = [ 2, 3, 4, 5, 6, 7, 8 ];
		$styles  = [ 'flat' => esc_html__( 'Flat', 'crane' ), 'corporate' => esc_html__( 'Corporate', 'crane' ), ];

		$target = [
			'default' => esc_html__( 'Default', 'crane' ),
			'same'    => esc_html__( 'Same window', 'crane' ),
			'blank'   => esc_html__( 'New window', 'crane' ),
		];

		$checkbox_select = [
			'default' => esc_html__( 'Default', 'crane' ),
			'1'       => esc_html__( 'On', 'crane' ),
			'0'       => esc_html__( 'Off', 'crane' )
		];

		?>

		<tr class="form-field">
			<th scope="row" valign="top"><label
					for="term_meta__custom_options"><?php esc_html_e( 'Override global settings', 'crane' ); ?></label>
			</th>
			<td>
				<input type="hidden"
				       value="<?php echo ( ! empty( $term_meta['custom_options'] ) && $term_meta['custom_options'] ) ? '1' : '0' ?>"
				       name="term_meta[custom_options]" id="term_meta__custom_options__val">
				<input type="checkbox" id="term_meta__custom_options">
				<span><?php esc_html_e( 'Override options defined in Theme options', 'crane' ); ?></span>
			</td>
		</tr>

		<tr class="form-field term_meta__custom_options__field">
			<th scope="row" valign="top"><label
					for="term_meta__has_sidebar"><?php esc_html_e( 'Sidebar position', 'crane' ); ?></label>
			</th>
			<td>
				<select name="term_meta[has-sidebar]" id="term_meta__has_sidebar">
					<?php foreach ( $has_sidebar as $key => $position ) { ?>
						<option <?php echo ( ! empty( $term_meta['has-sidebar'] ) && $term_meta['has-sidebar'] === $key ) ? 'selected' : '' ?>
							value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $position ); ?></option>
					<?php } ?>
				</select>
			</td>
		</tr>

		<?php
		crane_add_taxonomy_meta_field__select( array(
			'id'      => 'sidebar',
			'label'   => esc_html__( 'Sidebar', 'crane' ),
			'class'   => 'term_meta__custom_options__field term_meta__sidebar',
			'value'   => ( isset( $term_meta['sidebar'] ) ) ? $term_meta['sidebar'] : 'default',
			'options' => $sidebars,
			'opt_prm' => 'name'
		) );
		?>

		<?php
		crane_add_taxonomy_meta_field__number( array(
			'id'      => 'sidebar-width',
			'label'   => esc_html__( 'Sidebar width, %', 'crane' ),
			'class'   => 'term_meta__custom_options__field term_meta__sidebar',
			'value'   => ( isset( $term_meta['sidebar-width'] ) ) ? $term_meta['sidebar-width'] : 'default',
			'min'     => '0',
			'max'     => '100',
			'default' => '25',
		) );
		?>

		<?php
		crane_add_taxonomy_meta_field__number( array(
			'id'      => 'content-width',
			'label'   => esc_html__( 'Page content width, %', 'crane' ),
			'class'   => 'term_meta__custom_options__field term_meta__sidebar',
			'value'   => ( isset( $term_meta['content-width'] ) ) ? $term_meta['content-width'] : 'default',
			'min'     => '0',
			'max'     => '100',
			'default' => '75',
		) );
		?>

		<?php
		crane_add_taxonomy_meta_field__select( array(
			'id'      => 'sticky',
			'label'   => esc_html__( 'Sticky sidebar', 'crane' ),
			'class'   => 'term_meta__custom_options__field term_meta__sidebar',
			'value'   => ( isset( $term_meta['sticky'] ) ) ? $term_meta['sticky'] : 'default',
			'options' => $checkbox_select,
		) );
		?>

		<?php
		crane_add_taxonomy_meta_field__number( array(
			'id'      => 'sticky-offset',
			'label'   => esc_html__( 'Sticky sidebar top offset', 'crane' ),
			'class'   => 'term_meta__custom_options__field term_meta__sidebar',
			'value'   => ( isset( $term_meta['sticky-offset'] ) ) ? $term_meta['sticky-offset'] : 'default',
			'min'     => '0',
			'max'     => '1000',
			'default' => '15',
		) );
		?>

		<?php
		crane_add_taxonomy_meta_field__padding( array(
			'id'      => 'padding',
			'label'   => esc_html__( 'Set top/bottom space of content area and sidebar on desktop', 'crane' ),
			'class'   => 'term_meta__custom_options__field',
			'units'   => array( 'px' ),
			'value'   => ( isset( $term_meta['padding'] ) ) ? $term_meta['padding'] : 'default',
			'default' => array(
				'padding-top'    => '80',
				'padding-bottom' => '80',
				'units'          => 'px'
			)
		) );
		?>

		<?php
		crane_add_taxonomy_meta_field__padding( array(
			'id'      => 'padding-mobile',
			'label'   => esc_html__( 'Set top/bottom space of content area and sidebar on mobile', 'crane' ),
			'class'   => 'term_meta__custom_options__field',
			'units'   => array( 'px' ),
			'value'   => ( isset( $term_meta['padding-mobile'] ) ) ? $term_meta['padding-mobile'] : 'default',
			'default' => array(
				'padding-top'    => '40',
				'padding-bottom' => '40',
				'units'          => 'px'
			)
		) );
		?>

		<tr class="form-field term_meta__custom_options__field">
			<th scope="row" valign="top"><label
					for="term_meta__layout"><?php esc_html_e( 'Layout', 'crane' ); ?></label>
			</th>
			<td>
				<select name="term_meta[layout]" id="term_meta__layout">
					<option value=""><?php esc_html_e( 'Default', 'crane' ); ?></option>
					<?php foreach ( $layout as $key => $layout_name ) { ?>
						<option <?php echo ( ! empty( $term_meta['layout'] ) && $term_meta['layout'] === $key ) ? 'selected' : '' ?>
							value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $layout_name ); ?></option>
					<?php } ?>
				</select>
			</td>
		</tr>

		<?php
		crane_add_taxonomy_meta_field__select( array(
			'id'      => 'layout_mode',
			'label'   => esc_html__( 'Layout mode', 'crane' ),
			'class'   => 'term_meta__custom_options__field',
			'value'   => ( isset( $term_meta['layout_mode'] ) ) ? $term_meta['layout_mode'] : 'default',
			'options' => $layout_mode,
		) );
		?>

		<?php
		crane_add_taxonomy_meta_field__select( array(
			'id'      => 'img_proportion',
			'label'   => esc_html__( 'Image proportion', 'crane' ),
			'class'   => 'term_meta__custom_options__field',
			'value'   => ( isset( $term_meta['img_proportion'] ) ) ? $term_meta['img_proportion'] : 'default',
			'options' => $img_proportion,
		) );
		?>

		<?php
		crane_add_taxonomy_meta_field__select( array(
			'id'      => 'style',
			'label'   => esc_html__( 'Style', 'crane' ),
			'class'   => 'term_meta__custom_options__field',
			'value'   => ( isset( $term_meta['style'] ) ) ? $term_meta['style'] : 'default',
			'options' => $style,
		) );
		?>

		<?php
		crane_add_taxonomy_meta_field__select( array(
			'id'      => 'image_resolution',
			'label'   => esc_html__( 'Basic image resolution', 'crane' ),
			'class'   => 'term_meta__custom_options__field',
			'value'   => ( isset( $term_meta['image_resolution'] ) ) ? $term_meta['image_resolution'] : 'default',
			'options' => $image_sizes_select_values,
		) );
		?>

		<?php
		crane_add_taxonomy_meta_field__select( array(
			'id'      => 'hover_style',
			'label'   => esc_html__( 'Hover style', 'crane' ),
			'class'   => 'term_meta__custom_options__field',
			'value'   => ( isset( $term_meta['hover_style'] ) ) ? $term_meta['hover_style'] : 'default',
			'options' => $hover_style,
		) );
		?>

		<?php
		crane_add_taxonomy_meta_field__number( array(
			'id'      => 'grid_spacing',
			'label'   => esc_html__( 'Space between items', 'crane' ),
			'class'   => 'term_meta__custom_options__field',
			'value'   => ( isset( $term_meta['grid_spacing'] ) ) ? $term_meta['grid_spacing'] : 'default',
			'min'     => '0',
			'max'     => '100',
			'default' => '30',
		) );
		?>

		<?php
		crane_add_taxonomy_meta_field__number( array(
			'id'      => 'columns',
			'label'   => esc_html__( 'How many Columns?', 'crane' ),
			'class'   => 'term_meta__custom_options__field',
			'value'   => ( isset( $term_meta['columns'] ) ) ? $term_meta['columns'] : 'default',
			'min'     => '2',
			'max'     => '8',
			'default' => '4',
		) );
		?>

		<?php
		crane_add_taxonomy_meta_field__number( array(
			'id'      => 'max_width',
			'label'   => esc_html__( 'Items Stacking Width', 'crane' ),
			'class'   => 'term_meta__custom_options__field',
			'value'   => ( isset( $term_meta['max_width'] ) ) ? $term_meta['max_width'] : 'default',
			'min'     => '100',
			'max'     => '2000',
			'default' => '768',
		) );
		?>

		<?php
		crane_add_taxonomy_meta_field__number( array(
			'id'      => 'posts_limit',
			'label'   => esc_html__( 'How many posts?', 'crane' ),
			'class'   => 'term_meta__custom_options__field',
			'value'   => ( isset( $term_meta['posts_limit'] ) ) ? $term_meta['posts_limit'] : 'default',
			'min'     => '0',
			'max'     => '100',
			'default' => '12',
		) );
		?>

		<?php
		crane_add_taxonomy_meta_field__select( array(
			'id'      => 'show_title_description',
			'label'   => esc_html__( 'Show title?', 'crane' ),
			'class'   => 'term_meta__custom_options__field',
			'value'   => ( isset( $term_meta['show_title_description'] ) ) ? $term_meta['show_title_description'] : 'default',
			'options' => $checkbox_select,
		) );
		?>

		<?php
		crane_add_taxonomy_meta_field__select( array(
			'id'      => 'show_categories',
			'label'   => esc_html__( 'Show categories?', 'crane' ),
			'class'   => 'term_meta__custom_options__field',
			'value'   => ( isset( $term_meta['show_categories'] ) ) ? $term_meta['show_categories'] : 'default',
			'options' => $checkbox_select,
		) );
		?>

		<?php
		crane_add_taxonomy_meta_field__select( array(
			'id'      => 'show_custom_text',
			'label'   => esc_html__( 'Show custom text from meta?', 'crane' ),
			'class'   => 'term_meta__custom_options__field',
			'value'   => ( isset( $term_meta['show_custom_text'] ) ) ? $term_meta['show_custom_text'] : 'default',
			'options' => $checkbox_select,
		) );
		?>

		<?php
		crane_add_taxonomy_meta_field__select( array(
			'id'      => 'show_excerpt',
			'label'   => esc_html__( 'Show Excerpt?', 'crane' ),
			'class'   => 'term_meta__custom_options__field',
			'value'   => ( isset( $term_meta['show_excerpt'] ) ) ? $term_meta['show_excerpt'] : 'default',
			'options' => $checkbox_select,
		) );
		?>

		<?php
		crane_add_taxonomy_meta_field__select( array(
			'id'      => 'excerpt_strip_html',
			'label'   => esc_html__( 'Strip html in except?', 'crane' ),
			'class'   => 'term_meta__custom_options__field',
			'value'   => ( isset( $term_meta['excerpt_strip_html'] ) ) ? $term_meta['excerpt_strip_html'] : 'default',
			'options' => $checkbox_select,
		) );
		?>

		<?php
		crane_add_taxonomy_meta_field__number( array(
			'id'      => 'excerpt_height',
			'label'   => esc_html__( 'Excerpt height', 'crane' ),
			'class'   => 'term_meta__custom_options__field',
			'value'   => ( isset( $term_meta['excerpt_height'] ) ) ? $term_meta['excerpt_height'] : 'default',
			'min'     => '50',
			'max'     => '500',
			'default' => '170',
		) );
		?>

		<?php
		crane_add_taxonomy_meta_field__select( array(
			'id'      => 'show_read_more',
			'label'   => esc_html__( 'Show &quot;read more&quot; link', 'crane' ),
			'class'   => 'term_meta__custom_options__field',
			'value'   => ( isset( $term_meta['show_read_more'] ) ) ? $term_meta['show_read_more'] : 'default',
			'options' => $checkbox_select,
		) );
		?>

		<?php
		crane_add_taxonomy_meta_field__select( array(
			'id'      => 'show_imgtags',
			'label'   => esc_html__( 'Portfolio tags type', 'crane' ),
			'class'   => 'term_meta__custom_options__field',
			'value'   => ( isset( $term_meta['show_imgtags'] ) ) ? $term_meta['show_imgtags'] : 'default',
			'options' => $show_imgtags,
		) );
		?>

		<?php
		crane_add_taxonomy_meta_field__select( array(
			'id'      => 'sortable',
			'label'   => esc_html__( 'Show filtering by category', 'crane' ),
			'class'   => 'term_meta__custom_options__field',
			'value'   => ( isset( $term_meta['sortable'] ) ) ? $term_meta['sortable'] : 'default',
			'options' => $checkbox_select,
		) );
		?>

		<?php
		crane_add_taxonomy_meta_field__select( array(
			'id'      => 'sortable_align',
			'label'   => esc_html__( 'Filtering align', 'crane' ),
			'class'   => 'term_meta__custom_options__field term_meta__sortable_depend',
			'value'   => ( isset( $term_meta['sortable_align'] ) ) ? $term_meta['sortable_align'] : 'default',
			'options' => $sortable_align,
		) );
		?>

		<?php
		crane_add_taxonomy_meta_field__select( array(
			'id'      => 'sortable_style',
			'label'   => esc_html__( 'Filtering style', 'crane' ),
			'class'   => 'term_meta__custom_options__field term_meta__sortable_depend',
			'value'   => ( isset( $term_meta['sortable_style'] ) ) ? $term_meta['sortable_style'] : 'default',
			'options' => $sortable_style,
		) );
		?>

		<?php
		crane_add_taxonomy_meta_field__color( array(
			'id'      => 'sortable_background_color',
			'label'   => esc_html__( 'Filtering background color', 'crane' ),
			'class'   => 'term_meta__custom_options__field term_meta__sortable_depend term_meta__sortable_style_depend',
			'value'   => ( isset( $term_meta['sortable_background_color'] ) ) ? $term_meta['sortable_background_color'] : 'default',
			'default' => 'default',
		) );
		?>

		<?php
		crane_add_taxonomy_meta_field__color( array(
			'id'      => 'sortable_text_color',
			'label'   => esc_html__( 'Filtering text color', 'crane' ),
			'class'   => 'term_meta__custom_options__field term_meta__sortable_depend term_meta__sortable_style_depend',
			'value'   => ( isset( $term_meta['sortable_text_color'] ) ) ? $term_meta['sortable_text_color'] : 'default',
			'default' => 'default',
		) );
		?>

		<?php
		crane_add_taxonomy_meta_field__select( array(
			'id'      => 'pagination_type',
			'label'   => esc_html__( 'Pagination type', 'crane' ),
			'class'   => 'term_meta__custom_options__field',
			'value'   => ( isset( $term_meta['pagination_type'] ) ) ? $term_meta['pagination_type'] : 'default',
			'options' => array(
				'default'   => esc_html__( 'Default', 'crane' ),
				'0'         => esc_html__( 'No pagination', 'crane' ),
				'show_more' => esc_html__( 'Load more button', 'crane' ),
			),
		) );
		?>

		<?php
		crane_add_taxonomy_meta_field__color( array(
			'id'      => 'pagination_color',
			'label'   => esc_html__( 'Pagination button text color', 'crane' ),
			'class'   => 'term_meta__custom_options__field term_meta__pagination_type_depend',
			'value'   => ( isset( $term_meta['pagination_color'] ) ) ? $term_meta['pagination_color'] : 'default',
			'default' => 'default',
		) );
		?>

		<?php
		crane_add_taxonomy_meta_field__color( array(
			'id'      => 'pagination_background',
			'label'   => esc_html__( 'Pagination button background color', 'crane' ),
			'class'   => 'term_meta__custom_options__field term_meta__pagination_type_depend',
			'value'   => ( isset( $term_meta['pagination_background'] ) ) ? $term_meta['pagination_background'] : 'default',
			'default' => 'default',
		) );
		?>

		<?php
		crane_add_taxonomy_meta_field__color( array(
			'id'      => 'pagination_color_hover',
			'label'   => esc_html__( 'Pagination button hover & active text color', 'crane' ),
			'class'   => 'term_meta__custom_options__field term_meta__pagination_type_depend',
			'value'   => ( isset( $term_meta['pagination_color_hover'] ) ) ? $term_meta['pagination_color_hover'] : 'default',
			'default' => 'default',
		) );
		?>

		<?php
		crane_add_taxonomy_meta_field__color( array(
			'id'      => 'pagination_background_hover',
			'label'   => esc_html__( 'Pagination button hover & active background color', 'crane' ),
			'class'   => 'term_meta__custom_options__field term_meta__pagination_type_depend',
			'value'   => ( isset( $term_meta['pagination_background_hover'] ) ) ? $term_meta['pagination_background_hover'] : 'default',
			'default' => 'default',
		) );
		?>

		<?php
		crane_add_taxonomy_meta_field__text( array(
			'id'      => 'show_more_text',
			'label'   => esc_html__( 'Pagination button text', 'crane' ),
			'class'   => 'term_meta__custom_options__field term_meta__pagination_type_depend',
			'value'   => ( isset( $term_meta['show_more_text'] ) ) ? $term_meta['show_more_text'] : 'default',
			'default' => 'default',
		) );
		?>

		<?php
		crane_add_taxonomy_meta_field__select( array(
			'id'      => 'orderby',
			'label'   => esc_html__( 'Order by', 'crane' ),
			'class'   => 'term_meta__custom_options__field',
			'value'   => ( isset( $term_meta['orderby'] ) ) ? $term_meta['orderby'] : 'default',
			'options' => array(
				'default'       => esc_html__( 'Default (as in theme options)', 'crane' ),
				'id'            => esc_html__( 'Post id', 'crane' ),
				'title'         => esc_html__( 'Title', 'crane' ),
				'comment_count' => esc_html__( 'Comment count', 'crane' ),
				'random'        => esc_html__( 'Random', 'crane' ),
				'author'        => esc_html__( 'Author', 'crane' ),
			),
		) );
		?>

		<?php
		crane_add_taxonomy_meta_field__select( array(
			'id'      => 'order',
			'label'   => esc_html__( 'Order', 'crane' ),
			'class'   => 'term_meta__custom_options__field',
			'value'   => ( isset( $term_meta['order'] ) ) ? $term_meta['order'] : 'default',
			'options' => array(
				'default' => esc_html__( 'Default', 'crane' ),
				'ASC'     => esc_html__( 'Asc', 'crane' ),
				'DESC'    => esc_html__( 'Desc', 'crane' ),
			),
		) );
		?>

		<?php
		crane_add_taxonomy_meta_field__select( array(
			'id'      => 'custom_order',
			'label'   => esc_html__( 'Enable Custom order?', 'crane' ),
			'class'   => 'term_meta__custom_options__field term_meta__sortable_depend',
			'value'   => ( isset( $term_meta['custom_order'] ) ) ? $term_meta['custom_order'] : 'default',
			'options' => $checkbox_select,
		) );
		?>

		<?php
		crane_add_taxonomy_meta_field__select( array(
			'id'      => 'target',
			'label'   => esc_html__( 'Open on click in', 'crane' ),
			'class'   => 'term_meta__custom_options__field',
			'value'   => ( isset( $term_meta['target'] ) ) ? $term_meta['target'] : 'default',
			'options' => $target,
		) );
		?>

		<?php
		crane_add_taxonomy_meta_field__select( array(
			'id'      => 'nav_menu',
			'label'   => esc_html__( 'Navigation menu', 'crane' ),
			'class'   => 'term_meta__custom_options__field',
			'value'   => ( isset( $term_meta['nav_menu'] ) ) ? $term_meta['nav_menu'] : '0',
			'options' => crane_get_nav_menus(),
		) );

	}
}


if ( ! function_exists( 'crane_extra_tax_fields_portfolio_imgtags' ) ) {
	/**
	 * Extra field for wp taxonomy
	 *
	 * @param $tag
	 */
	function crane_extra_tax_fields_portfolio_imgtags( $tag ) {
		// We check for existing taxonomy meta for term ID
		$t_id_escaped = esc_attr( $tag->term_id );
		$term_meta    = maybe_unserialize( get_term_meta( $t_id_escaped, 'crane_term_additional_meta', true ) );
		if ( empty( $term_meta ) || ! $term_meta ) {
			$term_meta = array();
		}

		$image_id     = isset( $term_meta['imgtag'] ) ? $term_meta['imgtag'] : '';

		$image_esc = ! $image_id ? '' : wp_get_attachment_image( $image_id, 'thumbnail' );

		wp_enqueue_media();

		?>

		<tr class="form-field">
			<th scope="row" valign="top"><label><?php esc_html_e( 'Portfolio Image Tag', 'crane' ); ?></label></th>
			<td>
				<input type='hidden' id='crane-imgtag-<?php echo crane_clear_echo( $t_id_escaped ); ?>-value' class='small-text'
				       name='term_meta[imgtag]'
				       value='<?php echo esc_attr( $image_id ); ?>'/>
				<input type='button' id='crane-imgtag-<?php echo crane_clear_echo( $t_id_escaped ); ?>'
				       class='button crane-imgtag-upload-button'
				       value='Upload'/>
				<input type='button' id='crane-imgtag-<?php echo crane_clear_echo( $t_id_escaped ); ?>-remove'
				       class='button crane-imgtag-button-remove'
				       value='Remove'/>

				<div class='crane-imgtag-image-preview'><?php echo crane_clear_echo( $image_esc ); ?></div>
			</td>
		</tr>

		<?php
	}
}

if ( ! function_exists( 'crane_add_extra_tax_fields_portfolio_imgtags' ) ) {
	/**
	 * Extra field for wp taxonomy
	 *
	 * @param $taxonomy
	 */
	function crane_add_extra_tax_fields_portfolio_imgtags( $taxonomy ) {
		wp_enqueue_media();

		?>

		<div class="form-field term-group">
			<label for="crane-imgtag-new-value"><?php esc_html_e( 'Portfolio Image Tag', 'crane' ); ?></label>
			<input type='hidden' id='crane-imgtag-new-value' class='small-text' name='new-portfolio-imgtag' value=''/>
			<input type='button' id='crane-imgtag-new' class='button crane-imgtag-upload-button' value='Upload'/>
			<input type='button' id='crane-imgtag-new-remove' class='button crane-imgtag-button-remove' value='Remove'/>

			<div class='crane-imgtag-image-preview'></div>
		</div>

		<?php
	}
}

if ( ! function_exists( 'crane_save_extra_fields_callback' ) ) {
	/**
	 * Save extra taxonomy fields callback function
	 *
	 * @param $term_id string Taxonomy (Category) id (from DB)
	 */
	function crane_save_extra_fields_callback( $term_id ) {
		if ( isset( $_POST['term_meta'] ) ) {
			$t_id      = $term_id;
			$term_meta = maybe_unserialize( get_term_meta( $t_id, 'crane_term_additional_meta', true ) );
			if ( empty( $term_meta ) || ! $term_meta ) {
				$term_meta = array();
			}

			$term_keys = array_keys( $_POST['term_meta'] );

			foreach ( $term_keys as $key ) {
				if ( isset( $_POST['term_meta'][ $key ] ) ) {
					$term_meta[ $key ] = sanitize_text_field( wp_unslash( $_POST['term_meta'][ $key ] ) );
				}
			}

			update_term_meta( $t_id, 'crane_term_additional_meta', $term_meta );

		}
	}
}


if ( ! function_exists( 'crane_save_portfolio_imgtags_new_add' ) ) {
	/**
	 * Save extra taxonomy fields callback function for new term
	 *
	 * @param $term_id string Taxonomy id (from DB)
	 */
	function crane_save_portfolio_imgtags_new_add( $term_id, $tt_id ) {

		if ( isset( $_POST['new-portfolio-imgtag'] ) && '' !== $_POST['new-portfolio-imgtag'] ) {
			$term_meta = array();

			$imgtag = esc_attr( wp_unslash( $_POST['new-portfolio-imgtag'] ) );

			if ( $imgtag ) {
				$term_meta['imgtag'] = $imgtag;
				add_term_meta( $term_id, 'crane_term_additional_meta', $term_meta, true );
			}

		}
	}
}
