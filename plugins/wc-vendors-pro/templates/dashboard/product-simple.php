<?php
/**
 * The template for displaying the Simple Product Edit form
 *
 * Override this template by copying it to yourtheme/wc-vendors/dashboard/
 *
 * @package    WCVendors_Pro
 * @since      1.5.6
 * @version    1.6.3
 */
/**
 *   DO NOT EDIT ANY OF THE LINES BELOW UNLESS YOU KNOW WHAT YOU'RE DOING
 */

$title      = ( is_numeric( $object_id ) ) ? __( 'Save Changes', 'wcvendors-pro' ) : __( 'Add Product', 'wcvendors-pro' );
$page_title = ( is_numeric( $object_id ) ) ? __( 'Edit Product', 'wcvendors-pro' ) : __( 'Add Product', 'wcvendors-pro' );
$product    = ( is_numeric( $object_id ) ) ? wc_get_product( $object_id ) : null;
$post       = ( is_numeric( $object_id ) ) ? get_post( $object_id ) : null;

// Get basic information for the product
$product_title             = ( isset( $product ) && null !== $product ) ? $product->get_title() : '';
$product_description       = ( isset( $product ) && null !== $product ) ? $post->post_content : '';
$product_short_description = ( isset( $product ) && null !== $product ) ? $post->post_excerpt : '';
$post_status               = ( isset( $product ) && null !== $product ) ? $post->post_status : '';

/**
 *  Ok, You can edit the template below but be careful!
 */
?>

<h2><?php echo $page_title; ?></h2>

<?php do_action( 'wcvendors_before_product_form' ); ?>

<!-- Product Edit Form -->
<form method="post" action="" id="wcv-product-edit" class="wcv-form">

	<!-- Basic Product Details -->
	<div class="wcv-product-basic wcv-product">
		<?php do_action( 'wcv_before_product_details', $object_id ); ?>
		<!-- Product Title -->
		<?php WCVendors_Pro_Product_Form::title( $object_id, $product_title ); ?>
		<!-- Product Description -->
		<?php WCVendors_Pro_Product_Form::description( $object_id, $product_description ); ?>
		<!-- Product Categories -->
		<?php WCVendors_Pro_Product_Form::categories( $object_id ); ?>
		<?php do_action( 'wcv_after_product_details', $object_id ); ?>
	</div>

	<div class="all-100">
		<?php do_action( 'wcv_before_product_media', $object_id ); ?>
		<!-- Media uploader -->
		<div class="wcv-product-media">
			<?php WCVendors_Pro_Form_helper::product_media_uploader( $object_id ); ?>
		</div>
		<?php do_action( 'wcv_after_product_media', $object_id ); ?>
	</div>

	<hr/>

	<?php do_action( 'wcv_after_product_simple_before', $object_id ); ?>

	<!-- Product Type  -->
	<?php WCVendors_Pro_Product_Form::product_type_hidden( $object_id, 'simple' ); ?>

	<!-- Price and Sale Price -->
	<?php WCVendors_Pro_Product_Form::prices( $object_id ); ?>

	<!-- Tax -->
	<?php WCVendors_Pro_Product_Form::tax( $object_id ); ?>
	
	<!-- SKU  -->
	<?php WCVendors_Pro_Product_Form::sku( $object_id ); ?>
	<!-- Private listing  -->
	<?php WCVendors_Pro_Product_Form::private_listing( $object_id ); ?>

	<!-- Shipping rates  -->
	<?php WCVendors_Pro_Product_Form::shipping_rates( $object_id ); ?>

	<?php do_action( 'wcv_after_product_simple_after', $object_id ); ?>

		<!-- Upsells and grouping -->
	<?php do_action( 'wcv_before_product_simple_linked' ); ?>
	<?php WCVendors_Pro_Product_Form::grouped_products( $object_id, $product ); ?>
	<?php WCVendors_Pro_Product_Form::up_sells( $object_id ); ?>
	<?php WCVendors_Pro_Product_Form::crosssells( $object_id ); ?>
	<?php do_action( 'wcv_product_options_upsells_product_data' ); ?>
	<?php do_action( 'wcv_after_product_simple_linked' ); ?>

	<?php WCVendors_Pro_Product_Form::product_seo( $object_id ); ?>

	<?php WCVendors_Pro_Product_Form::form_data( $object_id, $post_status, $template ); ?>
	<?php WCVendors_Pro_Product_Form::save_button( $title ); ?>
	<?php WCVendors_Pro_Product_Form::draft_button( __( 'Save Draft', 'wcvendors-pro' ) ); ?>

</form>

<?php do_action( 'wcvendors_after_product_form' ); ?>
