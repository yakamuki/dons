<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );
/**
 * Add VisualComposer timeline shortcode Class.
 *
 * @package Grooni_Theme_Addons
 */


if ( ! class_exists( 'CT_Vc_Timeline_Widget' ) ) {

	include_once '_widget.php';

	class CT_Vc_Timeline_Widget extends CT_Vc_Widgets {

		public function init_fields() {
			$file = __DIR__ . '/config/timeline.php';
			if ( file_exists( $file ) ) {
				require_once $file;

				$config = new CT_Vc_Timeline_Config();

				$this->tag         = $config::get_data( 'tag' );
				$this->name        = $config::get_data( 'name' );
				$this->description = $config::get_data( 'description' );

				$this->fields          = $config::fields();
				$this->as_parent       = $config::as_parent();
				$this->is_container    = $config::is_container();
				$this->content_element = $config::content_element();
				$this->js_view         = $config::js_view();
				$this->icon            = $config::icon();
			}

		}

		public function render( $atts, $content = null ) {

			$content = do_shortcode( $content );
			$output  = <<<HTML
<div class="timeline crane-timeline">
    <div class="timeline__scale">
        {$content}
        <div class="timeline__line"></div>
    </div>
</div>
HTML;

			return $output;
		}

	}


	new CT_Vc_Timeline_Widget();

	class WPBakeryShortCode_ct_vc_timeline extends WPBakeryShortCodesContainer {
	}

}
