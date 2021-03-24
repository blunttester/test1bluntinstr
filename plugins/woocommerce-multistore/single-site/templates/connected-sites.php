<?php
$active_sites   = get_option( 'woonet_child_sites', array() );
$inactive_sites = get_option( 'woonet_child_sites_deactivated', array() );

if ( get_transient( 'woomulti_check_site_connection' ) ) {
	$connected_sites = get_transient( 'woomulti_check_site_connection' );
} else {
	$connected_sites = array_merge( $active_sites, $inactive_sites );
	set_transient( 'woomulti_check_site_connection', $connected_sites, 60 );
}

$connected_sites = array_merge( $active_sites, $inactive_sites );

$_engine            = new WOO_MSTORE_SINGLE_NETWORK_SYNC_ENGINE();
$connection_results = $_engine->check_connection_details();
?>
<div class='woonet-pages'>
	<h1>  Network Sites </h1>
	<p> A list of sites connected to the network. </p>
	<div class="error notice" style='display: none;'></div>
	<div class="notice-success notice" style='display: none;'></div>

	<a class='add-to-network-btn' href='<?php echo admin_url( 'admin.php?page=woonet-connect-child' ); ?>'> Add </a>
		
	<?php if ( ! empty( $connected_sites ) ) : ?>

	<table class='woonet-sites-table'> 
		<tr> 
			<th> Site </th>
			<th> Status </th>
			<th> Date Added </th>
			<th> Connection Status </th>
			<th> Action </th>
		</tr>
		<?php foreach ( $connected_sites as $key => $connected_site ) : ?>
			<tr> 
				<td> 
					<a target='_blank' href='<?php echo $connected_site['site_url']; ?>'> 
						<?php
						echo str_replace(
							array(
								'http://',
								'https://',
							),
							'',
							$connected_site['site_url']
						);
						?>
						 
					</a> 
				</td>
				<td> 
				<?php
				if ( isset( $active_sites [ $key ] ) ) {
					echo 'Active';
				} else {
					echo 'Inactive';
				}
				?>
				 </td>
				<td> <?php echo date( 'Y/m/d', $connected_site['date_added'] ); ?> </td>
				<td style="max-width:150px"> 
				<?php
				if ( isset( $connection_results[ $connected_site['uuid'] ] ) ) {
					switch ( $connection_results[ $connected_site['uuid'] ]['status'] ) {
						case 'success':
							echo 'Child site version ' . esc_html( $connection_results[ $connected_site['uuid'] ]['result']['version'] );
							break;
						case 'failed':
							echo esc_html( $connection_results[ $connected_site['uuid'] ]['message'] );
							break;
					}
				}
				?>
				 </td>
				<td> 
					<form action='<?php echo admin_url( 'admin.php?page=woonet-connected-sites' ); ?>' method='POST'> 
						<?php wp_nonce_field( 'woonet_delete_site' ); ?>
						<input type="hidden" value='<?php echo $key; ?>' name="__key">
						<button type='submit' class='button-secondary' name='submit' value='remove' onclick='return confirm("Do you really want to delete the site?");'> Remove </button>
						<?php
						if ( isset( $active_sites [ $key ] ) ) {
							?>
								<button type='submit' class='button-primary woomulti-deactivate-button' name='submit' value='deactivate' onclick='return confirm("Do you really want to deactivate the site? Deactivated sites are hidden from the sync options, but the settings will be preserved.");'> Deactivate </button>
							<?php
						} else {
							?>
								<button type='submit' class='button-primary' name='submit' value='activate' onclick='return confirm("Do you really want to activate the site?");'> Activate </button>
							<?php
						}
						?>
					</form>
				</td>
			</tr>
		<?php endforeach; ?>
	</table>
	<?php else : ?>
		<p class='woonet-sites-empty'> Follow the <a href='<?php echo admin_url( 'admin.php?page=woonet-setup-wizard' ); ?>'> Setup Wizard </a> to add a new site. </p>
	<?php endif; ?>
</div>
