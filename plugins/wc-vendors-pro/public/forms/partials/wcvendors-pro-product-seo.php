<?php
/**
 * Display Product SEO form fields
 *
 * @package    WCVendors_Pro
 * @version    1.5.8
 * @since      1.5.8
 */
?>
<div id="wcv_product_seo" class="wcv-metaboxes-wrapper">

	<div class="wcv-cols-group wcv-horizontal-gutters product-seo">
		<div class="all-100">
			<div id="wcv-attr-message" class="inline notice woocommerce-message">
				<?php _e( 'Leave field empty to use product details.', 'wcvendors-pro' ); ?>
			</div>
		</div>
	</div>
	<div class="wcv-cols-group wcv-horizontal-gutters product-seo">
		<div class="all-100">
			<!-- Product SEO Title -->
			<?php
			$disable_product_seo = wc_string_to_bool( get_option( 'wcvendors_hide_product_seo' ) );
			$hide_title          = wc_string_to_bool( get_option( 'wcvendors_hide_product_seo_title' ) );
			$hide_description    = wc_string_to_bool( get_option( 'wcvendors_hide_product_seo_description' ) );
			$hide_keywords       = wc_string_to_bool( get_option( 'wcvendors_hide_product_seo_keywords' ) );
			$hide_opengraph      = wc_string_to_bool( get_option( 'wcvendors_hide_product_seo_opengraph' ) );
			$hide_twitter_card   = wc_string_to_bool( get_option( 'wcvendors_hide_product_seo_twitter' ) );

			if ( ! $disable_product_seo && ! $hide_title ) :
				WCVendors_Pro_Form_Helper::input(
					apply_filters(
						'wcv_product_seo_title_field',
						array(
							'id'            => 'wcv_product_seo_title',
							'label'         => __( 'SEO title', 'wcvendors-pro' ),
							'placeholder'   => __( 'SEO title', 'wcvendors-pro' ),
							'type'          => 'text',
							'desc_tip'      => true,
							'description'   => __( 'The product title will be used if left blank', 'wcvendors-pro' ),
							'value'         => $seo_title,
							'wrapper_start' => '<div class="control-group"><div class="all-100 small-100">',
							'wrapper_end'   => '</div></div>',
						)
					)
				);
			endif;
			?>

			<!-- Product SEO Description -->
			<?php
			if ( ! $disable_product_seo && ! $hide_description ) :
			WCVendors_Pro_Form_Helper::textarea(
				apply_filters(
					'wcv_product_seo_description_field',
					array(
						'id'            => 'wcv_product_seo_description',
						'label'         => __( 'SEO description', 'wcvendors-pro' ),
						'placeholder'   => __( 'SEO description', 'wcvendors-pro' ),
						'type'          => 'text',
						'desc_tip'      => true,
						'description'   => __( 'The product short description will be used if left blank', 'wcvendors-pro' ),
						'value'         => $seo_description,
						'wrapper_start' => '<div class="control-group"><div class="all-100 small-100">',
						'wrapper_end'   => '</div></div>',
					)
				)
			);
		endif;
			?>

			<!-- Product SEO Keywords -->
			<?php
			if ( ! $disable_product_seo && ! $hide_keywords ) :
			WCVendors_Pro_Form_Helper::input(
				apply_filters(
					'wcv_product_seo_keywords_field',
					array(
						'id'            => 'wcv_product_seo_keywords',
						'label'         => __( 'Keywords', 'wcvendors-pro' ),
						'placeholder'   => __( 'Keywords', 'wcvendors-pro' ),
						'type'          => 'text',
						'value'         => $seo_keywords,
						'desc_tip'      => true,
						'description'   => __( 'Keywords should be comma separated. The product categories/tags will be used if left blank.', 'wcvendors-pro' ),
						'wrapper_start' => '<div class="control-group"><div class="all-100 small-100">',
						'wrapper_end'   => '</div></div>',
					)
				)
			);
		endif;
			?>

			<!-- Enable OpenGraph -->
			<?php
			if ( ! $disable_product_seo && ! $hide_opengraph ) :
			WCVendors_Pro_Form_Helper::input(
				apply_filters(
					'wcv_product_seo_opengraph_field',
					array(
						'id'            => 'wcv_product_seo_opengraph',
						'label'         => __( 'Allow output of OpenGraph data for this product', 'wcvendors-pro' ),
						'type'          => 'checkbox',
						'value'         => $seo_opengraph,
						'wrapper_start' => '<div class="control-group"><div class="all-100 small-100">',
						'wrapper_end'   => '</div></div>',
					)
				)
			);
		endif;
			?>

			<!-- Enable Output of Twitter Card -->
			<?php
			if ( ! $disable_product_seo && ! $hide_twitter_card ) :
			WCVendors_Pro_Form_Helper::input(
				apply_filters(
					'wcv_product_seo_twitter_card_field',
					array(
						'id'            => 'wcv_product_seo_twitter_card',
						'label'         => __( 'Allow output of Twitter Card for this product', 'wcvendors-pro' ),
						'type'          => 'checkbox',
						'value'         => $seo_twitter_card,
						'wrapper_start' => '<div class="control-group"><div class="all-100 small-100">',
						'wrapper_end'   => '</div></div>',
					)
				)
			);
		endif;
			?>
		</div>
	</div>
</div>
