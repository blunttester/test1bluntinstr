<?php
/*
 * Checkout API
 */
class Checkout_Fi {
	private $version="0001";
	private $language="FI";
	private $country="FIN";
	private $currency="EUR";
	private $device="1";
	private $content="1";
	private $type="0";
	private $algorithm="3";
	private $aggregate_merchant_id="";
	private $shop_merchant_id=""; 
	private $reseller_secret="";
	private $stamp=0;
	private $amount=0;
	private $reference="";
	private $message="";
	private $return="";
	private $cancel="";
	private $reject="";
	private $delayed="";
	private $delivery_date="";
	private $firstname="";
	private $familyname="";
	private $address="";
	private $postcode="";
	private $postoffice="";
	private $status="";
	private $order_id=0;

	public function __construct( $aggregate_merchant_id, $shop_merchant_id, $aggregate_merchant_secret ) {

		$this->aggregate_merchant_id = $aggregate_merchant_id; // reseller merchant id
		$this->shop_merchant_id = $shop_merchant_id; //shop merchant id
		$this->aggregate_merchant_secret = $aggregate_merchant_secret; // security key (about 80 chars)
	}

	/*
 	 * generates MAC and prepares values for creating payment
	 */
		public function generateCheckout( $data ) {

		foreach ( $data as $key => $value ) {
			$this->{$key}=$value;
		}

		$total = 0;
		foreach ( $this->items as $key => $receivers ) {
			foreach ($receivers as $receiver_key => $receiver_value) {
				$total += $receiver_value['total'];
			}
		}

		$xml = '<?xml version="1.0"?>
					<checkout xmlns="http://checkout.fi/request">
					    <request type="aggregator" test="false">
					        <aggregator>'.$this->aggregate_merchant_id.'</aggregator>
					        <version>0002</version>
					        <stamp>'.$this->stamp.'</stamp>
					        <reference>' . $this->reference . '</reference>
					        <description>' . $this->message . '</description>
					        <device>' . $this->device . '</device>
					        <content>' . $this->content . '</content>
					        <type>'. $this->type . '</type>
					        <algorithm>'. $this->algorithm . '</algorithm>
					        <currency>'. $this->currency . '</currency>
					        <items>';    
						   	
       	foreach ($this->getXMLItems() as $item) { 

	       	$xml .= '<item>
	       				<code>'.$item['code'].'</code>
	       				<description>'.$item['description'].'</description>
	       				<price currency="EUR">'.$item['price'].'</price>
	       				<merchant>'.$item['merchant'].'</merchant> 
	       				<control>['.$item['control'].']</control> 
	        		</item>'; 

       	} 
       	

		$xml .='
            <amount currency="EUR">'. $total * 100 . '</amount>
       </items>
       <buyer>
          <country>'. $this->country . '</country>
          <language>FI</language>
      </buyer>
      <delivery>
	      <date>' . date('Ymd') . '</date>
	      <company vatid=""></company>
          <firstname>'. $this->firstname . '</firstname>
          <familyname>'. $this->familyname . '</familyname>
          <address><![CDATA['.$this->address.']]></address>
          <postalcode>'. $this->postcode . '</postalcode>
          <postaloffice></postaloffice>
          <country>'. $this->country . '</country>
          <email></email>
          <gsm></gsm>
          <language></language>
      </delivery>
      <control type="default">
<!-- @type=default = only online or offline rule is executed -->
            <return>'. htmlentities($this->return) . '</return>
            <reject>'. htmlentities($this->reject) . '</reject>
            <cancel>'. htmlentities($this->cancel) . '</cancel>
      </control>
    </request>
</checkout>';
	
		// echo "<pre>"; 
		// print_r( $xml ); 
		// echo "</pre>"; 
		// wp_die('Dying...'); 

		$password = $this->aggregate_merchant_secret;
		$xml  = base64_encode( $xml );
		$mac  = strtoupper( md5( "{$xml}+{$password}" ) );

		$post = array( 'CHECKOUT_XML' => $xml, 'CHECKOUT_MAC' => $mac );

		return $post;
	}

