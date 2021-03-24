<?php

/**
 * The feedback edit form
 *
 * This file is used to display the feedback edit form on the backend.
 *
 * @link       http://www.wcvendors.com
 * @since      1.0.0
 * @version    1.3.3
 *
 * @package    WCVendors_Pro
 * @subpackage WCVendors_Pro/includes/partials/ratings
 */

$user         = get_userdata( stripslashes( $feedback->vendor_id ) );
$vendor_id    = '<a href="' . admin_url( 'user-edit.php?user_id=' . stripslashes( $feedback->vendor_id ) ) . '">' . WCV_Vendors::get_vendor_shop_name( stripslashes( $feedback->vendor_id ) ) . '</a>';
$product_link = '<a href="' . admin_url( 'post.php?post=' . stripslashes( $feedback->product_id ) . '&action=edit' ) . '">' . get_the_title( stripslashes( $feedback->product_id ) ) . '</a>';
$order_link   = '<a href="' . admin_url( 'post.php?post=' . stripslashes( $feedback->order_id ) . '&action=edit' ) . '">' . stripslashes( $feedback->order_id ) . '</a>';
$user         = get_userdata( stripslashes( $feedback->customer_id ) );
$customer     = '<a href="' . admin_url( 'user-edit.php?user_id=' . stripslashes( $feedback->customer_id ) ) . '">' . $user->display_name . '</a>';
$postdate     = date_i18n( get_option( 'date_format' ), strtotime( $feedback->postdate ) );


?>

<form action="" method="post">
	<input type="hidden" name="rating_id" value="<?php echo $feedback->id; ?>"/>
	<input type="hidden" name="action" value="save"/>

	<h3><?php printf( __( 'Edit %s Rating', 'wcvendors-pro' ), wcv_get_vendor_name() ); ?></h3>
	<h4><?php echo __( 'Rating Details', 'wcvendors-pro' ); ?></h4>
	<p><?php printf( __( '<strong>Order #: %1$s</strong>| Posted by : %2$s for %3$s on %4$s', 'wcvendors-pro' ), $order_link, $customer, $product_link, $postdate ); ?>
	</p>

	<table class="form-table wcv-form-table">
		<tbody>
		<tr>
			<th scope="row">
				<label for="rating"><?php echo __( 'Feedback rating', 'wcvendors-pro' ); ?></label>
			</th>

			<td>
				<?php
				$rating = '';
				for ( $i = 1; $i <= stripslashes( $feedback->rating ); $i ++ ) {
					$rating .= '<svg class="wcv-icon wcv-icon-sm">
						<use xlink:href="' . WCV_PRO_PUBLIC_ASSETS_URL . 'svg/wcv-icons.svg#wcv-icon-star"></use>
					</svg>';
				}
				for ( $i = stripslashes( $feedback->rating ); $i < 5; $i ++ ) {
					$rating .= '<svg class="wcv-icon wcv-icon-sm">
						<use xlink:href="' . WCV_PRO_PUBLIC_ASSETS_URL . 'svg/wcv-icons.svg#wcv-icon-star-o"></use>
					</svg>';
				}
				echo $rating;
				?>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<label for="rating_title"><?php echo __( 'Feedback title', 'wcvendors-pro' ); ?></label>
			</th>

			<td>
				<input type="text" value="<?php echo $feedback->rating_title; ?>" id="rating_title" name="rating_title">
				<br>
				<span class="description"><?php echo __( 'The feedback title.', 'wcvendors-pro' ); ?></span>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<label for="comments"><?php echo __( 'Feedback comment', 'wcvendors-pro' ); ?></label>
			</th>

			<td>
				<textarea id="rating_comments" name="rating_comments"><?php echo $feedback->comments; ?></textarea>
				<br>
				<span class="description"><?php echo __( 'The feedback comment.', 'wcvendors-pro' ); ?></span>
			</td>
		</tr>
		</tbody>
	</table>
	<p class="submit"><input type="submit" value="Save changes" class="button-primary" name="Submit"></p>
</form>
