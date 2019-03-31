<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );
/**
 * Template part for displaying post meta
 *
 * @package crane
 */

$layout_options = crane_get_options_for_current_blog();

$is_show_meta =
	(
		! empty( $layout_options['show_pubdate'] ) ||
		! empty( $layout_options['show_author'] ) ||
		! empty( $layout_options['show_cats'] )
	) ? true : false;


$categories_list = get_the_category_list( ', ' );


?>

<?php if ( $is_show_meta ) : ?>
	<div class="crane-blog-meta">

		<?php if ( $layout_options['show_author'] ) {
			printf( __( '<span>by</span> <a href="%1$s" title="%2$s" rel="author">%3$s</a>', 'crane' ),
				esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ),
				esc_attr( sprintf( __( 'View all posts by %s', 'crane' ), get_the_author() ) ),
				get_the_author()
			);
		} ?>

		<?php if ( $layout_options['show_cats'] ) {
			if ( '' != $categories_list ) {
				printf( __( '<span>in</span> %1$s.', 'crane' ), $categories_list );
			} else {
				echo '.';
			}
		} ?>

		<?php if ( $layout_options['show_pubdate'] ) {
			echo sprintf( __( '<span>Posted</span> <a href="%1$s" title="%2$s" rel="bookmark">%3$s</a>', 'crane' ),
				get_month_link( get_the_time( 'Y' ), get_the_time( 'm' ) ),
				esc_attr( get_the_time() ),
				get_the_date()
			);
		} ?>

	</div>
<?php endif; ?>
