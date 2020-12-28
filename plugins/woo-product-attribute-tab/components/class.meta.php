<?php

/**
 * Namespace declaration
 */
namespace MJJ\WooProductAttributeTab;

/**
 * Exit if accessed directly
 */
defined('ABSPATH') or die();

/**
 * Dependencies
 */
require_once('trait.singleton.php');

/**
 * This class defines, saves and prints additional product attribute meta information that are
 * needed by this plugin to function correctly.
 */
class Meta {

    /**
     * Use Singleton trait to disallow multiple instances of this class.
     * You may also fetch the instance of this class to remove registered filter and action hooks.
     */
    use Singleton;

    /**
     * The default meta key for product attribute tab descriptions.
     * @var string
     */
    private $attr_meta_key = 'product-tab-description';

    /**
     * Constructs a new instance of this class and registers the required actions and filters.
     */
    private function __construct() {
        add_action('init', array($this, 'init'));
    }

    /**
     * Initialization hook that registers actions for all available product attribute taxonomies.
     */
    public function init() {
        if (function_exists('wc_get_attribute_taxonomy_names')) {
            foreach (wc_get_attribute_taxonomy_names() as $pa) {
                add_action("{$pa}_add_form_fields", array($this, 'attribute_add_field'), 10);
                add_action("${pa}_edit_form_fields", array($this, 'attribute_edit_field'), 10);
                add_action("created_{$pa}", array($this, 'save_field'), 10, 1);
                add_action("edited_{$pa}", array($this, 'save_field'), 10, 1);
            }
        }
        add_action('woocommerce_after_add_attribute_fields', array($this, 'display_taxonomy_fields'), 10);
        add_action('woocommerce_after_edit_attribute_fields', array($this, 'display_taxonomy_fields'), 10);
        add_action('woocommerce_attribute_added', array($this, 'taxonomy_save_field'), 10, 2);
        add_action('woocommerce_attribute_updated', array($this, 'taxonomy_save_field'), 10, 2);

        add_action('product_cat_add_form_fields', array($this, 'category_add_new_meta_field'), 10, 1);
        add_action('product_cat_edit_form_fields', array($this, 'category_edit_meta_field'), 10, 1);
        add_action('edited_product_cat', array($this, 'category_save_field'), 10, 1);
        add_action('create_product_cat', array($this, 'category_save_field'), 10, 1);
    }

    /**
     * Display the taxonomy fields.
     */
    public function display_taxonomy_fields() {
        $meta = null;
        if (isset($_GET['edit'])) {
            $meta = $this->get_attribute_meta(absint($_GET['edit']));
        }
        woocommerce_admin_fields($this->get_taxonomy_fields($meta));
    }

    /**
     * Prints a text-area field with label for defining a product tab description of a new product attribute.
     *
     * @param string $taxonomy The current product attribute taxonomy.
     */
    public function attribute_add_field($taxonomy = null) {
        Util::load_template('pa-add-form-field.php', array('meta_key' => $this->get_attribute_meta_key()));
    }

    /**
     * Prints a text-area field with label for editing the product tab description of an existing product attribute.
     *
     * @param stdClass $term The current $term object.
     * @param string $taxonomy The current product attribute taxonomy.
     */
    public function attribute_edit_field($term, $taxonomy = null) {
        Util::load_template('pa-edit-form-field.php', array(
            'meta_key' => $this->get_attribute_meta_key(),
            'description' => $term ? get_term_meta($term->term_id, $this->get_attribute_meta_key(), true) : ''
        ));
    }

    /**
     * Saves the attribute meta in the request.
     *
     * @param int $taxonomy_id ID of the taxonomy that is being saved.
     * @param object $taxonomy The taxonomy object.
     */
    public function taxonomy_save_field($taxonomy_id, $taxonomy) {
        global $wpdb;
        foreach ($this->get_taxonomy_fields() as $key => $field) {
            if (isset($_POST[$key])) {
                $value = $_POST[$key];
                $taxonomy[$key] = $value;
                $this->update_database($key);
            }
        }
        $wpdb->update($wpdb->prefix . 'woocommerce_attribute_taxonomies', $taxonomy, array('attribute_id' => $taxonomy_id));
    }

