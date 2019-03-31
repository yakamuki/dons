<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );
/**
 * Last images from media library or featured images widget Class.
 *
 * @package crane
 */


if ( ! class_exists( 'Grooni_Widget_RecentImages' ) ) {
	/**
	 * Adds Crane recent images widget
	 * It shows the last images from the posts featured image or media library
	 *
	 */
	class Grooni_Widget_RecentImages extends WP_Widget {

		/**
		 * Register widget with WordPress.
		 * Sets up the widgets name, html-class, description
		 */
		public function __construct() {
			parent::__construct(
				'grooni_widget_recent_images',
				esc_html__( 'Grooni images', 'grooni-theme-addons' ),
				array(
					'classname'                   => 'grooni_widget_recent_images',
					'description'                 => esc_html__( 'It shows the last images of the blogs and media library', 'grooni-theme-addons' ),
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
			if ( $instance && ! empty( $instance ) ) {
				$title      = isset( $instance['ct_ri_title'] ) ? esc_attr( $instance['ct_ri_title'] ) : '';
				$showFrom   = isset( $instance['ct_ri_showfrom'] ) ? (int) esc_attr( $instance['ct_ri_showfrom'] ) : 1;
				$showCount  = isset( $instance['ct_ri_showcount'] ) ? (int) esc_attr( $instance['ct_ri_showcount'] ) : 3;
				$showRows   = isset( $instance['ct_ri_showrows'] ) ? (int) esc_attr( $instance['ct_ri_showrows'] ) : 2;
				$style      = isset( $instance['ct_ri_style'] ) ? esc_attr( $instance['ct_ri_style'] ) : 'classic';
				$actionType = isset( $instance['ct_ri_action'] ) ? (int) esc_attr( $instance['ct_ri_action'] ) : 1;
				$size       = isset( $instance['ct_ri_size'] ) ? esc_attr( $instance['ct_ri_size'] ) : 'thumbnail';
			} else {
				$title      = esc_html__( 'Images', 'grooni-theme-addons' );
				$showCount  = 3;
				$showRows   = 2;
				$showFrom   = 1;
				$style      = 'classic';
				$actionType = 1;
				$size       = 'thumbnail';
			}

			?>
			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'ct_ri_title' ) ); ?>"><?php esc_html_e( 'Widget title', 'grooni-theme-addons' ); ?></label>
				<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'ct_ri_title' ) ); ?>"
				       name="<?php echo esc_attr( $this->get_field_name( 'ct_ri_title' ) ); ?>" type="text"
				       value="<?php echo esc_attr( $title ); ?>">
			</p>

			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'ct_ri_showrows' ) ); ?>"><?php esc_html_e( 'How many rows to show? (min 1, max 6)', 'grooni-theme-addons' ); ?></label>
				<select name="<?php echo esc_attr( $this->get_field_name( 'ct_ri_showrows' ) ); ?>"
				        id="<?php echo esc_attr( $this->get_field_id( 'ct_ri_showrows' ) ); ?>" class="widefat">
					<?php
					for ( $i = 1; $i <= 6; $i ++ ) {
						echo '<option ' . ( ( $showRows == $i ) ? 'selected' : '' ) . ' value="' . $i . '">' . $i . '</option>';
					}
					?>
				</select>
			</p>

			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'ct_ri_showcount' ) ); ?>"><?php esc_html_e( 'How many images in row? (min 1, max 4)', 'grooni-theme-addons' ); ?></label>
				<select name="<?php echo esc_attr( $this->get_field_name( 'ct_ri_showcount' ) ); ?>"
				        id="<?php echo esc_attr( $this->get_field_id( 'ct_ri_showcount' ) ); ?>" class="widefat">
					<?php
					for ( $i = 1; $i <= 4; $i ++ ) {
						echo '<option ' . ( ( $showCount == $i ) ? 'selected' : '' ) . ' value="' . $i . '">' . $i . '</option>';
					}
					?>
				</select>
			</p>

			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'ct_ri_style' ) ); ?>"><?php esc_html_e( 'Images style', 'grooni-theme-addons' ); ?></label>
				<select name="<?php echo esc_attr( $this->get_field_name( 'ct_ri_style' ) ); ?>"
				        id="<?php echo esc_attr( $this->get_field_id( 'ct_ri_style' ) ); ?>" class="widefat">
					<option <?php echo( ( $style == 'classic' ) ? 'selected' : '' ); ?>
						value="classic"><?php esc_html_e( 'Classic', 'grooni-theme-addons' ); ?></option>
				</select>
			</p>

			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'ct_ri_size' ) ); ?>"><?php esc_html_e( 'Image size', 'grooni-theme-addons' ); ?></label>
				<select name="<?php echo esc_attr( $this->get_field_name( 'ct_ri_size' ) ); ?>"
				        id="<?php echo esc_attr( $this->get_field_id( 'ct_ri_size' ) ); ?>" class="widefat">
					<option <?php echo( ( $size == 'thumbnail' ) ? 'selected' : '' ); ?>
						value="thumbnail"><?php esc_html_e( 'Thumbnail', 'grooni-theme-addons' ); ?></option>

					<option <?php echo( ( $size == 'medium' ) ? 'selected' : '' ); ?>
						value="medium"><?php esc_html_e( 'Medium', 'grooni-theme-addons' ); ?></option>

					<option <?php echo( ( $size == 'large' ) ? 'selected' : '' ); ?>
						value="large"><?php esc_html_e( 'Large', 'grooni-theme-addons' ); ?></option>

					<option <?php echo( ( $size == 'original' ) ? 'selected' : '' ); ?>
						value="original"><?php esc_html_e( 'Original', 'grooni-theme-addons' ); ?></option>
				</select>
			</p>

			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'ct_ri_showfrom' ) ); ?>"><?php esc_html_e( 'Show from:', 'grooni-theme-addons' ); ?></label>
				<select name="<?php echo esc_attr( $this->get_field_name( 'ct_ri_showfrom' ) ); ?>"
				        id="<?php echo esc_attr( $this->get_field_id( 'ct_ri_showfrom' ) ); ?>" class="widefat">
					<option <?php echo( ( $showFrom == 1 ) ? 'selected' : '' ); ?>
						value="1"><?php esc_html_e( 'Show images from media library', 'grooni-theme-addons' ); ?></option>
					<option <?php echo( ( $showFrom == 2 ) ? 'selected' : '' ); ?>
						value="2"><?php esc_html_e( 'Show featured images of blogs', 'grooni-theme-addons' ); ?></option>
					<option <?php echo( ( $showFrom == 3 ) ? 'selected' : '' ); ?>
						value="3"><?php esc_html_e( 'Show featured image from Single portfolio', 'grooni-theme-addons' ); ?></option>
				</select>
			</p>

			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'ct_ri_action' ) ); ?>"><?php esc_html_e( 'Action on click', 'grooni-theme-addons' ); ?></label>
				<select name="<?php echo esc_attr( $this->get_field_name( 'ct_ri_action' ) ); ?>"
				        id="<?php echo esc_attr( $this->get_field_id( 'ct_ri_action' ) ); ?>" class="widefat">
					<option <?php echo( ( $actionType == 1 ) ? 'selected' : '' ); ?>
						value="1"><?php esc_html_e( 'Open in lightbox', 'grooni-theme-addons' ); ?></option>
					<option <?php echo( ( $actionType == 2 ) ? 'selected' : '' ); ?>
						value="2"><?php esc_html_e( 'Open attachment page', 'grooni-theme-addons' ); ?></option>
					<option <?php echo( ( $actionType == 3 ) ? 'selected' : '' ); ?>
						value="3"><?php esc_html_e( 'Open post', 'grooni-theme-addons' ); ?></option>
				</select>

				<script type="text/javascript">
					jQuery(function () {
						jQuery(document).ready(function ($) {

							var actionField = "#<?php echo esc_attr( $this->get_field_id( 'ct_ri_action' )); ?>";
							var showFrom = "#<?php echo esc_attr( $this->get_field_id( 'ct_ri_showfrom' )); ?>";

							$(showFrom).on('change', function () {
								if ($(this).val() == '1') {
									$(actionField + " option[value='2']").show();
									$(actionField + " option[value='3']").hide();
									if ($(actionField).val() !== '1') {
										$(actionField).val('2').change();
									}
								} else {
									$(actionField + " option[value='2']").hide();
									$(actionField + " option[value='3']").show();
									if ($(actionField).val() !== '1') {
										$(actionField).val('3').change();
									}
								}
							});

							if ($(showFrom).val() == '1') {
								$(actionField + " option[value='2']").show();
								$(actionField + " option[value='3']").hide();
								if ($(actionField).val() !== '1') {
									$(actionField).val('2').change();
								}
							} else {
								$(actionField + " option[value='2']").hide();
								$(actionField + " option[value='3']").show();
								if ($(actionField).val() !== '1') {
									$(actionField).val('3').change();
								}
							}
						});
					});
				</script>

			</p>
			<?php
		}

		/**
		 * Sanitize widget form values as they are saved.
		 *
		 * @see WP_Widget::update()
		 *
		 * @param array $new_instance Values just sent to be saved.
		 * @param array $old_instance Previously saved values from database.
		 *
		 * @return array Updated safe values to be saved.
		 */
		public function update( $new_instance, $old_instance ) {
			$instance = $old_instance;
			// Fields
			$instance['ct_ri_title']     = strip_tags( $new_instance['ct_ri_title'] );
			$instance['ct_ri_showfrom']  = strip_tags( $new_instance['ct_ri_showfrom'] );
			$instance['ct_ri_showcount'] = strip_tags( $new_instance['ct_ri_showcount'] );
			$instance['ct_ri_showrows']  = strip_tags( $new_instance['ct_ri_showrows'] );
			$instance['ct_ri_style']     = strip_tags( $new_instance['ct_ri_style'] );
			$instance['ct_ri_action']    = strip_tags( $new_instance['ct_ri_action'] );
			$instance['ct_ri_size']      = strip_tags( $new_instance['ct_ri_size'] );


			return $instance;
		}

		/**
		 * Front-end display of widget.
		 *
		 * @see WP_Widget::widget()
		 *
		 * @param array $args Display arguments including 'before_title', 'after_title', 'before_widget', and 'after_widget'.
		 * @param array $instance The settings for the particular instance of the widget.
		 *
		 */
		public function widget( $args, $instance ) {

			$widgetTitle = isset( $instance['ct_ri_title'] ) ? esc_html( $instance['ct_ri_title'] ) : '';
			$showFrom    = isset( $instance['ct_ri_showfrom'] ) ? (int) esc_attr( $instance['ct_ri_showfrom'] ) : 1;
			$showCount   = isset( $instance['ct_ri_showcount'] ) ? (int) esc_attr( $instance['ct_ri_showcount'] ) : 3;
			$showRows    = isset( $instance['ct_ri_showrows'] ) ? (int) esc_attr( $instance['ct_ri_showrows'] ) : 2;
			$style       = isset( $instance['ct_ri_style'] ) ? esc_attr( $instance['ct_ri_style'] ) : 'classic';
			$actionType  = isset( $instance['ct_ri_action'] ) ? (int) esc_attr( $instance['ct_ri_action'] ) : 1;
			$size        = isset( $instance['ct_ri_size'] ) ? esc_attr( $instance['ct_ri_size'] ) : 'thumbnail';

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
					//$imageDesc  = apply_filters( 'the_description', $attachment->post_content );
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

				echo $args['before_widget'];
			if ( $widgetTitle && ! empty( $widgetTitle ) ) {
				echo $args['before_title'] . $widgetTitle . $args['after_title'];
			}

			echo '<div class="crane-w-images crane-w-images--open-'. esc_attr( $action_type_txt ).' crane-w-images_style--' . esc_attr( $style ) . ' crane-w-images-col-' . esc_attr( $showCount ) . '">';

			if ( count( $recentItemsArr ) ) {
				$result = implode( ' ', $recentItemsArr );
			} else {
				$result = '<p class="crane-w-images-no-content">' . esc_html__( 'Please add images from media or featured image from posts.', 'grooni-theme-addons' ) . '</p>';
			}

			echo $result. '</div>'; // class="widget-wrapper"
			echo $args['after_widget'];
		}

	}
}

