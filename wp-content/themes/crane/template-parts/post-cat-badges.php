<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );
/**
 * Template part for displaying category badges.
 *
 * @package crane
 */

global $crane_options;
$cats = wp_get_post_categories( get_the_ID() );

if ( ! empty( $cats ) ) { ?>
	<div class="crane-blog-meta__cats">

		<?php
		foreach ( $cats as $cat_ID ) {
			$cat_data = get_term( intval( $cat_ID ), 'category' );
			?>
			<span><a href="<?php echo esc_url( get_category_link( $cat_data ) ); ?>"><?php echo esc_html( $cat_data->name ); ?></a></span>
		<?php } ?>

	</div>
<?php }
