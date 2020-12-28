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
require_once('class.meta.php');
require_once('class.util.php');

/**
 * Handles the registering and rendering of the additional product attribute tabs.
 */
class Tabs {

    /**
     * Use Singleton trait to disallow multiple instances of this class.
     * You may also fetch the instance of this class to remove registered filter and action hooks.
     */
    use Singleton;

    /**
     * Constructs a new instance of this class and registers the required actions and filters.
     */
    protected function __construct() {
        add_filter('the_content', array($this, 'extend_product_description'), 99, 1);
        add_filter('woocommerce_product_tabs', array($this, 'add_product_tabs'), 5);
        //add_filter('woocommerce_product_attribute_tab_content_term', array($this, 'enable_shortcodes'), 10, 1);
        add_filter('woocommerce_product_term_tab_content', array($this, 'enable_shortcodes'), 10, 1);
    }

    /**
     * Allow the use of shortcodes within the given content.
     *
     * @param  string $content The content the process shortcodes on.
     * @return string The process content with shortcodes enabled.
     */
    public function enable_shortcodes($content) {
        $content = str_replace(']]>', ']]&gt;', $content);

        // Using `the_content` filters but breaks the code in certain setups
        // $content = apply_filters('the_content', $content);

        // Using the `do_shortcode` works for most shortcodes but not `embed`
        $content = do_shortcode($content);

        $content = wpautop($content);

        return $content;
    }

    /**
     * Extend the given content of a product with all appropriate attribute descriptions.
     * The function will do nothing if the content is not from a product, or if no attribute descriptions are available.
     *
     * @param  string $content The content to modify.
     * @return string The extended content.
     */
    public function extend_product_description($content) {
        if (function_exists('is_product') && is_product()) {
            $tab_descriptions = $this->get_attribute_tab_descriptions('append');
            if ($tab_descriptions) {
                $content .= implode('', $tab_descriptions);
            }
            $cat_descriptions = $this->get_category_tab_descriptions('append');
            if ($cat_descriptions) {
                $content .= implode('', $cat_descriptions);
            }
        }
        return $content;
    }

    /**
     * Get a list of all attribute descriptions filtered by the given display type.
     * Returns an empty array if no according attribute descriptions are found.
     *
     * @param  string $display_type The display type to filter by. Default is `tab`.
     * @return array All attribute descriptions for the given display type.
     */
    public function get_category_tab_descriptions($display_type='tab') {
        $contents = array();
        $product = Util::get_product();
        $terms = wp_get_post_terms($product->get_id(), 'product_cat');
        foreach ($terms as $term) {
            $description = $this->get_term_tab_description($term);
            if ($description) {
                $contents[] = $description;
            }
        }
        return $contents;
    }

    /**
     * Get a list of all attribute descriptions filtered by the given display type.
     * Returns an empty array if no according attribute descriptions are found.
     *
     * @param  string $display_type The display type to filter by. Default is `tab`.
     * @return array All attribute descriptions for the given display type.
     */
    public function get_attribute_tab_descriptions($display_type='tab') {
        $contents = array();
        foreach ($this->get_attributes($display_type) as $attribute) {
            $term_contents = $this->get_attribute_term_tab_descriptions($attribute);
            if ($term_contents) {
                $contents = array_merge($term_contents, $contents);
            }
        }
        return $contents;
    }

    /**
     * Get a list of product attributes filtered by the given display type.
     * Returns an empty array if no according attributes are found.
     *
     * @param  string $display_type The display type to filter by. Default is `tab`.
     * @return array All attributes that match the given display type.
     */
    public function get_attributes($display_type='tab') {
        $product = Util::get_product();
        return $product ? array_filter($product->get_attributes(), function($attribute) use ($display_type) {
            if ($attribute['is_taxonomy']) {
                $attribute_meta = Meta::instance()->get_attribute_meta($attribute['id']);
                $attribute_display_type = $attribute_meta['attribute_display_type'];
                return !$attribute_display_type && $display_type == 'tab' || $attribute_display_type == $display_type;
            }
            return false;
        }) : [];
    }

