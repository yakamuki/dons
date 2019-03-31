<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );
/**
 * The template for displaying all pages.
 *
 * @package crane
 */

get_header();

if ( 'crane_footer' !== get_post_type() ) {

	$current_page_options = crane_get_options_for_current_page();
	crane_breadcrumbs( $current_page_options['breadcrumbs'] );

	while ( have_posts() ) : the_post();

		get_template_part( 'template-parts/content', 'page' );

	endwhile; // End of the loop.
}

get_footer();
