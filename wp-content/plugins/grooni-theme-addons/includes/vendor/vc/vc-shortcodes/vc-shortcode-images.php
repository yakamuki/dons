<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );
/**
 * Add VisualComposer blog shortcode Class.
 *
 * @package Grooni_Theme_Addons
 */


if ( ! class_exists( 'CT_Vc_Images_Widget' ) ) {

	include_once '_widget.php';

	class CT_Vc_Images_Widget extends CT_Vc_Widgets {

		function __construct() {

			parent::__construct();

		}

		public function init_fields() {

			$file = __DIR__ . '/config/images.php';
			if ( file_exists( $file ) ) {
				require_once $file;

				$config = new CT_Vc_Images_Config();

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

			$widgetTitle = isset( $atts['title'] ) ? esc_html( $atts['title'] ) : '';
			$showFrom    = isset( $atts['from'] ) ? (int) esc_attr( $atts['from'] ) : 1;
			$showCount   = isset( $atts['count'] ) ? (int) esc_attr( $atts['count'] ) : 3;
			$showRows    = isset( $atts['rows'] ) ? (int) esc_attr( $atts['rows'] ) : 2;
			$style       = isset( $atts['style'] ) ? esc_attr( $atts['style'] ) : 'classic';
			$actionType  = isset( $atts['action'] ) ? (int) esc_attr( $atts['action'] ) : 1;
			$size        = isset( $atts['size'] ) ? esc_attr( $atts['size'] ) : 'thumbnail';

			$allCount = $showCount * $showRows;

			switch ( $showFrom ) {
				case 1: // Show images from media library
					$argsAttach = array(
						'post_type'      => 'attachment',
						'post_mime_type' => 'image',
						'meta_query'     => array(
							array(
								'key'     => '_grooni_include_image_to_widget',
								'value'   => '1',
								'compare' => '=',
							)
						),
						'numberposts'    => $allCount * 2,
						'post_status'    => 'inherit',
						'orderby'        => 'rand',
						'nopaging'       => true,
					);
					break;
				case 2: // Show featured images of blogs
					$argsAttach = array(
						'post_type'   => 'post',
						'meta_query'  => array(
							array(
								'key'     => '_thumbnail_id',
								'value'   => '',
								'compare' => '!=',
							),
							array(
								'key'     => '_grooni_include_image_to_widget',
								'value'   => '1',
								'compare' => '=',
							)
						),
						'numberposts' => $allCount * 2,
						'post_status' => 'publish',
						'orderby'     => 'date',
						'nopaging'    => true,
					);
					break;
				case 3: // Show featured image from Single portfolio
					$argsAttach = array(
						'post_type'   => 'crane_portfolio',
						'meta_query'  => array(
							array(
								'key'     => '_thumbnail_id',
								'value'   => '',
								'compare' => '!=',
							),
							array(
								'key'     => '_grooni_include_image_to_widget',
								'value'   => '1',
								'compare' => '=',
							)
						),
						'numberposts' => $allCount * 2,
						'post_status' => 'publish',
						'orderby'     => 'date',
						'nopaging'    => true,
					);
					break;
				default:
					$argsAttach = array(
						'post_type'   => 'attachment',
						'numberposts' => $allCount * 2,
						'post_status' => 'inherit',
						'orderby'     => 'date',
						'nopaging'    => true,
					);
					break;
			}

			$recentItemsArr = array();

			global $post;

			// write global $post to temporary $tmp_post
			$tmp_post = $post;

			$attachments = get_posts( $argsAttach );
			if ( $attachments ) {
				foreach ( $attachments as $attachment ) {

					if ( count( $recentItemsArr ) >= $allCount ) {
						break;
					}

					if ( $attachment->post_password ) {
						continue;
					}

					if ( 1 == $showFrom && ! get_post_meta( $attachment->ID, '_grooni_include_image_to_widget', true ) ) {
						continue;
					}

					// thumbnail size
					$size_params = ( 'full' == $size || 'original' == $size ) ? 'full' : $size;


					if ( $showFrom == 1 ) { // Show images from media library
						// (false|array) Returns an array (url, width, height, is_intermediate), or false, if no image is available.
						$image_src = wp_get_attachment_image_src(
							$attachment->ID,
							$size_params
						);

						if ( ! isset( $image_src[0] ) || ! $image_src[0] ) {
							continue;
						}

						if ( 1 == $actionType ) { // lightbox
							$image_url = wp_get_attachment_url( $attachment->ID );
						} else { // attachment page
							$image_url = get_permalink( $attachment->ID );
						}

					} else { // Show featured images from blogs and from portfolio

						// (false|array) Returns an array (url, width, height, is_intermediate), or false, if no image is available.
						$image_src = wp_get_attachment_image_src(
							get_post_thumbnail_id( $attachment->ID ),
							$size_params
						);

						if ( ! isset( $image_src[0] ) || ! $image_src[0] ) {
							continue;
						}

						if ( 1 == $actionType ) { // lightbox
							$image_url = wp_get_attachment_url( get_post_thumbnail_id( $attachment->ID ) );
						} elseif ( 2 == $actionType ) { // attachment page
							$image_url = get_attachment_link( get_post_thumbnail_id( $attachment->ID ) );
						} else { // post url
							$image_url = get_permalink( $attachment->ID );
						}
					}


					$imageWidth  = null;
					$imageHeight = null;

					if ( is_array( $image_src ) ) {
						$imageWidth  = ' width="' . esc_attr( $image_src[1] ) . '"';
						$imageHeight = ' height="' . esc_attr( $image_src[2] ) . '"';
						$image_src   = $image_src[0];
					}
					$imageTitle     = apply_filters( 'the_title', $attachment->post_title );
					$linkActionText = '';
					if ( 1 == $actionType ) {
						$linkActionText = ' class="crane-popup-box"';
					}

					if ( grooni_is_lazyload_enabled() ) {
						$image_src_attr = 'data-src="' . esc_url( $image_src ) . '"';
						$image_class_attr = 'class="lazyload"';
					} else {
						$image_src_attr = 'src="' . esc_url( $image_src ) . '"';
						$image_class_attr = '';
					}

					$recentItem = '<div class="crane-w-images-item">';
					$recentItem .= '  <a href="' . esc_url( $image_url ) . '"' . $linkActionText . '>';
					$recentItem .= '    <img ' . $image_src_attr . ' alt="' . esc_attr( $imageTitle ) . '"' . $imageWidth . $imageHeight . $image_class_attr . '>';
					$recentItem .= '  </a>';
					$recentItem .= '</div>';

					$recentItemsArr[] = $recentItem;

				}
			}

			// return temporary
			$post = $tmp_post;


			if ( 1 == $actionType ) { // lightbox
				$action_type_txt = 'lightbox';
			} elseif ( 2 == $actionType ) { // attachment page
				$action_type_txt = 'attach';
			} else { // post url
				$action_type_txt = 'post';
			}

			$output .= $args['before_widget'];
			if ( $widgetTitle && ! empty( $widgetTitle ) ) {
				$output .= $args['before_title'] . $widgetTitle . $args['after_title'];
			}
			$output .= '<div class="crane-w-images crane-w-images--open-' . esc_attr( $action_type_txt ) . ' crane-w-images--style-' . esc_attr( $style ) . ' crane-w-images-col-' . esc_attr( $showCount ) . '">';

			if ( count( $recentItemsArr ) ) {
				$output .= implode( ' ', $recentItemsArr );
			} else {
				$output .= '<p class="crane-w-images-no-content">' . esc_html__( 'Please add images from media or featured image from posts.', 'grooni-theme-addons' ) . '</p>';
			}

			$output .= '</div>'; // class="widget-wrapper"
			$output .= $args['after_widget'];


			return $output;
		}


	}

	new CT_Vc_Images_Widget();

}
