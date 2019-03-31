<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );
/**
 * Template part for displaying post in masonry style.
 *
 * @package crane
 */

?>
<div id="post-<?php the_ID(); ?>" <?php post_class( array( 'crane-blog-grid-item' ) ); ?>>
	<div class="crane-blog-grid-item-wrapper">

		<?php get_template_part( 'template-parts/format/masonry/embed' ); ?>

		<?php get_template_part( 'template-parts/post_wrapper_masonry' ); ?>

	</div>
</div>
