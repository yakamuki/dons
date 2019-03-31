<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );
/**
 * Recent comment widget Class.
 *
 * @package crane
 */


if ( ! class_exists( 'Grooni_Widget_RecentComments' ) ) {

	/**
	 * Recent comment widget Class.
	 */
	class Grooni_Widget_RecentComments extends WP_Widget {

		/**
		 * Sets up a new Recent Posts widget instance.
		 */
		public function __construct() {
			parent::__construct(
				'grooni_widget_recent_comments',
				esc_html__( 'Grooni recent comments', 'grooni-theme-addons' ),
				array(
					'classname'                   => 'grooni_widget_recent_comments',
					'description'                 => esc_html__( 'It shows the most recent Comments', 'grooni-theme-addons' ),
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
			$title          = isset( $instance['title'] ) ? $instance['title'] : esc_html__( 'Recent Comments', 'grooni-theme-addons' );
			$number         = isset( $instance['number'] ) ? absint( $instance['number'] ) : 5;
			$post_type      = isset( $instance['post_type'] ) ? esc_attr( $instance['post_type'] ) : 'post';
			$excerpt_height = isset( $instance['excerpt_height'] ) ? absint( $instance['excerpt_height'] ) : 80;

			$post_types_array = array(
				'post'         => esc_html__( 'Blog', 'grooni-theme-addons' ),
				'page'         => esc_html__( 'Pages', 'grooni-theme-addons' ),
				'crane_portfolio' => esc_html__( 'Portfolio', 'grooni-theme-addons' ),
				'product'      => esc_html__( 'Woocommerce product', 'grooni-theme-addons' ),
				'any'          => esc_html__( 'Any post types', 'grooni-theme-addons' ),
			);

			?>
			<p><label
					for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title:', 'grooni-theme-addons' ); ?></label>
				<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"
				       name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text"
				       value="<?php echo esc_attr( $title ); ?>"/></p>

			<p><label
					for="<?php echo esc_attr( $this->get_field_id( 'number' ) ); ?>"><?php esc_html_e( 'Number of comments to show:', 'grooni-theme-addons' ); ?></label>
				<input class="tiny-text" id="<?php echo esc_attr( $this->get_field_id( 'number' ) ); ?>"
				       name="<?php echo esc_attr( $this->get_field_name( 'number' ) ); ?>" type="number" step="1"
				       min="1"
				       value="<?php echo esc_attr( $number ); ?>" size="3"/></p>

			<p><label
					for="<?php echo esc_attr( $this->get_field_id( 'excerpt_height' ) ); ?>"><?php esc_html_e( 'Excerpt height:', 'grooni-theme-addons' ); ?></label>
				<input id="<?php echo esc_attr( $this->get_field_id( 'excerpt_height' ) ); ?>"
				       name="<?php echo esc_attr( $this->get_field_name( 'excerpt_height' ) ); ?>" type="number"
				       step="1" min="0"
				       value="<?php echo esc_attr( $excerpt_height ); ?>" size="5"/></p>

			<p>
				<label
					for="<?php echo esc_attr( $this->get_field_id( 'post_type' ) ); ?>"><?php esc_html_e( 'Show comments from', 'grooni-theme-addons' ); ?></label>
				<select name="<?php echo esc_attr( $this->get_field_name( 'post_type' ) ); ?>"
				        id="<?php echo esc_attr( $this->get_field_id( 'post_type' ) ); ?>" class="widefat">
					<?php
					foreach ( $post_types_array as $type_name => $type_label ) {
						echo '<option ' . ( ( $post_type == $type_name ) ? 'selected' : '' ) . ' value="' . $type_name . '">' . $type_label . '</option>';
					}
					?>
				</select>
			</p>
			<?php
		}


		/**
		 * Outputs the content for the current Recent Comments widget instance.
		 *
		 * @param array $args Display arguments including 'before_title', 'after_title',
		 *                        'before_widget', and 'after_widget'.
		 * @param array $instance Settings for the current Recent Comments widget instance.
		 */
		public function widget( $args, $instance ) {
			if ( ! isset( $args['widget_id'] ) ) {
				$args['widget_id'] = $this->id;
			}

			$output = '';

			$title          = ( ! empty( $instance['title'] ) ) ? esc_html( $instance['title'] ) : '';
			$number         = ( ! empty( $instance['number'] ) ) ? absint( $instance['number'] ) : 5;
			$post_type      = ( ! empty( $instance['post_type'] ) ) ? esc_attr( $instance['post_type'] ) : 'post';
			$excerpt_height = ( ! empty( $instance['excerpt_height'] ) ) ? absint( $instance['excerpt_height'] ) : 80;

			$arg = array(
				'number'      => $number,
				'status'      => 'approve',
				'post_status' => 'publish'
			);

			if ( 'any' !== $post_type ) {
				$arg['post_type'] = $post_type;
			}

			$comments = get_comments( $arg );

			$output .= $args['before_widget'];
			if ( $title ) {
				$output .= $args['before_title'] . $title . $args['after_title'];
			}

			$output .= '<ul class="crane-re-comments__list">';
			if ( is_array( $comments ) && $comments ) {
				// Prime cache for associated posts. (Prime post term cache if we need it for permalinks.)
				$post_ids = array_unique( wp_list_pluck( $comments, 'comment_post_ID' ) );
				_prime_post_caches( $post_ids, strpos( get_option( 'permalink_structure' ), '%category%' ), false );

				foreach ( (array) $comments as $comment ) {
					$output .= '<li class="crane-re-comments__item">';
					$output .= '	<div class="crane-re-comments__text" data-height ="'.$excerpt_height.'">';
					$output .= '<p>';
					$output .= $comment->comment_content;
					$output .= '</p>';
					$output .= '	</div>';

					$output .= '	<div class="crane-re-comments__meta">';
					/* translators: comments widget: 1: comment author, 2: post link */
					$output .= sprintf( esc_html_x( '%1$s %2$s %3$s', 'widgets', 'grooni-theme-addons' ),
						'<span class="crane-re-comments__author">' . get_comment_author_link( $comment ) . '</span>',
						'<span>on</span>',
						'<a href="' . esc_url( get_comment_link( $comment ) ) . '">' . get_the_title( $comment->comment_post_ID ) . '</a>'
					);
					$output .= '	</div>';
					$output .= '</li>';
				}
			}
			$output .= '</ul>';
			$output .= $args['after_widget'];

			echo $output;
		}


		/**
		 * Handles updating settings for the current Recent Comments widget instance.
		 *
		 * @param array $new_instance New settings for this instance as input by the user via
		 *                            WP_Widget::form().
		 * @param array $old_instance Old settings for this instance.
		 *
		 * @return array Updated settings to save.
		 */
		public function update( $new_instance, $old_instance ) {
			$instance                   = $old_instance;
			$instance['title']          = sanitize_text_field( $new_instance['title'] );
			$instance['number']         = absint( $new_instance['number'] );
			$instance['post_type']      = esc_attr( $new_instance['post_type'] );
			$instance['excerpt_height'] = absint( $new_instance['excerpt_height'] );

			return $instance;
		}

	}
}

add_action( 'widgets_init', function () {
	register_widget( 'Grooni_Widget_RecentComments' );
} );
