<?php

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

/**
 * Run uninstall logic to remove options, and leftover settings.
 */
class WOO_MSTORE_SINGLE_UNINSTALLER {

	public function __construct() {
		// pass.
	}
}

new WOO_MSTORE_SINGLE_UNINSTALLER();
