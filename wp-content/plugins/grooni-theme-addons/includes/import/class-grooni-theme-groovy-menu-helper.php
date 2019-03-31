<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );


class Grooni_Theme_Addons_Groovy_Menu_Helper {

	/**
	 * Constructor
	 */
	public function __construct() {

	}


	/**
	 * Import fonts from attachment file
	 *
	 * @return mixed
	 */
	public function groovy_menu_import_fonts( $fonts_for_import = array() ) {
		if ( class_exists( 'ZipArchive' ) ) {

			global $wp_filesystem;
			if ( empty( $wp_filesystem ) ) {
				if ( file_exists( ABSPATH . '/wp-admin/includes/file.php' ) ) {
					require_once ABSPATH . '/wp-admin/includes/file.php';
					WP_Filesystem();
				}
			}
			if ( empty( $wp_filesystem ) ) {
				return false;
			}

			foreach ( $fonts_for_import as $import_font_name => $import_font_attachment ) {

				$filename = get_attached_file( $import_font_attachment['id'] );
				$zip      = new ZipArchive();
				if ( $zip->open( $filename ) ) {
					$fonts = GroovyMenuFieldIcons::getFonts();

					$selection      = $zip->getFromName( 'selection.json' );
					$selection_data = json_decode( $selection, true );
					$name           = $import_font_attachment['old_name'];

					$font_files = [
						'woff' => $zip->getFromName( 'fonts/' . $selection_data['metadata']['name'] . '.woff' ),
						'ttf'  => $zip->getFromName( 'fonts/' . $selection_data['metadata']['name'] . '.ttf' ),
						'svg'  => $zip->getFromName( 'fonts/' . $selection_data['metadata']['name'] . '.svg' ),
						'eot'  => $zip->getFromName( 'fonts/' . $selection_data['metadata']['name'] . '.eot' ),
						'css'  => ( new GroovyMenuSettings )->generateFontsCss( $name, $selection_data ),
					];

					$dir = GroovyMenuUtils::getFontsDir();

					foreach ( $font_files as $font_key => $font_data ) {
						$font_path = $dir . $name . '.' . $font_key;
						if ( ! $wp_filesystem->put_contents( $font_path, $font_data, 0644 ) ) {
							return sprintf( esc_html__( 'Failed to create file: %s', 'grooni-theme-addons' ), $font_path );
						}
					}

					$icons = array();
					foreach ( $selection_data['icons'] as $icon ) {
						$icons[] = array(
							'name' => $icon['icon']['tags'][0],
							'code' => $icon['properties']['code']
						);
					}
					$fonts[ $name ] = array( 'icons' => $icons, 'name' => $selection_data['metadata']['name'] );
					GroovyMenuFieldIcons::setFonts( $fonts );
				}

			}

		} else {
			return esc_html__( 'Wasn&apos;t able to work with Zip Archive. Missing php-zip extension.', 'grooni-theme-addons' );
		}

		return true;
	}


	/**
	 * Import groovy menu global settings
	 *
	 * @param string $path_2_file json data
	 *
	 * @return bool
	 */
	public function groovy_menu_import_settings( $json = '' ) {

		if ( empty( $json ) ) {
			return false;
		}


		if ( class_exists( 'GroovyMenuPreset' ) && class_exists( 'GroovyMenuSettings' ) ) {

			GroovyMenuPreset::install();

			$global_settings = json_decode( $json, true );

			if ( ! empty( $global_settings ) ) {
				$GroovyMenuSettings = new GroovyMenuSettings();
				$GroovyMenuSettings->settings()->updateGlobal( $global_settings );
			}

			return true;
		}

		return false;
	}


	/**
	 * Import Groovy menu preset
	 *
	 * @return bool
	 */
	public function groovy_menu_import_one_preset( $preset_data ) {
		if ( class_exists( 'GroovyMenuPreset' ) ) {

			GroovyMenuPreset::install();

			foreach ( GroovyMenuPreset::getAll() as $presetRow ) {
				if ( $preset_data['id'] == $presetRow->id ) {
					return false;
				}
			}

			global $wp_filesystem;
			if ( empty( $wp_filesystem ) ) {
				if ( file_exists( ABSPATH . '/wp-admin/includes/file.php' ) ) {
					require_once ABSPATH . '/wp-admin/includes/file.php';
					WP_Filesystem();
				}
			}
			if ( empty( $wp_filesystem ) ) {
				return false;
			}

			$presetId = GroovyMenuPreset::create( $preset_data['name'], false, $preset_data['id'] );
			if ( isset( $preset_data['image'] ) && $preset_data['image'] ) {
				GroovyMenuPreset::setPreviewById( $presetId, $preset_data['image'] );
			}

			$style = new GroovyMenuStyle( $presetId );
			foreach ( $preset_data['settings'] as $field => $value ) {
				if (
					is_array( $value ) &&
					( isset( $value['type'] ) && $value['type'] == 'media' ) &&
					( isset( $value['data'] ) && $value['data'] )
				) {
					$upload_dir = wp_upload_dir();
					$filename   = $upload_dir['path'] . '/' . $field . '_' . $presetId . '.png';
					$tmpFile    = $wp_filesystem->put_contents( $filename, base64_decode( $value['data'] ), 0644 );

					if ( ! $tmpFile ) {
						echo sprintf( esc_html__( 'Failed to create file: %s', 'grooni-theme-addons' ), $filename );
					}

					$attachment = array(
						'guid'           => $upload_dir['url'] . '/' . basename( $filename ),
						'post_mime_type' => $value['post_mime_type'],
						'post_title'     => basename( $filename ),
						'post_content'   => '',
						'post_status'    => 'inherit'
					);

					$value = wp_insert_attachment( $attachment, $filename );
					require_once ABSPATH . 'wp-admin/includes/image.php';

					$attachData = wp_generate_attachment_metadata( $value, $tmpFile );
					wp_update_attachment_metadata( $value, $attachData );

				}
				$style->set( $field, $value );
			}
			$style->update();


			return true;
		}

		return false;
	}


}

