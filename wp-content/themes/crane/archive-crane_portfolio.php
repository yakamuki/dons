<?php /* Template Name: Portfolio */
defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );
/**
 * The template for displaying portfolio list pages
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 * @package crane
 */

$template = get_post_meta( get_the_ID(), '_wp_page_template', true );

if ( 'archive-crane_portfolio.php' === $template ) {
	$current_page = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
	$args         = array(
		'posts_per_page' => get_option( 'posts_per_page' ),
		'post_type'      => 'crane_portfolio',
		'post_status'    => 'publish',
		'orderby'        => 'date',
		'paged'          => $current_page
	);
	query_posts( $args );

	$wp_query->is_archive = true;
	$wp_query->is_home    = false;
}


get_template_part( 'template-parts/archive', 'crane_portfolio' );
