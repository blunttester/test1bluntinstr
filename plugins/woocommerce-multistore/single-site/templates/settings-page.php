<?php $options = get_option('woonet_options', array());  ?>
<div class="wrap woonet-settings-page">
	<?php if ( get_option('woonet_network_type') == 'master' ): ?>
		<h2><?php esc_html_e( 'Global Settings', 'woonet' ); ?></h2>
	<?php endif; ?>

	<form id="form_data" name="form" method="post">
		<br/>
		<?php if ( get_option('woonet_network_type') == 'master' ): ?>
		<table class="form-table">
			<tbody>
				<tr valign="top">
					<th scope="row">
						<select name="synchronize-stock">
							<option value="yes" 
									<?php selected( 'yes', $options['synchronize-stock'] ); ?>><?php esc_html_e( 'Yes', 'woonet' ); ?></option>
							<option value="no" <?php selected( 'no', $options['synchronize-stock'] ); ?>>
									<?php esc_html_e( 'No', 'woonet' ); ?></option>
						</select>
					</th>
					<td>
						<label><?php esc_html_e( 'Always maintain stock synchronization for re-published products', 'woonet' ); ?>
							<span class='tips' data-tippy-content='<?php esc_html_e( 'Stock updates either manually or checkout will also change other shops that have the product.', 'woonet' ); ?>'>
							<span class="dashicons dashicons-info"></span></span>
						</label>
					</td>
				</tr>

				<tr valign="top">
					<th scope="row">
						<select name="sync-all-metadata">
							<option value="no" <?php selected( 'no', $options['sync-all-metadata'] ); ?>><?php esc_html_e( 'No', 'woonet' ); ?></option>
							<option value="yes" <?php selected( 'yes', $options['sync-all-metadata'] ); ?>><?php esc_html_e( 'Yes', 'woonet' ); ?></option>
						</select>
					</th>
					<td>
						<label><?php esc_html_e( 'Sync metadata created by other plugins', 'woonet' ); ?>
							<span class='tips'
								  data-tippy-content='<?php esc_html_e( 'If enabled, all metadata will be synced. It may break some plugins.', 'woonet' ); ?>'>
							<span class="dashicons dashicons-info"></span></span></label>
					</td>
				</tr>

				<tr valign="top">
					<th scope="row">
						<select name="synchronize-trash">
							<option value="yes" <?php selected( 'yes', $options['synchronize-trash'] ); ?>><?php esc_html_e( 'Yes', 'woonet' ); ?></option>
							<option value="no" <?php selected( 'no', $options['synchronize-trash'] ); ?>><?php esc_html_e( 'No', 'woonet' ); ?></option>
						</select>
					</th>
					<td>
						<label><?php esc_html_e( 'Trash the child product when the parent product is trashed', 'woonet' ); ?>
							<span class='tips'
								  data-tippy-content='<?php esc_html_e( 'Sync child product status when the parent product is trashed/untrashed/deleted.', 'woonet' ); ?>'>
							<span class="dashicons dashicons-info"></span></span></label>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">
						<select name="publish-capability">
							<option value="administrator" <?php selected( 'administrator', $options['publish-capability'] ); ?>><?php esc_html_e( 'Administrator', 'woonet' ); ?></option>
							<option value="shop_manager" <?php selected( 'shop_manager', $options['publish-capability'] ); ?>><?php esc_html_e( 'Shop Manager', 'woonet' ); ?></option>
						</select>
					</th>
					<td>
						<label><?php esc_html_e( 'Minimum user role to allow MultiStore Publish', 'woonet' ); ?></label>
					</td>
				</tr>
			</tbody>
		</table>
		<?php endif; ?>

	<?php
	echo "<h2> Fields control </h2>";
	echo '<h4>' . __( 'Child product inherit Parent changes', 'woonet' ) . '</h4>';
	echo '<div id="fields-control">';
	echo '<table class="form-table"><tbody>';

	?>

	<?php

	$option_name = 'child_inherit_changes_fields_control__status';
	echo '<tr valign="top"><th scope="row">';
		printf(
			'<select name="%s"><option value="yes" %s>%s</option><option value="no" %s>%s</option></select>',
			"{$option_name}",
			selected( $options[ $option_name ], 'yes', false ),
			__( 'Yes', 'woonet' ),
			selected( $options[ $option_name ], 'no', false ),
			__( 'No', 'woonet' )
		);
		echo '</th><td>';
			printf(
				'<label>%s<span class="tips" data-tippy-content="%s"><span class="dashicons dashicons-info"></span></span></label>',
				__( 'Child product inherit product status changes', 'woonet' ),
				__( 'This works in conjunction with Child product inherit Parent changes being active on individual product page.', 'woonet' )
			);
		echo '</td></tr>';


	$option_name = 'child_inherit_changes_fields_control__title';
	echo '<tr valign="top"><th scope="row">';
		printf(
			'<select name="%s"><option value="yes" %s>%s</option><option value="no" %s>%s</option></select>',
			"{$option_name}",
			selected( $options[ $option_name ], 'yes', false ),
			__( 'Yes', 'woonet' ),
			selected( $options[ $option_name ], 'no', false ),
			__( 'No', 'woonet' )
		);
		echo '</th><td>';
			printf(
				'<label>%s<span class="tips" data-tippy-content="%s"><span class="dashicons dashicons-info"></span></span></label>',
				__( 'Child product inherit title changes', 'woonet' ),
				__( 'This works in conjunction with Child product inherit Parent changes being active on individual product page.', 'woonet' )
			);
		echo '</td></tr>';


		$option_name = 'child_inherit_changes_fields_control__description';
		echo '<tr valign="top"><th scope="row">';
			printf(
				'<select name="%s"><option value="yes" %s>%s</option><option value="no" %s>%s</option></select>',
				"{$option_name}",
				selected( $options[ $option_name ], 'yes', false ),
				__( 'Yes', 'woonet' ),
				selected( $options[ $option_name ], 'no', false ),
				__( 'No', 'woonet' )
			);
		echo '</th><td>';
			printf(
				'<label>%s<span class="tips" data-tippy-content="%s"><span class="dashicons dashicons-info"></span></span></label>',
				__( 'Child product inherit description changes', 'woonet' ),
				__( 'This works in conjunction with Child product inherit Parent changes being active on individual product page.', 'woonet' )
			);
		echo '</td></tr>';

		$option_name = 'child_inherit_changes_fields_control__short_description';
		echo '<tr valign="top"><th scope="row">';
			printf(
				'<select name="%s"><option value="yes" %s>%s</option><option value="no" %s>%s</option></select>',
				"{$option_name}",
				selected( $options[ $option_name ], 'yes', false ),
				__( 'Yes', 'woonet' ),
				selected( $options[ $option_name ], 'no', false ),
				__( 'No', 'woonet' )
			);
		echo '</th><td>';
			printf(
				'<label>%s<span class="tips" data-tippy-content="%s"><span class="dashicons dashicons-info"></span></span></label>',
				__( 'Child product inherit short description changes', 'woonet' ),
				__( 'This works in conjunction with Child product inherit Parent changes being active on individual product page.', 'woonet' )
			);
		echo '</td></tr>';

		$option_name = 'child_inherit_changes_fields_control__price';
		echo '<tr valign="top"><th scope="row">';
			printf(
				'<select name="%s"><option value="yes" %s>%s</option><option value="no" %s>%s</option></select>',
				"{$option_name}",
				selected( $options[ $option_name ], 'yes', false ),
				__( 'Yes', 'woonet' ),
				selected( $options[ $option_name ], 'no', false ),
				__( 'No', 'woonet' )
			);
		echo '</th><td>';
			printf(
				'<label>%s<span class="tips" data-tippy-content="%s"><span class="dashicons dashicons-info"></span></span></label>',
				__( 'Child product inherit price changes', 'woonet' ),
				__( 'This works in conjunction with Child product inherit Parent changes being active on individual product page.', 'woonet' )
			);
		echo '</td></tr>';

		$option_name = 'child_inherit_changes_fields_control__product_cat';
		echo '<tr valign="top"><th scope="row">';
			printf(
				'<select name="%s"><option value="yes" %s>%s</option><option value="no" %s>%s</option></select>',
				"{$option_name}",
				selected( $options[ $option_name ], 'yes', false ),
				__( 'Yes', 'woonet' ),
				selected( $options[ $option_name ], 'no', false ),
				__( 'No', 'woonet' )
			);
		echo '</th><td>';
			printf(
				'<label>%s<span class="tips" data-tippy-content="%s"><span class="dashicons dashicons-info"></span></span></label>',
				__( 'Child product inherit product categories changes', 'woonet' ),
				__( 'This works in conjunction with Child product inherit Parent changes being active on individual product page.', 'woonet' )
			);
		echo '</td></tr>';

		$option_name = 'child_inherit_changes_fields_control__product_tag';
		echo '<tr valign="top"><th scope="row">';
			printf(
				'<select name="%s"><option value="yes" %s>%s</option><option value="no" %s>%s</option></select>',
				"{$option_name}",
				selected( $options[ $option_name ], 'yes', false ),
				__( 'Yes', 'woonet' ),
				selected( $options[ $option_name ], 'no', false ),
				__( 'No', 'woonet' )
			);
		echo '</th><td>';
			printf(
				'<label>%s<span class="tips" data-tippy-content="%s"><span class="dashicons dashicons-info"></span></span></label>',
				__( 'Child product inherit product tags changes', 'woonet' ),
				__( 'This works in conjunction with Child product inherit Parent changes being active on individual product page.', 'woonet' )
			);
		echo '</td></tr>';

		$option_name = 'child_inherit_changes_fields_control__variations';
		echo '<tr valign="top"><th scope="row">';
			printf(
				'<select name="%s"><option value="yes" %s>%s</option><option value="no" %s>%s</option></select>',
				"{$option_name}",
				selected( $options[ $option_name ], 'yes', false ),
				__( 'Yes', 'woonet' ),
				selected( $options[ $option_name ], 'no', false ),
				__( 'No', 'woonet' )
			);
		echo '</th><td>';
			printf(
				'<label>%s<span class="tips" data-tippy-content="%s"><span class="dashicons dashicons-info"></span></span></label>',
				__( 'Child product inherit product variations', 'woonet' ),
				__( 'This works in conjunction with Child product inherit Parent changes being active on individual product page.', 'woonet' )
			);
		echo '</td></tr>';

		$option_name = 'child_inherit_changes_fields_control__attributes';
		echo '<tr valign="top"><th scope="row">';
			printf(
				'<select name="%s"><option value="yes" %s>%s</option><option value="no" %s>%s</option></select>',
				"{$option_name}",
				selected( $options[ $option_name ], 'yes', false ),
				__( 'Yes', 'woonet' ),
				selected( $options[ $option_name ], 'no', false ),
				__( 'No', 'woonet' )
			);
		echo '</th><td>';
			printf(
				'<label>%s<span class="tips" data-tippy-content="%s"><span class="dashicons dashicons-info"></span></span></label>',
				__( 'Child product inherit product attributes', 'woonet' ),
				__( 'This works in conjunction with Child product inherit Parent changes being active on individual product page.', 'woonet' )
			);
		echo '</td></tr>';

		$option_name = 'child_inherit_changes_fields_control__product_image';
		echo '<tr valign="top"><th scope="row">';
			printf(
				'<select name="%s"><option value="yes" %s>%s</option><option value="no" %s>%s</option></select>',
				"{$option_name}",
				selected( $options[ $option_name ], 'yes', false ),
				__( 'Yes', 'woonet' ),
				selected( $options[ $option_name ], 'no', false ),
				__( 'No', 'woonet' )
			);
		echo '</th><td>';
			printf(
				'<label>%s<span class="tips" data-tippy-content="%s"><span class="dashicons dashicons-info"></span></span></label>',
				__( 'Child product inherit product image', 'woonet' ),
				__( 'This works in conjunction with Child product inherit Parent changes being active on individual product page.', 'woonet' )
			);
		echo '</td></tr>';

		$option_name = 'child_inherit_changes_fields_control__product_gallery';
		echo '<tr valign="top"><th scope="row">';
			printf(
				'<select name="%s"><option value="yes" %s>%s</option><option value="no" %s>%s</option></select>',
				"{$option_name}",
				selected( $options[ $option_name ], 'yes', false ),
				__( 'Yes', 'woonet' ),
				selected( $options[ $option_name ], 'no', false ),
				__( 'No', 'woonet' )
			);
		echo '</th><td>';
			printf(
				'<label>%s<span class="tips" data-tippy-content="%s"><span class="dashicons dashicons-info"></span></span></label>',
				__( 'Child product inherit image gallery', 'woonet' ),
				__( 'This works in conjunction with Child product inherit Parent changes being active on individual product page.', 'woonet' )
			);
		echo '</td></tr>';

		$option_name = 'child_inherit_changes_fields_control__category_changes';
		echo '<tr valign="top"><th scope="row">';
			printf(
				'<select name="%s"><option value="yes" %s>%s</option><option value="no" %s>%s</option></select>',
				"{$option_name}",
				selected( $options[ $option_name ], 'yes', false ),
				__( 'Yes', 'woonet' ),
				selected( $options[ $option_name ], 'no', false ),
				__( 'No', 'woonet' )
			);
		echo '</th><td>';
			printf(
				'<label>%s<span class="tips" data-tippy-content="%s"><span class="dashicons dashicons-info"></span></span></label>',
				__( 'Child product inherit category image and description changes', 'woonet' ),
				__( 'This works in conjunction with Child product inherit Parent changes being active on individual product page.', 'woonet' )
			);
		echo '</td></tr>';

		$option_name = 'child_inherit_changes_fields_control__reviews';
		echo '<tr valign="top"><th scope="row">';
			printf(
				'<select name="%s"><option value="yes" %s>%s</option><option value="no" %s>%s</option></select>',
				"{$option_name}",
				selected( $options[ $option_name ], 'yes', false ),
				__( 'Yes', 'woonet' ),
				selected( $options[ $option_name ], 'no', false ),
				__( 'No', 'woonet' )
			);
		echo '</th><td>';
			printf(
				'<label>%s<span class="tips" data-tippy-content="%s"><span class="dashicons dashicons-info"></span></span></label>',
				__( 'Child product inherit product reviews.', 'woonet' ),
				__( 'This works in conjunction with Child product inherit Parent changes being active on individual product page.', 'woonet' )
			);
		echo '</td></tr>';

		$option_name = 'child_inherit_changes_fields_control__slug';
		echo '<tr valign="top"><th scope="row">';
			printf(
				'<select name="%s"><option value="yes" %s>%s</option><option value="no" %s>%s</option></select>',
				"{$option_name}",
				selected( $options[ $option_name ], 'yes', false ),
				__( 'Yes', 'woonet' ),
				selected( $options[ $option_name ], 'no', false ),
				__( 'No', 'woonet' )
			);
		echo '</th><td>';
			printf(
				'<label>%s<span class="tips" data-tippy-content="%s"><span class="dashicons dashicons-info"></span></span></label>',
				__( 'Child product inherit product URL (slug).', 'woonet' ),
				__( 'This works in conjunction with Child product inherit Parent changes being active on individual product page.', 'woonet' )
			);
		echo '</td></tr>';

		$option_name = 'child_inherit_changes_fields_control__purchase_note';
		echo '<tr valign="top"><th scope="row">';
			printf(
				'<select name="%s"><option value="yes" %s>%s</option><option value="no" %s>%s</option></select>',
				"{$option_name}",
				selected( $options[ $option_name ], 'yes', false ),
				__( 'Yes', 'woonet' ),
				selected( $options[ $option_name ], 'no', false ),
				__( 'No', 'woonet' )
			);
		echo '</th><td>';
			printf(
				'<label>%s<span class="tips" data-tippy-content="%s"><span class="dashicons dashicons-info"></span></span></label>',
				__( 'Child product inherit product purchase note.', 'woonet' ),
				__( 'This works in conjunction with Child product inherit Parent changes being active on individual product page.', 'woonet' )
			);
		echo '</td></tr>';

		/**
		* Sync upsell products
		**/
		$option_name = 'child_inherit_changes_fields_control__upsell';

		if ( empty( $options[ $option_name ]) )
		{
			$options[ $option_name ] = 'yes';
		} 

		if ( !empty( $options[ $option_name ]) 
			 &&  $options[ $option_name ] == 'yes')
		{
			$womulti_show_warning = "style='display:block;'";
		} else {
			$womulti_show_warning = "style='display:none;'";
		}

		echo '<tr valign="top"><th scope="row">';
			printf(
				'<select class="woomulti_option_with_warning"  name="%s"><option value="yes" %s>%s</option><option value="no" %s>%s</option></select>',
				"{$option_name}",
				selected( $options[ $option_name ], 'yes', false ),
				__( 'Yes', 'woonet' ),
				selected( $options[ $option_name ], 'no', false ),
				__( 'No', 'woonet' )
			);
		echo '</th><td>';
			printf(
				'<label>%s<span class="tips" data-tippy-content="%s"><span class="dashicons dashicons-info"></span></span></label>',
				__( 'Child product inherit Upsells.', 'woonet' ),
				__( 'This works in conjunction with Child product inherit Parent changes being active on individual product page.', 'woonet' )
			);
			echo "<p " . $womulti_show_warning .  " class='woomulti_options_warning'> An upsell product needs to be synced with the child store before it can be synced as upsell for a child store product. </p>";
		echo '</td></tr>';
		/** Sync Upsell end **/

		/**
		* Sync cross-sells products
		**/
		$option_name = 'child_inherit_changes_fields_control__cross_sells';

		if ( empty( $options[ $option_name ]) )
		{
			$options[ $option_name ] = 'no';
		}

		if ( !empty( $options[ $option_name ]) 
			 &&  $options[ $option_name ] == 'yes')
		{
			$womulti_show_warning = "style='display:block;'";
		} else {
			$womulti_show_warning = "style='display:none;'";
		}

		echo '<tr valign="top"><th scope="row">';
			printf(
				'<select class="woomulti_option_with_warning" name="%s"><option value="yes" %s>%s</option><option value="no" %s>%s</option></select>',
				"{$option_name}",
				selected( $options[ $option_name ], 'yes', false ),
				__( 'Yes', 'woonet' ),
				selected( $options[ $option_name ], 'no', false ),
				__( 'No', 'woonet' )
			);
		echo '</th><td>';
			printf(
				'<label>%s<span class="tips" data-tippy-content="%s"><span class="dashicons dashicons-info"></span></span></label>',
				__( 'Child product inherit Cross-sells.', 'woonet' ),
				__( 'This works in conjunction with Child product inherit Parent changes being active on individual product page.', 'woonet' )
			);
			echo "<p " . $womulti_show_warning . " class='woomulti_options_warning'> A cross-sell products needs to be synced with the child store before it can be synced as cross-sell for a child store product. </p>";
		echo '</td></tr>';
		/** Sync Cross-sells end **/

	echo '</tbody></table>';
	?>

		<?php do_action( 'woo_mstore/options/options_output' ); ?>

		<p class="submit">
			<input type="submit" name="Submit" class="button-primary"
				   value="<?php esc_html_e( 'Save Settings', 'woonet' ); ?>">
		</p>

		<?php wp_nonce_field( 'mstore_form_submit', 'mstore_form_nonce' ); ?>
		<input type="hidden" name="mstore_form_submit" value="true"/>

	</form>
</div>