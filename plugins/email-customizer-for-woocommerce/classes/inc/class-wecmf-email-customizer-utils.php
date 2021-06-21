<?php
/**
 * Email Customizer for WooCommerce common functions
 *
 * @author    ThemeHiGH
 * @category  Admin
 */

if(!defined('ABSPATH')){ exit; }

if(!class_exists('WECMF_Email_Customizer_Utils')) :
class WECMF_Email_Customizer_Utils {
	private static $css_elm_props_map;
	const OPTION_KEY_TEMPLATE_SETTINGS = 'thwecmf_template_settings';
	const SETTINGS_KEY_TEMPLATE_LIST = 'templates';
	const SETTINGS_KEY_TEMPLATE_MAP = 'template_map';
	const OPTION_KEY_ADVANCED_SETTINGS = 'thwecmf_advanced_settings';
	const OPTION_KEY_WECMF_MISC = 'thwecmf_misc_settings';
	const THWECMF_EMAIL_INDEX = array(
		'new_order',
		'cancelled_order',
		'failed_order',
		'customer_on_hold_order',
		'customer_processing_order',
		'customer_completed_order',
		'customer_refunded_order',
		'customer_invoice',
		'customer_note',
		'customer_reset_password',
		'customer_new_account'
	);

	/**
     * Element css property array
     *
	 * @return string wrapper content
     */
	public static function css_elm_props_mapping(){
		$elm_css_map = array(
    		'text'	=> array(
    			'.thwecmf-block-text'	=>	array(
    				'color', 'font_size', 'm_t', 'm_r', 'm_b', 'm_l', 'text_align'
    			),
    			'.thwecmf-block-text .thwecmf-block-text-holder'	=>	array(
    				'color', 'font_size', 'font_weight', 'line_height', 'font_family', 'text_align', 'bg_color', 'b_t', 'b_r', 'b_b', 'b_l', 'border_color', 'border_style', 'p_t', 'p_r', 'p_b', 'p_l', 'upload_bg_url'
    			),
    			'.thwecmf-block-text .wecmf-txt-wrap'		=>	array(
    				'color', 'font_size'
    			),
                '.thwecmf-block-text *:not(br):not(a)'      =>  array(
                    'color', 'font_size'
                ),
    		),
    		'image'	=>	array(
    			'.thwecmf-block-image' => array(
    				'img_bg_color'		
    			),
    			'.thwecmf-block-image td.thwecmf-image-column' => array(
    				'content_align', 'img_p_t', 'img_p_r', 'img_p_b', 'img_p_l'
    			),
    			'.thwecmf-block-image td.thwecmf-image-column p' => array(
    				'img_size_width', 'img_size_height',
    			),
                '.thwecmf-block-image td.thwecmf-image-column p img' => array(
                    'img_size_height',
                ),
    		),
    		'gap'	=>	array(
    			'.thwecmf-block-gap'	=> array(
    				'height', 'bg_color', 'b_t', 'b_b', 'b_l', 'b_r',  'border_style', 'border_color'
    			),
    		),
    		'divider'	=>	array(
    			'.thwecmf-block-divider '	=>	array(
    				'm_t', 'm_r', 'm_b', 'm_l'
    			),
    			'.thwecmf-block-divider td'	=>	array(
    				'p_t', 'p_r', 'p_b', 'p_l', 'content_align'
    			),
    			'.thwecmf-block-divider td hr'	=>	array(
    				'width', 'divider_height', 'divider_color', 'divider_style'
    			),
    		),
    		'social'	=>	array(
    			'.thwecmf-block-social'	=>	array( 'bg_color', 'upload_bg_url'),
    			'.thwecmf-block-social .thwecmf-social-outer-td'	=>	array('p_t', 'p_r', 'p_b', 'p_l'),
    			'.thwecmf-block-social .thwecmf-social-td'	=>	array( 'icon_p_t', 'icon_p_r', 'icon_p_b', 'icon_p_l'),
                '.thwecmf-block-social .thwecmf-social-icon'    =>  array( 'img_size_width', 'img_size_height'),
    			'.thwecmf-block-social .thwecmf-social-icon img' =>	array('img_size_height'),
    			'.thwecmf-block-social td.thwecmf-td-fb'	=>	array('url1'),
    			'.thwecmf-block-social td.thwecmf-td-mail'	=>	array('url2'),
    			'.thwecmf-block-social td.thwecmf-td-tw'	=>	array('url3'),
    			'.thwecmf-block-social td.thwecmf-td-yb'	=>	array('url4'),
    			'.thwecmf-block-social td.thwecmf-td-lin'	=>	array('url5'),
    			'.thwecmf-block-social td.thwecmf-td-pin'	=>	array('url6'),
    			'.thwecmf-block-social td.thwecmf-td-insta'	=>	array('url7'),
    		),

    		'button'	=>	array(
    			'.thwecmf-button-wrapper-table'	=>	array(
    				'size_width', 'size_height', 'm_t', 'm_r', 'm_b', 'm_l', 'p_t', 'p_r', 'p_b', 'p_l'
    			),
    			'.thwecmf-button-wrapper-table .thwecmf-button-wrapper'	=>	array(
    				'font_weight', 'font_size', 'font_family','color', 'bg_color','b_t', 'b_b', 'b_l', 'b_r',  'border_style', 'border_color','content_p_t', 'content_p_r', 'content_p_b', 'content_p_l', 'text_align'
    			),

    			' .thwecmf-button-link'	=>	array(
    				'font_weight', 'line_height', 'font_size','font_family','color', 'text_align'
    			),
    		),
    		'header_details' => array(
    			'.thwecmf-block-header'	=>	array('size_width', 'size_height', 'bg_color', 'upload_bg_url', 'bg_repeat', 'bg_position', 'bg_size', 'm_t', 'm_r', 'm_b', 'm_l', 'b_t', 'b_r', 'b_b', 'b_l', 'border_style', 'border_color'),

    			' .thwecmf-header-logo-tr'	=> array('upload_img_url'),

    			' .thwecmf-header-logo'	=> array('content_align', 'img_p_t', 'img_p_r', 'img_p_b', 'img_p_l'),

                ' .thwecmf-header-logo .thwecmf-header-logo-ph' => array('img_m_t', 'img_m_r', 'img_m_b', 'img_m_l', 'img_size_height','img_size_width','img_border_width_top','img_border_width_right', 'img_border_width_bottom', 'img_border_width_left', 'img_border_color', 'img_border_style', 'img_bg_color', 'align'),
    			' .thwecmf-header-logo .thwecmf-header-logo-ph img'	=> array('img_size_height'),

    			' .thwecmf-header-text'	=> array('p_t', 'p_r', 'p_b', 'p_l'),
    			' .thwecmf-header-text h1'	=> array('font_size', 'color', 'font_weight', 'text_align', 'line_height','font_family'),
    		),
    		'order_details'	=>	array(
    			'.thwecmf-block-order'	=>	array(
    				'bg_color','upload_bg_url'
    			),
    			'.thwecmf-block-order .thwecmf-order-padding'	=>	array(
    				'p_t', 'p_r', 'p_b', 'p_l'
    			),
    			'.thwecmf-block-order .thwecmf-order-heading'	=>	array(
    				'color', 'font_size','font_family'
    			),
    			'.thwecmf-block-order .thwecmf-order-table'	=>	array(
    				'content_bg_color', 'content_border_color','details_font_family'
    			),
    			'.thwecmf-block-order .thwecmf-td'	=>	array(
    				'details_font_size', 'details_color', 'details_font_family','content_border_color',
    			),
    			'.thwecmf-block-order .thwecmf-td .thwecmf-order-item-img'	=> array('product_img'),
    		),
    		'billing_address'	=>	array(
    			'.thwecmf-block-billing .thwecmf-address-alignment'	=> array( 'align' ),
    			'.thwecmf-block-billing .thwecmf-address-wrapper-table'	=> array(
    				'size_width', 'size_height', 'bg_color', 'b_t', 'b_r', 'b_b', 'b_l', 'border_style', 'border_color', 'm_t', 'm_r', 'm_b', 'm_l','upload_bg_url'
    			),
    			'.thwecmf-block-billing .thwecmf-billing-padding'		=> array(
    				'p_t', 'p_r', 'p_b', 'p_l'
    			),
    			'.thwecmf-block-billing .thwecmf-billing-header'	=> array(
    				'font_size', 'color','text_align', 'font_family'
    			),
    			'.thwecmf-block-billing .thwecmf-billing-body'	=> array(
    				'details_font_size', 'details_color','details_text_align','details_font_family'
    			),
    		),
    		'shipping_address'	=>	array(
    			'.thwecmf-block-shipping .thwecmf-address-alignment'	=> array( 'align' ),
    			'.thwecmf-block-shipping .thwecmf-address-wrapper-table'	=> array(
    				'size_width', 'size_height', 'bg_color', 'b_t', 'b_r', 'b_b', 'b_l', 'border_style', 'border_color', 'm_t', 'm_r', 'm_b', 'm_l', 'upload_bg_url'
    			),
    			'.thwecmf-block-shipping .thwecmf-shipping-padding'		=> array(
    				'p_t', 'p_r', 'p_b', 'p_l'
    			),
    			'.thwecmf-block-shipping .thwecmf-shipping-header'	=> array(
    				'font_size', 'color','text_align','font_family'
    			),
    			'.thwecmf-block-shipping .thwecmf-shipping-body'	=> array(
    				'details_font_size', 'details_color','details_text_align','details_font_family'
    			),
    		),
    		'customer_address'	=>	array(
    			'.thwecmf-block-customer .thwecmf-address-wrapper-table'	=> array(
    				'size_width', 'size_height', 'bg_color', 'upload_bg_url', 'bg_size', 'bg_position', 'bg_repeat', 'b_t', 'b_r', 'b_b', 'b_l', 'border_style', 'border_color', 'm_t', 'm_r', 'm_b', 'm_l'
    			),
    			'.thwecmf-block-customer .thwecmf-customer-padding'		=> array(
    				'p_t', 'p_r', 'p_b', 'p_l'
    			),
    			'.thwecmf-block-customer .thwecmf-customer-header'	=> array(
    				'font_size', 'color','text_align','font_family'
    			),
    			'.thwecmf-block-customer .thwecmf-customer-body'	=> array(
    				'details_font_size', 'details_color','details_text_align','details_font_family',
    			),
    		),
    		'order_details'	=>	array(
    			'.thwecmf-block-order'	=>	array(
    				'align', 'size_width', 'size_height', 'bg_color', 'm_t', 'm_r', 'm_b', 'm_l', 'upload_bg_url', 'bg_size', 'bg_repeat', 'bg_repeat', 'b_t', 'b_r', 'b_b', 'b_l', 'border_style', 'border_color'
    			),
    			'.thwecmf-block-order .thwecmf-order-padding'	=>	array(
    				'p_t', 'p_r', 'p_b', 'p_l'
    			),
    			'.thwecmf-block-order .thwecmf-order-heading'	=>	array(
    				'color', 'font_size', 'text_align', 'font_weight', 'line_height','font_family'
    			),
    			'.thwecmf-block-order .thwecmf-order-table'	=>	array(
    				'content_size_width', 'content_size_height', 'content_bg_color', 'content_border_color', 'content_m_t', 'content_m_r', 'content_m_b', 'content_m_l','details_font_family'
    			),
    			'.thwecmf-block-order .thwecmf-td'	=>	array(
    				'details_font_size', 'details_color', 'details_text_align','details_font_family','content_border_color', 'details_font_weight', 'details_line_height', 'content_p_t', 'content_p_r', 'content_p_b', 'content_p_l'
    			),
    			'.thwecmf-block-order .thwecmf-td .thwecmf-order-item-img'	=> array('product_img'),
    		),
    		'gif'	=>	array(
    			'.thwecmf-block-gif'	=>	array(
    				'upload_bg_url', 'bg_position', 'bg_size', 'bg_repeat', 'bg_color', 'm_t', 'm_r', 'm_b', 'm_l'
    			),
    			'.thwecmf-block-gif td.thwecmf-gif-column'	=>	array(
    				'content_align'
    			),
    			'.thwecmf-block-gif td.thwecmf-gif-column p'	=>	array(
    				'img_size_width', 'img_size_height', 'p_t', 'p_r', 'p_b', 'p_l', 'b_t', 'b_r', 'b_b', 'b_l', 'border_color', 'border_style'
    			),
                '.thwecmf-block-gif td.thwecmf-gif-column p img'    =>  array('img_size_height'),
    		),
    		't_builder'	=>	array(
    			'.thwecmf-main-builder .thwecmf-builder-column'	=> 	array(
    				'b_t', 'b_r', 'b_b', 'b_l', 'border_style', 'border_color', 'bg_color', 'upload_bg_url'
    			),
    		),
    	);
		return $elm_css_map;
	}

