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



class Api {
	public function __construct(
		Config $config,
		Auth $auth
	) {
		$this->config = $config;
		$this->auth = $auth;

		add_action( 'init', function( ) {
			add_rewrite_rule( 'omega-api/(.*)$', 'index.php?omega-api=$matches[1]', 'top' );
		} );

		add_filter( 'query_vars', function( $query_vars ) {
			$query_vars[] = 'omega-api';
			return $query_vars;
		} );

		add_action( 'parse_request', function() {
			if (!isset($GLOBALS['wp']->query_vars['omega-api'])) {
				return;
			}
			$route = untrailingslashit( $GLOBALS['wp']->query_vars['omega-api'] );
			if ($route == "") {
				return;
			}
			$this->router($route);
		});
	}

	public function filter_woocommerce_rest_check_permissions( $permission, $context, $object_id, $post_type ) {
		if ($context == 'read') {
			return true;
		}
		return $permission;
	}


	/**
	 * Copy from class WP_REST_Server.
	 */
	public function get_headers( $server ) {
		$headers = array();

		// CONTENT_* headers are not prefixed with HTTP_.
		$additional = array(
			'CONTENT_LENGTH' => true,
			'CONTENT_MD5'    => true,
			'CONTENT_TYPE'   => true,
		);

		foreach ( $server as $key => $value ) {
			if ( strpos( $key, 'HTTP_' ) === 0 ) {
				$headers[ substr( $key, 5 ) ] = $value;
			} elseif ( isset( $additional[ $key ] ) ) {
				$headers[ $key ] = $value;
			}
		}

		return $headers;
	}

	/**
	 * Copy from class WP_REST_Server.
	 */
	public static function get_raw_data() {
		global $HTTP_RAW_POST_DATA;
		if ( ! isset( $HTTP_RAW_POST_DATA ) ) {
			$HTTP_RAW_POST_DATA = file_get_contents( 'php://input' );
		}
		return $HTTP_RAW_POST_DATA;
	}

	/**
	 * @param \WP_REST_Request $request
	 * @param array $handler
	 * @return \WP_REST_Request
	 */
	protected function validateWooRequest($request, $handler) {
		$args = array();
		$request->set_url_params( $args );
		$request->set_attributes( $handler );
		$defaults = array();
		foreach ($args as $arg => $options ) {
			if ( isset( $options['default'] ) ) {
				$defaults[ $arg ] = $options['default'];
			}
		}

		$request->set_default_params( $defaults );

		$check_required = $request->has_valid_params();
		if ( is_wp_error( $check_required ) ) {
			$response = $check_required;
		} else {
			$check_sanitized = $request->sanitize_params();
			if ( is_wp_error( $check_sanitized ) ) {
				$response = $check_sanitized;
			}
		}
		return $request;
	}

	public function router($path) {
		header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );
		header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Pragma: no-cache");

		$request    = new \WP_REST_Request(  $_SERVER['REQUEST_METHOD'] );
		$request->set_query_params( wp_unslash( $_GET ) );
		$request->set_body_params( wp_unslash( $_POST ) );
		$request->set_file_params( $_FILES );
		$request->set_headers( $this->get_headers( wp_unslash( $_SERVER ) ) );
		$request->set_body( $this->get_raw_data() );

		if ($path == "omegacommerce/search/config/save") {
			$this->auth->saveConfig($request);
			die;
		}
		if (!$this->auth->isValidRequest($request)) {
			header( "HTTP/1.1 401 Unauthorized" );
			echo json_encode( array( "status" => "401. Access denied." ) );
			die;
		}
		if ($request->get_param("d")) {
			ini_set('display_errors', 1);
			ini_set('display_startup_errors', 1);
			error_reporting(E_ALL);
		}
		global $woocommerce;
		if (!$woocommerce) {
            header( "HTTP/1.1 406 Not Acceptable" );
            echo json_encode( array( "status" => "We cant find WooCommerce plugin in your WordPress installation." ) );
            die;
        }
		if (version_compare( $woocommerce->version, "3.0.0", "<" ) ) {
			header( "HTTP/1.1 406 Not Acceptable" );
			echo json_encode( array( "status" => "We dont support WooCommerce version ".$woocommerce->version. ".Please upgrade WooCommerce plugin." ) );
			die;
		}
		$wcApi = new \WC_API();
		$wcApi->register_wp_admin_settings();
		// allow woo to read products
		add_filter( 'woocommerce_rest_check_permissions', array($this, 'filter_woocommerce_rest_check_permissions'), 100000, 4 );

