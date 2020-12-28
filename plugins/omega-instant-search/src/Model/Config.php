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

class Config
{
	public function __construct(
		$version
	)
	{
		$this->version = $version;
	}

    /**
     * @return string
     */
    public function getID()
    {
        return $this->getValue('omega_api/access/id');
    }


    /**
     * @param string $value
     * @return void
     */
    public function setID($value)
    {
        $this->saveValue('omega_api/access/id', $value);

    }

    /**
     * @return string
     */
    public function getSecretKey()
    {
        return $this->getEncryptedValue('omega_api/access/secret_key');
    }


    /**
     * @param string $value
     * @return void
     */
    public function setSecretKey($value)
    {
        $this->saveValueEncrypted('omega_api/access/secret_key', $value);

    }

    /**
     * @return string
     */
    public function getBaseApiUrl()
    {
        $url = $this->getValue('omega_api/access/base_url');
        if ($url == "") { //if plugin enables first time, we have empty url. need additional checks.
            $url = "https://search.omegacommerce.com";
        }
        return rtrim($url, "/");
    }

	/**
	 * @return array
	 */
	public function getApiHeaders()
	{
		$header = $this->getValue('omega_api/access/header');
		if ($header == "") {
			return array();
		}
		return explode(",", $header);
	}

	
    /**
     * @return bool
     */
    public function isValidateSSL()
    {
        if ($this->getValue('omega_api/access/is_validate_ssl') === "") {//true by default
            return true;
        }
        return $this->getValue('omega_api/access/is_validate_ssl');
    }

	/**
	 * @return string
	 */
	public function getVersion()
	{
		return $this->version;
	}

	/**
	 * {@inheritdoc}
	 */
	function getValue($path)
	{
		return get_option($this->convert_to_wp_style($path), '');
	}

	/**
	 * {@inheritdoc}
	 */
	function saveValue($path, $value)
	{
		update_option($this->convert_to_wp_style($path), $value);
	}

	/**
	 * {@inheritdoc}
	 */
	function getEncryptedValue($path)
	{
		return get_option($this->convert_to_wp_style($path), '');
	}

	/**
	 * {@inheritdoc}
	 */
	function saveValueEncrypted($path, $value)
	{
		update_option($this->convert_to_wp_style($path), $value);
	}

	/**
	 * @param string $path
	 * @return string
	 */
	private function convert_to_wp_style($path)
	{
		return str_replace('/', '_', $path);
	}
}
