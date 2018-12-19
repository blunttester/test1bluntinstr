<?php
/**
 * Template for search form that separates blog posts search from products search
 * @package themify
 * @since 1.0.0
 */

$post_slug = 'post';
$product_slug = 'product';

if ( class_exists( 'WPML_Slug_Translation' ) && method_exists( 'WPML_Slug_Translation', 'get_translated_slug' ) ) {
	global $sitepress, $wp_post_types, $wpml_slug_translation;
	$current_language = $sitepress->get_current_language();
	if ( isset( $wpml_slug_translation ) ) {
		if ( $translated_post_slug = $wpml_slug_translation->get_translated_slug( $wp_post_types['post']->rewrite['slug'], $current_language ) ) {
			$post_slug = $translated_post_slug;
		}
		if ( $translated_product_slug = $wpml_slug_translation->get_translated_slug( $wp_post_types['product']->rewrite['slug'], $current_language ) ) {
			$product_slug = $translated_product_slug;
		}
	} else {
		if ( $translated_post_slug = WPML_Slug_Translation::get_translated_slug( $wp_post_types['post']->rewrite['slug'], $current_language ) ) {
			$post_slug = $translated_post_slug;
		}
		if ( $translated_product_slug = WPML_Slug_Translation::get_translated_slug( $wp_post_types['product']->rewrite['slug'], $current_language ) ) {
			$product_slug = $translated_product_slug;
		}
	}
}

$exclude_posts = themify_get( 'setting-search_exclude_post' );
$exclude_products = themify_get( 'setting-search_exclude_product' );

$option_hidden = themify_get( 'setting-shop_search_option_hidden' );
$option_preselect = themify_get( 'setting-shop_search_option_preselect' );

?>

<?php if ( ! $exclude_posts || ! $exclude_products ) : ?>

	<form method="get" id="searchform" action="<?php echo home_url(); ?>/">

		<?php if ( $option_preselect != 'product' ) : // == 'post' || empty || null ?>
			<input type="hidden" class="search-type" name="post_type" value="<?php echo esc_attr( $post_slug ); ?>" />
		<?php elseif ( $option_preselect == 'product' ) : ?>
			<input type="hidden" class="search-type" name="post_type" value="<?php echo esc_attr( $product_slug ); ?>" />
		<?php endif ?>

		<input type="text" name="s" id="s"  placeholder="<?php esc_attr_e( 'Search', 'themify' ); ?>" />

		<?php if ( $option_hidden != 'on' ) : // == empty || null ?>
			<div class="search-option">
				<input id="search-blog" class="search-blog" <?php if ( $option_preselect != 'product' ) echo 'checked="checked"' ?> type="radio" name="search-option" value="<?php echo esc_attr( $post_slug ); ?>" /> <label for="search-blog"><?php _e( 'Blog', 'themify' ); ?></label>
				<input id="search-shop" class="search-shop" <?php if ( $option_preselect == 'product' ) echo 'checked="checked"' ?> type="radio" name="search-option" value="<?php echo esc_attr( $product_slug ); ?>" /> <label for="search-shop"><?php _e( 'Shop', 'themify' ); ?></label>
			</div>
		<?php elseif ( $option_preselect != 'product' ) : ?>
			<input id="search-blog" class="search-blog" type="hidden" name="search-option" value="<?php echo esc_attr( $post_slug ); ?>" />
		<?php elseif ( $option_preselect == 'product' ) : ?>
			<input id="search-shop" class="search-shop" type="hidden" name="search-option" value="<?php echo esc_attr( $product_slug ); ?>" />
		<?php endif ?>

	</form>

<?php endif ?>
