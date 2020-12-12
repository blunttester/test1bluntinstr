<?php
/**
 * Plugin Name: WooCommerce Checkout (Finland) Payment Gateway Modified by WC Vendors 
 * Plugin URI: http://www.wcvendors.com/
 * Description: Accept credit cards and money transfers in WooCommerce with the Checkout Gateway
 * Author: SkyVerge, WC Vendors (wcvendors@gmail.com)
 * Author URI: http://www.wcvendors.com/
 * Version: 1.4.0
 * Text Domain: woocommerce-gateway-checkout-fi
 * Domain Path: /i18n/languages/
 *
 * Copyright: (c) 2011-2014 SkyVerge, Inc. (info@skyverge.com)
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package   WC-Checkout-Fi
 * @author    SkyVerge
 * @category  Payment-Gateways
 * @copyright Copyright (c) 2011-2014, SkyVerge, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Required functions
if ( ! function_exists( 'woothemes_queue_update' ) ) {
	require_once( 'woo-includes/woo-functions.php' );
}

// Plugin updates
woothemes_queue_update( plugin_basename( __FILE__ ), '48e88957e27c7d9ceb7234d8df636261', '18625' );

// Require needed Checkout Interface Module
require_once( "Checkout_Fi.php" );
require_once( 'class-sv-wc-gateway-checkout-fi-plugin-compatibility.php' );

add_action('plugins_loaded', 'init_checkout_fi', 0);

function init_checkout_fi() {

	// Execute only if WooCommerce is enabled
	if ( ! class_exists( 'WC_Payment_Gateway' ) ) { return; }

	else {

		/**
		 * Localization
		 */
		load_plugin_textdomain('woocommerce-gateway-checkout-fi', false, dirname( plugin_basename( __FILE__ ) ) . '/i18n/languages');

		// Load some basic css styles
		add_action('wp_print_styles', 'add_checkout_stylesheet');

		function add_checkout_stylesheet() {
			$myStyleUrl = plugins_url( 'assets/css/frontend/checkout-fi.min.css', __FILE__ );
			$myStyleFile = WP_PLUGIN_DIR . '/woocommerce-gateway-checkout-fi-wcv/assets/css/frontend/checkout-fi.min.css';

			if ( file_exists( $myStyleFile ) ) {
				wp_register_style( 'myStyleSheets', $myStyleUrl );
				wp_enqueue_style( 'myStyleSheets');
			}
		}

		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'checkout_fi_action_links' );

		/**
		 * action_links function.
		 */
		function checkout_fi_action_links( $links ) {

			$plugin_links = array(
				'<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section=wc_gateway_checkout_fi' ) . '">' . __( 'Settings', 'woocommerce-gateway-checkout-fi' ) . '</a>',
				'<a href="http://docs.woothemes.com/document/checkout-fi/">' . __( 'Docs', 'woocommerce-gateway-checkout-fi' ) . '</a>',
				'<a href="http://support.woothemes.com/">' . __( 'Support', 'woocommerce-gateway-checkout-fi' ) . '</a>',
			);

			return array_merge( $plugin_links, $links );
		}

		//  WC Vendors Customisations 
		add_action('wcvendors_settings_after_paypal', 'wcv_add_custom_merchant_id_field');

		function wcv_add_custom_merchant_id_field() {
		    ?>
		    <div class="wcv_merchant_id_container">
		      <p><b><?php _e( 'Merchant ID', 'woocommerce-gateway-checkout-fi' ); ?></b><br/>
		        <?php _e( 'Your Checkout.fi merchant ID.', 'woocommerce-gateway-checkout-fi' ); ?><br/>

		        <input type="text" name="wcv_merchant_id" id="wcv_merchant_id" placeholder="1234" value="<?php echo get_user_meta( get_current_user_id(), 'wcv_merchant_id', true ); ?>" />
		      </p>
		    </div>
		    <?php
		  }

		add_action( 'wcvendors_admin_after_commission_due', 'wcv_admin_user_info' );

		function wcv_admin_user_info( $user ) { ?>
		  <tr>
		    <th><label for="wcv_merchant_id"><?php _e( 'Merchant ID', 'woocommerce-gateway-checkout-fi' ); ?></label></th>
		    <td><input type="text" name="wcv_merchant_id" id="wcv_merchant_id" value="<?php echo get_user_meta( $user->ID, 'wcv_merchant_id', true ); ?>" class="regular-text"></td>
		  </tr>
		<?php }

		add_action( 'wcvendors_shop_settings_saved', 'wcv_save_merchant_id' );

		add_action( 'wcvendors_update_admin_user', 'wcv_save_merchant_id' );
		function wcv_save_merchant_id( $user_id )
		{
		  if ( isset( $_POST['wcv_merchant_id'] ) ) {
		    update_user_meta( $user_id, 'wcv_merchant_id', $_POST['wcv_merchant_id'] );
		  }
		}


		function add_jQuery_libraries() {
		    wp_register_script('jquery-validation-plugin', 'http://ajax.aspnetcdn.com/ajax/jquery.validate/1.11.1/jquery.validate.min.js', array('jquery'));
		    wp_enqueue_script('jquery-validation-plugin');
		}
 
		// Wordpress action that says, hey wait! lets add the scripts mentioned in the function as well.
		add_action( 'admin_enqueue_scripts', 'add_jQuery_libraries' );

		add_action('admin_menu', 'create_merchant_id_menu');

		/** 
		*
		*	Create hidden admin page to display the create merchant id form 
		*/
		function create_merchant_id_menu() { 

				add_submenu_page(
				      null, 
				      __('Create Mechant ID - Checkout Finland', 'woocommerce-gateway-checkout-fi'),
				      __('Create Mechant ID - Checkout Finland', 'woocommerce-gateway-checkout-fi'), 
				      'manage_options', 
				      'cif-createmerchant', 
				      'create_mechant_id_page'
				);
			}

		/** 
		*
		*	Page output for creating a merchant id 
		*	
		*/
		function create_mechant_id_page() { ?>

		<script type="text/javascript">
			jQuery(document).ready(function(){
				jQuery('#createmerchant_form').validate();
			});
		</script>


		<div class="wrap">
		<h2><?php _e('Create Mechant ID - Checkout Finland', 'woocommerce-gateway-checkout-fi'); ?></h2>

		<?php 

		$company_name  			= isset($_POST['company_name']) ? $_POST['company_name'] : ''; 
		$company_vat_id 		= isset($_POST['company_vat_id']) ? $_POST['company_vat_id'] : ''; 
		$company_marketing_name = isset($_POST['company_marketing_name']) ? $_POST['company_marketing_name'] : ''; 
		$company_email 			= isset($_POST['company_email']) ? $_POST['company_email'] : ''; 
		$company_gsm 			= isset($_POST['company_gsm']) ? $_POST['company_gsm'] : ''; 
		$company_address 		= isset($_POST['company_address']) ? $_POST['company_address'] : ''; 
		$company_url		 	= isset($_POST['company_url']) ? $_POST['company_url'] : ''; 
		$company_info 			= isset($_POST['company_info']) ? $_POST['company_info'] : ''; 

		$shop_merchant_id		= isset($_POST['shop_merchant_id']) ? $_POST['shop_merchant_id'] : ''; 
		$shop_merchant_secret	= isset($_POST['shop_merchant_secret']) ? $_POST['shop_merchant_secret'] : ''; 


		if ($_POST) {  

			$woocommerce_checkout_fi_settings = get_option('woocommerce_checkout_fi_settings'); 

			$target_url = 'https://rpcapi.checkout.fi/reseller/createMerchant';

		    $username = $woocommerce_checkout_fi_settings['api_id']; // api user
		    $password = $woocommerce_checkout_fi_settings['api_secret']; // api password

		    // echo 'Username: '.$username.' Password: '.$password.'<br />'; 

		    $postdata = http_build_query(
		         array(
		             'company'  => $_POST['company_name'],                 // Company name
		             'vat_id'   => $_POST['company_vat_id'],               // Vat id/business id
		             'name'     => $_POST['company_marketing_name'],       // Marketing name of company/name of the service
		             'email'    => $_POST['company_email'],                // Email of customer service, extranet password and api merchant secret will be sent here
		             'gsm'      => $_POST['company_gsm'],                  // customer service phone number
		             'address'  => $_POST['company_address'],
		             'url'      => $_POST['company_url'],
		             'type'     => '4',                                 // shop-in-shop vendor = 5, shop-in-shop marketplace = 4
		             'info'     => $_POST['company_info'],                 // General info text about the merchant
		             'kkhinta'  => 0,                                   // 0, 25 or 45, this value is the merchants monthly payment in €, if using a custom pricing use pricing code (that checkout finland will create), if creating marketplace use 0
		         )
		    );

			$opts = array(
		     'http'     => array(
		     'method'   => "POST",
		     'header'   => "Content-Type: application/x-www-form-urlencoded\r\nAuthorization: Basic " . base64_encode("$username:$password"),
		     'content'  => $postdata
		    ));

		    $context    = stream_context_create($opts);
		    $response   = file_get_contents($target_url, false, $context);

		    if ($response) {

		        $merchant = @simplexml_load_string($response);

		        if(isset($merchant->id)) 
		        {
		            
		            echo '<h2>Merchant ID Successfully Created</h2>'; 
		            echo '<div>'; 
		            echo '<h4>Merchant ID: '.$merchant->id.'</h4>';
		            echo '<h4>Merchant Secret: '.$merchant->secret.'</h4>';
		            echo '</div>'; 

					$woocommerce_checkout_fi_settings[$shop_merchant_id] = $merchant->id;
					$woocommerce_checkout_fi_settings[$shop_merchant_secret] = $merchant->secret;

					update_option('woocommerce_checkout_fi_settings', $woocommerce_checkout_fi_settings); 
					
		            
		        } else { 

		        	echo '<h3>'.__('There was an error connecting to the API.', 'woocommerce-gateway-checkout-fi' ).'</h3>'; 
		        	echo '<pre>'; 
					print_r($response); 
					echo '</pre>'; 

		        }
		    }
		} 

		?> 

		<form id="createmerchant_form" method="post" action="">

		    <table class="form-table">
		        <tr valign="top">
		        <th scope="row"><?php _e('Company Name','woocommerce-gateway-checkout-fi'); ?></th>
		        <td><input type="text" name="company_name" value="<?php echo $company_name;  ?>" required /></td>
		        </tr>
		         
		        <tr valign="top">
		        <th scope="row"><?php _e('Company Vat Id','woocommerce-gateway-checkout-fi'); ?></th>
		        <td><input type="text" name="company_vat_id" value="<?php echo $company_vat_id; ?>" required /></td>
		        </tr>
		        
		        <tr valign="top">
		        <th scope="row"><?php _e('Company Marketing Name','woocommerce-gateway-checkout-fi'); ?></th>
		        <td><input type="text" name="company_marketing_name" value="<?php echo $company_marketing_name; ?>"required  /></td>
		        </tr>

		        <tr valign="top">
		        <th scope="row"><?php _e('Company Email','woocommerce-gateway-checkout-fi'); ?></th>
		        <td><input type="email" name="company_email" value="<?php echo $company_email; ?>" required /></td>
		        </tr>

		        <tr valign="top">
		        <th scope="row"><?php _e('Company GSM','woocommerce-gateway-checkout-fi'); ?></th>
		        <td><input type="text" name="company_gsm" value="<?php echo $company_gsm; ?>" required /></td>
		        </tr>

		        <tr valign="top">
		        <th scope="row"><?php _e('Company Address','woocommerce-gateway-checkout-fi'); ?></th>
		        <td><input type="text" name="company_address" value="<?php echo $company_address; ?>" required /></td>
		        </tr>

		        <tr valign="top">
		        <th scope="row"><?php _e('Company URL','woocommerce-gateway-checkout-fi'); ?></th>
		        <td><input type="text" name="company_url" value="<?php echo $company_url; ?>" required /></td>
		        </tr>     

		         <tr valign="top">
		        <th scope="row"><?php _e('Company Info','woocommerce-gateway-checkout-fi'); ?></th>
		        <td><input type="text" name="company_info" value="<?php echo $company_info; ?>" required /></td>
		        </tr>     
		    </table>
		    
		    <?php submit_button('Submit'); ?>

		</form>
		</div>
		<?php }

		//  WC Vendors Customisations END

		/**
		 * Checkout extends default WooCommerce Payment Gateway class
		 */

		class WC_Gateway_Checkout_Fi extends WC_Payment_Gateway {

			public function __construct() {

				$this->id           = 'checkout_fi';
				$this->method_title = __( 'Checkout', 'woocommerce-gateway-checkout-fi' );
				$this->icon         = plugins_url( '/assets/images/checkout.png', __FILE__ );
				$this->has_fields   = false;

				add_action('admin_menu', array( $this, 'create_merchant_id_menu') );

				// Load the form fields.
				$this->init_form_fields();

				// Load the settings.
				$this->init_settings();

				// Define user set variables
				$this->title                		= $this->settings['title'];
				$this->description          		= $this->settings['description'];
				$this->api_id 		    			= $this->settings['api_id']; 
				$this->api_secret      				= $this->settings['api_secret'];
				$this->aggregate_merchant_id 		= $this->settings['aggregate_merchant_id'];
				$this->aggregate_merchant_secret 	= $this->settings['aggregate_merchant_secret'];
				$this->shop_merchant_id     		= $this->settings['shop_merchant_id'];
				$this->shop_merchant_secret    		= $this->settings['shop_merchant_secret'];
				$this->adult_entertainment  		= $this->settings['adult_entertainment'];

				// Actions
				add_action( 'woocommerce_api_wc_gateway_checkout_fi', array($this, 'check_checkout_fi_response') );
				add_action( 'valid_checkout_fi_payment', array($this, 'successful_request') );
				add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
				add_action( 'woocommerce_receipt_checkout_fi', array($this, 'receipt_page'));				
			}

			/**
			 * Initialise Gateway Settings Form Fields
			 */
			function init_form_fields() {

				$link = add_query_arg(
		            array(
		            	'page' => 'cif-createmerchant', 
						'shop_merchant_id' => 'shop_merchant_id', 
						'shop_merchant_secret' => 'shop_merchant_secret'
		            ),
		            admin_url('admin.php')
		        );

				$this->form_fields = array(
					'enabled' => array(
						'title' => __( 'Enable/Disable', 'woocommerce-gateway-checkout-fi' ),
						'type' => 'checkbox',
						'label' => __( 'Enable Checkout', 'woocommerce-gateway-checkout-fi' ),
						'default' => 'yes'
					),
					'title' => array(
						'title' => __( 'Title', 'woocommerce-gateway-checkout-fi' ),
						'type' => 'text',
						'description' => __( 'This controls the title which the user sees during checkout.', 'woocommerce-gateway-checkout-fi' ),
						'default' => __( 'Checkout', 'woocommerce-gateway-checkout-fi' )
					),
					'description' => array(
						'title' => __( 'Description', 'woocommerce-gateway-checkout-fi' ),
						'type' => 'textarea',
						'description' => __( 'This controls the description which the user sees during checkout.', 'woocommerce-gateway-checkout-fi' ),
						'default' => 'Pay via Checkout. You can pay with online bank account or with a credit card.',
					),
					'api_id' => array(
						'title' => __( 'API ID', 'woocommerce-gateway-checkout-fi' ),
						'type' => 'text',
						'description' => __( 'Please enter your Checkout merchant id; this is needed in order to connect to the payment gateway.', 'woocommerce-gateway-checkout-fi' ),
						'default' => ''
					),
					'api_secret' => array(
						'title' => __( 'API Secret', 'woocommerce-gateway-checkout-fi' ),
						'type' => 'password',
						'description' => __( 'Please enter your Checkout merchant secret; this is needed in order to take commission.', 'woocommerce-gateway-checkout-fi' ),
						'default' => ''
					),
					'aggregate_merchant_id' => array(
						'title' => __( 'Aggregate Merchant ID', 'woocommerce-gateway-checkout-fi' ),
						'type' => 'text',
						'description' => __( 'Please enter your Checkout aggregate merchant id; this is needed in order to connect to the payment gateway.', 'woocommerce-gateway-checkout-fi' ),
						'default' => ''
					),
					'aggregate_merchant_secret' => array(
						'title' => __( 'Aggregate Merchant Secret', 'woocommerce-gateway-checkout-fi' ),
						'type' => 'password',
						'description' => __( 'Please enter your Checkout aggregate merchant secret; this is needed in order to take payment.', 'woocommerce-gateway-checkout-fi' ),
						'default' => ''
					),
					'shop_merchant_id' => array(
						'title' => __( 'Shop Merchant ID', 'woocommerce-gateway-checkout-fi' ),
						'type' => 'text',
						'description' => __( 'Your marketplace merchant id; this is generated and needed in order to take payment. <a href="'.$link.'">Generate MarketPlace Merchant ID</a>', 'woocommerce-gateway-checkout-fi' ),
						'default' => ''
					),
					'shop_merchant_secret' => array(
						'title' => __( 'Shop Merchant Secret', 'woocommerce-gateway-checkout-fi' ),
						'type' => 'password',
						'description' => __( 'Please enter your Generated Checkout merchant secret; this is needed in order to take payment.', 'woocommerce-gateway-checkout-fi' ),
						'default' => ''
					),
					'adult_entertainment' => array(
						'title' => __( 'Adult Entertainment', 'woocommerce-gateway-checkout-fi' ),
						'type' => 'checkbox',
						'label' => __( 'Check this one if you are selling products that are categorized as \'adult entertainment\'.', 'woocommerce-gateway-checkout-fi' ),
						'default' => 'no'
					)		
				);

			}

			/**
			 * Admin Panel Options
			 * - Options for bits like 'title' and availability on a country-by-country basis
			 *
			 * @since 1.0.0
			 */
			public function admin_options() {

				?>
				<h3><?php _e('Checkout', 'woocommerce-gateway-checkout-fi'); ?></h3>
				<p><?php _e('Checkout works by sending user to Checkout portal to enter their payment information.', 'woocommerce-gateway-checkout-fi'); ?></p>
				<table class="form-table">

				<?php if ( 'EUR' == get_woocommerce_currency() ) { ?>

					<table class="form-table">
					<?php
					// Generate the HTML For the settings form.
					$this->generate_settings_html();
					?>
					</table><!--/.form-table-->

				<?php } else { ?>

					<div class="inline error"><p><strong><?php _e( 'Gateway Disabled', 'woothemes' ); ?></strong> <?php echo sprintf( __( 'Choose Euros as your store currency in <a href="%s">General Options</a> to enable this Gateway.', 'woocommerce' ), admin_url( 'admin.php?page=wc-settings&tab=general' ) ); ?></p></div>

				<?php } // End check currency ?>

				</table><!--/.form-table-->

				<?php

			} // End admin_options()

			/**
			 * Check if this gateway is enabled and using correct currency. Only EUR allowed.
			 */
			function is_available() {

				if ( $this->enabled=="yes" ) {

				// Currency check
				if ( ! in_array(get_woocommerce_currency(), array('EUR') ) ) return false;

				// Required fields check
				if ( ! $this->aggregate_merchant_id || ! $this->shop_merchant_id || ! $this->aggregate_merchant_secret ) return false;
					return true;
				}

				return false;
			}

			/**
			 * There are no payment fields for Checkout, but we want to show the description if set.
			 */
			function payment_fields() {
				if ( $this->description ) echo wpautop( wptexturize( $this->description ) );
			}

			/**
			 * Generate the Checkout button link
			 */
			public function generate_checkout_fi_form( $order_id ) {

				$order = wc_get_order( $order_id );
				$orderNumber = $order_id;
				$price = number_format( $order->get_total(), 2, '.', '' ) * 100;

				// Create new payment
				$payment = new Checkout_Fi($this->aggregate_merchant_id, $this->shop_merchant_id,  $this->aggregate_merchant_secret);

				// Order information
				$paymentData = array();
				$paymentData["stamp"] = substr(sha1($orderNumber . time()), 0, 20);
				$paymentData["reference"] = index_number($orderNumber);
				$paymentData["order_id"] = $orderNumber; 
				$paymentData["message"] = sprintf(__('Order %s', 'woocommerce-gateway-checkout-fi'), $order->get_order_number() );
				// $paymentData["return"] = add_query_arg('wc-api', 'wc_gateway_checkout_fi', add_query_arg(array('utm_nooverride' => '1'), $this->get_return_url( $order )));
				$return_args = array('wc-api' => 'wc_gateway_checkout_fi', 'key'=> $order->order_key, 'order' => $order->id ); 
				$paymentData["return"] = add_query_arg( $return_args, $this->get_return_url( $order ) );
				$paymentData["cancel"] = str_replace( '&amp;', '&', $order->get_cancel_order_url() );
				$paymentData["reject"] = $paymentData["cancel"];
				$paymentData["delayed"] = $this->get_return_url( $order );
				$paymentData["amount"] = $price; // price in cents
				$paymentData["delivery_date"] = date("Ymd");
				$paymentData["firstname"] = $order->billing_first_name;
				$paymentData["familyname"] = $order->billing_last_name;
				$paymentData["address"] = $order->billing_address_1;
				$paymentData["postcode"] = $order->billing_postcode;
				$paymentData["postoffice"] = $order->billing_city;
				$paymentData["content"] = $this->adult_entertainment == "no" ? '1' : '2';
				// WC Vendors Customisation 
				$paymentData['items']         = WCV_Vendors::get_vendor_dues_from_order($order, false);
				// WC Vendors Customisation END
				// XML mode with bank buttons
				$return = $payment->getCheckoutXML($paymentData); 

				$xml = simplexml_load_string($return, null, LIBXML_NOCDATA);

				// Create form with the return url to redirect client to Checkout payment page
				if( ! $xml ) {
					$post = $payment->generateCheckout($paymentData);
					$print_form = '<form action="https://payment.checkout.fi/" method="post" id="checkout_fi_payment_form">';

					foreach( $post as $field => $value ) {
						$print_form .= '<input type="hidden" name="'.$field.'" value="'.$value.'" />';
					}

					$print_form .= '<input type="submit" class="button-alt" id="submit_checkout_fi_payment_form" value="'.__('Pay via Checkout', 'woocommerce-gateway-checkout-fi').'" /> <a class="button cancel" href="'.esc_url( $order->get_cancel_order_url() ).'">'.__('Cancel order &amp; restore cart', 'woocommerce-gateway-checkout-fi').'</a>
						<script type="text/javascript">
							jQuery(function(){
								jQuery("body").block( {
									message: "<img src=\"'.esc_url( WC()->plugin_url() ).'/assets/images/ajax-loader.gif\" alt=\"Redirecting...\" style=\"float:left; margin-right: 10px;\" />'.__('Thank you for your order. We are now redirecting you to Checkout to make payment.', 'woocommerce-gateway-checkout-fi').'",
									overlayCSS: {
											background: "#fff",
											opacity: 0.6
									},
									css: {
										padding:        20,
										textAlign:      "center",
										color:          "#555",
										border:         "3px solid #aaa",
										backgroundColor:"#fff",
										cursor:         "wait",
										lineHeight:		"32px"
									}
								});
								jQuery(function(){setTimeout(function(){jQuery("#submit_checkout_fi_payment_form").click();}, 4000);});
							});
						</script>
					</form>';
					return $print_form;
				}

				// Create page with bank buttons
				elseif( $xml ) {
					$print_xml = "";

					foreach( $xml->payments->payment->banks as $bankX ) {

						foreach($bankX as $bank) {
							$print_xml .= '<div class="checkout-banks">';
							$print_xml .= '<form action="'.$bank['url'].'" method="post">';

							foreach( $bank as $key => $value ) {
								$print_xml .= '<input type="hidden" name="'.$key.'" value="'.$value.'" />';
							}

							$print_xml .= '<span><input type="image" src="'.$bank['icon'].'" /></span>';
							$print_xml .= '<div>'.$bank['name'].'</div>';
							$print_xml .= '</form>';
							$print_xml .= '</div>';
						}

					}

					return $print_xml;
				}

				// Create error message if payment generation failed
				else {
					return '<script type="text/javascript">
						jQuery(function(){
							jQuery("body").block( {
								message: "<p>' . __("There was something wrong when processing the payment to Checkout and no transaction was made. Please contact the shop administrator if this happens again.", "checkout") .'</p><a class=\"button\" href=\"'. get_bloginfo('wpurl') .'\">' . __("Return to shop frontpage.", "checkout") . '</a>",
								overlayCSS: {
									background: "#fff",
									opacity: 0.6
								},
								css: {
									padding:         20,
									textAlign:       "center",
									color:           "#555",
									border:          "3px solid #aaa",
									backgroundColor: "#fff",
									cursor:          "pointer",
									lineHeight:      "32px"
								}
							});
						});
					</script>';
				}
			} // End generate button link

			/**
			 * Process the payment and return the result
			 */
			function process_payment( $order_id ) {

				$order = wc_get_order( $order_id );

				WC()->cart->empty_cart();

				return array(
					'result'   => 'success',
					'redirect' => $order->get_checkout_payment_url( true ),
				);
			}

			/**
			 * receipt_page
			 */
			function receipt_page( $order ) {

				echo '<p>'.__('Thank you for your order, please choose your payment method.', 'woocommerce-gateway-checkout-fi').'</p>';
				echo $this->generate_checkout_fi_form( $order );
			}

			/**
			 * Check for valid response
			 */
			function check_checkout_fi_response() {
				$payment = new Checkout_Fi($this->aggregate_merchant_id, $this->shop_merchant_id, $this->aggregate_merchant_secret);
				$order_id = isset( $GLOBALS['wp']->query_vars['order-received'] ) ? absint( $GLOBALS['wp']->query_vars['order-received'] ) : 0;

				// If we got proper answer validate the MAC
				if( isset( $_GET['MAC'] ) ) {

					if( $payment->validateCheckout( $_GET, $order_id ) ) {

						if( $payment->isPaid() ) {

							$orderdetails = array("orderid" => $order_id, "orderreference" => $_GET['REFERENCE'], "ordercheckout" => $_GET['PAYMENT']);

							// If payment is pending
							if( $_GET['STATUS'] == "5" ) {
								$order = wc_get_order( $order_id );
								$order->update_status('on-hold', sprintf(__('Order payment via Checkout is pending confirmation.', 'woocommerce-gateway-checkout-fi')));
							}

							// If payment is done
							else {
								do_action("valid_checkout_fi_payment", $orderdetails);
								$order = wc_get_order( $order_id );
								wp_redirect( $this->get_return_url( $order ) );
								exit;
							}
						}

						// Payment failed
						else {
							$order = $order = wc_get_order( $order_id );
							$order->update_status('failed', sprintf(__('Checkout payment failed. Payment Checkout archive record is %s.', 'woocommerce-gateway-checkout-fi'), strtolower($_GET['PAYMENT']) ) );
						}
					}

					// Payment failed and there was possibly attempt to bypass actual payment
					else {
						$order = $order = wc_get_order( $order_id );
						$order->update_status('failed', sprintf(__('Checkout payment failed. Possible hacking attempt. Payment Checkout archive record is %s.', 'woocommerce-gateway-checkout-fi'), strtolower($_GET['PAYMENT']) ) );
					}
				}

			}

			/**
			 * Successful Payment!
			 */
			function successful_request( $orderdetails ) {

				$order = wc_get_order( $orderdetails['orderid'] );
				$order->payment_complete();
				$order->add_order_note( sprintf(__('Checkout payment complete. Payment reference is %s. Payment Checkout archive record is %s.', 'woocommerce-gateway-checkout-fi'), strtolower($orderdetails['orderreference']), strtolower($orderdetails['ordercheckout']) ) );
			}

		} // End class


		/**
		 * Function for generating correctly formed index number (viitenumero)
		 */
		function index_number( $order_number ) {

			if( strlen($order_number) == 1 ) $order_number = "00" . $order_number;

			if( strlen( $order_number ) == 2 ) $order_number = "0" . $order_number;

			if( strlen( $order_number ) > 19 ) return 0;

			$factors = array( '7', '3', '1', '7', '3', '1', '7', '3', '1', '7', '3', '1', '7', '3', '1', '7', '3', '1', '7' );
			$checksum = 0;
			$j = 0;
			$tmp = $order_number;
			settype( $tmp, "string" );

			for( $i = strlen( $tmp ) - 1; $i > -1; $i-- ) {
				$checksum = $checksum + $factors[$j++] * intval(substr($tmp, $i, 1));
			}

			$checksum = ceil( $checksum / 10 ) * 10 - $checksum;
			$index = "$order_number$checksum";

			return $index;
		}

		/**
		 * Add the gateway to WooCommerce
		 */
		function add_checkout_fi_gateway( $methods ) {
			$methods[] = 'WC_Gateway_Checkout_Fi'; return $methods;
		}

		add_filter('woocommerce_payment_gateways', 'add_checkout_fi_gateway' );

	}

}
