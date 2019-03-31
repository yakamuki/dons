<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );
/**
 * Template part for displaying related posts.
 *
 * @package crane
 */


global $post;

$tags = wp_get_post_tags( $post->ID );

if ( $tags ) {
	global $crane_options;

	$tag_ids = array();
	foreach ( $tags as $one_tag ) {
		$tag_ids[] = $one_tag->term_id;
	}
	$args          = array(
		'tag__in'        => $tag_ids,
		'post__not_in'   => array( $post->ID ),
		'posts_per_page' => 3, // Maximum number of related posts to display.
		'orderby'        => 'date',
		'order'          => 'ASC',
	);
	$related_query = new WP_Query( $args );

	if ( $related_query->post_count ) : ?>

		<div class="crane-related-posts-wrapper">
			<h4><?php esc_html_e( 'Related posts', 'crane' ); ?></h4>

			<div class="crane-related-posts-container">

				<?php
				while ( $related_query->have_posts() ) {
					$related_query->the_post();

					$attachment_id = get_post_thumbnail_id( get_the_ID() );

					$related_img_escaped = wp_get_attachment_image_url( $attachment_id, 'crane-related' );

					if ( ! $related_img_escaped ) {
						$related_img_escaped = crane_get_thumb( get_the_ID(), 'crane-related' );
					}

					if ( $related_img_escaped ) {
						$attr                = crane_is_lazyload_enabled() ? ' data-bg' : ' style';
						$related_img_escaped = esc_url( $related_img_escaped );
						$related_img_escaped = crane_is_lazyload_enabled() ? $attr . '="' . $related_img_escaped . '"' : $attr . '="background-image: url(\'' . $related_img_escaped . '\');"';
					} else {
						$related_img_escaped = '';
					}

					?>
					<div class="crane-related-post">
						<a class="crane-related-post__link" href="<?php the_permalink(); ?>">
							<?php
							if ( $related_img_escaped || ( isset( $crane_options['show_featured_placeholders'] ) && $crane_options['show_featured_placeholders'] ) ) : ?>
								<span class="crane-related-post__img-wrapper<?php echo crane_get_placeholder_html_class( $related_img_escaped ); ?> <?php echo crane_is_lazyload_enabled() ? 'lazyload' : ''; ?>"<?php echo crane_clear_echo( $related_img_escaped ); ?>></span>
							<?php endif; ?>
							<span><?php the_title(); ?></span>
						</a>
						<span class="crane-related-post__date"><?php echo get_the_date( '', get_the_ID() ); ?></span>
					</div>
					<?php
				} ?>

			</div>
		</div>
		<hr class="post-divider">

		<?php
	endif;

	wp_reset_postdata();

}
