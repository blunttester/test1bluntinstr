<?php
/**
 * API to check plugin version.
 */

class WOO_MSTORE_SINGLE_VERSION {

	/**
	 * Initialize the action hooks and load the plugin classes
	 **/
	public function __construct() {
		add_action( 'wp_ajax_nopriv_woomulti_version', array( $this, 'hook_woomulti_version_check' ), 10, 0 );

		if ( get_option( 'woonet_network_type' ) == 'master' ) {
			add_action( 'admin_init', array( $this, 'check_versions' ), 10, 0 );
		}

		if ( get_transient( 'woonet_show_update_notice' ) ) {
			add_action( 'admin_notices', array( $this, 'show_update_notice' ), 10, 0 );
		}
	}

	public function hook_woomulti_version_check() {

		$_engine = new WOO_MSTORE_SINGLE_NETWORK_SYNC_ENGINE();

		if ( ! $_engine->is_request_authenticated( $_POST ) ) {
			wp_send_json(
				array(
					'error'   => 'failed',
					'message' => 'Authentication required.',
				)
			);
		}

		wp_send_json(
			array(
				'status'  => 'success',
				'result'  => defined( 'WOO_MSTORE_VERSION' ) ? WOO_MSTORE_VERSION : '',
				'message' => 'Version retrieved succesfully.',
			)
		);
	}

	/**
	 * Check all child sites and notify the user to
	 * update if running an older version.
	 */
	public function check_versions() {

		// only run on the master site.
		if ( get_option( 'woonet_network_type' ) != 'master' ) {
			return;
		}

		if ( get_transient( 'woonet_version_check' ) ) {
			return;
		}

		// Do not check more than once every 72 hours.
		set_transient( 'woonet_version_check', time(), 72 * 60 * 60 );
		$_set_update_notice = false;

		$engine         = new WOO_MSTORE_SINGLE_NETWORK_SYNC_ENGINE();
		$versions       = $engine->get_versions();
		$update_notices = array();

		foreach ( $versions as $version ) {
			if ( isset( $version['status'] ) && $version['status'] == 'failed' ) {
				$_set_update_notice = true;
			} elseif ( ! empty( $version['result'] ) && defined( 'WOO_MSTORE_VERSION' ) && version_compare( WOO_MSTORE_VERSION, $version['result'], '!=' ) ) {
				$_set_update_notice = true;
			}
		}

		if ( $_set_update_notice === true ) {
			set_transient( 'woonet_show_update_notice', true, 12 * 60 * 60 );
		} else {
			delete_transient( 'woonet_show_update_notice' );
		}
	}

	/**
	 * Show update notice
	 */
	public function show_update_notice() {
		?>
		<div class="notice notice-warning is-dismissible">
			<p><?php _e( 'Some of your child sites may be running older versions of <a target="_blank" href="https://woomultistore.com/"> WooMultistore</a>. You may update from WordPress or download the plugin from the <a target="_blank" href="https://woomultistore.com/my-account/downloads/"> download section </a> of our website. If you have updated recently, you can ignore the warning.' ); ?></p>
		</div>
		<?php
	}
}

$GLOBALS['WOO_MSTORE_SINGLE_VERSION'] = new WOO_MSTORE_SINGLE_VERSION();
