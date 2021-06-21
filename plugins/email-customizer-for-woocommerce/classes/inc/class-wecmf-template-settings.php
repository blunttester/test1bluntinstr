<?php
/**
 * Woo Email Customizer
 *
 * @author    ThemeHiGH
 * @category  Admin
 */

if(!defined('ABSPATH')){ exit; }

if(!class_exists('WECMF_Template_Settings')):
class WECMF_Template_Settings extends WECMF_Builder_Settings {
	protected static $_instance = null;
	private $cell_props_L = array();
	private $cell_props_CB = array();
	private $section_props = array();
	private $image_props;
	private $default_settings = array();
	private $edit_url;
	private $template_status = array();
	private $template_list = array();
	private $map_msgs = array();
	private $templates = array();

	public function __construct() {
		parent::__construct('template_settings', '');
		$this->init_constants();
	}
	
	public static function instance() {
		if(is_null(self::$_instance)){
			self::$_instance = new self();
		}
		return self::$_instance;
	}
	
	public function init_constants(){
		$this->cell_props = array( 
			'label_cell_props' => 'width="18%"', 
			'input_width' => '350px',  
		);
		$this->cell_props_L = array( 
			'label_cell_props' => 'width="25%"', 
			'input_cell_props' => 'width="34%"', 
			'input_width' => '350px',  
		);
		
		$this->edit_url = $this->get_admin_url();

		$this->image_props = 'style="width:100%;height:100%;"'; 

		$this->default_settings = array('default'=>'Default');
		
		$this->template_status =array(
			'0'=>'admin-new-order',
			'1'=>'admin-cancelled-order',
			'2'=>'admin-failed-order',
			'3'=>'customer-completed-order',
			'4'=>'customer-on-hold-order',
			'5'=>'customer-processing-order',
			'6'=>'customer-refunded-order',
			'7'=>'customer-invoice',
			'8'=>'customer-note',
			'9'=>'customer-reset-password',
			'10'=>'customer-new-account',
		);
		$this->map_msgs = array(
			true	=> array(
				'msg' 	=> 	array(
					'save'				=>	'Settings Saved',
					'reset'				=>	'Template Settings Successfully Reset',
					'reset-template'	=>	'Template Reset Successfully',
				),
				'class'		=>	'thwecmf-save-success',
			),
			false	=> array(
				'msg' 	=> 	array(
					'save'				=>	'Your changes were not saved due to an error (or you made none!)',
					'reset'				=>	'Reset not done due to an error (or nothing to reset!)',
					'reset-template'	=>	'An error occured while reseting the template ( or nothing to reset )',
				),
				'class'		=>	'thwecmf-save-error',
			),
		);
	}

	/**
     * Render the contents of the page
     *
     */
	public function render_page(){
		$this->output_tabs();
		$this->render_content();
	}

	/**
     * Prepare template settings to be saved
     *
	 * @return  string $settings settings to be saved
     */
	private function prepare_settings(){
		$settings = WECMF_Email_Customizer_Utils::thwecmf_get_template_settings();
		$template_map = isset( $settings[WECMF_Email_Customizer_Utils::SETTINGS_KEY_TEMPLATE_MAP] ) ? $settings[WECMF_Email_Customizer_Utils::SETTINGS_KEY_TEMPLATE_MAP] : array();
		$file_ext = 'php';
		foreach ($_POST['i_template-list'] as $key => $value) {
			$template_map[$this->template_status[sanitize_text_field( $key )]] = sanitize_text_field($value);
		}
		$settings[WECMF_Email_Customizer_Utils::SETTINGS_KEY_TEMPLATE_MAP] = $template_map;
		return $settings;
	}

	/**
     * Save the template mapping
     *
	 * @return  string message to be displayed after saving
     */
	private function save_settings(){
		$result = false;
		if( !isset($_POST['i_template-list']) || !isset( $_POST['thwecmf_template_map'] ) || !wp_verify_nonce( $_POST['thwecmf_template_map'], 'template_map_action' ) || !WECMF_Email_Customizer_Utils::is_user_capable() ){
			wp_die( '<div class="wecm-wp-die-message">Action failed. Could not verify nonce.</div>' );
		}

		$temp_data = array();
   		$settings = $this->prepare_settings();
   		$result = WECMF_Email_Customizer_Utils::thwecmf_save_template_settings($settings);
		
		return $this->get_action_message( $result, 'save' );
	}

