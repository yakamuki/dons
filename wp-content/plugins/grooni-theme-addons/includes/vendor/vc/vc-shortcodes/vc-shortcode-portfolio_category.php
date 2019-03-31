<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );
/**
 * Add VisualComposer blog shortcode Class.
 *
 * @package Grooni_Theme_Addons
 */


if ( ! class_exists( 'CT_Vc_PortfolioCategory_Widget' ) ) {

	include_once '_widget.php';

	class CT_Vc_PortfolioCategory_Widget extends CT_Vc_Widgets {

		function __construct() {

			parent::__construct();

		}

		public function init_fields() {

			$file = __DIR__ . '/config/portfolio_category.php';
			if ( file_exists( $file ) ) {
				require_once $file;

				$config = new CT_Vc_PortfolioCategory_Config();

				$this->tag         = $config::get_data( 'tag' );
				$this->name        = $config::get_data( 'name' );
				$this->description = $config::get_data( 'description' );

				$this->fields          = $config::fields();
				$this->as_parent       = $config::as_parent();
				$this->content_element = $config::content_element();
				$this->icon            = $config::icon();
			}

		}

		public function render( $atts, $content = null ) {

			$atts = $this->fill_empty_atts( $atts );

			$output = $this->render_items( $atts );

			return $output;

		}


		/**
		 * Front-end render (wrapper)
		 *
		 * @param $atts
		 *
		 * @return string
		 */
		protected function render_items( $atts ) {

			$args = array(
				'before_widget' => '<div class="widget">',
				'after_widget'  => '</div>',
				'before_title'  => '<h4 class="widget-title">',
				'after_title'   => '</h4>'
			);

			$output = '';

			$title = ( ! empty( $atts['title'] ) ) ? esc_html( $atts['title'] ) : '';

			$c = ! empty( $atts['count'] ) ? '1' : '0';
			$h = ! empty( $atts['hierarchical'] ) ? '1' : '0';
			$d = ! empty( $atts['dropdown'] ) ? '1' : '0';

			$output .= $args['before_widget'];
			if ( $title ) {
				$output .= $args['before_title'] . $title . $args['after_title'];
			}

			$cat_args = array(
				'taxonomy'     => 'crane_portfolio_cats',
				'orderby'      => 'name',
				'show_count'   => $c,
				'hierarchical' => $h,
				'echo'         => false
			);

			if ( $d ) {
				$dropdown_id = "ct_dropdown-{$this->get_new_uniqid( true )}";

				$cat_args['show_option_none'] = esc_html__( 'Select Portfolio Category', 'grooni-theme-addons' );
				$cat_args['id']               = $dropdown_id;

				$js_cats    = array();
				$exist_cats = crane_get_terms_by_taxonomy( 'crane_portfolio_cats' );
				foreach ( $exist_cats as $cat ) {
					$js_cats[ $cat['id'] ] = $cat['slug'];
				}

				$output .= wp_dropdown_categories( $cat_args );

				$output .= '
				<script type="text/javascript">
					/* <![CDATA[ */
					(function () {
						var dropdown = document.getElementById("' . esc_js( $dropdown_id ) . '");
						var exist_cats = ' . json_encode( $js_cats ) . ';

						function onCatChange() {
							if (dropdown.options[dropdown.selectedIndex].value > 0) {
								location.href = "' . esc_url( home_url() ) . '/?crane_portfolio_cats=" + exist_cats[dropdown.options[dropdown.selectedIndex].value];
							}
						}

						dropdown.onchange = onCatChange;
					})();
					/* ]]> */
				</script>
				';

			} else {

				$output .= '<ul class="crane-category__list">';

				$cat_args['title_li'] = '';
				$output .= wp_list_categories( $cat_args );

				$output .= '</ul>';

			}

			$output .= $args['after_widget'];


			return $output;
		}


	}

	new CT_Vc_PortfolioCategory_Widget();

}
