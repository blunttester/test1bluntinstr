<?php
/**
 * Woo Email Customizer Settings
 *
 * @author   ThemeHiGH
 * @category Admin
 */

if(!defined('ABSPATH')){ exit; }

if(!class_exists('WECMF_Settings')) :
class WECMF_Settings {
	protected static $_instance = null;	
	public $admin = null;
	public $frontend_fields = null;
	private $plugins_pages = null;

	public function __construct() {
		$required_classes = apply_filters('th_wecmf_require_class', array(
			'admin' => array(
				'classes/class-wecmf-settings-page.php',
				'classes/inc/class-wecmf-builder-settings.php',
				'classes/inc/class-wecmf-general-template.php',
				'classes/inc/class-wecmf-template-settings.php',
			),
			'common' => array(
				'classes/inc/class-wecmf-email-customizer-utils.php',
			),
		));
		
		$this->include_required( $required_classes );

		$this->plugin_pages = array(
			'toplevel_page_thwecmf_email_customizer',
		);
		
		add_action( 'admin_init', array( $this, 'collapse_admin_sidebar' ) );
		add_action( 'admin_init', array( $this, 'prepare_preview') );
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_filter( 'woocommerce_screen_ids', array( $this, 'add_screen_id' ) );
		add_filter( 'plugin_action_links_'.TH_WECMF_BASE_NAME, array($this, 'add_settings_link' ) );
		add_action( 'admin_body_class', array( $this, 'add_thwec_body_class') );
		$directory = $this->get_template_directory();
		!defined('THWECMF_CUSTOM_T_PATH') && define('THWECMF_CUSTOM_T_PATH', $directory);
		!defined('TH_WECMF_T_PATH') && define('TH_WECMF_T_PATH', TH_WECMF_PATH.'classes/inc/templates/');
		$this->init();
		!defined('THWECMF_LOGIN_USER') && define('THWECMF_LOGIN_USER', WECMF_Email_Customizer_Utils::get_logged_user_email());
	}

