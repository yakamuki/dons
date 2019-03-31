<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Class CT_Vc_Templates_Panel_Editor
 */
Class CT_Vc_Templates_Panel_Editor extends Vc_Templates_Panel_Editor {

	public function removeTemplates() {
		$this->default_templates = false;
	}

	public function getBackendDefaultTemplate($return = false ) {
		parent::getBackendDefaultTemplate($return);
	}
}
