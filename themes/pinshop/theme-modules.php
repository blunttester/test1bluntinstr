<?php

/*
To add custom modules to the theme, create a new 'custom-modules.php' file in the theme folder.
They will be added to the theme automatically.
*/

/**
 * Shop modules
 * @since 1.0.0
 ***************************************************************************/

/**
 * Markup to allow search form options to pre-select blog or shop, and hide it
 * @param array $data
 * @return string
 */
function shop_search_field_option( $data = array() ) {

	$data = themify_get_data();
	
	/**
	 * Variable key in theme settings
	 * @var string
	 */
	$key = 'setting-shop_search_option_preselect';

	/**
	 * Module markup
	 * @var string
	 */
	$html = sprintf('<p><label for="%1$s"><input type="radio" id="%1$s" name="%3$s" value="%4$s" %6$s /> %8$s</label>'.
					'<br><label for="%2$s"><input type="radio" id="%2$s" name="%3$s" value="%5$s" %7$s /> %9$s</label></p>',
		esc_attr( $key . '_post' ),
		esc_attr( $key . '_product' ),
		$key,
		'post',
		'product',
		checked( isset( $data[$key] ) ? $data[$key] : 'post', 'post', false ),
		checked( isset( $data[$key] ) ? $data[$key] : '', 'product', false ),
		__( 'Pre-select search form to Blog.', 'themify' ),
		__( 'Pre-select search form to Shop.', 'themify' )
	);
	
	/**
	 * Variable key in theme settings
	 * @var string
	 */
	$key = 'setting-shop_search_option_hidden';

	/**
	 * Module markup
	 * @var string
	 */
	$html .= sprintf('<p><label for="%1$s"><input type="checkbox" id="%1$s" name="%1$s" %2$s /> %3$s</label></p>',
		$key,
		checked( isset( $data[$key] ) ? $data[$key] : '', 'on', false ),
		__( 'Hide the search option.', 'themify' )
	);

	return $html;
}

/**
 * Markup for fixed header selection module
 * @param array $data
 * @return string
 */
function themify_fixed_header_module( $data = array() ) {
	/**
	 * Variable key in theme settings
	 * @var string
	 */
	$key = 'setting-fixed_header_disabled';

	/**
	 * Module markup
	 * @var string
	 */
	$html = sprintf('<p><label for="%1$s"><input type="checkbox" id="%1$s" name="%1$s" %2$s /> %3$s</label></p>',
		$key,
		checked( themify_get( $key ), 'on', false ),
		__('Check to disable Fixed Header.', 'themify')
	);

	return $html;
}

/**
 * Markup for module to disable masonry in shop and archive layout
 * @param array $data
 * @return string
 */
function shop_archive_layout( $data = array() ) {

	$data = themify_get_data();
	/**
	 * Sidebar option
	 */
	$val = isset( $data['setting-shop_archive_layout'] ) ? $data['setting-shop_archive_layout'] : '';
	$options = array(
		array('value' => 'sidebar1', 'img' => 'images/layout-icons/sidebar1.png', 'title' => __('Sidebar Right', 'themify')),
		array('value' => 'sidebar1 sidebar-left', 'img' => 'images/layout-icons/sidebar1-left.png', 'title' => __('Sidebar Left', 'themify')),
		array('value' => 'sidebar-none', 'img' => 'images/layout-icons/sidebar-none.png','selected' => true, 'title' => __('No Sidebar', 'themify'))
	);

	$html = '<p><span class="label">' . __('Archive Sidebar Option', 'themify') . '</span>';
	foreach ( $options as $option ) {
		if ( ( '' == $val || ! $val || ! isset( $val ) ) && ( isset( $option['selected'] ) && $option['selected'] ) ) {
			$val = $option['value'];
		}
		$class = $val == $option['value'] ?"selected":"";
		$html.= '<a href="#" class="preview-icon '.$class.'" title="'.$option['title'].'"><img src="'.THEME_URI.'/'.$option['img'].'" alt="'.$option['value'].'"  /></a>';
	}
	$html.= '<input type="hidden" name="setting-shop_archive_layout" class="val" value="'.$val.'" /></p>';

	/**
	 * Variable key in theme settings
	 * @var string
	 */
	$key = 'setting-shop_masonry_disabled';

	/**
	 * Module markup
	 * @var string
	 */
	$html.= sprintf('<p><label for="%1$s"><input type="checkbox" id="%1$s" name="%1$s" %2$s /> %3$s</label></p>',
		$key,
		checked( themify_get( $key ), 'on', false ),
		__( 'Disable masonry layout in product archive view.', 'themify' )
	);

	return $html;
}

/**
 * Choose pagination or infinite scroll
 * @param array $data
 * @return string
 */
function themify_pagination_infinite($data=array()){
	$data = themify_get_data();

	$html = '<p>';

	//Infinite Scroll
	$html .= '<input ' . checked( themify_check( 'setting-more_posts' ) ? themify_get( 'setting-more_posts' ) : 'infinite', 'infinite', false ) . ' type="radio" name="setting-more_posts" value="infinite" /> ';
	$html .= __('Infinite Scroll (posts are loaded on the same page)', 'themify');
	$html .= '<div class="pushlabel disable-autoinfinite" data-show-if-element="[name=setting-more_posts]:checked" data-show-if-value="infinite">';
	$html .= '<label for="setting-autoinfinite"><input type="checkbox" id="setting-autoinfinite" name="setting-autoinfinite" '.checked( themify_get( 'setting-autoinfinite' ), 'on', false ).'/> ' . __('Disable automatic infinite scroll', 'themify').'</label>';
	$html .= '<br/>';
	$html .= '<label for="setting-infinite-url"><input type="checkbox" id="setting-infinite-url" name="setting-infinite-url" '.checked( themify_get( 'setting-infinite-url' ), 'on', false ).'/> ' . __('Disable page number updates on address URL on scrolling', 'themify').'</label>';
	$html .= '</div>';
	$html .= '<br/>';

	//Numbered pagination
	$html .= '<input ' . checked( themify_get( 'setting-more_posts' ), 'pagination', false ) . ' type="radio" name="setting-more_posts" value="pagination" /> ';
	$html .= __('Standard Pagination', 'themify');
	$html .= '</p>';

	return $html;
}

