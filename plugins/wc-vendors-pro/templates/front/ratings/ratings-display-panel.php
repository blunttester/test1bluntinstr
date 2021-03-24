<?php
/**
 * The Template for displaying the product ratings in the product panel
 *
 * Override this template by copying it to yourtheme/wc-vendors/front/ratings
 *
 * @package    WCVendors_Pro
 * @version    1.2.3
 */

// This outputs the star rating
$stars = '';
for ( $i = 1; $i <= stripslashes( $rating ); $i ++ ) {
	$stars .= '<svg class="wcv-icon wcv-icon-sm">
                <use xlink:href="' . WCV_PRO_PUBLIC_ASSETS_URL . 'svg/wcv-icons.svg#wcv-icon-star"></use>
            </svg>';
}
for ( $i = stripslashes( $rating ); $i < 5; $i ++ ) {
	$stars .= '<svg class="wcv-icon wcv-icon-sm">
                    <use xlink:href="' . WCV_PRO_PUBLIC_ASSETS_URL . 'svg/wcv-icons.svg#wcv-icon-star-o"></use>
                </svg>';
}
?>

<h4>
<?php
if ( ! empty( $rating_title ) ) {
		echo $rating_title;
}
?>
	<?php echo $stars; ?></h4>
<span><?php _e( 'Posted on', 'wcvendors-pro' ); ?><?php echo $post_date; ?></span>
				<?php
				_e( ' by ', 'wcvendors-pro' );
				echo $customer_name;
				?>
<br/>
<p><?php echo $comment; ?></p>
<hr/>