		if ($request->get_param( 'orderby' ) == "") {
			$request->set_param( 'orderby' , 'date');
		}
		global $sitepress;
		if ($lang = $request->get_param("lang")) {
			$sitepress->switch_lang( $lang );
		}
		$response = false;
		if ($path == "version") {
			$response = $this->auth->getVersion($request);
		} elseif ($path == "wc/v2/products") {
			$wc       = new \WC_REST_Products_Controller();
			$handler =
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_items' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
					'args'                => $wc->get_collection_params(),
				)
			;

			$request = $this->validateWooRequest($request, $handler);
			$response = $wc->get_items( $request );
			$this->addWMPL($response);
		} elseif ($path == "wc/v2/products/categories") {
			$wc = new \WC_REST_Product_Categories_Controller();
			$handler =
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_items' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
					'args'                => $wc->get_collection_params(),
				)
			;
			$request = $this->validateWooRequest($request, $handler);
			if (!isset($request['per_page'])) {
				$request['per_page'] = 10;
			}
			$request['orderby'] = 'id';
			if (!isset($request['page'])) {
				$request['page'] = 1;
			}
			$response = $wc->get_items($request);
		} elseif ($path == "wc/v2/taxes") {
			$wc       = new \WC_REST_Taxes_Controller();
			$handler =
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_items' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
					'args'                => $wc->get_collection_params(),
				)
			;
			$request = $this->validateWooRequest($request, $handler);
			if (!isset($request['per_page'])) {
				$request['per_page'] = 10;
			}
			$request['orderby'] = 'id';
			if (!isset($request['page'])) {
				$request['page'] = 1;
			}
			$response = $wc->get_items($request);
		} elseif (preg_match("/wc\/v2\/products\/(?P<product_id>[\d]+)\/variations/", $path, $matches)) {
			$wc = new \WC_REST_Product_Variations_Controller();
			$request['product_id'] = $matches['product_id'];
			$response = $wc->get_items($request);
		} elseif (preg_match("/wc\/v2\/settings\/(?P<group_id>[\w-]+)/", $path, $matches)) {
			$wc       = new \WC_REST_Setting_Options_Controller();
			$request['group_id'] = $matches['group_id'];
			$response = $wc->get_items( $request );
		} elseif (preg_match("/wc\/v2\/products\/attributes/", $path, $matches)) {
			$wc       = new \WC_REST_Product_Attributes_Controller();
			$response = $wc->get_items( $request );
		} elseif ($path == "wp/v2/pages") {
			$wc       = new \WP_REST_Posts_Controller("page");
			$handler =
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_items' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
					'args'                => $wc->get_collection_params(),
				)
			;
			$request = $this->validateWooRequest($request, $handler);
			if (!isset($request['per_page'])) {
				$request['per_page'] = 10;
			}
			$request['orderby'] = 'id';
			if (!isset($request['page'])) {
				$request['page'] = 1;
			}
			$response = $wc->get_items( $request );
		} elseif ($path == "wp/v2/posts") {
			$wc       = new \WP_REST_Posts_Controller("post");
			$handler =
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_items' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
					'args'                => $wc->get_collection_params(),
				)
			;
			$request = $this->validateWooRequest($request, $handler);
			if (!isset($request['per_page'])) {
				$request['per_page'] = 10;
			}
			$request['orderby'] = 'id';
			if (!isset($request['page'])) {
				$request['page'] = 1;
			}
			$response = $wc->get_items( $request );
		} elseif ($path == "omegacommerce/search/changes") {
			$response = $this->auth->getChanges($request);
		} elseif ($path == "search/settings") {
			$response = $this->getSettings();
		}
		if (!$response) {
			print_r($response);
			die;
		}
		$jsonOptions = 0;
		if (defined( 'JSON_UNESCAPED_SLASHES')) {
			$jsonOptions |= JSON_UNESCAPED_SLASHES;
		}
		if (defined( 'JSON_UNESCAPED_UNICODE')) {
			$jsonOptions |= JSON_UNESCAPED_UNICODE;
		}
		if (defined( 'JSON_PARTIAL_OUTPUT_ON_ERROR')) {
			$jsonOptions |= JSON_PARTIAL_OUTPUT_ON_ERROR;
		}
		$jsonOptions |= JSON_PRETTY_PRINT;

		if (is_a($response, "WP_Error")) {
			echo json_encode(_wp_json_prepare_data( $response), $jsonOptions);
		} elseif (is_array($response)) {
			echo json_encode( _wp_json_prepare_data($response), $jsonOptions);
		} else {
			echo json_encode( _wp_json_prepare_data( $response->jsonSerialize()), $jsonOptions);
		}
		die;
	}

	public function addWMPL($response)
	{
		$data = $response->get_data();
		if (function_exists("wpml_get_language_information")) {
			foreach ( $data as $k =>$item ) {
				$l = wpml_get_language_information( "", $item['id'] );
				$item['locale'] =  $l['locale'];
				$item['language_code'] =  $l['language_code'];
				$data[$k] = $item;
			}
			$response->set_data($data);
		}
	}
	public function getSettings()
	{
//		$res = array();
//		foreach ($this->getBlogs() as $blog) {
//			$blogId = $blog['blog_id'];
			$blogId =  get_current_blog_id();
			$this->switchToBlog($blogId);
			$data = array();
			$data['blog_id'] = $blogId;
			if (function_exists("wpml_get_active_languages_filter")) {
				$data['wpml_active_languages'] = \wpml_get_active_languages_filter( "" );
			}
			$data['name'] = get_option('blogname');
			$data['url'] = get_option('siteurl');
			$data['currency'] = $this->getBlogOption($blogId, 'woocommerce_currency', '');
			$data['locale'] = get_locale();
			$data['currency_format'] = $this->getWoocommercePriceFormat();
			$data['decimal_format'] = $this->getWoocommerceDecimalFormat();
			$data['tax_display_shop'] = get_option( 'woocommerce_tax_display_shop' );
			$data['prices_include_tax'] = get_option( 'woocommerce_prices_include_tax' );
			$data['price_display_suffix'] = get_option( 'woocommerce_price_display_suffix' );
			$data['woocommerce_default_country'] = get_option( 'woocommerce_default_country' );
			$data['woocommerce_permalinks'] = get_option( 'woocommerce_permalinks' );
			$data['version'] = $this->config->getVersion();
			$this->restoreCurrentBlog();
//			$res[] = $data;
//		}

		return $data;
	}

	/**
	 * @return array
	 */
	public function getBlogs()
	{
		if (is_multisite()) {
			if (function_exists('get_sites')) {
				$sites = get_sites();
			} else {
				$sites = wp_get_sites();
			}
		} else {
			$sites = array(
				array(
					'blog_id' => get_current_blog_id(),
				)
			);
		}
		return $sites;
	}


	/**
	 * Defines custom decimal format according to Woocommerce settings
	 * @return string
	 */
	private function getWoocommerceDecimalFormat()
	{
		// #,##0.###
		$decimalFormat = "#" . wc_get_price_thousand_separator() . '##0';
		if(wc_get_price_decimals()) {
			$decimalFormat .= wc_get_price_decimal_separator() . str_repeat("#", wc_get_price_decimals());
		}
		return $decimalFormat;
	}
	/**
	 * @return string
	 */
	private function getWoocommercePriceFormat()
	{
		$currency = get_option('woocommerce_currency', '');
		if (!function_exists('get_woocommerce_currency_symbol')) {
			return '';
		}
		$currency_pos = get_option('woocommerce_currency_pos');
		$decimal_sep = get_option('woocommerce_price_decimal_sep');
		$format = '%1$s%2$s';
		switch ($currency_pos) {
			case 'left' :
				$format = '%1$s%2$s';
				break;
			case 'right' :
				$format = '%2$s%1$s';
				break;
			case 'left_space' :
				$format = '%1$s&nbsp;%2$s';
				break;
			case 'right_space' :
				$format = '%2$s&nbsp;%1$s';
				break;
		}
		$currency_pos = apply_filters('woocommerce_price_format', $format, $currency_pos);
		$currency_symbol = get_woocommerce_currency_symbol($currency);
		return str_replace(array('%1$s', '%2$s'), array($currency_symbol, '0' . $decimal_sep . '00'), $currency_pos);
	}
	private function getBlogOption($id, $option, $default = false)
	{
		if (function_exists("getBlogOption")) {
			return getBlogOption($id, $option, $default);
		}
		return get_option($option, $default);
	}
	private function restoreCurrentBlog()
	{
		if (function_exists("restoreCurrentBlog")) {
			return restoreCurrentBlog();
		}
	}
	private function switchToBlog($id)
	{
		if (function_exists("switchToBlog")) {
			return switchToBlog($id);
		}
	}
}