<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

if ( ! class_exists( 'ReduxFramework_crane_groovy_menu' ) ) {

	/**
	 * Main ReduxFramework_crane_groovy_menu class
	 */
	class ReduxFramework_crane_groovy_menu extends ReduxFramework {

		function __construct( $field = array(), $value = '', $parent ) {

			$this->parent = $parent;
			$this->field  = $field;
			$this->value  = $value;

			if ( empty( $this->extension_dir ) ) {
				$this->extension_dir = trailingslashit( str_replace( '\\', '/', dirname( __FILE__ ) ) );
				$this->extension_url = site_url( str_replace( trailingslashit( str_replace( '\\', '/', ABSPATH ) ), '', $this->extension_dir ) );
			}

			// Set default args for this field to avoid bad indexes. Change this to anything you use.
			$defaults    = array(
				'options'          => array(),
				'stylesheet'       => '',
				'output'           => true,
				'enqueue'          => false,
				'enqueue_frontend' => false
			);
			$this->field = wp_parse_args( $this->field, $defaults );

		}

		/**
		 * Field Render Function.
		 * Takes the vars and outputs the HTML for the field in the settings
		 *
		 * @access      public
		 * @return      void
		 */
		public function render() {
			$this->field = wp_parse_args(
				$this->field,
				array(
					'full_width' => true,
					'overflow'   => 'inherit',
				)
			);

			$field_id_escaped   = esc_attr( $this->parent->args['opt_name'] . '-' . $this->field['id'] );
			$preview_wrapper_id = $field_id_escaped . '_modal_preview';

			if ( class_exists( 'GroovyMenuPreset' ) ) {
				$current = $this->get_preset( $this->value );

				$html_presets_sanitized = '<div class="groovy-menu-redux-current">';
				$html_presets_sanitized .= $this->render_preset( $current, true, null, $field_id_escaped );
				$html_presets_sanitized .= '<input class="grooni-metabox-groovy-value" type="hidden" data-id="' . esc_attr( $this->field['id'] ) . '" name="' . esc_attr( $this->field['name'] . $this->field['name_suffix'] ) . '" id="' . $field_id_escaped . '-value" value="' . esc_attr( $this->value ) . '">';
				$html_presets_sanitized .= '</div><div class="clear"></div>';

				$html_presets_sanitized .= '
<div class="modal fade modal-fullscreen groovy-menu-redux-modal" id="' . $field_id_escaped . '-preset-modal" tabindex="-1">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">
					<span aria-hidden="true">&times;</span>
				</button>
				<h4 class="modal-title">' . esc_html__( 'Select preset', 'crane' ) . '</h4>
			</div>
			<div class="modal-body">
';

				foreach ( GroovyMenuPreset::getAll() as $preset ) {
					$html_presets_sanitized .= $this->render_preset( $preset, false, $current, null, $preview_wrapper_id );
				}

				$html_presets_sanitized .= '
			</div>
			<div class="modal-footer">
				<div class="btn-group">
					<button type="button" class="btn modal-btn" data-dismiss="modal">' . esc_html__( 'Close', 'crane' ) . '</button>
				</div>
			</div>
		</div>
	</div>
</div>
';

				$html_presets_sanitized .= $this->render_preview_modal( $preview_wrapper_id );


			} else {
				$html_presets_sanitized = '<input class="grooni-metabox-groovy-value" type="hidden" name="' . esc_attr( $this->field['name'] . $this->field['name_suffix'] ) . '" value="' . esc_attr( $this->value ) . '">';
				$html_presets_sanitized .= esc_html__( 'Please, install and activate Groovy Menu plugin', 'crane' );
			}


			?>
			<div class="crane-groovy-menu-select-wrapper">
				<?php echo crane_clear_echo( $html_presets_sanitized ); ?>
			</div>
			<?php

		}


		public function render_preview_modal( $wrapper_id = 'groovy_menu-preview-modal' ) {
			$lang            = [ ];
			$lang['Preview'] = __( 'Preview', 'crane' );
			$lang['Default'] = __( 'Default', 'crane' );
			$lang['Sticky']  = __( 'Sticky', 'crane' );

			$html = <<<HTML
<script>
var previewMobileWidth;
</script>
<div class="modal fade modal-fullscreen preview-modal-wrapper" id="{$wrapper_id}" tabindex="-1">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-body iframe--size-desktop">
				<div class="preview-size-change">
					<div class="modal-info">
						<span class="modal-title">{$lang['Preview']}</span>
						<span class="modal-preview-name"></span>
					</div>

					<div class="preview-size-change__tabs">
						<a href="#" data-size="desktop" class="active">
							<svg version="1.1" class="svg-preview-desktop" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="27px" height="23px"
		 viewBox="0 0 27 23" style="enable-background:new 0 0 27 23;" xml:space="preserve">
								<g>
									<path d="M26.1,0.6c-0.4-0.4-1-0.6-1.6-0.6H2.4C1.8,0,1.3,0.2,0.9,0.6c-0.4,0.4-0.6,1-0.6,1.6v15c0,0.6,0.2,1.1,0.6,1.6
										c0.4,0.4,1,0.6,1.6,0.6H10c0,0.4-0.1,0.7-0.2,1.1c-0.1,0.4-0.3,0.7-0.4,1c-0.1,0.3-0.2,0.5-0.2,0.6c0,0.2,0.1,0.4,0.3,0.6
										C9.5,22.9,9.7,23,10,23H17c0.2,0,0.4-0.1,0.6-0.3c0.2-0.2,0.3-0.4,0.3-0.6c0-0.1-0.1-0.3-0.2-0.6c-0.1-0.3-0.3-0.6-0.4-1
										c-0.1-0.4-0.2-0.7-0.2-1.1h7.5c0.6,0,1.1-0.2,1.6-0.6c0.4-0.4,0.6-1,0.6-1.6v-15C26.8,1.6,26.6,1.1,26.1,0.6z M25,13.7
										c0,0.1,0,0.2-0.1,0.3c-0.1,0.1-0.2,0.1-0.3,0.1H2.4c-0.1,0-0.2,0-0.3-0.1C2,13.9,2,13.8,2,13.7V2.2C2,2.1,2,2,2.1,1.9
										c0.1-0.1,0.2-0.1,0.3-0.1h22.1c0.1,0,0.2,0,0.3,0.1C25,2,25,2.1,25,2.2V13.7L25,13.7z"/>
								</g>
							</svg>
						</a>
						<a href="#" data-size="tablet">
							<svg version="1.1" class="svg-preview-mobile" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="16px" height="27px" viewBox="0 0 16 27" style="enable-background:new 0 0 16 27;" xml:space="preserve">
						<path d="M13.2,0.2H2.8C1.2,0.2,0,1.4,0,2.9v0.3v18.8v2.1c0,1.5,1.2,2.8,2.8,2.8h10.5c1.5,0,2.8-1.2,2.8-2.8v-2.1V3.2V2.9 C16,1.4,14.8,0.2,13.2,0.2z M8,25.4c-0.7,0-1.3-0.6-1.3-1.3c0-0.7,0.6-1.3,1.3-1.3c0.7,0,1.3,0.6,1.3,1.3C9.3,24.8,8.7,25.4,8,25.4z M14.7,21.3H1.3V3.9h13.3V21.3z"/>
							</svg>
						</a>
					</div>

					<div class="preview-color-change__tabs">
						<a href="#" data-color="transparent" class="active">
							<span class="preview-color-placeholder"></span>
						</a>
						<a href="#" data-color="black">
							<span class="preview-color-placeholder"></span>
						</a>
						<a href="#" data-color="gray">
							<span class="preview-color-placeholder"></span>
						</a>
						<a href="#" data-color="white">
							<span class="preview-color-placeholder"></span>
						</a>
					</div>


					<div class="preview-sticky-change__tabs">
						<a href="#" data-sticky="false" class="active">{$lang['Default']}</a>
						<a href="#" data-sticky="true">{$lang['Sticky']}</a>
					</div>
					<span class="close" data-dismiss="modal"></span>
				</div>
				<div class="modal-body-iframe"></div>
			</div>
		</div>
	</div>
</div>
HTML;

			return $html;

		}


		/**
		 * Output Function.
		 * Used to enqueue to the front-end
		 * @access      public
		 * @return      void
		 */
		public function output() {

			if ( $this->field['enqueue_frontend'] ) {

			}

		}


		protected function render_preset( $preset, $current = false, $selected = null, $link_id = null, $field_id = 'preview-modal' ) {
			$html = '';

			if ( class_exists( 'GroovyMenuPreset' ) && function_exists( 'GroovyMenuPreviewModal' ) ) {

				if ( ! $preset ) {
					$preset = GroovyMenuPreset::getById( GroovyMenuPreset::getDefaultPreset() );
				}

				if ( ! $preset ) {
					return '<p>' . esc_html__( 'Please, create a preset.', 'crane' ) . '</p>';
				}

				$screenshot_escaped = esc_attr( GroovyMenuPreset::getPreviewById( $preset->id ) );

				$class   = '';
				$preview = '';
				if ( $current ) {
					$class = 'preset--current';

				} else {
					$preview = '
					<div class="preset-opts__dropdown">
					    <i class="gm-show-preset-preview fa fa-search" data-showmodal="' . $field_id . '"></i>
					</div>
					';
				}
				if ( ! is_null( $selected ) && isset( $selected->id ) && $selected->id === $preset->id ) {
					$class = 'preset--selected';
				}

				$preset_name = esc_attr( $preset->name );
				$preset_id   = esc_attr( $preset->id );
				$modal_id    = $link_id ? ' data-modal-id="' . esc_attr( $link_id ) . '"' : '';

				$pattern =
					'
<div class="preset preset--groovy %1$s" data-name="%2$s" data-id="%3$s">
	<div class="preset-inner">
		<a class="preset-placeholder" href="#"%4$s>
			<img src="%5$s" alt="preset %6$s" />
		</a>
		<div class="preset-info">
			<div class="preset-title">
				<span class="preset-title__alpha">%6$s</span>
			</div>
			%7$s
		</div>
	</div>
</div>
';
				$html    = sprintf(
					$pattern,
					$class,
					$preset_name,
					$preset_id,
					$modal_id,
					$screenshot_escaped,
					$preset_name,
					$preview
				);

			}

			return $html;
		}


		protected function get_preset( $preset_id = null ) {
			$preset = null;

			if ( class_exists( 'GroovyMenuPreset' ) ) {
				if ( empty( $preset_id ) || $preset_id === 'default' ) {
					$preset = GroovyMenuPreset::getById( GroovyMenuPreset::getDefaultPreset() );
				} else {
					$preset = GroovyMenuPreset::getById( $preset_id );
				}

			}

			return $preset;
		}

	}
}
