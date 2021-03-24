<?php
/**
 * Store opening hours form
 *
 * This file is used to load the store opening hours form
 *
 * @link       http://www.wcvendors.com
 * @since      1.5.9
 * @version    1.7.4
 *
 * @package    WCVendors_Pro
 * @subpackage WCVendors_Pro/public/partials/settings
 */

$time_format = apply_filters( 'wcv_opening_hours_time_format', wc_time_format() );
$labels      = wcv_days_labels();
?>

<div class="wcv-column-group wcv-horizontal-gutters wcv-opening-hours-wrapper">
	<?php echo is_admin() ? '<h3>' : '<p>'; ?>
	<?php esc_attr_e( 'Store opening hours', 'wcvendors-pro' ); ?>
	<?php echo is_admin() ? '</h3>' : '</p>'; ?>

	<table class="form-table">
		<thead>
		<tr>
			<th style="width:10%;"><?php esc_attr_e( '&nbsp;', 'wcvendors-pro' ); ?></th>
			<th style="width:30%;"><?php esc_attr_e( 'Day', 'wcvendors-pro' ); ?></th>
			<th style="width:20%;"><?php esc_attr_e( 'Open', 'wcvendors-pro' ); ?></th>
			<th style="width:20%;"><?php esc_attr_e( 'Closing', 'wcvendors-pro' ); ?></th>
			<th style="width:20%;">
				<a href="#" id="add-work-hours" title="<?php esc_attr_e( 'Add new row', 'wcvendors-pro' ); ?>">
					<svg class="wcv-icon wcv-icon-md">
						<use xlink:href="<?php echo esc_url_raw( WCV_PRO_PUBLIC_ASSETS_URL ); ?>svg/wcv-icons.svg#wcv-icon-plus"></use>
					</svg>
				</a>
			</th>
		</tr>
		</thead>
		<tbody id="opening-hours">
		<?php foreach ( $hours as $opening ) : ?>

			<?php
			if ( ! isset( $labels[ $opening['day'] ] ) ) {
				continue;
			}
			?>
			<tr>
				<td>
					<?php if ( ! $opening['status'] ) : ?>
						<input type="checkbox" name="status[]" class="status" value="0" />
					<?php else : ?>
						<input type="checkbox" name="status[]" class="status"
								value="1" <?php echo 1 == $opening['status'] ? 'checked' : ''; ?> />
					<?php endif; ?>
				</td>
				<td>
					<label class="days-label"><?php echo esc_attr( $labels[ $opening['day'] ] ); ?></label>
					<input type="hidden" name="days[]" class="days-hidden" value="<?php echo esc_attr( $opening['day'] ); ?>"/>
					<span class="edit-days"></span>
				</td>
				<td>
					<label class="open-label"><?php echo esc_attr( ( 'open' === $opening['open'] || 'closed' == $opening['open'] ) ? esc_attr( $labels[ $opening['open'] ] ) : esc_attr( gmdate( $time_format, strtotime( $opening['open'] ) ) ) ); ?></label>
					<input type="hidden" name="open[]" class="open-hidden" value="<?php echo esc_attr( $opening['open'] ); ?>"
							data-list="newday"/>
					<span class="edit-opening"></span>
				</td>
				<td>
					<label class="close-label"><?php echo esc_attr( ( 'open' === $opening['close'] || 'closed' === $opening['close'] ) ? $labels[ $opening['close'] ] : esc_attr( gmdate( $time_format, strtotime( $opening['close'] ) ) ) ); ?></label>
					<input type="hidden" name="close[]" class="close-hidden" value="<?php echo esc_attr( $opening['close'] ); ?>"/>
					<span class="edit-closing"></span>
				</td>
				<td>
					<a href="#" data-action="edit" class="edit" title="<?php esc_attr_e( 'Edit row', 'wcvendors-pro' ); ?>">
						<svg class="wcv-icon wcv-icon-md">
							<use xlink:href="<?php echo esc_url_raw( WCV_PRO_PUBLIC_ASSETS_URL ); ?>svg/wcv-icons.svg#wcv-icon-pen-square"></use>
						</svg>
					</a>

					<a href="#" data-action="done" class="done hidden"
							title="<?php esc_attr_e( 'Done editing', 'wcvendors-pro' ); ?>">
						<svg class="wcv-icon wcv-icon-md">
							<use xlink:href="<?php echo esc_url_raw( WCV_PRO_PUBLIC_ASSETS_URL ); ?>svg/wcv-icons.svg#wcv-icon-check-square"></use>
						</svg>
					</a>

					<a href="#" class="remove-row" title="<?php esc_attr_e( 'Remove this Row', 'wcvendors-pro' ); ?>">
						<svg class="wcv-icon wcv-icon-md">
							<use xlink:href="<?php echo esc_url_raw( WCV_PRO_PUBLIC_ASSETS_URL ); ?>svg/wcv-icons.svg#wcv-icon-times"></use>
						</svg>
					</a>
				</td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
</div>
