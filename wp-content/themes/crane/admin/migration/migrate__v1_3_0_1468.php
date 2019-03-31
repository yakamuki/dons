<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Theme Migrate file.
 *
 * @package crane
 */
class Crane_migrate__v1_3_0_1468 extends Crane_Migration {

	function migrate() {

		$this->db_version = '1.3.0.1468';

		$_redux_otions     = maybe_unserialize( get_option( 'crane_options' ) );

		if ( class_exists( 'Redux' ) && is_array( $_redux_otions ) ) {

			$privacy_embeds = isset( $_redux_otions['privacy-embeds' ] ) ? $_redux_otions['privacy-embeds' ] : false;

			Redux::setOption( 'crane_options', 'privacy-preferences', $privacy_embeds );

		}


		$this->success();

		return true;
	}

}
