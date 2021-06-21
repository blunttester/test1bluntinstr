<?php
/**
 * Email Customizer for WooCommerce Builder Functions
 *
 * @author    ThemeHiGH
 * @category  Admin
 */

if(!defined('ABSPATH')){ exit; }

if(!class_exists('WECMF_Builder_Settings')):
class WECMF_Builder_Settings extends WECMF_Settings_Page {
	protected static $_instance = null;
	private $cell_props_T = array();
	private $cell_props_FT = array();
	private $cell_props_4T = array();
	private $cell_props_S  = array();
	private $json_css_class = array();
	private $template_json_css = '';
	private $default_css = array();
	private $css_props = array();
	private $css_elm_props_map = array();
	private $field_props = array();
	private $template_display_name = '';
	private $thwecmf_templates = array();
	private $font_family_list = array();
	private $cell_props_2I = array();

	public function __construct() {
		parent::__construct();
		$this->get_field_form_props();
	}

	public static function instance() {
		if(is_null(self::$_instance)){
			self::$_instance = new self();
		}
		return self::$_instance;
	}	

	public function get_field_form_props(){
		$this->cell_props_T = array( 
			'input_width' => '136px',
			'input_height' => '30px',
			'input_b_r' => '4px',
			'input_font_size' => '13px',  
		);

		$this->cell_props_T = array( 
			'input_width' => '279px',
			'input_height' => '30px',
			'input_b_r' => '4px',
			'input_font_size' => '13px',  
		);

		$this->cell_props_4T = array( 
			'label_cell_props' => 'style="width:13%"', 
			'input_cell_props' => 'style="width:34%"', 
		);
		
		$this->cell_props_S = array( 
			'input_width' => '136px', 
			'input_b_r' => '4px', 
			'input_font_size' => '13px', 
		);

		$this->json_css_class = array(
			'row'		=> 'thwecmf-row',
			'column'	=> 'thwecmf-column-padding',
		);

		$this->cell_props_2I = array('input_width' => '136px','input_margin' => '0px 6px 0px 0px', 'input_height' => '30px', 'input_b_r' => '4px', 'input_font_size' => '13px');

		$this->template_json_css = '';

		$this->default_css = array( 
        	'color' 			=> 'transparent',
        	'background-color' 	=> 'transparent',
        	'border-color'		=> 'transparent',
        	'background-color' 	=> 'transparent',
        	'padding-top' 		=> '0px',
        	'padding-right'  	=> '0px',
        	'padding-bottom' 	=> '0px',
        	'padding-left' 		=> '0px',
        	'background-image' 	=> 'none',
        );

        $this->font_family_list = array(
			"helvetica" 	=> 	"'Helvetica Neue',Helvetica,Roboto,Arial,sans-serif",
        	"georgia" 		=> 	"Georgia, serif",
        	"times" 		=> 	"'Times New Roman', Times, serif",
        	"arial" 		=> 	"Arial, Helvetica, sans-serif",
        	"arial-black" 	=> 	"'Arial Black', Gadget, sans-serif",
        	"comic-sans" 	=> 	"'Comic Sans MS', cursive, sans-serif",
        	"impact" 		=> 	"Impact, Charcoal, sans-serif",
        	"tahoma"	 	=> 	"Tahoma, Geneva, sans-serif",
        	"trebuchet" 	=> 	"'Trebuchet MS', Helvetica, sans-serif",
        	"verdana" 		=>	"Verdana, Geneva, sans-serif",
		);

		$this->css_props = array(  
	        'p_t'=>'padding-top',
	        'p_r'=>'padding-right',
	        'p_b'=>'padding-bottom',
	        'p_l'=>'padding-left', 
	        'm_t'=>'margin-top',
	        'm_r'=>'margin-right',
	        'm_b'=>'margin-bottom',
	        'm_l' => 'margin-left',
	        'width' => 'width',
	        'height' => 'height',
	        'size_width' => 'width',
	        'size_height' => 'height',
	        'b_t' => 'border-top',
	        'b_r' => 'border-right',
	        'b_b' => 'border-bottom',
	        'b_l' => 'border-left',
	        'border_style' => 'border-style',
	        'border_color' => 'border-color',
	        'bg_color'	=> 'background-color',
	        'upload_img_url' => 'display',
	        'color' => 'color',
	        'font_size' => 'font-size',
	        'line_height' => 'line-height',
	        'font_weight' => 'font-weight',
	        'font_family' => 'font-family',
	        'text_align' => 'text-align',
	        'align' => 'float',
	        'content_align' => 'text-align',
	        'img_width' => 'width',
	        'img_height' => 'height',
	        'img_size_width' => 'width',
	        'img_size_height' => 'height',
	        'details_color' => 'color',
	        'details_font_size' => 'font-size',
	        'details_text_align' => 'text-align',
	        'details_font_family' => 'font-family',
	        'divider_height' => 'border-top-width',
	        'divider_color' => 'border-top-color',
	        'divider_style' => 'border-top-style',
	        'url' => 'display',
	        'upload_bg_url' => 'background-image',
	        'img_bg_color'	=> 'background-color',
	        'img_p_t' => 'padding-top',
	        'img_p_r' => 'padding-right',
	        'img_p_b' => 'padding-bottom',
	        'img_p_l' => 'padding-left',
			'icon_p_t' => 'padding-top',
			'icon_p_r' => 'padding-right',
			'icon_p_b' => 'padding-bottom',
			'icon_p_l' => 'padding-left',
			'url1' => 'display',
	        'url2' => 'display',
	        'url3' => 'display',
	        'url4' => 'display',
	        'url5' => 'display',
	        'url6' => 'display',
	        'url7' => 'display',
	        'content_p_t' => 'padding-top',
	        'content_p_r' => 'padding-right',
	        'content_p_b' => 'padding-bottom',
	        'content_p_l' => 'padding-left',
	        'product_img' => 'display',
	        'content_border_color' => 'border-color'
    	);

		$this->css_elm_props_map = WECMF_Email_Customizer_Utils::css_elm_props_mapping();
		$text_align = array('left' => 'Left','center' => 'Center','right' => 'Right');
		$float_align = array('left' => 'Left', 'right' => 'Right', 'none' => 'Center');
		$divider_options = array('dotted' => 'Dotted','solid' => 'Line','dashed' => 'Dashed');
		$font_list = array(
			'helvetica' => 'Helvetica',
			'georgia' => 'Georgia',
			'times' => 'Times New Roman',
			'arial' => 'Arial',
			'arial-black' => 'Arial Black',
			'comic-sans'=>'Comic Sans MS',
			'impact'=>'Impact',
			'tahoma'=>'Tahoma',
			'trebuchet'=>'Trebuchet MS',
			'verdana'=>'Verdana'
		);
		$border_style = array(
			'solid'=>'solid', 
			'dotted'=>'dotted', 
			'dashed'=>'dashed', 
			'none'=>'none',
		);
		$rad_options = array(
			'text'=>'Text',
			'html'=>'Html',
		);
		$this->field_props = array(
			'width' => array('type'=>'text', 'name'=>'width', 'label'=>'Width', 'value'=>''),
			'height' => array('type'=>'text', 'name'=>'height', 'label'=>'Height', 'value'=>''),
			'padding' => array('type'=>'fourside', 'name'=>'padding', 'label'=>'Padding', 'value'=>''),
			'margin' => array('type'=>'fourside', 'name'=>'margin', 'label'=>'Margin', 'value'=>''),			
			'img_width' => array('type'=>'text', 'name'=>'img_width', 'label'=>'Image Width', 'value'=>''),
			'img_height' => array('type'=>'text', 'name'=>'img_height', 'label'=>'Image Height', 'value'=>''),
			'img_size' => array('type'=>'twoside', 'name'=>'img_size', 'label'=>'Size', 'value'=>''),
			'img_size_range' => array('type'=>'range', 'name'=>'img_size_range', 'label'=>'Size', 'min'=>'10', 'max' => '100', 'value' => '50', 'class' => 'thwecmf-slider'),
			'icon_padding' => array('type'=>'fourside', 'name'=>'icon_padding', 'label'=>'Icon Padding', 'value'=>''),
			'img_border_radius' => array('type'=>'text', 'name'=>'img_border_radius', 'label'=>'Border Radius', 'value'=>''),
			'border_width' => array('type'=>'fourside', 'name'=>'border_width', 'label'=>'Border Width', 'value'=>''),
			'border_style' => array('type'=>'select', 'name'=>'border_style', 'label'=>'Border Style', 'options'=>$border_style),
			'border_color' => array('type'=>'colorpicker', 'name'=>'border_color', 'label'=>'Border Color', 'value'=>'','placeholder'=>'Color'),
			'border_radius' => array('type'=>'text', 'name'=>'border_radius', 'label'=>'Border Radius', 'value'=>''),
			'divider_height' => array('type'=>'text', 'name'=>'divider_height', 'label'=>'Divider Height', 'value'=>''),
			'divider_color' => array('type'=>'colorpicker', 'name'=>'divider_color', 'label'=>'Divider Color', 'value'=>'','placeholder'=>'Color'),
			'divider_style' => array('type'=>'select', 'name'=>'divider_style', 'label'=>'Divider Style', 'options'=>$border_style),
			'bg_color' => array('type'=>'colorpicker', 'name'=>'bg_color', 'label'=>'Color', 'placeholder'=>'Color', 'value'=>''),
			'url' => array('type'=>'text', 'name'=>'url', 'label'=>'URL', 'value'=>''),
			'upload_bg_url' => array('type'=>'hidden', 'name'=>'upload_bg_url', 'label'=>'', 'value'=>'','class'=>'thwecmf-upload-url'),
			'upload_img_url' => array('type'=>'hidden', 'name'=>'upload_img_url', 'label'=>'', 'value'=>'','class'=>'thwecmf-upload-url'),
			'title' => array('type'=>'text', 'name'=>'title', 'label'=>'Title', 'value'=>''),
			'content' => array('type'=>'text', 'name'=>'content', 'label'=>'Content', 'value'=>''),
			'textarea_content' => array('type'=>'textarea', 'name'=>'textarea_content', 'label'=>'Content', 'value'=>''),
			'color' => array('type'=>'colorpicker', 'name'=>'color', 'label'=>'Color', 'value'=>'', 'placeholder'=>'Color',),
			'font_size' => array('type'=>'text', 'name'=>'font_size', 'label'=>'Size', 'value'=>'', 'placeholder'=>'Size'),
			'details_color' => array('type'=>'colorpicker', 'name'=>'details_color', 'label'=>'Color', 'value'=>'','placeholder'=>'Color'),
			'details_font_size' => array('type'=>'text', 'name'=>'details_font_size', 'label'=>'Font Size', 'value'=>'','placeholder'=>'Size'),
			'content_align' => array('type'=>'alignment-icons', 'name'=>'content_align', 'label'=>'Alignment', 'class'=>'thwecmf-text-align-input', 'options'=>$float_align,'icon_flag'=>false),
			'text_align' => array('type'=>'alignment-icons', 'name'=>'text_align', 'label'=>'Text align', 'class'=>'thwecmf-text-align-input', 'icon_flag'=>true, 'options'=>$text_align),
			'details_text_align' => array('type'=>'alignment-icons', 'name'=>'details_text_align', 'label'=>'Text align', 'class'=>'thwecmf-text-align-input','icon_flag'=>true, 'options'=>$text_align),
			'textareacontent' => array('type'=>'textarea', 'name'=>'textareacontent', 'label'=>'Content', 'value'=>''),
			'bg_image' => array('type'=>'text', 'name'=>'bg_image', 'label'=>'Image', 'value'=>'',),

			'size' => array('type'=>'twoside', 'name'=>'size', 'label'=>'Size', 'value'=>''),
			'content_size' => array('type'=>'twoside', 'name'=>'content_size', 'label'=>'Size', 'value'=>'', 'class' => 'thwecmf-premium-disabled-input', 'hint_text'=>'Premium Feature'),
			'vertical_align' => array('type'=>'select', 'name'=>'vertical_align', 'label'=>'Vertical Align', 'options'=>array('top'=>'Top'), 'class' => 'thwecmf-premium-disabled-input'),
			'img_padding' => array('type'=>'fourside', 'name'=>'img_padding', 'label'=>'Image Padding', 'value'=>''),
			'img_border_width' => array('type'=>'fourside', 'name'=>'img_border_width', 'label'=>'Border Width', 'value'=>''),
			'img_border_style' => array('type'=>'select', 'name'=>'img_border_style', 'label'=>'Border Style', 'options'=>$border_style),
			'img_border_color' => array('type'=>'colorpicker', 'name'=>'img_border_color', 'label'=>'Border Color', 'value'=>'','placeholder'=>'Color'),
			'img_bg_color' => array('type'=>'colorpicker', 'name'=>'img_bg_color', 'label'=>'BG Color', 'value'=>'','placeholder'=>'Color'),
			
			'font_weight' => array('type'=>'text', 'name'=>'font_weight', 'label'=>'Weight', 'value'=>'','placeholder'=>'Font Weight'),
			'font_family' => array('type'=>'select', 'name'=>'font_family', 'label'=>'Family', 'options'=> $font_list),
			'line_height' => array('type'=>'text', 'name'=>'line_height', 'label'=>'Line Height', 'value'=>'','placeholder'=>'Line height'),
			'details_font_weight' => array('type'=>'text', 'name'=>'details_font_weight', 'label'=>'Font Weight', 'value'=>'','placeholder'=>'Font weight'),
			'details_line_height' => array('type'=>'text', 'name'=>'details_line_height', 'label'=>'Line Height', 'value'=>'','placeholder'=>'Line height'),
			'details_font_family' => array('type'=>'select', 'name'=>'details_font_family', 'label'=>'Font Family', 'options'=> $font_list),
			'align' => array('type'=>'alignment-icons', 'name'=>'align', 'label'=>'Alignment', 'class'=>'thwecmf-text-align-input', 'icon_flag'=>false, 'class' => 'thwecmf-premium-disabled-input', 'hint_text'=>'Premium Feature'),
			'border_spacing' => array('type'=>'text', 'name'=>'border_spacing', 'label'=>'Column Spacing', 'class' => 'thwecmf-premium-disabled-input'),
			'url1'	=> array(
				'type'=>'text', 'name'=>'url1', 'label'=>'Facebook', 'value'=>''
			),
			'url2'	=> array(
				'type'=>'text', 'name'=>'url2', 'label'=>'Gmail', 'value'=>''
			),
			'url3'	=> array(
				'type'=>'text', 'name'=>'url3', 'label'=>'Twitter ', 'value'=>''
			),
			'url4'	=> array(
				'type'=>'text', 'name'=>'url4', 'label'=>'Youtube', 'value'=>''
			),
			'url5'	=> array(
				'type'=>'text', 'name'=>'url5', 'label'=>'Linkedin', 'value'=>''
			),
			'url6'	=> array(
				'type'=>'text', 'name'=>'url6', 'label'=>'Pinterest', 'value'=>''
			),
			'url7'	=> array(
				'type'=>'text', 'name'=>'url7', 'label'=>'Instagram', 'value'=>''
			),
			'content_padding' => array('type'=>'fourside', 'name'=>'content_padding', 'label'=>'Content Padding', 'value'=>''),
			'checkbox_option_image'		=> array('type'=>'checkbox','name'=>'checkbox_option_image','label'=>'Product Image','checked'=>0),
			'content_bg_color' => array('type'=>'colorpicker', 'name'=>'content_bg_color', 'label'=>'Color', 'value'=>'','placeholder'=>'Color'),
			'content_border_color' => array('type'=>'colorpicker', 'name'=>'content_border_color', 'label'=>'Border Color', 'value'=>'','placeholder'=>'Color'),
		);

		$this->thwecmf_templates = array(
			'admin_new_order',		
			'admin_cancelled_order',
			'admin_failed_order',	
			'customer_completed_order',
			'customer_on_hold_order',
			'customer_processing_order',
			'customer_refunded_order',
			'customer_invoice',
			'customer_note',
			'customer_reset_password',
			'customer_new_account',
		);
	}

	/**
     * Save the template mapping
     *
	 * @param  string $t_name template name of chosen template
	 * @return  array $template_data get chosen template data
     */
	private function get_template_data($t_name){
		$template_data = false;
		if($t_name){
			$t_list = WECMF_Email_Customizer_Utils::thwecmf_get_template_settings();
			if( WECMF_Email_Customizer_Utils::wecm_valid( $t_name, true ) ){
				$template_data = isset( $t_list['templates'][$t_name] ) ? WECMF_Email_Customizer_Utils::sanitize_template_data( $t_list['templates'][$t_name] ) : WECMF_Email_Customizer_Utils::thwecmf_get_templates($t_name);
			}else{
				$this->thwecmf_invalid_template();
			}
			$this->template_display_name = isset ( $template_data['display_name'] ) ? sanitize_text_field( $template_data['display_name'] ) : '';
			$template_data = isset( $template_data['template_data'] ) ? wp_kses_post( $template_data['template_data'] ) : '';

			if($template_data == '' || $this->template_display_name == '' ){
				$this->thwecmf_invalid_template();
			}
		}
		return $template_data;
	}

	/**
     * Redirect incase of invalid template
     *
     */
	private function thwecmf_invalid_template(){
		$url =  admin_url('admin.php?page=thwecmf_email_customizer');
		wp_redirect($url); 
	}

	/**
     * Get the display name of the chosen template
     *
	 * @param  string $t_name template name of chosen template
	 * @return string display name of the chosen template
     */
	private function get_temp_display_name($t_name){
		return $file_name = $t_name ? $t_name : "";
	}

	/**
     * Render the builder main page
     *
     */
	public function render_template_builder(){
		$template_details = isset($_POST['i_template_name']) ? sanitize_text_field($_POST['i_template_name']): false;
		if ( isset( $_POST['i_edit_template'] ) && $template_details ){
			if( !wp_verify_nonce( $_POST['thwecmf_edit_template_'.$template_details], 'thwecmf_edit_template'  ) || !WECMF_Email_Customizer_Utils::is_user_capable() ){
				wp_die( '<div class="wecm-wp-die-message">Action failed. Could not verify nonce.</div>' );
			}
		}
		$template_json = '';
		$template_name = '';
		if($template_details){
			$template_json = $this->get_template_data($template_details);
			$template_name = $this->get_temp_display_name($template_details);
		}
		?>
		<div id="thwecmf-template-builder-wrapper" class="thwecmf-tbuilder-wrapper">
			<?php
			$this->render_customizer_modal_block();
			$this->render_customizer_header_block();
			$this->render_customizer_preview_sidebar( $template_details );
			$this->render_customizer_editor_block($template_json);
			$this->render_customizer_sidebar_block($template_json);
			?>
			<div id="thwecmf_tbuilder_editor_preview" class="thwecmf-tbuilder-editor-preview" style="display: none;"></div>
		</div>	
		<?php
		$this->render_customizer_helper_elements();
	    $this->output_builder_sidebar_layout();
	    $this->output_builder_sidebar_layout_element();
	    $this->output_builder_sidebar_layout_settings();
		$this->render_template_elements();
		$this->template_test_email_box();
	}

	/**
     * Render the builder page sidebar
     *
     */
	public function render_customizer_preview_sidebar( $template_details ){
        ?>
        <div class="thwecmf-tbuilder-preview-sidebar">
        	<div class="thwecmf-preview-sidebar-content">
    			<input type="hidden" name="thwecmf_css" id="thwecmf_css" value="">
				<input type="hidden" name="thwecmf_template" id="thwecmf_template" value="<?php echo esc_attr( $template_details ); ?>">
	        	<?php
	        	$this->preview_sidebar_dropbox_woo_orders();
		        $this->preview_sidebar_dropbox_woo_emails();
		        $this->render_sidebar_buttons();
		        ?>
	    	</div>
        </div>
        <?php
    }

    /**
     * Render the builder main page preview WooCommerce orders dropdown
     *
     */
    public function preview_sidebar_dropbox_woo_orders(){
		$orders = WECMF_Email_Customizer_Utils::get_woo_orders();
		?>
		<div class="thwecmf-preview-sidebar-icons thwecmf-woo-orders">
			<select name="thwecmf_preview_order" id="thwecmf_preview_order" class="thwecmf-preview-filter">
            		<option value="">Select an Order</option>
	                <?php
	           	 	if( is_array( $orders ) ){
	                	foreach ($orders as $key => $order) {
	                    	$buyer = $this->get_buyer_info( $order );
	                    	$order_id = $order->get_id();
	                    	if( $buyer ){
								$user_string = sprintf( '(#%1$s) %2$s', $order_id, $buyer );
								echo '<option value="'.esc_attr( $order_id ).'">'. wp_kses_post( $user_string ) .'</option>';
	    					}
	                	}
	            	}
	            	?>
        		</select>
        	</div>
		<?php
	}

	/**
     * Get the buyers info from WooCommerce orders
     *
     * @param object $order WooCommerce order
     */
	public function get_buyer_info( $order ){
		$buyer = false;
		if ( $order->get_billing_first_name() || $order->get_billing_last_name() ) {
			$buyer = trim( sprintf( _x( '%1$s %2$s', 'full name', 'woocommerce' ), $order->get_billing_first_name(), $order->get_billing_last_name() ) );
		} elseif ( $order->get_billing_company() ) {
			$buyer = trim( $order->get_billing_company() );
		} elseif ( $order->get_customer_id() ) {
			$user  = get_user_by( 'id', $order->get_customer_id() );
			$buyer = ucwords( $user->display_name );
		}
		return $buyer;
	}

	/**
	 * Render the builder main page preview WooCommerce emails list
	 *
	 */
	public function preview_sidebar_dropbox_woo_emails(){
		$wc_emails = WC_Emails::instance();
		$wc_emails = isset( $wc_emails->emails ) ? $wc_emails->emails : false;
		?>
		<div class="thwecmf-preview-sidebar-icons thwecmf-woo-emails">
			<select name="thwecmf_preview_email" id="thwecmf_preview_email" class="thwecmf-preview-filter">
				<option value="">Choose an email</option>
				<?php 
				if( $wc_emails && is_array( $wc_emails ) ){
					foreach ($wc_emails as $wc_key => $wc_email) {
						if( !WECMF_Email_Customizer_Utils::is_compatible_email( $wc_email ) ){
							continue;
						}
						echo '<option value="'.esc_attr( $wc_key ).'">'.esc_html( $wc_email->title ).'</option>';
					}
				}
				?>
			</select>
		</div>
	<?php
	}

	/**
	 * Render Preview and Test Email button in preview sidebar
	 *
	 */
	public function render_sidebar_buttons(){
    	?>
    	<div class="thwecmf-preview-sidebar-icons" style="text-align: center;">
    		<button type="button" class="button thwecmf-preview" name="thwecmf_preview" onclick="thwecmfPreviewSidebarActions(this)">Preview</button>
    		<button type="button" class="button thwecmf-test-mail" name="thwecmf_test_mail" onclick="thwecmfPreviewSidebarActions(this)">Test Email</button>
    	</div>
    	<?php
    }

