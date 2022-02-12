<?php
/**
 * The template for displaying the shipping label
 *
 * Override this template by copying it to yourtheme/wc-vendors/dashboard/order
 *
 * @package    WCVendors_Pro
 * @version    1.7.5
 * @since      1.0.4
 *
 * The following variables are available in this template
 *
 *  $order
 *  $store_name
 *  $store_address1
 *  $store_address2
 *  $store_city
 *  $store_state
 *  $store_country
 *  $store_postcode
 *  $products
 */
?>

<html>
<head>
	<title></title>
	<style type="text/css">
		body {
			padding: 2em 3em;
			font-family: arial,sans-serif;
			line-height: 1.48;
			font-size: 14px;
			max-width: 21cm;
		}
		.row {
			display: flex;
			justify-content: space-between;
		}
		.row > * {
			flex-shrink: 0;
			flex-grow: 0;
		}
		.row > *:nth-child(2) {
			width: 40%;
		}
		#shipping-label {
			text-transform: uppercase;
			border-bottom: 2px dashed #aaa;
			margin-bottom: 2em;
			padding-bottom: 2em;
		}
		#shipping-label .customer {
			font-size: 18px;
		}
		#shipping-label .vendor {
			text-align: right;
			font-size: 12px;
		}
		#vendor-info {
			margin-bottom: 2em;
		}
		#customer-info {
			margin-bottom: 2em;
		}
		.vendor-logo {
			font-weight: bold;
			text-transform: uppercase;
			font-size: 3em;
		}
		.vendor-logo img {
			max-height: 120px;
			width: auto;
		}
		address {
			font-style: normal;
		}
		h3 {
			text-transform: uppercase;
			margin: 0;
		}
		table {
			border-collapse: collapse;
			font-size: 14px;
			width: 100%;
		}
		td, th {
			border-width: 1px 0;
			border-style: solid;
			border-color: #aaa;
			padding: 5px 10px;
		}
		table thead {
			background: #222;
			color: #fff;
		}
		table .product {
			width: 80%;
			text-align: left;
		}
		table .quantity {
			width: 20%;
			text-align: center;
		}
	</style>
</head>
<body>
	<div id="shipping-label">
		<div class="row">
			<address class="customer">
				<h3><?php _e( 'Ship to:', 'wcvendors-pro' ); ?></h3>
				<?php echo $ship_to; ?>
			</address>
			<address class="vendor">
				<?php echo $store_name; ?><br/>
				<?php echo $ship_from; ?>
			</address>
		</div>
	</div>

	<div id="vendor-info">
		<div class="row">
			<div class="vendor-logo">
				<?php echo $store_icon ? $store_icon : $store_name; ?>
			</div>
			<address>
				<strong><?php echo $store_name; ?></strong><br/>
				<?php echo $ship_from; ?>
			</address>
		</div>
	</div>

	<div id="customer-info">
		<div class="row">
			<address>
				<?php echo $ship_to; ?>
			</address>
			<div class="order-details">
				<?php echo __( 'Order number:', 'wcvendors-pro' ); ?> <?php echo $order->get_order_number(); ?></br>
				<?php echo __( 'Order date:', 'wcvendors-pro' ); ?> <?php echo $order->get_date_created()->format( 'F j, Y' ); ?>
			</div>
		</div>
	</div>

	<table id="picking-list">
		<thead>
			<tr>
				<th class="product"><?php echo __( 'Product', 'wcvendors-pro' ); ?></th>
				<th class="quantity"><?php echo __( 'Quantity', 'wcvendors-pro' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( $products as $product ) : ?>
				<tr>
					<td class="product"><?php echo $product['name']; ?></td>
					<td class="quantity"><?php echo $product['quantity']; ?></td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

</body>
</html>
