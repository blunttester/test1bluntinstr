<?php


/*
To add custom PHP functions to the theme, create a child theme (https://themify.me/docs/child-theme) and add it to the child theme functions.php file. 
They will be added to the theme automatically.
*/

/* 	Enqueue (and dequeue) Stylesheets and Scripts
/***************************************************************************/

function themify_theme_enqueue_scripts(){
	global $wp_query;

	// Get theme version for Themify theme scripts and styles
	$theme_version = wp_get_theme()->display( 'Version' );

	//jScrollPane stylesheet
	wp_enqueue_style( 'jscrollpane', themify_enque(THEME_URI . '/jquery.jscrollpane.css'));

	//Google Web Fonts
	wp_enqueue_style( 'google-fonts', themify_https_esc('http://fonts.googleapis.com/css'). '?family=Oswald&subset=latin,latin-ext');

	//Themify base stylesheet
	wp_enqueue_style( 'theme-style', themify_enque( THEME_URI . '/style.css' ), array(), $theme_version );

	if( themify_woocommerce_active() ) {
		//Themify shop stylesheet
		wp_enqueue_style( 'themify-shop', themify_enque(THEME_URI . '/shop.css'));
	}

	//Themify Media Queries stylesheet
	wp_enqueue_style( 'themify-media-queries', themify_enque(THEME_URI . '/media-queries.css'));

	// Themify child base styling
	if( is_child_theme() ) {
		wp_enqueue_style( 'theme-style-child', themify_enque( get_stylesheet_uri() ), array(), $theme_version );
	}

	///////////////////
	//Enqueue scripts
	///////////////////

	//isotope, used to re-arrange blocks
	if ( themify_woocommerce_active() && is_woocommerce() && !themify_get( 'setting-shop_masonry_disabled' )  ) {
		wp_enqueue_script( 'themify-isotope', THEME_URI . '/js/jquery.isotope.min.js', array('imagesloaded'), false, true );
	}

	//creates infinite scroll
	wp_enqueue_script( 'infinitescroll', THEME_URI . '/js/jquery.infinitescroll.min.js', array('imagesloaded'), false, true );

	//Slider script
	wp_enqueue_script( 'jquery-slider', THEME_URI . '/js/jquery.slider.min.js', array('jquery'), false, true );

	
	//Mouse wheel support for jScrollPane
	wp_enqueue_script( 'mousewheel', THEME_URI . '/js/jquery.mousewheel.min.js', array('jquery'), false, true );

	//jScrollPane
	wp_enqueue_script( 'jscrollpane', THEME_URI . '/js/jquery.jscrollpane.min.js', array('jquery'), false, true );

	//Themify internal script
	wp_enqueue_script( 'theme-script',	themify_enque(THEME_URI . '/js/themify.script.js'), array('jquery', 'jquery-effects-core'), false, true );


	// Get auto infinite scroll setting
	$autoinfinite = '';
	if ( ! themify_get( 'setting-autoinfinite' ) ) {
		$autoinfinite = 'auto';
	}

	//Inject variable values in gallery script
	wp_localize_script( 'theme-script', 'themifyScript', array(
		'lightbox' => themify_lightbox_vars_init(),
		'lightboxContext' => apply_filters( 'themify_lightbox_context', '#pagewrap' ),
		'variableLightbox' => themify_check( 'setting-variable_lightbox' ) ? '' : 'variable-lightbox',
		'loadingImg'   => THEME_URI . '/images/loading.gif',
		'maxPages'	   => $wp_query->max_num_pages,
		'currentPage' => get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1,
		'autoInfinite' => $autoinfinite,
		'infiniteURL' => themify_check( 'setting-infinite-url' ) ? 1 : 0,
		'fixedHeader'  => themify_check( 'setting-fixed_header_disabled' )? '': 'fixed-header',
	));

	if( themify_woocommerce_active() ) {
		//Themify shop script
		wp_enqueue_script( 'theme-shop',	themify_enque(THEME_URI . '/js/themify.shop.js'), array('jquery'), false, true );

		// Get carousel variables
		$carou_visible = themify_get('setting-product_slider_visible');
		$carou_autoplay = themify_get('setting-product_slider_auto');
		$carou_speed = themify_get('setting-product_slider_speed');
		$carou_scroll = themify_get('setting-product_slider_scroll');
		$carou_wrap = themify_get('setting-product_slider_wrap');
		//Inject variable values in themify.shop.js
		wp_localize_script( 'theme-shop', 'themifyShop', array(
				'visible'	=> $carou_visible? $carou_visible : '4',
				'autoplay'	=> $carou_autoplay? $carou_autoplay : 0,
				'speed'	=> $carou_speed? $carou_speed : 300,
				'scroll'	=> $carou_scroll? $carou_scroll : 1,
				'wrap'	=> ('' == $carou_wrap || 'yes' == $carou_wrap)? 'circular' : null,
				'redirect'=> get_option( 'woocommerce_cart_redirect_after_add' ) === 'yes'?wc_get_cart_url():false,
                                'wc_variation_url'=> str_replace( array( 'http:', 'https:' ), '', WC()->plugin_url() ) . '/assets/js/frontend/add-to-cart-variation.min.js',
                                'wc_version'=>WC()->version,
                                'variations_text'=>array(
                                         'i18n_no_matching_variations_text' => esc_attr__( 'Sorry, no products matched your selection. Please choose a different combination.', 'woocommerce' ),
                                         'i18n_make_a_selection_text'       => esc_attr__( 'Please select some product options before adding this product to your cart.', 'woocommerce' ),
                                         'i18n_unavailable_text'            => esc_attr__( 'Sorry, this product is unavailable. Please choose a different combination.', 'woocommerce' ),
                                        )
			)
		);
	}

	//WordPress thread comment reply script
	if ( is_single() || is_page() ) wp_enqueue_script( 'comment-reply' );
}


