<?php

class WOO_MSTORE_options_interface {


		var $licence;

	function __construct() {

		$this->licence = new WOO_MSTORE_licence();

		if ( isset( $_GET['page'] ) && $_GET['page'] == 'woo-ms-options' ) {
			add_action( 'init', array( $this, 'options_update' ), 1 );
		}

		add_action( 'network_admin_menu', array( $this, 'network_admin_menu' ) );
		
		if ( ! $this->licence->licence_key_verify() ) {
			add_action( 'admin_notices', array( $this, 'admin_no_key_notices' ) );
			add_action( 'network_admin_notices', array( $this, 'admin_no_key_notices' ) );
		}
	}

	function __destruct() {

	}

	function network_admin_menu() {
			$parent_slug = 'settings.php';

			$hookID = add_submenu_page( $parent_slug, 'WooMultistore ', 'WooMultistore ', 'manage_options', 'woo-ms-options', array( $this, 'options_interface' ) );

			add_action( 'load-' . $hookID, array( $this, 'load_dependencies' ) );
			add_action( 'load-' . $hookID, array( $this, 'admin_notices' ) );

			add_action( 'admin_print_styles-' . $hookID, array( $this, 'admin_print_styles' ) );
			add_action( 'admin_print_scripts-' . $hookID, array( $this, 'admin_print_scripts' ) );
	}

