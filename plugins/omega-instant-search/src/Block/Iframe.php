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

class Iframe
{
    public function __construct(
        \OmegaCommerce\Model\Config $config,
        \OmegaCommerce\Model\Auth $auth
    )
    {
        $this->config = $config;
        $this->auth = $auth;
    }

    /**
     * @return string
     */
    public function getApplicationUrl()
    {
        $params = array();
        $params["version"] = $this->config->getVersion();
        $params["ID"] = $this->config->getID();

        return $this->config->getBaseApiUrl()."?".http_build_query($params);
    }

    /**
     * @return string
     */
    public function toHtml()
    {
        try {
		    $token = $this->auth->getToken();
		    $data = array(
			    'token' => $token,
			    'ID'    => $this->config->getID()
		    );
	    } catch (\OmegaCommerce\Model\ApiException $e) {
			//try to register and token again
		    $data = $this->auth->register();
	    }
        $json = json_encode($data);

        return <<<HTML
<style>
    #update-nag, .update-nag {
        display: none;
    }
    .notice {
        display: none;
    }
    iframe {
        margin-left: -20px;
        margin-top: -10px;
        border: none;
        width: 100%;
        box-sizing: border-box
        z-index: 1000000;
        min-height: 800px;
        height: 100%;
    }
    iframe:focus {
        outline: none;
    }
    iframe[seamless] {
        display: block;
    }
</style>
<iframe id="omega-application-iframe" src="{$this->getApplicationUrl()}"></iframe>
<!--height: calc(100% - 56px);-->
<!--margin-top: 56px-->
<script>
    jQuery(window).load(function(){
        var iframe = document.getElementById('omega-application-iframe').contentWindow;
        var obj = {$json};
        iframe.postMessage(JSON.stringify({key: 'omega-app-storage', data: obj}), "*");
    });
    window.addEventListener('message', function (e) {
        var payload = e.data;
        if (payload == 'reload') {
            window.location.reload();
        }
    }, false);
</script>
HTML;

    }
}