    /**
     * Get a list of all attribute descriptions of the given attribute.
     * Returns an empty array if no according attribute descriptions are found.
     * Every attribute description is enclosed in a separate paragraph tag (<p>) by default.
     *
     * @param  array $attribute The attribute to get descriptions for for.
     * @return array All attribute descriptions of the given attribute.
     */
    public function get_attribute_term_tab_descriptions($attribute) {
        $product = Util::get_product();
        $contents = array();
        if ($product) {
            $terms = wp_get_post_terms($product->get_id(), $attribute['name']);
            $attribute_meta = Meta::instance()->get_attribute_meta($attribute['id']);
            $display_type = $attribute_meta['attribute_display_type'];
            foreach ($terms as $term) {
                $tab_content = null;
                if (isset($attribute_meta['attribute_description_source']) && $attribute_meta['attribute_description_source'] == 'term') {
                    $tab_content = term_description($term->term_id, $attribute['name']);
                }
                $content = $this->get_term_tab_description($term, $tab_content);
                if ($content) {
                    $contents[] = $content;
                }
            }
        }
        return $contents;
    }

    protected function get_term_tab_description($term, $tab_content = null) {
        $tab_content = $tab_content ?: get_term_meta($term->term_id, Meta::instance()->get_attribute_meta_key(), true);
        if ($tab_content) {
            $content = $this->replace_content_placeholders($tab_content, $term, Util::get_product());

            /**
             * Filter for the term tab description after formatting.
             * Use this filter to customize term tab description output.
             *
             * @param string $content The formatted content.
             * @param stdClass $term The current attribute term object.
             */
            return apply_filters('woocommerce_product_term_tab_content', $content, $term) ?: '';
        }
        return $tab_content ?: '';
    }

    /**
     * Replaces all placeholders in the given content with the appropriate values,
     * based on the given term and product instances.
     *
     * @param  string $content The content.
     * @param  \WC_Term $term The term.
     * @param  \WC_Product $product The product.
     * @return array The content with placeholders replaced or stripped.
     */
    private function replace_content_placeholders($content, $term, $product) {
        return preg_replace_callback_array(array(
            '/{product.([^:}]*?)(:(:)?(.*))?}/i' => function($match) use ($product) {
                $method = 'get_' . $match[1];
                if ($method == 'get_title') {
                    $method = 'get_name'; // backwards compatibility
                }
                $value = '';
                if (method_exists($product, $method)) {
                    $value = \call_user_func(array($product, $method));
                }
                if (isset($match[4]) && !empty($match[4])) {
                    $after = isset($match[3]) && !empty($match[3]);
                    $part = trim(strstr($value, $match[4], !$after));
                    if ($part) {
                        $value = $after ? substr($part, 1) : $part;
                    }
                }
                return $value;
            },
            '/{attribute.([^:}]*?):?(\d)?}/i' => function($match) use ($product) {
                $terms = wp_get_post_terms($product->get_id(), 'pa_' . $match[1]);
                if (isset($match[2]) && !empty($match[2]) && isset($terms[$match[2]])) {
                    return $terms[$match[2]]->name;
                }
                return join(', ', array_map(function($term) {
                    return $term->name;
                }, $terms));
            },
            '/{meta.([^:}]*?)(:(:)?(.*))?}/i' => function($match) use ($product) {
                $value = get_post_meta($product->get_id(), $match[1], true) ?: '';
                if (isset($match[4]) && !empty($match[4])) {
                    $after = isset($match[3]) && !empty($match[3]);
                    $part = strstr($value, $match[4], !$after);
                    if ($part) {
                        $value = $after ? substr($part, 1) : $part;
                    }
                    $value = trim($value);
                }
                return $value;
            }
        ), $content);
    }

