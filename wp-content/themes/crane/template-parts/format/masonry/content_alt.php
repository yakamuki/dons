<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );
/**
 * Template part for displaying post in masonry style.
 *
 * @package crane
 */

?>
<div id="post-<?php the_ID(); ?>" <?php post_class( ['crane-blog-grid-item', 'crane-blog-grid-item-alt'] ); ?>>
	<div class="crane-blog-grid-item-wrapper">
		<?php

		get_template_part( 'template-parts/post_wrapper_masonry' );

		get_template_part( 'template-parts/format/masonry/embed' );

		?>
	</div>
</div>
