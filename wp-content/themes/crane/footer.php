<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );
/**
 * The template for displaying the footer.
 *
 * Contains the closing of the #content div and all content after.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package crane
 */

global $crane_options;
$current_page_options = crane_get_options_for_current_page();
?>

</div> <?php /* crane-content closing tag */ ?>

<?php

do_action( 'crane_before_footer' );

get_template_part( 'template-parts/footer-type' );

do_action( 'crane_after_footer' );

?>

<?php wp_footer(); ?>

</body>
</html>
