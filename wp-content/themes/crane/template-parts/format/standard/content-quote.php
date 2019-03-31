<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );
/**
 * The template used for displaying page content with quote
 *
 * @package crane
 */

$layout_options = crane_get_options_for_current_blog();

$target = '';
if ( 'blank' === $layout_options['target'] ) {
	$target = ' target="_blank"';
}

$is_share = false;
if ( ( $layout_options['show_comment_link'] ) || $layout_options['show_share_button'] ) {
	$is_share = true;
}

?>
<article id="post-<?php the_ID(); ?>" <?php post_class( array( 'crane-row-flex', 'crane-blog-grid-item' ) ); ?>>
	<?php if ( $is_share ) : ?>
		<div class="crane-col-sm-1 hidden-xs">
			<?php get_template_part( 'template-parts/share' ); ?>
		</div>
	<?php endif; ?>

	<div class="crane-col-sm-1<?php echo ( $is_share ? '1' : '2' ); ?> post__blockquote">
		<?php get_template_part( 'template-parts/post_format_selector' ); ?>

		<?php get_template_part( 'template-parts/post_wrapper' ); ?>

	</div>
</article>
<?php
