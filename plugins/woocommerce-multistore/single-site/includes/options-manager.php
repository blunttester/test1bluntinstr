<?php
/**
 * WooMultistore single site init
 */

class WOO_MSTORE_OPTIONS_MANAGER {

	/**
	 * Hold default options or settings.
	 *
	 * @var $options
	 */
	private $defaults = array(
		// Global settings.
		'synchronize-stock'                                		  => 'no',
		'synchronize-trash'                                		  => 'no',
		'publish-capability'                               		  => 'administrator',
		'sync-all-metadata'                                		  => 'no',
		'sync-custom-taxonomy'                             		  => 'no',
		'sync-custom-metadata'                             		  => 'no',
		'sequential-order-numbers'                         		  => 'no',
		'use-global-image'                         		   		  => 'no',
		'sync-by-sku'                         		   	   		  => 'no',

		// Override global settings on each site
		'override__synchronize-stock' 							  => 'no',

		// Site specific settings.
		'child_inherit_changes_fields_control__status'     		  => 'yes',
		'child_inherit_changes_fields_control__title'      		  => 'yes',
		'child_inherit_changes_fields_control__description' 	  => 'yes',
		'child_inherit_changes_fields_control__short_description' => 'yes',
		'child_inherit_changes_fields_control__price'      		  => 'yes',
		'child_inherit_changes_fields_control__sale_price'        => 'yes',
		'child_inherit_changes_fields_control__product_cat' 	  => 'yes',
		'child_inherit_changes_fields_control__product_tag' 	  => 'yes',
		'child_inherit_changes_fields_control__product_image' 	  => 'yes',
		'child_inherit_changes_fields_control__product_gallery'   => 'yes',
		'child_inherit_changes_fields_control__variations' 		  => 'yes',
		'child_inherit_changes_fields_control__attributes' 		  => 'yes',
		'child_inherit_changes_fields_control__category_changes'  => 'yes',
		'child_inherit_changes_fields_control__reviews'    		  => 'yes',
		'child_inherit_changes_fields_control__slug'       		  => 'yes',
		'child_inherit_changes_fields_control__purchase_note' 	  => 'yes',
		'child_inherit_changes_fields_control__upsell'     	      => 'no',
		'child_inherit_changes_fields_control__cross_sells' 	  => 'no',
		'child_inherit_changes_fields_control__sku'        		  => 'yes',
		'child_inherit_changes_fields_control__variations_sku' 	  => 'yes',
		'child_inherit_changes_fields_control__featured'   		  => 'yes',
		'child_inherit_changes_fields_control__shipping_class' 	  => 'yes',
	);

	/**
	 * Rules for options.
	 *
	 * @todo implement the rules method.
	 * @var array
	 */
	private $rules = array();

	/**
	 * Holds current site settings.
	 *
	 * @var array
	 */
	private $options = array();

	/**
	 * __construct
	 *
	 * @return void
	 */
	public function __construct() {
		$this->load_options();
	}

	/**
	 * Retrieve current site settings
	 *
	 * Fall back to defaults if options is not defined.
	 *
	 * @param mixed $option_name
	 * @param mixed $default
	 * @return string
	 */
	public function get( $option_name, $get_all = false ) {
		if ( $get_all === true ) {
			return $this->options;
		}

		if ( isset( $this->options[ $option_name ] ) ) {
			return $this->options[ $option_name ];
		}

		if ( isset( $this->defaults[ $option_name ] ) ) {
			return $this->defaults[ $option_name ];
		}

		return null;
	}

	/**
	 * Get default settings.
	 *
	 * @return array
	 */
	public function get_defaults() {
		return $this->defaults;
	}

	/**
	 * Update site settings.
	 *
	 * @param array $options
	 * @return bool
	 */
	public function update( $options ) {
		$this->set_props( $options );
		return update_option( 'woonet_options', $this->options );
	}

	/**
	 * Load current site options.
	 *
	 * @return void
	 */
	public function load_options() {
		$this->options = get_option( 'woonet_options' );
	}

	/**
	 * Set option value from array of options
	 *
	 * @param mixed $options
	 * @return void
	 */
	private function set_props( $options ) {
		if ( is_array( $options ) ) {
			foreach ( $options as $key => $value ) {
				if ( array_key_exists( $key, $this->defaults ) ) {
					$this->options[ sanitize_key( $key ) ] = sanitize_key( $value );
				}
			}
		}
	}
}
