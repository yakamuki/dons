<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );
/**
 * The template part for displaying a message that posts cannot be found.
 *
 * Learn more: http://codex.wordpress.org/Template_Hierarchy
 *
 * @package crane
 */

?>

<div class="no-results not-found">
	<div class="crane-container">

			<h1 class="crane-search-title"><?php esc_html_e( 'Nothing Found', 'crane' ); ?></h1>

			<?php if ( is_home() && current_user_can( 'publish_posts' ) ) : ?>

				<p><?php printf( wp_kses( __( 'Ready to publish your first post? <a href="%1$s">Get started here</a>.', 'crane' ), array( 'a' => array( 'href' => array() ) ) ),
						admin_url( 'post-new.php' ) ); ?></p>

			<?php elseif ( is_search() ) : ?>

				<p><?php esc_html_e( 'Sorry, but nothing matched your search terms. Please try again with some different keywords.', 'crane' ); ?></p>
				<?php get_search_form(); ?>

				<?php
			else : ?>

				<p><?php esc_html_e( 'It seems we can&rsquo;t find what you&rsquo;re looking for. Perhaps searching can help.', 'crane' ); ?></p>
				<?php get_search_form(); ?>

			<?php endif; ?>
	</div>
</div>
