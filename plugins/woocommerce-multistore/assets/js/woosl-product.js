    jQuery(document).ready(function()
        {
            jQuery('#woonet_data input[type="checkbox"]').change( function() {
                woonet_checkbox(this);
            } );

            jQuery('#woonet_toggle_all_sites').change(function() {
                if(jQuery(this).is(":checked")) {
                    
                    jQuery('#woonet_data input[type="checkbox"]._woonet_publish_to').each(function() {
                        if(jQuery(this).prop('disabled')    ==  false)
                            {
                                jQuery(this).prop('checked', true);
                                woonet_checkbox( this );
                                woonet_publsih_to_site_checkbox(jQuery(this));
                            }    
                    })
     
                }
                else {
                    jQuery('#woonet_data input[type="checkbox"]._woonet_publish_to').each(function() {
                        if(jQuery(this).prop('disabled')    ==  false)
                            {
                                jQuery(this).prop('checked', false);
                                woonet_checkbox( this );
                                woonet_publsih_to_site_checkbox(jQuery(this));
                            }    
                    })     
                }
                    
            });
            
            
            jQuery('#woonet_toggle_child_product_inherit_updates').change(function() {
                if(jQuery(this).is(":checked")) {
                    
                    jQuery('#woonet_data input[type="checkbox"]._woonet_publish_to_child_inheir').each(function() {
                        if(jQuery(this).prop('disabled')    ==  false)
                            {
                                jQuery(this).prop('checked', true);
                                woonet_checkbox( this );
                            }
                    })
     
                }
                else {
                    jQuery('#woonet_data input[type="checkbox"]._woonet_publish_to_child_inheir').each(function() {
                        if(jQuery(this).prop('disabled')    ==  false)
                            {
                                jQuery(this).prop('checked', false);
                                woonet_checkbox( this );
                            }
                    })     
                }
                    
            });
            
            
            
            jQuery('#woonet_data input[type="checkbox"]._woonet_publish_to').change(function() {
                        woonet_publsih_to_site_checkbox(jQuery(this));
                    });

            jQuery('#woonet_data input[type="checkbox"]._woonet_publish_to').each( function( index, element ) {
                // console.log( element, jQuery(element).is(":checked") );
                woonet_publsih_to_site_checkbox( element );
            } );

            jQuery('#woonet_data input[type="checkbox"]').each( function( index, element ) {
                woonet_checkbox( element );
            } );
    });
    
    function woonet_checkbox( element ) {
        jQuery( element ).prev('input[type="hidden"]').val(
            jQuery( element ).is(":checked") ? 'yes' : 'no'
        );
    }

    function woonet_publsih_to_site_checkbox(element)
        {
            
            var group_id    =   jQuery(element).closest('p.form-field').attr('data-group-id');
            
            if(jQuery(element).is(":checked")) {
                    jQuery('#woonet_data').find('.form-field.group_' + group_id).slideDown();
                    jQuery(element).closest('p.form-field').find('.description .warning').slideUp();
                }
                else {
                    jQuery('#woonet_data').find('.form-field.group_' + group_id).slideUp();
                    
                    if(jQuery(element).attr('data-default-value')   !=  '')
                        jQuery(element).closest('p.form-field').find('.description .warning').slideDown();
                }   
            
        }