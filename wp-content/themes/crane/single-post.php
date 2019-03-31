<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );
/**
 * The template for displaying all single posts.
 * @package crane
 */

get_header();

do_action( 'crane_before_blog-single' );

while ( have_posts() ) : the_post();

	get_template_part( 'template-parts/content', 'single' );

endwhile; // End of the loop.

do_action( 'crane_after_blog-single' );

get_footer();
