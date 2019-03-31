<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );
/**
 * Template part for displaying post wrapper
 *
 * @package crane
 */

$layout_options = crane_get_options_for_current_blog();

ob_start();

if ( $layout_options['show_title_description'] ) {
	$text_title_escaped = the_title(
		sprintf( '<h4 class="crane-blog-grid-meta__title"><a href="%s" rel="bookmark" %s class="blog-masonry__item__title">',
			esc_url( get_permalink() ),
			( $layout_options['target'] === 'blank' ) ? 'target="_blank"' : ''
		), '</a></h4>', false );

	if ( empty( $text_title_escaped ) ) {
		$text_title_escaped = sprintf( '<h4 class="crane-blog-grid-meta__title"><a href="%s" rel="bookmark" %s class="blog-masonry__item__title">%s</a></h4>',
			esc_url( get_permalink() ),
			( $layout_options['target'] === 'blank' ) ? 'target="_blank"' : '',
			get_the_date( '', get_the_ID() )
		);
	}

	echo crane_clear_echo( $text_title_escaped );

}

if ( $layout_options['show_tags'] ) {

	get_template_part( 'template-parts/post', 'tag-badges' );

}

if ( $layout_options['show_excerpt'] ) {

	$post_id = get_the_ID();
	$content_escaped = get_the_excerpt( $post_id );

	if ( empty( $content_escaped ) ) {
		if ( $layout_options['excerpt_strip_html'] ) {
			$content_escaped = apply_filters( 'the_excerpt', get_the_excerpt( $post_id ) );
		} else {
			$content_escaped = apply_filters( 'the_content', get_post_field( 'post_content', $post_id ) );
		}
	}

	if ( ! empty( $content_escaped ) ) {
		?>
		<div class="crane-blog-grid-meta__excerpt">
			<?php echo crane_clear_echo( $content_escaped ); ?>
		</div>
		<?php
	}

}

if ( ! empty( $layout_options['show_read_more'] ) && $layout_options['show_read_more'] ) {
	echo sprintf( '<a class="post__readmore" href="%s" %s rel="bookmark"><span class="fa fa-chevron-right" aria-hidden="true"></span><span>%s</span></a>',
		esc_url( get_permalink() ),
		( $layout_options['target'] == 'blank' ) ? 'target="_blank"' : '',
		esc_html__( 'Read more', 'crane' )
	);
}

if (
	'cell' !== $layout_options['layout']
	&&
	(
		( 'none' !== $layout_options['show_post_meta'] && 'masonry' === $layout_options['layout'] )
		||
		( $layout_options['show_comment_link'] && comments_open() )
		||
		( $layout_options['show_share_button'] )
	)
) { ?>
	<div class="crane-blog-grid-meta__wrapper">
		<?php if ( 'author-and-date' === $layout_options['show_post_meta'] ) { ?>
			<div class="crane-blog-grid-meta__author__avatar">
				<img src="<?php echo get_avatar_url( get_the_author_meta( 'ID' ) ); ?>" alt="<?php echo get_the_author(); ?> avatar">
			</div>
		<?php } ?>
		<?php if ( 'none' !== $layout_options['show_post_meta'] ) { ?>
			<div class="crane-blog-grid-meta__author__info">
				<?php if ( 'author-and-date' === $layout_options['show_post_meta'] ) { ?>
					<span class="crane-blog-grid-meta__author__name"><?php echo get_the_author(); ?></span>
				<?php } ?>
				<span class="crane-blog-grid-meta__author__pub-date"><?php echo get_the_date(); ?></span>
			</div>
			<?php
		}
		if ( $layout_options['show_comment_link'] && comments_open() ) {
			get_template_part( 'template-parts/comment-counter' );
		}

		if ( $layout_options['show_share_button'] ) {
			get_template_part( 'template-parts/share', 'social' );
		}
		?>
	</div>
<?php }


$grid_meta_block_html = ob_get_contents();
ob_end_clean();
if ( ! empty( $grid_meta_block_html ) ) { ?>
	<div class="crane-blog-grid-meta">
		<?php echo crane_clear_echo( $grid_meta_block_html ); ?>
	</div>
<?php }