add_action( 'widgets_init', function () {
	register_widget( 'Grooni_Widget_RecentImages' );
} );

if ( ! function_exists( 'grooni_ri_edit_media_custom_field' ) ) {
	/**
	 * Adding a field for exclude attachment
	 *
	 * @param $form_fields
	 * @param $post
	 *
	 * @return mixed
	 */
	function grooni_ri_edit_media_custom_field( $form_fields, $post ) {
		$images_mime_type = array( 'image/jpeg', 'image/jpg', 'image/png', 'image/gif' );
		if ( ! isset( $post->post_mime_type ) || ! in_array( $post->post_mime_type, $images_mime_type ) ) {
			return $form_fields;
		}

		$js_text = '
		<script type="text/javascript">
			jQuery(function () {
				jQuery(document).ready(function ($) {
					var attachment_id = $(".attachment-details").data("id");
					if (! attachment_id ) {
						attachment_id = $("#post #post_ID").val();
					}

					if (attachment_id) {
						var $showInput = $("#attachments-"+attachment_id+"-grooni_image_widget");
						var isChecked = $showInput.val() ? " checked" : "";
						$showInput.after("<input type=\'checkbox\' name=\'grooni_image_widget-"+attachment_id+"\' class=\'grooni_image_widget-"+attachment_id+"\' value=\'"+$showInput.val()+"\'"+isChecked+">");
						$showInput.hide();

						$(".grooni_image_widget-"+attachment_id ).on("change", function () {
							if ( $(this).prop("checked") ) {
								$showInput.val("1");
							} else {
								$showInput.val("");
							}
							$showInput.change();
						});
					}
				});
			});
		</script>
		';

		$form_fields['grooni_image_widget'] = array(
			'label'      => esc_html__( 'Show in widget', 'grooni-theme-addons' ),
			'input'      => 'text',
			'value'      => esc_attr( get_post_meta( $post->ID, '_grooni_include_image_to_widget', true ) ),
			'helps'      => esc_html__( 'Use to display in the &laquo;Grooni images&raquo; widgets', 'grooni-theme-addons' ),
			'extra_rows' => [ 'gr-additional-script hidden' => [ $js_text ] ],
		);

		return $form_fields;
	}
}

if ( ! function_exists( 'grooni_ri_save_media_custom_field' ) ) {
	/**
	 * Update exclude flag on Save action
	 *
	 * @param $post
	 * @param $attachment
	 *
	 * @return mixed
	 */
	function grooni_ri_save_media_custom_field( $post, $attachment ) {
		update_post_meta( $post['ID'], '_grooni_include_image_to_widget', $attachment['grooni_image_widget'] );

		return $post;
	}
}
add_filter( 'attachment_fields_to_edit', 'grooni_ri_edit_media_custom_field', 11, 2 );
add_filter( 'attachment_fields_to_save', 'grooni_ri_save_media_custom_field', 11, 2 );
