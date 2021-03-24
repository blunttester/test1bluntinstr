<?php

/**
 * Output a form fields table with required check boxes
 *
 * This file is used to display the feedback edit form on the backend.
 *
 * @link       http://www.wcvendors.com
 * @since      1.5.0
 *
 * @package    WCVendors_Pro
 * @subpackage WCVendors_Pro/admin/settings/partials/
 */

$colspan = ( $require ) ? 3 : 2;

?>

<table class="form_field_required">
	<thead>
	<tr>
		<th class="form_field_title"><?php _e( 'Field', 'wcvendors-pro' ); ?></th>
		<th class="form_field_hide"><?php _e( 'Hide', 'wcvendors-pro' ); ?></th>
		<th class="form_field_required">
		<?php
		if ( $require ) :
			?>
			<?php _e( 'Required', 'wcvendors-pro' ); ?><?php endif; ?></th>
	</tr>
	</thead>
	<tbody>
	<?php foreach ( $value['fields'] as $field ) : ?>
		<tr>
			<td><?php echo $field['title']; ?></td>
			<td><?php wcv_admin_checkbox( $field ); ?></td>
			<td>
			<?php
			if ( $require ) :
				?>
				<?php wcv_admin_checkbox( $field, true ); ?><?php endif; ?></td>
		</tr>
	<?php endforeach; ?>
	</tbody>
</table>