/**
 * Creates module with settings for product slider
 * @param array
 * @return string
 * @since 1.0.0
 */
function themify_product_slider($data=array()){
	$data = themify_get_data();

	/** Slider values @var array */
	$slider_ops = array( __('On', 'themify') => 'on', __('Off', 'themify') => 'off' );
	/** Slider status */
		$enabled_display = '';
		$display_posts = '';
		$display_images = '';
		$display_posts_display = '';
		$display_images_display = '';
	if ( '' == themify_get( 'setting-product_slider_enabled' ) || 'on' == themify_get( 'setting-product_slider_enabled' ) ) {
		$enabled_checked = "selected='selected'";
	} else {
		$enabled_display = "style='display:none;'";
	}

	$show_options = array('' => '',__('Yes', 'themify') => 'yes', __('No', 'themify') => 'no');
	$auto_options = array(0,1,2,3,4,5,6,7);
	$scroll_options = array(1,2,3,4,5,6,7);
	$speed_options = array( __('Fast', 'themify')=>300, __('Normal', 'themify')=>1000, __('Slow', 'themify')=>2000);
	$wrap_options = array( __('Yes', 'themify') => 'yes', __('No', 'themify') => 'no' );
	$image_options = array("one","two","three","four","five","six","seven","eight","nine","ten");

	/**
	 * HTML for settings panel
	 * @var string
	 */
	$output = '<p><span class="label">' . __('Enable Slider', 'themify') . '</span> <select name="setting-product_slider_enabled" class="feature_box_enabled_check">';
	/** Iterate through slider options */
	foreach ( $slider_ops as $key => $val ) {
		$output .= '<option value="'.$val.'" ' . selected( themify_get( 'setting-product_slider_enabled' ), $val, false ) . '>' . $key . '</option>';
	}
	$output .= '</select>' . '</p>

				<div class="feature_box_enabled_display" '.$enabled_display.'>
				<p class="pushlabel feature_box_posts">';
					$output .= wp_dropdown_categories(
						array("show_option_all"=> __('Featured Products', 'themify'),
							  'show_option_none' => __( 'Disabled', 'themify' ),
						"hide_empty"=>0,
						"echo"=>0,
						"name"=>"setting-product_slider_posts_category",
						'selected' => themify_get( 'setting-product_slider_posts_category' ),
						'taxonomy' => 'product_cat'
					));
	$output .=	'<br/><input type="text" name="setting-product_slider_posts_slides" value="' . themify_get( 'setting-product_slider_posts_slides' ) . '" class="width2" /> ' . __('number of products to be queried', 'themify') . '
				</p>';

	$output .= '<p class="feature_box_posts">
					<span class="label">' . __('Hide Title', 'themify') . '</span>
					<select name="setting-product_slider_hide_title">';
					foreach($show_options as $name => $option){
							if ( themify_get( 'setting-product_slider_hide_title' ) == $option ) {
								$output .= '<option value="'.$option.'" selected="selected">'.$name.'</option>';
							} else {
								$output .= '<option value="'.$option.'">'.$name.'</option>';
							}
						}
	$output .= '	</select>
				</p>';

	$output .= '<p class="feature_box_posts">
					<span class="label">' . __('Hide Price', 'themify') . '</span>
					<select name="setting-product_slider_hide_price">';
					foreach($show_options as $name => $option){
							if ( themify_get( 'setting-product_slider_hide_price' ) == $option ) {
								$output .= '<option value="'.$option.'" selected="selected">'.$name.'</option>';
							} else {
								$output .= '<option value="'.$option.'">'.$name.'</option>';
							}
						}
	$output .= '	</select>
				</p>';


	$output .= '<p>
					<span class="label">' . __('Visible', 'themify') . '</span>
					<select name="setting-product_slider_visible">';
					for($x = 1; $x <= apply_filters('themify_product_slider_visible', 7); $x++){
						$output .= '<option value="'.$x.'" ' . selected( themify_check( 'setting-product_slider_visible' ) ? themify_get( 'setting-product_slider_visible' ) : 4, $x, false ) . '>'.$x.'</option>';
					}
		$output .=	'</select> <small>' . __('(# of slides visible at the same time)', 'themify') . '</small>
				</p>
				<p>
				<span class="label">' . __('Auto Play', 'themify') . '</span>
							<select name="setting-product_slider_auto">
							';
						foreach($auto_options as $option){
							if ( themify_get( 'setting-product_slider_auto' ) == $option ) {
								$output .= '<option value="'.$option.'" selected="selected">'.$option.'</option>';
							} else {
								$output .= '<option value="'.$option.'">'.$option.'</option>';
							}
						}
		$output .= '
					</select> <small>' . __('(auto advance slider, 0 = off)', 'themify') . '</small>
				</p>
				<p>
				<span class="label">' . __('Scroll', 'themify') . '</span>
							<select name="setting-product_slider_scroll">
							';
						foreach($scroll_options as $option){
							if ( themify_get( 'setting-product_slider_scroll' ) == $option ) {
								$output .= '<option value="'.$option.'" selected="selected">'.$option.'</option>';
							} else {
								$output .= '<option value="'.$option.'">'.$option.'</option>';
							}
						}
		$output .= '
					</select>
				</p>
				<p>
					<span class="label">' . __('Speed', 'themify') . '</span>
					<select name="setting-product_slider_speed">';
					foreach($speed_options as $name => $val){
						if ( themify_get( 'setting-product_slider_speed' ) == $val ) {
							$output .= '<option value="'.$val.'" selected="selected">'.$name.'</option>';
						} else {
							$output .= '<option value="'.$val.'">'.$name.'</option>';
						}
					}
		$output .= '</select>
				</p>
				<p>
					<span class="label">' . __('Wrap Slides', 'themify') . '</span>
					<select name="setting-product_slider_wrap">';
					foreach($wrap_options as $name => $value){
							if ( themify_get( 'setting-product_slider_wrap' ) == $value ) {
								$output .= '<option value="'.$value.'" selected="selected">'.$name.'</option>';
							} else {
								$output .= '<option value="'.$value.'">'.$name.'</option>';
							}
						}
		$output .=	'</select>
				</p>
				</div>';
	return $output;
}

