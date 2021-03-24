<?php
/**
 * WooMultistore sites manager
 */

class WOO_MSTORE_SINGLE_SITE_MANAGER {

	/**
	 * $sites_active
	 *
	 * @var array $sites_active
	 */
	private $sites_active = array();

	/**
	 * $sites_deactivated
	 *
	 * @var array $sites_deactivated
	 */
	private $sites_deactivated = array();

	public function __construct() {
		$this->sites_active      = get_option( 'woonet_child_sites', array() );
		$this->sites_deactivated = get_option( 'woonet_child_sites_deactivated', array() );
	}

	public function get_type() {
		return get_option( 'woonet_network_type', '' );
	}

	public function get_master_site() {
		return get_option( 'woonet_master_connect', array() );
	}

	public function get_sites( $status = 'active' ) {

		if ( $status = 'all' ) {
			return array_merge(
				$this->sites_active,
				$this->sites_deactivated
			);
		}

		if ( $status == 'active' ) {
			return $this->sites_active;
		}

		if ( $status == 'inactive' ) {
			return $this->sites_deactivated;
		}

		return array();
	}

	public function get_uuid_by_key( $key ) {

		if ( $this->get_type() == 'child' ) {
			$master = $this->get_master_site();

			if ( $master['key'] == $key ) {
				return $master['uuid'];
			}
		} elseif ( $this->get_type() == 'master' ) {
			$sites = $this->get_sites();

			foreach ( $sites as $k => $site ) {
				if ( $site['site_key'] == $key ) {
					return $site['uuid'];
				}
			}
		}

		return null;
	}

	public function update_sites( $sites, $status = 'active' ) {}
	public function delete_sites( $sites, $status = 'active' ) {}
	public function get_site( $id, $status = 'active' ) {}
	public function delete_site( $id, $status = 'active' ) {}
	public function update_site( $id, $status = 'active' ) {}
}
