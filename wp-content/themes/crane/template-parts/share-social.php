<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );
/**
 * Template part for displaying share icons.
 *
 * @package crane
 */

global $crane_options;

$social = array();

$title = esc_attr( get_the_title() );
$url   = esc_url( get_permalink() );

if ( ! empty( $crane_options['share-social-facebook'] ) ) {
	$social['facebook'] = '<a class="facebook-share crane-share-list__link" data-title="' . $title . '"
	   data-url="' . $url . '" href="#">
	   <i class="crane-icon fa fa-facebook"></i></a>';
}
if ( ! empty( $crane_options['share-social-twitter'] ) ) {
	$social['twitter'] = '<a class="twitter-share crane-share-list__link" data-title="' . $title . '"
	   data-url="' . $url . '" href="#">
	   <i class="crane-icon fa fa-twitter"></i></a>';
}
if ( ! empty( $crane_options['share-social-googleplus'] ) ) {
	$social['googleplus'] = '<a class="googleplus-share crane-share-list__link" data-title="' . $title . '"
	   data-url="' . $url . '" href="#">
	   <i class="crane-icon fa fa-google-plus"></i></a>';
}
if ( ! empty( $crane_options['share-social-pinterest'] ) ) {
	$social['pinterest'] = '<a class="pinterest-share crane-share-list__link" data-title="' . $title . '"
	   data-url="' . $url . '" href="#">
	   <i class="crane-icon fa fa-pinterest-p"></i></a>';
}
if ( ! empty( $crane_options['share-social-linkedin'] ) ) {
	$social['linkedin'] = '<a class="linkedin-share crane-share-list__link" data-title="' . $title . '"
	   data-url="' . $url . '" href="#">
	   <i class="crane-icon fa fa-linkedin"></i></a>';
}

if ( ! empty( $social ) ) {
	?>
	<div class="crane-share">
		<i class="crane-share-icon crane-icon icon-Horn"></i>

		<div class="crane-share-list">
			<?php echo implode( ' ', $social ); ?>
		</div>
	</div>
	<?php
}
