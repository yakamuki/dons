<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );
/**
 * The template for displaying portfolio info block
 *
 * @package crane
 */


$current_page_options = crane_get_options_for_current_page();

$show_meta = false;

foreach ( $current_page_options['portfolio-single'] as $opt_key => $opt_val ) {
	if ( $opt_val ) {
		$show_meta = true;
	}
}

if ( ! $show_meta ) {
	return;
}


if ( $current_page_options['portfolio-single']['show-border'] ) {
	echo '<hr class="crane-portfolio__meta--border"></hr>';
}

?>

<div class="crane-portfolio__meta">
	<?php if ( $current_page_options['portfolio-single']['show-tags'] ) { ?>
		<?php
		$the_tags = get_the_term_list( get_the_ID(), 'crane_portfolio_tags', '', ', ' );
		if ( $the_tags && ! is_wp_error( $the_tags ) ) {
			?>
			<div class="portfolio__single-project__info__tags">
				<i class="fa fa-tags"></i>
				<?php echo crane_clear_echo( $the_tags ); ?>
			</div>
		<?php } ?>
	<?php } ?>

	<?php if ( $current_page_options['portfolio-single']['show-date'] ) { ?>
		<div class="portfolio__single-project__info__date">
			<i class="fa fa-calendar"></i>
			<span class="portfolio__single-project__info__date__value"><?php the_date() ?> </span>
		</div>
	<?php } ?>

	<?php if ( $current_page_options['portfolio-single']['show-cats'] ) { ?>
		<?php
		$the_cats = get_the_term_list( get_the_ID(), 'crane_portfolio_cats', '', ', ' );
		if ( $the_cats && ! is_wp_error( $the_cats ) ) {
			?>
			<div class="portfolio-categories">
				<?php echo esc_html__( 'Categories:', 'crane' ) . ' ' . $the_cats; ?>
			</div>
		<?php } ?>
	<?php } ?>

	<?php if ( $current_page_options['portfolio-single']['show-share'] ) { ?>
		<div class="crane-share">
			<?php get_template_part( 'template-parts/share', 'social' ); ?>
		</div>
	<?php } ?>
</div>
