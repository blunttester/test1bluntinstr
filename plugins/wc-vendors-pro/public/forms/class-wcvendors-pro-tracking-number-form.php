<?php
/**
 * The WCVendors Pro Tracking Number Form Class
 *
 * This is the tracking number form class
 *
 * @package    WCVendors_Pro
 * @subpackage WCVendors_Pro/public/forms
 */

/**
 * Tacking number form
 *
 * @version 1.7.10
 * @since   1.0.0
 */
class WCVendors_Pro_Tracking_Number_Form {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @var      string $wcvendors_pro The ID of this plugin.
	 */
	private $wcvendors_pro;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @var      string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * Is the plugin in debug mode
	 *
	 * @since    1.0.0
	 * @var      bool $debug plugin is in debug mode
	 */
	private $debug;

	/**
	 * Is the plugin base directory
	 *
	 * @since    1.0.0
	 * @var      string $base_dir string path for the plugin directory
	 */
	private $base_dir;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 *
	 * @param string $wcvendors_pro The name of the plugin.
	 * @param string $version       The version of this plugin.
	 * @param bool   $debug         Whether debug is enabled or not.
	 */
	public function __construct( $wcvendors_pro, $version, $debug ) {

		$this->wcvendors_pro = $wcvendors_pro;
		$this->version       = $version;
		$this->debug         = $debug;
		$this->base_dir      = plugin_dir_path( dirname( __FILE__ ) );

	}

	/**
	 *  Output required form data
	 *
	 * @since    1.0.0
	 *
	 * @param int    $order_id  The order d for this meta if any.
	 * @param string $button_text The button text.
	 */
	public static function form_data( $order_id, $button_text ) {

		WCVendors_Pro_Form_Helper::input(
			apply_filters(
				'wcv_tracking_number_order_id',
				array(
					'type'  => 'hidden',
					'id'    => '_wcv_order_id',
					'value' => $order_id,
				)
			)
		);

		wp_nonce_field( 'wcv-add-tracking-number', 'wcv_add_tracking_number' );

		self::save_button( $button_text );

	}

	/**
	 *  Output tracking number.
	 *
	 * @param string $tracking_number The tracking number.
	 * @param int    $order_id        The order id.
	 *
	 * @version 1.7.7
	 * @since   1.0.0
	 */
	public static function tracking_number( $tracking_number, $order_id ) {

		// Tracking number.
		WCVendors_Pro_Form_Helper::input(
			apply_filters(
				'wcv_tracking_number',
				array(
					'id'                => '_wcv_tracking_number_' . $order_id,
					'label'             => __( 'Tracking number', 'wcvendors-pro' ),
					'placeholder'       => __( 'Tracking number', 'wcvendors-pro' ),
					'type'              => 'text',
					'value'             => $tracking_number,
					'custom_attributes' => array(
						'required' => '',
					),
				)
			)
		);

	} // tracking_number

	/**
	 *  Output date shipped date picker
	 *
	 * @since    1.0.0
	 *
	 * @param string $date_shipped The date shipped.
	 * @param int    $order_id The order id.
	 */
	public static function date_shipped( $date_shipped, $order_id ) {

		// Date shipped.
		WCVendors_Pro_Form_Helper::input(
			apply_filters(
				'wcv_tracking_number_date_shipped',
				array(
					'id'          => '_wcv_date_shipped_' . $order_id,
					'label'       => __( 'Date shipped', 'wcvendors-pro' ),
					'class'       => 'wcv-datepicker wcv-init-picker wcv_shipped_date _wcv_date_shipped_' . $order_id,
					'value'       => $date_shipped,
					'placeholder' => 'YYYY-MM-DD',
				)
			)
		);

	} // date_shipped

	/**
	 *  Output shipping providers
	 *
	 * @since   1.0.0
	 * @version 1.7.10
	 *
	 * @param string $shipping_provider Shipping provider.
	 * @param int    $order_id           Order ID for this meta if any.
	 */
	public static function shipping_provider( $shipping_provider, $order_id ) {

		// Shipping Provider.
		WCVendors_Pro_Form_Helper::nested_select(
			apply_filters(
				'wcv_tracking_number_shipping_provider',
				array(
					'id'         => '_wcv_shipping_provider_' . $order_id,
					'label'      => __( 'Shipping provider', 'wcvendors-pro' ),
					'value'      => $shipping_provider,
					'class'      => 'wcv_shipping_provider select2',
					'value_type' => 'key',
					'options'    => WCVendors_Pro_Order_Controller::shipping_providers(),
				)
			)
		);
	} // shipping_provider

	/**
	 *  Output add tracking number button
	 *
	 * @since    1.0.0
	 *
	 * @param  string $button_text The save button text.
	 */
	public static function save_button( $button_text ) {

		WCVendors_Pro_Form_helper::submit(
			apply_filters(
				'wcv_tracking_number_save_button',
				array(
					'id'    => 'tracking_number_save_button',
					'value' => $button_text,
					'class' => '',
				)
			)
		);

	} // save_button

}
