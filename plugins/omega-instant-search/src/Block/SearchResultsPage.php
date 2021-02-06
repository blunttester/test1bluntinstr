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
namespace OmegaCommerce\Block;


class SearchResultsPage
{
    /**
     * Construct the plugin object
     */
    public function __construct()
    {
        add_filter('parse_request', array(&$this, 'parse_request'));

        add_shortcode('omega_search_results', array($this, 'insertHtml'));
        if (strpos($_SERVER['REQUEST_URI'], "omega-search") !== false) {
            add_filter('body_class', array(&$this, 'addBodyClass'));
        }
    }

    /**
     * We need this to handle URLs like: /omega-search/?q=а&post_type=page#q=%D0%B0&post_type=page
     * This URLs are possible because of additional params in the form.
     * @param \WP $wp
     * @return \WP
     */
    public function parse_request($wp) {
        if (isset($wp->query_vars["pagename"]) && $wp->query_vars["pagename"] == "omega-search") {
            unset($wp->query_vars["post_type"]);
        }
        return $wp;
    }

    public function addBodyClass($classes)
    {
        $classes[] = "archive tax-product_cat woocommerce woocommerce-page omega-search-result";
        return $classes;
    }

    public function insertHtml()
    {
        global $wp_query;
        $origQuery = clone($wp_query);


        $html = "";
        if (is_plugin_active("woocommerce/woocommerce.php") && function_exists("woocommerce_product_loop_start")) {
            $args = array(
                'taxonomy' => 'product_cat',
            );
            $all_categories = get_categories($args);
            if (count($all_categories)) {
                $c = next($all_categories);
                if ($c) {
                    $params = array(
                        'posts_per_page' => 5,
                        'post_type' => 'product',
                        'product_cat' => $c->slug,
                    );
                    $wp_query = new \WP_Query($params);
                } else {
                    $wp_query = new \WP_Query(array());
                }
            } else {
                $wp_query = new \WP_Query(array());
            }
            ob_start();
            woocommerce_product_loop_start();
            echo "####";
            woocommerce_product_loop_end();
            $content = ob_get_contents();
            ob_end_clean();


            $wp_query = $origQuery;

            $blocks = explode("####", $content);
            $beforeListHTML = $blocks[0];
            $afterListHTML = $blocks[1];

            $html .= <<<HTML
<script>'' +
    (function () {
        if (typeof window.OMEGA_CONFIG == "undefined") {
            window.OMEGA_CONFIG = {}
        }
        window.OMEGA_CONFIG.beforeListHTML = `{$beforeListHTML}`;
        window.OMEGA_CONFIG.afterListHTML = `{$afterListHTML}`;
    })();
</script>
HTML;
            //in some cases function may not be defined in admin panel, post edit page
            if (function_exists("wc_print_notices")) {
                wc_print_notices();
            }
        }
        $html .= '
<style>
.os-page-container {
   min-height: 300px;
}
.os-spinner {
    width: 49px;
    height: 49px;
    border: 3px solid #eee;
    border-radius: 50%;
    border-left-color: transparent;
    border-right-color: transparent;
    animation: cssload-spin 1550ms infinite linear;
    -o-animation: cssload-spin 1550ms infinite linear;
    -ms-animation: cssload-spin 1550ms infinite linear;
    -webkit-animation: cssload-spin 1550ms infinite linear;
    -moz-animation: cssload-spin 1550ms infinite linear;
    margin: 0 auto;
    }
    @keyframes cssload-spin {
    100% {
        transform: rotate(360deg);
    }
    }
    @-o-keyframes cssload-spin {
    100% {
        -o-transform: rotate(360deg);
        transform: rotate(360deg);
    }
    }
    @-ms-keyframes cssload-spin {
    100% {
        -ms-transform: rotate(360deg);
        transform: rotate(360deg);
    }
    }
    @-webkit-keyframes cssload-spin {
    100% {
        -webkit-transform: rotate(360deg);
        transform: rotate(360deg);
    }
    }
    @-moz-keyframes cssload-spin {
    100% {
        -moz-transform: rotate(360deg);
        transform: rotate(360deg);
    }
}
#comments {
    display: none;
}
</style><div class="os-page-container"><div class="os-spinner"></div></div>';

        return $html;
    }

}