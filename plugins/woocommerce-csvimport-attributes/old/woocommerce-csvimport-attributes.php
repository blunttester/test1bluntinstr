<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class Woocommerce_CSVimport_Attributes {

		public $version;
		public $name;
		public $remote_slug;
		public $url;
		public $description;

		public function __construct()
		{
			global $woocsvImport;
			$this->version  = '3.0.0';
			$this->name = 'Import attributes';
			$this->url = 'https://allaerd.org/shop/woocommerce-import-attributes/';
			$this->remote_slug = 'woocommerce-import-attributes';

			$woocsvImport->addons[$this->remote_slug] = $this;

			//add the hook to add attributes data!
			add_action('woocsv_product_after_body_save' , array( $this, 'saveAttributes' ),100 );

			//create a submenu for the add-on
			add_action('admin_menu', array($this, 'adminMenu'));

			//populate the fields for the dropdowns in the header section
			$this->addToFields();
		}

		public function adminMenu()
		{
			add_submenu_page( 'woocsv_import', 'Attributes', 'Attributes', 'manage_options', 'woocsvAttributes', array($this, 'addToAdmin'));
		}

		public function addToFields()
		{
			global $wpdb, $woocsvImport;

			//get the attributes
			$attributes = $wpdb->get_results ("select * from {$wpdb->prefix}woocommerce_attribute_taxonomies");
			if ($attributes) {
				foreach ($attributes as $attribute) {
					//add them to the fields list
					$woocsvImport->fields[] = 'pa_'.$attribute->attribute_name;
				}
			}

			//attribute field
			$woocsvImport->fields[] = 'attributes';
		}


		public function saveAttributes() {
			global $wpdb, $wooProduct, $woocsvImport;

			$product_attributes = '';

			//check if there is a attributes column

			$key = array_search('attributes', $woocsvImport->header );

			//check if it has values
			if ( $key !== FALSE )  {

				//split the attributes if there are multiple. att1|att2|att3
				$attributes = explode('|', $wooProduct->rawData[$key]);

				//check if merging is enabled.
				if ( $woocsvImport->options['merge_products'] == 1 ) {
					if ( isset( $wooProduct->meta['_product_attributes'] ) ) {
						$product_attributes = $wooProduct->meta['_product_attributes'];
					} else {
						$product_attributes = '';
					}

				} else {
					$product_attributes = '';
					//clear relation between term and product for attributes
					$temp_attributes = $wpdb->get_col ("select attribute_name from {$wpdb->prefix}woocommerce_attribute_taxonomies");
					if ($temp_attributes) {
						foreach ($temp_attributes as $a)
							wp_set_object_terms( $wooProduct->body['ID'], NULL, 'pa_'.$a , false );
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
					$key = array_search('pa_'.$attribute, $woocsvImport->header );

					if ( $key !== FALSE ){
						//check if the attribute has values and if there are multiple like value1|value2|value3
						if ( $wooProduct->rawData[$key] ) {
							$terms = explode('|', $wooProduct->rawData[$key]);
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


										wp_set_object_terms( $wooProduct->body['ID'],(int)$new_cat['term_id'],'pa_'.$attribute, true );
									}
								}
							}
						} else {
							// or unlink them if there are none
							wp_set_object_terms( $wooProduct->body['ID'],NULL,'pa_'.$attribute, FALSE );
						}
					}
					//save the attributes
					$wooProduct->meta['_product_attributes'] =  $product_attributes;
				}
			}
		}


		function addToAdmin()
		{
			global $wpdb, $woocommerce;
			$attributes = $wpdb->get_results ("select * from {$wpdb->prefix}woocommerce_attribute_taxonomies");

			//create attribute url
			if ( str_replace('.', '', $woocommerce->version) >= 210)
				$attr_url = get_admin_url() . 'edit.php?post_type=product&page=product_attributes';
			else
				$attr_url = get_admin_url() . 'edit.php?post_type=product&page=woocommerce_attributes';
?>

		<div class="wrap">
						<?php 
			$class = "error";
			$message = "You have an old version of the woocommerce CSV importer installed! Not all new functionality will work, please update to version 3.0.0 or higher. ";
		    echo"<div class=\"$class\"> <p>$message</p></div>";
		    ?>
		<div id="icon-themes" class="icon32"><br></div>
		<h2 class="nav-tab-wrapper">
			<a href="#" class="nav-tab nav-tab-active">Import product attributes</a>
		</h2>
		<?php if (empty($attributes) ) : ?>
			<div class="error"><p>There are no attributes yet. Please goto the  <a href="<?php echo $attr_url;?>">attribute screen</a> to create them.</p>
			</div>
		<?php else: ?>
			<h2>You have the following attributes:</h2>
			<ul>
			<?php foreach ($attributes as $attribute)
					echo '<li>Attribute: <b><i>'.$attribute->attribute_name.',</i></b> use this header tag in your CSV: <code>pa_'. $attribute->attribute_name.' </code></li>';
?>
			</ol>
			<p>
				Goto the  <a href="<?php echo $attr_url;?>">attribute screen</a> to create more!
			</p>
		<?php endif; ?>
		<h2>Usage</h2>
		<p>
			There are several new fields available for you when you create a header:
			<h4>attributes</h4>
			In here you fill in the attributes you want to use. You can have multiple and have them visible or not.
			<code>color->1|size->0</code> Buy default all attributes are visible. So <code>color|size</code> would be enough.
			Attributes can have multiple values, use the pipe to separate them. <code>red|green|blue</code>
			The most basic product could look like:<br/>
			<code>
			sku;post_title;attributes;pa_color;pa_size;pa_brand</br>
			1;product 1;color|size;red;medium;</br>
			2;product 2;size|brand;;large;nike</br>
			3;product 3;color->0|size|brand->0;red;small|adidas</br>
			</code>
		</p>
		<p>If you are not sure how to import attributes products, read the <a target="_blank" href="http://allaerd.org/documentation/">documentation</a> Or you can try the example <a href="<?php echo plugin_dir_url(__file__);?>/example.csv">CSV</a></p>
		</div>
		<?php


		}
}