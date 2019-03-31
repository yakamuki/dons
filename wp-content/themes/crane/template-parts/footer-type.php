<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );
/**
 * Template part for displaying footer.
 *
 * @package crane
 */


if ( is_preview() && 'crane_footer' === get_post_type() ) {

	while ( have_posts() ) : the_post();

		$crane_footer = apply_filters( 'the_content', get_the_content() );

		echo '<footer class="footer">';

		echo apply_filters( 'crane_footer_the_content', $crane_footer );

		echo '</footer>';

	endwhile; // End of the loop.

} else {

	echo apply_filters( 'crane_footer_the_content', crane_get_footer_data( 'html' ) );

}
