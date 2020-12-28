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

namespace OmegaCommerce;

class Manager
{
    public function __construct(
        $version
    )
    {
        $this->version = $version;
        $this->build();
    }

    public function build() {

        $config = new Model\Config($this->version);

        $apiClient = new Model\Client($config);
        $auth = new Model\Auth($config, $apiClient);
        $apiIframe = new Block\Iframe($config, $auth);

        $applicationController = new Controller\Admin\ApplicationController($auth, $apiIframe);
        $settingController = new Controller\Admin\SettingController($auth);

        new Model\Menu($applicationController, $settingController);
        new Model\Observer($config);
        new Block\Autocomplete($config);
        new Block\SearchResultsPage();
        new Model\Api($config, $auth);

        $search = new Model\Module($auth);

        $databaseMigration = new Model\DatabaseMigration();
        register_activation_hook(__FILE__, array($databaseMigration, 'install'));
        register_activation_hook(__FILE__, array($databaseMigration, 'installData'));
    }
}

/**
 * Usage:
 * \OmegaCommerce\pr($entity, "x.x.x.x");
 *
 * @param mixed $ar
 * @param string $ip
 * @param bool $die
 * @return void
 */
function pr($ar, $ip, $die = false) {
    if ($ip != getIP()) {
        return;
    }
    echo "<pre>";
    print_r($ar);
    echo "</pre>";
    if ($die) {
        die;
    }
}

/**
 * @return string
 */
function getIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    }
    if (!empty($_SERVER['HTTP_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_FORWARDED_FOR'];
    }
    if (!empty($_SERVER['HTTP_FORWARDED'])) {
        return $_SERVER['HTTP_FORWARDED'];
    }
    if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
        return $_SERVER['HTTP_CF_CONNECTING_IP'];
    }
    return $_SERVER['REMOTE_ADDR'];
}