function themify_add_minify_vars($vars){
    $vars['minify']['css']['lightbox'] = themify_enque(THEMIFY_URI.'/css/lightbox.css',true);
    return $vars;
}

add_action( 'wp_enqueue_scripts', 'themify_theme_enqueue_scripts', 11 );
add_filter('themify_main_script_vars','themify_add_minify_vars',10,1);
/**
 * Add viewport tag for responsive layouts
 * @package themify
 */
function themify_viewport_tag(){
	echo "\n".'<meta name="viewport" content="width=device-width, initial-scale=1">'."\n";
}
add_action( 'wp_head', 'themify_viewport_tag' );

/* Custom Write Panels
 ***************************************************************************/

///////////////////////////////////////
// Setup Write Panel Options
///////////////////////////////////////

// Post Meta Box Options
$post_meta_box_options = array(
	// Layout
	array(
		"name" 		=> "layout",
		"title" 		=> __('Sidebar Option', 'themify'),
		"description" => "",
		"type" 		=> "layout",
		'show_title' => true,
		"meta"		=> array(
			array("value" => "default", "img" => "images/layout-icons/default.png", "selected" => true, 'title' => __('Default', 'themify')),
			array('value' => 'sidebar1', 'img' => 'images/layout-icons/sidebar1.png', 'title' => __('Sidebar Right', 'themify')),
			array('value' => 'sidebar1 sidebar-left', 'img' => 'images/layout-icons/sidebar1-left.png', 'title' => __('Sidebar Left', 'themify')),
			array('value' => 'sidebar-none', 'img' => 'images/layout-icons/sidebar-none.png', 'title' => __('No Sidebar ', 'themify'))
		)
	),
		// Content Width
		array(
			'name'=> 'content_width',
			'title' => __('Content Width', 'themify'),
			'description' => '',
			'type' => 'layout',
			'show_title' => true,
			'meta' => array(
				array(
					'value' => 'default_width',
					'img' => 'themify/img/default.png',
					'selected' => true,
					'title' => __( 'Default', 'themify' )
				),
				array(
					'value' => 'full_width',
					'img' => 'themify/img/fullwidth.png',
					'title' => __( 'Fullwidth', 'themify' )
				)
			)
		),
	// Post Image
	array(
		"name" 		=> "post_image",
		"title" 		=> __('Featured Image', 'themify'),
		"description" => '',
		"type" 		=> "image",
		"meta"		=> array()
	),
	// Featured Image Size
	array(
		'name'	=>	'feature_size',
		'title'	=>	__('Image Size', 'themify'),
		'description' => __('Image sizes can be set at <a href="options-media.php">Media Settings</a> and <a href="https://wordpress.org/plugins/regenerate-thumbnails/" target="_blank">Regenerated</a>', 'themify'),
		'type'		 =>	'featimgdropdown',
		'display_callback' => 'themify_is_image_script_disabled'
	),
	// Image Width
	array(
		"name" 		=> "image_width",
		"title" 		=> __('Image Width', 'themify'),
		"description" => "",
		"type" 		=> "textbox",
		"meta"		=> array("size"=>"small")
	),
	// Image Height
	array(
		"name" 		=> "image_height",
		"title" 		=> __('Image Height', 'themify'),
		"description" => __('Enter height = 0 to disable vertical cropping with img.php enabled', 'themify'),
		"type" 		=> "textbox",
		"meta"		=> array("size"=>"small")
	),
	// Hide Post Title
	array(
		"name" 		=> "hide_post_title",
		"title" 		=> __('Hide Post Title', 'themify'),
		"description" => "",
		"type" 		=> "dropdown",
		"meta"		=> array(
			array("value" => "default", "name" => "", "selected" => true),
			array("value" => "yes", "name" => "Yes"),
			array("value" => "no",	"name" => "No")
		)
	),
	// Unlink Post Title
	array(
		"name" 		=> "unlink_post_title",
		"title" 		=> __('Unlink Post Title', 'themify'),
		"description" => __('Unlink post title (it will display the post title without link)', 'themify'),
		"type" 		=> "dropdown",
		"meta"		=> array(
			array("value" => "default", "name" => "", "selected" => true),
			array("value" => "yes", "name" => "Yes"),
			array("value" => "no",	"name" => "No")
		)
	),
	// Hide Post Meta
	array(
		"name" 		=> "hide_post_meta",
		"title" 		=> __('Hide Post Meta', 'themify'),
		"description" => "",
		"type" 		=> "dropdown",
		"meta"		=> array(
			array("value" => "default", "name" => "", "selected" => true),
			array("value" => "yes", "name" => "Yes"),
			array("value" => "no",	"name" => "No")
		)
	),
	// Hide Post Date
	array(
		"name" 		=> "hide_post_date",
		"title" 		=> __('Hide Post Date', 'themify'),
		"description" => "",
		"type" 		=> "dropdown",
		"meta"		=> array(
			array("value" => "default", "name" => "", "selected" => true),
			array("value" => "yes", "name" => "Yes"),
			array("value" => "no",	"name" => "No")
		)
	),
	// Hide Post Image
	array(
		"name" 		=> "hide_post_image",
		"title" 		=> __('Hide Featured Image', 'themify'),
		"description" => "",
		"type" 		=> "dropdown",
		"meta"		=> array(
			array("value" => "default", "name" => "", "selected" => true),
			array("value" => "yes", "name" => "Yes"),
			array("value" => "no",	"name" => "No")
		)
	),
	// Unlink Post Image
	array(
		"name" 		=> "unlink_post_image",
		"title" 		=> __('Unlink Featured Image', 'themify'),
		"description" => __('Display the Featured Image without link)', 'themify'),
		"type" 		=> "dropdown",
		"meta"		=> array(
			array("value" => "default", "name" => "", "selected" => true),
			array("value" => "yes", "name" => "Yes"),
			array("value" => "no",	"name" => "No")
		)
	),
	// Video URL
	array(
		'name' 		=> 'video_url',
		'title' 		=> __('Video URL', 'themify'),
		'description' => __('Video embed URL such as YouTube or Vimeo video url (<a href="https://themify.me/docs/video-embeds">details</a>).', 'themify'),
		'type' 		=> 'textbox',
		'meta'		=> array()
	),
	// External Link
	array(
		"name" 		=> "external_link",
		"title" 		=> __('External Link', 'themify'),
		"description" => __('Link Featured Image and Post Title to external URL', 'themify'),
		"type" 		=> "textbox",
		"meta"		=> array()
	),
	// Lightbox Link + Zoom icon
	themify_lightbox_link_field()
);


