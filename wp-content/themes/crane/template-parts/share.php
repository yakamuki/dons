<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );
/**
 * Template part for displaying share icons.
 *
 * @package crane
 */


$layout_options = crane_get_options_for_current_blog();

if ( $layout_options['show_comment_link'] && comments_open() ) { ?>
        <a class="crane-comments" href="<?php echo comments_link() ?>">
            <i class="crane-icon icon-Chat"></i>
            <span class="crane-comments__count"><?php echo get_comments_number() ?></span>
        </a>
<?php } ?>

<?php
if ( $layout_options['show_share_button'] ) {
	get_template_part( 'template-parts/share', 'social' );
}
