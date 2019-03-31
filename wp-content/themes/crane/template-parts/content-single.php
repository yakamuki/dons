<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );
/**
 * Template part for displaying single post.
 *
 * @package crane
 */

global $crane_options;

$Crane_Meta_Data      = crane_get_meta_data();
$post_id              = get_the_ID();
$current_page_options = crane_get_options_for_current_page();

$is_share = false;
if ( ( $current_page_options['show_comment_link'] ) || $current_page_options['show_share_button'] ) {
	$is_share = true;
}

$post_class = [ 'blog-single-post' ];

$sidebar      = $current_page_options['has-sidebar'];
$sidebar_name = $current_page_options['sidebar'];

$categories = array();
foreach ( wp_get_post_categories( $post_id ) as $categoryId ) {
	$category     = get_category( $categoryId );
	$categories[] = array( 'term_id' => $category->term_id, 'name'=>$category->name );
}


$show_featured = isset( $crane_options['blog-single-show-featured'] ) ? $crane_options['blog-single-show-featured'] : true;
if ( 'default' != $Crane_Meta_Data->get( 'post-show-featured', $post_id ) ) {
	$show_featured = $Crane_Meta_Data->get( 'post-show-featured', $post_id );
}

$show_content_title = isset( $crane_options['blog-single-show-content-title'] ) ? $crane_options['blog-single-show-content-title'] : true;
if ( 'default' != $Crane_Meta_Data->get( 'post-show-content-title', $post_id ) ) {
	$show_content_title = $Crane_Meta_Data->get( 'post-show-content-title', $post_id );
}

$show_meta_in_featured_box = isset( $crane_options['blog-single-show-meta-in-featured'] ) ? $crane_options['blog-single-show-meta-in-featured'] : false;
if ( 'default' != $Crane_Meta_Data->get( 'post-show-meta-in-featured', $post_id ) ) {
	$show_meta_in_featured_box = $Crane_Meta_Data->get( 'post-show-meta-in-featured', $post_id );
}

$text_title = the_title( '', '', false );

if ( empty( $text_title ) ) {
	$text_title = get_the_date( '', $post_id );
}


if ( 'link' === get_post_format() ) {
	$show_content_title = false;
}


if ( $show_featured ) {

	$src = crane_get_thumb( $post_id, 'full', true );
	if ( ! empty( $src ) ) {

		$attr_escaped = 'style';
		$styles       = $src ? 'background-image: url(' . esc_url( $src[0] ) . ');' : '';
		$size         = empty( $Crane_Meta_Data->get( 'post-featured-size', $post_id ) ) ? 'fullscreen' : esc_attr( $Crane_Meta_Data->get( 'post-featured-size', $post_id ) );

		$additional_html_class = '';
		if ( $size === 'custom' ) {
			$styles .= 'max-width: ' . esc_attr( $Crane_Meta_Data->get( 'post-featured-width', $post_id ) ) . 'px;';
			$styles .= 'height: ' . esc_attr( $Crane_Meta_Data->get( 'post-featured-height', $post_id ) ) . 'px;';
		} elseif ( $size === 'fullwidth' ) {
			$styles .= 'width: 100%; height: ' . esc_attr( $Crane_Meta_Data->get( 'post-featured-height', $post_id ) ) . 'px;';
		} elseif ( $size === 'fullscreen' ) {
			$additional_html_class = 'bg-h-100';
		} elseif ( $size === 'default' ) {
			$styles .= 'width: ' . ( $src ? esc_attr( $src[1] ) : '320' ) . 'px; height: ' . ( $src ? esc_attr( $src[2] ) : '320' ) . 'px;';
		} else { // $size === 'default'
			$styles .= 'width: 100%; height: ' . ( $src ? esc_attr( $src[2] ) : '320' ) . 'px;';
		}
		$attr_escaped .= '="' . $styles . '"';

		$additional_html_class .= crane_get_placeholder_html_class( $src );

		?>
		<div class="crane-featured-block <?php echo esc_attr( $additional_html_class ); ?>" <?php echo crane_clear_echo( $attr_escaped ); ?>>
			<div class="crane-featured-block__overlay"></div>
			<div class="crane-container">
				<?php if ( $show_meta_in_featured_box ) : ?>
					<div class="crane-featured-block__content">
						<h1 class="crane-featured-block__page-title"><?php echo wp_kses_post( $text_title ); ?></h1>
						<ul class="crane-featured-block__categories">
							<?php $last = end( $categories );
							$coma       = ',';
							foreach ( $categories as $cats ) {
								echo '<li><a href="'. get_category_link( $cats['term_id'] ).'" alt="category image">' . $cats['name'] . ( $last['name'] === $cats['name'] ? '' : $coma ) . '</a></li> ';
							}
							?>
						</ul>
					</div>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}
}

crane_breadcrumbs( $current_page_options['breadcrumbs'] );

?>
<div <?php post_class( $post_class ); ?>>
	<div class="crane-container">
		<div class="crane-row-flex">
			<?php if ( $sidebar && 'at-left' === $sidebar ) {
				crane_generate_dynamic_sidebar( $sidebar_name, 'aside' );
			} ?>
			<article class="crane-content-inner blog-inner">
				<div class="crane-row-flex">
					<?php if ( $is_share ) : ?>
						<div class="crane-col-sm-1 hidden-xs">
							<?php get_template_part( 'template-parts/share' ); ?>
						</div>
					<?php endif; ?>

					<div class="crane-col-sm-1<?php echo ( $is_share ? '1' : '2' ); ?> crane-col-xs-12">
						<?php if ( $show_content_title ) : ?>
							<h1 class="crane-blog-inner-title"><?php echo wp_kses_post( $text_title ); ?></h1>
						<?php endif; ?>
						<?php get_template_part( 'template-parts/post_format_selector' ); ?>
						<div class="crane-post-meta">
							<?php get_template_part( 'template-parts/single_meta' ); ?>
						</div>
						<?php if ( $content_escaped = apply_filters( 'the_content', get_the_content() ) ) : ?>
							<div class="blog-single-post__txt-wrapper">
								<?php echo crane_clear_echo( $content_escaped ); ?>
							</div>
						<?php endif;
						if ( isset( $crane_options['blog-single-show-tags'] ) && $crane_options['blog-single-show-tags'] ) {
							get_template_part( 'template-parts/post', 'tag-badges' );
						}

						wp_link_pages( array(
							'before'      => '<div class="page-links">',
							'after'       => '</div>',
							'link_before' => '<span>',
							'link_after'  => '</span>',
						) );

						if ( $current_page_options['show-author-info'] ) {
							get_template_part( 'template-parts/author_info' );
						}
						?>
						<hr class="post-divider">
						<?php if ( $current_page_options['show-related-posts'] ) {
							get_template_part( 'template-parts/related_posts' );
						}

						if ( ! post_password_required() && ( comments_open() || get_comments_number() ) ) {
							comments_template();
						}
						?>
					</div>
				</div>
			</article>
			<?php if ( $sidebar && 'at-right' === $sidebar ) {
				crane_generate_dynamic_sidebar( $sidebar_name, 'aside' );
			} ?>
		</div>
	</div>
	<?php get_template_part( 'template-parts/prev_next_links' ); ?>
</div>