	public function options_interface() {
		if ( ! $this->licence->licence_key_verify() ) {
			$this->licence_form();

			return;
		}

		if ( $this->licence->licence_key_verify() ) {
			$this->licence_deactivate_form();
		}

		global $WOO_MSTORE;
		$options = $WOO_MSTORE->functions->get_options();

		?>
			<div class="wrap">
				<div id="icon-settings" class="icon32"></div>
				<h2 class='woonet-general-setitngs-header'><?php esc_html_e( 'General Settings', 'woonet' ); ?></h2>
				<div class='woonet-additional-settings'>  
					<?php if ( isset( $options['sync-custom-taxonomy'] ) && $options['sync-custom-taxonomy'] == 'yes' ) : ?>
						<a class='button button-primary' href="<?php echo esc_url( network_admin_url( 'admin.php?page=woonet-set-taxonomy' ) ); ?>" class='Shipping options'>Set Taxonomy</a>
					<?php endif; ?>
					<?php if ( isset( $options['sync-custom-metadata'] ) && $options['sync-custom-metadata'] == 'yes' ) : ?>
						<a class='button button-primary' href="<?php echo esc_url( network_admin_url( 'admin.php?page=woonet-set-taxonomy#sec-metadata' ) ); ?>" class='Shipping options'>Set Metadata</a>
					<?php endif; ?>
				</div>

				<form id="form_data" name="form" method="post">
					<br/>
					<table class="form-table">
						<tbody>

						<tr valign="top">
							<th scope="row">
								<select name="synchronize-stock">
									<option value="yes" <?php selected( 'yes', $options['synchronize-stock'] ); ?>><?php esc_html_e( 'Yes', 'woonet' ); ?></option>
									<option value="no" <?php selected( 'no', $options['synchronize-stock'] ); ?>><?php esc_html_e( 'No', 'woonet' ); ?></option>
								</select>
							</th>
							<td>
								<label><?php esc_html_e( 'Always maintain stock synchronization for re-published products', 'woonet' ); ?>
									<span class='tips'
										  data-tip='<?php esc_html_e( 'Stock updates either manually or on checkout will also change other shops that have the product.', 'woonet' ); ?>'><span
												class="dashicons dashicons-info"></span></span></label>
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
										  data-tip='<?php esc_html_e( 'When parent product is trashed, trash the child products too.', 'woonet' ); ?>'><span
												class="dashicons dashicons-info"></span></span></label>
							</td>
						</tr>

						<tr valign="top">
							<th scope="row">
								<select name="sequential-order-numbers">
									<option value="yes" <?php selected( 'yes', $options['sequential-order-numbers'] ); ?>><?php esc_html_e( 'Yes', 'woonet' ); ?></option>
									<option value="no" <?php selected( 'no', $options['sequential-order-numbers'] ); ?>><?php esc_html_e( 'No', 'woonet' ); ?></option>
								</select>
							</th>
							<td>
								<label>
									<?php esc_html_e( 'Use sequential order numbers across the multisite environment', 'woonet' ); ?>
									<span class='tips'
										  data-tip='<?php esc_html_e( 'Order numbers will be created in sequence across the network for invoices and orders.', 'woonet' ); ?>'>
										  <span class="dashicons dashicons-info"></span>
									</span>
								</label>
							</td>
						</tr>

						<tr valign="top">
							<th scope="row">
								<select name="publish-capability">
									<option value="super-admin" <?php selected( 'super-admin', $options['publish-capability'] ); ?>><?php esc_html_e( 'Super Admin', 'woonet' ); ?></option>
									<option value="administrator" <?php selected( 'administrator', $options['publish-capability'] ); ?>><?php esc_html_e( 'Administrator', 'woonet' ); ?></option>
									<option value="shop_manager" <?php selected( 'shop_manager', $options['publish-capability'] ); ?>><?php esc_html_e( 'Shop Manager', 'woonet' ); ?></option>
								</select>
							</th>
							<td>
								<label><?php esc_html_e( 'Minimum user role to allow MultiStore Publish', 'woonet' ); ?>
								<span class='tips'
										  data-tip='<?php esc_html_e( 'Set the user role that has access to Multisite features.', 'woonet' ); ?>'>
										  <span class="dashicons dashicons-info"></span>
									</span>
								</label>
							</td>
						</tr>

						<tr valign="top">
							<th scope="row">
								<select name="network-user-info">
									<option value="yes" <?php selected( $options['network-user-info'], 'yes' ); ?>><?php esc_html_e( 'Yes', 'woonet' ); ?></option>
									<option value="no" <?php selected( $options['network-user-info'], 'no' ); ?>><?php esc_html_e( 'No', 'woonet' ); ?></option>
								</select>
							</th>
							<td>
								<label>
									<?php esc_html_e( 'Show customers orders from all stores in My Account', 'woonet' ); ?>
									<span class='tips'
										  data-tip='<?php esc_html_e( 'When enabled, customers will see orders from the whole network under My Account page.', 'woonet' ); ?>'>
										  <span class="dashicons dashicons-info"></span>
									</span>
								</label>
							</td>
						</tr>


						<?php 

						 if ( empty($options['sync-coupons']) ) {
						 	$options['sync-coupons'] = 'no';
						 }
						 
						?>
						<tr valign="top">
							<th scope="row">
								<select name="sync-coupons">
									<option value="yes" <?php selected( $options['sync-coupons'], 'yes' ); ?>><?php esc_html_e( 'Yes', 'woonet' ); ?></option>
									<option value="no" <?php selected( $options['sync-coupons'], 'no' ); ?>><?php esc_html_e( 'No', 'woonet' ); ?></option>
								</select>
							</th>
							<td>
								<label><?php esc_html_e( 'Sync coupons', 'woonet' ); ?>
									<span class='tips'
										data-tip='<?php esc_html_e( 'Sync coupon codes across the network.', 'woonet' ); ?>'><span
										class="dashicons dashicons-info"></span>
									</span>
								</label>
							</td>
						</tr>

						<?php 

						 if ( empty($options['sync-custom-taxonomy']) ) {
						 	$options['sync-custom-taxonomy'] = 'no';
						 }
						 
						?>
						<tr valign="top">
							<th scope="row">
								<select name="sync-custom-taxonomy">
									<option value="yes" <?php selected( $options['sync-custom-taxonomy'], 'yes' ); ?>><?php esc_html_e( 'Yes', 'woonet' ); ?></option>
									<option value="no" <?php selected( $options['sync-custom-taxonomy'], 'no' ); ?>><?php esc_html_e( 'No', 'woonet' ); ?></option>
								</select>
							</th>
							<td>
								<label><?php esc_html_e( 'Sync custom taxonomy', 'woonet' ); ?>
									<span class='tips'
										data-tip='<?php esc_html_e( 'If enabled, you can select which custom taxonomy will be synced with the child sites.', 'woonet' ); ?>'>
									<span class="dashicons dashicons-info"></span></span></label>
							</td>
						</tr>

						<?php 

						 if ( empty($options['sync-custom-metadata']) ) {
						 	$options['sync-custom-metadata'] = 'no';
						 }
						 
						?>
						<tr valign="top">
							<th scope="row">
								<select name="sync-custom-metadata">
									<option value="yes" <?php selected( $options['sync-custom-metadata'], 'yes' ); ?>><?php esc_html_e( 'Yes', 'woonet' ); ?></option>
									<option value="no" <?php selected( $options['sync-custom-metadata'], 'no' ); ?>><?php esc_html_e( 'No', 'woonet' ); ?></option>
								</select>
							</th>
							<td>
								<label><?php esc_html_e( 'Sync custom metadata ', 'woonet' ); ?>
									<span class='tips'
										data-tip='<?php esc_html_e( 'If enabled, you can select which custom metadata will be synced with the child sites.', 'woonet' ); ?>'>
									<span class="dashicons dashicons-info"></span></span></label>
							</td>
						</tr>

						<?php 

						 if ( empty($options['use-global-image']) ) {
						 	$options['use-global-image'] = 'no';
						 }
						 
						?>
						<tr valign="top">
							<th scope="row">
								<select name="use-global-image">
									<option value="yes" <?php selected( $options['use-global-image'], 'yes' ); ?>><?php esc_html_e( 'Yes', 'woonet' ); ?></option>
									<option value="no" <?php selected( $options['use-global-image'], 'no' ); ?>><?php esc_html_e( 'No', 'woonet' ); ?></option>
								</select>
							</th>
							<td>
								<label><?php esc_html_e( 'Use global image', 'woonet' ); ?>
						 			<p style='display:inline-block; font-size:0.7em;font-style:italic'> (Experimental) </p>
									<span class='tips'
											data-tip='<?php esc_html_e( 'When enabled, parent product images will be linked to the child product images without being copied to the child sites.', 'woonet' ); ?>'><span
											class="dashicons dashicons-info"></span>
									</span>
								</label>
							</td>
						</tr>

						<?php 

						 if ( empty($options['sync-by-sku']) ) {
						 	$options['sync-by-sku'] = 'no';
						 }
						 
						?>
						<tr valign="top">
							<th scope="row">
								<select name="sync-by-sku">
									<option value="yes" <?php selected( $options['sync-by-sku'], 'yes' ); ?>><?php esc_html_e( 'Yes', 'woonet' ); ?></option>
									<option value="no" <?php selected( $options['sync-by-sku'], 'no' ); ?>><?php esc_html_e( 'No', 'woonet' ); ?></option>
								</select>
							</th>
							<td>
								<label>
								<?php esc_html_e( 'Sync by SKU', 'woonet' ); ?>
								<p style='display:inline-block; font-size:0.7em;font-style:italic'> (Experimental) </p>
								<span class='tips'
										data-tip='<?php esc_html_e( 'When enabled, existing products on the child sites will be linked by SKU.', 'woonet' ); ?>'><span
										class="dashicons dashicons-info"></span>
									</span>
								</label>
							</td>
						</tr>
						</tbody>
					</table>

				<?php
					$blog_ids = $WOO_MSTORE->functions->get_active_woocommerce_blog_ids();
				if ( isset( $options['blog_tab_order'] ) ) {
					$blog_tab_order = array_filter( array_map( 'intval', $options['blog_tab_order'] ) );
					$blog_ids       = array_merge(
						array_intersect( $blog_tab_order, $blog_ids ),
						array_diff( $blog_ids, $blog_tab_order )
					);
				}
				if ( $blog_ids ) {
					echo '<h4>' . __( 'Child product inherit parent changes - Field controls', 'woonet' ) . '</h4>';
					echo '<div id="fields-control">';

						echo '<ul>';
					foreach ( $blog_ids as $index => $blog_id ) {
						switch_to_blog( $blog_id );

						$blog_name = get_bloginfo( 'name' );

						$blog_ids[ $index ] = array( $blog_id, $blog_name );

						printf(
							'<li><a href="#tabs-%d">%s</a><input type="hidden" name="blog_tab_order[]" value="%d" /></li>',
							$blog_id,
							$blog_name,
							$blog_id
						);

						restore_current_blog();
					}
						echo '</ul>';

					foreach ( $blog_ids as $blog_data ) {
						list( $blog_id, $blog_name ) = $blog_data;

						printf( '<div id="tabs-%d"><h3>%s options</h3>', $blog_id, $blog_name );

							echo '<table class="form-table"><tbody>';


								$option_name = 'child_inherit_changes_fields_control__status';
								
								echo '<tr valign="top"><th scope="row">';
									printf(
										'<select name="%s"><option value="yes" %s>%s</option><option value="no" %s>%s</option></select>',
										"{$option_name}[{$blog_id}]",
										selected( $options[ $option_name ][ $blog_id ], 'yes', false ),
										__( 'Yes', 'woonet' ),
										selected( $options[ $option_name ][ $blog_id ], 'no', false ),
										__( 'No', 'woonet' )
									);
									echo '</th><td>';
										printf(
											'<label>%s<span class="tips" data-tip="%s"><span class="dashicons dashicons-info"></span></span></label>',
											__( 'Child product inherit product status changes', 'woonet' ),
											__( 'This works in conjunction with <b>Child product inherit parent changes</b> being active on individual product page.', 'woonet' )
										);
									echo '</td></tr>';

								$option_name = 'child_inherit_changes_fields_control__title';

								echo '<tr valign="top"><th scope="row">';
									printf(
										'<select name="%s"><option value="yes" %s>%s</option><option value="no" %s>%s</option></select>',
										"{$option_name}[{$blog_id}]",
										selected( $options[ $option_name ][ $blog_id ], 'yes', false ),
										__( 'Yes', 'woonet' ),
										selected( $options[ $option_name ][ $blog_id ], 'no', false ),
										__( 'No', 'woonet' )
									);
									echo '</th><td>';
										printf(
											'<label>%s<span class="tips" data-tip="%s"><span class="dashicons dashicons-info"></span></span></label>',
											__( 'Child product inherit title changes', 'woonet' ),
											__( 'This works in conjunction with <b>Child product inherit parent changes</b> being active on individual product page.', 'woonet' )
										);
									echo '</td></tr>';

									$option_name = 'child_inherit_changes_fields_control__description';
									echo '<tr valign="top"><th scope="row">';
										printf(
											'<select name="%s"><option value="yes" %s>%s</option><option value="no" %s>%s</option></select>',
											"{$option_name}[{$blog_id}]",
											selected( $options[ $option_name ][ $blog_id ], 'yes', false ),
											__( 'Yes', 'woonet' ),
											selected( $options[ $option_name ][ $blog_id ], 'no', false ),
											__( 'No', 'woonet' )
										);
									echo '</th><td>';
										printf(
											'<label>%s<span class="tips" data-tip="%s"><span class="dashicons dashicons-info"></span></span></label>',
											__( 'Child product inherit description changes', 'woonet' ),
											__( 'This works in conjunction with <b>Child product inherit parent changes</b> being active on individual product page.', 'woonet' )
										);
									echo '</td></tr>';

									$option_name = 'child_inherit_changes_fields_control__short_description';
									echo '<tr valign="top"><th scope="row">';
										printf(
											'<select name="%s"><option value="yes" %s>%s</option><option value="no" %s>%s</option></select>',
											"{$option_name}[{$blog_id}]",
											selected( $options[ $option_name ][ $blog_id ], 'yes', false ),
											__( 'Yes', 'woonet' ),
											selected( $options[ $option_name ][ $blog_id ], 'no', false ),
											__( 'No', 'woonet' )
										);
									echo '</th><td>';
										printf(
											'<label>%s<span class="tips" data-tip="%s"><span class="dashicons dashicons-info"></span></span></label>',
											__( 'Child product inherit short description changes', 'woonet' ),
											__( 'This works in conjunction with <b>Child product inherit parent changes</b> being active on individual product page.', 'woonet' )
										);
									echo '</td></tr>';

									$option_name = 'child_inherit_changes_fields_control__price';
									echo '<tr valign="top"><th scope="row">';
										printf(
											'<select name="%s"><option value="yes" %s>%s</option><option value="no" %s>%s</option></select>',
											"{$option_name}[{$blog_id}]",
											selected( $options[ $option_name ][ $blog_id ], 'yes', false ),
											__( 'Yes', 'woonet' ),
											selected( $options[ $option_name ][ $blog_id ], 'no', false ),
											__( 'No', 'woonet' )
										);
									echo '</th><td>';
										printf(
											'<label>%s<span class="tips" data-tip="%s"><span class="dashicons dashicons-info"></span></span></label>',
											__( 'Child product inherit price changes', 'woonet' ),
											__( 'This works in conjunction with <b>Child product inherit parent changes</b> being active on individual product page.', 'woonet' )
										);
									echo '</td></tr>';

									$option_name = 'child_inherit_changes_fields_control__product_cat';
									echo '<tr valign="top"><th scope="row">';
										printf(
											'<select name="%s"><option value="yes" %s>%s</option><option value="no" %s>%s</option></select>',
											"{$option_name}[{$blog_id}]",
											selected( $options[ $option_name ][ $blog_id ], 'yes', false ),
											__( 'Yes', 'woonet' ),
											selected( $options[ $option_name ][ $blog_id ], 'no', false ),
											__( 'No', 'woonet' )
										);
									echo '</th><td>';
										printf(
											'<label>%s<span class="tips" data-tip="%s"><span class="dashicons dashicons-info"></span></span></label>',
											__( 'Child product inherit product category changes', 'woonet' ),
											__( 'This works in conjunction with <b>Child product inherit parent changes</b> being active on individual product page.', 'woonet' )
										);
									echo '</td></tr>';

									$option_name = 'child_inherit_changes_fields_control__product_tag';
									echo '<tr valign="top"><th scope="row">';
										printf(
											'<select name="%s"><option value="yes" %s>%s</option><option value="no" %s>%s</option></select>',
											"{$option_name}[{$blog_id}]",
											selected( $options[ $option_name ][ $blog_id ], 'yes', false ),
											__( 'Yes', 'woonet' ),
											selected( $options[ $option_name ][ $blog_id ], 'no', false ),
											__( 'No', 'woonet' )
										);
									echo '</th><td>';
										printf(
											'<label>%s<span class="tips" data-tip="%s"><span class="dashicons dashicons-info"></span></span></label>',
											__( 'Child product inherit product tag changes', 'woonet' ),
											__( 'This works in conjunction with <b>Child product inherit parent changes</b> being active on individual product page.', 'woonet' )
										);
									echo '</td></tr>';

									$option_name = 'child_inherit_changes_fields_control__variations';
									echo '<tr valign="top"><th scope="row">';
										printf(
											'<select name="%s"><option value="yes" %s>%s</option><option value="no" %s>%s</option></select>',
											"{$option_name}[{$blog_id}]",
											selected( $options[ $option_name ][ $blog_id ], 'yes', false ),
											__( 'Yes', 'woonet' ),
											selected( $options[ $option_name ][ $blog_id ], 'no', false ),
											__( 'No', 'woonet' )
										);
									echo '</th><td>';
										printf(
											'<label>%s<span class="tips" data-tip="%s"><span class="dashicons dashicons-info"></span></span></label>',
											__( 'Child product inherit product variations', 'woonet' ),
											__( 'This works in conjunction with <b>Child product inherit parent changes</b> being active on individual product page.', 'woonet' )
										);
									echo '</td></tr>';

									$option_name = 'child_inherit_changes_fields_control__attributes';
									echo '<tr valign="top"><th scope="row">';
										printf(
											'<select name="%s"><option value="yes" %s>%s</option><option value="no" %s>%s</option></select>',
											"{$option_name}[{$blog_id}]",
											selected( $options[ $option_name ][ $blog_id ], 'yes', false ),
											__( 'Yes', 'woonet' ),
											selected( $options[ $option_name ][ $blog_id ], 'no', false ),
											__( 'No', 'woonet' )
										);
									echo '</th><td>';
										printf(
											'<label>%s<span class="tips" data-tip="%s"><span class="dashicons dashicons-info"></span></span></label>',
											__( 'Child product inherit product attributes', 'woonet' ),
											__( 'This works in conjunction with <b>Child product inherit parent changes</b> being active on individual product page.', 'woonet' )
										);
									echo '</td></tr>';

									$option_name = 'child_inherit_changes_fields_control__product_image';
									echo '<tr valign="top"><th scope="row">';
										printf(
											'<select name="%s"><option value="yes" %s>%s</option><option value="no" %s>%s</option></select>',
											"{$option_name}[{$blog_id}]",
											selected( $options[ $option_name ][ $blog_id ], 'yes', false ),
											__( 'Yes', 'woonet' ),
											selected( $options[ $option_name ][ $blog_id ], 'no', false ),
											__( 'No', 'woonet' )
										);
									echo '</th><td>';
										printf(
											'<label>%s<span class="tips" data-tip="%s"><span class="dashicons dashicons-info"></span></span></label>',
											__( 'Child product inherit product image', 'woonet' ),
											__( 'This works in conjunction with <b>Child product inherit parent changes</b> being active on individual product page.', 'woonet' )
										);
									echo '</td></tr>';

									$option_name = 'child_inherit_changes_fields_control__product_gallery';
									echo '<tr valign="top"><th scope="row">';
										printf(
											'<select name="%s"><option value="yes" %s>%s</option><option value="no" %s>%s</option></select>',
											"{$option_name}[{$blog_id}]",
											selected( $options[ $option_name ][ $blog_id ], 'yes', false ),
											__( 'Yes', 'woonet' ),
											selected( $options[ $option_name ][ $blog_id ], 'no', false ),
											__( 'No', 'woonet' )
										);
									echo '</th><td>';
										printf(
											'<label>%s<span class="tips" data-tip="%s"><span class="dashicons dashicons-info"></span></span></label>',
											__( 'Child product inherit product gallery', 'woonet' ),
											__( 'This works in conjunction with <b>Child product inherit parent changes</b> being active on individual product page.', 'woonet' )
										);
									echo '</td></tr>';

									$option_name = 'child_inherit_changes_fields_control__category_changes';
									echo '<tr valign="top"><th scope="row">';
										printf(
											'<select name="%s"><option value="yes" %s>%s</option><option value="no" %s>%s</option></select>',
											"{$option_name}[{$blog_id}]",
											selected( $options[ $option_name ][ $blog_id ], 'yes', false ),
											__( 'Yes', 'woonet' ),
											selected( $options[ $option_name ][ $blog_id ], 'no', false ),
											__( 'No', 'woonet' )
										);
									echo '</th><td>';
										printf(
											'<label>%s<span class="tips" data-tip="%s"><span class="dashicons dashicons-info"></span></span></label>',
											__( 'Child product inherit category image and description changes', 'woonet' ),
											__( 'This works in conjunction with <b>Child product inherit parent changes</b> being active on individual product page.', 'woonet' )
										);
									echo '</td></tr>';

									$option_name = 'child_inherit_changes_fields_control__reviews';
									echo '<tr valign="top"><th scope="row">';
										printf(
											'<select name="%s"><option value="yes" %s>%s</option><option value="no" %s>%s</option></select>',
											"{$option_name}[{$blog_id}]",
											selected( $options[ $option_name ][ $blog_id ], 'yes', false ),
											__( 'Yes', 'woonet' ),
											selected( $options[ $option_name ][ $blog_id ], 'no', false ),
											__( 'No', 'woonet' )
										);
									echo '</th><td>';
										printf(
											'<label>%s<span class="tips" data-tip="%s"><span class="dashicons dashicons-info"></span></span></label>',
											__( 'Child product inherit product reviews', 'woonet' ),
											__( 'This works in conjunction with <b>Child product inherit parent changes</b> being active on individual product page.', 'woonet' )
										);
									echo '</td></tr>';

									$option_name = 'child_inherit_changes_fields_control__slug';
									echo '<tr valign="top"><th scope="row">';
										printf(
											'<select name="%s"><option value="yes" %s>%s</option><option value="no" %s>%s</option></select>',
											"{$option_name}[{$blog_id}]",
											selected( $options[ $option_name ][ $blog_id ], 'yes', false ),
											__( 'Yes', 'woonet' ),
											selected( $options[ $option_name ][ $blog_id ], 'no', false ),
											__( 'No', 'woonet' )
										);
									echo '</th><td>';
										printf(
											'<label>%s<span class="tips" data-tip="%s"><span class="dashicons dashicons-info"></span></span></label>',
											__( 'Child product inherit product URL (slug)', 'woonet' ),
											__( 'This works in conjunction with <b>Child product inherit parent changes</b> being active on individual product page.', 'woonet' )
										);
									echo '</td></tr>';

									$option_name = 'child_inherit_changes_fields_control__purchase_note';
									echo '<tr valign="top"><th scope="row">';
										printf(
											'<select name="%s"><option value="yes" %s>%s</option><option value="no" %s>%s</option></select>',
											"{$option_name}[{$blog_id}]",
											selected( $options[ $option_name ][ $blog_id ], 'yes', false ),
											__( 'Yes', 'woonet' ),
											selected( $options[ $option_name ][ $blog_id ], 'no', false ),
											__( 'No', 'woonet' )
										);
									echo '</th><td>';
										printf(
											'<label>%s<span class="tips" data-tip="%s"><span class="dashicons dashicons-info"></span></span></label>',
											__( 'Child product inherit product purchase note', 'woonet' ),
											__( 'This works in conjunction with <b>Child product inherit parent changes</b> being active on individual product page.', 'woonet' )
										);
									echo '</td></tr>';

									/**
									* Shipping Class
									**/
									$option_name = 'child_inherit_changes_fields_control__shipping_class';

									if ( empty( $options[ $option_name ][ $blog_id ]) )
									{
										$options[ $option_name ][ $blog_id ] = 'yes';
									} 

									if ( !empty( $options[ $option_name ][ $blog_id ]) 
										 &&  $options[ $option_name ][ $blog_id ] == 'yes')
									{
										$womulti_show_warning = "style='display:block;'";
									} else {
										$womulti_show_warning = "style='display:none;'";
									}

									echo '<tr valign="top"><th scope="row">';
										printf(
											'<select class="woomulti_option_with_warning"  name="%s"><option value="yes" %s>%s</option><option value="no" %s>%s</option></select>',
											"{$option_name}[{$blog_id}]",
											selected( $options[ $option_name ][ $blog_id ], 'yes', false ),
											__( 'Yes', 'woonet' ),
											selected( $options[ $option_name ][ $blog_id ], 'no', false ),
											__( 'No', 'woonet' )
										);
									echo '</th><td>';
										printf(
											'<label>%s<span class="tips" data-tip="%s"><span class="dashicons dashicons-info"></span></span></label>',
											__( 'Child product inherit shipping class', 'woonet' ),
											__( 'This works in conjunction with <b>Child product inherit parent changes</b> being active on individual product page.', 'woonet' )
										);
									echo '</td></tr>';
									/** Shipping class end **/


									/**
									* Sync upsell products
									**/
									$option_name = 'child_inherit_changes_fields_control__upsell';

									if ( empty( $options[ $option_name ][ $blog_id ]) )
									{
										$options[ $option_name ][ $blog_id ] = 'no';
									} 

									if ( !empty( $options[ $option_name ][ $blog_id ]) 
										 &&  $options[ $option_name ][ $blog_id ] == 'yes')
									{
										$womulti_show_warning = "style='display:block;'";
									} else {
										$womulti_show_warning = "style='display:none;'";
									}

									echo '<tr valign="top"><th scope="row">';
										printf(
											'<select class="woomulti_option_with_warning"  name="%s"><option value="yes" %s>%s</option><option value="no" %s>%s</option></select>',
											"{$option_name}[{$blog_id}]",
											selected( $options[ $option_name ][ $blog_id ], 'yes', false ),
											__( 'Yes', 'woonet' ),
											selected( $options[ $option_name ][ $blog_id ], 'no', false ),
											__( 'No', 'woonet' )
										);
									echo '</th><td>';
										printf(
											'<label>%s<span class="tips" data-tip="%s"><span class="dashicons dashicons-info"></span></span></label>',
											__( 'Child product inherit Upsells', 'woonet' ),
											__( 'This works in conjunction with <b>Child product inherit parent changes</b> being active on individual product page.', 'woonet' )
										);
										echo "<p " . $womulti_show_warning .  " class='woomulti_options_warning'> An upsell product needs to be synced with the child store before it can be synced as upsell for a child store product. </p>";
									echo '</td></tr>';
									/** Sync Upsell end **/

									/**
									* Sync cross-sells products
									**/
									$option_name = 'child_inherit_changes_fields_control__cross_sells';

									if ( empty( $options[ $option_name ][ $blog_id ]) )
									{
										$options[ $option_name ][ $blog_id ] = 'no';
									}

									if ( !empty( $options[ $option_name ][ $blog_id ]) 
										 &&  $options[ $option_name ][ $blog_id ] == 'yes')
									{
										$womulti_show_warning = "style='display:block;'";
									} else {
										$womulti_show_warning = "style='display:none;'";
									}

									echo '<tr valign="top"><th scope="row">';
										printf(
											'<select class="woomulti_option_with_warning" name="%s"><option value="yes" %s>%s</option><option value="no" %s>%s</option></select>',
											"{$option_name}[{$blog_id}]",
											selected( $options[ $option_name ][ $blog_id ], 'yes', false ),
											__( 'Yes', 'woonet' ),
											selected( $options[ $option_name ][ $blog_id ], 'no', false ),
											__( 'No', 'woonet' )
										);
									echo '</th><td>';
										printf(
											'<label>%s<span class="tips" data-tip="%s"><span class="dashicons dashicons-info"></span></span></label>',
											__( 'Child product inherit Cross-sells', 'woonet' ),
											__( 'This works in conjunction with <b>Child product inherit parent changes</b> being active on individual product page.', 'woonet' )
										);
										echo "<p " . $womulti_show_warning . " class='woomulti_options_warning'> A cross-sell products needs to be synced with the child store before it can be synced as cross-sell for a child store product. </p>";
									echo '</td></tr>';
									/** Sync Cross-sells end **/

									do_action( 'woo_mstore/options/options_output/child_inherit_changes_fields_control', $blog_id );

								echo '</tbody></table>';

							echo '</div>';
					}

							echo '</div>';
				}
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
			<?php
	}

