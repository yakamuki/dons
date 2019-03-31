<?php 

class VC_Price_table
{
	function __construct()
	{
		add_action( 'vc_before_init', array($this, 'vc_advanced_price_table' ));
		add_action( 'wp_enqueue_scripts', array($this, 'adding_front_scripts' ));
		add_action( 'init', array( $this, 'check_if_vc_is_install' ) );
		add_action( 'init', array( $this, 'check_if_vc_is_install' ) );
		remove_filter( 'the_content', 'wpautop' );
	}

		
	function vc_advanced_price_table() {
		include 'render/price.php';
	}

	function adding_front_scripts() {
		wp_enqueue_style( 'font-awesome', plugin_dir_url( __FILE__ ).'/css/css/font-awesome.min.css' );
	}


	function check_if_vc_is_install(){
        if ( ! defined( 'WPB_VC_VERSION' ) ) {
            // Display notice that Visual Compser is required
            add_action('admin_notices', array( $this, 'showVcVersionNotice' ));
            return;
        }			
	}
	function showVcVersionNotice() {
	    ?>
	    <div class="notice notice-warning is-dismissible">
	        <p>Please install <a href="https://codecanyon.net/item/visual-composer-page-builder-for-wordpress/242431?ref=nasir179125">Visual Composer</a> to use Price Table.</p>
	    </div>
	    <?php
	}

}


$price_object = new VC_Price_table;
 ?>