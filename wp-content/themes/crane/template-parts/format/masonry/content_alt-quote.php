<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );
/**
 * The template used for displaying page content with quote
 *
 * @package crane
 */

$layout_options = crane_get_options_for_current_blog();

if ( 'cell' === $layout_options['layout'] ) {
?>
	<div id="post-<?php the_ID(); ?>" <?php post_class( [ 'crane-blog-grid-item', 'crane-blog-grid-item-alt' ] ); ?>>
		<div class="crane-blog-grid-item-wrapper">

			<div class="crane-blog-grid-meta">

				<?php get_template_part( 'template-parts/post_format_selector' ); ?>

			</div>

			<?php get_template_part( 'template-parts/format/masonry/embed' ); ?>

		</div>
	</div>
<?php

} else {

	get_template_part( 'template-parts/format/masonry/content_alt' );

}
