<?php

/**
 * Product SEO Meta Template
 * This template outputs the SEO meta on the product header
 *
 * @since   1.5.8
 * @version 1.5.9
 * @author  WC Vendors <WC Vendors>
 */
?>
<?php if( ! wc_string_to_bool( get_option('wcvendors_hide_product_seo_title') ) ): ?>
<meta name="title" content="<?php echo $seo_title; ?>"/>
<?php endif; ?>
<?php if( ! wc_string_to_bool( get_option('wcvendors_hide_product_seo_description') ) ): ?>
<meta name="description" content="<?php echo wcv_strip_html( $seo_description ); ?>"/>
<?php endif; ?>
<?php if( ! wc_string_to_bool( get_option('wcvendors_hide_product_seo_keywords') ) ): ?>
<meta name="keywords" content="<?php echo strtolower( $seo_keywords ); ?>"/>
<?php endif; ?>

<!-- Schema.org markup -->
<?php if ( $seo_title && ! wc_string_to_bool( get_option('wcvendors_hide_product_seo_title') ) ) : ?>
	<meta itemprop="name" content="<?php echo $seo_title; ?>">
<?php endif; ?>
<?php if ( $seo_description && ! wc_string_to_bool( get_option('wcvendors_hide_product_seo') ) ) : ?>
	<meta itemprop="description" content="<?php echo wcv_strip_html( $seo_description ); ?>">
<?php endif; ?>
<?php if ( filter_var( $seo_image_url, FILTER_VALIDATE_URL ) ) : ?>
	<meta itemprop="image" content="<?php echo $seo_image_url; ?>">
<?php endif; ?>
<!-- End Schema.org markup -->


<?php if ( wc_string_to_bool( $seo_twitter_card  ) && ! wc_string_to_bool( get_option('wcvendors_hide_product_seo_twitter') ) ) : ?>
	<!-- Twitter Card data -->
	<meta name="twitter:card" content="product">
	<?php if ( $seo_twitter_author ) : ?>
		<meta name="twitter:site" content="@<?php echo $seo_twitter_author; ?>">
	<?php endif; ?>
	<?php if ( $seo_title && ! wc_string_to_bool( get_option('wcvendors_hide_product_seo_title') ) ) : ?>
		<meta name="twitter:title" content="<?php echo $seo_title; ?>">
	<?php endif; ?>
	<?php if ( $seo_description && ! wc_string_to_bool( get_option('wcvendors_hide_product_seo_description') ) ) : ?>
		<meta name="twitter:description" content="<?php echo wcv_strip_html( $seo_description ); ?>">
	<?php endif; ?>
	<?php if ( $seo_twitter_author ) : ?>
		<meta name="twitter:creator" content="@<?php echo $seo_twitter_author; ?>">
	<?php endif; ?>
	<?php if ( filter_var( $seo_image_url, FILTER_VALIDATE_URL ) == true ) : ?>
		<meta name="twitter:image" content="<?php echo $seo_image_url; ?>">
	<?php endif; ?>
	<?php if ( $seo_product_amount ) : ?>
		<meta name="twitter:data1" content="<?php echo $seo_currency_symbol . '' . $seo_product_amount; ?>">
		<meta name="twitter:label1" content="Price">

		<?php
	endif;
endif;
?>
<!-- End Twitter Card Data -->

<?php if ( wc_string_to_bool( $seo_opengraph ) && ! wc_string_to_bool( get_option('wcvendors_hide_product_seo_opengraph') ) ) : ?>
	<!-- Open Graph Data -->
	<?php if ( $seo_title && ! wc_string_to_bool( get_option('wcvendors_hide_product_seo_title') ) ) : ?>
		<meta property="og:title" content="<?php echo $seo_title; ?>"/>
	<?php endif; ?>
	<meta property="og:type" content="product"/>
	<?php if ( $seo_store_url ) : ?>
		<meta property="og:url" content="<?php echo $seo_store_url; ?>"/>
	<?php endif; ?>
	<?php if ( filter_var( $seo_image_url, FILTER_VALIDATE_URL ) == true ) : ?>
		<meta property="og:image" content="<?php echo $seo_image_url; ?>"/>
	<?php endif; ?>
	<?php if ( $seo_description && ! wc_string_to_bool( get_option('wcvendors_hide_product_seo_description') ) ) : ?>
		<meta property="og:description" content="<?php echo wcv_strip_html( $seo_description ); ?>"/>
	<?php endif; ?>
	<?php if ( $seo_store_name ) : ?>
		<meta property="og:site_name" content="<?php echo $seo_store_name; ?>"/>
	<?php endif; ?>
	<?php if ( $seo_product_amount ) : ?>
		<meta property="og:price:amount" content="<?php echo $seo_product_amount; ?>"/>
	<?php endif; ?>
	<?php if ( $seo_currency_code ) : ?>
		<meta property="og:price:currency" content="<?php echo $seo_currency_code; ?>"/>
	<?php endif; ?>
	<!-- / Open Graph Data -->
<?php endif; ?>