	/**
     * Get the element css properties
     *
     * @param  string||boolean $name element name if any
	 * @return array css properties
     */
	public static function css_elm_props( $name=false ){
		$props = array(
	        "one_column"=> array(
	            "row"=> array("p_t"=>"0px","p_r"=>"0px","p_b"=>"0px","p_l"=>"0px","b_t"=>"0px","b_r"=>"0px","b_b"=>"0px","b_l"=>"0px","border_style"=>"none","border_color"=>"","bg_color"=>"","upload_bg_url"=>""),
	            "column"=> array("width"=>"100%","p_t"=>"10px","p_r"=>"10px","p_b"=>"10px","p_l"=>"10px","text_align"=>"center","b_t"=>"1px","b_r"=>"1px","b_b"=>"1px","b_l"=>"1px","border_style"=>"dotted","border_color"=>"#dddddd","bg_color"=>"","upload_bg_url"=>""),
	        ),

	        "two_column" => array(
	            "row" => array("p_t"=>"","p_r"=>"","p_b"=>"","p_l"=>"","b_t"=>"0px","b_r"=>"0px","b_b"=>"0px","b_l"=>"0px","border_style"=>"none","border_color"=>"","bg_color"=>"","upload_bg_url"=>""),
	            "column" => array("width"=>"50%","p_t"=>"10px","p_r"=>"10px","p_b"=>"10px","p_l"=>"10px","b_t"=>"1px","b_r"=>"1px","b_b"=>"1px","b_l"=>"1px","border_style"=>"dotted","border_color"=>"#dddddd","bg_color"=>"","text_align"=>"center","upload_bg_url"=>""),
	        ),
	        "three_column" => array(
	            "row" => array("p_t"=>"0px","p_r"=>"0px","p_b"=>"0px","p_l"=>"0px","b_t"=>"0px","b_r"=>"0px","b_b"=>"0px","b_l"=>"0px","border_style"=>"none","border_color"=>"","bg_color"=>"","upload_bg_url"=>""),
	            "column" => array("width"=>"33.333333333333336%","p_t"=>"10px","p_r"=>"10px","p_b"=>"10px","p_l"=>"10px","b_t"=>"1px","b_r"=>"1px","b_b"=>"1px","b_l"=>"1px","border_style"=>"dotted","border_color"=>"#dddddd","bg_color"=>"","text_align"=>"center","upload_bg_url"=>"") ,
	        ),
	        "four_column" => array(
	            "row" => array("p_t"=>"0px","p_r"=>"0px","p_b"=>"0px","p_l"=>"0px","b_t"=>"0px","b_r"=>"0px","b_b"=>"0px","b_l"=>"0px","border_style"=>"none","border_color"=>"","bg_color"=>"","upload_bg_url"=>""),
	            "column" => array("width"=>"25%","p_t"=>"10px","p_r"=>"10px","p_b"=>"10px","p_l"=>"10px","b_t"=>"1px","b_r"=>"1px","b_b"=>"1px","b_l"=>"1px","border_style"=>"dotted","border_color"=>"#dddddd","bg_color"=>"","text_align"=>"center","upload_bg_url"=>""),
	        ),
	        "header_details" => array(
	            "css" => array("size_width"=>"100%","size_height"=>"auto", "p_t"=>"30px","p_r"=>"0px","p_b"=>"30px","p_l"=>"0px","color"=>"#ffffff","font_size"=>"40px","line_height"=>"22px","text_align"=>"center","font_family"=>"georgia","font_weight"=>"normal","img_p_t"=>"15px","img_p_r"=>"5px","img_p_b"=>"15px","img_p_l"=>"5px","bg_color"=>"#0099ff","b_t"=>"0px","b_r"=>"0px","b_b"=>"0px","b_l"=>"0px","border_style"=>"none","border_color"=>"","content"=>"","upload_bg_url"=>"","upload_img_url"=>"","content_align"=>"center","img_size_width"=>"155px","img_size_height"=>"103px"),
	            "text" => array("content"=>"Email Template Header","upload_img_url"=>""),
	        ),
	        "customer_address" => array(
	            "css" => array("align"=>"center","color"=>"#0099ff","text_align"=>"center","font_size"=>"18px","line_height"=>"22px","font_weight"=>"bold","font_family"=>"helvetica","details_color"=>"#444444","details_text_align"=>"center","details_font_size"=>"13px","details_line_height"=>"22px","details_font_weight"=>"normal","details_font_family"=>"helvetica","p_t"=>"5px","p_r"=>"0px","p_b"=>"2px","p_l"=>"0px","m_t"=>"0px","m_r"=>"auto","m_b"=>"0px","m_l"=>"auto","bg_color"=>"","upload_bg_url"=>"","content"=>""),
	            "text" => array("content"=>"Customer Details"),
	        ),
	        "order_details" => array(
	            "css" => array("color"=>"#4286f4","font_size"=>"18px","font_family"=>"helvetica","details_text_align"=>"left","details_color"=>"#636363","details_font_size"=>"14px","details_font_family"=>"helvetica","content_border_color"=>"#e5e5e5","p_t"=>"20px","p_r"=>"48px","p_b"=>"20px","p_l"=>"48px","upload_bg_url"=>"","bg_color"=>"","product_img"=>"none"),
	            "text" => array("content"=>"Order"),
	        ),
	        "billing_address" => array(
	            "css" => array("align"=>"center","color"=>"#0099ff","text_align"=>"center","font_size"=>"18px","font_family"=>"helvetica","details_color"=>"#444444","details_text_align"=>"center","details_font_size"=>"13px","details_font_family"=>"helvetica","size_width"=>"100%","size_height"=>"115px","p_t"=>"5px","p_r"=>"0px","p_b"=>"2px","p_l"=>"0px","m_t"=>"0px","m_r"=>"0px","m_b"=>"0px","m_l"=>"0px","b_t"=>"0px","b_r"=>"0px","b_b"=>"0px","b_l"=>"0px","border_style"=>"none","border_color"=>"","bg_color"=>"","content"=>"","upload_bg_url"=>""),
	            "text" => array("content"=>"Billing Details"),
	        ),
	        "shipping_address" => array(
	            "css" => array("align"=>"center","color"=>"#0099ff","text_align"=>"center","font_size"=>"18px","font_family"=>"helvetica","details_color"=>"#444444","details_text_align"=>"center","details_font_size"=>"13px","details_font_family"=>"helvetica","size_width"=>"100%","size_height"=>"115px","p_t"=>"5px","p_r"=>"0px","p_b"=>"2px","p_l"=>"0px","m_t"=>"0px","m_r"=>"0px","m_b"=>"0px","m_l"=>"0px","b_t"=>"0px","b_r"=>"0px","b_b"=>"0px","b_l"=>"0px","border_style"=>"none","border_color"=>"","bg_color"=>"","content"=>"","upload_bg_url"=>""),
	            "text" => array("content"=>"Shipping Details"),
	        ),
	        "text" => array(
	            "css" => array("color"=>"#636363","align"=>"center", "font_size"=>"13px","line_height"=>"22px","font_weight"=>"400","font_family"=>"helvetica","bg_color"=>"", "upload_bg_url"=>"","b_t"=>"0px", "b_r"=>"0px", "b_b"=>"0px", "b_l"=>"0px", "border_color"=>"", "border_style"=>"none", "m_t"=>"0px", "m_r"=>"auto", "m_b"=>"0px", "m_l"=>"auto", "p_t"=>"15px", "p_r"=>"15px", "p_b"=>"15px", "p_l"=>"15px", "text_align"=>"center","textarea_content"=>""),
	            "text" => array("textarea_content"=>"Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry&#39;s standard dummy text ever since the 1500."),
	        ),
	        "image" => array(
	            "css" => array("img_size_width"=>"50%","img_size_height"=>"auto","align"=>"","upload_img_url"=>"","content_align"=>"center","img_p_t"=>"10px","img_p_r"=>"0px","img_p_b"=>"10px","img_p_l"=>"0px","img_bg_color"=>""),
	            "text" => array("upload_img_url"=>""),
	        ),
	        "social" => array(
	            "css" => array("p_t"=>"0px","p_r"=>"0px","p_b"=>"0px","p_l"=>"0px","img_size_width"=>"40px","img_size_height"=>"40px","icon_p_t"=>"15px","icon_p_r"=>"3px","icon_p_b"=>"15px","icon_p_l"=>"3px","content_align"=>"center","bg_color"=>"","upload_bg_url"=>"","url1"=>"table-cell","url2"=>"table-cell","url3"=>"table-cell","url4"=>"table-cell","url5"=>"table-cell","url6"=>"table-cell","url7"=>"table-cell"),
	            "text" => array("url1"=>"http=>//www.facebook.com/","url2"=>"https=>//mail.google.com/mail/?view=cm&to=yourmail@example.com&bcc=somemail@example.com","url3"=>"http=>//www.twitter.com/","url4"=>"http=>//www.youtube.com/","url5"=>"https=>//www.linkedin.com/","url6"=>"http=>//www.pinterest.com/","url7"=>"http=>//www.instagram.com/"),
	        ),
	        "button" => array(
	            "css" => array("size_width"=>"80px","size_height"=>"20px","text_align"=>"center","color"=>"#fff","font_size"=>"13px","line_height"=>"22px","font_family"=>"helvetica","font_weight"=>"400","p_t"=>"10px","p_r"=>"0px","p_b"=>"10px","p_l"=>"0px","m_t"=>"0px","m_r"=>"auto","m_b"=>"0px","m_l"=>"auto","content_p_t"=>"10px","content_p_r"=>"0px","content_p_b"=>"10px","content_p_l"=>"0px","b_t"=>"1px","b_r"=>"1px","b_b"=>"1px","b_l"=>"1px","border_style"=>"solid","border_color"=>"#4169e1","bg_color"=>"#4169e1","content"=>"","url"=>"","title"=>""),
	            "text" => array("content"=>"Click Here","url"=>"#"),
	        ),
	        "divider" => array(
	            "css" => array("width"=>"70%","divider_height"=>"2px","divider_color"=>"#808080","divider_style"=>"solid","m_t"=>"0px","m_r"=>"auto","m_b"=>"0px","m_l"=>"auto","p_t"=>"20px","p_r"=>"0px","p_b"=>"20px","p_l"=>"0px","content_align"=>"center"),
	            "text" => array(),
	        ),
	        "gap" => array(
	            "css"=> array("height"=>"48px","b_t"=>"0px","b_r"=>"0px","b_b"=>"0px","b_l"=>"0px","border_style"=>"none","border_color"=>"","bg_color"=>""),
	            "text" => array(),
	        ),
	        "gif" => array(
	            "css" => array("p_t"=>"10px","p_r"=>"10px","p_b"=>"10px","p_l"=>"10px","bg_color"=>"","img_size_width"=>"50%","img_size_height"=>"auto","content_align"=>"center","upload_img_url"=>""),
	            "text" => array("upload_img_url"=>""),
	        ),
	        "t_builder" => array(
	            "css" => array("b_t"=>"1px","b_r"=>"1px","b_b"=>"1px","b_l"=>"1px","border_style"=>"solid","border_color"=>"#dedede","bg_color"=>"#ffffff","upload_bg_url"=>""),
	            "text" => array(),
	        )
	    );

		if( $name ){
			$props = isset( $props[$name] ) ? $props[$name] : false;
			if( $props ){
				return array_merge( $props['css'], $props['text'] );
			}
		}
		return $props;
	}

