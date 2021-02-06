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
	private $test_email_override;
	private static $css_elm_props_map;
	const OPTION_KEY_TEMPLATE_SETTINGS = 'thwecmf_template_settings';
	const SETTINGS_KEY_TEMPLATE_LIST = 'templates';
	const SETTINGS_KEY_TEMPLATE_MAP = 'template_map';
	const OPTION_KEY_ADVANCED_SETTINGS = 'thwecmf_advanced_settings';
	const OPTION_KEY_WECMF_MISC = 'thwecmf_misc_settings';

	public function __construct() {
		$this->test_email_override = apply_filters('thwecmf_enable_test_mail_save',false);
	}

	public static function css_elm_props_mapping(){
		$elm_css_map = array(
    		'text'	=> array(
    			'.thwecmf-block-text'	=>	array(
    				'color', 'align', 'font_size', 'size_width', 'size_height', 'm_t', 'm_r', 'm_b', 'm_l', 'text_align'
    			),
    			'.thwecmf-block-text .thwecmf-block-text-holder'	=>	array(
    				'color', 'font_size', 'text_align', 'bg_color', 'b_t', 'b_r', 'b_b', 'b_l', 'border_color', 'border_style', 'p_t', 'p_r', 'p_b', 'p_l'
    			),
    			'.thwecmf-block-text *'		=>	array(
    				'color', 'font_size'
    			),
    		),
    		'image'	=>	array(
    			'.thwecmf-block-image td.thwecmf-image-column' => array(
    				'content_align'
    			),
    			'.thwecmf-block-image td.thwecmf-image-column p' => array(
    				'img_size_width', 'img_size_height' 
    			),
    		),	
    		'billing_address'	=>	array(
    			'.thwecmf-block-billing .thwecmf-address-alignment'	=> array( 'align' ),
    			'.thwecmf-block-billing .thwecmf-address-wrapper-table'	=> array(
    				'size_width', 'size_height', 'bg_color', 'b_t', 'b_r', 'b_b', 'b_l', 'border_style', 'border_color', 'm_t', 'm_r', 'm_b', 'm_l'
    			),
    			'.thwecmf-block-billing .thwecmf-billing-padding'		=> array(
    				'p_t', 'p_r', 'p_b', 'p_l'
    			),
    			'.thwecmf-block-billing .thwecmf-billing-header'	=> array(
    				'font_size', 'color','text_align'
    			),
    			'.thwecmf-block-billing .thwecmf-billing-body'	=> array(
    				'details_font_size', 'details_color','details_text_align'
    			),
    		),
    		'shipping_address'	=>	array(
    			'.thwecmf-block-shipping .thwecmf-address-alignment'	=> array( 'align' ),
    			'.thwecmf-block-shipping .thwecmf-address-wrapper-table'	=> array(
    				'size_width', 'size_height', 'bg_color', 'b_t', 'b_r', 'b_b', 'b_l', 'border_style', 'border_color', 'm_t', 'm_r', 'm_b', 'm_l'
    			),
    			'.thwecmf-block-shipping .thwecmf-shipping-padding'		=> array(
    				'p_t', 'p_r', 'p_b', 'p_l'
    			),
    			'.thwecmf-block-shipping .thwecmf-shipping-header'	=> array(
    				'font_size', 'color','text_align'
    			),
    			'.thwecmf-block-shipping .thwecmf-shipping-body'	=> array(
    				'details_font_size', 'details_color','details_text_align'
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
    		't_builder'	=>	array(
    			'.thwecmf-main-builder .thwecmf-builder-column'	=> 	array(
    				'b_t', 'b_r', 'b_b', 'b_l', 'border_style', 'border_color', 'bg_color'
    			),
    		),
    	);
		return apply_filters('thwecmf_css_elm_props_mapping',$elm_css_map);
	}

	public static function thwecmf_woo_version_check( $version = '3.0' ) {
	  	if(function_exists( 'is_woocommerce_active' ) && is_woocommerce_active() ) {
			global $woocommerce;
			if( version_compare( $woocommerce->version, $version, ">=" ) ) {
		  		return true;
			}
	  	}
	  	return false;
	}

	public static function thwecmf_emogrifier_version_check( $version = '3.6' ) {
	  	if(function_exists( 'is_woocommerce_active' ) && is_woocommerce_active() ) {
			global $woocommerce;
			if( version_compare( $woocommerce->version, $version, ">" ) ) {
		  		return true;
			}
	  	}
	  	return false;
	}

	public static function prepare_template_name($display_name){
		$name = strtolower($display_name);
		$name = preg_replace('/\s+/', '_', $name);
		return $name;
	}

	public static function thwecmf_is_json_decode($data){
		$json_data = json_decode($data);
		$json_data = json_last_error() == JSON_ERROR_NONE ?  $json_data : false;
		return $json_data;
	}

	public static function is_user_capable(){
		$capable = false;
		$user = wp_get_current_user();
		$allowed_roles = apply_filters('thwecmf_user_capabilities_override', array('editor', 'administrator') );
		if( array_intersect($allowed_roles, $user->roles ) ) {
   			$capable = true;
   		}
   		return $capable;
	}

	public static function thwecmf_setup_initial_settings(){
		$settings = self::thwecmf_get_template_settings();
		if(isset($settings['templates']) && empty($settings['templates'])){
			$settings = self::thwecmf_save_template_settings(self::get_default_templates_json());
		}else{
			return true;
		}
		return $settings;
	}
	
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

	public static function thwecmf_get_template_list($settings=false){
		if(!is_array($settings)){
			$settings = self::thwecmf_get_template_settings();
		}
		return is_array($settings) && isset($settings[self::SETTINGS_KEY_TEMPLATE_LIST]) ? $settings[self::SETTINGS_KEY_TEMPLATE_LIST] : array();
	}

	public static function thwecmf_get_template_map($settings=false){
		if(!is_array($settings)){
			$settings = self::thwecmf_get_template_settings();
		}
		return is_array($settings) && isset($settings[self::SETTINGS_KEY_TEMPLATE_MAP]) ? $settings[self::SETTINGS_KEY_TEMPLATE_MAP] : array();
	}

	public static function thwecmf_reset_template_map(){
		$settings = self::thwecmf_get_template_settings();
		if( is_array($settings) && isset($settings[self::SETTINGS_KEY_TEMPLATE_MAP]) ){
			$settings[self::SETTINGS_KEY_TEMPLATE_MAP] = array();
		}
		return $settings;
		
	}

	public static function thwecmf_save_template_settings($settings, $new=false){
		$result = false;
		if($new){
			$result = add_option(self::OPTION_KEY_TEMPLATE_SETTINGS, $settings);
		}else{
			$result = update_option(self::OPTION_KEY_TEMPLATE_SETTINGS, $settings);
		}
		return $result;
	}

	public static function thwecmf_get_advanced_settings(){
		$settings = get_option(self::OPTION_KEY_ADVANCED_SETTINGS);
		return empty($settings) ? false : $settings;
	}
	
	public static function thwecmf_get_setting_value($settings, $key){
		if(is_array($settings) && isset($settings[$key])){
			return $settings[$key];
		}
		return '';
	}
	
	public static function thwecmf_get_settings($key){
		$settings = self::thwecmf_get_advanced_settings();
		if(is_array($settings) && isset($settings[$key])){
			return $settings[$key];
		}
		return '';
	}

	public static function get_wecmf_misc_settings($key){
		$settings = get_option(self::OPTION_KEY_WECMF_MISC);
		if(is_array($settings) && isset($settings[$key])){
			return $settings[$key];
		}
		return '';
	}

	public static function do_file_exist($file,$ext){
    	$extension = $ext ? $ext : 'php'; 
		$path = THWECMF_CUSTOM_T_PATH.$file.'.'.$extension;
    	return file_exists($path) ? true : false;
	}

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

	public static function thwecmf_get_templates( $name=false ){
		$path = TH_WECMF_PATH.'classes/inc/settings.txt';
		$content = file_get_contents( $path );
		$settings = unserialize(base64_decode($content));
		$settings = isset( $settings['templates'] ) ? $settings['templates'] : '';
		if( $name ){
			$settings = isset( $settings[$name] ) ? $settings[$name] : $settings;
		}
		return $settings;
	}

	public static function thwecmf_reset_templates( $template ){
		$reset = false;
		$db_settings = self::thwecmf_get_template_settings();
		$template_settings = self::thwecmf_get_templates();
		if( isset( $template_settings[$template] ) ){
			$db_settings['templates'][$template] = $template_settings[$template];
			$reset = self::thwecmf_save_template_settings( $db_settings);
		}
		return $reset;
	}	

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

	public static function is_valid_action(){
		$ajax_ref = check_ajax_referer( 'thwecmf_ajax_security', 'thwecmf_security', false);
		if( $ajax_ref && self::is_user_capable() ) {
			return true;
		}
		return false;
	}

	public static function get_logged_user_email(){
		$email = '';
	   	$current_user = wp_get_current_user();
		if( $current_user !== 0 ){
			$email =  $current_user->user_email;
		}
		return $email;
	}

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

	public static function dump( $str ){
		?>
		<pre>
			<?php echo var_dump($str); ?>
		</pre>
		<?php
	}

}
endif;