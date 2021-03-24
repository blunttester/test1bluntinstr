<?php

class WOO_Date_Update {
	public function update_run() {

		global $WOO_MSTORE;

		//set the start update option to let know others there's an update in progress
		add_site_option( 'mstore_update_wizard_started', 'true' );

		echo '<div class="wrap">';
		echo '<h1>Update</h1>';
		echo '<br/>';

		$options = $WOO_MSTORE->functions->get_options();

		$version = empty( $options['version'] ) ? '1' : $options['version'];

		if ( version_compare( $version, WOO_MSTORE_VERSION, '<' ) ) {
			if ( version_compare( $version, '1.5.1', '<' ) ) {
				include_once( WOO_MSTORE_PATH . '/include/updates/update-1.5.1.php' );

				//update the options, in case of timeout, to allow later for resume
				$options['version'] = '1.5';
				$WOO_MSTORE->functions->update_options( $options );
			}

			if ( version_compare( $version, '2.0.17', '<' ) ) {
				include_once( WOO_MSTORE_PATH . '/include/updates/update-2.0.17.php' );

				$options['version'] = '2.0.17';
				$WOO_MSTORE->functions->update_options( $options );
			}
		}

		delete_site_option( 'mstore_update_wizard_started' );

		//set the last version
		$options['version'] = WOO_MSTORE_VERSION;
		$WOO_MSTORE->functions->update_options( $options );

		echo '<p>' . __( 'Update successfully completed.', 'woonet' ) . '</p>';
		echo '</div>';
	}
}
