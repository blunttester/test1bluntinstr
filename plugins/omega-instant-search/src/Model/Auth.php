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

class Auth {
	protected $token = false;
	public function __construct(
		Config $config,
		Client $client
	) {
		$this->config = $config;
		$this->client = $client;

		add_action( 'rest_api_init', function () {
			register_rest_route( 'omegacommerce/search', '/config/save/', array(
				'methods'  => 'POST',
				'callback' => array( $this, 'saveConfig' ),
			) );
		} );

		add_action( 'rest_api_init', function () {
			register_rest_route( 'omegacommerce/search', '/changes/', array(
				'methods'  => 'GET',
				'callback' => array( $this, 'getChanges' ),
			) );
		} );


		add_action('admin_init',  array(&$this, 'updatePluginCheck'));
	}

	public function updatePluginCheck() {
		$version = get_option('omega_search_plugin_version');
		if ($version != $this->config->getVersion()) {
			$this->register();
			update_option('omega_search_plugin_version', $this->config->getVersion());
		};
	}

	/*
	 * http://lti.tools/oauth/
	 *  curl -XGET 'http://example.com/wp-json/omegacommerce/search/changes?from=-62135596800&oauth_consumer_key=c04a90ed-6734-4cda-83b6-ade8b03c8278&oauth_nonce=8810125041787926705&oauth_signature=Hp5heqCQGYv3EeCsX%252BeHwl5b%252F1Y%253D&oauth_signature_method=HMAC-SHA1&oauth_timestamp=1565092364&oauth_version=1.0&to=1565092364' -H 'Accept: application/json' -H 'Content-Type: application/json'
	 */
	public function isValidRequest( \WP_REST_Request $request ) {
		$protocol = ( isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] === 'on' ? "https" : "http" );
		if ($protocol == "https") {
			if ($request->get_param("consumer_secret") == $this->config->getSecretKey()) {
				return true;
			}
		}
		$vars   = [];
		$uri = $_SERVER['REQUEST_URI'];
		if ( strpos( $uri, "?" ) !== false ) {
			$ar       = explode( "?", $uri );
			$uri      = $ar[0];
			$paramStr = $ar[1];
			parse_str( $paramStr, $params );
			foreach ( $params as $key => $value ) {
				$vars[ $key ] = $value;
			}
		}
		$oauth_signature = $vars['oauth_signature'];
		$oauth_signature = rawurldecode( str_replace( ' ', '+', $oauth_signature ) );
		unset( $vars['oauth_signature'] );