	/**
     * Get css properties of layouts (row/column)
     *
     * @param  string $name layout name
	 * @return string css properties
     */
	public static function get_layout_props( $name ){
		$props = array();
		$column_row_map = array( 'one_column_one' => 'one_column', 'two_column_one' => 'two_column', 'two_column_two' => 'two_column', 'three_column_one' => 'three_column', 'three_column_two' => 'three_column', 'three_column_three' => 'three_column', 'four_column_one' => 'four_column', 'four_column_two' => 'four_column', 'four_column_three' => 'four_column', 'four_column_four' => 'four_column');
		$default_props = self::css_elm_props();
   		if( in_array( $name, $column_row_map ) ){
   			$props = isset( $default_props[$name]['row'] ) ? $default_props[$name]['row'] : array();

   		}else if( isset( $column_row_map[$name] ) ){
   			$column = $column_row_map[$name];
   			$props = isset( $default_props[$column]['column'] ) ? $default_props[$column]['column'] : array();

   		}
   		return $props;
	}

	/**
     * WooCommerce version check
     *
     * @param  string $version default version to be checked against
	 * @return boolean current version is greater than 3.0 or not
     */
	public static function thwecmf_woo_version_check( $version = '3.0' ) {
	  	if(function_exists( 'is_woocommerce_active' ) && is_woocommerce_active() ) {
			global $woocommerce;
			if( version_compare( $woocommerce->version, $version, ">=" ) ) {
		  		return true;
			}
	  	}
	  	return false;
	}