	/**
     * Get the path of the template
     *
	 * @param  string $template name of template file
	 * @param  boolean $backup choose between custom/default template
	 * @return string $file path of the desired template file
     */
	private function get_template_path( $template, $backup=false ){
		$file = $template.'.php';
		if( $backup ){
			$file = esc_url( TH_WECMF_T_PATH ).$file;
		}else{
			$file = esc_url( THWECMF_CUSTOM_T_PATH ).$file;
		}
		return $file;
	}

	/**
     * Reset the template to default
     *
	 * @return string message to be displayed based on the action
     */
	private function reset_template(){
		$result = false;
		$file_reset = false;
		$template = isset( $_POST['i_template_name'] ) ? sanitize_text_field( $_POST['i_template_name'] ) : false;
		if( $template ){
			if( !wp_verify_nonce( $_POST['thwecmf_edit_template_'.$template], 'thwecmf_edit_template' ) || !WECMF_Email_Customizer_Utils::is_user_capable() ){
				wp_die( '<div class="wecm-wp-die-message">Action failed. Could not verify nonce.</div>' );
			}
			$result = WECMF_Email_Customizer_Utils::thwecmf_reset_templates( $template );
			$edited_template = $this->get_template_path( $template );
			$backup_template = $this->get_template_path( $template, true );
			if( file_exists( $backup_template ) ){
				$file_reset = copy( $backup_template,$edited_template );
			}
		}
		return $this->get_action_message( $result || $file_reset, 'reset-template');
	}

	/**
     * Reset the template map settings
     *
	 * @return string message to be displayed based on the action
     */
	private function reset_settings(){
		$result = false;

		if( !isset( $_POST['thwecmf_template_map'] ) || !wp_verify_nonce( $_POST['thwecmf_template_map'], 'template_map_action' ) || !WECMF_Email_Customizer_Utils::is_user_capable() ){
			wp_die( '<div class="wecm-wp-die-message">Action failed. Could not verify nonce.</div>' );
		}else{
			$result = $this->reset_to_default();
		}
		return $this->get_action_message( $result, 'reset' );
	}

	/**
     * Reset the template map settings to default
     *
	 * @return boolean settings reset or not
     */
	public function reset_to_default() {
		$settings = WECMF_Email_Customizer_Utils::thwecmf_reset_template_map();
		$result = WECMF_Email_Customizer_Utils::thwecmf_save_template_settings($settings);
		return $result;
	}

	/**
     * Get the message and message class based on the action provided
     *
	 * @param  boolean $map_result action was successful or not
	 * @param  string $map_action name of the action
	 * @return array $result array containing the message and message class based on the action
     */
	private function get_action_message( $map_result, $map_action ){
		$result = false;
		if( !is_null( $map_result ) && $map_action ){
			$result['class'] = isset( $this->map_msgs[$map_result]['class'] ) ? $this->map_msgs[$map_result]['class'] : '';
			$result['msg'] = isset( $this->map_msgs[$map_result]['msg'][$map_action] ) ? $this->map_msgs[$map_result]['msg'][$map_action] : '';
		}
		return $result;
	}

	/**
     * Render the page content and manage page actions
     *
     */
	private function render_content(){
		$response = '';

		if( isset( $_POST['save_settings'] ) ){
			$response = $this->save_settings();
		
		}else if( isset( $_POST['reset_settings'] ) ){
			$response = $this->reset_settings();

		}else if( isset( $_POST['reset_template'] ) ){
			$response = $this->reset_template();
		}
		if( is_array( $response ) ){
			?>	
			<div id="thwecmf_temp_map_save_messages" class="thwecmf-show-save <?php echo esc_attr($response['class']); ?>">
				<?php echo esc_html($response['msg']); ?>
			</div>
			<script type="text/javascript">
				jQuery(function($) {
				    setTimeout(function(){
						$("#thwecmf_temp_map_save_messages").remove();
					}, 2000);
				});
			</script>
		<?php
		}
		$this->init_field_form_props();
		$this->render_manage_templates(true);
		$this->render_template_mapping(true);
		$this->render_template_manager_table();
    }

    /**
     * Load the template mapping data
     *
     */
    public function init_field_form_props(){
		$this->template_map = WECMF_Email_Customizer_Utils::thwecmf_get_template_map();
	}

