<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );
/**
 * Template part for displaying posts. Standard format.
 *
 * @package crane
 */


$is_share       = false;
$layout_options = crane_get_options_for_current_blog();

if ( ( $layout_options['show_comment_link'] ) || $layout_options['show_share_button'] ) {
	$is_share = true;
}

$additional_css_class = array( 'crane-row-flex' );
if ( 'standard' === $layout_options['layout'] ) {
	$additional_css_class[] = 'crane-blog-grid-item';
}

?>
<article id="post-<?php the_ID(); ?>" <?php post_class( $additional_css_class ); ?>>
	<?php if ( $is_share ) : ?>
		<div class="crane-col-sm-1 hidden-xs">
			<?php get_template_part( 'template-parts/share' ); ?>
		</div>
	<?php endif; ?>
	<div class="post__main crane-col-sm-1<?php echo ( $is_share ? '1' : '2' ); ?> crane-col-xs-12">
		<?php

		get_template_part( 'template-parts/format/standard/embed' );

		get_template_part( 'template-parts/post_wrapper' );

		?>
	</div>
</article>
