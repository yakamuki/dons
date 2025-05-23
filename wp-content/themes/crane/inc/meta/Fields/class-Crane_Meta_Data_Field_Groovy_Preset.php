<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Groovy menu preset selector field for Posts Meta.
 *
 * @package crane
 */
class Crane_Meta_Data_Field_Groovy_Preset extends Crane_Meta_Data_Field {

	public function renderField( $post_id ) {

		$preview_wrapper_id = 'groovy_menu_' . $post_id . '_modal_preview';

		if ( class_exists( 'GroovyMenuPreset' ) ) {
			$current     = $this->getCurrent();
			$select_text = esc_html__( 'Select preset', 'crane' );
			$close_text  = esc_html__( 'Close', 'crane' );
			$html        = $this->renderPreset( $current, true );
			$html .= '<input class="grooni-metabox-groovy-value" type="hidden" name="' . esc_attr( $this->getName() ) . '" value="' . esc_attr( $this->getValue( $post_id ) ) . '">';
			$html .= '<div class="clear"></div>';

			$html .= '
<div class="modal fade modal-fullscreen" id="groovy-select-preset-modal" tabindex="-1">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">
					<span aria-hidden="true">&times;</span>
				</button>
				<h4 class="modal-title">' . $select_text . '</h4>
			</div>
			<div class="modal-body">
';

			foreach ( GroovyMenuPreset::getAll() as $preset ) {
				$html .= $this->renderPreset( $preset, false, $current, $preview_wrapper_id );
			}

			$html .= '
			</div>
			<div class="modal-footer">
				<div class="btn-group">
					<button type="button" class="btn modal-btn" data-dismiss="modal">' . $close_text . '</button>
				</div>
			</div>
		</div>
	</div>
</div>
';

			$html .= $this->render_preview_modal( $preview_wrapper_id );

			return $html;
		} else {
			$html = '<input class="grooni-metabox-groovy-value" type="hidden" name="' . esc_attr( $this->getName() ) . '" value="' . esc_attr( $this->getValueFromCustomMeta( $post_id ) ) . '">';
			$html .= esc_html__( 'Please, install and activate Groovy Menu plugin', 'crane' );

			return $html;
		}

	}

	protected function getCurrent() {
		$preset = null;

		if ( class_exists( 'GroovyMenuPreset' ) ) {
			$id = $this->getValue();
			if ( empty( $id ) || $id === 'default' ) {
				global $crane_options;
				global $post_type;

				switch ( $post_type ) {
					case 'page':
						$gm_preset_id = empty( $crane_options['regular-page-menu'] )
							?
							'default'
							:
							$crane_options['regular-page-menu'];
						break;

					case 'post':
						$gm_preset_id = empty( $crane_options['blog-single-menu'] )
							?
							'default'
							:
							$crane_options['blog-single-menu'];
						break;

					case 'crane_portfolio':
						$gm_preset_id = empty( $crane_options['portfolio-single-menu'] )
							?
							'default'
							:
							$crane_options['portfolio-single-menu'];
						break;

					case 'product':
						$gm_preset_id = empty( $crane_options['shop-single-menu'] )
							?
							'default'
							:
							$crane_options['shop-single-menu'];
						break;

					default:
						$gm_preset_id = GroovyMenuPreset::getDefaultPreset();
						break;
				}

				$preset = GroovyMenuPreset::getById( $gm_preset_id );
			} else {
				$preset = GroovyMenuPreset::getById( intval( $id ) );
			}

		}

		return $preset;
	}

	protected function getValueFromCustomMeta( $post_id = 0 ) {
		if ( ! $post_id ) {
			$post_id = get_the_ID();
		}
		$post = get_post( $post_id );

		return get_post_meta( $post_id, 'gm_custom_preset_id', true );
	}

	protected function renderPreset( $preset, $current = false, $selected = null, $field_id = 'preview-modal' ) {
		$html = '';

		if ( class_exists( 'GroovyMenuPreset' ) && function_exists( 'GroovyMenuPreviewModal' ) ) {

			if ( ! $preset ) {
				$preset = GroovyMenuPreset::getById( GroovyMenuPreset::getDefaultPreset() );
			}

			if ( ! $preset ) {
				return '<p>' . __( 'Please, create a preset.', 'crane' ) . '</p>';
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

			$html = <<<HTML
<div class="preset preset--groovy {$class}" data-name="{$preset_name}" data-id="{$preset_id}">
	<div class="preset-inner">
		<a class="preset-placeholder" href="#">
			<img src="{$screenshot_escaped}" alt="preset {$preset_name}" />
		</a>
		<div class="preset-info">
			<div class="preset-title">
				<span class="preset-title__alpha">{$preset_name}</span>
			</div>
			{$preview}
		</div>
	</div>
</div>
HTML;
		}

		return $html;
	}


	protected function render_preview_modal( $wrapper_id = 'groovy_menu-preview-modal' ) {
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


}
