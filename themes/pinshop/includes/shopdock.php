<?php
/**
 * Template for cart
 */
global $woocommerce;
?>
<?php themify_shopdock_before(); //hook ?>
<div id="shopdock">

	<div id="shopdock-inner">
		<?php themify_shopdock_start(); //hook ?>
		<div id="cart-tag">
			<span id="cart-loader"></span> 
			<span class="total-item"><?php echo sprintf(_n('%d item', '%d items', $woocommerce->cart->cart_contents_count, 'themify'), $woocommerce->cart->cart_contents_count); ?></span>
		</div>

		<?php
		// check whether cart is not empty
		if(sizeof($woocommerce->cart->get_cart())>0):
		?>
		<div id="cart-wrap">
        
			<div id="cart-list">

				<?php get_template_part('includes/loop-product', 'cart'); ?>
				
			</div>
			<!-- /cart-list -->

			<p class="cart-total">
				<?php
					echo $woocommerce->cart->get_cart_total();
				?>
			</p>
			
			<?php themify_checkout_start(); //hook ?>
			<p class="checkout-button">
			<?php if(sizeof($woocommerce->cart->get_cart()) > 0){ ?>
					<a id="view-cart" href="<?php echo esc_url( wc_get_cart_url() ) ?>">
						<?php _e('View Cart', 'themify') ?>
					</a>
			<?php } ?>
			
				<button type="submit" class="button checkout" onClick="document.location.href='<?php echo esc_url( wc_get_checkout_url() ); ?>'; return false;"><?php _e('Checkout', 'themify')?></button>
			</p>
			<!-- /checkout-botton -->
			<?php themify_checkout_end(); //hook ?>
		</div>
		<!-- /#cart-wrap -->
		<?php endif; // cart whether is not empty?>
		<?php themify_shopdock_end(); //hook ?>
	</div>
	<!-- /shopdock-inner -->
</div>
<!-- /#shopdock -->
<?php themify_shopdock_after(); //hook ?>