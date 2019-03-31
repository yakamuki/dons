<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );
/**
 * The header for this theme.
 *
 * This is the template that displays all of the <head> section, main menu section and everything up until <div class="crane-content">
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package crane
 */

global $crane_options;

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="http://gmpg.org/xfn/11">
	<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">

	<?php wp_head(); ?>

</head>

<body <?php body_class(); ?>>
<?php

if ( is_preview() && 'crane_footer' === get_post_type() ) {

	// dummy ...

} else {

	do_action( 'crane_before_primary_menu_area' );


	do_action( 'crane_primary_menu_area' );


	do_action( 'crane_after_primary_menu_area' );

}


echo ( isset( $crane_options['preloader'] ) && $crane_options['preloader'] ) ? '<div class="preloader"></div>' : '' ?>

	<div class="crane-content">
