<?php

/**
 * To Show Menu Nav Links
 *
 * This file is used to display the pro menu pro nav links.
 *
 * @link       http://www.wcvendors.com
 * @since      1.7.6
 *
 * @package    WCVendors_Pro
 * @subpackage WCVendors_Pro/admin/partials/vendors
 */

?>
<div id="wcvendor-pro-mate-box-links" class="categorydiv">
	<ul id="wcvendor-pro-mate-box-links-tabs" class="wcvendor-pro-mate-box-links-tabs add-menu-item-tabs">
		<li class="tabs">
			<a class="nav-tab-link" data-type="tabs-panel-wcvendor-pro-mate-box-links-all" href="<?php echo esc_url( add_query_arg( 'wcvendor-pro-mate-box-links-tab', 'all', remove_query_arg( $removed_args ) ) ); ?>#tabs-panel-wcvendor-pro-mate-box-links-all">
				<?php _e( 'View All', 'wcvendors-pro' ); ?>
			</a>
		</li>
	</ul>
	<div id="tabs-panel-wcvendor-pro-mate-box-links-all" class="tabs-panel tabs-panel-view-all tabs-panel-active">
		<ul id="wcvendor-pro-mate-box-links-checklist-all" class="categorychecklist form-no-clear">
		<?php
			echo walk_nav_menu_tree( array_map( 'wp_setup_nav_menu_item', $my_items['item'] ), 0, (object) array( 'walker' => $walker ) );
		?>
		</ul>
	</div>
	<p class="button-controls wp-clearfix">
		<span class="add-to-menu">
			<input type="submit"<?php wp_nav_menu_disabled_check( $nav_menu_selected_id ); ?> class="button-secondary submit-add-to-menu right" value="<?php esc_attr_e( 'Add to Menu', 'wcvendors-pro' ); ?>" name="add-wcvendor-pro-mate-box-links-menu-item" id="submit-wcvendor-pro-mate-box-links" />
			<span class="spinner"></span>
		</span>
	</p>
</div>
