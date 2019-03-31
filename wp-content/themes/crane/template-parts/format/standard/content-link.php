<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );
/**
 * Template part for displaying posts. Link format.
 *
 * @package crane
 */

$layout_options = crane_get_options_for_current_blog();

$target_esc = '';
if ( 'blank' === $layout_options['target'] ) {
	$target_esc = ' target="_blank"';
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

	<div class="crane-col-sm-1<?php echo ( $is_share ? '1' : '2' ); ?> crane-col-xs-12">

		<?php get_template_part( 'template-parts/post_format_selector' ); ?>

		<?php get_template_part( 'template-parts/post', 'meta' ); ?>

		<div class="post__main__txt-wrapper">

			<?php get_template_part( 'template-parts/shrink-excerpt' ); ?>

			<?php if ( $layout_options['show_tags'] ) : ?>
				<?php get_template_part( 'template-parts/post', 'tag-badges' ); ?>
			<?php endif; ?>

			<?php if ( ! empty( $layout_options['show_read_more'] ) && $layout_options['show_read_more'] ) { ?>
				<a class="post__readmore" href="<?php echo esc_url( get_permalink() ); ?>"<?php echo crane_clear_echo( $target_esc ); ?>>
					<span class="fa fa-chevron-right" aria-hidden="true"></span>
					<span><?php esc_html_e( 'Read more', 'crane' ); ?></span>
				</a>
			<?php } ?>
		</div>
		<hr class="post-divider">
	</div>
</article>