	/**
     * Check WooCommerce version for emogrifier comaptibility
     *
     * @param  string $version default version to be checked against
	 * @return sboolean current version is greater than 3.6 or not
     */
	public static function thwecmf_emogrifier_version_check( $version = '3.6' ) {
	  	if(function_exists( 'is_woocommerce_active' ) && is_woocommerce_active() ) {
			global $woocommerce;
			if( version_compare( $woocommerce->version, $version, ">" ) ) {
		  		return true;
			}
	  	}
	  	return false;
	}

	/**
	 * Get decoded json data
	 *
	 * @param  string $data json data
	 * @return object || boolean decoded json data
	 */
	public static function is_json_decode($data){
		$json_data = json_decode($data);
		$json_data = json_last_error() == JSON_ERROR_NONE ?  $json_data : false;
		return $json_data;
	}

	/**
	 * created template directory
	 *
	 * @return boolean whether directory exists or directory created
	 */
	public static function create_directory(){
		$upload_dir = wp_upload_dir();
	    $wecm_dir = $upload_dir['basedir'].'/thwec_templates';
	  	$wecm_dir = trailingslashit($wecm_dir);
	  	$dir_exists = !file_exists($wecm_dir) && !is_dir($wecm_dir);
	  	if( $dir_exists ){
	  		return wp_mkdir_p( $wecm_dir );
	  	}
	  	return $dir_exists; 	
	}

