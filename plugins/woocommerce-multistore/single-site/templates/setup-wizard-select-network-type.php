<div class='woonet-setup-wizard woonet-network-type woonet-pages'>
	<img src='<?php echo plugins_url( '/assets/images/computer.png' ,  dirname(dirname(__FILE__) ) ); ?>' alt='Lock'/>
	<h1> Select Network Type </h1>
	<p> Your network consists of a master store and many child stores. Master store is used as the controller for all child stores. Below you can select the type of store you want to set up on this site.  <a href='#' class='woonet-network-type-whats-difference-btn'> What's the difference? </a></p>
	<p style='display: none;' class='woonet-network-type-whats-difference'>
		Master store can create and sync products across the network, while child stores can only create their own products and receive synced products from the master store. You can view all orders from your child stores only on the master store. The master store also keeps track of stock and manages the synchoronization of products. In any network, there can be only one master store. 
	</p>
	<?php if ( !empty($_SESSION['mstore_form_submit_messages']) ): ?>
		<?php foreach( $_SESSION['mstore_form_submit_messages'] as $error ): ?>
			<div class="error notice">
		        <p><?php _e( esc_html($error), 'woonet' ); ?></p>
		    </div>
		<?php endforeach; ?>
	<?php endif; ?>
	<?php $network_type = get_option('woonet_network_type'); ?>
	<form  class='network-type-form' autocomplete="off" action='<?php menu_page_url( 'woonet-network-type' ) ?>' method='post'> 
		<?php wp_nonce_field( 'woonet_select_network_type'); ?>
		<p> 
			<input type="radio" id='woonet_network_type_op1' name="woonet_network_type" 
					value="master" <?php echo !empty($network_type) && $network_type == 'master' ? 'checked="checked"': ''; ?>>
			<label for='woonet_network_type_op1'> Master Store  </label>
		</p>    

        <p> 
        	<input type="radio" id='woonet_network_type_op2' name="woonet_network_type" 
        			value="child" <?php echo !empty($network_type) && $network_type == 'child' ? 'checked="checked"': ''; ?>>
        	<label for='woonet_network_type_op2'> Child Store </label>
        </p>

        <p class='spacer'> </p>

		<button type='submit' class='button-primary'> Submit </button>
	</form>
</div>