/**
 * Creates module for general shop layout and settings
 * @param array
 * @return string
 * @since 1.0.0
 */
function themify_shop_layout($data=array()){
	$data = themify_get_data();

	$options = array(
		array('value' => 'sidebar1', 'img' => 'images/layout-icons/sidebar1.png', 'selected' => true, 'title' => __('Sidebar Right', 'themify')),
		array('value' => 'sidebar1 sidebar-left', 'img' => 'images/layout-icons/sidebar1-left.png', 'title' => __('Sidebar Left', 'themify')),
		array('value' => 'sidebar-none', 'img' => 'images/layout-icons/sidebar-none.png', 'title' => __('No Sidebar', 'themify'))
	);
	$products_layout_options = array(
		 array('value' => 'grid4', 'img' => 'images/layout-icons/grid4.png', 'selected' => true, 'title' => __('Grid 4', 'themify')),
		 array('value' => 'grid3', 'img' => 'images/layout-icons/grid3.png', 'title' => __('Grid 3', 'themify')),
		 array('value' => 'grid2', 'img' => 'images/layout-icons/grid2.png', 'title' => __('Grid 2', 'themify'))
	);
	$default_options = array(
		'' => '',
		__('Yes', 'themify') => 'yes',
		__('No', 'themify') => 'no'
	);
	$content_options = array(
		__('None', 'themify') => '',
		__('Short Description', 'themify') => 'excerpt',
		__('Full Content', 'themify') => 'content'
	);

	$val = isset( $data['setting-shop_layout'] ) ? $data['setting-shop_layout'] : '';

	/**
	 * Modules output
	 * @var String
	 * @since 1.0.0
	 */
	$output = '';

	/**
	 * Sidebar option
	 */
	$output .= '<p><span class="label">' . __('Page Sidebar Option', 'themify') . '</span>';
	foreach ( $options as $option ) {
		if ( ( '' == $val || ! $val || ! isset( $val ) ) && ( isset( $option['selected'] ) && $option['selected'] ) ) {
			$val = $option['value'];
		}
		if ( $val == $option['value'] ) {
			$class = "selected";
		} else {
			$class = "";
		}
		$output .= '<a href="#" class="preview-icon '.$class.'" title="'.$option['title'].'"><img src="'.THEME_URI.'/'.$option['img'].'" alt="'.$option['value'].'"  /></a>';
	}
	$output .= '<input type="hidden" name="setting-shop_layout" class="val" value="'.$val.'" /></p>';
	$output .= '<p>
					<span class="label">' . __('Products per page', 'themify') . '</span>
					<input type="text" name="setting-shop_products_per_page" value="' . themify_get( 'setting-shop_products_per_page' ) . '" class="width2" />
				</p>';

	/**
	 * Products Catalog Layout
	 */
	$output .= '<p>
					<span class="label">' . __('Product Layout', 'themify') . '</span>';
	$val = isset( $data['setting-products_layout'] ) ? $data['setting-products_layout'] : '';
	foreach($products_layout_options as $option){
		if ( ( '' == $val || ! $val || ! isset( $val ) ) && ( isset( $option['selected'] ) && $option['selected'] ) ) {
			$val = $option['value'];
		}
		if ( $val == $option['value'] ) {
			$class = "selected";
		} else {
			$class = "";
		}
		$output .= '<a href="#" class="preview-icon '.$class.'" title="'.$option['title'].'"><img src="'.THEME_URI.'/'.$option['img'].'" alt="'.$option['value'].'"  /></a>';
	}
	$output .= '	<input type="hidden" name="setting-products_layout" class="val" value="'.$val.'" />
				</p>';

	/**
	 * Hide Title Options
	 * @var String
	 * @since 1.1.2
	 */
	$hide_title = '';
	foreach($default_options as $name => $option){
		if ( themify_get( 'setting-product_archive_hide_title' ) == $option ) {
			$hide_title .= '<option value="'.$option.'" selected="selected">'.$name.'</option>';
		} else {
			$hide_title .= '<option value="'.$option.'">'.$name.'</option>';
		}
	}
	$output .= '<p class="feature_box_posts">
					<span class="label">' . __('Hide Product Title', 'themify') . '</span>
					<select name="setting-product_archive_hide_title">
						'.$hide_title.'
					</select>
				</p>';

	/**
	 * Hide Price Options
	 * @var String
	 * @since 1.1.2
	 */
	$hide_price = '';
	foreach($default_options as $name => $option){
		if ( themify_get( 'setting-product_archive_hide_price' ) == $option ) {
			$hide_price .= '<option value="'.$option.'" selected="selected">'.$name.'</option>';
		} else {
			$hide_price .= '<option value="'.$option.'">'.$name.'</option>';
		}
	}
	$output .= '<p class="feature_box_posts">
					<span class="label">' . __('Hide Product Price', 'themify') . '</span>
					<select name="setting-product_archive_hide_price">
						'.$hide_price.'
					</select>
				</p>';

	/**
	 * Hide Add to Cart Button
	 * @var String
	 */
	$hide_cart_button = '';
	foreach($default_options as $name => $option){
		if ( themify_get( 'setting-product_archive_hide_cart_button' ) == $option ) {
			$hide_cart_button .= '<option value="'.$option.'" selected="selected">'.$name.'</option>';
		} else {
			$hide_cart_button .= '<option value="'.$option.'">'.$name.'</option>';
		}
	}
	$output .= '<p class="feature_box_posts">
					<span class="label">' . __('Hide Add to Cart Button', 'themify') . '</span>
					<select name="setting-product_archive_hide_cart_button">
						'.$hide_cart_button.'
					</select>
				</p>';

	/**
	 * Hide Breadcrumbs
	 * @var String
	 */
	$output .= '<p><span class="label">' . __('Hide Shop Breadcrumbs', 'themify') . '</span>
				<label for="setting-hide_shop_breadcrumbs"><input type="checkbox" id="setting-hide_shop_breadcrumbs" name="setting-hide_shop_breadcrumbs" '.checked( themify_get( 'setting-hide_shop_breadcrumbs' ), 'on', false ).' /> ' . __('Check to hide shop breadcrumbs', 'themify') . '</label></p>';

	/**
	 * Hide Product Count
	 * @var String
	 */
	$output .= '<p><span class="label">' . __('Hide Product Count', 'themify') . '</span>
				<label for="setting-hide_shop_count"><input type="checkbox" id="setting-hide_shop_count" name="setting-hide_shop_count" '.checked( themify_get( 'setting-hide_shop_count' ), 'on', false ).' /> ' . __('Check to hide products counting', 'themify') . '</label></p>';

	/**
	 * Hide Sorting Bar
	 * @var String
	 */
	$output .= '<p><span class="label">' . __('Hide Sorting Bar', 'themify') . '</span>
				<label for="setting-hide_shop_sorting"><input type="checkbox" id="setting-hide_shop_sorting" name="setting-hide_shop_sorting" '.checked( themify_get( 'setting-hide_shop_sorting' ), 'on', false ).' /> ' . __('Check to hide product sorting bar', 'themify') . '</label></p>';

	/**
	 * Hide Shop Page Title
	 * @var String
	 */
	$output .= '<p><span class="label">' . __('Hide Shop Page Title', 'themify') . '</span>
				<label for="setting-hide_shop_title"><input type="checkbox" id="setting-hide_shop_title" name="setting-hide_shop_title" '.checked( themify_get( 'setting-hide_shop_title' ), 'on', false ).' /> ' . __('Check to hide shop page title', 'themify') . '</label></p>';

	/**
	 * Show Short Description Options
	 * @var String
	 * @since 1.1.2
	 */
	$show_short = '';
	foreach($content_options as $name => $option){
		if ( themify_get( 'setting-product_archive_show_short' ) == $option ) {
			$show_short .= '<option value="'.$option.'" selected="selected">'.$name.'</option>';
		} else {
			$show_short .= '<option value="'.$option.'">'.$name.'</option>';
		}
	}
	$output .= '<p class="feature_box_posts">
					<span class="label">' . __('Product Description', 'themify') . '</span>
					<select name="setting-product_archive_show_short">
						'.$show_short.'
					</select>
				</p>';

	/**
	 * Show variable products in lightbox
	 * @var String
	 */
	$output .= '
		<p>
			<span class="label">' . __( 'Variable Products Lightbox', 'themify' ) . '</span>
			<label for="setting-variable_lightbox"><input type="checkbox" id="setting-variable_lightbox"
			 name="setting-variable_lightbox" '.checked( themify_get( 'setting-variable_lightbox' ), 'on', false ).' /> ' . __( 'Check to disable the variable product lightbox', 'themify' ) . '</label>
		</p>';

	return $output;
}

