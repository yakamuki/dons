<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );
/**
 * The template for displaying 404 pages (not found).
 *
 * @package crane
 */

global $crane_options;


get_header();

if ( crane_check_404_page() ) :

	$current_page_options = crane_get_options_for_current_page();
	crane_breadcrumbs( $current_page_options['breadcrumbs'] );

	get_template_part( 'template-parts/content', 'page' );

else :

	$title = isset( $crane_options['404-title'] ) ? $crane_options['404-title'] : esc_html__( 'Oops, This Page Could Not Be Found!', 'crane' );
	$text = isset( $crane_options['404-text'] ) ? $crane_options['404-text'] : esc_html__( 'Unfortunately, the page was not found. It was deleted or moved and is now at another address.', 'crane' );


	?>
	<div class="crane-404">
		<div class="crane-404-wrapper">
			<div class="crane-container">
				<div class="crane-row">
					<div class="crane-404__header-group col-md-8 col-md-offset-2">
						<h3 class="crane-404__header"><?php echo wp_kses_post( $title ); ?></h3>

						<p class="crane-404__subheader"><?php echo wp_kses_post( $text ); ?></p>

						<div class="crane-404__heading-spacer"></div>
						<img src="<?php echo get_template_directory_uri(); ?>/assets/images/wp/404.png" height="207"
						     width="529" alt="404 image" class="crane-404__img">

						<form method="get" action="/" class="form-group crane-404__form-group">
							<input placeholder="<?php esc_html_e( 'Search Query ...', 'crane' ); ?>" type="text"
							       name="s" class="form-control crane-404__search">
							<input type="submit" value="search" class="hidden"/>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>

	<?php


endif;

get_footer();