	/**
     * Render the template manage tab of the page where custom created templates can be managed
     *
     */

	 public function render_manage_templates( $render = true ){
		if( $render ){
			echo '<table id="thwecmf_manage_template_table" style="display:none;"><tbody>';
		}
		$this->render_template_manage_header();
		$this->prepare_templates_list();

		if( $render ){
			echo '</tbody></table>';
		}
	}

	/**
     * Render the manage template header
	 *
     */
	public function render_template_manage_header(){
		?>
		<tr class="thwecmf-template-manager-header-links">
			<td colspan="2">
				<h3 class="thwecmf-template-title">Templates</h3>
			</td>
		</tr>
		<?php
	}

	/**
     * Render the templates manage tab table
     *
     */
	public function prepare_templates_list(){
		$link_tab = '';
		?>
		<tr>
			<td colspan="2">
				<table class="wc_emails widefat thpladmin-form-email-notification-table">
					<thead>
						<tr>
							<th class="thwecmf-template-column-name">Name</td>
							<th class="thwecmf-template-column-assigned">
								Assigned To
							</th>
						</tr>
					</thead>
					<tbody>
						<?php $this->render_templates_list(); ?>
					</tbody>
				</table>
			</td>
		</tr>
		<?php
	}

	/**
     * Render the custom templates list in the manage template tab
     *
     */
	public function render_templates_list(){
		foreach (WECMF_Email_Customizer_Utils::email_statuses() as $tkey => $tvalue) {
			$tkey = str_replace('-', '_', $tkey);
			$this->render_settings( $tkey, $tvalue, false, '' );
		}
	}

	/**
     * Render the rows of the template manage tab table
     *
     */
	public function render_settings( $key, $label ){
		?>
		<tr>
			<td class="thwecmf-template-manage-columns thwecmf-template-column-name">
				<?php 
				echo '<p class="thwecmf-template-single-title">'.esc_html( $label ).'</p>'; 
				$this->template_action_links( $key );
				?>		
			</td>
			<td class="thwecmf-template-manage-columns thwecmf-template-column-assigned thwecmf-template-column-user">
				<?php 
				echo $this->get_assigned_to_status( $key ); 
				?>
			</td>
		</tr>
		<?php
	}

	/**
     * Render the assigned status of each custom templates in the manage template tab
     *
	 * @param  string $key template key of the assigned template
	 * @return string assigned email status name if any or '--' string
     */
	public function get_assigned_to_status( $key ){
		$email_status = array();
		if( in_array( $key, $this->template_map ) ){
			$status = array_keys( $this->template_map, $key );
			if( is_array( $status ) ){
				foreach ($status as $skey => $svalue) {
					if( array_key_exists( $svalue, WECMF_Email_Customizer_Utils::email_statuses() )  ){
						array_push( $email_status, WECMF_Email_Customizer_Utils::email_statuses()[$svalue] );
					}
				}
			}
			
		}
		return !empty( $email_status ) ? implode(', ' , $email_status) : '--';
	}

	/**
     * Render the template action links - Edit, Reset
     *
	 * @param  string $key template key
     */
	public function template_action_links( $key ){
		?>
		<form name="thwecmf_edit_template_form_<?php echo esc_attr( $key ); ?>" action="" method="POST">
			<?php
		    	if ( function_exists('wp_nonce_field') ){
					wp_nonce_field( 'thwecmf_edit_template', 'thwecmf_edit_template_'.$key );
		    	}
		    ?>
			<input type="hidden" name="i_template_name" value="<?php echo esc_attr( $key ); ?>">
			<button type="submit" class="thwecmf-template-action-links" formaction="<?php echo esc_url( $this->edit_url ); ?>" name="i_edit_template">Edit</button> | 
			<button type="submit" class="thwecmf-template-action-links thwecmf-reset-link" name="reset_template">Reset</button>
		</form>
		<?php
	}

	/**
     * Render the template mapping contents
     *
	 * @param  boolean $render whether to display it or hide it in a table for later use
     */

	public function render_template_mapping( $render = true ){
		
		if( $render ){
			echo '<table id="thwecmf_map_template_table" style="display:none;"><tbody>';
		}
		$this->render_template_mapping_subheader();
		$template_map = WECMF_Email_Customizer_Utils::thwecmf_get_template_map();
		$this->render_map_template_form();
		if( $render ){
			echo '</tbody></table>';
		}
	}

