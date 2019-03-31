<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );
/**
 * Template part for displaying comment counter
 *
 * @package crane
 */

$layout_options = crane_get_options_for_current_blog();

$target_esc     = '';
if ( 'blank' === $layout_options['target'] ) {
	$target_esc = ' target="_blank"';
}

?>
<a href="<?php echo comments_link(); ?>" class="crane-comments"<?php echo crane_clear_echo( $target_esc ); ?>>
	<i class="crane-icon icon-Chat"></i>
	<span class="crane-comments__count"><?php echo get_comments_number(); ?></span>
</a>
