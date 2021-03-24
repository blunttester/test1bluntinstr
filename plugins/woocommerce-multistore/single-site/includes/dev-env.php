<?php

/**
 * Disable certain security features on development machines.
 *
 * @class   WOO_MSTORE_SINGLE_DEV_ENV
 * @since   3.0.6
 */
class WOO_MSTORE_SINGLE_DEV_ENV {
	public function __construct() {
		add_action('init', array($this, 'init'), 10, 0);
	}

	public function init() {
		// disable curl SSL validation.
		add_filter('http_request_args', array($this, 'disable_ssl_validation'), 10, 2);
		add_filter('http_request_reject_unsafe_urls', array($this, 'reject_unsafe_url'), 10, 2);
	}

	public function disable_ssl_validation( $params, $url ) {
		$params['sslverify'] = false;
        return $params;
	}

	public function reject_unsafe_url( $flag, $url ) {
		return false;
	}
}

$GLOBALS['WOO_MSTORE_SINGLE_DEV_ENV'] = new WOO_MSTORE_SINGLE_DEV_ENV();