// Page Meta Box Options
$page_meta_box_options = array(
	// Page Layout
	array(
		"name" 		=> "page_layout",
		"title"		=> __('Sidebar Option', 'themify'),
		"description"	=> "",
		"type"		=> "layout",
		'show_title' => true,
		"meta"		=> array(
			array("value" => "default", "img" => "images/layout-icons/default.png", "selected" => true, 'title' => __('Default', 'themify')),
			array('value' => 'sidebar1', 'img' => 'images/layout-icons/sidebar1.png', 'title' => __('Sidebar Right', 'themify')),
			array('value' => 'sidebar1 sidebar-left', 'img' => 'images/layout-icons/sidebar1-left.png', 'title' => __('Sidebar Left', 'themify')),
			array('value' => 'sidebar-none', 'img' => 'images/layout-icons/sidebar-none.png', 'title' => __('No Sidebar ', 'themify'))
		)
	),
	// Content Width
		array(
			'name'=> 'content_width',
			'title' => __('Content Width', 'themify'),
			'description' => 'Select "Fullwidth" if the page is to be built with the Builder without the sidebar (it will make the Builder content fullwidth).',
			'type' => 'layout',
			'show_title' => true,
			'meta' => array(
				array(
					'value' => 'default_width',
					'img' => 'themify/img/default.png',
					'selected' => true,
					'title' => __( 'Default', 'themify' )
				),
				array(
					'value' => 'full_width',
					'img' => 'themify/img/fullwidth.png',
					'title' => __( 'Fullwidth', 'themify' )
				)
			)
		),
		// Hide page title
	array(
		"name" 		=> "hide_page_title",
		"title"		=> __('Hide Page Title', 'themify'),
		"description"	=> "",
		"type" 		=> "dropdown",
		"meta"		=> array(
			array("value" => "default", "name" => "", "selected" => true),
			array("value" => "yes", "name" => "Yes"),
			array("value" => "no",	"name" => "No")
		)
	),
		// Custom menu for page
        array(
            'name' 		=> 'custom_menu',
            'title'		=> __( 'Custom Menu', 'themify' ),
            'description'	=> '',
            'type'		=> 'dropdown',
            'meta'		=> themify_get_available_menus(),
        ),
);

