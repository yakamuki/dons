<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );
/**
 * The template part for displaying results in search pages.
 *
 * Learn more: http://codex.wordpress.org/Template_Hierarchy
 *
 * @package crane
 */

$current_content = get_the_excerpt() ? get_the_excerpt() : get_the_content();

?>
<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
		<?php the_title( sprintf( '<h3 class="entry-title"><a href="%s" rel="bookmark">', esc_url( get_permalink() ) ),
			'</a></h3>' ); ?>

		<?php if ( 'post' === get_post_type() ) : ?>
			<div class="entry-meta">
				<?php crane_posted_on(); ?>
			</div>
		<?php endif; ?>

	<div class="entry-summary">
		<?php echo mb_substr( strip_tags( preg_replace( '#<style(.*?)>(.*?)</style>#is', '', preg_replace( '#<script(.*?)>(.*?)</script>#is', '', do_shortcode( $current_content ) ) ) ), 0, 500 ) . '...'; ?>
	</div>

		<?php crane_entry_footer(); ?>
</article>
