<?php
/**
 * The template for displaying the store settings form
 *
 * Override this template by copying it to yourtheme/wc-vendors/dashboard/
 *
 * @package    WCVendors_Pro
 * @version    1.7.10
 */

$settings_social = (array) get_option( 'wcvendors_hide_settings_social' );
$social_total    = count( $settings_social );
$social_count    = 0;
foreach ( $settings_social as $value ) {
	if ( 1 == $value ) {
		++$social_count;
	}
}

?>

	<h3><?php _e( 'Settings', 'wcvendors-pro' ); ?></h3>

<?php do_action( 'wcvendors_settings_before_form' ); ?>

	<form method="post" id="wcv-store-settings" action="#" class="wcv-form">

		<?php WCVendors_Pro_Store_Form::form_data(); ?>

		<div class="wcv-tabs top" data-prevent-url-change="true">

			<?php WCVendors_Pro_Store_Form::store_form_tabs(); ?>

			<!-- Store Settings Form -->

			<div class="tabs-content" id="store">

				<!-- Store Name -->
				<?php WCVendors_Pro_Store_Form::store_name( $store_name ); ?>

				<?php do_action( 'wcvendors_settings_after_shop_name' ); ?>

				<!-- Store Description -->
				<?php WCVendors_Pro_Store_Form::store_description( $store_description ); ?>

				<?php do_action( 'wcvendors_settings_after_shop_description' ); ?>
				<br/>

				<!-- Seller Info -->
				<?php WCVendors_Pro_Store_Form::seller_info(); ?>


				<?php do_action( 'wcvendors_settings_after_seller_info' ); ?>

				<br/>

				<!-- Company URL -->
				<?php do_action( 'wcvendors_settings_before_company_url' ); ?>
				<?php WCVendors_Pro_Store_Form::company_url(); ?>
				<?php do_action( 'wcvendors_settings_after_company_url' ); ?>


				<!-- Store Phone -->
				<?php do_action( 'wcvendors_settings_before_store_phone' ); ?>
				<?php WCVendors_Pro_Store_Form::store_phone(); ?>
				<?php do_action( 'wcvendors_settings_after_store_phone' ); ?>

				<!-- Store Address -->
				<?php do_action( 'wcvendors_settings_before_address' ); ?>

				<?php WCVendors_Pro_Store_Form::show_hide_map(); ?>
				<?php WCVendors_Pro_Store_Form::location_picker(); ?>
				<?php WCVendors_Pro_Store_Form::coordinates(); ?>
				<?php WCVendors_Pro_Store_Form::store_address_country(); ?>
				<?php WCVendors_Pro_Store_Form::store_address1(); ?>
				<?php WCVendors_Pro_Store_Form::store_address2(); ?>
				<?php WCVendors_Pro_Store_Form::store_address_city(); ?>
				<?php WCVendors_Pro_Store_Form::store_address_state(); ?>
				<?php WCVendors_Pro_Store_Form::store_address_postcode(); ?>

				<?php do_action( 'wcvendors_settings_after_address' ); ?>

				<!-- Store Opening Hours -->
				<?php WCVendors_Pro_Store_Form::store_opening_hours_form(); ?>

				<!-- Store Vacation Mode -->
				<?php do_action( 'wcvendors_settings_before_vacation_mode' ); ?>
				<?php WCVendors_Pro_Store_Form::vacation_mode(); ?>
				<?php do_action( 'wcvendors_settings_after_vacation_mode' ); ?>

				<!-- Vendor Store Notice -->
				<?php do_action( 'wcvendors_settings_before_vendor_store_notice' ); ?>
				<?php WCVendors_Pro_Store_Form::enable_store_notice(); ?>
				<?php WCVendors_Pro_Store_Form::vendor_store_notice(); ?>
				<?php do_action( 'wcvendors_settings_after_vendor_store_notice' ); ?>

				<!-- Store GA Code -->
				<?php do_action( 'wcvendors_settings_before_ga_code' ); ?>
				<?php WCVendors_Pro_Store_Form::store_ga_code(); ?>
				<?php do_action( 'wcvendors_settings_after_ga_code' ); ?>
			</div>
			<?php do_action( 'wcvendors_settings_after_store_tab' ); ?>

			<?php do_action( 'wcvendors_settings_before_payment_tab' ); ?>
			<div class="tabs-content" id="payment">
				<!-- Paypal address -->
				<?php do_action( 'wcvendors_settings_before_paypal' ); ?>

				<?php WCVendors_Pro_Store_Form::paypal_address(); ?>

				<?php WCVendors_Pro_Store_Form::bank_account_name(); ?>
				<?php WCVendors_Pro_Store_Form::bank_account_number(); ?>
				<?php WCVendors_Pro_Store_Form::bank_name(); ?>
				<?php WCVendors_Pro_Store_Form::bank_routing_number(); ?>
				<?php WCVendors_Pro_Store_Form::bank_iban(); ?>
				<?php WCVendors_Pro_Store_Form::bank_bic_swift(); ?>

				<?php do_action( 'wcvendors_settings_after_paypal' ); ?>
			</div>
			<?php do_action( 'wcvendors_settings_after_payment_tab' ); ?>

			<?php do_action( 'wcvendors_settings_before_branding_tab' ); ?>
			<div class="tabs-content" id="branding">
				<?php do_action( 'wcvendors_settings_before_branding' ); ?>

				<!-- Store Banner -->
				<?php WCVendors_Pro_Store_Form::store_banner(); ?>

				<!-- Store Icon -->
				<?php WCVendors_Pro_Store_Form::store_icon(); ?>

				<?php do_action( 'wcvendors_settings_after_branding' ); ?>
			</div>
			<?php do_action( 'wcvendors_settings_after_branding_tab' ); ?>

			<?php do_action( 'wcvendors_settings_before_shipping_tab' ); ?>
			<div class="tabs-content" id="shipping">

				<?php do_action( 'wcvendors_settings_before_shipping' ); ?>

				<!-- Shipping Rates -->
				<?php WCVendors_Pro_Store_Form::vendor_shipping_type(); ?>
				<?php WCVendors_Pro_Store_Form::shipping_rates(); ?>

				<?php do_action( 'wcvendors_settings_after_shipping' ); ?>

				<hr/>

				<!-- Shiping Information  -->

				<?php WCVendors_Pro_Store_Form::product_handling_fee( $shipping_details ); ?>
				<?php WCVendors_Pro_Store_Form::shipping_from( $shipping_details ); ?>
				<?php WCVendors_Pro_Store_Form::shipping_address( $shipping_details ); ?>

			</div>
			<?php do_action( 'wcvendors_settings_after_shipping_tab' ); ?>

			<?php do_action( 'wcvendors_settings_before_social_tab' ); ?>
			<?php if ( $social_count != $social_total ) : ?>
				<div class="tabs-content" id="social">
					<?php do_action( 'wcvendors_settings_before_social' ); ?>
					<?php WCVendors_Pro_Store_Form::render_social_media_settings(); ?>
					<?php do_action( 'wcvendors_settings_after_social' ); ?>
				</div>
			<?php endif; ?>
			<?php do_action( 'wcvendors_settings_after_social_tab' ); ?>

			<?php do_action( 'wcvendors_settings_before_policies_tab' ); ?>

			<!-- Store Policy -->
			<div class="tabs-content" id="policies">
				<?php do_action( 'wcvendors_settings_before_policies' ); ?>

				<?php WCVendors_Pro_Store_Form::store_policy( 'privacy', __( 'Privacy policy', 'wcvendors-pro' ) ); ?>
				<?php WCVendors_Pro_Store_Form::store_policy( 'terms', __( 'Terms and conditions', 'wcvendors-pro' ) ); ?>
				<?php WCVendors_Pro_Store_Form::shipping_policy( $shipping_details ); ?>
				<?php WCVendors_Pro_Store_Form::return_policy( $shipping_details ); ?>

				<?php do_action( 'wcvendors_settings_after_policies' ); ?>
			</div>

			<?php do_action( 'wcvendors_settings_after_policies_tab' ); ?>

			<?php do_action( 'wcvendors_settings_before_seo_tab' ); ?>

			<!-- Store SEO -->
			<div class="tabs-content" id="seo">
				<?php do_action( 'wcvendors_settings_before_seo' ); ?>
				<!-- SEO Title -->
				<?php WCVendors_Pro_Store_Form::seo_title(); ?>
				<!-- Meta description -->
				<?php WCVendors_Pro_Store_Form::seo_meta_description(); ?>
				<!-- Meta keywords -->
				<?php WCVendors_Pro_Store_Form::seo_meta_keywords(); ?>
				<!-- Facebook title -->
				<?php WCVendors_Pro_Store_Form::seo_fb_title(); ?>
				<!-- Facebook description -->
				<?php WCVendors_Pro_Store_Form::seo_fb_description(); ?>
				<!-- Facebook image  -->
				<?php WCVendors_Pro_Store_Form::seo_fb_image(); ?>
				<!-- Twitter Title -->
				<?php WCVendors_Pro_Store_Form::seo_twitter_title(); ?>
				<!-- Twitter Description -->
				<?php WCVendors_Pro_Store_Form::seo_twitter_description(); ?>
				<!-- Twitter Image -->
				<?php WCVendors_Pro_Store_Form::seo_twitter_image(); ?>

				<?php do_action( 'wcvendors_settings_after_seo' ); ?>
			</div>

			<?php do_action( 'wcvendors_settings_after_seo_tab' ); ?>

			<!-- Submit Button -->
			<!-- DO NOT REMOVE THE FOLLOWING TWO LINES -->
			<?php WCVendors_Pro_Store_Form::save_button( __( 'Save Changes', 'wcvendors-pro' ) ); ?>
		</div>
	</form>
<?php
do_action( 'wcvendors_settings_after_form' );
