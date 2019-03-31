<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Theme Migrate file.
 *
 * @package crane
 */
class Crane_migrate__v1_3_8_1569 extends Crane_Migration {

	function migrate() {

		$this->db_version = '1.3.8.1569';

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

			$this->add_migrate_debug_log( 'Get posts post_type=' . $post_type . ' with meta_key=grooni_meta' );

			$posts = get_posts( array_merge( [ 'post_type' => $post_type ], $args ) );

			if ( ! $posts ) {
				continue;
			}

			$this->add_migrate_debug_log( 'Work with ' . count( $posts ) . ' posts' );

			foreach ( $posts as $post ) {
				$post_id = (int) $post->ID;

				$meta = get_post_custom( $post_id );
				if ( isset( $meta['grooni_meta'] ) && is_array( $meta['grooni_meta'] ) ) {
					$meta = json_decode( array_shift( $meta['grooni_meta'] ), true );
				}

				if ( empty( $meta ) ) {
					continue;
				}

				$title = isset( $meta['title'] ) ? trim( $meta['title'] ) : '';

				if ( '' === $title ) {
					continue;
				}

				if ( '1' === $title ) {
					$title = '';
				}

				if ( $title !== $meta['title'] ) {
					$meta['title'] = $title;

					$values = array();
					foreach ( $meta as $field => $val ) {
						$values[ $field ] = addslashes( $val );
					}

					// save to meta
					update_metadata( 'post', $post_id, 'grooni_meta', json_encode( $values, JSON_UNESCAPED_UNICODE ) );

				}

			}

			unset( $posts );

		}


		$this->success();

		return true;
	}

}
