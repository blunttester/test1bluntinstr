<div class='woomulti-bulk-sync-page'> 
	<h1> Sync all products in your network. </h1>

	<p> Normally, you would be using the regular sync page as that offers more control. However, when you are setting up the plugin for the first time, you may have a lot of products that you want to sync with your child sites. This feature is designed for that purpose. 
	</p>

	<form id='bulk-sync-form' action='#' method='POST'>
	<input class='select-all-products' type='checkbox' name='select-all-products' checked='checked' value='1' />
	<label> Select All Products </label> <br />
	<h2> Select Categories </h2>
	<p> If you want to select by category, unselect Select All Products </p>
	<?php
		$all_categories = get_categories(
			array(
				'taxonomy'   => 'product_cat',
				'hide_empty' => false,
			)
		);


		foreach ( $all_categories as $cat ) {
			?>
			<input disabled='disabled' type='checkbox' class='select-categories' name='select_categories[]' value='<?php echo $cat->term_id; ?>' />
			<label> <?php echo $cat->name; ?> </label> <br />
			<?php
		}
		?>

	<?php if ( is_multisite() ) : ?>
	<h2> Sync Products From </h2>
	<p> Products in the <strong> current store </strong> will be synced with other stores in the network. <br /> If you want to sync from another site, go to that site instead. </p>
	<select name='select-parent-site'>
		<?php
		$sites = get_sites();

		foreach ( $sites as $site ) {
			if ( $site->blog_id == get_current_blog_id() ) {
				echo "<option value='" . $site->blog_id . "'> " . $site->domain . $site->path . '</option>';
			}
		}
		?>
	</select>
	<?php endif; ?>

	<h2> Select Child Sites </h2>
	<p> Select all the sites you want to sync with. </p>
	<?php
		$all_categories = get_categories(
			array(
				'taxonomy'   => 'product_cat',
				'hide_empty' => false,
			)
		);

		if ( is_multisite() ) {
			$sites = get_sites();

			foreach ( $sites as $site ) {
				?>
			<input checked='checked' type='checkbox' class='select-child-sites child-sites-id-<?php echo $site->blog_id; ?>' name='select_child_sites[]' value='<?php echo $site->blog_id; ?>' />
			<label> <?php echo $site->domain . $site->path; ?> </label> <br />
				<?php
			}
		} else {
			$sites = get_option( 'woonet_child_sites' );

			foreach ( $sites as $site ) {
				?>
			<input checked='checked' type='checkbox' class='select-child-sites child-sites-id-<?php echo $site['uuid']; ?>' name='select_child_sites[]' value='<?php echo $site['uuid']; ?>' />
			<label> <?php echo $site['site_url']; ?> </label> <br />
				<?php
			}
		}

		?>

	<h2> Sync Settings </h2>
	<p> Select stock and sync settings. </p>
	<?php
		$sync_options = array(
			'child-sync' => array(
				'label' => 'Child product inherit Parent changes',
				'value' => 'yes',
			),

			'stock-sync' => array(
				'label' => 'If checked, any stock change will syncronize across product tree',
				'value' => 'yes',
			),
		);

		foreach ( $sync_options as $key => $value ) {
			?>
			<input checked='checked' type='checkbox' class='select-sync-settings <?php echo $key; ?>' name='<?php echo $key; ?>' value='<?php echo $value['value']; ?>' />
			<label> <?php echo $value['label']; ?> </label> <br />
			<?php
		}
		?>


	<div class='sync-progress' style='display: none;'>
		<img src='<?php echo plugins_url( '/../assets/ajax-loader.gif', __FILE__ ); ?>' alt='Loader Image'/>
		<p style='display:block;'> <span style='display:block;'> Sync in progress </span> </p>
	</div>
	<?php if ( ! empty( $_REQUEST['queue_id'] ) ) : ?>
		<input type='hidden' id='start-sync-operation' name='start-sync-operation' value='1' />
	<?php endif; ?>
	<button type='button' id='bulk-sync-button' class='button-primary'> Sync Selected Products </button>
	<button type='button' data-attr='<?php echo network_admin_url() . 'admin.php?page=woonet-bulk-sync-products'; ?>' style='display:none;' id='bulk-sync-reload' class='button-primary'> Complete Sync </button>
	<button type='button' data-attr='<?php echo network_admin_url() . 'admin.php?page=woonet-bulk-sync-products'; ?>' id='bulk-sync-cancel-button' class='button-primary' style="visibility: hidden;"> Cancel </button>
	</form>
</div>