    /**
     * created preivew directory
     *
     * @return boolean whether directory exists or directory created
     */
	public static function create_preview(){
		if( ! is_dir( self::get_template_preview_directory() ) ){
			return wp_mkdir_p(self::get_template_preview_directory());
		}
		return is_dir( self::get_template_preview_directory() );
	}

    /**
     * delete preivew directory
     *
     * @return boolean whether directory deleted or not
     */
	public static function delete_preview(){
		$dir = self::get_template_preview_directory();
		if( file_exists( $dir ) && is_dir( $dir ) ){
			self::delete_directory( $dir );
		}
	}

    /**
     * Delete the directory and files
     *
     * @param string $dir directory / file path
     * @return boolean whether directory deleted or not
     */
	public static function delete_directory( $dir ){
		$files = scandir( $dir ); // get all file names
		foreach( $files as $file ){ // iterate files
			if( $file != '.' && $file != '..' ){ //scandir() contains two values '.' & '..' 
				if( is_file( $dir.'/'.$file ) ){
					unlink( $dir.'/'.$file ); // delete file		  	
				}else if( is_dir( $dir.'/'.$file ) ){
					self::delete_directory( $dir.'/'.$file );
				}
			}
		}
		return rmdir( $dir );
	}

    /**
     * Get the path for template preview file
     *
     * @param string $name preview file name
     * @return string preview template path
     */
	public static function preview_path( $name ){
		return esc_url( THWECMF_CUSTOM_T_PATH ).'preview/thwecmf-html-preview-'.$name.'.php';
	}

	/**
	 * Prepare template name key from user input name
	 *
	 * @param  string $display_name user entered template name
	 * @return string $name template name key
	 */
	public static function prepare_template_name($display_name){
		$name = strtolower($display_name);
		$name = preg_replace('/\s+/', '_', $name);
		return $name;
	}

	/**
	 * Get decoded json data
	 *
	 * @param  string $data json data
	 * @return object || boolean decoded json data
	 */
	public static function thwecmf_is_json_decode($data){
		$json_data = json_decode($data);
		$json_data = json_last_error() == JSON_ERROR_NONE ?  $json_data : false;
		return $json_data;
	}

	/**
	 * Check if user has capability|| roles to do actions
	 *
	 * @return boolean capable or not
	 */
	public static function is_user_capable(){
		$capable = false;
		$user = wp_get_current_user();
		$allowed_roles = apply_filters('thwecmf_user_capabilities_override', array('editor', 'administrator') );
		if( array_intersect($allowed_roles, $user->roles ) ) {
   			$capable = true;
   		}else if( is_super_admin($user->ID ) ){
   			$capable = true;
   		}
   		return $capable;
	}

