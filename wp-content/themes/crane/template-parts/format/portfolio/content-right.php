<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );
/**
 * Template part for displaying portfolio post. Right type.
 *
 * @package crane
 */

?>
  <div class="crane-row-flex">
    <div class="crane-col-sm-4">
		<?php get_template_part( 'template-parts/portfolio/content' ); ?>
		<?php get_template_part( 'template-parts/portfolio/portfolio_info' ); ?>
    </div>
    <div class="crane-col-sm-8">
		<?php get_template_part( 'template-parts/portfolio/images' ); ?>
    </div>
  </div>
<?php get_template_part( 'template-parts/portfolio/comments' );
