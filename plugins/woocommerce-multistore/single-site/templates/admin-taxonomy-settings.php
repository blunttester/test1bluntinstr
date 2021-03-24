<?php $options = new WOO_MSTORE_OPTIONS_MANAGER(); ?>
<div class="wrap">
	<div id="icon-settings" class="icon32"></div>
	<h2 class='woonet-general-setitngs-header'><?php esc_html_e( 'Custom Taxonomy & Metadata Settings', 'woonet' ); ?></h2>
	<div class='woonet-additional-settings'>  
		<?php if ( $options->get( 'sync-custom-taxonomy' ) == 'yes' ) : ?>
			<a class='button button-primary' href="<?php echo esc_url( admin_url( 'admin.php?page=woonet-set-taxonomy#sec-taxonomy' ) ); ?>" class='Shipping options'> Taxonomy Section</a>
		<?php endif; ?>
		<?php if ( $options->get( 'sync-custom-metadata' ) == 'yes' ) : ?>
			<a class='button button-primary' href="<?php echo esc_url( admin_url( 'admin.php?page=woonet-set-taxonomy#sec-metadata' ) ); ?>" class='Shipping options'> Metadata Section</a>
		<?php endif; ?>
	</div>
	<form id="form_data" name="form" method="post">
		<table class="form-table">
			<tbody>
			<?php if ( $options->get( 'sync-custom-taxonomy' ) == 'yes' ) : ?>
			<tr valign="top">
				<td colsize='2'> <h4 id='sec-taxonomy'> Select the custom taxonomies you want to sync with the child sites. </h4> </td>
			</tr>
				<?php
				if ( ! empty( $GLOBALS['WOO_MSTORE_CUSTOM_TAXONOMIES'] ) ) :
					$saved_taxonomy = get_option( 'woonet_settings_custom_taxonomy', array() );
					?>
					<tr valign="top">
						<th scope="row"> Taxonomy </th>
						<td> Site <a href='#' class='woonet-taxonomy-select-all'> Select All </a> </td>
					</tr>
					<?php foreach ( $GLOBALS['WOO_MSTORE_CUSTOM_TAXONOMIES'] as $tax ) : ?>
						<tr valign="top">
							<th scope="row"><?php echo esc_html_e( $tax ); ?></th>
							<td>
                                <a href='#' class='woonet-taxonomy-select-all-sites'> Select All </a> <br />
								<?php
								$sites = get_option( 'woonet_child_sites' );

								foreach ( $sites as $site ) {
									if ( isset( $saved_taxonomy[ $tax ][ $site['uuid'] ] ) ) {
										$checked = 'checked="checked"';
									} else {
										$checked = '';
									}

									$name = "__woonet_tax_settings[{$tax}][{$site['uuid']}]";
									?>
									<label> <input type='checkbox' name='<?php echo esc_attr( $name ); ?>' value='yes' <?php echo esc_attr( $checked ); ?>  /> <?php echo esc_html_e( trim( str_replace( array( 'http://', 'https://' ), '', $site['site_url'] ), '/' ) ); ?>  </label> <br />
									<?php
								}
								?>
							</td>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
			<?php endif; ?>
				<?php if ( $options->get( 'sync-custom-metadata' ) == 'yes' ) : ?>
				<tr valign="top">
					<td colspan='2'> 
						<h4 id='sec-metadata'> Custom Metadata </h4> 
						<p> Enter the custom metadata keys that need to be synced with the child sites. <strong> Enter only one key in each line. </strong></p>
					</td>
				</tr>
				<tr valign="top">
					<td> 
						<?php $textarea_placeholder = "_custom_meta_key_1 \n _custom_meta_key_2 \n _custom_meta_key_3\n"; ?>
						<textarea cols='50' placeholder="<?php echo esc_html( $textarea_placeholder ); ?>" rows='10' name='__woonet_settings_custom_metadata'><?php echo esc_html( get_option( 'woonet_settings_custom_metadata' ) ); ?></textarea>
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
