<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );
/**
 * Portfolio category widget Class.
 *
 * @package crane
 */


if ( ! class_exists( 'Grooni_Widget_PortfolioCategory' ) ) {

	/**
	 * Portfolio category widget Class.
	 */
	class Grooni_Widget_PortfolioCategory extends WP_Widget {

		/**
		 * Sets up a new Recent Posts widget instance.
		 */
		public function __construct() {
			parent::__construct(
				'grooni_widget_portfolio_category',
				esc_html__( 'Grooni portfolio categories', 'grooni-theme-addons' ),
				array(
					'classname'                   => 'grooni_widget_portfolio_category',
					'description'                 => esc_html__( 'It shows portfolio categories', 'grooni-theme-addons' ),
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
			//Defaults
			$instance     = wp_parse_args( (array) $instance, array( 'title' => '' ) );
			$title        = sanitize_text_field( $instance['title'] );
			$count        = isset( $instance['count'] ) ? (bool) $instance['count'] : false;
			$hierarchical = isset( $instance['hierarchical'] ) ? (bool) $instance['hierarchical'] : false;
			$dropdown     = isset( $instance['dropdown'] ) ? (bool) $instance['dropdown'] : false;
			?>
			<p><label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title:', 'grooni-theme-addons' ); ?></label>
				<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"
				       name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text"
				       value="<?php echo esc_attr( $title ); ?>"/></p>

			<p><input type="checkbox" class="checkbox" id="<?php echo esc_attr( $this->get_field_id( 'dropdown' ) ); ?>"
			          name="<?php echo esc_attr( $this->get_field_name( 'dropdown' ) ); ?>"<?php checked( $dropdown ); ?> />
				<label for="<?php echo esc_attr( $this->get_field_id( 'dropdown' ) ); ?>"><?php esc_html_e( 'Display as dropdown', 'grooni-theme-addons' ); ?></label><br/>

				<input type="checkbox" class="checkbox" id="<?php echo esc_attr( $this->get_field_id( 'count' ) ); ?>"
				       name="<?php echo esc_attr( $this->get_field_name( 'count' ) ); ?>"<?php checked( $count ); ?> />
				<label for="<?php echo esc_attr( $this->get_field_id( 'count' ) ); ?>"><?php esc_html_e( 'Show post counts', 'grooni-theme-addons' ); ?></label><br/>

				<input type="checkbox" class="checkbox" id="<?php echo esc_attr( $this->get_field_id( 'hierarchical' ) ); ?>"
				       name="<?php echo esc_attr( $this->get_field_name( 'hierarchical' ) ); ?>"<?php checked( $hierarchical ); ?> />
				<label for="<?php echo esc_attr( $this->get_field_id( 'hierarchical' ) ); ?>"><?php esc_html_e( 'Show hierarchy', 'grooni-theme-addons' ); ?></label>
			</p>
			<?php
		}


		/**
		 * Outputs the content for portfolio categories widget instance.
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

			$c = ! empty( $instance['count'] ) ? '1' : '0';
			$h = ! empty( $instance['hierarchical'] ) ? '1' : '0';
			$d = ! empty( $instance['dropdown'] ) ? '1' : '0';

			echo $args['before_widget'];
			if ( $title ) {
				echo $args['before_title'] . $title . $args['after_title'];
			}

			$cat_args = array(
				'taxonomy'     => 'crane_portfolio_cats',
				'orderby'      => 'name',
				'show_count'   => $c,
				'hierarchical' => $h
			);

			if ( $d ) {
				$dropdown_id = "{$this->id_base}-dropdown-{$this->number}";

				$cat_args['show_option_none'] = esc_html__( 'Select Portfolio Category', 'grooni-theme-addons' );
				$cat_args['id']               = $dropdown_id;

				$js_cats    = array();
				$exist_cats = crane_get_terms_by_taxonomy( 'crane_portfolio_cats' );
				foreach ( $exist_cats as $cat ) {
					$js_cats[ $cat['id'] ] = $cat['slug'];
				}

				wp_dropdown_categories( $cat_args );
				?>

				<script type='text/javascript'>
					/* <![CDATA[ */
					(function () {
						var dropdown = document.getElementById("<?php echo esc_js( $dropdown_id ); ?>");
						var exist_cats = <?php echo json_encode( $js_cats ); ?>;

						function onCatChange() {
							if (dropdown.options[dropdown.selectedIndex].value > 0) {
								location.href = "<?php echo esc_url( home_url() ); ?>/?crane_portfolio_cats=" + exist_cats[dropdown.options[dropdown.selectedIndex].value];
							}
						}

						dropdown.onchange = onCatChange;
					})();
					/* ]]> */
				</script>

				<?php
			} else {
				?>
				<ul class="crane-category__list">
					<?php
					$cat_args['title_li'] = '';
					wp_list_categories( $cat_args );
					?>
				</ul>
				<?php
			}

			echo $args['after_widget'];
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
			$instance                 = $old_instance;
			$instance['title']        = sanitize_text_field( $new_instance['title'] );
			$instance['count']        = ! empty( $new_instance['count'] ) ? 1 : 0;
			$instance['hierarchical'] = ! empty( $new_instance['hierarchical'] ) ? 1 : 0;
			$instance['dropdown']     = ! empty( $new_instance['dropdown'] ) ? 1 : 0;

			return $instance;
		}


	}
}

add_action( 'widgets_init', function () {
	register_widget( 'Grooni_Widget_PortfolioCategory' );
} );
