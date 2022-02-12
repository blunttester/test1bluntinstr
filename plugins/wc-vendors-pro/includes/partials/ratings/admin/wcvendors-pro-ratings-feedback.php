<?php

/**
 * Feedback style for the vendor feedback table
 *
 * This file is used to display the ratings link
 *
 * @link       http://www.wcvendors.com
 * @since      1.0.0
 * @version    1.6.5
 *
 * @package    WCVendors_Pro
 * @subpackage WCVendors_Pro/includes/partials/ratings
 */

$rating   = '';
$title    = '';
$comments = '';

$comments = '<p>' . $fb->comments . '</p>';

for ( $i = 1; $i <= stripslashes( $fb->rating ); $i ++ ) {
	$rating .= '<svg class="wcv-icon wcv-icon-sm">
                    <use xlink:href="' . WCV_PRO_PUBLIC_ASSETS_URL . 'svg/wcv-icons.svg#wcv-icon-star"></use>
                </svg>';
}
for ( $i = stripslashes( $fb->rating ); $i < 5; $i ++ ) {
	$rating .= '<svg class="wcv-icon wcv-icon-sm">
                    <use xlink:href="' . WCV_PRO_PUBLIC_ASSETS_URL . 'svg/wcv-icons.svg#wcv-icon-star-o"></use>
                </svg>';
}

$title = '<h6>' . $fb->rating_title . '    ' . $rating . '</h6>';

$feedback = sprintf( '<div class="wcv_mobile_product wcv_mobile">%s</div><div class="wcv_mobile_customer wcv_mobile">%s</div><div class="wcv_mobile_date wcv_mobile">%s</div>%s %s', sprintf( __( 'Product : %s ', 'wcvendors-pro' ), $product_name ), sprintf( __( 'Customer : %s ', 'wcvendors-pro' ), $customer_name ), $date, $title, $comments );
