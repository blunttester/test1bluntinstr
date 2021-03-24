<?php
/**
 * The template for displaying the shop coupon form
 *
 * Override this template by copying it to yourtheme/wc-vendors/dashboard/
 *
 * @package    WCVendors_Pro
 * @since      1.0.0
 * @version    1.7.7
 *
 * /**
 *   DO NOT EDIT ANY OF THE LINES BELOW UNLESS YOU KNOW WHAT YOU'RE DOING
 */

$title       = ( is_numeric( $object_id ) ) ? __( 'Save Changes', 'wcvendors-pro' ) : __( 'Add Coupon', 'wcvendors-pro' );
$coupon      = ( is_numeric( $object_id ) ) ? new WC_Coupon( $object_id ) : new WC_Coupon();
$coupon_meta = ( is_numeric( $object_id ) ) ?
						array_map(
							function ( $a ) {
								return $a[0];
							},
							get_post_meta( $object_id )
							)
						: array();

/**
 *  Ok, You can edit the template below but be careful!
 */
?>

<h2><?php echo $title; ?></h2>

<!-- Product Edit Form -->
<form method="post" action="" id="wcv-shop_coupon-edit" class="wcv-form wcv-formvalidator">

	<!-- Coupon Code -->
	<?php WCVendors_Pro_Coupon_Form::coupon_code( $coupon->get_code() ); ?>
	<!-- Coupon description -->
	<?php WCVendors_Pro_Coupon_Form::coupon_description( $coupon->get_description() ); ?>

	<?php do_action( 'wcv_coupon_editor_after_coupon_description', $coupon ); ?>

	<div class="wcv-tabs top" data-prevent-url-change="true">
		<ul class="tabs-nav">
			<?php do_action( 'wcv_coupon_editor_tabs_start', $coupon ); ?>
			<li><a class="tabs-tab" href="#general"><?php _e( 'General', 'wcvendors-pro' ); ?></a></li>
			<li><a class="tabs-tab" href="#usage"><?php _e( 'Usage restrictions', 'wcvendors-pro' ); ?></a></li>
			<li><a class="tabs-tab" href="#limits"><?php _e( 'Usage limits', 'wcvendors-pro' ); ?></a></li>
			<?php do_action( 'wcv_coupon_editor_tabs_end', $coupon ); ?>
		</ul>

		<?php do_action( 'wcv_coupon_editor_before_general_content', $coupon ); ?>
		<div class="wcv-coupon-general tabs-content" id="general">
			<?php do_action( 'wcv_coupon_editor_general_start', $coupon ); ?>
			<!-- Discount Type -->
			<?php WCVendors_Pro_Coupon_Form::discount_type( $coupon->get_discount_type() ); ?>
			<!-- Apply to all products -->
			<?php WCVendors_Pro_Coupon_Form::apply_to_all_products( ( array_key_exists( 'apply_to_all_products', $coupon_meta ) ) ? $coupon_meta['apply_to_all_products'] : '' ); ?>
			<!-- Coupon Amount  -->
			<?php
			WCVendors_Pro_Coupon_Form::coupon_amount( $coupon->get_amount(), $coupon->get_discount_type() );
			?>
			<!-- Allow Free Shipping -->
			<?php WCVendors_Pro_Coupon_Form::free_shipping( ( array_key_exists( 'vendor_free_shipping', $coupon_meta ) ) ? $coupon_meta['vendor_free_shipping'] : '' ); ?>
			<!-- Coupon Expiry -->
			<?php WCVendors_Pro_Coupon_Form::expiry_date( $coupon->get_date_expires() ); ?>

			<?php do_action( 'wcv_coupon_editor_general_end', $coupon ); ?>
		</div>

		<?php do_action( 'wcv_coupon_editor_before_usage_content', $coupon ); ?>
		<div class="wcv-coupon-usage-restrictions tabs-content" id="usage">
			<?php do_action( 'wcv_coupon_editor_usage_end', $coupon ); ?>
			<!-- Min spend -->
			<?php WCVendors_Pro_Coupon_Form::minimum_amount( $coupon->get_minimum_amount() ); ?>
			<!-- Max spend  -->
			<?php WCVendors_Pro_Coupon_Form::maximum_amount( $coupon->get_maximum_amount() ); ?>
			<!-- individual use -->
			<?php WCVendors_Pro_Coupon_Form::individual_use( $coupon->get_individual_use() ); ?>
			<!-- exclude sale -->
			<?php WCVendors_Pro_Coupon_Form::exclude_sale_items( $coupon->get_exclude_sale_items() ); ?>
			<!-- Products  -->
			<?php WCVendors_Pro_Coupon_Form::products( $coupon->get_product_ids() ); ?>
			<!-- exclude Products -->
			<?php WCVendors_Pro_Coupon_Form::exclude_products( $coupon->get_excluded_product_ids() ); ?>
			<!-- Email restrictions -->
			<?php WCVendors_Pro_Coupon_Form::email_addresses( $coupon->get_email_restrictions() ); ?>
			<?php do_action( 'wcv_coupon_editor_usage_end', $coupon ); ?>
		</div>

		<?php do_action( 'wcv_coupon_editor_before_limits_content', $coupon ); ?>
		<div class="wcv-coupon-usage-limits tabs-content" id="limits">
			<?php do_action( 'wcv_coupon_editor_limits_start', $coupon ); ?>
			<!-- Usage limit per coupon -->
			<?php WCVendors_Pro_Coupon_Form::usage_limit( $coupon->get_usage_limit() ); ?>
			<!-- Limit usage to X items -->
			<?php WCVendors_Pro_Coupon_Form::limit_usage_to_x_items( $coupon->get_limit_usage_to_x_items() ); ?>

			<!-- Usage limit per user -->
			<?php WCVendors_Pro_Coupon_Form::usage_limit_per_user( $coupon->get_usage_limit_per_user() ); ?>

			<?php do_action( 'wcv_coupon_editor_limits_end', $coupon ); ?>
		</div>
		<?php do_action( 'wcv_coupon_editor_after_limits_content', $coupon ); ?>


		<?php do_action( 'wcv_coupon_editor_form_end', $coupon ); ?>
		<hr/>
		<br/>


		<br/>

		<!-- Form data -->
		<?php WCVendors_Pro_Coupon_Form::form_data( $title, $object_id ); ?>

</form>
