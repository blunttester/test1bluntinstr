<?php
/**
 * The Template for displaying the modern store header
 *
 * Override this template by copying it to yourtheme/wc-vendors/store
 *
 * @package    WCVendors_Pro
 * @version    1.7.9
 */

$store_icon             = '';
$store_icon_src         = wp_get_attachment_image_src(
	get_user_meta( $vendor_id, '_wcv_store_icon_id', true ),
	array( 150, 150 )
);
$store_banner_src       = wp_get_attachment_image_src( get_user_meta( $vendor_id, '_wcv_store_banner_id', true ), 'full' );
$store_banner_image_url = get_option( 'wcvendors_default_store_banner_src', wcv_get_default_store_banner_src() );

// see if the array is valid
if ( is_array( $store_icon_src ) ) {
	$store_icon = '<img src="' . $store_icon_src[0] . '" alt="" class="store-icon" />';
} else {
	$store_icon = '<img src="' . get_avatar_url( $vendor_id, array( 'size' => 150 ) ) . '" alt="" class="store-icon" />';
}

if ( is_array( $store_banner_src ) ) {
	$store_banner_image_url = $store_banner_src[0];
}

// Verified vendor
$verified_vendor       = ( array_key_exists( '_wcv_verified_vendor', $vendor_meta ) ) ? $vendor_meta['_wcv_verified_vendor'] : false;
$verified_vendor_label = __( get_option( 'wcvendors_verified_vendor_label', '' ), 'wcvendors-pro' );

// Store title
$_store_title      = isset( $vendor_meta['pv_shop_name'] ) ? $vendor_meta['pv_shop_name'] : '';
$store_title       = ( is_product() ) ? '<a href="' . WCV_Vendors::get_vendor_shop_page( $post->post_author ) . '">' . $_store_title . '</a>' : $_store_title;
$store_description = ( array_key_exists( 'pv_shop_description', $vendor_meta ) ) ? $vendor_meta['pv_shop_description'] : '';

$phone = ( array_key_exists( '_wcv_store_phone', $vendor_meta ) ) ? $vendor_meta['_wcv_store_phone'] : '';

// This is where you would load your own custom meta fields if you stored any in the settings page for the dashboard
?>

<?php do_action( 'wcv_before_vendor_store_header' ); ?>

<div class="wcv-store-header header-modern">
	<div class="upper">
		<div class="cover" style="background-image: url(<?php echo $store_banner_image_url; ?>);"></div>
		<div class="info">
			<div class="avatar">
				<?php echo $store_icon; ?>
			</div>
			<div class="about">
				<div class="name">
					<div class="txt"><?php echo $store_title; ?></div>
					<?php if ( $verified_vendor ) : ?>
						<div class="wcv-verified-vendor">
							<svg class="wcv-icon wcv-icon-sm">
								<use xlink:href="<?php echo WCV_PRO_PUBLIC_ASSETS_URL; ?>svg/wcv-icons.svg#wcv-icon-check-circle"></use>
							</svg>
							<span><?php echo __( $verified_vendor_label, 'wcvendors-pro' ); ?></span>
						</div>
					<?php endif; ?>
				</div>
				<p class="desc"><?php echo $store_description; ?></p>
				<?php if ( wcv_format_store_url( $vendor_id ) ) : ?>
					<p class="url"><?php echo wcv_format_store_url( $vendor_id ); ?></p>
				<?php endif; ?>
			</div>
		</div>
	</div>
	<div class="meta">
		<?php if ( ! wc_string_to_bool( get_option( 'wcvendors_ratings_management_cap', 'no' ) ) ) : ?>
			<div class="rating block">
				<div class="label">
					<?php echo __( 'Rating', 'wcvendors-pro' ); ?>
				</div>
				<div class="stars">
					<?php echo WCVendors_Pro_Ratings_Controller::ratings_link( $vendor_id, true ); ?>
				</div>
			</div>
		<?php endif; ?>
		<?php if ( $phone ) : ?>
			<div class="phone block">
				<div class="label">
					<?php echo __( 'Phone', 'wcvendors-pro' ); ?>
				</div>
				<a href="tel:<?php echo $phone; ?>">
					<svg class="wcv-icon wcv-icon-sm">
						<use xlink:href="<?php echo WCV_PRO_PUBLIC_ASSETS_URL; ?>svg/wcv-icons.svg#wcv-icon-phone"></use>
					</svg>
					<?php echo $phone; ?>
				</a>
			</div>
		<?php endif; ?>
		<?php if ( $address = wcv_format_store_address( $vendor_id ) ) : ?>
			<div class="address block">
				<div class="label">
					<?php echo __( 'Address', 'wcvendors-pro' ); ?>
				</div>
				<a href="http://maps.google.com/maps?&q=<?php echo $address; ?>">
					<address>
						<svg class="wcv-icon wcv-icon-sm">
							<use xlink:href="<?php echo WCV_PRO_PUBLIC_ASSETS_URL; ?>svg/wcv-icons.svg#wcv-icon-home"></use>
						</svg>
						<?php echo $address; ?>
					</address>
				</a>
			</div>
		<?php endif; ?>
		<?php if ( $social_icons = wcv_format_store_social_icons( $vendor_id ) ) : ?>
			<div class="social block">
				<div class="label">
					<?php echo __( 'Social', 'wcvendors-pro' ); ?>
				</div>
				<?php echo $social_icons; ?>
			</div>
		<?php endif; ?>
		<?php if ( wc_string_to_bool( get_option( 'wcvendors_show_store_total_sales', 'no' ) ) ) : ?>
			<div class="sales block">
				<div class="label">
					<?php echo __( 'Total Sales', 'wcvendors-pro' ); ?>
				</div>
				<div class="value">
					<svg class="wcv-icon wcv-icon-sm">
						<use xlink:href="<?php echo WCV_PRO_PUBLIC_ASSETS_URL; ?>svg/wcv-icons.svg#wcv-icon-info-circle"></use>
					</svg>
					<?php
					$label = WCVendors_Pro_Vendor_Controller::get_total_sales_label( $vendor_id, 'store' );
					echo do_shortcode( '[wcv_pro_vendor_totalsales vendor_id="' . $vendor_id . '" position="none"]' );
					?>
				</div>
			</div>
		<?php endif; ?>
	</div>
</div>

<?php
	do_action_deprecated( 'wcv_after_vendor_store_header', array( $vendor_id ), '1.7.10', 'wcvendors_after_vendor_store_header' );
	do_action( 'wcvendors_after_vendor_store_header' );
?>
