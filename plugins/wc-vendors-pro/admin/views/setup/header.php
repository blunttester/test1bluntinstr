<?php
/**
 * Admin View: Setup Wizard Header
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta name="viewport" content="width=device-width"/>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<title><?php esc_html_e( 'WC Vendors Pro &rsaquo; Setup Wizard', 'wcvendors-pro' ); ?></title>
	<?php wp_print_scripts( 'wcvendors-pro-setup' ); ?>
	<?php do_action( 'admin_print_styles' ); ?>
</head>
<body class="wcv-setup wp-core-ui">
<h1 id="wcv-logo"><a href="https://www.wcvendors.com/"><img
				src="<?php echo plugin_dir_url( WCV_PRO_PLUGIN_FILE ); ?>admin/assets/images/wcvendors_pro_logo.png"
				alt="WC Vendors Pro"/></a></h1>
