<?php
$config = array();

$config['plugin_screen'] = 'toplevel_page_maintenance';
$config['icon_border'] = 'none';
$config['icon_right'] = '35px';
$config['icon_bottom'] = '35px';
$config['icon_image'] = 'maintenance.png';
$config['icon_padding'] = '0px';
$config['icon_size'] = '65px';
$config['menu_accent_color'] = '#dd3036';
//$config['custom_css'] = '#wf-flyout .wff-menu-item .dashicons.dashicons-universal-access { font-size: 30px; padding: 0px 10px 0px 0; } #wf-flyout .ucp-icon .wff-icon img { max-width: 70%; } #wf-flyout .ucp-icon .wff-icon { line-height: 57px; }';

$config['menu_items'] = array(
  array('href' => 'https://wpreset.com/?ref=wff-maintenance', 'target' => '_blank', 'label' => 'Get WP Reset PRO with 50% off', 'icon' => 'wp-reset.png'),
  array('href' => admin_url('plugin-install.php?s=accessibe&tab=search&type=author'), 'target' => '_blank', 'label' => 'Make your site accessible to people with disabilities', 'icon' => 'dashicons-universal-access'),
  array('href' => 'https://underconstructionpage.com/?ref=wff-maintenance&coupon=welcome', 'target' => '_blank', 'label' => 'Create the perfect Under Construction Page', 'icon' => 'ucp.png', 'class' => 'ucp-icon'),
  array('href' => 'https://wpsticky.com/?ref=wff-maintenance', 'target' => '_blank', 'label' => 'Make a menu sticky with WP Sticky', 'icon' => 'dashicons-admin-post'),
  array('href' => 'https://wordpress.org/support/plugin/maintenance/reviews/?filter=5#new-post', 'target' => '_blank', 'label' => 'Rate the Plugin', 'icon' => 'dashicons-thumbs-up'),
  array('href' => 'https://wordpress.org/support/plugin/maintenance/#new-post', 'target' => '_blank', 'label' => 'Get Support', 'icon' => 'dashicons-sos'),
);