// Query Post Meta Box Options
$query_post_meta_box_options = array(
	// Notice
	array(
		'name' => '_query_posts_notice',
		'title' => '',
		'description' => '',
		'type' => 'separator',
		'meta' => array(
			'html' => '<div class="themify-info-link">' . sprintf( __( '<a href="%s">Query Posts</a> allows you to query WordPress posts from any category on the page. To use it, select a Query Category.', 'themify' ), 'https://themify.me/docs/query-posts' ) . '</div>'
		),
	),
	// Query Category
	array(
		"name" 		=> "query_category",
		"title"		=> __('Query Category', 'themify'),
		"description"	=> __('Select a category or enter multiple category IDs (eg. 2,5,6). Enter 0 to display all category.', 'themify'),
		"type"		=> "query_category",
		"meta"		=> array()
	),
	// Query All Post Types
	array(
		'name' => 'query_all_post_types',
		'type' => 'dropdown',
		'title' => __( 'Query All Post Types', 'themify'),
		'meta' =>array(
			array(
			'value' => '',
			'name' => '',
			),
			array(
			'value' => 'yes',
			'name' => 'Yes',
			),
			array(
			'value' => 'no',
			'name' => 'No',
			),
		)
	),
	// Descending or Ascending Order for Posts
	array(
		'name' 		=> 'order',
		'title'		=> __('Order', 'themify'),
		'description'	=> '',
		'type'		=> 'dropdown',
		'meta'		=> array(
			array('name' => __('Descending', 'themify'), 'value' => 'desc', 'selected' => true),
			array('name' => __('Ascending', 'themify'), 'value' => 'asc')
		)
	),
	// Criteria to Order By
	array(
		'name' 		=> 'orderby',
		'title'		=> __('Order By', 'themify'),
		'description'	=> '',
		'type'		=> 'dropdown',
		'meta'		=> array(
			array('name' => __('Date', 'themify'), 'value' => 'date', 'selected' => true),
			array('name' => __('Random', 'themify'), 'value' => 'rand'),
			array('name' => __('Author', 'themify'), 'value' => 'author'),
			array('name' => __('Post Title', 'themify'), 'value' => 'title'),
			array('name' => __('Comments Number', 'themify'), 'value' => 'comment_count'),
			array('name' => __('Modified Date', 'themify'), 'value' => 'modified'),
			array('name' => __('Post Slug', 'themify'), 'value' => 'name'),
			array('name' => __('Post ID', 'themify'), 'value' => 'ID'),
			array('name' => __( 'Custom Field String', 'themify' ), 'value' => 'meta_value'),
			array('name' => __( 'Custom Field Numeric', 'themify' ), 'value' => 'meta_value_num')
		),
		'hide' => 'date|rand|author|title|comment_count|modified|name|ID field-meta-key'
	),
	array(
		'name'			=> 'meta_key',
		'title'			=> __( 'Custom Field Key', 'themify' ),
		'description'	=> '',
		'type'			=> 'textbox',
		'meta'			=> array('size' => 'medium'),
		'class'			=> 'field-meta-key'
	),
	// Post Layout
	array(
		"name" 		=> "layout",
		"title"		=> __('Query Post Layout', 'themify'),
		"description"	=> "",
		"type"		=> "layout",
		'show_title' => true,
		"meta"		=> array(
			array('value' => 'list-post', 'img' => 'images/layout-icons/list-post.png', 'selected' => true, 'title' => __('List Post', 'themify')),
			array('value' => 'grid4', 'img' => 'images/layout-icons/grid4.png', 'title' => __('Grid 4', 'themify')),
			array('value' => 'grid3', 'img' => 'images/layout-icons/grid3.png', 'title' => __('Grid 3', 'themify')),
			array('value' => 'grid2', 'img' => 'images/layout-icons/grid2.png', 'title' => __('Grid 2', 'themify')),
			array('value' => 'list-large-image', 'img' => 'images/layout-icons/list-large-image.png', 'title' => __('List Large Image', 'themify')),
			array("value" => "list-thumb-image", "img" => "images/layout-icons/list-thumb-image.png")
		)
	),
	// Posts Per Page
	array(
		"name" 		=> "posts_per_page",
		"title"		=> __('Posts per page', 'themify'),
		"description"	=> "",
		"type"		=> "textbox",
		"meta"		=> array("size" => "small")
	),

	// Display Content
	array(
		"name" 		=> "display_content",
		"title"		=> __('Display Content', 'themify'),
		"description"	=> "",
		"type"		=> "dropdown",
			'meta'		=> array(
				array( 'name' => __('Full Content', 'themify'), 'value' => 'content' ),
				array( 'name' => __('Excerpt', 'themify'), 'value' => 'excerpt', 'selected' => true ),
				array( 'name' => __('None', 'themify'), 'value' => 'none' )
			),
			'default' => 'excerpt',
	),
	// Featured Image Size
	array(
		'name'	=>	'feature_size_page',
		'title'	=>	__('Image Size', 'themify'),
		'description' => __('Image sizes can be set at <a href="options-media.php">Media Settings</a> and <a href="https://wordpress.org/plugins/regenerate-thumbnails/" target="_blank">Regenerated</a>', 'themify'),
		'type'		 =>	'featimgdropdown',
		'display_callback' => 'themify_is_image_script_disabled'
	),
	// Image Width
	array(
		"name" 		=> "image_width",
		"title" 		=> __('Image Width', 'themify'),
		"description" => "",
		"type" 		=> "textbox",
		"meta"		=> array("size"=>"small")
	),
	// Image Height
	array(
		"name" 		=> "image_height",
		"title" 		=> __('Image Height', 'themify'),
		"description" => __('Enter height = 0 to disable vertical cropping with img.php enabled', 'themify'),
		"type" 		=> "textbox",
		"meta"		=> array("size"=>"small")
	),
	// Hide Title
	array(
		"name" 		=> "hide_title",
		"title"		=> __('Hide Post Title', 'themify'),
		"description"	=> "",
		"type" 		=> "dropdown",
		"meta"		=> array(
			array("value" => "default", "name" => "", "selected" => true),
			array("value" => "yes", "name" => "Yes"),
			array("value" => "no",	"name" => "No")
		)
	),
	// Unlink Post Title
	array(
		"name" 		=> "unlink_title",
		"title" 		=> __('Unlink Post Title', 'themify'),
		"description" => __('Unlink post title (it will display the post title without link)', 'themify'),
		"type" 		=> "dropdown",
		"meta"		=> array(
			array("value" => "default", "name" => "", "selected" => true),
			array("value" => "yes", "name" => "Yes"),
			array("value" => "no",	"name" => "No")
		)
	),
	// Hide Post Date
	array(
		"name" 		=> "hide_date",
		"title"		=> __('Hide Post Date', 'themify'),
		"description"	=> "",
		"type" 		=> "dropdown",
		"meta"		=> array(
			array("value" => "default", "name" => "", "selected" => true),
			array("value" => "yes", "name" => "Yes"),
			array("value" => "no",	"name" => "No")
		)
	),
	// Hide Post Meta
	array(
		"name" 		=> "hide_meta",
		"title"		=> __('Hide Post Meta', 'themify'),
		"description"	=> "",
		"type" 		=> "dropdown",
		"meta"		=> array(
			array("value" => "default", "name" => "", "selected" => true),
			array("value" => "yes", "name" => "Yes"),
			array("value" => "no",	"name" => "No")
		)
	),
	// Hide Post Image
	array(
		"name" 		=> "hide_image",
		"title" 		=> __('Hide Featured Image', 'themify'),
		"description" => "",
		"type" 		=> "dropdown",
		"meta"		=> array(
			array("value" => "default", "name" => "", "selected" => true),
			array("value" => "yes", "name" => "Yes"),
			array("value" => "no",	"name" => "No")
		)
	),
	// Unlink Post Image
	array(
		"name" 		=> "unlink_image",
		"title" 		=> __('Unlink Featured Image', 'themify'),
		"description" => __('Display the Featured Image without link)', 'themify'),
		"type" 		=> "dropdown",
		"meta"		=> array(
			array("value" => "default", "name" => "", "selected" => true),
			array("value" => "yes", "name" => "Yes"),
			array("value" => "no",	"name" => "No")
		)
	),
	// Page Navigation Visibility
	array(
		"name" 		=> "hide_navigation",
		"title"		=> __('Hide Page Navigation', 'themify'),
		"description"	=> "",
		"type" 		=> "dropdown",
		"meta"		=> array(
			array("value" => "default", "name" => "", "selected" => true),
			array("value" => "yes", "name" => "Yes"),
			array("value" => "no",	"name" => "No")
		)
	)
);

