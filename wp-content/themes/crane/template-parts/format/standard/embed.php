<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );
/**
 * Template part for displaying embed media.
 *
 * @package crane
 */

$embed_media = crane_get_first_embed_media( get_the_ID(), 900 );

if ( get_post_format() && $embed_media ) {

	echo '<div class="embeded-content">' . $embed_media['html'] . '</div>';

} else {

	$layout_options   = crane_get_options_for_current_blog();
	$image_resolution = ( isset( $layout_options['image_resolution'] ) && ! empty( $layout_options['image_resolution'] ) ) ? $layout_options['image_resolution'] : 'crane-featured';

	global $crane_options;

	$thumb_esc = '';

	if ( 'image' === get_post_format() ) {
		$content = get_post_field( 'post_content', get_the_ID() );
		if ( ! empty( $content ) ) {
			$output = preg_match( '#<img.+src=[\'"]([^\'"]+)[\'"].*?>#i', $content, $matches );
			if ( ! empty( $matches[1] ) ) {
				if ( crane_is_lazyload_enabled() ) {
					$attr_esc = 'data-src="' . esc_url( $matches[1] ) . '"';
					$attr_class = 'class="lazyload"';
				} else {
					$attr_esc = 'src="' . esc_url( $matches[1] ) . '"';
					$attr_class = '';
				}

				$output = preg_match( '#srcset=[\'"]([^\'"]+)[\'"]#i', $matches[0], $srcset );
				if ( ! empty( $srcset[1] ) ) {
					$attr_esc = $attr_esc . ' ' . $srcset[0];
				}

				$thumb_esc = '<img ' . $attr_esc . ' alt="' . esc_attr( get_the_title() ) . '"' . $attr_class . '>';
			}
		}
	}

	if ( empty( $thumb_esc ) ) {

		if (function_exists('grooni_filter_action')) {
			grooni_filter_action( 'add', 'image_srcset', 'crane_add_thumb_image_srcset', 10, 5 );
		}

		$attachment_id = get_post_thumbnail_id( get_the_ID() );
		$thumb_esc     = wp_get_attachment_image( $attachment_id, $image_resolution );

		if ( ! $thumb_esc ) {
			$thumb_esc = crane_get_thumb( get_the_ID(), $image_resolution );
			if ( $thumb_esc ) {
				if ( crane_is_lazyload_enabled() ) {
					$image_src_attr = 'data-src="' . esc_url( $thumb_esc ) . '"';
					$image_class_attr = 'class="lazyload"';
				} else {
					$image_src_attr = 'src="' . esc_url( $thumb_esc ) . '"';
					$image_class_attr = '';
				}

				$thumb_esc = '<img ' . $image_src_attr . ' alt="' . esc_attr( get_the_title() ) . '"' . $image_class_attr . '>';
			} else {
				$thumb_esc = '';
			}
		}

		if ( function_exists( 'grooni_filter_action' ) ) {
			grooni_filter_action( 'remove', 'image_srcset', 'crane_add_thumb_image_srcset', 10 );
		}
	}

	$target_esc     = '';
	if ( 'blank' === $layout_options['target'] ) {
		$target_esc = ' target="_blank"';
	}

	if ( ! empty( $thumb_esc ) ) :
		?>
		<div class="post__main__placeholder">
			<a href="<?php echo esc_url( get_permalink() ); ?>"<?php echo crane_clear_echo( $target_esc ); ?>>
				<?php echo crane_clear_echo( $thumb_esc ); ?>
			</a>
		</div>
		<?php

	endif;

}
