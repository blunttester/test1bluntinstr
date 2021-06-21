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

class Observer
{
    public function __construct(
        \OmegaCommerce\Model\Config $config
    )
    {
        add_action("woocommerce_api_create_product", array(&$this, "update_product"), 10, 4);
        add_action("woocommerce_api_edit_product", array(&$this, "update_product"), 10, 4);
        add_action("woocommerce_api_delete_product", array(&$this, "delete_product"), 10, 4);

	    if (strpos($_SERVER['REQUEST_URI'], "/wp-json") !== false) {
		    add_action("save_post", array(&$this, "update_post"), 10, 4); //possible save via rest api of editor
	    }
        if (!is_admin()) { //plugins may save products when add to the cart
            return;
        }
        add_action("save_post_product", array(&$this, "update_product"), 10, 4);
        add_action("save_post", array(&$this, "update_post"), 10, 4);
//        add_action("publish_page", array(&$this, "update_page"), 10, 4);

        add_action("wp_trash_post", array(&$this, "delete_post"), 10, 4);
        add_action("before_delete_post", array(&$this, "delete_post"), 10, 4);

//        add_action("created_category", array(&$this, "update_category"), 10, 4);
//        add_action("edited_category", array(&$this, "update_category"), 10, 4);
//        add_action("delete_category", array(&$this, "update_category"), 10, 4);

        add_action("created_product_cat", array(&$this, "update_category"), 10, 4);
        add_action("edited_product_cat", array(&$this, "update_category"), 10, 4);
        add_action("delete_product_cat", array(&$this, "delete_category"), 10, 4);
    }

	public function update_product( $id, $data ) {
		if ($data->post_status != 'publish') {
			return;
		}
		$this->saveChanges($id, 'product', 'updated');
	}

	public function update_page( $id, $data ) { //not working yet
		$this->saveChanges($id, 'page', 'updated');
	}

	public function update_post( $id, $data ) {
    	if ($data->post_type == 'product') {
    		return;
	    }
    	if ($data->post_status != 'publish') {
    		return;
	    }
    	if ($data->post_type == 'page') {
		    $this->saveChanges( $id, 'page', 'updated' );
	    } else {
		    $this->saveChanges( $id, 'post', 'updated' );
	    }
	}

	public function update_category( $id, $data ) {
		$this->saveChanges($id, 'category', 'updated');
	}

	public function delete_product( $id, $data ) {
		$this->saveChanges($id, 'product', 'deleted');
	}

	public function delete_post( $id ) {
        if (function_exists("wc_get_product")) {
            $product = wc_get_product($id);
            if ($product) {
                $this->saveChanges($id, 'product', 'deleted');
                return;
            }
        }
		$this->saveChanges($id, 'post', 'deleted');
	}

	public function delete_category( $id, $data ) {
		$this->saveChanges($id, 'category', 'deleted');
	}


	public function saveChanges($entityId, $entityType, $status) {
    	global $wpdb;
		$table = $wpdb->prefix . DatabaseMigration::OMEGA_CHANGES_TABLE;
		$now = time();

		$sql = "INSERT INTO $table (entity_id, entity_type, status, created_at, updated_at)
VALUES ($entityId, '$entityType', '$status', $now, $now)
ON DUPLICATE KEY UPDATE
   updated_at = $now, status='$status';";

		$wpdb->get_results($sql);
		if ($wpdb->last_error != "" ) {
			throw new \Exception($wpdb->last_error);
		}
	}
}