// Slider Meta Box Options
$slider_meta_box_options = array(
	// Post Layout
	array(
		"name" 		=> "layout",
		"title"		=> __('Slide Layout', 'themify'),
		"description"	=> "",
		"type"		=> "layout",
		'show_title' => true,
		"meta"		=> array(
			array('value' => 'slider-default', 'img' => 'images/layout-icons/slider-default.png', 'selected' => true, 'title' => __('Default', 'themify')),
			array('value' => 'slider-image-only', 'img' => 'images/layout-icons/slider-image-only.png', 'title' => __('Image Only', 'themify')),
			array('value' => 'slider-content-only', 'img' => 'images/layout-icons/slider-content-only.png', 'title' => __('Content Only', 'themify')),
			array('value' => 'slider-image-caption', 'img' => 'images/layout-icons/slider-image-caption.png', 'title' => __('Image Caption', 'themify'))
		)
	),
	// Feature Image
	array(
		"name" 		=> "feature_image",
		"title" 		=> __('Featured Image', 'themify'), //slider image
		"description" => "",
		"type" 		=> "image",
		"meta"		=> array()
	),
	// Featured Image Size
	array(
		'name'	=>	'feature_size',
		'title'	=>	__('Image Size', 'themify'),
		'description' => __('Image sizes can be set at <a href="options-media.php">Media Settings</a> and <a href="https://wordpress.org/plugins/regenerate-thumbnails/" target="_blank">Regenerated</a>', 'themify'),
		'type'		 =>	'featimgdropdown',
		'display_callback' => 'themify_is_image_script_disabled'
	),
	// Image Width
	array(
		"name" 		=> "image_width",
		"title" 		=> __('Image Width', 'themify'),
		"description" => "",
		"type" 		=> "textbox",
		"meta"		=> array("size"=>"small")
	),
	// Image Height
	array(
		"name" 		=> "image_height",
		"title" 		=> __('Image Height', 'themify'),
		"description" => "",
		"type" 		=> "textbox",
		"meta"		=> array("size"=>"small")
	),
	// Image Link
	array(
		"name" 		=> "image_link",
		"title" 		=> __('Image Link', 'themify'),
		"description" => "",
		"type" 		=> "textbox",
		"meta"		=> array()
	),
	// External Link
	array(
		"name" 		=> "external_link",
		"title" 		=> __('External Link', 'themify'),
		"description" => __('Link Featured Image and Post Title to external URL', 'themify'),
		"type" 		=> "textbox",
		"meta"		=> array()
	),
	// Lightbox Link + Zoom icon
	themify_lightbox_link_field()
);


