<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );
/**
 * The template for displaying content of the page.
 *
 * @package crane
 */

$current_page_options = crane_get_options_for_current_page();

$sidebar           = $current_page_options['has-sidebar'];
$sidebar_name      = $current_page_options['sidebar'];
$show_sidebar_html = ( $sidebar && is_active_sidebar( $sidebar_name ) ) || crane_is_additional_woocommerce_page();


$the_content_escaped = get_the_content();
$the_content_escaped = apply_filters( 'the_content', $the_content_escaped );
$the_content_escaped = str_replace( ']]>', ']]&gt;', $the_content_escaped );


$has_own_sections = ( strpos( $the_content_escaped, ' class="crane-section' ) !== false );
if ( ! $has_own_sections ) {
	$the_content_escaped = '
' . ( ! $show_sidebar_html ? '<div class="crane-content-inner page-inner">' : '' ) . '
<section class="crane-section">
  <div class="crane-container">
  ' . $the_content_escaped . '
  </div>
</section>
' . ( ! $show_sidebar_html ? '</div>' : '' );
}
?>

<div <?php post_class(); ?>>
	<?php if ( $show_sidebar_html ) : ?>
	<div class="crane-container">
		<div class="crane-row-flex">
			<?php endif; ?>

			<?php if ( $sidebar && 'at-left' === $sidebar ) {
				crane_generate_dynamic_sidebar( $sidebar_name, 'aside' );
			} ?>

			<?php if ( $show_sidebar_html ) : ?>
			<div class="crane-content-inner page-inner">
				<?php endif; ?>
				<div id="content">
					<?php echo crane_clear_echo( $the_content_escaped ); ?>
				</div>
				<?php
				// If comments are open or we have at least one comment, load up the comment template.
				if ( comments_open() || get_comments_number() ) {
					comments_template();
				}
				?>
				<?php if ( $show_sidebar_html ) : ?>
			</div>
		<?php endif; ?>

			<?php if ( $sidebar && 'at-right' === $sidebar ) {
				crane_generate_dynamic_sidebar( $sidebar_name, 'aside' );
			} ?>

			<?php if ( $show_sidebar_html ) : ?>
		</div>
	</div>
<?php endif; ?>
</div>
