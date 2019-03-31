<?php
/*
Plugin Name: Grooni Twitter Feeds widget
Description: Displays latest tweets from any Twitter account.
Plugin URI: http://grooni.com
Version: 1.0.8
Author: Grooni
Author URI: https://grooni.com
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Without directly access
}

if ( class_exists( 'WP_Widget' ) && ! class_exists( 'gr_twitter_widget' ) ) {

	include dirname( __FILE__ ) . '/twitter_valid_user.php';

	require dirname( __FILE__ ) . '/include/vendor/twitteroauth/autoload.php';
	// use Abraham\TwitterOAuth\TwitterOAuth;

	require_once dirname( __FILE__ ) . '/include/gr_twitter_widget.class.php';

	if ( class_exists( 'Abraham\TwitterOAuth\TwitterOAuth' ) && class_exists( 'grooni_twitter_widget' ) ) {
		add_action( 'widgets_init', function () {
			register_widget( 'grooni_twitter_widget' );
		} );
	}

}

require plugin_dir_path( __FILE__ ) . 'include/vendor/update_checker/plugin-update-checker.php';
if ( class_exists( 'Puc_v4_Factory' ) ) {
	$update_checker = Puc_v4_Factory::buildUpdateChecker(
		'http://updates.grooni.com/?action=get_metadata&slug=grooni_twitter_widget',
		__FILE__,
		'grooni_twitter_widget'
	);
}
