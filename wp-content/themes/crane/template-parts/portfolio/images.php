<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );
/**
 * The template for displaying portfolio image block
 *
 * @package crane
 */

global $crane_options;

$Crane_Meta_Data = crane_get_meta_data();
$size            = 'crane-portfolio-900';
$type            = $Crane_Meta_Data->get( 'portfolio-type', get_the_ID() );
$preview         = $Crane_Meta_Data->get( 'portfolio-image', get_the_ID(), null, array( 'size' => $size ) );
$full            = $Crane_Meta_Data->get( 'portfolio-image', get_the_ID() );

foreach ( $full as $key => $image ) {
	if ( $type === 'slider' ) {
		?>
			<img src="<?php echo esc_url( $image['path'][0] ); ?>" alt="portfolio image">
		<?php
	} elseif ( $type === 'grid' ) {
		if ( crane_is_lazyload_enabled() ) {
			$image_src_attr = 'data-src="' . esc_url( $preview[ $key ]['path'][0] ) . '"';
			$image_class_attr = 'class="crane-portfolio-grid-img lazyload"';
		} else {
			$image_src_attr = 'src="' . esc_url( $preview[ $key ]['path'][0] ) . '"';
			$image_class_attr = 'class="crane-portfolio-grid-img"';
		}
		?>
		<div class="portfolio-grid-item">
			<div class="crane-portfolio-grid-item-placeholder">
				<img <?php echo crane_clear_echo( $image_class_attr ); ?> <?php echo crane_clear_echo( $image_src_attr ); ?> alt="portfolio image">

				<div class="portfolio-hover">
					<div class="portfolio-hover-inner">
						<a href="<?php echo esc_url( $image['path'][0] ); ?>" class="portfolio-zoomlink" rel="portfolio-images">
							<i class="icon-Loop"></i>
						</a>
					</div>
				</div>

			</div>
		</div>
		<?php
	} else {
		if ( crane_is_lazyload_enabled() ) {
			$image_src_attr = 'data-src="' . esc_url( $image['path'][0] ) . '"';
			$image_class_attr = 'class="crane-portfolio-single__img lazyload"';
		} else {
			$image_src_attr = 'src="' . esc_url( $image['path'][0] ) . '"';
			$image_class_attr = 'class="crane-portfolio-single__img"';
		}
		?>
		<img <?php echo crane_clear_echo( $image_src_attr ); ?> <?php echo crane_clear_echo( $image_class_attr ); ?> alt="portfolio image">
		<?php
	}
}