	function options_update() {

		if ( isset( $_POST['mstore_licence_form_submit'] ) ) {
				$this->licence_form_submit();
				return;
		}

		if ( isset( $_POST['mstore_form_submit'] ) ) {
				// check nonce
			if ( ! wp_verify_nonce( $_POST['mstore_form_nonce'], 'mstore_form_submit' ) ) {
				return;
			}

				global $WOO_MSTORE;
				$options = $WOO_MSTORE->functions->get_options();

				global $mstore_form_submit_messages;

				// Global options.
				$options['synchronize-stock'] = $_POST['synchronize-stock'];
				$options['synchronize-trash'] = isset( $_POST['synchronize-trash'] ) && in_array( $_POST['synchronize-trash'], array( 'yes', 'no' ) ) ? $_POST['synchronize-trash'] : 'no';
				$options['sequential-order-numbers'] = $_POST['sequential-order-numbers'];
				$options['publish-capability']    	 = $_POST['publish-capability'];
				$options['network-user-info']     	 = isset( $_POST['network-user-info'] ) && in_array( $_POST['network-user-info'], array( 'yes', 'no' ) ) ? $_POST['network-user-info'] : 'yes';
				$options['sync-coupons']          	 = isset( $_POST['sync-coupons'] ) && in_array( $_POST['sync-coupons'], array( 'yes', 'no' ) ) ? $_POST['sync-coupons'] : 'yes';
				$options['use-global-image']      	 = isset( $_POST['use-global-image'] ) && in_array( $_POST['use-global-image'], array( 'yes', 'no' ) ) ? $_POST['use-global-image'] : 'yes';
				$options['sync-by-sku']           	 = isset( $_POST['sync-by-sku'] ) && in_array( $_POST['sync-by-sku'], array( 'yes', 'no' ) ) ? $_POST['sync-by-sku'] : 'yes';
				$options['sync-custom-taxonomy']  	 = isset( $_POST['sync-custom-taxonomy'] ) && in_array( $_POST['sync-custom-taxonomy'], array( 'yes', 'no' ) ) ? $_POST['sync-custom-taxonomy'] : 'yes';
				$options['sync-custom-metadata']  	 = isset( $_POST['sync-custom-metadata'] ) && in_array( $_POST['sync-custom-metadata'], array( 'yes', 'no' ) ) ? $_POST['sync-custom-metadata'] : 'yes';


				// Site specific options.
				$options['child_inherit_changes_fields_control__status']             = $_POST['child_inherit_changes_fields_control__status'];
				$options['child_inherit_changes_fields_control__title']             = $_POST['child_inherit_changes_fields_control__title'];
				$options['child_inherit_changes_fields_control__description']       = $_POST['child_inherit_changes_fields_control__description'];
				$options['child_inherit_changes_fields_control__short_description'] = $_POST['child_inherit_changes_fields_control__short_description'];
				$options['child_inherit_changes_fields_control__price']             = $_POST['child_inherit_changes_fields_control__price'];
				$options['child_inherit_changes_fields_control__product_cat']       = $_POST['child_inherit_changes_fields_control__product_cat'];
				$options['child_inherit_changes_fields_control__product_tag']       = $_POST['child_inherit_changes_fields_control__product_tag'];
				$options['child_inherit_changes_fields_control__variations']        = $_POST['child_inherit_changes_fields_control__variations'];
				$options['child_inherit_changes_fields_control__attributes']        = $_POST['child_inherit_changes_fields_control__attributes'];
				$options['child_inherit_changes_fields_control__category_changes']  = $_POST['child_inherit_changes_fields_control__category_changes'];
				$options['child_inherit_changes_fields_control__reviews']           = $_POST['child_inherit_changes_fields_control__reviews'];
				$options['child_inherit_changes_fields_control__slug']              = $_POST['child_inherit_changes_fields_control__slug'];
				$options['child_inherit_changes_fields_control__purchase_note']     = $_POST['child_inherit_changes_fields_control__purchase_note'];
				$options['child_inherit_changes_fields_control__upsell']      = $_POST['child_inherit_changes_fields_control__upsell'];
				$options['child_inherit_changes_fields_control__cross_sells'] = $_POST['child_inherit_changes_fields_control__cross_sells'];
				$options['child_inherit_changes_fields_control__product_image'] = $_POST['child_inherit_changes_fields_control__product_image'];
				$options['child_inherit_changes_fields_control__product_gallery'] = $_POST['child_inherit_changes_fields_control__product_gallery'];
				$options['child_inherit_changes_fields_control__shipping_class'] = $_POST['child_inherit_changes_fields_control__shipping_class'];

				$options['blog_tab_order'] = $_POST['blog_tab_order'];

				$options = apply_filters( 'woo_mstore/options/options_save', $options );

				foreach( $options as $key => $value ) {
					if ( is_array( $value ) ) {
						$options[ $key ] = array_map( 'strip_tags', $value );
					} else {
						$options[ $key ] = strip_tags( $value );
					}
				}

				$WOO_MSTORE->functions->update_options( $options );

				$mstore_form_submit_messages[] = __( 'Settings Saved', 'woonet' );

				// post processing
			if ( $options['sequential-order-numbers'] == 'yes' ) {
					/**
					 * Do not update if sequential order number is ono-zero
					 */
					include_once WOO_MSTORE_PATH . '/include/class.sequential-order-numbers.php';

					if ( WOO_SON::get_current_sequential_order_number() <= 0 ) {
						WOO_SON::network_update_order_numbers();
					} 
			}
		}

	}

