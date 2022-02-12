<?php
/**
 * Omega Commerce
 *
 * Licence: MIT https://opensource.org/licenses/MIT
 * Copyright: 2017 Omega Commerce LLC https://omegacommerce.com
 */
/**
 * Omega Commerce
 *
 * Licence: MIT https://opensource.org/licenses/MIT
 * Copyright: 2017 Omega Commerce LLC https://omegacommerce.com
 */
namespace OmegaCommerce\Model;

class DatabaseMigration
{

    const DB_SCHEMA_VERSION = '1.2';
    const DB_DATA_VERSION = '1.2';
    const OMEGA_CHANGES_TABLE = 'omega_index_changes';

    public function __construct(
    )
    {
        add_action('plugins_loaded', array(&$this, 'updateDBCheck'));
    }


    public function updateDBCheck()
    {
        $version = get_option('omega_core_db_version');
        if (version_compare($version, self::DB_SCHEMA_VERSION) == -1) {
            $this->install($version);
        }

        $version = get_option('omega_core_db_data_version');
        if (version_compare($version, self::DB_DATA_VERSION) == -1) {
            $this->installData($version);
        }
    }

    public function install($oldVersion)
    {
        global $wpdb;

        $table_name = $wpdb->prefix . self::OMEGA_CHANGES_TABLE;

	    $sql = "CREATE TABLE `$table_name` (
 	id bigint(20) NOT NULL AUTO_INCREMENT,
  `entity_id` bigint(20) NOT NULL,
  `entity_type` varchar(25) NOT NULL,
  `status` varchar(25) NOT NULL,
  `created_at` int,
  `updated_at` int,
  PRIMARY KEY (`id`),
  KEY `{$table_name}-uniq` (entity_id, entity_type)
        );
        ";
	    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	    dbDelta($sql);


        update_option('omega_core_db_version', self::DB_SCHEMA_VERSION);
    }

    public function installData($oldVersion)
    {
        update_option('omega_core_db_data_version', self::DB_DATA_VERSION);
    }
}