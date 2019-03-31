<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

class WPBakeryShortCode_na_price_listing extends WPBakeryShortCode {

	protected function content( $atts, $content = null ) {

		extract( shortcode_atts( array(
			'style' => 'price_style_1',
			'offer_visibility' => 'none',
			'offer_text' => '',
			'offer_bg' => '',
			'price_title' => '',
			'subtitle' => '',
			'price_visibility' => 'block',
			'price_currency' => '',
			'price_amount' => '',
			'price_plan' => '',
			'btn_text' => '',
			'btn_url' => '',
			'price_bg' => '',
			'top_bg' => '',
		), $atts ) );	
		wp_enqueue_style( 'price-listing-css', plugins_url( '../css/price_listing.css' , __FILE__ ));
		$content = wpb_js_remove_wpautop($content, true);
		ob_start(); ?>


		<?php switch ($style) {
			case 'design1':
				include 'includes/price1.php';
				break;
			case 'mega-price-table-2':
				include 'includes/price2.php';
				break;
			case 'mega-price-table-3':
				include 'includes/price2.php';
				break;
			case 'mega-price-table-4':
				include 'includes/price4.php';
				break;
			case 'mega-price-table-5':
				include 'includes/price5.php';
				break;
			case 'design6':
				include 'includes/price6.php';
				break;
			case 'design7':
				include 'includes/price7.php';
				break;
			case 'design8':
				include 'includes/price8.php';
				break;
			
			default:
				include 'includes/price1.php';
				break;
		} ?>

		<?php
		return ob_get_clean();
	}
}