	function load_dependencies() {

	}

	function admin_notices() {
			global $mstore_form_submit_messages;

		if ( $mstore_form_submit_messages == '' ) {
			return;
		}

			$messages = $mstore_form_submit_messages;

		if ( count( $messages ) > 0 ) {
				echo "<div id='notice' class='updated fade'><p>" . implode( '</p><p>', $messages ) . '</p></div>';
		}

	}

	public function admin_print_styles() {
		wp_enqueue_style( 'jquery-ui-ms', WOO_MSTORE_URL . '/assets/css/jquery-ui.css' );

		wp_enqueue_style( 'woosl-options', WOO_MSTORE_URL . '/assets/css/woosl-options.css' );
	}

	public function admin_print_scripts() {
		$WC_url = plugins_url() . '/woocommerce';
		wp_enqueue_script( 'jquery-tiptip', $WC_url . '/assets/js/jquery-tiptip/jquery.tipTip.js' );

		wp_enqueue_script(
			'jquery-ms',
			WOO_MSTORE_URL . '/assets/js/jquery-3.3.1.min.js',
			array()
		);
		wp_enqueue_script(
			'jquery-ui-ms',
			WOO_MSTORE_URL . '/assets/js/jquery-ui.min.js',
			array( 'jquery-ms' )
		);
		wp_add_inline_script( 'jquery-ui-ms', 'var $ms = $.noConflict(true);' );

		wp_enqueue_script(
			'woosl-options',
			WOO_MSTORE_URL . '/assets/js/woosl-options.js',
			array( 'jquery-ms', 'jquery-ui-ms', 'jquery-tiptip' )
		);
	}

