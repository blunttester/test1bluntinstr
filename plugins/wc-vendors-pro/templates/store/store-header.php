<?php
/**
 * The Template for displaying a store header
 *
 * Override this template by copying it to yourtheme/wc-vendors/store
 *
 * @package    WCVendors_Pro
 * @version    1.7.9
 */


$store_icon_src   = wp_get_attachment_image_src(
	get_user_meta( $vendor_id, '_wcv_store_icon_id', true ),
	array( 150, 150 )
);
$store_icon       = '';
$store_banner_src = wp_get_attachment_image_src( get_user_meta( $vendor_id, '_wcv_store_banner_id', true ), 'full' );
$store_banner     = '';

// see if the array is valid
if ( is_array( $store_icon_src ) ) {
	$store_icon = '<img src="' . $store_icon_src[0] . '" alt="" class="store-icon" />';
}

if ( is_array( $store_banner_src ) ) {
	$store_banner = '<img src="' . $store_banner_src[0] . '" alt="" class="store-banner" />';
} else {
	// Getting default banner
	$default_banner_src = get_option( 'wcvendors_default_store_banner_src', wcv_get_default_store_banner_src() );
	$store_banner       = '<img src="' . $default_banner_src . '" alt="" class="wcv-store-banner" style="max-height: 200px;"/>';
}

// Verified vendor
$verified_vendor       = ( array_key_exists( '_wcv_verified_vendor', $vendor_meta ) ) ? $vendor_meta['_wcv_verified_vendor'] : false;
$verified_vendor_label = __( get_option( 'wcvendors_verified_vendor_label', '' ), 'wcvendors-pro' );
// $verified_vendor_icon_src 	= get_option( 'wcvendors_verified_vendor_icon_src' );
// Store title
$store_title       = ( is_product() ) ? '<a href="' . WCV_Vendors::get_vendor_shop_page( $post->post_author ) . '">' . $vendor_meta['pv_shop_name'] . '</a>' : $vendor_meta['pv_shop_name'];
$store_description = ( array_key_exists( 'pv_shop_description', $vendor_meta ) ) ? $vendor_meta['pv_shop_description'] : '';

// Migrate to store address array
$phone = ( array_key_exists( '_wcv_store_phone', $vendor_meta ) ) ? $vendor_meta['_wcv_store_phone'] : '';

// This is where you would load your own custom meta fields if you stored any in the settings page for the dashboard
?>

<?php do_action( 'wcv_before_vendor_store_header' ); ?>

<div class="wcv-header-container">

	<div class="wcv-store-grid wcv-store-header">

		<div id="banner-wrap">

			<?php echo $store_banner; ?>

			<div id="inner-element">

				<?php if ( ! empty( $store_icon ) ) : ?>

					<div class="wcv-store-grid__col wcv-store-grid__col--1-of-3  store-brand">
						<div class="store-icon-img">
							<?php echo $store_icon; ?>
						</div>
						<div class="social-icons-container">
							<?php echo wcv_format_store_social_icons( $vendor_id ); ?>
						</div>
					</div>
				<?php endif; ?>

				<?php if ( ! empty( $store_icon ) ) : ?>
				<div class="wcv-store-grid__col wcv-store-grid__col--2-of-3 store-info">
					<?php else : ?>
					<div class="wcv-store-grid__col wcv-store-grid__col--3-of-3 store-info">
						<?php endif; ?>
						<?php do_action( 'wcv_before_vendor_store_title' ); ?>
						<h3><?php echo $store_title; ?></h3>
						<?php do_action( 'wcv_after_vendor_store_title' ); ?>
						<?php do_action( 'wcv_before_vendor_store_rating' ); ?>
						<?php
						if ( ! wc_string_to_bool( get_option( 'wcvendors_ratings_management_cap', 'no' ) ) ) {
							echo WCVendors_Pro_Ratings_Controller::ratings_link( $vendor_id, true );
						}
						?>
						<?php do_action( 'wcv_after_vendor_store_rating' ); ?>
						<?php do_action( 'wcv_before_vendor_store_description' ); ?>
						<?php if ( $verified_vendor ) : ?>
							<div class="wcv-verified-vendor">
								<svg class="wcv-icon wcv-icon-sm">
									<use xlink:href="<?php echo WCV_PRO_PUBLIC_ASSETS_URL; ?>svg/wcv-icons.svg#wcv-icon-check-circle"></use>
								</svg> &nbsp; <?php echo $verified_vendor_label; ?>
							</div>
						<?php endif; ?>
						<p><?php echo $store_description; ?></p>
						<?php if ( wcv_format_store_url( $vendor_id ) ) : ?>
							<p><?php echo wcv_format_store_url( $vendor_id ); ?></p>
						<?php endif; ?>
						<?php do_action( 'wcv_after_vendor_store_description' ); ?>

						<?php if ( empty( $store_icon ) ) : ?>
							<?php echo wcv_format_store_social_icons( $vendor_id ); ?>
						<?php endif; ?>
					</div>

				</div>
			</div>
		</div>
	</div>

	<div class="wcv-store-address-container wcv-store-grid ">

		<div class="wcv-store-grid__col wcv-store-grid__col--2-of-4 store-address">
			<?php if ( $address = wcv_format_store_address( $vendor_id ) ) { ?>
			<a href="http://maps.google.com/maps?&q=<?php echo $address; ?>">
				<address>
					<svg class="wcv-icon wcv-icon-sm">
						<use xlink:href="<?php echo WCV_PRO_PUBLIC_ASSETS_URL; ?>svg/wcv-icons.svg#wcv-plane"></use>
					</svg>
					<?php echo $address; ?>
				</address>
				</a><?php } ?>
		</div>
		<div class="wcv-store-grid__col wcv-store-grid__col--1-of-4 store-phone">
			<?php if ( $phone != '' ) { ?>
			<a href="tel:<?php echo $phone; ?>">
				<svg class="wcv-icon wcv-icon-sm">
					<use xlink:href="<?php echo WCV_PRO_PUBLIC_ASSETS_URL; ?>svg/wcv-icons.svg#wcv-icon-phone"></use>
				</svg>
				<?php echo $phone; ?>
				</a><?php } ?>
		</div>
		<?php if ( wc_string_to_bool( get_option( 'wcvendors_show_store_total_sales', 'no' ) ) ) : ?>
			<div class="wcv-store-grid__col wcv-store-grid__col--1-of-4 store-sales">
				<svg class="wcv-icon wcv-icon-sm">
					<use xlink:href="<?php echo WCV_PRO_PUBLIC_ASSETS_URL; ?>svg/wcv-icons.svg#wcv-icon-info-circle"></use>
				</svg>
				<?php
				$label = WCVendors_Pro_Vendor_Controller::get_total_sales_label( $vendor_id, 'store' );
				echo do_shortcode( '[wcv_pro_vendor_totalsales vendor_id="' . $vendor_id . '" label="' . $label . '"]' );
				?>
			</div>
		<?php endif; ?>
	</div>

	<?php
		do_action_deprecated( 'wcv_after_vendor_store_header', array(), '1.7.10', 'wcvendors_after_vendor_store_header' );
		do_action( 'wcvendors_after_vendor_store_header' );
	?>
