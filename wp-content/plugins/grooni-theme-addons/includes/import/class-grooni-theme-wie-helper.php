<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

class Grooni_Theme_Addons_WIE_Helper {

	protected $widgets_data = array();
	protected $import_sidebars = array();
	protected $wie_import_results = array();


	/**
	 * Constructor
	 */
	public function __construct( $options = array() ) {

		if ( isset( $options['widgets_data'] ) ) {
			$this->widgets_data = $options['widgets_data'];
		}

		if ( isset( $options['import_sidebars'] ) ) {
			$this->import_sidebars = $options['import_sidebars'];
		}

	}


	/**
	 * Import sidebar function.
	 *
	 * @param array $import_sidebars list of sidebars names. Import sidebars Only from this array
	 *
	 */
	public function wie_process_import_sidebars( $import_sidebars = array() ) {

		if ( empty( $import_sidebars ) && ! empty( $this->import_sidebars ) ) {
			$import_sidebars = $this->import_sidebars;
		}

		// Get file contents and decode
		$data         = ( is_string( $this->widgets_data ) ) ? json_decode( $this->widgets_data, true ) : $this->widgets_data;
		$current_data = get_option( 'sidebars_widgets' );

		if ( ! is_array( $current_data ) ) {
			$current_data = array();
		}
		$new_current_data = $current_data;

		if ( ! is_array( $data ) ) {
			wp_die( esc_html__( 'Import file format error.', 'grooni-theme-addons' ) );
		}

		$widgets = array();
		foreach ( $data as $key => $val ) {
			if ( in_array( $key, $import_sidebars ) && ( $key == 'crane_basic_sidebar' || ! in_array( $key, $current_data ) ) ) {
				$widgets[ $key ] = $val;
			}
		}

		if ( empty( $widgets ) ) {
			return;
		}

		foreach ( $widgets as $key => $val ) {
			if ( in_array( $key, $current_data ) || $key == 'crane_basic_sidebar' ) {
				unset( $new_current_data[ $key ] );
			}
		}

		if ( $new_current_data != $current_data ) {
			update_option( 'sidebars_widgets', $new_current_data );
		}

		$widgets = json_decode( json_encode( $widgets ) );

		// Import the widget data
		// Make results available for display on import/export page
		$this->wie_import_results = $this->wie_import_data( $widgets );

	}


