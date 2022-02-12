<?php

/**
 * WC Vendors Pro Admin functions
 *
 * Functions for the admin interface
 *
 * @package WCVendors Pro/Functions
 * @version 1.5.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


function wcv_admin_checkbox( $value, $required = false ) {

	if ( ! isset( $value['type'] ) ) {
		return;
	}
	if ( ! isset( $value['id'] ) ) {
		$value['id'] = '';
	}
	if ( ! isset( $value['title'] ) ) {
		$value['title'] = isset( $value['name'] ) ? $value['name'] : '';
	}
	if ( ! isset( $value['class'] ) ) {
		$value['class'] = '';
	}
	if ( ! isset( $value['css'] ) ) {
		$value['css'] = '';
	}
	if ( ! isset( $value['default'] ) ) {
		$value['default'] = '';
	}
	if ( ! isset( $value['desc'] ) ) {
		$value['desc'] = '';
	}
	if ( ! isset( $value['desc_tip'] ) ) {
		$value['desc_tip'] = false;
	}
	if ( ! isset( $value['placeholder'] ) ) {
		$value['placeholder'] = '';
	}
	if ( ! isset( $value['suffix'] ) ) {
		$value['suffix'] = '';
	}

	if ( $required && ! isset( $value ['required_id'] ) ) {
		return;
	}

	if ( $required && isset( $value['required_id'] ) && ! empty( $value['required_id'] ) ) {
		$value['id'] = $value['required_id'];
	}

	// Custom attribute handling
	$custom_attributes = array();

	if ( ! empty( $value['custom_attributes'] ) && is_array( $value['custom_attributes'] ) ) {
		foreach ( $value['custom_attributes'] as $attribute => $attribute_value ) {
			$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $attribute_value ) . '"';
		}
	}

	$option_value      = WCVendors_Admin_Settings::get_option( $value['id'], $value['default'] );
	$visibility_class  = array();
	$field_description = WCVendors_Admin_Settings::get_field_description( $value );
	extract( $field_description );

	if ( ! empty( $value['title'] ) ) { ?>
		<legend class="screen-reader-text"><span><?php echo esc_html( $value['title'] ); ?></span></legend>

		<?php
	}
	?>
	<input
			name="<?php echo esc_attr( $value['id'] ); ?>"
			id="<?php echo esc_attr( $value['id'] ); ?>"
			type="checkbox"
			class="<?php echo esc_attr( isset( $value['class'] ) ? $value['class'] : '' ); ?> wcv_admin_checkbox"
			value="1"
		<?php checked( $option_value, 'yes' ); ?>
		<?php echo implode( ' ', $custom_attributes ); ?>
	/>

	<?php

}
