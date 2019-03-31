<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );
/**
 * The template for displaying portfolio comments block
 *
 * @package crane
 */


if ( comments_open() || get_comments_number() ) {
	comments_template();
}
