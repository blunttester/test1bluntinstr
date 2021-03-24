<div class='woonet-setup-wizard woonet-license-key'>
	<img src='<?php echo plugins_url( '/assets/images/connect.png' ,  dirname(dirname(__FILE__) ) ); ?>' alt='Lock'/>
	<?php $master_site = get_option('woonet_master_connect'); ?>
	<?php if ( ! $master_site ) :?>
		<h1> Connect to Master Site </h1>
		<p> Please enter the code that you generated from the master site. </p>
		<div class="error notice" style='display: none;'>
	    </div>
	    <div class="notice-success notice" style='display: none;'>
	    </div>
		<form  autocomplete="off" action='#' method='GET' id='woonet-add-master-site'> 
			<input type='text' value='' placeholder="Connect Code">
			<button type='button' class='button-primary button-connect'> Add </button>
		</form>
	<?php else: ?>
		<h1> Connected to Master Site </h1>
		<p> Once disconnected, child site will no longer receive updates from the master site. You should also delete the site from the master site.  </p>
		<div class="error notice" style='display: none;'>
	    </div>
	    <div class="notice-success notice" style='display: none;'>
	    </div>
		<form  autocomplete="off" action='#' method='GET' id='woonet-delete-master-site'> 
			<br />
			<p> Receiving updates from <a target='_blank' href='<?php echo $master_site['master_url']; ?>'><?php echo $master_site['master_url']; ?></a></p>

			<button type='button' class='button-primary button-disconnect' style='width: 100%;'> Disconnect </button>
		</form>
	<?php endif; ?>
</div>