vc_map( array(
	"name" 			=> __( 'Price Table', 'infobox' ),
	"base" 			=> "na_price_listing",
	"category" 		=> __('toptrends'),
	"description" 	=> __('Create nice looking price tables', 'infobox'),
	"icon" => plugin_dir_url( __FILE__ ).'../icons/price.png',
	'params' => array(
		array(
            "type" 			=> 	"dropdown",
			"heading" 		=> 	__( 'Select Layout', 'pt-vc' ),
			"param_name" 	=> 	"style",
			"group" 		=> 	'Price Header',
			"value" 		=>  array(
				'Design 1'		=>  'design1',
				'Design 2'		=>  'mega-price-table-2',
				'Design 3'		=>  'mega-price-table-3',
				'Design 4'		=>  'mega-price-table-4',
				'Design 5'		=>  'mega-price-table-5',
				'Design 6'		=>  'design6',
				'Design 7'		=>  'design7',
				'Design 8'		=>  'design8',
			)
        ),
        array(
            "type" 			=> 	"textfield",
			"heading" 		=> 	__( 'Price Title', 'pt-vc' ),
			"param_name" 	=> 	"price_title",
			"description" 	=> 	__( 'It will be used as price package name', 'pt-vc' ),
			"group" 		=> 	'Price Header',
        ),
        array(
            "type" 			=> 	"textfield",
			"heading" 		=> 	__( 'Price Subtitle', 'pt-vc' ),
			"param_name" 	=> 	"subtitle",
			"description" 	=> 	__( 'It will show with title', 'pt-vc' ),
			"dependency" => array('element' => "style", 'value' => array('design7', 'design8')),
			"group" 		=> 	'Price Header',
        ),
        array(
            "type" 			=> 	"dropdown",
			"heading" 		=> 	__( 'Show/Hide price amount', 'pt-vc' ),
			"param_name" 	=> 	"price_visibility",
			"description" 	=> 	__( 'Select Show or Hide amount and currency ', 'pt-vc' ),
			"group" 		=> 	'Price Header',
			"value" 		=>  array(
				'Show' =>  'block',
				'Hide' =>  'none',
			)
        ),
        array(
            "type" 			=> 	"textfield",
			"heading" 		=> 	__( 'Currency sign', 'pt-vc' ),
			"param_name" 	=> 	"price_currency",
			"description" 	=> 	__( 'Write currency sign e.g "$"', 'pt-vc' ),
			"dependency" => array('element' => "price_visibility", 'value' => 'block'),
			"group" 		=> 	'Price Header',
        ),
        array(
            "type" 			=> 	"textfield",
			"heading" 		=> 	__( 'Amount', 'pt-vc' ),
			"param_name" 	=> 	"price_amount",
			"description" 	=> 	__( 'Write amount in number e.g 299', 'pt-vc' ),
			"dependency" => array('element' => "price_visibility", 'value' => 'block'),
			"group" 		=> 	'Price Header',
        ),
        array(
            "type" 			=> 	"textfield",
			"heading" 		=> 	__( 'Price plan', 'pt-vc' ),
			"param_name" 	=> 	"price_plan",
			"description" 	=> 	__( 'Price plan e.g "per month"', 'pt-vc' ),
			"dependency" => array('element' => "price_visibility", 'value' => 'block'),
			"group" 		=> 	'Price Header',
        ),

		array(
            "type" 			=> 	"dropdown",
			"heading" 		=> 	__( 'Show/Hide Offer', 'pt-vc' ),
			"param_name" 	=> 	"offer_visibility",
			"description" 	=> 	__( 'Select Show or Hide offer ', 'pt-vc' ),
			"dependency" => array('element' => "style", 'value' => 'design1'),
			"group" 		=> 	'Offer',
			"value" 		=>  array(
				'Hide' =>  'none',
				'Show' =>  'block',
			)
        ),
		array(
            "type" 			=> 	"textfield",
			"heading" 		=> 	__( 'Offer text', 'pt-vc' ),
			"param_name" 	=> 	"offer_text",
			"description" 	=> 	__( 'Write text for showing best offer in Ribbon e.g BEST', 'pt-vc' ),
			"dependency" => array('element' => "offer_visibility", 'value' => 'block'),
			"group" 		=> 	'Offer',
        ),
        array(
            "type" 			=> 	"colorpicker",
			"heading" 		=> 	__( 'Offer Background color', 'pt-vc' ),
			"param_name" 	=> 	"offer_bg",
			"description" 	=> 	__( 'background color of offer', 'pt-vc' ),
			"dependency" => array('element' => "offer_visibility", 'value' => 'block'),
			"group" 		=> 	'Offer',
        ),
        /* Features */

        array(
			"type" 			=> "textarea_html",
			"heading" 		=> __( 'Caption Text', 'ich-vc' ),
			"param_name" 	=> "content",
			"description" 	=> __( 'Enter your pricing content. You can use a UL list as shown by default but anything would really work!', 'ich-vc' ),
			"group" 		=> 'Features',
			"value"			=> '<li>30GB Storage</li><li>512MB Ram</li><li>10 databases</li><li>1,000 Emails</li><li>25GB Bandwidth</li>'
		),

        /* Button */

        array(
            "type" 			=> 	"textfield",
			"heading" 		=> 	__( 'Button Text', 'pt-vc' ),
			"param_name" 	=> 	"btn_text",
			"description" 	=> 	__( 'Button text name', 'pt-vc' ),
			"group" 		=> 	'Button',
        ),

        array(
            "type" 			=> 	"textfield",
			"heading" 		=> 	__( 'Button Url', 'pt-vc' ),
			"param_name" 	=> 	"btn_url",
			"description" 	=> 	__( 'Write Button URL for link', 'pt-vc' ),
			"group" 		=> 	'Button',
        ),

        /* colors */

        array(
            "type" 			=> 	"colorpicker",
			"heading" 		=> 	__( 'Header Background color', 'pt-vc' ),
			"param_name" 	=> 	"top_bg",
			"description" 	=> 	__( 'Top Header and button background color', 'pt-vc' ),
			"group" 		=> 	'Color',
        ),

        array(
            "type" 			=> 	"colorpicker",
			"heading" 		=> 	__( 'Content background color', 'pt-vc' ),
			"param_name" 	=> 	"price_bg",
			"description" 	=> 	__( 'Set complete background color', 'pt-vc' ),
			"group" 		=> 	'Color',
        ),
	),
) );

