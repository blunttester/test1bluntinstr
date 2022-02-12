<tr valign="top">
	<th scope="row" class="titledesc">
		<label><?php _e( 'License status', 'wc-checkout-sis' ); ?></label>
	</th>
	<td class="forminp">
		<div>
			<?php if ( $status ) { ?>
				<span class="markup-license-status-label license-ok"><?php _e( 'Active', 'wc-checkout-sis' ); ?></span>	
			<?php } else if ( $status_unknown ) { ?>
				<span class="markup-license-status-label license-unknown"><?php _e( 'Unknown', 'wc-checkout-sis' ); ?></span>	
			<?php } else { ?>
				<span class="markup-license-status-label license-disabled"><?php _e( 'Not active', 'wc-checkout-sis' ); ?></span>
				<?php if ( $error ) { ?>
					<span class="markup-license-error">(<?php echo $error; ?>)</span>
				<?php } ?>
			<?php } ?>
		</div>

		<?php if ( $last_checked ) { ?>
			<div class="markup-license-last-checked">
				<?php printf( __( 'Last checked: %s', 'wc-checkout-sis' ), get_date_from_gmt( date( 'Y-m-d H:i:s', $last_checked ), 'H:i - j.n.Y' ) ); ?>
			</div>
		<?php } ?>
	</td>
</tr>
