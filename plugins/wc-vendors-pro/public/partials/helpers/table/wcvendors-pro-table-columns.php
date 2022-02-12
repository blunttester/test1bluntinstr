<?php

/**
 * Table header code
 *
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @link       http://www.wcvendors.com
 * @since      1.0.0
 * @version    1.6.5
 *
 * @package    WCVendors_Pro
 * @subpackage WCVendors_Pro/public/partials/helpers/table
 */
?>

<!-- Output the table header -->
<thead>
<tr>
	<?php foreach ( $this->columns as $key => $column ) : ?>
		<?php
		if ( $column == 'ID' ) {
			continue;
		}
		?>
		<th class="<?php echo $key; ?>"><?php echo $column; ?></th>
	<?php endforeach; ?>
</tr>
</thead>
