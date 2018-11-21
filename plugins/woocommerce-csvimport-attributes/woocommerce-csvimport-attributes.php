<?php
/*
 	* Plugin Name:			Woocommerce CSV import attributes
 	* Plugin URI:			https://allaerd.org/shop/woocommerce-import-attributes/
 	* Description:			Import regular and manual attributes into Woocommerce

 	* Author:				Allaerd Mensonides
 	* Author URI:			https://allaerd.org
 	
 	* Version:				3.0.1
	* Requires at least: 	4.0
	* Tested up to: 		4.2
	
	* Text Domain: woocsv
	* Domain Path: /i18n/languages/
	 
	This plugin is part of the free woocommerce csv importer. It must be used in conjunction with it.
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// hook into woocommerce csv import 
add_action('woocsv_after_init', 'woocsv_import_attributes_init');

//old hook into woocommerce csv import 
add_action('woocsvAfterInit', 'woocsv_import_attributes_init_old');

function woocsv_import_attributes_init() {
	new woocsv_import_attributes();
}

function woocsv_import_attributes_init_old() {
	include_once 'old/woocommerce-csvimport-attributes.php';
	$Woocommerce_CSVimport_Attributes = new Woocommerce_CSVimport_Attributes();
}

class woocsv_import_attributes {

	public $version;
	public $name;
	public $remote_slug;
	public $url;

	public function __construct()
	{
		global $woocsv_import;
		
		//register plugin in importer
		$this->version  = '3.0.1';
		$this->name = 'Import attributes';
		$this->url = 'https://allaerd.org/shop/woocommerce-import-attributes/';
		$this->remote_slug = 'woocsv-attributes';
		$woocsv_import->addons[$this->remote_slug] = $this;
		
		//init hooks
		$this->hooks ();
		
		//populate the fields for the dropdowns in the header section
		$this->fields ();
	}
	
	/* init */
	public function hooks () {
		/* save regular attributes */
		add_action('woocsv_product_after_body_save' , array( $this, 'save_attributes' ),100 );
		
		/* save manual attributes */
		add_action('woocsv_product_after_body_save' , array( $this, 'save_manual_attributes' ),105 );

		/* settings */
		add_action( 'admin_init', array( $this,'settings' ));
		
		/* docs */
		add_action ('woocsv_documentation',array ( $this, 'content' ) );
	}

	/* settings */
	function settings () {
		add_settings_field('woocsv_manual_attributes', 'Manual Attributes', array($this,'manual_attributes'), 'woocsv-settings','woocsv-settings');
		register_setting( 'woocsv-settings', 'woocsv_manual_attributes', array($this,'options_validate') );
	}
	
	function manual_attributes () {
		$custom_fields = get_option('woocsv_manual_attributes');
		echo '<input type="text" class="large-text" id="woocsv_manual_attributes" name="woocsv_manual_attributes" placeholder="field1,field2,field3" value="'.$custom_fields.'">';
		echo '<p class="description">Add your manual attributes as a comma separated list.</p>';
	}
	
	// validation
	function options_validate($input) {
		//no validation yet
		return $input;
	}

	public function fields()
	{
		global $wpdb, $woocsv_import;

		//get the attributes
		$attributes = $wpdb->get_results ("select * from {$wpdb->prefix}woocommerce_attribute_taxonomies");
		if ($attributes) {
			foreach ($attributes as $attribute) {
				//add them to the fields list
				$woocsv_import->fields[] = 'pa_'.$attribute->attribute_name;
			}
		}

		//attribute field
		$woocsv_import->fields[] = 'attributes';
		
		//get the manual attributes
		$temp_manual_attributes = get_option( 'woocsv_manual_attributes' );
		if ( $temp_manual_attributes ) {
			$manual_attributes = explode(',', $temp_manual_attributes);
			foreach ($manual_attributes as $manual_attribute) {
				$woocsv_import->fields[] = 'ma_'.sanitize_title($manual_attribute);
			}
		}
		
		//manual_attributes
		$woocsv_import->fields[] = 'manual_attributes';
	}

	public function save_manual_attributes () {
		global $woocsv_import,$woocsv_product;
		
		//check if there is a attributes column
		$key = array_search('manual_attributes', $woocsv_import->header );
		//return if there are no manual attributes
		if ( $key === FALSE )  return;
		
		//explode attrbiutes
		$attributes = explode('|', $woocsv_product->raw_data[$key]);

		if ( empty ( $attributes[0] ) ) {
			$woocsv_import->import_log[] = 'product has no manual attributes';
			return;
		}
			
		$product_attributes = $woocsv_product->meta['_product_attributes'];
		
		$pos = 0;	
		//set the postition to the right value if variations allready toke some places
		if ($product_attributes) {
			foreach ($product_attributes as $x) {
				if ($x['position'] >= $pos) $pos = $x['position'];
			}
		}
			
		//loop through the attributes
		foreach ($attributes as $attribute) {

			//get the values for visible and is variation else assume it's 1
			list($attribute, $is_visible) = array_pad(explode('->', $attribute), 2, 1);			
			
			$manual_attribute = sanitize_title ( $attribute );
			//check if attribute value is in the header:  size -> pa_size
			$key = array_search('ma_'. $manual_attribute, $woocsv_import->header );
			
			$values = '';
		
			if ( $key !== FALSE ){ 
				$values = $woocsv_product->raw_data[$key];
			}
				
			//retreive values and merge them with the old ones
			if ( !empty ( $product_attributes[$manual_attribute]['value'] ) ){
				$values = implode('|', array_merge ( explode ('|', $product_attributes[$manual_attribute]['value'] ) , explode('|', $values) ) );
			}

			//fill in the array
			$product_attributes[ $manual_attribute ] = array
			(
				'name' => $attribute,
				'value' => $values,
				'position' => "$pos",
				'is_visible' => (int)$is_visible,
				'is_variation' => 0,
				'is_taxonomy' => 0,
			);

			//increase the position for the next one
			$pos ++;
		}
		
		//save the attributes
		$woocsv_product->meta['_product_attributes'] =  $product_attributes;
		
	}

	public function save_attributes() {
		global $wpdb, $woocsv_product, $woocsv_import;

		$product_attributes = '';

		//check if there is a attributes column
		$key = array_search('attributes', $woocsv_import->header );
		
		//check if it has values
		if ( $key !== FALSE )  {
			//split the attributes if there are multiple. att1|att2|att3
			
			//only get the attributes if they are in fact there
			if (isset ($woocsv_product->raw_data[$key])) {
				$attributes = explode('|', $woocsv_product->raw_data[$key]);				
			} else {
				$attributes = array();
			}


			//check if merging is enabled.
			if ( $woocsv_import->options['merge_products'] == 1 ) {
				if ( isset( $woocsv_product->meta['_product_attributes'] ) ) {
					$product_attributes = $woocsv_product->meta['_product_attributes'];
				} else {
					$product_attributes = '';
				}
			} else { 
				// no merging
				$product_attributes = '';
				//clear relation between term and product for attributes
				$temp_attributes = $wpdb->get_col ("select attribute_name from {$wpdb->prefix}woocommerce_attribute_taxonomies");
				if ($temp_attributes) {
					foreach ($temp_attributes as $a)
						wp_set_object_terms( $woocsv_product->body['ID'], NULL, 'pa_'.$a , false );
				}
			}

			$pos = 0;

			//set the postition to the right value if variations allready toke some places
			if ($product_attributes) {
				foreach ($product_attributes as $x) {
					if ($x['position'] > $pos) $pos = $x['position'];
				}
			}

			//loop through the attributes
			foreach ($attributes as $attribute) {
				
				//trim it to be sure
				//$attribute = trim($attribute);
				
				//get the values for visible and is variation else assume it's 1
				list($attribute, $is_visible) = array_pad(explode('->', $attribute), 2, 1);

				//fill in the array
				$product_attributes['pa_'.$attribute] = array
				(
					'name' => 'pa_'.$attribute,
					'value' => '',
					'position' => "$pos",
					'is_visible' => (int)$is_visible,
					'is_variation' => 0,
					'is_taxonomy' => 1,
				);

				//increase the position for the next one
				$pos ++;

				//now get the values of the product attribute
				$key = null;

				//check if attribute value is in the header:  size -> pa_size
				$key = array_search('pa_'. sanitize_title( $attribute ), $woocsv_import->header );

				if ( $key !== FALSE ){
					//check if the attribute has values and if there are multiple like value1|value2|value3
					if ( $woocsv_product->raw_data[$key] ) {
						$terms = explode('|', $woocsv_product->raw_data[$key]);
					} else {
						$terms = '';
					}

					if (!empty($terms)) {
						//link the values of the attrbutes to the product
						foreach ($terms as $category) {

							/// @since 3.0.0. add hierarchy of attributes
							$cats = explode( '|', $category );
							foreach ($cats as $cat) {
								$cat_taxs = explode( '->', $cat );
									
								$parent = 0;

								foreach ( $cat_taxs as $cat_tax) {
									
									$new_cat = term_exists( $cat_tax, 'pa_'.$attribute, $parent );
									
									if ( ! is_array( $new_cat ) ) {
										$new_cat = wp_insert_term( $cat_tax, 'pa_'.$attribute, array( 'slug' => $cat_tax, 'parent'=> $parent) );
									}
									if (!is_wp_error($new_cat)) {
										$parent = $new_cat['term_id'];
									}
									
									$term_taxonomy_ids = wp_set_object_terms( $woocsv_product->body['ID'],(int)$new_cat['term_id'],'pa_'.$attribute, true );
									if ( is_wp_error( $term_taxonomy_ids ) ) {
										$woocsv_import->import_log[] = "Attribute: $attribute failed with value $cat";
									} else {
										$woocsv_import->import_log[] = "Attribute: $attribute added to product with value $cat";
									}
								}
							}
						}
					} else {
						// or unlink them if there are none
						wp_set_object_terms( $woocsv_product->body['ID'],NULL,'pa_'.$attribute, FALSE );
					}
				}
				//save the attributes
				$woocsv_product->meta['_product_attributes'] =  $product_attributes;
			}
		}
	}


	function content()
	{
		global $wpdb, $woocommerce;
		$attributes = $wpdb->get_results ("select * from {$wpdb->prefix}woocommerce_attribute_taxonomies");

		//create attribute url
		if ( str_replace('.', '', $woocommerce->version) >= 210)
			$attr_url = get_admin_url() . 'edit.php?post_type=product&page=product_attributes';
		else
			$attr_url = get_admin_url() . 'edit.php?post_type=product&page=woocommerce_attributes';
?>

	<h2>Import product attributes</h2>
	<p class="description">
		There are three kind of attributes, regular attributes, manual attributes attributes used for variations. But attributes can only be one type!.
	</p>
		<h4>Regular Attributes</h4> 
		Regular Attributes are created up-front <a href="<?php echo $attr_url; ?>">here</a>. And can be imported using the <code>pa_</code> prefix and placing the attribute slug in the attributes column. It is very important that you use the slug of the attributes in all cases! 
		<ul>
		<li>If you want multiple attributes attached to your product, you list them in the attributes columns pipe separated <code>|</code> like this: <code>color|size|brand</code>.
		 <li>If you want to create a hierarchy you can use <code> -> </code>. Example: <code>cloths->pants|cloths->t-shirts</code>.
		 <li>If you want to make an attribute invisible you add <code>->0</code> to the attribute in the attributes_column <code>color->0|size|brand</code>.
		 </ul>
		<h4>Example:</h4>
		<code>
		sku,post_title,attributes,pa_color,pa_size,pa_brand</br>
		sku1,product 1,color|size,red,medium,</br>
		sku2,product 2,size|brand,,large,nike</br>
		sku3,product 3,color->0|size|brand->0,red,small|adidas</br>
		sku4,product 4,color|size,red,man->large|man->medium|woman->large|woman->medium,
		</code>
		
		<h4>Manual attributes</h4>
		Manual attributes are not created up-front but have to be listed in the <a href="<?php echo get_admin_url().'admin.php?page=woocsv-settings';?>">settings page</a> before you import They can be imported using the <code>ma_</code> prefix and placing the manual attributes in the <code>manual_attributes</code> column.

		<h4>Example:</h4>
		<code>
		sku,post_title,manual_attributes,ma_color,ma_size</br>
		sku1,product 1,color,red,,</br>
		sku2,product 2,color->0,red,,</br>
		sku3,product 3,color,red|green|blue,,</br>
		sku4,product 4,color|size,red,large<br/>
		sku5,product 5,color|size,red|green|blue,large|medium|small<br/>
		</code>
		
		<h4>Attributes used by variations</h4>
		These attributes can be imported using the <a href="<?php echo get_admin_url().'admin.php?page=woocsv-addons';?>">Variable products</a> add-on.

	<?php
	}
}