<?php
/**
 * Woo Email Customizer Setting Page
 *
 * @author   ThemeHiGH
 * @category Admin
 */

if(!defined('ABSPATH')){ exit; }

if(!class_exists('WECMF_Settings_Page')) :
abstract class WECMF_Settings_Page{
	protected $page_id = '';		
	protected $tabs = '';
    protected $get_params = '';

	public function __construct() {
		$this->tabs = array( 'fields' => 'Manage Product Options');
        $this->get_params = array(
            'premium' =>'Unlock Premium to Create Unlimited Templates. You can edit the available templates and create any designs', 
            'datamissing' => 'Something went wrong. Please try again.');
	}

	public function get_tabs(){
		return $this->tabs;
	}
	
	public function get_current_tab(){
		return $this->page_id;
	}
		
	public function output_tabs(){
		$current_tab = $this->get_current_tab();
		$tabs = $this->get_tabs();
		if(empty($tabs)){
			return;
		}
		if( WECMF_Email_Customizer_Utils::show_banner() ){
			$this->output_premium_version_notice();	
		}
	}

	public function output_premium_version_notice(){
		?>
		<table id="wecmf_upgrade" cellpadding="0" cellspacing="0">
			<tr>
		    	<td>
		            <img src="<?php echo plugins_url( '../assets/images/premium-upgrade.png', __FILE__ ); ?>" />
		            <a type="button" class="button btn btn-thwecmf-pro" href="https://www.themehigh.com/product/woocommerce-email-customizer/">Explore Pro</a>
		            <a type="button" class="button btn btn-thwecmf-demo" href="https://flydemos.com/wecm/">Demo</a>
		            <span class="dashicons dashicons-no-alt thwecmf-dashicon-delete"></span>
		        </td>
		    </tr>
		</table>
        <?php
	}
	
}
endif;