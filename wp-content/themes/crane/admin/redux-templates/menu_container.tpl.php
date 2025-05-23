<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );
/**
 * The template for the menu container of the panel.
 *
 * @author   Redux Framework
 * @author   Grooni
 * @package  Crane
 * @version: 3.5.4
 */

?>
<div class="redux-sidebar">
	<ul class="redux-group-menu">
		<?php
		foreach ( $this->parent->sections as $k => $section ) {
			$title = isset ( $section['title'] ) ? $section['title'] : '';

			$skip_sec = false;
			foreach ( $this->parent->hidden_perm_sections as $num => $section_title ) {
				if ( $section_title === $title ) {
					$skip_sec = true;
				}
			}

			if ( isset ( $section['customizer_only'] ) && $section['customizer_only'] == true ) {
				continue;
			}

			if ( false === $skip_sec ) {
				echo crane_clear_echo( $this->parent->section_menu( $k, $section ) );
				$skip_sec = false;
			}
		}

		/**
		 * action 'redux-page-after-sections-menu-{opt_name}'
		 *
		 * @param object $this ReduxFramework
		 */
		do_action( "redux-page-after-sections-menu-{$this->parent->args[ 'opt_name' ]}", $this );

		/**
		 * action 'redux/page/{opt_name}/menu/after'
		 *
		 * @param object $this ReduxFramework
		 */
		do_action( "redux/page/{$this->parent->args[ 'opt_name' ]}/menu/after", $this );
		?>
	</ul>
</div>
