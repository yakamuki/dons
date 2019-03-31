<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Theme Migrate file.
 *
 * @package crane
 */
class Crane_migrate__v1_0_5 extends Crane_Migration {

	function migrate() {

		$this->db_version = '1.0.5';

		$_redux_otions     = maybe_unserialize( get_option( 'crane_options' ) );

		if ( class_exists( 'Redux' ) && is_array( $_redux_otions ) ) {

			$image_resolution = isset( $_redux_otions['portfolio-archive-image_resolution' ] ) ? $_redux_otions['portfolio-archive-image_resolution' ] : '';
			Redux::setOption( 'crane_options', 'portfolio-archive-image_resolution', $this->get_resolution_by_number( $image_resolution) );


			$image_resolution = isset( $_redux_otions['blog-image_resolution'] ) ? $_redux_otions['blog-image_resolution'] : 'crane-featured';
			Redux::setOption( 'crane_options', 'blog-image_resolution', 'crane-featured' );

		}


		$this->success();

		return true;
	}


	function get_resolution_by_number( $basic_resolution ) {
		if ( is_numeric( $basic_resolution ) ) {
			$res = intval( $basic_resolution );
			if ( $res <= 300 ) {
				$basic_resolution = 'crane-portfolio-300';
			} elseif ( $res > 300 && $res <= 600 ) {
				$basic_resolution = 'crane-portfolio-600';
			} elseif ( $res > 600 && $res <= 900 ) {
				$basic_resolution = 'crane-portfolio-900';
			} else {
				$basic_resolution = 'crane-portfolio-900';
			}

			return $basic_resolution;
		}

		return 'crane-portfolio-300';

	}

}
