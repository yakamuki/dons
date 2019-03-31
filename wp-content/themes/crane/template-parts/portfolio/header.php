<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );
/**
 * The template for displaying portfolio header block
 *
 * @package crane
 */


global $crane_options;

$Crane_Meta_Data = crane_get_meta_data();
$post_id         = get_the_ID();
$show_featured   = isset( $crane_options['portfolio-single-show-featured-image'] ) ? $crane_options['portfolio-single-show-featured-image'] : false;
$featured_size   = isset( $crane_options['portfolio-single-featured-image-size'] ) ? $crane_options['portfolio-single-featured-image-size'] : 'fullscreen';
$featured_width  = isset( $crane_options['portfolio-single-featured-image-width'] ) ? $crane_options['portfolio-single-featured-image-width'] : '0';
$featured_height = isset( $crane_options['portfolio-single-featured-image-height'] ) ? $crane_options['portfolio-single-featured-image-height'] : '0';

$show_featured_meta = $Crane_Meta_Data->get( 'portfolio-show-featured', $post_id );

if ( 'default' !== $show_featured_meta && '2' !== $show_featured_meta ) {
	$show_featured   = $Crane_Meta_Data->get( 'portfolio-show-featured', $post_id );
	$featured_size   = $Crane_Meta_Data->get( 'portfolio-featured-size', $post_id );
	$featured_width  = $Crane_Meta_Data->get( 'portfolio-featured-width', $post_id );
	$featured_height = $Crane_Meta_Data->get( 'portfolio-featured-height', $post_id );
}

if ( $show_featured ) {
	$src          = crane_get_thumb( $post_id, 'full', true );
	$attr_escaped = 'style';
	$styles       = $src ? 'background-image: url(' . esc_url( $src[0] ) . ');' : '';
	$size         = esc_attr( $featured_size );
	if ( $size === 'custom' ) {
		$styles .= 'max-width: ' . esc_attr( $featured_width ) . 'px;';
		$styles .= 'height: ' . esc_attr( $featured_height ) . 'px;';
	} elseif ( $size === 'fullwidth' ) {
		$styles .= 'width: 100%; height: ' . esc_attr( $featured_height ) . 'px;';
	} elseif ( $size === 'default' ) {
		$styles .= 'width: 100%; height: ' . ( $src ? esc_attr( $src[2] ) : '320' ) . 'px';
	}
	$attr_escaped .= '="' . $styles . '"';

	$additional_class = crane_get_placeholder_html_class( $src );

	?>
	<div class="crane-portfolio-single-featured-image">
		<div <?php echo crane_clear_echo( $attr_escaped ); ?>
			class="portfolio-featured--size-<?php echo esc_attr( $featured_size ) . $additional_class; ?>">
			<?php
			if ( $Crane_Meta_Data->get( 'portfolio-show-return', $post_id ) ) {
				?>
				<a title="<?php esc_attr_e( 'Return to portfolio gallery', 'crane' ); ?>"
				   class="crane-portfolio-featured-image__btn"
				   href="<?php echo esc_url( $Crane_Meta_Data->get( 'portfolio-return-url', $post_id ) ); ?>"></a>
				<?php
			}
			?>
		</div>
	</div>
	<?php
}

$current_page_options = crane_get_options_for_current_page();

crane_breadcrumbs( $current_page_options['breadcrumbs'] );
