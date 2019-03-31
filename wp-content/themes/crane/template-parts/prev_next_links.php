<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );
/**
 * Template part for displaying prev \ next link of single posts.
 *
 * @package crane
 */


$current_page_options = crane_get_options_for_current_page();

if ( $current_page_options['show-prev-next-post'] ) {

	$taxonomy      = 'category';
	$subtitle_tmpl = '<span class="single-post-nav-date">%date</span>';

	if ( is_single() && function_exists( 'is_product' ) && is_product() ) {
		$taxonomy      = 'product_cat';
		$subtitle_tmpl = '<span class="single-post-nav-price">%price</span>';
	}


	echo '<div class="crane-single-post-nav-wrapper">';
	previous_post_link(
		'%link',
		'
			<span class="single-post-nav-content">
				<span class="single-post-nav-content-info">
					<span class="single-post-nav-title">%title</span>
					' . $subtitle_tmpl . '
				</span>
				%image
			</span>
			<span class="single-post-nav-arrow single-post-nav-arrow--prev"></span>',
		false,
		'',
		$taxonomy
	);
	next_post_link(
		'%link',
		'
			<span class="single-post-nav-arrow single-post-nav-arrow--next"></span>
			<span class="single-post-nav-content">
				%image
				<span class="single-post-nav-content-info">
					<span class="single-post-nav-title">%title</span>
			 		' . $subtitle_tmpl . '
			 	</span>
			</span>',
		false,
		'',
		$taxonomy
	);
	echo '</div>';

}
