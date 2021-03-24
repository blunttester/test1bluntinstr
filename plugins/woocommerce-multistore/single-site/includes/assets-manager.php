<?php
/**
 * WooMultistore single site init
 */

class WOO_MSTORE_SINGLE_ASSETS_MANAGER {

	/**
	 * Initialize the action hooks and load the plugin classes
	 **/
	public function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	/**
	 * All assets used by the plugin should be enqueued here.
	 **/
	public function enqueue_assets() {
		if ( is_admin() ) {
			wp_register_style( 'woomulti-single-css', plugins_url( '/assets/single/main.css', dirname( dirname( __FILE__ ) ) ), array(), WOO_MSTORE_VERSION );
			wp_enqueue_style( 'woomulti-single-css' );

			wp_register_script( 'woomulti-single-js', plugins_url( '/assets/single/main.js', dirname( dirname( __FILE__ ) ) ), array(), WOO_MSTORE_VERSION );
			wp_enqueue_script( 'woomulti-single-js' );
		}

		/**
		 * Load tippyjs only on options page
		 */
		// if ( is_admin()
		// && ! empty( $_GET['page'] )
		// && $_GET['page'] == 'woonet-woocommerce-settings' ) {
		// wp_register_script( 'woomulti-popper-js', plugins_url( '/assets/single/lib/popper.min.js', dirname( dirname( __FILE__ ) ) ), array(), WOO_MSTORE_VERSION );
		// wp_register_script( 'woomulti-tippy-js', plugins_url( '/assets/single/lib/tippy-bundle.umd.min.js', dirname( dirname( __FILE__ ) ) ), array(), WOO_MSTORE_VERSION );

		// wp_enqueue_script( 'woomulti-popper-js' );
		// wp_enqueue_script( 'woomulti-tippy-js' );
		// }
	}
}