/**
 * Creates module for single product settings
 * @param array
 * @return string
 * @since 1.0.0
 */
function themify_single_product($data=array()){
	$data = themify_get_data();

	$options = array(
		array('value' => 'sidebar1', 'img' => 'images/layout-icons/sidebar1.png', 'selected' => true, 'title' => __('Sidebar Right', 'themify')),
		array('value' => 'sidebar1 sidebar-left', 'img' => 'images/layout-icons/sidebar1-left.png', 'title' => __('Sidebar Left', 'themify')),
		array('value' => 'sidebar-none', 'img' => 'images/layout-icons/sidebar-none.png', 'title' => __('No Sidebar', 'themify'))
	);

	$default_options = array(
		array('name' => '', 'value' => ''),
		array('name' => __('Yes', 'themify'), 'value' => 'yes'),
		array('name' => __('No', 'themify'), 'value' => 'no')
	);

	$val = isset( $data['setting-single_product_layout'] ) ? $data['setting-single_product_layout'] : '';

	/**
	 * HTML for settings panel
	 * @var string
	 */
	$output = '<p><span class="label">' . __('Product Sidebar Option', 'themify') . '</span>';
	foreach ( $options as $option ) {
		if ( ( '' == $val || ! $val || ! isset( $val ) ) && ( isset( $option['selected'] ) && $option['selected'] ) ) {
			$val = $option['value'];
		}
		if ( $val == $option['value'] ) {
			$class = "selected";
		} else {
			$class = "";
		}
		$output .= '<a href="#" class="preview-icon '.$class.'" title="'.$option['title'].'"><img src="'.THEME_URI.'/'.$option['img'].'" alt="'.$option['value'].'"  /></a>';
	}
	$output .= '<input type="hidden" name="setting-single_product_layout" class="val" value="'.$val.'" /></p>';

	/**
	 * Hide Breadcrumbs
	 * @var String
	 */
	$output .= '<p><span class="label">' . __('Hide Shop Breadcrumbs', 'themify') . '</span>
				<label for="setting-hide_shop_single_breadcrumbs"><input type="checkbox" id="setting-hide_shop_single_breadcrumbs" name="setting-hide_shop_single_breadcrumbs" '.checked( themify_get( 'setting-hide_shop_single_breadcrumbs' ), 'on', false ).' /> ' . __('Check to hide shop breadcrumbs', 'themify') . '</label></p>';

	/**
	 * Product Reviews
	 */
	$output .= '<p><span class="label">' . __('Product reviews', 'themify') . '</span>
				<label for="setting-product_reviews"><input type="checkbox" id="setting-product_reviews" name="setting-product_reviews" '.checked( themify_get( 'setting-product_reviews' ), 'on', false ).' /> ' . __('Disable product reviews', 'themify') . '</label></p>';

	/**
	 * Related Products
	 */
	$output .= '<p><span class="label">' . __('Related products', 'themify') . '</span>
				<label for="setting-related_products"><input type="checkbox" id="setting-related_products" name="setting-related_products" '.checked( themify_get( 'setting-related_products' ), 'on', false ).' /> ' . __('Do not display related products', 'themify') . '</label></p>';

	$related_products_limit = themify_check( 'setting-related_products_limit' ) ? themify_get( 'setting-related_products_limit' ) : 3;
	$output .= '<p><span class="label">' . __('Related Products Limit', 'themify') . '</span>
				<input type="text" name="setting-related_products_limit" value="' . $related_products_limit . '" class="width2" /></p>';

	return $output;
}

