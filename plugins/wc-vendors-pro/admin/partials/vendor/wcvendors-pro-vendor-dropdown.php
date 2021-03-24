<?php
/**
 * Output the vendor metabox
 *
 * @link       http://www.wcvendors.com
 * @since      1.3.0
 * @version    1.6.5
 *
 * @package    WCVendors_Pro
 * @subpackage WCVendors_Pro/public/partials
 */

$output  = "<select style='width:150px;' name='vendor_id' id='vendor_id' class='select'>\n";
$output .= "\t<option value=''>" . sprintf( __( 'Select a %s', 'wcvendors-pro' ), wcv_get_vendor_name() ) . "</option>\n";
$output .= wcv_vendor_drop_down_options( $users, $wp_query->query_vars['author'] );
$output .= '</select>';
$output .= '<script type="text/javascript">jQuery(function() { jQuery("#vendor_id").select2(); } );</script>';
