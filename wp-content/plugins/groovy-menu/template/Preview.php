<?php defined( 'ABSPATH' ) || die( 'This script cannot be accessed directly.' );

global $groovyMenuSettings, $groovyMenuPreview;

$groovyMenuPreview = false;
$preset_id         = isset( $_GET['id'] ) ? esc_attr( $_GET['id'] ) : false;
$from_action       = isset( $_GET['from'] ) ? esc_attr( $_GET['from'] ) : null;
$rtl_flag          = isset( $_GET['d'] ) ? true : false;
$preset_params     = empty( $_POST['menu'] ) ? array() : $_POST['menu'];
$styles            = new GroovyMenuStyle( $preset_id );
$settings          = new GroovyMenuSettings();

// Save preview image.
if ( isset( $_POST ) && isset( $_POST['image'] ) && ! empty( $_GET['screen'] ) ) {
	$settings->savePreviewImage();
}

if ( 'api' === $from_action ) {

	$data = $settings->getPresetDataFromApiById( $preset_id );
	if ( ! empty( $data['settings'] ) ) {
		foreach ( $data['settings'] as $key => $val ) {
			$styles->set( $key, $val );
		}
	}

} elseif ( 'edit' === $from_action ) {

	if ( ! empty( $preset_params ) && is_array( $preset_params ) ) {
		foreach ( $preset_params as $group ) {
			foreach ( $group as $key => $val ) {
				$styles->set( $key, $val );
			}
		}
	}

}

$serialized_styles                          = $styles->serialize();
$groovyMenuSettings                         = $serialized_styles;
$groovyMenuSettings['preset']               = array(
	'id'   => $styles->getPreset()->getId(),
	'name' => $styles->getPreset()->getName(),
);
$groovyMenuSettings['extra_navbar_classes'] = $styles->getHtmlClasses();

// Disable admin bar.
add_filter( 'show_admin_bar', '__return_false' );
remove_action( 'wp_head', '_admin_bar_bump_cb' );

$style_name = 'preview' . ( $rtl_flag ? '-rtl' : '' ) . '.css';
wp_enqueue_style( 'groovy-preview-style', GROOVY_MENU_URL . 'assets/style/' . $style_name, [], 'v' . time() );

wp_enqueue_script( 'groovy-js-preview', GROOVY_MENU_URL . 'assets/js/preview.js', array( 'jquery' ), GROOVY_MENU_VERSION, true );


?><html <?php echo $rtl_flag ? 'dir="rtl"' : ''; ?>>
<head>
	<?php


	wp_head();


	/**
	 * Fires in <head> the groovy menu preview output.
	 *
	 * @since 1.2.20
	 */
	do_action( 'gm_preview_head_output' );


	?>
</head>
<body class="bg--transparent gm-preview-body" data-color="transparent">
<div class="gm-preload">
	<div class="ball-spin-fade-loader">
		<div></div>
		<div></div>
		<div></div>
		<div></div>
		<div></div>
		<div></div>
		<div></div>
		<div></div>
	</div>
</div>

<div class="gm-preview">
	<?php

	/**
	 * Fires before the groovy menu preview output.
	 *
	 * @since 1.2.20
	 */
	do_action( 'gm_before_preview' );

	$args = array(
		'menu'           => GroovyMenuUtils::getDefaultMenu(),
		'gm_preset_id'   => $preset_id,
		'theme_location' => GroovyMenuUtils::getMasterLocation(),
		'menu_class'     => 'nav-menu',
	);

	groovyMenu( $args );

	/**
	 * Fires after the groovy menu preview output.
	 *
	 * @since 1.2.20
	 */
	do_action( 'gm_after_preview' );

	?>
</div>


<?php


wp_footer();


/**
 * Fires in footer the groovy menu preview output.
 *
 * @since 1.2.20
 */
do_action( 'gm_preview_footer_output' );


?>

</body>

</html>
