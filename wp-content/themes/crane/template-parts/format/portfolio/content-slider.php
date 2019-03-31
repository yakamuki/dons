<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );
/**
 * Template part for displaying portfolio post. Slider type.
 *
 * @package crane
 */

?>
<div class="crane-portfolio-slider">
	<?php get_template_part( 'template-parts/portfolio/images' ); ?>
</div>
<?php get_template_part( 'template-parts/portfolio/content' ); ?>
<?php get_template_part( 'template-parts/portfolio/portfolio_info' ); ?>
<?php get_template_part( 'template-parts/portfolio/comments' );
