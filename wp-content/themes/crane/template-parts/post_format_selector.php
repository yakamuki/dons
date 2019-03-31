<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );
/**
 * Template part for displaying post formats.
 *
 * @package crane
 */

$current_page_options = crane_get_options_for_current_page();
$layout_options       = crane_get_options_for_current_blog();

$target = '';
if ( 'blank' === $layout_options['target'] ) {
	$target = ' target="_blank"';
}

switch ( get_post_format() ) {

	case 'quote':

		$content = get_post_field( 'post_content', get_the_ID() );
		if ( ! empty( $content ) ) {
			$output = preg_match( '#<blockquote.*?>(.+?)<\/blockquote>#is', $content, $matches );
			if ( $output && isset( $matches[1] ) ) {
				$crane_quote = $matches[1];
			}
		}

		if ( empty( $crane_quote ) ) {
			if ( 'cell' === $layout_options['layout'] ) {
				?><div class="crane-blockquote-main--empty"></div><?php
			}

			break;

		} else {
			?><blockquote class="crane-blockquote-main"><?php echo do_shortcode( $crane_quote ); ?></blockquote><?php
		}

		break;

	case 'link':

		$content    = get_post_field( 'post_content', get_the_ID() );
		if ( ! empty( $content ) ) {
			$output = preg_match( '#<a.+?href=[\'"]([^\'"]+)[\'"].*?>(.+?)<\/a>#is', $content, $matches );
			if ( $output ) {
				$crane_link = array( 'full' => $matches[0], 'url' => $matches[1], 'text' => $matches[2] );
			}
		}

		$show_tags = false;
		if ( ! is_single() && $layout_options['show_tags'] ) {
			$show_tags = true;
		}

		if ( 'masonry' === $current_page_options['template'] || 'masonry' === $layout_options['layout'] ) {
			$show_tags = false;
		}

		$show_title = false;
		if ( 'masonry' !== $current_page_options['template'] && $layout_options['show_title_description'] ) {
			$show_title = true;
		}

		if ( 'blog-single' === $current_page_options['type'] ) {
			$show_title = true;
		}

		if ( 'standard' === $current_page_options['template'] ) {
			$show_title = true;
		}

		if ( ! empty( $crane_link ) ) { ?>
			<div class="post-format-link__top">
				<?php if ( $show_title ) { ?>
					<?php the_title(
						sprintf( '<h2 class="crane-blog-post__title"><a href="%s" rel="bookmark" %s>',
							esc_url( get_permalink() ),
							( $layout_options['target'] === 'blank' ) ? 'target="_blank"' : ''
						), '</a></h2>', true ); ?>
				<?php } ?>
				<a class="post-format-link__link" href="<?php echo esc_url( $crane_link['url'] ); ?>"
				   target="_blank"><?php echo wp_kses_post( $crane_link['text'] ); ?></a>
			</div>
			<?php
		} elseif ( ! is_single() ) {
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
			} ?>

			<?php echo crane_clear_echo( $text_title_escaped ); ?>

			<?php get_template_part( 'template-parts/post', 'meta' ); ?>

			<?php
		}
		break;

	default:
		break;

}
