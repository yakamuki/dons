<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );
/**
 * Template part for displaying portfolio post. Grid type.
 *
 * @package crane
 */


$Crane_Meta_Data = crane_get_meta_data();

?>
  <div class="portfolio-single-grid" data-columns="<?php echo esc_attr( $Crane_Meta_Data->get( 'portfolio-grid-columns', get_the_ID() ) ); ?>">
	  <?php get_template_part( 'template-parts/portfolio/images' ); ?>
  </div>
<?php get_template_part( 'template-parts/portfolio/content' ); ?>
<?php get_template_part( 'template-parts/portfolio/portfolio_info' ); ?>
<?php get_template_part( 'template-parts/portfolio/comments' );