	public function getXMLItems() { 

		$xml_items = array(); 

		foreach ( $this->items as $key => $receivers ) {

			foreach ($receivers as $receiver_key => $receiver_value) {

				$item = array(); 

				$wcv_merchant_id = get_user_meta( $receiver_value['vendor_id'], 'wcv_merchant_id', true );
				$product_id = $receiver_value['product_id']; 
				$product = new WC_Product($product_id); 

				if ( !$wcv_merchant_id ) $wcv_merchant_id = $this->shop_merchant_id;
				
				$qty = $receiver_value['qty'] ? $receiver_value['qty'] : 1; 

				if ( empty( $receiver_value['total'] ) ) continue;

				$exists = $this->check_item($xml_items, $product_id); 

				// Set to commission for the store if required 
				$control = ( $wcv_merchant_id == $this->shop_merchant_id ) ? '{"a":' . $receiver_value['total'] * 100 . ',"m":"' . $wcv_merchant_id . '","d":"marketplace commission”}' : ''; 

				// Take the shop commission only 
				// if ( $wcv_merchant_id == $this->shop_merchant_id ) { 
				$item['code'] = $product_id; 
				$item['description'] = $product->get_title(); 
				$item['price'] = ($product->get_price_including_tax() * $qty ) *  100 ; 
				$item['merchant'] = get_user_meta( $product->post->post_author, 'wcv_merchant_id', true );
				$item['control'] = $control;
				$xml_items[] = $item; 
				// } 
			}
		}

		// echo 'XML Items'; 
		// echo "<pre>"; 
		// print_r( $xml_items ); 
		// echo "</pre>"; 

		// wp_die('Dying...'); 
		return $xml_items; 

	}


	private function check_item($items, $id) { 

		for ($i=0; $i < sizeof($items) ; $i++) { 
			if ($items[$i]['code'] == $id) return $i; 
		}

	}

	/*
	 * returns payment information in XML
	 */
	public function getCheckoutXML($data) {
		$this->device="10";
		// $this->sendPost($this->generateCheckout($data));
		return $this->postData($this->generateCheckout($data)); 
	}

	function postData($postData)
	{

	 $context = stream_context_create(array(
	    'http' => array(
	      'method' => 'POST',
	      'header' => 'Content-Type: application/x-www-form-urlencoded',
	      'content' => http_build_query($postData)
	    )
	  ));

	  $response_xml = file_get_contents('https://payment.checkout.fi', false, $context);

	  return $response_xml; 

	}

	public function validateCheckout($data, $order_id) {

		$keys=array("VERSION","STAMP","REFERENCE","PAYMENT","STATUS","ALGORITHM","MAC");

		$order = SV_WC_Gateway_Checkout_Fi_Plugin_Compatibility::wc_get_order( $order_id );

		$mac="";

		foreach($data as $key => $value) {
			if(!in_array($key,$keys)) continue;

			$key=strtolower($key);
			if($key!="mac") $this->{$key}=$value;
			else $mac=$value;
		}

		$generatedMac = strtoupper( hash_hmac( "sha256", "{$data['VERSION']}&{$data['STAMP']}&{$data['REFERENCE']}&{$data['PAYMENT']}&{$data['STATUS']}&{$data['ALGORITHM']}", $this->aggregate_merchant_secret ) );		
		
		if ( $mac==$generatedMac) {
			$order->add_order_note('Order Passed Checks.');
			return true;
		} else { 
			$order->add_order_note('Order Failed Checks but completing for testing purposes.');
			return false; 
		}

	}

	public function isPaid() {
		if($this->status!="") {
			if(in_array($this->status,array(2,4,5,6,7,8,9,10)))
				return true;
		}
		return false;
	}
}  // class Checkout
