<?php 
    global $WOO_MSTORE;
    $options = $WOO_MSTORE->functions->get_options(); 
?>
<div class="wrap">
	<div id="icon-settings" class="icon32"></div>
	<h2 class='woonet-general-setitngs-header'><?php esc_html_e( 'Custom Taxonomy & Metadata Settings', 'woonet' ); ?></h2>
	<div class='woonet-additional-settings'>  
		<?php if ( isset( $options['sync-custom-taxonomy'] ) && $options['sync-custom-taxonomy'] == 'yes' ) : ?>
			<a class='button button-primary' href="<?php echo esc_url( network_admin_url( 'admin.php?page=woonet-set-taxonomy#sec-taxonomy' ) ); ?>" class='Shipping options'> Taxonomy Section</a>
		<?php endif; ?>
		<?php if ( isset($options['sync-custom-metadata']) && $options['sync-custom-metadata'] == 'yes' ) : ?>
			<a class='button button-primary' href="<?php echo esc_url( network_admin_url( 'admin.php?page=woonet-set-taxonomy#sec-metadata' ) ); ?>" class='Shipping options'> Metadata Section</a>
		<?php endif; ?>
	</div>
	<form id="form_data" name="form" method="post">
		<table class="form-table">
			<tbody>
            <?php if ( isset( $options['sync-custom-taxonomy'] ) && $options['sync-custom-taxonomy'] == 'yes' ) : ?>
                <tr valign="top">
					<td colspan='2'> 
						<h4 id='sec-taxonomy'> Custom Taxonomy </h4> 
                        <p> Enter the custom taxonomies you want to sync with child products when a product is synced. Enter only one taxonomy in each line. </p>
					</td>
				</tr>
				<tr valign="top">
					<td> 
						<?php $textarea_placeholder = "_custom_taxonomy_key_1 \n _custom_taxonomy_key_2 \n _custom_taxonomy_key_3\n"; ?>
						<textarea cols='50' placeholder="<?php echo esc_html( $textarea_placeholder ); ?>" rows='10' name='__woonet_settings_custom_taxonomies'><?php echo esc_html( get_site_option( 'woonet_settings_custom_taxonomy' ) ); ?></textarea>
					</td>
                </tr>
			<?php endif; ?>
				<?php if ( isset($options['sync-custom-metadata']) && $options['sync-custom-metadata'] == 'yes' ) : ?>
				<tr valign="top">
					<td colspan='2'> 
						<h4 id='sec-metadata'> Custom Metadata </h4> 
						<p> Enter the custom metadata keys that need to be synced when a product is synced. Enter only one metakey in each line.</p>
					</td>
				</tr>
				<tr valign="top">
					<td> 
						<?php $textarea_placeholder = "_custom_meta_key_1 \n _custom_meta_key_2 \n _custom_meta_key_3\n"; ?>
						<textarea cols='50' placeholder="<?php echo esc_html( $textarea_placeholder ); ?>" rows='10' name='__woonet_settings_custom_metadata'><?php echo esc_html( get_site_option( 'woonet_settings_custom_metadata' ) ); ?></textarea>
					</td>
				</tr>
				<?php endif; ?>
			</tbody>
		</table>
		<p class="submit">
			<input type="submit" name="Submit" class="button-primary"
				value="<?php esc_html_e( 'Save Settings', 'woonet' ); ?>">
		</p>
		<?php wp_nonce_field( 'mstore_form_submit_taxonomies', '_mstore_form_submit_taxonomies_nonce' ); ?>
	</form>
</div>