    /**
	 * Render builder page header
	 *
	 */
	private function render_customizer_header_block(){
		?>
		<div class="thwecmf-tbuilder-header-panel">
			<div class="thwecmf-tbuilder-main-actions">
				<?php $this->render_builder_header_panel(); ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Render builder page editor
	 *
	 * @param string $template_json chosen template settings json
	 */
	private function render_customizer_editor_block($template_json){
		?>
		<div class="thwecmf-tbuilder-editor-wrapper thwecmf-tbuilder-sub-wrapper">
			<?php $this->output_builder_editor_panel($template_json); ?>
		</div>
		<?php
	}

	/**
	 * Render builder page sidebar
	 *
	 * @param string $template_json chosen template settings json
	 */
	private function render_customizer_sidebar_block($template_json){
		?>
		<div id="thwecmf-sidebar-element-wrapper" class="thwecmf-tbuilder-elm-wrapper thwecmf-tbuilder-sub-wrapper">
			<?php $this->output_builder_sidebar($template_json); ?>
		</div>
		<?php
	}

	/**
	 * Render builder page helper elements
	 *
	 */
	private function render_customizer_helper_elements(){
		?>
		<div id="thwecmf-ajax-load-modal"></div>
		<div id="thwecmf_builder_save_messages"></div>
		<?php
	}

	/**
	 * Render builder page header items
	 *
	 */
	private function render_builder_header_panel(){
		$orders = WECMF_Email_Customizer_Utils::get_woo_orders();
		$selected = '';
		?>
		<div class="thwecmf-tbuilder-header-action-left">
			<div class="thwec-new-template-icon thwecmf-header-icons">
			 	<div id="thwecmf_header_sidebar_nav" class="thwecmf-header-icons-holder thwecmf-icon-box thwecmf-header-tab thwecmf-inactive-nav" onclick="thwecmfSidebarBackNavigation(this)">
					<span class="thwecmf-header-icon-inner thwecmf-header-tab">
						<span class="dashicons dashicons-arrow-left-alt"></span>
						<span class="text-content">Back</span>
					</span>
				</div>
				<div class="thwecmf-header-icons-holder active-tab thwecmf-icon-box thwecmf-header-tab" onclick="thwecBuilderPreviewTab(this)" data-tab="builder">
					<span class="thwecmf-header-icon-inner thwecmf-header-tab">
						<span class="dashicons dashicons-admin-customizer"></span>
						<span class="text-content">Builder</span>
					</span>
				</div>
				<div class="thwecmf-header-icons-holder thwecmf-icon-box thwecmf-header-tab" onclick="thwecBuilderPreviewTab(this)" data-tab="preview">
					<span class="thwecmf-header-icon-inner thwecmf-header-tab">
						<span class="dashicons dashicons-visibility"></span>
						<span class="text-content">Preview</span>
					</span>			
				</div>
				<div class="thwecmf-header-icons-holder thwecmf-icon-box thwecmf-template-action-button" id="thwecmf_template_save_button">
					<span class="thwecmf-header-icon-inner thwecmf-header-button" onclick="thwecmfSaveTemplate(this)">
						<span class="dashicons dashicons-index-card"></span>
						<span class="text-content">Save</span>
						<input type="hidden" id="thwecmf_template_save_name" name="thwecmf_template_name" value="<?php echo esc_html( $this->template_display_name ); ?>">
					</span>
				</div>
				<div class="thwecmf-header-icons-holder thwecmf-meta-display-box<?php echo empty( $this->template_display_name) ? ' thwecmf-empty-name' : ''; ?>">
					<span class="thwecmf-header-icon-inner">
						<span class="text-content"><?php echo esc_html( $this->template_display_name ); ?></span>
					</span>
				</div>
			</div>
		</div>
	<?php
	}

	/**
	 * Render builder page editor contents
	 *
	 * @param string $template_json chosen template settings json
	 */
	private function output_builder_editor_panel($template_json) {
		?>
		<div class="template-wrapper-settings thwecmf-icon-wrapper">
			<img src="<?php echo esc_url( TH_WECMF_ASSETS_URL.'images/settings-cogwheel.svg'); ?>" onclick="thwecmfEditBuilderBlocks(this, 't_builder', 't_builder')" alt="Template Builder Settings" title="Template Builder Settings">
		</div>
		<table class="thwecmf-tbuilder-editor-grid">
			<tr>
				<td class="thwecmf-tbuilder-editor">
					<div id="thwecmf_drag_n_drop" class="thwecmf-dropable-wrapper">
						<?php 
						if($template_json){
							$this->render_template_blocks_json($template_json);
						}else { ?>
							<table cellpadding="0" cellspacing="0" border="0" width="600" id="tbf_t_builder" class="thwecmf-dropable sortable thwecmf-main-builder" data-global-id="1000" data-track-save="1000" data-css-change="true" data-css-props='{"b_t":"1px","b_r":"1px","b_b":"1px","b_l":"1px","border_style":"solid","border_color":"#dedede","bg_color":"#ffffff","upload_bg_url":""}'>
								<tr>
									<td class="thwecmf-builder-column"></td>
								</tr>
							</table>
						<?php } ?>
					</div>
				</td>
			</tr>
		</table>
		<?php
		$this->render_template_builder_css_section('thwecmf_template_css');
		?>
		<?php
	}

	/**
	 * Render builder page siebar contents
	 *
	 * @param string $template_json chosen template settings json
	 */
	private function output_builder_sidebar($template_json){
		?>
		<div class="thwecmf-sidebar-body-wrapper">
			<div id="thwecmf-sidebar-configure" class="thwecmf-settings-panel-tabs thwecmf-active-tab">
				<?php $this->render_template_builder_panel_configure($template_json); ?>
			</div>
			<div id="thwecmf-sidebar-settings" class="thwecmf-settings-panel-tabs inactive-tab"></div>
		</div>
		<?php
	}

	/**
	 * Render builder layout sidebar elements
	 *
	 */
	private function output_builder_sidebar_layout(){
	?>
		<div id="thwecmf_builder_panel_layout" style="display:none;">
			<div class="wecmf-layout-panel-outer-wrapper">
				<div class="wecmf-layout-panel-inner-wrapper">
					<table class="thwecmf-tbuilder-elm-grid">
						<tbody>
							<tr>
								<td class="thwecmf-layout-note"><p>Pick the column layout for the row</p></td>
							</tr>
							<tr>
								<td class="thwecmf-elm-col">
									<div id="thwecmf-one-column" class="thwecmf-tbuilder-elm column_layout" data-block-name="one_column">
										<img src="<?php echo esc_url( TH_WECMF_ASSETS_URL.'images/one_column.png' );?>" alt="One column">
										<p>1 Column</p>
									</div>
								</td>
							</tr>
							<tr>
								<td class="thwecmf-elm-col">
									<div id="thwecmf-two-column" class="thwecmf-tbuilder-elm thwecmf_column_layout" data-block-name="two_column">
										<img src="<?php echo esc_url( TH_WECMF_ASSETS_URL.'images/two_column.png' );?>" alt="Two column">
										<p>2 Column</p>
									</div>
								</td>
							</tr>
							<tr>
								<td class="thwecmf-elm-col">
									<div id="thwecmf-three-column" class="thwecmf-tbuilder-elm thwecmf_column_layout" data-block-name="three_column">
										<img src="<?php echo esc_url( TH_WECMF_ASSETS_URL.'images/three_column.png' );?>" alt="Three column">
										<p>3 Column</p>
									</div>
								</td>
							</tr>
							<tr>
								<td class="thwecmf-elm-col">
									<div id="thwecmf-four-column" class="thwecmf-tbuilder-elm thwecmf_column_layout" data-block-name="four_column">
										<img src="<?php echo esc_url( TH_WECMF_ASSETS_URL.'images/four_column.png' );?>" alt="Four column">
										<p>4 Column</p>
									</div>
								</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	<?php
	}

	/**
	 * Render builder layout sidebar form
	 *
	 */
	private function output_builder_sidebar_layout_element(){
	?>
		<div id="thwecmf_template_builder_panel_layout_element" style="display:none;">
			<form id="thwecmf_tbuilder_layout_elm_form" method ="post" action="">
				<input type="hidden" name="i_thwecmf_block_id" value="">
				<input type="hidden" name="i_thwecmf_block_name" value="">
				<input type="hidden" name="i_thwecmf_col_count" value="">
				<div class="outer-wrapper">
					<div class="inner-wrapper">
					<?php $this->panel_layout_elements_list(); ?>
					</div>
				</div>
			</form>
		</div>
	<?php
	}

	/**
	 * Render builder sidebar element settings form 
	 *
	 */
	private function output_builder_sidebar_layout_settings(){
		?>
		<div id="thwecmf_builder_block_edit_form" class="thwecmf-tbuilder-elm-edit" style="display: none;">
			<form name="thwecmf_builder_block_form" class="thwecmf-block-settings-form popup_form_class">
				<input type="hidden" name="i_thwecmf_block_id" value="">
				<input type="hidden" name="i_thwecmf_block_name" value="">
				<input type="hidden" name="i_thwecmf_block_props" value="">
				<input type="hidden" name="i_thwecmf_popup_flag" value="">
				<div class="thwecmf_field_form_outer_wrapper">
					<div class="thwecmf_field_form_inner_wrapper">
						<table class="thwecmf_field_form_general" cellspacing="10px">
							<tr>
								<td class="thwecmf-general-form-td"></td>
							</tr>
						</table>
					</div>
				</div>
			</form>
		</div>
		<?php
	}

	/**
	 * Render builder element settings and elements
	 *
	 */
	private function render_template_elements(){
		$this->render_elm_pp_layout_rows();
		$this->render_elm_pp_layout_cols();
		$this->render_elm_pp_text();
		$this->render_elm_pp_image();
		$this->render_elm_pp_divider();
		$this->render_elm_pp_gap();
		$this->render_elm_pp_social();
		$this->render_elm_pp_button();
		$this->render_elm_pp_gif();
		$this->render_elm_pp_header();
		$this->render_elm_pp_customer();
		$this->render_elm_pp_order();
		$this->render_elm_pp_billing();
		$this->render_elm_pp_shipping();
		$this->render_elm_pp_builder();
		$this->render_elm_1_column_layout();
		$this->render_elm_2_column_layout();
		$this->render_elm_3_column_layout();
		$this->render_elm_4_column_layout();
		$this->render_elm_header_details();
		$this->render_elm_customer_address();
		$this->render_elm_order_details();
		$this->render_elm_billing();
		$this->render_elm_shipping();
		$this->render_elm_text();
		$this->render_elm_image();
		$this->render_elm_divider();
		$this->render_elm_gap();
		$this->render_elm_social();
		$this->render_elm_button();
		$this->render_elm_gif();
		$this->render_hook_email_header();
		$this->render_hook_email_order_details();
		$this->render_hook_before_order_table();
		$this->render_hook_after_order_table();
		$this->render_hook_order_meta();
		$this->render_hook_customer_details();
		$this->render_hook_email_footer();
		$this->render_hook_downloadable_product();
		$this->render_track_row_content();
		$this->render_track_col_content();
		$this->render_track_elm_content();
		$this->render_track_hook_content();
		$this->render_pp_confirmation_alerts();
		$this->render_pp_confirmation_msg_line();
	}

	/**
	 * Render sidebar settings form border section fragment
	 *
	 * @param boolean||string $content to be displayed as heading
	 * @param string $prefix sidebar setting form field prefix
	 */
	private function render_builder_elm_pp_fragment_border($content=false,$prefix=''){
		$atts = array('content' => 'Border Properties', 'padding-top' => '10px');
		if($content){
			$atts['content'] = $content;
		}
		?>
		<table class="thwecmf-edit-form">
			<thead class="thwecmf-toggle-section">
			<?php
			$this->render_form_fragment_h_separator($atts);
			?>
			</thead>
			<tbody>
				<?php
					$this->render_form_fields($this->field_props[$prefix.'border_width'], $this->cell_props_T);  

					$this->render_form_fields($this->field_props[$prefix.'border_style'], $this->cell_props_S); 
					$this->render_form_fields($this->field_props[$prefix.'border_color'], $this->cell_props_T);
				?>
			</tbody>
		</table>
	<?php
	}

	/**
	 * Render sidebar settings form background section fragment
	 *
	 * @param boolean||string $content to be displayed as heading
	 * @param string $prefix sidebar setting form field prefix
	 * @param string $bg_image whether to display background image upload section
	 */
	private function render_builder_elm_pp_fragment_bg($content=false,$prefix='', $bg_image=true){
		$atts = array('content' => 'Background Properties', 'padding-top' => '10px');
		if($content){
			$atts['content'] = $content;
		}
		$cell_props = array('input_width' => '100px');
		$cell_props_combo = array('input_width' => '89px','input_margin' => '0px 6px 0px 0px', 'input_height' => '30px', 'input_b_r' => '4px','input_font_size' => '13px');
		$cell_props_combo_S = array('input_width' => '89px','input_margin' => '-4px 0px 0px 0px', 'input_height' => '30px', 'input_b_r' => '4px', 'input_font_size' => '13px');
		?>
		<table class="thwecmf-edit-form">
			<thead class="thwecmf-toggle-section">
				<?php
				$this->render_form_fragment_h_separator($atts);
				?>
			</thead>
			<tbody>
				<?php
				$this->render_form_fields($this->field_props[$prefix.'bg_color']);
				?>
				<tr class="thwecmf-input-spacer"><td></td></tr>
				<?php
				if( $bg_image ){
					$this->render_builder_elm_pp_fragment_img_upload('bg_image','upload_bg_url');
					echo '<tr class="thwecmf-input-spacer"><td></td></tr>';
				}
				?>
			</tbody>
		</table>
		<?php
	}

	/**
	 * Render sidebar settings form image section fragment
	 *
	 * @param string $props to be displayed for background image section or image element settings
	 * @param string $url_type image url or background image url
	 */
	private function render_builder_elm_pp_fragment_img_upload($props,$url_type, $type='' ){
		?>
		<tr>
			<td>Upload <?php echo $type; ?>image</td>
		</tr>
		<tr>
			<td>
				<div class="thwecmf-upload-action-settings thwecmf-img-preview-<?php echo esc_attr( $props );?>">
					<div class="thwecmf-upload-preview" data-default-url ="<?php echo esc_url(TH_WECMF_ASSETS_URL.'images/placeholder.png' ); ?>">
						<img src="<?php echo esc_url( TH_WECMF_ASSETS_URL.'images/placeholder.png' ); ?>" alt="Upload Preview">
					</div>
					<input type="button" name="thwecmf_image_upload" value="Upload" class="thwecmf-upload-button button" onclick="thwecmfUploadImage(this, '<?php echo esc_attr( $props ); ?>' )">
					<input type="button" name="thwecmf_remove_upload" value="Remove" class="thwecmf-remove-upload-btn button thwecmf-remove-upload-inactive" data-status="false" onclick="thwecmfRemoveImgUploaded(this)">
					<?php
					$this->render_form_fields($this->field_props[$url_type],false,false);
					?>
				</div>
				<div class="thwecmf-upload-notices"></div>
			</td>
		</tr>
		<?php
	}

	/**
	 * Render sidebar settings form text section fragment
	 *
	 * @param string $prefix sidebar setting form field prefix
	 * @param boolean $content whether to show content field or not
	 * @param boolean $line whether to show line height field or not
	 * @param boolean $weight whether to show font weight field or not
	 * @param boolean $family whether to show font family field or not
	 */
	private function render_builder_elm_pp_fragment_text($prefix='',$content=true,$line=true,$weight=true,$family=true){
		$cell_props = array('input_width' => '100px');
		$cell_props_combo = array('input_width' => '136px','input_margin' => '0px 6px 0px 0px', 'input_height' => '30px', 'input_b_r' => '4px', 'input_font_size' => '13px');
		$cell_props_combo_L = array('input_width' => '136px', 'input_height' => '30px', 'input_b_r' => '4px', 'input_font_size' => '13px');
		?>   
		<?php 
		if($content){      
			$this->render_form_fields($this->field_props[$prefix.'content'], $this->cell_props_FT);
		}
		?>
		<tr class="thwecmf-input-spacer"><td></td></tr>
		<tr>
			<td>
				<?php
				$this->render_form_fields($this->field_props[$prefix.'color'], $cell_props_combo, false);
				$this->render_form_fields($this->field_props[$prefix.'font_size'], $cell_props_combo, false);
				?>
			</td>
		</tr>
		<?php       
		$this->render_form_fields($this->field_props[$prefix.'text_align'], $this->cell_props_S);
		if( $line || $weight ){
			echo '<tr class="thwecmf-input-spacer"><td></td></tr>';
		}
		?>
		<tr>
			<td>
				<?php
				if( $line ){
					$this->render_form_fields($this->field_props[$prefix.'line_height'], $cell_props_combo,false);
				}
				if($weight){
					$this->render_form_fields($this->field_props[$prefix.'font_weight'], $cell_props_combo_L,false);
				}
				?>
			</td>
		</tr>
		<?php
		if( $family ){
			$this->render_form_fields($this->field_props[$prefix.'font_family'], $this->cell_props_S);
		}
		?>
		<tr class="thwecmf-input-spacer"><td></td></tr>
		<?php
	}

	/**
	 * Render sidebar settings form text section fragment
	 *
	 * @param string $prefix sidebar setting form field prefix
	 * @param boolean $content whether to show content field or not
	 * @param boolean $line whether to show line height field or not
	 * @param boolean $weight whether to show font weight field or not
	 * @param boolean $family whether to show font family field or not
	 */
	private function render_builder_elm_pp_fragment_img($content=false, $prefix='img_',$type='image'){
		$atts = array('content' => 'Image', 'padding-top' => '10px');
		if($content){
			$atts['content']=$content;
			$this->render_form_fragment_h_separator($atts);
		}
		$this->render_builder_elm_pp_fragment_img_upload($type,'upload_img_url');       
		$this->render_form_fields($this->field_props['img_size'], $this->cell_props_T);
		$this->render_form_fields($this->field_props['content_align'], $this->cell_props_S);
	}

	private function render_elm_pp_layout_rows(){
		?>
		<div id="thwecmf_field_form_id_row" class=" thpl-admin-form-table thwecmf-admin-form-table" style="display:none;">
			<table class="thwecmf-edit-form">  
				<thead class="thwecmf-toggle-section">
					<?php
					$atts = array('content' => 'Row', 'padding-top' => '10px');
					$this->render_form_fragment_h_separator($atts);
					?>
				</thead>
				<?php       
				$this->render_form_fields($this->field_props['padding'], $this->cell_props_4T);
				?>
			</table>
			<?php
			$this->render_builder_elm_pp_fragment_border(); 
			$this->render_builder_elm_pp_fragment_bg(); 
			?>
		</div>
        <?php   
	}

	/**
	 * Render sidebar column settings
	 */
	private function render_elm_pp_layout_cols(){
		?>
		<div id="thwecmf_field_form_id_col" class=" thpl-admin-form-table thwecmf-admin-form-table" style="display:none">
			<table class="thwecmf-edit-form">  
				<thead class="thwecmf-toggle-section">
					<?php
					$atts = array('content' => 'Column', 'padding-top' => '10px');
					$this->render_form_fragment_h_separator($atts);
					?>
				</thead>
				<?php
				$this->render_form_fields($this->field_props['width'], $this->cell_props_T);
				$this->render_form_fields($this->field_props['padding'], $this->cell_props_T);
				$this->render_form_fields($this->field_props['text_align'], $this->cell_props_T);
				?>
			</table>
			<?php       
				$this->render_builder_elm_pp_fragment_border(); 
				$this->render_builder_elm_pp_fragment_bg(); 
			?>
		</div>
        <?php   
	}

	/**
	 * Render sidebar divider settings
	 */
	private function render_elm_pp_divider(){
		?>
        <div id="thwecmf_field_form_id_divider" class=" thpl-admin-form-table thwecmf-admin-form-table" style="display:none;">
			<table class="thwecmf-edit-form">  
				<thead class="thwecmf-toggle-section">
					<?php
					$atts = array('content' => 'Divider Properties', 'padding-top' => '10px');
					$this->render_form_fragment_h_separator($atts);
					?>
				</thead>
				<tbody>
					<?php
					$this->render_form_fields($this->field_props['width'], $this->cell_props_T);
					$this->render_form_fields($this->field_props['divider_height'], $this->cell_props_T);
					$this->render_form_fields($this->field_props['divider_color'], $this->cell_props_T);
					$this->render_form_fields($this->field_props['divider_style'], $this->cell_props_S);       
					?>
				</tbody>
			</table>
			<table class="thwecmf-edit-form">	
  				<thead class="thwecmf-toggle-section">
					<?php
					$atts = array('content' => 'Additional', 'padding-top' => '10px');
					$this->render_form_fragment_h_separator($atts);
					?>
				</thead>
				<tbody>
					<?php
					$this->render_form_fields($this->field_props['content_align'], $this->cell_props_T);
					$this->render_form_fields($this->field_props['padding'], $this->cell_props_T);
					$this->render_form_fields($this->field_props['margin'], $this->cell_props_T);
					?>
				</tbody>
			</table>
        </div>
        <?php   
	}

	/**
	 * Render sidebar text settings
	 */
	private function render_elm_pp_text(){
		?>
		<div id="thwecmf_field_form_id_text" class="thpl-admin-form-table thwecmf-admin-form-table" style="display: none;">
			<table class="thwecmf-edit-form">
				<thead class="thwecmf-toggle-section">
					<?php
					$atts = array('content' => 'Content', 'padding-top' => '10px');
					$this->render_form_fragment_h_separator($atts);
					?>
				</thead>
				<tbody>
					<tr>
						<td style="text-align:center;">
							<?php
							echo '<textarea name="i_textarea_content" rows="12" cols="37" style="border-radius:4px;"></textarea>';
							?>
						</td>
					</tr>
				</tbody>
			</table>
			<table class="thwecmf-edit-form">	
				<thead class="thwecmf-toggle-section">
					<?php
					$atts = array('content' => 'Font Properties', 'padding-top' => '10px');
					$this->render_form_fragment_h_separator($atts);
					?>
				</thead>
				<tbody>
					<?php
					$this->render_builder_elm_pp_fragment_text(false,false);
					?>
				</tbody>
			</table>
			<?php
			$this->render_builder_elm_pp_fragment_border();
			$this->render_builder_elm_pp_fragment_bg();
			?>
			<table class="thwecmf-edit-form">	
				<thead class="thwecmf-toggle-section">
					<?php
					$atts = array('content' => 'Additional Properties', 'padding-top' => '10px', 'class' => 'thwecmf-seperator-heading');
					$this->render_form_fragment_h_separator($atts);
					?>
				</thead>   
				<?php       
				$this->render_form_fields($this->field_props['padding'], $this->cell_props_T);
				$this->render_form_fields($this->field_props['margin'], $this->cell_props_T);
				?>
			</table>
		</div>
        <?php   
	}

	/**
	 * Render sidebar image settings
	 */
	private function render_elm_pp_image(){
		?>
		<div id="thwecmf_field_form_id_image" class="thpl-admin-form-table thwecmf-admin-form-table" style="display:none">
  			<table class="thwecmf-edit-form">	
  				<thead class="thwecmf-toggle-section">
  					<?php
  					$atts = array('content' => 'Image', 'padding-top' => '10px', 'class' => 'thwecmf-seperator-heading');
					$this->render_form_fragment_h_separator($atts);
  					?>
  				</thead>   
				<?php       
				$this->render_builder_elm_pp_fragment_img(false); 
				?>
       		</table>
       		<table class="thwecmf-edit-form">
       			<thead class="thwecmf-toggle-section">
					<?php
  					$atts = array('content' => 'Additional Properties', 'padding-top' => '10px');
					$this->render_form_fragment_h_separator($atts);
					?>
				</thead>
				<tbody>
					<?php
					$this->render_form_fields($this->field_props['img_padding'], $this->cell_props_T);
					$this->render_form_fields($this->field_props['img_bg_color'], $this->cell_props_T);
					?>
				</tbody>
  			</table>
		</div>
        <?php   
	}

	/**
	 * Render sidebar header settings
	 */
	private function render_elm_pp_header(){
		?>
        <div id="thwecmf_field_form_id_header_details" class="thpl-admin-form-table thwecmf-admin-form-table" style="display: none;">
    
		<table class="thwecmf-edit-form">
			<thead class="thwecmf-toggle-section">
				<?php
				$atts = array('content' => 'Header Properties', 'padding-top' => '10px');
				$this->render_form_fragment_h_separator($atts,false);
				?>
			</thead>
			<tbody>
				<?php        
				$this->render_form_fields($this->field_props['size'], $this->cell_props_T);       
				?>	
			</tbody>
		</table>
		<table class="thwecmf-edit-form">	
  			<thead class="thwecmf-toggle-section">
  				<?php
				$atts = array('content' => 'Text Properties', 'padding-top' => '10px');
				$this->render_form_fragment_h_separator($atts,false);
				?>
  			</thead>
  			<tbody>
  			<?php
  			$this->render_builder_elm_pp_fragment_text(false);
  			$this->render_form_fields($this->field_props['padding'], $this->cell_props_T);
  			?>
  			</tbody>
		</table>
		<table class="thwecmf-edit-form">
			<thead class="thwecmf-toggle-section">
  				<?php
				$atts = array('content' => 'Logo Properties', 'padding-top' => '10px');
				$this->render_form_fragment_h_separator($atts,false);
				?>
  			</thead>
  			<tbody>
  				<?php
				$this->render_builder_elm_pp_fragment_img(false);
				?>
  			</tbody>
		</table>
		<?php
		$this->render_builder_elm_pp_fragment_border();
		$this->render_builder_elm_pp_fragment_bg('','',false);
		?>
        </div>
        <?php   
	}

	/**
	 * Render sidebar customer settings
	 */
	private function render_elm_pp_customer(){
		?>
		<div id="thwecmf_field_form_id_customer_address" class=" thpl-admin-form-table thwecmf-admin-form-table" style="display:none">
    		<table class="thwecmf-edit-form">	
				<thead class="thwecmf-toggle-section">
    			<?php
    			$atts = array('content' => 'Heading Properties', 'padding-top' => '10px');
				$this->render_form_fragment_h_separator($atts,false);
				?>	
    			</thead>
    			<tbody>
    				<?php
    				$this->render_builder_elm_pp_fragment_text(false, true, false, false);
    				?>
    			</tbody>
			</table>
			<table class="thwecmf-edit-form">	
				<thead class="thwecmf-toggle-section">
					<?php
					$atts = array('content' => 'Details Properties', 'padding-top' => '10px');
					$this->render_form_fragment_h_separator($atts,false);
					?>
				</thead>
				<tbody>
					<?php
					$this->render_builder_elm_pp_fragment_text('details_', false, false, false);
					?>
				</tbody>
    		</table>
			<?php       
			$this->render_builder_elm_pp_fragment_bg('','',false); 
			?>
			<table class="thwecmf-edit-form">	
				<thead class="thwecmf-toggle-section">
				<?php
				$atts = array('content' => 'Additional Properties', 'padding-top' => '10px');
				$this->render_form_fragment_h_separator($atts,false);
				?>
				</thead>
				<tbody>
					<?php       
				$this->render_form_fields($this->field_props['padding'], $this->cell_props_T);
				$this->render_form_fields($this->field_props['margin'], $this->cell_props_T);
				?>  
				</tbody>
			</table>			
        </div>
		<?php
	}

	/**
	 * Render sidebar order settings
	 */
	private function render_elm_pp_order(){
		?>
        <div id="thwecmf_field_form_id_order_details" class=" thpl-admin-form-table thwec-admin-form-table" style="display:none">
  			<table class="thwecmf-edit-form">	
				<thead class="thwecmf-toggle-section">
  					<?php
  					$atts = array('content' => 'Order Title Properties', 'padding-top' => '10px');
					$this->render_form_fragment_h_separator($atts,false);
  					?>
  				</thead>
  				<tbody>
  					<?php
  					$two_input = $this->cell_props_2I;
  					unset( $two_input['input_margin'] );
  					$two_input_left = $two_input;
  					?>
  					<tr>
  						<td>
  							<?php
  							$this->render_form_fields($this->field_props['color'], $this->cell_props_2I, false);
  							$this->render_form_fields($this->field_props['font_size'], $two_input_left, false);
  							?>
  						</td>
  					</tr>
  					<tr class="thwecmf-input-spacer"><td></td></tr>
  					<tr>
  						<td>
  							<?php
  							$this->render_form_fields($this->field_props['font_family'], $two_input, false);
  							?>
  						</td>
  					</tr>
  					<tr class="thwecmf-input-spacer"><td></td></tr>
  				</tbody>
  			</table>
 
			<table class="thwecmf-edit-form">	
				<thead class="thwecmf-toggle-section">
					<?php
					$atts = array('content' => 'Content Properties', 'padding-top' => '10px');
					$this->render_form_fragment_h_separator($atts,false);
					?>
				</thead>
				<tbody>
					<tr>
						<td>
							<?php
  							$this->render_form_fields($this->field_props['details_text_align'], $two_input, false);
  							?>
						</td>
					</tr>
					<tr class="thwecmf-input-spacer"><td></td></tr>
					<tr>
  						<td>
  							<?php
  							$this->render_form_fields($this->field_props['details_color'], $this->cell_props_2I, false);
  							$this->render_form_fields($this->field_props['details_font_size'], $two_input_left, false);
  							?>
  						</td>
  					</tr>
  					<tr class="thwecmf-input-spacer"><td></td></tr>
  					<tr>
  						<td>
  							<?php
  							$this->render_form_fields($this->field_props['details_font_family'], $two_input, false);
  							?>
  						</td>
  					</tr>
  					<tr class="thwecmf-input-spacer"><td></td></tr>
					<tr>
						<td>
							<?php
							$this->render_form_fields($this->field_props['checkbox_option_image'],false,false);
							?>
						</td>
					</tr>
				</tbody>
			</table>
			<table class="thwecmf-edit-form">
				<thead class="thwecmf-toggle-section">
					<?php
					$atts = array('content' => 'Table Properties', 'padding-top' => '10px');
					$this->render_form_fragment_h_separator($atts,false);
					?>
				</thead>
				<tbody>
					<?php
					$this->render_form_fields($this->field_props['content_border_color'], $this->cell_props_T);
					?>
					<tr class="thwecmf-input-spacer"><td></td></tr>
				</tbody>
			</table>
			<table>
				<thead class="thwecmf-toggle-section">
					<?php
					$atts = array('content' => 'Background Properties', 'padding-top' => '10px');
					$this->render_form_fragment_h_separator($atts,false);
					?>
				</thead>
				<tbody>
					<tr>
  						<td>
  							<?php
  							$bg_color = $this->field_props['bg_color'];
  							$bg_color['label'] = 'color';
  							$this->render_form_fields($bg_color, $two_input, false);
  							?>
  						</td>
  					</tr>
  					<tr class="thwecmf-input-spacer"><td></td></tr>
					<tr>
						<td>
							<?php
							$this->render_builder_elm_pp_fragment_img_upload('bg_image','upload_bg_url', 'background ');
							?>
						</td>
					</tr>
					<tr class="thwecmf-input-spacer"><td></td></tr>
				</tbody>
			</table>
			<table class="thwecmf-edit-form">
				<thead class="thwecmf-toggle-section">
					<?php
					$atts = array('content' => 'Additional Properties', 'padding-top' => '10px');
					$this->render_form_fragment_h_separator($atts);
					?>
				</thead>
				<tbody>
					<?php
					$this->render_form_fields($this->field_props['padding'], $this->cell_props_T);
					?>
				</tbody>
			</table>
        </div>
        <?php   
	}	

	/**
	 * Render sidebar billing settings
	 */
	private function render_elm_pp_billing(){
		?>
        <div id="thwecmf_field_form_id_billing_address" class="thpl-admin-form-table thwecmf-admin-form-table" style="display:none">
        	<table class="thwecmf-edit-form">	
				<thead class="thwecmf-toggle-section">
					<?php
  					$atts = array('content' => 'Heading Properties', 'padding-top' => '10px');
					$this->render_form_fragment_h_separator($atts);
					?>
				</thead>
				<tbody>
					<?php
					$this->render_builder_elm_pp_fragment_text(false, true, false, false); 
					?>
				</tbody>
			</table>
			<table class="thwecmf-edit-form">	
				<thead class="thwecmf-toggle-section">
					<?php
					$atts = array('content' => 'Details Properties', 'padding-top' => '10px');
					$this->render_form_fragment_h_separator($atts);
  					?>
				</thead>
				<tbody>
					<?php
					$this->render_builder_elm_pp_fragment_text('details_', false, false, false); 
  					?>
				</tbody>
			</table>
			<?php       
			$this->render_builder_elm_pp_fragment_border(); 
			$this->render_builder_elm_pp_fragment_bg(); 
			?>
			<table class="thwecmf-edit-form">	
				<thead class="thwecmf-toggle-section">
					<?php
					$atts = array('content' => 'Additional Properties', 'padding-top' => '10px');
					$this->render_form_fragment_h_separator($atts);
					?>
				</thead>
				<tbody>
					<?php         
					$this->render_form_fields($this->field_props['padding'], $this->cell_props_T);
					$this->render_form_fields($this->field_props['margin'], $this->cell_props_T);
					?>   
				</tbody>
			</table>
        </div>
        <?php   
	}

	/**
	 * Render sidebar shipping settings
	 */
	private function render_elm_pp_shipping(){
		?>
        <div id="thwecmf_field_form_id_shipping_address" class="thpl-admin-form-table thwecmf-admin-form-table" style="display:none">
        	<table class="thwecmf-edit-form">	
				<thead class="thwecmf-toggle-section">
					<?php
					$atts = array('content' => 'Heading Properties', 'padding-top' => '10px');
					$this->render_form_fragment_h_separator($atts);
					?>
				</thead>
				<tbody>
					<?php       
					$this->render_builder_elm_pp_fragment_text(false, true, false, false);
					?>   
				</tbody>
			</table>
			<table class="thwecmf-edit-form">	
				<thead class="thwecmf-toggle-section">
					<?php
					$atts = array('content' => 'Details Properties', 'padding-top' => '10px');
					$this->render_form_fragment_h_separator($atts);
					?>
				</thead>
				<tbody>
					<?php       
					$this->render_builder_elm_pp_fragment_text('details_', false, false, false);
					?>   
				</tbody>
			</table>
  			<?php       
			$this->render_builder_elm_pp_fragment_border(); 
			$this->render_builder_elm_pp_fragment_bg(); 
			?>
			<table class="thwecmf-edit-form">	
				<thead class="thwecmf-toggle-section">
					<?php
					$atts = array('content' => 'Additional Properties', 'padding-top' => '10px');
					$this->render_form_fragment_h_separator($atts);
					?>
				</thead>
				<tbody>
					<?php       
					$this->render_form_fields($this->field_props['padding'], $this->cell_props_T);
					$this->render_form_fields($this->field_props['margin'], $this->cell_props_T);
					?>   
				</tbody>
			</table>
        </div>
        <?php   
	}

	/**
	 * Render sidebar gap settings
	 */
	private function render_elm_pp_gap(){
		?>
        <div id="thwecmf_field_form_id_gap" class="thpl-admin-form-table thwecmf-admin-form-table" style="display:none">
        	<table class="thwecmf-edit-form">
				<thead class="thwecmf-toggle-section">
        			<?php
  					$atts = array('content' => 'Gap Properties', 'padding-top' => '10px');
					$this->render_form_fragment_h_separator($atts);
					?>
        		</thead>
        		<tbody>
        			<?php
        			$this->render_form_fields($this->field_props['height'], $this->cell_props_T); 
					?>
        		</tbody>      
			</table>
			<?php       
        	$this->render_builder_elm_pp_fragment_border();
        	$this->render_builder_elm_pp_fragment_bg('', '', false);
        	?>
        </div>
        <?php   
	}

	/**
	 * Render sidebar social settings
	 */
	private function render_elm_pp_social(){
		?>
		<div id="thwecmf_field_form_id_social" class="thpl-admin-form-table thwecmf-admin-form-table" style="display: none;">
  			<table class="thwecmf-edit-form">	
  				<thead class="thwecmf-toggle-section">
  					<?php
					$atts = array('content' => 'Icon Url Properties', 'padding-top' => '10px');
					$this->render_form_fragment_h_separator($atts,false);
					?>
				</thead>
				<tbody>
					<?php
					$this->render_form_fields($this->field_props['url1'], $this->cell_props_T);
					$this->render_form_fields($this->field_props['url2'], $this->cell_props_T);
	 				$this->render_form_fields($this->field_props['url3'], $this->cell_props_T);
					$this->render_form_fields($this->field_props['url4'], $this->cell_props_T); 
					$this->render_form_fields($this->field_props['url5'], $this->cell_props_T); 
					$this->render_form_fields($this->field_props['url6'], $this->cell_props_T);        
					$this->render_form_fields($this->field_props['url7'], $this->cell_props_T); 
					?>
				</tbody>
			</table>
  			<table class="thwecmf-edit-form">	
  				<thead class="thwecmf-toggle-section">
  					<?php
					$atts = array('content' => 'Icon Properties', 'padding-top' => '10px');
					$this->render_form_fragment_h_separator($atts,false);
					?>
  				</thead>
  				<tbody>
  					<?php
  					$this->render_form_fields($this->field_props['padding'], $this->cell_props_T);
            		$this->render_form_fields($this->field_props['img_size'], $this->cell_props_T);
					$this->render_form_fields($this->field_props['content_align'], $this->cell_props_T);
					$this->render_form_fields($this->field_props['icon_padding'], $this->cell_props_T);
  					?>
  				</tbody>
			</table>
			<?php
			$this->render_builder_elm_pp_fragment_bg('','', true);
			?>
        </div>
        <?php
	}

	/**
	 * Render sidebar button settings
	 */
	private function render_elm_pp_button(){
		?>
        <div id="thwecmf_field_form_id_button" class=" thpl-admin-form-table thwecmf-admin-form-table" style="display:none">
  			<table class="thwecmf-edit-form">	
				<thead class="thwecmf-toggle-section">
  					<?php
					$atts = array('content' => 'Button Content', 'padding-top' => '10px');
					$this->render_form_fragment_h_separator($atts,false);
					?>
  				</thead>
  				<tbody>
  					<?php
					$this->render_form_fields( $this->field_props['url'], $this->cell_props_T);
					$this->render_form_fields( $this->field_props['content'], $this->cell_props_T);
					?>
  				</tbody>
  			</table>
  			<table class="thwecmf-edit-form">	
				<thead class="thwecmf-toggle-section">
  					<?php
  					$atts = array('content' => 'Content Properties', 'padding-top' => '10px');
					$this->render_form_fragment_h_separator($atts,false);
  					?>
  				</thead>
  				<tbody>
  					<?php
  					$this->render_builder_elm_pp_fragment_text(false,false,true,false);
  					$this->render_form_fields($this->field_props['content_padding'], $this->cell_props_T);
  					?>
  				</tbody>
  			</table>	
			<?php
			$this->render_builder_elm_pp_fragment_border();	
			$this->render_builder_elm_pp_fragment_bg('','', false);
			?>	
			 <table class="thwecmf-edit-form">	
				<thead class="thwecmf-toggle-section">
  					 <?php
					$atts = array('content' => 'Additional Properties', 'padding-top' => '10px');
					$this->render_form_fragment_h_separator($atts,false);
					?>
  				</thead>
  				<tbody>
  					<?php
					$this->render_form_fields($this->field_props['size'], $this->cell_props_T);
					$this->render_form_fields($this->field_props['padding'], $this->cell_props_T);
					$this->render_form_fields($this->field_props['margin'], $this->cell_props_T);
					?>
  				</tbody>
  			</table>
        </div>
        <?php   
	}

	/**
	 * Render sidebar gif settings
	 */
	private function render_elm_pp_gif(){
		?>
		<div id="thwecmf_field_form_id_gif" class=" thpl-admin-form-table thwecmf-admin-form-table" style="display:none">
  			<table class="thwecmf-edit-form">
				<thead class="thwecmf-toggle-section">
  					<?php
  					$atts = array('content' => 'Gif Properties', 'padding-top' => '10px');
					$this->render_form_fragment_h_separator($atts,false);
					?>
				</thead>
				<tbody>
					<?php
  					$this->render_builder_elm_pp_fragment_img(false,false,'gif');
  					?>
  				</tbody>
  			</table>
  			<table class="thwecmf-edit-form">
				<thead class="thwecmf-toggle-section">
					<?php
  					$atts = array('content' => 'Additional Properties', 'padding-top' => '10px');
					$this->render_form_fragment_h_separator($atts,false);
					?>
				</thead>
				<tbody>
					<?php       
					$this->render_form_fields($this->field_props['padding'], $this->cell_props_T);
					$this->render_form_fields($this->field_props['bg_color'], $this->cell_props_T);
					?>
				</tbody>
  			</table>
        </div>
		<?php
	}

	/**
	 * Render sidebar builder settings
	 */
	private function render_elm_pp_builder(){
		?>
		<div id="thwecmf_field_form_id_t_builder" class=" thpl-admin-form-table thwecmf-admin-form-table" style="display:none">
			<?php
			$this->render_builder_elm_pp_fragment_border(false);
        	$this->render_builder_elm_pp_fragment_bg();
        	?>
        </div>
        <?php
	}

	/**
	 * Render one column layout content
	 */
	private function render_elm_1_column_layout(){
		?>
		<div id="thwecmf_template_layout_one_column" style="display:none;">
			<table class="thwecmf-row thwecmf-block-one-column thwecmf-builder-block" id="one_column" data-elm="row-1-col" data-css-props='{"p_t":"0px","p_r":"0px","p_b":"0px","p_l":"0px","b_t":"0px","b_r":"0px","b_b":"0px","b_l":"0px","border_style":"none","border_color":"","bg_color":"","upload_bg_url":""}' data-column-count="1" cellpadding="0" cellspacing="0">
				<tbody>
					<tr>
						<td class="thwecmf-column-padding thwecmf-col thwecmf-columns" id="one_column_1" data-css-props='{"width":"100%","p_t":"10px","p_r":"10px","p_b":"10px","p_l":"10px","text_align":"center","b_t":"1px","b_r":"1px","b_b":"1px","b_l":"1px","border_style":"dotted","border_color":"#dddddd","bg_color":"","upload_bg_url":""}'>
							<span class="thwecmf-builder-add-btn thwecmf-btn-add-element">+ Add Element</span>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<?php
	}
	
	/**
	 * Render two column layout content
	 */
	private function render_elm_2_column_layout(){
		?>
		<div id="thwecmf_template_layout_two_column" style="display:none;">
			<table class="thwecmf-row thwecmf-block-two-column thwecmf-builder-block" id="two_column" data-elm="row-2-col" cellpadding="0" cellspacing="0" data-css-props='{"p_t":"","p_r":"","p_b":"","p_l":"","b_t":"0px","b_r":"0px","b_b":"0px","b_l":"0px","border_style":"none","border_color":"","bg_color":"","upload_bg_url":""}' data-column-count="2"  cellpadding="0" cellspacing="0">
				<tbody>
					<tr>
						<td class="thwecmf-column-padding thwecmf-col thwecmf-columns" id="two_column_1"  data-css-props='{"width":"50%","p_t":"10px","p_r":"10px","p_b":"10px","p_l":"10px","b_t":"1px","b_r":"1px","b_b":"1px","b_l":"1px","border_style":"dotted","border_color":"#dddddd","bg_color":"","text_align":"center","upload_bg_url":""}'>
							<span class="thwecmf-builder-add-btn thwecmf-btn-add-element">+ Add Element</span>
						</td>
						<td class="thwecmf-column-padding thwecmf-col thwecmf-columns" id="two_column_2" data-css-props='{"width":"50%","p_t":"10px","p_r":"10px","p_b":"10px","p_l":"10px","b_t":"1px","b_r":"1px","b_b":"1px","b_l":"1px","border_style":"dotted","border_color":"#dddddd","bg_color":"","text_align":"center","upload_bg_url":""}'>
							<span class="thwecmf-builder-add-btn thwecmf-btn-add-element">+ Add Element</span>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<?php
	}

	/**
	 * Render three column layout content
	 */
	private function render_elm_3_column_layout(){
		?>
		<div id="thwecmf_template_layout_three_column" style="display:none;">
			<table class="thwecmf-row thwecmf-block-three-column thwecmf-builder-block" id="three_column" data-elm="row-2-col" cellpadding="0" cellspacing="0" data-css-props='{"p_t":"0px","p_r":"0px","p_b":"0px","p_l":"0px","b_t":"0px","b_r":"0px","b_b":"0px","b_l":"0px","border_style":"none","border_color":"","bg_color":"","upload_bg_url":""}' data-column-count="3"  cellpadding="0" cellspacing="0">
				<tbody>
					<tr>
						<td class="thwecmf-column-padding thwecmf-col thwecmf-columns" id="three_column_1"  data-css-props='{"width":"33.333333333333336%","p_t":"10px","p_r":"10px","p_b":"10px","p_l":"10px","b_t":"1px","b_r":"1px","b_b":"1px","b_l":"1px","border_style":"dotted","border_color":"#dddddd","bg_color":"","text_align":"center","upload_bg_url":""}'>
							<span class="thwecmf-builder-add-btn thwecmf-btn-add-element">+ Add Element</span>
						</td>
						<td class="thwecmf-column-padding thwecmf-col thwecmf-columns" id="three_column_2" data-css-props='{"width":"33.333333333333336%","p_t":"10px","p_r":"10px","p_b":"10px","p_l":"10px","b_t":"1px","b_r":"1px","b_b":"1px","b_l":"1px","border_style":"dotted","border_color":"#dddddd","bg_color":"","text_align":"center","upload_bg_url":""}'>
							<span class="thwecmf-builder-add-btn thwecmf-btn-add-element">+ Add Element</span>
						</td>
						<td class="thwecmf-column-padding thwecmf-col thwecmf-columns" id="three_column_3"  data-css-props='{"width":"33.333333333333336%","p_t":"10px","p_r":"10px","p_b":"10px","p_l":"10px","b_t":"1px","b_r":"1px","b_b":"1px","b_l":"1px","border_style":"dotted","border_color":"#dddddd","bg_color":"","text_align":"center","upload_bg_url":""}'>
							<span class="thwecmf-builder-add-btn thwecmf-btn-add-element">+ Add Element</span>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<?php
	}

	/**
	 * Render four column layout content
	 */
	private function render_elm_4_column_layout(){
		?>
		<div id="thwecmf_template_layout_four_column" style="display:none;">
			<table class="thwecmf-row thwecmf-block-four-column thwecmf-builder-block" id="four_column" data-elm="row-2-col" cellpadding="0" cellspacing="0" data-css-props='{"p_t":"0px","p_r":"0px","p_b":"0px","p_l":"0px","b_t":"0px","b_r":"0px","b_b":"0px","b_l":"0px","border_style":"none","border_color":"","bg_color":"","upload_bg_url":""}' data-column-count="4"  cellpadding="0" cellspacing="0">
				<tbody>
					<tr>
						<td class="thwecmf-column-padding thwecmf-col thwecmf-columns" id="four_column_1"  data-css-props='{"width":"25%","p_t":"10px","p_r":"10px","p_b":"10px","p_l":"10px","b_t":"1px","b_r":"1px","b_b":"1px","b_l":"1px","border_style":"dotted","border_color":"#dddddd","bg_color":"","text_align":"center","upload_bg_url":""}'>
							<span class="thwecmf-builder-add-btn thwecmf-btn-add-element">+ Add Element</span>
						</td>
						<td class="thwecmf-column-padding thwecmf-col thwecmf-columns" id="four_column_2" data-css-props='{"width":"25%","p_t":"10px","p_r":"10px","p_b":"10px","p_l":"10px","b_t":"1px","b_r":"1px","b_b":"1px","b_l":"1px","border_style":"dotted","border_color":"#dddddd","bg_color":"","text_align":"center","upload_bg_url":""}'>
							<span class="thwecmf-builder-add-btn thwecmf-btn-add-element">+ Add Element</span>
						</td>
						<td class="thwecmf-column-padding thwecmf-col thwecmf-columns" id="four_column_3"  data-css-props='{"width":"25%","p_t":"10px","p_r":"10px","p_b":"10px","p_l":"10px","b_t":"1px","b_r":"1px","b_b":"1px","b_l":"1px","border_style":"dotted","border_color":"#dddddd","bg_color":"","text_align":"center","upload_bg_url":""}'>
							<span class="thwecmf-builder-add-btn thwecmf-btn-add-element">+ Add Element</span>
						</td>
						<td class="thwecmf-column-padding thwecmf-col thwecmf-columns" id="four_column_4"  data-css-props='{"width":"25%","p_t":"10px","p_r":"10px","p_b":"10px","p_l":"10px","b_t":"1px","b_r":"1px","b_b":"1px","b_l":"1px","border_style":"dotted","border_color":"#dddddd","bg_color":"","text_align":"center","upload_bg_url":""}'>
							<span class="thwecmf-builder-add-btn thwecmf-btn-add-element">+ Add Element</span>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<?php
	}

	/**
	 * Render billing element content
	 */
	private function render_elm_billing(){
		?>
		<div id="thwecmf_template_elm_billing_address" style="display:none;">
			<table class="thwecmf-block thwecmf-block-billing thwecmf-builder-block" id="{billing_address}" data-block-name="billing_address" cellpadding="0" cellspacing="0" data-css-props='{"align":"center","color":"#0099ff","text_align":"center","font_size":"18px","font_family":"helvetica","details_color":"#444444","details_text_align":"center","details_font_size":"13px","details_font_family":"helvetica","size_width":"100%","size_height":"115px","p_t":"5px","p_r":"0px","p_b":"2px","p_l":"0px","m_t":"0px","m_r":"0px","m_b":"0px","m_l":"0px","b_t":"0px","b_r":"0px","b_b":"0px","b_l":"0px","border_style":"none","border_color":"","bg_color":"","content":"","upload_bg_url":""}' data-text-props='{"content":"Billing Details"}'>
      			<tr>
      				<td class="thwecmf-address-alignment" align="center">  	
      					<table class="thwecmf-address-wrapper-table" cellpadding="0" cellspacing="0">
      						<tr>
      							<td  class="thwecmf-billing-padding">
      								<h2 class="thwecmf-billing-header">Billing Details</h2>
			      					<p class="address thwecmf-billing-body">
			      						John Smith<br>
			     						252  Bryan Avenue<br>
			     						Minneapolis, MN 55412<br>
			     						United States (US)
			     						<br>333-6457<br><a href="#">johnsmith@gmail.com</a>
			      					</p>
      							</td>
      						</tr>
      					</table>
      				</td>
      			</tr>
      		</table>
		</div>
		<?php
	}

	/**
	 * Render header details element content
	 */
	private function render_elm_header_details(){
		?>
		<div id="thwecmf_template_elm_header_details" style="display: none;">
			<table class="thwecmf-block thwecmf-block-header thwecmf-builder-block" id="{header_details}" data-block-name="header_details" cellpadding="0" cellspacing="0" data-css-props='{"size_width":"100%","size_height":"auto", "p_t":"30px","p_r":"0px","p_b":"30px","p_l":"0px","color":"#ffffff","font_size":"40px","line_height":"100%","text_align":"center","font_family":"georgia","font_weight":"normal","img_p_t":"15px","img_p_r":"5px","img_p_b":"15px","img_p_l":"5px","bg_color":"#0099ff","b_t":"0px","b_r":"0px","b_b":"0px","b_l":"0px","border_style":"none","border_color":"","content":"","upload_bg_url":"","upload_img_url":"","content_align":"center","img_size_width":"155px","img_size_height":"103px"}' data-text-props='{"content":"Email Template Header","upload_img_url":""}'>
				<tr class="thwecmf-header-logo-tr">
					<td class="thwecmf-header-logo">
						<p class="thwecmf-header-logo-ph">
							<img src="<?php echo esc_url(TH_WECMF_ASSETS_URL.'images/placeholder.png'); ?>" alt="Logo">
						</p>
					</td>
				</tr>
				<tr>
					<td class="thwecmf-header-text">
						<h1>Email Template Header</h1>
					</td>
				</tr>
			</table>
		</div>
		<?php
	}

	/**
	 * Render customer address element content
	 */
	private function render_elm_customer_address(){
		?>
		<div id="thwecmf_template_elm_customer_address" style="display:none;">
			<table class="thwecmf-block thwecmf-block-customer thwecmf-builder-block" id="{customer_address}" data-block-name="customer_address" cellpadding="0" cellspacing="0" data-css-props='{"align":"center","color":"#0099ff","text_align":"center","font_size":"18px","font_family":"helvetica","details_color":"#444444","details_text_align":"center","details_font_size":"13px","details_font_family":"helvetica","p_t":"5px","p_r":"0px","p_b":"2px","p_l":"0px","m_t":"0px","m_r":"auto","m_b":"0px","m_l":"auto","bg_color":"","upload_bg_url":"","content":""}' data-text-props='{"content":"Customer Details"}'>
      			<tr>
      				<td class="thwecmf-address-alignment" align="center">
      					<table class="thwecmf-address-wrapper-table" cellpadding="0" cellspacing="0">
      						<tr>
      							<td class="thwecmf-customer-padding">
 			     					<h2 class="thwecmf-customer-header">Customer Details</h2>
      								<p class="address thwecmf-customer-body">
      								John Smith<br>333-6457<br><a href="#">johnsmith@gmail.com</a>
      								</p>
      							</td>
      						</tr>
      					</table>	
      				</td>
      			</tr>
      		</table>
		</div>
		<?php
	}

	/**
	 * Render order details element content
	 */
	private function render_elm_order_details(){
		?>
		<div id="thwecmf_template_elm_order_details" style="display:none;">
			<?php
			$thwec_total = array('label1'=>'Subtotal:','label2'=>'Shipping:','label3'=>'Payment method:','label4'=>'Total:','value1'=>'$20','value2'=>'Free shipping','value3'=>'Cash on delivery','value4'=>'$20');
			$thwec_item = array('item1'=>'T-shirt','item2'=>'Jeans','qty1'=>'1','qty2'=>'1','price1'=>'$5','price2'=>'$15');
			?>
			<span class="loop_start_before_order_table"></span>
			<table class="thwecmf-block thwecmf-block-order thwecmf-builder-block" id="{order_details}" data-block-name="order_details" cellpadding="0" cellspacing="0" data-css-props='{"color":"#4286f4","font_size":"18px","font_family":"helvetica","details_color":"#636363","details_font_size":"14px","details_font_family":"helvetica","details_text_align":"left","content_border_color":"#e5e5e5","p_t":"20px","p_r":"48px","p_b":"20px","p_l":"48px","upload_bg_url":"","bg_color":"","product_img":"none"}' data-text-props='{"content":"Order"}' align="center">
				<tr class="before_order_table"></tr>
				<tr>
					<td class="thwecmf-order-padding" align="center">
						<span class="woocommerce_email_before_order_table"></span>
      					<h2 class="thwecmf-order-heading"><u><span class="thwecmf-order-title">Order</span>#248</u> (January 22, 2019)</h2>
						<table class="thwecmf-order-table thwecmf-td" style="font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;" cellpadding="0" cellspacing="0">
							<thead>
								<tr>
									<th class="thwecmf-td thwecmf-order-head thwecmf-td-order-product" scope="col" style="">Product</th>
									<th class="thwecmf-td thwecmf-order-head thwecmf-td-order-quantity" scope="col" style="">Quantity</th>
									<th class="thwecmf-td thwecmf-order-head thwecmf-td-order-price" scope="col" style="">Price</th>
								</tr>
							</thead>
							<tbody>
								<tr class="item-loop-start"></tr>
								<?php for($j=1;$j<=2;$j++) { ?>
								<tr class="woocommerce_order_item_class-filter<?php echo $j; ?>">
									<td class="thwecmf-order-item thwecmf-td" style="vertical-align:middle;word-wrap:break-word;">
											<div class="thwecmf-order-item-img">
												<img src="<?php echo esc_url( TH_WECMF_ASSETS_URL.'images/product.png'); ?>" alt="Product Image">
											</div>
											<?php echo esc_html( $thwec_item['item'.$j] ); ?>
									</td>
									<td class="thwecmf-order-item-qty thwecmf-td" style="vertical-align:middle;">
										<?php echo esc_html( $thwec_item['qty'.$j] ); ?>
									</td>
									<td class="thwecmf-order-item-price thwecmf-td" style="vertical-align:middle;">
										<?php echo esc_html( $thwec_item['price'.$j] );?>
									</td>
								</tr>
								<?php } ?>
								<tr class="item-loop-end"></tr>
							</tbody>
							<tfoot class="thwecmf-order-footer">
								<tr class="order-total-loop-start"></tr>
							<?php 
							for($i=1;$i<=4;$i++){ ?>
								<tr class="thwecmf-order-footer-row">
									<th class="thwecmf-order-total-label thwecmf-td" scope="row" colspan="2"><?php echo esc_html( $thwec_total['label'.$i] ); ?></th>
									<td class="thwecmf-order-total-value thwecmf-td"><?php echo esc_html( $thwec_total['value'.$i] ); ?></td>
								</tr>
							<?php } ?>
							<tr class="order-total-loop-end" data-ot-css='<?php echo esc_attr( WECMF_Email_Customizer_Utils::get_ot_td_css( true ) ); ?>'></tr>
							</tfoot>
						</table>
      					</td>
      				</tr>
      			</table>
      			<span class="loop_end_after_order_table"></span>
			</div>

		<?php
	}

	/**
	 * Render shipping element content
	 */
	private function render_elm_shipping(){
		?>
		<div id="thwecmf_template_elm_shipping_address" style="display:none;">
			<table class="thwecmf-block thwecmf-block-shipping thwecmf-builder-block" id="{shipping_address}" data-block-name="shipping_address" cellpadding="0" cellspacing="0" data-css-props='{"align":"center","color":"#0099ff","text_align":"center","font_size":"18px","font_family":"helvetica","details_color":"#444444","details_text_align":"center","details_font_size":"13px","details_font_family":"helvetica","size_width":"100%","size_height":"115px","p_t":"5px","p_r":"0px","p_b":"2px","p_l":"0px","m_t":"0px","m_r":"0px","m_b":"0px","m_l":"0px","b_t":"0px","b_r":"0px","b_b":"0px","b_l":"0px","border_style":"none","border_color":"","bg_color":"","content":"","upload_bg_url":""}' data-text-props='{"content":"Shipping Details"}'>
      			<tr>
      				<td class="thwecmf-address-alignment" align="center">
      					<table class="thwecmf-address-wrapper-table" cellpadding="0" cellspacing="0">
      						<tr>
      							<td class="thwecmf-shipping-padding">      
     	 							<h2 class="thwecmf-shipping-header">Shipping Details</h2>
      								<p class="address thwecmf-shipping-body">
     								John Smith<br>
     								252  Bryan Avenue<br>
     								Minneapolis, MN 55412<br>
     								United States (US)
      								</p>
      							</td>
      						</tr>
      					</table>
      				</td>
      			</tr>
      		</table>
		</div>
		<?php
	}

	/**
	 * Render downloadable product element content
	 */
	private function render_hook_downloadable_product(){
		?>
		<div id="thwecmf_template_hook_downloadable_product" style="display: none;">
			<p class="thwecmf-hook-code" id="{downloadable_product}">{downloadable_product_table}</p>
		</div>
		<?php
	}

	/**
	 * Render text element content
	 */
	private function render_elm_text(){
		?>
		<div id="thwecmf_template_elm_text" style="display:none;">
			<table class="thwecmf-block thwecmf-block-text thwecmf-builder-block" id="{text}" data-block-name="text" data-css-props='{"color":"#636363","align":"center", "font_size":"13px","line_height":"150%","font_weight":"400","font_family":"helvetica","bg_color":"", "upload_bg_url":"","b_t":"0px", "b_r":"0px", "b_b":"0px", "b_l":"0px", "border_color":"", "border_style":"none", "m_t":"0px", "m_r":"auto", "m_b":"0px", "m_l":"auto", "p_t":"15px", "p_r":"15px", "p_b":"15px", "p_l":"15px", "text_align":"center","textarea_content":""}' data-text-props='{"textarea_content":"Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry&#39;s standard dummy text ever since the 1500."}' cellspacing="0" cellpadding="0">
				<tr>
					<td class="thwecmf-block-text-holder">Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500.</td>
				</tr>
			</table>
		</div>
		<?php
	}

	/**
	 * Render image element content
	 */
	private function render_elm_image(){
		?>
		<div id="thwecmf_template_elm_image" style="display:none;"> 
		    <table class="thwecmf-block thwecmf-block-image thwecmf-builder-block" id="{image}" cellpadding="0" cellspacing="0" data-block-name="image" align="center" data-css-props='{"img_size_width":"50%","img_size_height":"auto","align":"","upload_img_url":"","content_align":"center","img_p_t":"10px","img_p_r":"0px","img_p_b":"10px","img_p_l":"0px","img_bg_color":""}' data-text-props='{"upload_img_url":""}'>
		    	<tr>
		    		<td class="thwecmf-image-column"><p><img src="<?php echo esc_url(TH_WECMF_ASSETS_URL.'images/placeholder.png' ); ?>" alt="Default Image" width="288" height="186" /></p></td>
      			</tr>

      		</table>
		</div>
		<?php
	}

	/**
	 * Render divider element content
	 */
	private function render_elm_divider(){
		?>
		<div id="thwecmf_template_elm_divider" style="display:none;">
      		<table cellspacing="0" cellpadding="0" class="thwecmf-block thwecmf-builder-block thwecmf-block-divider" id="{divider}" data-block-name="divider" data-css-props='{"width":"70%","divider_height":"2px","divider_color":"#808080","divider_style":"solid","m_t":"0px","m_r":"auto","m_b":"0px","m_l":"auto","p_t":"20px","p_r":"0px","p_b":"20px","p_l":"0px","content_align":"center"}'>
      			<tr><td><hr></td></tr>
      		</table>
		</div>
		<?php
	}

	private function render_elm_gap(){
		?>
		<div id="thwecmf_template_elm_gap" style="display:none;">
      		<p class="thwecmf-block thwecmf-block-gap thwecmf-builder-block" id="{gap}" data-block-name="gap" data-css-props='{"height":"48px","b_t":"0px","b_r":"0px","b_b":"0px","b_l":"0px","border_style":"none","border_color":"","bg_color":""}'></p>
		</div>
		<?php
	}

	/**
	 * Render social element content
	 */
	private function render_elm_social(){
		?>
		<div id="thwecmf_template_elm_social" style="display:none;">
  			<table class="thwecmf-block thwecmf-block-social thwecmf-builder-block" id="{social}" data-block-name="social" data-css-props='{"p_t":"0px","p_r":"0px","p_b":"0px","p_l":"0px","img_size_width":"40px","img_size_height":"40px","icon_p_t":"15px","icon_p_r":"3px","icon_p_b":"15px","icon_p_l":"3px","content_align":"center","bg_color":"","upload_bg_url":"","url1":"table-cell","url2":"table-cell","url3":"table-cell","url4":"table-cell","url5":"table-cell","url6":"table-cell","url7":"table-cell"}' data-text-props='{"url1":"http://www.facebook.com/","url2":"https://mail.google.com/mail/?view=cm&to=yourmail@example.com&bcc=somemail@example.com","url3":"http://www.twitter.com/","url4":"http://www.youtube.com/","url5":"https://www.linkedin.com/","url6":"http://www.pinterest.com/","url7":"http://www.instagram.com/"}' cellspacing="0" cellpadding="0">
  				<tbody>
  					<tr>
	  					<td class="thwecmf-social-outer-td" align="center">
	  						<table class="thwecmf-social-inner-tb" cellspacing="0" cellpadding="0">
	  							<tr>
	  								<td class="thwecmf-social-td thwecmf-td-fb">
				  						<p class="thwecmf-social-icon"><a href="http://www.facebook.com" class="facebook">
				      						<img src="<?php echo esc_url(TH_WECMF_ASSETS_URL.'images/facebook.png'); ?>" alt="Facebook Icon" width="40" height="40">
				      					</a></p>
				      				</td>
						      		<td class="thwecmf-social-td thwecmf-td-mail">
										<p class="thwecmf-social-icon"><a href="https://mail.google.com/mail/?view=cm&to=yourmail@example.com&bcc=somemail@example.com" class="mail" >
											<img src="<?php echo esc_url(TH_WECMF_ASSETS_URL.'images/email.png'); ?>" alt="Google Icon" width="40" height="40">
										</a></p>
									</td>
									<td class="thwecmf-social-td thwecmf-td-tw">	
										<p class="thwecmf-social-icon"><a href="http://www.twitter.com" class="twitter">
											<img src="<?php echo esc_url(TH_WECMF_ASSETS_URL.'images/twitter.png'); ?>" alt="Twitter Icon" width="40" height="40">
										</a></p>
									</td>
									<td class="thwecmf-social-td thwecmf-td-yb">
										<p class="thwecmf-social-icon"><a href="http://www.youtube.com" class="youtube">
											<img src="<?php echo esc_url(TH_WECMF_ASSETS_URL.'images/youtube.png'); ?>" alt="Youtube Icon" width="40" height="40">
										</a></p>
									</td>
									<td class="thwecmf-social-td thwecmf-td-lin">
										<p class="thwecmf-social-icon"><a href="https://www.linkedin.com" class="linkedin">
											<img src="<?php echo esc_url(TH_WECMF_ASSETS_URL.'images/linkedin.png'); ?>" alt="Linkedin Icon" width="40" height="40">
										</a></p>
									</td>
									<td class="thwecmf-social-td thwecmf-td-pin">
										<p class="thwecmf-social-icon"><a href="http://www.pinterest.com" class="pinterest">
											<img src="<?php echo esc_url(TH_WECMF_ASSETS_URL.'images/pinterest.png'); ?>" alt="Pinterest Icon" width="40" height="40">
										</a></p>
									</td>
									<td class="thwecmf-social-td thwecmf-td-insta">
					  					<p class="thwecmf-social-icon"><a href="http://www.instagram.com" class="instagram">
					    					<img src="<?php echo esc_url(TH_WECMF_ASSETS_URL.'/images/instagram.png'); ?>" alt="Instagram Icon" width="40" height="40">
					  					</a></p>
					  				</td>
	  							</tr>
	  						</table>
	  					</td>
	  				</tr>
	  			</tbody>
  			</table>
		</div>
		<?php
	}

	/**
	 * Render button element content
	 */
	private function render_elm_button(){
		?>
		<div id="thwecmf_template_elm_button" style="display:none;">
       		<table cellspacing="0" cellpadding="0" class="thwecmf-block thwecmf-button-wrapper-table thwecmf-builder-block" id="{button}" data-block-name="button" align="center" data-css-props='{"size_width":"80px","size_height":"20px","text_align":"center","color":"#fff","font_size":"13px","line_height":"150%","font_family":"helvetica","font_weight":"400","p_t":"10px","p_r":"0px","p_b":"10px","p_l":"0px","m_t":"0px","m_r":"auto","m_b":"0px","m_l":"auto","content_p_t":"10px","content_p_r":"0px","content_p_b":"10px","content_p_l":"0px","b_t":"1px","b_r":"1px","b_b":"1px","b_l":"1px","border_style":"solid","border_color":"#4169e1","bg_color":"#4169e1","content":"","url":""}' data-text-props='{"content":"Click Here","url":"#"}'>
              <tr>
                  <td class="thwecmf-button-wrapper">
                      	<a href="#" title="Title text" class="thwecmf-button-link" style="text-decoration: none;">Click Here</a>
                  </td>
              </tr>
          </table>
		</div>
		<?php
	}

	/**
	 * Render gif element content
	 */
	private function render_elm_gif(){
		?>
		<div id="thwecmf_template_elm_gif" style="display:none;">
        	<table class="thwecmf-block thwecmf-block-gif thwecmf-builder-block" id="{gif}" data-block-name="gif" data-css-props='{"p_t":"10px","p_r":"10px","p_b":"10px","p_l":"10px","bg_color":"","img_size_width":"50%","img_size_height":"auto","content_align":"center","upload_img_url":""}' data-text-props='{"upload_img_url":""}' cellpadding="0" cellspacing="0">
		    	<tr>
		    		<td class="thwecmf-gif-column">
      					<p>
      						<img src="<?php echo esc_url( TH_WECMF_ASSETS_URL.'images/placeholder.png'); ?>" alt="Default" />
      					</p>
      				</td>
      			</tr>
      		</table>

		</div>
		<?php
	}

	/**
	 * Render email header hook content
	 */
	private function render_hook_email_header(){
		?>
		<div id="thwecmf_template_hook_email_header" style="display:none;">
			<p class="thwecmf-hook-code" id="{email_header}">{email_header_hook}</p>
		</div>
		<?php
	}

	/**
	 * Render email order details hook content
	 */
	private function render_hook_email_order_details(){
		?>
		<div id="thwecmf_template_hook_email_order_details" style="display:none;">
			<p class="thwecmf-hook-code" id="{email_order_details}">{email_order_details_hook}</p>
		</div>
		<?php		
	}

	/**
	 * Render email before order table hook content
	 */
	private function render_hook_before_order_table(){
		?>
		<div id="thwecmf_template_hook_before_order_table" style="display:none;">
			<p class="thwecmf-hook-code" id="{before_order_table}">{before_order_table_hook}</p>
		</div>
		<?php		
	}

	/**
	 * Render email after order table hook content
	 */
	private function render_hook_after_order_table(){
		?>
		<div id="thwecmf_template_hook_after_order_table" style="display:none;">
			<p class="thwecmf-hook-code" id="{after_order_table}">{after_order_table_hook}</p>
		</div>
		<?php		
	}

	/**
	 * Render email order meta hook content
	 */
	private function render_hook_order_meta(){
		?>
		<div id="thwecmf_template_hook_order_meta" style="display:none;">
			<p class="thwecmf-hook-code" id="{order_meta}">{order_meta_hook}</p>
		</div>
		<?php		
	}

	/**
	 * Render email customer details hook content
	 */
	private function render_hook_customer_details(){
		?>
		<div id="thwecmf_template_hook_customer_details" style="display:none;">
			<p class="thwecmf-hook-code" id="{customer_details}">{customer_details_hook}</p>
		</div>
		<?php		
	}

	/**
	 * Render email footer hook content
	 */
	private function render_hook_email_footer(){
		?>
		<div id="thwecmf_template_hook_email_footer" style="display:none;">
			<p class="thwecmf-hook-code" id="{email_footer}">{email_footer_hook}</p>
		</div>
		<?php		
	}

	/**
	 * Render sidebar layers row content
	 *
	 */
	private function render_track_row_content(){
		?>
		<div id="thwecmf_tracking_panel_row_html" style="display:none;">	
			<div class="thwecmf-layout-lis-item thwecmf-sortable-row-handle">
				<span class="thwecmf-row-name">Row</span>
				<div class="thwecmf-block-settings">
					<img class="thwecmf-template-action-edit" onclick="thwecmfEditBuilderBlocks(this, '{bl_id}', '{bl_name}')" src="<?php echo esc_url( TH_WECMF_ASSETS_URL.'images/pencil.png' );?>" style="margin-right: 1px;" alt="Edit" data-icon-attr="{bl_name}">
					<img class="thwecmf-template-action-delete" onclick="thwecmfDeleteBuilderBlocks(this)" src="<?php echo esc_url(TH_WECMF_ASSETS_URL.'images/delete-button.png' );?>" alt="Delete">
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Render sidebar layers column content
	 *
	 */
	private function render_track_col_content(){
		?>
		<div id="thwecmf_tracking_panel_col_html" style="display:none;">	
			<div class="thwecmf-layout-lis-item sortable-col-handle">
				<span class="thwecmf-column-name" title="Click here to toggle">Column</span>
				<div class="thwecmf-block-settings">
					<img class="thwecmf-template-action-edit" onclick="thwecmfEditBuilderBlocks(this, '{bl_id}', '{bl_name}')" src="<?php echo esc_url(TH_WECMF_ASSETS_URL.'images/pencil.png');?>" style="margin-right: 1px;" alt="Edit" data-icon-attr="{bl_name}">
					<img class="thwecmf-template-action-delete" onclick="thwecmfDeleteBuilderBlocks(this)" src="<?php echo esc_url(TH_WECMF_ASSETS_URL.'images/delete-button.png' );?>" alt="Delete">
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Render sidebar layers element content
	 *
	 */
	private function render_track_elm_content(){
		?>
		<div id="thwecmf_tracking_panel_elm_html" style="display:none;">	
			<div class="thwecmf-layout-lis-item sortable-elm-handle">
				<span class="thwecmf-element-name" title="Click here to toggle">{name}</span>
				<div class="thwecmf-block-settings">
					<img class="thwecmf-template-action-edit" onclick="thwecmfEditBuilderBlocks(this, '{bl_id}', '{bl_name}')" src="<?php echo esc_url( TH_WECMF_ASSETS_URL.'images/pencil.png' );?>" style="margin-right: 1px;" alt="Edit" data-icon-attr="{bl_attr_name}">
					<img class="thwecmf-template-action-delete" onclick="thwecmfDeleteBuilderBlocks(this)" src="<?php echo esc_url( TH_WECMF_ASSETS_URL.'images/delete-button.png' );?>" alt="Delete">
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Render sidebar layers hook content
	 *
	 */
	private function render_track_hook_content(){
		?>
		<div id="thwecmf_tracking_panel_hook_html" style="display:none;">	
			<div class="thwecmf-layout-lis-item sortable-elm-handle">
				<span class="thwecmf-hook-name" title="Click here to toggle">{name}</span>
				<div class="thwecmf-block-settings">
					<img class="thwecmf-template-action-delete" onclick="thwecmfDeleteBuilderBlocks(this)" src="<?php echo esc_url( TH_WECMF_ASSETS_URL.'images/delete-button.png' );?>" alt="Delete">
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Render builder confirmation alert form
	 *
	 */
	private function render_pp_confirmation_alerts(){
		?>
		<div id="thwecmf_confirmation_alerts" style="display: none;">
			<form name="thwecmf_confirmation_alert_form" id="thwecmf_confirmation_alert_form">
				<input type="hidden" name="i_thwecmf_column_reference" class="thwecmf-column-reference" value="">
				<input type="hidden" name="i_thwecmf_flag_reference" class="thwecmf-flag-reference" value="">
				<input type="hidden" name="i_thwecmf_column_id" class="thwecmf-column-id-reference" value="">
				<div class="thwecmf-confirmation-message-wrapper">
				<div class="thwecmf-messages"></div>
			</div>
			</form>
		</div>
		<?php
	}

	/**
	 * Clear builder confirmation message
	 *
	 */
	private function render_pp_confirmation_msg_line(){
		?>
		<div id="thwecmf_clear_builder_confirm" style="display: none;">
			All the unsaved changes will be lost. <br>Are you sure ?
		</div>
		<?php
	}

	/**
	 * Render builder blocks list
	 *
	 */
	private function panel_layout_elements_list(){
		?>
		<table class="thwecmf-tbuilder-elm-grid-layout-element">
			<tbody>
				<!-- Layouts  -->
				<tr>
					<td class="column-layouts">
						<div class="grid-category category-collapse">
							<p class="grid-title" onclick="thwecmfCollapseCategory(this)">Layouts<span class="dashicons dashicons-arrow-down-alt2 thwecmf-direction-arrow"></span>
							<div class="grid-content">
								<div class="thwecmf-elm-col">
									<div id="thwecmf-one-column" class="thwecmf-tbuilder-elm thwecmf_column_layout" data-block-name="one_column">
										<div class="thwecmf-elm-icon">
											<img src="<?php echo esc_url( TH_WECMF_ASSETS_URL.'images/one_column.png') ;?>" alt="One column">
										</div>
										<div class="thwecmf-elm-icon-text">1 Column</div>
									</div>
								</div>
								<div class="thwecmf-elm-col">
									<div id="thwecmf-two-column" class="thwecmf-tbuilder-elm column_layout" data-block-name="two_column">
										<div class="thwecmf-elm-icon">
											<img src="<?php echo esc_url( TH_WECMF_ASSETS_URL.'images/two_column.png') ;?>" alt="Two column">
										</div>
										<div class="thwecmf-elm-icon-text">
											<p>2 Column</p>
										</div>
									</div>
								</div>
								<div class="thwecmf-elm-col">
									<div id="thwecmf-three-column" class="thwecmf-tbuilder-elm column_layout" data-block-name="three_column">
										<div class="thwecmf-elm-icon">
											<img src="<?php echo esc_url( TH_WECMF_ASSETS_URL.'images/three_column.png' ); ?>" alt="Three column">
										</div>
										<div class="thwecmf-elm-icon-text">
											<p>3 Column</p>
										</div>
									</div>
								</div>
								<div class="thwecmf-elm-col">
									<div id="thwecmf-four-column" class="thwecmf-tbuilder-elm column_layout" data-block-name="four_column">
										<div class="thwecmf-elm-icon">
											<img src="<?php echo esc_url( TH_WECMF_ASSETS_URL.'images/four_column.png'); ?>" alt="Four column">
										</div>
										<div class="thwecmf-elm-icon-text">
											<p>4 Column</p>
										</div>
									</div>
								</div>
							</div>
						</div>
					</td>
				</tr>
				<tr class="section-gap"><td></td></tr>
				<tr>
					<td class="column-basic-elements">
						<div class="grid-category">
							<p class="grid-title" onclick="thwecmfCollapseCategory(this)">Basic Elements<span class="dashicons dashicons-arrow-down-alt2 thwecmf-direction-arrow"></span></p>
							<div class="grid-content">
								<div class="thwecmf-elm-col">
									<div id="thwecmf_text" class="thwecmf-tbuilder-elm block_element" data-block-name="text">
										<div class="thwecmf-elm-icon">
											<img src=" <?php echo esc_url( TH_WECMF_ASSETS_URL.'images/text.svg'); ?>" alt="Text">
										</div>
										<div class="thwecmf-elm-icon-text"><br>Text</div>
									</div>
								</div>
								<div class="thwecmf-elm-col">
									<div id="thwecmf_image" class="thwecmf-tbuilder-elm block_element" data-block-name="image">
										<div class="thwecmf-elm-icon">
											<img src=" <?php echo esc_url( TH_WECMF_ASSETS_URL.'images/image.svg'); ?>" alt="Image">
										</div>
										<div class="thwecmf-elm-icon-text"><br>Image</div>
									</div>
								</div>
								<div class="thwecmf-elm-col">
									<div id="thwecmf_divider" class="thwecmf-tbuilder-elm block_element" data-block-name="divider">
										<div class="thwecmf-elm-icon">
											<img src=" <?php echo esc_url( TH_WECMF_ASSETS_URL.'images/divider.svg'); ?>" alt="Divider">
										</div>
										<div class="thwecmf-elm-icon-text"><br>Divider</div>
									</div>
								</div>
								<div class="thwecmf-elm-col">
									<div id="thwecmf_gap" class="thwecmf-tbuilder-elm block_element" data-block-name="gap">
										<div class="thwecmf-elm-icon">
											<img src=" <?php echo esc_url( TH_WECMF_ASSETS_URL.'images/gap.svg' ); ?>" alt="Gap">
										</div>
										<div class="thwecmf-elm-icon-text"><br>Gap</div>
									</div>
								</div>
								<div class="thwecmf-elm-col">
									<div id="thwecmf_social" class="thwecmf-tbuilder-elm block_element" data-block-name="social">
										<div class="thwecmf-elm-icon">
											<img src=" <?php echo esc_url( TH_WECMF_ASSETS_URL.'images/social.svg' );?>" alt="Social icons">
										</div>
										<div class="thwecmf-elm-icon-text"><br>Social</div>
									</div>
								</div>
								<div class="thwecmf-elm-col">
									<div id="thwecmf_button" class="thwecmf-tbuilder-elm block_element" data-block-name="button">
										<div class="thwecmf-elm-icon">
											<img src=" <?php echo esc_url( TH_WECMF_ASSETS_URL.'images/button.svg') ;?>" alt="Button">
										</div>
										<div class="thwecmf-elm-icon-text"><br>Button</div>
									</div>
								</div>
								<div class="thwecmf-elm-col">
									<div id="thwecmf_gif" class="thwecmf-tbuilder-elm block_element" data-block-name="gif">
										<div class="thwecmf-elm-icon">
											<img src=" <?php echo esc_url(TH_WECMF_ASSETS_URL.'images/gif.svg') ;?>" alt="Gif">
										</div>
										<div class="thwecmf-elm-icon-text"><br>Gif</div>
									</div>
								</div>
								
							</div>
						</div>
					</td>
				</tr>
				<tr class="section-gap"><td></td></tr>
				<tr>
					<td class="woocommerce-elements">
						<div class="grid-category category-collapse">
							<p class="grid-title" onclick="thwecmfCollapseCategory(this)">WooCommerce Elements<span class="dashicons dashicons-arrow-down-alt2 thwecmf-direction-arrow"></span></p>
							<div class="grid-content">
								<div class="thwecmf-elm-col">
									<div id="thwecmf_header_details" class="thwecmf-tbuilder-elm block_element" data-block-name="header_details">
										<div class="thwecmf-elm-icon">
											<img src="<?php echo esc_url( TH_WECMF_ASSETS_URL.'images/header.svg' );?>" alt="Header">
										</div>
										<div class="thwecmf-elm-icon-text"><br>Header</div>
									</div>
								</div>
								<div class="thwecmf-elm-col">
									<div id="thwecmf_customer_address" class="thwecmf-tbuilder-elm block_element" data-block-name="customer_address">
										<div class="thwecmf-elm-icon">
											<img src=" <?php echo esc_url( TH_WECMF_ASSETS_URL.'images/customer-details.svg' );?>" alt="Customer details">
										</div>
										<div class="thwecmf-elm-icon-text"><br>Customer</div>
									</div>
								</div>
								<div class="thwecmf-elm-col">
									<div id="thwecmf_order_details" class="thwecmf-tbuilder-elm block_element" data-block-name="order_details">
										<div class="thwecmf-elm-icon">
											<img src=" <?php echo esc_url( TH_WECMF_ASSETS_URL.'images/order.svg'); ?>" alt="Order table">
										</div>
										<div class="thwecmf-elm-icon-text"><br>Order</div>
									</div>
								</div>
								<div class="thwecmf-elm-col">
									<div id="thwecmf_billing_address" class="thwecmf-tbuilder-elm block_element" data-block-name="billing_address">
										<div class="thwecmf-elm-icon">
											<img src=" <?php echo esc_url( TH_WECMF_ASSETS_URL.'images/billing-details.svg' );?>" alt="Blling details">
										</div>
										<div class="thwecmf-elm-icon-text"><br>Billing</div>
									</div>
								</div>
								<div class="thwecmf-elm-col">
									<div id="thwecmf_shipping_address" class="thwecmf-tbuilder-elm block_element" data-block-name="shipping_address">
										<div class="thwecmf-elm-icon">
											<img src=" <?php echo esc_url(TH_WECMF_ASSETS_URL.'images/shipping-details.svg' );?>" alt="Shipping details">
										</div>
										<div class="thwecmf-elm-icon-text"><br>Shipping</div>
									</div>
								</div>
								<div class="thwecmf-elm-col">
									<div id="thwecmf_woocommerce_email_footer" class="thwecmf-tbuilder-elm block_element hook_element" data-block-name="downloadable_product">
										<div class="thwecmf-elm-icon">
											<img src="<?php echo esc_url( TH_WECMF_ASSETS_URL.'images/downloadable-product.svg' );?>" alt="Downloadable product" >
										</div>
										<div class="thwecmf-elm-icon-text">Downloadable Product</div>
									</div>
								</div>
							</div>
						</div>
					</td>
				</tr>
				<tr class="section-gap"><td></td></tr>
				<tr>
					<td class="woocommerce-hooks">
						<div class="grid-category category-collapse">
							<p class="grid-title" onclick="thwecmfCollapseCategory(this)">WooCommerce Hooks<span class="dashicons dashicons-arrow-down-alt2 thwecmf-direction-arrow"></span></p>
							<div class="grid-content">
								<div class="thwecmf-elm-col">
									<div id="thwecmf_woocommerce_email_header" class="thwecmf-tbuilder-elm block_element hook_element" data-block-name="email_header">
										<div class="thwecmf-elm-icon">
											<img src="<?php echo esc_url( TH_WECMF_ASSETS_URL.'images/email-header-hook.svg' );?>" alt="Email header hook" >
										</div>
										<div class="thwecmf-elm-icon-text"><br>Email Header</div>
									</div>			
								</div>
								<div class="thwecmf-elm-col">
									<div id="thwecmf_woocommerce_email_order_details" class="thwecmf-tbuilder-elm block_element hook_element" data-block-name="email_order_details">
										<div class="thwecmf-elm-icon">
											<img src="<?php echo esc_url( TH_WECMF_ASSETS_URL.'images/email-order-details-hook.svg') ;?>" alt="Order details hook" >
										</div>
										<div class="thwecmf-elm-icon-text">Email Order Details</div>
									</div>	
								</div>
								<div class="thwecmf-elm-col">
									<div id="thwecmf_woocommerce_email_before_order_table" class="thwecmf-tbuilder-elm block_element hook_element" data-block-name="before_order_table">
										<div class="thwecmf-elm-icon">
											<img src="<?php echo esc_url( TH_WECMF_ASSETS_URL.'images/before-order-table-hook.svg') ;?>" alt="Before Order table hook" >
										</div>
										<div class="thwecmf-elm-icon-text">Before <br>Order Table</div>
									</div>		
								</div>
								<div class="thwecmf-elm-col">
									<div id="thwecmf_woocommerce_email_after_order_table" class="thwecmf-tbuilder-elm block_element hook_element" data-block-name="after_order_table">
										<div class="thwecmf-elm-icon">
											<img src="<?php echo esc_url( TH_WECMF_ASSETS_URL.'images/after-order-table-hook.svg' );?>" alt="After order table hook" >
										</div>
										<div class="thwecmf-elm-icon-text">After <br>Order Table</div>
									</div>		
								</div>
								<div class="thwecmf-elm-col">
									<div id="thwecmf_woocommerce_email_order_meta" class="thwecmf-tbuilder-elm block_element hook_element" data-block-name="order_meta">
										<div class="thwecmf-elm-icon">
											<img src="<?php echo esc_url( TH_WECMF_ASSETS_URL.'images/order-meta.svg' );?>" alt="Order meta hook" >
										</div>
										<div class="thwecmf-elm-icon-text"><br>Order Meta</div>
									</div>
								</div>
								<div class="thwecmf-elm-col">
									<div id="thwecmf_woocommerce_email_customer_address" class="thwecmf-tbuilder-elm block_element hook_element" data-block-name="customer_details">
										<div class="thwecmf-elm-icon">
											<img src="<?php echo esc_url( TH_WECMF_ASSETS_URL.'images/customer-details-hook.svg' );?>" alt="Customer details" >
										</div>
										<div class="thwecmf-elm-icon-text"><br>Customer Details</div>
									</div>		
								</div>
								<div class="thwecmf-elm-col">
									<div id="thwecmf_woocommerce_email_footer" class="thwecmf-tbuilder-elm block_element hook_element" data-block-name="email_footer">
										<div class="thwecmf-elm-icon">
											<img src="<?php echo esc_url( TH_WECMF_ASSETS_URL.'images/email-footer-hook.svg' );?>" alt="Email footer hook" >
										</div>
										<div class="thwecmf-elm-icon-text"><br>Email Footer</div>
									</div>		
								</div>
							</div>
						</div>
					</td>
				</tr>
			</tbody>
		</table>
		<?php
	}	

	/**
	 * Render builder sidebar content
	 *
	 * @param string $template_json template data json string
	 */
	private function render_template_builder_panel_configure($template_json){
		$toggle_layers = $template_json ? 'thwecmf-layers-toggle' : '';
		?>
		<div class="thwecmf-layers-outer-wrapper">
			<div class="thwecmf-layers-inner-wrapper">
				<table class="thwecmf-sidebar-config-elm-layers">
					<thead>
						<tr>
							<td class="thwecmf-configure-title"><b>Configure your email template</b></td>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td>
								<p class="thwecmf-empty-layer-msg <?php echo esc_attr( $toggle_layers ); ?>">Click on <strong>Add Row</strong> button to start building your email template.</p>
								<div class="thwecmf-builder-elm-layers">
									<?php 
									if($template_json){
										$this->thwecmf_json_tree_creator($template_json); 
									}
									?>
								</div>
							</td>
						</tr>	
						<tr>
							<td>
								<button type="button" onclick="thwecmfClickAddRow(this)" class="thwecmf-sidebar-add-row">Click to add a row</button>						
							</td>
						</tr>												
					</tbody>
				</table>
			</div>
		</div>
		<!-- Remove wecm-disclaimer table once outlook compatibility is fixed -->
		<table class="wecmf-disclaimer">
			<tr>
				<td>
					<p class="wecmf-disclaimer-text"><span class="dashicons dashicons-info wecmf-disclaimer-icon"></span> Email rendering in Outlook may differ due to limited style support.</p>
				</td>
			</tr>
		</table>
		<?php
	}

	/**
	 * Render sidebar content from json
	 *
	 * @param string $template_json template data json string
	 */
	private function thwecmf_json_tree_creator($template_json){
		$json_row = json_decode($template_json);
		$row_count = $json_row->row;
		if($json_row->row){
			foreach ($json_row->row as $row_child) {
				?>
				<div id="<?php echo esc_attr( $row_child->data_id ); ?>" class="thwecmf-rows thwecmf-panel-builder-block" data-columns="<?php echo esc_attr( $row_child->data_count );?>">
					<div class="thwecmf-layout-lis-item thwecmf-sortable-row-handle">
						<span class="thwecmf-row-name">Row</span>
						<div class="thwecmf-block-settings">
							<img class="thwecmf-template-action-edit" onclick="thwecmfEditBuilderBlocks(this, <?php echo esc_html( $row_child->data_id ); ?>, '<?php echo esc_html( $row_child->data_name ); ?>')" src="<?php echo esc_url( TH_WECMF_ASSETS_URL.'images/pencil.png' );?>" style="margin-right: 1px;" alt="Edit" data-icon-attr="<?php echo esc_attr( $row_child->data_name ); ?>">
							<img class="thwecmf-template-action-delete" onclick="thwecmfDeleteBuilderBlocks(this)" src="<?php echo esc_url( TH_WECMF_ASSETS_URL.'images/delete-button.png' );?>" alt="Delete">
						</div>
					</div>
					<div class="thwecmf-column-set">
					<?php 
						if(count($row_child->child) > 0 && $row_child->child[0]->data_type =='column'){
							foreach ($row_child->child as $child_col) {
								?>
								<div id="<?php echo esc_attr( $child_col->data_id ); ?>" class="thwecmf-columns thwecmf-panel-builder-block" data-parent="<?php echo esc_attr( $row_child->data_id ); ?>">	
									<div class="thwecmf-layout-lis-item sortable-col-handle">
										<span class="thwecmf-column-name" title="Click here to toggle">Column</span>
										<div class="thwecmf-block-settings">
											<img class="thwecmf-template-action-edit" onclick="thwecmfEditBuilderBlocks(this, <?php echo esc_html( $child_col->data_id ); ?>, '<?php echo esc_html( $child_col->data_name ); ?>')" src="<?php echo esc_url( TH_WECMF_ASSETS_URL.'images/pencil.png' ); ?>" style="margin-right: 1px;" alt="Edit" data-icon-attr="<?php echo esc_attr( $child_col->data_name ); ?>">
											<img class="thwecmf-template-action-delete" onclick="thwecmfDeleteBuilderBlocks(this)" src="<?php echo esc_url( TH_WECMF_ASSETS_URL.'images/delete-button.png' ); ?>" alt="Delete">
										</div>
									</div>
									<div class="thwecmf-element-set">
										<div class="thwecmf-hidden-sortable thwecmf-elements"></div>
										<?php
										if(count($child_col->child) > 0){
											foreach ($child_col->child as $child_elm) {
												if($child_elm->data_type == 'element'){
												?>
													<div id="<?php echo esc_attr( $child_elm->data_id ); ?>" class="thwecmf-elements thwecmf-panel-builder-block">	
														<div class="thwecmf-layout-lis-item sortable-elm-handle">
															<span class="thwecmf-element-name" title="Click here to toggle"><?php echo esc_attr( $child_elm->child ); ?></span>
															<div class="thwecmf-block-settings">
																<img class="thwecmf-template-action-edit" onclick="thwecmfEditBuilderBlocks(this, <?php echo esc_html( $child_elm->data_id ); ?>, '<?php echo esc_html( $child_elm->data_name ); ?>')" src="<?php echo esc_url( TH_WECMF_ASSETS_URL.'images/pencil.png' ); ?>" style="margin-right: 1px;" alt="Edit" data-icon-attr="<?php echo esc_attr( $child_elm->data_name ); ?>">
																<img class="thwecmf-template-action-delete" onclick="thwecmfDeleteBuilderBlocks(this)" src="<?php echo esc_url( TH_WECMF_ASSETS_URL.'images/delete-button.png' );?>" alt="Delete">
															</div>
														</div>
													</div>
												<?php
												}else if($child_elm->data_type == 'hook'){
												?>
													<div id="<?php echo esc_attr( $child_elm->data_id );?>" class="thwecmf-hooks thwecmf-panel-builder-block">
														<div class="thwecmf-layout-lis-item sortable-elm-handle">
															<span class="thwecmf-hook-name" title="Click here to toggle"><?php echo esc_html( $child_elm->child ); ?></span>
															<div class="thwecmf-block-settings">
																<img class="thwecmf-template-action-delete" onclick="thwecmfDeleteBuilderBlocks(this)" src="<?php echo esc_url( TH_WECMF_ASSETS_URL.'images/delete-button.png' );?>" alt="Delete">
															</div>
														</div>
													</div>
												<?php
												}
											}
										}
										?>
										<div class="thwecmf-btn-add-element panel-add-btn panel-add-element"><a href="#">Add Element</a></div>
									</div>
								</div>
								<?php
							}
						}
						?>
					</div>
				</div>
			<?php
			}
		}
	}

	/**
	 * Render sidebar layout layers from json data
	 *
	 * @param object $row_obj block object
	 */
	private function layout_layers_from_json($row_obj){
		?>
		<div id="<?php echo esc_attr( $row_obj[0]->data_id ); ?>" class="thwecmf-rows thwecmf-panel-builder-block" data-columns="<?php echo esc_attr( $row_obj[0]->data_count );?>">	
			<div class="thwecmf-layout-lis-item thwecmf-sortable-row-handle">
				<span class="thwecmf-row-name">Row</span>
				<div class="thwecmf-block-settings">
					<img class="thwecmf-template-action-edit" onclick="thwecmfEditBuilderBlocks(this, <?php echo esc_html( $row_obj[0]->data_id ); ?>, '<?php echo esc_html( $row_obj[0]->data_name ); ?>')" src="<?php echo esc_url ( TH_WECMF_ASSETS_URL.'images/pencil.png' );?>" style="margin-right: 1px;" alt="Edit" data-icon-attr="<?php echo esc_attr( $row_obj[0]->data_name ); ?>">
					<img class="thwecmf-template-action-delete" onclick="thwecmfDeleteBuilderBlocks(this)" src="<?php echo esc_url( TH_WECMF_ASSETS_URL.'images/delete-button.png' );?>" alt="Delete">
				</div>
			</div>
			<div class="thwecmf-column-set">
				<?php
				if(count($row_obj[0]->child) > 0){
					foreach ($row_obj[0]->child as $col_key) {
						if($col_key->data_type == 'column'){
						?>
							<div id="<?php echo esc_attr( $col_key->data_id ); ?>" class="thwecmf-columns thwecmf-panel-builder-block" data-parent="<?php echo esc_attr( $col_key->data_id ); ?>">	
								<div class="thwecmf-layout-lis-item sortable-col-handle">
									<span class="thwecmf-column-name" title="Click here to toggle">Column</span>
									<div class="thwecmf-block-settings">
										<img class="thwecmf-template-action-edit" onclick="thwecmfEditBuilderBlocks(this, <?php echo esc_html( $col_key->data_id ); ?>, '<?php echo esc_html( $col_key->data_name ); ?>')" src="<?php echo esc_url( TH_WECMF_ASSETS_URL.'images/pencil.png' );?>" style="margin-right: 1px;" alt="Edit" data-icon-attr="<?php echo esc_attr( $col_key->data_name ); ?>">
										<img class="thwecmf-template-action-delete" onclick="thwecmfDeleteBuilderBlocks(this)" src="<?php echo esc_url( TH_WECMF_ASSETS_URL.'images/delete-button.png' );?>" alt="Delete">
									</div>
								</div>
								<div class="thwecmf-element-set">
									<div class="thwecmf-hidden-sortable thwecmf-elements"></div>
									<?php
									if(count($col_key->child) > 0){
										foreach ($col_key->child as $elm_key) {
											if(isset($elm_key->row) && count($elm_key->row[0]->child) > 0){
												$this->layout_layers_from_json($elm_key->row);
											}else if($elm_key->data_type == 'element'){
												
											?>
												<div id="<?php echo esc_attr( $elm_key->data_id ); ?>" class="thwecmf-elements thwecmf-panel-builder-block">	
													<div class="thwecmf-layout-lis-item sortable-elm-handle">
														<span class="thwecmf-element-name" title="Click here to toggle"><?php echo esc_html( $elm_key->data_name ); ?></span>
														<div class="thwecmf-block-settings">
															<img class="thwecmf-template-action-edit" onclick="thwecmfEditBuilderBlocks(this, <?php echo esc_html( $elm_key->data_id ); ?>, '<?php echo esc_html( $elm_key->data_name ); ?>')" src="<?php echo esc_url( TH_WECMF_ASSETS_URL.'images/pencil.png' );?>" style="margin-right: 1px;" alt="Edit" data-icon-attr="<?php echo esc_attr( $elm_key->data_name); ?>">
															<img class="thwecmf-template-action-delete" onclick="thwecmfDeleteBuilderBlocks(this)" src="<?php echo esc_url( TH_WECMF_ASSETS_URL.'images/delete-button.png' );?>" alt="Delete">
														</div>
													</div>
												</div>
												<?php
											}else{
												?>
												<div id="<?php echo esc_attr( $elm_key->data_id ); ?>" class="thwecmf-hooks thwecmf-panel-builder-block">
													<div class="thwecmf-layout-lis-item sortable-elm-handle">
														<span class="thwecmf-hook-name" title="Click here to toggle"><?php echo esc_attr( $elm_key->child ); ?></span>
														<div class="thwecmf-block-settings">
															<img class="thwecmf-template-action-delete" onclick="thwecmfDeleteBuilderBlocks(this)" src="<?php echo esc_url( TH_WECMF_ASSETS_URL.'images/delete-button.png' );?>" alt="Delete">
														</div>
													</div>
												</div>
												<?php
											}
										}
									}
									?>
									<div class="thwecmf-btn-add-element panel-add-btn panel-add-element">
										<a href="#">Add Element</a>
									</div>
								</div>
							</div>
						<?php
						}
					}
				}
				?>
			</div>
		</div>
		<?php
	}

	/**
	 * Render builder and builder blocks from json object
	 *
	 * @param string $template_json json string
	 */
	private function render_template_blocks_json($template_json){
		$this->template_json_css = '';
		$builder_data = json_decode($template_json);
		$this->template_json_css .= $this->prepare_css_from_json($builder_data);
		?>
		<table cellpadding="0" cellspacing="0" border="0" width="600" id="tbf_t_builder" class="thwecmf-dropable sortable thwecmf-main-builder" data-global-id="<?php echo esc_attr( $builder_data->track_save ); ?>" data-track-save="<?php echo esc_attr( $builder_data->track_save ); ?>" data-css-change="true" data-css-props='<?php echo esc_attr( $builder_data->data_css );?>'>
			<tr>
				<td class="thwecmf-builder-column">
					<?php 
					$builder_row = $builder_data->row;
					if($builder_data->row && is_array( $builder_data->row )){
						foreach ($builder_data->row as $row_child) {
							$this->template_json_css .= $this->prepare_css_from_json($row_child);
							?>
							<table class="thwecmf-row thwecmf-block-<?php echo esc_attr(str_replace('_', '-', $row_child->data_name ) ); ?> thwecmf-builder-block" id="tbf_<?php echo esc_attr( $row_child->data_id ); ?>" data-css-props='<?php echo esc_attr( $row_child->data_css );?>' data-name="<?php echo esc_attr( $row_child->data_name );?>" data-column-count="<?php echo esc_attr( $row_child->data_count ); ?>"  cellpadding="0" cellspacing="0">
								<tbody>
									<tr>
										<?php
										if(count($row_child->child) > 0 && $row_child->child[0]->data_type =='column'){
											foreach ($row_child->child as $child_col) {
												$this->template_json_css .= $this->prepare_css_from_json($child_col);
											?>
												<td class="thwecmf-column-padding thwecmf-col thwecmf-columns" id="tbf_<?php echo esc_attr( $child_col->data_id );?>" data-css-props='<?php echo esc_attr( $child_col->data_css ); ?>' data-name="<?php echo esc_attr( $child_col->data_name );?>">
													<?php if(count($child_col->child) > 0){
														foreach ($child_col->child as $child_elm) {
															if(isset($child_elm->row) && count($child_elm->row[0]->child) > 0){
																$this->render_template_blocks_layout_json($child_elm->row);
															}else if($child_elm->data_type == 'element'){
																$this->template_json_css .= $this->prepare_css_from_json($child_elm);
																$this->render_builder_element_blocks($child_elm, $child_elm->data_name);
															}else{
																$this->render_builder_element_blocks($child_elm, $child_elm->data_name);
															}	
														}
													}else{
														echo '<span class="thwecmf-builder-add-btn thwecmf-btn-add-element">+ Add Element</span>';
													}?>
												</td>
											<?php
											}
										}
										?>
									</tr>
								</tbody>
							</table>
							<?php
						}
					}
					?>
				</td>
			</tr>
		</table>
	<?php
	}

	/**
	 * Render layout from json
	 *
	 * @param object $row_obj row object
	 */
	private function render_template_blocks_layout_json($row_obj){
		?>
		<table class="thwecmf-row thwecmf-block-<?php echo str_replace('_', '-', esc_attr( $row_obj[0]->data_name ) ); ?> thwecmf-builder-block" id="tbf_<?php echo esc_attr( $row_obj[0]->data_id ); ?>" data-css-props='<?php echo esc_attr( $row_obj[0]->data_css );?>' data-name="<?php echo esc_attr( $row_obj[0]->data_name );?>" data-column-count="<?php echo esc_attr( $row_obj[0]->data_count ); ?>"  cellpadding="0" cellspacing="0">
			<tbody>
				<tr>
					<?php
					$this->template_json_css .= $this->prepare_css_from_json($row_obj[0]);
					if(count($row_obj[0]->child) > 0){
						foreach ($row_obj[0]->child as $col_key) {
							$this->template_json_css .= $this->prepare_css_from_json($col_key);
							?>
							<td class="thwecmf-column-padding thwecmf-col thwecmf-columns" id="tbf_<?php echo esc_attr( $col_key->data_id );?>" data-css-props='<?php echo esc_attr( $col_key->data_css ); ?>' data-name="<?php echo esc_attr( $col_key->data_name );?>">
								<?php
								if(count($col_key->child) > 0){
									foreach ($col_key->child as $elm_key) {
										if(isset($elm_key->row) && count($elm_key->row[0]->child) > 0){
											$this->render_template_blocks_layout_json($elm_key->row);
										}else if($elm_key->data_type == 'element'){
											$this->template_json_css .= $this->prepare_css_from_json($elm_key);
											$this->render_builder_element_blocks($elm_key, $elm_key->data_name);
										}else{
											$this->render_builder_element_blocks($elm_key, $elm_key->data_name);
										}
									}
								}else{
									echo '<span class="thwecmf-builder-add-btn thwecmf-btn-add-element">+ Add Element</span>';
								}?>	
							</td>
						<?php
						}
					}
					?>
				</tr>
			</tbody>
		</table>
		<?php
	}

	/**
	 * Render element based on the data
	 *
	 * @param object $elm element properties
	 * @param string $elm_name element name 
	 */
	private function render_builder_element_blocks($elm, $elm_name){
		$content = '';
		switch (strtolower($elm_name)) {
			case 'text':
				$content = $this->render_builder_element_block_details_text($elm, $elm_name);
				break;
			
			case 'image':
				$content = $this->render_builder_element_block_details_image($elm, $elm_name);
				break;

			case 'divider':
				$content = $this->render_builder_element_block_details_divider($elm, $elm_name);
				break;

			case 'gap':
				$content = $this->render_builder_element_block_details_gap($elm, $elm_name);
				break;

			case 'social':
				$content = $this->render_builder_element_block_details_social($elm, $elm_name);
				break;

			case 'button':
				$content = $this->render_builder_element_block_details_button($elm, $elm_name);
				break;

			case 'gif':
				$content = $this->render_builder_element_block_details_gif($elm, $elm_name);
				break;

			case 'header_details':
				$content = $this->render_builder_element_block_details_header($elm, $elm_name);
				break;

			case 'customer_address':
				$content = $this->render_builder_element_block_details_customer($elm, $elm_name);
				break;

			case 'order_details':
				$content = $this->render_builder_element_block_details_order($elm, $elm_name);
				break;

			case 'billing_address':
				$content = $this->render_builder_element_block_details_billing($elm, $elm_name);
				break;

			case 'shipping_address':
				$content = $this->render_builder_element_block_details_shipping($elm, $elm_name);
				break;

			case 'downloadable_product_table':
				$content = $this->render_builder_element_block_details_downloadable_product($elm, $elm_name);
				break;

			case 'email_header_hook':
				$content = $this->render_builder_element_block_details_email_header_hook($elm, $elm_name);
				break;

			case 'email_order_details_hook':
				$content = $this->render_builder_element_block_details_email_order_details_hook($elm, $elm_name);
				break;

			case 'before_order_table_hook':
				$content = $this->render_builder_element_block_details_before_order_table_hook($elm, $elm_name);
				break;

			case 'after_order_table_hook':
				$content = $this->render_builder_element_block_details_after_order_table_hook($elm, $elm_name);
				break;

			case 'order_meta_hook':
				$content = $this->render_builder_element_block_details_order_meta_hook($elm, $elm_name);
				break;

			case 'customer_details_hook':
				$content = $this->render_builder_element_block_details_customer_address_hook($elm, $elm_name);
				break;

			case 'email_footer_hook':
				$content = $this->render_builder_element_block_details_email_footer_hook($elm, $elm_name);
				break;
			case 'downloadable_product':

			default:$content ='';
				break;
		}
		echo wp_kses( trim( $content ),wp_kses_allowed_html('post') );
	}

	/**
	 * Wrap text content in div
	 *
	 * @param string $content text element content
	 * @return string $data wrapped content
	 */
	private function wrapper_textarea_content($content){
		$data = '';
		$content_arr = preg_split("/(\r\n|\n|\r)/",$content);
		foreach ($content_arr as $key => $value) {
			$data .= '<div class="wecmf-txt-wrap">'.html_entity_decode( wp_kses( trim( $value ),wp_kses_allowed_html('post') ) ).'<br></div>';
		}
		return $data;
	}

	/**
	 * Prepare css from json data
	 *
	 * @param object $block_obj css property shortname
	 * @return string element inline style
	 */
	private function prepare_css_from_json($block_obj){
		$block_css = '';
		$type = isset($block_obj->data_type) ? $block_obj->data_type : false ;
		$json_css = isset($block_obj->data_css) ? json_decode($block_obj->data_css,true) : false;
		$id = '#tbf_'.$block_obj->data_id;
		if($json_css && $type){
			if($type == 'builder'){
				$block_css.= $id.' .thwecmf-builder-column{';
				foreach($json_css as $key => $value){
					if(isset($this->css_props[$key])){
						$property = $this->css_props[$key];
						if( empty($value) && array_key_exists($property, $this->default_css)){
							$value = $this->default_css[$property]; 
						}
						$block_css.= $property.':'.$value.';';
					}
				}
				$block_css.= '}';
			}else{
				$name = isset($block_obj->data_name) ? $block_obj->data_name : '';
				if($json_css){
					if(array_key_exists($type, $this->json_css_class)){
						$css_name = $this->json_css_class[$type];
						$default_props = WECMF_Email_Customizer_Utils::get_layout_props( $name );
						$block_css.= $id.'.'.$css_name.'{';
						foreach($default_props as $key => $value){
							if(isset($this->css_props[$key])){
								$property = $this->css_props[$key];
								$value = isset( $json_css[$key] ) ? $json_css[$key] : $value;
								if( empty($value) && array_key_exists($property, $this->default_css)){
									$value = $this->default_css[$property]; 
								}
								$block_css.= $property.':'.$value.';';
							}
						}
						$block_css.= '}';
					}else{
						if(isset($this->css_elm_props_map[$name])){
							$default_props = WECMF_Email_Customizer_Utils::css_elm_props( $name );
							foreach ($this->css_elm_props_map[$name] as $child_class => $index_value) {
								$block_css.= $id.$child_class.'{';
								foreach ($index_value as $css_attr) {
									$property = '';
									$css_value = ''; 
									if( isset($json_css[$css_attr]) && isset($this->css_props[$css_attr]) ){
										$property = $this->css_props[$css_attr];
										$block_css.= $this->get_element_style( $css_attr, $css_value, $json_css, $property );
									}else if( isset($default_props[$css_attr]) && isset($this->css_props[$css_attr])){
										$property = $this->css_props[$css_attr];
										$block_css.= $this->get_element_style( $css_attr, $css_value, $default_props, $property );
									}
								}
								$block_css.= '}';
							}
						}
					}
				}
			}
		}
		return $block_css;
	}

	/**
	 * Get style properties of element from json
	 *
	 * @param object $attr css property shortname
	 * @param object $value css property value
	 * @param array $props json css property shortname & value
	 * @param string $property css property
	 * @return string element inline style
	 */
	private function get_element_style( $attr, $value, $props, $property ){
		$property = $this->css_props[$attr];
		if( array_key_exists( $props[$attr], $this->font_family_list ) ){
			$value = $this->font_family_list[$props[$attr]];
		}else{
			$value = $props[$attr];
		}
		if( empty($value) && array_key_exists($property, $this->default_css)){
			$value = $this->default_css[$property]; 
		}
		return $this->css_props[$attr].':'.$value.';';
	}

	/**
	 * format textarea content
	 *
	 * @param object $obj text element object
	 * @return string $formatted_text formated text
	 */
	private function clean_textarea_contents_for_html($obj){
		$formatted_text = '';
		if($obj){
			$formatted_text = WECMF_Email_Customizer_Utils::thwecmf_is_json_decode($obj);
			if($formatted_text && isset($formatted_text->textarea_content)){
				$f_text = $formatted_text->textarea_content;
				$f_text = str_replace("'","&#39;", $f_text);
				$f_text = str_replace('"',"&quot;", $f_text);
				$formatted_text->textarea_content = $f_text;
			}
			$formatted_text = json_encode($formatted_text);
		}
		return $formatted_text;
	}

	/**
	 * Render text element block
	 *
	 * @param object $elm element properties
	 * @param string $elm_name element name 
	 */
	private function render_builder_element_block_details_text($elm, $elm_name){
		$content = '';
		$data_id = isset($elm->data_id) ? $elm->data_id : "";
		$data_css = isset($elm->data_css) ? $elm->data_css : "";
		$data_text_props = isset($elm->data_text) ? $elm->data_text: "";
		$json_txt_props = $data_text_props ? WECMF_Email_Customizer_Utils::thwecmf_is_json_decode($data_text_props) : false; 
		if(json_last_error() !== 0 && !$json_txt_props){
			$data_text_props = str_replace("\n","\\n",$data_text_props);
			$json_text = json_decode($data_text_props);
			$content .= $json_text->textarea_content;
		}else{
			$content .= isset($json_txt_props->textarea_content) ? $json_txt_props->textarea_content : "";
		}
		$content = $content ? $this->wrapper_textarea_content($content) : "";
		// $content = $content ? html_entity_decode($content) : "";
		?>
		<table class="thwecmf-block thwecmf-block-text thwecmf-builder-block" id="tbf_<?php echo esc_attr($data_id); ?>" data-block-name="<?php echo esc_attr( $elm_name );?>" data-css-props='<?php echo esc_attr( $data_css );?>' data-text-props='<?php echo htmlentities($data_text_props, ENT_QUOTES);?>' cellspacing="0" cellpadding="0">
			<tr>
				<td class="thwecmf-block-text-holder">
					<?php echo wp_kses( nl2br( $content ),wp_kses_allowed_html('post') ) ?>
				</td>
			</tr>
		</table>
		<?php
	}

	/**
	 * Render image element block
	 *
	 * @param object $elm element properties
	 * @param string $elm_name element name 
	 */
	private function render_builder_element_block_details_image($elm, $elm_name){
		if(isset($elm->data_text)){
			$content = WECMF_Email_Customizer_Utils::thwecmf_is_json_decode($elm->data_text);
			if($content){
				$image = isset($content->upload_img_url) && $content->upload_img_url !=='' ? $content->upload_img_url : esc_url( TH_WECMF_ASSETS_URL.'/images/placeholder.png' );
			}
		}
		$data_id = isset($elm->data_id) ? $elm->data_id : "";
		$data_css = isset($elm->data_css) ? $elm->data_css : "";
		$misc_css = isset($elm->data_misc) ? $elm->data_misc : false;
		$css_decode = !empty($elm->data_css) ? json_decode($elm->data_css) : false;
		$content_align = $css_decode ? $css_decode->content_align : "";
		$misc_obj = $misc_css ? WECMF_Email_Customizer_Utils::thwecmf_is_json_decode($misc_css) : false;
		$compatible_w = $misc_obj && isset($misc_obj->c_width)? $misc_obj->c_width : "";
		$compatible_h = $misc_obj && isset($misc_obj->c_height) ? $misc_obj->c_height : "";
		$data_text_props = isset($elm->data_text) ? $elm->data_text: "";

		?>
		<table class="thwecmf-block thwecmf-block-image thwecmf-builder-block" id="tbf_<?php echo esc_attr( $data_id ); ?>" data-block-name="<?php echo esc_attr( $elm_name );?>" data-css-props='<?php echo esc_attr( $data_css ); ?>' data-text-props='<?php echo esc_attr( $data_text_props ); ?>' cellpadding="0" cellspacing="0" align="center" data-misc='<?php echo esc_attr( $misc_css );?>'>
		    <tr>
		    	<td class="thwecmf-image-column">
					<p>
						<img src="<?php echo esc_url( $image ); ?>" alt="Default Image" width="<?php echo esc_attr( $compatible_w ); ?>" height="<?php echo esc_attr( $compatible_h ); ?>"  width="288" height="186"/>
					</p>
      			</td>
      		</tr>
      	</table>
		<?php
	}

	/**
	 * Render divider element block
	 *
	 * @param object $elm element properties
	 * @param string $elm_name element name 
	 */
	private function render_builder_element_block_details_divider($elm, $elm_name){
		$data_id = isset($elm->data_id) ? $elm->data_id : "";
		$data_css = isset($elm->data_css) ? $elm->data_css : "";
		$data_text_props = isset($elm->data_text) ? $elm->data_text: "";
		?>
		<table class="thwecmf-block thwecmf-builder-block thwecmf-block-divider" id="tbf_<?php echo esc_attr( $data_id );?>" data-block-name='<?php echo esc_attr( $elm_name );?>' data-css-props='<?php echo esc_attr( $data_css ); ?>' cellpadding="0" cellspacing="0">
			<tr>
				<td>
					<hr>
				</td>
			</tr>
		</table>
		<?php
	}

	/**
	 * Render gap element block
	 *
	 * @param object $elm element properties
	 * @param string $elm_name element name 
	 */
	private function render_builder_element_block_details_gap($elm, $elm_name){
		$data_id = isset($elm->data_id) ? $elm->data_id : "";
		$data_css = isset($elm->data_css) ? $elm->data_css : "";
		$data_text_props = isset($elm->data_text) ? $elm->data_text: "";
		?>
		<p class="thwecmf-block thwecmf-block-gap thwecmf-builder-block" id="tbf_<?php echo esc_attr( $data_id );?>" data-block-name='<?php echo esc_attr( $elm_name );?>' data-css-props='<?php echo esc_attr( $data_css );?>'></p>
		<?php
	}

	/**
	 * Render social element block
	 *
	 * @param object $elm element properties
	 * @param string $elm_name element name 
	 */
	private function render_builder_element_block_details_social($elm, $elm_name){
		if(isset($elm->data_text)){
			$content = WECMF_Email_Customizer_Utils::is_json_decode($elm->data_text);
			$url1 = isset($content->url1) && !empty($content->url1) ? $content->url1 : "";
			$url2 = isset($content->url2) && !empty($content->url2) ? $content->url2 : "";
			$url3 = isset($content->url3) && !empty($content->url3) ? $content->url3 : "";
			$url4 = isset($content->url4) && !empty($content->url4) ? $content->url4 : "";
			$url5 = isset($content->url5) && !empty($content->url5) ? $content->url5 : "";
			$url6 = isset($content->url6) && !empty($content->url6) ? $content->url6 : "";
			$url7 = isset($content->url7) && !empty($content->url7) ? $content->url7 : "";
		}
		$data_id = isset($elm->data_id) ? $elm->data_id : "";
		$data_css = isset($elm->data_css) ? $elm->data_css : "";
		$social_css = WECMF_Email_Customizer_Utils::is_json_decode($data_css);
		$align = isset($social_css->content_align) ? $social_css->content_align : "";
		$data_text_props = isset($elm->data_text) ? $elm->data_text: "";
		$data_css_comp = isset($elm->data_css_compat) ? $elm->data_css_compat : "";
		$css_comp = WECMF_Email_Customizer_Utils::is_json_decode($data_css_comp);
		$img_width = isset($css_comp->cmp_img_width) ? $css_comp->cmp_img_width : "";
		$img_height = isset($css_comp->cmp_img_height) ? $css_comp->cmp_img_height : "";
		$img_wrap_width = isset($css_comp->cmp_img_wrap_width) ? $css_comp->cmp_img_wrap_width : "";
		$img_wrap_height = isset($css_comp->cmp_img_wrap_height) ? $css_comp->cmp_img_wrap_height : "";
		?>
			<table class="thwecmf-block thwecmf-block-social thwecmf-builder-block" id="tbf_<?php echo esc_attr( $data_id );?>" data-block-name="<?php echo esc_attr( $elm_name );?>" data-css-props='<?php echo esc_attr( $data_css );?>' data-text-props='<?php echo esc_attr( $data_text_props );?>' cellspacing="0" cellpadding="0">
				<tr>
					<td class="thwecmf-social-outer-td" align="<?php echo esc_attr( $align ); ?>">
						<table class="thwecmf-social-inner-tb" cellspacing="0" cellpadding="0">
	  						<tr>
	  							<td class="thwecmf-social-td thwecmf-td-fb">
  									<p class="thwecmf-social-icon"><a href="<?php echo esc_url( $url1 );?>" class="facebook">
      									<img src="<?php echo esc_url( TH_WECMF_ASSETS_URL.'images/facebook.png') ; ?>" alt="Facebook Icon" width="<?php echo esc_attr( $img_width ); ?>" height="<?php echo esc_attr( $img_height ); ?>">
      								</a></p>
      							</td>
			      				<td class="thwecmf-social-td thwecmf-td-mail">
				  					<p class="thwecmf-social-icon"><a href="<?php echo esc_url( $url2 );?>" class="mail">
			    	  					<img src="<?php echo esc_url( TH_WECMF_ASSETS_URL.'images/email.png' ); ?>" alt="Email Icon" width="<?php echo esc_attr( $img_width ); ?>" height="<?php echo esc_attr( $img_height ); ?>">
				      				</a></p>
				      			</td>
				      			<td class="thwecmf-social-td thwecmf-td-tw">
				      				<p class="thwecmf-social-icon"><a href="<?php echo esc_url( $url3 );?>" class="twitter">
			    	  					<img src="<?php echo esc_url( TH_WECMF_ASSETS_URL.'images/twitter.png'); ?>" alt="Twitter Icon" width="<?php echo esc_attr( $img_width ); ?>" height="<?php echo esc_attr( $img_height ); ?>">
				      				</a></p>
				      			</td>
								
				      			<td class="thwecmf-social-td thwecmf-td-yb">
				      				<p class="thwecmf-social-icon"><a href="<?php echo esc_url( $url4 );?>" class="youtube">
			    	  					<img src="<?php echo esc_url( TH_WECMF_ASSETS_URL.'images/youtube.png'); ?>" alt="Youtube Icon" width="<?php echo esc_attr( $img_width ); ?>" height="<?php echo esc_attr( $img_height ); ?>">
				      				</a></p>
				      			</td>
				      			<td class="thwecmf-social-td thwecmf-td-lin">
				      				<p class="thwecmf-social-icon"><a href="<?php echo esc_url( $url5 );?>" class="linkedin">
			    	  					<img src="<?php echo esc_url( TH_WECMF_ASSETS_URL.'images/linkedin.png' ); ?>" alt="Linkedin Icon" width="<?php echo esc_attr( $img_width ); ?>" height="<?php echo esc_attr( $img_height ); ?>">
				      				</a></p>
				      			</td>
				      			<td class="thwecmf-social-td thwecmf-td-pin">
				      				<p class="thwecmf-social-icon"><a href="<?php echo esc_url( $url6 );?>" class="pinterest">
			    	  					<img src="<?php echo esc_url( TH_WECMF_ASSETS_URL.'images/pinterest.png' ); ?>" alt="Pinterest Icon" width="<?php echo esc_attr( $img_width ); ?>" height="<?php echo esc_attr( $img_height ); ?>">
				      				</a></p>
				      			</td>
				      			<td class="thwecmf-social-td thwecmf-td-insta">
				      				<p class="thwecmf-social-icon"><a href="<?php echo esc_url( $url7 );?>" class="instagram">
			    	  					<img src="<?php echo esc_url( TH_WECMF_ASSETS_URL.'/images/instagram.png'); ?>" alt="Instagram Icon" width="<?php echo esc_attr( $img_width ); ?>" height="<?php echo esc_attr( $img_height ); ?>">
				      				</a></p>
				      			</td>
	  						</tr>
	  					</table>		
					</td>
		  		</tr>
	  		</table>
		<?php
	}

	/**
	 * Render button element block
	 *
	 * @param object $elm element properties
	 * @param string $elm_name element name 
	 */
	private function render_builder_element_block_details_button($elm, $elm_name){
		if(isset($elm->data_text)){
			$text_props = WECMF_Email_Customizer_Utils::is_json_decode($elm->data_text);
			$content = isset($text_props->content) && !empty($text_props->content) ? $text_props->content : '' ;
			$data_css = isset($elm->data_css) && !empty($elm->data_css) ? $elm->data_css : '' ;
			$data_url = isset($text_props->url) && !empty($text_props->url) ? $text_props->url : '' ;
			$data_title = isset($text_props->title) && !empty($text_props->title) ? $text_props->title : '' ;
		}
		$data_id = isset($elm->data_id) ? $elm->data_id : "";
		$data_css = isset($elm->data_css) ? $elm->data_css : "";
		$data_text_props = isset($elm->data_text) ? $elm->data_text: "";
		?>
		<table cellspacing="0" cellpadding="0" class="thwecmf-block thwecmf-builder-block thwecmf-button-wrapper-table" id="tbf_<?php echo esc_attr( $data_id );?>" data-block-name="<?php echo esc_attr( $elm_name );?>" data-css-props='<?php echo esc_attr( $data_css );?>' data-text-props='<?php echo esc_attr( $data_text_props );?>' align="center">
              <tr>
                  <td class="thwecmf-button-wrapper">
                      	<a href="<?php echo esc_url( $data_url ); ?>" title="<?php echo esc_attr( $data_title ); ?>" class="thwecmf-button-link" style="text-decoration: none;"><?php echo esc_html( $content ); ?></a>
                  </td>
              </tr>
          </table>
		<?php
	}

	/**
	 * Render gif element block
	 *
	 * @param object $elm element properties
	 * @param string $elm_name element name 
	 */
	private function render_builder_element_block_details_gif($elm, $elm_name){
		if(isset($elm->data_text)){
			$content = WECMF_Email_Customizer_Utils::is_json_decode($elm->data_text);
			$gif = isset($content->upload_img_url) && !empty($content->upload_img_url) ? $content->upload_img_url : TH_WECMF_ASSETS_URL.'/images/placeholder.png';
		}
		$data_id = isset($elm->data_id) ? $elm->data_id : "";
		$data_css = isset($elm->data_css) ? $elm->data_css : "";
		$data_text_props = isset($elm->data_text) ? $elm->data_text: "";
		?>
		<table class="thwecmf-block thwecmf-block-gif thwecmf-builder-block" id="tbf_<?php echo esc_attr( $data_id );?>" data-block-name='<?php echo esc_attr( $elm_name );?>' data-css-props='<?php echo esc_attr( $data_css );?>' data-text-props='<?php echo esc_attr( $data_text_props );?>' cellspacing="0" cellpadding="0">
        	<tr>
        		<td class="thwecmf-gif-column">
        			<p><img src="<?php echo esc_url( $gif ); ?>" alt="Default" /></p>
        		</td>
        	</tr>
        </table>
		<?php
	}

	/**
	 * Render header details element block
	 *
	 * @param object $elm element properties
	 * @param string $elm_name element name 
	 */
	private function render_builder_element_block_details_header($elm, $elm_name){
		if(isset($elm->data_text)){
			$content = WECMF_Email_Customizer_Utils::is_json_decode($elm->data_text);
			if($content){
				$header_title = isset($content->content) && !empty($content->content) ? $content->content : '' ;
				$header_logo = isset($content->upload_img_url) && !empty($content->upload_img_url) ? $content->upload_img_url : "" ;
			}
		}
		$data_id = isset($elm->data_id) ? $elm->data_id : "";
		$data_css = isset($elm->data_css) ? $elm->data_css : "";
		$data_text_props = isset($elm->data_text) ? $elm->data_text: "";
		$data_css_comp = isset($elm->data_css_compat) ? $elm->data_css_compat : "";
		$css_comp = WECMF_Email_Customizer_Utils::is_json_decode($data_css_comp);
		$img_width = isset($css_comp->cmp_img_width) ? $css_comp->cmp_img_width : "";
		$img_height = isset($css_comp->cmp_img_height) ? $css_comp->cmp_img_height : "";
		$img_wrap_width = isset($css_comp->cmp_img_wrap_width) ? $css_comp->cmp_img_wrap_width : "";
		$img_wrap_height = isset($css_comp->cmp_img_wrap_height) ? $css_comp->cmp_img_wrap_height : "";
		?>
		<table class="thwecmf-block thwecmf-block-header thwecmf-builder-block" id="tbf_<?php echo esc_attr( $data_id ); ?>" data-block-name="<?php echo esc_attr( $elm_name );?>" data-css-props='<?php echo esc_attr( $data_css ); ?>' data-text-props='<?php echo esc_attr( $data_text_props ); ?>' cellpadding="0" cellspacing="0">
			<tr class="thwecmf-header-logo-tr">
				<td class="thwecmf-header-logo">
					<p class="thwecmf-header-logo-ph">
						<img src="<?php echo esc_url( $header_logo ); ?>" alt="Logo" width="<?php echo esc_attr( $img_width ); ?>" height="<?php echo esc_attr( $img_height ); ?>">
					</p>
				</td>
			</tr>
			<tr>
				<td class="thwecmf-header-text">
					<h1><?php echo esc_html( $header_title ); ?></h1>
				</td>
			</tr>
		</table>
		<?php
	}

	/**
	 * Render customer details element block
	 *
	 * @param object $elm element properties
	 * @param string $elm_name element name 
	 */
	private function render_builder_element_block_details_customer($elm, $elm_name){
		if(isset($elm->data_text)){
			$details = WECMF_Email_Customizer_Utils::is_json_decode($elm->data_text);
			if($details){
				$details = isset($details->content) && !empty($details->content) ? $details->content : "";
				$data_css = !empty($elm->data_css) ? json_decode($elm->data_css) : '';
				$align = !empty($data_css->align) ? $data_css->align : '';
			}
		}
		$data_id = isset($elm->data_id) ? $elm->data_id : "";
		$data_css = isset($elm->data_css) ? $elm->data_css : "";
		$data_text_props = isset($elm->data_text) ? $elm->data_text: "";
		?>
		<table class="thwecmf-block thwecmf-block-customer thwecmf-builder-block" cellpadding="0" cellspacing="0" id="tbf_<?php echo esc_attr( $data_id ); ?>" data-block-name='<?php echo esc_attr( $elm_name ); ?>' data-css-props='<?php echo esc_attr( $data_css ); ?>' data-text-props='<?php echo esc_attr( $data_text_props ); ?>'>
      		<tr>
      			<td class="thwecmf-address-alignment" align="<?php echo esc_attr( $align ); ?>">
      				<table class="thwecmf-address-wrapper-table" cellpadding="0" cellspacing="0">
      					<tr>
      						<td class="thwecmf-customer-padding">      
      							<h2 class="thwecmf-customer-header"><?php echo esc_html( $details ); ?></h2>
      							<p class="address thwecmf-customer-body">
      								John Smith<br>333-6457<br><a href="#">johnsmith@gmail.com</a>
      							</p>	
      						</td>
      					</tr>
      				</table>
      			</td>
      		</tr>
      	</table>
		<?php
	}

	 /**
     * Text block
     *
     * @param array $css css styles
     * @return string inline styles
     */ 
	private function prepare_order_table_column_json( $css ){
		$ot_td_json = [];
		$default_set = WECMF_Email_Customizer_Utils::get_ot_td_css();
		$css = json_decode( $css, true );
		if( json_last_error() === 0 ){
			foreach ($default_set as $index => $key) {
				$ot_td_json[$index] = isset( $css[$index] ) ? $css[$index] : '';
			}
		}
		return json_encode( $ot_td_json );
	}

	/**
	 * Render order details element block
	 *
	 * @param object $elm element properties
	 * @param string $elm_name element name 
	 */
	private function render_builder_element_block_details_order($elm, $elm_name){
		if($elm->data_text){
			$content = WECMF_Email_Customizer_Utils::is_json_decode($elm->data_text);
			if($content){
				$content = isset($content->content) && !empty($content->content) ? $content->content : "";
				$data_css = !empty($elm->data_css) ? json_decode($elm->data_css) : '';
				$align = !empty($data_css->align) ? $data_css->align : '';
			}
		}
		$data_id = isset($elm->data_id) ? $elm->data_id : "";
		$data_css = isset($elm->data_css) ? $elm->data_css : "";
		$product_css = WECMF_Email_Customizer_Utils::is_json_decode($data_css);
		$show_image = $product_css->product_img == 'block' ? 'show-product-img' : '';
		$data_text_props = isset($elm->data_text) ? $elm->data_text: "";
		$ot_td_css = $this->prepare_order_table_column_json( $data_css );
		$thwec_total = array('label1'=>'Subtotal:','label2'=>'Shipping:','label3'=>'Payment method:','label4'=>'Total:','value1'=>'$20','value2'=>'Free shipping','value3'=>'Cash on delivery','value4'=>'$20');

		$thwec_item = array('item1'=>'T-shirt','item2'=>'Jeans','qty1'=>'1','qty2'=>'1','price1'=>'$5','price2'=>'$15');
		?>
		<span class="loop_start_before_order_table"></span>
		<table class="thwecmf-block thwecmf-block-order thwecmf-builder-block" id="tbf_<?php echo esc_attr( $data_id ); ?>" data-block-name="<?php echo esc_attr( $elm_name ); ?>" cellpadding="0" cellspacing="0" data-css-props='<?php echo esc_attr( $data_css ); ?>' data-text-props='<?php echo esc_attr( $data_text_props ); ?>'>
			<tr class="before_order_table"></tr>
			<tr>
				<td class="thwecmf-order-padding" align="<?php echo esc_attr( $align ); ?>">
					<span class="woocommerce_email_before_order_table"></span>
						<h2 class="thwecmf-order-heading"><u><span class="thwecmf-order-title"><?php echo esc_html( $content ); ?></span>#248</u> (January 22, 2019)</h2>
					<table class="thwecmf-order-table thwecmf-td" style="font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;" cellpadding="0" cellspacing="0">
						<thead>
							<tr>
								<th class="thwecmf-td thwecmf-order-head thwecmf-td-order-product" scope="col" style="">Product</th>
								<th class="thwecmf-td thwecmf-order-head thwecmf-td-order-quantity" scope="col" style="">Quantity</th>
								<th class="thwecmf-td thwecmf-order-head thwecmf-td-order-price" scope="col" style="">Price</th>
							</tr>
						</thead>
						<tbody>
							<tr class="item-loop-start"></tr>
							<?php for($j=1;$j<=2;$j++) { ?>
							<tr class="woocommerce_order_item_class-filter<?php echo $j; ?>">
								<td class="thwecmf-order-item thwecmf-td" style="vertical-align:middle;word-wrap:break-word;">
										<div class="thwecmf-order-item-img <?php echo esc_attr( $show_image ); ?>">
											<img src="<?php echo esc_url( TH_WECMF_ASSETS_URL.'images/product.png' ); ?>" alt="Product Image">
										</div>
										<?php echo esc_html( $thwec_item['item'.$j] ); ?>
								</td>
								<td class="thwecmf-order-item-qty thwecmf-td" style="vertical-align:middle;">
									<?php echo esc_html( $thwec_item['qty'.$j] ); ?>
								</td>
								<td class="thwecmf-order-item-price thwecmf-td" style="vertical-align:middle;">
									<?php echo esc_html( $thwec_item['price'.$j] );?>
								</td>
							</tr>
							<?php } ?>
							<tr class="item-loop-end"></tr>
						</tbody>
						<tfoot class="thwecmf-order-footer">
							<tr class="order-total-loop-start"></tr>
						<?php 
						for($i=1;$i<=4;$i++){ ?>
							<tr class="thwecmf-order-footer-row">
								<th class="thwecmf-order-total-label thwecmf-td" scope="row" colspan="2"><?php echo esc_html( $thwec_total['label'.$i] ); ?></th>
								<td class="thwecmf-order-total-value thwecmf-td"><?php echo esc_html($thwec_total['value'.$i] ); ?></td>
							</tr>
						<?php } ?>
						<tr class="order-total-loop-end" data-ot-css='<?php echo esc_attr( $ot_td_css ); ?>'></tr>
						</tfoot>
					</table>
				</td>
			</tr>
		</table>
		<span class="loop_end_after_order_table"></span>
		<?php
	}

	/**
	 * Render billing element block
	 *
	 * @param object $elm element properties
	 * @param string $elm_name element name 
	 */
	private function render_builder_element_block_details_billing($elm, $elm_name){
		if(isset($elm->data_text)){
			$details = WECMF_Email_Customizer_Utils::thwecmf_is_json_decode($elm->data_text);
			if($details){
				$details = isset($details->content) && !empty($details->content) ? $details->content : "";
				$data_css = !empty($elm->data_css) ? json_decode($elm->data_css) : '';
				$align = !empty($data_css->align) ? $data_css->align : '';
			}
		}
		$data_id = isset($elm->data_id) ? $elm->data_id : "";
		$data_css = isset($elm->data_css) ? $elm->data_css : "";
		$data_text_props = isset($elm->data_text) ? $elm->data_text: "";
		?>
		<table class="thwecmf-block thwecmf-block-billing thwecmf-builder-block" cellpadding="0" cellspacing="0"  id="tbf_<?php echo esc_attr( $data_id );?>" data-block-name='<?php echo esc_attr( $elm_name );?>' data-css-props='<?php echo esc_attr( $data_css );?>' data-text-props='<?php echo esc_attr( $data_text_props );?>'>
  			<tr>
      			<td class="thwecmf-address-alignment" align="<?php echo esc_attr( $align ); ?>">
      				<table class="thwecmf-address-wrapper-table" cellpadding="0" cellspacing="0">
      					<tr>
      						<td class="thwecmf-billing-padding">      
  								<h2 class="thwecmf-billing-header"><?php echo wp_kses_post( $details ); ?></h2>
  								<p class="address thwecmf-billing-body">
  									John Smith<br>
 									252  Bryan Avenue<br>
 									Minneapolis, MN 55412<br>
 									United States (US)
 									<br>333-6457<br><a href="#">johnsmith@gmail.com</a>
  								</p>
  							</td>
  						</tr>
  					</table>
  				</td>
  			</tr>
  		</table>
		<?php
	}
	
	/**
	 * Render shipping element block
	 *
	 * @param object $elm element properties
	 * @param string $elm_name element name 
	 */
	private function render_builder_element_block_details_shipping($elm, $elm_name){
		if(isset($elm->data_text)){
			$details = WECMF_Email_Customizer_Utils::thwecmf_is_json_decode($elm->data_text);
			if($details){
				$details = isset($details->content) && !empty($details->content) ? $details->content : "";
				$data_css = !empty($elm->data_css) ? json_decode($elm->data_css) : '';
				$align = !empty($data_css->align) ? $data_css->align : '';
			}
		}
		$data_id = isset($elm->data_id) ? $elm->data_id : "";
		$data_css = isset($elm->data_css) ? $elm->data_css : "";
		$data_text_props = isset($elm->data_text) ? $elm->data_text: "";
		?>
		<table class="thwecmf-block thwecmf-block-shipping thwecmf-builder-block" cellpadding="0" cellspacing="0" id="tbf_<?php echo esc_attr( $data_id );?>" data-block-name='<?php echo esc_attr( $elm_name );?>' data-css-props='<?php echo esc_attr( $data_css );?>' data-text-props='<?php echo esc_attr( $data_text_props );?>'>
  			<tr>
  				<td class="thwecmf-address-alignment" align="<?php echo esc_attr( $align ); ?>">
  					<table class="thwecmf-address-wrapper-table" cellpadding="0" cellspacing="0">
  						<tr>
  							<td class="thwecmf-shipping-padding">          
			 	 				<h2 class="thwecmf-shipping-header"><?php echo wp_kses_post( $details ); ?></h2>
			  					<p class="address thwecmf-shipping-body">
			 						John Smith<br>
			 						252  Bryan Avenue<br>
			 						Minneapolis, MN 55412<br>
			 						United States (US)
			  					</p>
			  				</td>
			  			</tr>
			  		</table>
  				</td>
  			</tr>
  		</table>
		<?php		
	}

	/**
     * Downloadable product block
     *
     * @param object $elm block object
     * @param string $elm_name block name
     */ 
	private function render_builder_element_block_details_downloadable_product($elm, $elm_name){
		$data_id = isset($elm->data_id) ? $elm->data_id : "";
		$data_name = isset($elm->data_name) ? $elm->data_name : "";
		?>
			<p class="thwecmf-hook-code" id="tbf_<?php echo esc_attr( $data_id );?>">{<?php echo esc_html( $data_name ); ?>}</p>
		<?php
	}

	/**
	 * Render email header hook
	 *
	 * @param object $elm element properties
	 * @param string $elm_name element name 
	 */
	private function render_builder_element_block_details_email_header_hook($elm, $elm_name){
		$data_id = isset($elm->data_id) ? $elm->data_id : "";
		$data_name = isset($elm->data_name) ? $elm->data_name : "";
		?>
			<p class="thwecmf-hook-code" id="tbf_<?php echo esc_attr( $data_id );?>">{<?php echo esc_html( $data_name ); ?>}</p>
		<?php
	}

	/**
	 * Render email order details hook
	 *
	 * @param object $elm element properties
	 * @param string $elm_name element name 
	 */
	private function render_builder_element_block_details_email_order_details_hook($elm, $elm_name){
		$data_id = isset($elm->data_id) ? $elm->data_id : "";
		$data_name = isset($elm->data_name) ? $elm->data_name : "";
		?>
			<p class="thwecmf-hook-code" id="tbf_<?php echo esc_attr( $data_id );?>">{<?php echo esc_html( $data_name );?>}</p>
		<?php		
	}

	/**
	 * Render email before order table hook
	 *
	 * @param object $elm element properties
	 * @param string $elm_name element name 
	 */
	private function render_builder_element_block_details_before_order_table_hook($elm, $elm_name){
		$data_id = isset($elm->data_id) ? $elm->data_id : "";
		$data_name = isset($elm->data_name) ? $elm->data_name : "";
		?>
			<p class="thwecmf-hook-code" id="tbf_<?php echo esc_attr( $data_id );?>">{<?php echo esc_html( $data_name );?>}</p>
		<?php		
	}

	/**
	 * Render email after order table hook
	 *
	 * @param object $elm element properties
	 * @param string $elm_name element name 
	 */
	private function render_builder_element_block_details_after_order_table_hook($elm, $elm_name){
		$data_id = isset($elm->data_id) ? $elm->data_id : "";
		$data_name = isset($elm->data_name) ? $elm->data_name : "";
		?>	
			<p class="thwecmf-hook-code" id="tbf_<?php echo esc_attr( $data_id );?>">{<?php echo esc_html( $data_name );?>}</p>
		<?php		
	}

	/**
	 * Render email order meta hook
	 *
	 * @param object $elm element properties
	 * @param string $elm_name element name 
	 */
	private function render_builder_element_block_details_order_meta_hook($elm, $elm_name){
		$data_id = isset($elm->data_id) ? $elm->data_id : "";
		$data_name = isset($elm->data_name) ? $elm->data_name : "";
		?>	
			<p class="thwecmf-hook-code" id="tbf_<?php echo esc_attr( $data_id );?>">{<?php echo esc_html( $data_name );?>}</p>
		<?php		
	}

	/**
	 * Render customer address hook
	 *
	 * @param object $elm element properties
	 * @param string $elm_name element name 
	 */
	private function render_builder_element_block_details_customer_address_hook($elm, $elm_name){
		$data_id = isset($elm->data_id) ? $elm->data_id : "";
		$data_name = isset($elm->data_name) ? $elm->data_name : "";
		?>
			<p class="thwecmf-hook-code" id="tbf_<?php echo esc_attr( $data_id );?>">{<?php echo esc_html( $data_name );?>}</p>
		<?php		
	}

	/**
	 * Render email footer hook
	 *
	 * @param object $elm element properties
	 * @param string $elm_name element name 
	 */
	private function render_builder_element_block_details_email_footer_hook($elm, $elm_name){
		$data_id = isset($elm->data_id) ? $elm->data_id : "";
		$data_name = isset($elm->data_name) ? $elm->data_name : "";
		?>
			<p class="thwecmf-hook-code" id="tbf_<?php echo esc_attr( $data_id );?>">{<?php echo esc_html( $data_name );?>}</p>
		<?php		
	}

	 /**
	 * Render builder element styles
	 *
	 * @param array $wrapper_id stylesheet id
	 */
	private function render_template_builder_css_section($wrapper_id) {
		?>

		<style id="<?php echo esc_attr( $wrapper_id ); ?>" type="text/css">
			.thwecmf-main-builder{
				max-width:600px;
				width:600px;
				margin: auto; 
			}

			.thwecmf-block a{
                color: <?php echo esc_html( WECMF_Email_Customizer_Utils::get_template_global_css('link-color') ); ?>;
                text-decoration: <?php echo esc_html( WECMF_Email_Customizer_Utils::get_template_global_css('link-decoration') ); ?>;
            }

            <?php if( apply_filters( 'thwec_enable_global_link_color', false ) ){ ?>
                .thwecmf-main-builder .thwecmf-block a{
                    color: <?php echo esc_html( WECMF_Email_Customizer_Utils::get_template_global_css('link-color') ); ?>;
                    text-decoration: <?php echo esc_html( WECMF_Email_Customizer_Utils::get_template_global_css('link-decoration') ); ?>;
                }
            <?php } ?>

			.thwecmf-main-builder .thwecmf-builder-column{
				background-color:#ffffff;
				vertical-align: top;
				border-top-width: 1px;
				border-right-width: 1px;
				border-bottom-width: 1px;
				border-left-width: 1px;
				border-style: solid;
				border-color: #dedede;
				border-radius: 2px;
				background-size: cover;
				background-repeat: no-repeat;
			}
			.thwecmf_wrapper{
				background-color: #f7f7f7;
				margin: 0; 
				width: 100%;
			}
			.thwecmf-row{
				border-spacing: 0px;
			}

			.thwecmf-row,
			.thwecmf-block{
				width:100%;
				table-layout: fixed;
			}
			.thwecmf-block td{
				padding: 0;
			}
			.thwecmf-layout-block{
				overflow: hidden;
			}
			.thwecmf-row td{
				vertical-align: top;
				box-sizing: border-box;
			}
			.thwecmf-block-one-column,
			.thwecmf-block-two-column,
			.thwecmf-block-three-column,
			.thwecmf-block-four-column{
				max-width: 100%;
				margin: 0 auto;
				border:0px solid transparent;
			}

			.thwecmf-row .thwecmf-columns{
				border-top-width: 1px;
				border-right-width: 1px;
				border-bottom-width: 1px;
				border-left-width: 1px;
				border-style: dotted;
				border-color: #dddddd;
				word-break: break-word;
				padding: 10px 10px;
				text-align: center;
			}
			.thwecmf-block-one-column > tbody > tr > td{
				width: 100%;				
			}
			.thwecmf-block-two-column > tbody > tr > td{
				width: 50%;				
			}

			.thwecmf-block-divider{
				margin: 0;
			}

			.thwecmf-block-divider td{
				padding: 20px 0px;
				text-align: center;
			}

			.thwecmf-block-divider hr{
				display: inline-block;
				border:none;
				border-top: 2px solid transparent;
				border-color: gray;
				width:70%;
				height: 2px;
				margin: 0 auto;
			}

			.thwecmf-block-text{
				width: 100%;
				color: #636363;
				font-family: "Helvetica Neue",Helvetica,Roboto,Arial,sans-serif;
				font-size: 13px;
				line-height: 22px;
				text-align:center;
				margin: 0 auto;
				box-sizing: border-box;
			}

			.thwecmf-block-text .thwecmf-block-text-holder{
				color: #636363;
				font-family: "Helvetica Neue",Helvetica,Roboto,Arial,sans-serif;
				font-size: 13px;
				line-height: 150%;
				text-align: center;
				padding: 15px 15px;
				border-top-width: 0px;
				border-right-width: 0px;
				border-bottom-width: 0px;
				border-left-width: 0px;
				border-color: transparent;
				border-style: none;
				background-size: cover;
				background-repeat: no-repeat;
			}

			.thwecmf-block-image{
				width: auto;
				height: auto;
				max-width: 600px;
				box-sizing: border-box;
				width: 100%;
			}

			.thwecmf-block-image td.thwecmf-image-column{
				text-align: center;
				padding: 10px 0px;
				vertical-align: middle;
				line-height: 0;
			}

			.thwecmf-block-image p{
				margin: 0;
				display: inline-block;
				width: 50%;
				border-top-width: 0px;
				border-right-width: 0px;
				border-bottom-width: 0px;
				border-left-width: 0px;
				border-style: none;
				border-color: transparent;
			}

			.thwecmf-block-image img{
				width:100%;
				height:auto;
				max-width: 100%;
				max-height: 100%;
				display:block;
			}

			 .thwecmf-block-header{
                overflow: hidden;
                text-align: center;
                box-sizing: border-box;
                position: relative;
                width:100%;
                height: auto;
                margin:0 auto;
                max-width: 100%;
                background-size: 100%;
                background-repeat: no-repeat;
                background-position: center;
                background-color:#0099ff;
                border-top-width: 0px;
                border-right-width: 0px;
                border-bottom-width: 0px;
                border-left-width: 0px;
                border-style: none;
                border-color: transparent;
            }
            
            .thwecmf-block-header .thwecmf-header-logo{
                text-align: center;
                font-size: 0;
                line-height: 1;
                padding: 15px 5px 15px 5px;
            }

            .thwecmf-block-header .thwecmf-header-logo-tr{
                display: none;
            }

            .thwecmf-block-header .thwecmf-header-logo-ph{
                width:155px;
                height: 103px;
                margin:0 auto;
                border-top-width: 0px;
                border-right-width: 0px;
                border-bottom-width: 0px;
                border-left-width: 0px;
                border-style: none;
                border-color: transparent;
                display: inline-block;
            }

            .thwecmf-block-header .thwecmf-header-logo-ph img{
                width:100%;
                height:100%;
                display: block;
                max-width: 100%;
                max-height: 100%;
            }

            .thwecmf-block-header .thwecmf-header-text{
                padding: 30px 0px 30px 0px; 
                font-size: 0;
            }

            .thwecmf-block-header .thwecmf-header-text h1{
                margin:0 auto;
                width: 100%;
                max-width: 100%;
                color:#ffffff;
                font-size:40px;
                font-weight:300;
                mso-line-height-rule: exactly;
                line-height:100%;
                vertical-align: middle;
                text-align:center;
                font-family: Georgia, serif;
                border:1px solid transparent;
                box-sizing: border-box; 
            }

            .thwecmf-block-header .thwecmf-header-text h3{
                padding:0px;
                margin:0;
                color:#ffffff;
                font-size:22px;
                font-weight:300;
                text-align:center;
                font-family: times;
                line-height:150%;       
            }

            .thwecmf-block-header .thwecmf-header-text p{
                margin:0 auto;
                width: 100%;
                max-width: 100%;
                color:#ffffff;
                font-size:40px;
                font-weight:300;
                mso-line-height-rule: exactly;
                line-height:150%;
                text-align:center;
                font-family: Georgia, serif;
                border:1px solid transparent;
                box-sizing: border-box; 
            }

			.thwecmf-block-shipping .thwecmf-shipping-padding,
			.thwecmf-block-billing .thwecmf-billing-padding,
			.thwecmf-block-customer .thwecmf-customer-padding{
				padding-top: 5px;
				padding-right: 0px;
				padding-bottom: 2px;
				padding-left: 0px;
			}

			.thwecmf-block-billing,
			.thwecmf-block-shipping,
			.thwecmf-block-customer,
			.thwecmf-block-shipping .thwecmf-address-alignment,
			.thwecmf-block-billing .thwecmf-address-alignment,
			.thwecmf-block-customer .thwecmf-address-customer{
				margin: 0;
				padding:0;
				border: 0px none transparent;
				border-collapse: collapse;
				box-sizing: border-box;
			}

			.thwecmf-block-billing .thwecmf-address-wrapper-table,
			.thwecmf-block-shipping .thwecmf-address-wrapper-table{
				background-size: cover;
				background-repeat: no-repeat;
			}

			.thwecmf-block-billing .thwecmf-address-wrapper-table,
			.thwecmf-block-shipping .thwecmf-address-wrapper-table,
			.thwecmf-block-customer .thwecmf-address-wrapper-table{
				width:100%;
				height: 115px;
				border-top-width: 0px;
				border-right-width: 0px;
				border-bottom-width: 0px;
				border-left-width: 0px;
				border-style: none;
				border-color: transparent;
			}

			.thwecmf-block-billing .thwecmf-billing-header,
			.thwecmf-block-shipping .thwecmf-shipping-header,
			.thwecmf-block-customer .thwecmf-customer-header {
				color:#0099ff;
				display:block;
				font-family:"Helvetica Neue",Helvetica,Roboto,Arial,sans-serif;
				font-size:18px;
				font-weight:bold;
				line-height:150%;
				text-align:center;
				margin: 0px;
			}

			.thwecmf-block-billing .thwecmf-billing-body,
			.thwecmf-block-shipping .thwecmf-shipping-body,
			.thwecmf-block-customer .thwecmf-customer-body {
				text-align:center;
				line-height:150%;
				border:0px !important;
				font-family: 'Helvetica Neue',Helvetica,Roboto,Arial,sans-serif;
				font-size: 13px;
				padding: 0px 0px 0px 0px;
				margin: 13px 0px;
			}

			.thwecmf-block-custom-hook{
				margin: 0;
				line-height: 0;
			}

			.thwecmf-block-gap{
				height:48px;
				margin: 0;
				box-sizing: border-box;
				border-top-width: 0px;
				border-right-width: 0px;
				border-bottom-width: 0px;
				border-left-width: 0px;
				border-style: none;
				border-color: transparent;
			}

			 .thwecmf-block-social{
                text-align: center;
                width:100%;
                box-sizing: border-box;
                background-size: cover;
                background-repeat: no-repeat;
                background-color: transparent;
                margin: 0 auto;
                border-top-width: 0px;
                border-right-width: 0px;
                border-bottom-width: 0px;
                border-left-width: 0px;
                border-style: none;
                border-color: transparent;
            }

            .thwecmf-block-social .thwecmf-social-outer-td{
                padding-top: 0px;
                padding-right: 0px;
                padding-bottom: 0px;
                padding-left: 0px;
            }

            .thwecmf-block-social .thwecmf-social-td{
                padding: 15px 3px 15px 3px;
                font-size: 0;
                line-height: 1px;
            }

            .thwecmf-block-social .thwecmf-social-icon{
                width: 40px;
                height: 40px;
                margin: 0px;
                text-decoration:none;
                box-shadow:none;
            }
    
            .thwecmf-block-social .thwecmf-social-icon img {
                width: 100%;
                height: 100%;
                display:block;
                max-width: 100%;
                max-height: 100%;
            }

            .thwecmf-button-wrapper-table{
                width: 80px;
                margin: 0 auto;
                padding-top: 10px;
                padding-right: 0px;
                padding-bottom: 10px;
                padding-left: 0px;
            }

            .thwecmf-button-wrapper-table td{
                border-radius: 2px;
                background-color: #4169e1;
                text-align: center;
                padding: 10px 0px;
                border-top-width: 1px;
                border-right-width: 1px;
                border-bottom-width: 1px;
                border-left-width: 1px;
                border-style: solid;
                border-color: #4169e1;
                text-decoration: none;
                color: #fff;
                font-size: 13px;
                vertical-align: middle;
            }

            .thwecmf-button-wrapper-table td a.thwecmf-button-link{
                color: #fff;
                line-height: 150%;
                font-size: 13px;
                text-decoration: none;
                font-family: 'Helvetica Neue',Helvetica,Roboto,Arial,sans-serif;
            }

            .thwecmf-block-gif{
                margin: 0;
                width: 100%;
                height: auto;
                max-width: 600px;
                box-sizing: border-box;
            }

            .thwecmf-block-gif td.thwecmf-gif-column{
                text-align: center;
            }

            .thwecmf-block-gif td.thwecmf-gif-column p{
                margin: 0;
                width: 50%;
                padding: 10px 10px;
                display: inline-block;
                vertical-align: top;
                border-top-width: 0px;
                border-right-width: 0px;
                border-bottom-width: 0px;
                border-left-width: 0px;
                border-style: none;
                border-color: transparent;
            }

            .thwecmf-block-gif td.thwecmf-gif-column img {
                width:100%;
                height:auto;
                display:block;
                max-width: 100%;
                max-height: 100%;
            }

             .thwecmf-block-order{
                background-color: white;
                margin: 0 auto;
                background-size: 100%;
                background-repeat: no-repeat;
                background-position: center;
                background-size: cover;
                border-top-width: 0px;
                border-right-width: 0px;
                border-bottom-width: 0px;
                border-left-width: 0px;
                border-color: transparent;
                border-style: none;
            }

            .thwecmf-block-order td{
                word-break: unset;
            }

            .thwecmf-block-order .thwecmf-order-padding {
                padding:20px 48px;
            }

            .thwecmf-block-order .thwecmf-order-heading {
                font-size:18px;
                text-align:left;
                line-height:100%;
                color: #4286f4;
                font-family: 'Helvetica Neue',Helvetica,Roboto,Arial,sans-serif;
            }

            .thwecmf-block-order .thwecmf-order-table {
                table-layout: fixed;
                width:100%;
                font-family: "Helvetica Neue",Helvetica,Roboto,Arial,sans-serif;
                color: #636363;
                border: 1px solid #e5e5e5;
                border-collapse:collapse;
            }
            .thwecmf-block-order .thwecmf-td {
                color: #636363;
                border: 1px solid #e5e5e5;
                padding:12px;
                text-align: left;
                font-size: 14px;
                line-height: 150%;
            }

            <?php if( apply_filters( 'thwec_order_table_column_auto_width', true ) ) : ?>
                .thwecmf-order-table td,
                .thwecmf-order-table th{
                    word-break: keep-all;
                }

                .thwecmf-block-order .thwecmf-order-table {
                    table-layout: auto;
                }
            <?php endif; ?>

            .thwecmf-block-order .thwecmf-order-item-img{
                margin-bottom: 5px;
                display: none;
            }
            .thwecmf-block-order .thwecmf-order-item-img img{
                width: 32px;
                height: 32px;
                display: inline;
                height: auto;
                outline: none;
                line-height: 100%;
                vertical-align: middle;
                margin-right: 10px;
                text-decoration: none;
                text-transform: capitalize;
            }

            .thwecmf_downloadable_table td,
            .thwecmf_downloadable_table th{
            	font-size: 14px; ?>;
            }

		</style>
		<style id="<?php echo esc_attr( $wrapper_id ); ?>_override" type="text/css">
			<?php if($this->template_json_css !== ''){
				echo wp_kses( stripslashes( $this->template_json_css ), 'strip' );
			}?>

		</style>
		<style id="<?php echo esc_attr( $wrapper_id ); ?>_preview_override" type="text/css">
			<?php if($this->template_json_css){
				$json_css_override = str_replace('tbf_', 'tpf_', $this->template_json_css);
				echo wp_kses( stripslashes( $json_css_override ), 'strip' ); 
			}?>
		</style>
		<?php
	}

	 /**
	 * Render form field horizontal seperator
	 *
	 * @param array $atts field style properties
	 * @param boolean $icon whether to show icon or not
	 */
	public function render_form_fragment_h_separator($atts = array(),$icon=false){
		$args = shortcode_atts( array(
			'colspan' 	     => '',
			'padding-top'    => '5px',
			'padding-bottom' => '',
			'border-style'   => 'dashed',
    		'border-width'   => '1px',
			'border-color'   => '#e6e6e6',
			'content'	     => '',
			'class'			 => 'thwecmf-seperator-heading',
			'padding'  		 => '8px 0px 6px 5px',
			'font-size'  	 => '13px'
		), $atts );
		$style  = $args['padding-bottom'] ? 'padding-bottom:'.$args['padding-bottom'].';' : '';
		$style .= $args['padding'] ? 'padding:'.$args['padding'].';' : '';
		$style .= $args['border-style'] ? ' border-bottom:'.$args['border-width'].' '.$args['border-style'].' '.$args['border-color'].';' : '';
		?>
		<tr class="thwecmf-spacer"><td></td></tr>
        <tr><td colspan="<?php echo esc_attr( $args['colspan'] ); ?>" style="<?php echo esc_attr( $style ); ?>" class="<?php echo esc_attr( $args['class'] ); ?>"><?php echo esc_html( $args['content'] ); 
        if($icon){
        	echo '<span class="dashicons dashicons-arrow-down-alt2 thwecmf-direction-arrow"></span>';
        }
        ?>	
        </td></tr>
        <tr class="thwecmf-spacer"><td></td></tr>
        <?php
	}

	/**
	 * Render form field tooltip
	 *
	 * @param array $tooltip tooltip content
	 */
	public function render_form_element_tooltip($tooltip){
        $tooltip_html = '';
        if($tooltip){
            $tooltip_html = '<a href="javascript:void(0)" title="'. esc_attr( $tooltip ).'" class="thpladmin_tooltip"><span class="dashicons dashicons-editor-help"></span></a>';
        }
        ?>
        <td style="width: 26px; padding:0px;"><?php echo wp_kses( $tooltip_html ,wp_kses_allowed_html('post') ); ?></td>
        <?php
    }

    /**
	 * Render form fields
	 *
	 * @param array $field field properties
	 * @param array $atts arguments to render fields
	 * @param boolean $render_cell whether to wrap field html in a table cell
	 * @return string $field_html html string of the field
	 */
    public function render_form_fields($field, $atts = array(), $render_cell = true){
		$premium_class = false;
		if($field && is_array($field)){
			$args = shortcode_atts( array(
				'label_cell_props' => '',
				'input_cell_props' => '',
				'label_cell_colspan' => '',
				'input_cell_colspan' => '',
			), $atts );

			$pro_feature = '';
			
			$ftype     = isset($field['type']) ? $field['type'] : 'text';
			$flabel    = isset($field['label']) && !empty($field['label']) ? __($field['label']) : '';
			$sub_label = isset($field['sub_label']) && !empty($field['sub_label']) ? __($field['sub_label']) : '';
			$tooltip   = isset($field['hint_text']) && !empty($field['hint_text']) ? __($field['hint_text']) : '';
			$template_error = isset($field['template_error']) && !empty($field['template_error']) ? $field['template_error'] : '';
			$fclass    = isset($field['class']) && !empty($field['class']) ? $field['class'] : false;

			$field_html = '';
			$additional_data = '';

			if($ftype == 'text'){
				$field_html = $this->render_form_fields_inputtext($field, $atts);
				
			}
			else if($ftype == 'hidden'){
				$field_html = $this->render_form_fields_hidden($field,$atts);
			}else if($ftype == 'textarea'){
				$field_html = $this->render_form_fields_textarea($field, $atts);
				   
			}else if($ftype == 'select'){
				$field_html = $this->render_form_fields_select($field, $atts);     
				
			}else if($ftype == 'multiselect'){
				$field_html = $this->render_form_fields_multiselect($field, $atts);     
				
			}else if($ftype == 'colorpicker'){
				$field_html = $this->render_form_fields_colorpicker($field, $atts);
				$additional_data .= 'thwecmf-color-picker-wrapper';              
            
			}else if($ftype == 'twoside'){
				$field_html = $this->render_form_fields_twoside($field, $atts);              
            
			}else if($ftype == 'fourside'){
				$field_html = $this->render_form_fields_fourside($field, $atts);              
            
			}else if($ftype == 'alignment-icons'){
				$field_html = $this->render_form_fields_alignment_icon($field, $atts);
			}else if($ftype == 'checkbox'){
				$field_html = $this->render_form_fields_checkbox($field, $atts, $render_cell);   
			}else if($ftype == 'radio'){
				$field_html = $this->render_form_fields_radio($field, $atts, $render_cell);
			}else if($ftype == 'number'){
				$field_html = $this->render_form_fields_number($field, $atts);   
			}else if($ftype == 'range'){
				$field_html = $this->render_form_fields_range_slider($field, $atts);
			}

			if($render_cell && $render_cell !== 'template-map'){
				$required_html = isset($field['required']) && $field['required'] ? '<abbr class="required" title="required">*</abbr>' : '';
				$label_cell_props = !empty($args['label_cell_props']) ? $args['label_cell_props'] : '';
				$input_cell_props = !empty($args['input_cell_props']) ? $args['input_cell_props'] : '';
				echo '<tr class="thwecmf-input-spacer"><td></td></tr>';
				if($flabel){
					$flabel = $premium_class ? $flabel = $flabel.$pro_feature : $flabel;
				?>
				<tr>
					<td class="<?php echo esc_attr( $label_cell_props ); ?>">
						<?php 
						echo $flabel;
						echo $required_html;
						if($sub_label){
							?>
							<br/><span class="thpladmin-subtitle"><?php echo $sub_label; ?></span>
							<?php 
						}
						?>
					</td>
				</tr>
				<?php } ?>
				<tr>
					<td class="<?php echo esc_attr( $additional_data ); ?>" >
						<?php 
						echo $field_html;
						?>
					</td>
				</tr>
				<?php
				if($template_error){
					echo '<tr><td><span class="thwecmf-missing-template-warning">Template not found</span></td></tr>';
				}
			}else if($render_cell=='template-map'){
				$required_html = isset($field['required']) && $field['required'] ? '<abbr class="required" title="required">*</abbr>' : '';
				$label_cell_props = !empty($args['label_cell_props']) ? $args['label_cell_props'] : '';
				$input_cell_props = !empty($args['input_cell_props']) ? $args['input_cell_props'] : '';
				?>
				<tr>
					<td <?php echo esc_attr( $label_cell_props ); ?> >
						<?php 
						echo esc_html( $flabel ); 
						echo $required_html;
						if($sub_label){
							?>
							<br/><span class="thpladmin-subtitle"><?php echo esc_html( $sub_label ); ?></span>
							<?php 
						}
						?>
					</td>
					<td <?php echo esc_attr( $additional_data ); ?> >
						<?php echo $field_html; ?>
					</td>
				</tr>
				<?php
			}else{
				echo '<div class="thpladmin-input-wrapper">';
				$title = '<span class="thpladmin-title">'.$flabel.$pro_feature.'</span>';
				echo $title.$field_html;
				echo '</div>';
			}
		}
	}

	/**
	 * Render form field inputtext
	 *
	 * @param array $field field properties
	 * @param array $atts arguments to render fields
	 * @return string $field_html html string of the field
	 */
	private function render_form_fields_inputtext($field, $atts = array()){
		$field_html = '';
		if($field && is_array($field)){
			$field_props = $this->prepare_form_field_props($field, $atts);
			$fclass = isset($field['class']) ? $field['class'] : '';
			$field_html = '<input type="text" class="'.esc_attr( $fclass ).'" '. $field_props .' />';
		}
		return $field_html;
	}
	
	/**
	 * Render form field hidden
	 *
	 * @param array $field field properties
	 * @param array $atts arguments to render fields
	 * @return string $field_html html string of the field
	 */
	private function render_form_fields_hidden($field, $atts = array()){
		$field_html = '';
		if($field && is_array($field)){
			$field_props = $this->prepare_form_field_props($field, $atts);
			$field_html = '<input type="hidden" '. $field_props .' />';
		}
		return $field_html;
	}

	/**
	 * Render form field textarea
	 *
	 * @param array $field field properties
	 * @param array $atts arguments to render fields
	 * @return string $field_html html string of the field
	 */
	private function render_form_fields_textarea($field, $atts = array()){
		$field_html = '';
		if($field && is_array($field)){
			$args = shortcode_atts( array(
				'rows' => '4',
				'cols' => '100',
			), $atts );
		
			$fvalue = isset($field['value']) ? $field['value'] : '';
			$field_props = $this->prepare_form_field_props($field, $atts);
			$field_html = '<textarea '. $field_props .' rows="'.esc_attr( $args['rows'] ).'" cols="'.esc_attr( $args['cols'] ).'" >'.$fvalue.'</textarea>';
		}
		return $field_html;
	}
	
	/**
	 * Render form field select
	 *
	 * @param array $field field properties
	 * @param array $atts arguments to render fields
	 * @return string $field_html html string of the field
	 */
	private function render_form_fields_select($field, $atts = array()){
		$field_html = '';
		if($field && is_array($field)){
			$fvalue = isset($field['value']) ? $field['value'] : '';
			$field_props = $this->prepare_form_field_props($field, $atts);
			
			$field_html = '<select '. $field_props .' >';
			foreach($field['options'] as $value => $label){
				$selected = $value == $fvalue ? 'selected' : '';
				$field_html .= '<option value="'. esc_attr( trim($value) ).'" '.$selected.'>'. __($label) .'</option>';
			}
			$field_html .= '</select>';
		}
		return $field_html;
	}
	
	/**
	 * Render form field multiselect
	 *
	 * @param array $field field properties
	 * @param array $atts arguments to render fields
	 * @return string $field_html html string of the field
	 */
	private function render_form_fields_multiselect($field, $atts = array()){
		$field_html = '';
		if($field && is_array($field)){
			$field_props = $this->prepare_form_field_props($field, $atts);
			
			$field_html = '<select multiple="multiple" '. $field_props .' class="thpladmin-enhanced-multi-select" >';
			foreach($field['options'] as $value => $label){
				$field_html .= '<option value="'. esc_attr( trim($value) ).'" >'. __($label) .'</option>';
			}
			$field_html .= '</select>';
		}
		return $field_html;
	}
	
	/**
	 * Render form field radio
	 *
	 * @param array $field field properties
	 * @param array $atts arguments to render fields
	 * @param boolean $render_cell whether to wrap field html in a table cell
	 * @return string $field_html html string of the field
	 */
	private function render_form_fields_radio($field, $atts = array(), $render_cell = true){
		$field_html = '';
		$args = shortcode_atts( array(
			'label_props' => '',
			'cell_props'  => 3,
			'render_input_cell' => false,
			'render_label_cell' => false,
			), $atts );

		$atts = array(
		'input_width' => 'auto',
		);

		if($field && is_array($field)){
			$fvalue = isset($field['value']) ? $field['value'] : '';	
			$fclass = isset($field['class']) && !empty($field['class']) ? 'r_f'. $field['class'] : '';
			$onchange = isset($field['onchange']) && !empty($field['onchange']) ? ' onchange="'.$field['onchange'].'"' : '';
			foreach ($field['options'] as $value => $label) {
				$checked ='';
				$flabel = isset($label['name']) && !empty($label['name']) ? __($label['name'],'') : '';
				$checked = $value === $fvalue ? 'checked' : '';
				$selected = $value === $fvalue ? 'rad-selected' : '';	
				$field_html .= '<input type="radio" name="i_' . esc_attr( $field['name'] ) . '" class="'.esc_attr( $fclass ).'"value="'. esc_attr( trim($value) ).'" ' . $checked . $onchange . '/>'.$label;
			}
		}	
		return $field_html;
	}

	/**
	 * Render form field checkbox
	 *
	 * @param array $field field properties
	 * @param array $atts arguments to render fields
	 * @param boolean $render_cell whether to wrap field html in a table cell
	 * @return string $field_html html string of the field
	 */
	private function render_form_fields_checkbox($field, $atts = array(), $render_cell = true){
		$field_html = '';
		if($field && is_array($field)){
			$args = shortcode_atts( array(
				'label_props' => '',
				'cell_props'  => 3,
				'render_input_cell' => false,
			), $atts );
		
			$fid 	= 'a_f'. $field['name'];
			$fclass = isset($field['class']) && !empty($field['class']) ? 'c_f'. $field['class'] : '';
			$flabel = isset($field['label']) && !empty($field['label']) ? __($field['label']) : '';
			
			$field_props  = $this->prepare_form_field_props($field, $atts);
			$field_props .= $field['checked'] ? ' checked' : '';
			$field_html  = '<input type="checkbox" id="'. $fid .'" class="'.esc_attr( $fclass ).'" '.$field_props .' />';
			$field_html .= '<label for="'. esc_attr( $fid ) .'" '. $args['label_props'] .' > '. $flabel .'</label>';
		}
		if(!$render_cell && $args['render_input_cell']){
			return '<td '. $args['cell_props'] .' >'. $field_html .'</td>';
		}else{
			return $field_html;
		}
	}
	
	/**
	 * Render form field number
	 *
	 * @param array $field field properties
	 * @param array $atts arguments to render fields
	 * @param boolean $render_cell whether to wrap field html in a table cell
	 * @return string $field_html html string of the field
	 */
	private function render_form_fields_number($field, $atts = array(), $render_cell = true){
		$field_html = '';
		if($field && is_array($field)){
			$flabel = isset($field['label']) && !empty($field['label']) ? __($field['label']) : '';
			$fmin = isset($field['min']) ? __($field['min']) : '';			
			$fmax = isset($field['max']) && !empty($field['max']) ? __($field['max']) : '';
			$fstep = isset($field['step']) && !empty($field['step']) ? __($field['step']) : '';
			$field_props  = 'min="'.esc_attr( $fmin ).'" max="'.esc_attr( $fmax ).'" step="'.esc_attr( $fstep ).'"';
			$field_props .= $this->prepare_form_field_props($field, $atts);
			$field_html = '<input type="number" '. $field_props .' />';
		}
		return $field_html;
	}

	/**
	 * Render form field slider
	 *
	 * @param array $field field properties
	 * @param array $atts arguments to render fields
	 * @param boolean $render_cell whether to wrap field html in a table cell
	 * @return string $field_html html string of the field
	 */
	private function render_form_fields_range_slider($field, $atts = array(), $render_cell = true){
		$field_html = '';
		if($field && is_array($field)){
			$flabel = isset($field['label']) && !empty($field['label']) ? __($field['label']) : '';
			$fmin = isset($field['min']) ? __($field['min']) : '';			
			$fmax = isset($field['max']) && !empty($field['max']) ? __($field['max']) : '';
			$fstep = isset($field['step']) && !empty($field['step']) ? __($field['step']) : '';
			$field_props  = 'min="'.esc_attr( $fmin ).'" max="'.esc_attr( $fmax ).'" step="'.esc_attr( $fstep ).'"';
			$field_props .= $this->prepare_form_field_props($field, $atts);
			$field_html = '<input type="range" '. $field_props .' />';
		}
		return $field_html;
	}
	
	/**
	 * Render form field colorpicker
	 *
	 * @param array $field field properties
	 * @param array $atts arguments to render fields
	 * @return string $field_html html string of the field
	 */
	private function render_form_fields_colorpicker($field, $atts = array()){
		$field_html = '';
		if($field ){
			// error_log('onsode');
			$margin = isset($atts['input_margin']) ? $atts['input_margin'] : '';
			$atts = array('input_width' => '105px','input_height'=>'30px','input_font_size' => '13px');
			$atts['input_margin'] = $margin;

			$field_props = $this->prepare_form_field_props($field, $atts);
			
			$field_html  = '<span class="thpladmin-colorpickpreview '.esc_attr( $field['name'] ).'_preview" style=""></span>';
            $field_html .= '<input type="text" '. $field_props .' class="thpladmin-colorpick" size="8" autocomplete="off"/>';
		}
		return $field_html;
	}

	/**
	 * Render form field with two input fields in a row
	 *
	 * @param array $field field properties
	 * @param array $atts arguments to render fields
	 * @return string $field_html html string of the field
	 */
	private function render_form_fields_twoside($field, $atts = array()){
				$field_html = '';
		if($field && is_array($field)){
			$fclass  = isset($field['class']) ? $field['class'] : '';
			$fclass .= ' size-input-group';
			$atts_width = array('input_name_suffix' => '_width', 'input_margin' => '0 6px 0 0', 'input_width'=>'136px', 'input_height'=>'30px', 'input_b_r' => '4px', 'input_font_size' => '13px');
			$atts_height = array('input_name_suffix' => '_height','input_width'=>'136px', 'input_height'=>'30px', 'input_b_r' => '4px', 'input_font_size' => '13px');
			$field_props_width = $this->prepare_form_field_props($field, $atts_width);
			$field_props_height = $this->prepare_form_field_props($field, $atts_height);
			$field_html  = '<input type="text" placeholder="Width" class="'.esc_attr( $fclass ).'" '. $field_props_width .' />';
			$field_html .= '<input type="text" placeholder="Height" class="'.esc_attr( $fclass ).'" '. $field_props_height .' />';
		}
		return $field_html;
	}

	/**
	 * Render form field with four inputs in a row
	 *
	 * @param array $field field properties
	 * @param array $atts arguments to render fields
	 * @return string $field_html html string of the field
	 */
	private function render_form_fields_fourside($field, $atts = array()){
		$field_html = '';
		if($field && is_array($field)){
			$fclass  = isset($field['class']) ? $field['class'] : '';
			$fclass .= ' input-group';
			$atts_top = array('input_name_suffix' => '_top', 'input_margin' => '0 6px 0 0','input_width'=>'65px','input_height'=>'30px','input_b_r' => '4px', 'input_font_size' => '13px');
			$atts_right = array('input_name_suffix' => '_right', 'input_margin' => '0 6px 0 0','input_width'=>'65px','input_height'=>'30px','input_b_r' => '4px', 'input_font_size' => '13px');
			$atts_bottom = array('input_name_suffix' => '_bottom', 'input_margin' => '0 6px 0 0','input_width'=>'65px','input_height'=>'30px','input_b_r' => '4px', 'input_font_size' => '13px');
			$atts_left = array('input_name_suffix' => '_left','input_width'=>'65px','input_height'=>'30px','input_b_r' => '4px', 'input_font_size' => '13px');
			$field_props_top = $this->prepare_form_field_props($field, $atts_top);
			$field_props_right = $this->prepare_form_field_props($field, $atts_right);
			$field_props_bottom = $this->prepare_form_field_props($field, $atts_bottom);
			$field_props_left = $this->prepare_form_field_props($field, $atts_left);
			$field_html = '<div class="thpladmin-input-wrapper"><span class="thpladmin-title thwecmf-input-sublabel">Top</span>';
			$field_html  .= '<input type="text" placeholder="Top" class="'.esc_attr( $fclass ).'" '. $field_props_top .' /></div>';
			$field_html .= '<div class="thpladmin-input-wrapper"><span class="thpladmin-title thwecmf-input-sublabel">Right</span>';
			$field_html .= '<input type="text" placeholder="Right" class="'.esc_attr( $fclass ).'" '. $field_props_right .' /></div>';
			$field_html .= '<div class="thpladmin-input-wrapper"><span class="thpladmin-title thwecmf-input-sublabel">Bottom</span>';
			$field_html .= '<input type="text" placeholder="Bottom" class="'.esc_attr( $fclass ).'" '. $field_props_bottom .' /></div>';
			$field_html .= '<div class="thpladmin-input-wrapper"><span class="thpladmin-title thwecmf-input-sublabel">Left</span>';
			$field_html .= '<input type="text" placeholder="Left" class="'.esc_attr( $fclass ).'" '. $field_props_left .' /></div>';
		}
		return $field_html;
	}

	/**
	 * Render form field alignment icon
	 *
	 * @param array $field field properties
	 * @param array $atts arguments to render fields
	 * @return string $field_html html string of the field
	 */
	public function render_form_fields_alignment_icon($field, $atts = array()){
		$field_html = '';
		if($field && is_array($field)){
			$field_props = $this->prepare_form_field_props($field, $atts);
			$fclass = isset($field['class']) ? $field['class'] : '';
			$wrap_suffix = $fclass === 'thwecmf-premium-disabled-input' ? "icon-wrap" : "img-wrapper"; 
			$active_icon = $fclass === 'thwecmf-premium-disabled-input' ? " thwecmf-active-icon" : ""; 
			$field_html = '<div class="thwecmf-aligment-icon-wrapper '.esc_attr( $fclass ).'">';
			$field_html .= '<div class="'.esc_attr( $wrap_suffix ).'" data-align="left" style="margin-right:6px;"><img src='.esc_url( TH_WECMF_ASSETS_URL.'images/align-left.svg').' alt="left"></div>';
			$field_html .= '<div class="'.esc_attr( $wrap_suffix.$active_icon ).'" data-align="center" style="margin-right:6px;"><img src='.esc_url( TH_WECMF_ASSETS_URL.'images/align-center.svg' ).' alt="center"></div>';
			$field_html .= '<div class="'.esc_attr( $wrap_suffix ).'" data-align="right" style="margin-right:6px;"><img src='.esc_url( TH_WECMF_ASSETS_URL.'images/align-right.svg' ).' alt="right"></div>';
			if(isset($field['icon_flag']) && $field['icon_flag']){
				$field_html .= '<div class="'.esc_attr( $wrap_suffix ).'" data-align="justify"><img src='.esc_url(TH_WECMF_ASSETS_URL.'images/align-justify.svg').' alt="justify"></div>';
			}
			if($fclass !== 'thwecmf-premium-disabled-input'){
				$field_html .= '<br><input type="hidden" class="'.esc_attr( $fclass ).'" '. $field_props .' />';
			}
			$field_html .= '</div>';
		}
		return $field_html;
	}

	/**
	 * Prepare form field properties
	 *
	 * @param array $field field properties
	 * @param array $atts arguments to render fields
	 * @return string $field_props html string of the field properties
	 */
	private function prepare_form_field_props($field, $atts = array()){
		$field_props = '';
		$args = shortcode_atts( array(
			'input_width' => '',
			'input_height' => '',
			'input_name_prefix' => 'i_',
			'input_name_suffix' => '',
			'input_margin' => '',
			'input_b_r' => '',
			'input_font_size' => '',
		), $atts );
		
		$ftype = isset( $field['type'] ) ? $field['type'] : 'text';
		$fclass    = isset($field['class']) && !empty($field['class']) ? sanitize_html_class( $field['class'] ) : false;
		if($fclass && $fclass == 'thwecmf-premium-disabled-input'){
			$pro_feature .= '<span class="th-premium-feature-msg">Pro</span>';
		}
		if($ftype == 'multiselect' && isset( $args['input_name_suffix'] ) ){
			$args['input_name_suffix'] = $args['input_name_suffix'].'[]';
		}
		$fname  = $args['input_name_prefix'].$field['name'].$args['input_name_suffix'];
		$fvalue = isset($field['value']) ? $field['value'] : '';
		$input_width  = $args['input_width'] ? 'width:'.$args['input_width'].';' : '';
		$input_height  = $args['input_height'] ? 'height:'.$args['input_height'].';' : '';
		$input_margin  = $args['input_margin'] ? 'margin:'.$args['input_margin'].';' : '';
		$input_b_r = $args['input_b_r'] ? 'border-radius:'.$args['input_b_r'].';' : '';
		$input_font_size = $args['input_font_size'] ? 'font-size:'.$args['input_font_size'].';' : '';
		$field_props  = 'name="'. esc_attr( $fname ).'" style="'. esc_attr( $input_width.$input_height.$input_b_r.$input_margin.$input_font_size ).'"';
		if($ftype !== 'select'){
			$field_props  .= 'value="'. esc_attr( $fvalue ).'"';
		}
		$field_props .= ( isset($field['placeholder']) && !empty($field['placeholder']) ) ? ' placeholder="'.esc_attr( $field['placeholder'] ).'"' : '';
		$field_props .= ( isset($field['class']) && !empty($field['class']) ) ? ' class="'.esc_attr( $field['class'] ).'"' : '';
		$field_props .= ( isset($field['onchange']) && !empty($field['onchange']) ) ? ' onchange="'.esc_attr( $field['onchange'] ).'"' : '';
		$field_props .= ( isset($field['class']) && !empty($field['class']) && $field['class'] == 'thwecmf-premium-disabled-input' ) ? 'disabled' : '';
		return $field_props;
	}

	/**
	 * Get the url of the templates page
	 *
	 * @param string $tab tab url parameter
	 * @param string $section section url parameter
	 * @return string url of the template page
	 */
	public function get_admin_url($tab = false, $section = false){
		$url = 'admin.php?page=thwecmf_email_customizer';
		if($tab && !empty($tab)){
			$url .= '&tab='. $tab;
		}
		if($section && !empty($section)){
			$url .= '&section='. $section;
		}
		return admin_url($url);
	}

	/**
	 * Test mail modal content
	 *
	 */
	public function template_test_email_box(){
		$user_email = apply_filters('thwecmf_set_testmail_recepient', true) ? THWECMF_LOGIN_USER : "";
		?>
		<div id="thwecmf_modal_test_mail" style="display: none;">
			<div class="thwecmf-model-header">
				<span class="dashicons dashicons-no-alt thwecmf-hover" onclick="thwecmfCloseModal(this)"></span>
			</div>
			<div class="thwecmf-modal-body">
				<p><b>Email id</b></p>
				<div class="thwecmf-test-mail-modal">
					<input type="text" class="thwecmf-test-mail-input" id="test_mail_input" name="test_mail_input" placeholder="Enter an email id" value="<?php echo esc_attr( sanitize_email( $user_email ) ); ?>">
					<button class="button btn btn-primary thwecmf-hover" onclick="thwecmfClickTestMailAction(this)">Send</button>
				</div>
				<p><i>Enter an email id and click Send button, to see how the email template looks in email clients</i></p>
				<div class="thwecmf-action-validation-box">
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Render builder page modal
	 *
	 */
	private function render_customizer_modal_block(){
		?>
		<div id="thwecmf_builder_header_action_box">
			<div class="content-box"></div>
		</div>
		<?php
	}
}
endif;