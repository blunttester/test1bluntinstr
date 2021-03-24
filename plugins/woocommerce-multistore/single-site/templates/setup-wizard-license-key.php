<div class='woonet-setup-wizard woonet-license-key'>
	<img src='<?php echo plugins_url( '/assets/images/lock.png' ,  dirname(dirname(__FILE__) ) ); ?>' alt='Lock'/>
	<?php $mstore_license = get_option('mstore_license');  ?>
	<?php if ( get_option('woonet_network_type') == 'master' ): ?>
		<?php if ( !empty($mstore_license) ): ?>
			<h1> Remove License Key </h1>
			<p> Removing the license will deactivate the plugin. You can also manage all your licenses from your 
				<a href='https://woomultistore.com/my-account/orders/' target='_blank'> account dashboard </a> 
			</p>
		<?php else: ?>
			<h1> Enter License Key </h1>
			<p> Please go to your <a href='https://woomultistore.com/my-account/' target='_blank'> account dashboard </a> 
				and generate a license key for this site. You will need one license key for each site. </p>
		<?php endif; ?>
	<?php if ( !empty($_SESSION['mstore_form_submit_messages']) ): ?>
		<?php foreach( $_SESSION['mstore_form_submit_messages'] as $error ): ?>
			<div class="error notice">
		        <p><?php _e( esc_html($error), 'woonet' ); ?></p>
		    </div>
		<?php endforeach; ?>
	<?php endif; ?>
	<form  autocomplete="off" action='<?php menu_page_url( 'woonet-license-key' ) ?>' method='post'> 
		<?php wp_nonce_field( 'woonet_license_verify_submit'); ?>
		<?php if ( !empty($mstore_license) ): ?>
			<input type='hidden' name='woonet_license_key_remove' value='yes'>
			<button onclick='return confirm("Do you really want to remove the license?");' type='submit' style='background:#e14d43; border-color:#e14d43; width:100%;' class='button-primary button-remove-license'> Remove </button>
		<?php else: ?>
			<input type='text' name='woonet_license_key' value='' placeholder="Enter your license key here">
			<button type='submit' class='button-primary'> Submit </button>
		<?php endif; ?>
	</form> 
	<?php else: ?>
		<h1 style='text-align: center;'> Child site doesn't need a license. </h1>
	<?php endif; ?>
</div>