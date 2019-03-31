<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );
/**
 * Posts Meta Settings Class.
 *
 * @package crane
 */


class Crane_Meta_Data_Settings {
	protected static $meta;

	public static function addMeta( Crane_Meta_Data_Group $meta ) {
		self::$meta[] = $meta;
	}

	/**
	 * @param $name
	 * @param $title
	 * @param $fields Crane_Meta_Data_Field[]
	 */
	public static function addMetaGroup( $name, $title, $fields ) {
		$groups = [ ];
		foreach ( $fields as $field ) {
			foreach ( $field->getPostType() as $type ) {
				$groups[ $type ][] = $field;
			}
		}
		foreach ( $groups as $type => $fields ) {
			self::addMeta( new Crane_Meta_Data_Group( $name, $title, $type, $fields ) );
		}
	}

	/**
	 * @return Crane_Meta_Data_Group[]
	 */
	public static function getMeta() {
		return self::$meta;
	}

	/**
	 * Save mata data
	 *
	 * @param int $post_id
	 * @param bool|int $revision_post_id
	 */
	public static function saveMeta( $post_id, $revision_post_id = false, $update = false ) {

		$values             = array();
		$independent_values = array();
		$values_exist       = 0;

		foreach ( self::getMeta() as $group ) {
			foreach ( $group->getFields() as $field ) {

				$new_value = $field->getValueNew();

				if ( ! is_null( $new_value ) ) {
					if ( true === $field->isIndependentMeta() ) {
						$independent_values[ $field->getName() ] = addslashes( $new_value );
					}

					$values[ $field->getName() ] = addslashes( $new_value );
					$values_exist++;
				}

			}
		}

		if ( $values_exist ) {
			self::updatePostMetaData( $post_id, 'grooni_meta', json_encode( $values, JSON_UNESCAPED_UNICODE ), $revision_post_id );
		}

		if ( ! empty( $independent_values ) ) {
			foreach ( $independent_values as $meta_key => $meta_value ) {
				self::updatePostMetaData( $post_id, $meta_key, $meta_value, $revision_post_id );
			}
		}

	}


	/**
	 * @param int $post_id
	 * @param $meta_key
	 * @param $meta_value
	 * @param bool|int $revision_post_id
	 */
	public static function updatePostMetaData( $post_id, $meta_key, $meta_value, $revision_post_id ) {

		if ( empty( $revision_post_id ) ) {

			update_post_meta( $post_id, $meta_key, $meta_value );

		} else {

			/*
			 * Use the underlying update_metadata() function vs add_post_meta()
			 * to ensure metadata is added to the revision post and not its parent.
			 * First param: Type of object metadata is for (e.g., comment, post, or user)
			 */
			update_metadata( 'post', $revision_post_id, $meta_key, $meta_value );

		}

	}



}
