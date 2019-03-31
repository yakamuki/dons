<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );
/**
 * Template part for displaying post wrapper
 *
 * @package crane
 */

$layout_options = crane_get_options_for_current_blog();

$target_esc = '';
if ( 'blank' === $layout_options['target'] ) {
	$target_esc = ' target="_blank"';
}

$text_title_escaped = the_title(
	sprintf( '<h2 class="crane-blog-post__title"><a href="%s" rel="bookmark" %s>',
		esc_url( get_permalink() ),
		( $layout_options['target'] === 'blank' ) ? 'target="_blank"' : ''
	), '</a></h2>', false );

if ( empty( $text_title_escaped ) ) {
	$text_title_escaped = sprintf( '<h2 class="crane-blog-post__title"><a href="%s" rel="bookmark" %s>%s</a></h2>',
		esc_url( get_permalink() ),
		( $layout_options['target'] === 'blank' ) ? 'target="_blank"' : '',
		get_the_date( '', get_the_ID() )
	);
}

?>
<div class="post__main__txt-wrapper">

	<?php echo crane_clear_echo( $text_title_escaped ); ?>

	<?php get_template_part( 'template-parts/post', 'meta' ); ?>

	<?php
	if ( 'quote' !== get_post_format() ) {
		get_template_part( 'template-parts/shrink-excerpt' );
	}
	?>

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