/**
 * General Custom Modules
 ***************************************************************************/

	///////////////////////////////////////////
	// Default Page Layout Module - Action
	///////////////////////////////////////////
	function themify_default_page_layout($data=array()){
		$data = themify_get_data();

		$options = array(
			array('value' => 'sidebar1', 'img' => 'images/layout-icons/sidebar1.png', 'selected' => true, 'title' => __('Sidebar Right', 'themify')),
			array('value' => 'sidebar1 sidebar-left', 'img' => 'images/layout-icons/sidebar1-left.png', 'title' => __('Sidebar Left', 'themify')),
			array('value' => 'sidebar-none', 'img' => 'images/layout-icons/sidebar-none.png', 'title' => __('No Sidebar', 'themify'))
		 );

		$default_options = array(
			array('name'=>'','value'=>''),
			array('name'=> __('Yes', 'themify'),'value'=>'yes'),
			array('name'=> __('No', 'themify'),'value'=>'no')
		);

		$val = isset( $data['setting-default_page_layout'] ) ? $data['setting-default_page_layout'] : '';

		/**
		 * HTML for settings panel
		 * @var string
		 */
		$output = '<p>
						<span class="label">' . __('Page Sidebar Option', 'themify') . '</span>';
		foreach ( $options as $option ) {
			if ( ( '' == $val || ! $val || ! isset( $val ) ) && ( isset( $option['selected'] ) && $option['selected'] ) ) {
				$val = $option['value'];
			}
			if ( $val == $option['value'] ) {
				$class = "selected";
			} else {
				$class = "";
			}
			$output .= '<a href="#" class="preview-icon '.$class.'" title="'.$option['title'].'"><img src="'.THEME_URI.'/'.$option['img'].'" alt="'.$option['value'].'"  /></a>';
		}
		$output .= '<input type="hidden" name="setting-default_page_layout" class="val" value="'.$val.'" /></p>';
		$output .= '<p>
						<span class="label">' . __('Hide Title in All Pages', 'themify') . '</span>

						<select name="setting-hide_page_title">';
						foreach ( $default_options as $title_option ) {
							if ( isset( $data['setting-hide_page_title'] ) && ( $title_option['value'] == $data['setting-hide_page_title'] ) ) {
								$output .= '<option selected="selected" value="'.$title_option['value'].'">'.$title_option['name'].'</option>';
							} else {
								$output .= '<option value="'.$title_option['value'].'">'.$title_option['name'].'</option>';
							}
						}


		$output .=	'</select>
					</p>';
		/**
		 * Hide Feauted images in All Pages
		 */
		$output .= '<p>
                    <span class="label">' . __('Hide Featured Image', 'themify') . '</span>
                    <select name="setting-hide_page_image">' .
                        themify_options_module($default_options, 'setting-hide_page_image') . '
                    </select>
                </p>';

        /**
		 * Featured Image dimensions
		 */
		$output .= '<p>
					<span class="label">' . __('Image Size', 'themify') . '</span>
					<input type="text" class="width2" name="setting-page_featured_image_width" value="' . themify_get( 'setting-page_featured_image_width' ) . '" /> ' . __('width', 'themify') . ' <small>(px)</small>
					<input type="text" class="width2 show_if_enabled_img_php" name="setting-page_featured_image_height" value="' . themify_get( 'setting-page_featured_image_height' ) . '" /> <span class="show_if_enabled_img_php">' . __('height', 'themify') . ' <small>(px)</small></span>
					<br /><span class="pushlabel show_if_enabled_img_php"><small>' . __('Enter height = 0 to disable vertical cropping with img.php enabled', 'themify') . '</small></span>
				</p>';

		if ( isset( $data['setting-comments_pages'] ) && $data['setting-comments_pages'] ) {
			$pages_checked = "checked='checked'";
		}
		$output .= '<p><span class="label">' . __('Page Comments', 'themify') . '</span><label for="setting-comments_pages"><input type="checkbox" id="setting-comments_pages" name="setting-comments_pages" '.checked( themify_get( 'setting-comments_pages' ), 'on', false ).' /> ' . __('Disable comments in all Pages', 'themify') . '</label></p>';

		return $output;
	}

	///////////////////////////////////////////
	// Default Index Layout Module - Action
	///////////////////////////////////////////
	function themify_default_layout($data=array()){
		$data = themify_get_data();

		$prefix = 'setting-default_';

		if ( ! isset( $data['setting-default_more_text'] ) || '' == $data['setting-default_more_text'] ) {
			$more_text = "More";
		} else {
			$more_text = $data['setting-default_more_text'];
		}

		$default_options = array(
								array('name'=>'','value'=>''),
								array('name'=> __('Yes', 'themify'),'value'=>'yes'),
								array('name'=> __('No', 'themify'),'value'=>'no')
							);
		$default_layout_options = array(
								array('name'=> __('Full Content', 'themify'),'value'=>'content'),
								array('name'=> __('Excerpt', 'themify'),'value'=>'excerpt'),
								array('name'=> __('None', 'themify'),'value'=>'none')
							);
		$default_post_layout_options = array(
			array('value' => 'list-post', 'img' => 'images/layout-icons/list-post.png', 'title' => __('List Post', 'themify'), "selected" => true),
			array('value' => 'grid4', 'img' => 'images/layout-icons/grid4.png', 'title' => __('Grid 4', 'themify')),
			array('value' => 'grid3', 'img' => 'images/layout-icons/grid3.png', 'title' => __('Grid 3', 'themify')),
			array('value' => 'grid2', 'img' => 'images/layout-icons/grid2.png', 'title' => __('Grid 2', 'themify')),
			array('value' => 'list-large-image', 'img' => 'images/layout-icons/list-large-image.png', 'title' => __('List Large Image', 'themify')),
			array('value' => 'list-thumb-image', 'img' => 'images/layout-icons/list-thumb-image.png', 'title' => __('List Thumb Image', 'themify'))
		);

		$options = array(
			array('value' => 'sidebar1', 'img' => 'images/layout-icons/sidebar1.png', 'title' => __('Sidebar Right', 'themify'), "selected" => true),
			array('value' => 'sidebar1 sidebar-left', 'img' => 'images/layout-icons/sidebar1-left.png', 'title' => __('Sidebar Left', 'themify')),
			array('value' => 'sidebar-none', 'img' => 'images/layout-icons/sidebar-none.png', 'title' => __('No Sidebar', 'themify'))
		);

		$val = isset( $data['setting-default_layout'] ) ? $data['setting-default_layout'] : '';

		/**
		 * HTML for settings panel
		 * @var string
		 */
		$output = '<div class="themify-info-link">' . __( 'Here you can set the <a href="https://themify.me/docs/default-layouts">Default Layouts</a> for WordPress archive post layout (category, search, archive, tag pages, etc.), single post layout (single post page), and the static Page layout. The default single post and page layout can be override individually on the post/page > edit > Themify Custom Panel.', 'themify' ) . '</div>';

		$output .= '<p>
						<span class="label">' . __('Archive Sidebar Option', 'themify') . '</span>';
		foreach ( $options as $option ) {
			if ( ( '' == $val || ! $val || ! isset( $val ) ) && ( isset( $option['selected'] ) && $option['selected'] ) ) {
				$val = $option['value'];
			}
			if ( $val == $option['value'] ) {
				$class = "selected";
			} else {
				$class = "";
			}
			$output .= '<a href="#" class="preview-icon '.$class.'" title="'.$option['title'].'"><img src="'.THEME_URI.'/'.$option['img'].'" alt="'.$option['value'].'"  /></a>';
		}

		$output .= '<input type="hidden" name="setting-default_layout" class="val" value="'.$val.'" />';
		$output .= '</p>';
		$output .= '<p>
						<span class="label">' . __('Post Layout', 'themify') . '</span>';

		$val = isset( $data['setting-default_post_layout'] ) ? $data['setting-default_post_layout'] : '';

		foreach ( $default_post_layout_options as $option ) {
			if ( ( '' == $val || ! $val || ! isset( $val ) ) && ( isset( $option['selected'] ) && $option['selected'] ) ) {
				$val = $option['value'];
			}
			if ( $val == $option['value'] ) {
				$class = "selected";
			} else {
				$class = "";
			}
			$output .= '<a href="#" class="preview-icon '.$class.'" title="'.$option['title'].'"><img src="'.THEME_URI.'/'.$option['img'].'" alt="'.$option['value'].'"  /></a>';
		}

		$output .= '<input type="hidden" name="setting-default_post_layout" class="val" value="'.$val.'" />
					</p>
					<p>
						<span class="label">' . __('Display Content', 'themify') . '</span>
						<select name="setting-default_layout_display">';
						foreach ( $default_layout_options as $layout_option ) {
							if ( isset( $data['setting-default_layout_display'] ) && ( $layout_option['value'] == $data['setting-default_layout_display'] ) ) {
								$output .= '<option selected="selected" value="'.$layout_option['value'].'">'.$layout_option['name'].'</option>';
							} else {
								$output .= '<option value="'.$layout_option['value'].'">'.$layout_option['name'].'</option>';
							}
						}
		$output .=	'	</select></p>';
		
		/**
		 * Excerpt length
		 */
		$output .= '<p style="display:none">
						<span class="pushlabel vertical-grouped">
							<label>
								<input class="width2" type="text" value="' . ( isset( $data[ $prefix . 'excerpt_length' ] ) ? esc_attr( $data[ $prefix . 'excerpt_length' ] ) : '' ) . '" name="' . esc_attr( $prefix ) . 'excerpt_length"> '
								. __( 'Excerpt length (enter number of words)', 'themify' ) . '
							</label>
						</span>
					</p>';

		$output .= '<p>
						<span class="label">' . __('More Text', 'themify') . '</span>
						<input type="text" name="setting-default_more_text" value="'.$more_text.'">
<span class="pushlabel vertical-grouped"><label for="setting-excerpt_more"><input type="checkbox" value="1" id="setting-excerpt_more" name="setting-excerpt_more" '.checked( themify_get( 'setting-excerpt_more' ), 1, false ).'/> ' . __('Display more link button in excerpt mode as well.', 'themify') . '</label></span>
					</p>';

		/**
		 * Order & OrderBy Options
		 */
		if( function_exists( 'themify_post_sorting_options' ) )
			$output .= themify_post_sorting_options('setting-index_order', $data);

		/**
		 * Hide Post Title
		 */
		$output .=	'<p>
						<span class="label">' . __('Hide Post Title', 'themify') . '</span>

						<select name="setting-default_post_title">';
						foreach ( $default_options as $title_option ) {
							if ( isset( $data['setting-default_post_title'] ) && ( $title_option['value'] == $data['setting-default_post_title'] ) ) {
								$output .= '<option selected="selected" value="'.$title_option['value'].'">'.$title_option['name'].'</option>';
							} else {
								$output .= '<option value="'.$title_option['value'].'">'.$title_option['name'].'</option>';
							}
						}
		$output .=	'</select>
					</p>
					<p>
						<span class="label">' . __('Unlink Post Title', 'themify') . '</span>

						<select name="setting-default_unlink_post_title">';
						foreach ( $default_options as $title_option ) {
							if ( isset( $data['setting-default_unlink_post_title'] ) && ( $title_option['value'] == $data['setting-default_unlink_post_title'] ) ) {
								$output .= '<option selected="selected" value="'.$title_option['value'].'">'.$title_option['name'].'</option>';
							} else {
								$output .= '<option value="'.$title_option['value'].'">'.$title_option['name'].'</option>';
							}
						}
		$output .=	'</select>
					</p>
					<p>
						<span class="label">' . __('Hide Post Meta', 'themify') . '</span>

						<select name="setting-default_post_meta">';
						foreach ( $default_options as $title_option ) {
							if ( isset( $data['setting-default_post_meta'] ) && ( $title_option['value'] == $data['setting-default_post_meta'] ) ) {
								$output .= '<option selected="selected" value="'.$title_option['value'].'">'.$title_option['name'].'</option>';
							} else {
								$output .= '<option value="'.$title_option['value'].'">'.$title_option['name'].'</option>';
							}
						}
		$output .=	'</select>
					</p>
					<p>
						<span class="label">' . __('Hide Post Date', 'themify') . '</span>

						<select name="setting-default_post_date">';
						foreach ( $default_options as $title_option ) {
							if ( isset( $data['setting-default_post_date'] ) && ( $title_option['value'] == $data['setting-default_post_date'] ) ) {
								$output .= '<option selected="selected" value="'.$title_option['value'].'">'.$title_option['name'].'</option>';
							} else {
								$output .= '<option value="'.$title_option['value'].'">'.$title_option['name'].'</option>';
							}
						}
		$output .=	'</select>
					</p>
					<p>
						<span class="label">' . __('Auto Featured Image', 'themify') . '</span>

						<label for="setting-auto_featured_image"><input type="checkbox" value="1" id="setting-auto_featured_image" name="setting-auto_featured_image" '.checked( themify_get( 'setting-auto_featured_image' ), 1, false ).'/> ' . __('If no featured image is specified, display first image in content.', 'themify') . '</label>
					</p>

					<p>
						<span class="label">' . __('Hide Featured Image', 'themify') . '</span>

						<select name="setting-default_post_image">';
						foreach ( $default_options as $title_option ) {
							if ( isset( $data['setting-default_post_image'] ) && ( $title_option['value'] == $data['setting-default_post_image'] ) ) {
								$output .= '<option selected="selected" value="'.$title_option['value'].'">'.$title_option['name'].'</option>';
							} else {
								$output .= '<option value="'.$title_option['value'].'">'.$title_option['name'].'</option>';
							}
						}
		$output .=	'</select>
					</p>
					<p>
						<span class="label">' . __('Unlink Featured Image', 'themify') . '</span>

						<select name="setting-default_unlink_post_image">';
						foreach ( $default_options as $title_option ) {
							if ( isset( $data['setting-default_unlink_post_image'] ) && ( $title_option['value'] == $data['setting-default_unlink_post_image'] ) ) {
								$output .= '<option selected="selected" value="'.$title_option['value'].'">'.$title_option['name'].'</option>';
							} else {
								$output .= '<option value="'.$title_option['value'].'">'.$title_option['name'].'</option>';
							}
						}
		$output .=	'</select>
					</p>';

		$output .= themify_feature_image_sizes_select('image_post_feature_size');

		$data = themify_get_data();
		$options = array( 'left', 'right' );

		$output .= '<p>
						<span class="label">' . __('Image Size', 'themify') . '</span>
						<input type="text" class="width2" name="setting-image_post_width" value="' . themify_get( 'setting-image_post_width' ) . '" /> ' . __('width', 'themify') . ' <small>(px)</small>
						<input type="text" class="width2 show_if_enabled_img_php" name="setting-image_post_height" value="' . themify_get( 'setting-image_post_height' ) . '" /> <span class="show_if_enabled_img_php">' . __('height', 'themify') . ' <small>(px)</small></span>
						<br /><span class="pushlabel show_if_enabled_img_php"><small>' . __('Enter height = 0 to disable vertical cropping with img.php enabled', 'themify') . '</small></span>
					</p>';
		return $output;
	}

	///////////////////////////////////////////
	// Default Single Post Layout
	///////////////////////////////////////////
	function themify_default_post_layout($data=array()){

		$data = themify_get_data();

		$default_options = array(
			array('name'=>'','value'=>''),
			array('name'=> __('Yes', 'themify'),'value'=>'yes'),
			array('name'=> __('No', 'themify'),'value'=>'no')
		);

		$val = isset( $data['setting-default_page_post_layout'] ) ? $data['setting-default_page_post_layout'] : '';

		/**
		 * HTML for settings panel
		 * @var string
		 */
		$output = '<p><span class="label">' . __('Post Sidebar Option', 'themify') . '</span>';

		$options = array(
			array('value' => 'sidebar1', 'img' => 'images/layout-icons/sidebar1.png', 'selected' => true, 'title' => __('Sidebar Right', 'themify')),
			array('value' => 'sidebar1 sidebar-left', 'img' => 'images/layout-icons/sidebar1-left.png', 'title' => __('Sidebar Left', 'themify')),
			array('value' => 'sidebar-none', 'img' => 'images/layout-icons/sidebar-none.png', 'title' => __('No Sidebar', 'themify'))
		);

		foreach ( $options as $option ) {
			if ( ( '' == $val || ! $val || ! isset( $val ) ) && ( isset( $option['selected'] ) && $option['selected'] ) ) {
				$val = $option['value'];
			}
			if ( $val == $option['value'] ) {
				$class = "selected";
			} else {
				$class = "";
			}
			$output .= '<a href="#" class="preview-icon '.$class.'" title="'.$option['title'].'"><img src="'.THEME_URI.'/'.$option['img'].'" alt="'.$option['value'].'"  /></a>';
		}

		$output .= '<input type="hidden" name="setting-default_page_post_layout" class="val" value="'.$val.'" />';
		$output .= '</p>
					<p>
						<span class="label">' . __('Hide Post Title', 'themify') . '</span>

						<select name="setting-default_page_post_title">';
						foreach ( $default_options as $title_option ) {
							if ( isset( $data['setting-default_page_post_title'] ) && ( $title_option['value'] == $data['setting-default_page_post_title'] ) ) {
								$output .= '<option selected="selected" value="'.$title_option['value'].'">'.$title_option['name'].'</option>';
							} else {
								$output .= '<option value="'.$title_option['value'].'">'.$title_option['name'].'</option>';
							}
						}
		$output .=	'</select>
					</p>
					<p>
						<span class="label">' . __('Unlink Post Title', 'themify') . '</span>

						<select name="setting-default_page_unlink_post_title">';
						foreach ( $default_options as $title_option ) {
							if ( isset( $data['setting-default_page_unlink_post_title'] ) && ( $title_option['value'] == $data['setting-default_page_unlink_post_title'] ) ) {
								$output .= '<option selected="selected" value="'.$title_option['value'].'">'.$title_option['name'].'</option>';
							} else {
								$output .= '<option value="'.$title_option['value'].'">'.$title_option['name'].'</option>';
							}
						}
		$output .=	'</select>
					</p>
					<p>
						<span class="label">' . __('Hide Post Meta', 'themify') . '</span>

						<select name="setting-default_page_post_meta">';
						foreach ( $default_options as $title_option ) {
							if ( isset( $data['setting-default_page_post_meta'] ) && ( $title_option['value'] == $data['setting-default_page_post_meta'] ) ) {
								$output .= '<option selected="selected" value="'.$title_option['value'].'">'.$title_option['name'].'</option>';
							} else {
								$output .= '<option value="'.$title_option['value'].'">'.$title_option['name'].'</option>';
							}
						}
		$output .=	'</select>
					</p>
					<p>
						<span class="label">' . __('Hide Post Date', 'themify') . '</span>

						<select name="setting-default_page_post_date">';
						foreach ( $default_options as $title_option ) {
							if ( isset( $data['setting-default_page_post_date'] ) && ( $title_option['value'] == $data['setting-default_page_post_date'] ) ) {
								$output .= '<option selected="selected" value="'.$title_option['value'].'">'.$title_option['name'].'</option>';
							} else {
								$output .= '<option value="'.$title_option['value'].'">'.$title_option['name'].'</option>';
							}
						}
		$output .=	'</select>
					</p>
					<p>
						<span class="label">' . __('Hide Featured Image', 'themify') . '</span>

						<select name="setting-default_page_post_image">';
						foreach ( $default_options as $title_option ) {
							if ( isset( $data['setting-default_page_post_image'] ) && ( $title_option['value'] == $data['setting-default_page_post_image'] ) ) {
								$output .= '<option selected="selected" value="'.$title_option['value'].'">'.$title_option['name'].'</option>';
							} else {
								$output .= '<option value="'.$title_option['value'].'">'.$title_option['name'].'</option>';
							}
						}
		$output .=	'</select>
					</p><p>
						<span class="label">' . __('Unlink Featured Image', 'themify') . '</span>

						<select name="setting-default_page_unlink_post_image">';
						foreach ( $default_options as $title_option ) {
							if ( isset( $data['setting-default_page_unlink_post_image'] ) && ( $title_option['value'] == $data['setting-default_page_unlink_post_image'] ) ) {
								$output .= '<option selected="selected" value="'.$title_option['value'].'">'.$title_option['name'].'</option>';
							} else {
								$output .= '<option value="'.$title_option['value'].'">'.$title_option['name'].'</option>';
							}
						}
		$output .= '</select></p>';
	    $output .= themify_feature_image_sizes_select('image_post_single_feature_size');
	    $output .= '<p>
						<span class="label">' . __('Image Size', 'themify') . '</span>
						<input type="text" class="width2" name="setting-image_post_single_width" value="' . themify_get( 'setting-image_post_single_width' ) . '" /> width <small>(px)</small>
						<input type="text" class="width2" name="setting-image_post_single_height" value="' . themify_get( 'setting-image_post_single_height' ) . '" /> height <small>(px)</small>
						<br /><span class="pushlabel show_if_enabled_img_php"><small>' . __('Enter height = 0 to disable vertical cropping with img.php enabled', 'themify') . '</small></span>
					</p>';
		if ( themify_check( 'setting-comments_posts' ) ) {
			$comments_posts_checked = "checked='checked'";
		}
		$output .= '<p><span class="label">' . __('Post Comments', 'themify') . '</span><label for="setting-comments_posts"><input type="checkbox" id="setting-comments_posts" name="setting-comments_posts" '.checked( themify_get( 'setting-comments_posts' ), 'on', false ).' /> ' . __('Disable comments in all Posts', 'themify') . '</label></p>';

		if ( themify_check( 'setting-post_author_box' ) ) {
			$author_box_checked = "checked='checked'";
		}
		$output .= '<p><span class="label">' . __('Show Author Box', 'themify') . '</span><label for="setting-post_author_box"><input type="checkbox" id="setting-post_author_box" name="setting-post_author_box" '.checked( themify_get( 'setting-post_author_box' ), 'on', false ).' /> ' . __('Show author box in all Posts', 'themify') . '</label></p>';

		// Post Navigation
		$pre = 'setting-post_nav_';
		$output .= '
		<p>
			<span class="label">' . __('Post Navigation', 'themify') . '</span>
			<label for="'.$pre.'disable">
				<input type="checkbox" id="'.$pre.'disable" name="'.$pre.'disable" '. checked( themify_get( $pre.'disable' ), 'on', false ) .'/> ' . __('Remove Post Navigation', 'themify') . '
				</label>
		<span class="pushlabel vertical-grouped">
				<label for="'.$pre.'same_cat">
					<input type="checkbox" id="'.$pre.'same_cat" name="'.$pre.'same_cat" '. checked( themify_get( $pre.'same_cat' ), 'on', false ) .'/> ' . __('Show only posts in the same category', 'themify') . '
				</label>
			</span>
		</p>';

		return $output;
	}
?>