	function admin_no_key_notices() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

			$screen = get_current_screen();

		if ( is_multisite() ) {
			if ( isset( $screen->id ) && $screen->id == 'settings_page_woo-ms-options-network' ) {
				return;
			}
			?>
							<div class="updated fade"><p><?php esc_html_e( 'WooMultistore plugin is inactive, please enter your', 'woonet' ); ?> <a href="<?php echo network_admin_url(); ?>settings.php?page=woo-ms-options"><?php esc_html_e( 'Licence Key', 'woonet' ); ?></a></p></div>
						<?php
		}
	}

	function licence_form_submit() {
			global $mstore_form_submit_messages;

			// check for de-activation
		if ( isset( $_POST['mstore_licence_form_submit'] ) && isset( $_POST['mstore_licence_deactivate'] ) && wp_verify_nonce( $_POST['mstore_license_nonce'], 'mstore_license' ) ) {
				global $mstore_form_submit_messages;

				$license_data = get_site_option( 'mstore_license' );
				$license_key  = $license_data['key'];

				// build the request query
				$args        = array(
					'woo_sl_action'     => 'deactivate',
					'licence_key'       => $license_key,
					'product_unique_id' => WOO_MSTORE_PRODUCT_ID,
					'domain'            => WOO_MSTORE_INSTANCE,
				);
				$request_uri = WOO_MSTORE_APP_API_URL . '?' . http_build_query( $args, '', '&' );
				$data        = wp_remote_get( $request_uri );

				if ( is_wp_error( $data ) || $data['response']['code'] != 200 ) {
						$mstore_form_submit_messages[] .= __( 'There was a problem connecting to ', 'woonet' ) . WOO_MSTORE_APP_API_URL;
						return;
				}

				$response_block = json_decode( $data['body'] );
				// retrieve the last message within the $response_block
				$response_block = $response_block[ count( $response_block ) - 1 ];
				$response       = $response_block->message;

				if ( isset( $response_block->status ) ) {
					if ( $response_block->status == 'success' && $response_block->status_code == 's201' ) {
							// the license is active and the software is active
							$mstore_form_submit_messages[] = $response_block->message;

							$license_data = get_site_option( 'mstore_license' );

							// save the license
							$license_data['key']        = '';
							$license_data['last_check'] = time();

							update_site_option( 'mstore_license', $license_data );
					} else { // if message code is e104  force de-activation
						if ( $response_block->status_code == 'e002' || $response_block->status_code == 'e104' ) {
							$license_data = get_site_option( 'mstore_license' );

							// save the license
							$license_data['key']        = '';
							$license_data['last_check'] = time();

							update_site_option( 'mstore_license', $license_data );
						} else {
							$mstore_form_submit_messages[] = __( 'There was a problem deactivating the licence: ', 'woonet' ) . $response_block->message;

							return;
						}
					}
				} else {
						$mstore_form_submit_messages[] = __( 'There was a problem with the data block received from ', 'woonet' ) . WOO_MSTORE_APP_API_URL;
						return;
				}

					// redirect
					wp_redirect( network_admin_url( 'settings.php?page=woo-ms-options', 'relative' ) );
					die();

		}

		if ( isset( $_POST['mstore_licence_form_submit'] ) && wp_verify_nonce( $_POST['mstore_license_nonce'], 'mstore_license' ) ) {

				$license_key = isset( $_POST['license_key'] ) ? sanitize_key( trim( $_POST['license_key'] ) ) : '';

			if ( $license_key == '' ) {
					$mstore_form_submit_messages[] = __( "Licence Key can't be empty", 'woonet' );
					return;
			}

				// build the request query
				$args        = array(
					'woo_sl_action'     => 'activate',
					'licence_key'       => $license_key,
					'product_unique_id' => WOO_MSTORE_PRODUCT_ID,
					'domain'            => WOO_MSTORE_INSTANCE,
				);
				$request_uri = WOO_MSTORE_APP_API_URL . '?' . http_build_query( $args, '', '&' );
				$data        = wp_remote_get( $request_uri );

				if ( is_wp_error( $data ) || $data['response']['code'] != 200 ) {
						$mstore_form_submit_messages[] .= __( 'There was a problem connecting to ', 'woonet' ) . WOO_MSTORE_APP_API_URL;
						return;
				}

				$response_block = json_decode( $data['body'] );
				// retrieve the last message within the $response_block
				$response_block = $response_block[ count( $response_block ) - 1 ];
				$response       = $response_block->message;

				if ( isset( $response_block->status ) ) {
					if ( $response_block->status == 'success' && in_array( $response_block->status_code, array( 's100', 's101' ) ) ) {
							// the license is active and the software is active
							$mstore_form_submit_messages[] = $response_block->message;

							$license_data = get_site_option( 'mstore_license' );

							// save the license
							$license_data['key']        = $license_key;
							$license_data['last_check'] = time();

							update_site_option( 'mstore_license', $license_data );

					} else {
						$mstore_form_submit_messages[] = __( 'There was a problem activating the licence: ', 'woonet' ) . $response_block->message;
						return;
					}
				} else {
						$mstore_form_submit_messages[] = __( 'There was a problem with the data block received from ', 'woonet' ) . WOO_MSTORE_APP_API_URL;
						return;
				}

					// redirect
					wp_redirect( network_admin_url( 'settings.php?page=woo-ms-options', 'relative' ) );
					die();
		}

	}

	function licence_form() {
		?>
						<div class="wrap"> 
							<div id="icon-settings" class="icon32"></div>
							<h2><?php esc_html_e( 'WooMultistore', 'woonet' ); ?><br />&nbsp;</h2>
							
							
							<form id="form_data" name="form" method="post">
								<div class="postbox">
									
								<?php wp_nonce_field( 'mstore_license', 'mstore_license_nonce' ); ?>
										<input type="hidden" name="mstore_licence_form_submit" value="true" />
										   
										

										 <div class="section section-text ">
											<h4 class="heading"><?php esc_html_e( 'License Key', 'woonet' ); ?></h4>
											<div class="option">
												<div class="controls">
													<input type="text" value="" name="license_key" class="text-input">
												</div>
												<div class="explain"><?php esc_html_e( 'Enter the License Key you got when bought this product. If you lost the key, you can always retrieve it from', 'woonet' ); ?> <a href="https://woomultistore.com/premium-plugins/my-account/" target="_blank"><?php esc_html_e( 'My Account', 'woonet' ); ?></a><br />
										<?php esc_html_e( 'More keys can be generate from', 'woonet' ); ?> <a href="https://woomultistore.com/premium-plugins/my-account/" target="_blank"><?php esc_html_e( 'My Account', 'woonet' ); ?></a>
												</div>
											</div> 
										</div>

									
								</div>
								
								<p class="submit">
									<input type="submit" name="Submit" class="button-primary" value="<?php esc_html_e( 'Save', 'woonet' ); ?>">
								</p>
							</form> 
						</div> 
				<?php

	}

	function licence_deactivate_form() {
			$license_data = get_site_option( 'mstore_license' );

		if ( is_multisite() ) {
			?>
								<div class="wrap"> 
									<div id="icon-settings" class="icon32"></div>
				<?php
		}

		?>
						<div id="form_data">
						<h2 class="subtitle"><?php esc_html_e( 'Software License', 'woonet' ); ?></h2>
						<div class="postbox">
							<form id="form_data" name="form" method="post">    
						<?php wp_nonce_field( 'mstore_license', 'mstore_license_nonce' ); ?>
								<input type="hidden" name="mstore_licence_form_submit" value="true" />
								<input type="hidden" name="mstore_licence_deactivate" value="true" />

								 <div class="section section-text ">
									<h4 class="heading"><?php esc_html_e( 'License Key', 'woonet' ); ?></h4>
									<div class="option">
										<div class="controls">
									<?php
									if ( $this->licence->is_local_instance() ) {
										?>
												<p>Local instance, no key applied.</p>
										<?php
									} else {
										?>
											<p><b><?php echo esc_html_e( substr( $license_data['key'], 0, 20 ) ); ?>-xxxxxxxx-xxxxxxxx</b> &nbsp;&nbsp;&nbsp;<a class="button-secondary" title="Deactivate" href="javascript: void(0)" onclick="jQuery(this).closest('form').submit();">Deactivate</a></p>
											<?php } ?>
										</div>
										<div class="explain"><?php esc_html_e( 'You can generate more keys from', 'woonet' ); ?> <a href="https://woomultistore.com/premium-plugins/my-account/" target="_blank">My Account</a>
										</div>
									</div> 
								</div>
							 </form>
						</div>
						</div> 
					<?php

					if ( is_multisite() ) {
						?>
								</div>
							<?php
					}
	}

	function licence_multisite_require_nottice() {
		?>
						<div class="wrap"> 
							<div id="icon-settings" class="icon32"></div>

							<h2 class="subtitle"><?php esc_html_e( 'Software License', 'woonet' ); ?></h2>
							<div id="form_data">
								<div class="postbox">
									<div class="section section-text ">
										<h4 class="heading"><?php esc_html_e( 'License Key Required', 'woonet' ); ?>!</h4>
										<div class="option">
											<div class="explain"><?php esc_html_e( 'Enter the License Key you got when bought this product. If you lost the key, you can always retrieve it from', 'woonet' ); ?> <a href="https://woomultistore.com/premium-plugins/my-account/" target="_blank"><?php esc_html_e( 'My Account', 'woonet' ); ?></a><br />
									<?php esc_html_e( 'More keys can be generate from', 'woonet' ); ?> <a href="https://woomultistore.com/premium-plugins/my-account/" target="_blank"><?php esc_html_e( 'My Account', 'woonet' ); ?></a>
											</div>
										</div> 
									</div>
								</div>
							</div>
						</div> 
				<?php

	}


}



?>
