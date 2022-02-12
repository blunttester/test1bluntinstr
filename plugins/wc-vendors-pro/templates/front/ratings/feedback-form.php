<?php
/**
 * The template for displaying the feedback form for vendor ratings
 *
 * Override this template by copying it to yourtheme/wc-vendors/front/ratings
 *
 * @package    WCVendors_Pro
 * @version    1.2.5
 */
?>

<?php wc_print_notices(); ?>

<?php if ( isset( $_GET['wcv_order_id'] ) ) : ?>

	<h1><?php esc_attr_e( 'Rate your experience', 'wcvendors-pro' ); ?></h1>

	<p>
		<?php
		/* translators: %1$s: order number, %2$s: order date format, %3$s: order status */
		printf( __( 'Order #<mark class="order-number">%1$s</mark> was placed on <mark class="order-date">%2$s</mark> and is currently <mark class="order-status">%3$s</mark>.', 'wcvendors-pro' ), $order->get_order_number(), date_i18n( get_option( 'date_format' ), strtotime( $order_date ) ), $order->get_status() );
		?>
	</p>

	<form method="post" name="wcv_feedback" class="wcv-form" data-parsley-validate>

		<?php

		$fn = 0;

		foreach ( $products as $product ) {
			$product_id       = $product['product_id'];
			$product_feedback = wp_filter_object_list( $feedback, array( 'product_id' => $product_id ) );
			if ( ! empty( $product_feedback ) ) {
				$product_feedback = reset( $product_feedback );
			}
			$vendor_id = WCV_Vendors::get_vendor_from_product( $product_id );
			$shop_name = WCV_Vendors::is_vendor( $vendor_id )
				? sprintf( '<a href="%s">%s</a>', WCV_Vendors::get_vendor_shop_page( $vendor_id ), WCV_Vendors::get_vendor_shop_name( $vendor_id ) )
				: get_bloginfo( 'name' );

			$comments     = $product_feedback ? $product_feedback->comments : '';
			$rating_title = $product_feedback ? $product_feedback->rating_title : '';
			$rating       = ! empty( $product_feedback ) ? $product_feedback->rating : '';

			// Does the product exist ?
			if ( is_string( get_post_status( $product['product_id'] ) ) ) {
				echo '<a href="' . get_permalink( $product_id ) . '">' . $product['name'] . '</a> ' . __( 'from', 'wcvendors-pro' ) . '&nbsp;' . $shop_name . '</br>';
			} else {
				echo $product['name'] . ' from ' . $shop_name . '</br>';
			}
			?>
			<div class="control-group">
				<label class="rating-label"><?php _e( 'Rate the product on a scale of 1 to 5. (1=Poor and 5=Excellent)', 'wcvendors-pro' ); ?></label>
				<div class="all-100">
					<input type="hidden" id="wcv-star-rating-<?php echo $fn; ?>-input"
						name="wcv-feedback[<?php echo $fn; ?>][star-rating]"
						value="<?php echo $rating; ?>"
						class="star-rating-input"
						data-fn="<?php echo $fn; ?>">

					<label for="wcv-star-rating-<?php echo $fn; ?>" id="wcv-star-rating-<?php echo $fn; ?>-label" class="wcv_star-rating-container" data-fn="<?php echo $fn; ?>"
						data-star-open="<?php echo WCV_PRO_PUBLIC_ASSETS_URL; ?>svg/wcv-icons.svg#wcv-icon-star-o"
						data-star-closed="<?php echo WCV_PRO_PUBLIC_ASSETS_URL; ?>svg/wcv-icons.svg#wcv-icon-star">

						<?php for ( $i = 1; $i < 6; $i ++ ) : ?>
						<a href="#" id="star-<?php echo $fn . '-' . $i; ?>" class="star-icon" id="wcv-star-rating-<?php echo $fn; ?>" data-fn="<?php echo $fn; ?>" data-index="<?php echo $i; ?>">
							<svg class="wcv-icon wcv-icon-sm">
								<use xlink:href="<?php echo WCV_PRO_PUBLIC_ASSETS_URL; ?>svg/wcv-icons.svg#wcv-icon-star-o"></use>
							</svg>
						</a>
						<?php endfor; ?>
					</label>
				</div>
				<div class="all-30"></div>
			<p></p>

			<input type="text" class="feedback-title" name="wcv-feedback[<?php echo $fn; ?>][rating_title]" style="width:60%"
				   value="<?php echo $rating_title; ?>" placeholder="<?php _e( 'Title', 'wcvendors-pro' ); ?>" data-parsley-required="true"/>
			<textarea name="wcv-feedback[<?php echo $fn; ?>][comments]" style="width:60%"
					  placeholder="<?php _e( 'Comments: (e.g. delivery experience, item as described, quality of customer service)', 'wcvendors-pro' ); ?>"
					  data-parsley-trigger="keyup"
					  data-parsley-minlength="20"
			><?php echo $comments; ?></textarea>
			<input type="hidden" name="wcv-feedback[<?php echo $fn; ?>][vendor_id]" value="<?php echo $vendor_id; ?>">
			<input type="hidden" name="wcv-feedback[<?php echo $fn; ?>][product_id]" value="<?php echo $product_id; ?>">
			<input type="hidden" name="wcv-feedback[<?php echo $fn; ?>][customer_id]" value="<?php echo get_current_user_id(); ?>">
			<?php if ( $product_feedback ) : ?>
				<input type="hidden" name="wcv-feedback[<?php echo $fn; ?>][feedback_id]" value="<?php echo $product_feedback->id; ?>">
			<?php endif; ?>
			<br/>

			<br/>

			<?php
			$fn ++;
		}
		?>

		<p><input type="submit" value="<?php esc_attr_e( 'Submit feedback', 'wcvendors-pro' ); ?>"></p>
		<input type="hidden" name="wcv-order_id" value="<?php echo $order->get_order_number(); ?>">
		<?php wp_nonce_field( 'wcv-submit_feedback', '_wcv-submit_feedback' ); ?>
		<input type="hidden" name="action" value="post">

	</form>

<?php else : ?>


<?php endif; ?>
