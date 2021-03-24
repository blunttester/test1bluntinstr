<?php
/**
 * The template for displaying the dashboard quicklinks
 *
 * Override this template by copying it to yourtheme/wc-vendors/dashboard/
 *
 * @package    WCVendors_Pro
 * @version    1.6.6
 */
?>

<div class="wcv-cols-group wcv-horizontal-gutters">
	<div class="all-40 small-100">
		<?php do_action( 'wcv_dashboard_before_quick_actions' ); ?>
		<?php foreach ( $quick_links as $link => $details ) : ?>
			<a href="<?php echo $details['url']; ?>" class="button quick-link-btn <?php echo $link; ?>"><?php echo $details['label']; ?></a>
		<?php endforeach; ?>
		<?php do_action( 'wcv_dashboard_after_quick_actions' ); ?>
	</div>
	<div class="all-60 small-100 wcv-usage-stats align-right">
		<?php do_action( 'wcv_dashboard_before_usage_statistics' ); ?>

		<?php foreach ( $stats as $key => $stat ) : ?>
			<?php $stat = wp_parse_args( $stat, array(
				'icon'     => '',
				'template' => '%s / %s',
				'over'     => false,
			) ); ?>
			<button class="button <?php echo $key; ?> <?php echo $stat['over'] ? 'over_limit' : ''; ?>">
				<?php if ( $stat['icon'] ) : ?>
					<svg class="wcv-icon wcv-icon-sm">
						<use xlink:href="<?php echo WCV_PRO_PUBLIC_ASSETS_URL; ?>svg/wcv-icons.svg#<?php echo $stat['icon']; ?>"></use>
					</svg>
				<?php endif; ?>
				<?php echo sprintf( $stat['template'], $stat['usage'], $stat['limit'] ); ?>
			</button>
		<?php endforeach; ?>

		<?php do_action( 'wcv_dashboard_after_usage_statistics' ); ?>
	</div>
</div>
