<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Theme Migrate file.
 *
 * @package crane
 */
class Crane_migrate__v1_0_1 extends Crane_Migration {

	function migrate() {

		$this->db_version = '1.0.1';


		$breadcrumbs_redux = 'both_within'; // by default
		$breadcrumbs_types = array(
			'breadcrumbs-regular',
			'breadcrumbs-portfolio',
			'breadcrumbs-blog',
			'breadcrumbs-shop'
		);

		$_redux_otions     = maybe_unserialize( get_option( 'crane_options' ) );
		$bc_position_redux = empty( $_redux_otions['breadcrumbs-position'] ) ? '' : $_redux_otions['breadcrumbs-position'];

		// cleanup user inputs
		if ( ! empty( $bc_position_redux ) ) {
			switch ( $bc_position_redux ) {
				case 'before':
					$bc_position_redux = 'before';
					break;
				case 'within':
					$bc_position_redux = 'within';
					break;
				case 'after':
					$bc_position_redux = 'after';
					break;
			}
		}


		if ( class_exists( 'Redux' ) && is_array( $_redux_otions ) ) {

			if ( ! empty( $bc_position_redux ) ) {

				foreach ( $breadcrumbs_types as $bc_type ) {

					$breadcrumbs_redux = isset( $_redux_otions[ $bc_type ] ) ? $_redux_otions[ $bc_type ] : '';

					if ( 'both' === $breadcrumbs_redux ) {
						switch ( $bc_position_redux ) {
							case 'before':
								$breadcrumbs_redux = 'both_before';
								break;
							case 'within':
								$breadcrumbs_redux = 'both_within';
								break;
							case 'after':
								$breadcrumbs_redux = 'both_after';
								break;
						}

						Redux::setOption( 'crane_options', $bc_type, $breadcrumbs_redux );

					}

				}

			}

		}


		// For meta data of content
		foreach ( [ 'page', 'post', 'crane_portfolio', 'product' ] as $post_type ) {
			$args = array(
				'numberposts'      => - 1,
				'category'         => 0,
				'orderby'          => 'ID',
				'order'            => 'ASC',
				'include'          => array(),
				'exclude'          => array(),
				'meta_key'         => 'grooni_meta',
				'meta_value'       => '',
				'suppress_filters' => true,
			);

			$posts = get_posts( array_merge( [ 'post_type' => $post_type ], $args ) );

			if ( ! $posts ) {
				continue;
			}

			foreach ( $posts as $post ) {
				$post_id = (int) $post->ID;

				$meta = get_post_custom( $post_id );
				if ( isset( $meta['grooni_meta'] ) && is_array( $meta['grooni_meta'] ) ) {
					$meta = json_decode( array_shift( $meta['grooni_meta'] ), true );
				}

				if ( empty( $meta ) ) {
					continue;
				}

				$breadcrumbs = $breadcrumbs_position = '';
				if ( isset( $meta['breadcrumbs'] ) ) {
					$breadcrumbs = $meta['breadcrumbs'];
				}
				if ( isset( $meta['breadcrumbs_position'] ) ) {
					$breadcrumbs_position = $meta['breadcrumbs_position'];
					unset( $meta['breadcrumbs_position'] );
				}

				if ( empty( $breadcrumbs ) ) {
					$breadcrumbs = 'default';
				}


				switch ( $breadcrumbs_position ) {
					case 'default':
						if ( 'both' === $breadcrumbs ) {
							$breadcrumbs = 'both_' . $bc_position_redux;
						}
						break;

					case 'before':
						$breadcrumbs = 'both_before';
						break;

					case 'within':
						$breadcrumbs = 'both_within';
						break;

					case 'after':
						$breadcrumbs = 'both_after';
						break;
				}

				// write new param
				$meta['breadcrumbs'] = $breadcrumbs;

				$values = array();
				foreach ( $meta as $field => $val ) {
					$values[ $field ] = addslashes( $val );
				}

				// save to meta
				update_metadata( $post_type, $post_id, 'grooni_meta', json_encode( $values, JSON_UNESCAPED_UNICODE ) );

			}

			unset( $posts );

		}

		$this->success();

		return true;
	}

}