	/**
	 * Import widget JSON data
	 *
	 * @global array $wp_registered_sidebars
	 *
	 * @param object $data JSON widget data from .wie file
	 *
	 * @return array Results array
	 */
	public function wie_import_data( $data ) {

		global $wp_registered_sidebars;

		// Have valid data?
		// If no data or could not decode
		if ( empty( $data ) || ! is_object( $data ) ) {
			wp_die( esc_html__( 'Import data could not be read. Please try a different file.', 'grooni-theme-addons' ) );
		}

		// Hook before import
		do_action( 'grooni_wie_before_import' );
		$data = apply_filters( 'grooni_theme_addons_wie_import_data', $data );

		// Get all available widgets site supports
		$available_widgets = $this->wie_available_widgets();

		// Get all existing widget instances
		$widget_instances = array();
		foreach ( $available_widgets as $widget_data ) {
			$widget_instances[ $widget_data['id_base'] ] = get_option( 'widget_' . $widget_data['id_base'] );
		}

		// Begin results
		$results = array();

		// Loop import data's sidebars
		foreach ( $data as $sidebar_id => $widgets ) {

			// Skip inactive widgets
			// (should not be in export file)
			if ( 'wp_inactive_widgets' == $sidebar_id ) {
				continue;
			}

			// Check if sidebar is available on this site
			// Otherwise add widgets to inactive, and say so
			if ( isset( $wp_registered_sidebars[ $sidebar_id ] ) ) {
				$sidebar_available    = true;
				$use_sidebar_id       = $sidebar_id;
				$sidebar_message_type = 'success';
				$sidebar_message      = '';
			} else {
				$sidebar_available    = false;
				$use_sidebar_id       = 'wp_inactive_widgets'; // add to inactive if sidebar does not exist in theme
				$sidebar_message_type = 'error';
				$sidebar_message      = esc_html__( 'Sidebar does not exist in theme (using Inactive)', 'grooni-theme-addons' );
			}

			// Result for sidebar
			$results[ $sidebar_id ]['name']         = ! empty( $wp_registered_sidebars[ $sidebar_id ]['name'] ) ? $wp_registered_sidebars[ $sidebar_id ]['name'] : $sidebar_id; // sidebar name if theme supports it; otherwise ID
			$results[ $sidebar_id ]['message_type'] = $sidebar_message_type;
			$results[ $sidebar_id ]['message']      = $sidebar_message;
			$results[ $sidebar_id ]['widgets']      = array();

			// Loop widgets
			foreach ( $widgets as $widget_instance_id => $widget ) {

				$fail                = false;
				$widget_message_type = $widget_message = 'n.a.';

				// Get id_base (remove -# from end) and instance ID number
				$id_base            = preg_replace( '/-[0-9]+$/', '', $widget_instance_id );
				$instance_id_number = str_replace( $id_base . '-', '', $widget_instance_id );

				// Does site support this widget?
				if ( ! $fail && ! isset( $available_widgets[ $id_base ] ) ) {
					$fail                = true;
					$widget_message_type = 'error';
					$widget_message      = esc_html__( 'Site does not support widget', 'grooni-theme-addons' ); // explain why widget not imported
				}

				// Filter to modify settings object before conversion to array and import
				// Leave this filter here for backwards compatibility with manipulating objects (before conversion to array below)
				// Ideally the newer wie_widget_settings_array below will be used instead of this
				$widget = apply_filters( 'grooni_wie_widget_settings', $widget ); // object

				// Convert multidimensional objects to multidimensional arrays
				// Some plugins like Jetpack Widget Visibility store settings as multidimensional arrays
				// Without this, they are imported as objects and cause fatal error on Widgets page
				// If this creates problems for plugins that do actually intend settings in objects then may need to consider other approach: https://wordpress.org/support/topic/problem-with-array-of-arrays
				// It is probably much more likely that arrays are used than objects, however
				$widget = json_decode( json_encode( $widget ), true );

				// Filter to modify settings array
				// This is preferred over the older wie_widget_settings filter above
				// Do before identical check because changes may make it identical to end result (such as URL replacements)
				$widget = apply_filters( 'grooni_wie_widget_settings_array', $widget );

				// Does widget with identical settings already exist in same sidebar?
				if ( ! $fail && isset( $widget_instances[ $id_base ] ) ) {

					// Get existing widgets in this sidebar
					$sidebars_widgets = get_option( 'sidebars_widgets' );
					$sidebar_widgets  = isset( $sidebars_widgets[ $use_sidebar_id ] ) ? $sidebars_widgets[ $use_sidebar_id ] : array(); // check Inactive if that's where will go

					// Loop widgets with ID base
					$single_widget_instances = ! empty( $widget_instances[ $id_base ] ) ? $widget_instances[ $id_base ] : array();
					foreach ( $single_widget_instances as $check_id => $check_widget ) {

						// Is widget in same sidebar and has identical settings?
						if ( in_array( "$id_base-$check_id", $sidebar_widgets ) && (array) $widget == $check_widget ) {

							$fail                = true;
							$widget_message_type = 'warning';
							$widget_message      = esc_html__( 'Widget already exists', 'grooni-theme-addons' ); // explain why widget not imported

							break;

						}

					}

				}

				// No failure
				if ( ! $fail ) {

					// Add widget instance
					$single_widget_instances   = get_option( 'widget_' . $id_base ); // all instances for that widget ID base, get fresh every time
					$single_widget_instances   = ! empty( $single_widget_instances ) ? $single_widget_instances : array( '_multiwidget' => 1 ); // start fresh if have to
					$single_widget_instances[] = $widget; // add it

					// Get the key it was given
					end( $single_widget_instances );
					$new_instance_id_number = key( $single_widget_instances );

					// If key is 0, make it 1
					// When 0, an issue can occur where adding a widget causes data from other widget to load, and the widget doesn't stick (reload wipes it)
					if ( '0' === strval( $new_instance_id_number ) ) {
						$new_instance_id_number                             = 1;
						$single_widget_instances[ $new_instance_id_number ] = $single_widget_instances[0];
						unset( $single_widget_instances[0] );
					}

					// Move _multiwidget to end of array for uniformity
					if ( isset( $single_widget_instances['_multiwidget'] ) ) {
						$multiwidget = $single_widget_instances['_multiwidget'];
						unset( $single_widget_instances['_multiwidget'] );
						$single_widget_instances['_multiwidget'] = $multiwidget;
					}

					// Update option with new widget
					update_option( 'widget_' . $id_base, $single_widget_instances );

					// Assign widget instance to sidebar
					$sidebars_widgets                      = get_option( 'sidebars_widgets' ); // which sidebars have which widgets, get fresh every time
					$new_instance_id                       = $id_base . '-' . $new_instance_id_number; // use ID number from new widget instance
					$sidebars_widgets[ $use_sidebar_id ][] = $new_instance_id; // add new instance to sidebar
					update_option( 'sidebars_widgets', $sidebars_widgets ); // save the amended data

					// After widget import action
					$after_widget_import = array(
						'sidebar'           => $use_sidebar_id,
						'sidebar_old'       => $sidebar_id,
						'widget'            => $widget,
						'widget_type'       => $id_base,
						'widget_id'         => $new_instance_id,
						'widget_id_old'     => $widget_instance_id,
						'widget_id_num'     => $new_instance_id_number,
						'widget_id_num_old' => $instance_id_number
					);
					do_action( 'grooni_wie_after_widget_import', $after_widget_import );

					// Success message
					if ( $sidebar_available ) {
						$widget_message_type = 'success';
						$widget_message      = esc_html__( 'Imported', 'grooni-theme-addons' );
					} else {
						$widget_message_type = 'warning';
						$widget_message      = esc_html__( 'Imported to Inactive', 'grooni-theme-addons' );
					}

				}

				// Result for widget instance
				$results[ $sidebar_id ]['widgets'][ $widget_instance_id ]['name']         = isset( $available_widgets[ $id_base ]['name'] ) ? $available_widgets[ $id_base ]['name'] : $id_base; // widget name or ID if name not available (not supported by site)
				$results[ $sidebar_id ]['widgets'][ $widget_instance_id ]['title']        = ! empty( $widget['title'] ) ? $widget['title'] : __( 'No Title', 'grooni-theme-addons' ); // show "No Title" if widget instance is untitled
				$results[ $sidebar_id ]['widgets'][ $widget_instance_id ]['message_type'] = $widget_message_type;
				$results[ $sidebar_id ]['widgets'][ $widget_instance_id ]['message']      = $widget_message;

			}

		}

		// Hook after import
		do_action( 'grooni_wie_after_import' );

		// Return results
		return apply_filters( 'grooni_wie_import_results', $results );

	}


	public function wie_available_widgets() {

		global $wp_registered_widget_controls;

		$widget_controls = $wp_registered_widget_controls;

		$available_widgets = array();

		foreach ( $widget_controls as $widget ) {

			if ( ! empty( $widget['id_base'] ) && ! isset( $available_widgets[ $widget['id_base'] ] ) ) { // no dupes

				$available_widgets[ $widget['id_base'] ]['id_base'] = $widget['id_base'];
				$available_widgets[ $widget['id_base'] ]['name']    = $widget['name'];

			}

		}

		return apply_filters( 'grooni_theme_addons_wie_available_widgets', $available_widgets );
	}


	public function get_result() {
		return $this->wie_import_results;
	}


}
