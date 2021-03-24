<div class='woonet-setup-wizard woonet-network-type'>
	<img src='<?php echo plugins_url( '/assets/images/checklist.png' ,  dirname(dirname(__FILE__) ) ); ?>' alt='Lock'/>
	<h1> Setup Wizard </h1>
	<p>Thank you for installing the plugin. The wizard will help you to get started with the plugin.</p>
	<?php if ( !empty($_SESSION['mstore_form_submit_messages']) ): ?>
		<?php foreach( $_SESSION['mstore_form_submit_messages'] as $error ): ?>
			<div class="error notice">
		        <p><?php _e( esc_html($error), 'woonet' ); ?></p>
		    </div>
		<?php endforeach; ?>
	<?php endif; ?>
	<?php 
	$network_type = get_option('woonet_network_type');
	$mstore_license = get_option('mstore_license'); 
	$woonet_child_sites = get_option('woonet_child_sites');
	$woonet_master_connect = get_option('woonet_master_connect');
	?>
	<ul class='wizard-checkelist'>
		<li> 
			<?php if ( !empty($network_type) ): ?>
				<img src='<?php echo plugins_url( '/assets/images/checked.png' ,  dirname(dirname(__FILE__) ) ); ?>' />
			<?php else: ?>
				<img src='<?php echo plugins_url( '/assets/images/checked_unchecked.png' ,  dirname(dirname(__FILE__) ) ); ?>' />
			<?php endif; ?>
			<a href='<?php echo admin_url('admin.php?page=woonet-network-type'); ?>'> <h3> Select Network Type </h3> </a>
		</li>

		<li> 
			<?php if ( (!empty($network_type) && $network_type == 'child') || !empty($mstore_license) ): ?>
				<img src='<?php echo plugins_url( '/assets/images/checked.png' ,  dirname(dirname(__FILE__) ) ); ?>' />
			<?php else: ?>
				<img src='<?php echo plugins_url( '/assets/images/checked_unchecked.png' ,  dirname(dirname(__FILE__) ) ); ?>' />
			<?php endif; ?>
			<a href='<?php echo admin_url('admin.php?page=woonet-license-key'); ?>'> 
				<?php if ( !empty($network_type) && $network_type == 'master' && !empty($mstore_license) ): ?>
					<h3> Manage License Key </h3> 
				<?php else: ?>
					<h3> Enter License Key </h3> 
				<?php endif; ?>
			</a>
		</li>

		<?php if ( $network_type == 'child' ): ?>
			<li> 
				<?php if ( !empty( $woonet_master_connect ) ): ?>
					<img src='<?php echo plugins_url( '/assets/images/checked.png' ,  dirname(dirname(__FILE__) ) ); ?>' />
				<?php else: ?>
					<img src='<?php echo plugins_url( '/assets/images/checked_unchecked.png' ,  dirname(dirname(__FILE__) ) ); ?>' />
				<?php endif; ?>
				<a href='<?php echo admin_url('admin.php?page=woonet-connect-master'); ?>'> <h3> Connect to Master Site </h3> </a>
			</li>
		<?php endif; ?>

		<?php if ( $network_type == 'master' ): ?>
			<li> 
				<?php if ( !empty( $woonet_child_sites ) ): ?>
					<img src='<?php echo plugins_url( '/assets/images/checked.png' ,  dirname(dirname(__FILE__) ) ); ?>' />
				<?php else: ?>
					<img src='<?php echo plugins_url( '/assets/images/checked_unchecked.png' ,  dirname(dirname(__FILE__) ) ); ?>' />
				<?php endif; ?>
				<a href='<?php echo admin_url('admin.php?page=woonet-connect-child'); ?>'> <h3> Connect Child Sites </h3> </a>
			</li>
		<?php endif; ?>
	</ul>
</div>