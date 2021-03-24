<?php

/**
 * Settings page for the plugin
 *
 * @since 3.0.0
 * @package WooMultistore
 */
class WOO_MSTORE_options_interface {

	/**
	 * $licence
	 *
	 * @var object
	 */
	private $licence;

	/**
	 * $errors
	 *
	 * @var array
	 */
	private $errors = array();

	/**
	 * $success
	 *
	 * @var array
	 */
	private $success = array();

	/**
	 * $options_manager
	 *
	 * @var object
	 */
	private $options_manager;

	/**
	 * __construct
	 *
	 * @return void
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'init' ), 10, PHP_INT_MAX );
	}

	/**
	 * init
	 *
	 * @return void
	 */
	public function init() {
		$this->licence         = new WOO_MSTORE_licence();
		$this->options_manager = new WOO_MSTORE_OPTIONS_MANAGER();

		/**
		 * We don't need the settings page on the child site.
		 */
		if ( ! $this->licence->licence_key_verify() ) {
			return;
		}

		if ( get_option( 'woonet_network_type' ) == 'master' ) {
			add_action( 'admin_menu', array( $this, 'network_admin_menu' ), PHP_INT_MAX );
		} else {
			add_action( 'admin_menu', array( $this, 'child_menu_link' ), PHP_INT_MAX );
		}
	}

	/**
	 * network_admin_menu
	 *
	 * @return void
	 */
	public function network_admin_menu() {
			$hookID = add_submenu_page(
				'woonet-woocommerce',
				'Settings',
				'Settings',
				'manage_options',
				'woonet-woocommerce-settings',
				array( $this, 'options_interface' )
			);

			add_action( 'load-' . $hookID, array( $this, 'options_update' ) );
			add_action( 'load-' . $hookID, array( $this, 'load_dependencies' ) );
			// add_action( 'load-' . $hookID, array( $this, 'admin_notices' ) );

			add_action( 'admin_print_styles-' . $hookID, array( $this, 'admin_print_styles' ) );
			add_action( 'admin_print_scripts-' . $hookID, array( $this, 'admin_print_scripts' ) );
	}

