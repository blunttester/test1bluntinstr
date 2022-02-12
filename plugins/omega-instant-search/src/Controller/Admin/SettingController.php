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
namespace OmegaCommerce\Controller\Admin;


class SettingController
{
    /**
     * Construct the plugin object
     */
    public function __construct(
        \OmegaCommerce\Model\Auth $auth
    )
    {
        $this->auth = $auth;
    }

    /**
     *  Show settings page handler
     */
    public function showAction()
    {
        if (!current_user_can(\OmegaCommerce\Model\Menu::OMEGA_COMMERCE_CAPABILITY)) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        // Render the settings template
        require_once(sprintf("%ssrc/view/templates/settings.php", WP_OMEGA_COMMERCE_PLUGIN_PATH));
    }
}