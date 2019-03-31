<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );
/**
 * Media field for Posts Meta.
 *
 * @package crane
 */


class Crane_Meta_Data_Field_Media extends Crane_Meta_Data_Field {

	public function getValue( $post_id = null, $args = array() ) {

		$size = is_admin() ? 'medium_large' : 'full';

		if ( isset( $args['size'] ) ) {
			$size = $args['size'];
		}
		$_images = explode( ',', parent::getValue( $post_id ) );
		$images  = array();

		if ( ! empty( $_images ) && is_array( $_images ) ) {
			foreach ( $_images as $image_id ) {
				$image_id = intval( $image_id );
				if ( ! empty( $image_id ) ) {
					$images[] = array(
						'id'   => $image_id,
						'path' => crane_get_thumb( $image_id, $size, true, true ),
					);
				}
			}
		}

		return $images;
	}

	public function renderField( $post_id ) {
		$images = $this->getValue( $post_id );
		$change_text = esc_html__( 'Change Image', 'crane' );
		$remove_text = esc_html__( 'Remove Image', 'crane' );
		$add_text = esc_html__( 'Add Image', 'crane' );

		$imagesSerialized = json_encode( $images );
		$html             = <<<HTML
<div class="grooni-meta-field-file-tpl grooni-meta-field-file">
	<div class="grooni-meta-media-preview">

	</div>
	<div>
		<input type="button" name="upload-btn" class="grooni-meta-upload-btn button-secondary" value="{$change_text}">
		<input type="button" name="remove-btn" class="grooni-meta-remove-btn button-secondary" value="{$remove_text}" />
	</div>
	<input type="hidden" class="grooni-meta-upload-input" name="{$this->getName()}[]" value="" data-url="" />
</div>
<div class="grooni-meta-field-file-container" data-images='{$imagesSerialized}'>
</div>
<a href="#" class="grooni-meta-field-file-add button-primary">{$add_text}</a>
HTML;

		return $html;
	}


	public function getValueNew() {
		$return_media = array();

		if ( isset( $_POST[ $this->getName() ] ) ) {
			$media_ids = wp_unslash( $_POST[ $this->getName() ] );
			if ( ! empty( $media_ids ) && is_array( $media_ids ) ) {
				foreach ( $media_ids as $id ) {
					if ( ! empty( $id ) && is_string( $id ) && intval( $id ) ) {
						$return_media[] = $id;
					}
				}
			} elseif ( ! empty ( $media_ids ) && is_string( $media_ids ) ) {
				if ( intval( $media_ids ) ) {
					$return_media[] = $media_ids;
				}
			}
		}

		return empty( $return_media ) ? null : implode( ',', $return_media );
	}


}