	/**
	 * Get plugin initial settings
	 *
	 * @return array $settings plugin settings
	 */
	public static function thwecmf_setup_initial_settings(){
		$settings = self::thwecmf_get_template_settings();
		if(isset($settings['templates']) && empty($settings['templates'])){
			$settings = self::thwecmf_save_template_settings(self::get_default_templates_json());
		}else{
			return true;
		}
		return $settings;
	}
	
    /**
     * Get the template settings
     *
     * @return array template settings
     */
	public static function thwecmf_get_template_settings(){
		$settings = get_option(self::OPTION_KEY_TEMPLATE_SETTINGS);
		if(empty($settings)){
			$settings = array(
				self::SETTINGS_KEY_TEMPLATE_LIST => array(), 
				self::SETTINGS_KEY_TEMPLATE_MAP => array()
			);
		}
		return $settings;
	}

    /**
     * Get list of templates created
     *
     * @param boolean/array $settings template settings
     * @return array list of created templates or empty array
     */
	public static function thwecmf_get_template_list($settings=false){
		if(!is_array($settings)){
			$settings = self::thwecmf_get_template_settings();
		}
		return is_array($settings) && isset($settings[self::SETTINGS_KEY_TEMPLATE_LIST]) ? $settings[self::SETTINGS_KEY_TEMPLATE_LIST] : array();
	}

    /**
     * Get the template map
     *
     * @param boolean/array $settings template settings
     * @return array template map
     */
	public static function thwecmf_get_template_map($settings=false){
		if(!is_array($settings)){
			$settings = self::thwecmf_get_template_settings();
		}
		return is_array($settings) && isset($settings[self::SETTINGS_KEY_TEMPLATE_MAP]) ? $settings[self::SETTINGS_KEY_TEMPLATE_MAP] : array();
	}

     /**
     * Reset the template settings
     *
     * @return array resetted template settings
     */
	public static function thwecmf_reset_template_map(){
		$settings = self::thwecmf_get_template_settings();
		if( is_array($settings) && isset($settings[self::SETTINGS_KEY_TEMPLATE_MAP]) ){
			$settings[self::SETTINGS_KEY_TEMPLATE_MAP] = array();
		}
		return $settings;
		
	}

    /**
     * Save template settings
     *
     * @param array $settings template settings to save
     * @param boolean $new update existing or add new settings
     * @return boolean $result settings saved or not
     */
	public static function thwecmf_save_template_settings($settings, $new=false){
		$result = false;
		if($new){
			$result = add_option(self::OPTION_KEY_TEMPLATE_SETTINGS, $settings);
		}else{
			$result = update_option(self::OPTION_KEY_TEMPLATE_SETTINGS, $settings);
		}
		return $result;
	}

    /**
     * Get advanced settings
     *
     * @return boolean/array advanced settings. boolean if empty
     */
	public static function thwecmf_get_advanced_settings(){
		$settings = get_option(self::OPTION_KEY_ADVANCED_SETTINGS);
		return empty($settings) ? false : $settings;
	}
	
    /**
     * Save value of a key from template settings
     *
     * @param array $settings template settings from which key to be retrieved
     * @param string $key key to be retrieved from settings
     * @return string value corresponding to the key from $settings
     */
	public static function thwecmf_get_setting_value($settings, $key){
		if(is_array($settings) && isset($settings[$key])){
			return $settings[$key];
		}
		return '';
	}
	
    /**
     * Get value of corresponding key from advanced settings
     *
     * @param string $key key to be retrieved from settings
     * @return string value corresponding to the key from $settings
     */
	public static function thwecmf_get_settings($key){
		$settings = self::thwecmf_get_advanced_settings();
		if(is_array($settings) && isset($settings[$key])){
			return $settings[$key];
		}
		return '';
	}

    /**
     * Get value of corresponding key from miscellaneous settings or miscellaneous settings itself
     *
     * @param string $key key to be retrieved from miscellaneous settings
     * @return string value corresponding to the key from $settings / miscellaneous settings
     */
	public static function get_wecmf_misc_settings($key=false){
		$settings = get_option(self::OPTION_KEY_WECMF_MISC);
        if( $key ){
            if(is_array($settings) && isset($settings[$key])){
                return $settings[$key];
            }
        }
		return $settings;
	}

    /**
     * Set miscellaneous settings 
     *
     * @param array $settings settings to be saved as miscellaneous settings
     * @return boolean settings updated or not
     */
    public static function set_wecmf_misc_settings($settings){
        return update_option(self::OPTION_KEY_WECMF_MISC, $settings);
    }

    /**
     * Update miscellaneous settings to show / hide banner
     *
     * @return boolean $show show/hide banner
     */
    public static function show_banner(){
        $show = true;
        $settings = self::get_wecmf_misc_settings();
        if( isset( $settings['banner_expiry'] ) ){
            $expiry_date = $settings['banner_expiry'];
            $timezone = new DateTimeZone( 'UTC' );
            $now = new DateTime("now", $timezone );
            $now = $now->format('Y-m-d H:i:s');
            $show =  strtotime( $expiry_date ) <= strtotime( $now );
            if( $show ){
                unset( $settings['banner_expiry'] );
                self::set_wecmf_misc_settings( $settings );
            }
        }
        return $show;
    }

    /**
     * Get the template file path
     *
     * @param string $file name of the template path to be retrieved
     * @param boolean $preview if path of template retrieved should be preview template or not
     * @param boolean/string $ext extension of the file to be previewed
     * @return string/boolean file path if it exists or false
     */
	public static function get_template($file,$preview,$ext=false){
    	$extension = $ext ? $ext : 'php'; 
    	if( $preview ){
    		$file = self::preview_path( $file );
    	}else{
    		$file = esc_url( THWECMF_CUSTOM_T_PATH ).$file.'.'.$extension;
    	}
		
    	return file_exists($file) ? $file : false;
	}

