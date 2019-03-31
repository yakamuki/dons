<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );
/**
 * Recent post widget Class.
 *
 * @package crane
 */


if ( ! class_exists( 'Grooni_Widget_RecentPosts' ) ) {

	/**
	 * Recent post widget Class.
	 */
	class Grooni_Widget_RecentPosts extends WP_Widget {

		/**
		 * Sets up a new Recent Posts widget instance.
		 */
		public function __construct() {
			parent::__construct(
				'grooni_widget_recent_posts',
				esc_html__( 'Grooni recent posts', 'grooni-theme-addons' ),
				array(
					'classname'                   => 'grooni_widget_recent_posts',
					'description'                 => esc_html__( 'It shows the most recent Posts', 'grooni-theme-addons' ),
					'customize_selective_refresh' => true,
				)
			);
		}


		/**
		 * Back-end widget form.
		 *
		 * @see WP_Widget::form()
		 *
		 * @param array $instance Previously saved values from database.
		 *
		 * @return null
		 */
		public function form( $instance ) {
			$title     = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : esc_html__( 'Recent Posts', 'grooni-theme-addons' );
			$number    = isset( $instance['number'] ) ? absint( $instance['number'] ) : 5;
			$show_date = isset( $instance['show_date'] ) ? (bool) $instance['show_date'] : false;
			?>
			<p><label
					for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title:', 'grooni-theme-addons' ); ?></label>
				<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"
				       name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text"
				       value="<?php echo esc_attr( $title ); ?>"/>
			</p>

			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'number' ) ); ?>"><?php esc_html_e( 'Number of posts to show:', 'grooni-theme-addons' ); ?></label>
				<input class="tiny-text" id="<?php echo esc_attr( $this->get_field_id( 'number' ) ); ?>"
				       name="<?php echo esc_attr( $this->get_field_name( 'number' ) ); ?>" type="number" step="1"
				       min="1"
				       value="<?php echo esc_attr( $number ); ?>" size="3"/></p>

			<p><input class="checkbox" type="checkbox"<?php checked( $show_date ); ?>
			          id="<?php echo esc_attr( $this->get_field_id( 'show_date' ) ); ?>"
			          name="<?php echo esc_attr( $this->get_field_name( 'show_date' ) ); ?>"/>
				<label for="<?php echo esc_attr( $this->get_field_id( 'show_date' ) ); ?>"><?php esc_html_e( 'Display post date?', 'grooni-theme-addons' ); ?></label>
			</p>
			<?php
		}


		/**
		 * Outputs the content for the current Recent Posts widget instance.
		 *
		 * @param array $args Display arguments including 'before_title', 'after_title',
		 *                        'before_widget', and 'after_widget'.
		 * @param array $instance Settings for the current Recent Posts widget instance.
		 */
		public function widget( $args, $instance ) {
			if ( ! isset( $args['widget_id'] ) ) {
				$args['widget_id'] = $this->id;
			}

			$title = ( ! empty( $instance['title'] ) ) ? esc_html( $instance['title'] ) : '';

			$number = ( ! empty( $instance['number'] ) ) ? absint( $instance['number'] ) : 5;
			if ( ! $number ) {
				$number = 5;
			}
			$show_date = empty( $instance['show_date'] ) ? false : true;

			$request_args = array(
				'numberposts'      => $number,
				'offset'           => 0,
				'category'         => 0,
				'orderby'          => 'post_date',
				'order'            => 'DESC',
				'include'          => '',
				'exclude'          => '',
				'meta_key'         => '',
				'meta_value'       => '',
				'post_type'        => 'post',
				'post_status'      => 'publish',
				'suppress_filters' => true
			);

			$recent_posts = wp_get_recent_posts( $request_args );

			if ( ! empty( $recent_posts ) && is_array( $recent_posts ) ) {
				echo $args['before_widget'];
				if ( $title ) {
					echo $args['before_title'] . $title . $args['after_title'];
				}
				?>
				<ul class="crane-re-posts">
					<?php foreach ( $recent_posts as $recent ) {

						if ( ! isset( $recent['ID'] ) || ! $recent['ID'] ) {
							continue;
						}

						$post_title = $recent['post_title'] ? : esc_html__( 'Post:', 'grooni-theme-addons' ) . ' ' . $recent['ID'];
						$the_date   = mysql2date( get_option( 'date_format' ), $recent['post_date'] );
						$post_img   = get_the_post_thumbnail( $recent['ID'], 'thumbnail' );
						$post_img_url = get_the_post_thumbnail_url( $recent['ID'], 'thumbnail' );

						if ( ! $post_img ) {
							$post_img = crane_get_thumb( $recent['ID'], 'thumbnail' );
							if ( $post_img ) {
								if ( grooni_is_lazyload_enabled() ) {
									$image_src_attr = 'data-src="' . esc_url( $post_img ) . '"';
									$image_class_attr = 'class="attachment-thumbnail size-thumbnail wp-post-image lazyload"';
								} else {
									$image_src_attr = 'src="' . esc_url( $post_img ) . '"';
									$image_class_attr = 'class="attachment-thumbnail size-thumbnail wp-post-image"';
								}

								$post_img = '<img ' . $image_src_attr . $image_class_attr . ' alt="">';
							} else {
								$post_img = '';
							}
						}

						if ( grooni_is_lazyload_enabled() ) {
							$image_src_attr = 'data-src="' . esc_url( $post_img_url ) . '"';
							$image_class_attr = 'class="lazyload"';
						} else {
							$image_src_attr = 'src="' . esc_url( $post_img_url ) . '"';
							$image_class_attr = '';
						}

						?>
						<li class="crane-re-posts__item">
							<?php
							global $crane_options;
							if ( $post_img || ( isset( $crane_options['show_featured_placeholders'] ) && $crane_options['show_featured_placeholders'] ) ) : ?>
								<div class="crane-re-posts__img<?php echo crane_get_placeholder_html_class( $post_img ); ?>">
									<?php if ( $post_img_url ) : ?>
										<img <?php echo $image_src_attr; ?> alt="<?php echo esc_html( $post_title ); ?>" <?php echo $image_class_attr; ?>>
									<?php endif; ?>
								</div>
							<?php endif; ?>
							<div class="crane-re-posts__meta">
								<a class="crane-re-posts__link"
								   href="<?php the_permalink( $recent['ID'] ); ?>"><?php echo esc_html( $post_title ); ?></a>
								<?php if ( $show_date ) : ?>
									<span class="crane-re-posts__date"><?php echo esc_html( $the_date ); ?></span>
								<?php endif; ?>
							</div>
						</li>
					<?php } ?>
				</ul>
				<?php echo $args['after_widget']; ?>

			<?php }
		}


		/**
		 * Handles updating the settings for the current Recent Posts widget instance.
		 *
		 * @param array $new_instance New settings for this instance as input by the user via
		 *                            WP_Widget::form().
		 * @param array $old_instance Old settings for this instance.
		 *
		 * @return array Updated settings to save.
		 */
		public function update( $new_instance, $old_instance ) {
			$instance              = $old_instance;
			$instance['title']     = sanitize_text_field( $new_instance['title'] );
			$instance['number']    = absint( $new_instance['number'] );
			$instance['show_date'] = isset( $new_instance['show_date'] ) ? (bool) $new_instance['show_date'] : false;

			return $instance;
		}

	}
}

add_action( 'widgets_init', function () {
	register_widget( 'Grooni_Widget_RecentPosts' );
} );
