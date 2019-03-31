<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );
/**
 * Add VisualComposer timeline-item shortcode Class.
 *
 * @package Grooni_Theme_Addons
 */


if ( ! class_exists( 'CT_Vc_Timeline_item_Widget' ) ) {

	include_once '_widget.php';

	class CT_Vc_Timeline_item_Widget extends CT_Vc_Widgets {

		public function init_fields() {
			$file = __DIR__ . '/config/timeline_item.php';
			if ( file_exists( $file ) ) {
				require_once $file;

				$config = new CT_Vc_Timeline_item_Config();

				$this->tag         = $config::get_data( 'tag' );
				$this->name        = $config::get_data( 'name' );
				$this->description = $config::get_data( 'description' );

				$this->fields   = $config::fields();
				$this->as_child = $config::as_child();
			}
		}

		public function render( $atts, $content = null ) {
			$title = $text = '';
			extract( $this->fill_empty_atts( $atts ) );

			$output = <<<HTML
<div class="timeline__scale__item">
    <div class="timeline__scale__item__year">
        <div class="timeline__scale__item__year-inner">
            {$title}
            <span class="timeline__scale__item__year__circle"><span></span></span>
        </div>
    </div>
    <span class="timeline__scale__item__txt invisible">{$text}</span>
</div>
HTML;

			return $output;
		}

	}


	new CT_Vc_Timeline_item_Widget();

	class WPBakeryShortCode_ct_vc_timeline_item extends WPBakeryShortCode {
	}

}
