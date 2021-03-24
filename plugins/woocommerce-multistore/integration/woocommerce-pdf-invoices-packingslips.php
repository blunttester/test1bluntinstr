<?php
/**
 * Integrate WooCommerce PDF Invoices & Packing Slips
 * Created by WP Overnight
 * URL: https://wpovernight.com/
 * Plugin URL: https://wordpress.org/plugins/woocommerce-pdf-invoices-packing-slips/
 *
 * @since 4.1.3
 */

use WPO\WC\PDF_Invoices\Compatibility\WC_Core as WCX;
use WPO\WC\PDF_Invoices\Compatibility\Order as WCX_Order;
use WPO\WC\PDF_Invoices\Compatibility\Product as WCX_Product;

class WOO_MSTORE_INTEGRATION_PDF_INVOICE_PACKINGSLIPS {
	/**
	 * Initialize the action hooks and load the plugin classes
	 **/
	public function __construct() {
		/**
		 * This is a non-multisite extension.
		 */
		if ( is_multisite() ) {
			return;
		}

		add_filter( 'WOO_MSTORE_ORDER/woocommerce_add_order_to_results', array( $this, 'add_listing_actions' ), 10, 2 );
		add_action( 'WOO_MSTORE_ORDER/woocommerce_admin_order_actions_end', array( $this, 'show_listing_actions' ), 10, 1 );
	}

	/**
	 * Add PDF actions to the orders listing
	 */
	public function add_listing_actions( $order_data, $order ) {
		// do not show buttons for trashed orders
		if ( $order->get_status() == 'trash' ) {
			return;
		}

		$this->disable_storing_document_settings();

		$listing_actions = array();
		$documents       = WPO_WCPDF()->documents->get_documents();
		foreach ( $documents as $document ) {
			$document_title = $document->get_title();
			$icon           = ! empty( $document->icon ) ? $document->icon : WPO_WCPDF()->plugin_url() . '/assets/images/generic_document.png';
			if ( $document = wcpdf_get_document( $document->get_type(), $order ) ) {
				$document_title                           = method_exists( $document, 'get_title' ) ? $document->get_title() : $document_title;
				$listing_actions[ $document->get_type() ] = array(
					'url'    => admin_url( "admin-ajax.php?action=generate_wpo_wcpdf&document_type={$document->get_type()}&order_ids=" . WCX_Order::get_id( $order ) . '&order_key=' . $order->get_order_key() ),
					'img'    => $icon,
					'alt'    => 'PDF ' . $document_title,
					'exists' => method_exists( $document, 'exists' ) ? $document->exists() : false,
				);
			}
		}

		$order_data['__pdf_invoice_packingslips_actions'] = $listing_actions;

		return $order_data;
	}

	public function disable_storing_document_settings() {
		add_filter( 'wpo_wcpdf_document_store_settings', array( $this, 'return_false' ), 9999 );
	}

	public function return_false() {
		return false;
	}

	/**
	 * Runs on the child site to show invoice buttons from child sites.
	 *
	 * @param mixed $the_order
	 * @return void
	 */
	public function show_listing_actions( $the_order ) {
		if ( empty( $the_order['__pdf_invoice_packingslips_actions'] ) ) {
			return;
		}

		echo '<br />';

		foreach ( $the_order['__pdf_invoice_packingslips_actions'] as $action => $data ) {
			?>
			<a href="<?php echo $data['url']; ?>" class="button tips wpo_wcpdf <?php echo $data['exists'] == true ? 'exists ' . $action : $action; ?>" target="_blank" alt="<?php echo $data['alt']; ?>" data-tip="<?php echo $data['alt']; ?>">
				<img src="<?php echo $data['img']; ?>" alt="<?php echo $data['alt']; ?>" width="16">
			</a>
			<?php
		}
	}
}

new WOO_MSTORE_INTEGRATION_PDF_INVOICE_PACKINGSLIPS();
