<?php defined( 'ABSPATH' ) || die( 'This script cannot be accessed directly.' );

/**
 * @return string
 */
function GroovyMenuRenderIconsModal() {
	$icons               = '';
	$lang                = [];
	$lang['Select-icon'] = esc_html__( 'Select icon', 'groovy-menu' );
	$lang['Close']       = esc_html__( 'Close', 'groovy-menu' );

	foreach ( GroovyMenuFieldIcons::getFonts() as $fontName => $font ) {
		$icons .= '
<div class="groovy-iconset" data-name="' . $fontName . '">
	<span class="groovy-iconset-name">' . $font['name'] . '</span>
	<div class="groovy-icons">
';

		foreach ( $font['icons'] as $icon ) {
			$icons .= '<span class="groovy-icon ' . $fontName . '-' . $icon['name'] . '" data-class="' . $fontName . '-' . $icon['name'] . '"></span>';
		}
		$icons .= '</div></div>';
	}

	$out  = '';
	$out .= <<<HTML
	<div class="modal fade modal-centered" id="gm-icon-settings-modal" tabindex="-1">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title">{$lang['Select-icon']}</h4>
				</div>
				<div class="modal-body">
					{$icons}
				</div>
				<div class="modal-footer">
					<div class="btn-group">
						<button type="button" class="btn modal-btn" data-dismiss="modal">{$lang['Close']}</button>
					</div>
				</div>
			</div>
		</div>
	</div>
HTML;


	return $out;

}