		foreach ( $vars as $key => $value ) {
			$key          = str_replace( '%', '%25', rawurlencode( rawurldecode( $key ) ) );
			$value        = str_replace( '%', '%25', rawurlencode( rawurldecode( $value ) ) );
			$vars[ $key ] = $value;
		}
		uksort( $vars, 'strcmp' );
		$vars2 = [];
		foreach ( $vars as $k => $v ) {
			$vars2[] = $k . '%3D' . $v;
		}
		$str              = implode( '%26', $vars2 );
		$base_request_uri = $protocol . "://{$_SERVER['HTTP_HOST']}{$uri}";
		$str              = strtoupper( $_SERVER['REQUEST_METHOD'] ) . '&' . rawurlencode( $base_request_uri ) . '&' . $str;
		$alg              = strtolower( str_replace( 'HMAC-', '', $vars['oauth_signature_method'] ) );
		if ($alg != "sha1") {
			return false;
		}
		$signature        = base64_encode( hash_hmac( $alg, $str, $this->config->getSecretKey() . "&", true ) );
//		echo $str;
//		echo "\n";
//		echo $oauth_signature;
		return $signature === $oauth_signature;
	}


	public function getVersion( \WP_REST_Request $request ) {
		$data = array("version" => $this->config->getVersion());
		$response = rest_ensure_response(  $data);
		return $response;
	}

	public function getChanges( \WP_REST_Request $request ) {
		$from = (int) $request->get_param( 'after' );
		$to   = (int) $request->get_param( 'to' );
		$size = (int) $request->get_param( 'size' );
		if ( ! $size ) {
			$size = 255;
		}
		global $wpdb;
		$table = $wpdb->prefix . DatabaseMigration::OMEGA_CHANGES_TABLE;
		$sql   = "SELECT id, entity_id, entity_type, created_at FROM $table WHERE created_at > $from AND created_at <= $to ORDER BY created_at asc LIMIT 0, $size";

		$res   = $wpdb->get_results( $sql );
		if ( $wpdb->last_error != "" ) {
			throw new \Exception( $wpdb->last_error );
		}
		$data = array( "status" => "success", "items" => $res);

		$ids = array( 0 );
		foreach ( $res as $item ) {
			$ids[] = $item->id;
		}
		$sql = "DELETE FROM $table WHERE id IN (" . implode( ",", $ids ) . ");";
		$wpdb->get_results( $sql );
		if ( $wpdb->last_error != "" ) {
			throw new \Exception( $wpdb->last_error );
		}
		$response = rest_ensure_response(  $data);
		return $response;
	}

	public function saveConfig( \WP_REST_Request $request ) {
		$params = $request->get_body_params();
		if ( ! isset( $params['hash'] ) ) {
			header( "HTTP/1.1 401 Unauthorized" );
			echo "fail 1";
			die;
		}
		if ( $params['hash'] != md5( $this->config->getSecretKey() ) ) {
			header( "HTTP/1.1 401 Unauthorized" );
			echo "fail 2";
			die;
		}
		$this->config->setID( $params['id'] );
		echo "OK";
		die;
	}

	/**
	 * @return bool
	 */
	public function isAuthorized() {
		return $this->config->getSecretKey() != "" && $this->config->getID() != "";
	}

	/**
	 * @return array|false
	 */
	public function remove() {
		try {
			return $this->client->unprotectedRequest( Client::METHOD_POST, $this->getAuthUrl( "/auth/remove" ) );
		} catch ( \Exception $e ) {
		}
	}

	/**
	 * @param string $siteURL
	 * @param string $id
	 * @param string $secretKey
	 *
	 * @return array
	 */
	public function getRegistrationData($siteURL, $id, $secretKey ) {
		$data = array(
			'secret_key' => $secretKey,
			'id' => $id,
			'url'        => $siteURL,
			'version' => $this->config->getVersion(),
		);
		return $data;
	}

	public function getWoocommerceAuthorizationURL() {
		if ( isset( $_GET['success'] ) && $_GET['success'] == 0 ) { //customer pressed deny
			return admin_url();
		}
		$url         = site_url();
		$returnURL   = urlencode( admin_url( "/admin.php?page=omega_commerce" ) );
		$apiUrl      = $this->config->getBaseApiUrl();
		$callbackUrl = urlencode( $apiUrl . "/woocommerce/v1/auth/woocommerce_key?store_base_url=" . urlencode( $url ) );

		return "{$url}/wc-auth/v1/authorize?app_name=Omega+Instant+Search&scope=read&user_id=1&return_url=$returnURL&callback_url=$callbackUrl";
	}

	/**
	 * @return string
	 */
	public function getSiteURL() {
		$siteURL = site_url();
		if (isset($_SERVER['HTTP_REFERER'])) {
			$schema = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_SCHEME);
			$siteURL = str_replace("http://", $schema."://", $siteURL);
			$siteURL = str_replace("https://", $schema."://", $siteURL);
		}
		return $siteURL;
	}
	
	/**
	 * @param string $siteURL
	 *
	 * @return array
	 */
	public function register() {
		$siteURL = $this->getSiteURL();
		$secretKey = $this->config->getSecretKey();
		if (!$secretKey) {
			$secretKey = bin2hex( openssl_random_pseudo_bytes( 20 ) );
			$this->config->setSecretKey( $secretKey );
		}
		$data = $this->getRegistrationData( $siteURL, $this->config->getID(), $secretKey );
		$res = $this->client->unprotectedRequest( Client::METHOD_POST, "/auth/register", array(), $data );
		if ($this->config->getID() == "" || $this->config->getID() != $res['ID']) {
			$this->config->setID($res['ID']);
		}
		return $res;
	}

	/**
	 * @return array
	 * @throws \OmegaCommerce\Model\ApiException
	 */
	public function getToken() {
		if ($this->token) {
			return $this->token;
		}
		$token = $this->client->unprotectedRequest( "POST", $this->getAuthUrl( '/auth/token' ) );
		$this->token = $token['token'];
		return $this->token;
	}

	/**
	 * @param string $requestURL
	 * @param array $data
	 *
	 * @return string
	 */
	public function getAuthUrl( $requestURL, $data = array() ) {
		if ( $this->config->getID() == "" ) {
			throw new \Exception( "cant do request (1)" );
		}

		$data['timestamp'] = time();
		$data2             = array(
			"v"  => $this->config->getVersion(),
			"ID" => $this->config->getID(),
		);
		$data              = array_merge( $data, $data2 );
		ksort( $data );
		$url = array();
		foreach ( $data as $k => $v ) {
			$url[] = "$k=$v";
		}
		$url  = implode( "&", $url );
		$hmac = hash_hmac( 'sha256', $url, $this->config->getSecretKey() );

		return $requestURL . "?$url&hmac=$hmac";
	}
}