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


class ApplicationController
{
    public function __construct(
        \OmegaCommerce\Model\Auth $auth,
        \OmegaCommerce\Block\Iframe $iframe
    )
    {
        $this->auth = $auth;
        $this->iframe = $iframe;
    }

	public function showError($message)
	{
		echo "<div id=\"message\" class=\"error\"><p>{$message}</p></div>";
	}
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
	    if (!$this->auth->isAuthorized() || !get_option('omega_upgrade_flag')) {
		    try {
		        $this->auth->register();
			    update_option('omega_upgrade_flag', 1); //make sure, that we call register after upgrade to 2.0
		    } catch (\OmegaCommerce\Model\ApiException $e) {
			    $this->showError($e->getMessage());
			    die;
		    }
		    wp_redirect(admin_url( 'admin.php?page=omega_commerce'));//reload page
		    return;
	    }
    	try {
		    $token = $this->auth->getToken();
	    } catch (\OmegaCommerce\Model\ApiException $e) {
	    	if ($e->getCode() == 401) {
			    $this->auth->register();
			    wp_redirect( admin_url( 'admin.php?page=omega_commerce' ) );//reload page
			    return;
		    }
		    $this->showError($e->getMessage());
		    die;
	    }

        try {
            echo $this->iframe->toHtml();
        } catch (\Exception $e) {
	        $this->showError($e->getMessage());
	        die;
        }
    }
}