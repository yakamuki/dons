<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );
/**
 * Template part for displaying embed media.
 *
 * @package crane
 */

$layout_options   = crane_get_options_for_current_blog();
$image_resolution = ( isset( $layout_options['image_resolution'] ) && ! empty( $layout_options['image_resolution'] ) ) ? $layout_options['image_resolution'] : 'crane-featured';

global $crane_options;
$show_placeholders = ( isset( $crane_options['show_featured_placeholders'] ) && $crane_options['show_featured_placeholders'] );

$embed_content_esc = '';

$crane_show_thumb = false;
if ( has_post_format( 'video' ) || has_post_format( 'audio' ) || has_post_format( 'gallery' ) ) {

	$embed_media = crane_get_first_embed_media( get_the_ID(), 400 );
	// $embed_media['html'] sanitized with do_shortcode() function
	$embed_content_esc = '<div class="embeded-content' . ( has_post_format( 'video' ) ? ' crane-video' : '' ) . '">' . $embed_media['html'] . '</div>';

} elseif ( get_post_format() && 'cell' !== $layout_options['layout'] ) {

	if ( 'masonry' === $layout_options['layout'] ) {
		echo '<div class="crane-blog-header" >';
	}

	get_template_part( 'template-parts/post_format_selector' );

	if ( 'masonry' === $layout_options['layout'] ) {
		echo '</div>';
	}

} else {

	$crane_show_thumb = true;

}