///////////////////////////////////////
// Build Write Panels
///////////////////////////////////////
themify_build_write_panels(array(
		array(
			"name"		=> __('Post Options', 'themify'), // Name displayed in box
			'id' => 'post-options',
			"options"	=> $post_meta_box_options, 	// Field options
			"pages"	=> "post"					// Pages to show write panel
		),
		array(
			"name"		=> __('Page Options', 'themify'),
			'id' => 'page-options',
			"options"	=> $page_meta_box_options,
			"pages"	=> "page"
		),
		array(
			"name"		=> __('Query Posts', 'themify'),
			'id' => 'query-posts',
			"options"	=> $query_post_meta_box_options,
			"pages"	=> "page"
		),
		array(
			"name"		=> __('Homepage Slider Options', 'themify'),
			'id' => 'slider-options',
			"options"	=> $slider_meta_box_options,
			"pages"	=> "slider"
		)
	)
);


/* 	Custom Functions
/***************************************************************************/

///////////////////////////////////////
// Enable WordPress feature image
///////////////////////////////////////
add_theme_support( 'post-thumbnails' );

///////////////////////////////////////
// Register Custom Menu Function
///////////////////////////////////////
function themify_register_custom_nav() {
	if (function_exists('register_nav_menus')) {
		register_nav_menus( array(
			'main-nav' => __( 'Main Navigation', 'themify' )
		) );
	}
}

// Register Custom Menu Function - Action
add_action('init', 'themify_register_custom_nav');

///////////////////////////////////////
// Default Main Nav Function
///////////////////////////////////////
function themify_default_main_nav() {
	echo '<ul id="main-nav" class="main-nav clearfix">';
	wp_list_pages('title_li=');
	echo '</ul>';
}

///////////////////////////////////////
// Register Sidebars
///////////////////////////////////////
if ( function_exists('register_sidebar') ) {
	register_sidebar(array(
		'name' => __('Sidebar', 'themify'),
		'id' => 'sidebar-main',
		'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget' => '</div>',
		'before_title' => '<h4 class="widgettitle">',
		'after_title' => '</h4>',
	));
	register_sidebar(array(
		'name' => __('Social Widget', 'themify'),
		'id' => 'social-widget',
		'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget' => '</div>',
		'before_title' => '<strong class="widgettitle">',
		'after_title' => '</strong>',
	));
}

///////////////////////////////////////
// Footer Sidebars
///////////////////////////////////////
themify_register_grouped_widgets();

// Exclude CPT for sidebar
add_filter( 'themify_exclude_CPT_for_sidebar', 'themify_CPT_exclude_sidebar' );

if( ! function_exists('themify_CPT_exclude_sidebar') ) {
	/**
	 * Exclude Custom Post Types
	 */
	function themify_CPT_exclude_sidebar($CPT = array()) {
		
		$pinshop = array();
		
		if(empty($CPT)){
			$CPT = array('post', 'page', 'attachment', 'tbuilder_layout', 'tbuilder_layout_part', 'section');
		}
		
		if(themify_woocommerce_active()){
			$pinshop[] = 'product';
		}

		$CPT = array_merge($CPT, $pinshop);

		return $CPT;
	}
}

