<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );
/**
 * The template for displaying attachments.
 *
 * @package crane
 */

global $crane_options;

$post_id              = get_the_ID();
$current_page_options = crane_get_options_for_current_page();

$sidebar      = $current_page_options['has-sidebar'];
$sidebar_name = $current_page_options['sidebar'];

get_header();

crane_breadcrumbs( $current_page_options['breadcrumbs'] );

?>
	<div <?php post_class(); ?>>
		<div class="crane-container">
			<div class="crane-row-flex">

				<?php if ( $sidebar && 'at-left' === $sidebar ) {
					crane_generate_dynamic_sidebar( $sidebar_name, 'aside' );
				} ?>
				<div class="crane-content-inner page-inner">
					<div id="content">
						<?php the_title( sprintf( '<h1 class="crane-blog-post__title">', esc_url( get_permalink() ) ), '</h1>' ); ?>
						<div class="attachment__media-wrapper">
							<?php $image_size = apply_filters( 'wporg_attachment_size', 'large' );
							echo wp_get_attachment_image( $post_id, $image_size ); ?>
						</div>
						<?php if ( has_excerpt() ) : ?>
							<div class="attachment__excerpt-wrapper">
								<?php the_excerpt(); ?>
							</div>
						<?php endif; ?>
					</div>
					<?php
					// If comments are open or we have at least one comment, load up the comment template.
					if ( comments_open() || get_comments_number() ) {
						comments_template();
					}
					?>
				</div>
				<?php if ( $sidebar && 'at-right' === $sidebar ) {
					crane_generate_dynamic_sidebar( $sidebar_name, 'aside' );
				} ?>

			</div>
		</div>
	</div>
<?php

get_footer();
