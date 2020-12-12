<?php
// create custom plugin settings menu
add_action('admin_menu', 'baw_create_menu');

function baw_create_menu() {

	//create new top-level menu
	add_menu_page('BAW Plugin Settings', 'BAW Settings', 'administrator', __FILE__, 'baw_settings_page',plugins_url('/images/icon.png', __FILE__));

	//call register settings function
	add_action( 'admin_init', 'register_mysettings' );
}


function create_merchant_id( ) { 

    $target_url = 'https://rpcapi.checkout.fi/reseller/createMerchant';

    $username = $this->merchant_id; // reseller api user
    $password = $this->merchant_secret; // reseller api password

    $postdata = http_build_query(
         array(
             'company'  => $_POST'company_name'],                 // Company name
             'vat_id'   => $_POST'company_vat_id'],               // Vat id/business id
             'name'     => $_POST'company_marketing_name'],       // Marketing name of company/name of the service
             'email'    => $_POST'company_email'],                // Email of customer service, extranet password and api merchant secret will be sent here
             'gsm'      => $_POST'company_gsm'],                  // customer service phone number
             'address'  => $_POST'company_address'],
             'url'      => $_POST'company_url'],
             'type'     => '4',                                 // shop-in-shop vendor = 5, shop-in-shop marketplace = 4
             'info'     => $_POST['company_info'],                 // General info text about the merchant
             'kkhinta'  => 0,                                   // 0, 25 or 45, this value is the merchants monthly payment in €, if using a custom pricing use pricing code (that checkout finland will create), if creating marketplace use 0
         )
    );

    $opts = array(
     'http'     => array(
     'method'   => "POST",
     'header'   => "Content-Type: application/x-www-form-urlencoded\r\nAuthorization: Basic " . base64_encode("$username:$password"),
     'content'  => $postdata
    ));

    $context    = stream_context_create($opts);
    $response   = file_get_contents($target_url, false, $context);

    if ($response) {

        $merchant = @simplexml_load_string($response);

        if(isset($merchant->id)) 
        {
            $this->merchant = $merchant;        // Merhants ID
            
        }
    }

} // End create_merchant_id 

function create_mechant_id_page() { ?>
<div class="wrap">
<h2>Create Mechant ID - Checkout Finland</h2>

<form method="post" action="">
    <?php //settings_fields( 'baw-settings-group' ); ?>
    <?php //do_settings_sections( 'baw-settings-group' ); ?>

    <table class="form-table">
        <tr valign="top">
        <th scope="row">Company Name</th>
        <td><input type="text" name="company_name" value="<?php echo esc_attr( get_option('company_name') ); ?>" /></td>
        </tr>
         
        <tr valign="top">
        <th scope="row">Company Vat Id</th>
        <td><input type="text" name="company_vat_id" value="<?php echo esc_attr( get_option('company_vat_id') ); ?>" /></td>
        </tr>
        
        <tr valign="top">
        <th scope="row">Company Marketing Name</th>
        <td><input type="text" name="company_marketing_name" value="<?php echo esc_attr( get_option('company_marketing_name') ); ?>" /></td>
        </tr>

        <tr valign="top">
        <th scope="row">Company Email</th>
        <td><input type="text" name="company_email" value="<?php echo esc_attr( get_option('company_email') ); ?>" /></td>
        </tr>

        <tr valign="top">
        <th scope="row">Company GSM</th>
        <td><input type="text" name="company_gsm" value="<?php echo esc_attr( get_option('company_gsm') ); ?>" /></td>
        </tr>

        <tr valign="top">
        <th scope="row">Company Address</th>
        <td><input type="text" name="company_address" value="<?php echo esc_attr( get_option('company_address') ); ?>" /></td>
        </tr>

        <tr valign="top">
        <th scope="row">Company URL</th>
        <td><input type="text" name="company_url" value="<?php echo esc_attr( get_option('company_url') ); ?>" /></td>
        </tr>     

         <tr valign="top">
        <th scope="row">Company Info</th>
        <td><input type="text" name="company_info" value="<?php echo esc_attr( get_option('company_info') ); ?>" /></td>
        </tr>     
    </table>
    
    <?php submit_button(); ?>

</form>
</div>
<?php } ?>