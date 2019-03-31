<?php /* Template Name: Blog */
defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );
/**
 * The template for displaying archive pages.
 *
 * @package crane
 */

$template = get_post_meta( get_the_ID(), '_wp_page_template', true );

if ( 'archive.php' === $template ) {
	$current_page = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
	$args         = array(
		'posts_per_page' => get_option( 'posts_per_page' ),
		'post_type'      => 'post',
		'post_status'    => 'publish',
		'orderby'        => 'date',
		'paged'          => $current_page
	);
	query_posts( $args );

	$wp_query->is_archive = true;
	$wp_query->is_home    = false;
}


get_header();


$current_page_options = crane_get_options_for_current_page();
$layout_options       = crane_get_options_for_current_blog();

$layout      = ( ! empty( $layout_options['layout'] ) ? $layout_options['layout'] : 'standard' );
$alt_layout  = false;
$items_count = 0;

$sidebar      = $current_page_options['has-sidebar'];
$sidebar_name = $current_page_options['sidebar'];

crane_breadcrumbs( $current_page_options['breadcrumbs'] );

?>
	<div class="crane-container">
		<div class="crane-row-flex">
			<?php if ( $sidebar && 'at-left' === $sidebar ) {
				crane_generate_dynamic_sidebar( $sidebar_name, 'aside' );
			} ?>
			<section class="crane-content-inner blog-inner">
				<div id="content">
					<?php if ( in_array( $layout, array( 'cell', 'masonry' ) ) ) {
						$classes = array( 'crane-blog-layout-' . esc_attr( $layout ) );
						if ( $layout_options['columns'] ) {
							$classes[] = 'crane-column-' . esc_attr( $layout_options['columns'] );
						}
						if ( 'masonry' === $layout && isset( $layout_options['style'] ) && $layout_options['style'] ) {
							$classes[] = 'crane-blog-style-' . esc_attr( $layout_options['style'] );
						}
						if ( 'masonry' === $layout ) {
							$classes[] =
								(
									isset( $layout_options['img_proportion'] ) &&
									$layout_options['img_proportion'] &&
									'original' !== $layout_options['img_proportion']
								)
									? 'crane-blog-ratio_' . esc_attr( $layout_options['img_proportion'] ) : 'crane-blog-ratio_origin';
						}

						echo '<div class="crane-blog-widget ' . implode( ' ', $classes ) . '" data-params="' . htmlentities( json_encode( $layout_options ) ) . '">';
						echo '<div class="crane-blog-grid loading">';
						echo '<div class="crane-blog-grid-sizer"></div>';
					} ?>
					<?php if ( have_posts() ) {

						while ( have_posts() ) {
							the_post();

							if ( 'standard' === $layout ) {

								get_template_part( 'template-parts/format/standard/content', get_post_format() );

							} else {

								if ( 'cell' === $layout ) {
									if ( $items_count >= $layout_options['columns'] ) {
										$alt_layout  = ! $alt_layout;
										$items_count = 0;
									}
								}

								$alt = $alt_layout ? '_alt' : '';

								get_template_part( 'template-parts/format/masonry/content' . $alt, get_post_format() );

								$items_count ++;

							}

						}

					} else {

						get_template_part( 'template-parts/content', 'none' );

					}

					?>
					<?php if ( in_array( $layout, array( 'cell', 'masonry' ) ) ) {
						echo '</div>'; // crane-blog-grid
						echo '</div>'; // crane-blog-widget
					} ?>
				</div>
				<?php crane_the_posts_pagination( 'blog', $layout_options ); ?>
			</section>
			<?php if ( $sidebar && 'at-right' === $sidebar ) {
				crane_generate_dynamic_sidebar( $sidebar_name, 'aside' );
			} ?>
		</div>
	</div>
<?php get_footer();
