<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );
/**
 * The template for displaying all single portfolio posts.
 *
 * @package crane
 */

get_header();

do_action( 'crane_before_portfolio-single' );

while ( have_posts() ) : the_post();

	get_template_part( 'template-parts/content', 'portfolio' );

endwhile; // End of the loop.

do_action( 'crane_after_portfolio-single' );

get_footer();
