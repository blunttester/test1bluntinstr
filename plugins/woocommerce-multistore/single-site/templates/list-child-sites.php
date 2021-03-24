<div class='child_sites'>
	<table> 
		<?php 

		$child_sites = get_option('woonet_child_sites'); 

		if ( !empty( $child_sites ) ) {
			foreach( $child_sites as $child_site ) {
				?>
				<tr> 
					<td> <?php echo $child_site['id']; ?> </td>
					<td> <?php echo $child_site['site_url']; ?> </td>
					<td> <button type='button' class='button-danger'> Delete </button> </td>
				</tr>
				<?php
			}
		}

		?>

	</table>
</div>