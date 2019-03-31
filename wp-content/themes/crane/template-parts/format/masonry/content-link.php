<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );
/**
 * Template part for displaying posts. Link format.
 *
 * @package crane
 */


$layout_options = crane_get_options_for_current_blog();

if ( 'cell' === $layout_options['layout'] ) {
	?>
	<div id="post-<?php the_ID(); ?>" <?php post_class( 'crane-blog-grid-item' ); ?>>
		<div class="crane-blog-grid-item-wrapper">

			<?php get_template_part( 'template-parts/format/masonry/embed' ); ?>

			<div class="crane-blog-grid-meta">

				<?php get_template_part( 'template-parts/post_format_selector' ); ?>

				<?php
				if ( $layout_options['show_excerpt'] ) {
					$post_id = get_the_ID();

					if ( $layout_options['excerpt_strip_html'] ) {
						$content_escaped = apply_filters( 'the_excerpt', get_the_excerpt( $post_id ) );
					} else {
						$content_escaped = apply_filters( 'the_content', get_post_field( 'post_content', $post_id ) );
					}

					if ( ! empty( $content_escaped ) ) {
						?>
						<div class="crane-blog-grid-meta__excerpt">
							<?php echo crane_clear_echo( $content_escaped ); ?>
						</div>
						<?php
					}
				}
				?>
			</div>

		</div>
	</div>
	<?php

} else {

	get_template_part( 'template-parts/format/masonry/content' );

}