if( ! function_exists('themify_theme_comment') ) {
	/**
	 * Custom Theme Comment
	 * @param object $comment Current comment.
	 * @param array $args Parameters for comment reply link.
	 * @param int $depth Maximum comment nesting depth.
	 * @since 1.0.0
	 */
	function themify_theme_comment($comment, $args, $depth) {
		$GLOBALS['comment'] = $comment;
		?>

	<li id="comment-<?php comment_ID() ?>">
		<p class="comment-author"> <?php echo get_avatar($comment,$size='48'); ?> <?php printf( '<cite>%s</cite>', get_comment_author_link()) ?><br />
			<small class="comment-time"><strong>
					<?php comment_date( apply_filters( 'themify_comment_date', '' ) ); ?>
				</strong> @
				<?php comment_time( apply_filters( 'themify_comment_time', '' ) ); ?>
				<?php edit_comment_link(__('Edit', 'themify'),' [',']') ?>
			</small> </p>
		<div class="commententry">
			<?php if ($comment->comment_approved == '0') : ?>
				<p><em>
						<?php _e('Your comment is awaiting moderation.', 'themify') ?>
					</em></p>
			<?php endif; ?>
			<?php comment_text() ?>
		</div>
		<p class="reply">
			<?php comment_reply_link(array_merge( $args, array('add_below' => 'comment', 'depth' => $depth, 'reply_text' => __( 'Reply', 'themify' ), 'max_depth' => $args['max_depth']))) ?>
		</p>
	<?php
	}
}

/**
 * Displays a link to edit the entry
 */
function themify_edit_link() {
	edit_post_link(__('Edit', 'themify'), '<span class="edit-button">[', ']</span>');
}

/**
 * Alters condition to filter layout class
 * @param bool
 * @return bool
 */
function themify_theme_default_layout_condition($condition){
	return $condition || themify_is_function('is_shop') || themify_is_function('is_product') || themify_is_function('is_product_category') || themify_is_function('is_product_tag');
}
/**
 * Returns default shop layout
 * @param String $class
 * @return @String
 */
function themify_theme_default_layout($class) {
	if( themify_is_function('is_shop') || themify_is_function('is_product_category') || themify_is_function('is_product_tag') ) {
		$class = themify_get('setting-shop_layout')? themify_get('setting-shop_layout') : 'sidebar1';
	} elseif( themify_is_function('is_product') ){
		$class = themify_get('setting-single_product_layout')? themify_get('setting-single_product_layout') : 'sidebar1';
	}
	return $class;
}
/**
 * Alters condition to filter post layout class
 * @param bool
 * @return bool
 */
function themify_theme_default_post_layout_condition($condition) {
	return $condition || themify_is_function('is_shop') || themify_is_function('is_product_category') || themify_is_function('is_product_tag');
};
/**
 * Returns default shop layout
 * @param String $class
 * @return @String
 */
function themify_theme_default_post_layout($class) {
	if( themify_is_function('is_shop') || themify_is_function('is_product_category') || themify_is_function('is_product_tag') ) {
		$class = '' != themify_get('setting-products_layout')? themify_get('setting-products_layout') : 'grid4';
	}
	return $class;
};

/**
 * Checks if it's the function name passed exists and in that case, it calls the function
 * @param string $context
 * @return bool|mixed
 * @since 1.2.8
 */
function themify_is_function( $context = '' ) {
	if( function_exists( $context ) )
		return call_user_func( $context );
	else
		return false;
}

// Filters to change body class applied in shop
add_filter('themify_default_layout_condition', 'themify_theme_default_layout_condition');
add_filter('themify_default_layout', 'themify_theme_default_layout');
add_filter('themify_default_post_layout_condition', 'themify_theme_default_post_layout_condition');
add_filter('themify_default_post_layout', 'themify_theme_default_post_layout');

///////////////////////////////////////
// Start Woocommerce functions
///////////////////////////////////////

// Declare Woocommerce support
add_theme_support( 'woocommerce' );

add_action( 'themify_layout_before', 'themify_theme_layout_before' );
add_action( 'template_redirect', 'themify_redirect_product_ajax_content', 20 );
add_action( 'admin_notices', 'themify_check_ecommerce_environment_admin' );

add_filter( defined( 'WOOCOMMERCE_VERSION' ) && version_compare( WOOCOMMERCE_VERSION, '3.3.0', '>=' )
	? 'woocommerce_get_script_data' : 'woocommerce_params', 'themify_woocommerce_params' );
add_filter('themify_body_classes', 'themify_woocommerce_site_notice_class');

