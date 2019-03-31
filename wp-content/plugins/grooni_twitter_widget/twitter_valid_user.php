<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'wp_ajax_userValidate', 'gr_tw_user_validation' );
function gr_tw_user_validation() {
	if ( sanitize_text_field( $_GET['action'] ) == 'userValidate' ) {
		global $wp_filesystem;
		if ( empty( $wp_filesystem ) ) {
			require_once ABSPATH . '/wp-admin/includes/file.php';
			WP_Filesystem();
		}

		header( 'Content-type: application/json' );
		$username = sanitize_text_field( $_GET['gr_tw_userName'] );

		$url = "https://twitter.com/intent/user?screen_name=" . $username;
		if ( ! $wp_filesystem->get_contents( $url ) ) {
			$data = array(
				'data'  => __( 'Invalid user name', 'grooni-tw' ),
				'class' => 'gr_tw-user_invalid',
				'tst'   => json_encode( $_GET )
			);
		} else {
			$data = array(
				'data'  => __( 'Valid user name', 'grooni-tw' ),
				'class' => 'gr_tw-user_valid',
				'tst'   => json_encode( $_GET )
			);
		}
		echo json_encode( $data );
	}
	exit();
}
