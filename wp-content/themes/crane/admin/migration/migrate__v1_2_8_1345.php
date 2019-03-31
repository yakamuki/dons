<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Theme Migrate file.
 *
 * @package crane
 */
class Crane_migrate__v1_2_8_1345 extends Crane_Migration {

	function migrate() {

		$this->db_version = '1.2.8.1345';

		$_redux_otions = maybe_unserialize( get_option( 'crane_options' ) );

		$options = array(
			'search-padding',
			'shop-single-padding',
			'shop-archive-padding',
			'blog-single-padding',
			'blog-archive-padding',
			'portfolio-single-padding',
			'portfolio-archive-padding',
			'regular-page-padding',
		);

		$padding_desktop_default = '{"padding-top":"80px","padding-bottom":"80px"}';


		if ( class_exists( 'Redux' ) && is_array( $_redux_otions ) ) {

			foreach ( $options as $opt ) {

				$padding_desktop = isset( $_redux_otions[ $opt ] ) ? $_redux_otions[ $opt ] : null ;

				if (!empty( $padding_desktop) && $padding_desktop_default !== json_encode( $padding_desktop)){
					Redux::setOption( 'crane_options', $opt . '-mobile', $padding_desktop );
				}

			}

		}


		$this->success();

		return true;

	}



}