    /**
     * Adds the additional product tab if any of the product attributes has an additional product tab description.
     * The default name of the product tab is the name of the product attribute taxonomy.
     * Every attribute description is enclosed in a separate paragraph tag (<p>) by default.
     *
     * @param  array $tabs The current tabs.
     * @return The modified tabs as applicable.
     */
    public function add_product_tabs($tabs) {
        $product = Util::get_product();
        $contents = array();

        if ($product) {
            foreach ($this->get_attributes('tab') as $attribute) {
                $term_contents = $this->get_attribute_term_tab_descriptions($attribute);
                if ($term_contents) {
                    $attribute_meta = Meta::instance()->get_attribute_meta($attribute['id']);
                    $title = wc_attribute_label($attribute['name'], $product);
                    if (isset($attribute_meta['attribute_tab_title']) && !empty($attribute_meta['attribute_tab_title'])) {
                        $title = $attribute_meta['attribute_tab_title'];
                    }

                    /**
                     * Filter for modifying the attribute tab title.
                     * Use this filter to dynamically set an tab tilter for specific attributes.
                     * The default tab title is the attribute tab title configured in the attribute settings or,
                     * if not present, the standard attribute title.
                     *
                     * @param string The attribute tab title.
                     * @param obj The associated product.
                     * @param obj array The associated attribute.
                     */
                    $title = apply_filters('woocommerce_product_attribute_tab_title', $title, $product, $attribute);

                    /**
                     * Filter for modifying the default tab priority offset.
                     * Use this filter to set either a global static offset or a dynamic offset for specific attributes.
                     * The default tab offset is `20`. This is not random, but chosen because of the default WooCommerce tab priorities.
                     *
                     *  - Description: 10
                     *  - Additional Information: 20
                     *  - Reviews: 30
                     *
                     * In order to display the product attribute tabs between the Additional Information and Reviews tab, 20 is a good base.
                     * The final attribute tab priority is calculated using the sum of the base and the attribute term order number of the product.
                     *
                     * If you have configured an absolute attribute tab priority in the attribute settings, the offset is irrelevant.
                     * if not present, the standard attribute title.
                     *
                     * @param string The attribute tab offset. Default is `20`.
                     * @param obj The associated product.
                     * @param obj array The associated attribute.
                     * @see   woocommerce_default_product_tabs() in the WC template functions for more information on the default tabs.
                     */
                    $offset = apply_filters('woocommerce_product_attribute_tab_priority_offset', 20, $product, $attribute);
                    $priority = $offset + $attribute['position'];
                    if (isset($attribute_meta['attribute_tab_priority']) && !empty($attribute_meta['attribute_tab_priority'])) {
                        $priority = $attribute_meta['attribute_tab_priority'];
                    }

                    /**
                     * Filter for modifying the default calculated or absolute tab priority.
                     * Use this filter to set either a global static tab priority or a dynamic one for specific attributes.
                     * The default attribute tab priority is the sum of the priority offset and the attribute term order number of the product,
                     * or, if available the absolute attribute tab priority configured in the attribute settings.
                     *
                     * @param string The attribute tab offset. Default is `20` + term position.
                     * @param obj The associated product.
                     * @param obj array The associated attribute.
                     * @see   woocommerce_default_product_tabs() in the WC template functions for more information on WooCommerce tabs.
                     */
                    $priority = apply_filters('woocommerce_product_attribute_tab_priority', $priority, $product, $attribute);

                    $tabs[$attribute['name']] = array(
                        'title'    => $title,
                        'priority' => $priority,
                        'callback' => array($this, 'render_tab'),
                        'content'  => implode('', $term_contents ?: []),
                    );
                }
            }
            // Check if main description tab exists and add if necessary
            if (!isset($tabs['description']) && !empty($this->get_attributes('append'))) {
                $tabs['description'] = array(
				    'title'    => __('Description', 'woocommerce'),
				    'priority' => 10,
				    'callback' => 'woocommerce_product_description_tab'
		        );
            }
        }
        return $tabs;
    }

    /**
     * Renders the tab content using the given parameters.
     *
     * @param  string $key The unique tab key.
     * @param  array $tab The tab information.
     */
    public function render_tab($key, $tab) {

        $content = $tab['content'];
        $tab_heading = '<h4>' . $tab['title'] . '</h4>';

        /**
         * Filter for modifying the attribute tab title heading.
         * The default value is the tab title wrapped in `<h4>` HTML tag.
         * Use this filter if you wish to customize this behavior.
         *
         * @param string $tab_heading The tab heading.
         * @param array $tab The tab configuration.
         */
        echo apply_filters('woocommerce_product_attribute_tab_heading', $tab_heading, $tab);

        /**
         * Filter for modifying the attribute tab content.
         * The default value is the tab content, sanitized and with expanded shortcodes.
         * Use this filter if you wish to customize this behavior.
         *
         * @param string $content The tab content.
         * @param array $tab The tab configuration.
         */
        echo apply_filters('woocommerce_product_term_tab_content', $content, $tab);
    }
}

?>