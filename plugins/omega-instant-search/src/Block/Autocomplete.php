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

class Autocomplete
{

    public function __construct(
        \OmegaCommerce\Model\Config $config
    )
    {
        add_action('wp_head', array(&$this, 'autocomplete'));
        $this->config = $config;
        $this->storeId = get_current_blog_id();
    }

    /**
     * @return void
     */
    public function autocomplete()
    {
        echo $this->toHtml();
        include WP_OMEGA_COMMERCE_PLUGIN_PATH . "src/view/templates/searchForm.php";
        if ($css = get_option('omega_search_custom_css')) {
            echo "<style>$css</style>";
        }
    }

    /**
     * @return string
     */
    public function toHtml()
    {
        $id = $this->config->getID();
        if (!$id) {
            return "";
        }

        // Check, whether Omega Search is excluded on this page
        $excludeOption = get_option('omega_search_exclude_pages');
        $disablePages = explode(PHP_EOL, $excludeOption);
         if(count($disablePages)) {
            global $wp;
            $current_url = home_url(add_query_arg(array(),$wp->request));
            foreach ($disablePages as $page) {
                if (fnmatch(trim($page), $current_url) ||
                    fnmatch(trim($page), $current_url . "/")) {
                    return "";
                }
            }
        }

        $url = $this->config->getBaseApiUrl();
        $url = str_replace('https://', '', str_replace('http://', '', $url));
        $url = rtrim($url, '/');
        $page = $this->getSearchResultPage();

        $resultsUrl = get_permalink($page);
        return <<<HTML
<script data-cfasync="false" src="//{$url}/instant/initjs?ID={$id}&seid={$this->storeId}" async></script>
<script>'' +
    (function () {
        var endpoint = '{$url}';
        var protocol= ("https:" === document.location.protocol ? "https://" : "http://");
        //url must have the same protocol as page. otherwise js errors possible.
        var url = '{$resultsUrl}'
        url = url.replace("https://", protocol)
        url = url.replace("http://", protocol)
        if (typeof window.OMEGA_CONFIG == "undefined") {
            window.OMEGA_CONFIG = {}
        }
        window.OMEGA_CONFIG.searchResultUrl = url
    })();
</script>
HTML;

    }

    /**
     * @return \WP_Post
     */
    public function getSearchResultPage()
    {
        $slug = 'omega-search';
        //     $pages = get_pages(array("name" => $slug));
        $page = get_page_by_path($slug, OBJECT, 'page');
        if (!$page) {
            wp_insert_post(array(
                'post_title' => __('Search Results'),
                'post_type' => 'page',
                'post_content' => '[omega_search_results]',
                'post_name' => $slug,
                'post_status' => 'publish',
            ));
            $page = get_page_by_path($slug, OBJECT, 'page');
        }
        if (function_exists("pll_current_language")) { //support of https://wordpress.org/plugins/polylang/
            $lang = pll_current_language();
            $page = pll_get_post($page->ID, $lang);
        }
        return $page;
    }
}