<?php
/**
 * Admin View: Quick Edit Product
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$nonce    = wp_create_nonce( 'woocommerce_multisite_quick_edit_nonce' );
$options  = get_option('woonet_options');

?>

<fieldset id="woonet-quick-edit-fields" class="woocommerce-multistore-fields inline-edit-col">

	<h4><?php _e( 'Multisite - Publish to', 'woonet' ); ?></h4>

	<div class="inline-edit-col">

		<p class="form-field no_label woonet_toggle_all_sites inline">
			<input type="hidden" name="woonet_toggle_all_sites" value="" />
			<input class="woonet_toggle_all_sites inline" id="woonet_toggle_all_sites" value="yes" type="checkbox" />
			<b><span class="description"><?php _e( 'Toggle all Sites', 'woonet' ); ?></span></b>
		</p>

		<p class='woomulti-quick-update-notice'> Note: A linked product (upsell, cross-sell or grouped product) needs to be synced with the child store before it can be synced as upsell, cross-sell or grouped product for a child store product. </p>

		<div class="woonet_sites">
			<?php
				$_sites = get_option('woonet_child_sites');
				
				if ( ! empty( $_sites ) ) {
					foreach ( $_sites as $site ) {

						echo '<p class="form-field no_label _woonet_publish_to inline" data-group-id="' . $site['uuid'] . '">';

						echo '<label class="alignleft">';
						printf(
							'<input type="hidden" name="_woonet_publish_to_%s" value="" /><input type="checkbox" value="yes" id="_woonet_publish_to_%s" class="_woonet_publish_to" />',
							$site['uuid'], 
							$site['uuid']
						);
						printf(
							'<span class="checkbox-title woomulti-store-name">%s <span class="warning">%s</span></span>',
							str_replace( array('http://', 'https://'), '', $site['site_url']),
							__( '<b>Warning:</b> By unselecting this shop the product is unasigned, but not deleted from the shop, witch should be done manually.', 'woonet' )
						);
						echo '</label><br class="clear">';

						echo '<label class="alignleft pl">';
						printf(
							'<input type="hidden" name="_woonet_publish_to_%s_child_inheir" value="" /><input type="checkbox" value="yes" id="_woonet_publish_to_%s_child_inheir">',
							$site['uuid'],
							$site['uuid']
						);
						printf(
							'<span class="checkbox-title">%s</span>',
							__( 'Child product inherit Parent changes', 'woonet' )
						);
						echo '</label><br class="clear">';

						echo '<label class="alignleft pl">';
						printf(
							'<input type="hidden" name="_woonet_%s_child_stock_synchronize" value="" /><input type="checkbox" value="yes" id="_woonet_%s_child_stock_synchronize" %s />',
							$site['uuid'],
							$site['uuid'],
							'yes' == $options['synchronize-stock'] ? 'disabled="disabled"' : ''
						);
						printf(
							'<span class="checkbox-title">%s</span>',
							__( 'If checked, any stock change will synchronize across product tree.', 'woonet' )
						);
						echo '</label><br class="clear">';

						echo '</p>';
					}
				}
			?>
		</div>
	</div>

</fieldset>

<fieldset id="woonet-quick-edit-fields-slave" class="woocommerce-multistore-fields inline-edit-col">

	<p class="form-field _woonet_description inline">
		<span class="description"><?php _e( 'WooMultistore options are disabled. Child product can\'t be re-published to other sites.', 'woonet' ); ?></span>
	</p>
	<?php if ( get_option('woonet_network_type') == 'master' ) { ?>
		<p class="form-field no_label _woonet_child_inherit_updates inline">
			<input type="hidden" name="_woonet_child_inherit_updates" value="" />
			<input type="checkbox" class="_woonet_child_inherit_updates inline" id="_woonet_child_inherit_updates" value="yes" />
			<span class="description"><?php _e( 'If checked, this product will inherit any parent updates', 'woonet' ); ?></span>
		</p>
		<p class="form-field no_label _woonet_child_stock_synchronize inline">
			<input type="hidden" name="_woonet_child_stock_synchronize" value="" />
			<input type="checkbox" class="_woonet_child_stock_synchronize inline" id="_woonet_child_stock_synchronize" value="yes" <?php disabled( $options['synchronize-stock'], 'yes' ); ?> />
			<span class="description"><?php _e( 'If checked, any stock change will synchronize across product tree.', 'woonet' ); ?></span>
		</p>
	<?php } ?>

</fieldset>

<input type="hidden" name="_is_master_product" value="" />
<input type="hidden" name="master_blog_id" value="" />
<input type="hidden" name="product_blog_id" value="" />
<input type="hidden" name="woocommerce_multisite_quick_edit" value="1" />
<input type="hidden" name="woocommerce_multisite_quick_edit_nonce" value="<?php echo $nonce; ?>" />
