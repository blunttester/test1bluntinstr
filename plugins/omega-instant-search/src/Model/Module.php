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

class Module
{
    const REDIRECT_OPTION = "omega_search_do_activation_redirect";

    /**
     * Construct the plugin object
     */
    public function __construct(
        \OmegaCommerce\Model\Auth $auth
    )
    {
        $this->auth = $auth;
        add_action('admin_init', array(&$this, 'initSettings'));

        register_activation_hook(OMEGA_COMMERCE_SEARCH_FILE, array(&$this, 'activate'));
        register_deactivation_hook(OMEGA_COMMERCE_SEARCH_FILE, array(&$this, 'deactivate'));

        $this->checkForRedirect();

	    add_action('admin_init', function(){
		    if (!get_option('omega_rewrites_flush_flag')) {
			    flush_rewrite_rules();
			    update_option('omega_rewrites_flush_flag', 1);
		    }
	    });
    }

    /**
     * Initialize some custom settings
     */
    public function initSettings()
    {
        //we need to register option, which will be shown on the settings page.
        //so they will be saved with settings saving
//        register_setting('omega_search-group', 'omega_api_sync_time');
//        register_setting('omega_search-group', 'omega_api_sync_allowed');
//        register_setting('omega_search-group', 'omega_api_max_sync_number');
        register_setting('omega_search-group', 'omega_api_access_base_url');
        register_setting('omega_search-group', 'omega_api_access_header');
        register_setting('omega_search-group', 'omega_search_box_selector');
        register_setting('omega_search-group', 'omega_search_custom_css');
        register_setting('omega_search-group', 'omega_search_exclude_pages');
        register_setting('omega_search-group', 'omega_api_access_is_validate_ssl');
//        register_setting('omega_search-group', 'omega_api_access_id');
//        register_setting('omega_search-group', 'omega_api_access_secret_key');
//        register_setting('omega_search-group', 'omega_api_reindex_by_cron');
//        register_setting('omega_search-group', 'omega_api_reindex_after_save');
//        register_setting('omega_search-group', 'omega_api_reindex_by_cron_interval');


        add_option('omega_api_max_sync_number', 30);
        add_option('omega_api_access_base_url', "https://search.omegacommerce.com");
        add_option('omega_api_access_is_validate_ssl', true);
        add_option('omega_api_reindex_by_cron', 1);
        add_option('omega_api_reindex_after_save', 0);
        add_option('omega_api_reindex_by_cron_interval', 5);


        if (current_user_can('manage_options')) {
            add_action('admin_notices', array(&$this, 'addNotices'));
        }
    }

    /**
     * @return void
     */
    private function checkForRedirect() {
        if (get_option(self::REDIRECT_OPTION) == "activate") {
            delete_option(self::REDIRECT_OPTION);
	        try {
		        $this->auth->register();
	        } catch (\OmegaCommerce\Model\ApiException $e) {
		        die($e->getMessage());
	        }

            header("Location: admin.php?page=omega_commerce", true, 302);
            die;
        }
    }

    /**
     * Activate plugin
     */
    public function activate()
    {
	    add_option( self::REDIRECT_OPTION, "activate" );
    }

    /**
     * Deactivate the plugin
     */
    public function deactivate()
    {
        $this->auth->remove();
    }

    public function addNotices()
    {
	    $current_screen = get_current_screen();
    	if ($current_screen && $current_screen->id != "plugins" && strpos($current_screen->id, "omega") === false) {
    		return;
	    }
	    if (strpos(site_url(), "localhost") || strpos(site_url(), "127.0.0.1")) {
		    echo '<div id="message" class="error">
	<p>
	Sorry, but Omega Instant Search uses Wordpress REST API and can\'t work on localhost.
    </p>
</div>
';
	    }
    }
}