	/**
	 * options_interface
	 *
	 * @return void
	 */
	public function options_interface() {
		if ( ! $this->licence->licence_key_verify() ) {
			$this->licence_form();
			return;
		}

		if ( $this->licence->licence_key_verify() ) {
			$this->licence_deactivate_form();
		}

		$_engine = new WOO_MSTORE_SINGLE_NETWORK_SYNC_ENGINE();
		$this->options_manager->load_options();

		$options = $_engine->get_options();
		$options = array_merge(
			array(
				'master' => array(
					'status' => 'success',
					'result' => array_merge(
						get_option( 'woonet_options' ),
						array( 'blog_name' => get_bloginfo( 'name' ) )
					),
				),
			),
			$options
		);
		?>
			<div class="wrap">
				<?php
					// error notices
				if ( ! empty( $options ) ) {
					foreach ( $options as $key => $value ) {
						if ( isset( $value['status'] ) && $value['status'] == 'failed' ) {
							$this->errors[] = esc_html( $value['message'] );
						}
					}
				}
				?>
				<div id="icon-settings" class="icon32"></div>
				<h2 class='woonet-general-setitngs-header'><?php esc_html_e( 'General Settings', 'woonet' ); ?></h2>
				<div class='woonet-additional-settings'>  
					<?php if ( $this->options_manager->get( 'sync-custom-taxonomy' ) == 'yes' ) : ?>
						<a class='button button-primary' href="<?php echo esc_url( admin_url( 'admin.php?page=woonet-set-taxonomy' ) ); ?>" class='Shipping options'>Set Taxonomy</a>
					<?php endif; ?>
					<?php if ( $this->options_manager->get( 'sync-custom-metadata' ) == 'yes' ) : ?>
						<a class='button button-primary' href="<?php echo esc_url( admin_url( 'admin.php?page=woonet-set-taxonomy#sec-metadata' ) ); ?>" class='Shipping options'>Set Metadata</a>
					<?php endif; ?>
				</div>
				<form id="form_data" name="form" method="post">
					<br/>
					<table class="form-table">
						<tbody>

						<tr valign="top">
							<th scope="row">
								<select name="__options[master][synchronize-stock]">
									<option value="yes" <?php selected( 'yes', $options['master']['result']['synchronize-stock'] ); ?>><?php esc_html_e( 'Yes', 'woonet' ); ?></option>
									<option value="no" <?php selected( 'no', $options['master']['result']['synchronize-stock'] ); ?>><?php esc_html_e( 'No', 'woonet' ); ?></option>
								</select>
							</th>
							<td>
								<label><?php esc_html_e( 'Always maintain stock synchronization for re-published products', 'woonet' ); ?>
									<span class='tips'
										data-tip='<?php esc_html_e( 'Stock updates either manually or checkout will also change other shops that have the product.', 'woonet' ); ?>'><span
												class="dashicons dashicons-info"></span></span></label>
							</td>
						</tr>

						<tr valign="top">
							<th scope="row">
								<select name="__options[master][sync-all-metadata]">
									<option value="no" <?php selected( 'no', $options['master']['result']['sync-all-metadata'] ); ?>><?php esc_html_e( 'No', 'woonet' ); ?></option>
									<option value="yes" <?php selected( 'yes', $options['master']['result']['sync-all-metadata'] ); ?>><?php esc_html_e( 'Yes', 'woonet' ); ?></option>
								</select>
							</th>
							<td>
								<label><?php esc_html_e( 'Sync metadata created by other plugins', 'woonet' ); ?>
									<span class='tips'
									data-tip='<?php esc_html_e( 'If enabled, all metadata will be synced. It may break some plugins.', 'woonet' ); ?>'>
									<span class="dashicons dashicons-info"></span></span></label>
							</td>
						</tr>

						<tr valign="top">
							<th scope="row">
								<select name="__options[master][synchronize-trash]">
									<option value="yes" <?php selected( 'yes', $options['master']['result']['synchronize-trash'] ); ?>><?php esc_html_e( 'Yes', 'woonet' ); ?></option>
									<option value="no" <?php selected( 'no', $options['master']['result']['synchronize-trash'] ); ?>><?php esc_html_e( 'No', 'woonet' ); ?></option>
								</select>
							</th>
							<td>
								<label><?php esc_html_e( 'Trash the child product when the parent product is trashed', 'woonet' ); ?>
									<span class='tips'
										data-tip='<?php esc_html_e( 'Sync child product status when the parent product is trashed/untrashed/deleted.', 'woonet' ); ?>'><span
												class="dashicons dashicons-info"></span></span></label>
							</td>
						</tr>

						<tr valign="top">
							<th scope="row">
								<select name="__options[master][publish-capability]">
									<option value="administrator" <?php selected( 'administrator', $options['master']['result']['publish-capability'] ); ?>><?php esc_html_e( 'Administrator', 'woonet' ); ?></option>
									<option value="shop_manager" <?php selected( 'shop_manager', $options['master']['result']['publish-capability'] ); ?>><?php esc_html_e( 'Shop Manager', 'woonet' ); ?></option>
								</select>
							</th>
							<td>
								<label><?php esc_html_e( 'Minimum user role to allow MultiStore Publish', 'woonet' ); ?>
								<span class='tips'
										data-tip='<?php esc_html_e( 'User role which can access multisite features.', 'woonet' ); ?>'><span
												class="dashicons dashicons-info"></span></span>
								</label>
							</td>
						</tr>
						
						<tr valign="top">
							<th scope="row">
								<select name="__options[master][sync-custom-taxonomy]">
									<option value="no" <?php selected( 'no', $options['master']['result']['sync-custom-taxonomy'] ); ?>><?php esc_html_e( 'No', 'woonet' ); ?></option>
									<option value="yes" <?php selected( 'yes', $options['master']['result']['sync-custom-taxonomy'] ); ?>><?php esc_html_e( 'Yes', 'woonet' ); ?></option>
								</select>
							</th>
							<td>
								<label><?php esc_html_e( 'Sync custom taxonomy', 'woonet' ); ?>
									<span class='tips'
										data-tip='<?php esc_html_e( 'If enabled, you can select which custom taxonomy will be synced with the child sites.', 'woonet' ); ?>'>
									<span class="dashicons dashicons-info"></span></span></label>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row">
								<select name="__options[master][sync-custom-metadata]">
									<option value="no" <?php selected( 'no', $options['master']['result']['sync-custom-metadata'] ); ?>><?php esc_html_e( 'No', 'woonet' ); ?></option>
									<option value="yes" <?php selected( 'yes', $options['master']['result']['sync-custom-metadata'] ); ?>><?php esc_html_e( 'Yes', 'woonet' ); ?></option>
								</select>
							</th>
							<td>
								<label><?php esc_html_e( 'Sync custom metadata ', 'woonet' ); ?>
									<span class='tips'
										data-tip='<?php esc_html_e( 'If enabled, you can select which custom metadata will be synced with the child sites.', 'woonet' ); ?>'>
									<span class="dashicons dashicons-info"></span></span></label>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row">
								<select name="__options[master][sequential-order-numbers]">
									<option value="no" <?php selected( 'no', $options['master']['result']['sequential-order-numbers'] ); ?>><?php esc_html_e( 'No', 'woonet' ); ?></option>
									<option value="yes" <?php selected( 'yes', $options['master']['result']['sequential-order-numbers'] ); ?>><?php esc_html_e( 'Yes', 'woonet' ); ?></option>
								</select>
							</th>
							<td>
								<label><?php esc_html_e( 'Use sequential order numbers across the network', 'woonet' ); ?>
									<span class='tips'
										data-tip='<?php esc_html_e( 'If enabled, the order numbers will be created in sequence across the network of sites.', 'woonet' ); ?>'>
									<span class="dashicons dashicons-info"></span></span></label>
							</td>
						</tr>
						</tbody>
					</table>
				<?php
					$this->admin_notices_errros();
					$this->admin_notices_success();
				?>
				<?php
				if ( ! empty( $options ) && count( $options ) >= 1 ) {
					echo '<h4>' . __( 'Child product inherit Parent changes - Fields control', 'woonet' ) . '</h4>';
					echo '<div id="fields-control">';

					echo '<ul>';

					$count = 0;
					foreach ( $options  as $index => $value ) {
						if ( empty( $value['status'] ) || $value['status'] != 'success' ) {
							continue;
						}

						printf(
							'<li><a href="#tabs-%d">%s</a><input type="hidden" name="blog_tab_order[]" value="%d" /></li>',
							$count,
							$value['result']['blog_name'],
							$count
						);
						$count++;
					}
					echo '</ul>';

					$count = 0;

					foreach ( $options as $index => $value ) {
						if ( empty( $value['status'] ) || $value['status'] != 'success' ) {
							continue;
						}

						printf( '<div id="tabs-%d"><h3>%s options</h3>', $count, $value['result']['blog_name'] );
						   $count++;
						echo '<table class="form-table"><tbody>';

							$option_name = 'child_inherit_changes_fields_control__status';

							echo '<tr valign="top"><th scope="row">';
								printf(
									'<select name="%s"><option value="yes" %s>%s</option><option value="no" %s>%s</option></select>',
									"__options[{$index}][{$option_name}]",
									selected( $value['result'][ $option_name ], 'yes', false ),
									__( 'Yes', 'woonet' ),
									selected( $value['result'][ $option_name ], 'no', false ),
									__( 'No', 'woonet' )
								);
								echo '</th><td>';
									printf(
										'<label>%s<span class="tips" data-tip="%s"><span class="dashicons dashicons-info"></span></span></label>',
										__( 'Child product inherit product status changes', 'woonet' ),
										__( 'This works in conjunction with <b>Child product inherit Parent changes</b> being active on individual product page.', 'woonet' )
									);
								echo '</td></tr>';

								$option_name = 'child_inherit_changes_fields_control__featured';

								echo '<tr valign="top"><th scope="row">';
									printf(
										'<select name="%s"><option value="yes" %s>%s</option><option value="no" %s>%s</option></select>',
										"__options[{$index}][{$option_name}]",
										selected( $value['result'][ $option_name ], 'yes', false ),
										__( 'Yes', 'woonet' ),
										selected( $value['result'][ $option_name ], 'no', false ),
										__( 'No', 'woonet' )
									);
									echo '</th><td>';
										printf(
											'<label>%s<span class="tips" data-tip="%s"><span class="dashicons dashicons-info"></span></span></label>',
											__( 'Child product inherit featured status changes', 'woonet' ),
											__( 'This works in conjunction with <b>Child product inherit Parent changes</b> being active on individual product page.', 'woonet' )
										);
									echo '</td></tr>';

							$option_name = 'child_inherit_changes_fields_control__title';

							echo '<tr valign="top"><th scope="row">';
								printf(
									'<select name="%s"><option value="yes" %s>%s</option><option value="no" %s>%s</option></select>',
									"__options[{$index}][{$option_name}]",
									selected( $value['result'][ $option_name ], 'yes', false ),
									__( 'Yes', 'woonet' ),
									selected( $value['result'][ $option_name ], 'no', false ),
									__( 'No', 'woonet' )
								);
								echo '</th><td>';
									printf(
										'<label>%s<span class="tips" data-tip="%s"><span class="dashicons dashicons-info"></span></span></label>',
										__( 'Child product inherit title changes', 'woonet' ),
										__( 'This works in conjunction with <b>Child product inherit Parent changes</b> being active on individual product page.', 'woonet' )
									);
								echo '</td></tr>';

								$option_name = 'child_inherit_changes_fields_control__description';
								echo '<tr valign="top"><th scope="row">';
									printf(
										'<select name="%s"><option value="yes" %s>%s</option><option value="no" %s>%s</option></select>',
										"__options[{$index}][{$option_name}]",
										selected( $value['result'][ $option_name ], 'yes', false ),
										__( 'Yes', 'woonet' ),
										selected( $value['result'][ $option_name ], 'no', false ),
										__( 'No', 'woonet' )
									);
								echo '</th><td>';
									printf(
										'<label>%s<span class="tips" data-tip="%s"><span class="dashicons dashicons-info"></span></span></label>',
										__( 'Child product inherit description changes', 'woonet' ),
										__( 'This works in conjunction with <b>Child product inherit Parent changes</b> being active on individual product page.', 'woonet' )
									);
								echo '</td></tr>';

								$option_name = 'child_inherit_changes_fields_control__short_description';
								echo '<tr valign="top"><th scope="row">';
									printf(
										'<select name="%s"><option value="yes" %s>%s</option><option value="no" %s>%s</option></select>',
										"__options[{$index}][{$option_name}]",
										selected( $value['result'][ $option_name ], 'yes', false ),
										__( 'Yes', 'woonet' ),
										selected( $value['result'][ $option_name ], 'no', false ),
										__( 'No', 'woonet' )
									);
								echo '</th><td>';
									printf(
										'<label>%s<span class="tips" data-tip="%s"><span class="dashicons dashicons-info"></span></span></label>',
										__( 'Child product inherit short description changes', 'woonet' ),
										__( 'This works in conjunction with <b>Child product inherit Parent changes</b> being active on individual product page.', 'woonet' )
									);
								echo '</td></tr>';

								$option_name = 'child_inherit_changes_fields_control__sku';
								echo '<tr valign="top"><th scope="row">';
									printf(
										'<select name="%s"><option value="yes" %s>%s</option><option value="no" %s>%s</option></select>',
										"__options[{$index}][{$option_name}]",
										selected( $value['result'][ $option_name ], 'yes', false ),
										__( 'Yes', 'woonet' ),
										selected( $value['result'][ $option_name ], 'no', false ),
										__( 'No', 'woonet' )
									);
								echo '</th><td>';
									printf(
										'<label>%s<span class="tips" data-tip="%s"><span class="dashicons dashicons-info"></span></span></label>',
										__( 'Child product inherit SKU changes', 'woonet' ),
										__( 'This works in conjunction with <b>Child product inherit Parent changes</b> being active on individual product page.', 'woonet' )
									);
								echo '</td></tr>';

								$option_name = 'child_inherit_changes_fields_control__price';
								echo '<tr valign="top"><th scope="row">';
									printf(
										'<select name="%s"><option value="yes" %s>%s</option><option value="no" %s>%s</option></select>',
										"__options[{$index}][{$option_name}]",
										selected( $value['result'][ $option_name ], 'yes', false ),
										__( 'Yes', 'woonet' ),
										selected( $value['result'][ $option_name ], 'no', false ),
										__( 'No', 'woonet' )
									);
								echo '</th><td>';
									printf(
										'<label>%s<span class="tips" data-tip="%s"><span class="dashicons dashicons-info"></span></span></label>',
										__( 'Child product inherit regular price changes', 'woonet' ),
										__( 'This works in conjunction with <b>Child product inherit Parent changes</b> being active on individual product page.', 'woonet' )
									);
								echo '</td></tr>';

								$option_name = 'child_inherit_changes_fields_control__sale_price';
								echo '<tr valign="top"><th scope="row">';
									printf(
										'<select name="%s"><option value="yes" %s>%s</option><option value="no" %s>%s</option></select>',
										"__options[{$index}][{$option_name}]",
										selected( $value['result'][ $option_name ], 'yes', false ),
										__( 'Yes', 'woonet' ),
										selected( $value['result'][ $option_name ], 'no', false ),
										__( 'No', 'woonet' )
									);
								echo '</th><td>';
									printf(
										'<label>%s<span class="tips" data-tip="%s"><span class="dashicons dashicons-info"></span></span></label>',
										__( 'Child product inherit sale price changes', 'woonet' ),
										__( 'This works in conjunction with <b>Child product inherit Parent changes</b> being active on individual product page.', 'woonet' )
									);
								echo '</td></tr>';

								$option_name = 'child_inherit_changes_fields_control__product_cat';
								echo '<tr valign="top"><th scope="row">';
									printf(
										'<select name="%s"><option value="yes" %s>%s</option><option value="no" %s>%s</option></select>',
										"__options[{$index}][{$option_name}]",
										selected( $value['result'][ $option_name ], 'yes', false ),
										__( 'Yes', 'woonet' ),
										selected( $value['result'][ $option_name ], 'no', false ),
										__( 'No', 'woonet' )
									);
								echo '</th><td>';
									printf(
										'<label>%s<span class="tips" data-tip="%s"><span class="dashicons dashicons-info"></span></span></label>',
										__( 'Child product inherit product categories changes', 'woonet' ),
										__( 'This works in conjunction with <b>Child product inherit Parent changes</b> being active on individual product page.', 'woonet' )
									);
								echo '</td></tr>';

								$option_name = 'child_inherit_changes_fields_control__product_tag';
								echo '<tr valign="top"><th scope="row">';
									printf(
										'<select name="%s"><option value="yes" %s>%s</option><option value="no" %s>%s</option></select>',
										"__options[{$index}][{$option_name}]",
										selected( $value['result'][ $option_name ], 'yes', false ),
										__( 'Yes', 'woonet' ),
										selected( $value['result'][ $option_name ], 'no', false ),
										__( 'No', 'woonet' )
									);
								echo '</th><td>';
									printf(
										'<label>%s<span class="tips" data-tip="%s"><span class="dashicons dashicons-info"></span></span></label>',
										__( 'Child product inherit product tags changes', 'woonet' ),
										__( 'This works in conjunction with <b>Child product inherit Parent changes</b> being active on individual product page.', 'woonet' )
									);
								echo '</td></tr>';

								$option_name = 'child_inherit_changes_fields_control__variations';
								echo '<tr valign="top"><th scope="row">';
									printf(
										'<select name="%s"><option value="yes" %s>%s</option><option value="no" %s>%s</option></select>',
										"__options[{$index}][{$option_name}]",
										selected( $value['result'][ $option_name ], 'yes', false ),
										__( 'Yes', 'woonet' ),
										selected( $value['result'][ $option_name ], 'no', false ),
										__( 'No', 'woonet' )
									);
								echo '</th><td>';
									printf(
										'<label>%s<span class="tips" data-tip="%s"><span class="dashicons dashicons-info"></span></span></label>',
										__( 'Child product inherit product variations', 'woonet' ),
										__( 'This works in conjunction with <b>Child product inherit Parent changes</b> being active on individual product page.', 'woonet' )
									);
								echo '</td></tr>';

								$option_name = 'child_inherit_changes_fields_control__attributes';
								echo '<tr valign="top"><th scope="row">';
									printf(
										'<select name="%s"><option value="yes" %s>%s</option><option value="no" %s>%s</option></select>',
										"__options[{$index}][{$option_name}]",
										selected( $value['result'][ $option_name ], 'yes', false ),
										__( 'Yes', 'woonet' ),
										selected( $value['result'][ $option_name ], 'no', false ),
										__( 'No', 'woonet' )
									);
								echo '</th><td>';
									printf(
										'<label>%s<span class="tips" data-tip="%s"><span class="dashicons dashicons-info"></span></span></label>',
										__( 'Child product inherit product attributes', 'woonet' ),
										__( 'This works in conjunction with <b>Child product inherit Parent changes</b> being active on individual product page.', 'woonet' )
									);
								echo '</td></tr>';

								$option_name = 'child_inherit_changes_fields_control__variations_sku';
								echo '<tr valign="top"><th scope="row">';
									printf(
										'<select name="%s"><option value="yes" %s>%s</option><option value="no" %s>%s</option></select>',
										"__options[{$index}][{$option_name}]",
										selected( $value['result'][ $option_name ], 'yes', false ),
										__( 'Yes', 'woonet' ),
										selected( $value['result'][ $option_name ], 'no', false ),
										__( 'No', 'woonet' )
									);
								echo '</th><td>';
									printf(
										'<label>%s<span class="tips" data-tip="%s"><span class="dashicons dashicons-info"></span></span></label>',
										__( 'Child product inherit variation SKU', 'woonet' ),
										__( 'This works in conjunction with <b>Child product inherit Parent changes</b> being active on individual product page.', 'woonet' )
									);
								echo '</td></tr>';

								$option_name = 'child_inherit_changes_fields_control__product_image';
								echo '<tr valign="top"><th scope="row">';
									printf(
										'<select name="%s"><option value="yes" %s>%s</option><option value="no" %s>%s</option></select>',
										"__options[{$index}][{$option_name}]",
										selected( $value['result'][ $option_name ], 'yes', false ),
										__( 'Yes', 'woonet' ),
										selected( $value['result'][ $option_name ], 'no', false ),
										__( 'No', 'woonet' )
									);
								echo '</th><td>';
									printf(
										'<label>%s<span class="tips" data-tip="%s"><span class="dashicons dashicons-info"></span></span></label>',
										__( 'Child product inherit product image', 'woonet' ),
										__( 'This works in conjunction with <b>Child product inherit Parent changes</b> being active on individual product page.', 'woonet' )
									);
								echo '</td></tr>';

								$option_name = 'child_inherit_changes_fields_control__product_gallery';
								echo '<tr valign="top"><th scope="row">';
									printf(
										'<select name="%s"><option value="yes" %s>%s</option><option value="no" %s>%s</option></select>',
										"__options[{$index}][{$option_name}]",
										selected( $value['result'][ $option_name ], 'yes', false ),
										__( 'Yes', 'woonet' ),
										selected( $value['result'][ $option_name ], 'no', false ),
										__( 'No', 'woonet' )
									);
								echo '</th><td>';
									printf(
										'<label>%s<span class="tips" data-tip="%s"><span class="dashicons dashicons-info"></span></span></label>',
										__( 'Child product inherit product gallery', 'woonet' ),
										__( 'This works in conjunction with <b>Child product inherit Parent changes</b> being active on individual product page.', 'woonet' )
									);
								echo '</td></tr>';

								$option_name = 'child_inherit_changes_fields_control__category_changes';
								echo '<tr valign="top"><th scope="row">';
									printf(
										'<select name="%s"><option value="yes" %s>%s</option><option value="no" %s>%s</option></select>',
										"__options[{$index}][{$option_name}]",
										selected( $value['result'][ $option_name ], 'yes', false ),
										__( 'Yes', 'woonet' ),
										selected( $value['result'][ $option_name ], 'no', false ),
										__( 'No', 'woonet' )
									);
								echo '</th><td>';
									printf(
										'<label>%s<span class="tips" data-tip="%s"><span class="dashicons dashicons-info"></span></span></label>',
										__( 'Child product inherit category image and description changes', 'woonet' ),
										__( 'This works in conjunction with <b>Child product inherit Parent changes</b> being active on individual product page.', 'woonet' )
									);
								echo '</td></tr>';

								$option_name = 'child_inherit_changes_fields_control__reviews';
								echo '<tr valign="top"><th scope="row">';
									printf(
										'<select name="%s"><option value="yes" %s>%s</option><option value="no" %s>%s</option></select>',
										"__options[{$index}][{$option_name}]",
										selected( $value['result'][ $option_name ], 'yes', false ),
										__( 'Yes', 'woonet' ),
										selected( $value['result'][ $option_name ], 'no', false ),
										__( 'No', 'woonet' )
									);
								echo '</th><td>';
									printf(
										'<label>%s<span class="tips" data-tip="%s"><span class="dashicons dashicons-info"></span></span></label>',
										__( 'Child product inherit product reviews.', 'woonet' ),
										__( 'This works in conjunction with <b>Child product inherit Parent changes</b> being active on individual product page.', 'woonet' )
									);
								echo '</td></tr>';

								$option_name = 'child_inherit_changes_fields_control__slug';
								echo '<tr valign="top"><th scope="row">';
									printf(
										'<select name="%s"><option value="yes" %s>%s</option><option value="no" %s>%s</option></select>',
										"__options[{$index}][{$option_name}]",
										selected( $value['result'][ $option_name ], 'yes', false ),
										__( 'Yes', 'woonet' ),
										selected( $value['result'][ $option_name ], 'no', false ),
										__( 'No', 'woonet' )
									);
								echo '</th><td>';
									printf(
										'<label>%s<span class="tips" data-tip="%s"><span class="dashicons dashicons-info"></span></span></label>',
										__( 'Child product inherit product URL (slug).', 'woonet' ),
										__( 'This works in conjunction with <b>Child product inherit Parent changes</b> being active on individual product page.', 'woonet' )
									);
								echo '</td></tr>';

								$option_name = 'child_inherit_changes_fields_control__purchase_note';
								echo '<tr valign="top"><th scope="row">';
									printf(
										'<select name="%s"><option value="yes" %s>%s</option><option value="no" %s>%s</option></select>',
										"__options[{$index}][{$option_name}]",
										selected( $value['result'][ $option_name ], 'yes', false ),
										__( 'Yes', 'woonet' ),
										selected( $value['result'][ $option_name ], 'no', false ),
										__( 'No', 'woonet' )
									);
								echo '</th><td>';
									printf(
										'<label>%s<span class="tips" data-tip="%s"><span class="dashicons dashicons-info"></span></span></label>',
										__( 'Child product inherit product purchase note.', 'woonet' ),
										__( 'This works in conjunction with <b>Child product inherit Parent changes</b> being active on individual product page.', 'woonet' )
									);
								echo '</td></tr>';

								$option_name = 'child_inherit_changes_fields_control__shipping_class';
								echo '<tr valign="top"><th scope="row">';
									printf(
										'<select name="%s"><option value="no" %s>%s</option><option value="yes" %s>%s</option></select>',
										"__options[{$index}][{$option_name}]",
										selected( $value['result'][ $option_name ], 'no', false ),
										__( 'No', 'woonet' ),
										selected( $value['result'][ $option_name ], 'yes', false ),
										__( 'Yes', 'woonet' )
									);
								echo '</th><td>';
									printf(
										'<label>%s<span class="tips" data-tip="%s"><span class="dashicons dashicons-info"></span></span></label>',
										__( 'Child product inherit shipping class.', 'woonet' ),
										__( 'This works in conjunction with <b>Child product inherit Parent changes</b> being active on individual product page.', 'woonet' )
									);
								echo '</td></tr>';

								/**
								* Sync upsell products
								*/
								$option_name = 'child_inherit_changes_fields_control__upsell';

								if ( empty( $value['result'][ $option_name ] ) ) {
									$value['result'][ $option_name ] = 'no';
								}

								if ( ! empty( $value['result'][ $option_name ] )
								&& $value['result'][ $option_name ] == 'yes' ) {
									$womulti_show_warning = "style='display:block;'";
								} else {
									$womulti_show_warning = "style='display:none;'";
								}

								echo '<tr valign="top"><th scope="row">';
									printf(
										'<select class="woomulti_option_with_warning"  name="%s"><option value="yes" %s>%s</option><option value="no" %s>%s</option></select>',
										"__options[{$index}][{$option_name}]",
										selected( $value['result'][ $option_name ], 'yes', false ),
										__( 'Yes', 'woonet' ),
										selected( $value['result'][ $option_name ], 'no', false ),
										__( 'No', 'woonet' )
									);
								echo '</th><td>';
									printf(
										'<label>%s<span class="tips" data-tip="%s"><span class="dashicons dashicons-info"></span></span></label>',
										__( 'Child product inherit Upsells.', 'woonet' ),
										__( 'This works in conjunction with <b>Child product inherit Parent changes</b> being active on individual product page.', 'woonet' )
									);
									echo '<p ' . $womulti_show_warning . " class='woomulti_options_warning'> An upsell product needs to be synced with the child store before it can be synced as upsell for a child store product. </p>";
								echo '</td></tr>';
								/** Sync Upsell end */

								/**
								* Sync cross-sells products
								*/
								$option_name = 'child_inherit_changes_fields_control__cross_sells';

								if ( empty( $value['result'][ $option_name ] ) ) {
									$value['result'][ $option_name ] = 'no';
								}

								if ( ! empty( $value['result'][ $option_name ] )
											&& $value['result'][ $option_name ] == 'yes' ) {
									$womulti_show_warning = "style='display:block;'";
								} else {
									$womulti_show_warning = "style='display:none;'";
								}

								echo '<tr valign="top"><th scope="row">';
									printf(
										'<select class="woomulti_option_with_warning" name="%s"><option value="yes" %s>%s</option><option value="no" %s>%s</option></select>',
										"__options[{$index}][{$option_name}]",
										selected( $value['result'][ $option_name ], 'yes', false ),
										__( 'Yes', 'woonet' ),
										selected( $value['result'][ $option_name ], 'no', false ),
										__( 'No', 'woonet' )
									);
								echo '</th><td>';
									printf(
										'<label>%s<span class="tips" data-tip="%s"><span class="dashicons dashicons-info"></span></span></label>',
										__( 'Child product inherit Cross-sells.', 'woonet' ),
										__( 'This works in conjunction with <b>Child product inherit Parent changes</b> being active on individual product page.', 'woonet' )
									);
									echo '<p ' . $womulti_show_warning . " class='woomulti_options_warning'> A cross-sell products needs to be synced with the child store before it can be synced as cross-sell for a child store product. </p>";
								echo '</td></tr>';
								/** Sync Cross-sells end */

								do_action( 'woo_mstore/options/options_output/child_inherit_changes_fields_control', $index );
								/**
								 * Override default settings section.
								 */
								echo '<tr valign="top"><th scope="row">';
                                echo "<h2 style='font-size:1em;'> Override General Settings </h2>";
								echo '</th><td>';
								echo '</td></tr>';

								/** stock sync */
								$option_name = 'override__synchronize-stock';
								echo '<tr valign="top"><th scope="row">';
									printf(
										'<select name="%s"><option value="no" %s>%s</option><option value="yes" %s>%s</option></select>',
										"__options[{$index}][{$option_name}]",
										selected( $value['result'][ $option_name ], 'no', false ),
										__( 'No', 'woonet' ),
										selected( $value['result'][ $option_name ], 'yes', false ),
										__( 'Yes', 'woonet' )
									);
								echo '</th><td>';
									printf(
										'<label>%s<span class="tips" data-tip="%s"><span class="dashicons dashicons-info"></span></span></label>',
										__( 'Disable stock sync.', 'woonet' ),
										__( 'If set to yes, stock sync will be disabled for this particular site. Only effective is stock sync if enabled.', 'woonet' )
									);
								echo '</td></tr>';
								/** end override stock sync */
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

	/**
	 * options_update
	 *
	 * @return void
	 */
	public function options_update() {
		if ( isset( $_POST['mstore_licence_form_submit'] ) ) {
			$this->licence_form_submit();
			return;
		}

		if ( isset( $_POST['mstore_form_submit'] ) ) {
			if ( ! wp_verify_nonce( $_POST['mstore_form_nonce'], 'mstore_form_submit' ) ) {
				return;
			}

			if ( ! empty( $_POST['__options'] ) ) {
				$options = $_POST['__options'];
				$options = apply_filters( 'woo_mstore/options/options_save', $options );

				$engine  = new WOO_MSTORE_SINGLE_NETWORK_SYNC_ENGINE();
				$manager = new WOO_MSTORE_OPTIONS_MANAGER();

				// Save master site options.
				if ( ! empty( $_POST['__options']['master'] ) ) {
					$manager->update( $_POST['__options']['master'] );
					$this->success[] = 'Settings updated on ' . get_bloginfo( 'name' );
				}

				// Send options to the network.
				if ( ! empty( $_POST['__options']['master'] ) ) {
					$responses = $engine->update_options( $options );

					if ( ! empty( $responses ) ) {
						foreach ( $responses as $key => $value ) {
							if ( ! empty( $value['status'] ) && $value['status'] == 'failed' ) {
								$this->errors[] = esc_html( $value['message'] );
							} elseif ( ! empty( $value['status'] ) && $value['status'] == 'success' ) {
								$this->success[] = esc_html( $value['message'] );
							}
						}
					}
				}
			}
		}
	}

	/**
	 * load_dependencies
	 *
	 * @return void
	 */
	function load_dependencies() {

	}

	/**
	 * admin_notices show errors
	 *
	 * @return void
	 */
	function admin_notices_errros() {
		if ( empty( $this->errors ) ) {
			return;
		}

		if ( count( $this->errors ) > 0 ) {
			foreach ( $this->errors as $error ) {
				echo "<div class='woomulti-errors'><p>" . esc_html( $error ) . '</p></div>';
			}
		}
	}

	/**
	 * admin_notices show errors
	 *
	 * @return void
	 */
	function admin_notices_success() {
		if ( empty( $this->success ) ) {
			return;
		}

		if ( count( $this->success ) > 0 ) {
			foreach ( $this->success as $message ) {
				echo "<div class='woomulti-success'><p>" . esc_html( $message ) . '</p></div>';
			}
		}
	}

	/**
	 * admin_print_styles
	 *
	 * @return void
	 */
	public function admin_print_styles() {
		wp_enqueue_style( 'jquery-ui-ms', WOO_MSTORE_URL . '/../assets/css/jquery-ui.css' );
		wp_enqueue_style( 'woosl-options', WOO_MSTORE_URL . '/../assets/css/woosl-options.css' );
	}

	/**
	 * admin_print_scripts
	 *
	 * @return void
	 */
	public function admin_print_scripts() {
		$WC_url = plugins_url() . '/woocommerce';
		wp_enqueue_script( 'jquery-tiptip', $WC_url . '/assets/js/jquery-tiptip/jquery.tipTip.js' );

		wp_enqueue_script(
			'jquery-ms',
			WOO_MSTORE_URL . '/../assets/js/jquery-3.3.1.min.js',
			array()
		);
		wp_enqueue_script(
			'jquery-ui-ms',
			WOO_MSTORE_URL . '/../assets/js/jquery-ui.min.js',
			array( 'jquery-ms' )
		);
		wp_add_inline_script( 'jquery-ui-ms', 'var $ms = $.noConflict(true);' );

		wp_enqueue_script(
			'woosl-options',
			WOO_MSTORE_URL . '/../assets/js/woosl-options.js',
			array( 'jquery-ms', 'jquery-ui-ms', 'jquery-tiptip' )
		);
	}

	/**
	 * licence_form_submit
	 *
	 * @return void
	 */
	public function licence_form_submit() {
			// check for de-activation
		if ( isset( $_POST['mstore_licence_form_submit'] ) && isset( $_POST['mstore_licence_deactivate'] ) && wp_verify_nonce( $_POST['mstore_license_nonce'], 'mstore_license' ) ) {
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
						$this->errors[] = __( 'There was a problem connecting to ', 'woonet' ) . WOO_MSTORE_APP_API_URL;
						return;
				}

				$response_block = json_decode( $data['body'] );
				// retrieve the last message within the $response_block
				$response_block = $response_block[ count( $response_block ) - 1 ];
				$response       = $response_block->message;

				if ( isset( $response_block->status ) ) {
					if ( $response_block->status == 'success' && $response_block->status_code == 's201' ) {
							// the license is active and the software is active
							$this->errors[] = $response_block->message;

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
							$this->errors[] = __( 'There was a problem deactivating the licence: ', 'woonet' ) . $response_block->message;

							return;
						}
					}
				} else {
						$this->errors[] = __( 'There was a problem with the data block received from ', 'woonet' ) . WOO_MSTORE_APP_API_URL;
						return;
				}

					// redirect
					wp_redirect( admin_url( 'admin.php?page=woonet-setup-wizard', 'relative' ) );
					die();

		}

		if ( isset( $_POST['mstore_licence_form_submit'] ) && wp_verify_nonce( $_POST['mstore_license_nonce'], 'mstore_license' ) ) {

				$license_key = isset( $_POST['license_key'] ) ? sanitize_key( trim( $_POST['license_key'] ) ) : '';

			if ( $license_key == '' ) {
					$this->errors[] = __( "Licence Key can't be empty", 'woonet' );
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
						$this->errors[] .= __( 'There was a problem connecting to ', 'woonet' ) . WOO_MSTORE_APP_API_URL;
						return;
				}

				$response_block = json_decode( $data['body'] );
				// retrieve the last message within the $response_block
				$response_block = $response_block[ count( $response_block ) - 1 ];
				$response       = $response_block->message;

				if ( isset( $response_block->status ) ) {
					if ( $response_block->status == 'success' && in_array( $response_block->status_code, array( 's100', 's101' ) ) ) {
							// the license is active and the software is active
							$this->errors[] = $response_block->message;

							$license_data = get_site_option( 'mstore_license' );

							// save the license
							$license_data['key']        = $license_key;
							$license_data['last_check'] = time();

							update_site_option( 'mstore_license', $license_data );

					} else {
						$this->errors[] = __( 'There was a problem activating the licence: ', 'woonet' ) . $response_block->message;
						return;
					}
				} else {
						$this->errors[] = __( 'There was a problem with the data block received from ', 'woonet' ) . WOO_MSTORE_APP_API_URL;
						return;
				}

					// redirect
					wp_redirect( admin_url( 'settings.php?page=woo-ms-options', 'relative' ) );
					die();
		}

	}

	/**
	 * licence_form
	 *
	 * @return void
	 */
	public function licence_form() {
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

	public function licence_deactivate_form() {
		$license_data = get_option( 'mstore_license' );
		?>
		<div class="wrap"> 
			<div id="icon-settings" class="icon32"></div>
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
					</div>
		<?php
	}

	public function licence_multisite_require_nottice() {
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

	public function child_menu_link() {
		$hookID = add_submenu_page(
			'woonet-woocommerce',
			'Settings (Main site)',
			'Settings (Main site)',
			'manage_options',
			'woonet-woocommerce-settings',
			array( $this, 'redirect_child_settings' )
		);
		add_action( 'load-' . $hookID, array( $this, 'redirect_child_settings' ) );
	}

	public function redirect_child_settings() {
		$option = get_option( 'woonet_master_connect' );
		if ( ! empty( $option['master_url'] ) ) {
			wp_redirect( esc_url( $option['master_url'] . '/wp-admin/admin.php?page=woonet-woocommerce-settings' ) );
			die();
		}
	}
}

$GLOBALS['WOO_MSTORE_options_interface'] = new WOO_MSTORE_options_interface();
