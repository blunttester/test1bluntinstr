<?php

/**
 * Ratings link
 *
 * This file is used to display the ratings link
 *
 * @link       http://www.wcvendors.com
 * @since      1.0.0
 * @version    1.4.5
 *
 * @package    WCVendors_Pro
 * @subpackage WCVendors_Pro/includes/partials/ratings
 */

$ratings_text = ( $ratings_count == 1 ) ? __( 'rating', 'wcvendors-pro' ) : __( 'ratings', 'wcvendors-pro' );

?>

<?php if ( $ratings_count == 0 && $feedback_system == 1 ) { ?>
	<?php for ( $i = 1; $i <= 5; $i ++ ) { ?>
		<svg class="wcv-icon wcv-icon-sm">
			<use xlink:href="<?php echo WCV_PRO_PUBLIC_ASSETS_URL; ?>svg/wcv-icons.svg#wcv-icon-star"></use>
		</svg>
	<?php } ?>
	<?php echo sprintf( __( '( %s ) ratings', 'wcvendors-pro' ), $ratings_count ); ?>

<?php } elseif ( $ratings_count == 0 && $feedback_system == 0 ) { ?>
	<?php for ( $i = 1; $i <= 5; $i ++ ) { ?>
		<svg class="wcv-icon wcv-icon-sm">
			<use xlink:href="<?php echo WCV_PRO_PUBLIC_ASSETS_URL; ?>svg/wcv-icons.svg#wcv-icon-star-o"></use>
		</svg>
	<?php } ?>
	<?php echo sprintf( '( %s %s )', $ratings_count, $ratings_text ); ?>
<?php } else { ?>

	<?php
	if ( $link ) {
		?>
		<a href="<?php echo $url; ?>"><?php } ?>
	<?php for ( $i = 1; $i <= number_format( $ratings_average ); $i ++ ) { ?>
		<svg class="wcv-icon wcv-icon-sm">
			<use xlink:href="<?php echo WCV_PRO_PUBLIC_ASSETS_URL; ?>svg/wcv-icons.svg#wcv-icon-star"></use>
		</svg>
	<?php } ?>
	<?php for ( $i = number_format( $ratings_average ); $i < 5; $i ++ ) { ?>
		<svg class="wcv-icon wcv-icon-sm">
			<use xlink:href="<?php echo WCV_PRO_PUBLIC_ASSETS_URL; ?>svg/wcv-icons.svg#wcv-icon-star-o"></use>
		</svg>
	<?php } ?>
	<span class="txt">
		<?php echo sprintf( '( %s %s ) %s', $ratings_count, $ratings_text, $link_text ); ?>
	</span>
	<?php
	if ( $link ) {
		?>
		 </a><?php } ?>

<?php } ?>
