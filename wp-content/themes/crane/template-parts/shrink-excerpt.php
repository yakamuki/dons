<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );
/**
 * Template part for displaying posts excerpt
 *
 * @package crane
 */

$layout_options = crane_get_options_for_current_blog();
$post_id        = get_the_ID();

if ( $layout_options['show_excerpt'] ) {

	$content_escaped = get_the_excerpt( $post_id );

	if ( empty( $content_escaped ) ) {
		if ( $layout_options['excerpt_strip_html'] ) {
			$content_escaped = apply_filters( 'the_excerpt', get_the_excerpt( $post_id ) );
			if ( ! empty( $content_escaped ) ) {
				$content_escaped = '<p>' . $content_escaped . '</p>';
			}
		} else {
			$content_escaped = apply_filters( 'the_content', get_post_field( 'post_content', $post_id ) );
			if ( ! empty( $content_escaped ) ) {
				$content_escaped = $content_escaped . '<br>';
			}
		}
	}

}

if ( ! empty( $content_escaped ) ) : ?>
    <div class="post__main__txt">
		<?php echo crane_clear_echo( $content_escaped ); ?>
		<?php
		wp_link_pages( array(
			'before'      => '<div class="page-links">',
			'after'       => '</div>',
			'link_before' => '<span>',
			'link_after'  => '</span>',
		) );
		?>
    </div>
<?php endif;
