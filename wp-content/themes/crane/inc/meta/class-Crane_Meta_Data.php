<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );
/**
 * Posts Meta init.
 *
 * @package crane
 */


if ( ! class_exists( 'Crane_Meta_Data' ) ) {

	include_once get_parent_theme_file_path( 'inc/meta/Fields/class-Crane_Meta_Data_Field.php' );
	include_once get_parent_theme_file_path( 'inc/meta/Fields/class-Crane_Meta_Data_Field_Text.php' );
	include_once get_parent_theme_file_path( 'inc/meta/Fields/class-Crane_Meta_Data_Field_Textarea.php' );
	include_once get_parent_theme_file_path( 'inc/meta/Fields/class-Crane_Meta_Data_Field_Checkbox.php' );
	include_once get_parent_theme_file_path( 'inc/meta/Fields/class-Crane_Meta_Data_Field_Radio.php' );
	include_once get_parent_theme_file_path( 'inc/meta/Fields/class-Crane_Meta_Data_Field_Select.php' );
	include_once get_parent_theme_file_path( 'inc/meta/Fields/class-Crane_Meta_Data_Field_Select_Sidebar.php' );
	include_once get_parent_theme_file_path( 'inc/meta/Fields/class-Crane_Meta_Data_Field_Media.php' );
	include_once get_parent_theme_file_path( 'inc/meta/Fields/class-Crane_Meta_Data_Field_Number.php' );
	include_once get_parent_theme_file_path( 'inc/meta/Fields/class-Crane_Meta_Data_Field_Groovy_Preset.php' );

	include_once get_parent_theme_file_path( 'inc/meta/class-Crane_Meta_Data_Settings.php' );
	include_once get_parent_theme_file_path( 'inc/meta/class-Crane_Meta_Data_Group.php' );


	class Crane_Meta_Data {
		/**
		 * @var Crane_Meta_Data_Field[][]
		 */
		public $fields;

		public function init() {

			if ( function_exists( 'grooni_add_meta_action' ) ) {
				grooni_add_meta_action();
			}
			add_action( 'save_post', array( $this, 'do_save_post' ), 10, 3 );
			add_action( 'edit_attachment', array( $this, 'do_save_post' ) );
			add_action( 'add_attachment', array( $this, 'do_save_post' ) );

			// Work with meta of revisions
			add_action( 'wp_restore_post_revision', array( $this, 'restore_post_revision_meta' ), 20, 2 );
			add_filter( '_wp_post_revision_fields', array( $this, 'display_revision_fields' ) );
			add_filter( '_wp_post_revision_field_grooni_meta', array( $this, 'display_revision_field' ), 10, 2 );

			$_groups = Crane_Meta_Data_Settings::getMeta();

			if ( is_array( $_groups ) ) {
				foreach ( $_groups as $group ) {
					$postTypes = $group->getPostType();

					foreach ( $group->getFields() as $field ) {
						if ( is_array( $postTypes ) ) {
							foreach ( $postTypes as $type ) {
								$this->fields[ $type ][ $field->getName() ] = $field;
							}
						} else {
							$this->fields[ $postTypes ][ $field->getName() ] = $field;
						}

					}
				}
			}
		}


		public function restore_post_revision_meta( $post_id, $revision_id ) {

			$post          = get_post( $post_id );
			$revision      = get_post( $revision_id );
			$revision_meta = get_metadata( 'post', $revision->ID, 'grooni_meta', true );

			if ( false !== $revision_meta ) {
				update_post_meta( $post_id, 'grooni_meta', $revision_meta );
			} else {
				delete_post_meta( $post_id, 'grooni_meta' );
			}

		}


		public function display_revision_fields( $fields ) {

			$fields['grooni_meta'] = __( 'Crane theme meta options', 'crane' );

			return $fields;

		}


		public function display_revision_field( $value, $field ) {

			if ( ! empty( $value ) ) {
				$data = json_decode( $value, true );

				if ( empty( $data ) ) {
					return '';
				}

				$array_to_table = '';

				// Cycle through the array
				foreach ( $data as $id => $val ) {
					$array_to_table .= $id . ' : ' . $val . "\n";
				}

				if ( ! empty( $array_to_table ) ) {
					$value = $array_to_table;
				}

			}

			return $value;

		}


		public function get( $field, $post_id, $postType = null, $args = array(), $check_preview = true ) {

			if ( $post_id === null ) {
				$post_id = get_the_ID();
			}
			if ( is_null( $postType ) ) {
				$postType = get_post_type();
			}

			$last_revision = false;
			if ( $check_preview && $this->is_preview() ) {
				$post_revisions = wp_get_post_revisions( $post_id, array( 'numberposts' => 1 ) );
				if ( ! empty( $post_revisions ) ) {
					foreach ( $post_revisions as $revision ) {
						$last_revision = $revision;
						break;
					}
					$post_id = isset( $last_revision->ID ) ? $last_revision->ID : $post_id;
				}
			}

			if ( isset( $this->fields[ $postType ][ $field ] ) ) {
				return $this->fields[ $postType ][ $field ]->getValue( $post_id, $args );
			}

		}


		public function get_default( $field, $postType = null ) {
			if ( is_null( $postType ) ) {
				$postType = get_post_type();
			}
			if ( isset( $this->fields[ $postType ][ $field ] ) ) {
				return $this->fields[ $postType ][ $field ]->getDefault();
			}

		}


		public function is_preview() {
			if ( is_admin() ) {
				return ( isset( $_POST['wp-preview'] ) && $_POST['wp-preview'] === 'dopreview' );
			}

			return ( isset( $_GET['preview'] ) && $_GET['preview'] === 'true' );
		}


		public static function show_post_metaboxes() {

			if ( function_exists( 'grooni_add_metabox_by_post_type' ) ) {

				foreach ( Crane_Meta_Data_Settings::getMeta() as $group ) {
					$postType = $group->getPostType();
					if ( is_array( $postType ) ) {
						foreach ( $postType as $type ) {
							grooni_add_metabox_by_post_type( $group, $type );
						}
					} else {
						grooni_add_metabox_by_post_type( $group, $postType );
					}
				}

			}

		}


		public function do_save_post( $post_id, $post = null, $update = false ) {

			// $post_id and $post are required
			if ( empty( $post_id ) ) {
				return $post_id;
			}

			// don't save for Migration process
			if ( ! empty( $_GET['crane-theme-migrate-job'] ) || ( defined( 'CRANE_DOING_MIGRATE_JOB' ) && CRANE_DOING_MIGRATE_JOB ) ) {
				return $post_id;
			}

			$current_post_type = isset( $_POST['post_type'] ) ? wp_unslash( trim( $_POST['post_type'] ) ) : null;

			// Dont' save meta for unsupported post types.
			if ( ! in_array( $current_post_type, array( 'page', 'post', 'crane_portfolio', 'product' ), true ) ) {
				return $post_id;
			}

			if ( defined( 'WP_LOAD_IMPORTERS' ) && WP_LOAD_IMPORTERS ) {
				return $post_id;
			}

			$post_revision = wp_is_post_revision( $post_id );
			$post_autosave = wp_is_post_autosave( $post );

			// Dont' save meta for autosaves
			if ( ! $post_revision && ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE && is_int( $post_autosave ) ) ) {
				return $post_id;
			}

			// don't save for "Quick Edit"
			if ( ! empty( $_POST['post_ID'] ) && isset( $_POST['action'] ) && 'inline-save' === $_POST['action'] ) {
				return $post_id;
			}

			// don't save for "Bulk Edit"
			if ( isset( $_REQUEST['action'] ) && 'edit' === $_REQUEST['action'] && isset( $_REQUEST['post_status'] ) && 'all' === $_REQUEST['post_status'] && isset( $_REQUEST['bulk_edit'] ) ) {
				return $post_id;
			}

			// check permissions
			if ( crane_is_theme_preview() ) {
				return $post_id;
			} elseif ( isset( $_POST['post_type'] ) && 'page' === $_POST['post_type'] ) {
				if ( ! current_user_can( 'edit_page', $post_id ) ) {
					return $post_id;
				}
			} elseif ( ! current_user_can( 'edit_post', $post_id ) ) {
				return $post_id;
			}

			$revision = false;
			if ( $post_id && $post_revision && isset( $post->ID ) ) {

				$revision = $post->ID;

			} else {

				if (
					! empty( $_POST['wp-preview'] ) && 'dopreview' === $_POST['wp-preview'] &&
					! empty( $_POST['post_status'] ) && 'draft' === $_POST['post_status']
				) {
					$post_revisions = wp_get_post_revisions( $post_id, array( 'numberposts' => 1 ) );

					if ( ! empty( $post_revisions ) ) {
						foreach ( $post_revisions as $revision_object ) {
							$last_revision = $revision_object;
							break;
						}
						$post_id  = isset( $last_revision->ID ) ? $last_revision->ID : $post_id;
						$revision = $post_id;
					}
				}
			}

			Crane_Meta_Data_Settings::saveMeta( $post_id, $revision, $update );

			return $post_id;

		}

	}
}
