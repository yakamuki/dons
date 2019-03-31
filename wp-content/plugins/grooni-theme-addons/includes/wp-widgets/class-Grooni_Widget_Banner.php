<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );
/**
 * Image banner widget Class.
 *
 * @package crane
 */


if ( ! class_exists( 'Grooni_Widget_Banner' ) ) {

	/**
	 * Image banner widget Class.
	 */
	class Grooni_Widget_Banner extends WP_Widget {

		/**
		 * Sets up a new Banner widget instance.
		 */
		function __construct() {
			parent::__construct(
				'grooni_widget_banner',
				esc_html__( 'Grooni banner widget', 'grooni-theme-addons' ),
				array(
					'description'                 => esc_html__( 'Simple banner widget', 'grooni-theme-addons' ),
					'customize_selective_refresh' => true,
				)
			);
		}

		public function widget( $args, $instance ) {
			if ( ! empty( $instance['image'] ) ) {
				echo $args['before_widget'];
				echo '<div class="crane-widget-banner">';

				if ( ! empty( $instance['title'] ) ) {
					echo $args['before_title'];
					echo esc_html( $instance['title'] );
					echo $args['after_title'];
				}

				$image = wp_get_attachment_image_src(
					$instance['image'],
					'full'
				);

				$image_src = isset( $image[0] ) ? $image[0] : '';

				$output = '';

				if ( $image_src ) {
					$output .= '<img class="crane-widget-banner-img" src="' . esc_url( $image_src ) . '">';
				}

				if ( isset( $instance['action'] ) && 'url' == $instance['action'] ) {
					$output = '<a class="crane-widget-banner-link" href="' . esc_url( $instance['link'] ) . '" target="_blank">' . $output . '</a>';
				}

				echo $output;

				echo '</div>';
				echo $args['after_widget'];
			}
		}

		public function form( $instance ) {

			$link = '';
			if ( isset( $instance['link'] ) ) {
				$link = $instance['link'];
			}

			$title = '';
			if ( isset( $instance['title'] ) ) {
				$title = $instance['title'];
			}

			$action = '';
			if ( isset( $instance['action'] ) ) {
				$action = $instance['action'];
			}

			$image     = '';
			$image_src = '';
			if ( isset( $instance['image'] ) ) {
				$image     = $instance['image'];
				$image_src = wp_get_attachment_image_src( $image, [ 60, 60 ] );
				$image_src = isset( $image_src[0] ) ? $image_src[0] : '';
			}

			?>

			<div class="crane-bn_widget_wrapper">
				<p>
					<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title:', 'grooni-theme-addons' ); ?></label>
					<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"
					       name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text"
					       value="<?php echo esc_attr( $title ); ?>"/>
				</p>

				<p>
					<label for="<?php echo esc_attr( $this->get_field_id( 'action' ) ); ?>"><?php esc_html_e( 'Action on click:', 'grooni-theme-addons' ); ?></label>
					<select name="<?php echo esc_attr( $this->get_field_name( 'action' ) ); ?>"
					        id="<?php echo esc_attr( $this->get_field_id( 'action' ) ); ?>"
					        class="crane-bn_widget_action_field">
						<option <?php echo( ( $action == 'none' || ! $action ) ? 'selected' : '' ); ?>
							value="none"><?php esc_html_e( 'Without action', 'grooni-theme-addons' ); ?></option>
						<option <?php echo( ( $action == 'url' ) ? 'selected' : '' ); ?>
							value="url"><?php esc_html_e( 'Go to the URL', 'grooni-theme-addons' ); ?></option>
					</select>
				</p>
				<p class="crane-bn_link_wrap">
					<label for="<?php echo esc_attr( $this->get_field_id( 'link' ) ); ?>"><?php esc_html_e( 'Link:', 'grooni-theme-addons' ); ?></label>
					<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'link' ) ); ?>"
					       name="<?php echo esc_attr( $this->get_field_name( 'link' ) ); ?>" type="text"
					       value="<?php echo esc_attr( $link ); ?>"/>
				</p>

				<p>
					<label for="<?php echo esc_attr( $this->get_field_name( 'image' ) ); ?>"><?php esc_html_e( 'Image:', 'grooni-theme-addons' ); ?></label>
					<input class="widefat image-id" name="<?php echo esc_attr( $this->get_field_name( 'image' ) ); ?>"
					       id="<?php echo esc_attr( $this->get_field_id( 'image' ) ); ?>" type="hidden"
					       value="<?php echo esc_attr( $image ); ?>"/>
					<input class="crane-banner-widget-upload-image-button button button-primary" type="button"
					       value="<?php esc_attr_e( 'Upload Image', 'grooni-theme-addons' ); ?>"/>
				</p>

				<div class="crane-banner-widget-upload-image"><img src="<?php echo esc_url( $image_src ); ?>"
				                                                   alt="" <?php if ( ! $image_src ) {
						echo 'style="display:none;"';
					} ?>/>
				</div>
			</div>
			<?php
		}

		public function update( $new_instance, $old_instance ) {
			$instance           = array();
			$instance['action'] = ( ! empty( $new_instance['action'] ) ) ? strip_tags( $new_instance['action'] ) : '';
			$instance['link']   = ( ! empty( $new_instance['link'] ) ) ? strip_tags( $new_instance['link'] ) : '';
			$instance['image']  = ( ! empty( $new_instance['image'] ) ) ? strip_tags( $new_instance['image'] ) : '';
			$instance['title']  = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';

			return $instance;
		}
	}
}

add_action( 'widgets_init', function () {
	register_widget( 'Grooni_Widget_Banner' );
} );
