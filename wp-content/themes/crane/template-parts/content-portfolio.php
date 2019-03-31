<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );
/**
 * The template for displaying all single portfolio posts.
 *
 * @package crane
 */

global $crane_options;

$Crane_Meta_Data      = crane_get_meta_data();
$post_id              = get_the_ID();
$post_class           = [ 'portfolio-single-post' ];
$current_page_options = crane_get_options_for_current_page();
$sidebar              = $current_page_options['has-sidebar'];
$sidebar_name         = $current_page_options['sidebar'];

get_template_part( 'template-parts/portfolio/header' );

?>
  <div <?php post_class( $post_class ); ?>>
    <div class="crane-container">
      <div class="crane-row-flex">
		  <?php if ( $sidebar && 'at-left' === $sidebar ) {
			  crane_generate_dynamic_sidebar( $sidebar_name, 'aside' );
		  } ?>
        <div class="crane-content-inner portfolio-single-inner">
			<?php get_template_part( 'template-parts/format/portfolio/content', $Crane_Meta_Data->get( 'portfolio-type', get_the_ID() ) ); ?>
        </div>
		  <?php if ( $sidebar && 'at-right' === $sidebar ) {
			  crane_generate_dynamic_sidebar( $sidebar_name, 'aside' );
		  } ?>
      </div>
    </div>
	  <?php get_template_part( 'template-parts/prev_next_links' ); ?>
  </div>
<?php
