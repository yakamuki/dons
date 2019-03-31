<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );
/**
 * Template part for displaying category badges.
 *
 * @package crane
 */

global $crane_options;
$tags   = wp_get_post_tags( get_the_ID() );
$layout_options = crane_get_options_for_current_blog();
$target = ( isset( $layout_options['target'] ) && 'blank' === $layout_options['target'] ) ? '_blank' : '_self';

if ( ! empty( $tags ) ) { ?>
	<div class="crane-blog-tag-list">
		<?php

		echo '<span>' . esc_html__( 'Tags', 'crane' ) . '</span>';

		foreach ( $tags as $tag ) {
			$attr_escaped      = 'style';
			$styles            = '';
			$name              = $tag->name;
			$data              = maybe_unserialize( get_term_meta( $tag->term_id, 'crane_term_additional_meta', true ) ) ? : array();
			$color             = isset( $data['color'] ) ? $data['color'] : 'grey';
			$color_custom      = ( isset( $data['color'] ) && $data['color'] === 'custom' && ! empty( $data['color_custom'] ) ) ? $data['color_custom'] : '';
			$color_text_custom = ( isset( $data['color'] ) && $data['color'] === 'custom' && ! empty( $data['color_text_custom'] ) ) ? $data['color_text_custom'] : '';

			if ( esc_attr( $color_custom ) ) {
				$styles .= 'background-color: ' . esc_attr( $color_custom ) . ';';
			}
			if ( esc_attr( $color_text_custom ) ) {
				$styles .= 'color: ' . esc_attr( $color_text_custom ) . ';';
			}
			$attr_escaped .= '="' . $styles . '"';
			?>

			<a class="crane-blog-tag-item" href="<?php echo esc_url( get_category_link( $tag ) ); ?>" target="<?php echo esc_attr( $target ); ?>">
				<span class="crane-blog-tag-txt crane-blog-tag-style--<?php echo esc_attr( $color ); ?>" <?php echo crane_clear_echo( $attr_escaped ); ?>><?php echo esc_html( $name ); ?></span>
			</a>
		<?php } ?>
	</div>
<?php }