     /**
     * Get the template file path
     *
     * @return array list of email statuses avialable in plugin
     */
	public static function email_statuses(){
		$email_statuses = array(
			'admin-new-order' 			=> 'Admin New Order',
			'admin-cancelled-order'		=> 'Admin Cancelled Order',
			'admin-failed-order'		=> 'Admin Failed Order',
			'customer-completed-order'	=> 'Customer Completed Order',
			'customer-on-hold-order'	=> 'Customer On Hold Order',
			'customer-processing-order'	=> 'Customer Processing Order',
			'customer-refunded-order'	=> 'Customer Refund Order',
			'customer-invoice'			=> 'Customer Invoice / Order details',
			'customer-note'				=> 'Customer Note',
			'customer-reset-password'	=> 'Reset Password',
			'customer-new-account'		=> 'New Account',
		);
		return $email_statuses;
	}

    /**
     * Get default template contents
     *
     * @return boolean/array default template contents / false if not found
     */
	public static function thwecmf_get_templates( $name ){
		$path = esc_url( TH_WECMF_PATH.'classes/inc/settings.txt' );
		$content = file_get_contents( $path );
		$settings = unserialize(base64_decode($content));
		if( $name && isset( $settings['templates'][$name] ) ){
			return self::sanitize_template_data( $settings['templates'][$name] );
		}
		return false;
	}

    /**
     * Reset the template to default
     *
     * @param string $template template name of the template to reset
     * @return boolean reset or not
     */
	public static function thwecmf_reset_templates( $template ){
		$reset = false;
		$db_settings = self::thwecmf_get_template_settings();
		$template_settings = self::thwecmf_get_templates( $template );
		if( $template_settings &&  is_array( $template_settings ) ){
			$db_settings['templates'][$template] = $template_settings;
			$reset = self::thwecmf_save_template_settings( $db_settings);
		}
		return $reset;
	}	

    /**
     * Sanitize template data
     *
     * @param array template data
     * @return array sanitized template data
     */
    public static function sanitize_template_data( $data ){
        foreach ( $data as $key => $value ) {
            if( $key === "file_name" ){
                $data[$key] = sanitize_file_name( $value );

            }else if( $key === "display_name" ){
                $data[$key] = sanitize_text_field( $value );

            }else if( $key === "template_data" ){
                $data[$key] = wp_kses( $value ,wp_kses_allowed_html('post') );

            }
        }
        return $data;
    }

    /**
     * Check if the template is a valid email customizer template
     *
     * @return boolean valid template or not
     */
	public static function wecm_valid( $name = '', $key=false ){
		if( $key && !empty( $name ) ){
			$name = str_replace("_", "-", $name);
		}else{
			$name = isset($_POST['template_name']) ? sanitize_text_field($_POST['template_name']) : "";
			$name = $name ?str_replace(" ", "-", strtolower($name)) : $name;
		}
		if( $name && array_key_exists( $name, self::email_statuses() ) ){
			return true;
		}
		return false;
	}

    /**
     * Check if the tempalte save action is a valid action
     *
     * @return boolean valid action or not
     */
	public static function is_valid_action(){
		$ajax_ref = check_ajax_referer( 'thwecmf_ajax_security', 'thwecmf_security', false);
		if( $ajax_ref && self::is_user_capable() ) {
			return true;
		}
		return false;
	}

    /**
     * Check if a the operation to hide banner is valid
     *
     * @return boolean valid operation or not
     */
    public static function is_valid_banner_action(){
        $ajax_ref = check_ajax_referer( 'thwecmf_banner_ajax_security', 'thwecmf_security', false);
        if( $ajax_ref && self::is_user_capable() ) {
            return true;
        }
        return false;
    }

    /**
     * Check if the template is in customizable template list
     *
     * @return boolean whether it is a customizable template or not
     */
	public static function is_template($name=''){
		$template = !empty( $name ) ? $name : false;
		$template = !$template && isset( $_POST['template_name'] ) ? sanitize_text_field( $_POST['template_name'] ) : $template;
		$template = str_replace( " ", "_", $template);
		if( $template && in_array( $template, self::THWECMF_EMAIL_INDEX ) ){
			return true;
		}
		if( $template && in_array( str_replace("admin_", "", $template ), self::THWECMF_EMAIL_INDEX )  ){
			return true;
		}
		return false;
	}

     /**
     * Get the logged in user email
     *
     * @return string logged in user email
     */
	public static function get_logged_user_email(){
		$email = '';
	   	$current_user = wp_get_current_user();
		if( $current_user !== 0 ){
			$email =  $current_user->user_email;
		}
		return $email;
	}

	/**
	 * Get logged in user object
	 *
	 * @return boolean||object user object
	 */
	public static function get_logged_in_user(){
	   	$current_user = wp_get_current_user();
		if( $current_user !== 0 ){
			return $current_user;
			
		}
		return false;
	}

	/**
	 * Check if empty or not
	 *
	 * @param  string $value variable||key (in case of array) to be tested 
	 * @param  string $type type of variable
	 * @param  string||boolean $index array key if string
	 * @return boolean empty or not
	 */
	public static function is_not_empty( $value, $type, $index=false ){
		switch ( $type ) {
			case 'array':
				$empty = is_array( $value ) && !empty( $value );
				break;
			default:
				$empty = isset( $value[$index] ) && !empty( $value[$index] ); 
				break;
		}

		return $empty;
	}

