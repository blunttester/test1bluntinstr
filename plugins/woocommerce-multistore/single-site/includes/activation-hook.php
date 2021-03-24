<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WOO_MSTORE_SINGLE_ACTIVATION_HOOK {

	/**
	 * Default plugin options
	 **/
	public $default_options = array();

	/**
	 * Init
	 **/
	public function __construct() {
		$manager               = new WOO_MSTORE_OPTIONS_MANAGER();
		$this->default_options = $manager->get_defaults();
		$this->run();
	}

	/**
	 * Init
	 **/
	public function run() {
		$options = get_option( 'woonet_options', array() );

		foreach ( $this->default_options as $key => $value ) {
			if ( ! isset( $options[ $key ] ) ) {
				$options[ $key ] = $value;
			}
		}

		update_option( 'woonet_options', $options );
	}
}

new WOO_MSTORE_SINGLE_ACTIVATION_HOOK();