if ( $crane_show_thumb || has_post_format( 'audio' ) ) {

	if ( function_exists( 'grooni_filter_action' ) ) {
		grooni_filter_action( 'add', 'image_srcset', 'crane_add_thumb_image_srcset', 10, 5 );
	}

	// Start output buffer record
	ob_start();

	$target_esc = '';
	if ( 'blank' === $layout_options['target'] ) {
		$target_esc = ' target="_blank"';
	}

	if ( 'cell' === $layout_options['layout'] ) {

		$thumb_esc = crane_get_thumb( get_the_ID(), $image_resolution, false, true );
		$attr_esc  = crane_is_lazyload_enabled() ? 'data-bg' : 'style';

		if ( ! $thumb_esc ) {
			$thumb_esc = crane_get_thumb( get_the_ID(), $image_resolution );
		}

		if ( ! $thumb_esc ) {
			$thumb_esc = $show_placeholders ? 'class="crane-blog-grid-item-placeholder crane-placeholder crane-placeholder-' . rand( 1, 10 ) . '"' : '';
		} else {
			$attr_esc .= crane_is_lazyload_enabled() ? '="' . esc_url( $thumb_esc ) . '"' : '="background-image: url(\'' . esc_url( $thumb_esc ) . '\');"';
			$thumb_esc = crane_is_lazyload_enabled() ? 'class="crane-blog-grid-item-placeholder lazyload" ' . $attr_esc : 'class="crane-blog-grid-item-placeholder" ' . $attr_esc;
		}


		if ( has_post_format( 'audio' ) && $embed_content_esc ) {

			if ( isset( $embed_media['shortcode'] ) && 'audio' !== $embed_media['shortcode'] ) {

				echo crane_clear_echo( $embed_content_esc );

			} else {

				?>
				<div <?php echo crane_clear_echo( $thumb_esc ); ?>>
					<?php echo crane_clear_echo( $embed_content_esc ); ?>
				</div>
				<?php
			}

		} else {

			?>
			<div <?php echo crane_clear_echo( $thumb_esc ); ?>>
				<a href="<?php echo esc_url( get_permalink() ); ?>"<?php echo crane_clear_echo( $target_esc ); ?>></a>
			</div>
			<?php

		}


	} else {

		$attachment_id = get_post_thumbnail_id( get_the_ID() );

		if ( has_post_format( 'audio' ) && $embed_content_esc ) {

			$thumb_esc = crane_get_thumb( $attachment_id, $image_resolution, false, true );

			if ( isset( $embed_media['shortcode'] ) && 'audio' !== $embed_media['shortcode'] ) {

				echo crane_clear_echo( $embed_content_esc );

			} else {

				if ( ! $thumb_esc ) {

					$thumb_esc = $show_placeholders ? ' class="crane-blog-grid-item-placeholder crane-placeholder crane-placeholder-' . rand( 1, 10 ) . '"' : '';
					if ( isset( $layout_options['img_proportion'] ) &&
					     $layout_options['img_proportion'] &&
					     'original' === $layout_options['img_proportion']
					) {

						echo crane_clear_echo( $embed_content_esc );

					} else {
						?>
						<div<?php echo crane_clear_echo( $thumb_esc ); ?>>
							<?php echo crane_clear_echo( $embed_content_esc ); ?>
						</div>
						<?php
					}

				} else {
					if ( crane_is_lazyload_enabled() ) {
						$image_src_attr   = 'data-src="' . esc_url( $thumb_esc ) . '"';
						$image_class_attr = 'class="img-responsive lazyload"';
					} else {
						$image_src_attr   = 'src="' . esc_url( $thumb_esc ) . '"';
						$image_class_attr = 'class="img-responsive"';
					}

					if ( isset( $layout_options['img_proportion'] ) &&
					     $layout_options['img_proportion'] &&
					     'original' === $layout_options['img_proportion']
					) {
						?>
						<div class="crane-blog-grid-item-placeholder">
							<?php echo '<img ' . $image_src_attr . ' alt="' . esc_attr( get_the_title() ) . ' "' . $image_class_attr . '>'; ?>
							<?php echo crane_clear_echo( $embed_content_esc ); ?>
						</div>
						<?php
					} else {
						$attr_esc = crane_is_lazyload_enabled() ? 'data-bg' : 'style';
						$url      = esc_url( $thumb_esc );
						$attr_esc .= crane_is_lazyload_enabled() ? '="' . $url . '"' : '="background-image: url(\'' . $url . '\');"';

						?>
						<div class="crane-blog-grid-item-placeholder<?php echo crane_is_lazyload_enabled() ? ' lazyload' : ''; ?>" <?php echo crane_clear_echo( $attr_esc ); ?>>
							<?php echo crane_clear_echo( $embed_content_esc ); ?>
						</div>
						<?php
					}
				}
			}

		} else {
			if ( isset( $layout_options['img_proportion'] ) &&
			     $layout_options['img_proportion'] &&
			     'original' === $layout_options['img_proportion']
			) {

				$thumb_esc = wp_get_attachment_image( $attachment_id, $image_resolution, false, [
					'class' => 'img-responsive',
					'alt'   => esc_attr( get_the_title() ),
				] );
				$thumb_url = wp_get_attachment_image_src( $attachment_id, $image_resolution, false );

				if ( ! $thumb_esc ) {
					$thumb_esc = crane_get_thumb( get_the_ID(), $image_resolution );
					if ( $thumb_esc ) {
						if ( crane_is_lazyload_enabled() ) {
							$image_src_attr   = 'data-src="' . esc_url( $thumb_esc ) . '"';
							$image_class_attr = 'class="img-responsive lazyload"';
						} else {
							$image_src_attr   = 'src="' . esc_url( $thumb_esc ) . '"';
							$image_class_attr = 'class="img-responsive"';
						}

						$thumb_esc = '<img ' . $image_src_attr . ' alt="' . esc_attr( get_the_title() ) . '"' . $image_class_attr . '>';
					} else {
						$thumb_esc = '';
					}
				}

				if ( $thumb_esc || $show_placeholders ) {
					if ( crane_is_lazyload_enabled() ) {
						$image_src_attr   = 'data-src="' . $thumb_url[0] . '"';
						$image_class_attr = 'class="img-responsive lazyload"';
					} else {
						$image_src_attr   = 'src="' . $thumb_url[0] . '"';
						$image_class_attr = 'class="img-responsive"';
					}
					?>
					<div class="crane-blog-grid-item-placeholder<?php echo crane_get_placeholder_html_class( $thumb_esc ); ?>">
						<a href="<?php echo esc_url( get_permalink() ); ?>"<?php echo crane_clear_echo( $target_esc ); ?>>
							<?php if ( $thumb_url ) {
								echo '<img ' . $image_src_attr . ' alt="' . esc_attr( get_the_title() ) . '"' . $image_class_attr . '>';
							} ?>
						</a>
					</div>
					<?php
				}

			} else {
				$attr       = '';
				$thumb_esc  = crane_get_thumb( $attachment_id, $image_resolution, false, true );
				$background = '';
				if ( $thumb_esc ) {
					$attr = crane_is_lazyload_enabled() ? 'data-bg' : 'style';
					$url  = esc_url( $thumb_esc );
					$attr .= crane_is_lazyload_enabled() ? '="' . $url . '"' : '="background-image: url(\'' . $url . '\');"';
				}

				if ( $thumb_esc || $show_placeholders ) {
					?>
					<div class="<?php echo crane_is_lazyload_enabled() ? 'lazyload ' : ''; ?>crane-blog-grid-item-placeholder<?php echo crane_get_placeholder_html_class( $thumb_esc ); ?>"<?php echo crane_clear_echo( $attr ); ?>>
						<a href="<?php echo esc_url( get_permalink() ); ?>"<?php echo crane_clear_echo( $target_esc ); ?>></a>
					</div>
					<?php
				}
			}

		}


	}

	// Save output buffer to var
	$output_embed_html = ob_get_clean();

	if ( function_exists( 'grooni_filter_action' ) ) {
		grooni_filter_action( 'remove', 'image_srcset', 'crane_add_thumb_image_srcset', 10 );
	}


} else {

	$output_embed_html = $embed_content_esc;

}


if ( ! empty( $output_embed_html ) && 'masonry' === $layout_options['layout'] ) {
	echo '<div class="crane-blog-header" >' . $output_embed_html . '</div>';
} else {
	echo crane_clear_echo( $output_embed_html );
}