	/**
	 * Email compatibility styles
	 *
	 * @return string styles
	 */
	public static function get_thwecmf_styles(){
		$styles = '#tpf_t_builder #template_container,#tpf_t_builder #template_header,#tpf_t_builder #template_body,#tpf_t_builder #template_footer{width:100% !important;}';
		$styles.= '#tpf_t_builder #template_container{width:100% !important;border:0px none transparent !important;box-shadow: none !important;}';
		$styles .= '#tpf_t_builder #body_content > table:first-child > tbody > tr > td{padding:15px 0px !important;}'; //To remove the padding after header when woocommerce header hook used in template (48px 48px 0px) 
		$styles .= '#tpf_t_builder div > table td,#tpf_t_builder div > table th{ font-size: 14px;line-height:150%;font-family: "Helvetica Neue", Helvetica, Roboto, Arial, sans-serif;}';
		
		// Fix - Order table quantity column header text breaks
		$styles .= '#tpf_t_builder div > table.td th, #tpf_t_builder div > table.td td{word-break: keep-all;}';
		$styles.= '#tpf_t_builder #wrapper{padding:0;background-color:transparent;}';
		$styles.= '.main-builder .thwecmf-block a{color: #1155cc;}';
		$styles.= '#tpf_t_builder .thwecmf-columns p{color:#636363;font-size:14px;}';
		$styles.= '#tpf_t_builder .thwecmf-columns .td .td{padding:12px;}';
		$styles.= '#tpf_t_builder ul.wc-item-meta{font-size: small;margin: 1em 0 0;padding: 0;list-style: none;}';
		$styles.= '#tpf_t_builder ul.wc-item-meta li{margin: 0.5em 0 0;padding: 0;}';
		$styles.= '#tpf_t_builder ul.wc-item-meta li p{margin: 0;}';
		$styles.= '#tpf_t_builder .thwecmf-columns .address{font-size:14px;line-height:150%;}';
        if( apply_filters( 'thwec_enable_global_link_color', false ) ){
               $styles.= '#tpf_t_builder .thwecmf-block a{
                    color: '.self::get_template_global_css('link-color').';
                    text-decoration: '.self::get_template_global_css('link-decoration').';
                }';
        }
        $styles.= '.thwecmf_downloadable_table{border:1px solid #e5e5e5;}';
        $styles.= '.thwecmf_downloadable_table th,.thwecmf_downloadable_table td{ font-size: 14px;line-height:150%;font-family: "Helvetica Neue", Helvetica, Roboto, Arial, sans-serif;padding:12px;}';
		return $styles;
	}

	/**
	 * Check if the action is template edit action
	 *
	 * @param  string $page page slug
	 * @return boolean template edit action or not
	 */
	public static function edit_template( $page ){
		if( $page == 'thwecmf_email_customizer' && isset( $_POST['i_edit_template'] ) ){
			return true;
		}
		return false;
	}

	/**
	 * Check if template file exists or not
	 *
	 * @return boolean template file existence
	 */
	public static function get_status(){
		$filename = isset( $_POST['i_template_name'] ) ? sanitize_text_field( $_POST['i_template_name'] ) : false;
		if( $filename ){
			$file = esc_url( rtrim(THWECMF_CUSTOM_T_PATH, '/') ).'/'.$filename.'.php';
			if( file_exists( $file ) ){
				return true;
			}
		}
		return false;
	}

	/**
	 * Get order table column css
	 *
	 * @param  boolean $json whether to json encode or not
	 * @return string||array order table column style
	 */
	public static function get_ot_td_css( $json=false ){
		$content = array("details_color"=>"#636363","details_text_align"=>"left","details_font_size"=>"14px","details_line_height"=>"150%","details_font_weight"=>"","details_font_family"=>"helvetica","content_p_t"=>"12px","content_p_r"=>"12px","content_p_b"=>"12px","content_p_l"=>"12px","content_border_color"=>"#e5e5e5");
		return $json ? json_encode( $content ) : $content;
	}

    /**
     * Font family list
     *
     * @return array font family list
     */
    public static function font_family_list(){
        return array(
            "helvetica"     =>  "'Helvetica Neue',Helvetica,Roboto,Arial,sans-serif",
            "georgia"       =>  "Georgia, serif",
            "times"         =>  "'Times New Roman', Times, serif",
            "arial"         =>  "Arial, Helvetica, sans-serif",
            "arial-black"   =>  "'Arial Black', Gadget, sans-serif",
            "comic-sans"    =>  "'Comic Sans MS', cursive, sans-serif",
            "impact"        =>  "Impact, Charcoal, sans-serif",
            "tahoma"        =>  "Tahoma, Geneva, sans-serif",
            "trebuchet"     =>  "'Trebuchet MS', Helvetica, sans-serif",
            "verdana"       =>  "Verdana, Geneva, sans-serif",
        );
    }

	/**
	 * Check if current email template is an allowed template
	 *
	 * @param  string $email email template name
	 * @return boolean compatible email or not
	 */
	public static function is_compatible_email( $email ){
		if( in_array( $email->id, self::THWECMF_EMAIL_INDEX ) ){
			return true;
		}
		return false;
	}

	/**
	 * Get preview directory path
	 *
	 * @return string directory path
	 */
    public static function get_template_preview_directory(){
    	return esc_url( THWECMF_CUSTOM_T_PATH.'/preview' );
    }

	/**
	 * Get WooCommerce orders
	 *
	 * @return array orders
	 */
	public static function get_woo_orders(){
		$count = apply_filters( 'thwec_template_preview_order_count', 5 );
		$orders = new WP_Query(
			array(
				'post_type'      => 'shop_order',
				'post_status'    => array_keys( wc_get_order_statuses() ),
				'posts_per_page' => $count,
			)
		);
		$order_objects = [];
		if ( $orders->posts ) {
			foreach ( $orders->posts as $order ) {
				$order_objects[] = wc_get_order( $order->ID );
			}
		}
		return $order_objects;
	}

    /**
     * Global link styles to be used in email template
     *
     * @return string style
     */
    public static function get_template_global_css( $type ){
        $css = '';
        if( $type == 'link-color' ){
            $link_color = '#1155cc';
            $link_color = apply_filters('thwecmf_template_link_color', sanitize_hex_color( $link_color ) );
            $css = is_null( $link_color ) ? '#1155cc' : $link_color;

        }else if( $type == 'link-decoration' ){
            $css = apply_filters('thwecmf_template_link_decoration', sanitize_text_field( 'underline' ) );
        }
        
        return $css;
    }

	public static function dump( $str ){
		?>
		<pre>
			<?php echo var_dump($str); ?>
		</pre>
		<?php
	}

}
endif;