    /**
     * Update the database for the given field.
     * Check if a column for the given field already exists in the database and if not, create it.
     * The new filed is created in the `woocommerce_attribute_taxonomies` table created by WooCommerce.
     *
     * @param  string $field The field to look for and create if inexistent.
     * @return bool `true` if the field exists or was created successfully, `false` otherwise.
     */
    public function update_database($field) {
        global $wpdb;
        $row = $wpdb->get_results("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS
                                   WHERE table_name = '{$wpdb->prefix}woocommerce_attribute_taxonomies' AND column_name = '{$field}'"  );

        if (empty($row)) {
           return $wpdb->query("ALTER TABLE {$wpdb->prefix}woocommerce_attribute_taxonomies ADD {$field} VARCHAR(50) DEFAULT NULL");
        }
        return true;
    }

    /**
     * Saves the product tab description in the request. The function uses the `update_term_meta` function,
     * and can therefore be used to save new values or update existing ones.
     * Empty values are allowed, in order to unset/delete the current product tab description.
     *
     * @param  int $term_id ID of the term that is being saved.
     */
    public function save_field($term_id) {
        if (isset($_POST[$this->get_attribute_meta_key()])) {
            $description = $_POST[$this->get_attribute_meta_key()];
            update_term_meta($term_id, $this->get_attribute_meta_key(), $description);
        }
    }

    /**
     * Gets the additional field definition for product attribute tab taxonomy settings.
     *
     * @return array The field definition
     */
    public function get_taxonomy_fields($defaults = null, $prefix = 'attribute') {
        $fields = array(
            $prefix . '_display_type' => array(
                'name'    => __('Display Type', 'woo-product-attribute-tab'),
                'type'    => 'select',
                'id'      => $prefix . '_display_type',
                'desc'    => __('Choose how you want your product attribute descriptions of this taxonomy to be displayed in the product detail section.', 'woo-product-attribute-tab'),
                'options' => array(
                    'tab'    => __('Display in separate tab (default)', 'woo-product-attribute-tab'),
                    'append' => __('Append to main description', 'woo-product-attribute-tab'),
                    'hide'   => __('Do not display', 'woo-product-attribute-tab'),
                ),
                'default' => 'tab'
            ),

            $prefix . '_description_source' => array(
                'name'    => __('Description source', 'woo-product-attribute-tab' ),
                'type'    => 'select',
                'id'      => $prefix . '_description_source',
                'desc'    => __('Choose the source of your attribute description. Either tab description field provided by this plugin, or the standard term description provided by WooCommerce.', 'woo-product-attribute-tab' ),
                'options' => array(
                    'tab'  => __('Use attribute tab description (default)', 'woo-product-attribute-tab'),
                    'term' => __('Use WooCommerce term description', 'woo-product-attribute-tab')
                ),
                'default' => 'tab'
            ),

            $prefix . '_tab_title' => array(
                'name'    => __('Alternative tab title', 'woo-product-attribute-tab'),
                'type'    => 'text',
                'id'      => $prefix . '_tab_title',
                'desc'    => __('Use this option to set an alternative tab title, rather than the default attribute name', 'woo-product-attribute-tab'),
                'default' => ''
            ),

            $prefix . '_tab_priority' => array(
                'name'    => __('Absolute tab priority', 'woo-product-attribute-tab'),
                'type'    => 'number',
                'id'      => $prefix . '_tab_priority',
                'desc'    => __('Use this option to set an absolute tab priority (position) and override the default behavior: <code>default offset + attribute position</code>. Note that the attribute position depends on order of the product attributes.', 'woo-product-attribute-tab'),
                'default' => ''
            ),
        );

        foreach ($fields as $key => &$field) {
            $field['desc'] = '<p class="description">' . $field['desc'] . '</p>';
            if ($defaults && isset($defaults[$key])) {
                $field['default'] = $defaults[$key];
            }
        }

        /**
         * Filter for adding new attribute taxonomy fields.
         * Please be careful with this filter. It is advisable to only add new field definitions.
         * Removing or modifying existing ones may cause data loss and unexpected behavior.
         *
         * @param array $field The attribute taxonomy fields to modify.
         */
        return apply_filters('woocommerce_product_attribute_tab_taxonomy_fields', $fields);
    }

    /**
     * Gets the meta key of the additional product attribute description.
     *
     * @return string The meta key
     */
    public function get_attribute_meta_key() {
        return $this->attr_meta_key;
    }

    /**
     * Gets all attribute meta data from the database for the attribute with the given ID.
     *
     * @param  int $attribute_id The attribute ID.
     * @return array|bool The attribute meta array of false if nothing was found.
     */
    public function get_attribute_meta($attribute_id) {
        global $wpdb;
        $result = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}woocommerce_attribute_taxonomies WHERE attribute_id = '{$attribute_id}'", ARRAY_A);

        /**
         * Filter for modifying attribute meta data after it has been fetched from the database.
         *
         * @param array $result The read meta data from the database, maybe `false` in error cases.
         * @param int $attribute_id The attribute taxonomy ID.
         */
        return apply_filters('woocommerce_product_attribute_tab_meta', $result, $attribute_id);
    }

    /**
     * Show the add new category meta field to add product descriptions.
     *
     * @param stdClass $term The current $term object.
     */
    public function category_add_new_meta_field() {
        Util::load_template('cat-add-form-field.php', array('meta_key' => $this->get_attribute_meta_key()));
    }

    /**
     * Show the edit category meta field to add product descriptions.
     *
     * @param stdClass $term The current $term object.
     */
    public function category_edit_meta_field($term) {
        Util::load_template('cat-edit-form-field.php', array(
            'meta_key' => $this->get_attribute_meta_key(),
            'description' => $term ? get_term_meta($term->term_id, $this->get_attribute_meta_key(), true) : ''
        ));
    }

    /**
     * Save the extra product category fields.
     *
     * @param  int $term_id ID of the term that is being saved.
     */
    public function category_save_field($term_id) {
        $meta = filter_input(INPUT_POST, $this->get_attribute_meta_key());
        update_term_meta($term_id, $this->get_attribute_meta_key(), $meta);
    }

}

?>