	public static function instance() {
		if(is_null(self::$_instance)){
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function prepare_preview(){
		if( isset( $_GET['preview'] ) ){
			$order_id = isset( $_GET['id'] ) ? absint( base64_decode( $_GET['id'] ) ) : false;
			$email_index = isset( $_GET['email'] ) ? sanitize_text_field( base64_decode( $_GET['email'] ) ) : false;
			$template = isset($_GET['preview']) ? sanitize_text_field( base64_decode( $_GET['preview'] ) ) : '';
			$content = $this->admin_instance->prepare_preview( $order_id, $email_index, $template, true );
			echo $this->render_preview( $content );
			die;
		}
	}

	public function render_preview( $content ){
		?>
		<html>
			<head>
				<title>Preview - Email Customizer for WooCommerce (Themehigh)</title>
				<style>
					body{
						margin: 0;
					}
				</style>
				
			</head>
			<body>
				<?php echo $content; ?>
				<script>
					var links = document.getElementsByClassName('thwecmf-link');
					var email = '';
					for (var i = 0; i < links.length; i++){
						email = links[i].innerHTML;
						links[i].innerHTML = '<a href="mailto:'+esc_attr( email )+'">'+esc_html( email )+'</a>';
					}
				</script>
			</body>
		</html>
		<?php
	}

	protected function get_template_directory(){
	    $upload_dir = wp_upload_dir();
	    $dir = $upload_dir['basedir'].'/thwec_templates';
      	$dir = trailingslashit($dir);
      	return $dir;
	}

	protected function include_required( $required_classes ) {
		foreach($required_classes as $section => $classes ) {
			foreach( $classes as $class ){
				if('common' == $section  || ('frontend' == $section && !is_admin() || ( defined('DOING_AJAX') && DOING_AJAX) ) 
					|| ('admin' == $section && is_admin()) && file_exists( TH_WECMF_PATH . $class )){
					require_once( esc_url( TH_WECMF_PATH ) . $class );
				}
			}
		}
	}

	public function init() {	
		if(is_admin()){
			$this->admin_instance = WECMF_General_Template::instance();
		}
		add_filter('woocommerce_locate_template', array($this, 'thwecmf_woo_locate_template'), 999, 3);	
		add_filter('woocommerce_email_styles', array($this, 'thwecmf_woocommerce_email_styles') );
	}

	public function wecmf_capability() {
		$allowed = array('manage_woocommerce', 'manage_options');
		$capability = apply_filters('thwecmf_required_capability', 'manage_woocommerce');

		if(!in_array($capability, $allowed)){
			$capability = 'manage_woocommerce';
		}
		return $capability;
	}

	public function admin_menu() {
		global $wp;
		
		$page  = isset( $_GET['page'] ) ? esc_attr( $_GET['page'] ) : 'thwecmf_email_customizer';

		$capability = $this->wecmf_capability();
		$this->screen_id = add_menu_page(esc_attr__('Email Customizer'), esc_attr__('Email Customizer'), esc_html( $capability ), 'thwecmf_email_customizer', array($this, 'output_settings'), 'dashicons-admin-customizer', 56);

		add_action('admin_print_scripts', array($this, 'disable_admin_notices'));
		add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
	}

	public function collapse_admin_sidebar(){
		$page = isset( $_GET['page'] ) ? sanitize_text_field( $_GET['page'] ) : false;
		if( WECMF_Email_Customizer_Utils::edit_template( $page ) ){
			if( get_user_setting('mfold') != 'f' ){
				set_user_setting('mfold', 'f');
			}
		}else{
			set_user_setting('mfold', 'o');
		}
	}

	public function add_screen_id($ids){
		$ids[] = 'woocommerce_page_thwecmf_email_customizer';
		$ids[] = strtolower(__('WooCommerce', 'woocommerce')) .'_page_thwecmf_email_customizer';
		return $ids;
	}
	
	public function add_settings_link($links) {
		$settings_link = '<a href="'.admin_url('admin.php?page=thwecmf_email_customizer').'">'. esc_html__('Settings') .'</a>';
		array_unshift($links, $settings_link);
		return $links;
	}

	public function output_settings() {
		$page  = isset( $_GET['page'] ) ? sanitize_text_field( $_GET['page'] ) : 'thwecmf_email_customizer';

		if( WECMF_Email_Customizer_Utils::edit_template( $page ) ){
			$fields_instance = WECMF_General_Template::instance();	
			$fields_instance->render_page();

		}else{	
			$fields_instance = WECMF_Template_Settings::instance();	
			$fields_instance->render_page();	

		}
	}

	public function disable_admin_notices(){
		$page  = isset( $_GET['page'] ) ? sanitize_text_field( $_GET['page'] ) : '';
		if( WECMF_Email_Customizer_Utils::edit_template( $page ) ){
			global $wp_filter;
      		if (is_user_admin() ) {
        		if (isset($wp_filter['user_admin_notices'])){
            		unset($wp_filter['user_admin_notices']);
        		}
      		} elseif(isset($wp_filter['admin_notices'])){
            	unset($wp_filter['admin_notices']);
      		}
      		if(isset($wp_filter['all_admin_notices'])){
        		unset($wp_filter['all_admin_notices']);
      		}
		}
	}

	public function thwecmf_woo_locate_template($template, $template_name, $template_path){
		$template_map = WECMF_Email_Customizer_Utils::thwecmf_get_template_map();
		if($template_map && strpos($template_name, 'emails/') !== false){ 
		    $search = array('emails/', '.php');
            $replace = array('', '');
		    $template_name_new = str_replace($search, $replace, $template_name);
			if(array_key_exists($template_name_new, $template_map)) {
    			$template_name_new = $template_map[$template_name_new];
    			if($template_name_new != ''){  
        			$email_template = $this->get_email_template($template_name_new);  
    				if($email_template){
    					return $email_template;
    				}
    			}		
    		}
    	}
       	return $template;
	}

	public function thwecmf_woocommerce_email_styles($buffer){
		$styles = WECMF_Email_Customizer_Utils::get_thwecmf_styles();
		return $buffer.$styles;
	}
	
	public function get_email_template($t_name){
    	$path = false;

    	$path = $this->get_email_template_path( $t_name );
    	if( $path ){
    	   	return $path;
    	}
    	
    	$path = $this->get_email_template_path( $t_name, true );
    	if( $path ){
    		return $path;
    	}

    	return $path;
    }

    public function get_email_template_path( $name, $default=false ){
    	$path = $default ? esc_url( TH_WECMF_T_PATH ).$name.'.php' : esc_url( THWECMF_CUSTOM_T_PATH ).$name.'.php';
    	return file_exists( $path ) ? $path : false;
    }

    public function render_advanced_content(){
    	?>
    	<div id="wecmf_builder_page_disabled">
    		<div class="wecmf-feature-access-wrapper">
    			<div class="wecmf-feature-access">
    				<p><b>Upgrade to Premium to access this feature</b></p>
    				<p>Goto <a href=>Templates</a> to edit and assign templates to email status</p>
    			</div>
    		</div>
    	</div>
    	<?php
    }

    public function add_thwec_body_class( $classes ){
		$page = isset( $_GET['page'] ) ? sanitize_text_field( $_GET['page'] ) : false;
		if( $page === 'thwecmf_email_customizer' ){
			$classes .= isset( $_POST['i_edit_template'] ) ? ' thwecmf-builder-page' : ' thwecmf-template-page';
		}
		return $classes;
	}

	public function enqueue_admin_scripts($hook){
		if(!in_array($hook, $this->plugin_pages)){
			return;
		}
		
		wp_enqueue_media();
		wp_enqueue_style (array('woocommerce_admin_styles', 'jquery-ui-style'));
		wp_enqueue_style ('thwecmf-admin-style', plugins_url('/assets/css/thwecmf-admin.min.css', dirname(__FILE__)), array(), TH_WECMF_VERSION);
		wp_enqueue_style('wp-color-picker');
		wp_enqueue_style('raleway-style','https://fonts.googleapis.com/css?family=Raleway:400,600,800');
		wp_enqueue_script('thwecmf-admin-script', plugins_url('/assets/js/thwecmf-admin.min.js', dirname(__FILE__)), 
		array('jquery', 'jquery-ui-core', 'jquery-ui-draggable', 'jquery-ui-droppable', 'jquery-ui-sortable', 'jquery-ui-dialog', 'jquery-tiptip', 'wc-enhanced-select', 'select2', 'wp-color-picker'), TH_WECMF_VERSION, true);

		$wecmf_var = array(
            'admin_url' 	=> admin_url(),
            'ajaxurl'   	=> admin_url( 'admin-ajax.php' ),
            'ajax_nonce' 	=> wp_create_nonce('thwecmf_ajax_security'),
            'ajax_banner_nonce' => wp_create_nonce('thwecmf_banner_ajax_security'),
            'elm_css_map' 	=> WECMF_Email_Customizer_Utils::css_elm_props_mapping(),
            'props'			=> WECMF_Email_Customizer_Utils::css_elm_props(),
            'tstatus'		=> WECMF_Email_Customizer_Utils::get_status(),
            'template' 		=> isset( $_POST['i_template_name'] ) ? sanitize_text_field( $_POST['i_template_name'] ) : '',
            'preview_order' => wp_create_nonce( 'thwecmf_preview_order' ),
            'reset_preview' => wp_create_nonce('thwecmf_reset_preview'),
        );
		wp_localize_script('thwecmf-admin-script', 'thwecmf_admin_var', $wecmf_var);
	}
}
endif;