<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );
/**
 * The template for displaying portfolio content block
 *
 * @package crane
 */


$current_page_options = crane_get_options_for_current_page();

if ( $current_page_options['portfolio-single']['show-title'] ) {
	the_title( sprintf( '<h1 class="crane-portfolio-single-post-title">', esc_url( get_permalink() ) ), '</h1>' );
}

the_content();
