<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );
/**
 * The template for displaying single post meta data.
 *
 * @package crane
 */

$categories_list = get_the_category_list( ', ' );

if ( '' != $categories_list ) {
	$meta_text = wp_kses( __( '<span>by</span> <a href="%3$s">%2$s</a> <span>in</span> %1$s<span>.</span> <span>Posted</span> <a href="%5$s">%4$s</a>', 'crane' ), array( 'a' => array( 'href' => array() ), 'span' => array() ) );
} else {
	$meta_text = wp_kses( __( '<span>by</span> <a href="%3$s">%2$s</a><span>.</span> <span>Posted</span> <a href="%5$s">%4$s</a>', 'crane' ), array( 'a' => array( 'href' => array() ), 'span' => array() ) );
}

printf(
	$meta_text,
	$categories_list,
	get_the_author(),
	get_author_posts_url( get_the_author_meta( 'ID' ) ),
	get_the_date(),
	get_day_link( get_the_date('Y'), get_the_date( 'm' ), get_the_date( 'd' ) )
);