	/**
     * Render the template mapping tab subheader
     *
     */
	public function render_template_mapping_subheader(){
		?>
		<tr>
			<td colspan="2">
				<i class="thwecmf-template-map-subtitle">You can assign the custom templates to the corresponding WooCommerce transaction emails here.</i>
			</td>
		</tr>
        <tr class="thwecmf-spacer"><td></td></tr>
        <?php
	}

	/**
     * Render the template mapping form
     *
     */
	private function render_map_template_form(){
		?>
		<tr>
			<td colspan="2">            
			    <form name="template_map_form" action="" method="POST">
			    	<input type="hidden" name="thwecmf_notification_tab" value="mapping">
			    	<?php
			    	if ( function_exists('wp_nonce_field') ){
						wp_nonce_field( 'template_map_action', 'thwecmf_template_map' );
			    	}
			    	$this->render_woocommerce_email_notificaiton_table();
			    	?>
	                <p class="submit">
						<input type="submit" name="save_settings" class="button-primary" value="Save changes" onclick="return thwecmfTemplateMapValidation(this)">
	                    <input type="submit" name="reset_settings" class="button" value="Reset to default" 
						onclick="return confirm('Are you sure you want to reset to default settings? all your changes will be deleted.');">
	            	</p>
	            </form>
		    </td>
		</tr>       
    	<?php
    }

    /**
     * Render template mapping form table
     *
     */
    private function render_woocommerce_email_notificaiton_table(){
    	$settings = $this->template_map;
    	?>
    	<table class="wc_emails widefat thwecmf-mapping-table" id="thpladmin-form-email-notification-table" cellspacing="0">
			<thead>
				<tr>
					<?php
					$columns =  array(
							'name'       => 'Email',
							'email_type' => 'Template',
					);
					foreach ( $columns as $key => $column ) {
						echo '<th class="wc-email-settings-table-' . esc_attr( $key ) . '">' . esc_html( $column ) . '</th>';
					}
					?>
				</tr>
			</thead>
			<tbody>
				<?php
				$field = array( 'name' => 'template-list[]','class'=>'thwecmf-template-map-select2','label' => '', 'type' => 'select','options' => array('' => 'Default Template'));
					foreach (WECMF_Email_Customizer_Utils::email_statuses() as $key => $label) {
						$status = str_replace('-', '_', $key);
						$email_field = $field;
						$email_field['label'] = $label;
						$email_field['options'] = array_merge( $field['options'], array( $status => $label ));

						if($key !=='section_map_templates'){
							if(is_array($settings) && isset($settings[$key])){
								$email_field['value'] = $settings[$key];
							}
							$this->render_form_fields($email_field, $this->cell_props_L,'template-map');
						}
					}
				?>
			</tbody>
		</table>
    	<?php
    }

    /**
     * Render templates submenu main table
     *
     */
    public function render_template_manager_table(){
		 $notif_tab = isset( $_POST['thwecmf_notification_tab'] ) && !empty( $_POST['thwecmf_notification_tab'] ) ? sanitize_key( $_POST['thwecmf_notification_tab'] ) : 'manage';
		$manage_class = $map_class = 'thwecmf-template-manage-tabs';
	
		if( $notif_tab == 'mapping' ){
			$map_class .= ' thwecmf-template-manage-active';
		}else{
			$manage_class .= ' thwecmf-template-manage-active';
		}
		?>
		<div id="thwecmf_template_manager">
			<table id="wecmf_email_template_manager_table" cellspacing="0">
				<thead>
					<tr>
						<th class="<?php echo $manage_class; ?>" data-name="manage">
							<?php echo esc_html__( 'Manage Template', 'woocommerce-email-customizer-pro' ); ?>
						</th>
						<th class="<?php echo $map_class; ?>" data-name="mapping">
							<?php echo esc_html__( 'Template Mapping', 'woocommerce-email-customizer-pro' ); ?>
						</th>
					</tr>
				</thead>
				<tbody>
					<?php 
					if( $notif_tab == 'manage' ){
						$this->render_manage_templates( false );
					}else{
						$this->render_template_mapping( false );
					}
					?>
				</tbody>
			</table>
		</div>
		<?php
	}
	
}
endif;