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

class Client
{
    const METHOD_GET = 'GET';
    const METHOD_POST = 'POST';
    const METHOD_PUT = 'PUT';
    const METHOD_DELETE = 'DELETE';

    const BASE_URL = '/woocommerce/v1';

    public function __construct(
        Config $config
    )
    {
        $this->config = $config;
        $this->host = $config->getBaseApiUrl();
    }

    /**
     * @param string $method
     * @param string $action
     * @param array $params
     * @param array $data
     * @return array|false
     * @throws \OmegaCommerce\Model\ApiException
     */
    public function unprotectedRequest($method, $action, $params = array(), $data = array())
    {
        $url = $this->host . self::BASE_URL . $action;
        $url = rtrim($url, '/');

        if (is_array($params) && count($params)) {
            $url .= '?' . http_build_query($params);
        }
        $curlHandle = curl_init();

        $headers = array(
            'Content-type: application/json',
        );
	    $headers = array_merge($headers, $this->config->getApiHeaders());

        // If CURL for some reason is not available, use WP_HTTP remote requests
        if($curlHandle !== NULL) {
	        curl_setopt($curlHandle, CURLOPT_HTTPHEADER, $headers);

	        set_time_limit(0);
	        curl_setopt($curlHandle, CURLOPT_TIMEOUT, 60 * 10);
	        curl_setopt($curlHandle, CURLOPT_CONNECTTIMEOUT, 5);

	        //Return the output instead of printing it
	        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
	        curl_setopt($curlHandle, CURLOPT_FAILONERROR, true);
	        curl_setopt($curlHandle, CURLOPT_ENCODING, '');

	        curl_setopt($curlHandle, CURLOPT_URL, $url);
	        curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, true);
	        curl_setopt($curlHandle, CURLOPT_SSL_VERIFYHOST, 2);


	        curl_setopt($curlHandle, CURLOPT_NOSIGNAL, 1);
	        curl_setopt($curlHandle, CURLOPT_FAILONERROR, false);

	        if (!$this->config->isValidateSSL()) {
	            curl_setopt($curlHandle, CURLOPT_SSL_VERIFYHOST, 0);
	            curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, 0);
	        }

	        if ($method === self::METHOD_GET) {
	            curl_setopt($curlHandle, CURLOPT_CUSTOMREQUEST, 'GET');
	            curl_setopt($curlHandle, CURLOPT_HTTPGET, true);
	            curl_setopt($curlHandle, CURLOPT_POST, false);
	        } elseif ($method === self::METHOD_POST) {
	            $body = ($data) ? json_encode($data) : '';
	            curl_setopt($curlHandle, CURLOPT_CUSTOMREQUEST, 'POST');
	            curl_setopt($curlHandle, CURLOPT_POST, true);
	            curl_setopt($curlHandle, CURLOPT_POSTFIELDS, $body);
	        } elseif ($method === self::METHOD_DELETE) {
	            curl_setopt($curlHandle, CURLOPT_CUSTOMREQUEST, 'DELETE');
	            curl_setopt($curlHandle, CURLOPT_POST, false);
	        } elseif ($method === self::METHOD_PUT) {
	            $body = ($data) ? json_encode($data) : '';
	            curl_setopt($curlHandle, CURLOPT_CUSTOMREQUEST, 'PUT');
	            curl_setopt($curlHandle, CURLOPT_POSTFIELDS, $body);
	            curl_setopt($curlHandle, CURLOPT_POST, true);
	        }
	        curl_exec($curlHandle);

	        $httpStatus = curl_getinfo($curlHandle, CURLINFO_HTTP_CODE);

	        $response = curl_multi_getcontent($curlHandle);
	        $error = curl_error($curlHandle);
	        curl_close($curlHandle);
        } else {
            $body = ($data) ? json_encode($data) : '';
            $wpHttpParams = array(
                'method' => $method,
                'timeout' => 60 * 10,
                'redirection' => 5,
                'httpversion' => '1.0',
                'blocking' => true,
                'sslverify' => $this->config->isValidateSSL(),
                'headers' => $headers,
                'body' => ($method === self::METHOD_POST || $method === self::METHOD_PUT) ? $body : false,
                'cookies' => array(),
            );

			$result = "";
			if($method === self::METHOD_POST || $method === self::METHOD_PUT) {
				$result = wp_remote_post($url, $wpHttpParams);
			} elseif ($method === self::METHOD_DELETE) {
				$result = wp_remote_request($url,
				    array(
				        'headers' => $headers,
				        'method'     => 'DELETE'
				    )
				);
			} else {
				$result = wp_remote_get($url, $wpHttpParams);
			}
			if (is_object($result)) {
			    /** @var \WP_Error */
                $response = $result->get_error_message();
                $httpStatus = $result->get_error_code();
            } else {
                $httpStatus = $result["response"]["code"];
                $response = $result["body"];
            }
        }


	    if ($httpStatus != 200) {
		    if ($httpStatus == 0) {
			    throw new ApiException($error." Connection error.", $httpStatus);
		    }
		    $result = json_decode($response, true);
		    if (isset($result['error'])) {
			    throw new ApiException($result['error'], $httpStatus );
		    } else {
			    throw new ApiException($response, $httpStatus );
		    }
	    }
	    $result = json_decode($response, true);
        return $result;
    }
}