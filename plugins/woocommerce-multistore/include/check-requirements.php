<?php
/**
 * @deprecated no longer being used as we check whether WooCommerce is active 
 * on the class that loads the plugin. 
 */
if ( ! function_exists( 'is_plugin_active' ) ) {
	require_once ABSPATH . 'wp-admin/includes/plugin.php';
}

if ( is_multisite() ) {

	if ( ! is_plugin_active_for_network( 'woocommerce/woocommerce.php' ) ) {

		if ( is_plugin_active_for_network( 'woocommerce-multistore/woocommerce-multistore.php' ) ) {
			deactivate_plugins( 'woocommerce-multistore/woocommerce-multistore.php' );
		}

		$message = <<<'EOD'
<h1>Oops, something is wrong.</h1>
<p>WooCommerce is required to use WooMultistore. Please network activate WooCommerce before activating WooMultistore.</p>
EOD;

		wp_die( $message, '', array( 'back_link' => true ) );

	}

} else {

	$message = <<<'EOD'
<h1>Oops, something is wrong.</h1>
<p>
<strong>Did you configure your installation as multisite yet?</strong>
</p>
<p>
WooCommerce Mutistore requires WordPress multisite. Please make sure that your installation meets the installation requirements as described in our <a href="https://woomultistore.com/documentation/" target="_blank">documentation</a>.
</p>
<p>
You may find these links helpful:<br/>
<a href="https://codex.wordpress.org/Create_A_Network" target="_blank">Create A Network</a><br/>
<a href="https://codex.wordpress.org/WordPress_Multisite_Domain_Mapping" target="_blank">WordPress Multisite Domain Mapping</a><br/>
<a href="https://woomultistore.com/wp-multisite-change-subsite-from-subdomain-to-domain/" target="_blank">WP Multisite – Change subsite from subdomain to domain</a>
</p>
<p>
If you wish to clone your site, please use <a href="https://wordpress.org/plugins/ns-cloner-site-copier/" target="_blank">NS Cloner – Site Copier</a><br/> 
</p>
<p>
<i><small>(After you copy a site using NS Cloner, please delete all products from the new site. And, then sync them using the plugin’s network admin menu or WordPress’s bulk editor).</small></i>
</p>
EOD;

	if ( is_plugin_active( 'woocommerce-multistore/woocommerce-multistore.php' ) ) {
		deactivate_plugins( 'woocommerce-multistore/woocommerce-multistore.php' );
	}

	wp_die( $message, '', array(
		'link_url'  => self_admin_url( 'plugins.php' ),
		'link_text' => function_exists( '__' ) ? __( '&laquo; Back' ) : '&laquo; Back',
	) );

}