if ( ! function_exists( 'themify_woocommerce_site_notice_class' ) ) {
	/**
	 * Add additional class when Woocommerce site wide notice is enabled.
	 * @param array $classes
	 * @return array
	 * @since 1.2.8
	 */
	function themify_woocommerce_site_notice_class( $classes ) {
		$notice = get_option( 'woocommerce_demo_store' );
		if ( ! empty( $notice ) && 'no' != $notice ) {
			$classes[] = 'site-wide-notice';
		}
		return $classes;
	}
}

if ( ! function_exists( 'themify_woocommerce_active' ) ) {
	/**
	 * Checks if Woocommerce is installed and active
	 * @return bool
	 */
	function themify_woocommerce_active() {
		$plugin = 'woocommerce/woocommerce.php';
		$network_active = false;
		if ( is_multisite() ) {
			$plugins = get_site_option( 'active_sitewide_plugins' );
			if ( isset( $plugins[$plugin] ) )
				$network_active = true;
		}
		return in_array( $plugin, apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) || $network_active;
	}
}

if ( ! function_exists( 'themify_get_ecommerce_template' ) ) {
	/**
	 * Checks if Woocommerce is active and loads the requested template
	 * @param string $template
	 * @since 1.2.8
	 */
	function themify_get_ecommerce_template( $template = '' ) {
		if ( themify_woocommerce_active() )
			get_template_part( $template );
	}
}

/**
 * Include slider and, if Woocommerce is active, product slider
 * @since 1.2.8
 */
function themify_theme_layout_before() {
	if(is_front_page() && !is_paged()){ get_template_part( 'includes/slider'); }
	if(is_front_page() && !is_paged()){ themify_get_ecommerce_template( 'includes/product-slider'); }
}

/**
 * Add woocommerce_enable_ajax_add_to_cart option to JS
 * @param Array
 * @return Array
 */
function themify_woocommerce_params($params){
	if( is_array( $params ) ) {
		$params = array_merge( $params, array(
			'option_ajax_add_to_cart' => ( 'yes' == get_option( 'woocommerce_enable_ajax_add_to_cart' ) ) ? 'yes' : 'no'
		) );
	}
	
	return $params;
}

/**
 * Single product lightbox
 **/
function themify_redirect_product_ajax_content() {
	global $post, $wp_query;
	// locate template single page in lightbox
	if (is_single() && isset($_GET['ajax']) && $_GET['ajax']) {
		// remove admin bar inside iframe
		add_filter( 'show_admin_bar', '__return_false' );
		if (have_posts()) {
			woocommerce_single_product_content_ajax();
			die();
		} else {
			$wp_query->is_404 = true;
		}
	}
}

if ( ! function_exists( 'themify_check_ecommerce_environment_admin' ) ) {
	/**
	 * Check in admin if Woocommerce is enabled and show a notice otherwise.
	 * @since 1.3.0
	 */
	function themify_check_ecommerce_environment_admin() {
		if ( ! themify_woocommerce_active() ) {
			$warning = 'installwoocommerce';
			if ( ! get_option( 'themify_warning_' . $warning ) ) {
				wp_enqueue_script( 'themify-admin-warning' );
				echo '<div class="update-nag">'.__('Remember to install and activate WooCommerce plugin to enable the shop.', 'themify'). ' <a href="#" class="themify-close-warning" data-warning="' . $warning . '" data-nonce="' . wp_create_nonce( 'themify-warning' ) . '">' . __("Got it, don't remind me again.", 'themify') . '</a></div>';
			}
		}
	}
}

if ( ! function_exists( 'themify_check_ecommerce_scripts' ) ) {
	function themify_check_ecommerce_scripts() {
		wp_register_script( 'themify-admin-warning', THEME_URI . '/js/themify.admin.warning.js', array('jquery'), false, true );
	}
	add_action( 'admin_enqueue_scripts', 'themify_check_ecommerce_scripts' );
}

if ( ! function_exists( 'themify_dismiss_warning' ) ) {
	function themify_dismiss_warning() {
		check_ajax_referer( 'themify-warning', 'nonce' );
		$result = false;
		if ( isset( $_POST['warning'] ) ) {
			$result = update_option( 'themify_warning_' . $_POST['warning'], true );
		}
		if ( $result ) {
			echo 'true';
		} else {
			echo 'false';
		}
		die;
	}
	add_action( 'wp_ajax_themify_dismiss_warning', 'themify_dismiss_warning' );
}

// Load required files
if ( themify_woocommerce_active() ) {
	require_once(TEMPLATEPATH . '/woocommerce/theme-woocommerce.php'); // WooCommerce overrides
	require_once(TEMPLATEPATH . '/woocommerce/woocommerce-hooks.php'); // WooCommerce hook overrides
	require_once(TEMPLATEPATH . '/woocommerce/woocommerce-template.php'); // WooCommerce template overrides
}
